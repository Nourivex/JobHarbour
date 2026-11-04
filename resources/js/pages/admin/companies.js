/**
 * Admin Portal: daftar perusahaan. Pencarian, filter status, dan halaman dilakukan di server (GET /api/admin/companies).
 */
import { admin } from '../../api.js';
import { bootAdmin } from '../../admin/shell.js';
import { pager, tableMessage } from '../../admin/ui.js';
import { debounce } from '../../recruiter/components.js';
import { esc, hydrateIcons, companyLogo, verifyBadge, VERIFY, formatDate, emptyState, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const params = new URLSearchParams(location.search);
const TABS = [['', 'Semua'], ['pending', 'Menunggu'], ['revision_required', 'Perlu revisi'], ['verified', 'Terverifikasi'], ['unverified', 'Belum diverifikasi'], ['suspended', 'Ditangguhkan'], ['rejected', 'Ditolak']];
const state = { q: params.get('q') || '', status: params.get('status') || '', page: Number(params.get('page')) || 1, counts: {} };

function syncUrl() {
    const p = new URLSearchParams();
    if (state.q) p.set('q', state.q);
    if (state.status) p.set('status', state.status);
    if (state.page > 1) p.set('page', state.page);
    history.replaceState(null, '', p.toString() ? `?${p}` : location.pathname);
}

function renderTabs() {
    const total = Object.values(state.counts).reduce((s, n) => s + Number(n), 0);
    $('co-tabs').innerHTML = TABS.map(([key, label]) => {
        const n = key ? state.counts[key] || 0 : total;
        return `<button type="button" role="tab" class="ws-tab" data-status="${key}" aria-selected="${state.status === key}">${label} <span class="ws-count">${n}</span></button>`;
    }).join('');
}

async function load() {
    syncUrl();
    $('co-rows').innerHTML = tableMessage(5, '<div class="ws-skel h-10"></div>');
    try {
        const res = await admin.companies({ q: state.q || undefined, status: state.status || undefined, page: state.page, per_page: 20 });
        state.counts = res.meta?.counts || {};
        renderTabs();
        const list = res.data || [];
        if (!list.length) {
            $('co-rows').innerHTML = tableMessage(5, emptyState({
                iconName: 'building-2',
                title: state.q || state.status ? 'Tidak ada perusahaan yang cocok' : 'Belum ada perusahaan terdaftar',
                text: state.q || state.status ? 'Ubah kata kunci atau status.' : 'Perusahaan muncul setelah recruiter melengkapi profil perusahaannya.',
            }));
            $('co-pager').innerHTML = '';
            return;
        }
        $('co-rows').innerHTML = list.map((c) => `
            <tr class="cursor-pointer" data-href="/admin/companies/${c.id}">
                <td>
                    <a href="/admin/companies/${c.id}" class="flex items-center gap-3 min-w-0">
                        ${companyLogo(c, 'w-9 h-9 text-xs')}
                        <span class="min-w-0">
                            <span class="block font-semibold text-[var(--ws-text)] truncate">${esc(c.name)}</span>
                            <span class="block ws-meta truncate">${esc([c.industry, c.city].filter(Boolean).join(' · ') || '-')}</span>
                        </span>
                    </a>
                </td>
                <td data-label="Pemilik"><span class="block truncate max-w-[14rem]">${esc(c.owner?.name || '-')}</span><span class="block ws-meta truncate max-w-[14rem]">${esc(c.owner?.email || '')}</span></td>
                <td data-label="Status">${verifyBadge(c.verification_status)}</td>
                <td data-label="Lowongan aktif" class="num">${c.jobs_open}<span class="ws-meta"> / ${c.jobs_total}</span></td>
                <td data-label="Terdaftar" class="whitespace-nowrap">${formatDate(c.created_at)}</td>
            </tr>`).join('');
        $('co-pager').innerHTML = pager(res.meta?.pagination, 'perusahaan');
        hydrateIcons($('co-pager'));
    } catch (err) {
        $('co-rows').innerHTML = tableMessage(5, emptyState({ iconName: 'circle-alert', title: 'Daftar perusahaan gagal dimuat', text: esc(errorMessage(err)) }));
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-companies' });
    if (!ctx) return;
    if (!VERIFY[state.status]) state.status = '';

    $('co-q').value = state.q;
    $('co-q').addEventListener('input', debounce((e) => { state.q = e.target.value.trim(); state.page = 1; load(); }, 250));
    $('co-tabs').addEventListener('click', (e) => {
        const t = e.target.closest('[data-status]');
        if (!t) return;
        state.status = t.dataset.status;
        state.page = 1;
        load();
    });
    document.addEventListener('click', (e) => {
        const pg = e.target.closest('[data-goto-page]');
        if (pg && !pg.disabled) { state.page = Number(pg.dataset.gotoPage); load(); return; }
        const row = e.target.closest('tr[data-href]');
        if (row && !e.target.closest('a')) window.location.href = row.dataset.href;
    });
    await load();
});
