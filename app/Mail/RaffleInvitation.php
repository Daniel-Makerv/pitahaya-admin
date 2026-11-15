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
 * Correo de invitación (carta de PITAMEX) con la liga personal al registro + rifa.
 * Plantilla: resources/views/emails/raffle-invitation.blade.php
 */
class RaffleInvitation extends Mailable implements ShouldQueue
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
            subject: 'Gracias por visitarnos en la Expo Agroalimentaria: su información y su boleto para la rifa',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.raffle-invitation',
            with: [
                'raffle'         => $this->raffle,
                'participant'    => $this->participant,
                'prizes'         => $this->raffle->prizeList(),
                'registerUrl'    => $this->participant->invitationUrl(),
                'logoUrl'        => config('raffles.logo_url'),
                'temarioUrl'     => config('raffles.temario_url'),
                'privacyUrl'     => config('raffles.privacy_url'),
                'unsubscribeUrl' => config('raffles.unsubscribe_url'),
            ],
        );
    }
}
