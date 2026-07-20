<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Absen Magang</title>
    
    <!-- PWA Settings -->
    <meta name="theme-color" content="#2e4085">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/Logo/Logo_Aplikasi.png') }}">

    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite('resources/css/auth-login.css')

    <style>
        /* PWA Promotion Banner Styles */
        .pwa-banner {
            background: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            padding: 16px;
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            color: #f8fafc;
            animation: pwaSlideUp 0.4s ease-out;
        }

        .pwa-banner-content {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .pwa-icon {
            background: rgba(255, 204, 51, 0.15);
            color: #ffcc33;
            border-radius: 12px;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .pwa-text-group {
            flex: 1;
        }

        .pwa-title {
            font-weight: 600;
            font-size: 0.95rem;
            margin-bottom: 2px;
        }

        .pwa-desc {
            font-size: 0.78rem;
            color: #94a3b8;
            line-height: 1.4;
        }

        .btn-pwa-action {
            background: #ffcc33;
            color: #0f172a;
            border: none;
            border-radius: 10px;
            padding: 10px;
            font-weight: 600;
            font-size: 0.88rem;
            cursor: pointer;
            transition: background 0.2s;
            text-align: center;
        }

        .btn-pwa-action:hover {
            background: #ffe082;
        }

        @keyframes pwaSlideUp {
            0% { opacity: 0; transform: translateY(15px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        /* Desktop Floating Layout (sebelah kanan layar melayang) */
        @media (min-width: 769px) {
            .pwa-banner {
                position: fixed;
                right: 30px;
                bottom: 30px;
                width: 340px;
                margin-top: 0;
                z-index: 9999;
                border: 1px solid rgba(255, 255, 255, 0.12);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            }
        }
    </style>
</head>
<body>

    <div class="bg-header-wave">
        <svg viewBox="0 0 1440 260" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0,200 C360,240 720,280 1080,220 C1260,190 1380,110 1440,60 L1440,0 L0,0 Z" fill="url(#bg-gradient-def)"></path>
            <defs>
                <linearGradient id="bg-gradient-def" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#1b264f" />
                    <stop offset="50%" stop-color="#2e4085" />
                    <stop offset="100%" stop-color="#384877" />
                </linearGradient>
            </defs>
        </svg>
    </div>

    <div class="login-container">
        <div class="card">
            <div class="header">
                <div class="login-logo-container">
                    <img src="{{ asset('images/Logo/Logo_PU.png') }}" alt="Logo PU" class="login-logo">
                </div>
                <div class="logo-title">Sistem Absen Magang</div>
                <div class="subtitle">Direktorat Bina Teknik Jalan dan Jembatan</div>
            </div>

            <h2 class="login-heading">Masuk</h2>

            @if ($errors->any() && !($errors->has('email') && $errors->first('email') === 'Akun Anda dinonaktifkan. Silakan hubungi administrator.'))
                <div class="error-banner">
                    <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                    </svg>
                    <span>Email atau password yang Anda masukkan salah.</span>
                </div>
            @endif

            @if ($errors->has('email') && $errors->first('email') === 'Akun Anda dinonaktifkan. Silakan hubungi administrator.')
                <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.17.2/dist/sweetalert2.all.min.js" integrity="sha384-Y1zzU5I7+ujiuXE1zuR3FJEPzfjZulUbsE9v7KDX7ztQ+fpy+aCix9RgfskCL2Oz" crossorigin="anonymous"></script>
                <div id="login-error-flag" data-error="Akun Anda dinonaktifkan. Silakan hubungi administrator."></div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" class="form-control" placeholder="Email Anda" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Kata Sandi" required>
                        <button type="button" class="password-toggle" id="password-toggle" aria-label="Tampilkan kata sandi" aria-pressed="false">
                            <span class="password-toggle-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Ingat Saya
                    </label>
                </div>

                <button type="submit" class="btn-submit">Masuk</button>
            </form>

            {{-- <div class="demo-accounts">
                <div class="demo-title">Akun Demo (Password: password)</div>
                <ul class="demo-list">
                    <li>
                        <span>superadmin@absendjj.com</span>
                        <span class="role-badge badge-sa">Super Admin</span>
                    </li>
                    <li>
                        <span>hendra.pembimbing@absendjj.com</span>
                        <span class="role-badge badge-ad">Pembimbing</span>
                    </li>
                    <li>
                        <span>adit.peserta@absendjj.com</span>
                        <span class="role-badge badge-pe">Intern</span>
                    </li>
                </ul>
            </div> --}}
        </div>

        <!-- PWA Install Recommendation Banner -->
        <div id="pwa-install-banner" class="pwa-banner" style="display: none;">
            <div class="pwa-banner-content">
                <div class="pwa-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                        <line x1="12" y1="18" x2="12.01" y2="18"></line>
                    </svg>
                </div>
                <div class="pwa-text-group">
                    <div class="pwa-title">Instal Aplikasi Absen</div>
                    <div class="pwa-desc">Akses absen magang lebih cepat & stabil langsung dari layar utama HP Anda.</div>
                </div>
            </div>
            <button type="button" id="btn-pwa-install" class="btn-pwa-action">Instal Sekarang</button>
        </div>
    </div>

    @vite('resources/js/auth-login.js')

    <script>
        // Register Service Worker on Login Page to satisfy PWA criteria
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Login SW Registered!'))
                    .catch(err => console.log('Login SW Registration failed:', err));
            });
        }

        // PWA Installation prompt handler
        let deferredPrompt;
        const pwaBanner = document.getElementById('pwa-install-banner');
        const installBtn = document.getElementById('btn-pwa-install');

        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent the mini-infobar from appearing on mobile
            e.preventDefault();
            // Stash the event so it can be triggered later.
            deferredPrompt = e;
            // Update UI to show the install promotion banner
            if (pwaBanner) {
                pwaBanner.style.display = 'flex';
            }
        });

        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (!deferredPrompt) return;
                // Show the install prompt
                deferredPrompt.prompt();
                // Wait for the user to respond to the prompt
                const { outcome } = await deferredPrompt.userChoice;
                // We've used the prompt, and can't use it again, discard it
                deferredPrompt = null;
                // Hide our install promotion banner
                if (pwaBanner) {
                    pwaBanner.style.display = 'none';
                }
            });
        }

        // Hide banner if app is already installed
        window.addEventListener('appinstalled', () => {
            deferredPrompt = null;
            if (pwaBanner) {
                pwaBanner.style.display = 'none';
            }
        });
    </script>
</body>
</html>

