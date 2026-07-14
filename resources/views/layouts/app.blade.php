<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIAREA - Sistem Absen Kafe')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet" />
    <link rel="icon" href="{{ asset('img/area.png') }}" type="image/png">
    <style>
        :root {
            /* Palet Warna Premium */
            --cafe-primary: #d1ab48; /* Elegant Gold / Caramel */
            --cafe-secondary: #211915; /* Deep Espresso (Sangat Gelap Mewah) */
            --cafe-accent: #D4AF37; /* Metallic Gold */
            --cafe-light: #FBF9F6; /* Ivory / Pearl White */
            --cafe-dark: #2C241B; /* Dark Text */
            --cafe-border: #E8E1D7; /* Soft Border */
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--cafe-light);
            color: var(--cafe-dark);
            letter-spacing: -0.01em;
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: var(--cafe-primary) !important;
            letter-spacing: 1px;
        }

        .nav-link {
            color: var(--cafe-dark) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--cafe-primary) !important;
        }

        .nav-link.active {
            color: var(--cafe-primary) !important;
            border-bottom: 2px solid var(--cafe-primary);
        }

        /* --- SIDEBAR PREMIUM --- */
        .sidebar {
            background: var(--cafe-secondary);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            width: 280px; 
            z-index: 1000;
            padding-top: 0;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08); 
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            background: transparent;
        }

        .sidebar-brand {
            color: var(--cafe-light);
            font-size: 1.5rem;
            font-weight: 700;
            font-family: 'Playfair Display', serif; 
            display: flex;
            align-items: center;
            gap: 0.75rem;
            letter-spacing: 1px;
        }

        /* Styling Khusus Logo Gambar di Sidebar (Updated: Circle) */
        .sidebar-brand img {
            height: 40px; /* Tinggi fix */
            width: 40px;  /* Lebar fix sama dengan tinggi agar jadi persegi sempurna dulu */
            object-fit: cover; /* Memastikan gambar mengisi lingkaran tanpa gepeng */
            border-radius: 50%; /* MAGIC: Ini yang bikin jadi bulat */
            border: 2px solid var(--cafe-primary); /* Tambah border emas biar keren */
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2)); 
            padding: 2px; /* Sedikit jarak antara gambar dan border */
            background: white; /* Background putih di belakang logo transparan */
        }

        .sidebar-menu {
            padding: 1.5rem 0;
            list-style: none;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 0.25rem;
        }

        /* Bentuk Menu jadi 'Pill' modern */
        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #A49E99; 
            text-decoration: none;
            padding: 0.875rem 1.5rem;
            margin: 0 1rem;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-menu a:hover {
            background-color: rgba(184, 134, 11, 0.1); 
            color: var(--cafe-primary);
            transform: translateX(4px);
        }

        .sidebar-menu a.active {
            background: linear-gradient(135deg, var(--cafe-primary), #9E7308);
            color: white;
            box-shadow: 0 4px 15px rgba(184, 134, 11, 0.35);
        }

        .sidebar-menu i {
            font-size: 1.25rem;
            width: 1.5rem;
            text-align: center;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: transparent;
        }

        /* Tombol Logout Transparan elegan */
        .sidebar-footer .btn-outline-light {
            border-color: rgba(255,255,255,0.2);
            color: #A49E99;
            border-radius: 10px;
            padding: 0.6rem;
            transition: all 0.3s ease;
        }
        .sidebar-footer .btn-outline-light:hover {
            background: rgba(255, 59, 48, 0.1);
            color: #FF3B30;
            border-color: #FF3B30;
        }

        /* --- MAIN CONTENT & TOPBAR --- */
        .main-content {
            margin-left: 280px;
            background-color: var(--cafe-light);
            min-height: 100vh;
        }

        /* Efek Glassmorphism (Kaca) di Topbar */
        .topbar {
            background: rgba(251, 249, 246, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            padding: 1.25rem 2rem;
            border-bottom: 1px solid rgba(0,0,0,0.03);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            position: sticky;
            top: 0;
            z-index: 999;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-weight: 600;
        }

        .topbar-user img {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: white;
            padding: 2px;
            border: 2px solid var(--cafe-primary);
            box-shadow: 0 4px 10px rgba(184, 134, 11, 0.2);
        }

        /* --- CARDS & WIDGETS --- */
        .card {
            border: 1px solid var(--cafe-border);
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            border-radius: 16px;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06);
        }

        .stat-card {
            background: white;
            padding: 1.75rem;
            border-radius: 16px;
            border: 1px solid var(--cafe-border);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: var(--cafe-primary);
            border-radius: 16px 0 0 16px;
        }

        .stat-number {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--cafe-secondary);
            font-family: 'Playfair Display', serif;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #888;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        /* --- BUTTONS --- */
        .btn-cafe {
            background: linear-gradient(135deg, var(--cafe-primary), #9E7308);
            border: none;
            color: white;
            font-weight: 600;
            padding: 0.6rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(184, 134, 11, 0.25);
            transition: all 0.3s ease;
        }

        .btn-cafe:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(184, 134, 11, 0.4);
            color: white;
        }

        .btn-cafe-outline {
            background: transparent;
            border: 2px solid var(--cafe-primary);
            color: var(--cafe-primary);
            font-weight: 600;
            border-radius: 10px;
            padding: 0.5rem 1.4rem;
        }

        .btn-cafe-outline:hover {
            background: rgba(184, 134, 11, 0.1);
            color: var(--cafe-primary);
        }

        .badge-cafe {
            background-color: rgba(184, 134, 11, 0.1);
            color: var(--cafe-primary);
            font-weight: 600;
            padding: 0.4em 0.8em;
            border-radius: 6px;
        }

        /* --- ALERTS --- */
        .alert-cafe {
            background-color: white;
            border: 1px solid var(--cafe-border);
            border-left: 4px solid var(--cafe-primary);
            color: var(--cafe-dark);
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }

        /* --- TABLES --- */
        .table {
            background: white;
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 12px;
            overflow: hidden;
        }

        .table thead {
            background-color: rgba(248, 246, 243, 0.5);
        }

        .table thead th {
            color: #888;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--cafe-border);
            padding: 1.2rem 1rem;
        }

        .table tbody td {
            padding: 1rem;
            border-bottom: 1px solid var(--cafe-border);
            vertical-align: middle;
        }

        .table tbody tr {
            transition: background-color 0.2s ease;
        }

        .table tbody tr:hover {
            background-color: rgba(184, 134, 11, 0.03);
        }

        /* --- FORMS --- */
        .form-control, .form-select {
            border: 1px solid var(--cafe-border);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            background-color: white;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.01);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--cafe-primary);
            box-shadow: 0 0 0 4px rgba(184, 134, 11, 0.1);
            background-color: white;
        }

        .page-header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--cafe-secondary);
            font-family: 'Playfair Display', serif;
            margin-bottom: 0.5rem;
        }

        .text-cafe {
            color: var(--cafe-primary) !important;
        }

        .text-cafe-secondary {
            color: var(--cafe-secondary) !important;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: none;
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 10px 0 30px rgba(0,0,0,0.5);
            }

            .main-content {
                margin-left: 0;
            }
            
            .topbar {
                padding: 1rem;
            }
        }
    </style>

    @stack('styles')
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-brand">
                <img src="{{ asset('img/area.png') }}" alt="Logo SIAREA">
                <span>SIAREA</span>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="@if (Route::current()->getName() === 'admin.dashboard') active @endif">
                    <i class="bi bi-grid-1x2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.employees.index') }}" class="@if (str_contains(Route::current()->getName(), 'employees')) active @endif">
                    <i class="bi bi-people"></i>
                    <span>Karyawan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.qr.index') }}" class="@if (str_contains(Route::current()->getName(), 'qr')) active @endif">
                    <i class="bi bi-qr-code-scan"></i>
                    <span>QR Code</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.salary.index') }}" class="@if (str_contains(Route::current()->getName(), 'salary')) active @endif">
                    <i class="bi bi-wallet2"></i>
                    <span>Gaji</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.attendance.report') }}" class="@if (str_contains(Route::current()->getName(), 'attendance.report')) active @endif">
                    <i class="bi bi-journal-text"></i>
                    <span>Laporan</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.profile') }}" class="@if (Route::current()->getName() === 'admin.profile') active @endif">
                    <i class="bi bi-person-gear"></i>
                    <span>Pengaturan Akun</span>
                </a>
            </li>
        </ul>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                @csrf
                <button type="submit" class="btn btn-outline-light w-100 d-flex justify-content-center align-items-center gap-2">
                    <i class="bi bi-box-arrow-right"></i> <span>Logout</span>
                </button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div>
                <button class="btn btn-light rounded-circle shadow-sm" id="sidebarToggle" onclick="toggleSidebar()" style="width: 40px; height: 40px; padding: 0;">
                    <i class="bi bi-list fs-5"></i>
                </button>
            </div>
            <div class="topbar-user">
                <a href="{{ route('admin.profile') }}" class="text-decoration-none d-flex align-items-center gap-2">
                    <span class="d-none d-md-block" style="color: var(--cafe-secondary);">{{ Auth::user()->name }}</span>
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ Auth::user()->id }}&backgroundColor=FBF9F6" alt="User Avatar" />
                </a>
            </div>
        </div>

        <div class="container-fluid px-4 pb-4">
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <strong>Terjadi Kesalahan!</strong>
                    </div>
                    <ul class="mb-0 mt-2 text-muted">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-4" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-x-circle-fill text-danger fs-5"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');

            if (window.innerWidth <= 768) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>

    @stack('scripts')
</body>

</html>