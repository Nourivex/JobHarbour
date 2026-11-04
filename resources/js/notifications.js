/**
 * Lonceng pemberitahuan untuk pengguna yang sudah masuk (halaman publik dan Portal Recruiter).
 * Dipasang otomatis pada elemen [data-notif="nav" | "ws"]. Daftar dimuat saat halaman dibuka,
 * diperbarui tiap 60 detik selama tab terlihat, dan saat panel dibuka.
 * Animasi hanya opacity/transform dan mengikuti setelan kurangi gerakan.
 */
import { notifications, session, util } from './api.js';

const POLL_MS = 60000;
const BELL = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>';

function timeAgo(iso) {
    const diff = (Date.now() - new Date(iso).getTime()) / 1000;
    if (diff < 60) return 'baru saja';
    if (diff < 3600) return `${Math.floor(diff / 60)} menit lalu`;
    if (diff < 86400) return `${Math.floor(diff / 3600)} jam lalu`;
    if (diff < 86400 * 7) return `${Math.floor(diff / 86400)} hari lalu`;
    return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}

function mount(host) {
    const esc = util.escapeHtml;
    const id = `jh-notif-panel-${Math.random().toString(36).slice(2, 7)}`;
    host.classList.add('jh-notif');
    host.innerHTML = `
        <button type="button" class="jh-notif-btn" aria-label="Pemberitahuan" aria-expanded="false" aria-controls="${id}">
            ${BELL}<span class="jh-notif-badge" hidden></span>
        </button>
        <div class="jh-notif-panel" id="${id}" role="region" aria-label="Pemberitahuan" hidden>
            <div class="jh-notif-head">
                <p class="jh-notif-title">Pemberitahuan</p>
                <button type="button" class="jh-notif-readall" hidden>Tandai semua dibaca</button>
            </div>
            <ul class="jh-notif-list" aria-live="polite"><li class="jh-notif-empty">Memuat...</li></ul>
        </div>`;

    const btn = host.querySelector('.jh-notif-btn');
    const badge = host.querySelector('.jh-notif-badge');
    const panel = host.querySelector('.jh-notif-panel');
    const list = host.querySelector('.jh-notif-list');
    const readAll = host.querySelector('.jh-notif-readall');
    let items = [];
    let unread = 0;

    const setUnread = (n) => {
        const grew = n > unread;
        unread = n;
        badge.hidden = !n;
        badge.textContent = n > 9 ? '9+' : String(n);
        btn.setAttribute('aria-label', n ? `Pemberitahuan, ${n} belum dibaca` : 'Pemberitahuan');
        readAll.hidden = !n;
        if (grew) {
            badge.classList.remove('is-pop');
            void badge.offsetWidth;
            badge.classList.add('is-pop');
        }
    };

    const render = () => {
        list.innerHTML = items.length
            ? items.map((n) => `
                <li>
                    <button type="button" class="jh-notif-item ${n.read ? '' : 'is-unread'}" data-id="${n.id}" data-link="${esc(n.link || '')}">
                        <span class="jh-notif-dot" aria-hidden="true"></span>
                        <span class="jh-notif-text">
                            <span class="jh-notif-item-title">${esc(n.title)}</span>
                            ${n.body ? `<span class="jh-notif-item-body">${esc(n.body)}</span>` : ''}
                            <span class="jh-notif-time">${timeAgo(n.created_at)}</span>
                        </span>
                    </button>
                </li>`).join('')
            : '<li class="jh-notif-empty">Belum ada pemberitahuan. Kabar soal lamaran dan keputusan admin akan muncul di sini.</li>';
    };

    const load = async () => {
        try {
            const res = await notifications.list();
            items = res.data || [];
            setUnread(res.meta?.unread_count || 0);
            render();
        } catch (e) {
            if (!panel.hidden && !items.length) list.innerHTML = '<li class="jh-notif-empty">Pemberitahuan gagal dimuat. Coba lagi nanti.</li>';
        }
    };

    const open = (state) => {
        panel.hidden = !state;
        btn.setAttribute('aria-expanded', String(state));
        host.classList.toggle('is-open', state);
        if (state) load();
    };

    btn.addEventListener('click', (e) => {
        e.stopPropagation();
        open(panel.hidden);
    });
    document.addEventListener('click', (e) => { if (!panel.hidden && !host.contains(e.target)) open(false); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !panel.hidden) { open(false); btn.focus(); }
    });

    list.addEventListener('click', async (e) => {
        const item = e.target.closest('.jh-notif-item');
        if (!item) return;
        const target = items.find((n) => String(n.id) === item.dataset.id);
        if (target && !target.read) {
            target.read = true;
            setUnread(Math.max(0, unread - 1));
            item.classList.remove('is-unread');
            notifications.read(target.id).catch(() => {});
        }
        if (item.dataset.link) window.location.href = item.dataset.link;
    });

    readAll.addEventListener('click', async () => {
        items.forEach((n) => { n.read = true; });
        setUnread(0);
        render();
        notifications.readAll().catch(() => load());
    });

    load();
    setInterval(() => { if (document.visibilityState === 'visible') load(); }, POLL_MS);
    document.addEventListener('visibilitychange', () => { if (document.visibilityState === 'visible') load(); });
}

export function initNotifications(root = document) {
    if (!session.isLoggedIn()) return;
    root.querySelectorAll('[data-notif]').forEach((host) => {
        if (!host.dataset.notifMounted) {
            host.dataset.notifMounted = '1';
            mount(host);
        }
    });
}
