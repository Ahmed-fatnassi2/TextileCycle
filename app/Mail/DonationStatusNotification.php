<?php

namespace App\Mail;

use App\Models\Donation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class DonationStatusNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Donation $donation,
        public string $status,
        public Carbon $changedAt,
        public string $trackingUrl,
    ) {
        $this->afterCommit();
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Mise à jour de votre don - TextileCycle');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.donation-status',
            with: [
                'reference' => $this->donation->trackingReference(),
                'category' => $this->donation->category,
                'quantity' => $this->donation->quantity_items,
                'plannedDate' => $this->donation->donation_date,
                'status' => $this->status,
                'changedAt' => $this->changedAt,
                'trackingUrl' => $this->trackingUrl,
            ],
        );
    }
}
