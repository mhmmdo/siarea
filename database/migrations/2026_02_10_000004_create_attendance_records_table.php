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
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('qr_id')->constrained('qr_codes')->onDelete('restrict');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('restrict');
            $table->date('date'); // Tanggal absen
            $table->time('check_in_time'); // Jam check-in
            $table->decimal('user_latitude', 10, 8); // GPS latitude user saat scan
            $table->decimal('user_longitude', 11, 8); // GPS longitude user saat scan
            $table->integer('distance_from_qr'); // Jarak dalam meter dari QR location
            $table->boolean('is_late')->default(false); // True jika late
            $table->timestamps();
            
            // Ensure unique attendance per shift per day per employee
            $table->unique(['employee_id', 'date', 'shift_id']);
            
            // Indexes untuk query cepat
            $table->index(['employee_id', 'date']);
            $table->index(['date', 'shift_id']);
            $table->index('is_late');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
