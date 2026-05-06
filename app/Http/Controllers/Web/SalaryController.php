<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\SalaryCalculation;
use App\Services\SalaryService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SalaryController extends Controller
{
    protected $salaryService;

    public function __construct(SalaryService $salaryService)
    {
        $this->salaryService = $salaryService;
    }

    /**
     * Show salary list
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $query = SalaryCalculation::query();

        if ($request->has('employee_id')) {
            $query->where('employee_id', $request->input('employee_id'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($month && $year) {
            $query->whereYear('period_date', $year)
                ->whereMonth('period_date', $month);
        }

        $salaries = $query->with('employee.user')
            ->orderBy('period_date', 'desc')
            ->paginate(15);

        $employees = Employee::with('user')
            ->orderBy('user_id')
            ->get();

        return view('admin.salary.index', [
            'salaries' => $salaries,
            'employees' => $employees,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Show salary detail
     */
    public function show(int $id)
    {
        $salary = SalaryCalculation::with([
            'employee.user',
            'employee.lateRecords',
        ])->findOrFail($id);

        return view('admin.salary.show', [
            'salary' => $salary,
        ]);
    }

    /**
     * Show calculate salary form
     */
    public function calculate(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $employees = Employee::with('user')
            ->where('status', 'active')
            ->orderBy('user_id')
            ->get();

        $existingSalaries = SalaryCalculation::whereYear('period_date', $year)
            ->whereMonth('period_date', $month)
            ->pluck('employee_id')
            ->toArray();

        return view('admin.salary.calculate', [
            'employees' => $employees,
            'month' => $month,
            'year' => $year,
            'existingSalaries' => $existingSalaries,
        ]);
    }

    /**
     * Store calculated salary
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
            'employee_ids' => 'sometimes|array',
            'employee_ids.*' => 'exists:employees,id',
        ]);

        $month = $validated['month'];
        $year = $validated['year'];
        $employeeIds = $validated['employee_ids'] ?? [];

        $periodDate = Carbon::createFromDate($year, $month, 1);

        if (empty($employeeIds)) {
            // Calculate for all active employees
            $employees = Employee::where('status', 'active')->get();
        } else {
            $employees = Employee::whereIn('id', $employeeIds)->get();
        }

        $count = 0;
        foreach ($employees as $employee) {
            // Check if already exists
            $existing = SalaryCalculation::where('employee_id', $employee->id)
                ->whereYear('period_date', $year)
                ->whereMonth('period_date', $month)
                ->exists();

            if (!$existing) {
                $this->salaryService->calculateSalary($employee->id, $month, $year);
                $count++;
            }
        }

        return redirect()->route('admin.salary.index', [
            'month' => $month,
            'year' => $year,
        ])->with('success', "Gaji untuk $count karyawan berhasil dihitung");
    }

    /**
     * Approve salary
     */
    public function approve(int $id)
    {
        $salary = SalaryCalculation::findOrFail($id);

        if ($salary->status !== 'draft') {
            return redirect()->back()
                ->with('error', 'Hanya gaji dengan status draft yang bisa disetujui');
        }

        $salary->update(['status' => 'approved', 'approved_at' => now()]);

        return redirect()->back()
            ->with('success', 'Gaji berhasil disetujui');
    }

    /**
     * Bulk approve salaries
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'salary_ids' => 'required|array',
            'salary_ids.*' => 'exists:salary_calculations,id',
        ]);

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $updated = SalaryCalculation::whereIn('id', $validated['salary_ids'])
            ->where('status', 'draft')
            ->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

        return redirect()->route('admin.salary.index', [
            'month' => $month,
            'year' => $year,
        ])->with('success', "$updated gaji berhasil disetujui");
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(int $id)
    {
        $salary = SalaryCalculation::findOrFail($id);

        if ($salary->status !== 'approved') {
            return redirect()->back()
                ->with('error', 'Hanya gaji yang sudah disetujui yang bisa ditandai dibayar');
        }

        $salary->update(['status' => 'paid', 'paid_at' => now()]);

        return redirect()->back()
            ->with('success', 'Gaji berhasil ditandai dibayar');
    }

    /**
     * Bulk mark as paid
     */
    public function bulkMarkAsPaid(Request $request)
    {
        $validated = $request->validate([
            'salary_ids' => 'required|array',
            'salary_ids.*' => 'exists:salary_calculations,id',
        ]);

        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $updated = SalaryCalculation::whereIn('id', $validated['salary_ids'])
            ->where('status', 'approved')
            ->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

        return redirect()->route('admin.salary.index', [
            'month' => $month,
            'year' => $year,
        ])->with('success', "$updated gaji berhasil ditandai dibayar");
    }

    /**
     * Show salary report
     */
    public function report(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $salaries = SalaryCalculation::whereYear('period_date', $year)
            ->whereMonth('period_date', $month)
            ->with('employee.user')
            ->orderBy('employee_id')
            ->get();

        $summary = [
            'total_employees' => $salaries->count(),
            'total_base_salary' => $salaries->sum('base_salary'),
            'total_deduction' => $salaries->sum('total_deduction'),
            'total_final_salary' => $salaries->sum('final_salary'),
            'draft_count' => $salaries->where('status', 'draft')->count(),
            'approved_count' => $salaries->where('status', 'approved')->count(),
            'paid_count' => $salaries->where('status', 'paid')->count(),
        ];

        return view('admin.salary.report', [
            'salaries' => $salaries,
            'summary' => $summary,
            'month' => $month,
            'year' => $year,
        ]);
    }

    /**
     * Show employee salary history
     */
    public function employeeHistory(int $employeeId)
    {
        $employee = Employee::with('user')->findOrFail($employeeId);

        $salaries = SalaryCalculation::where('employee_id', $employeeId)
            ->orderBy('period_date', 'desc')
            ->paginate(12);

        return view('admin.salary.employee-history', [
            'employee' => $employee,
            'salaries' => $salaries,
        ]);
    }

    /**
     * Export salary report
     */
    public function export(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $salaries = SalaryCalculation::whereYear('period_date', $year)
            ->whereMonth('period_date', $month)
            ->with('employee.user')
            ->orderBy('employee_id')
            ->get();

        // Generate CSV
        $filename = "salary_report_{$year}_{$month}.csv";
        $handle = fopen('php://memory', 'w');

        // Headers
        fputcsv($handle, [
            'No.',
            'Nama Karyawan',
            'Gaji Pokok',
            'Total Pengurangan',
            'Gaji Akhir',
            'Status',
        ]);

        // Data
        $no = 1;
        foreach ($salaries as $salary) {
            fputcsv($handle, [
                $no++,
                $salary->employee->user->name,
                $salary->base_salary,
                $salary->total_deduction,
                $salary->final_salary,
                ucfirst($salary->status),
            ]);
        }

        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"$filename\"");
    }
}
