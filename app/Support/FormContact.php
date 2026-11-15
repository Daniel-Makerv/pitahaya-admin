<?php

namespace App\Support;

use App\Models\Form;
use Illuminate\Support\Str;

/**
 * Saca nombre, correo y teléfono de un registro de forms,
 * funcione con llaves viejas (email, phone_contact) o con el texto de la pregunta ("Correo").
 */
class FormContact
{
    /**
     * @return array{name: string, email: ?string, phone: ?string}
     */
    public static function from(Form $form): array
    {
        $data = self::decode($form->form);

        return [
            'name'  => $form->name ?: 'Sin nombre',
            'email' => self::email($data),
            'phone' => self::find($data, ['phone_contact'], ['telefono', 'whatsapp', 'celular']),
        ];
    }

    public static function decode(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private static function email(array $data): ?string
    {
        // 1. Llave vieja o pregunta que diga correo/email
        $value = self::find($data, ['email'], ['correo', 'email', 'e-mail']);

        if ($value && filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return Str::lower($value);
        }

        // 2. Cualquier respuesta que sea un correo válido
        foreach ($data as $answer) {
            if (is_string($answer) && filter_var(trim($answer), FILTER_VALIDATE_EMAIL)) {
                return Str::lower(trim($answer));
            }
        }

        return null;
    }

    /**
     * Busca primero por llave exacta y luego por palabras en el texto de la pregunta.
     */
    private static function find(array $data, array $keys, array $words): ?string
    {
        foreach ($keys as $key) {
            if (! empty($data[$key]) && is_scalar($data[$key])) {
                return trim((string) $data[$key]);
            }
        }

        foreach ($data as $question => $answer) {
            if (! is_scalar($answer) || trim((string) $answer) === '') {
                continue;
            }

            $normalized = Str::lower(Str::ascii((string) $question));

            foreach ($words as $word) {
                if (str_contains($normalized, $word)) {
                    return trim((string) $answer);
                }
            }
        }

        return null;
    }
}
