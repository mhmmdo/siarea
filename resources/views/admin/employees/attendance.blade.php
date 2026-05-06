@extends('layouts.app')

@section('title', 'Riwayat Absensi - ' . $employee->user->name)

@push('styles')
<style>
    /* Styling khusus Halaman Riwayat Absensi */
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
    .icon-box.sapphire { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }

    /* Tombol Aksi Minimalis */
    .btn-action {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: none;
        transition: all 0.2s ease;
        background: rgba(184, 134, 11, 0.1); 
        color: var(--cafe-primary);
    }
    .btn-action:hover { 
        background: var(--cafe-primary); 
        color: white; 
        transform: translateY(-2px); 
    }

    /* Modal Styling Premium */
    .modal-content {
        border: none;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
    }
    .modal-header {
        background: linear-gradient(135deg, var(--cafe-secondary) 0%, #3a2a22 100%);
        color: white;
        border-bottom: none;
        padding: 1.5rem 2rem;
    }
    .modal-title {
        font-family: 'Playfair Display', serif;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .modal-header .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
        opacity: 0.8;
    }
    .modal-body {
        padding: 2rem;
        background-color: var(--cafe-light);
    }
    .detail-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid var(--cafe-border);
        height: 100%;
    }
    .detail-section h6 {
        color: var(--cafe-secondary);
        font-weight: 700;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px dashed var(--cafe-border);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .detail-table td {
        padding: 0.5rem 0;
        color: #555;
    }
    .detail-table td:first-child {
        font-weight: 600;
        width: 45%;
        color: #888;
    }

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
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Riwayat Absensi</h1>
            <p class="text-muted mb-0">
                Data kehadiran staf: <strong style="color: var(--cafe-primary);">{{ $employee->user->name }}</strong>
            </p>
        </div>
        <div>
            <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-light shadow-sm border px-3 py-2 text-muted d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali ke Profil
            </a>
        </div>
    </div>

    <div class="filter-wrapper mb-4">
        <form action="{{ route('admin.employees.attendance', $employee->id) }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-muted small fw-semibold">Pilih Bulan</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-month text-muted"></i></span>
                    <select name="month" class="form-select border-start-0 ps-0">
                        @for ($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @if ($month == $m) selected @endif>
                                {{ \Carbon\Carbon::create(now()->year, $m, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small fw-semibold">Pilih Tahun</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-event text-muted"></i></span>
                    <select name="year" class="form-select border-start-0 ps-0">
                        @for ($y = now()->year - 2; $y <= now()->year; $y++)
                            <option value="{{ $y }}" @if ($year == $y) selected @endif>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-cafe w-100 shadow-sm" style="padding: 0.75rem;">
                    <i class="bi bi-search me-2"></i> Tampilkan Data
                </button>
            </div>
        </form>
    </div>

    <div class="row mb-4">
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="stat-card h-100 border-0 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Total Hari Kerja</p>
                        <p class="stat-number">{{ $records->total() }}</p>
                    </div>
                    <div class="icon-box gold">
                        <i class="bi bi-calendar-check-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="stat-card h-100 border-0 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Tepat Waktu</p>
                        <p class="stat-number" style="color: #2E8B57;">{{ $records->where('is_late', false)->count() }}</p>
                    </div>
                    <div class="icon-box emerald">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3 mb-md-0">
            <div class="stat-card h-100 border-0 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Terlambat</p>
                        <p class="stat-number" style="color: #B22222;">{{ $records->where('is_late', true)->count() }}</p>
                    </div>
                    <div class="icon-box ruby">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card h-100 border-0 shadow-sm bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="stat-label">Sudah Pulang</p>
                        <p class="stat-number" style="color: #0d6efd;">{{ $records->filter(fn($r) => $r->checkout)->count() }}</p>
                    </div>
                    <div class="icon-box sapphire">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            @if ($records->count() > 0)
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead style="background-color: rgba(248, 246, 243, 0.5); border-bottom: 1px solid var(--cafe-border);">
                            <tr>
                                <th class="ps-4">Tanggal</th>
                                <th>Shift</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Durasi</th>
                                <th>Status</th>
                                <th>Potongan</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($records as $record)
                                <tr style="border-bottom: 1px solid var(--cafe-border);">
                                    <td class="ps-4 py-3">
                                        <div class="d-flex flex-column">
                                            <strong class="text-dark">{{ $record->date->format('d M Y') }}</strong>
                                            <small class="text-muted">{{ $record->date->translatedFormat('l') }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-sun opacity-50 me-1"></i> {{ $record->shift->name ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $record->check_in_time->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        @if($record->checkout)
                                            <span class="fw-medium text-dark">{{ $record->checkout->check_out_time->format('H:i') }}</span>
                                        @else
                                            <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-2 py-1 border border-warning border-opacity-25">Belum Pulang</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->checkout)
                                            <span class="text-muted small"><i class="bi bi-hourglass-split me-1"></i> {{ $record->getElapsedTime() }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->is_late)
                                            <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3 py-1 border border-danger border-opacity-25">Terlambat</span>
                                        @elseif($record->checkout && $record->checkout->is_early)
                                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info px-3 py-1 border border-info border-opacity-25">Pulang Awal</span>
                                        @else
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1 border border-success border-opacity-25">Tepat Waktu</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($record->lateRecord)
                                            <span class="fw-bold" style="color: #B22222;">
                                                -Rp {{ number_format($record->lateRecord->late_deduction_amount, 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <button class="btn-action" data-bs-toggle="modal" data-bs-target="#detailModal{{ $record->id }}" title="Lihat Detail Log">
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <div class="modal fade text-start" id="detailModal{{ $record->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title d-flex align-items-center gap-2">
                                                            <i class="bi bi-file-earmark-person"></i> Detail Log Kehadiran
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="text-center mb-4">
                                                            <h5 class="fw-bold text-dark">{{ $record->date->translatedFormat('l, d F Y') }}</h5>
                                                            <span class="badge bg-light text-dark border px-3 py-2 mt-1">{{ $record->shift->name ?? '-' }} Shift</span>
                                                        </div>

                                                        <div class="row g-4">
                                                            <div class="col-md-6">
                                                                <div class="detail-section">
                                                                    <h6><i class="bi bi-box-arrow-in-right"></i> Log Check-In</h6>
                                                                    <table class="table table-sm table-borderless detail-table mb-0">
                                                                        <tr>
                                                                            <td>Jam Scan QR</td>
                                                                            <td class="text-dark fw-bold">{{ $record->check_in_time->format('H:i:s') }} WIB</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Titik Koordinat</td>
                                                                            <td>
                                                                                <a href="https://maps.google.com/?q={{ $record->user_latitude }},{{ $record->user_longitude }}" target="_blank" class="text-decoration-none" style="color: var(--cafe-primary);">
                                                                                    <i class="bi bi-geo-alt-fill"></i> Lihat di Peta
                                                                                </a>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Jarak Toleransi</td>
                                                                            <td><span class="badge bg-light text-dark border">{{ $record->distance_from_qr }} meter</span></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>Status Masuk</td>
                                                                            <td>
                                                                                @if($record->is_late)
                                                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Terlambat</span>
                                                                                @else
                                                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Valid & Tepat Waktu</span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    </table>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="detail-section">
                                                                    <h6><i class="bi bi-box-arrow-left"></i> Log Check-Out</h6>
                                                                    @if($record->checkout)
                                                                        <table class="table table-sm table-borderless detail-table mb-0">
                                                                            <tr>
                                                                                <td>Jam Scan Pulang</td>
                                                                                <td class="text-dark fw-bold">{{ $record->checkout->check_out_time->format('H:i:s') }} WIB</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Titik Koordinat</td>
                                                                                <td>
                                                                                    <a href="https://maps.google.com/?q={{ $record->checkout->check_out_latitude }},{{ $record->checkout->check_out_longitude }}" target="_blank" class="text-decoration-none" style="color: var(--cafe-primary);">
                                                                                        <i class="bi bi-geo-alt-fill"></i> Lihat di Peta
                                                                                    </a>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Jarak Toleransi</td>
                                                                                <td><span class="badge bg-light text-dark border">{{ $record->checkout->distance_from_qr }} meter</span></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td>Durasi Bekerja</td>
                                                                                <td class="fw-bold" style="color: var(--cafe-primary);">{{ $record->getElapsedTime() }}</td>
                                                                            </tr>
                                                                        </table>
                                                                    @else
                                                                        <div class="text-center py-4">
                                                                            <i class="bi bi-clock-history fs-1 text-muted opacity-25 d-block mb-2"></i>
                                                                            <span class="text-muted small">Karyawan belum melakukan Check-out</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if($record->lateRecord)
                                                            <div class="detail-section mt-4" style="background: linear-gradient(to right, #fff, rgba(178, 34, 34, 0.02)); border-left: 4px solid #B22222;">
                                                                <h6 style="color: #B22222; border-bottom: none; margin-bottom: 0.5rem;"><i class="bi bi-exclamation-octagon"></i> Rincian Denda Keterlambatan</h6>
                                                                <div class="row">
                                                                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                                                                        <small class="text-muted d-block">Jam Seharusnya</small>
                                                                        <strong>{{ $record->lateRecord->expected_time }}</strong>
                                                                    </div>
                                                                    <div class="col-md-3 col-6 mb-2 mb-md-0">
                                                                        <small class="text-muted d-block">Durasi Telat</small>
                                                                        <strong class="text-danger">{{ $record->lateRecord->duration_minutes }} Menit</strong>
                                                                    </div>
                                                                    <div class="col-md-6 text-md-end mt-2 mt-md-0">
                                                                        <small class="text-muted d-block">Total Potongan Gaji</small>
                                                                        <h4 class="mb-0 fw-bold" style="color: #B22222;">- Rp {{ number_format($record->lateRecord->late_deduction_amount, 0, ',', '.') }}</h4>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-0 p-4">
                    @if ($records->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $records->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            @else
                <div class="empty-state">
                    <div class="icon-box gold mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-journal-x"></i>
                    </div>
                    <h5 class="fw-bold text-dark" style="font-family: 'Playfair Display', serif;">Tidak Ada Data</h5>
                    <p class="mb-0">Belum ada riwayat absensi untuk periode bulan dan tahun yang dipilih.</p>
                </div>
            @endif
        </div>
    </div>
@endsection