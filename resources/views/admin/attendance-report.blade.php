@extends('layouts.app')

@section('title', 'Laporan Absensi - SIAREA')

@push('styles')
<style>
    /* Styling khusus Halaman Laporan */
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

    .table-avatar {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(184, 134, 11, 0.05);
        color: var(--cafe-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        border: 1px solid rgba(184, 134, 11, 0.2);
    }

    .empty-state {
        padding: 4rem 1rem;
        text-align: center;
        color: #a09c98;
    }

    /* Custom Pagination untuk Tema Cafe */
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
    .pagination .page-item.disabled .page-link {
        color: #ccc;
        background-color: transparent;
        border-color: var(--cafe-border);
    }
</style>
@endpush

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Laporan Absensi</h1>
            <p class="text-muted mb-0">Pantau riwayat kehadiran dan jam kerja operasional staf.</p>
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-cafe-outline">
                <i class="bi bi-printer me-2"></i>Cetak
            </button>
        </div>
    </div>

    <div class="filter-wrapper mb-4">
        <form action="{{ route('admin.attendance.report') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Cari Karyawan</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Nama..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Mulai Tanggal</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Sampai Tanggal</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Status Kehadiran</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="on_time" @if (request('status') === 'on_time') selected @endif>Tepat Waktu</option>
                    <option value="late" @if (request('status') === 'late') selected @endif>Terlambat</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-cafe w-100 shadow-sm" style="padding: 0.75rem;">
                    Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    @if (isset($statistics))
        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label">Total Kehadiran</p>
                            <p class="stat-number">{{ $statistics['total_attendance'] ?? 0 }}</p>
                        </div>
                        <div class="icon-box gold">
                            <i class="bi bi-file-earmark-check-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label">Tepat Waktu</p>
                            <p class="stat-number" style="color: #2E8B57;">{{ $statistics['on_time'] ?? 0 }}</p>
                        </div>
                        <div class="icon-box emerald">
                            <i class="bi bi-check-circle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stat-card h-100">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="stat-label">Terlambat</p>
                            <p class="stat-number" style="color: #B22222;">{{ $statistics['late'] ?? 0 }}</p>
                        </div>
                        <div class="icon-box ruby">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if (!empty($records) && $records->count() > 0)
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="border-bottom">
                            <tr>
                                <th class="ps-4">No.</th>
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>Shift</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Durasi</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $record)
                                <tr>
                                    <td class="ps-4 text-muted fw-medium">
                                        {{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <strong class="text-dark">{{ \Carbon\Carbon::parse($record->date)->format('d M Y') }}</strong>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($record->date)->translatedFormat('l') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.employees.attendance', $record->employee->id) }}" class="text-decoration-none d-flex align-items-center gap-3">
                                            <div class="table-avatar">
                                                {{ strtoupper(substr($record->employee->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <span class="fw-semibold text-dark">{{ $record->employee->user->name ?? '-' }}</span>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-sun"></i> {{ $record->shift->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-medium">{{ $record->check_in_time->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        @if($record->checkout)
                                            <span class="text-dark fw-medium">{{ $record->checkout->check_out_time->format('H:i') }}</span>
                                        @else
                                            <span class="text-muted small border px-2 py-1 rounded">Belum Pulang</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->checkout)
                                            <span class="fw-medium" style="color: var(--cafe-primary);">
                                                <i class="bi bi-hourglass-split me-1"></i> {{ $record->getElapsedTime() }}
                                            </span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @if ($record->is_late)
                                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3 py-2 border border-danger border-opacity-25">Terlambat</span>
                                        @elseif($record->checkout && $record->checkout->is_early)
                                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info px-3 py-2 border border-info border-opacity-25">Pulang Awal</span>
                                        @else
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2 border border-success border-opacity-25">Tepat Waktu</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8">
                                        </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-top p-4">
                    <div class="d-flex justify-content-center">
                        {{ $records->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <i class="bi bi-folder-x mb-3 d-block opacity-25" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold text-dark" style="font-family: 'Playfair Display', serif;">Data Tidak Ditemukan</h5>
                    <p class="mb-0">Tidak ada riwayat absensi yang cocok dengan filter yang Anda pilih.</p>
                </div>
            @endif
        </div>
    </div>
@endsection