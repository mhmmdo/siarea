@extends('layouts.app')

@section('title', 'Statistik QR Code - SIAREA')

@push('styles')
<style>
    /* Styling khusus Halaman Statistik */
    .filter-wrapper {
        background: white;
        border: 1px solid var(--cafe-border);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
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

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 16px;
        border: 1px solid var(--cafe-border);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 100%;
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
        font-size: 2rem;
        font-weight: 700;
        margin: 0;
        font-family: 'Playfair Display', serif;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .info-list li {
        display: flex;
        align-items: center;
        padding: 0.875rem 0;
        border-bottom: 1px dashed var(--cafe-border);
    }
    .info-list li:last-child {
        border-bottom: none;
        padding-bottom: 0;
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
    .pagination .page-link:hover {
        background-color: rgba(184, 134, 11, 0.1);
        color: var(--cafe-primary);
        border-color: var(--cafe-primary);
    }
    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, var(--cafe-primary), #9E7308);
        border-color: var(--cafe-primary);
        color: white;
        box-shadow: 0 4px 10px rgba(184, 134, 11, 0.2);
    }
</style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Statistik Lokasi</h1>
            <p class="text-muted mb-0">Laporan performa absensi pada titik <strong style="color: var(--cafe-primary);">{{ $qr->name }}</strong></p>
        </div>
        <div>
            <a href="{{ route('admin.qr.show', $qr->id) }}" class="btn btn-light shadow-sm border px-3 py-2 text-muted d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali ke Detail
            </a>
        </div>
    </div>

    <div class="filter-wrapper mb-4">
        <form action="{{ route('admin.qr.statistics', $qr->id) }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="start_date" class="form-label text-muted small fw-semibold">Dari Tanggal</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar3"></i></span>
                    <input type="date" id="start_date" name="start_date" class="form-control border-start-0 ps-0" value="{{ $startDate ?? now()->startOfMonth()->format('Y-m-d') }}">
                </div>
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label text-muted small fw-semibold">Sampai Tanggal</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar3-fill"></i></span>
                    <input type="date" id="end_date" name="end_date" class="form-control border-start-0 ps-0" value="{{ $endDate ?? now()->format('Y-m-d') }}">
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-cafe w-100 shadow-sm" style="padding: 0.75rem;">
                    <i class="bi bi-funnel me-2"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
            <div class="stat-card">
                <div>
                    <p class="stat-label">Total Aktivitas Scan</p>
                    <p class="stat-number" style="color: var(--cafe-secondary);">{{ $totalScans ?? 0 }}</p>
                </div>
                <div class="icon-box gold">
                    <i class="bi bi-qr-code-scan"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
            <div class="stat-card" style="border-left: 4px solid #2E8B57;">
                <div>
                    <p class="stat-label">Kehadiran Tepat Waktu</p>
                    <p class="stat-number" style="color: #2E8B57;">{{ $onTimeCount ?? 0 }}</p>
                </div>
                <div class="icon-box emerald">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="stat-card" style="border-left: 4px solid #B22222;">
                <div>
                    <p class="stat-label">Kehadiran Terlambat</p>
                    <p class="stat-number" style="color: #B22222;">{{ $lateCount ?? 0 }}</p>
                </div>
                <div class="icon-box ruby">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-white p-4 border-bottom-0">
                    <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-info-square me-2 text-cafe"></i> Konfigurasi Titik Absen
                    </h6>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <ul class="info-list">
                        <li>
                            <div class="text-muted small w-50">Shift Beroperasi</div>
                            <div class="w-50 text-end">
                                <span class="badge bg-light text-dark border"><i class="bi bi-sun me-1"></i> {{ $qr->shift->name ?? '-' }}</span>
                            </div>
                        </li>
                        <li>
                            <div class="text-muted small w-50">Jam Aktif</div>
                            <div class="w-50 text-end fw-medium text-dark">
                                {{ \Carbon\Carbon::parse($qr->shift->start_time ?? '00:00')->format('H:i') }} - {{ \Carbon\Carbon::parse($qr->shift->end_time ?? '00:00')->format('H:i') }} WIB
                            </div>
                        </li>
                        <li>
                            <div class="text-muted small w-50">Radius Toleransi</div>
                            <div class="w-50 text-end fw-medium text-dark">
                                {{ $qr->radius_meters ?? 100 }} Meter
                            </div>
                        </li>
                        <li>
                            <div class="text-muted small w-50">Status QR Code</div>
                            <div class="w-50 text-end">
                                @if ($qr->is_active)
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1">Aktif Memindai</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-1">Nonaktif</span>
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px;">
                <div class="card-header bg-white p-4 border-bottom-0">
                    <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-pie-chart me-2 text-cafe"></i> Rasio Kedisiplinan
                    </h6>
                </div>
                <div class="card-body px-4 pb-4 pt-0 d-flex flex-column justify-content-center">
                    @php
                        $onTimePercentage = $totalScans > 0 ? round(($onTimeCount / $totalScans) * 100, 1) : 0;
                        $latePercentage = $totalScans > 0 ? round(($lateCount / $totalScans) * 100, 1) : 0;
                    @endphp
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <span class="fw-semibold text-dark"><i class="bi bi-check-circle text-success me-2"></i>Tepat Waktu</span>
                            <h4 class="mb-0 fw-bold text-success">{{ $onTimePercentage }}%</h4>
                        </div>
                        <div class="progress rounded-pill shadow-sm" style="height: 12px; background-color: rgba(46, 139, 87, 0.1);">
                            <div class="progress-bar" role="progressbar" style="background-color: #2E8B57; width: {{ $onTimePercentage }}%; transition: width 1s ease-in-out;"></div>
                        </div>
                    </div>

                    <div>
                        <div class="d-flex justify-content-between align-items-end mb-2">
                            <span class="fw-semibold text-dark"><i class="bi bi-exclamation-circle text-danger me-2"></i>Terlambat</span>
                            <h4 class="mb-0 fw-bold text-danger">{{ $latePercentage }}%</h4>
                        </div>
                        <div class="progress rounded-pill shadow-sm" style="height: 12px; background-color: rgba(178, 34, 34, 0.1);">
                            <div class="progress-bar" role="progressbar" style="background-color: #B22222; width: {{ $latePercentage }}%; transition: width 1s ease-in-out;"></div>
                        </div>
                    </div>
                    
                    @if($totalScans == 0)
                        <div class="mt-3 text-center text-muted small">
                            <em>Belum ada data scan untuk dikalkulasi.</em>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                <i class="bi bi-list-columns-reverse me-2 text-cafe"></i> Detail Transaksi Scan
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                @if (!empty($records) && $records->count() > 0)
                    <table class="table table-borderless align-middle mb-0">
                        <thead style="background-color: rgba(248, 246, 243, 0.5); border-bottom: 1px solid var(--cafe-border);">
                            <tr>
                                <th class="ps-4">#</th>
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>Jadwal Shift</th>
                                <th>Waktu Scan</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Profil</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $record)
                                <tr style="border-bottom: 1px solid var(--cafe-border);">
                                    <td class="ps-4 text-muted fw-medium">{{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <span class="text-dark fw-medium">{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-avatar-sm shadow-sm">
                                                {{ strtoupper(substr($record->employee->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <a href="{{ route('admin.employees.show', $record->employee->id) }}" class="text-decoration-none fw-semibold text-dark hover-cafe">
                                                {{ $record->employee->user->name ?? '-' }}
                                            </a>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-muted small"><i class="bi bi-sun opacity-50 me-1"></i> {{ $record->shift->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <code class="text-dark bg-light px-2 py-1 rounded border">{{ \Carbon\Carbon::parse($record->time ?? $record->created_at)->format('H:i') }} WIB</code>
                                    </td>
                                    <td>
                                        @if ($record->is_late)
                                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3 py-1 border border-danger border-opacity-25">Terlambat</span>
                                        @else
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1 border border-success border-opacity-25">Tepat Waktu</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('admin.employees.show', $record->employee->id) }}" class="btn-action" title="Lihat Profil">
                                            <i class="bi bi-person"></i>
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
                @else
                    <div class="empty-state">
                        <div class="icon-box gold mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                            <i class="bi bi-clipboard-x"></i>
                        </div>
                        <h5 class="fw-bold text-dark" style="font-family: 'Playfair Display', serif;">Data Kosong</h5>
                        <p class="mb-0">Tidak ada riwayat scan pada lokasi ini dalam rentang waktu yang dipilih.</p>
                    </div>
                @endif
            </div>
            
            @if ($records->hasPages())
                <div class="card-footer bg-white border-0 p-4">
                    <div class="d-flex justify-content-center">
                        {{ $records->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <style>
        .hover-cafe:hover { color: var(--cafe-primary) !important; text-decoration: underline !important; }
    </style>
@endsection