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
        Schema::create('npc_part_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_part_id')->constrained('npc_parts')->onDelete('cascade');
            $table->foreignId('npc_master_checkpoint_id')->constrained('npc_master_checkpoints')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_part_checkpoints');
    }
};
