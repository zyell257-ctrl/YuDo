@extends('layouts.app')

@section('title', 'Absensi - Ludo Tracker')

@section('content')
<div class="page-title-bar">
    <div>
        <div class="page-title">Absensi</div>
        <div class="page-subtitle">{{ $hariIni->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
    </div>
</div>

{{-- Date Banner --}}
<div class="date-banner">
    <div class="date-banner-left">
        <div class="day">{{ $hariIni->locale('id')->isoFormat('dddd') }}</div>
        <div class="date">{{ $hariIni->locale('id')->isoFormat('D MMMM Y') }}</div>
    </div>
    <div class="date-banner-right">
        <div class="time" id="jam-sekarang">{{ $hariIni->format('H:i') }}</div>
    </div>
</div>

{{-- Statistik --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:0 14px 16px;">
    <div class="card-ludo" style="text-align:center;padding:14px;">
        <div style="font-size:26px;font-weight:800;color:var(--green);" id="count-hadir">
            {{ $attendances->where('status_hadir','hadir')->count() }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);font-weight:600;margin-top:2px;">HADIR</div>
    </div>
    <div class="card-ludo" style="text-align:center;padding:14px;">
        <div style="font-size:26px;font-weight:800;color:var(--red);" id="count-tidak">
            {{ $attendances->where('status_hadir','tidak_hadir')->count() }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);font-weight:600;margin-top:2px;">TIDAK HADIR</div>
    </div>
</div>

{{-- Daftar Pemain (read-only) --}}
<div class="page-section">
    <div class="section-title"><i class="bi bi-people"></i> Daftar Pemain</div>

    <div id="attendance-list">
        @forelse($players as $player)
            @php
                $att    = $attendances->get($player->id);
                $status = $att ? $att->status_hadir : 'tidak_hadir';
            @endphp
            <div class="card-ludo" id="player-card-{{ $player->id }}" data-status="{{ $status }}">
                <div style="display:flex;align-items:center;gap:12px;">

                    {{-- Avatar tersinkron dari tabel pemain --}}
                    <div style="position:relative;flex-shrink:0;">
                        @if($player->foto_profile_url)
                            <img class="player-avatar-img" src="{{ $player->foto_profile_url }}" alt="{{ $player->nama_pemain }}" loading="lazy" style="border-color:{{ $player->avatar_color }};">
                        @else
                            <div class="player-avatar" style="background:{{ $player->avatar_color }};">
                                {{ $player->initials }}
                            </div>
                        @endif
                        {{-- Status dot --}}
                        <div style="position:absolute;bottom:0;right:0;width:13px;height:13px;border-radius:50%;border:2px solid var(--bg-secondary);background:{{ $status === 'hadir' ? 'var(--green)' : 'var(--red)' }};"></div>
                    </div>

                    {{-- Info --}}
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:15px;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $player->nama_pemain }}
                        </div>
                        <div style="margin-top:4px;">
                            @if($status === 'hadir')
                                <span class="badge-hadir"><i class="bi bi-check-circle-fill"></i> Hadir</span>
                            @else
                                <span class="badge-tidak-hadir"><i class="bi bi-x-circle-fill"></i> Tidak Hadir</span>
                            @endif
                        </div>
                    </div>

                    {{-- Icon status --}}
                    <div style="font-size:26px;flex-shrink:0;">
                        @if($status === 'hadir')
                            <span style="color:var(--green);">✓</span>
                        @else
                            <span style="color:var(--red);">✗</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <img src="/images/logo.png" alt="Salam Sendok" class="empty-state-logo" loading="lazy">
                <div class="empty-state-title">Belum ada pemain</div>
                <div class="empty-state-desc">Admin belum menambahkan pemain.</div>
            </div>
        @endforelse
    </div>
</div>

<div style="padding:0 14px 8px;text-align:center;font-size:12px;color:var(--text-muted);">
    <i class="bi bi-arrow-repeat"></i> Diperbarui otomatis setiap 15 detik
</div>
@endsection

@section('scripts')
<script>
setInterval(() => {
    const now = new Date();
    document.getElementById('jam-sekarang').textContent =
        String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
}, 10000);

setInterval(() => {
    fetch('{{ route("api.attendance.today") }}')
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            let h = 0, t = 0;
            data.data.forEach(p => {
                const card = document.getElementById(`player-card-${p.player_id}`);
                if (!card) return;
                card.dataset.status = p.status_hadir;
                p.status_hadir === 'hadir' ? h++ : t++;
            });
            document.getElementById('count-hadir').textContent = h;
            document.getElementById('count-tidak').textContent = t;
        });
}, 15000);
</script>
@endsection
