<?php

namespace App\Mail;

use App\Models\Association;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssociationRequestConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Association $association)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Demande d’association reçue - TextileCycle');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.association-request',
            with: [
                'association' => $this->association,
                'statusUrl' => route('association.status'),
            ],
        );
    }
}
