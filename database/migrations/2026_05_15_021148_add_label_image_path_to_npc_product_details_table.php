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
        Schema::table('npc_product_details', function (Blueprint $table) {
            // Gambar khusus untuk label QC, berbeda dengan sketch_image_path yang dipakai di checksheet
            $table->string('label_image_path')->nullable()->after('sketch_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('npc_product_details', function (Blueprint $table) {
            $table->dropColumn('label_image_path');
        });
    }
};
