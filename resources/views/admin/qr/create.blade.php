@extends('layouts.app')

@section('title', 'Buat QR Code - SIAREA')

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
            z-index: 1; /* Fix Leaflet z-index issues */
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
            color: var(--cafe-secondary);
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 0.4rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-item h6 i {
            color: var(--cafe-primary);
        }

        .info-item p {
            color: #777;
            font-size: 0.85rem;
            margin-bottom: 0;
            line-height: 1.5;
        }
    </style>
@endpush

@section('content')
    <div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h1 style="font-family: 'Playfair Display', serif; color: var(--cafe-secondary); font-weight: 700; font-size: 2.25rem;">Buat Titik QR Code</h1>
            <p class="text-muted mb-0">Tentukan lokasi validasi absensi (Geofencing)</p>
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
                    <form action="{{ route('admin.qr.store') }}" method="POST" id="qrForm">
                        @csrf

                        <div class="form-section-title">
                            <i class="bi bi-qr-code-scan"></i> Detail QR Code
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Area/Lokasi <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0"><i class="bi bi-pin-map"></i></span>
                                    <input type="text" class="form-control border-start-0 ps-0 @error('name') is-invalid @enderror" id="name" name="name" placeholder="Contoh: Kasir Depan, Area Dapur" value="{{ old('name') }}" required>
                                </div>
                                @error('name')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="shift_id" class="form-label">Shift Beroperasi <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm" style="border-radius: 10px; overflow: hidden;">
                                    <span class="input-group-text border-end-0"><i class="bi bi-clock-history"></i></span>
                                    <select class="form-select border-start-0 ps-0 @error('shift_id') is-invalid @enderror" id="shift_id" name="shift_id" required>
                                        <option value="">Pilih Shift Aktif</option>
                                        @foreach ($shifts ?? [] as $shift)
                                            <option value="{{ $shift->id }}" @if (old('shift_id') == $shift->id) selected @endif>
                                                {{ $shift->name }} ({{ \Carbon\Carbon::parse($shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($shift->end_time)->format('H:i') }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('shift_id')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="mb-4" style="opacity: 0.1;">

                        <div class="form-section-title d-flex justify-content-between align-items-center">
                            <div><i class="bi bi-globe-americas"></i> Pengaturan Geofencing</div>
                            <button type="button" class="btn btn-sm btn-cafe-outline px-3" onclick="detectCurrentLocation()" id="btnLocation">
                                <i class="bi bi-crosshair me-1"></i> Deteksi Lokasi Saya
                            </button>
                        </div>

                        <div class="form-group mb-4">
                            <div class="map-wrapper mb-2">
                                <div id="map" style="height: 380px;"></div>
                            </div>
                            <small class="text-muted"><i class="bi bi-info-circle me-1 text-cafe"></i> Geser dan klik pada peta untuk menjatuhkan pin lokasi. Karyawan hanya bisa absen jika berada dalam lingkaran radius.</small>
                        </div>

                        <div class="row bg-light p-3 rounded-4 border mb-4 mx-0">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.000001" class="form-control form-control-sm @error('latitude') is-invalid @enderror" id="latitude" name="latitude" value="{{ old('latitude') }}" required readonly style="background-color: #e9ecef;">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                                <input type="number" step="0.000001" class="form-control form-control-sm @error('longitude') is-invalid @enderror" id="longitude" name="longitude" value="{{ old('longitude') }}" required readonly style="background-color: #e9ecef;">
                            </div>
                            <div class="col-md-4">
                                <label for="radius_meters" class="form-label text-cafe">Radius Validasi (Meter)</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" class="form-control @error('radius_meters') is-invalid @enderror" id="radius_meters" name="radius_meters" value="{{ old('radius_meters', 100) }}" min="10" max="1000">
                                    <span class="input-group-text">m</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3 pt-2">
                            <button type="submit" class="btn btn-cafe shadow-sm px-4 py-2 d-flex align-items-center gap-2">
                                <i class="bi bi-save"></i> Generate QR Code
                            </button>
                            <a href="{{ route('admin.qr.index') }}" class="btn btn-light shadow-sm px-4 py-2 border d-flex align-items-center gap-2 text-muted">
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
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h5 class="fw-bold mb-0" style="color: var(--cafe-secondary); font-family: 'Playfair Display', serif;">Aturan Geofencing</h5>
                </div>
                
                <div class="info-item">
                    <h6><i class="bi bi-geo"></i> Titik Pusat Lokasi</h6>
                    <p>Lokasi yang Anda klik di peta akan menjadi titik tengah. Karyawan memindai QR Code harus berada dekat dari titik ini.</p>
                </div>

                <div class="info-item">
                    <h6><i class="bi bi-bullseye"></i> Lingkaran Radius</h6>
                    <p>Radius menentukan seberapa jauh karyawan boleh bergeser dari titik pusat saat absen. Direkomendasikan <strong>50 - 100 meter</strong> untuk mengatasi GPS ponsel yang kurang akurat.</p>
                </div>

                <div class="info-item">
                    <h6><i class="bi bi-shield-check"></i> Kode Unik</h6>
                    <p>Sistem akan otomatis meng-enkripsi token unik ke dalam gambar QR Code sehingga tidak dapat dipalsukan.</p>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.min.js"></script>
        <script>
            // Initialize map (Default terpusat di Area Banjarmasin)
            const map = L.map('map').setView([-3.3194, 114.5908], 14);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);

            let marker = null;
            let radiusCircle = null; // Menyimpan layer lingkaran geofence
            let accuracyCircle = null; // Menyimpan layer akurasi GPS HP

            const latInput = document.getElementById('latitude');
            const lngInput = document.getElementById('longitude');
            const radiusInput = document.getElementById('radius_meters');

            // Fungsi untuk menggambar lingkaran radius secara live
            function drawRadius() {
                if (!marker) return;
                
                let radiusValue = parseInt(radiusInput.value);
                if (isNaN(radiusValue) || radiusValue <= 0) radiusValue = 100; // Default fallback

                // Hapus radius lama jika ada
                if (radiusCircle) {
                    map.removeLayer(radiusCircle);
                }

                // Gambar radius baru warna Emas (Cafe Theme)
                radiusCircle = L.circle(marker.getLatLng(), {
                    color: '#B8860B',
                    fillColor: '#B8860B',
                    fillOpacity: 0.15,
                    weight: 2,
                    radius: radiusValue
                }).addTo(map);
            }

            // Set initial marker jika form validation error & mereturn old values
            if (latInput.value && lngInput.value) {
                const initLatLng = [parseFloat(latInput.value), parseFloat(lngInput.value)];
                marker = L.marker(initLatLng).addTo(map);
                map.setView(initLatLng, 16);
                drawRadius();
            }

            // Click event pada Peta
            map.on('click', function (e) {
                if (marker) {
                    map.removeLayer(marker);
                }
                
                marker = L.marker(e.latlng).addTo(map);
                latInput.value = e.latlng.lat.toFixed(6);
                lngInput.value = e.latlng.lng.toFixed(6);
                
                drawRadius(); // Otomatis gambar radius saat diklik
            });

            // Update radius saat admin mengganti angka di input form
            radiusInput.addEventListener('input', drawRadius);

            // Fungsi Deteksi Lokasi Admin (Device GPS)
            function detectCurrentLocation() {
                if (!navigator.geolocation) {
                    alert('Browser Anda tidak mendukung fitur deteksi lokasi (Geolocation).');
                    return;
                }

                const btn = document.getElementById('btnLocation');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split spinner-border spinner-border-sm me-1" role="status"></i> Mendeteksi...';
                btn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        const accuracy = position.coords.accuracy;

                        // Hapus marker/lingkaran lama
                        if (marker) map.removeLayer(marker);
                        if (accuracyCircle) map.removeLayer(accuracyCircle);

                        // Pasang marker baru
                        marker = L.marker([lat, lng]).addTo(map);
                        
                        // Lingkaran biru tipis menandakan akurasi GPS HP
                        accuracyCircle = L.circle([lat, lng], {
                            color: '#4A90E2',
                            fillColor: '#4A90E2',
                            fillOpacity: 0.1,
                            weight: 1,
                            radius: accuracy
                        }).addTo(map);

                        // Update input form
                        latInput.value = lat.toFixed(6);
                        lngInput.value = lng.toFixed(6);

                        // Gambar Geofence Radius
                        drawRadius();

                        // Zoom peta ke lokasi admin
                        map.flyTo([lat, lng], 17, {
                            animate: true,
                            duration: 1.5
                        });

                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    },
                    function (error) {
                        let errorMsg = 'Gagal mendeteksi lokasi.';
                        if (error.code === error.PERMISSION_DENIED) {
                            errorMsg = 'Izin akses lokasi ditolak oleh browser.';
                        } else if (error.code === error.POSITION_UNAVAILABLE) {
                            errorMsg = 'Sinyal GPS tidak tersedia.';
                        } else if (error.code === error.TIMEOUT) {
                            errorMsg = 'Waktu pencarian lokasi habis (Timeout).';
                        }
                        
                        alert(errorMsg);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            }
        </script>
    @endpush
@endsection