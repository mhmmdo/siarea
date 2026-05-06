@extends('layouts.user')

@section('title', 'Riwayat Gaji Saya - SIAREA')

@push('styles')
<style>
    /* Styling Khusus Riwayat Gaji User */
    .salary-card {
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        border: 1px solid var(--cafe-border);
        margin-bottom: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
    }

    .amount-large {
        font-family: 'Playfair Display', serif;
        font-weight: 800;
        color: var(--cafe-primary);
        font-size: 1.25rem;
    }

    .amount-label {
        font-size: 0.75rem;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
    }

    .period-text {
        font-weight: 700;
        color: var(--cafe-secondary);
        font-size: 1.1rem;
    }

    .financial-detail-row {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px dashed #eee;
    }
    .financial-detail-row:last-child { border-bottom: none; }

    @media (min-width: 992px) {
        .desktop-px { padding: 0 3rem; }
    }
</style>
@endpush

@section('content')
    <div class="app-header py-4 px-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 style="font-family: 'Playfair Display', serif; font-weight: 700; margin-bottom: 0;">Dompet Saya</h2>
                <p class="mb-0 opacity-75 small">Riwayat penghasilan bulanan Anda</p>
            </div>
            <div class="bg-white bg-opacity-20 p-2 rounded-3 text-white">
                <i class="bi bi-wallet2 fs-3"></i>
            </div>
        </div>
    </div>

    <div class="desktop-px mt-n3 px-3">
        
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="margin-top: -1.5rem;">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3 text-dark small text-uppercase letter-spacing-1">Ketentuan Gaji Dasar</h6>
                <div class="row g-3">
                    <div class="col-6 border-end">
                        <small class="text-muted d-block">Gaji Pokok</small>
                        <strong class="text-dark">Rp {{ number_format($employee->basic_salary, 0, ',', '.') }}</strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Denda Telat</small>
                        <strong class="text-danger">Rp {{ number_format($employee->late_deduction_amount, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if($salaries->count() > 0)
            <div class="card border-0 shadow-sm rounded-4 d-none d-lg-block mb-5">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Periode</th>
                                    <th class="text-end">Gaji Pokok</th>
                                    <th class="text-end">Potongan</th>
                                    <th class="text-end">Gaji Bersih</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salaries as $salary)
                                    <tr class="border-bottom">
                                        <td class="ps-4 py-3">
                                            <strong class="text-dark">{{ \Carbon\Carbon::parse($salary->period_date)->translatedFormat('F Y') }}</strong>
                                        </td>
                                        <td class="text-end text-muted">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</td>
                                        <td class="text-end text-danger">- Rp {{ number_format($salary->total_deduction, 0, ',', '.') }}</td>
                                        <td class="text-end">
                                            <span class="fw-bold" style="color: var(--cafe-primary);">Rp {{ number_format($salary->final_salary, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if($salary->status === 'paid')
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">Dibayar</span>
                                            @elseif($salary->status === 'approved')
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-3">Disetujui</span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill px-3">Draft</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="d-lg-none">
                @foreach($salaries as $salary)
                    <div class="salary-card">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="period-text">{{ \Carbon\Carbon::parse($salary->period_date)->translatedFormat('F Y') }}</span>
                            @if($salary->status === 'paid')
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill small">Lunas</span>
                            @elseif($salary->status === 'approved')
                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill small">Valid</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 rounded-pill small">Draft</span>
                            @endif
                        </div>

                        <div class="bg-light rounded-4 p-3 mb-3">
                            <div class="financial-detail-row">
                                <span class="small text-muted">Gaji Dasar</span>
                                <span class="small fw-semibold text-dark">Rp {{ number_format($salary->base_salary, 0, ',', '.') }}</span>
                            </div>
                            <div class="financial-detail-row">
                                <span class="small text-muted">Potongan Telat</span>
                                <span class="small fw-semibold text-danger">- Rp {{ number_format($salary->total_deduction, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="amount-label">Gaji Bersih Diterima</div>
                                <div class="amount-large">Rp {{ number_format($salary->final_salary, 0, ',', '.') }}</div>
                            </div>
                            <div class="text-end">
                                <i class="bi bi-receipt text-cafe fs-3 opacity-50"></i>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="d-flex justify-content-center my-4 pb-5">
                {{ $salaries->links('pagination::bootstrap-4') }}
            </div>

        @else
            <div class="text-center py-5 bg-white rounded-4 shadow-sm border mx-3">
                <div class="icon-box gold mx-auto mb-3" style="width: 80px; height: 80px; font-size: 2.5rem; background: rgba(184, 134, 11, 0.1); color: var(--cafe-primary); display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <h5 class="fw-bold text-dark">Belum Ada Data Gaji</h5>
                <p class="text-muted small">Slip gaji bulanan Anda akan muncul di sini setelah dihitung oleh admin.</p>
            </div>
        @endif

        <div class="text-center mt-2 d-none d-lg-block pb-5">
            <a href="{{ route('user.dashboard') }}" class="btn btn-light shadow-sm border px-4 py-2 text-muted fw-semibold rounded-pill">
                <i class="bi bi-arrow-left me-2"></i> Kembali ke Beranda
            </a>
        </div>
    </div>
@endsection