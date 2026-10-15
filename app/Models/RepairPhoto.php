<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RepairPhoto extends Model
{
    use HasFactory;

    public const TYPE_BEFORE = 'Avant';
    public const TYPE_AFTER = 'Après';

    protected $fillable = ['repair_request_id', 'path', 'type'];

    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class);
    }
}
