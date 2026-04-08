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
        Schema::create('npc_master_checkpoints', function (Blueprint $table) {
            $table->id();
            $table->integer('point_number')->unique()->comment('Urutan pengecekan');
            $table->string('check_item')->comment('Item yang dicek');
            $table->string('standard')->nullable()->comment('Standar / Toleransi');
            $table->string('method')->nullable()->comment('Metode pengecekan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_master_checkpoints');
    }
};
