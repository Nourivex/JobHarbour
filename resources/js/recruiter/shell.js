/**
 * Kerangka Portal Recruiter: penjaga akses, sidebar, kartu perusahaan, dan banner status verifikasi.
 * Setiap halaman memanggil bootShell() lalu menerima pengguna dan profil perusahaan.
 */
import JH, { session, toast } from '../api.js';
import { data } from './data.js';
import { hydrateIcons, icon, initials, VERIFY, esc, companyLogo, stageOf, interviewOf } from './ui.js';

export function initSidebar() {
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

    // Desktop: sidebar ringkas (ikon saja), pilihan disimpan per peramban
    const collapseBtn = document.querySelector('[data-sidebar-collapse]');
    const syncCollapse = () => {
        const on = document.documentElement.classList.contains('ws-collapsed');
        collapseBtn?.setAttribute('aria-pressed', String(on));
        collapseBtn?.setAttribute('aria-label', on ? 'Lebarkan sidebar' : 'Ringkas sidebar');
        collapseBtn?.setAttribute('title', on ? 'Lebarkan sidebar' : 'Ringkas sidebar');
    };
    syncCollapse();
    // Dua fase supaya teks menu tidak hilang mendadak: teks memudar dulu, baru lebar sidebar berubah.
    // Saat melebar, lebar berubah dulu lalu teks muncul kembali.
    const root = document.documentElement;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let phase = null;
    collapseBtn?.addEventListener('click', () => {
        clearTimeout(phase);
        const on = !root.classList.contains('ws-collapsed');
        try { localStorage.setItem('jh_ws_collapsed', on ? '1' : '0'); } catch (e) { /* abaikan */ }
        if (reduceMotion.matches) {
            root.classList.toggle('ws-collapsed', on);
            syncCollapse();
            return;
        }
        root.classList.add('ws-labels-hidden');
        if (on) {
            phase = setTimeout(() => {
                root.classList.add('ws-collapsed');
                root.classList.remove('ws-labels-hidden');
                syncCollapse();
            }, 110);
        } else {
            root.classList.remove('ws-collapsed');
            syncCollapse();
            phase = setTimeout(() => root.classList.remove('ws-labels-hidden'), 200);
        }
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.body.classList.contains('ws-nav-open')) setOpen(false);
    });
}

export function renderUser(user) {
    document.getElementById('ws-user-avatar').textContent = initials(user.name);
    document.getElementById('ws-user-name').textContent = user.name || '-';
    document.getElementById('ws-user-role').textContent = user.role === 'admin' ? 'Administrator' : 'Recruiter';
    if (user.role === 'admin') {
        document.getElementById('ws-admin-nav')?.classList.remove('hidden');
        document.getElementById('ws-company-nav')?.classList.add('hidden');
        const card = document.getElementById('ws-company-card');
        card?.setAttribute('href', '/admin');
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
    const color = { success: '#4ADE80', info: '#7CC4F5', warning: '#FBBF24', danger: '#F87171', neutral: '#CBD5E1' }[v.tone];
    statusEl.innerHTML = `<span class="ws-status-dot" style="--c:${color}"></span>${v.label}`;
    logoEl.outerHTML = companyLogo(company, 'ws-company-logo');
}

/**
 * Banner di atas konten bila perusahaan belum bisa mempublikasikan lowongan.
 * Di layar kecil hanya judul singkat dan tombol yang tampil; penjelasan lengkap ada di halaman tujuan.
 */
function renderBanner(user, company) {
    const banner = document.getElementById('ws-banner');
    const dot = document.getElementById('ws-nav-verify');
    if (user.role === 'admin') return;
    const status = company?.verification_status || 'none';
    if (status === 'verified') return;

    dot?.classList.remove('hidden');
    if (document.body.dataset.page === 'verification') return;

    const copy = {
        none: ['Lengkapi profil perusahaan untuk mulai merekrut.', 'Sebelum diverifikasi, kamu bisa mempublikasikan 1 lowongan sebagai uji coba.', '/recruiter/company', 'Lengkapi profil', 'info', 'Profil perusahaan belum lengkap'],
        unverified: ['Perusahaanmu belum terverifikasi.', 'Kamu bisa mempublikasikan 1 lowongan sebagai uji coba. Verifikasi untuk mempublikasikan tanpa batas.', '/recruiter/verification', 'Ajukan verifikasi', 'info', 'Perusahaan belum terverifikasi'],
        pending: ['Pengajuan verifikasi sedang diperiksa admin.', 'Selama diperiksa, 1 lowongan boleh dipublikasikan sebagai uji coba.', '/recruiter/verification', 'Lihat status', 'info', 'Verifikasi sedang diperiksa'],
        revision_required: ['Admin meminta perbaikan verifikasi.', esc(company?.verification_note || 'Lihat catatan admin lalu ajukan ulang.'), '/recruiter/verification', 'Perbaiki pengajuan', 'warning', 'Verifikasi perlu perbaikan'],
        rejected: ['Pengajuan verifikasi ditolak.', esc(company?.verification_note || 'Lihat catatan admin lalu ajukan ulang.'), '/recruiter/verification', 'Perbaiki dan ajukan ulang', 'danger', 'Verifikasi ditolak'],
        suspended: ['Perusahaan sedang ditangguhkan.', esc(company?.verification_note || 'Hubungi admin JobHarbour untuk informasi lebih lanjut.'), '/recruiter/verification', 'Lihat detail', 'danger', 'Perusahaan ditangguhkan'],
    }[status];

    banner.className = `ws-banner ws-banner-${copy[4]}`;
    banner.innerHTML = `
        <div class="ws-banner-inner">
            ${icon({ danger: 'shield-alert', warning: 'file-pen-line' }[copy[4]] || 'info', 'w-5 h-5')}
            <p class="flex-1 min-w-0">
                <strong class="hidden sm:inline">${copy[0]}</strong> <span class="hidden sm:inline">${copy[1]}</span>
                <strong class="sm:hidden">${copy[5]}</strong>
            </p>
            <a href="${copy[2]}" class="ws-btn ws-btn-sm ws-btn-secondary whitespace-nowrap" aria-label="${copy[3]}"><span class="hidden sm:inline">${copy[3]}</span><span class="sm:hidden">Buka</span>${icon('arrow-right', 'w-4 h-4')}</a>
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

/** Lencana jumlah "perlu ditinjau" di menu Kandidat. */
export function setReviewCount(n) {
    const el = document.getElementById('ws-nav-review');
    if (!el) return;
    el.textContent = n;
    el.classList.toggle('hidden', !n);
}

/** Lencana menu dihitung dari daftar lamaran: berkas di Screening dan wawancara 7 hari ke depan. */
export function setNavCounts(apps) {
    setReviewCount(apps.filter((a) => stageOf(a) === 'screening').length);
    const el = document.getElementById('ws-nav-interviews');
    if (!el) return;
    const now = Date.now();
    const n = apps.filter((a) => {
        const s = stageOf(a) === 'interview' ? interviewOf(a) : null;
        const t = s ? new Date(s.datetime).getTime() : NaN;
        return t >= now - 3600000 && t <= now + 7 * 86400000;
    }).length;
    el.textContent = n;
    el.classList.toggle('hidden', !n);
}

const TRIAL_FULL = 'Batas 1 lowongan uji coba sudah terpakai. Verifikasi perusahaan untuk mempublikasikan lowongan tanpa batas.';
const BLOCKED = {
    suspended: 'Perusahaan sedang ditangguhkan admin sehingga lowongan tidak dapat dipublikasikan.',
    revision_required: 'Admin meminta perbaikan verifikasi. Perbaiki pengajuan untuk mempublikasikan lowongan.',
    rejected: 'Pengajuan verifikasi ditolak. Perbaiki data verifikasi untuk mempublikasikan lowongan.',
};

/**
 * Aturan publikasi, sama dengan JobVacancyController::publishBlocked di backend:
 * admin dan perusahaan terverifikasi bebas; ditangguhkan/perlu revisi/ditolak diblok;
 * selain itu boleh 1 lowongan aktif sebagai uji coba. jobs = lowongan milik recruiter (GET /jobs/my).
 * Mengembalikan { ok, trial, reason }.
 */
export function publishRule(ctx, jobs = [], exceptId = null) {
    if (ctx?.isAdmin) return { ok: true, trial: false, reason: '' };
    // Lowongan yang diturunkan admin tidak bisa dibuka lagi sampai dipulihkan (JOB_REMOVED_BY_ADMIN di backend)
    const self = exceptId != null ? jobs.find((j) => String(j.id) === String(exceptId)) : null;
    if (self?.moderation_status === 'removed') {
        return { ok: false, trial: false, reason: `Diturunkan admin JobHarbour: ${self.moderation_reason || 'tanpa keterangan'}. Perbaiki isinya lalu hubungi admin.` };
    }
    const status = ctx?.company?.verification_status || null;
    if (status === 'verified') return { ok: true, trial: false, reason: '' };
    if (BLOCKED[status]) return { ok: false, trial: false, reason: BLOCKED[status] };
    const open = jobs.filter((j) => j.status === 'open' && String(j.id) !== String(exceptId)).length;
    return open < 1 ? { ok: true, trial: true, reason: '' } : { ok: false, trial: true, reason: TRIAL_FULL };
}

export function canPublish(ctx, jobs = [], exceptId = null) {
    return publishRule(ctx, jobs, exceptId).ok;
}
