@extends('layouts.app')

@section('title', 'Absensi - Ludo Tracker')

@section('content')
@php
    $draftHadirCount = $players->filter(fn($player) => optional($attendances->get($player->id))->status_hadir === 'hadir')->count();
    $draftTidakCount = $players->count() - $draftHadirCount;
    $hasSavedAttendance = $attendances->isNotEmpty();
@endphp

<div class="page-title-bar">
    <div>
        <div class="page-title">Absensi</div>
        <div class="page-subtitle">{{ $hariIni->locale('id')->isoFormat('dddd, D MMMM Y') }}</div>
    </div>
    <div style="display:flex;gap:8px;">
        <button class="btn-outline-ludo" id="save-attendance-btn" style="width:auto;padding:8px 14px;font-size:12px;" onclick="saveAttendance()">
            <i class="bi bi-save-fill"></i> Simpan
        </button>
        <button class="btn-gold-ludo" style="width:auto;padding:8px 14px;font-size:12px;" onclick="hadirSemua()">
            <i class="bi bi-check2-all"></i> Hadir Semua
        </button>
    </div>
</div>

<div id="attendance-save-state"
     style="margin:0 14px 12px;padding:10px 12px;border:1px solid {{ $hasSavedAttendance ? 'rgba(16,185,129,0.28)' : 'rgba(244,196,48,0.28)' }};border-radius:10px;background:{{ $hasSavedAttendance ? 'rgba(16,185,129,0.08)' : 'rgba(244,196,48,0.08)' }};color:var(--text-secondary);font-size:12px;font-weight:600;">
    <i class="bi {{ $hasSavedAttendance ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' }}"></i>
    <span id="attendance-save-text">{{ $hasSavedAttendance ? 'Absensi hari ini sudah tersimpan. Ubah status lalu klik Simpan untuk edit.' : 'Absensi hari ini belum tersimpan. Atur status lalu klik Simpan.' }}</span>
</div>

<div class="date-banner">
    <div class="date-banner-left">
        <div class="day">{{ $hariIni->locale('id')->isoFormat('dddd') }}</div>
        <div class="date">{{ $hariIni->locale('id')->isoFormat('D MMMM Y') }}</div>
    </div>
    <div class="date-banner-right">
        <div class="time" id="jam-sekarang">{{ $hariIni->format('H:i') }}</div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:0 14px 16px;">
    <div class="card-ludo" style="text-align:center;padding:14px;">
        <div style="font-size:26px;font-weight:800;color:var(--green);" id="count-hadir">
            {{ $draftHadirCount }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);font-weight:600;margin-top:2px;">HADIR</div>
    </div>
    <div class="card-ludo" style="text-align:center;padding:14px;">
        <div style="font-size:26px;font-weight:800;color:var(--red);" id="count-tidak">
            {{ $draftTidakCount }}
        </div>
        <div style="font-size:11px;color:var(--text-muted);font-weight:600;margin-top:2px;">TIDAK HADIR</div>
    </div>
</div>

<div class="page-section">
    <div class="section-title"><i class="bi bi-people"></i> Daftar Pemain</div>

    <div id="attendance-list">
        @forelse($players as $player)
            @php
                $att = $attendances->get($player->id);
                $status = $att ? $att->status_hadir : 'tidak_hadir';
            @endphp
            <div class="card-ludo" id="player-card-{{ $player->id }}" data-status="{{ $status }}">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="position:relative;flex-shrink:0;">
                        @if($player->foto_profile_url)
                            <img class="player-avatar-img" src="{{ $player->foto_profile_url }}" alt="{{ $player->nama_pemain }}" loading="lazy" style="border-color:{{ $player->avatar_color }};">
                        @else
                            <div class="player-avatar" style="background:{{ $player->avatar_color }};">
                                {{ $player->initials }}
                            </div>
                        @endif
                        <div id="status-dot-{{ $player->id }}"
                             style="position:absolute;bottom:0;right:0;width:13px;height:13px;border-radius:50%;border:2px solid var(--bg-card);background:{{ $status === 'hadir' ? 'var(--green)' : 'var(--red)' }};"></div>
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div style="font-size:15px;font-weight:600;color:var(--text-primary);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $player->nama_pemain }}
                        </div>
                        <div id="status-label-{{ $player->id }}" style="margin-top:4px;">
                            @if($status === 'hadir')
                                <span class="badge-hadir"><i class="bi bi-check-circle-fill"></i> Hadir</span>
                            @else
                                <span class="badge-tidak-hadir"><i class="bi bi-x-circle-fill"></i> Tidak Hadir</span>
                            @endif
                        </div>
                    </div>

                    <button class="attendance-toggle {{ $status === 'hadir' ? 'hadir' : 'tidak-hadir' }}"
                            id="toggle-{{ $player->id }}"
                            onclick="toggleAttendance({{ $player->id }}, this)"
                            aria-label="Ubah status {{ $player->nama_pemain }}">
                    </button>
                </div>
            </div>
        @empty
            <div class="empty-state">
                <img src="/images/logo.png" alt="Salam Sendok" class="empty-state-logo" loading="lazy">
                <div class="empty-state-title">Belum ada pemain</div>
                <div class="empty-state-desc">Tambah pemain dari menu manajemen pemain.</div>
            </div>
        @endforelse
    </div>
</div>

<input type="hidden" id="today-date" value="{{ $today }}">
@endsection

@section('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const todayDate = document.getElementById('today-date').value;
let attendanceDirty = false;

setInterval(() => {
    const now = new Date();
    document.getElementById('jam-sekarang').textContent =
        String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
}, 10000);

function toggleAttendance(playerId, btn) {
    const card = document.getElementById(`player-card-${playerId}`);
    const label = document.getElementById(`status-label-${playerId}`);
    const dot = document.getElementById(`status-dot-${playerId}`);
    const isHadir = card.dataset.status === 'hadir';
    const newStatus = isHadir ? 'tidak_hadir' : 'hadir';

    card.dataset.status = newStatus;
    btn.classList.toggle('hadir', newStatus === 'hadir');
    btn.classList.toggle('tidak-hadir', newStatus !== 'hadir');
    label.innerHTML = newStatus === 'hadir'
        ? '<span class="badge-hadir"><i class="bi bi-check-circle-fill"></i> Hadir</span>'
        : '<span class="badge-tidak-hadir"><i class="bi bi-x-circle-fill"></i> Tidak Hadir</span>';
    if (dot) dot.style.background = newStatus === 'hadir' ? 'var(--green)' : 'var(--red)';
    updateCounts();
    markAttendanceDirty();
}

function hadirSemua() {
    document.querySelectorAll('[id^="player-card-"]').forEach(card => {
        const pid = card.id.replace('player-card-', '');
        card.dataset.status = 'hadir';
        const toggle = document.getElementById(`toggle-${pid}`);
        const label = document.getElementById(`status-label-${pid}`);
        const dot = document.getElementById(`status-dot-${pid}`);
        if (toggle) { toggle.classList.add('hadir'); toggle.classList.remove('tidak-hadir'); }
        if (label) label.innerHTML = '<span class="badge-hadir"><i class="bi bi-check-circle-fill"></i> Hadir</span>';
        if (dot) dot.style.background = 'var(--green)';
    });
    updateCounts();
    markAttendanceDirty();
    showToast('Semua pemain ditandai hadir. Klik Simpan untuk menyimpan.', 'info');
}

function saveAttendance() {
    const rows = Array.from(document.querySelectorAll('[id^="player-card-"]')).map(card => ({
        player_id: Number(card.id.replace('player-card-', '')),
        status: card.dataset.status === 'hadir' ? 'hadir' : 'tidak_hadir',
    }));

    if (!rows.length) {
        showToast('Belum ada pemain untuk disimpan.', 'error');
        return;
    }

    const btn = document.getElementById('save-attendance-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

    fetch('/admin/absensi/simpan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ tanggal: todayDate, attendances: rows }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            showToast(data.message || 'Gagal menyimpan absensi.', 'error');
            return;
        }

        attendanceDirty = false;
        setSaveState('Absensi tersimpan. Kamu masih bisa edit status lalu klik Simpan lagi.', 'saved');
        showToast(data.message || 'Absensi tersimpan.', 'success');
    })
    .catch(() => showToast('Koneksi bermasalah saat menyimpan absensi.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save-fill"></i> Simpan';
    });
}

function updateCounts() {
    let hadir = 0;
    let tidak = 0;
    document.querySelectorAll('[id^="player-card-"]').forEach(card => {
        card.dataset.status === 'hadir' ? hadir++ : tidak++;
    });
    document.getElementById('count-hadir').textContent = hadir;
    document.getElementById('count-tidak').textContent = tidak;
}

function markAttendanceDirty() {
    attendanceDirty = true;
    setSaveState('Ada perubahan yang belum disimpan.', 'dirty');
}

function setSaveState(message, state) {
    const wrap = document.getElementById('attendance-save-state');
    const text = document.getElementById('attendance-save-text');
    const icon = wrap.querySelector('i');
    text.textContent = message;

    const isSaved = state === 'saved';
    wrap.style.borderColor = isSaved ? 'rgba(16,185,129,0.28)' : 'rgba(244,196,48,0.28)';
    wrap.style.background = isSaved ? 'rgba(16,185,129,0.08)' : 'rgba(244,196,48,0.08)';
    icon.className = `bi ${isSaved ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'}`;
}

window.addEventListener('beforeunload', e => {
    if (!attendanceDirty) return;
    e.preventDefault();
    e.returnValue = '';
});
</script>
@endsection
