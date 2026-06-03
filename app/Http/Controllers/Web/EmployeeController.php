<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use App\Models\Shift;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    /**
     * Show employees list
     */
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'shift']);

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($request->has('shift_id')) {
            $query->where('shift_id', $request->input('shift_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        $employees = $query->paginate(15);
        $shifts = Shift::all();
    
        return view('admin.employees.index', [
            'employees' => $employees,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Show create employee form
     */
    public function create()
    {
        $shifts = Shift::all();

        return view('admin.employees.create', [
            'shifts' => $shifts,
        ]);
    }

    /**
     * Store new employee
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'phone' => 'sometimes|string|max:20',
            'shift_id' => 'required|exists:shifts,id',
            'base_salary' => 'required|numeric|min:0',
            'late_deduction_amount' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        // Create user first
        $password = $validated['email']; // Use email as password initially
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($password),
            'role' => 'employee',
            'status' => 'active',
        ]);

        // Create employee
        $employee = Employee::create([
            'user_id' => $user->id,
            'shift_id' => $validated['shift_id'],
            'full_name' => $validated['name'],
            'phone' => $validated['phone'] ?? '',
            'basic_salary' => $validated['base_salary'],
            'late_deduction_amount' => $validated['late_deduction_amount'] ?? 10000,
            'hire_date' => now()->format('Y-m-d'),
            'status' => isset($validated['is_active']) && !$validated['is_active'] ? 'inactive' : 'active',
        ]);

        return redirect()->route('admin.employees.show', $employee->id)
            ->with('success', 'Karyawan berhasil dibuat')
            ->with('tempPassword', $password);
    }

    /**
     * Show employee detail
     */
    public function show(int $id)
    {
        $employee = Employee::with(['user', 'shift'])
            ->findOrFail($id);

        // Get this month attendance
        $startDate = now()->startOfMonth();
        $endDate = now()->endOfMonth();
        
        $attendance = AttendanceRecord::where('employee_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['shift'])
            ->orderBy('date', 'desc')
            ->get();

        $attendance_count = $attendance->count();
        $late_count = $attendance->where('is_late', true)->count();

        // Format attendance for template
        $attendance_records = $attendance->map(function($rec) {
            return [
                'date' => $rec->date,
                'shift_name' => $rec->shift->name ?? '-',
                'time' => $rec->check_in_time ? $rec->check_in_time->format('H:i') : '-',
                'is_late' => $rec->is_late,
            ];
        })->toArray();

        return view('admin.employees.show', [
            'employee' => $employee,
            'attendance_count' => $attendance_count,
            'late_count' => $late_count,
            'attendance_records' => $attendance_records,
        ]);
    }

    /**
     * Show edit employee form
     */
    public function edit(int $id)
    {
        $employee = Employee::findOrFail($id);
        $shifts = Shift::all();

        return view('admin.employees.edit', [
            'employee' => $employee,
            'shifts' => $shifts,
        ]);
    }

    /**
     * Update employee
     */
    public function update(Request $request, int $id)
    {
        $employee = Employee::with('user')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string|max:20',
            'shift_id' => 'sometimes|exists:shifts,id',
            'base_salary' => 'sometimes|numeric|min:0',
            'late_deduction_amount' => 'sometimes|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        // Update user
        if (isset($validated['name']) || isset($validated['email'])) {
            $employee->user->update([
                'name' => $validated['name'] ?? $employee->user->name,
                'email' => $validated['email'] ?? $employee->user->email,
            ]);
        }

        // Update employee
        $employeeData = [];
        if (isset($validated['name'])) $employeeData['full_name'] = $validated['name'];
        if (isset($validated['phone'])) $employeeData['phone'] = $validated['phone'];
        if (isset($validated['shift_id'])) $employeeData['shift_id'] = $validated['shift_id'];
        if (isset($validated['base_salary'])) $employeeData['basic_salary'] = $validated['base_salary'];
        if (isset($validated['late_deduction_amount'])) $employeeData['late_deduction_amount'] = $validated['late_deduction_amount'];
        if (isset($validated['is_active'])) $employeeData['status'] = $validated['is_active'] ? 'active' : 'inactive';

        $employee->update($employeeData);

        return redirect()->route('admin.employees.show', $employee->id)
            ->with('success', 'Data karyawan berhasil diupdate');
    }

    /**
     * Delete employee
     */
    public function destroy(int $id)
    {
        $employee = Employee::findOrFail($id);
        $user = $employee->user;

        $employee->delete();
        $user->delete();

        return redirect()->route('admin.employees.index')
            ->with('success', 'Employee berhasil dihapus');
    }

    /**
     * Show employee attendance history
     */
    public function attendance(Request $request, int $id)
    {
        $employee = Employee::findOrFail($id);

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $startDate = now()->setYear($year)->setMonth($month)->startOfMonth();
        $endDate = now()->setYear($year)->setMonth($month)->endOfMonth();

        $records = AttendanceRecord::where('employee_id', $id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['shift', 'lateRecord'])
            ->orderBy('date', 'asc')
            ->paginate(31);

        return view('admin.employees.attendance', [
            'employee' => $employee,
            'records' => $records,
            'month' => $month,
            'year' => $year,
        ]);
    }
}
