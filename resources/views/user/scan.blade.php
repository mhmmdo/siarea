@extends('layouts.user')

@section('title', 'Scan Absensi - SIAREA')

@push('styles')
    <style>
        .scan-wrapper {
            max-width: 500px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* Kotak Kamera Fokus Utama */
        #qr-reader-container {
            position: relative;
            width: 100%;
            aspect-ratio: 1/1;
            border-radius: 30px;
            overflow: hidden;
            background: #000;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            border: 5px solid white;
            display: none; /* Muncul setelah GPS aktif */
        }

        #qr-reader-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Overlay Frame Scanner */
        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 75%;
            height: 75%;
            border: 2px solid rgba(255,255,255,0.2);
            border-radius: 25px;
            z-index: 5;
        }

        .scanner-corner {
            position: absolute;
            width: 35px;
            height: 35px;
            border: 5px solid var(--cafe-primary);
        }
        .top-left { top: -2px; left: -2px; border-right: 0; border-bottom: 0; border-top-left-radius: 25px; }
        .top-right { top: -2px; right: -2px; border-left: 0; border-bottom: 0; border-top-right-radius: 25px; }
        .bottom-left { bottom: -2px; left: -2px; border-right: 0; border-top: 0; border-bottom-left-radius: 25px; }
        .bottom-right { bottom: -2px; right: -2px; border-left: 0; border-top: 0; border-bottom-right-radius: 25px; }

        .scanner-line {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(to right, transparent, var(--cafe-primary), transparent);
            box-shadow: 0 0 15px var(--cafe-primary);
            animation: scanLine 2.5s ease-in-out infinite;
        }

        @keyframes scanLine {
            0% { top: 5%; }
            50% { top: 95%; }
            100% { top: 5%; }
        }

        .qr-info-item {
            background: white;
            border: 1px solid var(--cafe-border);
            border-radius: 16px;
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        }

        /* State Styles */
        .btn-huge {
            padding: 1.5rem;
            border-radius: 24px;
            font-weight: 700;
            font-size: 1.1rem;
        }
    </style>
@endpush

@section('content')
    <div class="app-header py-5 px-4 text-center">
        <h2 style="font-family: 'Playfair Display', serif; font-weight: 700; margin-bottom: 8px;">Presensi Digital</h2>
        <p class="mb-0 opacity-75 small">Arahkan kamera ke QR Code yang tersedia di lokasi</p>
    </div>

    <div class="scan-wrapper">
        @if ($errors->has('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 text-center small">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first('error') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-5 overflow-hidden mb-4">
            <div class="card-body p-4">
                
                <div id="initial-view" class="text-center py-4">
                    <div class="icon-box gold mx-auto mb-4" style="width: 80px; height: 80px; font-size: 2rem; background: rgba(184, 134, 11, 0.1); color: var(--cafe-primary); display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="bi bi-camera-fill"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Mulai Presensi</h5>
                    <p class="text-muted small mb-4 px-3">Ketuk tombol di bawah untuk mengaktifkan sistem GPS dan Kamera absen.</p>
                    
                    <button type="button" class="btn btn-cafe btn-huge w-100 shadow" onclick="startAttendanceProcess()">
                        <i class="bi bi-qr-code-scan me-2"></i> Buka Scanner
                    </button>
                </div>

                <div id="scanning-view" style="display: none;">
                    <div id="qr-reader-container">
                        <video id="qr-reader-video" playsinline></video>
                        <div class="scanner-overlay">
                            <div class="scanner-corner top-left"></div>
                            <div class="scanner-corner top-right"></div>
                            <div class="scanner-corner bottom-left"></div>
                            <div class="scanner-corner bottom-right"></div>
                            <div class="scanner-line"></div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4">
                        <div class="d-inline-flex align-items-center gap-2 badge rounded-pill bg-dark bg-opacity-75 px-4 py-2">
                            <span class="spinner-grow spinner-grow-sm text-warning" role="status"></span>
                            <span class="small fw-bold">Mencari QR Code...</span>
                        </div>
                        <p class="text-muted small mt-3">GPS aktif di latar belakang <i class="bi bi-shield-check text-success"></i></p>
                    </div>
                </div>

                <input type="hidden" id="lat-hidden" name="latitude">
                <input type="hidden" id="lng-hidden" name="longitude">

            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3 px-2">
            <h6 class="fw-bold mb-0" style="color: var(--cafe-secondary);">Lokasi Terdaftar</h6>
            <span class="badge bg-white text-muted border fw-normal">{{ $qrCodes->count() }} Area</span>
        </div>

        @forelse ($qrCodes as $qr)
            <div class="qr-info-item shadow-sm">
                <div>
                    <strong class="text-dark d-block">{{ $qr->name }}</strong>
                    <small class="text-muted"><i class="bi bi-clock me-1"></i> {{ $qr->shift->name ?? '-' }}</small>
                </div>
                <span class="badge bg-light text-cafe border px-2 py-1 small fw-normal">
                    {{ \Carbon\Carbon::parse($qr->shift->start_time ?? '00:00')->format('H:i') }} - {{ \Carbon\Carbon::parse($qr->shift->end_time ?? '00:00')->format('H:i') }}
                </span>
            </div>
        @empty
            <div class="text-center py-4 bg-white rounded-4 border border-dashed">
                <p class="text-muted small mb-0">Tidak ada titik absen aktif.</p>
            </div>
        @endforelse
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        let videoStream = null;
        const video = document.getElementById('qr-reader-video');

        function startAttendanceProcess() {
            const btn = event.target.closest('button');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses...';
            btn.disabled = true;

            if (!navigator.geolocation) {
                alert('GPS tidak didukung browser ini.');
                return;
            }

            // Ambil GPS secara diam-diam
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    document.getElementById('lat-hidden').value = position.coords.latitude;
                    document.getElementById('lng-hidden').value = position.coords.longitude;

                    // Switch View (Hilangkan tombol, munculkan kamera)
                    document.getElementById('initial-view').style.display = 'none';
                    document.getElementById('scanning-view').style.display = 'block';
                    document.getElementById('qr-reader-container').style.display = 'block';

                    startCamera();
                },
                (error) => {
                    alert('Harap aktifkan GPS Anda untuk melakukan absen.');
                    btn.innerHTML = '<i class="bi bi-qr-code-scan me-2"></i> Buka Scanner';
                    btn.disabled = false;
                },
                { enableHighAccuracy: true }
            );
        }

        function startCamera() {
            navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
                .then((stream) => {
                    videoStream = stream;
                    video.srcObject = stream;
                    video.setAttribute("playsinline", true);
                    video.play();
                    requestAnimationFrame(tick);
                })
                .catch((err) => {
                    alert("Kamera gagal diakses: " + err.message);
                });
        }

        function tick() {
            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, canvas.width, canvas.height);

                if (code) {
                    submitAttendance(code.data);
                    return;
                }
            }
            requestAnimationFrame(tick);
        }

        function submitAttendance(qrCode) {
            // Matikan kamera
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
            }

            // Tampilan Sukses Singkat
            const container = document.getElementById('qr-reader-container');
            container.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-success text-white">
                    <i class="bi bi-check-circle-fill" style="font-size: 5rem;"></i>
                    <h4 class="fw-bold mt-3">QR Berhasil Scan!</h4>
                    <p class="small opacity-75">Sedang mengirim data...</p>
                </div>
            `;

            // Kirim Form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ route('user.attendance.scan') }}";
            
            const data = {
                '_token': "{{ csrf_token() }}",
                'qr_code': qrCode,
                'latitude': document.getElementById('lat-hidden').value,
                'longitude': document.getElementById('lng-hidden').value
            };

            for (const key in data) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }
    </script>
@endpush