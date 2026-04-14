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
        // 1. Create npc_purchase_orders table
        Schema::create('npc_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('npc_event_id')->nullable(); 
            $table->string('po_no');
            $table->timestamps();
        });

        // 2. Normalize npc_processes
        Schema::table('npc_processes', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('process_name');
            $table->dropColumn('department');
        });

        // 3. Normalize npc_part_processes
        Schema::table('npc_part_processes', function (Blueprint $table) {
            $table->unsignedBigInteger('process_id')->nullable()->after('npc_part_id');
            $table->dropColumn(['process_name', 'department']);
        });

        // 4. Normalize npc_parts
        Schema::table('npc_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('npc_purchase_order_id')->nullable()->after('id');
            $table->unsignedBigInteger('product_id')->nullable()->after('npc_purchase_order_id');
            
            // Drop foreign key constraints before dropping columns
            // Since it was created using ->constrained('npc_events')
            $table->dropForeign(['npc_event_id']);
            
            $table->dropColumn([
                'npc_event_id', 
                'po_no', 
                'part_no', 
                'part_name', 
                'department', 
                'process'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_parts', function (Blueprint $table) {
            $table->unsignedBigInteger('npc_event_id')->nullable()->after('id');
            $table->string('po_no')->nullable();
            $table->string('part_no')->nullable();
            $table->string('part_name')->nullable();
            $table->string('department')->nullable();
            $table->string('process')->nullable();
            
            $table->dropColumn(['npc_purchase_order_id', 'product_id']);
        });

        Schema::table('npc_part_processes', function (Blueprint $table) {
            $table->string('process_name')->nullable()->after('npc_part_id');
            $table->string('department')->nullable()->after('process_name');
            
            $table->dropColumn('process_id');
        });

        Schema::table('npc_processes', function (Blueprint $table) {
            $table->string('department')->nullable()->after('process_name');
            $table->dropColumn('department_id');
        });

        Schema::dropIfExists('npc_purchase_orders');
    }
};
