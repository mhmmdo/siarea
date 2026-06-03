<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\SalaryCalculation;
use App\Models\QrCode;
use App\Services\AttendanceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Show user dashboard
     */
    public function dashboard()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect('/login')->with('error', 'Employee record not found');
        }

        // Today's attendance
        $todayAttendance = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->with('shift')
            ->first();

        // Check if can checkout (past shift end time)
        $canCheckout = false;
        $shiftEndTime = null;
        $timeUntilCheckout = null;

        if ($todayAttendance && !$todayAttendance->hasCheckedOut()) {
            $shiftEndTime = $todayAttendance->shift->end_time;
            $currentTime = now()->format('H:i:s');
            
            // Handle overnight shifts
            if ($shiftEndTime < $todayAttendance->shift->start_time) {
                // Overnight shift: check if current time is >= end time OR in early morning part
                $currentTimeMinutes = $this->timeToMinutes($currentTime);
                $endTimeMinutes = $this->timeToMinutes($shiftEndTime);
                
                if ($currentTimeMinutes >= $endTimeMinutes) {
                    $canCheckout = true;
                }
            } else {
                // Normal shift: check if current time >= end time
                if ($currentTime >= $shiftEndTime) {
                    $canCheckout = true;
                } else {
                    // Calculate time remaining until checkout
                    $endMinutes = $this->timeToMinutes($shiftEndTime);
                    $currentMinutes = $this->timeToMinutes($currentTime);
                    $remainingMinutes = $endMinutes - $currentMinutes;
                    
                    $hours = intdiv($remainingMinutes, 60);
                    $minutes = $remainingMinutes % 60;
                    $timeUntilCheckout = sprintf('%02d:%02d', $hours, $minutes);
                }
            }
        }

        // This month stats
        $monthAttendance = AttendanceRecord::where('employee_id', $employee->id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->count();

        // Latest salary
        $latestSalary = SalaryCalculation::where('employee_id', $employee->id)
            ->latest('period_date')
            ->first();

        return view('user.dashboard', [
            'employee' => $employee,
            'todayAttendance' => $todayAttendance,
            'monthAttendance' => $monthAttendance,
            'latestSalary' => $latestSalary,
            'canCheckout' => $canCheckout,
            'shiftEndTime' => $shiftEndTime,
            'timeUntilCheckout' => $timeUntilCheckout,
        ]);
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

    /**
     * Show attendance history
     */
    public function attendance()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect('/login')->with('error', 'Employee record not found');
        }

        $attendance = AttendanceRecord::where('employee_id', $employee->id)
            ->orderBy('date', 'desc')
            ->paginate(15);

        return view('user.attendance', [
            'employee' => $employee,
            'attendance' => $attendance,
        ]);
    }

    /**
     * Show salary information
     */
    public function salary()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect('/login')->with('error', 'Employee record not found');
        }

        $salaries = SalaryCalculation::where('employee_id', $employee->id)
            ->orderBy('period_date', 'desc')
            ->paginate(12);

        return view('user.salary', [
            'employee' => $employee,
            'salaries' => $salaries,
        ]);
    }

    /**
     * Show user profile
     */
    public function profile()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect('/login')->with('error', 'Employee record not found');
        }

        return view('user.profile', [
            'user' => $user,
            'employee' => $employee,
        ]);
    }

    /**
     * Show QR scan page (Check-in or Checkout)
     */
    public function scan()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect('/login')->with('error', 'Employee record not found');
        }

        // Get today's attendance record
        $todayAttendance = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->with('shift', 'checkout')
            ->first();

        // Check if can checkout (past shift end time)
        $canCheckout = false;
        $shiftEndTime = null;
        $timeUntilCheckout = null;

        if ($todayAttendance && !$todayAttendance->hasCheckedOut()) {
            $shiftEndTime = $todayAttendance->shift->end_time;
            $currentTime = now()->format('H:i:s');
            
            // Handle overnight shifts
            if ($shiftEndTime < $todayAttendance->shift->start_time) {
                // Overnight shift: check if current time is >= end time OR in early morning part
                $currentTimeMinutes = $this->timeToMinutes($currentTime);
                $endTimeMinutes = $this->timeToMinutes($shiftEndTime);
                
                if ($currentTimeMinutes >= $endTimeMinutes) {
                    $canCheckout = true;
                }
            } else {
                // Normal shift: check if current time >= end time
                if ($currentTime >= $shiftEndTime) {
                    $canCheckout = true;
                } else {
                    // Calculate time remaining until checkout
                    $endMinutes = $this->timeToMinutes($shiftEndTime);
                    $currentMinutes = $this->timeToMinutes($currentTime);
                    $remainingMinutes = $endMinutes - $currentMinutes;
                    
                    $hours = intdiv($remainingMinutes, 60);
                    $minutes = $remainingMinutes % 60;
                    $timeUntilCheckout = sprintf('%02d:%02d', $hours, $minutes);
                }
            }
        }

        // Get active QR codes
        $qrCodes = QrCode::where('is_active', true)->get();

        return view('user.scan', [
            'employee' => $employee,
            'todayAttendance' => $todayAttendance,
            'qrCodes' => $qrCodes,
            'canCheckout' => $canCheckout,
            'shiftEndTime' => $shiftEndTime,
            'timeUntilCheckout' => $timeUntilCheckout,
        ]);
    }

    /**
     * Show QR checkout scan page
     */
    public function checkoutScan()
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return redirect('/login')->with('error', 'Employee record not found');
        }

        // Get today's attendance record without checkout
        $attendance = AttendanceRecord::where('employee_id', $employee->id)
            ->whereDate('date', today())
            ->doesntHave('checkout')
            ->first();

        if (!$attendance) {
            return redirect('/dashboard')->with('error', 'Tidak ada absensi masuk hari ini atau sudah check-out');
        }

        // Get active QR codes
        $qrCodes = QrCode::where('is_active', true)->get();

        return view('user.checkout', [
            'employee' => $employee,
            'attendance' => $attendance,
            'qrCodes' => $qrCodes,
        ]);
    }

    /**
     * Submit attendance scan
     */
    public function submitScan(Request $request)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            return back()->with('error', 'Employee record not found');
        }

        $validated = $request->validate([
            'qr_code' => 'required|string|exists:qr_codes,code',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Use AttendanceService to process attendance
        $service = new AttendanceService();
        $result = $service->processQRScan(
            $employee->id,
            $validated['qr_code'],
            $validated['latitude'],
            $validated['longitude']
        );

        if ($result['success']) {
            return back()->with('success', $result['message']);
        } else {
            return back()->withErrors(['error' => $result['message']]);
        }
    }

    /**
     * Submit attendance checkout
     */
    public function submitCheckout(Request $request, $attendanceId)
    {
        $user = Auth::user();
        $employee = Employee::where('user_id', $user->id)->first();

        if (!$employee) {
            $message = 'Employee record not found';
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $message], 404)
                : back()->with('error', $message);
        }

        $validated = $request->validate([
            'qr_code' => 'required|string|exists:qr_codes,code',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // Use AttendanceService to process checkout
        $service = new AttendanceService();
        $result = $service->processCheckout(
            $employee->id,
            $attendanceId,
            $validated['qr_code'],
            $validated['latitude'],
            $validated['longitude']
        );

        if ($result['success']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect' => route('user.dashboard')
                ]);
            }
            return back()->with('success', $result['message']);
        } else {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }
            return back()->withErrors(['error' => $result['message']]);
        }
    }
}
