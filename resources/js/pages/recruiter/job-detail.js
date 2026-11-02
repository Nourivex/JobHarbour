import { jobs as jobsApi, toast, confirm, util } from '../../api.js';
import { bootShell, canPublish } from '../../recruiter/shell.js';
import { data, jobById, appsOfJob, stageCounts, averageScore } from '../../recruiter/data.js';
import { initMenus } from '../../recruiter/components.js';
import { initJobForm, openJobForm, skillSuggestions } from '../../recruiter/job-form.js';
import { mountCandidateWorkspace } from '../../recruiter/candidate-workspace.js';
import {
    esc, icon, hydrateIcons, LEVEL, jobStatusBadge, scoreInfo, formatDate, relativeTime, emptyState, errorMessage,
} from '../../recruiter/ui.js';
import { EMPLOYMENT_SHORT, salaryRange } from '../../job-ui.js';

const $ = (id) => document.getElementById(id);
const jobId = $('jd-root').dataset.jobId;
const state = { ctx: null, jobs: [], apps: [], job: null, workspace: null };

function renderHeader() {
    const j = state.job;
    document.title = `${j.title} - Portal Recruiter JobHarbour`;
    $('ws-crumb').textContent = j.title;
    $('jd-title').textContent = j.title;
    $('jd-badges').innerHTML = jobStatusBadge(j.status) + (j.status === 'open' ? `<a href="/jobs/${j.id}" target="_blank" rel="noopener" class="ws-pill ws-pill-neutral hover:underline">${icon('external-link', 'w-3.5 h-3.5')}Lihat di katalog</a>` : '');
    $('jd-sub').textContent = [j.company, j.location, EMPLOYMENT_SHORT[j.employment_type] || j.employment_type].filter(Boolean).join(' · ');

    const ok = canPublish(state.ctx);
    const btns = [`<button type="button" class="ws-btn ws-btn-secondary" data-act="edit">${icon('pencil')}Edit</button>`];
    if (j.status === 'draft' || j.status === 'closed') {
        btns.push(`<span ${ok ? '' : 'data-tip="Perusahaan harus terverifikasi terlebih dahulu"'}><button type="button" class="ws-btn ws-btn-primary" data-act="publish" ${ok ? '' : 'disabled'}>${icon('send')}${j.status === 'draft' ? 'Publikasikan' : 'Buka lagi'}</button></span>`);
    }
    if (j.status === 'open') btns.push(`<button type="button" class="ws-btn ws-btn-secondary" data-act="close">${icon('lock')}Tutup lowongan</button>`);
    if (j.status === 'closed' || j.status === 'draft') btns.push(`<button type="button" class="ws-btn ws-btn-ghost" data-act="archive">${icon('archive')}Arsipkan</button>`);
    if (j.status === 'archived') btns.push(`<button type="button" class="ws-btn ws-btn-secondary" data-act="restore">${icon('rotate-ccw')}Pulihkan</button>`);
    $('jd-actions').innerHTML = btns.join('');
}

/** Empat angka yang menjawab "bagaimana lowongan ini berjalan", semuanya dari data lamaran. */
function renderStats() {
    const j = state.job;
    const apps = appsOfJob(state.apps, j.id);
    const c = stageCounts(apps);
    const avg = averageScore(apps);
    const inProcess = c.assessment + c.interview + c.offering;
    const left = j.expires_at ? Math.ceil((new Date(j.expires_at) - Date.now()) / 86400000) : null;
    const stat = (label, value, sub) => `
        <div class="ws-stat"><dt class="ws-kpi-label">${label}</dt><dd class="ws-stat-value">${value}</dd><p class="ws-kpi-sub">${sub}</p></div>`;
    $('jd-stats').innerHTML = [
        stat('Pelamar', apps.length, c.screening ? `${c.screening} menunggu screening` : 'Tidak ada yang menunggu'),
        stat('Rata-rata skor', avg === null ? '-' : `<span style="color:${scoreInfo(avg).color}">${avg}%</span>`, avg === null ? 'Belum ada lamaran' : scoreInfo(avg).label),
        stat('Dalam proses', inProcess, `${c.interview} di tahap wawancara`),
        stat('Diterima', `${c.hired}<span class="text-base font-semibold text-[var(--ws-muted)]">/${Number(j.quota) || 0}</span>`,
            left === null ? 'Tanpa batas waktu' : left < 0 ? 'Batas lamaran sudah lewat' : `Ditutup ${left} hari lagi`),
    ].join('');
    $('jd-count').textContent = apps.length;
}

function renderDetail() {
    const j = state.job;
    $('jd-description').textContent = j.description || '';
    const reqs = j.requirements || [];
    const total = reqs.reduce((s, r) => s + (Number(r.weight) || LEVEL[r.level]?.weight || 1), 0);
    $('jd-reqs').innerHTML = reqs.length ? `
        <ul class="space-y-2.5">
            ${reqs.map((r) => {
                const lv = LEVEL[r.level] || LEVEL.required;
                const w = Number(r.weight) || lv.weight;
                return `<li class="flex items-center gap-3 text-sm">
                    <span class="flex-1 min-w-0 truncate font-medium">${esc(util.skillName(r))}</span>
                    <span class="ws-meta">${lv.label} · ${w}</span>
                    <span class="w-16 h-1.5 rounded-full bg-[var(--ws-surface-2)] overflow-hidden" title="${Math.round((w / total) * 100)}% dari total bobot"><span class="block h-full" style="width:${(w / total) * 100}%;background:${lv.color}"></span></span>
                </li>`;
            }).join('')}
        </ul>` : '<p class="ws-meta">Belum ada syarat skill. Tanpa syarat, skor kecocokan tidak bisa dihitung.</p>';

    const salary = salaryRange(j);
    $('jd-info').innerHTML = [
        ['Gaji', salary || 'Tidak dicantumkan'],
        ['Kuota', `${Number(j.quota) || 0} orang`],
        ['Batas lamaran', j.expires_at ? formatDate(j.expires_at) : 'Tanpa batas'],
        ['Dibuat', formatDate(j.created_at)],
        ['Diperbarui', relativeTime(j.updated_at)],
    ].map(([k, v]) => `<div class="flex justify-between gap-3"><dt class="ws-meta">${k}</dt><dd class="text-right font-medium">${esc(v)}</dd></div>`).join('');
}

function renderAll() {
    renderHeader();
    renderStats();
    renderDetail();
    hydrateIcons($('ws-main'));
}

function showPanel(name) {
    document.querySelectorAll('[data-panel]').forEach((t) => t.setAttribute('aria-selected', String(t.dataset.panel === name)));
    $('jd-panel-candidates').classList.toggle('hidden', name !== 'candidates');
    $('jd-panel-detail').classList.toggle('hidden', name !== 'detail');
}

async function setStatus(status, confirmOpts) {
    if (confirmOpts && !(await confirm(confirmOpts))) return;
    try {
        if (status === 'closed' && state.job.status === 'open') await jobsApi.close(state.job.id);
        else await jobsApi.update(state.job.id, { status });
        state.job.status = status;
        state.job.updated_at = new Date().toISOString();
        toast({ open: 'Lowongan dipublikasikan.', closed: 'Lowongan ditutup.', archived: 'Lowongan diarsipkan.' }[status] || 'Status diperbarui.', 'success');
        renderAll();
    } catch (err) {
        toast(errorMessage(err, 'Status lowongan gagal diubah.'), 'error', 5000);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'job-detail' });
    if (!ctx) return;
    state.ctx = ctx;
    initMenus();
    initJobForm(ctx, {
        onSave: async () => {
            data.invalidate('jobs');
            state.jobs = await data.jobs();
            state.job = jobById(state.jobs, jobId);
            renderAll();
        },
    });

    document.addEventListener('click', (e) => {
        const tab = e.target.closest('[data-panel]');
        if (tab) { showPanel(tab.dataset.panel); return; }
        const a = e.target.closest('[data-act]');
        if (!a || a.disabled) return;
        const act = a.dataset.act;
        if (act === 'edit') openJobForm(state.job, skillSuggestions(state.jobs, state.apps));
        if (act === 'publish') setStatus('open');
        if (act === 'close') setStatus('closed', { title: 'Tutup lowongan ini?', message: 'Lowongan berhenti menerima lamaran. Kandidat yang sudah melamar tetap bisa diproses.', confirmText: 'Tutup lowongan', cancelText: 'Batal', danger: true });
        if (act === 'archive') setStatus('archived', { title: 'Arsipkan lowongan?', message: 'Lowongan disembunyikan dari daftar utama dan bisa dipulihkan kapan saja.', confirmText: 'Arsipkan', cancelText: 'Batal' });
        if (act === 'restore') setStatus('closed');
    });

    try {
        [state.jobs, state.apps] = await Promise.all([data.jobs(), data.applications()]);
        state.job = jobById(state.jobs, jobId);
        if (!state.job) throw Object.assign(new Error('not found'), { status: 404 });
        renderAll();
        // Lowongan tanpa pelamar yang belum dibuka: tampilkan detailnya dulu
        if (!appsOfJob(state.apps, jobId).length && state.job.status !== 'open') showPanel('detail');
        state.workspace = mountCandidateWorkspace({
            root: $('jd-workspace'),
            jobs: state.jobs,
            apps: appsOfJob(state.apps, jobId),
            jobId,
            navApps: state.apps,
            onChange: () => { renderStats(); hydrateIcons($('jd-stats')); },
        });
    } catch (err) {
        $('jd-content').classList.add('hidden');
        $('jd-error').classList.remove('hidden');
        $('jd-error').innerHTML = emptyState({
            iconName: err?.status === 404 ? 'search' : 'circle-alert',
            title: err?.status === 404 ? 'Lowongan tidak ditemukan' : 'Lowongan gagal dimuat',
            text: err?.status === 404 ? 'Lowongan ini tidak ada atau bukan milik perusahaanmu.' : esc(errorMessage(err)),
            action: `<a href="/recruiter/jobs" class="ws-btn ws-btn-secondary">${icon('arrow-left')}Semua lowongan</a>`,
        });
    }
});
