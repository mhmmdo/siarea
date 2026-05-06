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
        Schema::create('late_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('attendance_id')->unique()->constrained('attendance_records')->onDelete('cascade');
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('restrict');
            $table->date('date'); // Tanggal late
            $table->time('expected_time'); // Expected arrival time dari shift
            $table->time('actual_time'); // Actual check-in time
            $table->integer('duration_minutes'); // Berapa menit late
            $table->decimal('late_deduction_amount', 15, 2); // Berapa gaji dipotong
            $table->timestamps();
            
            // Indexes
            $table->index(['employee_id', 'date']);
            $table->index(['date', 'shift_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('late_records');
    }
};
