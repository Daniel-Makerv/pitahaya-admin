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
        ]);

        $lastOrder = WhatsAppQuestion::where(
            'block_id',
            $block->id
        )->max('sort_order');

        WhatsAppQuestion::create([
            'block_id' => $block->id,
            'text' => $data['text'],
            'type' => $data['type'],
            'sort_order' => ($lastOrder ?? 0) + 1,
            'required' => $data['required'],
            'active' => true,
        ]);

        return back()->with(
            'success',
            'Pregunta creada correctamente.'
        );
    }
}
