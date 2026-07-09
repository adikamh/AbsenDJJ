<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - AbsenDJJ</title>
    
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @vite('resources/css/dashboard-layout.css')
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('images/Logo/Logo_PU.png') }}" alt="Logo PU" class="sidebar-logo">
            <span class="logo-text">AbsenDJJ</span>
        </div>

        <button type="button" class="sidebar-toggle" id="sidebar-toggle" aria-label="Buka/tutup sidebar">
            <span class="sidebar-toggle-icon">›</span>
        </button>

        <ul class="nav-menu">
            <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    <span class="nav-label">Dashboard</span>
                </a>
            </li>
            
            @if(auth()->user()->isSuperAdmin())
                <li class="nav-item {{ request()->routeIs('super-admin.pembimbing') ? 'active' : '' }}">
                    <a href="{{ route('super-admin.pembimbing') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        <span class="nav-label">Kelola Pembimbing</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('super-admin.peserta') ? 'active' : '' }}">
                    <a href="{{ route('super-admin.peserta') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                        <span class="nav-label">Kelola Peserta</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('super-admin.instansi') ? 'active' : '' }}">
                    <a href="{{ route('super-admin.instansi') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-label">Kelola Instansi</span>
                    </a>
                </li>

                <li class="nav-item {{ request()->routeIs('super-admin.settings') ? 'active' : '' }}">
                    <a href="{{ route('super-admin.settings') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.533 1.533 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.533 1.533 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-label">Pengaturan</span>
                    </a>
                </li>
            @endif

            @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a href="{{ route('admin.interns') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                        <span class="nav-label">Anak Bimbingan</span>
                    </a>
                </li>
            @endif

            @if(auth()->user()->isPeserta())
                <li class="nav-item">
                    <a href="{{ route('peserta.attendance') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-label">Riwayat Absen</span>
                    </a>
                </li>
            @endif
        </ul>

        <div class="user-panel">
            <div class="user-info">
                <span class="user-name">{{ auth()->user()->nama_lengkap }}</span>
                <span class="user-role">{{ auth()->user()->role->nama_role }}</span>
            </div>
            
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="btn-logout">
                    <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="nav-label">Keluar</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Main Content Panel -->
    <div class="main-content">
        <div class="page-header">
            <h1 class="page-title">@yield('header_title')</h1>
            <div class="page-header-actions">
                <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Ganti tema">
                    <span class="theme-toggle-icon">☀️</span>
                    <span class="theme-toggle-text">Light</span>
                </button>
                <span class="badge badge-info">Online</span>
            </div>
        </div>

        @yield('content')
    </div>

    @if(!request()->hasCookie('cookie_consent') && !session('cookie_consent'))
        <div class="cookie-modal-backdrop" id="cookie-modal-backdrop">
            <div class="cookie-modal" role="dialog" aria-modal="true" aria-labelledby="cookie-title">
                <h3 id="cookie-title">Kebijakan Cookie</h3>
                <p>Kami menggunakan cookie untuk menjaga sesi Anda tetap aman dan meningkatkan pengalaman penggunaan aplikasi.</p>
                <div class="cookie-modal-actions">
                    <button type="button" class="btn-cookie btn-cookie-secondary" data-consent="declined">Tolak</button>
                    <button type="button" class="btn-cookie btn-cookie-primary" data-consent="accepted">Setuju</button>
                </div>
            </div>
        </div>
    @endif

    <script>
        window.absenNotifications = {
            success: @json(session('success')),
            error: @json(session('error')),
            validationError: @json($errors->any()),
        };
    </script>
    @vite('resources/js/dashboard-layout.js')
    @stack('scripts')
</body>
</html>
