@extends('layouts.app')

@section('title', 'Kelola QR Code - SIAREA')

@push('styles')
<style>
    /* Styling khusus Halaman Index QR Code */
    .filter-wrapper {
        background: white;
        border: 1px solid var(--cafe-border);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    }

    /* Tombol Aksi Minimalis Mewah */
    .btn-action {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        border: none;
        transition: all 0.2s ease;
    }
    
    .btn-view { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
    .btn-view:hover { background: #0dcaf0; color: white; transform: translateY(-2px); }
    
    .btn-edit { background: rgba(184, 134, 11, 0.1); color: var(--cafe-primary); }
    .btn-edit:hover { background: var(--cafe-primary); color: white; transform: translateY(-2px); }
    
    .btn-stats { background: rgba(102, 16, 242, 0.1); color: #6610f2; }
    .btn-stats:hover { background: #6610f2; color: white; transform: translateY(-2px); }

    .btn-delete { background: rgba(220, 53, 69, 0.1); color: #dc3545; }
    .btn-delete:hover { background: #dc3545; color: white; transform: translateY(-2px); }

    .empty-state {
        padding: 4rem 1rem;
        text-align: center;
        color: #a09c98;
    }

    .qr-icon-wrapper {
        width: 40px;
        height: 40px;
        background: rgba(184, 134, 11, 0.05);
        border: 1px solid rgba(184, 134, 11, 0.15);
        color: var(--cafe-primary);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
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
    .pagination .page-item.disabled .page-link {
        color: #ccc;
        background-color: transparent;
        border-color: var(--cafe-border);
    }
</style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Kelola QR Code</h1>
            <p class="text-muted mb-0">Manajemen titik lokasi absensi karyawan</p>
        </div>
        <div>
            <a href="{{ route('admin.qr.create') }}" class="btn btn-cafe shadow-sm d-flex align-items-center gap-2" style="padding: 0.75rem 1.5rem;">
                <i class="bi bi-qr-code-scan"></i> Buat QR Code Baru
            </a>
        </div>
    </div>

    <div class="filter-wrapper mb-4">
        <form action="{{ route('admin.qr.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label text-muted small fw-semibold">Pencarian</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama lokasi QR..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Filter Shift</label>
                <select name="shift_id" class="form-select">
                    <option value="">Semua Shift</option>
                    @foreach ($shifts ?? [] as $shift)
                        <option value="{{ $shift->id }}" @if (request('shift_id') == $shift->id) selected @endif>
                            {{ $shift->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-semibold">Status</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="active" @if (request('status') === 'active') selected @endif>Aktif</option>
                    <option value="inactive" @if (request('status') === 'inactive') selected @endif>Nonaktif</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-cafe-outline w-100" style="padding: 0.75rem;">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            @if (!empty($qrCodes) && $qrCodes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="border-bottom" style="background-color: rgba(248, 246, 243, 0.5);">
                            <tr>
                                <th class="ps-4" style="width: 5%;">No.</th>
                                <th>Nama Lokasi</th>
                                <th>Shift Aktif</th>
                                <th>Koordinat & Radius</th>
                                <th>Jam Operasional</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($qrCodes as $qr)
                                <tr class="border-bottom">
                                    <td class="ps-4 text-muted fw-medium">
                                        {{ ($qrCodes->currentPage() - 1) * $qrCodes->perPage() + $loop->iteration }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="qr-icon-wrapper shadow-sm">
                                                <i class="bi bi-qr-code"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-size: 1.05rem;">{{ $qr->name ?? '-' }}</div>
                                                <div class="text-muted font-monospace mt-1" style="font-size: 0.75rem; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; border: 1px solid #eee; display: inline-block;">
                                                    {{ substr($qr->code, 0, 12) }}...
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if ($qr->shift)
                                            <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-sun opacity-50 me-1"></i> {{ $qr->shift->name }}</span>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-1">
                                            <span class="text-dark small"><i class="bi bi-geo-alt text-danger opacity-75 me-1"></i> {{ substr($qr->latitude, 0, 7) }}, {{ substr($qr->longitude, 0, 7) }}</span>
                                            <span class="text-muted small"><i class="bi bi-bullseye text-success opacity-75 me-1"></i> Radius: <strong>{{ $qr->radius_meters ?? 100 }}m</strong></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark fw-medium small"><i class="bi bi-clock text-muted me-1"></i> {{ \Carbon\Carbon::parse($qr->shift->start_time ?? '00:00')->format('H:i') }} - {{ \Carbon\Carbon::parse($qr->shift->end_time ?? '00:00')->format('H:i') }}</span>
                                    </td>
                                    <td>
                                        @if ($qr->is_active)
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2 border border-success border-opacity-25">Aktif</span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3 py-2 border border-secondary border-opacity-25">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.qr.show', $qr->id) }}" class="btn-action btn-view" title="Lihat/Print QR">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.qr.statistics', $qr->id) }}" class="btn-action btn-stats" title="Lihat Statistik Penggunaan">
                                                <i class="bi bi-bar-chart-fill"></i>
                                            </a>
                                            <a href="{{ route('admin.qr.edit', $qr->id) }}" class="btn-action btn-edit" title="Edit Setting GPS">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('admin.qr.destroy', $qr->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus titik QR ini? Karyawan tidak akan bisa absen di lokasi ini lagi.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action btn-delete" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
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
                        {{ $qrCodes->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            @else
                <div class="empty-state">
                    <div class="icon-box gold mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-qr-code"></i>
                    </div>
                    <h5 class="fw-bold text-dark" style="font-family: 'Playfair Display', serif;">Belum Ada Titik QR Code</h5>
                    <p class="mb-4">Sistem belum memiliki data titik lokasi QR atau tidak ada yang sesuai filter.</p>
                    <a href="{{ route('admin.qr.create') }}" class="btn btn-cafe">
                        <i class="bi bi-plus-lg me-2"></i>Buat QR Code Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection