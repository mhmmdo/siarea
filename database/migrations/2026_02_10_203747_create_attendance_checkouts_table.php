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
        Schema::create('attendance_checkouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attendance_id');
            $table->time('check_out_time');
            $table->decimal('check_out_latitude', 10, 8)->nullable();
            $table->decimal('check_out_longitude', 11, 8)->nullable();
            $table->decimal('distance_from_qr', 10, 2)->nullable();
            $table->boolean('is_early')->default(false); // Keluar sebelom jam kerja selesai
            $table->timestamps();

            // Foreign key
            $table->foreign('attendance_id')
                ->references('id')
                ->on('attendance_records')
                ->onDelete('cascade');

            // Indexes
            $table->index('attendance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_checkouts');
    }
};
