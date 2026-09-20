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
}
