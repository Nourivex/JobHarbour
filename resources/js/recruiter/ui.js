/**
 * Komponen tampilan bersama Portal Recruiter: ikon Lucide, label tahap dan status, serta potongan HTML kecil.
 * Semua nilai berasal dari data API; modul ini tidak menyimpan data.
 */
import {
    createElement, createIcons,
    ArrowLeft, ArrowRight, ArrowUpRight, BadgeCheck, Ban, Briefcase, BriefcaseBusiness, Building2, CalendarClock,
    CalendarDays, Check, CheckCheck, ChevronDown, ChevronRight, CircleAlert, CircleCheck, CircleDashed, CircleX, Clock,
    Copy, Download, Ellipsis, ExternalLink, Eye, FilePen, FileText, Filter, Globe, Hourglass, ImageUp, Inbox, Info,
    Kanban, LayoutDashboard, Link, ListFilter, Lock, LogOut, Mail, MapPin, Menu, Moon, PartyPopper, Pencil, Phone, Plus,
    RefreshCw, RotateCcw, Search, Send, Settings, ShieldAlert, ShieldCheck, ShieldX, Sparkles, Sun, Target, Trash2,
    TrendingUp, Upload, UserRound, Users, X, Archive, Megaphone, KeyRound, Palette,
    ChevronsLeft, ChartColumn, StickyNote, Video, Table2, LayoutList, CalendarCheck, UserCheck, Timer, ListChecks,
    FilePenLine, MessageSquareText,
} from 'lucide';
import { util } from '../api.js';

export const esc = util.escapeHtml;

const ICONS = {
    ArrowLeft, ArrowRight, ArrowUpRight, BadgeCheck, Ban, Briefcase, BriefcaseBusiness, Building2, CalendarClock,
    CalendarDays, Check, CheckCheck, ChevronDown, ChevronRight, CircleAlert, CircleCheck, CircleDashed, CircleX, Clock,
    Copy, Download, Ellipsis, ExternalLink, Eye, FilePen, FileText, Filter, Globe, Hourglass, ImageUp, Inbox, Info,
    Kanban, LayoutDashboard, Link, ListFilter, Lock, LogOut, Mail, MapPin, Menu, Moon, PartyPopper, Pencil, Phone, Plus,
    RefreshCw, RotateCcw, Search, Send, Settings, ShieldAlert, ShieldCheck, ShieldX, Sparkles, Sun, Target, Trash2,
    TrendingUp, Upload, UserRound, Users, X, Archive, Megaphone, KeyRound, Palette,
    ChevronsLeft, ChartColumn, StickyNote, Video, Table2, LayoutList, CalendarCheck, UserCheck, Timer, ListChecks,
    FilePenLine, MessageSquareText,
};

const toPascal = (name) => name.replace(/(^|-)([a-z0-9])/g, (_, __, c) => c.toUpperCase());

/** SVG ikon sebagai string untuk template literal. Ukuran dan ketebalan garis seragam. */
export function icon(name, cls = 'w-4 h-4') {
    const node = ICONS[toPascal(name)];
    if (!node) return '';
    const el = createElement(node);
    el.setAttribute('class', `lucide shrink-0 ${cls}`);
    el.setAttribute('stroke-width', '1.85');
    el.setAttribute('aria-hidden', 'true');
    return el.outerHTML;
}

/** Ganti semua <i data-lucide="..."> di dalam root dengan SVG. */
export function hydrateIcons(root = document) {
    createIcons({ icons: ICONS, attrs: { 'stroke-width': 1.85, 'aria-hidden': 'true' }, root });
}

// ------------------------------------------------------------------ tahap pipeline
/** Hired dipetakan dari tahap Penawaran berstatus lolos, tanpa tahap baru di backend. */
export const STAGES = ['screening', 'assessment', 'interview', 'offering', 'hired', 'rejected'];
export const ACTIVE_STAGES = ['screening', 'assessment', 'interview', 'offering'];
export const STAGE = {
    screening: { label: 'Screening', short: 'Screening', hint: 'Tinjau berkas', color: '#64748B', icon: 'inbox' },
    assessment: { label: 'Assessment', short: 'Assessment', hint: 'Uji kemampuan', color: '#1B65A6', icon: 'file-pen' },
    interview: { label: 'Interview', short: 'Interview', hint: 'Wawancara', color: '#B45309', icon: 'calendar-clock' },
    offering: { label: 'Offering', short: 'Offering', hint: 'Penawaran kerja', color: '#7C3AED', icon: 'send' },
    hired: { label: 'Hired', short: 'Hired', hint: 'Diterima bekerja', color: '#15803D', icon: 'party-popper' },
    rejected: { label: 'Rejected', short: 'Rejected', hint: 'Tidak dilanjutkan', color: '#B42318', icon: 'circle-x' },
};
export const STAGE_ID = {
    screening: 'Penyaringan berkas', assessment: 'Penilaian', interview: 'Wawancara',
    offering: 'Penawaran', hired: 'Diterima', rejected: 'Ditolak',
};

export function stageOf(app) {
    const tl = app?.pipeline_timeline;
    let raw = String(tl?.current_stage || app?.status || 'screening').toLowerCase();
    if (raw === 'submitted') raw = 'screening';
    if (raw === 'offering') {
        const stages = tl?.stages || [];
        const last = stages[stages.length - 1];
        if (last && last.stage === 'offering' && last.status === 'passed') return 'hired';
    }
    return STAGES.includes(raw) ? raw : 'screening';
}

/** Langkah berikutnya yang sah: maju satu tahap. Offering maju ke Hired. */
export function nextStageOf(stage) {
    return { screening: 'assessment', assessment: 'interview', interview: 'offering', offering: 'hired' }[stage] || null;
}

export function stageEnteredAt(app) {
    const stages = app?.pipeline_timeline?.stages || [];
    return stages.length ? stages[stages.length - 1].entered_at : app?.created_at;
}

export function interviewOf(app) {
    const stages = app?.pipeline_timeline?.stages || [];
    for (let i = stages.length - 1; i >= 0; i -= 1) {
        if (stages[i].interview_schedule?.datetime) return stages[i].interview_schedule;
    }
    return null;
}

export function stageBadge(stage) {
    const s = STAGE[stage] || STAGE.screening;
    return `<span class="ws-badge" style="--c:${s.color}"><span class="ws-dot"></span>${s.label}</span>`;
}

// ------------------------------------------------------------------ status lowongan
export const JOB_STATUS = {
    draft: { label: 'Draft', tone: 'neutral', icon: 'file-pen' },
    open: { label: 'Dipublikasikan', tone: 'success', icon: 'circle-check' },
    closed: { label: 'Ditutup', tone: 'warning', icon: 'lock' },
    archived: { label: 'Diarsipkan', tone: 'muted', icon: 'archive' },
};

export function jobStatusBadge(status) {
    const s = JOB_STATUS[status] || JOB_STATUS.closed;
    return `<span class="ws-pill ws-pill-${s.tone}">${icon(s.icon, 'w-3.5 h-3.5')}${s.label}</span>`;
}

// ------------------------------------------------------------------ status verifikasi
export const VERIFY = {
    unverified: { label: 'Belum diverifikasi', tone: 'neutral', icon: 'circle-dashed', text: 'Ajukan verifikasi agar lowongan bisa dipublikasikan.' },
    pending: { label: 'Sedang diperiksa', tone: 'info', icon: 'hourglass', text: 'Admin sedang memeriksa dokumen perusahaanmu.' },
    revision_required: { label: 'Perlu revisi', tone: 'warning', icon: 'file-pen-line', text: 'Admin meminta perbaikan. Baca catatannya, perbaiki, lalu ajukan ulang.' },
    verified: { label: 'Terverifikasi', tone: 'success', icon: 'shield-check', text: 'Perusahaan terverifikasi dan bisa mempublikasikan lowongan.' },
    rejected: { label: 'Ditolak', tone: 'danger', icon: 'shield-x', text: 'Pengajuan ditolak. Perbaiki sesuai catatan admin lalu ajukan ulang.' },
    suspended: { label: 'Ditangguhkan', tone: 'danger', icon: 'shield-alert', text: 'Perusahaan ditangguhkan admin. Lowongan tidak bisa dipublikasikan.' },
};

export function verifyBadge(status) {
    const v = VERIFY[status] || VERIFY.unverified;
    return `<span class="ws-pill ws-pill-${v.tone}">${icon(v.icon, 'w-3.5 h-3.5')}${v.label}</span>`;
}

// ------------------------------------------------------------------ skor kecocokan
export const LEVEL = {
    required: { label: 'Wajib', weight: 3, color: '#C8460B' },
    important: { label: 'Penting', weight: 2, color: '#1B65A6' },
    nice_to_have: { label: 'Bonus', weight: 1, color: '#64748B' },
};

export function scoreInfo(score) {
    const s = Math.round(Number(score) || 0);
    if (s >= 80) return { score: s, key: 'excellent', label: 'Sangat cocok', color: '#15803D', advice: 'Memenuhi hampir semua syarat. Layak diprioritaskan.' };
    if (s >= 50) return { score: s, key: 'good', label: 'Cukup cocok', color: '#1B65A6', advice: 'Memenuhi sebagian besar syarat. Tinjau skill yang kurang.' };
    return { score: s, key: 'under', label: 'Belum cocok', color: '#B45309', advice: 'Banyak syarat belum terpenuhi.' };
}

export function scoreRing(score, size = 'md') {
    const i = scoreInfo(score);
    const dims = { sm: 'w-10 h-10 text-[11px]', md: 'w-12 h-12 text-[12px]', lg: 'w-[72px] h-[72px] text-[17px]' }[size];
    return `<span class="ws-ring ws-ring-${size} ${dims}" style="--deg:${i.score * 3.6}deg;--c:${i.color}" role="img" aria-label="Skor kecocokan ${i.score} persen, ${i.label}"><span>${i.score}%</span></span>`;
}

/** Rincian Weighted Jaccard per syarat: bobot yang didapat dan total bobot. */
export function matchBreakdown(app, job) {
    const matched = new Set((app?.matched_skills || []).map((s) => String(s).toLowerCase().trim()));
    const rows = (job?.requirements || []).map((r) => {
        const name = util.skillName(r);
        const level = LEVEL[r.level] ? r.level : (Number(r.weight) === 2 ? 'important' : Number(r.weight) === 1 ? 'nice_to_have' : 'required');
        const weight = Number(r.weight) || LEVEL[level].weight;
        return { name, level, weight, ok: matched.has(String(name).toLowerCase().trim()) };
    }).filter((r) => r.name);
    const total = rows.reduce((s, r) => s + r.weight, 0);
    const earned = rows.filter((r) => r.ok).reduce((s, r) => s + r.weight, 0);
    return { rows, total, earned };
}

// ------------------------------------------------------------------ format
export function initials(name) {
    return util.initials(name || '?');
}

export function avatar(name, cls = 'w-10 h-10 text-sm') {
    return `<span class="ws-avatar ${cls}" aria-hidden="true">${esc(initials(name))}</span>`;
}

export function companyLogo(company, cls = 'w-12 h-12 text-base') {
    if (company?.logo_url) return `<img src="${esc(company.logo_url)}" alt="" class="ws-logo ${cls}">`;
    return `<span class="ws-logo ws-logo-fallback ${cls}" aria-hidden="true">${esc(initials(company?.name || '?'))}</span>`;
}

export function daysSince(iso) {
    if (!iso) return null;
    return Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 86400000));
}

export function relativeTime(iso) {
    return util.timeAgo(iso);
}

export function formatDate(iso, withTime = false) {
    return util.formatDate(iso, withTime);
}

export function formatDateTime(iso) {
    if (!iso) return '-';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '-';
    return d.toLocaleString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}

export function plural(n, word) {
    return `${Number(n).toLocaleString('id-ID')} ${word}`;
}

export function formatBytes(n) {
    if (!n) return '';
    if (n < 1024 * 1024) return `${Math.round(n / 1024)} KB`;
    return `${(n / (1024 * 1024)).toFixed(1)} MB`;
}

export function emptyState({ iconName = 'inbox', title, text = '', action = '' }) {
    return `
        <div class="ws-empty">
            <span class="ws-empty-icon">${icon(iconName, 'w-6 h-6')}</span>
            <p class="ws-empty-title">${esc(title)}</p>
            ${text ? `<p class="ws-empty-text">${text}</p>` : ''}
            ${action}
        </div>`;
}

export function skeletonRows(n = 3, h = 'h-16') {
    return Array.from({ length: n }, () => `<div class="${h} rounded-xl animate-skeleton ws-skel"></div>`).join('');
}

/** Pesan error dari ApiError (envelope API) dalam bentuk teks singkat. */
export function errorMessage(err, fallback = 'Terjadi kesalahan. Coba lagi.') {
    return err?.message || fallback;
}

/** Tampilkan error per kolom (422) di elemen [data-error-for]. Kembalikan true bila ada yang ditampilkan. */
export function showFieldErrors(form, err, map = {}) {
    const errors = err?.errors || err?.payload?.errors;
    if (!errors || typeof errors !== 'object') return false;
    let shown = false;
    Object.entries(errors).forEach(([key, msgs]) => {
        const k = key.split('.')[0];
        const target = form.querySelector(`[data-error-for="${k}"]`);
        if (target) {
            target.textContent = Array.isArray(msgs) ? msgs[0] : String(msgs);
            shown = true;
        }
        const input = map[k] ? form.querySelector(map[k]) : form.querySelector(`[name="${k}"]`);
        input?.classList.add('is-invalid');
        input?.setAttribute('aria-invalid', 'true');
    });
    return shown;
}

export function clearFieldErrors(form) {
    form.querySelectorAll('[data-error-for]').forEach((el) => { el.textContent = ''; });
    form.querySelectorAll('.is-invalid').forEach((el) => {
        el.classList.remove('is-invalid');
        el.removeAttribute('aria-invalid');
    });
}
