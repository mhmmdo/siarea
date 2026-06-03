@extends('layouts.user')

@section('title', 'Beranda Karyawan - SIAREA')

@push('styles')
<style>
    /* Styling Khusus Isi Dashboard */
    .app-header {
        background: linear-gradient(135deg, var(--cafe-secondary) 0%, #3a2a22 100%);
        padding: 2rem 1.5rem 4rem 1.5rem;
        border-radius: 0 0 30px 30px;
        color: white;
    }

    .avatar-sm {
        width: 45px; height: 45px;
        border-radius: 50%;
        border: 2px solid var(--cafe-primary);
        background: white;
    }

    .status-card-wrapper {
        margin: -3rem 1.5rem 2rem 1.5rem;
        position: relative;
        z-index: 10;
    }

    .status-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        border: 1px solid var(--cafe-border);
        text-align: center;
    }

    /* TOMBOL SCAN UTAMA (Muncul di Mobile & Desktop) */
    .btn-scan-main {
        width: 90px; height: 90px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--cafe-primary), #9E7308);
        color: white;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.5rem;
        margin: 0 auto 1rem auto;
        box-shadow: 0 8px 20px rgba(184, 134, 11, 0.4);
        border: none;
        transition: transform 0.2s;
        text-decoration: none;
    }
    .btn-scan-main:active { transform: scale(0.95); }

    .btn-scan-checkout {
        background: linear-gradient(135deg, #FF8C00, #B22222);
        box-shadow: 0 8px 20px rgba(178, 34, 34, 0.4);
    }

    .info-list-item {
        display: flex; justify-content: space-between; align-items: center;
        padding: 1.25rem; background: white; border: 1px solid var(--cafe-border);
        border-radius: 16px; margin-bottom: 0.75rem; box-shadow: 0 4px 10px rgba(0,0,0,0.02);
    }

    @media (min-width: 992px) {
        .app-header { padding: 3rem 3rem 5rem 3rem; border-radius: 0; }
        .status-card-wrapper { margin: -3.5rem 3rem 2rem 3rem; }
        .desktop-px { padding: 0 3rem; }
    }
</style>
@endpush

@section('content')
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <p class="mb-0 opacity-75 small">Selamat datang kembali,</p>
                <h1 style="font-family: 'Playfair Display', serif; font-size: 1.75rem; margin: 0;">{{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <small class="opacity-75 d-block">Shift Aktif</small>
                    <strong class="text-white">{{ auth()->user()->employee->shift->name ?? '-' }}</strong>
                </div>
                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ auth()->user()->id }}&backgroundColor=FBF9F6" alt="Profile" class="avatar-sm">
            </div>
        </div>
        <p class="mb-0 small opacity-75"><i class="bi bi-calendar3 me-1"></i> {{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    <div class="status-card-wrapper">
        <div class="row">
            <div class="col-lg-6 mx-auto">
                <div class="status-card">
                    
                    @if(!$todayAttendance)
                        <div class="text-center mb-4">
                            <span class="badge bg-light text-dark border px-3 py-2 mb-3 rounded-pill">Status: Belum Absen</span>
                            <h5 class="fw-bold text-dark">Siap Mulai Shift?</h5>
                            <p class="text-muted small mb-0">Klik tombol di bawah untuk melakukan pemindaian QR Code dan memulai pekerjaan Anda.</p>
                        </div>
                        
                        <a href="{{ route('user.scan') }}" class="text-decoration-none">
                            <button class="btn-scan-main mx-auto">
                                <i class="bi bi-qr-code-scan"></i>
                            </button>
                        </a>
                        <h6 class="fw-bold mt-2" style="color: var(--cafe-primary);">Scan Check-In</h6>
                    
                    @elseif($todayAttendance && !$todayAttendance->checkout)
                        <div class="text-center mb-3">
                            <div class="d-inline-flex align-items-center gap-2 badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 mb-3 rounded-pill">
                                <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true" style="width: 0.75rem; height: 0.75rem;"></span>
                                Sedang Bekerja
                            </div>
                            <h6 class="text-dark mb-1">Jam Masuk: <strong style="color: var(--cafe-primary);">{{ $todayAttendance->check_in_time->format('H:i') }} WIB</strong></h6>
                            
                            @if($todayAttendance->is_late)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 mt-1">Terlambat</span>
                            @endif
                        </div>

                        <hr class="border-secondary opacity-10 my-4" style="border-style: dashed;">

                        @if($canCheckout)
                            <div class="text-center mb-3">
                                <p class="text-muted small mb-0">Jam kerja telah selesai. Silakan lakukan absen pulang.</p>
                            </div>
                            <a href="{{ route('user.scan') }}" class="text-decoration-none">
                                <button class="btn-scan-main btn-scan-checkout mx-auto">
                                    <i class="bi bi-box-arrow-right"></i>
                                </button>
                            </a>
                            <h6 class="fw-bold text-danger mt-2">Scan Check-Out</h6>
                        @else
                            <div class="bg-light p-3 rounded-4 mb-2">
                                <i class="bi bi-lock-fill text-muted fs-2 d-block mb-2"></i>
                                <p class="text-muted small mb-0">Belum waktunya check-out.<br>Jadwal pulang Anda: <strong class="text-dark">{{ $shiftEndTime }}</strong></p>
                                @if($timeUntilCheckout)
                                    <span class="badge bg-white text-dark border mt-2 py-2 px-3 shadow-sm">Menunggu {{ $timeUntilCheckout }}</span>
                                @endif
                            </div>
                        @endif

                    @else
                        <div class="text-center">
                            <div class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-4 py-2 mb-3 rounded-pill">
                                <i class="bi bi-check2-all me-1"></i> Selesai Shift
                            </div>
                            <h4 class="fw-bold text-dark mb-4" style="font-family: 'Playfair Display', serif;">Kerja Bagus Hari Ini! 🎉</h4>
                            
                            <div class="d-flex justify-content-center gap-4 text-start bg-light p-3 rounded-4">
                                <div class="text-center px-2">
                                    <small class="text-muted d-block mb-1">Masuk</small>
                                    <strong class="text-dark fs-5">{{ $todayAttendance->check_in_time->format('H:i') }}</strong>
                                </div>
                                <div style="width: 2px; background: #ddd; border-radius: 2px;"></div>
                                <div class="text-center px-2">
                                    <small class="text-muted d-block mb-1">Pulang</small>
                                    <strong class="text-dark fs-5">{{ $todayAttendance->checkout->check_out_time->format('H:i') }}</strong>
                                </div>
                            </div>
                            
                            <div class="mt-4 pt-3 border-top dashed">
                                <p class="text-muted small mb-0">Total Durasi Kerja: <strong class="text-dark">{{ $todayAttendance->getElapsedTime() }}</strong></p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="desktop-px px-3 mb-4">
        <h5 class="fw-bold mb-3" style="color: var(--cafe-secondary);">Ringkasan Bulan Ini</h5>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="info-list-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-2 rounded-3 text-cafe"><i class="bi bi-calendar-check fs-4"></i></div>
                        <h6 class="mb-0 fw-bold text-dark">Total Kehadiran</h6>
                    </div>
                    <h4 class="mb-0 fw-bold" style="color: var(--cafe-primary);">{{ $monthAttendance }} <small class="text-muted fs-6 fw-normal">Hari</small></h4>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="info-list-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-light p-2 rounded-3 text-cafe"><i class="bi bi-cash-stack fs-4"></i></div>
                        <h6 class="mb-0 fw-bold text-dark">Estimasi Gaji Masuk</h6>
                    </div>
                    <h5 class="mb-0 fw-bold text-success">
                        @if($latestSalary)
                            Rp {{ number_format($latestSalary->final_salary, 0, ',', '.') }}
                        @else
                            <span class="text-muted fs-6 fw-normal">Belum Tersedia</span>
                        @endif
                    </h5>
                </div>
            </div>
        </div>
    </div>
@endsection