<?php

use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\EmployeeController;
use App\Http\Controllers\Web\QRController;
use App\Http\Controllers\Web\SalaryController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ============================================================================
// PUBLIC ROUTES
// ============================================================================
Route::get('/login', [AuthController::class, 'showLogin'])->name('login.show');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ============================================================================
// ADMIN ROUTES - Protected
// ============================================================================
Route::middleware(['auth:web', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Employee Management
    Route::prefix('employees')->group(function () {
        Route::get('/', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/{id}', [EmployeeController::class, 'show'])->name('employees.show');
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
        Route::get('/{id}/attendance', [EmployeeController::class, 'attendance'])->name('employees.attendance');
    });

    // QR Management
    Route::prefix('qr')->group(function () {
        Route::get('/', [QRController::class, 'index'])->name('qr.index');
        Route::get('/create', [QRController::class, 'create'])->name('qr.create');
        Route::post('/', [QRController::class, 'store'])->name('qr.store');
        Route::get('/{id}', [QRController::class, 'show'])->name('qr.show');
        Route::get('/{id}/edit', [QRController::class, 'edit'])->name('qr.edit');
        Route::put('/{id}', [QRController::class, 'update'])->name('qr.update');
        Route::delete('/{id}', [QRController::class, 'destroy'])->name('qr.destroy');
        Route::get('/{id}/statistics', [QRController::class, 'statistics'])->name('qr.statistics');
        Route::get('/{id}/download', [QRController::class, 'download'])->name('qr.download');
    });

    // Salary Management
    Route::prefix('salary')->group(function () {
        Route::get('/', [SalaryController::class, 'index'])->name('salary.index');
        Route::get('/calculate', [SalaryController::class, 'calculate'])->name('salary.calculate');
        Route::post('/', [SalaryController::class, 'store'])->name('salary.store');
        Route::get('/report', [SalaryController::class, 'report'])->name('salary.report');
        Route::get('/export', [SalaryController::class, 'export'])->name('salary.export');
        Route::post('/bulk-approve', [SalaryController::class, 'bulkApprove'])->name('salary.bulk-approve');
        Route::post('/bulk-mark-paid', [SalaryController::class, 'bulkMarkAsPaid'])->name('salary.bulk-mark-paid');
        Route::get('/{id}', [SalaryController::class, 'show'])->where('id', '\d+')->name('salary.show');
        Route::post('/{id}/approve', [SalaryController::class, 'approve'])->where('id', '\d+')->name('salary.approve');
        Route::post('/{id}/mark-paid', [SalaryController::class, 'markAsPaid'])->where('id', '\d+')->name('salary.mark-paid');
        Route::get('/employee/{id}/history', [SalaryController::class, 'employeeHistory'])->where('id', '\d+')->name('salary.employee-history');
    });

    // Reports
    Route::get('/attendance-report', [AdminController::class, 'attendanceReport'])->name('attendance.report');
});

// ============================================================================
// USER ROUTES - Protected
// ============================================================================
Route::middleware(['auth:web'])->prefix('dashboard')->name('user.')->group(function () {
    Route::get('/', [UserController::class, 'dashboard'])->name('dashboard');
    Route::get('/attendance', [UserController::class, 'attendance'])->name('attendance');
    Route::post('/attendance/scan', [UserController::class, 'submitScan'])->name('attendance.scan');
    Route::get('/scan', [UserController::class, 'scan'])->name('scan');
    Route::get('/checkout', [UserController::class, 'checkoutScan'])->name('checkout');
    Route::post('/attendance/{attendanceId}/checkout', [UserController::class, 'submitCheckout'])->name('attendance.checkout');
    Route::get('/salary', [UserController::class, 'salary'])->name('salary');
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
});
