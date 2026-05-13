/**
 * Ludo Tracker - Main JavaScript
 * Fungsi global yang digunakan di semua halaman
 */

// ============================================================
// TOAST NOTIFICATION
// ============================================================
/**
 * Tampilkan toast notification
 * @param {string} message - Pesan yang ditampilkan
 * @param {string} type - 'success' | 'error' | 'info'
 * @param {number} duration - Durasi dalam ms (default 3000)
 */
function showToast(message, type = 'info', duration = 3000) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const icons = {
        success: 'bi-check-circle-fill',
        error:   'bi-exclamation-circle-fill',
        info:    'bi-info-circle-fill',
    };

    const toast = document.createElement('div');
    toast.className = `toast-ludo toast-${type}`;
    toast.innerHTML = `<i class="bi ${icons[type] || icons.info}"></i><span>${message}</span>`;
    container.appendChild(toast);

    // Auto remove
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-8px)';
        toast.style.transition = 'opacity 0.2s, transform 0.2s';
        setTimeout(() => toast.remove(), 250);
    }, duration);
}

// ============================================================
// MODERN CONFIRM DIALOG
// ============================================================
function showConfirmDialog({
    title = 'Konfirmasi',
    message = 'Lanjutkan aksi ini?',
    confirmText = 'Ya',
    cancelText = 'Batal',
    danger = false,
} = {}) {
    return new Promise(resolve => {
        const existing = document.getElementById('confirm-dialog-ludo');
        if (existing) existing.remove();

        const wrap = document.createElement('div');
        wrap.id = 'confirm-dialog-ludo';
        wrap.className = 'confirm-dialog-ludo';
        wrap.innerHTML = `
            <div class="confirm-dialog-backdrop"></div>
            <div class="confirm-dialog-card">
                <div class="confirm-dialog-icon ${danger ? 'danger' : ''}">
                    <i class="bi ${danger ? 'bi-exclamation-triangle-fill' : 'bi-question-circle-fill'}"></i>
                </div>
                <div class="confirm-dialog-title">${title}</div>
                <div class="confirm-dialog-message">${message}</div>
                <div class="confirm-dialog-actions">
                    <button class="btn-outline-ludo" data-action="cancel">${cancelText}</button>
                    <button class="${danger ? 'btn-danger-ludo' : 'btn-gold-ludo'}" data-action="confirm">${confirmText}</button>
                </div>
            </div>
        `;
        document.body.appendChild(wrap);
        document.body.style.overflow = 'hidden';

        const close = value => {
            wrap.remove();
            document.body.style.overflow = '';
            resolve(value);
        };

        wrap.querySelector('[data-action="cancel"]').addEventListener('click', () => close(false));
        wrap.querySelector('[data-action="confirm"]').addEventListener('click', () => close(true));
        wrap.querySelector('.confirm-dialog-backdrop').addEventListener('click', () => close(false));
    });
}

// ============================================================
// IMAGE HELPERS
// ============================================================
function validateImageFile(file) {
    const allowed = ['image/jpeg', 'image/png', 'image/webp'];
    if (!file) return 'Pilih gambar terlebih dahulu.';
    if (!allowed.includes(file.type)) return 'Format gambar harus JPG, JPEG, PNG, atau WebP.';
    if (file.size > 5 * 1024 * 1024) return 'Ukuran gambar maksimal 5MB.';
    return null;
}

function compressImage(file, maxWidth = 1400, quality = 0.78) {
    return new Promise(resolve => {
        if (!file || !file.type.startsWith('image/')) {
            resolve(file);
            return;
        }

        const image = new Image();
        const objectUrl = URL.createObjectURL(file);
        image.onload = () => {
            const scale = Math.min(1, maxWidth / image.width);
            const canvas = document.createElement('canvas');
            canvas.width = Math.round(image.width * scale);
            canvas.height = Math.round(image.height * scale);
            canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
            canvas.toBlob(blob => {
                URL.revokeObjectURL(objectUrl);
                if (!blob) {
                    resolve(file);
                    return;
                }
                const ext = file.type === 'image/png' ? 'png' : (file.type === 'image/webp' ? 'webp' : 'jpg');
                resolve(new File([blob], `compressed-${Date.now()}.${ext}`, { type: file.type }));
            }, file.type, quality);
        };
        image.onerror = () => {
            URL.revokeObjectURL(objectUrl);
            resolve(file);
        };
        image.src = objectUrl;
    });
}

// ============================================================
// MODAL HELPERS (global)
// ============================================================
function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('show');
    document.body.style.overflow = 'hidden';
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.remove('show');
    document.body.style.overflow = '';
}

// Tutup modal dengan tombol Escape
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-ludo.show').forEach(m => {
            m.classList.remove('show');
        });
        document.body.style.overflow = '';
    }
});

// ============================================================
// ACTIVE NAV HIGHLIGHT (sudah di blade, ini sebagai fallback)
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const path  = window.location.pathname;
    const items = document.querySelectorAll('.bottom-nav .nav-item[href]');

    items.forEach(item => {
        const href = item.getAttribute('href');
        if (href && path.startsWith(href) && href !== '/') {
            item.classList.add('active');
        }
    });

    // Tampilkan flash message dari session jika ada
    // (ditangani oleh blade, tapi bisa juga dari URL param)
    const urlParams = new URLSearchParams(window.location.search);
    const flashMsg  = urlParams.get('msg');
    const flashType = urlParams.get('type') || 'info';
    if (flashMsg) {
        showToast(decodeURIComponent(flashMsg), flashType);
    }
});

// ============================================================
// SKELETON LOADING - replace dengan konten
// ============================================================
function hideSkeleton(containerId) {
    const el = document.getElementById(containerId);
    if (el) el.style.display = 'none';
}

// ============================================================
// LAZY IMAGE LOAD
// ============================================================
if ('IntersectionObserver' in window) {
    const imgObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    imgObserver.unobserve(img);
                }
            }
        });
    }, { rootMargin: '50px' });

    document.querySelectorAll('img[data-src]').forEach(img => imgObserver.observe(img));
}

// ============================================================
// PREVENT DOUBLE TAP ZOOM (mobile)
// ============================================================
let lastTouchEnd = 0;
document.addEventListener('touchend', e => {
    const now = Date.now();
    if (now - lastTouchEnd <= 300) {
        e.preventDefault();
    }
    lastTouchEnd = now;
}, { passive: false });

// ============================================================
// SMOOTH SCROLL BEHAVIOR
// ============================================================
function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}
