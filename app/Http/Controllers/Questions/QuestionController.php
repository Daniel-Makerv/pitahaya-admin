<?php

namespace App\Http\Controllers\Questions;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionBase;
use App\Models\QuestionSection;
use App\Models\QuestionType;
use App\Models\TypeForm;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class QuestionController extends Controller
{
    /* =========================================================
     |  Preguntas principales (question_sections)
     * ========================================================= */

    /**
     * Lista de preguntas principales + banco de preguntas generales.
     */
    public function index()
    {
        $preguntasPrincipales = QuestionSection::withCount('questions')
            ->orderBy('id')
            ->get()
            ->map(fn(QuestionSection $section) => [
                'id'              => $section->id,
                'title'           => $section->title,
                'description'     => $section->description,
                'total_preguntas' => $section->questions_count,
                'created_at'      => $section->created_at?->format('d/m/Y'),
            ]);

        return Inertia::render('Questions/Index', [
            'preguntasPrincipales' => $preguntasPrincipales,
            'preguntasGenerales'   => $this->generalQuestions()
                ->map(fn(QuestionBase $q) => $this->questionPayload($q))
                ->values(),
            'tipos'                => $this->tipos(),
            'typeForms'            => TypeForm::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Crea una pregunta principal.
     */
    public function store(Request $request)
    {
        $data = $this->validateSection($request);

        QuestionSection::create([
            'title'           => $data['title'],
            'description'     => $data['description'] ?? null,
            'count_questions' => 0,
        ]);

        return back()->with('success', 'Pregunta principal creada.');
    }

    /**
     * Detalle de una pregunta principal con sus preguntas secundarias.
     */
    public function edit(string $id)
    {
        $section = QuestionSection::with(['questions.type', 'questions.options'])->findOrFail($id);

        // Preguntas base que ya se agregaron a esta sección
        $addedIds = $section->questions
            ->pluck('question_base_id')
            ->filter()
            ->map(fn($id) => (int) $id)
            ->all();

        return Inertia::render('Questions/Edit', [
            'formularioBase' => [
                'id'          => $section->id,
                'title'       => $section->title,
                'description' => $section->description,
            ],
            'preguntas' => $section->questions
                ->map(fn(Question $q) => $this->questionPayload($q))
                ->values(),
            'preguntasGenerales' => $this->generalQuestions()
                ->map(fn(QuestionBase $q) => [
                    ...$this->questionPayload($q),
                    'added' => in_array($q->id, $addedIds, true),
                ])
                ->values(),
            'tipos' => $this->tipos(),
        ]);
    }

    /**
     * Edita el texto de una pregunta principal.
     */
    public function update(Request $request, QuestionSection $section)
    {
        $data = $this->validateSection($request);

        $section->update([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
        ]);

        return back()->with('success', 'Pregunta principal actualizada.');
    }

    /**
     * Elimina una pregunta principal (y sus preguntas secundarias por cascade).
     * Las preguntas generales no se tocan.
     */
    public function destroy(QuestionSection $section)
    {
        $section->delete();

        return back()->with('success', 'Pregunta principal eliminada.');
    }

    /* =========================================================
     |  Preguntas secundarias (dentro de una pregunta principal)
     * ========================================================= */

    /**
     * Agrega una pregunta secundaria nueva al final de la sección.
     */
    public function storeQuestion(Request $request, QuestionSection $section)
    {
        $data = $this->validateQuestion($request);

        DB::transaction(function () use ($data, $section) {
            $question = Question::create([
                'question_section_id' => $section->id,
                'question_type_id'    => $data['question_type_id'],
                'text'                => $data['text'],
                'required'            => $data['required'],
                'sort_order'          => $this->nextOrder($section->id),
            ]);

            $this->createOptions($question, $data);
        });

        return back()->with('success', 'Pregunta agregada.');
    }

    /**
     * Copia preguntas base a la sección (con sus opciones), en el orden seleccionado.
     */
    public function importQuestions(Request $request, QuestionSection $section)
    {
        $data = $request->validate([
            'questions'   => ['required', 'array', 'min:1'],
            'questions.*' => [
                'integer',
                'exists:questions_base,id',
            ],
        ], [
            'questions.required'   => 'Selecciona al menos una pregunta.',
            'questions.min'        => 'Selecciona al menos una pregunta.',
            'questions.*.exists'   => 'Una de las preguntas seleccionadas ya no existe.',
        ]);

        $ids = array_values(array_unique(array_map('intval', $data['questions'])));

        DB::transaction(function () use ($ids, $section) {
            $alreadyAdded = Question::where('question_section_id', $section->id)
                ->whereNotNull('question_base_id')
                ->pluck('question_base_id')
                ->map(fn($id) => (int) $id)
                ->all();

            $bases = QuestionBase::with('options')->whereIn('id', $ids)->get()->keyBy('id');
            $order = $this->nextOrder($section->id) - 1;

            foreach ($ids as $id) {
                $base = $bases->get($id);

                if (! $base || in_array($id, $alreadyAdded, true)) {
                    continue;
                }

                $copy = Question::create([
                    'question_section_id' => $section->id,
                    'question_type_id'    => $base->question_type_id,
                    'question_base_id'    => $base->id,
                    'text'                => $base->text,
                    'required'            => $base->required,
                    'sort_order'          => ++$order,
                ]);

                foreach ($base->options as $option) {
                    $copy->options()->create([
                        'text'       => $option->text,
                        'sort_order' => $option->sort_order,
                    ]);
                }
            }
        });

        return back()->with('success', 'Preguntas agregadas.');
    }

    /**
     * Edita el texto y si es obligatoria una pregunta secundaria (tipo y opciones no cambian).
     */
    public function updateQuestion(Request $request, QuestionSection $section, Question $question)
    {
        abort_unless((int) $question->question_section_id === (int) $section->id, 404);

        $data = $request->validate([
            'text' => ['required', 'string', 'max:1000'],
            'required' => ['required', 'boolean'],
        ], [
            'text.required' => 'Escribe el texto de la pregunta.',
            'text.max'      => 'La pregunta no puede pasar de 1000 caracteres.',
        ]);

        $question->update([
            'text'     => trim($data['text']),
            'required' => (bool) $data['required'],
        ]);

        return back()->with('success', 'Pregunta actualizada.');
    }

    /**
     * Guarda el nuevo orden de las preguntas secundarias (drag & drop).
     */
    public function reorder(Request $request, QuestionSection $section)
    {
        $data = $request->validate([
            'questions'   => ['required', 'array'],
            'questions.*' => [
                'integer',
                Rule::exists('questions', 'id')->where('question_section_id', $section->id),
            ],
        ]);

        DB::transaction(function () use ($data) {
            foreach ($data['questions'] as $index => $questionId) {
                Question::whereKey($questionId)->update(['sort_order' => $index + 1]);
            }
        });

        return back();
    }

    /**
     * Elimina una pregunta secundaria y recorre el orden de las que quedan.
     */
    public function destroyQuestion(QuestionSection $section, Question $question)
    {
        abort_unless((int) $question->question_section_id === (int) $section->id, 404);

        DB::transaction(function () use ($section, $question) {
            $question->delete();

            Question::where('question_section_id', $section->id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->each(fn(Question $q, int $i) => $q->update(['sort_order' => $i + 1]));
        });

        return back()->with('success', 'Pregunta eliminada.');
    }

    /* =========================================================
     |  Preguntas base / generales (questions_base)
     * ========================================================= */

    /**
     * Da de alta una pregunta en el banco de preguntas reutilizables.
     */
    public function storeGeneral(Request $request)
    {
        $data = $this->validateQuestion($request);

        DB::transaction(function () use ($data) {
            $base = QuestionBase::create([
                'question_type_id' => $data['question_type_id'],
                'text'             => $data['text'],
                'required'         => $data['required'],
            ]);

            $this->createOptions($base, $data);
        });

        return back()->with('success', 'Pregunta general creada.');
    }

    /**
     * Elimina una pregunta base. Las copias que ya están en secciones se conservan.
     */
    public function destroyGeneral(QuestionBase $questionBase)
    {
        $questionBase->delete();

        return back()->with('success', 'Pregunta general eliminada.');
    }

    /* =========================================================
     |  Helpers
     * ========================================================= */

    private function generalQuestions(): Collection
    {
        return QuestionBase::with(['type', 'options'])
            ->orderBy('text')
            ->get();
    }

    private function questionPayload(Question|QuestionBase $q): array
    {
        return [
            'id'      => $q->id,
            'text'    => $q->text,
            'required' => (bool) $q->required,
            'type'    => $q->type?->name,
            'order'   => $q->sort_order ?? null,
            'options' => $q->options->pluck('text')->values(),
        ];
    }

    private function tipos(): Collection
    {
        return QuestionType::orderBy('id')->get(['id', 'name', 'str']);
    }

    private function nextOrder(int $sectionId): int
    {
        return (int) Question::where('question_section_id', $sectionId)->max('sort_order') + 1;
    }

    /**
     * Guarda las opciones si el tipo es Selección única / múltiple.
     * Sirve para Question y QuestionBase (las dos tienen options()).
     */
    private function createOptions(Question|QuestionBase $question, array $data): void
    {
        if (! $data['requires_options']) {
            return;
        }

        foreach (array_values($data['options']) as $index => $text) {
            $question->options()->create([
                'text'       => $text,
                'sort_order' => $index + 1,
            ]);
        }
    }

    private function validateQuestion(Request $request): array
    {
        $type = QuestionType::find($request->integer('question_type_id'));
        $requiresOptions = $type?->requiresOptions() ?? false;

        $data = $request->validate([
            'text'             => ['required', 'string', 'max:1000'],
            'question_type_id' => ['required', 'integer', 'exists:question_types,id'],
            'required'         => ['sometimes', 'boolean'],
            'options'          => $requiresOptions ? ['required', 'array', 'min:2'] : ['nullable', 'array'],
            'options.*'        => ['required', 'string', 'max:255', 'distinct:ignore_case'],
        ], [
            'text.required'             => 'Escribe el texto de la pregunta.',
            'text.max'                  => 'La pregunta no puede pasar de 1000 caracteres.',
            'question_type_id.required' => 'Selecciona el tipo de pregunta.',
            'question_type_id.exists'   => 'El tipo de pregunta no es válido.',
            'options.required'          => 'Agrega al menos 2 opciones.',
            'options.min'               => 'Agrega al menos 2 opciones.',
            'options.*.required'        => 'Las opciones no pueden ir vacías.',
            'options.*.max'             => 'Cada opción puede tener máximo 255 caracteres.',
            'options.*.distinct'        => 'Hay opciones repetidas.',
        ]);

        $data['requires_options'] = $requiresOptions;
        $data['required'] = (bool) ($data['required'] ?? true);

        return $data;
    }

    private function validateSection(Request $request): array
    {
        return $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ], [
            'title.required'  => 'El título es obligatorio.',
            'title.max'       => 'El título no puede pasar de 255 caracteres.',
            'description.max' => 'La descripción no puede pasar de 255 caracteres.',
        ]);
    }
}
