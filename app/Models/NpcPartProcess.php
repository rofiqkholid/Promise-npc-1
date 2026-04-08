<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NpcPartProcess extends Model
{
    use HasFactory;

    protected $table = 'npc_part_processes';

    protected $fillable = [
        'npc_part_id',
        'process_name',
        'department',
        'target_completion_date',
        'actual_completion_date',
        'status',
        'sequence_order',
    ];

    public function part()
    {
        return $this->belongsTo(NpcPart::class, 'npc_part_id');
    }
}
