/**
 * Admin Portal: moderasi lowongan. Daftar dari server (GET /api/admin/jobs) dengan filter,
 * aksi turunkan/pulihkan dengan alasan (PATCH /api/admin/jobs/{id}/moderation, tercatat di Log Audit).
 */
import { admin, toast } from '../../api.js';
import { bootAdmin } from '../../admin/shell.js';
import { confirmAction, pager, moderationBadge, tableMessage, flash } from '../../admin/ui.js';
import { debounce } from '../../recruiter/components.js';
import { esc, icon, hydrateIcons, jobStatusBadge, formatDate, emptyState, errorMessage } from '../../recruiter/ui.js';

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
                    <span class="block font-semibold text-[var(--ws-text)]">${esc(j.title)}</span>
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

/** Pengaturan platform: sembunyikan lowongan data demo dari katalog publik. */
async function renderDemoSetting() {
    const box = $('jb-demo-setting');
    try {
        const hide = Boolean((await admin.settings()).data?.hide_demo_jobs);
        box.innerHTML = `
            <span class="min-w-0 flex-1">${hide
                ? '<b>Data demo disembunyikan</b> dari katalog publik. Pelamar hanya melihat lowongan nyata.'
                : '<b>Data demo masih tampil</b> di katalog publik, ditandai lencana "Data Demo".'}</span>
            <button type="button" class="ws-btn ${hide ? 'ws-btn-secondary' : 'ws-btn-primary'} ws-btn-sm" data-demo-toggle="${hide ? '0' : '1'}">${hide ? 'Tampilkan lagi' : 'Sembunyikan data demo'}</button>`;
    } catch (err) {
        box.innerHTML = `<span class="ws-meta">Pengaturan data demo gagal dimuat.</span>`;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-jobs' });
    if (!ctx) return;
    renderDemoSetting();
    $('jb-demo-setting').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-demo-toggle]');
        if (!btn) return;
        const hide = btn.dataset.demoToggle === '1';
        confirmAction({
            title: hide ? 'Sembunyikan data demo?' : 'Tampilkan data demo lagi?',
            subtitle: 'Pengaturan platform',
            impact: hide
                ? 'Lowongan bertanda data demo tidak tampil di katalog dan halaman detailnya tidak bisa dibuka publik. Data tidak dihapus dan tetap terlihat di sini.'
                : 'Lowongan data demo kembali tampil di katalog dengan lencana "Data Demo".',
            confirmText: hide ? 'Sembunyikan' : 'Tampilkan',
            tone: 'primary',
            reason: 'none',
            onConfirm: async () => {
                await admin.updateSettings({ hide_demo_jobs: hide });
                toast(hide ? 'Data demo disembunyikan dari katalog publik.' : 'Data demo tampil lagi di katalog.', 'success');
                await renderDemoSetting();
                flash($('jb-demo-setting'));
            },
        });
    });

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
        const btn = e.target.closest('[data-moderate]');
        if (!btn) return;
        const job = state.items.find((j) => String(j.id) === btn.dataset.id);
        if (!job) return;
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
            },
        });
    });

    await load();
});
