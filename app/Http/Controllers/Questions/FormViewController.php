<?php

namespace App\Http\Controllers\Questions;

use App\Models\Form;
use App\Models\TypeForm;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
/**
 * Ver una encuesta contestada (preguntas + respuestas) y exportarla a PDF.
 */
class FormViewController extends Controller
{
    /** Llaves del registro de la PWA que no son respuestas */
    private const META_KEYS = ['id', 'fecha', 'send_api'];

    /**
     * GET /forms/{form}/views
     */
    public function show(Form $form)
    {
        return Inertia::render('Questions/Show', [
            'registro' => $this->payload($form),
        ]);
    }

    /**
     * GET /forms/{form}/pdf
     */
    public function pdf(Form $form)
    {
        $registro = $this->payload($form);

        $fileName = 'encuesta_' . Str::slug($registro['name'] ?: 'registro') . '_' . $form->id . '.pdf';

        return Pdf::loadView('pdf.form', ['registro' => $registro])
            ->setPaper('letter')
            ->download($fileName);
    }

    /**
     * DELETE /forms/{form}
     * Desde la lista regresa a la misma página; desde el detalle regresa a la lista.
     */
    public function destroy(Request $request, Form $form)
    {
        $form->delete();

        $comesFromDetail = str_contains((string) $request->headers->get('referer'), "/forms/{$form->id}/view");

        return ($comesFromDetail ? redirect()->route('forms') : back())
            ->with('success', 'Encuesta eliminada.');
    }

    /**
     * Datos de la encuesta listos para la vista y el PDF.
     */
    private function payload(Form $form): array
    {
        $data = $this->decode($form->form);

        $answeredAt = $data['fecha'] ?? null;

        return [
            'id'          => $form->id,
            'name'        => $form->name,
            'type'        => TypeForm::whereKey($form->type_form_id)->value('name') ?? 'Sin área de interés',
            'answered_at' => $this->formatDate($answeredAt ?? $form->created_at),
            'received_at' => $this->formatDate($form->created_at),
            'answers'     => $this->answers($data),
        ];
    }

    /**
     * La columna form puede venir como texto JSON o como arreglo (según el cast del modelo).
     */
    private function decode(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        // Registros guardados con doble json_encode
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Convierte el registro en una lista [{question, answer}] en el orden en que se contestó.
     */
    private function answers(array $data): array
    {
        $answers = [];

        foreach ($data as $question => $answer) {
            if (in_array($question, self::META_KEYS, true)) {
                continue;
            }

            $answers[] = [
                'question' => $this->questionLabel((string) $question),
                'answer'   => $this->answerText($answer),
            ];
        }

        return $answers;
    }

    /**
     * Las preguntas nuevas ya vienen con su texto ("¿Cuál es tu nombre?").
     * Las viejas vienen como llave (name_complete) y se vuelven legibles ("Name complete").
     */
    private function questionLabel(string $key): string
    {
        // Llaves viejas: name_complete, Load_position, email, country...
        $isLegacyKey = (bool) preg_match('/^[A-Za-z0-9_]+$/', $key)
            && (str_contains($key, '_') || ctype_lower($key[0]));

        return $isLegacyKey
            ? Str::ucfirst(str_replace('_', ' ', Str::snake($key)))
            : $key;
    }

    private function answerText(mixed $answer): string
    {
        if (is_array($answer)) {
            return implode(', ', array_map('strval', $answer));
        }

        if (is_bool($answer)) {
            return $answer ? 'Sí' : 'No';
        }

        $text = trim((string) $answer);

        return $text === '' ? 'Sin respuesta' : $text;
    }

    private function formatDate(mixed $date): ?string
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date)
                ->timezone(config('app.timezone'))
                ->locale('es')
                ->translatedFormat('d \d\e F \d\e Y, H:i');
        } catch (\Throwable) {
            return (string) $date;
        }
    }
}
