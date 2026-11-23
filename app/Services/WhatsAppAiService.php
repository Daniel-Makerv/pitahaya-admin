<?php

namespace App\Services;

use App\Models\WhatsAppConversation;
use App\Models\WhatsAppFormSession;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Respuestas de la IA (OpenAI Responses API) cuando el prospecto ya terminó el cuestionario.
 *
 * Le manda a OpenAI:
 *  - las instrucciones del asistente
 *  - las respuestas que dio en el cuestionario (para que no vuelva a preguntarlas)
 *  - los últimos mensajes de la conversación (para que recuerde de qué venían hablando)
 */
class WhatsAppAiService
{
    /** Mensajes anteriores que se le mandan como contexto */
    private const HISTORY = 20;

    private const FALLBACK = 'Por el momento tuve un problema para responderte 🌵 Inténtalo nuevamente en unos momentos.';

    public function reply(WhatsAppConversation $conversation, string $message): string
    {
        try {
            $response = Http::withToken(config('services.openai.api_key'))
                ->acceptJson()
                ->timeout(45)
                ->post('https://api.openai.com/v1/responses', [
                    'model'        => config('services.openai.model'),
                    'instructions' => $this->instructions($conversation),
                    'input'        => $this->history($conversation, $message),
                ]);

            if ($response->failed()) {
                Log::error('Error OpenAI', [
                    'status' => $response->status(),
                    'body'   => Str::limit($response->body(), 1000),
                ]);

                return self::FALLBACK;
            }

            $text = $this->outputText($response->json() ?? []);

            if ($text === '') {
                Log::warning('OpenAI respondió sin texto', ['status' => data_get($response->json(), 'status')]);

                return self::FALLBACK;
            }

            return $text;
        } catch (\Throwable $e) {
            Log::error('Error WhatsAppAiService', ['message' => $e->getMessage()]);

            return self::FALLBACK;
        }
    }

    private function instructions(WhatsAppConversation $conversation): string
    {
        $instructions = <<<'TXT'
            Eres el asistente virtual de Pitamex.

            Pitamex es una empresa relacionada con el cultivo y comercialización de pitahaya.

            Tu función es atender prospectos que ya terminaron un formulario inicial.

            Reglas:
            - Responde siempre en español.
            - Sé amable y breve: es WhatsApp, máximo 3 o 4 renglones.
            - No inventes precios, disponibilidad ni información que no conozcas.
            - Si no tienes información suficiente, dilo claramente y ofrece que un asesor de Pitamex lo contacte.
            - No vuelvas a hacer el formulario ni preguntes lo que ya contestó.
            - No digas que eres ChatGPT.
            TXT;

        $answers = $this->formAnswers($conversation);

        if ($answers !== '') {
            $instructions .= "\n\nRespuestas que dio este prospecto en el formulario:\n" . $answers;
        }

        return $instructions;
    }

    private function formAnswers(WhatsAppConversation $conversation): string
    {
        $session = WhatsAppFormSession::with('answers.question')
            ->where('whatsapp_conversation_id', $conversation->id)
            ->where('status', 'completed')
            ->latest('id')
            ->first();

        if (! $session) {
            return '';
        }

        return $session->answers
            ->sortBy('id')
            ->map(fn ($a) => '- ' . Str::limit((string) $a->question?->text, 200) . ': ' . Str::limit((string) $a->answer, 300))
            ->implode("\n");
    }

    /**
     * Últimos mensajes como turnos user/assistant. El mensaje actual ya está guardado,
     * así que normalmente es el último; si no, se agrega.
     */
    private function history(WhatsAppConversation $conversation, string $message): array
    {
        $turns = $conversation->messages()
            ->whereNotNull('body')
            ->latest('id')
            ->limit(self::HISTORY)
            ->get()
            ->reverse()
            ->map(fn ($m) => [
                'role'    => $m->sender_type === 'customer' ? 'user' : 'assistant',
                'content' => Str::limit((string) $m->body, 1500),
            ])
            ->values()
            ->all();

        // La conversación debe empezar con el prospecto
        while ($turns && $turns[0]['role'] !== 'user') {
            array_shift($turns);
        }

        $last = end($turns);

        if (! $last || $last['role'] !== 'user' || $last['content'] !== Str::limit($message, 1500)) {
            $turns[] = ['role' => 'user', 'content' => $message];
        }

        return $turns;
    }

    /**
     * El texto de la respuesta. Con los modelos que razonan, output[0] es el razonamiento
     * y el mensaje viene después, por eso se busca el bloque de tipo "message".
     */
    private function outputText(array $data): string
    {
        $text = '';

        foreach (data_get($data, 'output', []) as $item) {
            if (data_get($item, 'type') !== 'message') {
                continue;
            }

            foreach (data_get($item, 'content', []) as $content) {
                if (data_get($content, 'type') === 'output_text') {
                    $text .= data_get($content, 'text', '');
                }
            }
        }

        return trim($text);
    }
}
