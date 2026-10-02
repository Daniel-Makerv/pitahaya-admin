<?php

namespace App\Http\Controllers\Questions;

use App\Models\WhatsAppForm;
use App\Models\WhatsAppFormBlock;
use App\Models\WhatsAppQuestion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WhatsAppQuestionController extends Controller
{
    public function store(
        Request $request,
        WhatsAppForm $form,
        WhatsAppFormBlock $block
    ) {
        abort_unless($block->form_id === $form->id, 404);

        $data = $request->validate([
            'text' => ['required', 'string'],

            'type' => [
                'required',
                'in:text,number,single_choice,multiple_choice',
            ],

            'required' => ['required', 'boolean'],

            'options' => ['nullable', 'array'],

            'options.*.text' => [
                'required',
                'string',
                'max:255',
            ],

            'options.*.score' => [
                'nullable',
                'integer',
            ],
        ]);

        /*
     * Si es selección única o múltiple,
     * debe tener al menos una opción.
     */
        if (
            in_array(
                $data['type'],
                ['single_choice', 'multiple_choice']
            ) &&
            empty($data['options'])
        ) {
            return back()->withErrors([
                'options' => 'Agrega al menos una opción.',
            ]);
        }

        $lastOrder = WhatsAppQuestion::where(
            'block_id',
            $block->id
        )->max('sort_order');

        /*
     * Crear pregunta
     */
        $question = WhatsAppQuestion::create([
            'block_id' => $block->id,
            'text' => $data['text'],
            'type' => $data['type'],
            'sort_order' => ($lastOrder ?? 0) + 1,
            'required' => $data['required'],
            'active' => true,
        ]);

        /*
     * Crear opciones
     */
        if (
            in_array(
                $question->type,
                ['single_choice', 'multiple_choice']
            )
        ) {
            foreach ($data['options'] ?? [] as $index => $option) {

                $question->options()->create([
                    'text' => $option['text'],
                    'score' => $option['score'] ?? 0,
                    'sort_order' => $index + 1,
                    'active' => true,
                ]);
            }
        }

        return back()->with(
            'success',
            'Pregunta creada correctamente.'
        );
    }

    public function move(Request $request, WhatsAppQuestion $question)
    {
        $data = $request->validate([
            'direction' => ['required', 'in:up,down'],
        ]);

        $query = WhatsAppQuestion::where('block_id', $question->block_id);

        if ($data['direction'] === 'up') {
            $otherQuestion = $query
                ->where('sort_order', '<', $question->sort_order)
                ->orderByDesc('sort_order')
                ->first();
        } else {
            $otherQuestion = $query
                ->where('sort_order', '>', $question->sort_order)
                ->orderBy('sort_order')
                ->first();
        }

        if (!$otherQuestion) {
            return back();
        }

        $currentOrder = $question->sort_order;

        $question->update([
            'sort_order' => $otherQuestion->sort_order,
        ]);

        $otherQuestion->update([
            'sort_order' => $currentOrder,
        ]);

        return back();
    }

    public function reorder(
        Request $request,
        WhatsAppFormBlock $block
    ) {
        $data = $request->validate([
            'questions' => ['required', 'array'],

            'questions.*.id' => [
                'required',
                'integer',
            ],

            'questions.*.sort_order' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        foreach ($data['questions'] as $item) {
            WhatsAppQuestion::where('id', $item['id'])
                ->where('block_id', $block->id)
                ->update([
                    'sort_order' => $item['sort_order'],
                ]);
        }

        return back();
    }

    public function update(
        Request $request,
        WhatsAppQuestion $question
    ) {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:1000'],
        ]);

        $question->update([
            'text' => $validated['text'],
        ]);

        return back()->with(
            'success',
            'Pregunta actualizada correctamente.'
        );
    }


    public function destroy(WhatsAppQuestion $question)
    {
        $question->options()->delete();

        $question->delete();

        return back()->with(
            'success',
            'Pregunta y opciones eliminadas correctamente.'
        );
    }
}
