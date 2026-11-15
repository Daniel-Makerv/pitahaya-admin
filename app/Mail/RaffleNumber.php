<?php

namespace App\Mail;

use App\Models\Raffle;
use App\Models\RaffleParticipant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Correo de confirmación con el número de rifa (se manda al terminar el registro).
 * Plantilla: resources/views/emails/raffle-number.blade.php
 */
class RaffleNumber extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Raffle $raffle,
        public RaffleParticipant $participant,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Su número para la rifa PITAMEX: '.$this->participant->formattedNumber(),
        );
    }

    public function content(): Content
    {
        $data = $this->participant->data ?? [];

        return new Content(
            view: 'emails.raffle-number',
            with: [
                'raffle'      => $this->raffle,
                'participant' => $this->participant,
                'number'      => $this->participant->formattedNumber(),
                'firstName'   => trim(explode(' ', trim($this->participant->name))[0] ?? ''),
                'place'       => collect([$data['municipio'] ?? null, $data['estado'] ?? null])->filter()->implode(', '),
                'prizes'      => $this->raffle->prizeList(),
                'drawDate'    => $this->raffle->draw_date?->locale('es')->translatedFormat('d \d\e F \d\e Y'),
                'logoUrl'     => config('raffles.logo_url'),
                'whatsappUrl' => config('raffles.whatsapp_url'),
            ],
        );
    }
}
