<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('npc_master_routings', function (Blueprint $table) {
            $table->id();
            
            // Simpan ID dari tabel products (Drawing)
            $table->integer('part_id')->index(); 
            
            // Simpan ID dari tabel npc_processes
            $table->bigInteger('process_id'); 
            
            // Urutan pengerjaan (1, 2, 3...)
            $table->integer('sequence_order');
            
            $table->timestamps();

            // Opsional: Jika masih satu database dengan Drawing, hidupkan FK ini:
            $table->foreign('part_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('process_id')->references('id')->on('npc_processes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('npc_master_routings');
    }
};