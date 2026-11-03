<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiUsageEvent extends Model
{
    protected $fillable = [
        'feature',
        'user_id',
        'model',
        'status_code',
        'succeeded',
        'input_tokens',
        'output_tokens',
    ];

    protected function casts(): array
    {
        return [
            'succeeded' => 'boolean',
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
        ];
    }
}
