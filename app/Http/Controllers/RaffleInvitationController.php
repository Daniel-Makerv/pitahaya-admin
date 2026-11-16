<?php

namespace App\Http\Controllers;

use App\Mail\RaffleNumber;
use App\Models\Raffle;
use App\Models\RaffleParticipant;
use App\Services\ScoringPitamex;
use App\Support\FormContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

/**
 * Registro de productor + rifa.
 *
 * Con invitación por correo (liga personal):
 *  GET  /rifa/invitacion/{token}            -> registro en 6 pasos (o boleto si ya confirmó)
 *  POST /rifa/invitacion/{token}/progreso   -> guarda el avance en cada "Siguiente"
 *  POST /rifa/invitacion/{token}            -> envía el registro: calificación + número + correo
 *
 * Sin correo (link público de la rifa, para compartir o usar en el stand):
 *  GET  /rifa/{code}                        -> el mismo registro, vacío
 *  POST /rifa/{code}/registro               -> al terminar el paso 1 crea al participante y regresa su token;
 *                                              de ahí en adelante usa las mismas rutas de arriba
 */
class RaffleInvitationController extends Controller
{
    /** Llaves internas del formulario que no son respuestas */
    private const INTERNAL_KEYS = ['_priv'];

    public function show(string $token)
    {
        $participant = $this->findParticipant($token);
        $raffle = $participant->raffle;

        if (! $raffle->isOpen() && $participant->status !== 'confirmed') {
            return view('raffles.closed', compact('participant', 'raffle'));
        }

        $saved = $participant->data ?? [];

        return view('raffles.invitation', [
            'raffle' => $raffle,
            'config' => [
                ...$this->baseConfig($raffle),
                'token'       => $participant->token,
                'progressUrl' => route('raffles.invitation.progress', $participant->token),
                'confirmUrl'  => route('raffles.invitation.confirm', $participant->token),

                // Avance guardado (si ya había empezado)
                'saved'       => $saved ?: null,
                'step'        => min((int) $participant->step, 5),

                // Si ya confirmó, se muestra directo su boleto
                'number'      => $participant->status === 'confirmed' ? $participant->formattedNumber() : null,

                // Prellenado con lo que dio en la Expo (solo si todavía no había empezado)
                'prefill'     => $saved ? null : $this->prefill($participant),
                'expoAnswers' => $saved ? null : $this->expoAnswers($participant),
            ],
        ]);
    }

    /**
     * GET /rifa/{code} — registro sin invitación (formulario vacío).
     */
    public function openShow(string $code)
    {
        $raffle = Raffle::where('public_code', strtoupper($code))->firstOrFail();

        if (! $raffle->isOpen()) {
            return view('raffles.closed', ['raffle' => $raffle, 'participant' => null]);
        }

        return view('raffles.invitation', [
            'raffle' => $raffle,
            'config' => [
                ...$this->baseConfig($raffle),
                'open'     => true,
                'code'     => $raffle->public_code,
                'startUrl' => route('raffles.open.start', $raffle->public_code),
                'token'    => null,
            ],
        ]);
    }

    /**
     * POST /rifa/{code}/registro — crea al participante con los datos del paso 1.
     * Regresa su token para que el resto del registro use las rutas normales.
     */
    public function openStart(Request $request, string $code): JsonResponse
    {
        $raffle = Raffle::where('public_code', strtoupper($code))->firstOrFail();

        if (! $raffle->isOpen()) {
            return response()->json(['message' => 'Esta rifa ya se sorteó y no recibe más participantes.'], 409);
        }

        $request->validate([
            'answers'          => ['required', 'array', 'max:80'],
            'answers.nombre'   => ['required', 'string', 'max:255'],
            'answers.telefono' => ['required', 'string', 'max:30', $this->phoneRule()],
            'answers.correo'   => ['nullable', 'email', 'max:255'],
            'step'             => ['required', 'integer', 'between:0,6'],
        ], $this->messages());

        $answers = $this->cleanAnswers($request->input('answers'));

        if ($raffle->confirmedWithPhone($answers['telefono'])) {
            return response()->json(['message' => $this->phoneTakenMessage()], 409);
        }

        $email = ! empty($answers['correo']) ? mb_strtolower($answers['correo']) : null;

        // Si ya tenía invitación con ese correo, se continúa con esa (no se duplica)
        $participant = $email
            ? RaffleParticipant::where('raffle_id', $raffle->id)->where('email', $email)->first()
            : null;

        if ($participant?->status === 'confirmed') {
            return response()->json(['message' => 'Ese correo ya está registrado en esta rifa. Revise su correo: ahí está su número.'], 409);
        }

        $participant ??= new RaffleParticipant([
            'raffle_id' => $raffle->id,
            'source'    => 'open',
            'email'     => $email,
        ]);

        $participant->fill([
            'name' => trim($answers['nombre']),
            'data' => array_merge($participant->data ?? [], $answers),
            'step' => max((int) $participant->step, (int) $request->input('step')),
        ])->save();

        return response()->json([
            'token'       => $participant->token,
            'progressUrl' => route('raffles.invitation.progress', $participant->token),
            'confirmUrl'  => route('raffles.invitation.confirm', $participant->token),
        ]);
    }

    /**
     * Guarda el avance del registro sin confirmar (cada "Siguiente").
     */
    public function progress(Request $request, string $token): JsonResponse
    {
        $participant = $this->findParticipant($token);

        if (! $participant->raffle->isOpen()) {
            return response()->json(['message' => 'La rifa ya está cerrada.'], 409);
        }

        $data = $request->validate([
            'answers' => ['required', 'array', 'max:80'],
            'step'    => ['required', 'integer', 'between:0,6'],
        ]);

        // Quien ya confirmó solo actualiza respuestas; su número no cambia
        $participant->update([
            'data' => $this->cleanAnswers($data['answers']),
            'step' => max((int) $participant->step, (int) $data['step']),
        ]);

        return response()->json(['ok' => true]);
    }

    /**
     * Envío final: valida, califica, asigna número (o regresa el mismo) y manda el correo.
     */
    public function confirm(Request $request, string $token, ScoringPitamex $scoring): JsonResponse
    {
        $participant = $this->findParticipant($token);
        $raffle = $participant->raffle;

        $request->validate([
            'answers'           => ['required', 'array', 'max:80'],
            'answers.nombre'    => ['required', 'string', 'max:255'],
            'answers.telefono'  => ['required', 'string', 'max:30', $this->phoneRule()],
            'answers.correo'    => ['nullable', 'email', 'max:255'],
            'answers.estado'    => ['required', 'string', 'max:100'],
            'answers.municipio' => ['required', 'string', 'max:150'],
            'answers._priv'     => ['accepted'],
        ], $this->messages());

        $answers = $this->cleanAnswers($request->input('answers'));
        $wasConfirmed = $participant->status === 'confirmed';

        // Un boleto por persona: el teléfono no se puede repetir en la misma rifa
        if ($raffle->confirmedWithPhone($answers['telefono'], $participant->id)) {
            return response()->json(['message' => $this->phoneTakenMessage()], 409);
        }

        // Quien se registró sin correo y lo dio en el formulario: se guarda para mandarle su número
        if (! $participant->email && ! empty($answers['correo'])) {
            $email = mb_strtolower($answers['correo']);
            $taken = RaffleParticipant::where('raffle_id', $raffle->id)->where('email', $email)->where('id', '!=', $participant->id)->exists();
            if (! $taken) {
                $participant->update(['email' => $email]);
            }
        }

        // La calificación es interna: si falla, el productor igual recibe su número
        try {
            [$score, $priority] = $scoring->calificar($answers, completo: true);
        } catch (Throwable $e) {
            Log::error('Rifa: no se pudo calificar el registro', ['participant' => $participant->id, 'error' => $e->getMessage()]);
            [$score, $priority] = [null, null];
        }

        try {
            $participant = $raffle->confirmParticipant($participant, $answers);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }

        $participant->update([
            'name'     => trim($answers['nombre']),
            'step'     => 6,
            'score'    => $score,
            'priority' => $priority,
        ]);

        // Correo con el número (solo la primera vez, para no duplicarlo si reenvía)
        if (! $wasConfirmed) {
            $this->sendNumberEmail($participant, $answers['correo'] ?? null);
        }

        return response()->json([
            'number' => $participant->formattedNumber(),
        ]);
    }

    /* ---------------------------------------------------------------- */

    /** Datos de la rifa que usa la página en los dos modos */
    private function baseConfig(Raffle $raffle): array
    {
        return [
            'open'        => false,
            'logo'        => asset('images/raffles/logo-pitamex.png'),
            'privacyUrl'  => config('raffles.privacy_url'),
            'prizes'      => $raffle->prizeList(),
            'drawDate'    => $raffle->draw_date?->locale('es')->translatedFormat('d \d\e F \d\e Y'),
            'digits'      => $raffle->digits,
            'closed'      => ! $raffle->isOpen(),
            'saved'       => null,
            'step'        => 0,
            'number'      => null,
            'prefill'     => null,
            'expoAnswers' => null,
        ];
    }

    private function phoneRule(): \Closure
    {
        return function ($attr, $value, $fail) {
            if (strlen(preg_replace('/\D/', '', (string) $value)) < 10) {
                $fail('El teléfono debe tener 10 dígitos.');
            }
        };
    }

    private function phoneTakenMessage(): string
    {
        return 'Este teléfono ya tiene un boleto en esta rifa. Solo se permite un número por persona.';
    }

    private function messages(): array
    {
        return [
            'answers.nombre.required'    => 'Escriba su nombre completo.',
            'answers.telefono.required'  => 'Escriba su teléfono.',
            'answers.correo.email'       => 'El correo no es válido.',
            'answers.estado.required'    => 'Seleccione su estado.',
            'answers.municipio.required' => 'Escriba su municipio.',
            'answers._priv.accepted'     => 'Debe aceptar el aviso de privacidad.',
        ];
    }

    private function findParticipant(string $token): RaffleParticipant
    {
        return RaffleParticipant::with(['raffle', 'form'])->where('token', $token)->firstOrFail();
    }

    /**
     * Solo se guardan textos, números, listas de textos y la ubicación {lat, lng}.
     */
    private function cleanAnswers(array $answers): array
    {
        $clean = [];

        foreach ($answers as $key => $value) {
            if (! is_string($key) || strlen($key) > 40) {
                continue;
            }

            if ($key === 'ubicacion' && is_array($value)) {
                if (isset($value['lat'], $value['lng']) && is_numeric($value['lat']) && is_numeric($value['lng'])) {
                    $clean[$key] = ['lat' => (float) $value['lat'], 'lng' => (float) $value['lng']];
                }
                continue;
            }

            if (is_array($value)) {
                $clean[$key] = array_values(array_map(fn ($v) => mb_substr((string) $v, 0, 255), array_filter($value, 'is_scalar')));
                continue;
            }

            if (is_bool($value) || in_array($key, self::INTERNAL_KEYS, true)) {
                $clean[$key] = (bool) $value;
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $clean[$key] = mb_substr(trim((string) $value), 0, 2000);
            }
        }

        // Si quitó "Sí" en riego, el tipo de riego ya no aplica
        if (($clean['riego'] ?? null) !== 'Sí') {
            unset($clean['tipo_riego']);
        }

        return $clean;
    }

    /**
     * Datos de contacto que ya tenemos: nombre, teléfono y correo.
     */
    private function prefill(RaffleParticipant $participant): array
    {
        $contact = $participant->form ? FormContact::from($participant->form) : ['phone' => null];

        // En invitaciones manuales sin nombre, "name" guarda el correo: no se prellena
        $name = $participant->name === $participant->email ? null : $participant->name;

        return array_filter([
            'nombre'   => $name,
            'telefono' => $contact['phone'] ? preg_replace('/[^\d\s+]/', '', $contact['phone']) : null,
            'correo'   => $participant->email,
        ]);
    }

    /**
     * Respuestas del formulario de la Expo, para prellenar en la página las preguntas que coincidan.
     */
    private function expoAnswers(RaffleParticipant $participant): array
    {
        if (! $participant->form) {
            return [];
        }

        return collect(FormContact::decode($participant->form->form))
            ->except(['id', 'fecha', 'send_api'])
            ->filter(fn ($v) => is_scalar($v) && trim((string) $v) !== '')
            ->map(fn ($v) => (string) $v)
            ->all();
    }

    private function sendNumberEmail(RaffleParticipant $participant, ?string $email): void
    {
        $email = $email ?: $participant->email;

        if (! $email) {
            return;
        }

        try {
            Mail::to($email, $participant->name)->queue(new RaffleNumber($participant->raffle, $participant));
        } catch (Throwable $e) {
            Log::error('Rifa: no se pudo enviar el número', ['participant' => $participant->id, 'error' => $e->getMessage()]);
        }
    }
}
