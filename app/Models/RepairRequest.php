<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RepairRequest extends Model
{
    use HasFactory;

    public const PROBLEM_ZIPPER = 'Fermeture éclair';
    public const PROBLEM_HOLE = 'Trou';
    public const PROBLEM_HEM = 'Ourlet';
    public const STATUS_PENDING = 'En attente';
    public const STATUS_QUOTE_PROPOSED = 'Devis proposé';
    public const STATUS_QUOTE_ACCEPTED = 'Devis accepté';
    public const STATUS_QUOTE_REFUSED = 'Devis refusé';
    public const STATUS_IN_PROGRESS = 'En cours';
    public const STATUS_READY_FOR_PICKUP = 'Prêt à récupérer';
    public const STATUS_REPAIRED = 'Réparé';
    public const STATUS_COLLECTED = 'Récupéré';

    protected $fillable = ['user_id', 'item_description', 'problem_type', 'cost', 'estimated_cost', 'estimated_completion_date', 'status', 'workshop_id'];

    protected $casts = ['cost' => 'decimal:2', 'estimated_cost' => 'decimal:2', 'estimated_completion_date' => 'date'];

    public static function problemTypes(): array
    {
        return [self::PROBLEM_ZIPPER, self::PROBLEM_HOLE, self::PROBLEM_HEM];
    }

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_QUOTE_PROPOSED, self::STATUS_QUOTE_ACCEPTED, self::STATUS_QUOTE_REFUSED, self::STATUS_IN_PROGRESS, self::STATUS_READY_FOR_PICKUP, self::STATUS_REPAIRED, self::STATUS_COLLECTED];
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(RepairPhoto::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (RepairRequest $repairRequest) {
            $repairRequest->photos->each(function (RepairPhoto $photo) {
                Storage::disk('public')->delete($photo->path);
            });
        });
    }
}
