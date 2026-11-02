<?php

namespace App\Services;

use App\Models\Donation;
use App\Notifications\DonationRescheduled;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Règles de planification des livraisons de dons : capacité par jour et notification
 * de l'association quand sa date change. Utilisé par le calendrier, le formulaire admin
 * et le formulaire des associations, pour que la règle soit la même partout.
 */
class DonationScheduler
{
    public function dailyDeliveryLimit(): int
    {
        return config('donations.daily_deliveries');
    }

    public function dailyItemsLimit(): int
    {
        return config('donations.daily_items');
    }

    /**
     * Charge d'une journée, en ignorant éventuellement un don (celui qu'on déplace).
     *
     * @return array{deliveries: int, items: int}
     */
    public function loadFor(CarbonInterface|string $date, ?int $ignoreDonationId = null): array
    {
        $query = Donation::whereDate('donation_date', Carbon::parse($date)->toDateString())
            ->when($ignoreDonationId, fn ($q) => $q->whereKeyNot($ignoreDonationId));

        return [
            'deliveries' => (clone $query)->count(),
            'items' => (int) (clone $query)->sum('quantity_items'),
        ];
    }

    /**
     * Raison pour laquelle on ne peut pas planifier ces pièces ce jour-là, ou null si c'est possible.
     */
    public function conflictFor(CarbonInterface|string $date, int $quantity, ?int $ignoreDonationId = null): ?string
    {
        $date = Carbon::parse($date);
        $load = $this->loadFor($date, $ignoreDonationId);
        $day = $date->locale('fr')->isoFormat('dddd D MMMM');

        if ($load['deliveries'] + 1 > $this->dailyDeliveryLimit()) {
            return "Le {$day} est complet : {$load['deliveries']}/{$this->dailyDeliveryLimit()} livraisons déjà prévues.";
        }

        if ($load['items'] + $quantity > $this->dailyItemsLimit()) {
            $left = max(0, $this->dailyItemsLimit() - $load['items']);

            return "Le {$day}, il ne reste que {$left} pièces de capacité (demande : {$quantity}).";
        }

        return null;
    }

    /**
     * Déplace un don et prévient l'association. Retourne false si l'email n'a pas pu partir.
     */
    public function reschedule(Donation $donation, CarbonInterface|string $newDate, User $actor): bool
    {
        $oldDate = $donation->donation_date->copy();
        $donation->update(['donation_date' => Carbon::parse($newDate)->toDateString()]);

        return $this->afterDateChange($donation, $oldDate, $actor);
    }

    /**
     * Trace le changement et envoie l'email. À appeler après toute modification de date par l'admin.
     */
    public function afterDateChange(Donation $donation, CarbonInterface $oldDate, User $actor): bool
    {
        if ($oldDate->isSameDay($donation->donation_date)) {
            return true;
        }

        $owner = $donation->association->user;

        User::logActivity(
            $owner,
            'Livraison de don replanifiée',
            "Don de {$donation->quantity_items} pièces déplacé du {$oldDate->format('d/m/Y')} au {$donation->donation_date->format('d/m/Y')}.",
            $actor,
            ['donation_id' => $donation->id],
        );

        try {
            $owner->notify(new DonationRescheduled($donation, $oldDate));

            return true;
        } catch (\Throwable $e) {
            // Un problème SMTP ne doit pas annuler le changement de date
            Log::warning('Email de replanification non envoyé', ['donation_id' => $donation->id, 'error' => $e->getMessage()]);

            return false;
        }
    }
}
