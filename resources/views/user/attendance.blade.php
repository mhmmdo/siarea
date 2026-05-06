@extends('layouts.user')

@section('title', 'Riwayat Absensi - SIAREA')

@push('styles')
<style>
    /* Styling Khusus Riwayat Absen User */
    .history-card {
        background: white;
        border-radius: 20px;
        padding: 1.25rem;
        border: 1px solid var(--cafe-border);
        margin-bottom: 1rem;
        transition: transform 0.2s;
        position: relative;
        overflow: hidden;
    }

    .history-card:active {
        transform: scale(0.98);
        background-color: var(--cafe-light);
    }

    .date-box {
        background: var(--cafe-light);
        color: var(--cafe-secondary);
        padding: 8px 12px;
        border-radius: 12px;
        text-align: center;
        min-width: 65px;
    }

    .date-box .day { font-size: 1.25rem; font-weight: 800; line-height: 1; }
    .date-box .month { font-size: 0.7rem; text-transform: uppercase; font-weight: 700; }

    .time-badge {
        font-family: 'Inter', sans-serif;
        font-weight: 700;
        color: var(--cafe-secondary);
        font-size: 0.95rem;
    }

    .duration-text {
        font-size: 0.75rem;
        color: #888;
    }

    @media (min-width: 992px) {
        .desktop-px { padding: 0 3rem; }
    }
</style>
@endpush

@section('content')
    <div class="app-header py-4 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 style="font-family: 'Playfair Display', serif; font-weight: 700; margin-bottom: 0;">Riwayat Absen</h2>
                <p class="mb-0 opacity-75 small">Pantau catatan kehadiran Anda</p>
            </div>
            <div class="bg-white bg-opacity-20 p-2 rounded-3">
                <i class="bi bi-calendar-check fs-3"></i>
            </div>
        </div>
    </div>

    <div class="desktop-px mt-n3 px-3">
        @if($attendance->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 d-none d-lg-block mb-5">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Tanggal</th>
                                    <th>Shift</th>
                                    <th>Jam Masuk</th>
                                    <th>Jam Pulang</th>
                                    <th>Durasi</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($attendance as $record)
                                    <tr class="border-bottom">
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold">{{ $record->date->translatedFormat('d M Y') }}</div>
                                            <small class="text-muted">{{ $record->date->translatedFormat('l') }}</small>
                                        </td>
                                        <td><span class="badge bg-light text-dark border fw-normal">{{ $record->shift->name ?? '-' }}</span></td>
                                        <td class="fw-bold text-dark">{{ $record->check_in_time->format('H:i') }}</td>
                                        <td>
                                            @if($record->checkout)
                                                <span class="fw-bold text-dark">{{ $record->checkout->check_out_time->format('H:i') }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td><small class="text-muted">{{ $record->checkout ? $record->getElapsedTime() : '-' }}</small></td>
                                        <td>
                                            @if($record->is_late)
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill">Terlambat</span>
                                            @elseif($record->checkout && $record->checkout->is_early)
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill">Pulang Awal</span>
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">Tepat Waktu</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            @if(!$record->checkout && $record->date->isToday())
                                                <a href="{{ route('user.dashboard') }}" class="btn btn-sm btn-cafe rounded-pill">Checkout Sekarang</a>
                                            @else
                                                <i class="bi bi-check2-all text-success"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-lg-none" style="margin-top: -1.5rem;">
                @foreach($attendance as $record)
                    <div class="history-card shadow-sm">
                        <div class="d-flex align-items-center gap-3">
                            <div class="date-box shadow-sm">
                                <div class="day">{{ $record->date->format('d') }}</div>
                                <div class="month">{{ $record->date->translatedFormat('M') }}</div>
                            </div>
                            
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="time-badge">
                                            {{ $record->check_in_time->format('H:i') }} 
                                            <span class="text-muted fw-normal mx-1">→</span> 
                                            {{ $record->checkout ? $record->checkout->check_out_time->format('H:i') : '--:--' }}
                                        </div>
                                        <div class="duration-text mt-1">
                                            <i class="bi bi-clock-history me-1"></i>
                                            {{ $record->checkout ? $record->getElapsedTime() : 'Sedang bekerja...' }}
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        @if($record->is_late)
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill small" style="font-size: 0.65rem;">Telat</span>
                                        @elseif($record->checkout && $record->checkout->is_early)
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill small" style="font-size: 0.65rem;">Awal</span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill small" style="font-size: 0.65rem;">Hadir</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        @if(!$record->checkout && $record->date->isToday())
                            <div class="mt-3 pt-3 border-top border-dashed text-center">
                                <a href="{{ route('user.dashboard') }}" class="btn btn-sm btn-cafe w-100 rounded-pill py-2">
                                    <i class="bi bi-box-arrow-right me-1"></i> Lakukan Absen Pulang
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center my-4 pb-5">
                {{ $attendance->links('pagination::bootstrap-4') }}
            </div>

        @else
            <div class="text-center py-5">
                <div class="icon-box gold mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem; background: rgba(184, 134, 11, 0.1); color: var(--cafe-primary); display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-calendar-x"></i>
                </div>
                <h5 class="fw-bold text-dark">Belum Ada Riwayat</h5>
                <p class="text-muted small">Catatan kehadiran Anda akan muncul di sini setelah Anda melakukan scan masuk.</p>
                <a href="{{ route('user.dashboard') }}" class="btn btn-cafe rounded-pill mt-2">Mulai Absen Sekarang</a>
            </div>
        @endif
    </div>
@endsection 