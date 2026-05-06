@extends('layouts.user')

@section('title', 'Profil Saya - SIAREA')

@push('styles')
<style>
    /* Styling Khusus Halaman Profil User */
    .profile-banner-container {
        position: relative;
        margin-bottom: 3rem;
    }

    .profile-cover {
        background: linear-gradient(135deg, var(--cafe-secondary) 0%, #3a2a22 100%);
        height: 140px;
        border-radius: 0 0 30px 30px;
        position: relative;
    }

    @media (min-width: 992px) {
        .profile-cover {
            border-radius: 20px;
            margin-top: 1rem;
        }
    }

    .profile-avatar-wrapper {
        position: absolute;
        bottom: -40px;
        left: 50%;
        transform: translateX(-50%);
        text-align: center;
    }

    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        border: 4px solid var(--cafe-light);
        background: white;
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }

    .profile-name-header {
        margin-top: 3.5rem;
        text-align: center;
    }

    .profile-name-header h2 {
        font-family: 'Playfair Display', serif;
        color: var(--cafe-secondary);
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .profile-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        border: 1px solid var(--cafe-border);
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        height: 100%;
    }

    .profile-section-title {
        font-weight: 700;
        color: var(--cafe-secondary);
        font-size: 1.1rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .profile-section-title i { color: var(--cafe-primary); }

    .info-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 0;
        border-bottom: 1px dashed var(--cafe-border);
    }
    .info-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(184, 134, 11, 0.08);
        color: var(--cafe-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    .info-content { flex-grow: 1; }
    
    .info-label {
        font-size: 0.75rem;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.2rem;
        font-weight: 600;
    }

    .info-value {
        font-size: 0.95rem;
        color: var(--cafe-dark);
        font-weight: 600;
        margin: 0;
    }

    /* Kotak Finansial Khusus di Profil */
    .financial-box {
        background: linear-gradient(to right, #fff, rgba(184, 134, 11, 0.03));
        border: 1px solid rgba(184, 134, 11, 0.2);
        border-radius: 12px;
        padding: 1rem;
        margin-top: 0.5rem;
    }

    .financial-box.danger {
        background: linear-gradient(to right, #fff, rgba(220, 53, 69, 0.03));
        border-color: rgba(220, 53, 69, 0.15);
    }
</style>
@endpush

@section('content')
    <div class="desktop-px px-lg-4">
        
        <div class="profile-banner-container">
            <div class="profile-cover"></div>
            <div class="profile-avatar-wrapper">
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ $user->id }}&backgroundColor=FBF9F6" alt="Profile" class="profile-avatar">
            </div>
            
            <div class="profile-name-header">
                <h2>{{ $employee->full_name ?? $user->name }}</h2>
                <div class="d-flex align-items-center justify-content-center gap-2 mt-1">
                    <span class="badge bg-light text-dark border px-3 py-1">ID: #{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</span>
                    @if($user->status === 'active')
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-1">
                            <i class="bi bi-check-circle-fill me-1"></i> Aktif
                        </span>
                    @else
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-1">
                            Nonaktif
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="row px-3 px-lg-0 g-4 mb-4">
            
            <div class="col-lg-6">
                <div class="profile-card">
                    <div class="profile-section-title">
                        <i class="bi bi-person-badge"></i> Identitas Akun
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-person"></i></div>
                        <div class="info-content">
                            <div class="info-label">Nama Pengguna</div>
                            <div class="info-value">{{ $user->name }}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-envelope"></i></div>
                        <div class="info-content">
                            <div class="info-label">Alamat Email</div>
                            <div class="info-value">{{ $user->email }}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-telephone"></i></div>
                        <div class="info-content">
                            <div class="info-label">Nomor Telepon / WhatsApp</div>
                            <div class="info-value">{{ $employee->phone ?? 'Belum diatur' }}</div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon" style="background: transparent; border: 1px solid var(--cafe-border); color: #888;"><i class="bi bi-shield-lock"></i></div>
                        <div class="info-content">
                            <div class="info-label">Hak Akses Sistem</div>
                            <div class="info-value">
                                @if($user->role === 'admin')
                                    <span class="badge bg-danger">Administrator</span>
                                @else
                                    <span class="badge bg-dark">Karyawan</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="profile-card">
                    <div class="profile-section-title">
                        <i class="bi bi-briefcase"></i> Detail Operasional
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-sun"></i></div>
                        <div class="info-content">
                            <div class="info-label">Jadwal Shift Aktif</div>
                            <div class="info-value">
                                {{ $employee->shift->name ?? '-' }}
                                @if($employee->shift)
                                    <small class="text-muted fw-normal ms-1">({{ \Carbon\Carbon::parse($employee->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('H:i') }})</small>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="info-item">
                        <div class="info-icon"><i class="bi bi-calendar-check"></i></div>
                        <div class="info-content">
                            <div class="info-label">Tanggal Bergabung</div>
                            <div class="info-value">
                                {{ $employee->hire_date ? \Carbon\Carbon::parse($employee->hire_date)->translatedFormat('d F Y') : 'Belum diatur' }}
                            </div>
                        </div>
                    </div>

                    <div class="financial-box mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="info-label mb-0 text-dark">Gaji Pokok Dasar</div>
                                <small class="text-muted" style="font-size: 0.7rem;">Nilai gaji sebelum potongan</small>
                            </div>
                            <div class="fs-5 fw-bold" style="color: var(--cafe-primary);">
                                Rp {{ number_format($employee->basic_salary ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <div class="financial-box danger mt-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="info-label mb-0 text-danger">Potongan Terlambat</div>
                                <small class="text-muted" style="font-size: 0.7rem;">Denda berlaku per 15 menit telat</small>
                            </div>
                            <div class="fs-6 fw-bold text-danger">
                                Rp {{ number_format($employee->late_deduction_amount ?? 0, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="text-center mt-2 d-none d-lg-block pb-4">
            <a href="{{ route('user.dashboard') }}" class="btn btn-light shadow-sm border px-4 py-2 text-muted fw-semibold rounded-pill">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Beranda
            </a>
        </div>

    </div>
@endsection