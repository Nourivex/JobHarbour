/**
 * Admin Portal: laporan pengguna dan banding recruiter (GET/PATCH /api/admin/reports).
 * Menutup laporan tidak otomatis memoderasi lowongan; tombol gabungan menjalankan moderasi lalu
 * menutup laporan sebagai dua tindakan terpisah sehingga keduanya tercatat di Log Audit.
 */
import { admin, toast } from '../../api.js';
import { bootAdmin, renderNavCounts } from '../../admin/shell.js';
import { confirmAction, pager, flash } from '../../admin/ui.js';
import { openDialog, bindDialog } from '../../recruiter/components.js';
import { esc, icon, hydrateIcons, relativeTime, formatDate, emptyState, skeletonRows, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const params = new URLSearchParams(location.search);
const state = {
    status: ['active', 'resolved', 'dismissed'].includes(params.get('status')) ? params.get('status') : 'active',
    kind: ['report', 'appeal'].includes(params.get('kind')) ? params.get('kind') : '',
    page: 1,
    items: [],
    current: null,
};

const STATUS = {
    open: ['Baru', 'warning'],
    in_review: ['Sedang ditinjau', 'info'],
    resolved: ['Selesai', 'success'],
    dismissed: ['Ditolak', 'muted'],
};

const kindBadge = (r) => (r.kind === 'appeal'
    ? `<span class="ws-pill ws-pill-info">${icon('rotate-ccw', 'w-3.5 h-3.5')}Banding</span>`
    : `<span class="ws-pill ws-pill-danger">${icon('circle-alert', 'w-3.5 h-3.5')}Laporan</span>`);
const statusBadge = (s) => `<span class="ws-pill ws-pill-${STATUS[s][1]}">${STATUS[s][0]}</span>`;
const targetLink = (r) => (r.target_type === 'job' ? `/admin/jobs?id=${r.target_id}` : `/admin/companies/${r.target_id}`);

function syncUrl() {
    const p = new URLSearchParams();
    if (state.status !== 'active') p.set('status', state.status);
    if (state.kind) p.set('kind', state.kind);
    history.replaceState(null, '', p.toString() ? `?${p}` : location.pathname);
}

async function load() {
    syncUrl();
    document.querySelectorAll('#rp-tabs [data-status]').forEach((t) => t.setAttribute('aria-selected', String(t.dataset.status === state.status)));
    $('rp-list').innerHTML = `<li class="p-4">${skeletonRows(3, 'h-16')}</li>`;
    try {
        const res = await admin.reports({ status: state.status, kind: state.kind || undefined, page: state.page, per_page: 20 });
        const c = res.meta?.counts || {};
        document.querySelector('[data-count="active"]').textContent = (c.open || 0) + (c.in_review || 0);
        document.querySelector('[data-count="resolved"]').textContent = c.resolved || 0;
        document.querySelector('[data-count="dismissed"]').textContent = c.dismissed || 0;
        renderNavCounts({ reports_open: (c.open || 0) + (c.in_review || 0) });
        state.items = res.data || [];
        if (!state.items.length) {
            $('rp-list').innerHTML = `<li class="p-5">${emptyState({
                iconName: state.status === 'active' ? 'check-check' : 'inbox',
                title: state.status === 'active' ? 'Tidak ada laporan yang menunggu' : 'Belum ada data',
                text: state.status === 'active' ? 'Laporan pengguna dan banding recruiter akan muncul di sini.' : '',
            })}</li>`;
            $('rp-pager').innerHTML = '';
            hydrateIcons($('rp-list'));
            return;
        }
        $('rp-list').innerHTML = state.items.map((r) => `
            <li data-row="${r.id}">
                <button type="button" class="adm-row-btn flex items-start gap-3 px-4 sm:px-5 py-3.5 hover:bg-[var(--ws-surface-2)] transition-colors" data-open="${r.id}">
                    <span class="min-w-0 flex-1">
                        <span class="flex flex-wrap items-center gap-1.5 mb-1">${kindBadge(r)}${statusBadge(r.status)}<span class="ws-meta">${esc(r.category_label)}</span></span>
                        <span class="block font-semibold text-[var(--ws-text)] truncate">${esc(r.target?.title || 'Objek sudah dihapus')}</span>
                        <span class="block ws-meta truncate">"${esc(r.description)}"</span>
                    </span>
                    <span class="text-right shrink-0">
                        <span class="block text-sm">${relativeTime(r.created_at)}</span>
                        <span class="hidden sm:block ws-meta max-w-[12rem] truncate">${esc(r.reporter?.name || '-')}</span>
                        ${r.related_reports ? `<span class="block text-[12px] font-semibold text-[var(--ws-warning)]">+${r.related_reports} lain</span>` : ''}
                    </span>
                </button>
            </li>`).join('');
        $('rp-pager').innerHTML = pager(res.meta?.pagination, 'laporan');
        hydrateIcons($('rp-list'));
        hydrateIcons($('rp-pager'));
    } catch (err) {
        $('rp-list').innerHTML = `<li class="p-5">${emptyState({ iconName: 'circle-alert', title: 'Laporan gagal dimuat', text: esc(errorMessage(err)) })}</li>`;
    }
}

function renderDrawer(r) {
    state.current = r;
    $('rp-d-badges').innerHTML = kindBadge(r) + statusBadge(r.status);
    $('rp-d-title').textContent = r.target?.title || 'Objek sudah dihapus';
    $('rp-d-sub').textContent = [r.target?.subtitle, r.target_type === 'job' ? 'Lowongan' : 'Perusahaan'].filter(Boolean).join(' · ');
    const removed = r.target?.moderation_status === 'removed';
    $('rp-d-body').innerHTML = `
        <dl class="adm-dl">
            <dt>Jenis</dt><dd>${esc(r.category_label)}</dd>
            <dt>Dari</dt><dd>${esc(r.reporter?.name || '-')} <span class="ws-meta">${esc(r.reporter?.email || '')}</span></dd>
            <dt>Diterima</dt><dd>${formatDate(r.created_at, true)}</dd>
            ${r.target ? `<dt>Objek</dt><dd><a href="${targetLink(r)}" class="text-[var(--ws-info)] hover:underline">${esc(r.target.title)}</a>${removed ? ' <span class="ws-pill ws-pill-danger">Diturunkan</span>' : ''}</dd>` : ''}
            ${r.related_reports ? `<dt>Laporan lain</dt><dd>${r.related_reports} laporan aktif untuk objek yang sama</dd>` : ''}
        </dl>
        <section>
            <h3 class="ws-h3 mb-1.5">${r.kind === 'appeal' ? 'Penjelasan recruiter' : 'Isi laporan'}</h3>
            <p class="text-sm text-[var(--ws-text-2)] whitespace-pre-line rounded-xl bg-[var(--ws-surface-2)] p-3">${esc(r.description)}</p>
        </section>
        ${r.resolution ? `<section><h3 class="ws-h3 mb-1.5">Hasil pemeriksaan</h3><p class="text-sm">${esc(r.resolution)}</p><p class="ws-meta mt-1">${r.handler ? `Oleh ${esc(r.handler.name)}` : ''}${r.handled_at ? ` · ${formatDate(r.handled_at, true)}` : ''}</p></section>` : ''}`;

    const active = ['open', 'in_review'].includes(r.status);
    const jobTarget = r.target_type === 'job' && r.target;
    let combo = '';
    if (active && jobTarget && r.kind === 'report' && !removed) combo = `<button type="button" class="ws-btn ws-btn-danger" data-act="remove-resolve">${icon('ban')}Turunkan lowongan</button>`;
    if (active && jobTarget && r.kind === 'appeal' && removed) combo = `<button type="button" class="ws-btn ws-btn-primary" data-act="restore-resolve">${icon('rotate-ccw')}Pulihkan lowongan</button>`;
    $('rp-d-foot').innerHTML = active
        ? `
            ${r.status === 'open' ? `<button type="button" class="ws-btn ws-btn-ghost mr-auto" data-act="in_review">${icon('eye')}Tandai ditinjau</button>` : '<span class="mr-auto"></span>'}
            <button type="button" class="ws-btn ws-btn-secondary" data-act="dismissed">${r.kind === 'appeal' ? 'Tolak banding' : 'Tolak laporan'}</button>
            ${combo || `<button type="button" class="ws-btn ws-btn-primary" data-act="resolved">${icon('check')}Selesaikan</button>`}`
        : '<button type="button" class="ws-btn ws-btn-ghost" data-dialog-close>Tutup</button>';
    hydrateIcons($('rp-drawer'));
}

function openReport(id) {
    const r = state.items.find((x) => String(x.id) === String(id));
    if (!r) return;
    renderDrawer(r);
    openDialog(bindDialog($('rp-drawer')));
}

async function afterAction(id, message) {
    toast(message, 'success');
    $('rp-drawer').close();
    await load();
    flash(document.querySelector(`#rp-list [data-row="${id}"]`));
}

function act(action) {
    const r = state.current;
    const title = r.target?.title || 'objek';
    if (action === 'in_review') {
        admin.updateReport(r.id, { status: 'in_review' })
            .then(() => afterAction(r.id, 'Laporan ditandai sedang ditinjau.'))
            .catch((err) => toast(errorMessage(err), 'error'));
        return;
    }
    const configs = {
        resolved: {
            title: 'Selesaikan laporan?', impact: 'Laporan ditutup dengan hasil pemeriksaan di bawah. Lowongan tidak diubah; gunakan moderasi bila perlu.',
            confirmText: 'Selesaikan', tone: 'primary', placeholder: 'contoh: Sudah dihubungi recruiter, informasi gaji diperbaiki.',
            run: (reason) => admin.updateReport(r.id, { status: 'resolved', resolution: reason }), done: 'Laporan diselesaikan.',
        },
        dismissed: {
            title: r.kind === 'appeal' ? 'Tolak banding?' : 'Tolak laporan?',
            impact: r.kind === 'appeal' ? 'Lowongan tetap diturunkan. Recruiter bisa mengajukan banding baru setelah memperbaiki lagi.' : 'Laporan dianggap tidak terbukti dan ditutup.',
            confirmText: 'Tolak', tone: 'danger', placeholder: 'contoh: Informasi lowongan sesuai dengan dokumen perusahaan.',
            run: (reason) => admin.updateReport(r.id, { status: 'dismissed', resolution: reason }), done: 'Ditolak dan tercatat di Log Audit.',
        },
        'remove-resolve': {
            title: 'Turunkan lowongan dan selesaikan?', impact: `"${esc(title)}" hilang dari katalog dan tidak bisa dilamar. Alasan dikirim ke recruiter, lalu laporan ditutup.`,
            confirmText: 'Turunkan dan selesaikan', tone: 'danger', placeholder: 'contoh: Terbukti meminta biaya pendaftaran kepada pelamar.',
            run: async (reason) => {
                await admin.moderateJob(r.target_id, { action: 'remove', reason });
                await admin.updateReport(r.id, { status: 'resolved', resolution: reason });
            },
            done: 'Lowongan diturunkan dan laporan diselesaikan.',
        },
        'restore-resolve': {
            title: 'Pulihkan lowongan dan selesaikan?', impact: `"${esc(title)}" kembali mengikuti statusnya semula, lalu banding ditutup.`,
            confirmText: 'Pulihkan dan selesaikan', tone: 'primary', placeholder: 'contoh: Gaji sudah diperbaiki sesuai kontrak resmi.',
            run: async (reason) => {
                await admin.moderateJob(r.target_id, { action: 'restore', reason });
                await admin.updateReport(r.id, { status: 'resolved', resolution: reason });
            },
            done: 'Lowongan dipulihkan dan banding diselesaikan.',
        },
    };
    const c = configs[action];
    confirmAction({
        title: c.title, subtitle: `${r.kind === 'appeal' ? 'Banding' : 'Laporan'} · ${title}`, impact: c.impact,
        confirmText: c.confirmText, tone: c.tone, reason: 'required', placeholder: c.placeholder,
        onConfirm: async (reason) => { await c.run(reason); await afterAction(r.id, c.done); },
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-reports' });
    if (!ctx) return;
    $('rp-kind').value = state.kind;
    $('rp-kind').addEventListener('change', (e) => { state.kind = e.target.value; state.page = 1; load(); });
    $('rp-tabs').addEventListener('click', (e) => {
        const t = e.target.closest('[data-status]');
        if (!t) return;
        state.status = t.dataset.status;
        state.page = 1;
        load();
    });
    document.addEventListener('click', (e) => {
        const pg = e.target.closest('[data-goto-page]');
        if (pg && !pg.disabled) { state.page = Number(pg.dataset.gotoPage); load(); return; }
        const open = e.target.closest('[data-open]');
        if (open) { openReport(open.dataset.open); return; }
        const a = e.target.closest('#rp-d-foot [data-act]');
        if (a && state.current) act(a.dataset.act);
    });
    await load();
});
