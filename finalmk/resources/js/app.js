import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

window.Alpine = Alpine;
window.Chart = Chart;
window.L = L;

// Persist dark mode across pages; default respects OS preference.
window.applyTheme = function (mode) {
    const dark = mode === 'dark';
    document.documentElement.classList.toggle('dark', dark);
    try { localStorage.setItem('theme', dark ? 'dark' : 'light'); } catch (e) {}
    document.querySelectorAll('[data-theme-icon-sun]').forEach(el => el.classList.toggle('hidden', dark));
    document.querySelectorAll('[data-theme-icon-moon]').forEach(el => el.classList.toggle('hidden', !dark));
};
(function initTheme() {
    let mode = 'light';
    try { mode = localStorage.getItem('theme'); } catch (e) {}
    if (!mode) mode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    window.applyTheme(mode);
})();
window.toggleTheme = function () {
    const dark = document.documentElement.classList.contains('dark');
    window.applyTheme(dark ? 'light' : 'dark');
};

// Toast helper used by flashes and AJAX actions.
window.toast = function (message, type = 'success') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 items-end';
        document.body.appendChild(container);
    }
    const el = document.createElement('div');
    const palette = type === 'error'
        ? 'bg-red-600 text-white'
        : type === 'info'
            ? 'bg-stone-800 text-white dark:bg-stone-200 dark:text-stone-900'
            : 'bg-leaf-600 text-white';
    el.className = `toast-enter ${palette} px-4 py-3 rounded-xl shadow-lift text-sm font-medium max-w-sm flex items-center gap-2`;
    el.textContent = message;
    container.appendChild(el);
    setTimeout(() => {
        el.style.transition = 'opacity .3s, transform .3s';
        el.style.opacity = '0';
        el.style.transform = 'translateY(6px)';
        setTimeout(() => el.remove(), 320);
    }, 3600);
};
// Surface session flashes queued by the server.
window.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('meta[name="flash-success"]').forEach(m => window.toast(m.content, 'success'));
    document.querySelectorAll('meta[name="flash-error"]').forEach(m => window.toast(m.content, 'error'));
    document.querySelectorAll('meta[name="flash-info"]').forEach(m => window.toast(m.content, 'info'));
});

// Central Leaflet helper: renders markers, supports a draggable pin & click-to-place.
window.initLeafletMap = function (el, options = {}) {
    if (!el) return null;
    const center = options.center || [40.7128, -74.0060];
    const zoom = options.zoom ?? 13;
    const map = L.map(el, { scrollWheelZoom: options.scrollWheelZoom ?? true }).setView(center, zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);

    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));

    const popupHtml = (m) => {
        const name = esc(m.name);
        const sub = esc(m.sub || m.days || m.address || '');
        const link = m.url
            ? `<a href="${esc(m.url)}" class="map-popup-link">View details &rarr;</a>`
            : '';
        return '<div style="min-width:140px;line-height:1.35">'
            + (name ? `<div style="font-weight:700">${name}</div>` : '')
            + (sub ? `<div style="margin-top:2px;font-size:12px;opacity:.75">${sub}</div>` : '')
            + (link ? `<div style="font-size:12px">${link}</div>` : '')
            + '</div>';
    };

    const markers = [];
    const byId = {};
    (options.markers || []).forEach((m) => {
        if (m.lat == null || m.lng == null) return;
        const marker = L.marker([m.lat, m.lng]).addTo(map);
        const html = m.popup || ((m.name || m.url) ? popupHtml(m) : null);
        if (html) marker.bindPopup(html);
        if (m.id != null) byId[m.id] = marker;
        markers.push(marker);
    });

    if (markers.length > 1) {
        try {
            const group = L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.15));
        } catch (e) {}
    }

    let pin = null;
    const setPin = (latlng, silent = false) => {
        if (pin) { pin.setLatLng(latlng); }
        else {
            pin = L.marker(latlng, { draggable: true }).addTo(map);
            pin.on('dragend', () => {
                const p = pin.getLatLng();
                el.dispatchEvent(new CustomEvent('pin-moved', { detail: { lat: p.lat, lng: p.lng }, bubbles: true }));
            });
        }
        map.setView(latlng, Math.max(map.getZoom(), 15));
        if (!silent) {
            el.dispatchEvent(new CustomEvent('pin-moved', { detail: { lat: latlng.lat, lng: latlng.lng }, bubbles: true }));
        }
    };
    if (options.draggablePin) {
        if (options.draggablePin.lat != null && options.draggablePin.lng != null) {
            setPin(L.latLng(options.draggablePin.lat, options.draggablePin.lng));
        }
        map.on('click', (e) => setPin(e.latlng));
    }

    setTimeout(() => map.invalidateSize(), 250);
    const handle = {
        map,
        markers,
        byId,
        setPin: (latlng) => setPin(latlng, true),
        getPin: () => pin,
    };
    el._leafletMap = handle;
    return handle;
};

// Toggle a favorite (farmer / product / market) via AJAX and update the heart.
window.toggleFavorite = async function (event, type, id, btn) {
    if (event) event.preventDefault();
    try {
        const res = await fetch('/favorites/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ type, id }),
        });
        if (res.status === 401) { window.location = '/login'; return; }
        const data = await res.json();
        const favorited = data.favorited;
        document.querySelectorAll(`[data-fav-type="${type}"][data-fav-id="${id}"]`).forEach((b) => {
            b.dataset.favorited = favorited ? '1' : '0';
            const svg = b.querySelector('svg');
            if (svg) {
                svg.classList.toggle('text-red-500', favorited);
                svg.classList.toggle('fill-current', favorited);
                svg.classList.toggle('text-stone-500', !favorited);
                svg.setAttribute('fill', favorited ? 'currentColor' : 'none');
            }
        });
        if (window.toast) window.toast(favorited ? 'Added to favorites' : 'Removed from favorites', 'success');
    } catch (e) {
        if (window.toast) window.toast('Could not update favorite', 'error');
    }
};

// Live password checklist shared by register / google-complete / reset / profile forms.
// Mirrors Illuminate\Validation\Rules\Password defaults configured in AppServiceProvider.
Alpine.data('passwordRules', () => ({
    password: '',
    confirmation: '',
    rules: [
        { key: 'length', label: '8–16 characters' },
        { key: 'upper', label: 'One uppercase letter' },
        { key: 'lower', label: 'One lowercase letter' },
        { key: 'number', label: 'One number' },
        { key: 'special', label: 'One special character' },
    ],
    init() {
        this.password = this.$root.querySelector('[name="password"]')?.value || '';
        this.confirmation = this.$root.querySelector('[name="password_confirmation"]')?.value || '';
    },
    get checks() {
        const p = this.password;
        return {
            length: p.length >= 8 && p.length <= 16,
            upper: /\p{Lu}/u.test(p),
            lower: /\p{Ll}/u.test(p),
            number: /\p{N}/u.test(p),
            special: /[\p{Z}\p{S}\p{P}]/u.test(p),
        };
    },
    get passwordValid() {
        return Object.values(this.checks).every(Boolean);
    },
    get confirmationMatches() {
        return this.password !== '' && this.password === this.confirmation;
    },
    get ready() {
        return this.passwordValid && this.confirmationMatches;
    },
}));

// Multi-image picker for product / market forms: previews, per-image removal
// and the 1–6 limit. Selected files are synced into a hidden file input so the
// regular form POST carries them.
Alpine.data('imagePicker', (options = {}) => ({
    max: options.max ?? 6,
    existing: options.existing ?? [],
    removed: [],
    files: [],
    error: '',
    _id: 0,

    init() {
        const form = this.$root.closest('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                if (this.count === 0) {
                    e.preventDefault();
                    this.error = 'Add at least 1 image before saving.';
                }
            });
        }
    },

    get existingActive() {
        return this.existing.filter((img) => !this.removed.includes(img.path));
    },
    get count() {
        return this.existingActive.length + this.files.length;
    },
    get thumbs() {
        return [
            ...this.existingActive.map((img) => ({ key: 'x:' + img.path, url: img.url, kind: 'existing', path: img.path })),
            ...this.files.map((f) => ({ key: 'n:' + f.id, url: f.url, kind: 'new' })),
        ];
    },

    onChange(event) {
        this.error = '';
        const picked = Array.from(event.target.files || []);
        const allowed = ['image/jpeg', 'image/png', 'image/webp'];
        let overflow = false;

        for (const file of picked) {
            if (this.count >= this.max) { overflow = true; break; }
            if (!allowed.includes(file.type)) {
                this.error = `"${file.name}" is not a supported image. Use JPG, PNG or WebP.`;
                continue;
            }
            if (file.size > 5 * 1024 * 1024) {
                this.error = `"${file.name}" is too large. Each image must be under 5 MB.`;
                continue;
            }
            this.files.push({ id: this._id++, file, url: URL.createObjectURL(file) });
        }
        if (overflow) this.error = `You can use up to ${this.max} images.`;
        // Reset the picker first, THEN copy the chosen files into it.
        // (Resetting after sync() wiped the files, so nothing was uploaded.)
        event.target.value = '';
        this.sync();
    },

    remove(thumb) {
        this.error = '';
        if (thumb.kind === 'existing') this.removed.push(thumb.path);
        else this.files = this.files.filter((f) => 'n:' + f.id !== thumb.key);
        this.sync();
    },

    sync() {
        const dt = new DataTransfer();
        this.files.forEach((f) => dt.items.add(f.file));
        const input = this.$refs.input;
        if (input) input.files = dt.files;
    },
}));

Alpine.start();
