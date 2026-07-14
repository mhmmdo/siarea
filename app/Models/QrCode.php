<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class QrCode extends Model
{
    use HasFactory;

    protected $table = 'qr_codes';

    protected $fillable = [
        'shift_id',
        'name',
        'code',
        'latitude',
        'longitude',
        'radius_meters',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
    ];

    /**
     * Relation: QR Code belongs to Shift
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Relation: QR Code has many attendance records
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'qr_id');
    }

    public function isCurrentlyActive(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Get shift times - these should always exist since QR is linked to shift
        if (!$this->shift) {
            \Log::warning('QR Code has no shift', ['qr_id' => $this->id]);
            return false;
        }

        $now = Carbon::now();
        $activeFrom = $this->shift->start_time;
        $activeUntil = $this->shift->end_time;

        try {
            // Parse times - handle both "HH:MM" and "HH:MM:SS" formats
            $fromParts = explode(':', (string)$activeFrom);
            $untilParts = explode(':', (string)$activeUntil);

            $fromHour = intval($fromParts[0]);
            $fromMinute = intval($fromParts[1]);
            $untilHour = intval($untilParts[0]);
            $untilMinute = intval($untilParts[1]);

            // Current time
            $currentHour = $now->hour;
            $currentMinute = $now->minute;

            // Convert to total minutes since midnight
            $currentTotalMinutes = $currentHour * 60 + $currentMinute;
            $fromTotalMinutes = $fromHour * 60 + $fromMinute;
            $untilTotalMinutes = $untilHour * 60 + $untilMinute;

            // Allow checking in up to 60 minutes before shift starts
            $allowedFromMinutes = $fromTotalMinutes - 60;
            if ($allowedFromMinutes < 0) {
                $allowedFromMinutes += 24 * 60;
            }

            \Log::debug('QR Time Check', [
                'qr_id' => $this->id,
                'qr_name' => $this->name,
                'is_active' => $this->is_active,
                'current' => sprintf('%02d:%02d', $currentHour, $currentMinute),
                'shift_start' => $activeFrom,
                'shift_end' => $activeUntil,
                'from_minutes' => $fromTotalMinutes,
                'allowed_from_minutes' => $allowedFromMinutes,
                'until_minutes' => $untilTotalMinutes,
                'current_minutes' => $currentTotalMinutes,
            ]);

            // Normal time range (e.g., 09:00 - 18:00)
            if ($allowedFromMinutes <= $untilTotalMinutes) {
                $isActive = $currentTotalMinutes >= $allowedFromMinutes && $currentTotalMinutes <= $untilTotalMinutes;
            }
            // Overnight range (e.g., 17:00 - 00:00)
            else {
                $isActive = $currentTotalMinutes >= $allowedFromMinutes || $currentTotalMinutes <= $untilTotalMinutes;
            }

            \Log::debug('QR Time Result', ['qr_id' => $this->id, 'is_active' => $isActive]);

            return $isActive;
        } catch (\Exception $e) {
            \Log::error('QR Time Parse Error', [
                'qr_id' => $this->id,
                'error' => $e->getMessage(),
                'shift_start' => $activeFrom,
                'shift_end' => $activeUntil,
            ]);
            return false;
        }
    }

    /**
     * Check if user is within radius of QR code location (using Haversine formula)
     * Returns distance in meters if within radius, false otherwise
     */
    public function checkGPSDistance($userLat, $userLon): int|false
    {
        $distance = $this->calculateHaversineDistance(
            $this->latitude,
            $this->longitude,
            $userLat,
            $userLon
        );

        if ($distance <= $this->radius_meters) {
            return intval($distance);
        }

        return false;
    }

    /**
     * Calculate distance between two GPS coordinates using Haversine formula
     * Returns distance in meters
     */
    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000; // meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Get scan statistics for today
     */
    public function getTodayScanStats()
    {
        return $this->attendanceRecords()
            ->whereDate('created_at', today())
            ->selectRaw('COUNT(*) as total_scans')
            ->selectRaw('SUM(CASE WHEN is_late THEN 1 ELSE 0 END) as late_count')
            ->first();
    }
}
