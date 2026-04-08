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
        Schema::create('npc_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->unsignedBigInteger('customer_id')->nullable(); // Refers to promise-drawing customers
            $table->unsignedBigInteger('model_id')->nullable(); // Refers to promise-drawing models
            $table->string('delivery_to')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('npc_events');
    }
};
