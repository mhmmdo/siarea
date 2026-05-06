@extends('layouts.app')

@section('title', 'Riwayat Gaji Karyawan - SIAREA')

@push('styles')
<style>
    /* Styling Khusus Riwayat Gaji */
    .profile-banner {
        background: linear-gradient(135deg, var(--cafe-secondary) 0%, #3a2a22 100%);
        border-radius: 16px;
        padding: 2rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(33, 25, 21, 0.15);
    }

    .avatar-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: white;
        padding: 3px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        border: 1px solid var(--cafe-border);
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        height: 100%;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        transition: transform 0.3s ease;
    }
    
    .stat-card:hover { transform: translateY(-3px); }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .stat-icon.gold { background: rgba(184, 134, 11, 0.1); color: var(--cafe-primary); }
    .stat-icon.sapphire { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .stat-icon.emerald { background: rgba(46, 139, 87, 0.1); color: #2E8B57; }

    .stat-info .stat-label {
        font-size: 0.85rem;
        color: #888;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }
    
    .stat-info .stat-number {
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
        color: var(--cafe-secondary);
    }

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

    .btn-action {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: none;
        transition: all 0.2s ease;
        background: rgba(13, 202, 240, 0.1); 
        color: #0dcaf0;
    }
    .btn-action:hover { background: #0dcaf0; color: white; transform: translateY(-2px); }

    .empty-state {
        padding: 4rem 1rem;
        text-align: center;
        color: #a09c98;
    }

    /* Custom Pagination */
    .pagination .page-link {
        color: var(--cafe-secondary);
        border-color: var(--cafe-border);
        border-radius: 8px;
        margin: 0 4px;
        transition: all 0.3s ease;
    }
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, var(--cafe-primary), #9E7308);
        border-color: var(--cafe-primary);
        color: white;
    }
</style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Riwayat Penggajian</h1>
            <p class="text-muted mb-0">Catatan pembayaran gaji per periode</p>
        </div>
        <div>
            <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-light shadow-sm border px-3 py-2 text-muted d-flex align-items-center gap-2">
                <i class="bi bi-person-badge"></i> Kembali ke Profil
            </a>
        </div>
    </div>

    <div class="profile-banner">
        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ $employee->user->id }}&backgroundColor=FBF9F6" alt="Avatar" class="avatar-wrapper">
        <div>
            <h3 class="fw-bold mb-1" style="font-family: 'Playfair Display', serif;">{{ $employee->user->name }}</h3>
            <div class="d-flex align-items-center gap-3 opacity-75 small">
                <span><i class="bi bi-envelope me-1"></i> {{ $employee->user->email }}</span>
                <span><i class="bi bi-hash me-1"></i> ID: {{ str_pad($employee->user->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
        </div>
    </div>

    <div class="row mb-4 gx-3">
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Gaji Pokok Dasar</p>
                    <p class="stat-number">Rp {{ number_format($employee->basic_salary ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3 mb-md-0">
            <div class="stat-card">
                <div class="stat-icon sapphire"><i class="bi bi-sun"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Shift Terdaftar</p>
                    <p class="stat-number">{{ $employee->shift->name ?? '-' }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="stat-icon emerald"><i class="bi bi-person-check"></i></div>
                <div class="stat-info">
                    <p class="stat-label">Status Karyawan</p>
                    <p class="stat-number mt-1">
                        @if ($employee->status === 'active')
                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1">Aktif</span>
                        @else
                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1">Nonaktif</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                <i class="bi bi-table me-2 text-cafe"></i> Log Pembayaran Gaji
            </h6>
        </div>
        <div class="card-body p-0">
            @if (!empty($salaries) && $salaries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-borderless table-financial align-middle mb-0">
                        <thead style="background-color: rgba(248, 246, 243, 0.5); border-bottom: 1px solid var(--cafe-border);">
                            <tr>
                                <th class="ps-4" style="width: 5%;">#</th>
                                <th>Periode Bulan</th>
                                <th class="text-end">Gaji Pokok</th>
                                <th class="text-end">Total Denda</th>
                                <th class="text-end">Gaji Bersih</th>
                                <th class="text-center">Status</th>
                                <th class="text-end pe-4">Kwitansi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salaries as $salary)
                                <tr style="border-bottom: 1px solid var(--cafe-border);">
                                    <td class="ps-4 text-muted fw-medium">{{ ($salaries->currentPage() - 1) * $salaries->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-calendar2-check text-muted opacity-50"></i>
                                            <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($salary->period_date)->translatedFormat('F Y') }}</span>
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
                                    <td class="text-center">
                                        @if ($salary->status === 'draft')
                                            <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3 py-1 border border-warning border-opacity-25">Draft</span>
                                        @elseif ($salary->status === 'approved')
                                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info px-3 py-1 border border-info border-opacity-25">Disetujui</span>
                                        @elseif ($salary->status === 'paid')
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1 border border-success border-opacity-25">Dibayar</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.salary.show', $salary->id) }}" class="btn-action" title="Lihat Slip Gaji">
                                            <i class="bi bi-receipt"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7"></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-0 p-4">
                    <div class="d-flex justify-content-center">
                        {{ $salaries->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div class="icon-box gold mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h5 class="fw-bold text-dark" style="font-family: 'Playfair Display', serif;">Belum Ada Riwayat</h5>
                    <p class="mb-0">Karyawan ini belum pernah menerima penggajian atau datanya belum dihitung.</p>
                </div>
            @endif
        </div>
    </div>
@endsection