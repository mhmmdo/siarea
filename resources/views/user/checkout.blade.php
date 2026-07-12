@extends('layouts.user')

@section('title', 'Scan Pulang - SIAREA')

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
            display: none; 
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
            /* Warna Merah/Oranye untuk Checkout */
            border: 5px solid #FF8C00; 
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
            background: linear-gradient(to right, transparent, #FF8C00, transparent);
            box-shadow: 0 0 15px #FF8C00;
            animation: scanLine 2.5s ease-in-out infinite;
        }

        @keyframes scanLine {
            0% { top: 5%; }
            50% { top: 95%; }
            100% { top: 5%; }
        }

        /* State Styles */
        .btn-huge {
            padding: 1.5rem;
            border-radius: 24px;
            font-weight: 700;
            font-size: 1.1rem;
            background: linear-gradient(135deg, #FF8C00, #B22222);
            border: none;
            color: white;
        }

        .info-card-mini {
            background: rgba(33, 25, 21, 0.03);
            border-radius: 16px;
            padding: 1rem;
            border: 1px solid var(--cafe-border);
            margin-bottom: 1.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="app-header py-5 px-4 text-center" style="background: linear-gradient(135deg, #3a2a22 0%, #211915 100%);">
        <h2 style="font-family: 'Playfair Display', serif; font-weight: 700; margin-bottom: 8px; color: white;">Check-out</h2>
        <p class="mb-0 opacity-75 small text-white">Scan QR Code untuk mengakhiri shift kerja Anda</p>
    </div>

    <div class="scan-wrapper">
        <div class="info-card-mini d-flex align-items-center gap-3 shadow-sm">
            <div class="bg-white p-2 rounded-3 text-primary shadow-sm">
                <i class="bi bi-clock-history fs-4"></i>
            </div>
            <div>
                <small class="text-muted d-block">Waktu Check-in Anda:</small>
                <strong class="text-dark">{{ $attendance->check_in_time->format('H:i') }} WIB</strong>
            </div>
        </div>

        @if ($errors->has('error'))
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4 text-center small">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first('error') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm rounded-5 overflow-hidden mb-4">
            <div class="card-body p-4">
                
                <div id="initial-view" class="text-center py-4">
                    <div class="icon-box mx-auto mb-4" style="width: 80px; height: 80px; font-size: 2rem; background: rgba(255, 140, 0, 0.1); color: #FF8C00; display: flex; align-items: center; justify-content: center; border-radius: 50%;">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2">Akhiri Pekerjaan</h5>
                    <p class="text-muted small mb-4 px-3">Ketuk tombol di bawah untuk verifikasi lokasi dan pindai QR Code pulang.</p>
                    
                    <button type="button" class="btn btn-huge w-100 shadow" onclick="startCheckoutProcess()">
                        <i class="bi bi-qr-code-scan me-2"></i> Buka Kamera Pulang
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
                            <span class="small fw-bold">Memindai Lokasi...</span>
                        </div>
                        <p class="text-muted small mt-3">GPS aktif di latar belakang <i class="bi bi-shield-check text-success"></i></p>
                    </div>
                </div>

                <input type="hidden" id="lat-hidden">
                <input type="hidden" id="lng-hidden">

            </div>
        </div>

        <div class="text-center">
            <a href="{{ route('user.dashboard') }}" class="btn btn-link text-muted text-decoration-none small">
                <i class="bi bi-x-circle me-1"></i> Batalkan Check-out
            </a>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/jsqr@1.4.0/dist/jsQR.js"></script>
    <script>
        let videoStream = null;
        const video = document.getElementById('qr-reader-video');
        const attendanceId = {{ $attendance->id }};

        function startCheckoutProcess() {
            const btn = event.target.closest('button');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Mengunci GPS...';
            btn.disabled = true;

            if (!navigator.geolocation) {
                alert('GPS tidak didukung browser ini.');
                return;
            }

            navigator.geolocation.getCurrentPosition(
                (position) => {
                    document.getElementById('lat-hidden').value = position.coords.latitude;
                    document.getElementById('lng-hidden').value = position.coords.longitude;

                    document.getElementById('initial-view').style.display = 'none';
                    document.getElementById('scanning-view').style.display = 'block';
                    document.getElementById('qr-reader-container').style.display = 'block';

                    startCamera();
                },
                (error) => {
                    alert('Gagal mengambil lokasi. Harap izinkan akses lokasi (GPS) di HP Anda.');
                    btn.innerHTML = '<i class="bi bi-qr-code-scan me-2"></i> Buka Kamera Pulang';
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
                .catch((err) => alert("Kamera gagal diakses. Pastikan Anda memberi izin kamera."));
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
                    performCheckout(code.data);
                    return;
                }
            }
            requestAnimationFrame(tick);
        }

        function performCheckout(qrCode) {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
            }

            const container = document.getElementById('qr-reader-container');
            container.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center h-100 bg-info text-white text-center p-3">
                    <i class="bi bi-send-check-fill" style="font-size: 5rem;"></i>
                    <h4 class="fw-bold mt-3">Verifikasi Selesai!</h4>
                    <p class="small opacity-75">Mencatat jam pulang Anda...</p>
                </div>
            `;

            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            fetch(`/dashboard/attendance/${attendanceId}/checkout`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    qr_code: qrCode,
                    latitude: document.getElementById('lat-hidden').value,
                    longitude: document.getElementById('lng-hidden').value
                })
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                if (res.status === 200) {
                    window.location.href = '{{ route('user.dashboard') }}';
                } else {
                    alert('Error: ' + (res.body.message || 'Gagal check-out'));
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan jaringan.');
                location.reload();
            });
        }
    </script>
@endpush