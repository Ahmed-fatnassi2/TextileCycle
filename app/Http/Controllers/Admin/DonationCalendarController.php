<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AssistantUnavailableException;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\DonationPlanningAssistant;
use App\Services\DonationScheduler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Calendrier des livraisons de dons (FullCalendar) avec replanification par glisser-déposer.
class DonationCalendarController extends Controller
{
    private const COLORS = [
        Donation::STATUS_PENDING => '#d97706',   // ambre
        Donation::STATUS_VALIDATED => '#0369a1', // bleu
        Donation::STATUS_DELIVERED => '#047857', // vert
    ];

    public function __construct(private DonationScheduler $scheduler)
    {
    }

    public function index(): View
    {
        return view('admin.donations.calendar', [
            'dailyDeliveries' => $this->scheduler->dailyDeliveryLimit(),
            'dailyItems' => $this->scheduler->dailyItemsLimit(),
            'colors' => self::COLORS,
        ]);
    }

    // Source d'événements : FullCalendar envoie ?start=...&end=... (période affichée)
    public function events(Request $request): JsonResponse
    {
        $range = $request->validate([
            'start' => ['required', 'date'],
            'end' => ['required', 'date', 'after:start'],
        ]);

        $donations = Donation::with('association')
            ->whereDate('donation_date', '>=', substr($range['start'], 0, 10))
            ->whereDate('donation_date', '<', substr($range['end'], 0, 10))
            ->orderBy('donation_date')
            ->get();

        return response()->json($donations->map(fn (Donation $d) => [
            'id' => (string) $d->id,
            'title' => "{$d->association->name} · {$d->quantity_items} pcs",
            'start' => $d->donation_date->toDateString(),
            'allDay' => true,
            'color' => self::COLORS[$d->status] ?? '#475569',
            // Un don livré ne bouge plus
            'editable' => $d->status !== Donation::STATUS_DELIVERED,
            'url' => route('admin.donations.show', $d),
            'extendedProps' => [
                'status' => $d->status,
                'quantity' => $d->quantity_items,
                'association' => $d->association->name,
                'reference' => $d->reference,
                'category' => $d->category,
                'city' => $d->delivery_city,
                'rescheduleUrl' => route('admin.donations.reschedule', $d),
            ],
        ]));
    }

    // Assistant de planning IA : renvoie des propositions, n'enregistre rien
    public function optimize(DonationPlanningAssistant $assistant): JsonResponse
    {
        try {
            return response()->json($assistant->propose());
        } catch (AssistantUnavailableException) {
            return response()->json([
                'message' => 'L’assistant de planning est momentanément indisponible. Réessayez dans un instant.',
            ], 503);
        }
    }

    // Appelé après un glisser-déposer. En cas de refus (422), le calendrier remet l'événement à sa place.
    public function reschedule(Request $request, Donation $donation): JsonResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
        ], [
            'date.after_or_equal' => 'Impossible de planifier une livraison dans le passé.',
        ]);

        if ($donation->status === Donation::STATUS_DELIVERED) {
            return response()->json(['message' => 'Ce don est déjà livré : sa date ne peut plus changer.'], 422);
        }

        if ($conflict = $this->scheduler->conflictFor($data['date'], $donation->quantity_items, $donation->id)) {
            return response()->json(['message' => $conflict], 422);
        }

        $donation->load('association.user');
        $emailSent = $this->scheduler->reschedule($donation, $data['date'], $request->user());

        $newDate = $donation->donation_date->locale('fr')->isoFormat('dddd D MMMM');

        return response()->json([
            'message' => $emailSent
                ? "Livraison déplacée au {$newDate}. L’association a été prévenue par email."
                : "Livraison déplacée au {$newDate}, mais l’email n’a pas pu être envoyé.",
            'emailSent' => $emailSent,
        ]);
    }
}
