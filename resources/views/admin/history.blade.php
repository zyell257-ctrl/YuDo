@extends('layouts.app')

@section('title', 'History - Ludo Tracker')

@section('content')
<div class="page-title-bar">
    <div>
        <div class="page-title">History</div>
        <div class="page-subtitle">Riwayat pertandingan dan absensi</div>
    </div>
</div>

<div class="filter-chips" style="padding-bottom:12px;">
    <button class="filter-chip active" id="tab-matches" onclick="switchHistoryMode('matches')">
        <i class="bi bi-trophy-fill"></i> Pertandingan
    </button>
    <button class="filter-chip" id="tab-attendance" onclick="switchHistoryMode('attendance')">
        <i class="bi bi-calendar-check-fill"></i> Absensi
    </button>
</div>

{{-- Search --}}
<div class="search-bar" id="match-search-wrap">
    <i class="bi bi-search search-icon"></i>
    <input
        type="search"
        class="search-input"
        id="search-input"
        placeholder="Cari nama pemain..."
        oninput="debounceSearch()"
    >
</div>

{{-- Filter Chips --}}
<div class="filter-chips">
    <button class="filter-chip period-filter active" data-filter="hari_ini" onclick="setFilter('hari_ini', this)">Hari Ini</button>
    <button class="filter-chip period-filter" data-filter="minggu_ini" onclick="setFilter('minggu_ini', this)">Minggu Ini</button>
    <button class="filter-chip period-filter" data-filter="bulan_ini" onclick="setFilter('bulan_ini', this)">Bulan Ini</button>
    <button class="filter-chip period-filter" data-filter="semua" onclick="setFilter('semua', this)">Semua</button>
    <div class="date-filter-wrap">
        <button class="filter-chip period-filter" data-filter="custom_date" type="button">
            <i class="bi bi-calendar3"></i> Tanggal
        </button>
        <input type="date" id="date-filter" class="date-filter-native" onchange="setCustomDate(this.value)" aria-label="Pilih tanggal history">
    </div>
</div>

{{-- Filter tambahan: pilih pemain --}}
<div style="padding: 0 14px; margin-bottom:14px;">
    <select class="form-ludo-select" id="player-filter" onchange="loadCurrentHistory()" style="background:var(--bg-card); color:var(--text-primary); font-size:13px;">
        <option value="">Semua Pemain</option>
        @foreach($players as $p)
            <option value="{{ $p->id }}">{{ $p->nama_pemain }}</option>
        @endforeach
    </select>
</div>

{{-- Kontainer hasil history --}}
<div class="page-section" style="padding:0 14px;">
    <div id="history-container">
        {{-- Loading skeleton --}}
        <div id="history-loading">
            @for($i = 0; $i < 3; $i++)
            <div class="card-ludo" style="margin-bottom:12px;">
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:14px;">
                    <div class="skeleton" style="width:80px; height:14px;"></div>
                    <div class="skeleton" style="width:60px; height:14px; margin-left:auto;"></div>
                </div>
                @for($j=0;$j<3;$j++)
                <div style="display:flex; align-items:center; gap:10px; padding:8px 0; border-bottom:1px solid var(--border);">
                    <div class="skeleton" style="width:34px; height:34px; border-radius:50%;"></div>
                    <div style="flex:1;">
                        <div class="skeleton" style="width:100px; height:13px; margin-bottom:6px;"></div>
                        <div class="skeleton" style="width:160px; height:11px;"></div>
                    </div>
                </div>
                @endfor
            </div>
            @endfor
        </div>
        <div id="history-list" style="display:none;"></div>
        <div id="history-empty" style="display:none;">
            <div class="empty-state">
                <img src="/images/logo.png" alt="Salam Sendok" class="empty-state-logo" loading="lazy">
                <div class="empty-state-title">Belum ada pertandingan</div>
                <div class="empty-state-desc">Coba ubah filter atau pilih tanggal lain.</div>
            </div>
        </div>
    </div>

    {{-- Pagination --}}
    <div id="pagination" style="display:flex; gap:8px; justify-content:center; padding:16px 0; display:none;">
        <button id="btn-prev" class="btn-outline-ludo" style="padding:8px 16px; font-size:13px;" onclick="changePage(-1)">← Prev</button>
        <span id="page-info" style="display:flex; align-items:center; font-size:13px; color:var(--text-muted);"></span>
        <button id="btn-next" class="btn-outline-ludo" style="padding:8px 16px; font-size:13px;" onclick="changePage(1)">Next →</button>
    </div>
</div>
@endsection

@section('scripts')
<script>
let currentFilter  = 'hari_ini';
let currentPage    = 1;
let currentSearch  = '';
let customDate     = '';
let searchTimer    = null;
let historyMode    = 'matches';

function switchHistoryMode(mode) {
    historyMode = mode;
    currentPage = 1;
    document.getElementById('tab-matches').classList.toggle('active', mode === 'matches');
    document.getElementById('tab-attendance').classList.toggle('active', mode === 'attendance');
    document.getElementById('match-search-wrap').style.display = mode === 'matches' ? '' : 'none';
    document.getElementById('player-filter').closest('div').style.display = mode === 'matches' ? '' : 'none';
    loadCurrentHistory();
}

function loadCurrentHistory() {
    if (historyMode === 'attendance') {
        loadAttendanceHistory();
    } else {
        loadHistory();
    }
}

// ===== LOAD HISTORY =====
async function loadHistory() {
    document.getElementById('history-loading').style.display = 'block';
    document.getElementById('history-list').style.display    = 'none';
    document.getElementById('history-empty').style.display   = 'none';
    document.getElementById('pagination').style.display      = 'none';

    const playerId = document.getElementById('player-filter').value;

    const params = new URLSearchParams({
        filter:    currentFilter,
        page:      currentPage,
        search:    currentSearch,
        player_id: playerId,
    });

    if (currentFilter === 'custom_date' && customDate) {
        params.set('tanggal', customDate);
    }

    try {
        const res  = await fetch(`/api/history?${params}`);
        const data = await res.json();

        document.getElementById('history-loading').style.display = 'none';

        if (!data.success || data.data.length === 0) {
            document.getElementById('history-empty').style.display = 'block';
            return;
        }

        renderHistory(data.data);
        renderPagination(data.pagination);

    } catch (e) {
        document.getElementById('history-loading').style.display = 'none';
        showToast('Gagal memuat history.', 'error');
    }
}

// ===== RENDER HISTORY CARDS =====
function renderHistory(matches) {
    const container = document.getElementById('history-list');
    container.style.display = 'block';

    let lastDate = null;
    let html     = '';

    matches.forEach(match => {
        // Tampilkan tanggal header jika beda hari
        if (match.tanggal_raw !== lastDate) {
            lastDate = match.tanggal_raw;

            html += `
            <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin:14px 0 8px;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-calendar3"></i>
                ${match.tanggal_match}
                <span style="flex:1;height:1px;background:var(--border);margin-left:4px;"></span>
            </div>`;
        }

        // Render match card
        const statusBadge = match.status_match === 'berlangsung'
            ? `<span class="badge-berlangsung"><i class="bi bi-record-circle-fill"></i> Live</span>`
            : `<span class="badge-selesai"><i class="bi bi-check-circle-fill"></i> Selesai</span>`;

        let scoresHtml = '';
        match.scores.forEach((s, i) => {
            const rank = s.rank && s.rank < 99 ? s.rank : null;
            const badge = rank
                ? `<span class="rank-medal rank-${rank}">${s.badge}</span>`
                : `<span style="font-size:13px;color:var(--text-muted);font-weight:600;">${i+1}</span>`;

            const avatarHtml = s.foto_profile_url
                ? `<img class="player-avatar-img sm" src="${s.foto_profile_url}" alt="${s.nama_pemain}" loading="lazy" style="border-color:${s.avatar_color};">`
                : `<div class="player-avatar sm" style="background:${s.avatar_color};">${s.initials}</div>`;

            scoresHtml += `
            <div class="score-row">
                <div style="font-size:18px;width:28px;text-align:center;flex-shrink:0;">${badge}</div>
                ${avatarHtml}
                <div class="score-row-info">
                    <div class="score-row-name">${s.nama_pemain}${s.position_label ? `<span class="position-pill">${s.position_label}</span>` : ''}</div>
                    <div class="score-row-sub">
                        <i class="bi bi-footprint"></i> Diinjek: <strong style="color:var(--red);">${s.skor_keinjek}</strong>
                    </div>
                </div>
            </div>`;
        });

        const proofHtml = match.bukti_foto_url
            ? `<div class="match-proof-thumb"><img src="${match.bukti_foto_url}" alt="Bukti Pertandingan #${match.nomor_match}" loading="lazy"></div>`
            : '';

        html += `
        <div class="card-ludo" style="margin-bottom:10px;">
            <div class="match-card-header">
                <div>
                    <div class="match-number">Pertandingan #${match.nomor_match}</div>
                    <div class="match-time"><i class="bi bi-clock"></i> ${match.waktu_mulai||'--:--'} ${match.waktu_selesai ? '– '+match.waktu_selesai : ''}</div>
                </div>
                ${statusBadge}
            </div>
            ${proofHtml}
            ${scoresHtml}
        </div>`;
    });

    container.innerHTML = html;
}

// ===== PAGINATION =====
function renderPagination(pag) {
    const wrap = document.getElementById('pagination');
    if (pag.last_page <= 1) { wrap.style.display = 'none'; return; }

    wrap.style.display = 'flex';
    document.getElementById('page-info').textContent = `${pag.current_page} / ${pag.last_page}`;
    document.getElementById('btn-prev').disabled = pag.current_page <= 1;
    document.getElementById('btn-next').disabled = pag.current_page >= pag.last_page;
}

function changePage(delta) {
    currentPage += delta;
    if (currentPage < 1) currentPage = 1;
    loadCurrentHistory();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ===== FILTER CHIPS =====
function setFilter(filter, btn) {
    currentFilter = filter;
    currentPage   = 1;
    document.querySelectorAll('.period-filter').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    loadCurrentHistory();
}

function setCustomDate(val) {
    customDate    = val;
    currentFilter = 'custom_date';
    currentPage   = 1;
    document.querySelectorAll('.period-filter').forEach(c => c.classList.remove('active'));
    document.querySelector('[data-filter="custom_date"]')?.classList.add('active');
    loadCurrentHistory();
}

// ===== SEARCH DEBOUNCE =====
function debounceSearch() {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        currentSearch = document.getElementById('search-input').value.trim();
        currentPage   = 1;
        loadCurrentHistory();
    }, 450);
}

async function loadAttendanceHistory() {
    document.getElementById('history-loading').style.display = 'block';
    document.getElementById('history-list').style.display    = 'none';
    document.getElementById('history-empty').style.display   = 'none';
    document.getElementById('pagination').style.display      = 'none';

    const params = new URLSearchParams({ filter: currentFilter, page: currentPage });
    if (currentFilter === 'custom_date' && customDate) {
        params.set('tanggal', customDate);
    }

    try {
        const res = await fetch(`/api/history/attendance?${params}`);
        const data = await res.json();
        document.getElementById('history-loading').style.display = 'none';

        if (!data.success || data.data.length === 0) {
            document.getElementById('history-empty').style.display = 'block';
            return;
        }

        renderAttendanceHistory(data.data);
        renderPagination(data.pagination);
    } catch (e) {
        document.getElementById('history-loading').style.display = 'none';
        showToast('Gagal memuat history absensi.', 'error');
    }
}

function renderAttendanceHistory(days) {
    const container = document.getElementById('history-list');
    container.style.display = 'block';

    container.innerHTML = days.map(day => {
        const renderNames = (items, type) => {
            if (!items.length) return '<div style="font-size:12px;color:var(--text-muted);padding:8px 0;">Tidak ada pemain.</div>';
            return items.map(p => {
                const avatar = p.foto_profile_url
                    ? `<img class="player-avatar-img sm" src="${p.foto_profile_url}" alt="${p.nama_pemain}" loading="lazy" style="border-color:${p.avatar_color};">`
                    : `<div class="player-avatar sm" style="background:${p.avatar_color};">${p.initials}</div>`;
                return `<div style="display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid var(--border);">
                    ${avatar}
                    <span style="font-size:13px;font-weight:600;color:var(--text-primary);">${p.nama_pemain}</span>
                </div>`;
            }).join('');
        };

        return `<div class="card-ludo" style="margin-bottom:12px;">
            <div class="match-card-header">
                <div>
                    <div class="match-number">${day.nama_hari}</div>
                    <div class="match-time"><i class="bi bi-calendar3"></i> ${day.tanggal}</div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                <span class="badge-hadir" style="text-align:center;"><i class="bi bi-check-circle-fill"></i> Hadir ${day.hadir.length}</span>
                <span class="badge-tidak-hadir" style="text-align:center;"><i class="bi bi-x-circle-fill"></i> Tidak ${day.tidak_hadir.length}</span>
            </div>
            <div style="display:grid;grid-template-columns:1fr;gap:12px;">
                <div>
                    <div style="font-size:11px;color:var(--green);font-weight:800;text-transform:uppercase;margin-bottom:4px;">Pemain Hadir</div>
                    ${renderNames(day.hadir, 'hadir')}
                </div>
                <div>
                    <div style="font-size:11px;color:var(--red);font-weight:800;text-transform:uppercase;margin-bottom:4px;">Tidak Hadir</div>
                    ${renderNames(day.tidak_hadir, 'tidak')}
                </div>
            </div>
        </div>`;
    }).join('');
}

// ===== INIT =====
loadCurrentHistory();
</script>
@endsection
