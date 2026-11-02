<?php

namespace App\Services;

use App\Models\Deposit;
use App\Models\DepositPoint;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Classe les points de collecte pour un citoyen. Utilisé par le chatbot comme "outil" :
 * l'IA ne calcule rien elle-même, elle explique le résultat de ce classement.
 *
 * Score (plus petit = meilleur) = distance en km + taux de remplissage × FILL_PENALTY_KM.
 * Exemple : un point rempli à 100 % "coûte" autant qu'un détour de 10 km.
 */
class DepositPointAdvisor
{
    // Fenêtre utilisée pour estimer le remplissage (pas encore de suivi des vidages)
    public const LOAD_WINDOW_DAYS = 30;

    public const FILL_PENALTY_KM = 10;

    public const NEARLY_FULL_PERCENT = 80;

    // Au-delà, le point n'est plus proposé
    public const MAX_FILL_PERCENT = 95;

    // Coordonnées approximatives des principales villes tunisiennes
    private const CITIES = [
        'tunis' => [36.8065, 10.1815], 'ariana' => [36.8625, 10.1956], 'la marsa' => [36.8782, 10.3247],
        'ben arous' => [36.7533, 10.2283], 'manouba' => [36.8081, 10.0972], 'la goulette' => [36.8181, 10.3050],
        'carthage' => [36.8528, 10.3233], 'nabeul' => [36.4561, 10.7376], 'hammamet' => [36.4000, 10.6167],
        'bizerte' => [37.2744, 9.8739], 'beja' => [36.7256, 9.1817], 'jendouba' => [36.5011, 8.7803],
        'le kef' => [36.1742, 8.7049], 'kef' => [36.1742, 8.7049], 'siliana' => [36.0849, 9.3708],
        'zaghouan' => [36.4029, 10.1429], 'sousse' => [35.8256, 10.6360], 'monastir' => [35.7643, 10.8113],
        'mahdia' => [35.5047, 11.0622], 'kairouan' => [35.6781, 10.0963], 'kasserine' => [35.1676, 8.8365],
        'sidi bouzid' => [35.0382, 9.4849], 'sfax' => [34.7400, 10.7600], 'gafsa' => [34.4250, 8.7842],
        'tozeur' => [33.9197, 8.1335], 'kebili' => [33.7044, 8.9690], 'gabes' => [33.8815, 10.0982],
        'medenine' => [33.3549, 10.5055], 'djerba' => [33.8076, 10.8451], 'tataouine' => [32.9297, 10.4518],
    ];

    /**
     * @return array{origin: ?array, recommendations: array, unavailable: array, error?: string, known_cities?: array}
     */
    public function recommend(?float $latitude = null, ?float $longitude = null, ?string $city = null, ?float $weightKg = null, int $limit = 3): array
    {
        $origin = $this->resolveOrigin($latitude, $longitude, $city);

        if ($city && ! $origin) {
            return [
                'origin' => null,
                'recommendations' => [],
                'unavailable' => [],
                'error' => 'unknown_city',
                'known_cities' => DepositPoint::query()->distinct()->orderBy('city')->pluck('city')->all(),
            ];
        }

        $points = $this->pointsWithLoad();

        $candidates = $points->filter(function (array $p) use ($weightKg) {
            if ($p['state'] !== DepositPoint::STATE_OUVERT || $p['fill_percent'] >= self::MAX_FILL_PERCENT) {
                return false;
            }

            return $weightKg === null || $p['remaining_kg'] >= $weightKg;
        });

        $withScore = $candidates->map(function (array $p) use ($origin) {
            $p['distance_km'] = $origin ? $this->distanceFrom($origin, $p) : null;
            $fillPenalty = ($p['fill_percent'] / 100) * self::FILL_PENALTY_KM;

            // Sans position, on ne peut classer que par remplissage. Points sans GPS relégués à la fin.
            $p['score'] = match (true) {
                $origin === null => $fillPenalty,
                $p['distance_km'] === null => PHP_FLOAT_MAX,
                default => $p['distance_km'] + $fillPenalty,
            };

            return $p;
        });

        $recommendations = $withScore->sortBy('score')->take($limit)->values()
            ->map(fn (array $p) => $this->present($p))
            ->all();

        // Points proches mais qui ne peuvent pas recevoir le dépôt : utile pour que l'IA explique pourquoi
        $unavailable = $points->reject(fn ($p) => $candidates->has($p['id']))
            ->map(function (array $p) use ($origin, $weightKg) {
                $p['distance_km'] = $origin ? $this->distanceFrom($origin, $p) : null;
                $p['reason'] = match (true) {
                    $p['state'] !== DepositPoint::STATE_OUVERT => "État : {$p['state']}",
                    $p['fill_percent'] >= self::MAX_FILL_PERCENT => 'Presque plein',
                    default => "Pas assez de place pour {$weightKg} kg",
                };

                return $p;
            })
            ->sortBy(fn ($p) => $p['distance_km'] ?? PHP_FLOAT_MAX)
            ->take(3)->values()
            ->map(fn (array $p) => [
                'name' => $p['name'],
                'city' => $p['city'],
                'distance_km' => $p['distance_km'],
                'reason' => $p['reason'],
            ])
            ->all();

        return [
            'origin' => $origin,
            'recommendations' => $recommendations,
            'unavailable' => $unavailable,
        ];
    }

    private function resolveOrigin(?float $latitude, ?float $longitude, ?string $city): ?array
    {
        // Une ville explicitement citée l'emporte sur la position GPS
        if ($city) {
            $key = $this->normalize($city);

            if (isset(self::CITIES[$key])) {
                return ['label' => Str::title($city), 'latitude' => self::CITIES[$key][0], 'longitude' => self::CITIES[$key][1], 'source' => 'city'];
            }

            // Ville inconnue de la table : centre des points enregistrés dans cette ville
            $inCity = DepositPoint::whereNotNull('latitude')->get()
                ->filter(fn ($p) => $this->normalize($p->city) === $key);

            if ($inCity->isNotEmpty()) {
                return ['label' => $inCity->first()->city, 'latitude' => $inCity->avg('latitude'), 'longitude' => $inCity->avg('longitude'), 'source' => 'city'];
            }

            return null;
        }

        if ($latitude !== null && $longitude !== null) {
            return ['label' => 'Position de l’utilisateur', 'latitude' => $latitude, 'longitude' => $longitude, 'source' => 'gps'];
        }

        return null;
    }

    /** @return Collection<int, array> indexées par id */
    private function pointsWithLoad(): Collection
    {
        $since = now()->subDays(self::LOAD_WINDOW_DAYS)->toDateString();

        return DepositPoint::query()
            ->where('state', '!=', DepositPoint::STATE_FERME)
            ->withSum(['deposits as load_kg' => fn ($q) => $q
                ->whereIn('status', [Deposit::STATUS_EN_ATTENTE, Deposit::STATUS_DEPOSE])
                ->whereDate('deposit_date', '>=', $since)], 'weight_kg')
            ->withCount(['deposits as pending_count' => fn ($q) => $q->where('status', Deposit::STATUS_EN_ATTENTE)])
            ->get()
            ->mapWithKeys(function (DepositPoint $point) {
                $load = (float) ($point->load_kg ?? 0);
                $capacity = max(1, (int) $point->capacity);

                return [$point->id => [
                    'id' => $point->id,
                    'name' => $point->name,
                    'address' => $point->address,
                    'city' => $point->city,
                    'state' => $point->state,
                    'latitude' => $point->latitude,
                    'longitude' => $point->longitude,
                    'capacity_kg' => $capacity,
                    'load_kg' => round($load, 1),
                    'remaining_kg' => round(max(0, $capacity - $load), 1),
                    'fill_percent' => (int) min(100, round($load / $capacity * 100)),
                    'pending_count' => (int) $point->pending_count,
                ]];
            });
    }

    private function distanceFrom(array $origin, array $p): ?float
    {
        if ($p['latitude'] === null || $p['longitude'] === null) {
            return null;
        }

        return round(DepositPoint::haversineKm($p['latitude'], $p['longitude'], $origin['latitude'], $origin['longitude']), 1);
    }

    private function present(array $p): array
    {
        return [
            'id' => $p['id'],
            'name' => $p['name'],
            'address' => $p['address'],
            'city' => $p['city'],
            'distance_km' => $p['distance_km'],
            'fill_percent' => $p['fill_percent'],
            'remaining_kg' => $p['remaining_kg'],
            'nearly_full' => $p['fill_percent'] >= self::NEARLY_FULL_PERCENT,
            'pending_bookings' => $p['pending_count'],
            'deposit_url' => route('deposits.create', ['point' => $p['id']]),
            'directions_url' => $p['latitude'] !== null
                ? "https://www.openstreetmap.org/directions?to={$p['latitude']}%2C{$p['longitude']}"
                : null,
        ];
    }

    private function normalize(string $value): string
    {
        return Str::of($value)->ascii()->lower()->replace(['-', '_'], ' ')->squish()->toString();
    }
}
