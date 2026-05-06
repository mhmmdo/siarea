<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\LateRecord;
use App\Models\QrCode;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function dashboard()
    {
        $totalEmployees = Employee::where('status', 'active')->count();
        
        // Today statistics - HADIR = all attendance (on time + late)
        $todayPresent = AttendanceRecord::whereDate('date', today())->count();
        
        // TERLAMBAT = attendance with is_late = true
        $todayLate = AttendanceRecord::whereDate('date', today())
            ->where('is_late', true)
            ->count();

        // This month statistics
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        
        // Month present = all attendance this month
        $monthPresent = AttendanceRecord::whereBetween('date', [$startDate, $endDate])->count();
        $monthTotal = AttendanceRecord::whereBetween('date', [$startDate, $endDate])->count();

        // Recent attendance
        $recentAttendanceObj = AttendanceRecord::with(['employee.user', 'shift'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent late records
        $recentLateObj = LateRecord::with(['employee.user', 'shift'])
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get();

        // Format for view
        $recentAttendance = $recentAttendanceObj->map(function($rec) {
            return [
                'employee_name' => $rec->employee->user->name ?? '-',
                'check_in_time' => $rec->check_in_time->format('H:i') ?? '-',
                'is_late' => $rec->is_late,
            ];
        })->toArray();

        $recentLate = $recentLateObj->map(function($late) {
            return [
                'employee_name' => $late->employee->user->name ?? '-',
                'duration_minutes' => $late->duration_minutes ?? 0,
                'late_deduction_amount' => $late->late_deduction_amount ?? 0,
            ];
        })->toArray();

        $stats = [
            'total_employees' => $totalEmployees,
            'today_present' => $todayPresent,
            'today_late' => $todayLate,
            'month_present' => $monthPresent,
            'month_total' => $monthTotal,
        ];

        return view('admin.dashboard', [
            'stats' => $stats,
            'recent_attendance' => $recentAttendance,
            'recent_late' => $recentLate,
        ]);
    }

    /**
     * Show attendance report
     */
    public function attendanceReport(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
        $employeeId = $request->input('employee_id');
        $status = $request->input('status');

        $query = AttendanceRecord::whereBetween('date', [$startDate, $endDate])
            ->with(['employee', 'shift', 'lateRecord']);

        if ($employeeId) {
            $query->where('employee_id', $employeeId);
        }

        if ($status === 'late') {
            $query->where('is_late', true);
        } elseif ($status === 'ontime') {
            $query->where('is_late', false);
        }

        $records = $query->paginate(15);
        $employees = Employee::where('status', 'active')->get();

        return view('admin.attendance-report', [
            'records' => $records,
            'employees' => $employees,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'employeeId' => $employeeId,
            'status' => $status,
        ]);
    }
}
