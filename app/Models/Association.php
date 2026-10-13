<?php

namespace App\Models;

use Database\Factories\AssociationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Association extends Model
{
    /** @use HasFactory<AssociationFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'En attente';

    public const STATUS_APPROVED = 'Approuvé';

    public const STATUS_REJECTED = 'Refusé';

    protected $fillable = [
        'user_id',
        'name',
        'contact_person',
        'phone',
        'needs_description',
        'status',
    ];

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class);
    }
}