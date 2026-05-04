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
        Schema::create('npc_spec_child_parts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->string('part_type');
            $table->string('sequence_label');
            $table->unsignedBigInteger('inventory_material_id')->nullable();
            $table->foreignId('std_part_id')->nullable()->constrained('npc_master_std_parts')->onDelete('set null');
            $table->string('thickness')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_spec_child_parts');
    }
};
