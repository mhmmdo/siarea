<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'qr_id',
        'shift_id',
        'date',
        'check_in_time',
        'user_latitude',
        'user_longitude',
        'distance_from_qr',
        'is_late',
    ];

    protected $casts = [
        'date' => 'date',
        'user_latitude' => 'decimal:8',
        'user_longitude' => 'decimal:8',
        'is_late' => 'boolean',
    ];

    /**
     * Relation: Attendance record belongs to Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Relation: Attendance record belongs to QR Code
     */
    public function qrCode(): BelongsTo
    {
        return $this->belongsTo(QrCode::class, 'qr_id');
    }

    /**
     * Relation: Attendance record belongs to Shift
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relation: Attendance record has one Late record (if employee was late)
     */
    public function lateRecord(): HasOne
    {
        return $this->hasOne(LateRecord::class, 'attendance_id');
    }

    /**
     * Relation: Attendance record has one Check-out record
     */
    public function checkout(): HasOne
    {
        return $this->hasOne(AttendanceCheckout::class, 'attendance_id');
    }

    /**
     * Check if employee has checked out
     */
    public function hasCheckedOut(): bool
    {
        return $this->checkout()->exists();
    }

    /**
     * Get elapsed time between check-in and check-out
     */
    public function getElapsedTime(): ?string
    {
        if (!$this->checkout) {
            return null;
        }

        try {
            $checkIn = Carbon::createFromFormat('H:i:s', (string)$this->check_in_time);
            $checkOut = Carbon::createFromFormat('H:i:s', (string)$this->checkout->check_out_time);
            
            // Handle overnight shifts
            if ($checkOut->lessThan($checkIn)) {
                $checkOut->addDay();
            }
            
            $diff = $checkOut->diff($checkIn);
            return "{$diff->h}h {$diff->i}m";
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if attendance is early or on time
     */
    public function isOnTime(): bool
    {
        return !$this->is_late;
    }

    /**
     * Parse check_in_time as Carbon time instance
     */
    public function getCheckInTimeAttribute($value)
    {
        // If value is null, return null
        if (!$value) {
            return null;
        }

        // If it's already a Carbon instance, return it
        if ($value instanceof Carbon) {
            return $value;
        }

        // Parse string to Carbon time
        try {
            return Carbon::createFromFormat('H:i:s', (string)$value);
        } catch (\Exception $e) {
            // If parsing fails, return as-is
            return $value;
        }
    }

    /**
     * Get status string for display
     */
    public function getStatusAttribute(): string
    {
        if ($this->is_late && $this->lateRecord) {
            $duration = $this->lateRecord->duration_minutes;
            return "Late {$duration} minutes";
        }

        return 'On Time';
    }
}
