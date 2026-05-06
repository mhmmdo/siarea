<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\SalaryCalculation;
use Carbon\Carbon;

class SalaryService
{
    /**
     * Calculate salary for a specific employee and period
     */
    public function calculateSalary(int $employeeId, $month, $year): ?SalaryCalculation
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return null;
        }

        return SalaryCalculation::calculateForPeriod(
            $employeeId,
            $month,
            $year
        );
    }

    /**
     * Calculate salary for all active employees for a period
     */
    public function calculateSalaryForAllEmployees($periodStartDate, $periodEndDate): array
    {
        $employees = Employee::where('status', 'active')->get();
        $results = [];

        foreach ($employees as $employee) {
            $salary = $this->calculateSalary($employee->id, $periodStartDate, $periodEndDate);
            if ($salary) {
                $results[] = $salary;
            }
        }

        return $results;
    }

    /**
     * Get salary calculation for an employee
     */
    public function getSalaryCalculation(int $employeeId, $month, $year): ?SalaryCalculation
    {
        $periodDate = Carbon::createFromDate($year, $month, 1);
        return SalaryCalculation::where('employee_id', $employeeId)
            ->where('period_date', $periodDate)
            ->first();
    }

    /**
     * Get all salary calculations for an employee
     */
    public function getEmployeeSalaryHistory(int $employeeId, $limit = 12): array
    {
        return SalaryCalculation::where('employee_id', $employeeId)
            ->orderBy('period_date', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * Approve salary calculation
     */
    public function approveSalary(int $salaryCalculationId): bool
    {
        $salary = SalaryCalculation::find($salaryCalculationId);
        if (!$salary) {
            return false;
        }

        return $salary->approve();
    }

    /**
     * Mark salary as paid
     */
    public function markSalaryAsPaid(int $salaryCalculationId): bool
    {
        $salary = SalaryCalculation::find($salaryCalculationId);
        if (!$salary) {
            return false;
        }

        if (!$salary->isApproved()) {
            return false;
        }

        return $salary->markAsPaid();
    }

    /**
     * Get salary summary for payroll processing
     */
    public function getPayrollSummary($month, $year): array
    {
        $periodDate = Carbon::createFromDate($year, $month, 1);
        $salaries = SalaryCalculation::where('period_date', $periodDate)
            ->with('employee.user')
            ->get();

        $summary = [
            'period_date' => $periodDate->format('F Y'),
            'total_employees' => $salaries->count(),
            'total_base_salary' => $salaries->sum('base_salary'),
            'total_deductions' => $salaries->sum('total_deduction'),
            'total_final_salary' => $salaries->sum('final_salary'),
            'approved_count' => $salaries->where('status', 'approved')->count(),
            'paid_count' => $salaries->where('status', 'paid')->count(),
            'draft_count' => $salaries->where('status', 'draft')->count(),
            'salaries' => $salaries->map(fn ($s) => [
                'employee_id' => $s->employee_id,
                'employee_name' => $s->employee->full_name,
                'base_salary' => $s->base_salary,
                'total_deduction' => $s->total_deduction,
                'final_salary' => $s->final_salary,
                'status' => $s->status,
                'paid_at' => $s->paid_at?->format('Y-m-d H:i'),
            ])->toArray(),
        ];

        return $summary;
    }

    /**
     * Get current month salary calculation
     */
    public function getCurrentMonthSalary(int $employeeId): ?SalaryCalculation
    {
        $now = now();
        return $this->getSalaryCalculation($employeeId, $now->month, $now->year);
    }

    /**
     * Bulk approve salaries
     */
    public function bulkApproveSalaries($salaryIds): array
    {
        $approved = [];
        $failed = [];

        foreach ($salaryIds as $id) {
            if ($this->approveSalary($id)) {
                $approved[] = $id;
            } else {
                $failed[] = $id;
            }
        }

        return [
            'approved_count' => count($approved),
            'failed_count' => count($failed),
            'approved_ids' => $approved,
            'failed_ids' => $failed,
        ];
    }

    /**
     * Bulk mark salaries as paid
     */
    public function bulkMarkAsPaid($salaryIds): array
    {
        $paid = [];
        $failed = [];

        foreach ($salaryIds as $id) {
            if ($this->markSalaryAsPaid($id)) {
                $paid[] = $id;
            } else {
                $failed[] = $id;
            }
        }

        return [
            'paid_count' => count($paid),
            'failed_count' => count($failed),
            'paid_ids' => $paid,
            'failed_ids' => $failed,
        ];
    }
}
