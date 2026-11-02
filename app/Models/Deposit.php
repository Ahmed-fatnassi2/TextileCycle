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
        'checked_in_at' => 'datetime',
        'weight_kg' => 'decimal:2',
    ];

    // Constantes statuts
    public const STATUS_EN_ATTENTE = 'En attente';
    public const STATUS_DEPOSE = 'Déposé';
    public const STATUS_TRIE = 'Trié';
    public const STATUS_REJETE = 'Rejeté';

    // Constantes états
    public const STATE_NEUF = 'Neuf';
    public const STATE_BON = 'Bon état';
    public const STATE_USE = 'Usé';
    public const STATE_DECHIRE = 'Déchiré';

    // Caractères du code QR (sans 0/O ni 1/I pour éviter les confusions à la saisie)
    private const CODE_ALPHABET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    protected static function booted(): void
    {
        static::creating(function (Deposit $deposit) {
            $deposit->code ??= static::generateCode();
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'TC-';
            for ($i = 0; $i < 8; $i++) {
                $code .= self::CODE_ALPHABET[random_int(0, strlen(self::CODE_ALPHABET) - 1)];
            }
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public static function statuses(): array
    {
        return [self::STATUS_EN_ATTENTE, self::STATUS_DEPOSE, self::STATUS_TRIE, self::STATUS_REJETE];
    }

    public static function states(): array
    {
        return [self::STATE_NEUF, self::STATE_BON, self::STATE_USE, self::STATE_DECHIRE];
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_EN_ATTENTE;
    }

    // Classes Tailwind du badge de statut
    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            self::STATUS_EN_ATTENTE => 'bg-amber-100 text-amber-800',
            self::STATUS_TRIE => 'bg-green-100 text-green-700',
            self::STATUS_REJETE => 'bg-red-100 text-red-700',
            default => 'bg-blue-100 text-blue-700',
        };
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

    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }
}
