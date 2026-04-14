<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcPart extends Model
{
    use HasFactory;

    protected $table = 'npc_parts';

    protected $fillable = [
        'npc_purchase_order_id',
        'product_id',
        'qty',
        'delivery_date',
        'actual_delivery',
        'actual_completion_date',
        'production_notes',
        'status',
        'qc_target_date',
        'mgm_target_date',
        'condition'
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(NpcPurchaseOrder::class, 'npc_purchase_order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Custom accessor / relationship-like method to get event if heavily relied upon
    public function getEventAttribute()
    {
        return $this->purchaseOrder ? $this->purchaseOrder->event : null;
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
