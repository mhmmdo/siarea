<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SalaryCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'period_date',
        'base_salary',
        'total_deduction',
        'final_salary',
        'status',
        'notes',
        'approved_at',
        'paid_at',
    ];

    protected $casts = [
        'period_date' => 'date',
        'base_salary' => 'decimal:2',
        'total_deduction' => 'decimal:2',
        'final_salary' => 'decimal:2',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Relation: Salary calculation belongs to Employee
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Mark salary as approved
     */
    public function approve(): bool
    {
        return $this->update(['status' => 'approved']);
    }

    /**
     * Mark salary as paid
     */
    public function markAsPaid(): bool
    {
        return $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    /**
     * Check if salary is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if salary is paid
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Get formatted final salary for display
     */
    public function getFormattedFinalSalaryAttribute(): string
    {
        return 'Rp ' . number_format($this->final_salary, 0, ',', '.');
    }

    /**
     * Get formatted total deduction for display
     */
    public function getFormattedTotalDeductionAttribute(): string
    {
        return 'Rp ' . number_format($this->total_deduction, 0, ',', '.');
    }

    /**
     * Get formatted base salary for display
     */
    public function getFormattedBaseSalaryAttribute(): string
    {
        return 'Rp ' . number_format($this->base_salary, 0, ',', '.');
    }

    /**
     * Calculate and save salary for a period
     * This is typically called after late records are finalized
     */
    public static function calculateForPeriod($employeeId, $periodMonth, $periodYear)
    {
        $employee = Employee::find($employeeId);
        if (!$employee) {
            return false;
        }

        // Create first day of period for period_date
        $periodDate = Carbon::createFromDate($periodYear, $periodMonth, 1);
        $startDate = $periodDate->copy()->startOfMonth();
        $endDate = $periodDate->copy()->endOfMonth();

        // Get total deduction from late records in this period
        $totalDeduction = $employee->getTotalDeductionInPeriod($startDate, $endDate);

        // Create or update salary calculation
        return SalaryCalculation::updateOrCreate(
            [
                'employee_id' => $employeeId,
                'period_date' => $periodDate,
            ],
            [
                'base_salary' => $employee->basic_salary,
                'total_deduction' => $totalDeduction,
                'final_salary' => $employee->basic_salary - $totalDeduction,
                'status' => 'draft',
            ]
        );
    }
}
