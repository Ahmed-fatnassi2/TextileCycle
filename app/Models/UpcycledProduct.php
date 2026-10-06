<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UpcycledProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'stock',
        'material_batch_id',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    public function materialBatch(): BelongsTo
    {
        return $this->belongsTo(MaterialBatch::class);
    }
}