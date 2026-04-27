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
        Schema::create('npc_role_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('npc_roles')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('npc_menus')->onDelete('cascade');
            
            // Granular permissions
            $table->boolean('can_view')->default(true);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_approve')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_role_menus');
    }
};
