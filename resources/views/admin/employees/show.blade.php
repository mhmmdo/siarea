@extends('layouts.app')

@section('title', 'Detail Karyawan - SIAREA')

@push('styles')
<style>
    /* Styling khusus Halaman Detail */
    .profile-header-bg {
        background: linear-gradient(135deg, var(--cafe-secondary) 0%, #3a2a22 100%);
        height: 120px;
        position: relative;
    }
    
    .profile-avatar-wrapper {
        margin-top: -60px;
        position: relative;
        z-index: 2;
    }

    .profile-avatar {
        width: 120px;
        height: 120px;
        border: 4px solid var(--cafe-light);
        background-color: white;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        border-radius: 50%;
        object-fit: cover;
    }

    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .info-list li {
        display: flex;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px dashed var(--cafe-border);
    }

    .info-list li:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(184, 134, 11, 0.08);
        color: var(--cafe-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        margin-right: 1rem;
    }

    .info-content h6 {
        font-size: 0.8rem;
        color: #888;
        margin-bottom: 0.2rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-content p {
        font-size: 1rem;
        color: var(--cafe-dark);
        font-weight: 600;
        margin: 0;
    }

    .salary-box {
        background: linear-gradient(to bottom right, #fff, rgba(184, 134, 11, 0.05));
        border: 1px solid rgba(184, 134, 11, 0.2);
        border-radius: 12px;
        padding: 1.25rem;
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
    .icon-box.emerald { background: rgba(46, 139, 87, 0.1); color: #2E8B57; }
    .icon-box.ruby { background: rgba(178, 34, 34, 0.1); color: #B22222; }

    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #a09c98;
    }
</style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Profil Karyawan</h1>
            <p class="text-muted mb-0">Detail informasi dan rekam jejak absensi</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.employees.index') }}" class="btn btn-light shadow-sm border px-3 py-2 text-muted d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('admin.employees.edit', $employee->id) }}" class="btn btn-cafe shadow-sm px-4 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i> Edit Profil
            </a>
        </div>
    </div>

    <div class="row gx-4">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px; overflow: hidden;">
                <div class="profile-header-bg"></div>
                <div class="card-body text-center pt-0 px-4 pb-4">
                    <div class="profile-avatar-wrapper">
                        <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ $employee->user->id }}&backgroundColor=FBF9F6" alt="Avatar" class="profile-avatar">
                    </div>
                    <h4 class="mt-3 fw-bold mb-1" style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary);">
                        {{ $employee->user->name ?? '-' }}
                    </h4>
                    <span class="badge bg-light text-secondary border px-2 py-1 mb-3">ID: #{{ str_pad($employee->user->id, 4, '0', STR_PAD_LEFT) }}</span>

                    <ul class="info-list text-start mt-3">
                        <li>
                            <div class="info-icon"><i class="bi bi-envelope"></i></div>
                            <div class="info-content">
                                <h6>Alamat Email</h6>
                                <p>{{ $employee->user->email ?? '-' }}</p>
                            </div>
                        </li>
                        <li>
                            <div class="info-icon"><i class="bi bi-telephone"></i></div>
                            <div class="info-content">
                                <h6>Nomor Telepon</h6>
                                <p>{{ $employee->phone ?? 'Tidak ada data' }}</p>
                            </div>
                        </li>
                        <li>
                            <div class="info-icon"><i class="bi bi-clock"></i></div>
                            <div class="info-content">
                                <h6>Jadwal Shift</h6>
                                <p>
                                    @if ($employee->shift)
                                        <span class="text-dark">{{ $employee->shift->name }}</span> 
                                        <span class="text-muted fw-normal ms-1">({{ \Carbon\Carbon::parse($employee->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('H:i') }})</span>
                                    @else
                                        <span class="text-muted fw-normal">Belum diatur</span>
                                    @endif
                                </p>
                            </div>
                        </li>
                        <li>
                            <div class="info-icon" style="background: transparent; border: 1px solid var(--cafe-border); color: #888;"><i class="bi bi-shield-check"></i></div>
                            <div class="info-content">
                                <h6>Status Akun</h6>
                                @if ($employee->status === 'active')
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1 border border-success border-opacity-25">Aktif Bekerja</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3 py-1 border border-secondary border-opacity-25">Nonaktif</span>
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-4" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-wallet2 me-2 text-cafe"></i> Detail Kompensasi
                    </h6>
                    
                    <div class="salary-box mb-3">
                        <p class="text-muted small fw-bold mb-1 text-uppercase">Gaji Pokok</p>
                        <h4 class="mb-0 fw-bold" style="color: var(--cafe-secondary);">
                            <span class="fs-6 text-muted me-1">Rp</span>{{ number_format($employee->basic_salary ?? 0, 0, ',', '.') }}
                        </h4>
                    </div>
                    
                    <div class="salary-box" style="background: linear-gradient(to bottom right, #fff, rgba(178, 34, 34, 0.03)); border-color: rgba(178, 34, 34, 0.1);">
                        <p class="text-muted small fw-bold mb-1 text-uppercase">Potongan Telat <span class="text-danger opacity-75">(per 15 menit)</span></p>
                        <h5 class="mb-0 fw-bold text-danger">
                            <span class="fs-6 opacity-75 me-1">- Rp</span>{{ number_format($employee->late_deduction_amount ?? 0, 0, ',', '.') }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="stat-card h-100 border-0 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="stat-label">Kehadiran Bulan Ini</p>
                                <p class="stat-number" style="color: #2E8B57;">{{ $attendance_count ?? 0 }} <span class="fs-6 text-muted">hari</span></p>
                            </div>
                            <div class="icon-box emerald">
                                <i class="bi bi-calendar2-check-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="stat-card h-100 border-0 shadow-sm" style="border-left-color: #B22222;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="stat-label">Total Terlambat</p>
                                <p class="stat-number" style="color: #B22222;">{{ $late_count ?? 0 }} <span class="fs-6 text-muted">kali</span></p>
                            </div>
                            <div class="icon-box ruby">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white p-4 border-bottom d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3" style="border-radius: 16px 16px 0 0;">
                    <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif; font-size: 1.1rem;">
                        <i class="bi bi-journal-text me-2 text-cafe"></i> Log Absensi
                    </h6>
                    
                    <form action="{{ route('admin.employees.attendance', $employee->id) }}" method="GET" class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm shadow-sm" style="border-radius: 8px; overflow: hidden; max-width: 220px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-calendar-month text-muted"></i></span>
                            <select name="month" class="form-select border-start-0 ps-0" onchange="this.form.submit()">
                                <option value="">Semua Bulan</option>
                                @for ($i = 1; $i <= 12; $i++)
                                    <option value="{{ $i }}" @if (request('month', now()->month) == $i) selected @endif>
                                        {{ \Carbon\Carbon::create(now()->year, $i, 1)->translatedFormat('F Y') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <noscript><button type="submit" class="btn btn-sm btn-cafe">Go</button></noscript>
                    </form>
                </div>

                <div class="card-body p-0">
                    @if (!empty($attendance_records) && count($attendance_records) > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead style="background-color: rgba(248, 246, 243, 0.5); border-bottom: 1px solid var(--cafe-border);">
                                    <tr>
                                        <th class="ps-4">Tanggal</th>
                                        <th>Shift Kerja</th>
                                        <th>Waktu Masuk</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($attendance_records as $record)
                                        <tr style="border-bottom: 1px solid var(--cafe-border);">
                                            <td class="ps-4 py-3">
                                                <div class="d-flex flex-column">
                                                    <strong class="text-dark">{{ \Carbon\Carbon::parse($record['date'])->format('d M Y') }}</strong>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($record['date'])->translatedFormat('l') }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2 py-1"><i class="bi bi-sun opacity-50 me-1"></i> {{ $record['shift_name'] ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-medium text-dark"><i class="bi bi-clock me-1 text-muted"></i> {{ $record['time'] ?? '-' }}</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                @if ($record['is_late'])
                                                    <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger px-3 py-1 border border-danger border-opacity-25">Terlambat</span>
                                                @else
                                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1 border border-success border-opacity-25">Tepat Waktu</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="bi bi-calendar-x fs-1 mb-3 d-block opacity-25"></i>
                            <h6 class="fw-bold text-dark">Belum Ada Catatan Absen</h6>
                            <p class="mb-0 text-muted small">Karyawan ini belum melakukan absensi pada bulan yang dipilih.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection