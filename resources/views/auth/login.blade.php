<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Absen Magang</title>
    
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    @vite('resources/css/auth-login.css')
</head>
<body>

    <div class="glow-sphere glow-sphere-1"></div>
    <div class="glow-sphere glow-sphere-2"></div>

    <div class="login-container">
        <div class="card">
            <div class="header">
                <img src="{{ asset('images/Logo/Logo_PU.png') }}" alt="Logo PU" class="login-logo">
                <div class="logo-title">Sistem Absen Magang</div>
                <div class="subtitle">Direktorat Bina Teknik Jalan dan Jembatan</div>
            </div>

            @if ($errors->any())
                <div class="error-banner">
                    <svg viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" />
                    </svg>
                    <span>Email atau password yang Anda masukkan salah.</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Alamat Email</label>
                    <div class="input-wrapper">
                        <input type="email" id="email" name="email" class="form-control" placeholder="nama@email.com" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Kata Sandi</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control" placeholder="********" required>
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
    </div>

    @vite('resources/js/auth-login.js')
</body>
</html>

