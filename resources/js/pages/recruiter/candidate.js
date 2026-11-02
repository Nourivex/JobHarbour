import { bootShell, setNavCounts } from '../../recruiter/shell.js';
import { data, jobById } from '../../recruiter/data.js';
import { initMenus } from '../../recruiter/components.js';
import {
    matchSection, otherSkillsSection, profileSection, documentsSection, historySection, timelineSection, renderActions,
    bindCvDownload, notesSection, bindNotes,
} from '../../recruiter/candidate-view.js';
import { esc, icon, hydrateIcons, stageOf, STAGE, initials, formatDate, relativeTime, emptyState, errorMessage, scoreInfo } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const appId = $('cd-root').dataset.applicationId;
const TABS = ['summary', 'profile', 'notes', 'history'];
const state = { app: null, job: null, apps: [], tab: TABS.includes(new URLSearchParams(location.search).get('tab')) ? new URLSearchParams(location.search).get('tab') : 'summary' };

/** Susunan tab sama dengan laci kandidat supaya recruiter menemukan informasi di tempat yang sama. */
function renderPanel() {
    const { app, job, apps } = state;
    document.querySelectorAll('#cd-tabs [data-tab]').forEach((t) => t.setAttribute('aria-selected', String(t.dataset.tab === state.tab)));
    const sections = {
        summary: [matchSection(app, job), otherSkillsSection(app)],
        profile: [profileSection(app), documentsSection(app)],
        notes: [notesSection(app)],
        history: [timelineSection(app), historySection(app, apps)],
    }[state.tab].filter(Boolean);
    const panel = $('cd-panel');
    panel.innerHTML = sections.join('<div class="ws-divider"></div>');
    bindCvDownload(panel);
    if (state.tab === 'notes') bindNotes(panel, app);
    hydrateIcons(panel);
}

function renderSide() {
    const { app } = state;
    const a = app.applicant || {};
    const stage = stageOf(app);
    const info = scoreInfo(app.match_score);
    $('cd-facts').innerHTML = [
        ['Tahap', `<span class="ws-stage"><span class="ws-dot" style="--c:${STAGE[stage].color}"></span>${STAGE[stage].label}</span>`],
        ['Skor kecocokan', `<span class="font-semibold" style="color:${info.color}">${info.score}%</span> <span class="ws-meta">${info.label}</span>`],
        ['Melamar', `${formatDate(app.created_at)} <span class="ws-meta">(${relativeTime(app.created_at)})</span>`],
        ['Lokasi', esc(a.location || '-')],
    ].map(([k, v]) => `<div class="flex items-center justify-between gap-3"><dt class="ws-meta">${k}</dt><dd class="text-right">${v}</dd></div>`).join('');

    renderActions($('cd-actions'), app, {
        job: state.job,
        onMoved: () => {
            data.invalidate('apps');
            renderSide();
            renderPanel();
        },
    });
    hydrateIcons($('cd-actions'));
}

function renderHeader() {
    const { app } = state;
    const a = app.applicant || {};
    document.title = `${a.name || 'Kandidat'} - Portal Recruiter JobHarbour`;
    $('ws-crumb').textContent = a.name || 'Detail Kandidat';
    $('cd-avatar').textContent = initials(a.name);
    $('cd-name').textContent = a.name || 'Kandidat';
    $('cd-sub').innerHTML = `Melamar <a href="/recruiter/jobs/${app.job_vacancy_id}" class="font-semibold text-[var(--ws-text)] hover:underline underline-offset-2">${esc(app.job_vacancy?.title || '-')}</a> · ${esc(app.job_vacancy?.company || '')}`;
    $('cd-back').href = `/recruiter/jobs/${app.job_vacancy_id}?candidate=${app.id}`;
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'candidate' });
    if (!ctx) return;
    initMenus();

    $('cd-tabs').addEventListener('click', (e) => {
        const t = e.target.closest('[data-tab]');
        if (!t || !state.app) return;
        state.tab = t.dataset.tab;
        const p = new URLSearchParams(location.search);
        if (state.tab === 'summary') p.delete('tab'); else p.set('tab', state.tab);
        history.replaceState(null, '', `${location.pathname}${p.toString() ? `?${p}` : ''}`);
        renderPanel();
    });

    try {
        const [app, jobs, apps] = await Promise.all([data.application(appId), data.jobs(), data.applications()]);
        setNavCounts(apps);
        Object.assign(state, { app, job: jobById(jobs, app.job_vacancy_id) || app.job_vacancy, apps });
        renderHeader();
        renderSide();
        renderPanel();
    } catch (err) {
        $('cd-content').classList.add('hidden');
        $('cd-error').classList.remove('hidden');
        const notFound = err?.status === 404 || err?.status === 403;
        $('cd-error').innerHTML = emptyState({
            iconName: notFound ? 'search' : 'circle-alert',
            title: notFound ? 'Kandidat tidak ditemukan' : 'Data kandidat gagal dimuat',
            text: notFound ? 'Lamaran ini tidak ada atau bukan untuk lowonganmu.' : esc(errorMessage(err)),
            action: `<a href="/recruiter/pipeline" class="ws-btn ws-btn-secondary">${icon('arrow-left')}Kembali ke kandidat</a>`,
        });
    }
});
