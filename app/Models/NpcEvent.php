<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NpcEvent extends Model
{
    use HasFactory;

    protected $table = 'npc_events';

    protected $fillable = [
        'master_event_id',
        'delivery_to',
        'customer_category_id',
        'delivery_group_id'
    ];

    public function masterEvent()
    {
        return $this->belongsTo(NpcMasterEvent::class, 'master_event_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(NpcPurchaseOrder::class, 'npc_event_id');
    }

    public function parts()
    {
        return $this->hasManyThrough(NpcPart::class, NpcPurchaseOrder::class, 'npc_event_id', 'npc_purchase_order_id');
    }

    public function customerCategory()
    {
        return $this->belongsTo(NpcCustomerCategory::class, 'customer_category_id');
    }

    public function deliveryGroup()
    {
        return $this->belongsTo(NpcDeliveryGroup::class, 'delivery_group_id');
    }
}
