<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpcInternalCategory extends Model
{
    protected $fillable = ['name'];

    public function customerCategories()
    {
        return $this->hasMany(NpcCustomerCategory::class, 'internal_category_id');
    }
}
