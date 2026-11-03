/**
 * Banner pengumuman situs (GET /api/announcements/active) di halaman publik dan portal.
 * Disaring berdasarkan peran pengguna (audience), bisa ditutup per pengumuman (diingat di browser).
 */
import { announcements, session, util } from './api.js';

const KEY = 'jh_ann_dismissed';
const ICON = {
    info: '<path d="M12 16v-4M12 8h.01"/><circle cx="12" cy="12" r="9"/>',
    warning: '<path d="m21.7 18-8-14a2 2 0 0 0-3.5 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3z"/><path d="M12 9v4M12 17h.01"/>',
};

function dismissed() {
    try { return JSON.parse(localStorage.getItem(KEY) || '[]'); } catch (e) { return []; }
}

export async function initAnnouncements() {
    const host = document.getElementById('jh-announcement');
    if (!host) return;
    let items = [];
    try {
        items = (await announcements.active()).data || [];
    } catch (e) {
        return; // banner bersifat tambahan: gagal dimuat berarti tidak ditampilkan
    }
    const role = session.isLoggedIn() ? session.user()?.role : null;
    const hidden = dismissed();
    const show = items.filter((a) => !hidden.includes(a.id) && (a.audience === 'all' || a.audience === role || role === 'admin'));
    if (!show.length) return;

    host.innerHTML = show.map((a) => `
        <div class="jh-ann jh-ann-${a.level}" role="status" data-ann="${a.id}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICON[a.level] || ICON.info}</svg>
            <p>${util.escapeHtml(a.message)}</p>
            <button type="button" data-ann-close aria-label="Tutup pengumuman">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
            </button>
        </div>`).join('');

    host.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-ann-close]');
        if (!btn) return;
        const bar = btn.closest('[data-ann]');
        const id = Number(bar.dataset.ann);
        try { localStorage.setItem(KEY, JSON.stringify([...new Set([...dismissed(), id])].slice(-20))); } catch (err) { /* abaikan */ }
        bar.classList.add('is-leaving');
        setTimeout(() => bar.remove(), 180);
    });
}
