<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'process_name',
        'department_id'
    ];

    public function department()
    {
        return $this->belongsTo(NpcDepartment::class, 'department_id');
    }
}
