<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepositPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'city',
        'latitude',
        'longitude',
        'capacity',
        'state',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    // Constantes pour éviter les fautes de frappe
    public const STATE_OUVERT = 'Ouvert';
    public const STATE_PLEIN = 'Plein';
    public const STATE_MAINTENANCE = 'En maintenance';
    public const STATE_FERME = 'Fermé';

    // Rayon moyen de la Terre, utilisé pour le calcul de distance
    private const EARTH_RADIUS_KM = 6371;

    // Liste des états pour les formulaires
    public static function states(): array
    {
        return [
            self::STATE_OUVERT,
            self::STATE_PLEIN,
            self::STATE_MAINTENANCE,
            self::STATE_FERME,
        ];
    }

    // Relation : un point de collecte reçoit plusieurs dépôts
    public function deposits()
    {
        return $this->hasMany(Deposit::class);
    }

    // Accessor : nombre de dépôts reçus
    public function getDepositsCountAttribute(): int
    {
        return $this->deposits()->count();
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function acceptsDeposits(): bool
    {
        return $this->state === self::STATE_OUVERT;
    }

    // Distance à vol d'oiseau entre ce point et une position, en km
    public function distanceTo(float $latitude, float $longitude): ?float
    {
        if (! $this->hasCoordinates()) {
            return null;
        }

        return self::haversineKm($this->latitude, $this->longitude, $latitude, $longitude);
    }

    // Formule de Haversine
    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_KM * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
