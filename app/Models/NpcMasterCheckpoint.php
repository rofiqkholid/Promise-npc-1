<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcMasterCheckpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'point_number',
        'check_item',
        'standard',
        'method',
        'is_active'
    ];

    public function parts()
    {
        return $this->belongsToMany(NpcPart::class, 'npc_part_checkpoints', 'npc_master_checkpoint_id', 'npc_part_id');
    }
}
