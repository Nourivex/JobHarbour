import { bootShell } from '../../recruiter/shell.js';
import { data } from '../../recruiter/data.js';
import { initMenus } from '../../recruiter/components.js';
import { mountCandidateWorkspace } from '../../recruiter/candidate-workspace.js';
import { esc, icon, emptyState, errorMessage, stageOf } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);

function lead(apps, jobs) {
    const active = apps.filter((a) => !['hired', 'rejected'].includes(stageOf(a))).length;
    const open = jobs.filter((j) => j.status === 'open').length;
    return `${apps.length} kandidat dari ${jobs.length} lowongan (${open} dibuka). ${active} masih dalam proses seleksi.`;
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'pipeline' });
    if (!ctx) return;
    initMenus();

    try {
        const [jobs, apps] = await Promise.all([data.jobs(), data.applications()]);
        $('pl-lead').textContent = lead(apps, jobs);
        mountCandidateWorkspace({
            root: $('pl-workspace'),
            jobs,
            apps,
            onChange: () => { $('pl-lead').textContent = lead(apps, jobs); },
        });
    } catch (err) {
        $('pl-workspace').innerHTML = `<div class="ws-card">${emptyState({
            iconName: 'circle-alert',
            title: 'Kandidat gagal dimuat',
            text: esc(errorMessage(err)),
            action: `<button type="button" class="ws-btn ws-btn-secondary" onclick="location.reload()">${icon('refresh-cw')}Muat ulang</button>`,
        })}</div>`;
    }
});
