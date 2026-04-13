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
            $table->foreignId('customer_category_id')->nullable()->after('event_name')->constrained('npc_customer_categories')->nullOnDelete();
            $table->foreignId('delivery_group_id')->nullable()->after('customer_category_id')->constrained('npc_delivery_groups')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_events', function (Blueprint $table) {
            $table->dropForeign(['customer_category_id']);
            $table->dropForeign(['delivery_group_id']);
            $table->dropColumn(['customer_category_id', 'delivery_group_id']);
        });
    }
};
