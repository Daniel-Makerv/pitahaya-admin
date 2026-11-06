<?php

namespace App\Http\Controllers\Questions;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionSection;
use App\Models\QuestionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class QuestionController extends Controller
{
    /**
     * Lista las preguntas principales (question_sections).
     */
    public function index()
    {
        $preguntasPrincipales = QuestionSection::withCount('questions')
            ->orderBy('id')
            ->get()
            ->map(fn (QuestionSection $section) => [
                'id'              => $section->id,
                'title'           => $section->title,
                'description'     => $section->description,
                'total_preguntas' => $section->questions_count,
                'created_at'      => $section->created_at?->format('d/m/Y'),
            ]);

        return Inertia::render('Questions/Index', [
            'preguntasPrincipales' => $preguntasPrincipales,
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

        return Inertia::render('Questions/Edit', [
            'formularioBase' => [
                'id'          => $section->id,
                'title'       => $section->title,
                'description' => $section->description,
            ],
            'preguntas' => $section->questions->map(fn (Question $q) => [
                'id'    => $q->id,
                'text'  => $q->text,
                'type'    => $q->type?->name,
                'order'   => $q->sort_order,
                'options' => $q->options->pluck('text')->values(),
            ])->values(),
            'tipos' => QuestionType::orderBy('id')->get(['id', 'name', 'str']),
        ]);
    }

    /**
     * Agrega una pregunta secundaria al final de la pregunta principal.
     */
    public function storeQuestion(Request $request, QuestionSection $section)
    {
        $type = QuestionType::find($request->integer('question_type_id'));
        $requiresOptions = $type?->requiresOptions() ?? false;

        $data = $request->validate([
            'text'             => ['required', 'string', 'max:1000'],
            'question_type_id' => ['required', 'integer', 'exists:question_types,id'],
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

        DB::transaction(function () use ($data, $section, $requiresOptions) {
            $question = $section->questions()->create([
                'question_type_id' => $data['question_type_id'],
                'text'             => $data['text'],
                'sort_order'       => (int) Question::where('question_section_id', $section->id)->max('sort_order') + 1,
            ]);

            // Solo Selección única / múltiple guardan opciones
            if ($requiresOptions) {
                foreach (array_values($data['options']) as $index => $text) {
                    $question->options()->create([
                        'text'       => $text,
                        'sort_order' => $index + 1,
                    ]);
                }
            }
        });

        return back()->with('success', 'Pregunta agregada.');
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
                ->each(fn (Question $q, int $i) => $q->update(['sort_order' => $i + 1]));
        });

        return back()->with('success', 'Pregunta eliminada.');
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
     * Elimina una pregunta principal (y sus preguntas secundarias por cascade).
     */
    public function destroy(QuestionSection $section)
    {
        $section->delete();

        return back()->with('success', 'Pregunta principal eliminada.');
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
