import { jobs as jobsApi, toast, confirm, util } from '../../api.js';
import { bootShell, setReviewCount, canPublish } from '../../recruiter/shell.js';
import { data, jobById, appsOfJob, stageCounts, averageScore } from '../../recruiter/data.js';
import { initMenus } from '../../recruiter/components.js';
import { initJobForm, openJobForm, skillSuggestions } from '../../recruiter/job-form.js';
import {
    esc, icon, hydrateIcons, STAGES, STAGE, LEVEL, stageOf, stageBadge, jobStatusBadge, scoreRing, formatDate, relativeTime,
    emptyState, errorMessage,
} from '../../recruiter/ui.js';
import { EMPLOYMENT_SHORT, salaryRange } from '../../job-ui.js';

const $ = (id) => document.getElementById(id);
const jobId = $('jd-root').dataset.jobId;
const state = { ctx: null, jobs: [], apps: [], job: null, filter: 'all' };

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

function renderSide() {
    const j = state.job;
    const apps = appsOfJob(state.apps, j.id);
    const c = stageCounts(apps);
    const max = Math.max(1, ...Object.values(c));
    $('jd-funnel').innerHTML = STAGES.map((s) => `
        <button type="button" class="w-full text-left group" data-filter="${s}" aria-pressed="${state.filter === s}">
            <span class="flex items-center justify-between text-sm mb-1">
                <span class="inline-flex items-center gap-2 ${state.filter === s ? 'font-semibold' : ''}"><span class="ws-dot" style="--c:${STAGE[s].color}"></span>${STAGE[s].label}</span>
                <span class="ws-num font-semibold">${c[s]}</span>
            </span>
            <span class="block h-2 rounded-full bg-[var(--ws-surface-2)] overflow-hidden"><span class="block h-full rounded-full transition-[width] duration-700" style="width:${(c[s] / max) * 100}%;background:${STAGE[s].color}"></span></span>
        </button>`).join('');

    const reqs = j.requirements || [];
    const total = reqs.reduce((s, r) => s + (Number(r.weight) || LEVEL[r.level]?.weight || 1), 0);
    $('jd-reqs').innerHTML = reqs.length ? `
        <ul class="space-y-2.5">
            ${reqs.map((r) => {
                const lv = LEVEL[r.level] || LEVEL.required;
                const w = Number(r.weight) || lv.weight;
                return `<li class="flex items-center gap-3 text-sm">
                    <span class="flex-1 min-w-0 truncate font-medium">${esc(util.skillName(r))}</span>
                    <span class="ws-meta">${lv.label}</span>
                    <span class="w-16 h-1.5 rounded-full bg-[var(--ws-surface-2)] overflow-hidden" title="${Math.round((w / total) * 100)}% dari total bobot"><span class="block h-full" style="width:${(w / total) * 100}%;background:${lv.color}"></span></span>
                </li>`;
            }).join('')}
        </ul>` : '<p class="ws-meta">Belum ada syarat skill.</p>';

    const salary = salaryRange(j);
    const avg = averageScore(apps);
    $('jd-info').innerHTML = [
        ['Gaji', salary || 'Tidak dicantumkan'],
        ['Kuota', `${c.hired} diterima dari ${Number(j.quota) || 0}`],
        ['Rata-rata skor', avg === null ? '-' : `${avg}%`],
        ['Batas lamaran', j.expires_at ? formatDate(j.expires_at) : 'Tanpa batas'],
        ['Dibuat', formatDate(j.created_at)],
        ['Diperbarui', relativeTime(j.updated_at)],
    ].map(([k, v]) => `<div class="flex justify-between gap-3"><dt class="ws-meta">${k}</dt><dd class="text-right font-medium">${esc(v)}</dd></div>`).join('');
}

function renderApplicants() {
    const all = appsOfJob(state.apps, state.job.id).sort((a, b) => (Number(b.match_score) || 0) - (Number(a.match_score) || 0));
    const list = state.filter === 'all' ? all : all.filter((a) => stageOf(a) === state.filter);
    $('jd-app-title').innerHTML = `Pelamar <span class="ws-count ml-1">${all.length}</span>${state.filter !== 'all' ? ` <button type="button" class="ws-pill ws-pill-neutral ml-2" data-filter="all">${STAGE[state.filter].label} ${icon('x', 'w-3 h-3')}</button>` : ''}`;
    $('jd-pipeline-link').href = `/recruiter/pipeline?job=${state.job.id}`;

    if (!all.length) {
        $('jd-applicants').innerHTML = emptyState({
            iconName: 'users',
            title: 'Belum ada pelamar',
            text: state.job.status === 'open' ? 'Pelamar akan muncul di sini setelah mengirim lamaran.' : 'Lowongan ini belum dipublikasikan, jadi belum bisa dilamar.',
        });
        return;
    }
    if (!list.length) {
        $('jd-applicants').innerHTML = `<p class="ws-meta py-6 text-center">Tidak ada pelamar di tahap ${STAGE[state.filter].label}.</p>`;
        return;
    }
    $('jd-applicants').innerHTML = `
        <ul class="divide-y divide-[var(--ws-border)] -my-2">
            ${list.map((a) => `
                <li>
                    <a href="/recruiter/candidates/${a.id}" class="flex items-center gap-3 py-3 -mx-2 px-2 rounded-xl hover:bg-[var(--ws-surface-2)] transition-colors">
                        ${scoreRing(a.match_score, 'sm')}
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold truncate">${esc(a.applicant?.name || 'Kandidat')}</span>
                            <span class="block ws-meta truncate">${(a.matched_skills || []).length} skill cocok · ${(a.missing_skills || []).length} kurang · melamar ${relativeTime(a.created_at)}</span>
                        </span>
                        ${stageBadge(stageOf(a))}
                    </a>
                </li>`).join('')}
        </ul>`;
}

function renderAll() {
    renderHeader();
    renderSide();
    renderApplicants();
    $('jd-description').textContent = state.job.description || '';
    hydrateIcons($('ws-main'));
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
        const f = e.target.closest('[data-filter]');
        if (f) {
            state.filter = state.filter === f.dataset.filter ? 'all' : f.dataset.filter;
            renderSide();
            renderApplicants();
            hydrateIcons($('ws-main'));
            return;
        }
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
        setReviewCount(state.apps.filter((x) => stageOf(x) === 'screening').length);
        state.job = jobById(state.jobs, jobId);
        if (!state.job) throw Object.assign(new Error('not found'), { status: 404 });
        renderAll();
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
