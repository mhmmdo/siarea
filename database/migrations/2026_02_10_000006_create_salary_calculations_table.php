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
        Schema::create('salary_calculations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->date('period_date'); // Periode (bulan-tahun)
            $table->decimal('base_salary', 15, 2); // Gaji pokok
            $table->decimal('total_deduction', 15, 2)->default(0); // Total potong gaji karena late
            $table->decimal('final_salary', 15, 2); // Base - Total deduction
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Ensure unique salary per period per employee
            $table->unique(['employee_id', 'period_date'], 'salary_calc_unique');
            
            // Indexes
            $table->index('employee_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_calculations');
    }
};
