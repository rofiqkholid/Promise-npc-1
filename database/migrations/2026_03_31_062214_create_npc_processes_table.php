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
        Schema::create('npc_processes', function (Blueprint $table) {
            $table->id();
            $table->string('process_name')->unique()->comment('Contoh: Stamping, Assy, SUPP');
            $table->string('department')->comment('PUD atau ME');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_processes');
    }
};
