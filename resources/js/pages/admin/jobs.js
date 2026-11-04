/**
 * Admin Portal: moderasi lowongan. Daftar dari server (GET /api/admin/jobs) dengan filter,
 * aksi turunkan/pulihkan dengan alasan (PATCH /api/admin/jobs/{id}/moderation, tercatat di Log Audit).
 */
import { admin, toast } from '../../api.js';
import { bootAdmin } from '../../admin/shell.js';
import { confirmAction, pager, moderationBadge, tableMessage, flash, mountDemoSetting } from '../../admin/ui.js';
import { debounce, bindDialog, openDialog } from '../../recruiter/components.js';
import { esc, icon, hydrateIcons, jobStatusBadge, formatDate, relativeTime, emptyState, skeletonRows, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const params = new URLSearchParams(location.search);
const FILTERS = ['id', 'q', 'moderation', 'status', 'source', 'demo'];
const state = { page: Number(params.get('page')) || 1, items: [] };
FILTERS.forEach((k) => { state[k] = params.get(k) || ''; });

const SOURCE = { direct: 'Recruiter', external: 'Situs karier resmi', partner: 'Mitra' };

function syncUrl() {
    const p = new URLSearchParams();
    FILTERS.forEach((k) => state[k] && p.set(k, state[k]));
    if (state.page > 1) p.set('page', state.page);
    history.replaceState(null, '', p.toString() ? `?${p}` : location.pathname);
}

async function load() {
    syncUrl();
    $('jb-rows').innerHTML = tableMessage(5, '<div class="ws-skel h-10"></div>');
    try {
        const query = { page: state.page, per_page: 20 };
        FILTERS.forEach((k) => { if (state[k]) query[k] = state[k]; });
        const res = await admin.jobs(query);
        state.items = res.data || [];
        $('jb-only').innerHTML = state.id
            ? `<button type="button" class="ws-fchip" data-clear-id>Hanya lowongan #${esc(state.id)} <span aria-hidden="true">&times;</span><span class="sr-only">tampilkan semua</span></button>`
            : '';
        if (!state.items.length) {
            $('jb-rows').innerHTML = tableMessage(5, emptyState({ iconName: 'briefcase-business', title: 'Tidak ada lowongan yang cocok', text: 'Ubah kata kunci atau filter.' }));
            $('jb-pager').innerHTML = '';
            return;
        }
        $('jb-rows').innerHTML = state.items.map((j) => {
            const removed = j.moderation_status === 'removed';
            return `
            <tr data-row="${j.id}">
                <td>
                    <button type="button" class="block text-left font-semibold text-[var(--ws-text)] hover:underline underline-offset-2" data-job="${j.id}">${esc(j.title)}</button>
                    <span class="block ws-meta">${esc([j.company, j.location].filter(Boolean).join(' · '))}${j.recruiter ? ` · oleh ${esc(j.recruiter.name)}` : ''}</span>
                    ${removed && j.moderation_reason ? `<span class="block mt-1 text-[13px] text-[var(--ws-danger)]">Alasan: ${esc(j.moderation_reason)}</span>` : ''}
                </td>
                <td data-label="Status"><span class="flex flex-wrap justify-end sm:justify-start gap-1">${jobStatusBadge(j.status)}${moderationBadge(j)}</span></td>
                <td data-label="Sumber" class="text-sm"><span class="block">${SOURCE[j.source_type] || esc(j.source_type || '-')}</span>${j.is_demo ? '<span class="ws-pill ws-pill-muted mt-1">Data demo</span>' : ''}</td>
                <td data-label="Pelamar" class="num">${j.applications_count}</td>
                <td class="adm-actions text-right whitespace-nowrap">
                    ${j.status === 'open' && !removed ? `<a href="/jobs/${j.id}" target="_blank" rel="noopener" class="ws-btn ws-btn-ghost ws-btn-sm" title="Buka di katalog">${icon('external-link')}</a>` : ''}
                    ${removed
                        ? `<button type="button" class="ws-btn ws-btn-secondary ws-btn-sm" data-moderate="restore" data-id="${j.id}">${icon('rotate-ccw')}Pulihkan</button>`
                        : `<button type="button" class="ws-btn ws-btn-danger-ghost ws-btn-sm" data-moderate="remove" data-id="${j.id}">${icon('ban')}Turunkan</button>`}
                </td>
            </tr>`;
        }).join('');
        $('jb-pager').innerHTML = pager(res.meta?.pagination, 'lowongan');
        hydrateIcons($('jb-rows'));
        hydrateIcons($('jb-pager'));
    } catch (err) {
        $('jb-rows').innerHTML = tableMessage(5, emptyState({ iconName: 'circle-alert', title: 'Daftar lowongan gagal dimuat', text: esc(errorMessage(err)) }));
    }
}

const LEVEL = { required: 'Wajib', important: 'Penting', nice_to_have: 'Bonus' };
const WEIGHT_LEVEL = { 3: 'required', 2: 'important', 1: 'nice_to_have' };
const REPORT_STATUS = { open: ['Baru', 'warning'], in_review: ['Diperiksa', 'info'], resolved: ['Selesai', 'success'], dismissed: ['Ditolak', 'muted'] };
const rupiah = (n) => `Rp${Number(n).toLocaleString('id-ID')}`;

/** Detail lowongan untuk pemeriksaan: isi, syarat skill, laporan, dan riwayat tindakan admin. */
async function openJob(id) {
    const drawer = bindDialog($('jb-drawer'));
    $('jb-d-title').textContent = 'Memuat lowongan...';
    $('jb-d-sub').textContent = '';
    $('jb-d-body').innerHTML = skeletonRows(4, 'h-14');
    $('jb-d-foot').innerHTML = '';
    openDialog(drawer);
    try {
        const j = (await admin.job(id)).data;
        state.current = j;
        const removed = j.moderation_status === 'removed';
        const list = (items, fn, empty) => (items.length ? `<ul class="divide-y divide-[var(--ws-border)] text-sm">${items.map(fn).join('')}</ul>` : `<p class="ws-meta">${empty}</p>`);
        const reqs = (j.requirements || []).map((r) => {
            const level = LEVEL[r.level] ? r.level : (WEIGHT_LEVEL[Number(r.weight)] || 'required');
            return `<span class="ws-pill ws-pill-${level === 'required' ? 'neutral' : 'muted'}">${esc(r.skill || r.name || '-')} <span class="opacity-70">· ${LEVEL[level]}</span></span>`;
        });
        const salary = j.salary_min || j.salary_max
            ? [j.salary_min, j.salary_max].filter(Boolean).map(rupiah).join(' sampai ')
            : 'Tidak dicantumkan';
        $('jb-d-title').textContent = j.title;
        $('jb-d-sub').textContent = [j.company, j.location].filter(Boolean).join(' · ');
        $('jb-d-body').innerHTML = `
            <div class="flex flex-wrap gap-1.5">${jobStatusBadge(j.status)}${moderationBadge(j)}${j.is_demo ? '<span class="adm-demo-tag">DEMO</span>' : ''}</div>
            ${removed ? `<div class="ws-callout ws-callout-warning">${icon('ban', 'w-4 h-4')}<p>Diturunkan ${j.moderated_at ? formatDate(j.moderated_at, true) : ''}.${j.moderation_reason ? ` Alasan: "${esc(j.moderation_reason)}"` : ''}</p></div>` : ''}
            <dl class="adm-dl">
                <dt>Sumber</dt><dd>${SOURCE[j.source_type] || esc(j.source_type || '-')}${j.source_url ? ` · <a href="${esc(j.source_url)}" target="_blank" rel="noopener" class="text-[var(--ws-info)] hover:underline">situs asal</a>` : ''}</dd>
                <dt>Recruiter</dt><dd>${j.recruiter ? `${esc(j.recruiter.name)} <span class="ws-meta">${esc(j.recruiter.email)}</span>` : '-'}</dd>
                ${j.company_record ? `<dt>Perusahaan recruiter</dt><dd><a href="/admin/companies/${j.company_record.id}" class="text-[var(--ws-info)] hover:underline">${esc(j.company_record.name)}</a></dd>` : ''}
                <dt>Gaji</dt><dd>${salary}</dd>
                <dt>Pelamar</dt><dd>${j.applications_count}</dd>
                <dt>Dibuat</dt><dd>${formatDate(j.created_at, true)}</dd>
                ${j.expires_at ? `<dt>Ditutup</dt><dd>${formatDate(j.expires_at, true)}</dd>` : ''}
            </dl>
            <section><h3 class="ws-h3 mb-2">Syarat skill</h3>${reqs.length ? `<div class="flex flex-wrap gap-1.5">${reqs.join('')}</div>` : '<p class="ws-meta">Tidak ada syarat skill.</p>'}</section>
            <section><h3 class="ws-h3 mb-2">Deskripsi</h3><p class="text-sm text-[var(--ws-text-2)] whitespace-pre-line max-h-56 overflow-y-auto">${esc(j.description || '-')}</p></section>
            <section><h3 class="ws-h3 mb-2">Laporan dan banding</h3>${list(j.reports, (r) => {
                const st = REPORT_STATUS[r.status] || [r.status, 'muted'];
                return `<li class="py-2.5"><span class="flex items-center justify-between gap-2"><b>${r.kind === 'appeal' ? 'Banding recruiter' : esc(r.category_label)}</b><span class="ws-pill ws-pill-${st[1]}">${st[0]}</span></span><span class="block text-[var(--ws-text-2)] mt-0.5">${esc(r.description)}</span><span class="block ws-meta mt-0.5">${esc(r.reporter?.name || '-')} · ${relativeTime(r.created_at)}${r.resolution ? ` · Hasil: "${esc(r.resolution)}"` : ''}</span></li>`;
            }, 'Belum pernah dilaporkan.')}</section>
            <section><h3 class="ws-h3 mb-2">Riwayat tindakan admin</h3>${list(j.history, (h) => `<li class="py-2"><span class="block">${esc(h.action_label)} oleh ${esc(h.actor?.name || '-')}</span><span class="block ws-meta">${formatDate(h.created_at, true)}${h.reason ? ` · "${esc(h.reason)}"` : ''}</span></li>`, 'Belum ada.')}</section>`;
        $('jb-d-foot').innerHTML = `
            <button type="button" class="ws-btn ws-btn-ghost mr-auto" data-dialog-close>Tutup</button>
            ${j.status === 'open' && !removed ? `<a href="/jobs/${j.id}" target="_blank" rel="noopener" class="ws-btn ws-btn-secondary">${icon('external-link')}Buka di katalog</a>` : ''}
            ${removed
                ? `<button type="button" class="ws-btn ws-btn-primary" data-moderate="restore" data-id="${j.id}">${icon('rotate-ccw')}Pulihkan</button>`
                : `<button type="button" class="ws-btn ws-btn-danger" data-moderate="remove" data-id="${j.id}">${icon('ban')}Turunkan</button>`}`;
        hydrateIcons($('jb-drawer'));
    } catch (err) {
        $('jb-d-title').textContent = 'Lowongan';
        $('jb-d-body').innerHTML = emptyState({ iconName: 'circle-alert', title: 'Detail lowongan gagal dimuat', text: esc(errorMessage(err)) });
        $('jb-d-foot').innerHTML = '<button type="button" class="ws-btn ws-btn-ghost" data-dialog-close>Tutup</button>';
        hydrateIcons($('jb-drawer'));
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-jobs' });
    if (!ctx) return;
    mountDemoSetting($('jb-demo-setting'));

    $('jb-q').value = state.q;
    $('jb-q').addEventListener('input', debounce((e) => { state.q = e.target.value.trim(); state.page = 1; load(); }, 250));
    ['moderation', 'status', 'source', 'demo'].forEach((k) => {
        $(`jb-${k}`).value = state[k];
        $(`jb-${k}`).addEventListener('change', (e) => { state[k] = e.target.value; state.page = 1; load(); });
    });

    document.addEventListener('click', (e) => {
        const pg = e.target.closest('[data-goto-page]');
        if (pg && !pg.disabled) { state.page = Number(pg.dataset.gotoPage); load(); return; }
        if (e.target.closest('[data-clear-id]')) { state.id = ''; state.page = 1; load(); return; }
        const open = e.target.closest('[data-job]');
        if (open) { openJob(open.dataset.job); return; }
        const btn = e.target.closest('[data-moderate]');
        if (!btn) return;
        const job = state.items.find((j) => String(j.id) === btn.dataset.id) || (String(state.current?.id) === btn.dataset.id ? state.current : null);
        if (!job) return;
        // Dari drawer: dialog konfirmasi tampil di atasnya, lalu drawer dimuat ulang dengan status baru
        const fromDrawer = Boolean(btn.closest('dialog'));
        const remove = btn.dataset.moderate === 'remove';
        confirmAction({
            title: remove ? 'Turunkan lowongan?' : 'Pulihkan lowongan?',
            subtitle: `${job.title} · ${job.company}`,
            impact: remove
                ? `Lowongan hilang dari katalog dan tidak bisa dilamar. ${job.applications_count ? `${job.applications_count} lamaran yang sudah masuk tetap tersimpan. ` : ''}Recruiter melihat alasannya dan tidak bisa membukanya lagi sampai dipulihkan.`
                : 'Lowongan kembali mengikuti status dari recruiter. Bila statusnya Dipublikasikan, lowongan tampil lagi di katalog.',
            confirmText: remove ? 'Turunkan' : 'Pulihkan',
            tone: remove ? 'danger' : 'primary',
            reason: remove ? 'required' : 'optional',
            placeholder: remove ? 'contoh: Gaji yang dicantumkan tidak sesuai dengan informasi dari perusahaan.' : '',
            onConfirm: async (reason) => {
                await admin.moderateJob(job.id, { action: remove ? 'remove' : 'restore', reason });
                toast(remove ? 'Lowongan diturunkan dan tercatat di Log Audit.' : 'Lowongan dipulihkan.', 'success');
                await load();
                flash(document.querySelector(`#jb-rows tr[data-row="${job.id}"]`));
                if (fromDrawer) openJob(job.id);
            },
        });
    });

    await load();
});
