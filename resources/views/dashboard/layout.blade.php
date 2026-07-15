<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') - Absen Magang</title>
    
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo/favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#007bff">
    <link rel="apple-touch-icon" href="/icon.png">

    @vite('resources/css/dashboard-layout.css')
    @stack('styles')
</head>
<body>

    <!-- Sidebar Backdrop for mobile -->
    <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="{{ asset('images/Logo/Logo_PU.png') }}" alt="Logo PU" class="sidebar-logo">
            <span class="logo-text">Absen Magang</span>
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
                <li class="nav-item {{ request()->routeIs('admin.interns*') ? 'active' : '' }}">
                    <a href="{{ route('admin.interns') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                        <span class="nav-label">Anak Bimbingan</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('admin.logbooks*') ? 'active' : '' }}">
                    <a href="{{ route('admin.logbooks') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm2 2h2v2H6V6zm2 3H6v2h2V9zm2-3h2v2h-2V6zm2 3h-2v2h2V9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-label">Logbook Anak Didik</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('admin.leaves*') ? 'active' : '' }}">
                    <a href="{{ route('admin.leaves') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-label">Izin & Sakit Anak Didik</span>
                    </a>
                </li>
            @endif

            @if(auth()->user()->isPeserta())
                <li class="nav-item {{ request()->routeIs('peserta.attendance') ? 'active' : '' }}">
                    <a href="{{ route('peserta.attendance') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-label">Riwayat Absen</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('peserta.logbook') ? 'active' : '' }}">
                    <a href="{{ route('peserta.logbook') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm2 2h2v2H6V6zm2 3H6v2h2V9zm2-3h2v2h-2V6zm2 3h-2v2h2V9z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-label">Logbook Kegiatan</span>
                    </a>
                </li>
                <li class="nav-item {{ request()->routeIs('peserta.leave') ? 'active' : '' }}">
                    <a href="{{ route('peserta.leave') }}">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-label">Izin / Sakit</span>
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
            <button type="button" class="mobile-toggle-btn" id="mobile-toggle-btn" aria-label="Buka menu">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
            </button>
            <h1 class="page-title">@yield('header_title')</h1>
            <div class="page-header-actions">
                @if(auth()->user()->isPeserta() || auth()->user()->isAdmin())
                    <div class="notification-dropdown-wrapper">
                        <button type="button" class="notification-bell-btn" id="notification-bell-btn" aria-label="Notifikasi">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span class="notification-badge" id="notification-badge" style="display: none;">0</span>
                        </button>
                        <div class="notification-menu" id="notification-menu">
                            <div class="notification-header">
                                <h4>Notifikasi</h4>
                                <button type="button" class="mark-all-read-btn" id="mark-all-read-btn">Tandai dibaca</button>
                            </div>
                            <div class="notification-list" id="notification-list">
                                <div style="color: var(--text-secondary); text-align: center; padding: 20px 0; font-size: 0.85rem;">Tidak ada notifikasi baru</div>
                            </div>
                        </div>
                    </div>
                @endif

                <button type="button" class="theme-toggle" id="theme-toggle" aria-label="Ganti tema">
                    <span class="theme-toggle-icon">☀️</span>
                    <span class="theme-toggle-text">Light</span>
                </button>
                <span class="badge badge-info">Online</span>

                <!-- Mobile Logout Button -->
                <form action="{{ route('logout') }}" method="POST" class="mobile-logout-form">
                    @csrf
                    <button type="submit" class="btn-logout-mobile" title="Keluar" aria-label="Keluar">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                    </button>
                </form>
            </div>
        </div>

        @yield('content')
    </div>

    <!-- Bottom Navigation Bar for mobile -->
    <div class="bottom-nav">
        <a href="{{ route('dashboard') }}" class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
            </svg>
            <span class="bottom-nav-label">Dashboard</span>
        </a>

        @if(auth()->user()->isSuperAdmin())
            <a href="{{ route('super-admin.pembimbing') }}" class="bottom-nav-item {{ request()->routeIs('super-admin.pembimbing') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                </svg>
                <span class="bottom-nav-label">Pembimbing</span>
            </a>
            <a href="{{ route('super-admin.peserta') }}" class="bottom-nav-item {{ request()->routeIs('super-admin.peserta') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                </svg>
                <span class="bottom-nav-label">Peserta</span>
            </a>
            <a href="{{ route('super-admin.instansi') }}" class="bottom-nav-item {{ request()->routeIs('super-admin.instansi') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/>
                </svg>
                <span class="bottom-nav-label">Instansi</span>
            </a>
            <a href="{{ route('super-admin.settings') }}" class="bottom-nav-item {{ request()->routeIs('super-admin.settings') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.533 1.533 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.533 1.533 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                </svg>
                <span class="bottom-nav-label">Setelan</span>
            </a>
        @endif

        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.interns') }}" class="bottom-nav-item {{ request()->routeIs('admin.interns*') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                </svg>
                <span class="bottom-nav-label">Bimbingan</span>
            </a>
            <a href="{{ route('admin.logbooks') }}" class="bottom-nav-item {{ request()->routeIs('admin.logbooks*') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm2 2h2v2H6V6zm2 3H6v2h2V9zm2-3h2v2h-2V6zm2 3h-2v2h2V9z" clip-rule="evenodd"/>
                </svg>
                <span class="bottom-nav-label">Logbook</span>
            </a>
            <a href="{{ route('admin.leaves') }}" class="bottom-nav-item {{ request()->routeIs('admin.leaves*') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                </svg>
                <span class="bottom-nav-label">Izin/Sakit</span>
            </a>
        @endif

        @if(auth()->user()->isPeserta())
            <a href="{{ route('peserta.attendance') }}" class="bottom-nav-item {{ request()->routeIs('peserta.attendance') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                <span class="bottom-nav-label">Absen</span>
            </a>
            <a href="{{ route('peserta.logbook') }}" class="bottom-nav-item {{ request()->routeIs('peserta.logbook') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm2 2h2v2H6V6zm2 3H6v2h2V9zm2-3h2v2h-2V6zm2 3h-2v2h2V9z" clip-rule="evenodd"/>
                </svg>
                <span class="bottom-nav-label">Logbook</span>
            </a>
            <a href="{{ route('peserta.leave') }}" class="bottom-nav-item {{ request()->routeIs('peserta.leave') ? 'active' : '' }}">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                </svg>
                <span class="bottom-nav-label">Izin/Sakit</span>
            </a>
        @endif
    </div>

    {{-- Floating Action Button (FAB) for Super Admin to Add Peserta / Pembimbing (Expandable Menu) --}}
    @if(auth()->check() && auth()->user()->isSuperAdmin() && (request()->routeIs('super-admin.*') || request()->routeIs('dashboard')))
        <div class="fab-container" id="global-fab-container">
            <!-- Expanded Menu -->
            <div class="fab-menu" id="global-fab-menu">
                <button type="button" class="fab-menu-item" id="fab-action-pembimbing" aria-label="Tambah Pembimbing">
                    <span class="fab-menu-label">Tambah Pembimbing</span>
                    <span class="fab-menu-icon">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                    </span>
                </button>
                <button type="button" class="fab-menu-item" id="fab-action-peserta" aria-label="Tambah Peserta">
                    <span class="fab-menu-label">Tambah Peserta</span>
                    <span class="fab-menu-icon">
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                        </svg>
                    </span>
                </button>
            </div>
            <!-- Main Trigger Button -->
            <button type="button" class="fab-main-btn" id="fab-main-trigger" aria-label="Menu Tambah">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="fab-icon">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
            </button>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('global-fab-container');
                const trigger = document.getElementById('fab-main-trigger');
                const actionPembimbing = document.getElementById('fab-action-pembimbing');
                const actionPeserta = document.getElementById('fab-action-peserta');

                if (!container || !trigger) return;

                // Toggle menu
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    container.classList.toggle('active');
                });

                // Close on click outside
                document.addEventListener('click', () => {
                    container.classList.remove('active');
                });

                // Trigger or redirect for Pembimbing
                actionPembimbing.addEventListener('click', () => {
                    const targetBtn = document.getElementById('open-add-pembimbing-modal');
                    if (targetBtn) {
                        targetBtn.click();
                    } else {
                        window.location.href = "{{ route('super-admin.pembimbing') }}?add=1";
                    }
                });

                // Trigger or redirect for Peserta
                actionPeserta.addEventListener('click', () => {
                    const targetBtn = document.getElementById('open-add-peserta-modal');
                    if (targetBtn) {
                        targetBtn.click();
                    } else {
                        window.location.href = "{{ route('super-admin.peserta') }}?add=1";
                    }
                });

                // Check URL query parameters to auto-open modal if redirected
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.get('add') === '1') {
                    setTimeout(() => {
                        const addPembimbingBtn = document.getElementById('open-add-pembimbing-modal');
                        const addPesertaBtn = document.getElementById('open-add-peserta-modal');
                        if (addPembimbingBtn) {
                            addPembimbingBtn.click();
                        } else if (addPesertaBtn) {
                            addPesertaBtn.click();
                        }
                        // Clean up URL parameters without refreshing the page
                        window.history.replaceState({}, document.title, window.location.pathname);
                    }, 400);
                }
            });
        </script>
    @endif


    {{-- Floating Action Button (FAB) for Admin (Pembimbing) to Approve Logbook / Izin (Right Position) --}}
    @if(auth()->check() && auth()->user()->isAdmin())
        <div class="fab-container-left" id="admin-fab-container">
            <!-- Expanded Menu -->
            <div class="fab-menu-left" id="admin-fab-menu">
                <a href="{{ route('admin.logbooks') }}" class="fab-menu-item-left" aria-label="Approve Logbook">
                    <span class="fab-menu-label-left">Approve Logbook</span>
                    <span class="fab-menu-icon-left">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </span>
                </a>
                <a href="{{ route('admin.leaves') }}" class="fab-menu-item-left" aria-label="Approve Izin/Sakit">
                    <span class="fab-menu-label-left">Approve Izin/Sakit</span>
                    <span class="fab-menu-icon-left">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                </a>
            </div>
            <!-- Main Trigger Button -->
            <button type="button" class="fab-main-btn-left" id="admin-fab-trigger" aria-label="Menu Admin">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="fab-icon-left">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
            </button>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('admin-fab-container');
                const trigger = document.getElementById('admin-fab-trigger');

                if (!container || !trigger) return;

                // Toggle menu
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    container.classList.toggle('active');
                });

                // Close on click outside
                document.addEventListener('click', () => {
                    container.classList.remove('active');
                });
            });
        </script>
    @endif


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
        window.userAttendanceStatus = {
            isPeserta: @json(auth()->check() && auth()->user()->isPeserta()),
            isAdmin: @json(auth()->check() && auth()->user()->isAdmin()),
            hasCheckedInToday: @json(auth()->check() && auth()->user()->isPeserta() && auth()->user()->attendances()->whereDate('tanggal', \Carbon\Carbon::today())->whereNotNull('jam_masuk')->exists()),
            hasCheckedOutToday: @json(auth()->check() && auth()->user()->isPeserta() && auth()->user()->attendances()->whereDate('tanggal', \Carbon\Carbon::today())->whereNotNull('jam_pulang')->exists()),
            isHolidayToday: @json(\App\Models\WorkSchedule::getScheduleForDate(\Carbon\Carbon::today())?->is_holiday ?? \Carbon\Carbon::today()->isWeekend())
        };
    </script>
    @vite('resources/js/dashboard-layout.js')
    @stack('scripts')

    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(reg => console.log('Service Worker Registered!'))
                .catch(err => console.log('SW Registration Failed:', err));
        });
    }
    </script>

    @if(config('app.env') === 'local')
        <script>
            (function() {
                let lastMtime = null;
                const checkInterval = 1500; // Poll every 1.5 seconds

                function checkReload() {
                    fetch('{{ route('dev-reload-check') }}')
                        .then(response => response.json())
                        .then(data => {
                            if (lastMtime === null) {
                                lastMtime = data.latest_mtime;
                            } else if (data.latest_mtime > lastMtime) {
                                console.log('File change detected! Reloading page...');
                                window.location.reload();
                            }
                        })
                        .catch(err => {
                            // Suppress errors
                        });
                }

                setInterval(checkReload, checkInterval);
            })();
        </script>
    @endif
</body>
</html>
