<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#0d0f1a">
    <link rel="manifest" href="/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <title>Login - Salam Sendok</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-primary:   #0d0f1a;
            --bg-card:      #1a1f35;
            --gold:         #f4c430;
            --blue:         #4361ee;
            --text-primary: #eef0f8;
            --text-muted:   #555c7a;
            --border:       rgba(255,255,255,0.08);
            --green:        #2ec27e;
            --red:          #e84545;
        }
        *, *::before, *::after { box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Background grid */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        /* Decorative glow */
        .bg-glow-1 {
            position: fixed; top: -100px; right: -100px;
            width: 350px; height: 350px; border-radius: 50%; pointer-events: none; z-index: 0;
            background: radial-gradient(circle, rgba(244,196,48,0.07), transparent 70%);
        }
        .bg-glow-2 {
            position: fixed; bottom: -120px; left: -80px;
            width: 380px; height: 380px; border-radius: 50%; pointer-events: none; z-index: 0;
            background: radial-gradient(circle, rgba(67,97,238,0.09), transparent 70%);
        }

        .login-container {
            position: relative; z-index: 1;
            width: 100%; max-width: 380px;
        }

        /* Logo area */
        .login-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-frame {
            width: 132px;
            height: 132px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .logo-img {
            display: block;
            width: 118px;
            height: 118px;
            object-fit: contain;
            filter: drop-shadow(0 4px 20px rgba(255,255,255,0.1));
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }
        .logo-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-top: 7px;
            font-weight: 500;
            letter-spacing: 0.3px;
        }
        .login-brand-name {
            display: block;
            width: 100%;
            margin-top: 4px;
            font-size: 25px;
            font-weight: 800;
            line-height: 1.1;
            background: linear-gradient(135deg, var(--gold), #fff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: 0;
            filter: drop-shadow(0 0 12px rgba(244,196,48,0.12));
        }

        /* Login card */
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 28px 24px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.4);
        }
        .card-title {
            font-size: 18px; font-weight: 700;
            margin-bottom: 4px;
            display: flex; align-items: center; gap: 8px;
        }
        .card-subtitle {
            font-size: 13px; color: var(--text-muted); margin-bottom: 24px;
        }

        /* Error */
        .alert-error {
            background: rgba(232,69,69,0.12);
            border: 1px solid rgba(232,69,69,0.25);
            border-radius: 12px; padding: 12px 14px;
            font-size: 13px; color: var(--red);
            margin-bottom: 18px;
            display: flex; align-items: center; gap: 8px;
        }

        /* Form */
        .form-group { margin-bottom: 16px; }
        .form-label {
            font-size: 12px; font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.8px;
            display: block; margin-bottom: 8px;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            font-size: 17px; color: var(--text-muted); pointer-events: none;
        }
        .form-input {
            width: 100%;
            background: var(--bg-primary);
            border: 1.5px solid var(--border);
            border-radius: 12px;
            padding: 13px 14px 13px 42px;
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
            font-size: 15px; outline: none;
            transition: border-color 0.18s ease;
            -webkit-appearance: none;
        }
        .form-input:focus { border-color: var(--blue); }
        .form-input::placeholder { color: var(--text-muted); }
        .toggle-password {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            color: var(--text-muted); cursor: pointer; font-size: 17px; padding: 0;
        }

        /* Submit button */
        .btn-login {
            width: 100%; background: var(--gold); color: #0d0f1a;
            border: none; border-radius: 12px; padding: 14px;
            font-family: 'Poppins', sans-serif; font-size: 15px; font-weight: 700;
            cursor: pointer; transition: opacity 0.18s, transform 0.1s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 8px;
        }
        .btn-login:active { transform: scale(0.97); opacity: 0.88; }

        .viewer-link {
            text-align: center; margin-top: 20px;
            font-size: 13px; color: var(--text-muted);
        }
        .viewer-link a {
            color: var(--blue); text-decoration: none; font-weight: 600;
        }

        .hint-box {
            background: rgba(67,97,238,0.08);
            border: 1px solid rgba(67,97,238,0.2);
            border-radius: 10px; padding: 10px 14px;
            font-size: 12px; color: #8a90b0;
            margin-top: 16px; text-align: center;
        }
    </style>
</head>
<body>
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>

    <div class="login-container">

        {{-- Logo Salam Sendok --}}
        <div class="login-logo">
            <div class="logo-frame">
                <img src="/images/logo.png" alt="Salam Sendok" class="logo-img">
            </div>
            <div class="login-brand-name">Salam Sendok</div>
            <div class="logo-subtitle">Pencatatan Skor YuDo</div>
        </div>

        {{-- Card Login --}}
        <div class="login-card">
            <div class="card-title">
                <i class="bi bi-shield-lock" style="color:var(--gold)"></i>
                Masuk sebagai Admin
            </div>
            <div class="card-subtitle">Verifikasi dlu dong...</div>

            @if ($errors->has('login'))
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ $errors->first('login') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert-error">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-wrap">
                        <i class="bi bi-person input-icon"></i>
                        <input type="text" id="username" name="username"
                               class="form-input"
                               placeholder="Masukkan username"
                               value="{{ old('username') }}"
                               autocomplete="username"
                               autocorrect="off" autocapitalize="none" required>
                    </div>
                    @error('username')
                        <div style="color:var(--red);font-size:12px;margin-top:6px;">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock input-icon"></i>
                        <input type="password" id="password" name="password"
                               class="form-input"
                               placeholder="Masukkan password"
                               autocomplete="current-password" required>
                        <button type="button" class="toggle-password" onclick="togglePassword()">
                            <i class="bi bi-eye" id="eye-icon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i> Masuk
                </button>
            </form>
        </div>

        <div class="viewer-link">
            Bukan admin? <a href="{{ route('viewer.attendance') }}">Lihat sebagai viewer →</a>
        </div>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        }
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
</body>
</html>
