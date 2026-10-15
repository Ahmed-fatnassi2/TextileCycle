<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'specialty', 'address', 'phone'];

    protected $casts = [];

    public function repairRequests()
    {
        return $this->hasMany(RepairRequest::class);
    }

    protected static function booted(): void
    {
        static::deleting(function (Workshop $workshop) {
            $workshop->repairRequests()->each(fn (RepairRequest $repairRequest) => $repairRequest->delete());
        });
    }
}
