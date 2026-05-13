@extends('layouts.app')

@section('title', 'Manajemen Pemain - Ludo Tracker')

@section('content')
<div class="page-title-bar">
    <div>
        <div class="page-title">Pemain</div>
        <div class="page-subtitle">Kelola profil dan roster Salam Sendok</div>
    </div>
</div>

<div class="player-management-grid" id="player-list">
    @forelse($players as $player)
        <div class="card-ludo player-manage-card" id="manage-player-{{ $player->id }}">
            @if($player->foto_profile_url)
                <img class="player-avatar-img lg" src="{{ $player->foto_profile_url }}" alt="{{ $player->nama_pemain }}" loading="lazy" style="border-color:{{ $player->avatar_color }};">
            @else
                <div class="player-avatar lg" style="background:{{ $player->avatar_color }};">{{ $player->initials }}</div>
            @endif
            <div class="player-manage-info">
                <div class="player-manage-name">{{ $player->nama_pemain }}</div>
                <div style="font-size:12px;color:var(--text-muted);margin-top:4px;">ID #{{ $player->id }}</div>
            </div>
            <button class="icon-btn-ludo" onclick='openPlayerModal(@json($player))' aria-label="Edit {{ $player->nama_pemain }}">
                <i class="bi bi-pencil-fill"></i>
            </button>
            <button class="icon-btn-ludo danger" onclick="deletePlayer({{ $player->id }}, '{{ addslashes($player->nama_pemain) }}')" aria-label="Hapus {{ $player->nama_pemain }}">
                <i class="bi bi-trash3-fill"></i>
            </button>
        </div>
    @empty
        <div class="empty-state">
            <img src="/images/logo.png" alt="Salam Sendok" class="empty-state-logo" loading="lazy">
            <div class="empty-state-title">Belum ada pemain</div>
            <div class="empty-state-desc">Tambah pemain pertama dengan tombol plus.</div>
        </div>
    @endforelse
</div>

<button class="fab" onclick="openPlayerModal()" aria-label="Tambah pemain">
    <i class="bi bi-plus"></i>
</button>

<div class="modal-ludo" id="modal-player-form">
    <div class="modal-ludo-backdrop" onclick="closeModal('modal-player-form')"></div>
    <div class="modal-ludo-content">
        <div class="modal-handle"></div>
        <div class="modal-title" id="player-modal-title">
            <i class="bi bi-person-plus-fill" style="color:var(--gold);"></i> Tambah Pemain
        </div>

        <img id="player-photo-preview" class="image-preview-circle" alt="Preview foto pemain">
        <div id="player-avatar-preview" class="player-avatar lg" style="background:#3b82f6;margin:0 auto 10px;">SS</div>

        <div class="form-ludo-group">
            <label class="form-ludo-label">Foto Profile</label>
            <button class="proof-upload-mini" type="button" onclick="document.getElementById('player-photo-input').click()" style="width:100%;">
                <i class="bi bi-camera-fill"></i> Pilih Foto
            </button>
            <input type="file" id="player-photo-input" accept="image/jpeg,image/png,image/webp" style="display:none;" onchange="previewPlayerPhoto(this)">
            <div style="font-size:11px;color:var(--text-muted);margin-top:6px;text-align:center;">JPG, JPEG, PNG, WebP - maks 5MB</div>
        </div>

        <div class="form-ludo-group">
            <label class="form-ludo-label">Nama Pemain</label>
            <input type="text" class="form-ludo-input" id="player-name-input" maxlength="50" placeholder="Nama pemain" oninput="syncAvatarPreview()">
        </div>

        <div class="form-ludo-group">
            <label class="form-ludo-label">Warna Avatar</label>
            <div style="display:flex;gap:10px;flex-wrap:wrap;padding:4px 0;">
                @foreach(['#ef4444','#3b82f6','#10b981','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#f97316','#14b8a6','#a855f7'] as $i => $color)
                    <button type="button" class="color-option player-color-option" data-color="{{ $color }}" onclick="selectPlayerColor('{{ $color }}', this)" style="width:34px;height:34px;border-radius:50%;background:{{ $color }};border:3px solid {{ $i === 1 ? '#fff' : 'transparent' }};"></button>
                @endforeach
            </div>
        </div>

        <input type="hidden" id="player-id-input">
        <input type="hidden" id="player-color-input" value="#3b82f6">

        <button class="btn-gold-ludo" id="player-save-btn" onclick="savePlayer()">
            <i class="bi bi-check-circle-fill"></i> Simpan Pemain
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
let selectedPlayerColor = '#3b82f6';
let selectedPlayerPhoto = null;

function initialsFromName(name) {
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length >= 2) return (parts[0][0] + parts[1][0]).toUpperCase();
    return (parts[0] || 'SS').slice(0, 2).toUpperCase();
}

function syncAvatarPreview() {
    document.getElementById('player-avatar-preview').textContent =
        initialsFromName(document.getElementById('player-name-input').value);
}

function selectPlayerColor(color, el) {
    selectedPlayerColor = color;
    document.getElementById('player-color-input').value = color;
    document.getElementById('player-avatar-preview').style.background = color;
    document.querySelectorAll('.player-color-option').forEach(btn => btn.style.borderColor = 'transparent');
    el.style.borderColor = '#fff';
}

function openPlayerModal(player = null) {
    selectedPlayerPhoto = null;
    document.getElementById('player-photo-input').value = '';
    document.getElementById('player-id-input').value = player?.id || '';
    document.getElementById('player-name-input').value = player?.nama_pemain || '';
    selectedPlayerColor = player?.avatar_color || '#3b82f6';
    document.getElementById('player-color-input').value = selectedPlayerColor;
    document.getElementById('player-avatar-preview').style.background = selectedPlayerColor;
    document.getElementById('player-modal-title').innerHTML = player
        ? '<i class="bi bi-pencil-fill" style="color:var(--gold);"></i> Edit Pemain'
        : '<i class="bi bi-person-plus-fill" style="color:var(--gold);"></i> Tambah Pemain';

    const photoPreview = document.getElementById('player-photo-preview');
    const avatarPreview = document.getElementById('player-avatar-preview');
    if (player?.foto_profile_url) {
        photoPreview.src = player.foto_profile_url;
        photoPreview.style.display = 'block';
        avatarPreview.style.display = 'none';
    } else {
        photoPreview.style.display = 'none';
        avatarPreview.style.display = 'flex';
    }
    syncAvatarPreview();
    document.querySelectorAll('.player-color-option').forEach(btn => {
        btn.style.borderColor = btn.dataset.color === selectedPlayerColor ? '#fff' : 'transparent';
    });
    openModal('modal-player-form');
}

function previewPlayerPhoto(input) {
    const file = input.files?.[0];
    const error = validateImageFile(file);
    if (error) {
        showToast(error, 'error');
        input.value = '';
        return;
    }
    selectedPlayerPhoto = file;
    const preview = document.getElementById('player-photo-preview');
    preview.src = URL.createObjectURL(file);
    preview.style.display = 'block';
    document.getElementById('player-avatar-preview').style.display = 'none';
}

async function savePlayer() {
    const id = document.getElementById('player-id-input').value;
    const name = document.getElementById('player-name-input').value.trim();
    if (name.length < 2) {
        showToast('Nama pemain minimal 2 karakter.', 'error');
        return;
    }

    const btn = document.getElementById('player-save-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Menyimpan...';

    const form = new FormData();
    form.append('nama_pemain', name);
    form.append('avatar_color', selectedPlayerColor);
    form.append('_token', csrfToken);
    if (id) form.append('_method', 'PUT');
    if (selectedPlayerPhoto) {
        form.append('foto_profile', await compressImage(selectedPlayerPhoto, 512, 0.68));
    }

    fetch(id ? `{{ url('/admin/pemain') }}/${id}` : '{{ route("admin.players.store") }}', {
        method: 'POST',
        body: form,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 650);
            return;
        }
        showToast(data.message || 'Gagal menyimpan pemain.', 'error');
    })
    .catch(() => showToast('Koneksi bermasalah.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle-fill"></i> Simpan Pemain';
    });
}

async function deletePlayer(id, name) {
    const ok = await showConfirmDialog({
        title: 'Hapus pemain?',
        message: `Pemain "${name}" beserta data absensi dan skor terkait akan dihapus.`,
        confirmText: 'Hapus',
        danger: true,
    });
    if (!ok) return;

    fetch(`{{ url('/admin/pemain') }}/${id}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': csrfToken },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            document.getElementById(`manage-player-${id}`)?.remove();
        } else {
            showToast(data.message || 'Gagal menghapus pemain.', 'error');
        }
    });
}
</script>
@endsection
