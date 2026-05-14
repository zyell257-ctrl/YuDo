@extends('layouts.app')

@section('title', 'Detail Pertandingan - Ludo Tracker')

@section('content')
@php
    $isAdmin = auth('admin')->check();
    $positionOrder = ['juara' => 1, 'runner_up' => 2, 'ketiga' => 3, 'keempat' => 4, 'kelima' => 5, 'keenam' => 6, 'none' => 99];
    $hasManualPosition = $match->scores->contains(fn($score) => $score->posisi !== 'none');
    $scores = $hasManualPosition
        ? $match->scores->sortBy(fn($score) => $positionOrder[$score->posisi] ?? 99)->values()
        : $match->scores->sortBy('id')->values();
@endphp

<div class="page-title-bar">
    <div>
        <div class="page-title">Detail Match #{{ $match->nomor_match }}</div>
        <div class="page-subtitle">{{ $match->tanggal_match->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
    </div>
    <span class="{{ $match->status_match === 'berlangsung' ? 'badge-berlangsung' : 'badge-selesai' }}">
        <i class="bi {{ $match->status_match === 'berlangsung' ? 'bi-record-circle-fill' : 'bi-check-circle-fill' }}"></i>
        {{ ucfirst($match->status_match) }}
    </span>
</div>

<div class="page-section">
    <div class="card-ludo">
        <div class="match-card-header">
            <div>
                <div class="match-number">Waktu Pertandingan</div>
                <div class="match-time">
                    <i class="bi bi-clock"></i>
                    {{ $match->waktu_mulai?->format('H:i') ?? '--:--' }}
                    @if($match->waktu_selesai)
                        - {{ $match->waktu_selesai->format('H:i') }}
                    @endif
                </div>
            </div>
        </div>

        @if($match->bukti_foto_url)
            <div class="match-proof-thumb">
                <img src="{{ $match->bukti_foto_url }}" alt="Bukti pertandingan #{{ $match->nomor_match }}" loading="lazy" onclick="openDetailProof('{{ $match->bukti_foto_url }}')" style="cursor:pointer;">
            </div>
        @else
            <div style="border:1px dashed var(--border);border-radius:var(--radius-md);padding:18px;text-align:center;color:var(--text-muted);font-size:13px;">
                <i class="bi bi-image"></i> Belum ada bukti foto pertandingan.
            </div>
        @endif
    </div>

    <div class="section-title"><i class="bi bi-trophy"></i> Skor Pemain</div>
    <div class="card-ludo">
        @forelse($scores as $i => $score)
            @php
                $player = $score->player;
            @endphp
            <div class="score-row">
                <div style="font-size:18px;width:28px;text-align:center;flex-shrink:0;">
                    @if($score->rank_number && $score->rank_number < 99)
                        <span class="rank-medal rank-{{ $score->rank_number }}">{{ $score->badge }}</span>
                    @else
                        <span style="font-size:13px;color:var(--text-muted);font-weight:600;">{{ $i + 1 }}</span>
                    @endif
                </div>
                @if($player->foto_profile_url)
                    <img class="player-avatar-img sm" src="{{ $player->foto_profile_url }}" alt="{{ $player->nama_pemain }}" loading="lazy" style="border-color:{{ $player->avatar_color }};">
                @else
                    <div class="player-avatar sm" style="background:{{ $player->avatar_color }};">{{ $player->initials }}</div>
                @endif
                <div class="score-row-info">
                    <div class="score-row-name">
                        {{ $player->nama_pemain }}
                        @if($score->position_label)
                            <span class="position-pill">{{ $score->position_label }}</span>
                        @endif
                    </div>
                    <div class="score-row-sub">
                        <i class="bi bi-footprint"></i> Diinjek:
                        <strong style="color:var(--red);">{{ $score->skor_keinjek }}</strong>
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align:center;color:var(--text-muted);font-size:13px;padding:18px;">Belum ada skor.</div>
        @endforelse
    </div>
</div>
@endsection

@section('scripts')
<script>
function openDetailProof(url) {
    const lightbox = document.createElement('div');
    lightbox.className = 'confirm-dialog-ludo';
    lightbox.innerHTML = `
        <div class="confirm-dialog-backdrop"></div>
        <div style="position:relative;width:100%;max-width:460px;">
            <button class="icon-btn-ludo" style="position:absolute;right:8px;top:8px;z-index:2;background:rgba(0,0,0,0.55);color:#fff;"><i class="bi bi-x-lg"></i></button>
            <img src="${url}" alt="Bukti pertandingan" style="width:100%;max-height:82vh;object-fit:contain;border-radius:16px;border:1px solid var(--border);background:#000;" loading="lazy">
        </div>
    `;
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
    const close = () => { lightbox.remove(); document.body.style.overflow = ''; };
    lightbox.querySelector('.confirm-dialog-backdrop').addEventListener('click', close);
    lightbox.querySelector('button').addEventListener('click', close);
}
</script>
@endsection
