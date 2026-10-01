<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WhatsAppService
{
    public function sendText(string $to, string $message): array
    {
        $phoneNumberId = config('services.whatsapp.phone_number_id');
        $token = config('services.whatsapp.access_token');

        $to = $this->normalizePhone($to);

        $response = Http::withToken($token)
            ->acceptJson()
            ->post(
                "https://graph.facebook.com/v26.0/{$phoneNumberId}/messages",
                [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Error enviando WhatsApp: ' . $response->body()
            );
        }

        return $response->json();
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        // México: elimina el antiguo prefijo móvil "1"
        // 5217223491801 -> 527223491801
        if (str_starts_with($phone, '521') && strlen($phone) === 13) {
            $phone = '52' . substr($phone, 3);
        }

        return $phone;
    }

    public function sendButtons(
        string $to,
        string $message,
        array $options
    ): array {
        $phoneNumberId = config(
            'services.whatsapp.phone_number_id'
        );

        $token = config(
            'services.whatsapp.access_token'
        );

        $to = $this->normalizePhone($to);

        $buttons = collect($options)
            ->take(3)
            ->map(function ($option) {
                return [
                    'type' => 'reply',
                    'reply' => [
                        // Esto es importante:
                        // mandamos el ID real de nuestra BD.
                        'id' => 'option_' . $option->id,
                        'title' => mb_substr(
                            $option->text,
                            0,
                            20
                        ),
                    ],
                ];
            })
            ->values()
            ->toArray();

        $response = Http::withToken($token)
            ->acceptJson()
            ->post(
                "https://graph.facebook.com/v26.0/{$phoneNumberId}/messages",
                [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $to,

                    'type' => 'interactive',

                    'interactive' => [
                        'type' => 'button',

                        'body' => [
                            'text' => $message,
                        ],

                        'action' => [
                            'buttons' => $buttons,
                        ],
                    ],
                ]
            );

        if ($response->failed()) {
            throw new RuntimeException(
                'Error enviando botones WhatsApp: '
                    . $response->body()
            );
        }

        return $response->json();
    }
}
