<?php

namespace App\Http\Controllers\Whatsapp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        if (
            $mode === 'subscribe' &&
            $token === config('services.whatsapp.verify_token')
        ) {
            Log::info('WhatsApp webhook verificado');

            return response($challenge, 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('Falló verificación WhatsApp', [
            'mode' => $mode,
        ]);

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        Log::info('WhatsApp webhook recibido', [
            'payload' => $request->all(),
        ]);

        return response('EVENT_RECEIVED', 200);
    }
}
