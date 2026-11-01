import { bootShell, setReviewCount } from '../../recruiter/shell.js';
import { data, jobById } from '../../recruiter/data.js';
import { initMenus } from '../../recruiter/components.js';
import {
    matchSection, skillsSection, profileSection, documentsSection, historySection, timelineSection, renderActions, bindCvDownload,
} from '../../recruiter/candidate-view.js';
import { esc, icon, hydrateIcons, stageOf, stageBadge, initials, formatDate, relativeTime, emptyState, errorMessage, scoreInfo } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const appId = $('cd-root').dataset.applicationId;

function render(app, job, apps) {
    const a = app.applicant || {};
    document.title = `${a.name || 'Kandidat'} - Portal Recruiter JobHarbour`;
    $('ws-crumb').textContent = a.name || 'Detail Kandidat';
    $('cd-avatar').textContent = initials(a.name);
    $('cd-name').textContent = a.name || 'Kandidat';
    $('cd-sub').innerHTML = `Melamar <a href="/recruiter/jobs/${app.job_vacancy_id}" class="font-semibold text-[var(--ws-text)] hover:underline underline-offset-2">${esc(app.job_vacancy?.title || '-')}</a> · ${esc(app.job_vacancy?.company || '')}`;
    $('cd-back').href = `/recruiter/pipeline?job=${app.job_vacancy_id}`;

    $('cd-match').innerHTML = matchSection(app, job);
    $('cd-skills').innerHTML = skillsSection(app);
    $('cd-profile').innerHTML = profileSection(app);
    $('cd-docs').innerHTML = documentsSection(app);
    $('cd-history').innerHTML = historySection(app, apps);
    $('cd-timeline').innerHTML = timelineSection(app);
    bindCvDownload($('cd-docs'));

    const info = scoreInfo(app.match_score);
    $('cd-facts').innerHTML = [
        ['Tahap', stageBadge(stageOf(app))],
        ['Skor kecocokan', `<span class="font-semibold" style="color:${info.color}">${info.score}% · ${info.label}</span>`],
        ['Diajukan', `${formatDate(app.created_at, true)} <span class="ws-meta">(${relativeTime(app.created_at)})</span>`],
        ['Lokasi kandidat', esc(a.location || '-')],
    ].map(([k, v]) => `<div class="flex items-center justify-between gap-3"><dt class="ws-meta">${k}</dt><dd class="text-right">${v}</dd></div>`).join('');

    renderActions($('cd-actions'), app, {
        onMoved: () => {
            data.invalidate('apps');
            render(app, job, apps);
        },
    });
    hydrateIcons($('ws-main'));
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'candidate' });
    if (!ctx) return;
    initMenus();

    try {
        const [app, jobs, apps] = await Promise.all([data.application(appId), data.jobs(), data.applications()]);
        setReviewCount(apps.filter((x) => stageOf(x) === 'screening').length);
        render(app, jobById(jobs, app.job_vacancy_id) || app.job_vacancy, apps);
    } catch (err) {
        $('cd-content').classList.add('hidden');
        $('cd-error').classList.remove('hidden');
        const notFound = err?.status === 404 || err?.status === 403;
        $('cd-error').innerHTML = emptyState({
            iconName: notFound ? 'search' : 'circle-alert',
            title: notFound ? 'Kandidat tidak ditemukan' : 'Data kandidat gagal dimuat',
            text: notFound ? 'Lamaran ini tidak ada atau bukan untuk lowonganmu.' : esc(errorMessage(err)),
            action: `<a href="/recruiter/pipeline" class="ws-btn ws-btn-secondary">${icon('arrow-left')}Kembali ke pipeline</a>`,
        });
    }
});
