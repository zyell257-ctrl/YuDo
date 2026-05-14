{{-- Komponen: Card Pertandingan --}}
{{-- Variable: $match (GameMatch dengan relasi scores.player) --}}

@php
    $isActive  = $match->status_match === 'berlangsung';
    $isAdmin   = auth('admin')->check();
    $positionOrder = ['juara' => 1, 'runner_up' => 2, 'ketiga' => 3, 'keempat' => 4, 'kelima' => 5, 'keenam' => 6, 'none' => 99];
    $hasManualPosition = $match->scores->contains(fn($score) => $score->posisi !== 'none');
    $scores    = $hasManualPosition
        ? $match->scores->sortBy(fn($score) => $positionOrder[$score->posisi] ?? 99)->values()
        : $match->scores->sortBy('id')->values();
    $hasProof  = filled($match->bukti_foto_pertandingan);
    $detailUrl = $isAdmin ? route('admin.matches.show', $match->id) : route('viewer.matches.show', $match->id);
    $positionOptions = collect([
        'juara' => 1,
        'runner_up' => 2,
        'ketiga' => 3,
        'keempat' => 4,
        'kelima' => 5,
        'keenam' => 6,
    ])->take($scores->count());
@endphp

<div class="card-ludo {{ $isActive ? 'card-active' : '' }}" id="match-card-{{ $match->id }}" data-has-proof="{{ $hasProof ? '1' : '0' }}" style="margin-bottom:12px;">

    {{-- Header card --}}
    <div class="match-card-header">
        <div>
            <div class="match-number">Pertandingan #{{ $match->nomor_match }}</div>
            <div class="match-time">
                <i class="bi bi-clock"></i>
                {{ $match->waktu_mulai?->format('H:i') ?? '--:--' }}
                @if($match->waktu_selesai)
                    – {{ $match->waktu_selesai->format('H:i') }}
                @endif
            </div>
        </div>
        <div style="display:flex; align-items:center; gap:8px;">
            @if($isActive)
                <span class="badge-berlangsung"><i class="bi bi-record-circle-fill"></i> Live</span>
            @else
                <span class="badge-selesai"><i class="bi bi-check-circle-fill"></i> Selesai</span>
            @endif

            @if($isAdmin)
            <div class="dropdown" style="position:relative;">
                <button onclick="toggleDropdown({{ $match->id }})" style="background:none; border:none; color:var(--text-muted); font-size:20px; cursor:pointer; padding:4px;">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <div id="dropdown-{{ $match->id }}" style="display:none; position:absolute; right:0; top:30px; background:var(--bg-card); border:1px solid var(--border); border-radius:12px; padding:6px; min-width:160px; z-index:50; box-shadow:0 8px 24px rgba(0,0,0,0.4);">
                    @if($isActive)
                    <button onclick="finishMatch({{ $match->id }}); toggleDropdown({{ $match->id }});" style="width:100%; background:none; border:none; color:var(--green); font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; padding:10px 14px; text-align:left; cursor:pointer; border-radius:8px; display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-flag-fill"></i> Selesaikan
                    </button>
                    @endif
                    <a href="{{ $detailUrl }}" style="width:100%; color:var(--text-secondary); font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; padding:10px 14px; text-align:left; cursor:pointer; border-radius:8px; display:flex; align-items:center; gap:8px; text-decoration:none;">
                        <i class="bi bi-eye-fill"></i> Detail
                    </a>
                    <button onclick="deleteMatch({{ $match->id }}); toggleDropdown({{ $match->id }});" style="width:100%; background:none; border:none; color:var(--red); font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; padding:10px 14px; text-align:left; cursor:pointer; border-radius:8px; display:flex; align-items:center; gap:8px;">
                        <i class="bi bi-trash3-fill"></i> Hapus
                    </button>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Bukti foto pertandingan --}}
    <div id="proof-wrap-{{ $match->id }}" class="match-proof-thumb" style="{{ $hasProof ? '' : 'display:none;' }}">
        <img id="proof-img-{{ $match->id }}"
             src="{{ $hasProof ? $match->bukti_foto_url : '' }}"
             alt="Bukti pertandingan #{{ $match->nomor_match }}"
             loading="lazy"
             onclick="openMatchProof?.('{{ $hasProof ? $match->bukti_foto_url : '' }}', 'Bukti Pertandingan #{{ $match->nomor_match }}')"
             style="cursor:pointer;">
    </div>

    {{-- Daftar skor pemain --}}
    <div>
        @foreach($scores as $i => $score)
        @php
            $player  = $score->player;
            $initials = $player->initials;
            $isWinner = $score->posisi !== 'none';
            $badge   = $score->badge;
            $rankNumber = $positionOrder[$score->posisi] ?? null;
        @endphp

        <div class="score-row {{ $isWinner ? 'winner-row' : '' }}">
            {{-- Rank --}}
            <div id="rank-display-{{ $score->id }}" style="font-size:18px; width:28px; text-align:center; flex-shrink:0;">
                @if($rankNumber && $rankNumber < 99)
                    <span class="rank-medal rank-{{ $rankNumber }}">{{ $badge }}</span>
                @else
                    <span style="font-size:13px; color:var(--text-muted); font-weight:600;">{{ $i+1 }}</span>
                @endif
            </div>

            {{-- Avatar --}}
            @if($player->foto_profile_url)
                <img class="player-avatar-img sm" src="{{ $player->foto_profile_url }}" alt="{{ $player->nama_pemain }}" loading="lazy" style="border-color:{{ $player->avatar_color }};">
            @else
                <div class="player-avatar sm" style="background:{{ $player->avatar_color }};">{{ $initials }}</div>
            @endif

            {{-- Info --}}
            <div class="score-row-info">
                <div class="score-row-name {{ $isWinner ? 'winner-name-wrap' : '' }}">
                    {{ $player->nama_pemain }}
                    <span id="position-pill-{{ $score->id }}" class="position-pill" {{ $score->position_label ? '' : 'hidden' }}>{{ $score->position_label }}</span>
                </div>
                <div class="score-row-sub">
                    <i class="bi bi-footprint"></i> Diinjek: <strong id="keinjek-{{ $score->id }}" style="color:var(--red);">{{ $score->skor_keinjek }}</strong>
                </div>
            </div>

            {{-- Kontrol skor (admin + pertandingan aktif) --}}
            @if($isAdmin && $isActive)
            <div style="display:flex; flex-direction:column; gap:7px; flex-shrink:0; width:92px;">
                {{-- Skor keinjek --}}
                <div class="score-control compact">
                    <button class="score-btn btn-minus score-btn-sm" onclick="changeScore({{ $score->id }}, 'keinjek', -1)" aria-label="Kurangi skor keinjek">−</button>
                    <button class="score-btn btn-plus score-btn-sm" onclick="changeScore({{ $score->id }}, 'keinjek', 1)" aria-label="Tambah skor keinjek">+</button>
                </div>
                <div class="rank-pick-grid" aria-label="Pilih posisi juara untuk {{ $player->nama_pemain }}">
                    @foreach($positionOptions as $positionValue => $rankNumber)
                        <button class="rank-pick-btn {{ $score->posisi === $positionValue ? 'selected' : '' }}"
                                id="rank-btn-{{ $score->id }}-{{ $positionValue }}"
                                data-score-id="{{ $score->id }}"
                                data-position="{{ $positionValue }}"
                                onclick="setMatchPosition({{ $match->id }}, {{ $score->id }}, '{{ $positionValue }}')"
                                type="button">
                            {{ $rankNumber }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
        @endforeach

        @if($scores->isEmpty())
            <div style="text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                Belum ada pemain di pertandingan ini.
            </div>
        @endif
    </div>

    @if($isAdmin && $isActive)
        <div class="match-proof-actions">
            <button class="proof-upload-mini" type="button" onclick="document.getElementById('proof-input-{{ $match->id }}').click()">
                <i class="bi bi-camera-fill"></i>
                <span id="proof-label-{{ $match->id }}">{{ $hasProof ? 'Ganti Bukti' : 'Upload Bukti' }}</span>
            </button>
            <button class="btn-gold-ludo {{ $hasProof ? '' : 'is-disabled' }}" id="finish-btn-{{ $match->id }}" onclick="finishMatch({{ $match->id }})" style="padding:10px 12px;font-size:12px;">
                <i class="bi bi-flag-fill"></i> Selesaikan
            </button>
        </div>
        <input type="file"
               id="proof-input-{{ $match->id }}"
               accept="image/jpeg,image/png,image/webp"
               style="display:none;"
               onchange="handleMatchProofInput({{ $match->id }}, this)">
    @else
        <div style="margin-top:10px;">
            <a class="btn-outline-ludo" href="{{ $detailUrl }}" style="display:flex;align-items:center;justify-content:center;gap:8px;text-decoration:none;">
                <i class="bi bi-eye-fill"></i> Lihat Detail
            </a>
        </div>
    @endif
</div>

<script>
function toggleDropdown(id) {
    const d = document.getElementById(`dropdown-${id}`);
    d.style.display = d.style.display === 'none' ? 'block' : 'none';
}

// Tutup dropdown jika klik di luar
document.addEventListener('click', e => {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('[id^="dropdown-"]').forEach(d => d.style.display = 'none');
    }
});
</script>
