<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'deposit_point_id',
        'weight_kg',
        'status',
        'state',
        'deposit_date',
    ];

    protected $casts = [
        'deposit_date' => 'date',
        'weight_kg' => 'decimal:2',
    ];

    // Constantes statuts
    public const STATUS_DEPOSE = 'Déposé';
    public const STATUS_TRIE = 'Trié';
    public const STATUS_REJETE = 'Rejeté';

    // Constantes états
    public const STATE_NEUF = 'Neuf';
    public const STATE_BON = 'Bon état';
    public const STATE_USE = 'Usé';
    public const STATE_DECHIRE = 'Déchiré';

    public static function statuses(): array
    {
        return [self::STATUS_DEPOSE, self::STATUS_TRIE, self::STATUS_REJETE];
    }

    public static function states(): array
    {
        return [self::STATE_NEUF, self::STATE_BON, self::STATE_USE, self::STATE_DECHIRE];
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function depositPoint()
    {
        return $this->belongsTo(DepositPoint::class);
    }
}