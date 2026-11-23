<?php

namespace App\Services;

use App\Models\Form;
use App\Models\TypeForm;
use App\Models\WhatsAppFormSession;
use Illuminate\Support\Str;

/**
 * Guarda un cuestionario de WhatsApp terminado en forms, con el mismo formato que la PWA,
 * para que aparezca en /forms, en su detalle, en el PDF, en el Excel y en los candidatos de las rifas.
 *
 *  forms.uuid          whatsapp-{id de la sesión}   (único: si se vuelve a exportar, se actualiza)
 *  forms.name          respuesta a la pregunta que diga "nombre", o el nombre de perfil de WhatsApp
 *  forms.type_form_id  área "WhatsApp · {nombre del formulario}" (se crea sola la primera vez)
 *  forms.form          { "id", "fecha", "<texto de la pregunta>": "<respuesta>", ..., "Teléfono (WhatsApp)", "Puntaje", "Origen" }
 */
class WhatsAppFormExporter
{
    public function export(WhatsAppFormSession $session): Form
    {
        $session->loadMissing(['form', 'conversation.contact', 'answers.question']);

        $uuid = 'whatsapp-' . $session->id;
        $contact = $session->conversation?->contact;
        $answeredAt = ($session->completed_at ?? now())->copy()->utc();

        // Mismas llaves de control que manda la PWA (id y fecha en ISO UTC)
        $data = [
            'id'    => $uuid,
            'fecha' => $answeredAt->format('Y-m-d\TH:i:s.v\Z'),
        ];

        // Respuestas en el orden en que las contestó
        foreach ($session->answers->sortBy('id') as $answer) {
            $question = trim((string) ($answer->question?->text ?? 'Pregunta ' . $answer->question_id));
            $key = Str::limit($question, 250, '');

            // Dos preguntas con el mismo texto: no se pisa la primera
            for ($i = 2; array_key_exists($key, $data); $i++) {
                $key = Str::limit($question, 240, '') . " ({$i})";
            }

            $data[$key] = (string) $answer->answer;
        }

        $data['Teléfono (WhatsApp)'] = $this->phone($contact?->phone ?? $contact?->wa_id);
        $data['Puntaje'] = (int) $session->score;
        $data['Origen'] = 'WhatsApp';

        $typeForm = TypeForm::firstOrCreate(
            ['str' => 'whatsapp_form_' . $session->form_id],
            ['name' => 'WhatsApp · ' . ($session->form?->name ?? 'Formulario')]
        );

        $form = Form::updateOrCreate(
            ['uuid' => $uuid],
            [
                'name'         => $this->name($data, $contact?->name),
                'form'         => json_encode($data, JSON_UNESCAPED_UNICODE),
                'type_form_id' => $typeForm->id,
            ]
        );

        $session->update(['exported_form_id' => $form->id]);

        return $form;
    }

    /** 5217221234567 -> 7221234567 (10 dígitos, como lo capturan en los formularios) */
    private function phone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);

        return strlen($digits) >= 10 ? substr($digits, -10) : ($digits ?: null);
    }

    private function name(array $data, ?string $profileName): string
    {
        foreach ($data as $question => $answer) {
            if (in_array($question, ['id', 'fecha'], true) || trim((string) $answer) === '' || $answer === 'Sin respuesta') {
                continue;
            }

            if (str_contains(Str::lower(Str::ascii((string) $question)), 'nombre')) {
                return Str::limit(trim((string) $answer), 250, '');
            }
        }

        return Str::limit(trim((string) $profileName) ?: 'Sin nombre', 250, '');
    }
}
