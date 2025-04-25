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
        Schema::create('calculate_histories', function (Blueprint $table) {
            $table->id();
            $table->string('trip_name');
            $table->integer('total_per_person');
            $table->integer('total');

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('tour_item_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calculate_histories');
    }
};
