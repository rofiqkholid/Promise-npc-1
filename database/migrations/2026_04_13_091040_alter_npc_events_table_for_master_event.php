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
        Schema::table('npc_events', function (Blueprint $table) {
            $table->unsignedBigInteger('master_event_id')->after('id')->nullable();
            
            // Drop old columns
            $table->dropColumn(['event_name', 'customer_id', 'model_id']);
            
            // Note: In strict production we would add foreign key constraint here
            // $table->foreign('master_event_id')->references('id')->on('npc_master_events')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_events', function (Blueprint $table) {
            $table->string('event_name')->after('id');
            $table->unsignedBigInteger('customer_id')->nullable()->after('event_name');
            $table->unsignedBigInteger('model_id')->nullable()->after('customer_id');

            $table->dropColumn('master_event_id');
        });
    }
};
