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
        Schema::table('npc_parts', function (Blueprint $table) {
            $table->date('actual_completion_date')->nullable()->after('delivery_date');
            $table->text('production_notes')->nullable()->after('actual_completion_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_parts', function (Blueprint $table) {
            //
        });
    }
};
