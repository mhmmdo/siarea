<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Masuk - SIAREA Premium</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet" />

    <style>
        :root {
            --cafe-primary: #B8860B; /* Elegant Gold */
            --cafe-secondary: #211915; /* Deep Espresso */
            --cafe-accent: #D4AF37; 
            --cafe-light: #FBF9F6; 
            --cafe-dark: #2C241B; 
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        /* Latar Belakang Foto Cafe Gelap */
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--cafe-secondary);
            background-image: linear-gradient(rgba(33, 25, 21, 0.85), rgba(33, 25, 21, 0.95)), url('https://images.unsplash.com/photo-1497935586351-b67a49e012bf?q=80&w=2000&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
            padding: 1rem;
            z-index: 10;
        }

        /* Glassmorphism Card */
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 2.5rem 2rem;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        /* Styling Kotak Logo Custom */
        .login-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 72px; /* Diperbesar sedikit untuk gambar */
            height: 72px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.9); /* Background putih agar logo PNG terlihat jelas */
            margin-bottom: 1rem;
            padding: 10px; /* Jarak aman agar logo tidak menabrak batas kotak */
            border: 1px solid rgba(184, 134, 11, 0.15);
        }

        .login-logo img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain; /* Memastikan logo tidak melar/peyang */
        }

        .login-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 800;
            color: var(--cafe-secondary);
            margin: 0;
            letter-spacing: 0.5px;
        }

        .login-subtitle {
            font-size: 0.9rem;
            color: #777;
            margin-top: 0.25rem;
            font-weight: 500;
        }

        /* Input Styling */
        .form-label {
            font-weight: 600;
            color: #555;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }

        .input-group-text {
            background-color: transparent;
            border-right: none;
            border-color: #ddd;
            color: #999;
            padding-left: 1.25rem;
        }

        .form-control {
            border-left: none;
            border-color: #ddd;
            padding: 0.8rem 1rem 0.8rem 0;
            font-size: 0.95rem;
            box-shadow: none !important;
            transition: border-color 0.3s ease;
        }

        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control {
            border-color: var(--cafe-primary);
        }

        .input-group:focus-within .input-group-text {
            color: var(--cafe-primary);
        }

        /* Custom Checkbox */
        .form-check-input {
            cursor: pointer;
            border-color: #ccc;
            width: 1.1em;
            height: 1.1em;
        }

        .form-check-input:checked {
            background-color: var(--cafe-primary);
            border-color: var(--cafe-primary);
        }

        .form-check-label {
            cursor: pointer;
            color: #666;
            font-size: 0.875rem;
            padding-top: 0.15rem;
        }

        /* Tombol Login */
        .btn-login {
            background: linear-gradient(135deg, var(--cafe-primary), #9E7308);
            border: none;
            color: white;
            font-weight: 600;
            font-size: 1rem;
            padding: 0.875rem 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            width: 100%;
            box-shadow: 0 4px 15px rgba(184, 134, 11, 0.3);
            margin-top: 1rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(184, 134, 11, 0.4);
            color: white;
        }

        /* Alert Styling */
        .alert-danger {
            background-color: rgba(220, 53, 69, 0.05);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: #dc3545;
            border-radius: 12px;
            font-size: 0.85rem;
        }

        /* Demo Box Estetik */
        .demo-credentials {
            background-color: #f8f9fa;
            border: 1px dashed #ccc;
            padding: 1rem;
            border-radius: 12px;
            font-size: 0.8rem;
            margin-top: 2rem;
            text-align: center;
            color: #666;
        }

        /* Toggle Password Button */
        .toggle-password {
            cursor: pointer;
            background: transparent;
            border: 1px solid #ddd;
            border-left: none;
            color: #999;
            padding-right: 1.25rem;
            display: flex;
            align-items: center;
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
        }
        .input-group:focus-within .toggle-password {
            border-color: var(--cafe-primary);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            
            <div class="login-header">
                <div class="login-logo shadow-sm">
                    <img src="{{ asset('img/area.png') }}" alt="Logo SIAREA">
                </div>
                
                <h1 class="login-title">SIAREA</h1>
                <p class="login-subtitle">Manajemen Absensi</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger px-3 py-2 d-flex gap-2 align-items-start">
                    <i class="bi bi-exclamation-triangle-fill mt-1"></i>
                    <div>
                        <strong class="d-block mb-1">Akses Ditolak!</strong>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf

                <div class="form-group mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" value="{{ old('email') }}" id="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Masukkan email terdaftar" required autofocus autocomplete="username">
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                        <input type="password" id="password" name="password" class="form-control border-end-0 @error('password') is-invalid @enderror" placeholder="••••••••" required autocomplete="current-password">
                        <div class="toggle-password" onclick="toggleVisibility()">
                            <i class="bi bi-eye" id="eyeIcon"></i>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check m-0">
                        <input type="checkbox" id="remember" name="remember" class="form-check-input" value="1">
                        <label for="remember" class="form-check-label select-none">Ingat sesi saya</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-login d-flex justify-content-center align-items-center gap-2">
                    Masuk ke Dasbor <i class="bi bi-arrow-right-short fs-5"></i>
                </button>
            </form>

        </div>
        
        <div class="text-center mt-4">
            <p style="color: rgba(255,255,255,0.5); font-size: 0.8rem; letter-spacing: 0.5px;">
                &copy; {{ date('Y') }} SIAREA. All rights reserved.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('bi-eye');
                eyeIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('bi-eye-slash');
                eyeIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>

</html>