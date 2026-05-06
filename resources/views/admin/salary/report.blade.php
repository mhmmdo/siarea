@extends('layouts.app')

@section('title', 'Laporan Gaji - SIAREA')

@push('styles')
<style>
    /* Styling Khusus Halaman Laporan Gaji */
    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        border: 1px solid var(--cafe-border);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover { transform: translateY(-3px); }

    .stat-label {
        font-size: 0.85rem;
        color: #888;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .stat-number {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
        font-family: 'Playfair Display', serif;
    }

    /* Ikon Khusus Card */
    .stat-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }

    .status-card {
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        border-left: 4px solid;
    }

    .status-draft { border-left-color: #ffc107; }
    .status-approved { border-left-color: #0dcaf0; }
    .status-paid { border-left-color: #198754; }

    /* Tabel Keuangan */
    .table-financial td {
        vertical-align: middle;
        padding: 1rem 0.5rem;
    }

    .amount-cell {
        font-family: 'Inter', monospace;
        font-weight: 600;
        letter-spacing: -0.5px;
    }

    .table-avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(184, 134, 11, 0.08);
        color: var(--cafe-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        border: 1px solid rgba(184, 134, 11, 0.2);
    }
</style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Laporan Gaji</h1>
            <p class="text-muted mb-0">Rekapitulasi keuangan periode <strong class="text-dark">{{ \Carbon\Carbon::create(now()->year, $month, 1)->translatedFormat('F') }} {{ $year }}</strong></p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.salary.index') }}" class="btn btn-light shadow-sm border px-3 py-2 text-muted d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <form action="{{ route('admin.salary.export') }}" method="GET" class="m-0">
                <input type="hidden" name="month" value="{{ $month }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <button type="submit" class="btn btn-success shadow-sm px-4 py-2 d-flex align-items-center gap-2" style="background-color: #2E8B57; border-color: #2E8B57;">
                    <i class="bi bi-cloud-arrow-down-fill"></i> Export CSV
                </button>
            </form>
        </div>
    </div>

    <div class="row mb-4 gx-3">
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="stat-card">
                <div class="stat-header">
                    <p class="stat-label mb-0">Total Karyawan</p>
                    <div class="stat-icon" style="background: rgba(184, 134, 11, 0.1); color: var(--cafe-primary);"><i class="bi bi-people"></i></div>
                </div>
                <p class="stat-number" style="color: var(--cafe-secondary);">{{ $summary['total_employees'] ?? 0 }} <span class="fs-6 text-muted font-sans-serif">Orang</span></p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3 mb-lg-0">
            <div class="stat-card">
                <div class="stat-header">
                    <p class="stat-label mb-0">Total Gaji Pokok</p>
                    <div class="stat-icon" style="background: rgba(108, 117, 125, 0.1); color: #6c757d;"><i class="bi bi-cash-stack"></i></div>
                </div>
                <p class="stat-number" style="color: #555;">
                    <span class="fs-5 text-muted me-1">Rp</span>{{ number_format($summary['total_base_salary'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-3 mb-md-0">
            <div class="stat-card" style="background: linear-gradient(to right, #fff, rgba(220, 53, 69, 0.02)); border-color: rgba(220, 53, 69, 0.1);">
                <div class="stat-header">
                    <p class="stat-label mb-0 text-danger">Total Potongan</p>
                    <div class="stat-icon" style="background: rgba(220, 53, 69, 0.1); color: #dc3545;"><i class="bi bi-graph-down-arrow"></i></div>
                </div>
                <p class="stat-number text-danger">
                    <span class="fs-5 opacity-75 me-1">- Rp</span>{{ number_format($summary['total_deduction'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card" style="background: linear-gradient(to bottom right, #fff, rgba(184, 134, 11, 0.05)); border-color: var(--cafe-primary);">
                <div class="stat-header">
                    <p class="stat-label mb-0" style="color: var(--cafe-primary);">Total Gaji Bersih</p>
                    <div class="stat-icon" style="background: var(--cafe-primary); color: white;"><i class="bi bi-wallet2"></i></div>
                </div>
                <p class="stat-number" style="color: var(--cafe-secondary);">
                    <span class="fs-5 opacity-75 me-1">Rp</span>{{ number_format($summary['total_final_salary'] ?? 0, 0, ',', '.') }}
                </p>
            </div>
        </div>
    </div>

    <h6 class="fw-bold mb-3" style="color: var(--cafe-secondary);"><i class="bi bi-pie-chart-fill me-2 text-cafe"></i> Distribusi Status Pembayaran</h6>
    <div class="row mb-4 gx-3">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card status-card status-draft">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Status Draft</h6>
                        <span class="text-muted small">Menunggu persetujuan</span>
                    </div>
                    <h3 class="mb-0 fw-bold text-warning">{{ $summary['draft_count'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="card status-card status-approved">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Disetujui</h6>
                        <span class="text-muted small">Menunggu pencairan</span>
                    </div>
                    <h3 class="mb-0 fw-bold text-info">{{ $summary['approved_count'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card status-card status-paid">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted text-uppercase small fw-bold mb-1">Lunas Dibayar</h6>
                        <span class="text-muted small">Gaji sudah ditransfer</span>
                    </div>
                    <h3 class="mb-0 fw-bold text-success">{{ $summary['paid_count'] ?? 0 }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                <i class="bi bi-table me-2 text-cafe"></i> Rincian Penggajian per Karyawan
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                @if (!empty($salaries) && count($salaries) > 0)
                    <table class="table table-borderless table-financial align-middle mb-0">
                        <thead style="background-color: rgba(248, 246, 243, 0.5); border-bottom: 1px solid var(--cafe-border);">
                            <tr>
                                <th class="ps-4" style="width: 5%;">#</th>
                                <th>Nama Karyawan</th>
                                <th class="text-end">Gaji Pokok</th>
                                <th class="text-end">Total Denda</th>
                                <th class="text-end">Gaji Bersih</th>
                                <th class="text-center pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salaries as $salary)
                                <tr style="border-bottom: 1px solid var(--cafe-border);">
                                    <td class="ps-4 text-muted fw-medium">{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-avatar-sm shadow-sm">
                                                {{ strtoupper(substr($salary->employee->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <a href="{{ route('admin.salary.show', $salary->id) }}" class="text-decoration-none fw-semibold text-dark hover-cafe">
                                                {{ $salary->employee->user->name ?? '-' }}
                                            </a>
                                        </div>
                                    </td>
                                    <td class="text-end text-muted amount-cell">
                                        Rp {{ number_format($salary->base_salary ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end amount-cell">
                                        @if($salary->total_deduction > 0)
                                            <span class="text-danger fw-semibold opacity-75">- Rp {{ number_format($salary->total_deduction ?? 0, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-muted opacity-50">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end amount-cell">
                                        <span class="fw-bold" style="color: var(--cafe-primary); font-size: 1.05rem;">
                                            Rp {{ number_format($salary->final_salary ?? 0, 0, ',', '.') }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-4">
                                        @if ($salary->status === 'draft')
                                            <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3 py-1 border border-warning border-opacity-25">Draft</span>
                                        @elseif ($salary->status === 'approved')
                                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info px-3 py-1 border border-info border-opacity-25">Disetujui</span>
                                        @elseif ($salary->status === 'paid')
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1 border border-success border-opacity-25">Dibayar</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-receipt fs-1 d-block mb-3 opacity-25"></i>
                                        <h6 class="fw-bold text-dark">Data Kosong</h6>
                                        <p class="mb-0">Tidak ada data penggajian untuk periode bulan ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-wallet2 fs-1 d-block mb-3 opacity-25"></i>
                        <h6 class="fw-bold text-dark">Data Belum Dihitung</h6>
                        <p class="mb-0">Tidak ada rincian gaji yang dapat ditampilkan. Silakan hitung gaji terlebih dahulu.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <style>
        .hover-cafe:hover { color: var(--cafe-primary) !important; text-decoration: underline !important; }
    </style>
@endsection