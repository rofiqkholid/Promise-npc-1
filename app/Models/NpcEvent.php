<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcEvent extends Model
{
    use HasFactory;

    protected $table = 'npc_events';

    protected $fillable = [
        'event_name',
        'customer_id',
        'model_id',
        'delivery_to'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function parts()
    {
        return $this->hasMany(NpcPart::class, 'npc_event_id');
    }
}
