<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class RaffleParticipant extends Model
{
    protected $fillable = [
        'raffle_id',
        'form_id',
        'name',
        'email',
        'token',
        'status',
        'number',
        'data',
        'step',
        'score',
        'priority',
        'place',
        'invited_at',
        'confirmed_at',
    ];

    protected $casts = [
        'data'         => 'array',
        'number'       => 'integer',
        'step'         => 'integer',
        'score'        => 'integer',
        'place'        => 'integer',
        'invited_at'   => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (RaffleParticipant $participant) {
            $participant->token ??= Str::random(48);
        });
    }

    public function raffle(): BelongsTo
    {
        return $this->belongsTo(Raffle::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    /** Enlace público de la invitación (va en el botón del correo) */
    public function invitationUrl(): string
    {
        return route('raffles.invitation.show', $this->token);
    }

    public function formattedNumber(): ?string
    {
        return $this->raffle?->formatNumber($this->number);
    }
}
