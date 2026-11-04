/**
 * Helper tampilan lowongan yang dipakai bersama oleh halaman Katalog dan Detail Lowongan.
 * Semua nilai diturunkan dari data API; tidak ada data tambahan yang dikarang.
 */
import { util } from './api.js';

const esc = util.escapeHtml;

export const EMPLOYMENT_SHORT = {
    'full-time': 'Full-time',
    'part-time': 'Part-time',
    contract: 'Kontrak',
    internship: 'Magang',
    remote: 'Remote',
};

// Palet ubin logo: dipilih tetap berdasarkan nama perusahaan supaya konsisten di semua halaman
const TILE_PALETTE = [
    { bg: '#E7F2FB', fg: '#1B65A6' },
    { bg: '#FFF1E6', fg: '#C8460B' },
    { bg: '#EEEAFD', fg: '#5B3FD1' },
    { bg: '#E7F5EC', fg: '#157F3B' },
    { bg: '#0C2238', fg: '#FFFFFF' },
    { bg: '#FDF2D9', fg: '#8A5A00' },
];

function hash(text) {
    let h = 0;
    for (let i = 0; i < text.length; i += 1) h = (h * 31 + text.charCodeAt(i)) >>> 0;
    return h;
}

/** Inisial perusahaan tanpa kata badan usaha (PT, CV, Tbk). */
export function companyInitials(company = '') {
    const words = company.replace(/\b(PT|CV|Tbk|Persero)\b\.?/gi, '').trim().split(/\s+/).filter(Boolean);
    return (words.slice(0, 2).map((w) => w[0]).join('') || '?').toUpperCase();
}

/** Ubin logo perusahaan berbasis inisial (backend belum menyimpan logo perusahaan). */
export function companyTile(company, size = 'w-14 h-14 text-lg') {
    const tone = TILE_PALETTE[hash(company || '') % TILE_PALETTE.length];
    return `<span class="company-tile ${size} shrink-0 rounded-2xl flex items-center justify-center font-heading font-extrabold" style="background:${tone.bg};color:${tone.fg}" aria-hidden="true">${esc(companyInitials(company))}</span>`;
}

/** Kode lowongan yang dibentuk dari tahun tayang dan ID database. */
export function jobCode(job) {
    const year = job.created_at ? new Date(job.created_at).getFullYear() : new Date().getFullYear();
    return `JOB-${year}-${String(job.id).padStart(5, '0')}`;
}

export function rupiah(n) {
    return 'Rp' + Number(n).toLocaleString('id-ID');
}

export function salaryRange(job) {
    if (!job.salary_min && !job.salary_max) return null;
    if (job.salary_min && job.salary_max) return `${rupiah(job.salary_min)} - ${rupiah(job.salary_max)}`;
    return rupiah(job.salary_min || job.salary_max);
}

export function remainingQuota(job) {
    return Math.max((Number(job.quota) || 0) - (Number(job.filled) || 0), 0);
}

/** Kategori skor memakai ambang yang sama dengan MatchmakingService (80 dan 50). */
export function scoreCategory(score) {
    if (score >= 80) return { key: 'excellent', label: 'Sangat Cocok', color: '#157F3B', soft: '#E7F5EC' };
    if (score >= 50) return { key: 'good', label: 'Cukup Cocok', color: '#1B65A6', soft: '#EBF3FB' };
    return { key: 'under', label: 'Belum Cocok', color: '#C8460B', soft: '#FFF3EA' };
}

/** Lowongan tersimpan hanya disimpan di browser karena belum ada endpoint-nya. */
export const savedJobs = {
    key: 'jh_saved_jobs',
    all() {
        try {
            return new Set(JSON.parse(localStorage.getItem(this.key) || '[]').map(Number));
        } catch (e) {
            return new Set();
        }
    },
    has(id) {
        return this.all().has(Number(id));
    },
    toggle(id) {
        const set = this.all();
        const n = Number(id);
        if (set.has(n)) set.delete(n);
        else set.add(n);
        try {
            localStorage.setItem(this.key, JSON.stringify([...set]));
        } catch (e) {
            /* abaikan: penyimpanan browser tidak tersedia */
        }
        return set.has(n);
    },
};

export const ICONS = {
    pin: '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    briefcase: '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    bookmark: '<path d="M19 21l-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/>',
    arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    check: '<path d="m5 12 5 5 9-10"/>',
    users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
    calendar: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    share: '<circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><path d="m8.6 13.5 6.8 4M15.4 6.5l-6.8 4"/>',
    wallet: '<rect x="2" y="6" width="20" height="14" rx="2"/><path d="M16 13h2M2 10h20M6 6V4h12v2"/>',
};

export function icon(name, cls = 'w-4 h-4') {
    return `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICONS[name] || ''}</svg>`;
}


/**
 * Lencana Asal Sumber Lowongan Kerja (Source Transparency).
 * Menghindari klaim sepihak dan memisahkan lowongan mitra langsung, portal karir resmi, dan demo.
 */
export function jobSourceBadge(job) {
    if (job.is_demo || job.verification_status === 'demo_sample') {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-amber-50 dark:bg-amber-950/70 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60" title="Data simulasi terverifikasi untuk pengujian dan demonstrasi"><svg class="w-3 h-3 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Data Demo</span>';
    }

    if (job.source_type === 'external' || job.job_source_type === 'official_career_page') {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 dark:bg-sky-950/70 text-sky-800 dark:text-sky-300 border border-sky-200 dark:border-sky-800/60" title="Diverifikasi langsung dari website karir resmi perusahaan"><svg class="w-3 h-3 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>Sumber Karier Resmi</span>';
    }

    if (job.source_type === 'partner' || job.verification_status === 'verified_partner') {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-800 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60" title="Mitra terverifikasi JobHarbour"><svg class="w-3 h-3 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Mitra Terverifikasi</span>';
    }

    if (job.verification_status === 'verified_official') {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-blue-50 dark:bg-blue-950/70 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/60" title="Perusahaan telah diverifikasi legalitasnya resmi oleh JobHarbour"><svg class="w-3 h-3 text-blue-600 dark:text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>Terverifikasi Resmi</span>';
    }

    if (job.verification_status === 'unverified') {
        return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700" title="Lowongan reguler masa uji coba"><svg class="w-3 h-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>Lowongan Reguler</span>';
    }

    return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700"><svg class="w-3 h-3 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>Dipublikasikan Perusahaan</span>';
}

/**
 * Lencana Status Lamaran Pelamar ("Sudah Dilamar").
 * Menandakan pelamar sudah pernah mengajukan berkas ke lowongan ini.
 */
export function jobAppliedBadge(applied = false) {
    if (!applied) return '';
    return '<span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 dark:bg-emerald-950/70 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800/60" title="Anda telah mengajukan lamaran untuk posisi ini"><svg class="w-3 h-3 text-emerald-600 dark:text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 5 5 9-10"/></svg>Sudah Dilamar</span>';
}
