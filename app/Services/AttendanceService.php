<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\AttendanceCheckout;
use App\Models\Employee;
use App\Models\LateRecord;
use App\Models\QrCode;
use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    /**
     * Process QR scan and create attendance record
     * 
     * Returns array with keys: success, message, data (if success), errors (if failed)
     */
    public function processQRScan(
        int $employeeId,
        string $qrCode,
        float $userLatitude,
        float $userLongitude
    ): array
    {
        return DB::transaction(function () use ($employeeId, $qrCode, $userLatitude, $userLongitude) {
            // Validation: Employee exists and active
            $employee = Employee::where('id', $employeeId)
                ->where('status', 'active')
                ->with('user', 'shift')
                ->first();

            if (!$employee) {
                return [
                    'success' => false,
                    'message' => 'Employee tidak ditemukan atau tidak aktif',
                    'errors' => ['employee' => 'Invalid employee'],
                ];
            }

            // Validation: QR code exists and active
            $qr = QrCode::where('code', $qrCode)
                ->where('is_active', true)
                ->with('shift')
                ->first();

            if (!$qr) {
                return [
                    'success' => false,
                    'message' => '❌ QR Tidak Valid',
                    'errors' => ['qr' => 'QR code tidak terdaftar atau sudah dinonaktifkan'],
                ];
            }

            // Validation: Employee is assigned to this shift
            if ($employee->shift_id !== $qr->shift_id) {
                return [
                    'success' => false,
                    'message' => '❌ QR Shift Tidak Sesuai',
                    'errors' => ['shift' => 'QR ini tidak untuk shift Anda'],
                ];
            }

            // Validation: User is within GPS radius of QR location
            $distance = $qr->checkGPSDistance($userLatitude, $userLongitude);
            if ($distance === false) {
                $gpsDistance = $this->calculateHaversineDistance(
                    $qr->latitude,
                    $qr->longitude,
                    $userLatitude,
                    $userLongitude
                );

                return [
                    'success' => false,
                    'message' => '❌ Lokasi Tidak Valid',
                    'errors' => [
                        'location' => "Anda berada di luar area cafe (>{$qr->radius_meters} meter)",
                        'distance' => "{$this->formatDistance($gpsDistance)}",
                    ],
                ];
            }

            // Validation: Employee hasn't already checked in today for this shift
            $existingAttendance = AttendanceRecord::where('employee_id', $employeeId)
                ->where('shift_id', $qr->shift_id)
                ->whereDate('date', today())
                ->first();

            if ($existingAttendance) {
                return [
                    'success' => false,
                    'message' => '✓ Sudah Absen Hari Ini',
                    'errors' => ['attendance' => 'Anda sudah absen untuk shift ini hari ini'],
                    'data' => [
                        'existing_time' => $existingAttendance->check_in_time->format('H:i'),
                        'status' => $existingAttendance->getStatusAttribute(),
                    ],
                ];
            }

            // Process attendance: Check if late
            $now = now();
            $currentTime = $now->format('H:i:s');
            $expectedTime = $employee->shift->expected_arrival_time;
            
            $isLate = $currentTime > $expectedTime;

            // Create attendance record
            $attendance = AttendanceRecord::create([
                'employee_id' => $employeeId,
                'qr_id' => $qr->id,
                'shift_id' => $qr->shift_id,
                'date' => today(),
                'check_in_time' => $now->format('H:i:s'),
                'user_latitude' => $userLatitude,
                'user_longitude' => $userLongitude,
                'distance_from_qr' => $distance,
                'is_late' => $isLate,
            ]);

            // If late, create late record and calculate deduction
            if ($isLate) {
                $durationMinutes = $this->calculateDurationMinutes($expectedTime, $currentTime);
                $numberOfBlocks = ceil($durationMinutes / 15);
                $deductionAmount = $numberOfBlocks * $employee->late_deduction_amount;

                LateRecord::create([
                    'employee_id' => $employeeId,
                    'attendance_id' => $attendance->id,
                    'shift_id' => $qr->shift_id,
                    'date' => today(),
                    'expected_time' => $expectedTime,
                    'actual_time' => $currentTime,
                    'duration_minutes' => $durationMinutes,
                    'late_deduction_amount' => $deductionAmount,
                ]);

                return [
                    'success' => true,
                    'message' => '✓ Absen Berhasil - TELAT',
                    'data' => [
                        'attendance_id' => $attendance->id,
                        'status' => 'LATE',
                        'check_in_time' => $now->format('H:i'),
                        'expected_time' => $expectedTime,
                        'duration_minutes' => $durationMinutes,
                        'deduction_amount' => $deductionAmount,
                        'formatted_deduction' => 'Rp ' . number_format($deductionAmount, 0, ',', '.'),
                    ],
                ];
            }

            return [
                'success' => true,
                'message' => '✓ Absen Berhasil - ON TIME',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'status' => 'ON TIME',
                    'check_in_time' => $now->format('H:i'),
                    'expected_time' => $expectedTime,
                    'location_verified' => true,
                    'distance_from_location' => "{$distance}m",
                ],
            ];
        });
    }

    /**
     * Process QR scan for check-out
     * 
     * Returns array with keys: success, message, data (if success), errors (if failed)
     */
    public function processCheckout(
        int $employeeId,
        int $attendanceId,
        string $qrCode,
        float $userLatitude,
        float $userLongitude
    ): array
    {
        return DB::transaction(function () use ($employeeId, $attendanceId, $qrCode, $userLatitude, $userLongitude) {
            // Validation: Attendance record exists and belongs to employee
            $attendance = AttendanceRecord::where('id', $attendanceId)
                ->where('employee_id', $employeeId)
                ->whereDate('date', today())
                ->with('employee', 'shift', 'qrCode')
                ->first();

            if (!$attendance) {
                return [
                    'success' => false,
                    'message' => 'Absensi tidak ditemukan',
                    'errors' => ['attendance' => 'Tidak ada absensi masuk hari ini'],
                ];
            }

            // Validation: Employee hasn't already checked out
            if ($attendance->hasCheckedOut()) {
                return [
                    'success' => false,
                    'message' => '✓ Sudah Pulang',
                    'errors' => ['checkout' => 'Anda sudah mencatat pulang hari ini'],
                    'data' => [
                        'checkout_time' => $attendance->checkout->check_out_time->format('H:i'),
                        'elapsed_time' => $attendance->getElapsedTime(),
                    ],
                ];
            }

            // Validation: QR code is valid and active
            $qr = QrCode::where('code', $qrCode)
                ->where('is_active', true)
                ->first();

            if (!$qr) {
                return [
                    'success' => false,
                    'message' => '❌ QR Tidak Valid',
                    'errors' => ['qr' => 'QR code tidak terdaftar atau sudah dinonaktifkan'],
                ];
            }

            // Validation: QR belongs to same shift
            if ($qr->shift_id !== $attendance->shift_id) {
                return [
                    'success' => false,
                    'message' => '❌ QR Shift Tidak Sesuai',
                    'errors' => ['shift' => 'QR ini tidak sesuai dengan shift Anda'],
                ];
            }

            // Validation: Current time is at or after shift end time
            $now = now();
            $currentTime = $now->format('H:i:s');
            $shiftEndTime = $attendance->shift->end_time;
            
            // Handle overnight shifts (e.g., 17:00-00:00)
            if ($shiftEndTime < $attendance->shift->start_time) {
                // Overnight shift: check if current time is after end time OR still before end time on next day
                $currentTimeMinutes = $this->timeToMinutes($currentTime);
                $endTimeMinutes = $this->timeToMinutes($shiftEndTime);
                
                // Allow checkout if time >= end time (next day) OR still within shift range
                if ($currentTimeMinutes < $endTimeMinutes) {
                    // We're in the "early morning" part of overnight shift (e.g., 23:00-00:00)
                    // Check if we've passed the end time
                    return [
                        'success' => false,
                        'message' => '❌ Belum Jam Pulang',
                        'errors' => ['time' => "Jam pulang adalah {$shiftEndTime}"],
                    ];
                }
            } else {
                // Normal shift: check if current time >= end time
                if ($currentTime < $shiftEndTime) {
                    return [
                        'success' => false,
                        'message' => '❌ Belum Jam Pulang',
                        'errors' => ['time' => "Jam pulang adalah {$shiftEndTime}"],
                    ];
                }
            }

            // Validation: User is within GPS radius of QR location
            $distance = $qr->checkGPSDistance($userLatitude, $userLongitude);
            if ($distance === false) {
                $gpsDistance = $this->calculateHaversineDistance(
                    $qr->latitude,
                    $qr->longitude,
                    $userLatitude,
                    $userLongitude
                );

                return [
                    'success' => false,
                    'message' => '❌ Lokasi Tidak Valid',
                    'errors' => [
                        'location' => "Anda berada di luar area cafe (>{$qr->radius_meters} meter)",
                        'distance' => "{$this->formatDistance($gpsDistance)}",
                    ],
                ];
            }

            // Process checkout
            $isEarly = $currentTime < $shiftEndTime;

            // Create checkout record
            $checkout = AttendanceCheckout::create([
                'attendance_id' => $attendance->id,
                'check_out_time' => $currentTime,
                'check_out_latitude' => $userLatitude,
                'check_out_longitude' => $userLongitude,
                'distance_from_qr' => $distance,
                'is_early' => $isEarly,
            ]);

            return [
                'success' => true,
                'message' => '✓ Pulang Berhasil' . ($isEarly ? ' - PULANG AWAL' : ''),
                'data' => [
                    'checkout_id' => $checkout->id,
                    'check_in_time' => $attendance->check_in_time->format('H:i'),
                    'check_out_time' => $now->format('H:i'),
                    'elapsed_time' => $this->calculateElapsedTime($attendance->check_in_time->format('H:i:s'), $currentTime),
                    'is_early' => $isEarly,
                    'shift_end_time' => $shiftEndTime,
                    'location_verified' => true,
                    'distance_from_location' => "{$distance}m",
                ],
            ];
        });
    }

    /**
     * Calculate elapsed time between check-in and check-out
     */
    private function calculateElapsedTime(string $checkInTime, string $checkOutTime): string
    {
        $checkIn = Carbon::createFromFormat('H:i:s', $checkInTime);
        $checkOut = Carbon::createFromFormat('H:i:s', $checkOutTime);
        
        // Handle overnight shifts (checkout time is smaller than check-in time)
        if ($checkOut->lessThan($checkIn)) {
            $checkOut->addDay();
        }
        
        $diff = $checkOut->diff($checkIn);
        return "{$diff->h}h {$diff->i}m";
    }

    /**
     * Get employee's attendance summary for today
     */
    public function getTodayAttendanceSummary(int $employeeId): array
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return [];
        }

        $attendance = $employee->todayAttendance();

        return [
            'name' => $employee->full_name,
            'shift' => $employee->shift->name,
            'shift_time' => "{$employee->shift->start_time} - {$employee->shift->end_time}",
            'expected_time' => $employee->shift->expected_arrival_time,
            'has_checked_in' => $attendance !== null,
            'checked_in_at' => $attendance?->check_in_time->format('H:i'),
            'status' => $attendance?->getStatusAttribute() ?? 'Belum absen',
            'is_late' => $attendance?->is_late ?? false,
            'late_details' => $this->getLateDetails($attendance),
        ];
    }

    /**
     * Get attendance history for a period
     */
    public function getAttendanceHistory(int $employeeId, $startDate = null, $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $employee = Employee::find($employeeId);
        if (!$employee) {
            return [];
        }

        $records = $employee->attendanceInPeriod($startDate, $endDate);

        return $records->map(fn ($record) => [
            'date' => $record->date->format('Y-m-d'),
            'day' => $record->date->format('l'),
            'check_in_time' => $record->check_in_time->format('H:i'),
            'shift' => $record->shift->name,
            'status' => $record->getStatusAttribute(),
            'is_late' => $record->is_late,
            'deduction' => $record->lateRecord?->late_deduction_amount ?? 0,
        ])->toArray();
    }

    /**
     * Calculate duration in minutes between two times
     */
    private function calculateDurationMinutes(string $expectedTime, string $actualTime): int
    {
        $expected = Carbon::createFromFormat('H:i:s', $expectedTime);
        $actual = Carbon::createFromFormat('H:i:s', $actualTime);

        return (int) $actual->diffInMinutes($expected);
    }

    /**
     * Calculate Haversine distance between two GPS coordinates
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
     * Format distance for display
     */
    private function formatDistance(float $meters): string
    {
        if ($meters < 1000) {
            return intval($meters) . ' meter';
        }

        return number_format($meters / 1000, 2) . ' km';
    }

    /**
     * Get late record details from attendance
     */
    private function getLateDetails(?AttendanceRecord $attendance): ?array
    {
        if (!$attendance || !$attendance->is_late) {
            return null;
        }

        $lateRecord = $attendance->lateRecord;
        if (!$lateRecord) {
            return null;
        }

        return [
            'duration_minutes' => $lateRecord->duration_minutes,
            'deduction_amount' => $lateRecord->late_deduction_amount,
            'formatted_deduction' => 'Rp ' . number_format($lateRecord->late_deduction_amount, 0, ',', '.'),
        ];
    }

    /**
     * Convert time string (H:i:s) to minutes since midnight
     */
    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        $hours = (int) $parts[0];
        $minutes = (int) $parts[1];
        
        return ($hours * 60) + $minutes;
    }
}
