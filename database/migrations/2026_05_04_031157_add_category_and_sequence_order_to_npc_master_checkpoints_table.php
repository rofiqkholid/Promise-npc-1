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
        Schema::table('npc_master_checkpoints', function (Blueprint $table) {
            $table->string('category')->nullable()->after('point_number');
            $table->integer('sequence_order')->default(0)->after('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_master_checkpoints', function (Blueprint $table) {
            $table->dropColumn(['category', 'sequence_order']);
        });
    }
};
