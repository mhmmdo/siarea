<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Karyawan - SIAREA')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet" />

    <style>
        :root {
            --cafe-primary: #B8860B; /* Elegant Gold */
            --cafe-secondary: #211915; /* Espresso */
            --cafe-light: #FBF9F6;
            --cafe-border: #E8E1D7;
            --cafe-danger: #B22222; /* Red for Checkout */
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--cafe-light);
            color: #2C241B;
        }

        /* --- SIDEBAR (Desktop Only) --- */
        .sidebar { display: none; }

        /* --- BOTTOM NAV (Mobile Only) --- */
        .bottom-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 0.5rem 0 0.75rem 0;
            border-top: 1px solid var(--cafe-border);
            border-radius: 24px 24px 0 0;
            box-shadow: 0 -5px 25px rgba(0,0,0,0.06);
            z-index: 1000;
        }

        .nav-item {
            text-align: center;
            color: #A0A0A0;
            text-decoration: none;
            font-size: 0.7rem;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
            padding-top: 5px;
            width: 20%;
        }

        .nav-item.active { color: var(--cafe-primary); }
        .nav-item.active-danger { color: var(--cafe-danger); }
        
        .nav-item.active::after {
            content: '';
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 20px;
            height: 3px;
            background: var(--cafe-primary);
            border-radius: 0 0 10px 10px;
        }

        .nav-item.active-danger::after {
            background: var(--cafe-danger);
        }

        .nav-item i { 
            display: block; 
            font-size: 1.4rem; 
            margin-bottom: 2px;
            transition: transform 0.3s ease;
        }

        .nav-item.active i, .nav-item.active-danger i {
            transform: translateY(-2px);
        }

        /* Scan Button Floating */
        .nav-scan-btn {
            background: linear-gradient(135deg, var(--cafe-primary), #9E7308);
            color: white !important; 
            width: 50px; 
            height: 50px; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            margin: -25px auto 4px auto; 
            box-shadow: 0 8px 20px rgba(184, 134, 11, 0.4);
            border: 4px solid white;
        }

        /* Tombol Scan Berubah Merah saat sudah Check-in */
        .nav-scan-btn.btn-checkout {
            background: linear-gradient(135deg, #FF8C00, var(--cafe-danger));
            box-shadow: 0 8px 20px rgba(178, 34, 34, 0.4);
        }

        .content-wrapper { padding-bottom: 100px; }

        /* --- DESKTOP LAYOUT --- */
        @media (min-width: 992px) {
            .bottom-nav { display: none; }
            
            .sidebar {
                display: block;
                background: var(--cafe-secondary);
                min-height: 100vh;
                position: fixed;
                left: 0; top: 0; width: 280px;
                z-index: 1000;
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.1); 
            }

            .sidebar-brand {
                padding: 2.5rem 1.5rem;
                color: white;
                font-family: 'Playfair Display', serif;
                font-size: 1.6rem;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 0.8rem;
            }

            .sidebar-brand img { 
                height: 40px; width: 40px; 
                object-fit: cover; border-radius: 50%; 
                border: 2px solid var(--cafe-primary);
                background: white;
                padding: 2px;
            }
            
            .sidebar-menu { list-style: none; padding: 1rem 0; margin: 0; }
            
            .sidebar-menu a {
                display: flex; align-items: center; gap: 1.2rem; 
                color: rgba(255,255,255,0.5);
                text-decoration: none; padding: 1rem 1.5rem; margin: 0.4rem 1.2rem;
                border-radius: 14px; transition: all 0.3s ease; font-weight: 500;
            }

            .sidebar-menu a i { font-size: 1.3rem; }

            .sidebar-menu a:hover {
                color: white;
                background: rgba(255,255,255,0.05);
            }

            .sidebar-menu a.active {
                background: linear-gradient(135deg, var(--cafe-primary), #9E7308);
                color: white;
                font-weight: 600;
                box-shadow: 0 10px 20px rgba(184, 134, 11, 0.2);
            }

            /* Warna khusus aktif saat checkout di desktop */
            .sidebar-menu a.active-checkout {
                background: linear-gradient(135deg, #FF8C00, var(--cafe-danger));
                color: white;
                box-shadow: 0 10px 20px rgba(178, 34, 34, 0.2);
            }

            .content-wrapper {
                margin-left: 280px;
                padding: 2rem 3rem;
            }
        }
    </style>
    @stack('styles')
</head>

<body>
    @php
        // Logika Pintar: Cek apakah user sudah absen masuk tapi belum pulang hari ini
        $attendanceToday = \App\Models\AttendanceRecord::where('employee_id', auth()->user()->employee->id)
                            ->whereDate('date', \Carbon\Carbon::today())
                            ->first();
                            
        $isWorking = $attendanceToday && !$attendanceToday->attendanceCheckout;
        
        // Tentukan Rute, Label, dan Ikon
        $scanRoute = $isWorking ? route('user.checkout') : route('user.scan');
        $scanLabel = $isWorking ? 'Scan Pulang' : 'Scan Masuk';
        $scanIcon = $isWorking ? 'bi-box-arrow-right' : 'bi-qr-code-scan';
        $scanActiveClass = $isWorking ? 'active-checkout' : 'active';
    @endphp

    <nav class="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('img/area.png') }}" alt="Logo" onerror="this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=SIAREA'">
            <span>SIAREA</span>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('user.dashboard') }}" class="{{ Route::is('user.dashboard') ? 'active' : '' }}">
                    <i class="bi bi-house-door"></i> Beranda
                </a>
            </li>
            <li>
                <a href="{{ route('user.attendance') }}" class="{{ Route::is('user.attendance*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history"></i> Riwayat Absen
                </a>
            </li>
            <!-- <li>
                <a href="{{ $scanRoute }}" class="{{ Route::is('user.scan*') || Route::is('user.checkout*') ? $scanActiveClass : '' }}">
                    <i class="bi {{ $scanIcon }}"></i> {{ $scanLabel }}
                </a>
            </li> -->
            <li>
                <a href="{{ route('user.salary') }}" class="{{ Route::is('user.salary*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i> Slip Gaji
                </a>
            </li>
            <li>
                <a href="{{ route('user.profile') }}" class="{{ Route::is('user.profile*') ? 'active' : '' }}">
                    <i class="bi bi-person"></i> Profil Saya
                </a>
            </li>
        </ul>
        
        <div style="position: absolute; bottom: 2rem; left: 1.2rem; right: 1.2rem;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100 d-flex justify-content-center align-items-center gap-2" style="border-color: rgba(255,255,255,0.15); color: rgba(255,255,255,0.6); border-radius: 12px; padding: 0.75rem;">
                    <i class="bi bi-box-arrow-right"></i> Keluar
                </button>
            </form>
        </div>
    </nav>

    <main class="content-wrapper">
        @yield('content')
    </main>

    <nav class="bottom-nav">
        <a href="{{ route('user.dashboard') }}" class="nav-item {{ Route::is('user.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-door{{ Route::is('user.dashboard') ? '-fill' : '' }}"></i>
            <span>Beranda</span>
        </a>
        <a href="{{ route('user.attendance') }}" class="nav-item {{ Route::is('user.attendance*') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>Riwayat</span>
        </a>
        
        <a href="{{ $scanRoute }}" class="nav-item {{ Route::is('user.scan*') || Route::is('user.checkout*') ? ($isWorking ? 'active-danger' : 'active') : '' }}">
            <div class="nav-scan-btn {{ $isWorking ? 'btn-checkout' : '' }}">
                <i class="bi {{ $scanIcon }}"></i>
            </div>
            <span style="color: {{ $isWorking ? 'var(--cafe-danger)' : 'var(--cafe-primary)' }}; font-weight: 800; margin-top: 2px; display: block;">
                {{ $isWorking ? 'PULANG' : 'SCAN' }}
            </span>
        </a>
        
        <a href="{{ route('user.salary') }}" class="nav-item {{ Route::is('user.salary*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i>
            <span>Gaji</span>
        </a>
        <a href="{{ route('user.profile') }}" class="nav-item {{ Route::is('user.profile*') ? 'active' : '' }}">
            <i class="bi bi-person{{ Route::is('user.profile*') ? '-fill' : '' }}"></i>
            <span>Profil</span>
        </a>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>