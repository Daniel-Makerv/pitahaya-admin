<?php

namespace App\Http\Controllers\Whatsapp;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWhatsAppWebhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Webhook de WhatsApp Cloud API.
 *
 * GET  /api/webhook/whatsapp  -> verificación de Meta
 * POST /api/webhook/whatsapp  -> eventos (mensajes, ecos de la app, estados...)
 *
 * El POST solo encola y responde 200 de inmediato. Todo el trabajo (formulario, IA, guardar)
 * lo hace App\Jobs\ProcessWhatsAppWebhook, así Meta no reintenta si OpenAI tarda.
 */
class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        if (
            $request->query('hub_mode') === 'subscribe' &&
            $request->query('hub_verify_token') === config('services.whatsapp.verify_token')
        ) {
            Log::info('WhatsApp webhook verificado');

            return response($request->query('hub_challenge'), 200)
                ->header('Content-Type', 'text/plain');
        }

        Log::warning('Falló verificación WhatsApp', ['mode' => $request->query('hub_mode')]);

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('WhatsApp webhook recibido', [
            'fields' => collect(data_get($payload, 'entry', []))
                ->flatMap(fn ($entry) => collect(data_get($entry, 'changes', []))->pluck('field'))
                ->all(),
        ]);

        ProcessWhatsAppWebhook::dispatch($payload);

        return response('EVENT_RECEIVED', 200);
    }
}
