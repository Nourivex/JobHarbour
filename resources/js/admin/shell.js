/**
 * Kerangka Admin Portal: penjaga akses khusus admin, sidebar, profil, dan angka antrean di navigasi.
 * Sidebar dan profil memakai fungsi yang sama dengan portal recruiter; isi menunya berbeda (layouts/admin.blade.php).
 */
import JH, { session, toast, admin } from '../api.js';
import { data } from '../recruiter/data.js';
import { initSidebar, renderUser } from '../recruiter/shell.js';
import { initMenus } from '../recruiter/components.js';
import { hydrateIcons } from '../recruiter/ui.js';

/** Angka "menunggu" di sidebar. queue = data.queue dari GET /api/admin/overview. */
export function renderNavCounts(queue) {
    if (!queue) return;
    const v = document.getElementById('adm-nav-verifications');
    if (v && ('verifications_pending' in queue)) {
        v.textContent = queue.verifications_pending || 0;
        v.classList.toggle('hidden', !queue.verifications_pending);
    }
    const r = document.getElementById('adm-nav-reports');
    const reportsOpen = (queue.reports_open || 0) + (queue.appeals_open || 0);
    if (r && ('reports_open' in queue)) {
        r.textContent = reportsOpen;
        r.classList.toggle('hidden', !reportsOpen);
    }
    if ('sources_degraded' in queue) document.getElementById('adm-nav-sources')?.classList.toggle('hidden', !queue.sources_degraded);
}

/**
 * Jalankan kerangka. Mengembalikan { user } atau null bila pengguna dialihkan.
 * Pemeriksaan sesungguhnya tetap di backend (jwt.auth:admin); ini hanya mencegah halaman kosong.
 */
export async function bootAdmin({ page, loadNav = true } = {}) {
    document.body.dataset.page = page;
    hydrateIcons();
    initSidebar();
    initMenus(); // menu akun di topbar

    const cached = session.user();
    if (!session.isLoggedIn() || !cached) {
        window.location.replace('/login?next=' + encodeURIComponent(window.location.pathname + window.location.search));
        return null;
    }
    if (cached.role !== 'admin') {
        toast('Halaman ini khusus untuk admin JobHarbour.', 'warning', 3000);
        window.location.replace(JH.homeFor(cached));
        return null;
    }
    renderUser(cached);

    let user = cached;
    try {
        user = await data.me();
        renderUser(user);
    } catch (err) {
        console.error('Gagal memuat akun:', err);
    }
    if (user.role !== 'admin') {
        window.location.replace(JH.homeFor(user));
        return null;
    }

    // Angka antrean di sidebar dimuat di latar; halaman Ringkasan mengisinya sendiri
    if (loadNav) admin.overview().then((res) => renderNavCounts(res.data?.queue)).catch(() => {});
    hydrateIcons();
    return { user };
}
