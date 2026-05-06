@extends('layouts.app')

@section('title', 'Manajemen Gaji - SIAREA')

@push('styles')
<style>
    /* Styling khusus Halaman Gaji */
    .filter-wrapper {
        background: white;
        border: 1px solid var(--cafe-border);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
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
    }
    
    .btn-view { background: rgba(13, 202, 240, 0.1); color: #0dcaf0; }
    .btn-view:hover { background: #0dcaf0; color: white; transform: translateY(-2px); }
    
    .btn-approve { background: rgba(46, 139, 87, 0.1); color: #2E8B57; }
    .btn-approve:hover { background: #2E8B57; color: white; transform: translateY(-2px); }
    
    .btn-paid { background: rgba(13, 110, 253, 0.1); color: #0d6efd; }
    .btn-paid:hover { background: #0d6efd; color: white; transform: translateY(-2px); }

    .empty-state {
        padding: 4rem 1rem;
        text-align: center;
        color: #a09c98;
    }

    /* Custom Checkbox Mewah */
    .form-check-input {
        width: 1.2em;
        height: 1.2em;
        cursor: pointer;
        border-color: #ccc;
    }
    .form-check-input:checked {
        background-color: var(--cafe-primary);
        border-color: var(--cafe-primary);
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

    /* Bulk Action Bar yang Melayang Bawah */
    .bulk-action-bar {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border-top: 1px solid var(--cafe-border);
        padding: 1rem 1.5rem;
        border-radius: 0 0 16px 16px;
    }
</style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Manajemen Gaji</h1>
            <p class="text-muted mb-0">Kelola perhitungan dan pembayaran gaji karyawan</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.salary.report') }}" class="btn btn-cafe-outline shadow-sm px-4 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-bar-graph"></i> Laporan
            </a>
            <a href="{{ route('admin.salary.calculate') }}" class="btn btn-cafe shadow-sm px-4 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-calculator"></i> Hitung Gaji Baru
            </a>
        </div>
    </div>

    <div class="filter-wrapper mb-4">
        <form action="{{ route('admin.salary.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Pilih Bulan</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-month text-muted"></i></span>
                    <select name="month" class="form-select border-start-0 ps-0">
                        <option value="">Semua Bulan</option>
                        @for ($i = 1; $i <= 12; $i++)
                            <option value="{{ $i }}" @if ($i == ($month ?? now()->month)) selected @endif>
                                {{ \Carbon\Carbon::create(now()->year, $i, 1)->translatedFormat('F') }}
                            </option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-semibold">Pilih Tahun</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-event text-muted"></i></span>
                    <select name="year" class="form-select border-start-0 ps-0">
                        @for ($i = now()->year; $i >= now()->year - 5; $i--)
                            <option value="{{ $i }}" @if ($i == ($year ?? now()->year)) selected @endif>{{ $i }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small fw-semibold">Status Pembayaran</label>
                <select name="status" class="form-select">
                    <option value="">Semua Status</option>
                    <option value="draft" @if(request('status') == 'draft') selected @endif>Draft (Belum Disetujui)</option>
                    <option value="approved" @if(request('status') == 'approved') selected @endif>Disetujui (Siap Bayar)</option>
                    <option value="paid" @if(request('status') == 'paid') selected @endif>Sudah Dibayar</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-cafe w-100 shadow-sm" style="padding: 0.75rem;">
                    Terapkan
                </button>
            </div>
        </form>
    </div>

    <form action="{{ route('admin.salary.bulk-approve') }}" method="POST" id="bulkForm">
        @csrf
    </form>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            @if (!empty($salaries) && $salaries->count() > 0)
                <div class="table-responsive">
                    <table class="table table-borderless align-middle mb-0">
                        <thead class="border-bottom" style="background-color: rgba(248, 246, 243, 0.5);">
                            <tr>
                                <th class="ps-4" style="width: 40px;">
                                    <input class="form-check-input shadow-sm" type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                                </th>
                                <th style="width: 50px;">#</th>
                                <th>Karyawan</th>
                                <th>Periode</th>
                                <th>Gaji Pokok</th>
                                <th>Potongan</th>
                                <th>Gaji Akhir (Bersih)</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salaries as $salary)
                                <tr class="border-bottom">
                                    <td class="ps-4">
                                        @if ($salary->status === 'draft')
                                            <input type="checkbox" form="bulkForm" class="form-check-input salary-checkbox shadow-sm" name="salary_ids[]" value="{{ $salary->id }}">
                                        @endif
                                    </td>
                                    <td class="text-muted fw-medium">{{ ($salaries->currentPage() - 1) * $salaries->perPage() + $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="table-avatar-sm shadow-sm">
                                                {{ strtoupper(substr($salary->employee->user->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <span class="fw-bold text-dark">{{ $salary->employee->user->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-calendar2-minus me-1"></i> {{ \Carbon\Carbon::parse($salary->period_date)->translatedFormat('M Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="text-muted fw-medium">Rp {{ number_format($salary->base_salary ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        <span class="text-danger fw-semibold opacity-75">- Rp {{ number_format($salary->total_deduction ?? 0, 0, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        <h6 class="mb-0 fw-bold" style="color: var(--cafe-primary);">
                                            Rp {{ number_format($salary->final_salary ?? 0, 0, ',', '.') }}
                                        </h6>
                                    </td>
                                    <td>
                                        @if ($salary->status === 'draft')
                                            <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3 py-1 border border-warning border-opacity-25">Draft</span>
                                        @elseif ($salary->status === 'approved')
                                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info px-3 py-1 border border-info border-opacity-25">Disetujui</span>
                                        @elseif ($salary->status === 'paid')
                                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1 border border-success border-opacity-25">Dibayar</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.salary.show', $salary->id) }}" class="btn-action btn-view" title="Lihat Slip Gaji">
                                                <i class="bi bi-receipt"></i>
                                            </a>

                                            @if ($salary->status === 'draft')
                                                <form action="{{ route('admin.salary.approve', $salary->id) }}" method="POST" style="display: inline;">
                                                    @csrf
                                                    <button type="submit" class="btn-action btn-approve" title="Setujui Slip Gaji Ini">
                                                        <i class="bi bi-check-lg text-lg"></i>
                                                    </button>
                                                </form>
                                            @elseif ($salary->status === 'approved')
                                            <form action="{{ route('admin.salary.mark-paid', $salary->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Tandai gaji ini sudah dibayar ke karyawan?');">
                                                    @csrf
                                                    <button type="submit" class="btn-action btn-paid" title="Tandai Sudah Ditransfer/Dibayar">
                                                        <i class="bi bi-cash-stack"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9"></td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bulk-action-bar d-flex flex-column flex-md-row justify-content-between align-items-center gap-3" id="bulkActionBar" style="display: none;">
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" form="bulkForm" class="btn btn-success shadow-sm d-flex align-items-center gap-2 px-3 py-2" id="bulkApproveBtn">
                            <i class="bi bi-check2-all"></i> Setujui yang Dipilih
                        </button>
                        <a href="{{ route('admin.salary.index', ['month' => $month, 'year' => $year]) }}" class="btn btn-light border text-muted px-3 py-2">
                            Batal
                        </a>
                    </div>
                    
                    <nav aria-label="Page navigation" class="m-0">
                        <ul class="pagination m-0">
                            {{ $salaries->appends(request()->query())->links('pagination::bootstrap-4') }}
                        </ul>
                    </nav>
                </div>

            @else
                <div class="empty-state">
                    <div class="icon-box gold mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem;">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h5 class="fw-bold text-dark" style="font-family: 'Playfair Display', serif;">Data Gaji Kosong</h5>
                    <p class="mb-4">Sistem belum menghitung gaji untuk periode bulan dan tahun yang Anda pilih.</p>
                    <a href="{{ route('admin.salary.calculate') }}" class="btn btn-cafe px-4 py-2">
                        <i class="bi bi-calculator me-2"></i>Mulai Hitung Gaji Baru
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Script Checkbox Select All
        function toggleSelectAll(checkbox) {
            const checkboxes = document.querySelectorAll('.salary-checkbox');
            checkboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
            updateBulkActionBar();
        }

        // Update bulk action bar visibility
        function updateBulkActionBar() {
            const checked = document.querySelectorAll('.salary-checkbox:checked').length;
            const bulkActionBar = document.getElementById('bulkActionBar');
            if (bulkActionBar) {
                if (checked > 0) {
                    bulkActionBar.style.display = 'flex';
                } else {
                    bulkActionBar.style.display = 'none';
                }
            }
        }

        // Attach event listeners
        const selectAllCheckbox = document.getElementById('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('click', function() {
                toggleSelectAll(this);
            });
        }

        document.querySelectorAll('.salary-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', updateBulkActionBar);
        });

        // Script Validasi Bulk Approve
        const bulkApproveBtn = document.getElementById('bulkApproveBtn');
        if (bulkApproveBtn) {
            bulkApproveBtn.addEventListener('click', function (e) {
                const checked = document.querySelectorAll('.salary-checkbox:checked').length;
                if (checked === 0) {
                    e.preventDefault();
                    alert('Silakan centang minimal 1 data gaji dengan status Draft untuk disetujui.');
                } else {
                    if(!confirm(`Anda yakin ingin menyetujui ${checked} data gaji karyawan yang dipilih?`)) {
                        e.preventDefault();
                    }
                }
            });
        }

        // Initial state
        updateBulkActionBar();
    });
</script>
@endpush