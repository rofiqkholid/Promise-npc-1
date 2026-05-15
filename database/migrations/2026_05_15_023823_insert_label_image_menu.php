<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Cari parent_id dari group "Master Data"
        $masterDataMenu = DB::table('npc_menus')
            ->whereNull('parent_id')
            ->where('title', 'Master Data')
            ->first();

        if ($masterDataMenu) {
            DB::table('npc_menus')->insert([
                'parent_id'  => $masterDataMenu->id,
                'title'      => 'Label Image Produk',
                'route_name' => 'master.product-images.index',
                'icon'       => 'fa-solid fa-image',
                'order'      => 11,
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('npc_menus')
            ->where('route_name', 'master.product-images.index')
            ->delete();
    }
};
