@extends('layouts.app')

@section('title', 'Pertandingan - Ludo Tracker')

@section('content')
<div class="page-title-bar">
    <div>
        <div class="page-title">Pertandingan</div>
        <div class="page-subtitle">{{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y') }}</div>
    </div>
    <div style="font-size:12px;color:var(--text-muted);display:{{ $matches->where('status_match', 'berlangsung')->count() ? 'flex' : 'none' }};align-items:center;gap:4px;">
        <div class="live-dot"></div> Live
    </div>
</div>

{{-- Foto harian + klik untuk fullscreen --}}
@if($dailyPhoto)
<div class="photo-header" style="cursor:pointer;" onclick="openFoto('{{ $dailyPhoto->foto_url }}', '{{ $dailyPhoto->deskripsi ?: 'Foto pertandingan hari ini' }}')">
    <img src="{{ $dailyPhoto->foto_url }}" alt="Foto hari ini" loading="lazy">
    <div class="photo-header-overlay">
        <div class="photo-header-label">
            📸 {{ $dailyPhoto->deskripsi ?: 'Foto pertandingan hari ini' }}
            <span style="margin-left:8px;font-size:11px;opacity:0.7;"><i class="bi bi-arrows-fullscreen"></i> Ketuk untuk perbesar</span>
        </div>
    </div>
</div>
@endif

<div class="page-section">
    <div class="section-title"><i class="bi bi-trophy"></i> Pertandingan Hari Ini</div>

    <div id="matches-container">
        @forelse($matches as $match)
            @include('components.match-card', ['match' => $match])
        @empty
            <div class="empty-state">
                <img src="/images/logo.png" alt="Salam Sendok" class="empty-state-logo" loading="lazy">
                <div class="empty-state-title">Belum ada pertandingan hari ini</div>
                <div class="empty-state-desc">Pertandingan belum dimulai. Cek lagi nanti.</div>
            </div>
        @endforelse
    </div>
</div>

<div style="padding:0 14px 8px;text-align:center;font-size:12px;color:var(--text-muted);">
    <i class="bi bi-arrow-repeat"></i> Skor diperbarui otomatis setiap 10 detik
</div>

{{-- ===== LIGHTBOX FULLSCREEN FOTO ===== --}}
<div id="lightbox" onclick="closeFoto()"
     style="display:none;position:fixed;inset:0;z-index:999;background:rgba(0,0,0,0.95);flex-direction:column;align-items:center;justify-content:center;padding:16px;">

    {{-- Tombol tutup --}}
    <button onclick="closeFoto()" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,0.15);border:none;color:#fff;width:40px;height:40px;border-radius:50%;font-size:20px;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:1000;">
        <i class="bi bi-x-lg"></i>
    </button>

    {{-- Foto --}}
    <img id="lightbox-img" src="" alt="Foto Pertandingan"
         style="max-width:100%;max-height:80vh;border-radius:12px;object-fit:contain;box-shadow:0 8px 40px rgba(0,0,0,0.6);"
         onclick="event.stopPropagation()">

    {{-- Caption --}}
    <div id="lightbox-caption"
         style="margin-top:14px;font-size:14px;color:rgba(255,255,255,0.8);text-align:center;font-family:'Poppins',sans-serif;font-weight:500;">
    </div>

    {{-- Hint --}}
    <div style="margin-top:8px;font-size:11px;color:rgba(255,255,255,0.4);font-family:'Poppins',sans-serif;">
        Ketuk di luar foto untuk menutup
    </div>
</div>
@endsection

@section('scripts')
<script>
// Lightbox
function openFoto(url, caption) {
    const lb = document.getElementById('lightbox');
    document.getElementById('lightbox-img').src = url;
    document.getElementById('lightbox-caption').textContent = '📸 ' + caption;
    lb.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeFoto() {
    document.getElementById('lightbox').style.display = 'none';
    document.body.style.overflow = '';
}

function openMatchProof(url, caption) {
    openFoto(url, caption);
}

// Tutup dengan tombol back/ESC
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeFoto(); });

// Auto reload tiap 10 detik
setInterval(() => location.reload(), 10000);
</script>
@endsection
