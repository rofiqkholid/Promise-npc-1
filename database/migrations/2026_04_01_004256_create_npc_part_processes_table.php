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
        Schema::create('npc_part_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('npc_part_id')->constrained('npc_parts')->onDelete('cascade');
            $table->string('process_name');
            $table->string('department')->nullable();
            $table->date('target_completion_date')->nullable();
            $table->date('actual_completion_date')->nullable();
            $table->string('status')->default('WAITING');
            $table->integer('sequence_order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_part_processes');
    }
};
