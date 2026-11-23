<?php

namespace Tests\Feature;

use App\Models\Form;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppForm;
use App\Models\WhatsAppFormBlock;
use App\Models\WhatsAppFormSession;
use App\Models\WhatsAppMessage;
use App\Models\WhatsAppQuestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Flujo completo del chatbot sin llamar a Meta ni a OpenAI (Http::fake).
 * php artisan test --filter=WhatsAppChatbotTest
 */
class WhatsAppChatbotTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-secret';

    private const CUSTOMER = '5217221234567';

    private int $sent = 0;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp.app_secret'      => self::SECRET,
            'services.whatsapp.phone_number_id' => '123',
            'services.whatsapp.access_token'    => 'token',
            'services.openai.api_key'           => 'sk-test',
            'services.openai.model'             => 'test-model',
        ]);

        Http::fake([
            'graph.facebook.com/*' => fn () => Http::response(['messages' => [['id' => 'wamid.out.' . (++$this->sent)]]]),
            'api.openai.com/*'     => Http::response([
                'output' => [
                    ['type' => 'reasoning', 'summary' => []],
                    ['type' => 'message', 'content' => [['type' => 'output_text', 'text' => 'Respuesta de la IA']]],
                ],
            ]),
        ]);

        $form = WhatsAppForm::create(['name' => 'Registro huerta', 'active' => true]);
        $start = WhatsAppFormBlock::create(['form_id' => $form->id, 'name' => 'Datos', 'sort_order' => 1, 'is_start' => true]);
        $second = WhatsAppFormBlock::create(['form_id' => $form->id, 'name' => 'Huerta', 'sort_order' => 2, 'is_final' => true]);

        WhatsAppQuestion::create(['block_id' => $start->id, 'text' => '¿Cuál es tu nombre?', 'type' => 'text', 'sort_order' => 1]);
        $hectares = WhatsAppQuestion::create(['block_id' => $start->id, 'text' => '¿Cuántas hectáreas tienes?', 'type' => 'number', 'sort_order' => 2]);
        $irrigation = WhatsAppQuestion::create(['block_id' => $second->id, 'text' => '¿Tienes riego?', 'type' => 'single_choice', 'sort_order' => 1]);
        $irrigation->options()->createMany([
            ['text' => 'Sí', 'score' => 10, 'sort_order' => 1],
            ['text' => 'No', 'score' => 0, 'sort_order' => 2],
        ]);
        $crops = WhatsAppQuestion::create(['block_id' => $second->id, 'text' => '¿Qué siembras?', 'type' => 'multiple_choice', 'sort_order' => 2]);
        $crops->options()->createMany([
            ['text' => 'Pitahaya', 'score' => 5, 'sort_order' => 1],
            ['text' => 'Maíz', 'score' => 1, 'sort_order' => 2],
            ['text' => 'Agave', 'score' => 2, 'sort_order' => 3],
        ]);
    }

    public function test_rejects_requests_without_a_valid_signature(): void
    {
        $this->postJson('/api/webhook/whatsapp', $this->textPayload('wamid.x', 'Hola'))->assertStatus(403);

        $this->assertSame(0, WhatsAppMessage::count());
    }

    public function test_full_questionnaire_is_saved_in_forms_and_then_the_ai_replies(): void
    {
        $this->send($this->textPayload('wamid.1', 'Hola'));
        $this->assertSame(1, WhatsAppFormSession::count());

        $this->send($this->textPayload('wamid.2', 'Juan Pérez'));
        $this->send($this->textPayload('wamid.3', 'como unas tres')); // número inválido: vuelve a preguntar
        $this->send($this->textPayload('wamid.4', '2,5 hectáreas'));

        $irrigationYes = WhatsAppQuestion::where('text', '¿Tienes riego?')->first()->options()->first();
        $this->send($this->buttonPayload('wamid.5', 'option_' . $irrigationYes->id, 'Sí'));
        $this->send($this->textPayload('wamid.6', '1, 3'));

        $session = WhatsAppFormSession::first();
        $this->assertSame('completed', $session->status);
        $this->assertSame(17, $session->score); // 10 riego + 5 pitahaya + 2 agave

        // Quedó en forms como lo guarda la PWA
        $form = Form::where('uuid', 'whatsapp-' . $session->id)->firstOrFail();
        $data = json_decode($form->form, true);

        $this->assertSame('Juan Pérez', $form->name);
        $this->assertSame($form->id, $session->fresh()->exported_form_id);
        $this->assertSame('2.5', $data['¿Cuántas hectáreas tienes?']);
        $this->assertSame('Sí', $data['¿Tienes riego?']);
        $this->assertSame('Pitahaya, Agave', $data['¿Qué siembras?']);
        $this->assertSame('7221234567', $data['Teléfono (WhatsApp)']);
        $this->assertSame('WhatsApp', $data['Origen']);
        $this->assertSame('7221234567', \App\Support\FormContact::from($form)['phone']); // lo encuentran las rifas
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}\.\d{3}Z$/', $data['fecha']);

        // Ya terminó: ahora contesta la IA y su respuesta se guarda
        $this->send($this->textPayload('wamid.7', '¿Dónde están ubicados?'));
        $this->assertTrue(WhatsAppMessage::where('sender_type', 'ai')->where('body', 'Respuesta de la IA')->exists());

        // Las preguntas del bot también quedaron guardadas
        $this->assertTrue(WhatsAppMessage::where('sender_type', 'ai')->where('body', 'like', '¿Cuál es tu nombre?%')->exists());
    }

    public function test_duplicate_webhook_is_processed_once(): void
    {
        $this->send($this->textPayload('wamid.dup', 'Hola'));
        $sentAfterFirst = $this->sent;

        $this->send($this->textPayload('wamid.dup', 'Hola'));

        $this->assertSame(1, WhatsAppMessage::where('meta_message_id', 'wamid.dup')->count());
        $this->assertSame($sentAfterFirst, $this->sent);
    }

    public function test_agent_stays_quiet_after_a_person_replies_from_the_phone(): void
    {
        $this->send($this->textPayload('wamid.1', 'Hola'));

        // Alguien de Pitamex contesta desde la app del celular
        $this->send([
            'entry' => [[
                'changes' => [[
                    'field' => 'smb_message_echoes',
                    'value' => ['message_echoes' => [[
                        'from' => '527220000000', 'to' => self::CUSTOMER, 'id' => 'wamid.echo.1',
                        'timestamp' => (string) now()->timestamp, 'type' => 'text', 'text' => ['body' => 'Hola, soy Javier'],
                    ]]],
                ]],
            ]],
        ]);

        $conversation = WhatsAppConversation::first();
        $this->assertSame('human', $conversation->mode);
        $this->assertTrue(WhatsAppMessage::where('sender_type', 'whatsapp_app')->exists());

        $sentBefore = $this->sent;
        $this->send($this->textPayload('wamid.2', 'Juan Pérez'));
        $this->assertSame($sentBefore, $this->sent, 'El bot no debe contestar mientras atiende una persona');

        // Se vence la pausa: vuelve el bot
        $conversation->update(['ai_paused_until' => now()->subMinute()]);
        $this->send($this->textPayload('wamid.3', 'Juan Pérez'));
        $this->assertGreaterThan($sentBefore, $this->sent);
        $this->assertSame('ai', $conversation->fresh()->mode);
    }

    /* ------------------------------------------------------------- */

    private function send(array $payload): void
    {
        $body = json_encode($payload);

        $this->call('POST', '/api/webhook/whatsapp', [], [], [], [
            'CONTENT_TYPE'              => 'application/json',
            'HTTP_X_HUB_SIGNATURE_256' => 'sha256=' . hash_hmac('sha256', $body, self::SECRET),
        ], $body)->assertOk();
    }

    private function textPayload(string $id, string $text): array
    {
        return $this->messagePayload(['id' => $id, 'type' => 'text', 'text' => ['body' => $text]]);
    }

    private function buttonPayload(string $id, string $replyId, string $title): array
    {
        return $this->messagePayload([
            'id' => $id,
            'type' => 'interactive',
            'interactive' => ['type' => 'button_reply', 'button_reply' => ['id' => $replyId, 'title' => $title]],
        ]);
    }

    private function messagePayload(array $message): array
    {
        return [
            'entry' => [[
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'contacts' => [['wa_id' => self::CUSTOMER, 'profile' => ['name' => 'Juan']]],
                        'messages' => [array_merge(['from' => self::CUSTOMER, 'timestamp' => (string) now()->timestamp], $message)],
                    ],
                ]],
            ]],
        ];
    }
}
