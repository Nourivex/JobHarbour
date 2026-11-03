/**
 * Admin Portal: Ringkasan sebagai pusat kendali. Urutan menurut prioritas kerja admin:
 * kondisi platform (pita navy), keputusan yang menunggu, kesehatan pasar kerja, pertumbuhan dan neraca skill,
 * lalu tindakan admin terbaru. Semua angka dari GET /api/admin/overview.
 * Nilai null ditampilkan "belum cukup data" beserta alasannya, bukan nol. Data demo selalu diberi penanda.
 * Animasi hanya transform/opacity dan mengikuti setelan kurangi gerakan.
 */
import { admin } from '../../api.js';
import { bootAdmin, renderNavCounts } from '../../admin/shell.js';
import { esc, icon, hydrateIcons, relativeTime, formatDateTime, emptyState, skeletonRows, errorMessage, plural } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const sum = (obj) => Object.values(obj || {}).reduce((s, n) => s + Number(n || 0), 0);
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const fmt = (n, digits = 0) => Number(n).toLocaleString('id-ID', { maximumFractionDigits: digits, minimumFractionDigits: digits });
const counter = (n, digits = 0) => `<span data-count-to="${n}" data-digits="${digits}">${fmt(n, digits)}</span>`;

/** Angka naik dari nol saat pertama tampil (450 ms). Elemen: [data-count-to] dan opsional [data-digits]. */
function countUp(root) {
    root.querySelectorAll('[data-count-to]').forEach((el) => {
        const target = Number(el.dataset.countTo);
        const digits = Number(el.dataset.digits || 0);
        if (reduceMotion.matches || !target) { el.textContent = fmt(target, digits); return; }
        const start = performance.now();
        const step = (now) => {
            const t = Math.min((now - start) / 450, 1);
            el.textContent = fmt(target * (1 - (1 - t) ** 3), digits);
            if (t < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    });
}

function stat(label, n, sub) {
    return `<div class="adm-stat"><span class="adm-stat-label">${label}</span><span class="adm-stat-num">${counter(n)}</span><span class="adm-stat-sub">${sub}</span></div>`;
}

/** Pita navy: status layanan di atas, isi platform di bawah. */
function renderCommand(d, healthy, windowDays) {
    const degraded = d.sources.degraded || [];
    const roles = d.users.by_role || {};
    const companies = d.companies.by_status || {};
    const j = d.jobs;
    const newSub = (n) => (n ? ` · <b>+${n}</b> dalam ${windowDays} hari` : '');
    const demoNote = j.open_demo
        ? `<span class="adm-demo-tag" title="Lowongan contoh dari seeder, bukan lowongan nyata">DEMO</span> ${j.open_real} nyata, ${j.open_demo} data demo`
        : 'semuanya lowongan nyata';
    $('ov-command').innerHTML = `
        <div class="adm-command-status">
            <span><span class="adm-dot ${healthy ? '' : 'is-bad'}"></span>API ${healthy ? 'berjalan normal' : 'tidak merespons'}</span>
            <span><span class="adm-dot ${!d.sources.total ? 'is-idle' : (degraded.length ? 'is-bad' : '')}"></span>${d.sources.total
                ? `${d.sources.enabled} dari ${d.sources.total} sumber lowongan aktif${degraded.length ? `, ${degraded.length} bermasalah · <a href="/admin/sources">Periksa</a>` : ''}`
                : 'Belum ada sumber lowongan otomatis'}</span>
            ${j.open_demo ? `<span><span class="adm-dot is-idle"></span>Data demo ${j.demo_hidden ? 'disembunyikan dari' : 'tampil di'} katalog publik</span>` : ''}
        </div>
        <div class="adm-command-stats">
            ${stat('Pengguna', sum(roles), `${roles.applicant || 0} pelamar, ${roles.recruiter || 0} recruiter${newSub(d.users.new_30d)}`)}
            ${stat('Perusahaan', sum(companies), `${companies.verified || 0} terverifikasi${newSub(d.companies.new_30d)}`)}
            ${stat('Lowongan terbuka', j.open_real + j.open_demo, demoNote)}
            ${stat('Lamaran', d.applications.total, d.applications.total ? `total sejak awal${newSub(d.applications.new_30d)}` : 'belum ada lamaran masuk')}
        </div>`;
}

/** Antrean keputusan sebagai daftar: yang berisi dan paling mendesak di atas. */
function renderQueue(q) {
    const suspended = q.users_suspended + q.companies_suspended;
    const reports = q.reports_open + q.appeals_open;
    const items = [
        { n: reports, icon: 'flag', label: 'Laporan dan banding', href: '/admin/reports', level: q.reports_open ? 3 : (q.appeals_open ? 2 : 0),
          sub: reports ? `${plural(q.reports_open, 'laporan')}, ${plural(q.appeals_open, 'banding')}${q.oldest_report_at ? `, tertua ${relativeTime(q.oldest_report_at)}` : ''}` : 'Tidak ada yang menunggu' },
        { n: q.verifications_pending, icon: 'badge-check', label: 'Verifikasi perusahaan', href: '/admin/verifications', level: q.verifications_pending ? 2 : 0,
          sub: q.oldest_pending_at ? `Tertua diajukan ${relativeTime(q.oldest_pending_at)}` : 'Tidak ada pengajuan menunggu' },
        { n: q.sources_degraded, icon: 'globe', label: 'Sumber lowongan bermasalah', href: '/admin/sources', level: q.sources_degraded ? 3 : 0,
          sub: q.sources_degraded ? 'Pengambilan terakhir gagal' : 'Semua sumber sehat' },
        { n: q.jobs_removed, icon: 'eye-off', label: 'Lowongan diturunkan', href: '/admin/jobs?moderation=removed', level: 0,
          sub: 'Tidak tampil di katalog sampai dipulihkan' },
        { n: suspended, icon: 'user-x', label: 'Akun dan perusahaan ditangguhkan', href: '/admin/users?status=suspended', level: 0,
          sub: `${plural(q.users_suspended, 'akun')}, ${plural(q.companies_suspended, 'perusahaan')}` },
    ].sort((a, b) => (b.level - a.level) || (Number(Boolean(b.n)) - Number(Boolean(a.n))));
    const pending = items.some((it) => it.level > 0);

    $('ov-queue').innerHTML = `
        ${pending ? '' : `<p class="adm-allclear mb-2">${icon('circle-check')}Tidak ada keputusan yang menunggu. Antrean bersih.</p>`}
        ${items.map((it) => `
            <a href="${it.href}" class="adm-queue-item ${it.level === 3 ? 'is-bad' : (it.level === 2 ? 'is-hot' : '')} ${it.n ? '' : 'is-clear'}">
                <span class="adm-queue-icon">${icon(it.icon)}</span>
                <span class="adm-queue-text"><span class="adm-queue-label">${it.label}</span><span class="adm-queue-sub">${it.sub}</span></span>
                <span class="adm-queue-num">${counter(it.n)}</span>
                ${icon('chevron-right', 'adm-queue-go')}
            </a>`).join('')}`;
}

function metric({ label, def, n, na, naText = 'Belum cukup data', digits = 0, unit = '', warn = false }) {
    return `<div class="adm-metric">
        <span class="adm-metric-label">${label}</span>
        ${na ? `<span class="adm-metric-na">${naText}</span>` : `<span class="adm-metric-num ${warn ? 'is-warn' : ''}">${counter(n, digits)}${unit}</span>`}
        <span class="adm-metric-def">${def}</span>
    </div>`;
}

function renderMarket(m, jobs) {
    $('ov-market-sub').textContent = m.open_jobs
        ? `Dihitung dari ${plural(m.open_jobs, 'lowongan terbuka')} yang tidak diturunkan.`
        : 'Belum ada lowongan terbuka untuk dihitung.';
    // Metrik pasar ikut menghitung data demo, jadi penandanya harus jelas
    const demoShare = jobs.open_demo && !jobs.open_real
        ? 'Semua lowongan terbuka saat ini adalah data demo. Angka di bawah belum mencerminkan pasar nyata.'
        : (jobs.open_demo ? `Termasuk ${plural(jobs.open_demo, 'lowongan')} data demo dari ${jobs.open_real + jobs.open_demo} lowongan terbuka.` : '');
    $('ov-market-note').innerHTML = demoShare
        ? `<div class="ws-callout ws-callout-warning mb-3">${icon('info', 'w-4 h-4')}<p><span class="adm-demo-tag mr-1">DEMO</span>${demoShare}</p></div>`
        : '';
    $('ov-market').innerHTML = [
        metric({ label: 'Lamaran per lowongan', n: m.applications_per_open_job ?? 0, digits: 1, na: m.applications_per_open_job === null,
            naText: 'Belum ada lowongan terbuka', def: 'Rata-rata lamaran yang diterima setiap lowongan terbuka.' }),
        metric({ label: 'Lowongan tanpa pelamar', n: m.open_without_applicants, na: !m.open_jobs, naText: 'Belum ada lowongan terbuka',
            warn: m.open_without_applicants > 0, def: 'Lowongan terbuka yang belum menerima satu lamaran pun.' }),
        metric({ label: 'Rata-rata skor cocok', n: m.avg_match_score ?? 0, digits: 1, unit: '%', na: m.avg_match_score === null,
            naText: 'Belum ada lamaran', def: 'Rata-rata skor pemadanan skill dari semua lamaran.' }),
        metric({ label: 'Waktu verifikasi', n: m.verification_days ?? 0, digits: 1, unit: ' hari', na: m.verification_days === null,
            naText: 'Belum ada pengajuan diputuskan', def: m.verifications_reviewed ? `Rata-rata dari pengajuan sampai putusan, ${plural(m.verifications_reviewed, 'pengajuan')}.` : 'Rata-rata dari pengajuan sampai putusan.' }),
    ].join('');
}

/** Grafik batang (pengguna baru) + garis (lowongan baru), SVG tanpa library. */
function renderGrowth(g) {
    const n = g.week_starts.length;
    const total = sum(g.users) + sum(g.jobs) + sum(g.applications);
    if (!total) {
        $('ov-growth').innerHTML = emptyState({ iconName: 'trending-up', title: 'Belum ada aktivitas 8 minggu terakhir', text: 'Grafik terisi setelah ada pengguna, lowongan, atau lamaran baru.' });
        hydrateIcons($('ov-growth'));
        return;
    }
    const W = 520; const H = 190; const pad = { l: 28, r: 8, t: 10, b: 26 };
    const max = Math.max(1, ...g.users, ...g.jobs);
    const step = (W - pad.l - pad.r) / n;
    const y = (v) => pad.t + (H - pad.t - pad.b) * (1 - v / max);
    const x = (i) => pad.l + step * i + step / 2;
    const bw = Math.min(26, step * 0.5);
    const label = (iso) => new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
    const ticks = [0, Math.round(max / 2), max].filter((v, i, a) => a.indexOf(v) === i);
    const linePts = g.jobs.map((v, i) => `${x(i).toFixed(1)},${y(v).toFixed(1)}`).join(' ');

    $('ov-growth').innerHTML = `
        <svg class="adm-chart" viewBox="0 0 ${W} ${H}" role="img" aria-label="Pengguna baru dan lowongan baru per minggu selama ${n} minggu terakhir">
            ${ticks.map((t) => `<line class="grid" x1="${pad.l}" x2="${W - pad.r}" y1="${y(t)}" y2="${y(t)}"/><text class="axis" x="${pad.l - 6}" y="${y(t) + 4}" text-anchor="end">${t}</text>`).join('')}
            ${g.users.map((v, i) => `<rect class="bar" style="--i:${i}" x="${(x(i) - bw / 2).toFixed(1)}" y="${y(v).toFixed(1)}" width="${bw.toFixed(1)}" height="${Math.max(0, H - pad.b - y(v)).toFixed(1)}" rx="3"><title>Minggu ${label(g.week_starts[i])}: ${v} pengguna baru, ${g.jobs[i]} lowongan baru, ${g.applications[i]} lamaran</title></rect>`).join('')}
            <polyline class="line" points="${linePts}"/>
            ${g.jobs.map((v, i) => `<circle class="dot" cx="${x(i).toFixed(1)}" cy="${y(v).toFixed(1)}" r="3.5"><title>${v} lowongan baru</title></circle>`).join('')}
            ${g.week_starts.map((w, i) => (i % 2 === n % 2 || i === n - 1 ? `<text class="axis" x="${x(i).toFixed(1)}" y="${H - 8}" text-anchor="middle">${label(w)}</text>` : '')).join('')}
        </svg>
        <div class="adm-legend mt-2">
            <span><i style="background:var(--ws-chart-1)"></i>Pengguna baru (${sum(g.users)})</span>
            <span><i style="background:var(--ws-chart-2)"></i>Lowongan baru (${sum(g.jobs)})</span>
            <span>Lamaran masuk: ${sum(g.applications)}</span>
        </div>`;
}

function renderSkills(s) {
    if (!s.items.length) {
        $('ov-skills').innerHTML = emptyState({ iconName: 'list-checks', title: 'Belum ada syarat skill', text: 'Muncul setelah ada lowongan terbuka dengan syarat skill.' });
        hydrateIcons($('ov-skills'));
        return;
    }
    $('ov-skills-sub').textContent = `Dari ${plural(s.open_jobs, 'lowongan terbuka')} dan ${plural(s.applicants, 'pelamar aktif')}.`;
    const pct = (v, total) => (total ? Math.round((v / total) * 100) : 0);
    $('ov-skills').innerHTML = `
        <div class="adm-legend mb-2">
            <span><i style="background:var(--ws-chart-2)"></i>Diminta lowongan</span>
            <span><i style="background:var(--ws-chart-1)"></i>Dimiliki pelamar</span>
        </div>
        <div>${s.items.map((it) => {
            const dj = pct(it.jobs, s.open_jobs);
            const da = pct(it.applicants, s.applicants);
            return `
            <div class="adm-skill">
                <span class="adm-skill-name" title="${esc(it.skill)}">${esc(it.skill)}</span>
                <span class="adm-skill-bars">
                    <span class="adm-skill-bar"><span class="adm-skill-track"><span style="width:${Math.max(dj, 2)}%;background:var(--ws-chart-2)"></span></span>${dj}% lowongan</span>
                    <span class="adm-skill-bar"><span class="adm-skill-track"><span style="width:${Math.max(da, 2)}%;background:var(--ws-chart-1)"></span></span>${da}% pelamar</span>
                </span>
            </div>`;
        }).join('')}</div>
        <p class="ws-meta mt-3">Skill yang banyak dicari tetapi jarang dimiliki pelamar menjelaskan kenapa skor kecocokan rendah.</p>`;
    // Dua frame: elemen harus tergambar dulu dalam keadaan awal agar transisinya berjalan
    requestAnimationFrame(() => requestAnimationFrame(() => $('ov-skills').querySelectorAll('.adm-skill-track > span').forEach((b) => b.classList.add('is-in'))));
}

function renderAudit(list) {
    if (!list.length) {
        // Ringkas: kartu ini selebar halaman, empty state besar terlalu kosong
        $('ov-audit').innerHTML = `<p class="ws-card-body flex items-center gap-2.5 text-sm text-[var(--ws-text-2)]">${icon('scroll-text', 'w-4 h-4 text-[var(--ws-muted)]')}Belum ada tindakan admin. Keputusan verifikasi, penangguhan, dan moderasi akan tercatat di sini.</p>`;
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

function skeletons() {
    $('ov-command').innerHTML = '<div class="h-[10.5rem] opacity-60"></div>';
    $('ov-queue').innerHTML = `<div class="px-4 pb-4">${skeletonRows(4, 'h-12')}</div>`;
    $('ov-market').innerHTML = Array.from({ length: 4 }, () => '<div class="adm-metric"><div class="ws-skel h-16 rounded-lg"></div></div>').join('');
    $('ov-growth').innerHTML = skeletonRows(1, 'h-40');
    $('ov-skills').innerHTML = skeletonRows(4, 'h-8');
}

async function load() {
    const btn = $('ov-refresh');
    btn.disabled = true;
    try {
        const [res, healthy] = await Promise.all([
            admin.overview(),
            admin.health().then(() => true).catch(() => false),
        ]);
        const d = res.data;
        renderNavCounts(d.queue);
        renderCommand(d, healthy, res.meta?.window_days || 30);
        renderQueue(d.queue);
        renderMarket(d.market, d.jobs);
        renderGrowth(d.growth);
        renderSkills(d.skills);
        renderAudit(d.recent_actions || []);
        hydrateIcons();
        countUp($('ws-main'));
        $('ov-updated').textContent = `Diperbarui ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}`;
    } catch (err) {
        // Gagal memuat dibedakan dari nol: tidak ada angka yang ditampilkan sama sekali
        $('ov-command').innerHTML = `<div class="adm-command-status"><span><span class="adm-dot is-bad"></span>Ringkasan gagal dimuat: ${esc(errorMessage(err))}</span></div>`;
        $('ov-queue').innerHTML = `<div class="ws-card-body">${emptyState({ iconName: 'circle-alert', title: 'Data tidak bisa diambil', text: 'Angka tidak ditampilkan agar tidak tertukar dengan nol. Coba muat ulang.', action: '<button type="button" class="ws-btn ws-btn-secondary" data-retry>Muat ulang</button>' })}</div>`;
        ['ov-market', 'ov-market-note', 'ov-growth', 'ov-skills', 'ov-audit'].forEach((id) => { $(id).innerHTML = ''; });
        hydrateIcons();
    }
    btn.disabled = false;
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-overview', loadNav: false });
    if (!ctx) return;
    skeletons();
    $('ov-refresh').addEventListener('click', load);
    $('ov-queue').addEventListener('click', (e) => { if (e.target.closest('[data-retry]')) { skeletons(); load(); } });
    await load();
});
