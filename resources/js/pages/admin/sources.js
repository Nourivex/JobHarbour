/**
 * Admin Portal: pemantauan sumber lowongan agregator (GET /api/aggregator/summary).
 * Satu-satunya aksi adalah sinkron manual dengan konfirmasi (POST /api/aggregator/sync, dicatat di Log Audit,
 * dibatasi 6 kali per menit). Tidak ada pengaturan API key atau kredensial di sini.
 */
import { admin, toast } from '../../api.js';
import { bootAdmin, renderNavCounts } from '../../admin/shell.js';
import { confirmAction } from '../../admin/ui.js';
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
            </div>
            ${s.is_enabled ? `<button type="button" class="ws-btn ws-btn-secondary ws-btn-sm shrink-0" data-sync="${esc(s.slug)}">${icon('refresh-cw')}Sinkron</button>` : ''}
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
        if (btn) sync(btn.dataset.sync);
    });
    await load();
});
