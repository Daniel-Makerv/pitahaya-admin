<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class Raffle extends Model
{
    protected $fillable = [
        'public_code',
        'name',
        'description',
        'prize',
        'digits',
        'draw_date',
        'status',
        'lottery_draw',
        'lottery_number',
        'target_number',
        'drawn_at',
    ];

    protected $casts = [
        'digits'        => 'integer',
        'draw_date'     => 'date',
        'drawn_at'      => 'datetime',
        'target_number' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Raffle $raffle) {
            $raffle->public_code ??= self::newPublicCode();
        });
    }

    /** Código del link público (8 caracteres, sin 0/O ni 1/I) */
    public static function newPublicCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 8; $i++) {
                $code .= 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'[random_int(0, 31)];
            }
        } while (self::where('public_code', $code)->exists());

        return $code;
    }

    /** Link para registrarse sin correo de invitación */
    public function publicUrl(): string
    {
        return route('raffles.open.show', $this->public_code);
    }

    /**
     * Otro participante de esta rifa ya confirmado con el mismo teléfono (un boleto por persona).
     */
    public function confirmedWithPhone(string $phone, ?int $exceptParticipantId = null): ?RaffleParticipant
    {
        $digits = substr(preg_replace('/\D/', '', $phone), -10);

        if (strlen($digits) < 10) {
            return null;
        }

        return $this->participants()
            ->where('status', 'confirmed')
            ->when($exceptParticipantId, fn ($q) => $q->where('id', '!=', $exceptParticipantId))
            ->get()
            ->first(fn (RaffleParticipant $p) => substr(preg_replace('/\D/', '', (string) ($p->data['telefono'] ?? '')), -10) === $digits);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(RaffleParticipant::class);
    }

    public function confirmedParticipants(): HasMany
    {
        return $this->participants()->where('status', 'confirmed');
    }

    /** Ganadores en orden: 1er lugar, 2º, 3º... */
    public function winners(): HasMany
    {
        return $this->participants()->whereNotNull('place')->orderBy('place');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /** Cuántos números caben: 3 cifras = 1000 (000-999) */
    public function capacity(): int
    {
        return 10 ** $this->digits;
    }

    /** 7 -> "007" */
    public function formatNumber(?int $number): ?string
    {
        return $number === null ? null : str_pad((string) $number, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Confirma la participación y le asigna un número libre al azar.
     * Bloquea la rifa para que dos confirmaciones al mismo tiempo no reciban el mismo número.
     */
    public function confirmParticipant(RaffleParticipant $participant, array $data): RaffleParticipant
    {
        return DB::transaction(function () use ($participant, $data) {
            $raffle = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if (! $raffle->isOpen()) {
                throw new RuntimeException('Esta rifa ya fue sorteada.');
            }

            $participant = RaffleParticipant::whereKey($participant->id)->lockForUpdate()->firstOrFail();

            // Ya había confirmado: solo actualiza sus datos, conserva su número
            if ($participant->status === 'confirmed') {
                $participant->update(['data' => $data]);

                return $participant;
            }

            $next = $raffle->randomFreeNumber();

            if ($next === null) {
                throw new RuntimeException('La rifa ya no tiene números disponibles.');
            }

            $participant->update([
                'status'       => 'confirmed',
                'number'       => $next,
                'data'         => $data,
                'confirmed_at' => now(),
            ]);

            return $participant;
        });
    }

    /**
     * Un número que nadie tenga en esta rifa, al azar. null si ya no hay.
     */
    public function randomFreeNumber(): ?int
    {
        $capacity = $this->capacity();
        $used = RaffleParticipant::where('raffle_id', $this->id)
            ->whereNotNull('number')
            ->pluck('number')
            ->flip();

        if ($used->count() >= $capacity) {
            return null;
        }

        // Con pocos números ocupados, adivinar al azar es lo más rápido
        if ($used->count() < $capacity * 0.7) {
            for ($i = 0; $i < 50; $i++) {
                $candidate = random_int(0, $capacity - 1);
                if (! $used->has($candidate)) {
                    return $candidate;
                }
            }
        }

        // Rifa casi llena: se elige entre los que quedan libres
        $free = array_values(array_diff(range(0, $capacity - 1), $used->keys()->all()));

        return $free[random_int(0, count($free) - 1)];
    }

    /** Premios: uno por línea */
    public function prizeList(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $this->prize))));
    }

    /**
     * Sorteo con la Lotería Nacional.
     * Se toman las últimas cifras del premio mayor (según "digits"; con 5 es el número completo).
     * Gana el número confirmado más cercano; 2º y 3º lugar, los siguientes más cercanos.
     * Si hay empate (uno arriba y uno abajo a la misma distancia), gana el número menor.
     * Hay tantos lugares como premios (mínimo 1).
     *
     * @return \Illuminate\Support\Collection<int, RaffleParticipant>  ganadores en orden
     */
    public function draw(string $lotteryNumber, ?string $lotteryDraw = null)
    {
        $digitsOnly = preg_replace('/\D/', '', $lotteryNumber);

        if (strlen($digitsOnly) < $this->digits) {
            throw new RuntimeException("El número de la Lotería debe tener al menos {$this->digits} cifras.");
        }

        $target = (int) substr($digitsOnly, -$this->digits);
        $places = max(1, count($this->prizeList()));

        return DB::transaction(function () use ($digitsOnly, $lotteryDraw, $target, $places) {
            $raffle = self::whereKey($this->id)->lockForUpdate()->firstOrFail();

            if (! $raffle->isOpen()) {
                throw new RuntimeException('Esta rifa ya fue sorteada.');
            }

            $numbers = RaffleParticipant::where('raffle_id', $raffle->id)
                ->where('status', 'confirmed')
                ->whereNotNull('number')
                ->pluck('number', 'id')
                ->all();

            if (! $numbers) {
                throw new RuntimeException('No hay participantes confirmados para sortear.');
            }

            $ranking = array_slice(self::rankByDistance($numbers, $target), 0, $places);

            foreach ($ranking as $index => $participantId) {
                RaffleParticipant::whereKey($participantId)->update(['place' => $index + 1]);
            }

            $raffle->update([
                'status'         => 'drawn',
                'lottery_draw'   => $lotteryDraw,
                'lottery_number' => $digitsOnly,
                'target_number'  => $target,
                'drawn_at'       => now(),
            ]);

            return RaffleParticipant::whereIn('id', $ranking)->orderBy('place')->get();
        });
    }

    /**
     * Ordena por cercanía al número de la Lotería; en empate, primero el número menor.
     *
     * @param  array<int, int>  $numbers  [participant_id => número]
     * @return array<int, int>  ids de participante en orden de lugar
     */
    public static function rankByDistance(array $numbers, int $target): array
    {
        $rows = [];
        foreach ($numbers as $id => $number) {
            $rows[] = ['id' => $id, 'number' => $number, 'distance' => abs($number - $target)];
        }

        usort($rows, fn ($a, $b) => [$a['distance'], $a['number']] <=> [$b['distance'], $b['number']]);

        return array_column($rows, 'id');
    }

    /**
     * @param  array<int, int>  $numbers  [participant_id => número]
     * @return int|null  id del participante ganador
     */
    public static function closestNumber(array $numbers, int $target): ?int
    {
        return self::rankByDistance($numbers, $target)[0] ?? null;
    }
}
