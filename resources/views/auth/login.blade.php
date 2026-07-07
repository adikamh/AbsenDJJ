<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AbsenDJJ</title>
    
    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Custom Vanilla Styles -->
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #311042 100%);
            --accent-primary: #a855f7;
            --accent-secondary: #ec4899;
            --accent-hover: #c084fc;
            --text-primary: #f8fafc;
            --text-secondary: #94a3b8;
            --glass-bg: rgba(30, 41, 59, 0.45);
            --glass-border: rgba(255, 255, 255, 0.08);
            --glass-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.5);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-gradient);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Glowing Background Elements */
        .glow-sphere {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.15) 0%, rgba(0,0,0,0) 70%);
            filter: blur(40px);
            z-index: 1;
        }
        .glow-sphere-1 {
            top: 10%;
            left: 15%;
        }
        .glow-sphere-2 {
            bottom: 10%;
            right: 15%;
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .card {
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px 30px;
            box-shadow: var(--glass-shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            box-shadow: 0 12px 40px 0 rgba(168, 85, 247, 0.15);
        }

        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .logo-title {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            font-weight: 800;
            letter-spacing: -1px;
            background: linear-gradient(to right, #c084fc, #ec4899);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }

        .subtitle {
            font-size: 0.9rem;
            color: var(--text-secondary);
            font-weight: 300;
            line-height: 1.4;
        }

        .form-group {
            margin-bottom: 22px;
            position: relative;
        }

        .form-label {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: var(--text-primary);
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.3);
            background: rgba(15, 23, 42, 0.8);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            font-size: 0.85rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .remember-me input {
            margin-right: 8px;
            accent-color: var(--accent-primary);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(168, 85, 247, 0.6);
            filter: brightness(1.1);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Error Banner */
        .error-banner {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            padding: 12px 16px;
            border-radius: 12px;
            font-size: 0.85rem;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-banner svg {
            flex-shrink: 0;
            width: 18px;
            height: 18px;
            fill: currentColor;
        }

        /* Custom Bullet List */
        .demo-accounts {
            margin-top: 30px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 16px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }

        .demo-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
            text-align: center;
        }

        .demo-list {
            list-style: none;
            font-size: 0.75rem;
            color: var(--text-secondary);
        }

        .demo-list li {
            margin-bottom: 6px;
            display: flex;
            justify-content: space-between;
            background: rgba(15, 23, 42, 0.3);
            padding: 6px 10px;
            border-radius: 6px;
        }

        .demo-list li:last-child {
            margin-bottom: 0;
        }

        .role-badge {
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-sa { background: rgba(168, 85, 247, 0.2); color: #c084fc; }
        .badge-ad { background: rgba(59, 130, 246, 0.2); color: #60a5fa; }
        .badge-pe { background: rgba(16, 185, 129, 0.2); color: #34d399; }
    </style>
</head>
<body>

    <div class="glow-sphere glow-sphere-1"></div>
    <div class="glow-sphere glow-sphere-2"></div>

    <div class="login-container">
        <div class="card">
            <div class="header">
                <div class="logo-title">AbsenDJJ</div>
                <div class="subtitle">Sistem Absensi & Logbook Praktik<br>Data Jalan & Jembatan</div>
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
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        Ingat Saya
                    </label>
                </div>

                <button type="submit" class="btn-submit">Masuk Aplikasi</button>
            </form>

            <div class="demo-accounts">
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
            </div>
        </div>
    </div>

</body>
</html>
