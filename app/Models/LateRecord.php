<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LateRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'attendance_id',
        'shift_id',
        'date',
        'expected_time',
        'actual_time',
        'duration_minutes',
        'late_deduction_amount',
    ];

    protected $casts = [
        'date' => 'date',
        'late_deduction_amount' => 'decimal:2',
    ];

    /**
     * Relation: Late record belongs to Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relation: Late record belongs to Attendance Record
     */
    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class, 'attendance_id');
    }

    /**
     * Relation: Late record belongs to Shift
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get formatted time difference display
     */
    public function getFormattedDurationAttribute(): string
    {
        return "{$this->duration_minutes} minutes";
    }

    /**
     * Get formatted deduction display
     */
    public function getFormattedDeductionAttribute(): string
    {
        return 'Rp ' . number_format($this->late_deduction_amount, 0, ',', '.');
    }
}
