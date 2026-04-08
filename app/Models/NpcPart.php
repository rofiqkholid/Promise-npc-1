<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcPart extends Model
{
    use HasFactory;

    protected $table = 'npc_parts';

    protected $fillable = [
        'npc_event_id',
        'po_no',
        'part_no',
        'part_name',
        'qty',
        'delivery_date',
        'actual_delivery',
        'actual_completion_date',
        'production_notes',
        'department',
        'process',
        'status',
        'qc_target_date',
        'mgm_target_date',
        'condition'
    ];

    public function npcEvent()
    {
        return $this->belongsTo(NpcEvent::class, 'npc_event_id');
    }

    public function checksheet()
    {
        return $this->hasOne(NpcChecksheet::class, 'npc_part_id');
    }

    public function processes()
    {
        return $this->hasMany(NpcPartProcess::class, 'npc_part_id')->orderBy('sequence_order');
    }

    public function checkpoints()
    {
        return $this->belongsToMany(NpcMasterCheckpoint::class, 'npc_part_checkpoints', 'npc_part_id', 'npc_master_checkpoint_id');
    }
}
