<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpcDepartment extends Model
{
    protected $fillable = ['name', 'full_name', 'is_active'];
}
