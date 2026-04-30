<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add po_no to npc_events
        Schema::table('npc_events', function (Blueprint $table) {
            $table->string('po_no')->nullable()->after('id');
        });

        // 2. Add npc_event_id to npc_parts
        Schema::table('npc_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('npc_event_id')->nullable()->after('id');
        });

        // 3. Migrate Data safely
        $purchaseOrders = DB::table('npc_purchase_orders')->get();
        foreach ($purchaseOrders as $po) {
            // Move PO number to Event
            DB::table('npc_events')
                ->where('id', $po->npc_event_id)
                ->update(['po_no' => $po->po_no]);

            // Link parts directly to Event
            DB::table('npc_parts')
                ->where('npc_purchase_order_id', $po->id)
                ->update(['npc_event_id' => $po->npc_event_id]);
        }

        // 4. Drop redundant columns and table
        Schema::table('npc_parts', function (Blueprint $table) {
            $table->dropColumn('npc_purchase_order_id');
            // Re-establish foreign key to event if needed (optional based on your design, omitting to match previous schema style)
        });

        Schema::dropIfExists('npc_purchase_orders');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Recreate npc_purchase_orders table
        Schema::create('npc_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('npc_event_id')->nullable(); 
            $table->string('po_no');
            $table->timestamps();
        });

        // 2. Re-add npc_purchase_order_id to npc_parts
        Schema::table('npc_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('npc_purchase_order_id')->nullable()->after('id');
        });

        // 3. Migrate data back
        $events = DB::table('npc_events')->whereNotNull('po_no')->get();
        foreach ($events as $event) {
            $poId = DB::table('npc_purchase_orders')->insertGetId([
                'npc_event_id' => $event->id,
                'po_no' => $event->po_no,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('npc_parts')
                ->where('npc_event_id', $event->id)
                ->update(['npc_purchase_order_id' => $poId]);
        }

        // 4. Drop simplified columns
        Schema::table('npc_parts', function (Blueprint $table) {
            $table->dropColumn('npc_event_id');
        });

        Schema::table('npc_events', function (Blueprint $table) {
            $table->dropColumn('po_no');
        });
    }
};
