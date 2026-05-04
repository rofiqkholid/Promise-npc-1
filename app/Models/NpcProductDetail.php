<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpcProductDetail extends Model
{
    protected $fillable = [
        'product_id',
        'sketch_image_path',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
