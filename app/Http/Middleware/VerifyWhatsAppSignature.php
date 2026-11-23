<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Comprueba que el POST del webhook de WhatsApp venga de Meta (cabecera X-Hub-Signature-256).
 *
 * Mientras WHATSAPP_APP_SECRET no esté en el .env, deja pasar la petición y avisa en el log,
 * para no tumbar el bot antes de configurarlo. En producción debe estar configurado.
 */
class VerifyWhatsAppSignature
{
    public function handle(Request $request, Closure $next)
    {
        $secret = (string) config('services.whatsapp.app_secret');

        if ($secret === '') {
            Log::warning('WhatsApp webhook: falta WHATSAPP_APP_SECRET, no se está validando la firma de Meta');

            return $next($request);
        }

        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, (string) $request->header('X-Hub-Signature-256'))) {
            Log::warning('WhatsApp webhook: firma inválida', ['ip' => $request->ip()]);

            return response('Invalid signature', 403);
        }

        return $next($request);
    }
}
