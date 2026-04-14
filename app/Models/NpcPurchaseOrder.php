<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcPurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'npc_purchase_orders';

    protected $fillable = [
        'npc_event_id',
        'po_no'
    ];

    public function event()
    {
        return $this->belongsTo(NpcEvent::class, 'npc_event_id');
    }

    public function parts()
    {
        return $this->hasMany(NpcPart::class, 'npc_purchase_order_id');
    }
}
