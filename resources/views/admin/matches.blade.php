@extends('layouts.app')

@section('title', 'Pertandingan - Ludo Tracker')

@section('content')

<div class="page-title-bar">
    <div>
        <div class="page-title">Pertandingan</div>
        <div class="page-subtitle">{{ \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('D MMMM Y') }}</div>
    </div>
    @auth('admin')
    <button onclick="openUploadPhotoModal()" style="background:rgba(255,255,255,0.06); border:1px solid var(--border); border-radius:10px; padding:8px 12px; color:var(--text-secondary); cursor:pointer; font-size:13px; font-family:'Poppins',sans-serif;">
        <i class="bi bi-camera"></i>
    </button>
    @endauth
</div>

{{-- Foto harian (jika ada) --}}
@if($dailyPhoto)
<div class="photo-header">
    <img src="{{ Storage::url($dailyPhoto->foto) }}" alt="Foto hari ini" loading="lazy">
    <div class="photo-header-overlay">
        <div class="photo-header-label">
            📸 {{ $dailyPhoto->deskripsi ?: 'Foto pertandingan hari ini' }}
        </div>
    </div>
</div>
@else
@auth('admin')
<div style="margin: 0 14px 16px;">
    <button onclick="openUploadPhotoModal()" style="width:100%; background:rgba(244,196,48,0.06); border:2px dashed rgba(244,196,48,0.25); border-radius:16px; padding:16px; color:var(--gold); font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
        <i class="bi bi-camera-fill" style="font-size:18px;"></i>
        Upload Foto Hari Ini
    </button>
</div>
@endauth
@endif

{{-- List pertandingan --}}
<div class="page-section">
    <div class="section-title"><i class="bi bi-trophy"></i> Pertandingan Hari Ini</div>

    <div id="matches-container">
        @if($matches->isEmpty())
            <div class="empty-state" id="empty-state">
                <img src="/images/logo.png" alt="Salam Sendok" class="empty-state-logo" loading="lazy">
                <div class="empty-state-title">Belum ada pertandingan hari ini</div>
                <div class="empty-state-desc">
                    @auth('admin')
                        Tekan tombol <strong>+</strong> untuk menambah pertandingan baru.
                    @else
                        Pertandingan belum dimulai. Cek lagi nanti.
                    @endauth
                </div>
            </div>
        @else
            @foreach($matches as $match)
                @include('components.match-card', ['match' => $match])
            @endforeach
        @endif
    </div>
</div>

{{-- FAB tambah pertandingan (admin only) --}}
@auth('admin')
<button class="fab" onclick="openAddMatchModal()" aria-label="Tambah pertandingan">
    <i class="bi bi-plus"></i>
</button>
@endauth

{{-- MODAL: Tambah Pertandingan --}}
@auth('admin')
<div class="modal-ludo" id="modal-add-match">
    <div class="modal-ludo-backdrop" onclick="closeModal('modal-add-match')"></div>
    <div class="modal-ludo-content">
        <div class="modal-handle"></div>
        <div class="modal-title"><i class="bi bi-trophy" style="color:var(--gold);"></i> Tambah Pertandingan</div>

        <p style="font-size:13px; color:var(--text-muted); margin-bottom:14px;">
            Pilih 2–6 pemain yang akan bertanding hari ini.
        </p>

        <div class="form-ludo-group">
            <label class="form-ludo-label">Pilih Pemain</label>
            <div class="player-checkbox-grid" id="player-picker">
                @foreach($hadirToday as $player)
                <div class="player-check-item" onclick="togglePlayerSelect(this, {{ $player->id }})" data-id="{{ $player->id }}">
                    <div class="player-avatar sm" style="background:{{ $player->avatar_color }};">{{ $player->initials }}</div>
                    <span class="player-check-name">{{ $player->nama_pemain }}</span>
                    <i class="bi bi-circle" style="font-size:18px; color:var(--text-muted); flex-shrink:0;" id="check-icon-{{ $player->id }}"></i>
                </div>
                @endforeach

                @if($hadirToday->isEmpty())
                <div style="grid-column: span 2; text-align:center; padding:20px; color:var(--text-muted); font-size:13px;">
                    Tidak ada pemain yang hadir hari ini. Atur absensi terlebih dahulu.
                </div>
                @endif
            </div>
        </div>

        <div style="margin-top:6px; font-size:12px; color:var(--text-muted); margin-bottom:16px;" id="selected-count">
            0 pemain dipilih (pilih 2–6)
        </div>

        <button class="btn-gold-ludo" onclick="submitAddMatch()" id="btn-submit-match">
            <i class="bi bi-play-circle-fill"></i> Mulai Pertandingan
        </button>
    </div>
</div>

{{-- MODAL: Upload Foto --}}
<div class="modal-ludo" id="modal-upload-photo">
    <div class="modal-ludo-backdrop" onclick="closeModal('modal-upload-photo')"></div>
    <div class="modal-ludo-content">
        <div class="modal-handle"></div>
        <div class="modal-title"><i class="bi bi-camera" style="color:var(--gold);"></i> Upload Foto Hari Ini</div>

        <div class="form-ludo-group">
            <label class="form-ludo-label">Foto Pertandingan</label>
            <div class="photo-upload-zone" onclick="document.getElementById('foto-input').click()">
                <div id="photo-preview" style="display:none; margin-bottom:12px;">
                    <img id="preview-img" src="" alt="Preview" style="width:100%; max-height:200px; object-fit:cover; border-radius:10px;">
                </div>
                <div id="upload-placeholder">
                    <i class="bi bi-cloud-upload" style="font-size:36px; color:var(--text-muted); display:block; margin-bottom:8px;"></i>
                    <div style="font-size:13px; color:var(--text-secondary); font-weight:600;">Ketuk untuk pilih foto</div>
                    <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">JPG, PNG, WebP — maks 5MB</div>
                </div>
            </div>
            <input type="file" id="foto-input" accept="image/*" style="display:none;" onchange="previewPhoto(this)">
        </div>

        <div class="form-ludo-group">
            <label class="form-ludo-label">Deskripsi (opsional)</label>
            <input type="text" class="form-ludo-input" id="foto-deskripsi" placeholder="Contoh: Sesi malam hari ini">
        </div>

        <button class="btn-gold-ludo" onclick="submitUploadPhoto()">
            <i class="bi bi-cloud-upload-fill"></i> Upload Foto
        </button>
    </div>
</div>
@endauth

<input type="hidden" id="today-date" value="{{ $today }}">
@endsection

@section('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const today     = document.getElementById('today-date').value;
let selectedPlayers = new Set();

// ===== MODAL HELPERS =====
function openModal(id) {
    document.getElementById(id).classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    document.getElementById(id).classList.remove('show');
    document.body.style.overflow = '';
}

function openAddMatchModal() { openModal('modal-add-match'); }
function openUploadPhotoModal() { openModal('modal-upload-photo'); }

// ===== PLAYER SELECTION =====
function togglePlayerSelect(el, id) {
    if (selectedPlayers.has(id)) {
        selectedPlayers.delete(id);
        el.classList.remove('selected');
        document.getElementById(`check-icon-${id}`).className = 'bi bi-circle';
        document.getElementById(`check-icon-${id}`).style.color = 'var(--text-muted)';
    } else {
        if (selectedPlayers.size >= 6) {
            showToast('Maksimal 6 pemain per pertandingan.', 'error');
            return;
        }
        selectedPlayers.add(id);
        el.classList.add('selected');
        document.getElementById(`check-icon-${id}`).className = 'bi bi-check-circle-fill';
        document.getElementById(`check-icon-${id}`).style.color = 'var(--blue)';
    }
    document.getElementById('selected-count').textContent =
        `${selectedPlayers.size} pemain dipilih (pilih 2–6)`;
}

// ===== TAMBAH PERTANDINGAN =====
function submitAddMatch() {
    if (selectedPlayers.size < 2) {
        showToast('Pilih minimal 2 pemain.', 'error');
        return;
    }

    fetch('{{ route("admin.matches.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ player_ids: [...selectedPlayers] }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            closeModal('modal-add-match');
            selectedPlayers.clear();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Gagal membuat pertandingan.', 'error');
        }
    });
}

// ===== UPDATE SKOR =====
function changeScore(scoreId, field, delta) {
    const display  = document.getElementById(`${field}-${scoreId}`);
    let   current  = parseInt(display.textContent) || 0;
    const newVal   = Math.max(0, current + delta);
    display.textContent = newVal;

    // Update ke server (debounced)
    clearTimeout(window[`scoreTimer_${scoreId}_${field}`]);
    window[`scoreTimer_${scoreId}_${field}`] = setTimeout(() => {
        const keinjek = parseInt(document.getElementById(`keinjek-${scoreId}`).textContent) || 0;
        const total   = parseInt(document.getElementById(`total-${scoreId}`).textContent) || 0;

        fetch(`{{ url('/admin/pertandingan') }}/${scoreId}/skor`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ score_id: scoreId, skor_keinjek: keinjek, total_skor: total }),
        })
        .then(r => r.json())
        .then(d => { if (!d.success) showToast('Gagal simpan skor.', 'error'); });
    }, 600);
}

function resetScore(scoreId) {
    document.getElementById(`keinjek-${scoreId}`).textContent = 0;
    document.getElementById(`total-${scoreId}`).textContent   = 0;

    fetch(`{{ url('/admin/pertandingan') }}/${scoreId}/skor`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ score_id: scoreId, skor_keinjek: 0, total_skor: 0 }),
    });
}

// ===== PILIH POSISI JUARA MANUAL =====
function setMatchPosition(matchId, scoreId, posisi) {
    fetch(`{{ url('/admin/pertandingan') }}/${matchId}/posisi`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ score_id: scoreId, posisi }),
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            showToast(data.message || 'Gagal memilih posisi juara.', 'error');
            return;
        }

        const card = document.getElementById(`match-card-${matchId}`);
        if (card && Array.isArray(data.positions)) {
            card.querySelectorAll('.rank-pick-btn').forEach(btn => btn.classList.remove('selected'));
            data.positions.forEach(item => {
                if (item.posisi && item.posisi !== 'none') {
                    document.getElementById(`rank-btn-${item.score_id}-${item.posisi}`)?.classList.add('selected');
                }
                updateRankBadge(item.score_id, item.rank);
            });
        }
        showToast(data.message, 'success');
    })
    .catch(() => showToast('Koneksi bermasalah saat memilih posisi juara.', 'error'));
}

function updateRankBadge(scoreId, rank) {
    const display = document.getElementById(`rank-display-${scoreId}`);
    const pill = document.getElementById(`position-pill-${scoreId}`);
    if (!display || !pill) return;

    if (rank && rank < 99) {
        const medals = { 1: '🥇', 2: '🥈', 3: '🥉' };
        display.innerHTML = `<span class="rank-medal rank-${rank}">${medals[rank] || rank}</span>`;
        pill.textContent = `Juara ${rank}`;
        pill.hidden = false;
    } else {
        pill.hidden = true;
    }
}

// ===== SELESAIKAN PERTANDINGAN =====
async function finishMatch(matchId) {
    const card = document.getElementById(`match-card-${matchId}`);
    if (card?.dataset.hasProof !== '1') {
        showToast('Upload bukti pertandingan terlebih dahulu', 'error');
        return;
    }

    const ok = await showConfirmDialog({
        title: 'Selesaikan pertandingan?',
        message: 'Skor dan posisi juara manual akan dikunci untuk pertandingan ini.',
        confirmText: 'Selesaikan',
    });
    if (!ok) return;

    fetch(`{{ url('/admin/pertandingan') }}/${matchId}/selesai`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message, 'error');
        }
    });
}

// ===== HAPUS PERTANDINGAN =====
async function deleteMatch(matchId) {
    const ok = await showConfirmDialog({
        title: 'Hapus pertandingan?',
        message: 'Semua skor dan bukti foto pertandingan ini akan ikut terhapus.',
        confirmText: 'Hapus',
        danger: true,
    });
    if (!ok) return;

    fetch(`{{ url('/admin/pertandingan') }}/${matchId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById(`match-card-${matchId}`)?.remove();
            checkEmpty();
        } else {
            showToast(data.message, 'error');
        }
    });
}

// ===== UPLOAD BUKTI PER MATCH =====
async function handleMatchProofInput(matchId, input) {
    const file = input.files?.[0];
    const error = validateImageFile(file);
    if (error) {
        showToast(error, 'error');
        input.value = '';
        return;
    }

    const previewUrl = URL.createObjectURL(file);
    const img = document.getElementById(`proof-img-${matchId}`);
    const wrap = document.getElementById(`proof-wrap-${matchId}`);
    if (img && wrap) {
        img.src = previewUrl;
        wrap.style.display = '';
    }

    const ok = await showConfirmDialog({
        title: 'Upload bukti ini?',
        message: 'Preview sudah tampil di card. File akan dikompres otomatis sebelum disimpan.',
        confirmText: 'Upload',
    });
    if (!ok) {
        input.value = '';
        URL.revokeObjectURL(previewUrl);
        return;
    }

    const compressed = await compressImage(file, 1400, 0.78);
    const form = new FormData();
    form.append('bukti_foto_pertandingan', compressed);
    form.append('_token', csrfToken);

    fetch(`{{ url('/admin/pertandingan') }}/${matchId}/bukti-foto`, {
        method: 'POST',
        body: form,
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) {
            showToast(data.message || 'Gagal upload bukti.', 'error');
            return;
        }

        const card = document.getElementById(`match-card-${matchId}`);
        const finishBtn = document.getElementById(`finish-btn-${matchId}`);
        const label = document.getElementById(`proof-label-${matchId}`);
        if (card) card.dataset.hasProof = '1';
        if (finishBtn) finishBtn.classList.remove('is-disabled');
        if (label) label.textContent = 'Ganti Bukti';
        if (img) img.src = data.url;
        showToast(data.message, 'success');
    })
    .catch(() => showToast('Koneksi bermasalah saat upload bukti.', 'error'))
    .finally(() => {
        input.value = '';
        URL.revokeObjectURL(previewUrl);
    });
}

function openMatchProof(url, caption) {
    if (!url) return;
    const lightbox = document.createElement('div');
    lightbox.className = 'confirm-dialog-ludo';
    lightbox.innerHTML = `
        <div class="confirm-dialog-backdrop"></div>
        <div style="position:relative;width:100%;max-width:440px;">
            <button class="icon-btn-ludo" style="position:absolute;right:8px;top:8px;z-index:2;background:rgba(0,0,0,0.55);color:#fff;"><i class="bi bi-x-lg"></i></button>
            <img src="${url}" alt="${caption}" style="width:100%;max-height:82vh;object-fit:contain;border-radius:16px;border:1px solid var(--border);background:#000;" loading="lazy">
            <div style="text-align:center;color:var(--text-secondary);font-size:13px;margin-top:10px;">${caption}</div>
        </div>
    `;
    document.body.appendChild(lightbox);
    document.body.style.overflow = 'hidden';
    const close = () => { lightbox.remove(); document.body.style.overflow = ''; };
    lightbox.querySelector('.confirm-dialog-backdrop').addEventListener('click', close);
    lightbox.querySelector('button').addEventListener('click', close);
}

function checkEmpty() {
    const container = document.getElementById('matches-container');
    if (!container.querySelector('.card-ludo')) {
        document.getElementById('empty-state')?.removeAttribute('style');
    }
}

// ===== UPLOAD FOTO =====
function previewPhoto(input) {
    if (!input.files[0]) return;
    const reader = new FileReader();
    reader.onload = e => {
        document.getElementById('preview-img').src = e.target.result;
        document.getElementById('photo-preview').style.display = 'block';
        document.getElementById('upload-placeholder').style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
}

async function submitUploadPhoto() {
    const input = document.getElementById('foto-input');
    if (!input.files[0]) { showToast('Pilih foto terlebih dahulu.', 'error'); return; }
    const error = validateImageFile(input.files[0]);
    if (error) { showToast(error, 'error'); return; }

    const compressed = await compressImage(input.files[0], 1400, 0.78);
    const form = new FormData();
    form.append('foto', compressed);
    form.append('deskripsi', document.getElementById('foto-deskripsi').value);
    form.append('_token', csrfToken);

    fetch('{{ route("admin.matches.uploadPhoto") }}', { method: 'POST', body: form })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast('Foto berhasil diupload!', 'success');
                closeModal('modal-upload-photo');
                setTimeout(() => location.reload(), 800);
            } else {
                showToast(data.message || 'Gagal upload.', 'error');
            }
        });
}

// ===== REALTIME POLLING (tiap 10 detik untuk viewer) =====
@guest('admin')
setInterval(() => {
    fetch('{{ route("api.matches.today") }}')
        .then(r => r.json())
        .then(data => {
            // Hanya update skor display jika ada perubahan
            // (simplified: full reload jika perlu)
        });
}, 10000);
@endguest
</script>
@endsection
