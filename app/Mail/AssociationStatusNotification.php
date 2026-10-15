<?php

namespace App\Mail;

use App\Models\Association;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AssociationStatusNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Association $association)
    {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Mise à jour de votre demande - TextileCycle');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.association-status',
            with: [
                'association' => $this->association,
                'statusUrl' => route('association.status'),
            ],
        );
    }
}
