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
        Schema::create('npc_checksheet_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('npc_checksheet_id');
            $table->string('point_check'); 
            $table->text('standard')->nullable();
            $table->text('samples')->nullable();
            $table->string('row_result')->nullable(); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_checksheet_details');
    }
};
