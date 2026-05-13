<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d0f1a">
    <meta name="description" content="Salam Sendok - Pencatatan Skor Ludo Harian">

    {{-- PWA Meta --}}
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Salam Sendok">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <title>@yield('title', 'Salam Sendok')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="/css/app.css" rel="stylesheet">

    @yield('styles')
</head>
<body>

    {{-- Sticky Header --}}
    <header class="app-header">
        <div class="header-content">
            {{-- Logo Salam Sendok --}}
            <div class="header-brand">
                <img src="/images/logo.png"
                     alt="Salam Sendok"
                     style="height:36px;width:auto;object-fit:contain;filter:drop-shadow(0 0 6px rgba(255,255,255,0.15));">
                <span class="brand-name">Salam Sendok</span>
            </div>
            <div class="header-right">
                @auth('admin')
                    <span class="admin-badge"><i class="bi bi-shield-check"></i> Admin</span>
                @endauth
            </div>
        </div>
    </header>

    {{-- Main Content --}}
    <main class="app-main" id="app-main">
        <div id="toast-container" aria-live="polite" aria-atomic="true"></div>
        @yield('content')
    </main>

    {{-- Bottom Navigation --}}
    <nav class="bottom-nav" id="bottom-nav">
        @auth('admin')
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-people-fill nav-icon"></i>
                <span class="nav-label">Absensi</span>
            </a>
            <a href="{{ route('admin.matches') }}" class="nav-item {{ request()->routeIs('admin.matches') ? 'active' : '' }}">
                <i class="bi bi-trophy-fill nav-icon"></i>
                <span class="nav-label">Tanding</span>
            </a>
            <a href="{{ route('admin.history') }}" class="nav-item {{ request()->routeIs('admin.history') ? 'active' : '' }}">
                <i class="bi bi-clock-history nav-icon"></i>
                <span class="nav-label">History</span>
            </a>
            <a href="{{ route('admin.players') }}" class="nav-item {{ request()->routeIs('admin.players') ? 'active' : '' }}">
                <i class="bi bi-person-vcard-fill nav-icon"></i>
                <span class="nav-label">Pemain</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="nav-item nav-logout-form">
                @csrf
                <button type="submit" class="nav-item-btn">
                    <i class="bi bi-box-arrow-right nav-icon"></i>
                    <span class="nav-label">Keluar</span>
                </button>
            </form>
        @else
            <a href="{{ route('viewer.attendance') }}" class="nav-item {{ request()->routeIs('viewer.attendance') ? 'active' : '' }}">
                <i class="bi bi-people-fill nav-icon"></i>
                <span class="nav-label">Absensi</span>
            </a>
            <a href="{{ route('viewer.matches') }}" class="nav-item {{ request()->routeIs('viewer.matches') ? 'active' : '' }}">
                <i class="bi bi-trophy-fill nav-icon"></i>
                <span class="nav-label">Tanding</span>
            </a>
            <a href="{{ route('viewer.history') }}" class="nav-item {{ request()->routeIs('viewer.history') ? 'active' : '' }}">
                <i class="bi bi-clock-history nav-icon"></i>
                <span class="nav-label">History</span>
            </a>
            <a href="{{ route('login') }}" class="nav-item">
                <i class="bi bi-shield-lock nav-icon"></i>
                <span class="nav-label">Admin</span>
            </a>
        @endauth
    </nav>

    <div class="modal-overlay" id="modal-overlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/js/app.js"></script>

    @yield('scripts')

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
</body>
</html>
