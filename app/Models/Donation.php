<?php

namespace App\Models;

use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'En attente';

    public const STATUS_VALIDATED = 'Validé';

    public const STATUS_DELIVERED = 'Livré';

    protected $fillable = [
        'association_id',
        'quantity_items',
        'donation_date',
        'status',
    ];

    protected $casts = [
        'donation_date' => 'date',
    ];

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_VALIDATED, self::STATUS_DELIVERED];
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }
}