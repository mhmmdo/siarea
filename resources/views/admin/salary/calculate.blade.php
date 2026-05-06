@extends('layouts.app')

@section('title', 'Hitung Gaji - SIAREA')

@push('styles')
<style>
    /* Styling khusus Form Kalkulasi Gaji */
    .form-section-title {
        font-family: 'Playfair Display', serif;
        color: var(--cafe-secondary);
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .form-section-title i {
        color: var(--cafe-primary);
        font-size: 1.25rem;
    }

    .input-group-text {
        background-color: rgba(248, 246, 243, 0.5);
        border-color: var(--cafe-border);
        color: #888;
    }

    /* Styling Daftar Karyawan */
    .employee-list-container {
        border: 1px solid var(--cafe-border);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    
    .employee-list-header {
        background: rgba(248, 246, 243, 0.8);
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--cafe-border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .employee-list-body {
        max-height: 400px;
        overflow-y: auto;
    }

    .employee-item {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--cafe-border);
        display: flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }

    .employee-item:hover {
        background-color: rgba(184, 134, 11, 0.03);
    }

    .employee-item:last-child {
        border-bottom: none;
    }

    .employee-item.disabled-item {
        background-color: #f8f9fa;
        opacity: 0.7;
    }

    .table-avatar-sm {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(184, 134, 11, 0.08);
        color: var(--cafe-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.9rem;
        border: 1px solid rgba(184, 134, 11, 0.2);
    }

    /* Custom Checkbox */
    .custom-checkbox {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
        border-color: #bbb;
    }
    .custom-checkbox:checked {
        background-color: var(--cafe-primary);
        border-color: var(--cafe-primary);
    }

    /* Info Panel Styling */
    .premium-info-panel {
        background: linear-gradient(to bottom right, #fff, var(--cafe-light));
        border: 1px solid var(--cafe-border);
        border-left: 4px solid var(--cafe-primary);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    .info-item {
        margin-bottom: 1.25rem;
    }
    .info-item:last-child {
        margin-bottom: 0;
    }
    .info-item h6 {
        color: var(--cafe-secondary);
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 0.4rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .info-item h6 i { color: var(--cafe-primary); }
    .info-item p {
        color: #777;
        font-size: 0.85rem;
        margin-bottom: 0;
        line-height: 1.5;
    }
    .info-item ul {
        padding-left: 1.5rem;
        margin-bottom: 0;
        font-size: 0.85rem;
        color: #777;
    }
    .info-item ul li { margin-bottom: 0.25rem; }
</style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Kalkulasi Gaji</h1>
            <p class="text-muted mb-0">Hitung otomatis gaji bersih berdasarkan data kehadiran</p>
        </div>
        <div>
            <a href="{{ route('admin.salary.index') }}" class="btn btn-cafe-outline shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row gx-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.salary.store') }}" method="POST" id="calculateForm">
                        @csrf

                        <div class="form-section-title">
                            <i class="bi bi-calendar-check"></i> Periode Penggajian
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="month" class="form-label fw-semibold text-muted small">Bulan <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0"><i class="bi bi-calendar-month"></i></span>
                                    <select class="form-select border-start-0 ps-0 @error('month') is-invalid @enderror" id="month" name="month" required>
                                        <option value="">-- Pilih Bulan --</option>
                                        @for ($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" @if ($i == ($month ?? now()->month)) selected @endif>
                                                {{ \Carbon\Carbon::create(now()->year, $i, 1)->translatedFormat('F') }}
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                @error('month')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="year" class="form-label fw-semibold text-muted small">Tahun <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0"><i class="bi bi-calendar-event"></i></span>
                                    <select class="form-select border-start-0 ps-0 @error('year') is-invalid @enderror" id="year" name="year" required>
                                        @for ($i = now()->year; $i >= now()->year - 5; $i--)
                                            <option value="{{ $i }}" @if ($i == ($year ?? now()->year)) selected @endif>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                @error('year')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="mb-4" style="opacity: 0.1;">

                        <div class="form-section-title d-flex justify-content-between align-items-center">
                            <div><i class="bi bi-people"></i> Pemilihan Karyawan</div>
                        </div>

                        <div class="form-group mb-4">
                            <div class="employee-list-container shadow-sm">
                                <div class="employee-list-header">
                                    <span class="fw-bold text-dark small"><i class="bi bi-list-check me-1"></i> Daftar Karyawan Aktif</span>
                                    <div class="form-check m-0">
                                        <input class="form-check-input custom-checkbox" type="checkbox" id="selectAllEmployees">
                                        <label class="form-check-label fw-semibold text-dark small" for="selectAllEmployees" style="cursor: pointer;">
                                            Pilih Semua
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="employee-list-body custom-scrollbar">
                                    @forelse ($employees ?? [] as $employee)
                                        @php
                                            $isExists = in_array($employee->id, $existingSalaries ?? []);
                                        @endphp
                                        
                                        <label class="employee-item {{ $isExists ? 'disabled-item' : '' }}" for="employee_{{ $employee->id }}" style="cursor: {{ $isExists ? 'not-allowed' : 'pointer' }}; margin: 0; width: 100%;">
                                            <div class="form-check m-0 me-3">
                                                <input class="form-check-input custom-checkbox employee-checkbox" type="checkbox" id="employee_{{ $employee->id }}" name="employee_ids[]" value="{{ $employee->id }}" @if ($isExists) disabled @endif>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="table-avatar-sm">
                                                        {{ strtoupper(substr($employee->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-bold text-dark">{{ $employee->user->name }}</h6>
                                                        <small class="text-muted"><i class="bi bi-envelope me-1"></i>{{ $employee->user->email }}</small>
                                                    </div>
                                                </div>
                                                
                                                <div class="text-end">
                                                    @if ($isExists)
                                                        <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1"><i class="bi bi-check-all"></i> Sudah Dihitung</span>
                                                    @else
                                                        <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-sun opacity-50"></i> {{ $employee->shift->name ?? '-' }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </label>
                                    @empty
                                        <div class="text-center py-5 text-muted">
                                            <i class="bi bi-people fs-1 d-block mb-2 opacity-25"></i>
                                            <p class="mb-0">Tidak ada karyawan aktif yang bisa dihitung gajinya.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            @error('employee_ids')
                                <div class="text-danger small mt-2 fw-semibold"><i class="bi bi-exclamation-triangle"></i> {{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-cafe d-flex align-items-start gap-3 mb-4 rounded-4 shadow-sm border-0">
                            <i class="bi bi-lightbulb-fill fs-4" style="color: var(--cafe-primary);"></i>
                            <div>
                                <h6 class="fw-bold mb-1" style="color: var(--cafe-secondary);">Catatan Sistem</h6>
                                <p class="mb-0 small text-muted">Jika tidak ada karyawan yang dicentang secara spesifik, sistem akan otomatis menghitung gaji untuk <strong>semua karyawan aktif</strong> yang belum dihitung pada bulan tersebut.</p>
                            </div>
                        </div>

                        <div class="d-flex gap-3 pt-2">
                            <button type="submit" class="btn btn-cafe shadow-sm px-4 py-2 d-flex align-items-center gap-2">
                                <i class="bi bi-calculator"></i> Mulai Kalkulasi
                            </button>
                            <a href="{{ route('admin.salary.index') }}" class="btn btn-light shadow-sm px-4 py-2 border d-flex align-items-center gap-2 text-muted">
                                Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="premium-info-panel sticky-top" style="top: 100px;">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="icon-box gold me-3" style="width: 40px; height: 40px; font-size: 1.2rem; background: rgba(184, 134, 11, 0.1); color: var(--cafe-primary); display: flex; align-items: center; justify-content: center; border-radius: 10px;">
                        <i class="bi bi-gear-wide-connected"></i>
                    </div>
                    <h5 class="fw-bold mb-0" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">Mesin Hitung</h5>
                </div>
                
                <div class="info-item">
                    <h6><i class="bi bi-cpu"></i> Bagaimana Sistem Bekerja?</h6>
                    <p class="mb-2">Saat tombol kalkulasi ditekan, sistem akan melakukan penarikan data secara otomatis:</p>
                    <ul>
                        <li>Menarik nominal <strong>Gaji Pokok</strong> terbaru.</li>
                        <li>Merekap total <strong>Kehadiran</strong> bulanan.</li>
                        <li>Menghitung total menit <strong>Keterlambatan</strong>.</li>
                        <li>Mengalikan denda telat otomatis.</li>
                    </ul>
                </div>

                <div class="info-item">
                    <h6><i class="bi bi-file-earmark-lock"></i> Status DRAFT</h6>
                    <p>Jangan khawatir terjadi kesalahan. Semua gaji yang baru dihitung akan masuk dalam status <span class="badge bg-warning text-dark border border-warning opacity-75 ms-1 px-2 py-1">DRAFT</span> dan tidak akan masuk ke buku kas sebelum Anda klik Setujui.</p>
                </div>
                
                <div class="info-item pt-3 border-top border-warning border-opacity-25" style="background: rgba(184, 134, 11, 0.03); margin: -1.5rem -1.5rem 0 -1.5rem; padding: 1.5rem; border-radius: 0 0 16px 16px;">
                    <h6 class="text-danger"><i class="bi bi-exclamation-triangle"></i> Peringatan Penting</h6>
                    <p class="mb-0" style="color: #888;">Pastikan semua karyawan sudah melakukan Check-Out pada akhir bulan sebelum proses kalkulasi ini dijalankan agar data valid.</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Script untuk fitur "Pilih Semua" Checkbox
            document.addEventListener('DOMContentLoaded', function() {
                const selectAllBtn = document.getElementById('selectAllEmployees');
                const employeeCheckboxes = document.querySelectorAll('.employee-checkbox:not([disabled])');

                if(selectAllBtn) {
                    selectAllBtn.addEventListener('change', function() {
                        const isChecked = this.checked;
                        employeeCheckboxes.forEach(function(checkbox) {
                            checkbox.checked = isChecked;
                        });
                    });
                }

                // Update "Pilih Semua" status jika checkbox satuan diklik
                employeeCheckboxes.forEach(function(checkbox) {
                    checkbox.addEventListener('change', function() {
                        const allChecked = Array.from(employeeCheckboxes).every(cb => cb.checked);
                        const someChecked = Array.from(employeeCheckboxes).some(cb => cb.checked);
                        
                        selectAllBtn.checked = allChecked;
                        selectAllBtn.indeterminate = someChecked && !allChecked;
                    });
                });
            });
        </script>
    @endpush
@endsection