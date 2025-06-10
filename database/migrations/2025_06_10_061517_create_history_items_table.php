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
        Schema::create('history_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('history_id')->constrained()->onDelete('cascade');
            $table->string('day_number');
            $table->string('day_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_items');
    }
};
