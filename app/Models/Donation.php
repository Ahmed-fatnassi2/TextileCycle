<?php

namespace App\Models;

use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
>>>>>>> origin/Donation/Association-"aziz"

class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'En attente';

    public const STATUS_VALIDATED = 'Validé';

    public const STATUS_DELIVERED = 'Livré';

<<<<<<< HEAD
    protected $fillable = [
        'association_id',
=======
    public const CATEGORY_CLOTHING = 'Vêtements';

    public const CATEGORY_SHOES = 'Chaussures';

    public const CATEGORY_HOME_LINEN = 'Linge de maison';

    public const CATEGORY_ACCESSORIES = 'Accessoires';

    public const CATEGORY_OTHER = 'Autre';

    protected $fillable = [
        'association_id',
        'category',
>>>>>>> origin/Donation/Association-"aziz"
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

<<<<<<< HEAD
=======
    public static function categories(): array
    {
        return [
            self::CATEGORY_CLOTHING,
            self::CATEGORY_SHOES,
            self::CATEGORY_HOME_LINEN,
            self::CATEGORY_ACCESSORIES,
            self::CATEGORY_OTHER,
        ];
    }

>>>>>>> origin/Donation/Association-"aziz"
    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }
<<<<<<< HEAD
}
=======

    public function statusHistory(): HasMany
    {
        return $this->hasMany(DonationStatusHistory::class);
    }

    public function trackingReference(): string
    {
        return 'TC-'.strtoupper(str_replace('-', '', $this->tracking_token));
    }

    protected static function booted(): void
    {
        static::creating(function (Donation $donation) {
            $donation->tracking_token ??= (string) Str::uuid();
            $donation->category ??= self::CATEGORY_CLOTHING;
            $donation->status ??= self::STATUS_PENDING;
        });
    }
}
>>>>>>> origin/Donation/Association-"aziz"
