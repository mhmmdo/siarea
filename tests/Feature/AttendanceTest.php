<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\QrCode;
use App\Models\AttendanceRecord;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected $employee;
    protected $shift;
    protected $qrCode;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AttendanceService();

        // Create a Shift (Morning Shift: 09:00 - 18:00)
        $this->shift = Shift::create([
            'name' => 'Shift Pagi',
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'expected_arrival_time' => '09:15:00',
            'late_threshold_minutes' => 15,
        ]);

        // Create a User
        $user = User::create([
            'name' => 'Karyawan Test',
            'username' => 'karyawantest',
            'email' => 'karyawan@test.com',
            'password' => bcrypt('password'),
            'role' => 'employee',
            'status' => 'active',
        ]);

        // Create an Employee
        $this->employee = Employee::create([
            'user_id' => $user->id,
            'shift_id' => $this->shift->id,
            'full_name' => 'Karyawan Test',
            'phone' => '081234567890',
            'basic_salary' => 3000000.00,
            'late_deduction_amount' => 50000.00,
            'hire_date' => today()->format('Y-m-d'),
            'status' => 'active',
        ]);

        // Create a QrCode (Lat: -6.200000, Lon: 106.800000, Radius: 50 meters)
        $this->qrCode = QrCode::create([
            'shift_id' => $this->shift->id,
            'name' => 'QR Cafe A',
            'code' => 'QR_TEST_123',
            'latitude' => -6.20000000,
            'longitude' => 106.80000000,
            'radius_meters' => 50,
            'is_active' => true,
        ]);
    }

    /**
     * Test presence outside geofencing radius.
     */
    public function test_presence_outside_radius()
    {
        // Mock current time during shift (10:00)
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));

        // Attempt scan from a far-away location (Lat: -6.300000, Lon: 106.900000)
        $result = $this->service->processQRScan(
            $this->employee->id,
            $this->qrCode->code,
            -6.300000,
            106.900000
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('lokasi', strtolower($result['message']));
        $this->assertDatabaseMissing('attendance_records', [
            'employee_id' => $this->employee->id,
        ]);
    }

    /**
     * Test presence outside active shift hours.
     */
    public function test_presence_outside_active_hours()
    {
        // Mock current time to be late at night (22:00) when shift is 09:00 - 18:00
        Carbon::setTestNow(Carbon::today()->setTime(22, 0, 0));

        // Attempt scan from within correct radius
        $result = $this->service->processQRScan(
            $this->employee->id,
            $this->qrCode->code,
            -6.200000,
            106.800000
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('aktif', strtolower($result['message']));
        $this->assertDatabaseMissing('attendance_records', [
            'employee_id' => $this->employee->id,
        ]);
    }

    /**
     * Test presence with invalid / inactive QR Code.
     */
    public function test_presence_using_invalid_qr()
    {
        // Mock current time during shift (10:00)
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));

        // Attempt scan with random non-existent code
        $result = $this->service->processQRScan(
            $this->employee->id,
            'INVALID_QR_XYZ',
            -6.200000,
            106.800000
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('tidak valid', strtolower($result['message']));
    }

    /**
     * Test double presence check-in attempt on the same day/shift.
     */
    public function test_double_presence_attempt()
    {
        // Mock current time during shift (10:00)
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));

        // First scan (should succeed)
        $firstResult = $this->service->processQRScan(
            $this->employee->id,
            $this->qrCode->code,
            -6.200000,
            106.800000
        );

        $this->assertTrue($firstResult['success']);
        $this->assertDatabaseHas('attendance_records', [
            'employee_id' => $this->employee->id,
        ]);

        // Second scan (should fail due to duplicate check-in check)
        $secondResult = $this->service->processQRScan(
            $this->employee->id,
            $this->qrCode->code,
            -6.200000,
            106.800000
        );

        $this->assertFalse($secondResult['success']);
        $this->assertStringContainsString('sudah absen', strtolower($secondResult['message']));
    }

    /**
     * Test concurrent checkout prevention (cannot check out multiple times).
     */
    public function test_concurrent_checkout_prevention()
    {
        // Mock current time to 10:00 for check-in
        Carbon::setTestNow(Carbon::today()->setTime(10, 0, 0));

        // Check in first
        $scanResult = $this->service->processQRScan(
            $this->employee->id,
            $this->qrCode->code,
            -6.200000,
            106.800000
        );
        $this->assertTrue($scanResult['success']);
        $attendanceId = $scanResult['data']['attendance_id'];

        // Mock current time to 17:50 (within early checkout buffer)
        Carbon::setTestNow(Carbon::today()->setTime(17, 50, 0));

        // First checkout (should succeed)
        $firstCheckout = $this->service->processCheckout(
            $this->employee->id,
            $attendanceId,
            $this->qrCode->code,
            -6.200000,
            106.800000
        );
        $this->assertTrue($firstCheckout['success']);

        // Second checkout (should fail)
        $secondCheckout = $this->service->processCheckout(
            $this->employee->id,
            $attendanceId,
            $this->qrCode->code,
            -6.200000,
            106.800000
        );
        $this->assertFalse($secondCheckout['success']);
        $this->assertStringContainsString('sudah pulang', strtolower($secondCheckout['message']));
    }
}
