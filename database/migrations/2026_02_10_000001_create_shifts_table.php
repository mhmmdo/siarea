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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Shift 1", "Shift 2"
            $table->time('start_time'); // 09:00
            $table->time('end_time'); // 17:00
            $table->time('expected_arrival_time'); // Expected jam datang
            $table->integer('late_threshold_minutes')->default(5); // Threshold untuk late (misal: 5 menit)
            $table->timestamps();
            
            // Indexes untuk query cepat
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
