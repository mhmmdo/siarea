<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'full_name',
        'phone',
        'basic_salary',
        'late_deduction_amount',
        'hire_date',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'basic_salary' => 'decimal:2',
        'late_deduction_amount' => 'decimal:2',
    ];

    /**
     * Relation: Employee belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation: Employee belongs to Shift
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relation: Employee has many attendance records
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    /**
     * Relation: Employee has many late records
     */
    public function lateRecords(): HasMany
    {
        return $this->hasMany(LateRecord::class);
    }

    /**
     * Relation: Employee has many salary calculations
     */
    public function salaryCalculations(): HasMany
    {
        return $this->hasMany(SalaryCalculation::class);
    }

    /**
     * Get today's attendance record for this employee
     */
    public function todayAttendance()
    {
        return $this->attendanceRecords()
            ->whereDate('date', today())
            ->first();
    }

    /**
     * Get attendance records within a date range
     */
    public function attendanceInPeriod($startDate, $endDate)
    {
        return $this->attendanceRecords()
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    }

    /**
     * Get late records within a date range
     */
    public function lateRecordsInPeriod($startDate, $endDate)
    {
        return $this->lateRecords()
            ->whereBetween('date', [$startDate, $endDate])
            ->get();
    }

    /**
     * Total deduction for a period
     */
    public function getTotalDeductionInPeriod($startDate, $endDate)
    {
        return $this->lateRecordsInPeriod($startDate, $endDate)
            ->sum('late_deduction_amount');
    }
}
