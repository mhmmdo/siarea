@extends('layouts.app')

@section('title', 'Detail QR Code - SIAREA')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" />
    <style>
        /* Styling khusus Halaman Detail QR */
        .qr-display-box {
            background: #fff;
            border: 2px dashed var(--cafe-border);
            border-radius: 16px;
            padding: 2rem;
            display: inline-block;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .qr-display-box::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            border-radius: 16px;
            border: 2px solid var(--cafe-primary);
            opacity: 0.1;
            pointer-events: none;
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

        .map-wrapper {
            border: 1px solid var(--cafe-border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            position: relative;
            z-index: 1;
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
    </style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">
                {{ $qr->name ?? 'Titik QR Code' }}
            </h1>
            <p class="text-muted mb-0">Detail informasi dan area pantauan geofencing</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.qr.index') }}" class="btn btn-light shadow-sm border px-3 py-2 text-muted d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
            <a href="{{ route('admin.qr.statistics', $qr->id) }}" class="btn btn-cafe-outline shadow-sm px-3 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-bar-chart-fill"></i> Statistik
            </a>
            <a href="{{ route('admin.qr.edit', $qr->id) }}" class="btn btn-cafe shadow-sm px-4 py-2 d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i> Edit Pengaturan
            </a>
        </div>
    </div>

    <div class="row gx-4">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-body text-center p-4">
                    <h6 class="fw-bold mb-4" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-qr-code-scan me-2 text-cafe"></i> Pindai Untuk Absen
                    </h6>
                    
                    <div class="qr-display-box shadow-sm">
                        {!! QrCode::size(220)->margin(1)->generate($qr->code) !!}
                    </div>
                    
                    <div class="mb-4">
                        <span class="text-muted small d-block mb-1">Token Identitas Unik</span>
                        <code class="text-break px-3 py-2 rounded bg-light border text-dark">{{ $qr->code }}</code>
                    </div>

                    <a href="{{ route('admin.qr.download', $qr->id) }}" class="btn btn-cafe w-100 py-2 d-flex justify-content-center align-items-center gap-2 shadow-sm">
                        <i class="bi bi-cloud-download"></i> Unduh Gambar QR
                    </a>
                    <p class="text-muted small mt-3 mb-0">Cetak dan tempelkan QR ini di area <strong>{{ $qr->name }}</strong>.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4">
                    <ul class="info-list">
                        <li>
                            <div class="info-icon"><i class="bi bi-sun"></i></div>
                            <div class="info-content w-100">
                                <h6>Shift Terhubung</h6>
                                <p class="d-flex justify-content-between align-items-center">
                                    {{ $qr->shift->name ?? '-' }}
                                    <span class="badge bg-light text-dark border fw-normal">{{ \Carbon\Carbon::parse($qr->shift->start_time ?? '00:00')->format('H:i') }} - {{ \Carbon\Carbon::parse($qr->shift->end_time ?? '00:00')->format('H:i') }}</span>
                                </p>
                            </div>
                        </li>
                        <li>
                            <div class="info-icon"><i class="bi bi-bullseye"></i></div>
                            <div class="info-content">
                                <h6>Radius Jarak Aman</h6>
                                <p>{{ $qr->radius_meters ?? 100 }} Meter</p>
                            </div>
                        </li>
                        <li>
                            <div class="info-icon" style="background: transparent; border: 1px solid var(--cafe-border); color: #888;"><i class="bi bi-shield-check"></i></div>
                            <div class="info-content">
                                <h6>Status Aktif</h6>
                                @if ($qr->is_active)
                                    <span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3 py-1 border border-success border-opacity-25 mt-1">Aktif</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary bg-opacity-10 text-secondary px-3 py-1 border border-secondary border-opacity-25 mt-1">Nonaktif</span>
                                @endif
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 16px;">
                <div class="card-header bg-white p-4 border-bottom-0 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-geo-alt-fill me-2 text-danger opacity-75"></i> Area Validasi GPS (Geofencing)
                    </h6>
                    <span class="badge bg-light text-dark border font-monospace">
                        {{ substr($qr->latitude, 0, 8) }}, {{ substr($qr->longitude, 0, 8) }}
                    </span>
                </div>
                <div class="card-body px-4 pb-4 pt-0">
                    <div class="map-wrapper">
                        <div id="map" style="height: 320px;"></div>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 16px;">
                        <div class="card-body p-4">
                            <h6 class="text-muted text-uppercase fw-bold small mb-2">Total Scan Bulan Ini</h6>
                            <h2 class="display-5 fw-bold mb-0" style="color: var(--cafe-primary);">{{ $stats->total_scans ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 text-center" style="border-radius: 16px; border-bottom: 4px solid #dc3545 !important;">
                        <div class="card-body p-4">
                            <h6 class="text-muted text-uppercase fw-bold small mb-2">Terlambat di Lokasi Ini</h6>
                            <h2 class="display-5 fw-bold mb-0 text-danger">{{ $stats->late_count ?? 0 }}</h2>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-header bg-white p-4 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0 fw-bold" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">
                        <i class="bi bi-clock-history me-2 text-cafe"></i> 10 Log Scan Terakhir
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if (!empty($recentScans) && count($recentScans) > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless align-middle mb-0">
                                <thead style="background-color: rgba(248, 246, 243, 0.5); border-bottom: 1px solid var(--cafe-border);">
                                    <tr>
                                        <th class="ps-4">Karyawan</th>
                                        <th>Shift / Jadwal</th>
                                        <th>Waktu Masuk</th>
                                        <th class="text-end pe-4">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentScans as $scan)
                                        <tr style="border-bottom: 1px solid var(--cafe-border);">
                                            <td class="ps-4 py-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="table-avatar-sm shadow-sm">
                                                        {{ strtoupper(substr($scan->employee->user->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                    <span class="fw-semibold text-dark">{{ $scan->employee->user->name ?? '-' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted small"><i class="bi bi-sun me-1"></i> {{ $scan->shift->name ?? '-' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <strong class="text-dark">{{ \Carbon\Carbon::parse($scan->created_at)->format('H:i') }} WIB</strong>
                                                    <small class="text-muted">{{ \Carbon\Carbon::parse($scan->created_at)->format('d M Y') }}</small>
                                                </div>
                                            </td>
                                            <td class="text-end pe-4">
                                                @if ($scan->is_late)
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
                        <div class="text-center p-5 text-muted">
                            <i class="bi bi-qr-code fs-1 d-block mb-3 opacity-25"></i>
                            <h6 class="fw-bold text-dark">Belum Ada Aktivitas</h6>
                            <p class="mb-0 small">Belum ada karyawan yang memindai QR Code ini untuk absensi.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
        <script>
            // Initialize Display Map (Read Only)
            const map = L.map('map', {
                zoomControl: false, // Menyembunyikan tombol + - agar bersih
                dragging: false, // Mencegah peta digeser
                scrollWheelZoom: false, // Mencegah zoom pakai scroll
                doubleClickZoom: false
            }).setView([{{ $qr->latitude ?? '-3.3194' }}, {{ $qr->longitude ?? '114.5908' }}], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            // Tambahkan Marker Emas
            const customIcon = L.divIcon({
                className: 'custom-div-icon',
                html: "<div style='background-color: var(--cafe-primary); width: 14px; height: 14px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 10px rgba(0,0,0,0.5);'></div>",
                iconSize: [14, 14],
                iconAnchor: [7, 7]
            });

            L.marker([{{ $qr->latitude ?? '-3.3194' }}, {{ $qr->longitude ?? '114.5908' }}], {icon: customIcon})
                .addTo(map)
                .bindPopup("<div class='text-center'><strong>{{ $qr->name }}</strong><br><small class='text-muted'>Radius: {{ $qr->radius_meters ?? 100 }}m</small></div>");

            // Tambahkan Lingkaran Radius Emas
            L.circle([{{ $qr->latitude ?? '-3.3194' }}, {{ $qr->longitude ?? '114.5908' }}], {
                radius: {{ $qr->radius_meters ?? 100 }},
                color: '#B8860B',
                weight: 2,
                opacity: 0.8,
                fillColor: '#B8860B',
                fillOpacity: 0.15
            }).addTo(map);
        </script>
    @endpush
@endsection