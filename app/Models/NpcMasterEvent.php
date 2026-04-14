<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpcMasterEvent extends Model
{
    protected $fillable = [
        'customer_id',
        'model_id',
        'name',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function transactions()
    {
        return $this->hasMany(NpcEvent::class, 'master_event_id');
    }
}
