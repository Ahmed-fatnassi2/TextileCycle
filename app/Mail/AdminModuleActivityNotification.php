<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminModuleActivityNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $activity,
        public string $name,
        public string $status,
        public string $detailsUrl,
        public ?string $reference = null,
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->activity.' - TextileCycle');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-module-activity',
            with: [
                'activity' => $this->activity,
                'name' => $this->name,
                'status' => $this->status,
                'detailsUrl' => $this->detailsUrl,
                'reference' => $this->reference,
            ],
        );
    }
}
