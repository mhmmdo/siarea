@extends('layouts.app')

@section('title', 'Dashboard - SIAREA')

@push('styles')
<style>
    /* Styling khusus halaman Dashboard untuk aksen Premium */
    .welcome-banner {
        background: linear-gradient(135deg, var(--cafe-secondary) 0%, #3a2a22 100%);
        border-radius: 16px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(33, 25, 21, 0.2);
    }
    
    .welcome-banner::after {
        content: '\F2E4'; /* Ikon cangkir kopi Bootstrap */
        font-family: 'bootstrap-icons';
        position: absolute;
        right: -10px;
        bottom: -45px;
        font-size: 12rem;
        opacity: 0.03;
        color: #ffffff;
        transform: rotate(-15deg);
        pointer-events: none;
    }

    .icon-box {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        font-size: 1.5rem;
    }

    .icon-box.gold { background: rgba(184, 134, 11, 0.1); color: var(--cafe-primary); }
    .icon-box.emerald { background: rgba(46, 139, 87, 0.1); color: #2E8B57; }
    .icon-box.ruby { background: rgba(178, 34, 34, 0.1); color: #B22222; }

    .action-card {
        border: 1px solid var(--cafe-border);
        border-radius: 14px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        background: white;
        text-decoration: none;
        color: var(--cafe-dark);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .action-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 24px rgba(184, 134, 11, 0.12);
        border-color: var(--cafe-primary);
        color: var(--cafe-primary);
    }

    .table-avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: var(--cafe-light);
        color: var(--cafe-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        border: 1px solid var(--cafe-border);
    }

    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #a09c98;
    }
</style>
@endpush

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="welcome-banner p-4 p-md-5 text-white">
                <div class="position-relative" style="z-index: 2;">
                    <p class="mb-1 opacity-75 fw-medium">Overview Hari Ini</p>
                    <h2 class="fw-bold mb-3" style="font-family: 'Playfair Display', serif; font-size: 2.25rem;">
                        Selamat Datang, di SIAREA {{ explode(' ', Auth::user()->name)[0] }}!
                    </h2>
                    <p class="mb-0 opacity-75">Pantau absensi karyawan dan kelola operasional kafe dengan mudah.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Total Karyawan</p>
                        <p class="stat-number">{{ $stats['total_employees'] ?? 0 }}</p>
                    </div>
                    <div class="icon-box gold">
                        <i class="bi bi-people-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Hadir Hari Ini</p>
                        <p class="stat-number" style="color: #2E8B57;">{{ $stats['today_present'] ?? 0 }}</p>
                    </div>
                    <div class="icon-box emerald">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Terlambat Hari Ini</p>
                        <p class="stat-number" style="color: #B22222;">{{ $stats['today_late'] ?? 0 }}</p>
                    </div>
                    <div class="icon-box ruby">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stat-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Bulan Ini</p>
                        <p class="stat-number" style="color: var(--cafe-primary);">
                            {{ $stats['month_present'] ?? 0 }}<span class="fs-6 text-muted">/{{ $stats['month_total'] ?? 0 }}</span>
                        </p>
                    </div>
                    <div class="icon-box gold">
                        <i class="bi bi-calendar2-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h5 class="fw-bold mb-3" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">Akses Cepat</h5>
            <div class="row g-3">
                <div class="col-md-3">
                    <a href="{{ route('admin.employees.create') }}" class="action-card">
                        <div class="icon-box gold" style="width: 40px; height: 40px; font-size: 1.2rem;"><i class="bi bi-person-plus"></i></div>
                        <span class="fw-semibold">Tambah Karyawan</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.qr.create') }}" class="action-card">
                        <div class="icon-box gold" style="width: 40px; height: 40px; font-size: 1.2rem;"><i class="bi bi-qr-code-scan"></i></div>
                        <span class="fw-semibold">Generate QR</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.salary.calculate') }}" class="action-card">
                        <div class="icon-box gold" style="width: 40px; height: 40px; font-size: 1.2rem;"><i class="bi bi-calculator"></i></div>
                        <span class="fw-semibold">Hitung Gaji</span>
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('admin.attendance.report') }}" class="action-card">
                        <div class="icon-box gold" style="width: 40px; height: 40px; font-size: 1.2rem;"><i class="bi bi-file-earmark-bar-graph"></i></div>
                        <span class="fw-semibold">Rekap Laporan</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white p-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary);">
                        <i class="bi bi-clock-history me-2 text-cafe"></i> Absensi Terbaru
                    </h6>
                </div>
                <div class="card-body px-0 pt-0">
                    @if (!empty($recent_attendance) && count($recent_attendance) > 0)
                        <div class="table-responsive px-4">
                            <table class="table table-borderless align-middle mb-0">
                                <thead class="border-bottom">
                                    <tr>
                                        <th class="ps-0">Karyawan</th>
                                        <th>Waktu</th>
                                        <th class="text-end pe-0">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recent_attendance as $record)
                                        <tr>
                                            <td class="ps-0">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="table-avatar">
                                                        {{ strtoupper(substr($record['employee_name'] ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <span class="fw-semibold text-dark">{{ $record['employee_name'] ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted fw-medium"><i class="bi bi-clock me-1"></i> {{ $record['check_in_time'] ?? '-' }}</span>
                                            </td>
                                            <td class="text-end pe-0">
                                                @if ($record['is_late'])
                                                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3 py-2">Terlambat</span>
                                                @else
                                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2">Tepat Waktu</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-inbox fs-1 mb-3 d-block opacity-50"></i>
                            <p class="mb-0">Belum ada karyawan yang absen hari ini.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100">
                <div class="card-header bg-white p-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary);">
                        <i class="bi bi-exclamation-triangle me-2 text-danger"></i> Log Keterlambatan
                    </h6>
                </div>
                <div class="card-body px-0 pt-0">
                    @if (!empty($recent_late) && count($recent_late) > 0)
                        <div class="table-responsive px-4">
                            <table class="table table-borderless align-middle mb-0">
                                <thead class="border-bottom">
                                    <tr>
                                        <th class="ps-0">Karyawan</th>
                                        <th>Keterlambatan</th>
                                        <th class="text-end pe-0">Potongan (Rp)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recent_late as $record)
                                        <tr>
                                            <td class="ps-0">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="table-avatar" style="color: #B22222; background: rgba(178, 34, 34, 0.05); border-color: rgba(178, 34, 34, 0.1);">
                                                        {{ strtoupper(substr($record['employee_name'] ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <span class="fw-semibold text-dark">{{ $record['employee_name'] ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-danger fw-medium">{{ $record['duration_minutes'] ?? 0 }} Menit</span>
                                            </td>
                                            <td class="text-end pe-0">
                                                <span class="fw-bold" style="color: var(--cafe-secondary);">
                                                    -{{ number_format($record['late_deduction_amount'] ?? 0, 0, ',', '.') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-check2-circle fs-1 mb-3 d-block text-success opacity-50"></i>
                            <p class="mb-0">Luar biasa! Tidak ada yang terlambat.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection