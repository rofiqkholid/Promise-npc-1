<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function vehicleModel()
    {
        return $this->belongsTo(VehicleModel::class, 'model_id');
    }

    public function mappedCheckpoints()
    {
        return $this->hasMany(ProductCheckpoint::class, 'product_id');
    }

    public function historyProblems()
    {
        return $this->hasMany(ProductHistoryProblem::class, 'product_id')->orderBy('created_at', 'desc');
    }
}
