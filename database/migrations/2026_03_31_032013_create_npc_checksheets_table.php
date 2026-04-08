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
        Schema::create('npc_checksheets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('npc_part_id');
            // QC Stage
            $table->unsignedBigInteger('qe_checked_by')->nullable();
            $table->dateTime('qe_check_date')->nullable();
            $table->decimal('accuracy_percentage', 5, 2)->nullable();
            $table->string('attachment_path')->nullable();
            // MGM Stage
            $table->unsignedBigInteger('mgm_checked_by')->nullable();
            $table->dateTime('mgm_check_date')->nullable();
            $table->string('final_result')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_checksheets');
    }
};
