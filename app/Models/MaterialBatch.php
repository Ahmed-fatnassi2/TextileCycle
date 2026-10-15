<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaterialBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_type',
        'weight',
        'quality_grade',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:2',
        ];
    }

    public function upcycledProducts(): HasMany
    {
        return $this->hasMany(UpcycledProduct::class);
    }
}