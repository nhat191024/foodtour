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
        Schema::create('history_buses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('history_id');
            $table->string('name');
            $table->text('address')->nullable();
            $table->text('description')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('departure_time')->nullable();
            $table->string('arrival_time')->nullable();
            $table->string('price')->nullable();
            $table->timestamps();

            $table->foreign('history_id')->references('id')->on('histories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('history_buses');
    }
};
