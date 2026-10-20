<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workshop extends Model
{
    use HasFactory;

<<<<<<< HEAD
    protected $fillable = ['name', 'specialty', 'address', 'phone', 'owner_id'];
=======
    protected $fillable = ['name', 'specialty', 'address', 'phone'];
>>>>>>> origin/Donation/Association-"aziz"

    protected $casts = [];

    public function repairRequests()
    {
        return $this->hasMany(RepairRequest::class);
    }

<<<<<<< HEAD
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

=======
>>>>>>> origin/Donation/Association-"aziz"
    protected static function booted(): void
    {
        static::deleting(function (Workshop $workshop) {
            $workshop->repairRequests()->each(fn (RepairRequest $repairRequest) => $repairRequest->delete());
        });
    }
}
