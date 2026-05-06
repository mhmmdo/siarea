<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class AttendanceCheckout extends Model
{
    use HasFactory;

    protected $table = 'attendance_checkouts';

    protected $fillable = [
        'attendance_id',
        'check_out_time',
        'check_out_latitude',
        'check_out_longitude',
        'distance_from_qr',
        'is_early',
    ];

    protected $casts = [
        'check_out_latitude' => 'decimal:8',
        'check_out_longitude' => 'decimal:8',
        'is_early' => 'boolean',
    ];

    /**
     * Relation: Check-out belongs to Attendance Record
     */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }

    /**
     * Parse check_out_time as Carbon time instance
     */
    public function getCheckOutTimeAttribute($value)
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof Carbon) {
            return $value;
        }

        try {
            return Carbon::createFromFormat('H:i:s', (string)$value);
        } catch (\Exception $e) {
            return $value;
        }
    }
}
