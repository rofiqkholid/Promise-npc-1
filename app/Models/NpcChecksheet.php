<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpcChecksheet extends Model
{
    protected $fillable = [
        'npc_part_id',
        'qe_checked_by',
        'qe_check_date',
        'mgm_checked_by',
        'mgm_check_date',
        'final_result'
    ];

    public function npcPart()
    {
        return $this->belongsTo(NpcPart::class);
    }

    public function details()
    {
        return $this->hasMany(NpcChecksheetDetail::class, 'npc_checksheet_id');
    }

    public function qeChecker()
    {
        return $this->belongsTo(User::class, 'qe_checked_by');
    }

    public function mgmChecker()
    {
        return $this->belongsTo(User::class, 'mgm_checked_by');
    }
}
