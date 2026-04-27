<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpcMenu extends Model
{
    protected $fillable = [
        'parent_id',
        'title',
        'route_name',
        'icon',
        'order',
        'is_active',
    ];

    public function parent()
    {
        return $this->belongsTo(NpcMenu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(NpcMenu::class, 'parent_id')->orderBy('order');
    }
}
