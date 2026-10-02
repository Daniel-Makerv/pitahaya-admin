<?php

namespace App\Services;

use App\Models\WhatsAppConversation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppAiService
{
    public function reply(
        WhatsAppConversation $conversation,
        string $message
    ): string {

        try {

            $response = Http::withToken(
                config('services.openai.api_key')
            )
                ->timeout(30)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => config('services.openai.model'),

                    'instructions' => '
                        Eres el asistente virtual de Pitamex.

                        Pitamex es una empresa relacionada con el cultivo
                        y comercialización de pitahaya.

                        Tu función es atender prospectos que ya terminaron
                        un formulario inicial.

                        Reglas:
                        - Responde siempre en español.
                        - Sé amable y breve.
                        - Estás conversando por WhatsApp.
                        - No inventes precios, disponibilidad ni información
                          que no conozcas.
                        - Si no tienes información suficiente, dilo claramente.
                        - No vuelvas a realizar el formulario.
                        - No digas que eres ChatGPT.
                    ',

                    'input' => [
                        [
                            'role' => 'user',
                            'content' => $message,
                        ],
                    ],
                ]);

            if ($response->failed()) {

                Log::error('Error OpenAI', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return 'Por el momento tuve un problema para responderte 🌵 Inténtalo nuevamente en unos momentos.';
            }

            $data = $response->json();

            return data_get(
                $data,
                'output.0.content.0.text',
                '¿En qué más puedo ayudarte? 🌵'
            );
        } catch (\Throwable $e) {

            Log::error('Error WhatsAppAiService', [
                'message' => $e->getMessage(),
            ]);

            return 'Por el momento tuve un problema para responderte 🌵';
        }
    }
}
