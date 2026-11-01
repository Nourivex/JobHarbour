/**
 * Kerangka Portal Recruiter: penjaga akses, sidebar, kartu perusahaan, dan banner status verifikasi.
 * Setiap halaman memanggil bootShell() lalu menerima pengguna dan profil perusahaan.
 */
import JH, { session, toast } from '../api.js';
import { data } from './data.js';
import { hydrateIcons, icon, initials, VERIFY, esc, companyLogo } from './ui.js';

function initSidebar() {
    const sidebar = document.getElementById('ws-sidebar');
    const openBtn = document.querySelector('[data-sidebar-open]');
    const setOpen = (open) => {
        // Animasi hanya saat dibuka/ditutup pengguna, bukan saat ukuran layar melewati breakpoint
        document.body.classList.add('ws-nav-animate');
        document.body.classList.toggle('ws-nav-open', open);
        openBtn?.setAttribute('aria-expanded', String(open));
        if (open) sidebar.querySelector('a, button')?.focus();
        else if (document.activeElement && sidebar.contains(document.activeElement)) openBtn?.focus();
    };
    openBtn?.addEventListener('click', () => setOpen(true));
    document.querySelectorAll('[data-sidebar-close]').forEach((el) => el.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.body.classList.contains('ws-nav-open')) setOpen(false);
    });
}

function renderUser(user) {
    document.getElementById('ws-user-avatar').textContent = initials(user.name);
    document.getElementById('ws-user-name').textContent = user.name || '-';
    document.getElementById('ws-user-role').textContent = user.role === 'admin' ? 'Administrator' : 'Recruiter';
    if (user.role === 'admin') {
        document.getElementById('ws-admin-nav')?.classList.remove('hidden');
        document.getElementById('ws-company-nav')?.classList.add('hidden');
        const card = document.getElementById('ws-company-card');
        card?.setAttribute('href', '/admin/verifications');
    }
}

function renderCompany(user, company) {
    const nameEl = document.getElementById('ws-company-name');
    const statusEl = document.getElementById('ws-company-status');
    const logoEl = document.getElementById('ws-company-logo');
    if (user.role === 'admin' && !company) {
        nameEl.textContent = 'Mode admin';
        statusEl.textContent = 'Melihat semua perusahaan';
        logoEl.innerHTML = icon('shield-check', 'w-4 h-4');
        return;
    }
    if (!company) {
        nameEl.textContent = 'Profil perusahaan belum dibuat';
        statusEl.innerHTML = `<span class="ws-status-dot" style="--c:#F59E0B"></span>Lengkapi sekarang`;
        logoEl.innerHTML = icon('building-2', 'w-4 h-4');
        return;
    }
    nameEl.textContent = company.name;
    const v = VERIFY[company.verification_status] || VERIFY.unverified;
    const color = { success: '#4ADE80', info: '#7CC4F5', danger: '#F87171', neutral: '#CBD5E1' }[v.tone];
    statusEl.innerHTML = `<span class="ws-status-dot" style="--c:${color}"></span>${v.label}`;
    logoEl.outerHTML = companyLogo(company, 'ws-company-logo');
}

/** Banner di atas konten bila perusahaan belum bisa mempublikasikan lowongan. */
function renderBanner(user, company) {
    const banner = document.getElementById('ws-banner');
    const dot = document.getElementById('ws-nav-verify');
    if (user.role === 'admin') return;
    const status = company?.verification_status || 'none';
    if (status === 'verified') return;

    dot?.classList.remove('hidden');
    if (document.body.dataset.page === 'verification') return;

    const copy = {
        none: ['Lengkapi profil perusahaan untuk mulai merekrut.', 'Sebelum diverifikasi, lowongan hanya bisa disimpan sebagai draft.', '/recruiter/company', 'Lengkapi profil', 'info'],
        unverified: ['Perusahaanmu belum terverifikasi.', 'Lowongan bisa disimpan sebagai draft, tetapi baru bisa dipublikasikan setelah verifikasi disetujui.', '/recruiter/verification', 'Ajukan verifikasi', 'info'],
        pending: ['Pengajuan verifikasi sedang diperiksa admin.', 'Siapkan lowonganmu sebagai draft. Publikasikan setelah disetujui.', '/recruiter/verification', 'Lihat status', 'info'],
        rejected: ['Pengajuan verifikasi ditolak.', esc(company?.verification_note || 'Lihat catatan admin lalu ajukan ulang.'), '/recruiter/verification', 'Perbaiki dan ajukan ulang', 'danger'],
        suspended: ['Perusahaan sedang ditangguhkan.', esc(company?.verification_note || 'Hubungi admin JobHarbour untuk informasi lebih lanjut.'), '/recruiter/verification', 'Lihat detail', 'danger'],
    }[status];

    banner.className = `ws-banner ws-banner-${copy[4]}`;
    banner.innerHTML = `
        <div class="ws-banner-inner">
            ${icon(copy[4] === 'danger' ? 'shield-alert' : 'info', 'w-5 h-5')}
            <p class="flex-1 min-w-0"><strong>${copy[0]}</strong> <span>${copy[1]}</span></p>
            <a href="${copy[2]}" class="ws-btn ws-btn-sm ws-btn-secondary whitespace-nowrap">${copy[3]}${icon('arrow-right', 'w-4 h-4')}</a>
        </div>`;
}

/**
 * Jalankan kerangka. Mengembalikan { user, company } atau null bila pengguna dialihkan.
 * @param {{ page: string, roles?: string[] }} options
 */
export async function bootShell({ page, roles = ['recruiter', 'admin'] } = {}) {
    document.body.dataset.page = page;
    hydrateIcons();
    initSidebar();

    const cached = session.user();
    if (!session.isLoggedIn() || !cached) {
        window.location.replace('/login?next=' + encodeURIComponent(window.location.pathname + window.location.search));
        return null;
    }
    if (!roles.includes(cached.role)) {
        toast('Halaman ini khusus untuk akun recruiter.', 'warning', 3000);
        window.location.replace(JH.homeFor(cached));
        return null;
    }
    renderUser(cached);

    let user = cached;
    let company = null;
    try {
        user = await data.me();
        renderUser(user);
        if (user.role === 'recruiter') company = await data.company();
    } catch (err) {
        console.error('Gagal memuat akun:', err);
    }
    renderCompany(user, company);
    renderBanner(user, company);
    hydrateIcons();
    return { user, company, isAdmin: user.role === 'admin' };
}

/** Lencana jumlah "perlu ditinjau" di menu Pipeline. */
export function setReviewCount(n) {
    const el = document.getElementById('ws-nav-review');
    if (!el) return;
    el.textContent = n;
    el.classList.toggle('hidden', !n);
}

export function canPublish(ctx) {
    return ctx?.isAdmin || ctx?.company?.verification_status === 'verified';
}
