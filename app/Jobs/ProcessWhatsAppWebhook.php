<?php

namespace App\Jobs;

use App\Models\WhatsAppContact;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppFormSession;
use App\Models\WhatsAppMessage;
use App\Services\WhatsAppAiService;
use App\Services\WhatsAppFormService;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Procesa un webhook de WhatsApp (lo encola WhatsAppWebhookController).
 *
 * - messages            -> mensaje del prospecto: se guarda y contesta el formulario o la IA
 * - smb_message_echoes  -> mensaje que alguien de Pitamex mandó desde la app del celular:
 *                          se guarda y el agente se calla en ese chat unas horas
 * - lo demás (estados, history...) por ahora se ignora
 */
class ProcessWhatsAppWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    public int $tries = 3;

    public int $timeout = 90;

    public function __construct(public array $payload) {}

    public function handle(
        WhatsAppService $whatsApp,
        WhatsAppFormService $forms,
        WhatsAppAiService $ai
    ): void {
        foreach (data_get($this->payload, 'entry', []) as $entry) {
            foreach (data_get($entry, 'changes', []) as $change) {
                $value = data_get($change, 'value', []);

                match (data_get($change, 'field')) {
                    'messages'           => $this->incoming($value, $whatsApp, $forms, $ai),
                    'smb_message_echoes' => $this->echoes($value),
                    default              => null,
                };
            }
        }
    }

    /* ---------------------------------------------------------------
     | Mensajes del prospecto
     * --------------------------------------------------------------- */

    private function incoming(array $value, WhatsAppService $whatsApp, WhatsAppFormService $forms, WhatsAppAiService $ai): void
    {
        foreach (data_get($value, 'messages', []) as $message) {
            $messageId = data_get($message, 'id');

            if (! $messageId || WhatsAppMessage::where('meta_message_id', $messageId)->exists()) {
                continue; // Meta reintentó: ya se procesó
            }

            $phone = (string) data_get($message, 'from');
            $waId = (string) data_get($value, 'contacts.0.wa_id', $phone);

            $contact = WhatsAppContact::updateOrCreate(
                ['wa_id' => $waId],
                array_filter([
                    'phone' => $phone,
                    'name'  => data_get($value, 'contacts.0.profile.name'),
                ])
            );

            // Un mensaje a la vez por contacto: si escribe dos seguidos, no se enciman las respuestas
            Cache::lock("whatsapp-contact-{$contact->id}", 120)
                ->block(60, fn () => $this->handleIncoming($contact, $message, $whatsApp, $forms, $ai));
        }
    }

    private function handleIncoming(
        WhatsAppContact $contact,
        array $message,
        WhatsAppService $whatsApp,
        WhatsAppFormService $forms,
        WhatsAppAiService $ai
    ): void {
        // Revisión de nuevo, ya con el candado (dos reintentos de Meta casi al mismo tiempo)
        if (WhatsAppMessage::where('meta_message_id', data_get($message, 'id'))->exists()) {
            return;
        }

        $conversation = $this->openConversation($contact);
        $type = (string) data_get($message, 'type');
        $body = $this->body($message);

        WhatsAppMessage::create([
            'whatsapp_conversation_id' => $conversation->id,
            'meta_message_id' => data_get($message, 'id'),
            'direction'   => 'inbound',
            'sender_type' => 'customer',
            'type'        => $type,
            'body'        => $body,
            'payload'     => $message,
            'sent_at'     => $this->sentAt($message),
        ]);

        $conversation->update(['last_message_at' => now()]);

        if (! $this->agentShouldReply($conversation)) {
            return; // lo está atendiendo una persona: solo se guarda
        }

        if ($body === null) {
            // Audio, foto, ubicación...: el bot no los entiende todavía
            if ($this->activeSession($conversation)) {
                $forms->askAgain($this->activeSession($conversation), 'Por ahora solo puedo leer texto 🌵 Escríbeme tu respuesta, por favor.');
            }

            return;
        }

        // 1. Está contestando el formulario
        if ($session = $this->activeSession($conversation)) {
            $forms->answer($session, $body, $this->replyId($message));

            return;
        }

        // 2. Nunca lo ha contestado: empieza
        $completed = WhatsAppFormSession::where('whatsapp_conversation_id', $conversation->id)
            ->where('status', 'completed')
            ->exists();

        if (! $completed && $forms->start($conversation)) {
            return;
        }

        // 3. Ya lo terminó (o no hay formulario activo): contesta la IA
        $reply = $ai->reply($conversation, $body);
        $result = $whatsApp->sendText($contact->phone, $reply);
        $whatsApp->record($conversation, $result, 'text', $reply);
    }

    /* ---------------------------------------------------------------
     | Mensajes que mandó una persona desde la app de WhatsApp Business
     * --------------------------------------------------------------- */

    private function echoes(array $value): void
    {
        foreach (data_get($value, 'message_echoes', []) as $echo) {
            $messageId = data_get($echo, 'id');

            if (! $messageId || WhatsAppMessage::where('meta_message_id', $messageId)->exists()) {
                continue;
            }

            $customer = (string) data_get($echo, 'to');
            $contact = WhatsAppContact::firstOrCreate(['wa_id' => $customer], ['phone' => $customer]);

            Cache::lock("whatsapp-contact-{$contact->id}", 120)->block(60, function () use ($contact, $echo, $messageId) {
                if (WhatsAppMessage::where('meta_message_id', $messageId)->exists()) {
                    return;
                }

                $conversation = $this->openConversation($contact);

                WhatsAppMessage::create([
                    'whatsapp_conversation_id' => $conversation->id,
                    'meta_message_id' => $messageId,
                    'direction'   => 'outbound',
                    'sender_type' => 'whatsapp_app',
                    'type'        => (string) data_get($echo, 'type', 'text'),
                    'body'        => $this->body($echo),
                    'payload'     => $echo,
                    'sent_at'     => $this->sentAt($echo),
                ]);

                $changes = ['last_message_at' => now()];

                // Si alguien lo pausó a mano (paused), se respeta
                if ($conversation->mode !== 'paused') {
                    $changes['mode'] = 'human';
                    $changes['ai_paused_until'] = now()->addHours(max(1, (int) config('services.whatsapp.human_pause_hours', 12)));
                }

                $conversation->update($changes);
            });
        }
    }

    /* ---------------------------------------------------------------
     | Apoyos
     * --------------------------------------------------------------- */

    private function openConversation(WhatsAppContact $contact): WhatsAppConversation
    {
        return WhatsAppConversation::firstOrCreate(
            ['whatsapp_contact_id' => $contact->id, 'status' => 'open'],
            ['mode' => 'ai', 'last_message_at' => now()]
        );
    }

    private function activeSession(WhatsAppConversation $conversation): ?WhatsAppFormSession
    {
        return WhatsAppFormSession::where('whatsapp_conversation_id', $conversation->id)
            ->where('status', 'in_progress')
            ->latest('id')
            ->first();
    }

    /** ai: contesta · human: se calla hasta que venza la pausa · paused: se calla */
    private function agentShouldReply(WhatsAppConversation $conversation): bool
    {
        if ($conversation->mode === 'ai') {
            return true;
        }

        if ($conversation->mode === 'human' && (! $conversation->ai_paused_until || $conversation->ai_paused_until->isPast())) {
            $conversation->update(['mode' => 'ai', 'ai_paused_until' => null]);

            return true;
        }

        return false;
    }

    private function body(array $message): ?string
    {
        $text = match (data_get($message, 'type')) {
            'text'        => data_get($message, 'text.body'),
            'interactive' => data_get($message, 'interactive.button_reply.title')
                ?? data_get($message, 'interactive.list_reply.title'),
            'button'      => data_get($message, 'button.text'),
            default       => null,
        };

        return is_string($text) && trim($text) !== '' ? trim($text) : null;
    }

    /** id del botón o de la fila de la lista que eligió (option_15) */
    private function replyId(array $message): ?string
    {
        return data_get($message, 'interactive.button_reply.id')
            ?? data_get($message, 'interactive.list_reply.id');
    }

    private function sentAt(array $message): Carbon
    {
        $timestamp = data_get($message, 'timestamp');

        return $timestamp ? Carbon::createFromTimestamp((int) $timestamp) : now();
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Error procesando WhatsApp webhook', [
            'message' => $e->getMessage(),
            'file'    => $e->getFile(),
            'line'    => $e->getLine(),
        ]);
    }
}
