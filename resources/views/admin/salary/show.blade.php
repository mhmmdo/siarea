@extends('layouts.app')

@section('title', 'Detail Gaji - SIAREA')

@push('styles')
<style>
    /* Styling Khusus Slip Gaji Premium */
    .receipt-card {
        background: #fff;
        border: 1px solid var(--cafe-border);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.02);
        position: relative;
        overflow: hidden;
    }

    .receipt-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 6px;
        background: linear-gradient(90deg, var(--cafe-primary), var(--cafe-secondary));
    }

    .receipt-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
    }

    .receipt-label {
        color: #888;
        font-weight: 500;
        font-size: 0.95rem;
    }

    .receipt-value {
        color: var(--cafe-secondary);
        font-weight: 600;
        font-size: 1.05rem;
    }

    .receipt-divider {
        border-top: 2px dashed var(--cafe-border);
        margin: 1rem 0;
    }

    .receipt-total .receipt-label {
        color: var(--cafe-secondary);
        font-weight: 700;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .receipt-total .receipt-value {
        color: var(--cafe-primary);
        font-weight: 800;
        font-size: 1.75rem;
        font-family: 'Playfair Display', serif;
    }

    /* Avatar & Info Karyawan */
    .employee-card {
        background: linear-gradient(135deg, rgba(248, 246, 243, 0.8) 0%, #fff 100%);
        border: 1px solid var(--cafe-border);
        border-radius: 16px;
        padding: 1.5rem;
    }

    .avatar-wrapper {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: white;
        border: 2px solid var(--cafe-primary);
        padding: 2px;
        box-shadow: 0 4px 10px rgba(184, 134, 11, 0.15);
    }

    /* Timeline Tracking Status */
    .timeline {
        position: relative;
        padding-left: 2rem;
        margin: 0;
        list-style: none;
    }
    .timeline::before {
        content: '';
        position: absolute;
        top: 0; bottom: 0; left: 7px;
        width: 2px;
        background: var(--cafe-border);
    }
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    .timeline-item:last-child { margin-bottom: 0; }
    
    .timeline-marker {
        position: absolute;
        left: -2rem;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: white;
        border: 3px solid var(--cafe-border);
        z-index: 1;
    }
    .timeline-item.completed .timeline-marker {
        border-color: var(--cafe-primary);
        background: var(--cafe-primary);
        box-shadow: 0 0 0 3px rgba(184, 134, 11, 0.2);
    }
    
    .timeline-content h6 {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 0.2rem;
        color: var(--cafe-secondary);
    }
    .timeline-content p {
        font-size: 0.8rem;
        color: #888;
        margin: 0;
    }

    /* Print Specific Styles */
    @media print {
        body { background: white !important; }
        .sidebar, .topbar, .btn-group, .action-card { display: none !important; }
        .main-content { margin-left: 0 !important; padding: 0 !important; }
        .receipt-card { box-shadow: none !important; border: 1px solid #000 !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Slip Gaji Karyawan</h1>
            <p class="text-muted mb-0">Periode Pembayaran: <strong class="text-dark">{{ \Carbon\Carbon::parse($salary->period_date)->translatedFormat('F Y') }}</strong></p>
        </div>
        <div class="btn-group gap-2">
            <button onclick="window.print()" class="btn btn-cafe-outline shadow-sm px-3 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-printer"></i> Cetak Slip
            </button>
            <a href="{{ route('admin.salary.index') }}" class="btn btn-light shadow-sm border px-3 py-2 text-muted d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row gx-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="receipt-card mb-4 p-4 p-md-5">
                <div class="d-flex justify-content-between align-items-start mb-4 pb-4 border-bottom">
                    <div>
                        <h4 class="fw-bold mb-1" style="color: var(--cafe-secondary);">SIAREA CAFE</h4>
                        <p class="text-muted small mb-0">Rincian Kompensasi & Pendapatan</p>
                    </div>
                    <div class="text-end">
                        <span class="d-block text-muted small mb-1">Status Dokumen</span>
                        @if ($salary->status === 'draft')
                            <span class="badge rounded-pill bg-warning bg-opacity-10 text-warning px-3 py-2 border border-warning border-opacity-25"><i class="bi bi-hourglass-split me-1"></i> DRAFT</span>
                        @elseif ($salary->status === 'approved')
                            <span class="badge rounded-pill bg-info bg-opacity-10 text-info px-3 py-2 border border-info border-opacity-25"><i class="bi bi-check2-circle me-1"></i> DISETUJUI</span>
                        @elseif ($salary->status === 'paid')
                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-2 border border-success border-opacity-25"><i class="bi bi-check-circle-fill me-1"></i> LUNAS DIBAYAR</span>
                        @endif
                    </div>
                </div>

                <div class="receipt-row">
                    <span class="receipt-label">Gaji Pokok (Base Salary)</span>
                    <span class="receipt-value">Rp {{ number_format($salary->base_salary ?? 0, 0, ',', '.') }}</span>
                </div>
                
                <div class="receipt-row">
                    <span class="receipt-label">Potongan Keterlambatan (Deductions)</span>
                    <span class="receipt-value text-danger">- Rp {{ number_format($salary->total_deduction ?? 0, 0, ',', '.') }}</span>
                </div>

                <div class="receipt-divider"></div>

                <div class="receipt-row receipt-total pt-2">
                    <span class="receipt-label">Total Gaji Bersih (Take Home Pay)</span>
                    <span class="receipt-value">Rp {{ number_format($salary->final_salary ?? 0, 0, ',', '.') }}</span>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-exclamation-octagon me-2 text-danger opacity-75"></i> Log Pemotongan Gaji (Terlambat)
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if (!empty($salary->employee->lateRecords) && count($salary->employee->lateRecords) > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead style="background-color: rgba(248, 246, 243, 0.5); border-bottom: 1px solid var(--cafe-border);">
                                    <tr>
                                        <th class="ps-4">Tanggal Kejadian</th>
                                        <th>Durasi Telat</th>
                                        <th class="text-end pe-4">Nominal Potongan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($salary->employee->lateRecords as $late)
                                        <tr style="border-bottom: 1px solid var(--cafe-border);">
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center gap-2">
                                                    <i class="bi bi-calendar-x text-muted opacity-50"></i>
                                                    <span class="fw-medium text-dark">{{ \Carbon\Carbon::parse($late->date)->translatedFormat('d F Y') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-danger border px-2 py-1"><i class="bi bi-clock me-1"></i> {{ $late->duration_minutes ?? 0 }} menit</span>
                                            </td>
                                            <td class="text-end pe-4">
                                                <span class="fw-bold text-danger">- Rp {{ number_format($late->late_deduction_amount ?? 0, 0, ',', '.') }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center p-5 text-muted">
                            <i class="bi bi-check2-circle fs-1 text-success opacity-50 d-block mb-3"></i>
                            <h6 class="fw-bold text-dark">Sempurna!</h6>
                            <p class="mb-0 small">Karyawan ini tidak memiliki catatan keterlambatan pada periode ini. <br>Tidak ada pemotongan gaji.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            
            <div class="card border-0 shadow-sm mb-4 action-card" style="border-radius: 16px; background: linear-gradient(135deg, var(--cafe-secondary) 0%, #3a2a22 100%);">
                <div class="card-body p-4 text-center">
                    <h6 class="text-white opacity-75 mb-3 text-uppercase small fw-bold">Panel Tindakan</h6>
                    
                    @if ($salary->status === 'draft')
                        <form action="{{ route('admin.salary.approve', $salary->id) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-3 fw-bold d-flex justify-content-center align-items-center gap-2" style="border-radius: 10px; background-color: #2E8B57; border-color: #2E8B57;">
                                <i class="bi bi-check-lg fs-5"></i> Setujui Nominal Gaji
                            </button>
                        </form>
                        <p class="text-white opacity-50 small mt-3 mb-0">Klik setuju jika perhitungan sudah benar dan siap untuk ditransfer.</p>
                    @endif

                    @if ($salary->status === 'approved')
                        <form action="{{ route('admin.salary.mark-paid', $salary->id) }}" method="POST" class="mb-2" onsubmit="return confirm('Apakah Anda yakin sudah mentransfer gaji ini ke karyawan?');">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold d-flex justify-content-center align-items-center gap-2" style="border-radius: 10px;">
                                <i class="bi bi-cash-stack fs-5"></i> Tandai Sudah Ditransfer
                            </button>
                        </form>
                        <p class="text-white opacity-50 small mt-3 mb-0">Klik tombol ini hanya jika Anda sudah memberikan/mentransfer uang kepada karyawan.</p>
                    @endif

                    @if ($salary->status === 'paid')
                        <div class="bg-white bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-check-circle-fill text-success fs-1 mb-2 d-block"></i>
                            <h6 class="text-white fw-bold mb-1">Transaksi Selesai</h6>
                            <p class="text-white opacity-75 small mb-0">Gaji telah dibayarkan.</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="employee-card mb-4">
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-secondary border-opacity-10">
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ $salary->employee->user->id }}&backgroundColor=FBF9F6" alt="Avatar" class="avatar-wrapper">
                    <div>
                        <h6 class="fw-bold mb-1 text-dark" style="font-size: 1.1rem;">{{ $salary->employee->user->name }}</h6>
                        <span class="badge bg-white text-dark border px-2">ID: #{{ str_pad($salary->employee->user->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </div>
                <div class="mb-2">
                    <small class="text-muted d-block">Alamat Email</small>
                    <span class="fw-medium text-dark">{{ $salary->employee->user->email }}</span>
                </div>
                <div>
                    <small class="text-muted d-block">Shift Kerja</small>
                    <span class="fw-medium text-dark"><i class="bi bi-sun text-cafe me-1"></i> {{ $salary->employee->shift->name ?? '-' }}</span>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white p-4 border-bottom-0">
                    <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-clock-history me-2 text-cafe"></i> Jejak Dokumen
                    </h6>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <ul class="timeline">
                        <li class="timeline-item completed">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>Gaji Dihitung (Draft)</h6>
                                <p>{{ $salary->created_at ? \Carbon\Carbon::parse($salary->created_at)->translatedFormat('d F Y, H:i') : '-' }}</p>
                            </div>
                        </li>

                        <li class="timeline-item @if($salary->approved_at) completed @endif">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>Gaji Disetujui</h6>
                                @if ($salary->approved_at)
                                    <p>{{ \Carbon\Carbon::parse($salary->approved_at)->translatedFormat('d F Y, H:i') }}</p>
                                @else
                                    <p>Menunggu persetujuan admin</p>
                                @endif
                            </div>
                        </li>

                        <li class="timeline-item @if($salary->paid_at) completed @endif">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <h6>Lunas Dibayar</h6>
                                @if ($salary->paid_at)
                                    <p class="text-success fw-bold">{{ \Carbon\Carbon::parse($salary->paid_at)->translatedFormat('d F Y, H:i') }}</p>
                                @else
                                    <p>Menunggu pembayaran</p>
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
@endsection