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
        Schema::create('npc_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_event_id')->constrained('npc_events')->onDelete('cascade');
            $table->string('po_no')->nullable();
            $table->string('part_no')->nullable();
            $table->string('part_name')->nullable();
            $table->integer('qty')->default(0);
            $table->date('delivery_date')->nullable();
            $table->date('actual_delivery')->nullable();
            $table->string('department')->nullable(); // Removed in new flow, moved to processes
            $table->string('process')->nullable(); // Removed in new flow, moved to processes
            $table->string('status')->default('PO_REGISTERED');
            $table->string('condition')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_parts');
    }
};
