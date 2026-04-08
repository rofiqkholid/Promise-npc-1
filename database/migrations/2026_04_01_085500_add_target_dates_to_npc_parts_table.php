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
        Schema::table('npc_parts', function (Blueprint $row) {
            $row->date('qc_target_date')->nullable()->after('status');
            $row->date('mgm_target_date')->nullable()->after('qc_target_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_parts', function (Blueprint $row) {
            $row->dropColumn(['qc_target_date', 'mgm_target_date']);
        });
    }
};
