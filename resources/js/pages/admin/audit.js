/**
 * Admin Portal: log audit (baca saja). GET /api/admin/audit-logs dengan filter jenis tindakan dan halaman di server.
 */
import { admin } from '../../api.js';
import { bootAdmin } from '../../admin/shell.js';
import { pager, tableMessage } from '../../admin/ui.js';
import { esc, hydrateIcons, formatDateTime, relativeTime, emptyState, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const params = new URLSearchParams(location.search);
const state = { action: params.get('action') || '', page: Number(params.get('page')) || 1, actionsLoaded: false };

const TARGET_LINK = {
    company: (id) => `/admin/companies/${id}`,
    user: () => '/admin/users',
    jobvacancy: () => '/admin/jobs',
    jobsource: () => '/admin/sources',
};

function syncUrl() {
    const p = new URLSearchParams();
    if (state.action) p.set('action', state.action);
    if (state.page > 1) p.set('page', state.page);
    history.replaceState(null, '', p.toString() ? `?${p}` : location.pathname);
}

async function load() {
    syncUrl();
    $('au-rows').innerHTML = tableMessage(5, '<div class="ws-skel h-10"></div>');
    try {
        const res = await admin.auditLogs({ action: state.action || undefined, page: state.page, per_page: 25 });
        if (!state.actionsLoaded) {
            $('au-action').insertAdjacentHTML('beforeend', Object.entries(res.meta?.actions || {}).map(([k, v]) => `<option value="${esc(k)}">${esc(v)}</option>`).join(''));
            $('au-action').value = state.action;
            state.actionsLoaded = true;
        }
        const list = res.data || [];
        if (!list.length) {
            $('au-rows').innerHTML = tableMessage(5, emptyState({
                iconName: 'scroll-text',
                title: state.action ? 'Belum ada tindakan jenis ini' : 'Belum ada tindakan admin',
                text: 'Keputusan verifikasi, penangguhan, moderasi, dan sinkronisasi akan tercatat di sini.',
            }));
            $('au-pager').innerHTML = '';
            return;
        }
        $('au-rows').innerHTML = list.map((a) => {
            const link = a.target_id && TARGET_LINK[a.target_type] ? TARGET_LINK[a.target_type](a.target_id) : null;
            return `
            <tr>
                <td class="whitespace-nowrap"><span class="block">${relativeTime(a.created_at)}</span><span class="block ws-meta">${formatDateTime(a.created_at)}</span></td>
                <td>${esc(a.actor?.name || 'Sistem')}</td>
                <td class="font-medium text-[var(--ws-text)]">${esc(a.action_label)}</td>
                <td class="max-w-[16rem]">${a.target_label ? (link ? `<a href="${link}" class="text-[var(--ws-info)] hover:underline">${esc(a.target_label)}</a>` : esc(a.target_label)) : '<span class="ws-meta">-</span>'}</td>
                <td class="max-w-[22rem] text-sm">${a.reason ? esc(a.reason) : '<span class="ws-meta">-</span>'}</td>
            </tr>`;
        }).join('');
        $('au-pager').innerHTML = pager(res.meta?.pagination, 'catatan');
        hydrateIcons($('au-pager'));
    } catch (err) {
        $('au-rows').innerHTML = tableMessage(5, emptyState({ iconName: 'circle-alert', title: 'Log audit gagal dimuat', text: esc(errorMessage(err)) }));
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-audit' });
    if (!ctx) return;
    $('au-action').addEventListener('change', (e) => { state.action = e.target.value; state.page = 1; load(); });
    document.addEventListener('click', (e) => {
        const pg = e.target.closest('[data-goto-page]');
        if (pg && !pg.disabled) { state.page = Number(pg.dataset.gotoPage); load(); }
    });
    await load();
});
