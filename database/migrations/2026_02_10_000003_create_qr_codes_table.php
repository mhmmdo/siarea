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
        Schema::create('qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('restrict');
            $table->string('name'); // "QR Main Counter", "QR Kasir", dll
            $table->string('code')->unique(); // Hash/string unik dari QR
            $table->decimal('latitude', 10, 8); // GPS Latitude
            $table->decimal('longitude', 11, 8); // GPS Longitude
            $table->integer('radius_meters')->default(100); // Default radius 100 meter
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Indexes
            $table->index('shift_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qr_codes');
    }
};
