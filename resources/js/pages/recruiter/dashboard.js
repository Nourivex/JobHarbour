import { bootShell, setNavCounts, canPublish } from '../../recruiter/shell.js';
import { data, appsOfJob, stageCounts, averageScore } from '../../recruiter/data.js';
import { initMenus } from '../../recruiter/components.js';
import { initJobForm, openJobForm, skillSuggestions } from '../../recruiter/job-form.js';
import {
    esc, icon, hydrateIcons, STAGE, STAGES, stageOf, interviewOf, scoreInfo, avatar, plural, relativeTime,
    formatDateTime, emptyState, skeletonRows, daysSince, VERIFY,
} from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);

function greeting(name) {
    const h = new Date().getHours();
    const part = h < 11 ? 'Selamat pagi' : h < 15 ? 'Selamat siang' : h < 18 ? 'Selamat sore' : 'Selamat malam';
    return `${part}, ${(name || 'Recruiter').split(/\s+/)[0]}`;
}

function kpi(href, iconName, label, value, sub) {
    return `
        <a href="${href}" class="ws-kpi">
            <span class="ws-kpi-label">${icon(iconName)}${label}</span>
            <span class="ws-kpi-value block">${value}</span>
            <span class="ws-kpi-sub block">${sub}</span>
        </a>`;
}

function renderOnboarding(ctx, jobs) {
    if (ctx.isAdmin) return;
    const status = ctx.company?.verification_status;
    const steps = [
        { done: Boolean(ctx.company) && !(ctx.company.missing_profile_fields || []).length, title: 'Lengkapi profil perusahaan', text: 'Nama, industri, alamat, dan kontak.', href: '/recruiter/company', cta: 'Buka profil' },
        { done: ['pending', 'verified'].includes(status), title: 'Ajukan verifikasi', text: 'Unggah NIB atau dokumen legal lainnya.', href: '/recruiter/verification', cta: 'Ajukan' },
        { done: status === 'verified', title: 'Tunggu persetujuan admin', text: 'Biasanya diperiksa dalam 1 sampai 2 hari kerja.', href: '/recruiter/verification', cta: 'Lihat status' },
        { done: jobs.some((j) => j.status === 'open'), title: 'Publikasikan lowongan pertama', text: 'Draft bisa disiapkan sejak sekarang.', href: '#', cta: 'Buat lowongan', newJob: true },
    ];
    if (steps.every((s) => s.done)) return;
    const current = steps.findIndex((s) => !s.done);
    const el = $('dash-onboarding');
    el.classList.remove('hidden');
    el.innerHTML = `
        <div class="ws-card ws-card-body">
            <div class="flex flex-wrap items-start justify-between gap-3 mb-5">
                <div>
                    <h2 id="dash-onboarding-title" class="ws-h2">Siapkan akun rekrutmenmu</h2>
                    <p class="ws-meta mt-0.5">${steps.filter((s) => s.done).length} dari ${steps.length} langkah selesai.</p>
                </div>
                ${status && VERIFY[status] ? `<a href="/recruiter/verification" class="ws-pill ws-pill-${VERIFY[status].tone}">${icon(VERIFY[status].icon, 'w-3.5 h-3.5')}${VERIFY[status].label}</a>` : ''}
            </div>
            <ol class="ws-steps">
                ${steps.map((s, i) => `
                    <li class="ws-step ${s.done ? 'is-done' : i === current ? 'is-current' : ''}">
                        <span class="ws-step-mark">${s.done ? icon('check') : i + 1}</span>
                        <div class="min-w-0">
                            <p class="ws-step-title">${s.title}</p>
                            <p class="ws-step-text">${s.text}</p>
                            ${i === current ? (s.newJob
                                ? `<button type="button" class="ws-btn ws-btn-sm ws-btn-primary mt-3" data-new-job>${s.cta}</button>`
                                : `<a href="${s.href}" class="ws-btn ws-btn-sm ws-btn-primary mt-3">${s.cta}</a>`) : ''}
                        </div>
                    </li>`).join('')}
            </ol>
        </div>`;
}

function renderKpis(jobs, apps) {
    const open = jobs.filter((j) => j.status === 'open');
    const drafts = jobs.filter((j) => j.status === 'draft').length;
    const counts = stageCounts(apps);
    const ready = apps.filter((a) => stageOf(a) === 'screening' && Number(a.match_score) >= 80).length;
    const week = apps.filter((a) => daysSince(a.created_at) <= 7).length;
    const upcoming = apps.map(interviewOf).filter((s) => s && new Date(s.datetime) >= Date.now() - 3600000)
        .sort((a, b) => new Date(a.datetime) - new Date(b.datetime));

    $('dash-kpis').innerHTML = [
        kpi('/recruiter/jobs?status=open', 'briefcase-business', 'Lowongan aktif', open.length, drafts ? `${drafts} draft belum terbit` : 'Tidak ada draft tertunda'),
        kpi('/recruiter/pipeline', 'users', 'Total kandidat', apps.length, week ? `${week} masuk 7 hari terakhir` : 'Belum ada yang baru minggu ini'),
        kpi('/recruiter/pipeline?stage=screening', 'inbox', 'Perlu ditinjau', counts.screening, ready ? `${ready} skor 80% ke atas` : 'Belum ada yang menonjol'),
        kpi('/recruiter/interviews', 'calendar-clock', 'Tahap interview', counts.interview, upcoming.length ? `Terdekat ${formatDateTime(upcoming[0].datetime)}` : 'Belum ada jadwal'),
    ].join('');
}

function actionItem(iconName, tone, title, text, href, cta) {
    return `
        <a href="${href}" class="group flex items-center gap-3.5 rounded-xl p-3 -mx-1 hover:bg-[var(--ws-surface-2)] transition-colors">
            <span class="w-10 h-10 shrink-0 rounded-xl inline-flex items-center justify-center" style="background:var(--ws-${tone}-soft);color:var(--ws-${tone})">${icon(iconName, 'w-5 h-5')}</span>
            <span class="min-w-0 flex-1">
                <span class="block font-semibold text-[var(--ws-text)]">${title}</span>
                <span class="block ws-meta truncate">${text}</span>
            </span>
            <span class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-[var(--ws-text-2)] group-hover:text-[var(--ws-text)]">${cta}${icon('chevron-right')}</span>
        </a>`;
}

function renderActions(ctx, jobs, apps) {
    const items = [];
    const byJob = (list) => {
        const m = new Map();
        list.forEach((a) => m.set(a.job_vacancy?.title || '-', (m.get(a.job_vacancy?.title || '-') || 0) + 1));
        return [...m.entries()].map(([t, n]) => `${t} (${n})`).join(', ');
    };

    const ready = apps.filter((a) => stageOf(a) === 'screening' && Number(a.match_score) >= 80);
    if (ready.length) items.push(actionItem('sparkles', 'success', `${plural(ready.length, 'kandidat')} siap dimajukan`, `Skor 80% ke atas, masih di Screening: ${esc(byJob(ready))}`, '/recruiter/pipeline?stage=screening&cat=excellent', 'Tinjau'));

    const review = apps.filter((a) => stageOf(a) === 'screening' && Number(a.match_score) < 80);
    if (review.length) items.push(actionItem('inbox', 'info', `${plural(review.length, 'berkas')} menunggu ditinjau`, esc(byJob(review)), '/recruiter/pipeline?stage=screening', 'Tinjau'));

    const overdue = apps.filter((a) => {
        const s = stageOf(a) === 'interview' ? interviewOf(a) : null;
        return s && new Date(s.datetime).getTime() < Date.now() - 3600000;
    });
    if (overdue.length) items.push(actionItem('calendar-clock', 'warning', `${plural(overdue.length, 'wawancara')} sudah lewat`, 'Catat hasilnya lalu majukan atau tolak kandidat.', '/recruiter/interviews', 'Putuskan'));

    const offering = apps.filter((a) => stageOf(a) === 'offering');
    if (offering.length) items.push(actionItem('send', 'warning', `${plural(offering.length, 'penawaran')} belum dikonfirmasi`, 'Tandai Hired setelah kandidat menerima tawaran.', '/recruiter/pipeline?stage=offering', 'Buka'));

    const drafts = jobs.filter((j) => j.status === 'draft');
    if (drafts.length) {
        const ok = canPublish(ctx);
        items.push(actionItem('file-pen', 'warning', `${plural(drafts.length, 'draft')} belum dipublikasikan`, ok ? esc(drafts.map((d) => d.title).join(', ')) : 'Publikasikan setelah perusahaan terverifikasi.', '/recruiter/jobs?status=draft', 'Buka'));
    }

    const soon = jobs.filter((j) => j.status === 'open' && j.expires_at && (new Date(j.expires_at) - Date.now()) / 86400000 <= 7);
    if (soon.length) items.push(actionItem('clock', 'danger', `${plural(soon.length, 'lowongan')} segera ditutup`, esc(soon.map((j) => j.title).join(', ')), '/recruiter/jobs?status=open', 'Periksa'));

    $('dash-actions').innerHTML = items.length
        ? items.join('')
        : emptyState({ iconName: 'check-check', title: 'Semua beres', text: 'Tidak ada yang menunggu keputusanmu saat ini.' });
}

function renderPerformance(jobs, apps) {
    const open = jobs.filter((j) => j.status === 'open')
        .map((j) => ({ job: j, apps: appsOfJob(apps, j.id) }))
        .sort((a, b) => b.apps.length - a.apps.length)
        .slice(0, 6);

    if (!open.length) {
        $('dash-perf').innerHTML = emptyState({ iconName: 'briefcase-business', title: 'Belum ada lowongan aktif', text: 'Lowongan yang dipublikasikan akan muncul di sini beserta jumlah pelamarnya.' });
        return;
    }

    $('dash-perf').innerHTML = `
        <div class="ws-table-wrap -mx-1">
            <table class="ws-table">
                <thead><tr><th>Lowongan</th><th class="num">Pelamar</th><th class="num">Rata-rata skor</th><th>Sebaran tahap</th><th class="num">Hired / kuota</th></tr></thead>
                <tbody>
                    ${open.map(({ job, apps: list }) => {
                        const c = stageCounts(list);
                        const avg = averageScore(list);
                        return `
                        <tr>
                            <td class="min-w-[200px]"><a href="/recruiter/jobs/${job.id}" class="ws-row-link">${esc(job.title)}</a><p class="ws-meta">${esc(job.location || '-')}</p></td>
                            <td class="num font-semibold text-[var(--ws-text)]">${list.length}</td>
                            <td class="num">${avg === null ? '<span class="ws-meta">-</span>' : `<span style="color:${scoreInfo(avg).color}" class="font-semibold">${avg}%</span>`}</td>
                            <td class="min-w-[140px]">
                                <div class="ws-dist" role="img" aria-label="${STAGES.filter((s) => c[s]).map((s) => `${STAGE[s].label} ${c[s]}`).join(', ') || 'Belum ada pelamar'}">
                                    ${STAGES.filter((s) => c[s]).map((s) => `<span style="flex:${c[s]};background:${STAGE[s].color}" title="${STAGE[s].label}: ${c[s]}"></span>`).join('')}
                                </div>
                            </td>
                            <td class="num">${c.hired}/${Number(job.quota) || 0}</td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
        <div class="mt-4 flex flex-wrap gap-x-4 gap-y-1">
            ${STAGES.map((s) => `<span class="inline-flex items-center gap-1.5 ws-meta"><span class="ws-dot" style="--c:${STAGE[s].color}"></span>${STAGE[s].label}</span>`).join('')}
        </div>`;
}

function renderInterviews(apps) {
    const list = apps.map((a) => ({ a, s: interviewOf(a) }))
        .filter(({ a, s }) => s && stageOf(a) === 'interview' && new Date(s.datetime) >= Date.now() - 3600000)
        .sort((x, y) => new Date(x.s.datetime) - new Date(y.s.datetime))
        .slice(0, 5);

    $('dash-interviews').innerHTML = list.length ? `
        <ul class="space-y-1">
            ${list.map(({ a, s }) => `
                <li>
                    <a href="/recruiter/candidates/${a.id}" class="flex items-center gap-3 rounded-xl p-2.5 -mx-2.5 hover:bg-[var(--ws-surface-2)] transition-colors">
                        <span class="w-12 shrink-0 text-center rounded-lg border border-[var(--ws-border)] py-1">
                            <span class="block text-[11px] font-semibold uppercase text-[var(--ws-primary)]">${new Date(s.datetime).toLocaleDateString('id-ID', { month: 'short' })}</span>
                            <span class="block text-lg font-bold leading-none ws-num">${new Date(s.datetime).getDate()}</span>
                        </span>
                        <span class="min-w-0">
                            <span class="block font-semibold truncate">${esc(a.applicant?.name || 'Kandidat')}</span>
                            <span class="block ws-meta truncate">${new Date(s.datetime).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })} · ${s.type === 'offline' ? 'Tatap muka' : 'Online'} · ${esc(a.job_vacancy?.title || '')}</span>
                        </span>
                    </a>
                </li>`).join('')}
        </ul>` : emptyState({ iconName: 'calendar-days', title: 'Tidak ada interview terjadwal', text: 'Jadwal dibuat saat kandidat dimajukan ke tahap Interview.' });
}

function renderActivity(apps) {
    const events = [];
    apps.forEach((a) => {
        const name = a.applicant?.name || 'Kandidat';
        const stages = a.pipeline_timeline?.stages || [];
        stages.forEach((s, i) => {
            let text;
            if (i === 0) text = `<strong>${esc(name)}</strong> melamar <span class="text-[var(--ws-text)]">${esc(a.job_vacancy?.title || '')}</span>`;
            else if (s.stage === 'rejected') text = `<strong>${esc(name)}</strong> ditolak`;
            else text = `<strong>${esc(name)}</strong> maju ke ${STAGE[s.stage]?.label || s.stage}`;
            events.push({ at: s.entered_at, text, stage: i === 0 ? 'screening' : s.stage, id: a.id });
            if (s.stage === 'offering' && s.status === 'passed' && s.completed_at) {
                events.push({ at: s.completed_at, text: `<strong>${esc(name)}</strong> resmi diterima (Hired)`, stage: 'hired', id: a.id });
            }
        });
        if (!stages.length) events.push({ at: a.created_at, text: `<strong>${esc(name)}</strong> melamar ${esc(a.job_vacancy?.title || '')}`, stage: 'screening', id: a.id });
    });
    const recent = events.filter((e) => e.at).sort((x, y) => new Date(y.at) - new Date(x.at)).slice(0, 7);

    $('dash-activity').innerHTML = recent.length ? `
        <ol class="ws-timeline">
            ${recent.map((e) => `
                <li class="ws-tl" style="--c:${STAGE[e.stage]?.color || '#64748B'}">
                    <a href="/recruiter/candidates/${e.id}" class="block text-sm text-[var(--ws-text-2)] hover:underline underline-offset-2">${e.text}</a>
                    <p class="ws-meta mt-0.5">${relativeTime(e.at)}</p>
                </li>`).join('')}
        </ol>` : emptyState({ iconName: 'inbox', title: 'Belum ada aktivitas', text: 'Aktivitas muncul saat pelamar mengirim lamaran.' });
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'dashboard' });
    if (!ctx) return;
    initMenus();

    $('dash-greeting').textContent = greeting(ctx.user.name);
    $('dash-lead').textContent = new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' })
        + (ctx.isAdmin ? ' · Mode admin: semua lowongan dan kandidat.' : ctx.company ? ` · ${ctx.company.name}` : '');
    ['dash-actions', 'dash-perf', 'dash-interviews', 'dash-activity'].forEach((id) => { $(id).innerHTML = skeletonRows(3, 'h-12'); });

    let jobs = [];
    let apps = [];
    const render = () => {
        renderOnboarding(ctx, jobs);
        renderKpis(jobs, apps);
        renderActions(ctx, jobs, apps);
        renderPerformance(jobs, apps);
        renderInterviews(apps);
        renderActivity(apps);
        setNavCounts(apps);
        hydrateIcons($('ws-main'));
    };

    initJobForm(ctx, {
        onSave: async () => {
            data.invalidate('jobs');
            jobs = await data.jobs();
            render();
        },
    });
    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-new-job]')) openJobForm(null, skillSuggestions(jobs, apps));
    });

    try {
        [jobs, apps] = await Promise.all([data.jobs(), data.applications()]);
        render();
    } catch (err) {
        console.error(err);
        $('dash-actions').innerHTML = emptyState({ iconName: 'circle-alert', title: 'Data gagal dimuat', text: 'Periksa koneksi ke server lalu muat ulang halaman.', action: '<button type="button" class="ws-btn ws-btn-secondary" onclick="location.reload()">Muat ulang</button>' });
    }
});
