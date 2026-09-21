<?php

namespace App\Services;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppForm;
use App\Models\WhatsAppFormSession;
use RuntimeException;
use App\Models\WhatsAppAnswer;
use App\Models\WhatsAppQuestion;

class WhatsAppFormService
{
    public function __construct(
        private WhatsAppService $whatsAppService
    ) {}

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

        $this->sendWelcomeMessage($conversation);


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

    private function sendWelcomeMessage(
        WhatsAppConversation $conversation
    ): void {

        $conversation->loadMissing('contact');

        $message = "¡Qué tal! Soy el asistente de Pitamex 🌵\n"
            . "Te hago 6 preguntas para conocer tu huerta. "
            . "Tarda menos de dos minutos.";

        $this->whatsAppService->sendText(
            $conversation->contact->phone,
            $message
        );
    }

    public function answer(
        WhatsAppFormSession $session,
        string $answer
    ): void {

        $question = $session->currentQuestion;

        if (!$question) {
            return;
        }

        /*
     * Guardar respuesta actual.
     */
        WhatsAppAnswer::updateOrCreate(
            [
                'form_session_id' => $session->id,
                'question_id' => $question->id,
            ],
            [
                'answer' => $answer,
                'option_id' => null,
                'score' => 0,
            ]
        );

        /*
     * Buscar la siguiente pregunta
     * dentro del mismo bloque.
     */
        $nextQuestion = WhatsAppQuestion::query()
            ->where('block_id', $session->current_block_id)
            ->where('active', true)
            ->where('sort_order', '>', $question->sort_order)
            ->orderBy('sort_order')
            ->first();

        /*
     * Si existe otra pregunta,
     * avanzamos hacia ella.
     */
        if ($nextQuestion) {

            $session->update([
                'current_question_id' => $nextQuestion->id,
            ]);

            $this->sendQuestion(
                $session->conversation,
                $nextQuestion
            );

            return;
        }

        /*
     * Por ahora, si ya no existen
     * más preguntas terminamos.
     *
     * Después aquí meteremos next_block_id.
     */
        $session->update([
            'current_question_id' => null,
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->whatsAppService->sendText(
            $session->conversation->contact->phone,
            '¡Gracias! Hemos terminado el formulario. 🌵'
        );
    }
}
