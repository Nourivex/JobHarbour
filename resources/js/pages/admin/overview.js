/**
 * Admin Portal: Ringkasan sebagai pusat kendali. Urutan menurut prioritas kerja admin:
 * keputusan yang menunggu, kesehatan pasar kerja, pertumbuhan dan neraca skill, lalu isi platform.
 * Semua angka dari GET /api/admin/overview. Nilai null ditampilkan "belum cukup data", bukan nol.
 * Animasi hanya transform/opacity dan mengikuti setelan kurangi gerakan.
 */
import { admin } from '../../api.js';
import { bootAdmin, renderNavCounts } from '../../admin/shell.js';
import { esc, icon, hydrateIcons, relativeTime, formatDateTime, emptyState, skeletonRows, errorMessage, plural } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const sum = (obj) => Object.values(obj || {}).reduce((s, n) => s + Number(n || 0), 0);
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const fmt = (n, digits = 0) => Number(n).toLocaleString('id-ID', { maximumFractionDigits: digits, minimumFractionDigits: digits });

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

function tile({ n, label, sub, href = null, tone = '', na = false, digits = 0, unit = '' }) {
    const clear = !na && !n && !tone;
    const tag = href ? 'a' : 'div';
    const value = na
        ? '<span class="adm-tile-na">Belum cukup data</span>'
        : `<span class="adm-tile-num"><span data-count-to="${n}" data-digits="${digits}">${fmt(n, digits)}</span>${unit}</span>`;
    return `<${tag} ${href ? `href="${href}"` : ''} class="adm-tile ${clear ? 'is-clear' : tone}">
        ${value}
        <span class="adm-tile-label">${label}</span>
        <span class="adm-tile-sub">${sub}</span>
    </${tag}>`;
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

function renderQueue(q) {
    const oldest = q.oldest_pending_at ? `tertua ${relativeTime(q.oldest_pending_at)}` : 'tidak ada yang menunggu';
    const suspended = q.users_suspended + q.companies_suspended;
    $('ov-queue').innerHTML = [
        tile({ n: q.verifications_pending, label: 'Verifikasi menunggu', sub: oldest, href: '/admin/verifications', tone: q.verifications_pending ? 'is-warn' : '' }),
        tile({ n: q.sources_degraded, label: 'Sumber bermasalah', sub: q.sources_degraded ? 'pengambilan terakhir gagal' : 'semua sehat', href: '/admin/sources', tone: q.sources_degraded ? 'is-bad' : '' }),
        tile({ n: q.companies_revision, label: 'Diminta revisi', sub: 'menunggu recruiter', href: '/admin/companies?status=revision_required' }),
        tile({ n: q.jobs_removed, label: 'Lowongan diturunkan', sub: 'tidak tampil di katalog', href: '/admin/jobs?moderation=removed' }),
        tile({ n: suspended, label: 'Ditangguhkan', sub: `${plural(q.users_suspended, 'akun')}, ${plural(q.companies_suspended, 'perusahaan')}`, href: '/admin/users?status=suspended' }),
    ].join('');
}

function renderMarket(m) {
    $('ov-market-sub').textContent = m.open_jobs ? `${plural(m.open_jobs, 'lowongan terbuka')} yang tidak diturunkan` : 'Belum ada lowongan terbuka';
    $('ov-market').innerHTML = [
        tile({ n: m.applications_per_open_job ?? 0, na: m.applications_per_open_job === null, digits: 1, label: 'Lamaran per lowongan', sub: 'rata-rata lowongan terbuka' }),
        tile({ n: m.open_without_applicants, na: !m.open_jobs, label: 'Lowongan tanpa pelamar', sub: 'perlu dipromosikan atau dicek', tone: m.open_without_applicants ? 'is-warn' : '' }),
        tile({ n: m.avg_match_score ?? 0, na: m.avg_match_score === null, digits: 1, unit: '%', label: 'Rata-rata skor cocok', sub: 'semua lamaran' }),
        tile({ n: m.verification_days ?? 0, na: m.verification_days === null, digits: 1, unit: ' hari', label: 'Waktu verifikasi', sub: m.verifications_reviewed ? `dari ${plural(m.verifications_reviewed, 'pengajuan')} diperiksa` : 'dari pengajuan sampai putusan' }),
    ].join('');
}

/** Grafik batang (pengguna baru) + garis (lowongan baru), SVG tanpa library. */
function renderGrowth(g) {
    const n = g.week_starts.length;
    const total = sum(g.users) + sum(g.jobs) + sum(g.applications);
    if (!total) {
        $('ov-growth').innerHTML = emptyState({ iconName: 'trending-up', title: 'Belum ada aktivitas 8 minggu terakhir' });
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
            <span><i style="background:var(--ws-accent)"></i>Pengguna baru (${sum(g.users)})</span>
            <span><i style="background:var(--ws-warning)"></i>Lowongan baru (${sum(g.jobs)})</span>
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
        <div>${s.items.map((it) => {
            const dj = pct(it.jobs, s.open_jobs);
            const da = pct(it.applicants, s.applicants);
            return `
            <div class="adm-skill">
                <span class="adm-skill-name" title="${esc(it.skill)}">${esc(it.skill)}</span>
                <span class="adm-skill-bars">
                    <span class="adm-skill-bar"><span class="adm-skill-track"><span style="width:${Math.max(dj, 2)}%;background:var(--ws-warning)"></span></span>${dj}% lowongan</span>
                    <span class="adm-skill-bar"><span class="adm-skill-track"><span style="width:${Math.max(da, 2)}%;background:var(--ws-accent)"></span></span>${da}% pelamar</span>
                </span>
            </div>`;
        }).join('')}</div>
        <p class="ws-meta mt-3">Skill yang banyak dicari tetapi jarang dimiliki pelamar menjelaskan kenapa skor kecocokan rendah.</p>`;
    // Batang tumbuh dari kiri setelah tampil
    // Dua frame: elemen harus tergambar dulu dalam keadaan awal agar transisinya berjalan
    requestAnimationFrame(() => requestAnimationFrame(() => $('ov-skills').querySelectorAll('.adm-skill-track > span').forEach((b) => b.classList.add('is-in'))));
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
    $('ov-data-sub').textContent = `"Baru" berarti ${windowDays} hari terakhir.`;
    $('ov-data').innerHTML = [
        row('Pengguna', sum(roles), `${roles.applicant || 0} pelamar, ${roles.recruiter || 0} recruiter${d.users.new_30d ? ` · ${d.users.new_30d} baru` : ''}`),
        row('Perusahaan', sum(companies), `${companies.verified || 0} terverifikasi`),
        row('Lowongan terbuka', d.jobs.open_real + d.jobs.open_demo, d.jobs.open_demo ? `${d.jobs.open_real} nyata, ${d.jobs.open_demo} data demo` : 'semuanya data nyata'),
        row('Lamaran', d.applications.total, d.applications.new_30d ? `${d.applications.new_30d} baru` : ''),
    ].join('');
}

function renderAudit(list) {
    if (!list.length) {
        $('ov-audit').innerHTML = `<div class="ws-card-body">${emptyState({ iconName: 'scroll-text', title: 'Belum ada tindakan admin', text: 'Keputusan verifikasi, penangguhan, dan moderasi tercatat di sini.' })}</div>`;
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

    $('ov-queue').innerHTML = Array.from({ length: 5 }, () => '<div class="ws-skel h-[5.5rem] rounded-xl"></div>').join('');
    $('ov-market').innerHTML = Array.from({ length: 4 }, () => '<div class="ws-skel h-[5.5rem] rounded-xl"></div>').join('');
    $('ov-growth').innerHTML = skeletonRows(1, 'h-40');
    $('ov-skills').innerHTML = skeletonRows(4, 'h-8');
    try {
        const [res, healthy] = await Promise.all([
            admin.overview(),
            admin.health().then(() => true).catch(() => false),
        ]);
        const d = res.data;
        renderNavCounts(d.queue);
        renderHealth(d, healthy);
        renderQueue(d.queue);
        renderMarket(d.market);
        renderGrowth(d.growth);
        renderSkills(d.skills);
        renderData(d, res.meta?.window_days || 30);
        renderAudit(d.recent_actions || []);
        countUp(document.getElementById('ws-main'));
    } catch (err) {
        $('ov-queue').innerHTML = `<div class="col-span-full">${emptyState({ iconName: 'circle-alert', title: 'Ringkasan gagal dimuat', text: esc(errorMessage(err)), action: '<button type="button" class="ws-btn ws-btn-secondary" onclick="location.reload()">Muat ulang</button>' })}</div>`;
        ['ov-market', 'ov-growth', 'ov-skills', 'ov-data'].forEach((id) => { $(id).innerHTML = ''; });
    }
    hydrateIcons();
});
