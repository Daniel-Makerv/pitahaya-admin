<?php

namespace App\Services;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppForm;
use App\Models\WhatsAppFormSession;
use RuntimeException;

class WhatsAppFormService
{
    public function __construct(
        private WhatsAppService $whatsAppService
    ) {
    }

    public function start(
        WhatsAppConversation $conversation
    ): ?WhatsAppFormSession {

        // Por ahora tomamos el primer formulario activo.
        $form = WhatsAppForm::query()
            ->where('active', true)
            ->first();

        if (!$form) {
            return null;
        }

        // Bloque inicial.
        $startBlock = $form->blocks()
            ->where('is_start', true)
            ->first();

        if (!$startBlock) {
            throw new RuntimeException(
                'El formulario no tiene un bloque inicial.'
            );
        }

        // Primera pregunta del bloque.
        $question = $startBlock->questions()
            ->orderBy('sort_order')
            ->first();

        if (!$question) {
            throw new RuntimeException(
                'El bloque inicial no tiene preguntas.'
            );
        }

        // Crear sesión.
        $session = WhatsAppFormSession::create([
            'whatsapp_conversation_id' => $conversation->id,
            'form_id' => $form->id,
            'current_block_id' => $startBlock->id,
            'current_question_id' => $question->id,
            'status' => 'in_progress',
            'score' => 0,
            'started_at' => now(),
        ]);

        $this->sendQuestion(
            $conversation,
            $question
        );

        return $session;
    }

    private function sendQuestion(
        WhatsAppConversation $conversation,
        $question
    ): void {

        $conversation->loadMissing('contact');

        $this->whatsAppService->sendText(
            $conversation->contact->phone,
            $question->text
        );
    }
}
