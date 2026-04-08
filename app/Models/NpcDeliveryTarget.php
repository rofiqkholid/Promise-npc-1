<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcDeliveryTarget extends Model
{
    use HasFactory;

    protected $fillable = [
        'target_name',
        'is_active'
    ];
}
