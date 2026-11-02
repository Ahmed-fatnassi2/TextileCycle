<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\DonationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\Rule;

class Donation extends Model
{
    /** @use HasFactory<DonationFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'En attente';

    public const STATUS_VALIDATED = 'Validé';

    public const STATUS_DELIVERED = 'Livré';

    // Public visé par la demande
    public const CATEGORIES = ['Bébés', 'Enfants', 'Femmes', 'Hommes', 'Mixte'];

    protected $fillable = [
        'association_id',
        'quantity_items',
        'category',
        'description',
        'delivery_address',
        'delivery_city',
        'donation_date',
        'status',
    ];

    protected $casts = [
        'donation_date' => 'date',
    ];

    protected static function booted(): void
    {
        // Référence lisible, unique et stable : DON-2026-0042 (année de soumission + numéro)
        static::created(function (Donation $donation) {
            if (! $donation->reference) {
                $donation->forceFill([
                    'reference' => sprintf('DON-%s-%04d', $donation->created_at->format('Y'), $donation->id),
                ])->saveQuietly();
            }
        });
    }

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_VALIDATED, self::STATUS_DELIVERED];
    }

    // Champs de la demande communs au formulaire association et au formulaire admin
    public static function detailRules(): array
    {
        return [
            'quantity_items' => ['required', 'integer', 'min:1', 'max:10000'],
            'category' => ['required', Rule::in(self::CATEGORIES)],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
            'delivery_address' => ['required', 'string', 'max:255'],
            'delivery_city' => ['required', 'string', 'max:100'],
        ];
    }

    public static function detailMessages(): array
    {
        return [
            'category.required' => 'Choisissez le public visé.',
            'category.in' => 'Catégorie invalide.',
            'description.required' => 'Décrivez votre besoin (pour qui, pourquoi, tailles…).',
            'description.min' => 'La description doit contenir au moins 10 caractères.',
            'delivery_address.required' => 'L’adresse de livraison est obligatoire.',
            'delivery_city.required' => 'La ville de livraison est obligatoire.',
        ];
    }

    // Date de soumission = date de création de la demande
    public function submittedAt(): ?CarbonInterface
    {
        return $this->created_at;
    }

    public function deliveryLocation(): string
    {
        return collect([$this->delivery_address, $this->delivery_city])->filter()->implode(', ') ?: '—';
    }

    public function statusBadgeClasses(): string
    {
        return match ($this->status) {
            self::STATUS_DELIVERED => 'border-emerald-200 bg-emerald-50 text-emerald-900',
            self::STATUS_VALIDATED => 'border-sky-200 bg-sky-50 text-sky-900',
            default => 'border-amber-200 bg-amber-50 text-amber-900',
        };
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }
}
