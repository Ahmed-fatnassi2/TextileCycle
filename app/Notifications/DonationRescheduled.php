<?php

namespace App\Notifications;

use App\Models\Donation;
use Carbon\CarbonInterface;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Email envoyé à l'association quand l'équipe change la date de livraison de son don.
class DonationRescheduled extends Notification
{
    public function __construct(public Donation $donation, public CarbonInterface $oldDate)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $newDate = $this->donation->donation_date->locale('fr')->isoFormat('dddd D MMMM YYYY');
        $oldDate = $this->oldDate->locale('fr')->isoFormat('dddd D MMMM YYYY');

        return (new MailMessage)
            ->subject('Nouvelle date de livraison pour votre don')
            ->greeting('Bonjour '.$this->donation->association->contact_person.',')
            ->line("La livraison de votre demande {$this->donation->reference} ({$this->donation->quantity_items} pièces, {$this->donation->category}) a été replanifiée par l’équipe TexTileCycle.")
            ->line('Lieu de livraison : '.$this->donation->deliveryLocation())
            ->line("Ancienne date : {$oldDate}")
            ->line("**Nouvelle date : {$newDate}**")
            ->action('Voir ma demande', route('association.donations.show', $this->donation))
            ->line('Si cette date ne vous convient pas, contactez-nous en répondant à cet email.')
            ->salutation('L’équipe TexTileCycle');
    }
}
