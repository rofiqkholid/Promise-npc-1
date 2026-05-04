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
        Schema::table('npc_master_std_parts', function (Blueprint $table) {
            $table->dropColumn('spec');
        });

        Schema::table('npc_spec_child_parts', function (Blueprint $table) {
            $table->string('spec')->nullable()->after('thickness');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_spec_child_parts', function (Blueprint $table) {
            $table->dropColumn('spec');
        });

        Schema::table('npc_master_std_parts', function (Blueprint $table) {
            $table->string('spec')->nullable()->after('name');
        });
    }
};
