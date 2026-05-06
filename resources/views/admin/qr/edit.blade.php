@extends('layouts.app')

@section('title', 'Edit QR Code - SIAREA')

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.css" />
    
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

        /* Map Container Styling */
        .map-wrapper {
            border: 2px solid var(--cafe-border);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            position: relative;
            z-index: 1;
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
    </style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Edit Titik QR Code</h1>
            <p class="text-muted mb-0">Perbarui pengaturan koordinat dan radius absensi</p>
        </div>
        <div>
            <a href="{{ route('admin.qr.index') }}" class="btn btn-cafe-outline shadow-sm d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row gx-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card border-0 shadow-sm" style="border-radius: 16px;">
                <div class="card-body p-4 p-md-5">
                    <form action="{{ route('admin.qr.update', $qr->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="form-section-title">
                            <i class="bi bi-qr-code-scan"></i> Detail Lokasi
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-12 mb-3">
                                <label for="name" class="form-label">Nama Area/Lokasi <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0"><i class="bi bi-pin-map"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $qr->name ?? '') }}" required>
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="mb-4" style="opacity: 0.1;">

                        <div class="form-section-title d-flex justify-content-between align-items-center">
                            <div><i class="bi bi-globe-americas"></i> Pengaturan Geofencing</div>
                            <button type="button" class="btn btn-sm btn-cafe-outline px-3" onclick="detectCurrentLocation()" id="geoBtn">
                                <i class="bi bi-crosshair me-1"></i> Perbarui ke Lokasi Saya
                            </button>
                        </div>

                        <div class="form-group mb-4">
                            <div class="map-wrapper mb-2">
                                <div id="map" style="height: 380px;"></div>
                            </div>
                            <small class="text-muted"><i class="bi bi-info-circle me-1 text-cafe"></i> Klik pada peta untuk memindahkan titik pusat lokasi validasi absensi karyawan.</small>
                        </div>

                        <div class="row bg-light p-3 rounded-4 border mb-4 mx-0">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.000001" class="form-control form-control-sm @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude', $qr->latitude ?? '') }}" required readonly style="background-color: #e9ecef;">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.000001" class="form-control form-control-sm @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude', $qr->longitude ?? '') }}" required readonly style="background-color: #e9ecef;">
                            </div>
                            <div class="col-md-4">
                                <label for="radius_meters" class="form-label text-cafe">Radius Validasi</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control @error('radius_meters') is-invalid @enderror" id="radius_meters" name="radius_meters" value="{{ old('radius_meters', $qr->radius_meters ?? 100) }}" min="10" max="1000">
                                    <span class="input-group-text">m</span>
                                </div>
                            </div>
                        </div>

                        <hr class="mb-4" style="opacity: 0.1;">

                        <div class="form-section-title">
                            <i class="bi bi-gear"></i> Konfigurasi Operasional
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-7 mb-3 mb-md-0">
                                <div class="alert alert-light border shadow-sm h-100 d-flex flex-column justify-content-center m-0">
                                    <h6 class="fw-bold mb-2 text-dark"><i class="bi bi-clock-history text-cafe me-1"></i> Info Shift Terhubung</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Nama Shift:</span>
                                        <span class="fw-semibold text-dark">{{ $qr->shift->name ?? '-' }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="text-muted small">Jam Aktif Absen:</span>
                                        <span class="badge bg-white text-dark border"><i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($qr->shift->start_time ?? '00:00')->format('H:i') }} - {{ \Carbon\Carbon::parse($qr->shift->end_time ?? '00:00')->format('H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="form-group h-100 bg-light p-3 rounded-4 border d-flex flex-column justify-content-center m-0">
                                    <label class="form-label mb-2">Status Operasional</label>
                                    <div class="form-check form-switch d-flex align-items-center gap-2 m-0 p-0">
                                        <input class="form-check-input ms-0 mt-0" type="checkbox" id="is_active" name="is_active" value="1" @if (old('is_active', $qr->is_active)) checked @endif>
                                        <label class="form-check-label fw-bold mb-0" for="is_active">Aktifkan QR</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 pt-2">
                            <button type="submit" class="btn btn-cafe shadow-sm px-4 py-2 d-flex align-items-center gap-2">
                                <i class="bi bi-check2-circle"></i> Simpan Perubahan
                            </button>
                            <a href="{{ route('admin.qr.show', $qr->id) }}" class="btn btn-light shadow-sm px-4 py-2 border d-flex align-items-center gap-2 text-muted">
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
                        <i class="bi bi-info-square"></i>
                    </div>
                    <h5 class="fw-bold mb-0" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">Data QR Code</h5>
                </div>
                
                <div class="info-item">
                    <h6><i class="bi bi-hash me-1"></i> Token Enkripsi</h6>
                    <p class="font-monospace text-break" style="font-size: 0.85rem; background: #f8f9fa; padding: 6px; border-radius: 6px; border: 1px solid #eee;">{{ $qr->code }}</p>
                </div>

                <div class="info-item">
                    <h6><i class="bi bi-calendar-plus me-1"></i> Tanggal Dibuat</h6>
                    <p>{{ $qr->created_at ? \Carbon\Carbon::parse($qr->created_at)->translatedFormat('d F Y') : '-' }}</p>
                    @if($qr->created_at)
                        <span class="text-muted small">{{ \Carbon\Carbon::parse($qr->created_at)->format('H:i') }} WIB</span>
                    @endif
                </div>

                <div class="info-item">
                    <h6><i class="bi bi-clock-history me-1"></i> Terakhir Diubah</h6>
                    <p>{{ $qr->updated_at ? \Carbon\Carbon::parse($qr->updated_at)->diffForHumans() : '-' }}</p>
                    @if($qr->updated_at)
                        <span class="text-muted small">{{ \Carbon\Carbon::parse($qr->updated_at)->translatedFormat('d F Y, H:i') }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
        <script>
            // Ambil koordinat awal dari database
            const initLat = {{ $qr->latitude ?? '-3.3194' }};
            const initLng = {{ $qr->longitude ?? '114.5908' }};
            const initRadius = {{ $qr->radius_meters ?? 100 }};

            const map = L.map('map').setView([initLat, initLng], 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            let marker = L.marker([initLat, initLng]).addTo(map);
            let radiusCircle = L.circle([initLat, initLng], {
                color: '#B8860B',
                fillColor: '#B8860B',
                fillOpacity: 0.15,
                weight: 2,
                radius: initRadius
            }).addTo(map);

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const radiusInput = document.getElementById('radius_meters');

            function updateRadius() {
                if (!marker) return;
                
                let radiusValue = parseInt(radiusInput.value);
                if (isNaN(radiusValue) || radiusValue <= 0) radiusValue = 100;

                if (radiusCircle) {
                    map.removeLayer(radiusCircle);
                }

                radiusCircle = L.circle(marker.getLatLng(), {
                    color: '#B8860B',
                    fillColor: '#B8860B',
                    fillOpacity: 0.15,
                    weight: 2,
                    radius: radiusValue
                }).addTo(map);
            }

            // Click event pada Peta
            map.on('click', function (e) {
                if (marker) map.removeLayer(marker);
                
                marker = L.marker(e.latlng).addTo(map);
                latInput.value = e.latlng.lat.toFixed(6);
                lngInput.value = e.latlng.lng.toFixed(6);
                
                updateRadius();
            });

            // Update radius saat admin mengubah angka
            radiusInput.addEventListener('input', updateRadius);

            let accuracyCircle = null;

            // Fungsi Deteksi Lokasi Admin
            function detectCurrentLocation() {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung fitur deteksi lokasi (Geolocation).');
                    return;
                }

                const btn = document.getElementById('geoBtn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split spinner-border spinner-border-sm me-1" role="status"></i> Mendeteksi...';
                btn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        if (marker) map.removeLayer(marker);
                        if (accuracyCircle) map.removeLayer(accuracyCircle);

                        marker = L.marker([lat, lng]).addTo(map);
                        
                        accuracyCircle = L.circle([lat, lng], {
                            color: '#4A90E2',
                            fillColor: '#4A90E2',
                            fillOpacity: 0.1,
                            weight: 1,
                            radius: accuracy
                        }).addTo(map);

                        latInput.value = lat.toFixed(6);
                        lngInput.value = lng.toFixed(6);

                        updateRadius();

                        map.flyTo([lat, lng], 17, { animate: true, duration: 1.5 });

                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    },
                    function (error) {
                        let errorMsg = 'Gagal mendeteksi lokasi.';
                        if (error.code === error.PERMISSION_DENIED) errorMsg = 'Izin akses lokasi ditolak.';
                        else if (error.code === error.POSITION_UNAVAILABLE) errorMsg = 'Sinyal GPS tidak tersedia.';
                        else if (error.code === error.TIMEOUT) errorMsg = 'Waktu pencarian habis (Timeout).';
                        
                        alert('❌ Error: ' + errorMsg);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }
        </script>
    @endpush
@endsection