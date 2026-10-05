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
        'capacity',
        'state',
    ];

    // Constantes pour éviter les fautes de frappe
    public const STATE_OUVERT = 'Ouvert';
    public const STATE_PLEIN = 'Plein';
    public const STATE_MAINTENANCE = 'En maintenance';
    public const STATE_FERME = 'Fermé';

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
}