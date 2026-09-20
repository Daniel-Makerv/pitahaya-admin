<?php

namespace App\Http\Controllers\Whatsapp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\WhatsAppContact;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppService;

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

    public function handle(
        Request $request,
        WhatsAppService $whatsAppService
    ) {
        try {

            $payload = $request->all();

            Log::info('WhatsApp webhook recibido', [
                'payload' => $payload,
            ]);

            $value = data_get(
                $payload,
                'entry.0.changes.0.value'
            );

            /*
         * No todos los webhooks contienen mensajes.
         * También recibiremos sent, delivered, read, etc.
         */
            $message = data_get($value, 'messages.0');

            if (!$message) {
                return response('EVENT_RECEIVED', 200);
            }

            $waId = data_get(
                $value,
                'contacts.0.wa_id'
            );

            $name = data_get(
                $value,
                'contacts.0.profile.name'
            );

            $phone = data_get(
                $message,
                'from'
            );

            $messageId = data_get(
                $message,
                'id'
            );

            $type = data_get(
                $message,
                'type'
            );

            /*
         * Por ahora solamente procesamos texto.
         */
            $body = match ($type) {
                'text' => data_get($message, 'text.body'),
                default => null,
            };

            /*
         * Evitar procesar dos veces el mismo
         * mensaje si Meta reintenta el webhook.
         */
            if (
                $messageId &&
                WhatsAppMessage::where(
                    'meta_message_id',
                    $messageId
                )->exists()
            ) {
                return response('EVENT_RECEIVED', 200);
            }

            /*
         * Crear o actualizar contacto.
         */
            $contact = WhatsAppContact::updateOrCreate(
                [
                    'wa_id' => $waId ?? $phone,
                ],
                [
                    'phone' => $phone,
                    'name' => $name,
                ]
            );

            /*
         * Buscar conversación abierta.
         */
            $conversation = WhatsAppConversation::firstOrCreate(
                [
                    'whatsapp_contact_id' => $contact->id,
                    'status' => 'open',
                ],
                [
                    'mode' => 'ai',
                    'last_message_at' => now(),
                ]
            );

            /*
         * Guardar mensaje.
         */
            WhatsAppMessage::create([
                'whatsapp_conversation_id' => $conversation->id,
                'meta_message_id' => $messageId,
                'direction' => 'inbound',
                'sender_type' => 'customer',
                'type' => $type,
                'body' => $body,
                'payload' => $message,
                'sent_at' => isset($message['timestamp'])
                    ? now()->setTimestamp((int) $message['timestamp'])
                    : now(),
            ]);

            /*
 * Responder automáticamente únicamente
 * cuando la conversación está en modo IA.
 */
            if ($conversation->mode === 'ai' && $type === 'text') {

                $reply = '¡Hola! Soy el asistente de Pitamex 🌱 ¿En qué podemos ayudarte?';

                $response = $whatsAppService->sendText(
                    $contact->phone,
                    $reply
                );

                $outboundMessageId = data_get(
                    $response,
                    'messages.0.id'
                );

                /*
     * Guardar mensaje enviado.
     */
                WhatsAppMessage::create([
                    'whatsapp_conversation_id' => $conversation->id,
                    'meta_message_id' => $outboundMessageId,
                    'direction' => 'outbound',
                    'sender_type' => 'ai',
                    'type' => 'text',
                    'body' => $reply,
                    'payload' => $response,
                    'sent_at' => now(),
                ]);

                $conversation->update([
                    'last_message_at' => now(),
                ]);
            }

            /*
         * Actualizar actividad de conversación.
         */
            $conversation->update([
                'last_message_at' => now(),
            ]);

            Log::info('Mensaje WhatsApp guardado', [
                'contact_id' => $contact->id,
                'conversation_id' => $conversation->id,
                'message_id' => $messageId,
                'body' => $body,
            ]);
        } catch (\Throwable $e) {

            Log::error('Error procesando WhatsApp webhook', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            // Por ahora lo dejamos en 200 para evitar
            // reintentos constantes de Meta durante desarrollo.
        }

        return response('EVENT_RECEIVED', 200);
    }
}
