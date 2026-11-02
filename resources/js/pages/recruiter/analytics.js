import { bootShell, setNavCounts } from '../../recruiter/shell.js';
import { data, appsOfJob, averageScore } from '../../recruiter/data.js';
import { initMenus } from '../../recruiter/components.js';
import { esc, icon, hydrateIcons, STAGE, stageOf, scoreInfo, emptyState, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const DAY = 86400000;
const FUNNEL = ['screening', 'assessment', 'interview', 'offering', 'hired'];
const state = { jobs: [], apps: [], job: 'all', period: 'all' };

/** Tahap terjauh yang pernah dicapai lamaran (penolakan tetap dihitung di tahap terakhir sebelum ditolak). */
function reached(app) {
    const stages = (app.pipeline_timeline?.stages || []).map((s) => s.stage);
    const set = new Set(['screening', ...stages.filter((s) => s !== 'rejected')]);
    if (stageOf(app) === 'hired') set.add('hired');
    return set;
}

/** Tahap tempat kandidat ditolak: entri terakhir sebelum entri "rejected". */
function rejectedAt(app) {
    const stages = app.pipeline_timeline?.stages || [];
    const i = stages.findIndex((s) => s.stage === 'rejected');
    if (i < 0) return null;
    return i > 0 ? stages[i - 1].stage : 'screening';
}

function scope() {
    let list = state.job === 'all' ? state.apps : appsOfJob(state.apps, state.job);
    if (state.period !== 'all') {
        const from = Date.now() - Number(state.period) * DAY;
        list = list.filter((a) => new Date(a.created_at).getTime() >= from);
    }
    return list;
}

const pct = (n, d) => (d ? Math.round((n / d) * 100) : null);
const avg = (xs) => (xs.length ? xs.reduce((s, x) => s + x, 0) / xs.length : null);
const days = (from, to) => (new Date(to) - new Date(from)) / DAY;

function stat(label, value, sub, iconName) {
    return `<div class="ws-stat"><p class="ws-kpi-label">${icon(iconName)}${label}</p><p class="ws-stat-value">${value}</p><p class="ws-kpi-sub">${sub}</p></div>`;
}

function render() {
    const apps = scope();
    if (!apps.length) {
        $('an-root').innerHTML = `<div class="ws-card">${emptyState({
            iconName: 'chart-column',
            title: state.apps.length ? 'Tidak ada lamaran pada pilihan ini' : 'Belum ada data untuk dianalisis',
            text: state.apps.length ? 'Ubah lowongan atau periode untuk melihat angka lain.' : 'Analitik muncul setelah pelamar mengirim lamaran ke lowonganmu.',
        })}</div>`;
        hydrateIcons($('an-root'));
        return;
    }

    const reach = Object.fromEntries(FUNNEL.map((s) => [s, 0]));
    apps.forEach((a) => reached(a).forEach((s) => { if (s in reach) reach[s] += 1; }));
    const hired = apps.filter((a) => stageOf(a) === 'hired');
    const rejected = apps.filter((a) => stageOf(a) === 'rejected');
    const active = apps.length - hired.length - rejected.length;

    // Waktu: lamaran masuk sampai keputusan pertama, dan sampai diterima.
    // Titik awal memakai tahap pertama di linimasa (bukan created_at) agar semua
    // selisih dihitung dari sumber waktu yang sama.
    const startOf = (a, st) => st[0]?.entered_at || a.created_at;
    const firstMove = apps.map((a) => {
        const st = a.pipeline_timeline?.stages || [];
        return st.length > 1 ? days(startOf(a, st), st[1].entered_at) : null;
    }).filter((x) => x !== null && x >= 0);
    const toHire = hired.map((a) => {
        const st = a.pipeline_timeline?.stages || [];
        const last = st[st.length - 1];
        return last?.completed_at ? days(startOf(a, st), last.completed_at) : null;
    }).filter((x) => x !== null && x >= 0);
    const fmtDays = (x) => (x === null ? '-' : x < 1 ? '< 1 hari' : `${Math.round(x)} hari`);

    const score = averageScore(apps);
    const top = Math.max(...FUNNEL.map((s) => reach[s]), 1);

    // Distribusi skor
    const bands = { excellent: 0, good: 0, under: 0 };
    apps.forEach((a) => { bands[scoreInfo(a.match_score).key] += 1; });

    // Tren mingguan 8 minggu terakhir
    const weekStart = (d) => { const x = new Date(d); x.setHours(0, 0, 0, 0); x.setDate(x.getDate() - ((x.getDay() + 6) % 7)); return x.getTime(); };
    const thisWeek = weekStart(Date.now());
    const weeks = Array.from({ length: 8 }, (_, i) => ({ start: thisWeek - (7 - i) * 7 * DAY, n: 0 }));
    apps.forEach((a) => {
        const w = weeks.find((x) => x.start === weekStart(a.created_at));
        if (w) w.n += 1;
    });
    const wMax = Math.max(...weeks.map((w) => w.n), 1);

    // Penolakan per tahap
    const rejBy = {};
    rejected.forEach((a) => { const s = rejectedAt(a) || 'screening'; rejBy[s] = (rejBy[s] || 0) + 1; });

    const jobsInScope = state.job === 'all' ? state.jobs.filter((j) => appsOfJob(apps, j.id).length) : state.jobs.filter((j) => String(j.id) === state.job);

    $('an-root').innerHTML = `
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-3" aria-label="Ringkasan">
            ${stat('Total lamaran', apps.length, `${active} masih diproses`, 'users')}
            ${stat('Rata-rata skor', score === null ? '-' : `<span style="color:${scoreInfo(score).color}">${score}%</span>`, score === null ? '' : scoreInfo(score).label, 'target')}
            ${stat('Waktu ke keputusan pertama', fmtDays(avg(firstMove)), firstMove.length ? `Rata-rata dari ${firstMove.length} lamaran` : 'Belum ada lamaran yang diproses', 'timer')}
            ${stat('Waktu sampai diterima', fmtDays(avg(toHire)), toHire.length ? `Rata-rata dari ${toHire.length} kandidat diterima` : 'Belum ada yang diterima', 'user-check')}
        </section>

        <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px] items-start">
            <section class="ws-card ws-card-body" aria-labelledby="an-funnel-title">
                <h2 id="an-funnel-title" class="ws-h2">Corong seleksi</h2>
                <p class="ws-meta mt-0.5 mb-5">Jumlah lamaran yang pernah mencapai setiap tahap, dan persentase yang lolos dari tahap sebelumnya.</p>
                <div class="ws-funnel">
                    ${FUNNEL.map((s, i) => {
                        const conv = i === 0 ? null : pct(reach[s], reach[FUNNEL[i - 1]]);
                        return `
                        <div class="ws-funnel-row">
                            <span class="text-sm font-medium inline-flex items-center gap-2"><span class="ws-dot" style="--c:${STAGE[s].color}"></span>${STAGE[s].label}</span>
                            <div class="ws-funnel-track">${reach[s] ? `<div class="ws-funnel-bar" style="width:${Math.max((reach[s] / top) * 100, 6)}%;background:${STAGE[s].color}">${reach[s]}</div>` : '<span class="flex items-center h-full px-3 text-[12.5px] text-[var(--ws-muted)]">0</span>'}</div>
                            <span class="ws-conv">${i === 0 ? '100%' : conv === null ? '-' : `${conv}% lolos`}</span>
                        </div>`;
                    }).join('')}
                </div>
                <div class="mt-6 grid sm:grid-cols-3 gap-3 text-sm">
                    <div class="rounded-xl bg-[var(--ws-surface-2)] p-3"><p class="ws-meta">Diterima</p><p class="mt-1 text-lg font-bold ws-num" style="color:${STAGE.hired.color}">${hired.length} <span class="text-sm font-medium text-[var(--ws-muted)]">(${pct(hired.length, apps.length) ?? 0}%)</span></p></div>
                    <div class="rounded-xl bg-[var(--ws-surface-2)] p-3"><p class="ws-meta">Ditolak</p><p class="mt-1 text-lg font-bold ws-num" style="color:${STAGE.rejected.color}">${rejected.length} <span class="text-sm font-medium text-[var(--ws-muted)]">(${pct(rejected.length, apps.length) ?? 0}%)</span></p></div>
                    <div class="rounded-xl bg-[var(--ws-surface-2)] p-3"><p class="ws-meta">Masih diproses</p><p class="mt-1 text-lg font-bold ws-num">${active}</p></div>
                </div>
                ${rejected.length ? `<p class="ws-meta mt-4">Penolakan terbanyak di tahap ${Object.entries(rejBy).sort((a, b) => b[1] - a[1]).map(([s, n]) => `${STAGE[s]?.label || s} (${n})`).join(', ')}.</p>` : ''}
            </section>

            <div class="space-y-6">
                <section class="ws-card ws-card-body" aria-labelledby="an-trend-title">
                    <h2 id="an-trend-title" class="ws-h2">Lamaran per minggu</h2>
                    <p class="ws-meta mt-0.5">8 minggu terakhir, minggu ini disorot.</p>
                    <div class="ws-bars" role="img" aria-label="${weeks.map((w) => `${new Date(w.start).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}: ${w.n}`).join(', ')}">
                        ${weeks.map((w, i) => `
                            <div>
                                <span class="ws-bar-value">${w.n || ''}</span>
                                <span class="ws-bar ${i === weeks.length - 1 ? 'is-current' : ''}" style="height:${w.n ? Math.max((w.n / wMax) * 100, 6) : 2}%"></span>
                                <span class="ws-bar-label">${new Date(w.start).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' })}</span>
                            </div>`).join('')}
                    </div>
                </section>
                <section class="ws-card ws-card-body" aria-labelledby="an-score-title">
                    <h2 id="an-score-title" class="ws-h2">Sebaran skor kecocokan</h2>
                    <p class="ws-meta mt-0.5 mb-4">Membantu menilai apakah syarat skill lowongan terlalu ketat atau terlalu longgar.</p>
                    <ul class="space-y-3 text-sm">
                        ${[['excellent', '80% ke atas'], ['good', '50 sampai 79%'], ['under', 'Di bawah 50%']].map(([k, label]) => {
                            const color = { excellent: '#15803D', good: '#1B65A6', under: '#B45309' }[k];
                            return `<li>
                                <div class="flex justify-between mb-1"><span class="inline-flex items-center gap-2"><span class="ws-dot" style="--c:${color}"></span>${label}</span><span class="ws-num font-semibold">${bands[k]} <span class="ws-meta">(${pct(bands[k], apps.length)}%)</span></span></div>
                                <span class="block h-2 rounded-full bg-[var(--ws-surface-2)] overflow-hidden"><span class="block h-full rounded-full" style="width:${pct(bands[k], apps.length)}%;background:${color}"></span></span>
                            </li>`;
                        }).join('')}
                    </ul>
                </section>
            </div>
        </div>

        <section class="ws-card mt-6" aria-labelledby="an-jobs-title">
            <div class="ws-card-head"><div><h2 id="an-jobs-title" class="ws-h2">Performa per lowongan</h2><p class="ws-meta mt-0.5">Dalam periode yang dipilih.</p></div></div>
            <div class="ws-card-body">
                <div class="ws-table-wrap">
                    <table class="ws-table">
                        <thead><tr><th>Lowongan</th><th class="num">Lamaran</th><th class="num">Rata-rata skor</th><th class="num">Sampai wawancara</th><th class="num">Diterima / kuota</th></tr></thead>
                        <tbody>
                            ${jobsInScope.map((j) => {
                                const list = appsOfJob(apps, j.id);
                                const a = averageScore(list);
                                const iv = list.filter((x) => reached(x).has('interview')).length;
                                const h = list.filter((x) => stageOf(x) === 'hired').length;
                                return `<tr>
                                    <td class="min-w-[200px]"><a href="/recruiter/jobs/${j.id}" class="ws-row-link">${esc(j.title)}</a><p class="ws-meta">${esc(j.location || '-')}</p></td>
                                    <td class="num font-semibold text-[var(--ws-text)]">${list.length}</td>
                                    <td class="num">${a === null ? '-' : `<span class="font-semibold" style="color:${scoreInfo(a).color}">${a}%</span>`}</td>
                                    <td class="num">${iv} <span class="ws-meta">(${pct(iv, list.length) ?? 0}%)</span></td>
                                    <td class="num">${h}/${Number(j.quota) || 0}</td>
                                </tr>`;
                            }).join('') || '<tr><td colspan="5" class="ws-meta">Tidak ada lowongan dengan lamaran.</td></tr>'}
                        </tbody>
                    </table>
                </div>
            </div>
        </section>`;
    hydrateIcons($('an-root'));
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'analytics' });
    if (!ctx) return;
    initMenus();

    $('an-job').addEventListener('change', (e) => { state.job = e.target.value; render(); });
    $('an-period').addEventListener('change', (e) => { state.period = e.target.value; render(); });

    try {
        [state.jobs, state.apps] = await Promise.all([data.jobs(), data.applications()]);
        $('an-job').innerHTML = '<option value="all">Semua lowongan</option>' + state.jobs
            .filter((j) => appsOfJob(state.apps, j.id).length)
            .map((j) => `<option value="${j.id}">${esc(j.title)}</option>`).join('');
        setNavCounts(state.apps);
        render();
    } catch (err) {
        $('an-root').innerHTML = `<div class="ws-card">${emptyState({ iconName: 'circle-alert', title: 'Analitik gagal dimuat', text: esc(errorMessage(err)), action: '<button type="button" class="ws-btn ws-btn-secondary" onclick="location.reload()">Muat ulang</button>' })}</div>`;
    }
});
