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
        Schema::create('npc_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Contoh: PUD, ME, SUPP');
            $table->string('full_name')->nullable()->comment('Contoh: PUD (Painting dll)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_departments');
    }
};
