@extends('layouts.app')

@section('title', 'Edit Karyawan - SIAREA')

@push('styles')
<style>
    /* Custom Form Styles untuk Tema Cafe */
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

    .form-label {
        font-weight: 600;
        color: #555;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .input-group-text {
        background-color: rgba(248, 246, 243, 0.5);
        border-color: var(--cafe-border);
        color: #888;
    }

    /* Custom Toggle Switch warna Premium */
    .form-switch .form-check-input {
        width: 3em;
        height: 1.5em;
        margin-top: 0.1em;
        cursor: pointer;
    }
    
    .form-switch .form-check-input:checked {
        background-color: var(--cafe-primary);
        border-color: var(--cafe-primary);
    }
    
    .form-switch .form-check-label {
        padding-top: 0.3em;
        padding-left: 0.5em;
        cursor: pointer;
        color: var(--cafe-secondary);
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
        padding-bottom: 1.25rem;
        border-bottom: 1px dashed var(--cafe-border);
    }

    .info-item:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .info-item h6 {
        color: #888;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.4rem;
    }

    .info-item p {
        color: var(--cafe-secondary);
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Edit Data Karyawan</h1>
            <p class="text-muted mb-0">Perbarui informasi personal dan operasional staf</p>
        </div>
        <div>
            <a href="{{ route('admin.employees.index') }}" class="btn btn-cafe-outline shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row gx-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.employees.update', $employee->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-section-title">
                            <i class="bi bi-person-vcard"></i> Informasi Personal
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0"><i class="bi bi-person"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $employee->user->name ?? '') }}" required>
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $employee->user->email ?? '') }}" required>
                                </div>
                                @error('email')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12 mb-3">
                                <label for="phone" class="form-label">Nomor Telepon (WhatsApp)</label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0"><i class="bi bi-telephone"></i></span>
                                    <input type="tel" class="form-control border-start-0 ps-0 @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $employee->phone ?? '') }}">
                                </div>
                                @error('phone')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="mb-4" style="opacity: 0.1;">

                        <div class="form-section-title">
                            <i class="bi bi-briefcase"></i> Detail Pekerjaan & Gaji
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label for="shift_id" class="form-label">Jadwal Shift <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0"><i class="bi bi-clock-history"></i></span>
                                    <select class="form-select border-start-0 ps-0 @error('shift_id') is-invalid @enderror" id="shift_id" name="shift_id" required>
                                        <option value="">-- Pilih Shift Kerja --</option>
                                        @foreach ($shifts ?? [] as $shift)
                                            <option value="{{ $shift->id }}" @if (old('shift_id', $employee->shift_id) == $shift->id) selected @endif>
                                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('shift_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="base_salary" class="form-label">Gaji Pokok <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0 fw-bold">Rp</span>
                                    <input type="number" class="form-control border-start-0 ps-0 @error('base_salary') is-invalid @enderror" id="base_salary" name="base_salary" value="{{ old('base_salary', $employee->basic_salary ?? 0) }}" min="0" required>
                                </div>
                                @error('base_salary')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="late_deduction_amount" class="form-label">Potongan Telat /15 Menit</label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0 fw-bold">Rp</span>
                                    <input type="number" class="form-control border-start-0 ps-0 @error('late_deduction_amount') is-invalid @enderror" id="late_deduction_amount" name="late_deduction_amount" value="{{ old('late_deduction_amount', $employee->late_deduction_amount ?? 0) }}" min="0">
                                </div>
                                @error('late_deduction_amount')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group mb-4 bg-light p-3 rounded-4 border">
                            <div class="form-check form-switch d-flex align-items-center gap-2 m-0 p-0">
                                <input class="form-check-input ms-0 mt-0" type="checkbox" id="is_active" name="is_active" value="1" @if (old('is_active', $employee->status === 'active')) checked @endif>
                                <label class="form-check-label fw-bold mb-0" for="is_active">
                                    Status Akun Aktif
                                </label>
                            </div>
                            <small class="text-muted ms-5 d-block mt-1">Jika dimatikan, karyawan ini tidak bisa melakukan login ke aplikasi.</small>
                        </div>

                        <div class="d-flex gap-3 pt-3">
                            <button type="submit" class="btn btn-cafe shadow-sm px-4 py-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check2-circle"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('admin.employees.show', $employee->id) }}" class="btn btn-light shadow-sm px-4 py-2 border d-flex align-items-center gap-2 text-muted">
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
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h5 class="fw-bold mb-0" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">Data Rekam Jejak</h5>
                </div>
                
                <div class="info-item">
                    <h6><i class="bi bi-hash me-1"></i> ID Pengguna</h6>
                    <p class="font-monospace" style="color: var(--cafe-primary);">#{{ str_pad($employee->user->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>

                <div class="info-item">
                    <h6><i class="bi bi-calendar-check me-1"></i> Tanggal Bergabung</h6>
                    <p>{{ $employee->created_at ? \Carbon\Carbon::parse($employee->created_at)->translatedFormat('d F Y') : '-' }}</p>
                    @if($employee->created_at)
                        <small class="text-muted">{{ \Carbon\Carbon::parse($employee->created_at)->format('H:i') }} WIB</small>
                    @endif
                </div>

                <div class="info-item">
                    <h6><i class="bi bi-clock-history me-1"></i> Terakhir Diperbarui</h6>
                    <p>{{ $employee->updated_at ? \Carbon\Carbon::parse($employee->updated_at)->diffForHumans() : '-' }}</p>
                    @if($employee->updated_at)
                        <small class="text-muted">{{ \Carbon\Carbon::parse($employee->updated_at)->translatedFormat('d F Y, H:i') }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection