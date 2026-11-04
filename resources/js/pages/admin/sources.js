/**
 * Admin Portal: pemantauan sumber lowongan agregator (GET /api/aggregator/summary).
 * Satu-satunya aksi adalah sinkron manual dengan konfirmasi (POST /api/aggregator/sync, dicatat di Log Audit,
 * dibatasi 6 kali per menit). Tidak ada pengaturan API key atau kredensial di sini.
 */
import { admin, toast } from '../../api.js';
import { bootAdmin, renderNavCounts } from '../../admin/shell.js';
import { confirmAction, flash } from '../../admin/ui.js';
import { esc, icon, hydrateIcons, relativeTime, formatDateTime, emptyState, skeletonRows, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
let sources = [];

function health(s) {
    if (!s.is_enabled) return '<span class="ws-pill ws-pill-muted">Nonaktif</span>';
    if (!s.last_fetched_at) return '<span class="ws-pill ws-pill-neutral">Belum pernah diambil</span>';
    return s.health_status === 'degraded'
        ? `<span class="ws-pill ws-pill-danger">${icon('circle-alert', 'w-3.5 h-3.5')}Bermasalah</span>`
        : `<span class="ws-pill ws-pill-success">${icon('circle-check', 'w-3.5 h-3.5')}Sehat</span>`;
}

function render(d) {
    const v = d.vacancies_breakdown || {};
    $('src-summary').innerHTML = `
        <div class="adm-health">
            <span><b class="ws-num">${d.enabled_sources}</b> dari ${d.total_sources} sumber aktif</span>
            <span><b class="ws-num">${v.active ?? 0}</b> lowongan aktif di database</span>
            <span><b class="ws-num">${v.stale ?? 0}</b> basi</span>
            <span><b class="ws-num">${v.expired ?? 0}</b> kedaluwarsa atau ditutup</span>
            <span><b class="ws-num">${v.demo_records ?? 0}</b> data demo</span>
        </div>`;

    if (!sources.length) {
        $('src-list').innerHTML = `<div class="ws-card ws-card-body">${emptyState({ iconName: 'globe', title: 'Belum ada sumber lowongan', text: 'Sumber ditambahkan developer lewat seeder atau konfigurasi backend.' })}</div>`;
        return;
    }
    $('src-list').innerHTML = `<div class="ws-card"><ul class="divide-y divide-[var(--ws-border)]">${sources.map((s) => `
        <li class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-start gap-3">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-semibold">${esc(s.name)}</p>
                    ${health(s)}
                </div>
                <p class="ws-meta mt-0.5">${esc([s.company_name, s.industry, s.coverage_regions].filter(Boolean).join(' · '))}</p>
                <p class="text-sm mt-2 text-[var(--ws-text-2)]">
                    ${s.last_success_at ? `Berhasil diambil <span title="${formatDateTime(s.last_success_at)}">${relativeTime(s.last_success_at)}</span>` : 'Belum pernah berhasil diambil'}
                    · ${s.total_vacancies_active ?? 0} lowongan aktif dari ${s.total_vacancies_discovered ?? 0} yang pernah ditemukan
                </p>
                ${s.health_status === 'degraded' && s.last_error_message ? `
                    <div class="ws-callout ws-callout-warning mt-2">${icon('circle-alert', 'w-4 h-4')}<p><b>Gagal ${relativeTime(s.last_error_at)}:</b> ${esc(s.last_error_message)}</p></div>` : ''}
                <div class="src-logs mt-3" id="src-logs-${s.id}" hidden></div>
            </div>
            <div class="flex flex-wrap gap-1.5 shrink-0">
                ${s.is_enabled ? `<button type="button" class="ws-btn ws-btn-secondary ws-btn-sm" data-sync="${esc(s.slug)}">${icon('refresh-cw')}Sinkron</button>` : ''}
                <button type="button" class="ws-btn ws-btn-ghost ws-btn-sm" data-logs="${s.id}" aria-expanded="false" aria-controls="src-logs-${s.id}">${icon('clock')}Riwayat</button>
                <button type="button" class="ws-btn ${s.is_enabled ? 'ws-btn-danger-ghost' : 'ws-btn-secondary'} ws-btn-sm" data-toggle="${s.id}">${icon(s.is_enabled ? 'ban' : 'rotate-ccw')}${s.is_enabled ? 'Nonaktifkan' : 'Aktifkan'}</button>
            </div>
        </li>`).join('')}</ul></div>`;
    hydrateIcons($('src-list'));
}

async function load() {
    try {
        const res = await admin.sources();
        sources = res.data?.sources || [];
        render(res.data || {});
        const degraded = sources.filter((s) => s.is_enabled && s.health_status === 'degraded').length;
        renderNavCounts({ sources_degraded: degraded });
    } catch (err) {
        $('src-list').innerHTML = `<div class="ws-card ws-card-body">${emptyState({ iconName: 'circle-alert', title: 'Data sumber gagal dimuat', text: esc(errorMessage(err)) })}</div>`;
    }
}

const LOG_STATUS = { success: ['Berhasil', 'success'], failed: ['Gagal', 'danger'], running: ['Berjalan', 'info'] };

/** Riwayat sinkronisasi dibuka di bawah sumbernya, dimuat saat pertama dibuka. */
async function toggleLogs(btn) {
    const box = $(`src-logs-${btn.dataset.logs}`);
    const open = box.hidden;
    box.hidden = !open;
    btn.setAttribute('aria-expanded', String(open));
    if (!open || box.dataset.loaded) return;
    box.innerHTML = skeletonRows(2, 'h-8');
    try {
        const logs = (await admin.sourceLogs(btn.dataset.logs)).data || [];
        box.dataset.loaded = '1';
        box.innerHTML = logs.length ? `
            <div class="ws-table-wrap rounded-xl border border-[var(--ws-border)]">
                <table class="ws-table adm-stack text-[13px]">
                    <thead><tr><th scope="col">Waktu</th><th scope="col">Hasil</th><th scope="col" class="num">Diambil</th><th scope="col" class="num">Baru</th><th scope="col" class="num">Diperbarui</th><th scope="col" class="num">Durasi</th></tr></thead>
                    <tbody>${logs.map((l) => `
                        <tr>
                            <td class="whitespace-nowrap" title="${formatDateTime(l.started_at)}">${relativeTime(l.started_at)}</td>
                            <td data-label="Hasil"><span class="ws-pill ws-pill-${(LOG_STATUS[l.status] || ['', 'neutral'])[1]}">${(LOG_STATUS[l.status] || [l.status])[0]}</span>${l.error_details ? `<span class="block ws-meta mt-1 max-w-[18rem] truncate" title="${esc(l.error_details)}">${esc(l.error_details)}</span>` : ''}</td>
                            <td data-label="Diambil" class="num">${l.vacancies_fetched ?? 0}</td>
                            <td data-label="Baru" class="num">${l.vacancies_created ?? 0}</td>
                            <td data-label="Diperbarui" class="num">${l.vacancies_updated ?? 0}</td>
                            <td data-label="Durasi" class="num">${l.duration_seconds !== null ? `${l.duration_seconds} dtk` : '-'}</td>
                        </tr>`).join('')}
                    </tbody>
                </table>
            </div>` : '<p class="ws-meta">Belum pernah disinkronkan.</p>';
    } catch (err) {
        box.innerHTML = `<p class="ws-error">${esc(errorMessage(err, 'Riwayat gagal dimuat.'))}</p>`;
    }
}

function toggleSource(id) {
    const s = sources.find((x) => x.id === id);
    const disable = s.is_enabled;
    confirmAction({
        title: disable ? 'Nonaktifkan sumber?' : 'Aktifkan sumber?',
        subtitle: s.name,
        impact: disable
            ? 'Lowongan dari sumber ini tidak akan diambil lagi saat sinkronisasi. Lowongan yang sudah ada tetap tersimpan dan ditandai basi bila tidak diperbarui.'
            : 'Sumber ikut diambil lagi pada sinkronisasi berikutnya.',
        confirmText: disable ? 'Nonaktifkan' : 'Aktifkan',
        tone: disable ? 'danger' : 'primary',
        reason: disable ? 'required' : 'none',
        placeholder: 'contoh: Situs karier sumber sedang tidak bisa diakses.',
        onConfirm: async (reason) => {
            await admin.toggleSource(id, { is_enabled: !disable, reason });
            toast(disable ? 'Sumber dinonaktifkan dan tercatat di Log Audit.' : 'Sumber diaktifkan kembali.', 'success');
            await load();
            flash(document.querySelector(`#src-list [data-toggle="${id}"]`)?.closest('li'));
        },
    });
}

function sync(slug) {
    const source = sources.find((s) => s.slug === slug);
    confirmAction({
        title: source ? `Sinkron ${source.name}?` : 'Sinkron semua sumber?',
        subtitle: source ? source.company_name || '' : `${sources.filter((s) => s.is_enabled).length} sumber aktif`,
        impact: 'Server mengambil ulang lowongan dari situs sumber, memperbarui yang berubah, dan menandai lowongan yang sudah tidak ada. Proses bisa memakan waktu beberapa detik. Dibatasi 6 kali per menit.',
        confirmText: 'Jalankan sinkron',
        tone: 'primary',
        reason: 'none',
        onConfirm: async () => {
            const res = await admin.syncSources(slug);
            toast(`${res.data?.sources_processed ?? 0} sumber selesai disinkron.`, 'success');
            await load();
        },
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-sources', loadNav: false });
    if (!ctx) return;
    $('src-list').innerHTML = skeletonRows(3, 'h-24');
    $('src-sync-all').addEventListener('click', () => sync(null));
    $('src-list').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-sync]');
        if (btn) { sync(btn.dataset.sync); return; }
        const logs = e.target.closest('[data-logs]');
        if (logs) { toggleLogs(logs); return; }
        const tg = e.target.closest('[data-toggle]');
        if (tg) toggleSource(Number(tg.dataset.toggle));
    });
    await load();
});
