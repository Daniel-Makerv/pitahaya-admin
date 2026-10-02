<?php

namespace App\Http\Controllers\Questions;

use App\Http\Controllers\Controller;
use App\Models\WhatsAppQuestion;
use App\Models\WhatsAppQuestionOption;
use Illuminate\Http\Request;

class WhatsAppQuestionOptionController extends Controller
{
    public function store(Request $request, WhatsAppQuestion $question)
    {
        $data = $request->validate([
            'text' => [
                'required',
                'string',
                'max:255',
            ],
            'score' => [
                'nullable',
                'integer',
            ],
        ]);

        /*
         * Solo preguntas de selección pueden tener opciones.
         */
        abort_unless(
            in_array($question->type, [
                'single_choice',
                'multiple_choice',
            ]),
            422
        );

        /*
         * Obtener el último orden.
         */
        $lastOrder = $question
            ->options()
            ->max('sort_order');

        /*
         * Crear opción.
         */
        $question->options()->create([
            'text' => $data['text'],
            'score' => $data['score'] ?? 0,
            'sort_order' => ($lastOrder ?? 0) + 1,
            'active' => true,
        ]);

        return back()->with(
            'success',
            'Opción agregada correctamente.'
        );
    }

    public function destroy(WhatsAppQuestionOption $option)
    {
        $option->delete();

        return back()->with(
            'success',
            'Opción eliminada correctamente.'
        );
    }
}
