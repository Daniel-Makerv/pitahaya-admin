<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\TypeForm;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class FormController extends Controller
{
    private const API_TOKEN = 'goro4vmm.gd3';

    /** Llaves del registro que no son respuestas */
    private const META_KEYS = ['id', 'fecha', 'send_api'];

    /**
     * POST /api/create/form
     *
     * Body que manda la PWA:
     * {
     *   "uuid": "section_3",            // id de la pregunta principal (o grupo viejo: plantOrEsqueaje, info...)
     *   "title": "Asesoría técnica",    // nombre de la pregunta principal
     *   "data": { "id": "...", "fecha": "...", "¿Cuál es tu nombre?": "Juan", ... }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        if (! hash_equals(self::API_TOKEN, (string) $request->bearerToken())) {
            return $this->fail('Token incorrecto', 401);
        }

        $uuid = trim((string) $request->input('uuid', ''));
        $title = trim((string) $request->input('title', ''));
        $data = $request->input('data');

        if ($uuid === '' || ! is_array($data)) {
            return $this->fail('Faltan uuid o data', 422);
        }

        $recordId = $data['id'] ?? null;

        try {
            // Ya se había recibido este registro (la PWA reintentó): se responde OK para que lo marque como enviado
            if ($recordId && Form::where('uuid', $recordId)->exists()) {
                return $this->ok('Registro ya existente');
            }

            Form::create([
                'uuid'         => $recordId,
                'name'         => $this->extractName($data),
                'form'         => json_encode($data, JSON_UNESCAPED_UNICODE),
                'type_form_id' => $this->resolveTypeForm($uuid, $title)->id,
            ]);
        } catch (Throwable $e) {
            Log::error('create/form: ' . $e->getMessage(), ['uuid' => $uuid, 'title' => $title]);

            return response()->json(['success' => false, 'message' => 'Error al guardar el registro'], 500);
        }

        Log::debug('create/form: registro guardado', ['uuid' => $uuid, 'record' => $recordId]);

        return $this->ok('Guardado correctamente');
    }

    /**
     * Busca el type_form de la pregunta principal; si no existe, lo crea.
     *  1. Por nombre (title)                       -> "Asesoría técnica"
     *  2. Por str (uuid)                           -> grupos viejos o pregunta principal renombrada
     *  3. No existe: lo crea con name = title y str = uuid
     */
    private function resolveTypeForm(string $uuid, string $title): TypeForm
    {
        $name = $title !== '' ? $title : $uuid;

        return TypeForm::where('name', $name)->first()
            ?? TypeForm::where('str', $uuid)->first()
            ?? TypeForm::create([
                'name' => $name,
                'str'  => $uuid,
            ]);
    }

    /**
     * Busca la respuesta del nombre: primero la llave vieja (name_complete),
     * luego cualquier pregunta que diga "nombre".
     */
    private function extractName(array $data): string
    {
        if (! empty($data['name_complete'])) {
            return Str::limit(trim((string) $data['name_complete']), 250, '');
        }

        foreach ($data as $question => $answer) {
            if (in_array($question, self::META_KEYS, true) || ! is_scalar($answer) || trim((string) $answer) === '') {
                continue;
            }

            if (str_contains(Str::lower(Str::ascii((string) $question)), 'nombre')) {
                return Str::limit(trim((string) $answer), 250, '');
            }
        }

        return 'Sin nombre';
    }

    private function ok(string $message): JsonResponse
    {
        return response()->json(['success' => true, 'message' => $message]);
    }

    private function fail(string $message, int $status): JsonResponse
    {
        Log::warning('create/form: ' . $message);

        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
