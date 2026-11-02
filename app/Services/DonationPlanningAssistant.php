<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Assistant de planning IA pour les livraisons de dons.
 *
 * 1. Laravel rassemble la situation réelle : livraisons en retard ou à venir, charge de chaque jour,
 *    capacité, besoins décrits par les associations.
 * 2. L'IA (OpenAI, sortie JSON structurée) propose des déplacements avec une justification.
 * 3. Laravel revérifie chaque proposition (jour autorisé, capacité, statut) et écarte celles
 *    qui ne respectent pas les règles. Rien n'est enregistré : l'admin accepte ou refuse chaque
 *    proposition, et l'acceptation passe par le même chemin que le glisser-déposer (email compris).
 */
class DonationPlanningAssistant
{
    public function __construct(private OpenAiClient $openAi, private DonationScheduler $scheduler)
    {
    }

    /**
     * @return array{summary: string, proposals: array, discarded: int, stats: array}
     */
    public function propose(): array
    {
        $today = today();
        $lastDay = $today->copy()->addDays(config('donations.planning_horizon_days') - 1);

        // Livraisons non livrées en retard ou dans l'horizon
        $candidates = Donation::with('association')
            ->whereIn('status', [Donation::STATUS_PENDING, Donation::STATUS_VALIDATED])
            ->whereDate('donation_date', '<=', $lastDay->toDateString())
            ->orderBy('donation_date')
            ->get()
            ->keyBy('id');

        $stats = [
            'candidates' => $candidates->count(),
            'overdue' => $candidates->filter(fn ($d) => $d->donation_date->lt($today))->count(),
        ];

        if ($candidates->isEmpty()) {
            return [
                'summary' => 'Aucune livraison en attente ou validée sur les '.config('donations.planning_horizon_days').' prochains jours : rien à optimiser.',
                'proposals' => [],
                'discarded' => 0,
                'stats' => $stats,
            ];
        }

        $loads = $this->dayLoads($today, $lastDay);

        $answer = $this->openAi->json(
            [
                ['role' => 'system', 'content' => $this->instructions()],
                ['role' => 'user', 'content' => json_encode(
                    $this->situation($today, $lastDay, $candidates, $loads),
                    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
                )],
            ],
            'planning_proposals',
            $this->schema(),
        );

        [$proposals, $discarded] = $this->validate($answer['proposals'] ?? [], $candidates, $loads, $today, $lastDay);

        return [
            'summary' => trim((string) ($answer['summary'] ?? '')),
            'proposals' => $proposals,
            'discarded' => $discarded,
            'stats' => $stats,
        ];
    }

    /**
     * Garde-fou : on rejoue les propositions dans l'ordre sur une copie de la charge des jours.
     *
     * @return array{0: array, 1: int} propositions valides, nombre d'écartées
     */
    private function validate(array $raw, Collection $candidates, array $loads, Carbon $today, Carbon $lastDay): array
    {
        $valid = [];
        $seen = [];
        $max = config('donations.planning_max_proposals');

        foreach ($raw as $proposal) {
            $donation = $candidates->get((int) ($proposal['donation_id'] ?? 0));
            $date = $proposal['new_date'] ?? '';

            if (! $donation || isset($seen[$donation->id]) || count($valid) >= $max) {
                continue;
            }
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || ! strtotime($date)) {
                continue;
            }

            $newDate = Carbon::parse($date);
            $oldKey = $donation->donation_date->toDateString();

            if ($newDate->lt($today) || $newDate->gt($lastDay) || $newDate->toDateString() === $oldKey) {
                continue;
            }

            $target = $loads[$newDate->toDateString()];
            if ($target['deliveries'] + 1 > $this->scheduler->dailyDeliveryLimit()
                || $target['items'] + $donation->quantity_items > $this->scheduler->dailyItemsLimit()) {
                continue;
            }

            // Applique le déplacement à la simulation pour les propositions suivantes
            if (isset($loads[$oldKey])) {
                $loads[$oldKey]['deliveries']--;
                $loads[$oldKey]['items'] -= $donation->quantity_items;
            }
            $loads[$newDate->toDateString()]['deliveries']++;
            $loads[$newDate->toDateString()]['items'] += $donation->quantity_items;
            $seen[$donation->id] = true;

            $valid[] = [
                'donation_id' => $donation->id,
                'association' => $donation->association->name,
                'quantity' => $donation->quantity_items,
                'status' => $donation->status,
                'overdue' => $donation->donation_date->lt($today),
                'from' => $oldKey,
                'from_label' => $donation->donation_date->locale('fr')->isoFormat('ddd D MMM'),
                'to' => $newDate->toDateString(),
                'to_label' => $newDate->locale('fr')->isoFormat('ddd D MMM'),
                'reason' => Str::limit(trim((string) ($proposal['reason'] ?? '')), 300),
                'reschedule_url' => route('admin.donations.reschedule', $donation),
            ];
        }

        return [$valid, count($raw) - count($valid)];
    }

    /** @return array<string, array{deliveries: int, items: int}> */
    private function dayLoads(Carbon $from, Carbon $to): array
    {
        $loads = [];
        for ($day = $from->copy(); $day->lte($to); $day->addDay()) {
            $loads[$day->toDateString()] = ['deliveries' => 0, 'items' => 0];
        }

        Donation::whereDate('donation_date', '>=', $from->toDateString())
            ->whereDate('donation_date', '<=', $to->toDateString())
            ->get(['donation_date', 'quantity_items'])
            ->each(function (Donation $d) use (&$loads) {
                $key = $d->donation_date->toDateString();
                $loads[$key]['deliveries']++;
                $loads[$key]['items'] += $d->quantity_items;
            });

        return $loads;
    }

    private function situation(Carbon $today, Carbon $lastDay, Collection $candidates, array $loads): array
    {
        return [
            'today' => $today->toDateString(),
            'planning_window' => ['first_day' => $today->toDateString(), 'last_day' => $lastDay->toDateString()],
            'daily_capacity' => [
                'max_deliveries' => $this->scheduler->dailyDeliveryLimit(),
                'max_items' => $this->scheduler->dailyItemsLimit(),
            ],
            // Place restante calculée ici : le modèle n'a pas à faire les soustractions
            'days' => collect($loads)->map(fn ($load, $date) => [
                'date' => $date,
                'weekday' => Carbon::parse($date)->locale('fr')->dayName,
                'planned_deliveries' => $load['deliveries'],
                'planned_items' => $load['items'],
                'remaining_deliveries' => max(0, $this->scheduler->dailyDeliveryLimit() - $load['deliveries']),
                'remaining_items' => max(0, $this->scheduler->dailyItemsLimit() - $load['items']),
            ])->values()->all(),
            'donations' => $candidates->map(fn (Donation $d) => [
                'donation_id' => $d->id,
                'association' => $d->association->name,
                'association_needs' => Str::limit($d->association->needs_description, 300),
                'request_description' => Str::limit((string) $d->description, 300),
                'category' => $d->category,
                'delivery_city' => $d->delivery_city,
                'quantity_items' => $d->quantity_items,
                'status' => $d->status,
                'current_date' => $d->donation_date->toDateString(),
                'overdue' => $d->donation_date->lt($today),
                'requested_on' => $d->created_at?->toDateString(),
            ])->values()->all(),
        ];
    }

    private function instructions(): string
    {
        $max = config('donations.planning_max_proposals');

        return <<<PROMPT
Tu es l'assistant de planification logistique de TexTileCycle (Tunisie). Tu reçois en JSON les livraisons de dons
à des associations et la charge de chaque jour. Propose des déplacements pour améliorer le planning.

Priorités, dans cet ordre :
1. Les livraisons en retard (overdue = true) doivent être replanifiées au plus tôt dans la fenêtre.
2. Ne dépasse jamais la capacité d'un jour (max_deliveries et max_items), en tenant compte de tes propres déplacements.
3. Rapproche les besoins urgents (familles, enfants, froid de l'hiver, situation d'urgence décrite dans association_needs ou request_description).
4. Équilibre la charge entre les jours et évite de surcharger un seul jour.
5. Les livraisons "Validé" ont déjà été confirmées à l'association : ne les déplace que si c'est nécessaire.

Contraintes :
- Utilise uniquement des donation_id présents dans les données et des dates de la fenêtre (planning_window), au format AAAA-MM-JJ.
- Au plus {$max} déplacements. Ne propose rien d'inutile : si le planning est déjà bon, renvoie une liste vide.
- reason : une phrase courte en français, concrète et exacte par rapport aux données
  (ex. « En retard depuis le 20/10 ; le 03/11 a encore 2 livraisons et 180 pièces de place »). N'écris pas qu'un jour est libre s'il a déjà des livraisons.
- summary : 1 à 2 phrases en français. Ne décris que les déplacements présents dans proposals, rien d'autre.
PROMPT;
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'summary' => ['type' => 'string'],
                'proposals' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'donation_id' => ['type' => 'integer'],
                            'new_date' => ['type' => 'string', 'description' => 'AAAA-MM-JJ'],
                            'reason' => ['type' => 'string'],
                        ],
                        'required' => ['donation_id', 'new_date', 'reason'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
            'required' => ['summary', 'proposals'],
            'additionalProperties' => false,
        ];
    }
}
