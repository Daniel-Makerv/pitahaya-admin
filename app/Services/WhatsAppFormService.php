<?php

namespace App\Services;

use App\Models\WhatsAppAnswer;
use App\Models\WhatsAppConversation;
use App\Models\WhatsAppForm;
use App\Models\WhatsAppFormBlock;
use App\Models\WhatsAppFormSession;
use App\Models\WhatsAppQuestion;
use App\Models\WhatsAppQuestionOption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Cuestionario del chatbot de WhatsApp.
 *
 * Flujo:
 *  start()  -> crea la sesión, manda bienvenida y la primera pregunta del bloque inicial
 *  answer() -> valida la respuesta, la guarda con su puntaje y manda la siguiente pregunta
 *  al final -> marca la sesión como completada y la guarda en forms (WhatsAppFormExporter)
 *
 * Orden de los bloques al terminar uno:
 *  1. next_block_id de la opción elegida (para preguntas que ramifican)
 *  2. next_block_id del bloque
 *  3. el siguiente bloque por sort_order, salvo que el bloque sea final (is_final)
 */
class WhatsAppFormService
{
    /** Tope de bloques por sesión, por si alguien configura un ciclo A -> B -> A */
    private const MAX_BLOCKS = 30;

    public function __construct(
        private WhatsAppService $whatsAppService,
        private WhatsAppFormExporter $exporter,
    ) {}

    /**
     * Empieza el cuestionario. Regresa null si no hay formulario activo o está mal armado
     * (en ese caso contesta la IA).
     */
    public function start(WhatsAppConversation $conversation): ?WhatsAppFormSession
    {
        // Se usa el primer formulario activo
        $form = WhatsAppForm::query()->where('active', true)->orderBy('id')->first();

        if (! $form) {
            return null;
        }

        $startBlock = $form->blocks()->where('is_start', true)->first() ?? $form->blocks()->first();
        [$block, $question] = $startBlock ? $this->firstQuestionFrom($startBlock) : [null, null];

        if (! $question) {
            Log::warning('WhatsApp: el formulario activo no tiene preguntas', ['form_id' => $form->id]);

            return null;
        }

        $session = WhatsAppFormSession::create([
            'whatsapp_conversation_id' => $conversation->id,
            'form_id'             => $form->id,
            'current_block_id'    => $block->id,
            'current_question_id' => $question->id,
            'status'              => 'in_progress',
            'score'               => 0,
            'started_at'          => now(),
        ]);

        $this->sendText(
            $conversation,
            "¡Qué tal! Soy el asistente de Pitamex 🌵\n"
            . 'Te hago unas preguntas para conocer tu huerta. '
            . 'Tarda menos de dos minutos.'
        );

        $this->sendQuestion($conversation, $question);

        return $session;
    }

    /**
     * Procesa la respuesta a la pregunta actual.
     *
     * @param  string       $text     lo que escribió o el título del botón que tocó
     * @param  string|null  $replyId  id del botón / fila de la lista (option_15), si tocó uno
     */
    public function answer(WhatsAppFormSession $session, string $text, ?string $replyId = null): void
    {
        $session->loadMissing('conversation.contact');
        $question = $session->currentQuestion;

        if (! $question) {
            $this->finish($session);

            return;
        }

        $parsed = $this->parse($question, $text, $replyId);

        if ($parsed === null) {
            $this->askAgain($session, $this->invalidMessage($question));

            return;
        }

        WhatsAppAnswer::updateOrCreate(
            ['form_session_id' => $session->id, 'question_id' => $question->id],
            [
                'answer'    => $parsed['answer'],
                'option_id' => $parsed['option']?->id,
                'score'     => $parsed['score'],
            ]
        );

        $session->update(['score' => (int) $session->answers()->sum('score')]);

        [$block, $next] = $this->nextQuestion($session, $question, $parsed['option']);

        if (! $next) {
            $this->finish($session);

            return;
        }

        $session->update([
            'current_block_id'    => $block->id,
            'current_question_id' => $next->id,
        ]);

        $this->sendQuestion($session->conversation, $next);
    }

    /** Manda un aviso y vuelve a hacer la pregunta actual */
    public function askAgain(WhatsAppFormSession $session, string $notice): void
    {
        $session->loadMissing('conversation.contact');

        $this->sendText($session->conversation, $notice);

        if ($session->currentQuestion) {
            $this->sendQuestion($session->conversation, $session->currentQuestion);
        }
    }

    /* ---------------------------------------------------------------
     | Fin del cuestionario
     * --------------------------------------------------------------- */

    private function finish(WhatsAppFormSession $session): void
    {
        $session->update([
            'current_question_id' => null,
            'status'              => 'completed',
            'completed_at'        => now(),
            'score'               => (int) $session->answers()->sum('score'),
        ]);

        // Que llegue a /forms aunque falle el mensaje de despedida
        try {
            $this->exporter->export($session);
        } catch (Throwable $e) {
            Log::error('WhatsApp: no se pudo guardar el cuestionario en forms', [
                'session_id' => $session->id,
                'error'      => $e->getMessage(),
            ]);
        }

        $this->sendText(
            $session->conversation,
            "¡Gracias! Ya tenemos tus respuestas 🌵\n"
            . 'Un asesor de Pitamex las revisará. Si tienes alguna duda, escríbela aquí.'
        );
    }

    /* ---------------------------------------------------------------
     | Siguiente pregunta
     * --------------------------------------------------------------- */

    /** @return array{0: ?WhatsAppFormBlock, 1: ?WhatsAppQuestion} */
    private function nextQuestion(WhatsAppFormSession $session, WhatsAppQuestion $current, ?WhatsAppQuestionOption $option): array
    {
        // Siguiente pregunta del mismo bloque
        $next = WhatsAppQuestion::query()
            ->where('block_id', $current->block_id)
            ->where('active', true)
            ->where('sort_order', '>', $current->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            return [$current->block, $next];
        }

        // Terminó el bloque: la opción elegida puede mandar a otro bloque
        $block = $this->nextBlock($current->block, $option?->next_block_id);
        $visited = 0;

        while ($block && $visited++ < self::MAX_BLOCKS) {
            if ($question = $block->questions()->first()) {
                return [$block, $question];
            }

            $block = $this->nextBlock($block); // bloque vacío: se brinca
        }

        return [null, null];
    }

    private function nextBlock(WhatsAppFormBlock $block, ?int $forcedBlockId = null): ?WhatsAppFormBlock
    {
        $targetId = $forcedBlockId ?: $block->next_block_id;

        if ($targetId) {
            $target = WhatsAppFormBlock::where('form_id', $block->form_id)->find($targetId);

            return $target && $target->id !== $block->id ? $target : null;
        }

        if ($block->is_final) {
            return null;
        }

        return WhatsAppFormBlock::query()
            ->where('form_id', $block->form_id)
            ->where(fn ($q) => $q
                ->where('sort_order', '>', $block->sort_order)
                ->orWhere(fn ($q) => $q->where('sort_order', $block->sort_order)->where('id', '>', $block->id)))
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    /** @return array{0: ?WhatsAppFormBlock, 1: ?WhatsAppQuestion} */
    private function firstQuestionFrom(WhatsAppFormBlock $block): array
    {
        $visited = 0;

        while ($block && $visited++ < self::MAX_BLOCKS) {
            if ($question = $block->questions()->first()) {
                return [$block, $question];
            }

            $block = $this->nextBlock($block);
        }

        return [null, null];
    }

    /* ---------------------------------------------------------------
     | Validar respuestas
     * --------------------------------------------------------------- */

    /**
     * @return array{answer: string, option: ?WhatsAppQuestionOption, score: int}|null  null = respuesta no válida
     */
    private function parse(WhatsAppQuestion $question, string $text, ?string $replyId): ?array
    {
        $text = trim($text);

        // Preguntas opcionales: "omitir" / "no sé" las brinca
        if (! $question->required && in_array($this->normalize($text), ['omitir', 'saltar', 'no se', 'paso'], true)) {
            return ['answer' => 'Sin respuesta', 'option' => null, 'score' => 0];
        }

        return match ($question->type) {
            'number'          => $this->parseNumber($text),
            'single_choice'   => $this->parseSingle($question, $text, $replyId),
            'multiple_choice' => $this->parseMultiple($question, $text),
            default           => $text !== '' ? ['answer' => Str::limit($text, 2000, ''), 'option' => null, 'score' => 0] : null,
        };
    }

    private function parseNumber(string $text): ?array
    {
        // Acepta "3", "2.5", "2,5 hectáreas", "1,200"
        if (! preg_match('/-?\d+(?:[.,]\d+)*/', $text, $match)) {
            return null;
        }

        $number = $match[0];

        // "1,200" (miles) vs "2,5" (decimal)
        $number = preg_match('/^\d{1,3}(,\d{3})+$/', $number)
            ? str_replace(',', '', $number)
            : str_replace(',', '.', $number);

        return is_numeric($number)
            ? ['answer' => $number, 'option' => null, 'score' => 0]
            : null;
    }

    private function parseSingle(WhatsAppQuestion $question, string $text, ?string $replyId): ?array
    {
        $options = $question->options()->get();

        if ($options->isEmpty()) {
            return $text !== '' ? ['answer' => $text, 'option' => null, 'score' => 0] : null;
        }

        $option = $this->optionFromReplyId($options, $replyId) ?? $this->matchOption($options, $text);

        return $option
            ? ['answer' => $option->text, 'option' => $option, 'score' => (int) $option->score]
            : null;
    }

    private function parseMultiple(WhatsAppQuestion $question, string $text): ?array
    {
        $options = $question->options()->get();

        if ($options->isEmpty()) {
            return $text !== '' ? ['answer' => $text, 'option' => null, 'score' => 0] : null;
        }

        // "1, 3", "1 y 3", "1 3", o los textos separados por coma
        $parts = preg_split('/\s*(?:,|;|\sy\s|\se\s|\s)\s*/u', $this->normalize($text), -1, PREG_SPLIT_NO_EMPTY);
        $chosen = collect();

        if (collect($parts)->every(fn ($p) => ctype_digit($p))) {
            foreach ($parts as $p) {
                if ($option = $options->values()->get((int) $p - 1)) {
                    $chosen->push($option);
                }
            }
        } else {
            foreach (preg_split('/\s*(?:,|;)\s*/u', $text, -1, PREG_SPLIT_NO_EMPTY) as $piece) {
                if ($option = $this->matchOption($options, $piece)) {
                    $chosen->push($option);
                }
            }
        }

        $chosen = $chosen->unique('id')->values();

        if ($chosen->isEmpty()) {
            return null;
        }

        return [
            'answer' => $chosen->pluck('text')->implode(', '),
            'option' => $chosen->count() === 1 ? $chosen->first() : null,
            'score'  => (int) $chosen->sum('score'),
        ];
    }

    private function optionFromReplyId(Collection $options, ?string $replyId): ?WhatsAppQuestionOption
    {
        if (! $replyId || ! preg_match('/^option_(\d+)$/', $replyId, $m)) {
            return null;
        }

        return $options->firstWhere('id', (int) $m[1]);
    }

    /** Por número ("2"), por texto exacto o por el texto recortado del botón/lista */
    private function matchOption(Collection $options, string $text): ?WhatsAppQuestionOption
    {
        $normalized = $this->normalize($text);

        if ($normalized === '') {
            return null;
        }

        // Texto exacto primero (por si una opción es literalmente "2")
        if ($exact = $options->first(fn ($o) => $this->normalize($o->text) === $normalized)) {
            return $exact;
        }

        if (ctype_digit($normalized)) {
            return $options->values()->get((int) $normalized - 1);
        }

        // Botones y listas recortan el texto a 20/24 caracteres
        return $options->first(fn ($o) => mb_strlen($normalized) >= 3 && str_starts_with($this->normalize($o->text), $normalized));
    }

    private function normalize(string $text): string
    {
        return trim(preg_replace('/\s+/', ' ', Str::lower(Str::ascii($text))));
    }

    private function invalidMessage(WhatsAppQuestion $question): string
    {
        return match ($question->type) {
            'number'          => 'Escríbeme solo el número, por ejemplo: 3',
            'single_choice'   => 'Elige una de las opciones, por favor 👇',
            'multiple_choice' => 'Escríbeme los números de las opciones, por ejemplo: 1, 3',
            default           => 'No alcancé a leer tu respuesta. ¿Me la escribes de nuevo?',
        };
    }

    /* ---------------------------------------------------------------
     | Enviar (y guardar en whats_app_messages)
     * --------------------------------------------------------------- */

    private function sendQuestion(WhatsAppConversation $conversation, WhatsAppQuestion $question): void
    {
        $conversation->loadMissing('contact');
        $phone = $conversation->contact->phone;
        $options = in_array($question->type, ['single_choice', 'multiple_choice'], true)
            ? $question->options()->get()
            : collect();

        // Opción única: botones (hasta 3) o lista (hasta 10)
        if ($question->type === 'single_choice' && $options->isNotEmpty() && $options->count() <= 10) {
            $result = $options->count() <= 3
                ? $this->whatsAppService->sendButtons($phone, $question->text, $options->all())
                : $this->whatsAppService->sendList($phone, $question->text, $options->all());

            $this->whatsAppService->record(
                $conversation,
                $result,
                'interactive',
                $question->text,
                ['question_id' => $question->id, 'options' => $options->pluck('text')->all()]
            );

            return;
        }

        // Texto, número, opción múltiple o más de 10 opciones: texto con la lista numerada
        $message = $question->text;

        if ($options->isNotEmpty()) {
            $message .= "\n";

            foreach ($options->values() as $i => $option) {
                $message .= "\n" . ($i + 1) . '. ' . $option->text;
            }

            $message .= $question->type === 'multiple_choice'
                ? "\n\nPuedes elegir varias: escribe los números separados por coma (ej. 1, 3)."
                : "\n\nEscribe el número de tu respuesta.";
        }

        if (! $question->required) {
            $message .= "\n(Si prefieres no contestar, escribe: omitir)";
        }

        $this->sendText($conversation, $message, ['question_id' => $question->id]);
    }

    private function sendText(WhatsAppConversation $conversation, string $message, array $payload = []): void
    {
        $conversation->loadMissing('contact');

        $result = $this->whatsAppService->sendText($conversation->contact->phone, $message);

        $this->whatsAppService->record($conversation, $result, 'text', $message, $payload);
    }
}
