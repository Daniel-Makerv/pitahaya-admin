<?php

namespace App\Http\Controllers;

use App\Mail\RaffleInvitation;
use App\Models\Form;
use App\Models\Raffle;
use App\Models\RaffleParticipant;
use App\Support\FormContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use RuntimeException;
use Throwable;

/**
 * Admin de rifas: crear, ver participantes, invitar desde formularios y sortear.
 */
class RaffleController extends Controller
{
    /* =========================================================
     |  Rifas
     * ========================================================= */

    public function index()
    {
        $raffles = Raffle::withCount([
            'participants',
            'participants as confirmed_count' => fn ($q) => $q->where('status', 'confirmed'),
        ])
            ->with('winners')
            ->latest()
            ->get()
            ->map(fn (Raffle $raffle) => [
                ...$this->rafflePayload($raffle),
                'invited_count'   => $raffle->participants_count,
                'confirmed_count' => $raffle->confirmed_count,
            ]);

        return Inertia::render('Raffles/Index', [
            'raffles' => $raffles,
        ]);
    }

    public function store(Request $request)
    {
        $raffle = Raffle::create($this->validateRaffle($request));

        return redirect()->route('raffles.show', $raffle)->with('success', 'Rifa creada.');
    }

    public function show(Raffle $raffle)
    {
        $raffle->load(['participants' => fn ($q) => $q->orderByRaw('place IS NULL')->orderBy('place')->orderByRaw('number IS NULL')->orderBy('number')->orderBy('name'), 'winners']);

        return Inertia::render('Raffles/Show', [
            'raffle'       => $this->rafflePayload($raffle),
            'participants' => $raffle->participants->map(fn (RaffleParticipant $p) => [
                'id'           => $p->id,
                'name'         => $p->name,
                'email'        => $p->email,
                'status'       => $p->status,
                'number'       => $raffle->formatNumber($p->number),
                'place'        => $p->place,
                'step'         => $p->step,
                'score'        => $p->score,
                'priority'     => $p->priority,
                'data'         => $p->data ?? [],
                'invited_at'   => $p->invited_at?->format('d/m/Y H:i'),
                'confirmed_at' => $p->confirmed_at?->format('d/m/Y H:i'),
            ])->values(),
        ]);
    }

    public function update(Request $request, Raffle $raffle)
    {
        $data = $this->validateRaffle($request);

        // Las cifras no se pueden cambiar si ya hay números asignados
        if ($raffle->participants()->whereNotNull('number')->exists()) {
            unset($data['digits']);
        }

        $raffle->update($data);

        return back()->with('success', 'Rifa actualizada.');
    }

    public function destroy(Raffle $raffle)
    {
        $raffle->delete();

        return redirect()->route('raffles.index')->with('success', 'Rifa eliminada.');
    }

    /* =========================================================
     |  Invitaciones
     * ========================================================= */

    /**
     * GET /raffles/{raffle}/candidates?search=
     * Registros de formularios con correo que todavía no están invitados a esta rifa.
     */
    public function candidates(Request $request, Raffle $raffle): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));

        $invitedEmails = $raffle->participants()->pluck('email')->map(fn ($e) => strtolower($e))->all();
        $invitedFormIds = $raffle->participants()->whereNotNull('form_id')->pluck('form_id')->all();

        $query = Form::leftJoin('type_forms', 'forms.type_form_id', '=', 'type_forms.id')
            ->select('forms.id', 'forms.name', 'forms.form', 'forms.created_at', 'type_forms.name as type')
            ->orderByDesc('forms.id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('forms.name', 'like', "%{$search}%")
                    ->orWhere('type_forms.name', 'like', "%{$search}%")
                    ->orWhere('forms.form', 'like', "%{$search}%");
            });
        }

        $candidates = [];
        $seenEmails = [];

        // Se recorren por bloques hasta juntar 100 con correo
        foreach ($query->cursor() as $form) {
            $contact = FormContact::from($form);
            $email = $contact['email'];

            if (! $email || in_array($email, $seenEmails, true)) {
                continue;
            }

            $seenEmails[] = $email;

            $candidates[] = [
                'id'      => $form->id,
                'name'    => $contact['name'],
                'email'   => $email,
                'phone'   => $contact['phone'],
                'type'    => $form->type,
                'date'    => $form->created_at?->format('d/m/Y'),
                'invited' => in_array($email, $invitedEmails, true) || in_array($form->id, $invitedFormIds, true),
            ];

            if (count($candidates) >= 100) {
                break;
            }
        }

        return response()->json(['data' => $candidates]);
    }

    /**
     * POST /raffles/{raffle}/invite  { form_ids: [1, 2, 3] }
     * Crea los participantes y les manda el correo. Se usa desde la rifa y desde /forms.
     */
    public function invite(Request $request, Raffle $raffle)
    {
        $data = $request->validate([
            'form_ids'   => ['required', 'array', 'min:1', 'max:200'],
            'form_ids.*' => ['integer', 'exists:forms,id'],
        ], [
            'form_ids.required' => 'Selecciona al menos un registro.',
            'form_ids.max'      => 'Máximo 200 invitaciones a la vez.',
        ]);

        if (! $raffle->isOpen()) {
            return back()->withErrors(['form_ids' => 'Esta rifa ya fue sorteada.']);
        }

        $sent = 0;
        $skipped = [];
        $failed = [];
        $delay = 0; // segundos: los correos salen escalonados para respetar el límite de Resend

        foreach (Form::whereIn('id', $data['form_ids'])->get() as $form) {
            $contact = FormContact::from($form);

            if (! $contact['email']) {
                $skipped[] = "{$contact['name']} (sin correo)";
                continue;
            }

            $participant = RaffleParticipant::firstOrNew([
                'raffle_id' => $raffle->id,
                'email'     => $contact['email'],
            ]);

            if ($participant->exists) {
                $skipped[] = "{$contact['name']} (ya invitado)";
                continue;
            }

            $participant->fill([
                'form_id'    => $form->id,
                'name'       => $contact['name'],
                'invited_at' => now(),
            ])->save();

            if ($this->sendInvitation($raffle, $participant, $delay)) {
                $sent++;
                $delay += config('raffles.send_interval', 1);
            } else {
                $failed[] = $contact['email'];
            }
        }

        $message = $sent === 1 ? 'Se programó 1 invitación.' : "Se programaron {$sent} invitaciones.";
        if ($skipped) {
            $message .= ' Omitidos: '.implode(', ', $skipped).'.';
        }

        $redirect = back()->with('success', $message);

        return $failed
            ? $redirect->withErrors(['form_ids' => 'No se pudo enviar a: '.implode(', ', $failed).'. Usa «Reenviar» en la rifa.'])
            : $redirect;
    }

    public function resend(Raffle $raffle, RaffleParticipant $participant)
    {
        abort_unless($participant->raffle_id === $raffle->id, 404);

        if (! $this->sendInvitation($raffle, $participant)) {
            return back()->withErrors(['resend' => "No se pudo enviar el correo a {$participant->email}."]);
        }

        $participant->update(['invited_at' => now()]);

        return back()->with('success', "Invitación reenviada a {$participant->email}.");
    }

    public function removeParticipant(Raffle $raffle, RaffleParticipant $participant)
    {
        abort_unless($participant->raffle_id === $raffle->id, 404);

        if (! $raffle->isOpen()) {
            return back()->withErrors(['participant' => 'No se pueden quitar participantes de una rifa sorteada.']);
        }

        $participant->delete();

        return back()->with('success', 'Participante eliminado.');
    }

    /* =========================================================
     |  Sorteo
     * ========================================================= */

    /**
     * POST /raffles/{raffle}/draw  { lottery_number: "45821", lottery_draw: "3912" }
     */
    public function draw(Request $request, Raffle $raffle)
    {
        $data = $request->validate([
            'lottery_number' => ['required', 'string', 'max:20', 'regex:/^[\d\s-]+$/'],
            'lottery_draw'   => ['nullable', 'string', 'max:50'],
        ], [
            'lottery_number.required' => 'Captura el número del premio mayor.',
            'lottery_number.regex'    => 'El número solo puede tener dígitos.',
        ]);

        try {
            $winners = $raffle->draw($data['lottery_number'], $data['lottery_draw'] ?? null);
        } catch (RuntimeException $e) {
            return back()->withErrors(['lottery_number' => $e->getMessage()]);
        }

        $first = $winners->first();

        return back()->with('success', "Sorteo realizado. 1er lugar: {$first->name} con el número {$raffle->formatNumber($first->number)}.");
    }

    /* =========================================================
     |  Helpers
     * ========================================================= */

    /**
     * Pone el correo en la cola (sale con $delay segundos de espera).
     * Requiere el worker de colas corriendo: php artisan queue:work
     */
    private function sendInvitation(Raffle $raffle, RaffleParticipant $participant, int $delay = 0): bool
    {
        try {
            Mail::to($participant->email, $participant->name)
                ->later(now()->addSeconds($delay), new RaffleInvitation($raffle, $participant));

            return true;
        } catch (Throwable $e) {
            Log::error('Rifa: no se pudo enviar invitación', [
                'raffle' => $raffle->id,
                'email'  => $participant->email,
                'error'  => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function rafflePayload(Raffle $raffle): array
    {
        return [
            'id'             => $raffle->id,
            'name'           => $raffle->name,
            'description'    => $raffle->description,
            'prize'          => $raffle->prize,
            'prizes'         => $raffle->prizeList(),
            'digits'         => $raffle->digits,
            'draw_date'      => $raffle->draw_date?->format('Y-m-d'),
            'draw_date_text' => $raffle->draw_date?->format('d/m/Y'),
            'status'         => $raffle->status,
            'lottery_draw'   => $raffle->lottery_draw,
            'lottery_number' => $raffle->lottery_number,
            'target_number'  => $raffle->formatNumber($raffle->target_number),
            'drawn_at'       => $raffle->drawn_at?->format('d/m/Y H:i'),
            'winners'        => $raffle->winners->map(fn (RaffleParticipant $w) => [
                'id'       => $w->id,
                'place'    => $w->place,
                'prize'    => $raffle->prizeList()[$w->place - 1] ?? null,
                'name'     => $w->name,
                'email'    => $w->email,
                'number'   => $raffle->formatNumber($w->number),
                'distance' => abs($w->number - (int) $raffle->target_number),
            ])->values(),
            'created_at'     => $raffle->created_at?->format('d/m/Y'),
        ];
    }

    private function validateRaffle(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'prize'       => ['nullable', 'string', 'max:1000'],
            'digits'      => ['required', 'integer', 'in:3,4,5'],
            'draw_date'   => ['nullable', 'date'],
        ], [
            'name.required' => 'Escribe el nombre de la rifa.',
            'digits.in'     => 'Las cifras deben ser 3, 4 o 5.',
        ]);
    }
}
