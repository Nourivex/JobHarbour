/**
 * Admin Portal: Ringkasan. Bentuknya kotak masuk keputusan (antrean berurutan), bukan kumpulan kartu angka.
 * Semua angka dari GET /api/admin/overview; antrean kosong ditulis sebagai kalimat, bukan angka nol besar.
 */
import { admin } from '../../api.js';
import { bootAdmin, renderNavCounts } from '../../admin/shell.js';
import { esc, icon, hydrateIcons, relativeTime, formatDateTime, emptyState, skeletonRows, errorMessage, plural } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const sum = (obj) => Object.values(obj || {}).reduce((s, n) => s + Number(n || 0), 0);

function queueItem({ n, title, detail, href, iconName, hot = false }) {
    const clear = !n;
    return `
        <a href="${href}" class="adm-queue-item ${clear ? 'is-clear' : hot ? 'is-hot' : ''}">
            <span class="ws-empty-icon !w-9 !h-9 !m-0 shrink-0">${icon(iconName, 'w-4 h-4')}</span>
            <span class="min-w-0 flex-1">
                <span class="block font-semibold">${title}</span>
                <span class="block ws-meta">${detail}</span>
            </span>
            <span class="adm-queue-num">${clear ? '0' : n}</span>
            ${icon('chevron-right', 'w-4 h-4 text-[var(--ws-muted)]')}
        </a>`;
}

function renderQueue(q) {
    const oldest = q.oldest_pending_at ? `Tertua diajukan ${relativeTime(q.oldest_pending_at)}.` : '';
    $('ov-queue').innerHTML = [
        queueItem({
            n: q.verifications_pending, hot: true, iconName: 'badge-check', href: '/admin/verifications',
            title: 'Verifikasi perusahaan menunggu',
            detail: q.verifications_pending ? `${oldest} Recruiter tidak bisa mempublikasikan tanpa batas sebelum disetujui.` : 'Tidak ada pengajuan yang menunggu.',
        }),
        queueItem({
            n: q.sources_degraded, hot: true, iconName: 'globe', href: '/admin/sources',
            title: 'Sumber lowongan bermasalah',
            detail: q.sources_degraded ? 'Pengambilan terakhir gagal. Lowongan dari sumber ini bisa basi.' : 'Semua sumber berhasil diambil pada percobaan terakhir.',
        }),
        queueItem({
            n: q.companies_revision, iconName: 'file-pen-line', href: '/admin/companies?status=revision_required',
            title: 'Perusahaan diminta revisi',
            detail: q.companies_revision ? 'Menunggu recruiter memperbaiki dan mengajukan ulang.' : 'Tidak ada yang sedang direvisi.',
        }),
        queueItem({
            n: q.jobs_removed, iconName: 'ban', href: '/admin/jobs?moderation=removed',
            title: 'Lowongan diturunkan',
            detail: q.jobs_removed ? 'Tidak tampil di katalog sampai dipulihkan.' : 'Tidak ada lowongan yang diturunkan.',
        }),
        queueItem({
            n: q.users_suspended + q.companies_suspended, iconName: 'user-x', href: '/admin/users?status=suspended',
            title: 'Akun dan perusahaan ditangguhkan',
            detail: q.users_suspended || q.companies_suspended
                ? `${plural(q.users_suspended, 'akun')} dan ${plural(q.companies_suspended, 'perusahaan')}.`
                : 'Tidak ada yang ditangguhkan.',
        }),
    ].join('');
}

function renderHealth(d, healthy) {
    const degraded = d.sources.degraded || [];
    $('ov-health').innerHTML = `
        <div class="adm-health">
            <span class="${healthy ? '' : 'is-bad'}"><span class="ws-status-dot"></span>API ${healthy ? 'berjalan normal' : 'tidak merespons'}</span>
            <span class="${degraded.length ? 'is-bad' : ''}"><span class="ws-status-dot"></span>${d.sources.total ? `${d.sources.enabled} dari ${d.sources.total} sumber lowongan aktif${degraded.length ? `, ${degraded.length} bermasalah` : ', semuanya sehat'}` : 'Belum ada sumber lowongan otomatis'}</span>
            ${degraded.length ? `<a href="/admin/sources" class="ws-btn ws-btn-ghost ws-btn-sm ml-auto">Periksa sumber${icon('arrow-right')}</a>` : ''}
        </div>`;
}

function row(label, value, sub = '') {
    return `<div class="flex items-baseline justify-between gap-3 py-2.5 border-b border-[var(--ws-border)] last:border-0">
        <span class="text-[var(--ws-text-2)]">${label}</span>
        <span class="text-right"><b class="ws-num text-[var(--ws-text)]">${value}</b>${sub ? `<span class="block ws-meta">${sub}</span>` : ''}</span>
    </div>`;
}

function renderData(d, windowDays) {
    const roles = d.users.by_role || {};
    const companies = d.companies.by_status || {};
    $('ov-data-sub').textContent = `Dihitung langsung dari database. "Baru" berarti ${windowDays} hari terakhir.`;
    $('ov-data').innerHTML = [
        row('Pengguna', sum(roles), `${roles.applicant || 0} pelamar, ${roles.recruiter || 0} recruiter${d.users.new_30d ? ` · ${d.users.new_30d} baru` : ''}`),
        row('Perusahaan', sum(companies), `${companies.verified || 0} terverifikasi`),
        row('Lowongan terbuka', d.jobs.open_real + d.jobs.open_demo, d.jobs.open_demo ? `${d.jobs.open_real} nyata, ${d.jobs.open_demo} data demo` : 'semuanya data nyata'),
        row('Lamaran', d.applications.total, d.applications.new_30d ? `${d.applications.new_30d} baru` : ''),
    ].join('');
}

function renderAudit(list) {
    if (!list.length) {
        $('ov-audit').innerHTML = `<div class="ws-card-body">${emptyState({ iconName: 'scroll-text', title: 'Belum ada tindakan admin', text: 'Keputusan verifikasi, penangguhan, dan moderasi akan tercatat di sini.' })}</div>`;
        return;
    }
    $('ov-audit').innerHTML = `<ul class="divide-y divide-[var(--ws-border)]">${list.map((a) => `
        <li class="px-5 py-3 flex items-start gap-3">
            <span class="min-w-0 flex-1">
                <span class="block text-sm"><b>${esc(a.actor?.name || 'Sistem')}</b> ${esc(a.action_label.toLowerCase())}${a.target_label ? `: <span class="text-[var(--ws-text)]">${esc(a.target_label)}</span>` : ''}</span>
                ${a.reason ? `<span class="block ws-meta truncate">"${esc(a.reason)}"</span>` : ''}
            </span>
            <time class="ws-meta whitespace-nowrap" datetime="${a.created_at}" title="${formatDateTime(a.created_at)}">${relativeTime(a.created_at)}</time>
        </li>`).join('')}</ul>`;
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-overview', loadNav: false });
    if (!ctx) return;

    $('ov-queue').innerHTML = `<div class="p-4">${skeletonRows(4, 'h-12')}</div>`;
    $('ov-data').innerHTML = skeletonRows(4, 'h-8');
    try {
        const [res, healthy] = await Promise.all([
            admin.overview(),
            admin.health().then(() => true).catch(() => false),
        ]);
        const d = res.data;
        renderNavCounts(d.queue);
        renderHealth(d, healthy);
        renderQueue(d.queue);
        renderData(d, res.meta?.window_days || 30);
        renderAudit(d.recent_actions || []);
    } catch (err) {
        $('ov-queue').innerHTML = `<div class="p-5">${emptyState({ iconName: 'circle-alert', title: 'Ringkasan gagal dimuat', text: esc(errorMessage(err)), action: '<button type="button" class="ws-btn ws-btn-secondary" onclick="location.reload()">Muat ulang</button>' })}</div>`;
        $('ov-data').innerHTML = '';
    }
    hydrateIcons();
});
