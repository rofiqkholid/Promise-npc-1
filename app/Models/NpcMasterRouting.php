<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NpcMasterRouting extends Model
{
    protected $fillable = ['part_id', 'process_id', 'sequence_order'];

    public function part()
    {
        return $this->belongsTo(Product::class, 'part_id');
    }

    public function process()
    {
        return $this->belongsTo(NpcProcess::class, 'process_id');
    }
}
