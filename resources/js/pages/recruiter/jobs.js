import { jobs as jobsApi, toast, confirm } from '../../api.js';
import { util } from '../../api.js';
import { bootShell, setNavCounts, publishRule } from '../../recruiter/shell.js';
import { data, appsOfJob, averageScore } from '../../recruiter/data.js';
import { initMenus, menu, debounce } from '../../recruiter/components.js';
import { initJobForm, openJobForm, skillSuggestions } from '../../recruiter/job-form.js';
import {
    esc, icon, hydrateIcons, jobStatusBadge, JOB_STATUS, scoreInfo, stageOf, formatDate, relativeTime, emptyState,
    skeletonRows, daysSince, errorMessage,
} from '../../recruiter/ui.js';
import { EMPLOYMENT_SHORT } from '../../job-ui.js';

const $ = (id) => document.getElementById(id);
const params = new URLSearchParams(window.location.search);

const state = {
    ctx: null,
    jobs: [],
    apps: [],
    status: JOB_STATUS[params.get('status')] ? params.get('status') : 'all',
    q: params.get('q') || '',
    sort: 'updated',
};

function syncUrl() {
    const p = new URLSearchParams();
    if (state.status !== 'all') p.set('status', state.status);
    if (state.q) p.set('q', state.q);
    history.replaceState(null, '', `${location.pathname}${p.toString() ? `?${p}` : ''}`);
}

function visibleJobs() {
    const q = state.q.trim().toLowerCase();
    const list = state.jobs.filter((j) => {
        if (state.status !== 'all' && j.status !== state.status) return false;
        if (state.status === 'all' && j.status === 'archived') return false;
        if (!q) return true;
        const hay = [j.title, j.location, j.company, ...(j.requirements || []).map(util.skillName)].join(' ').toLowerCase();
        return hay.includes(q);
    });
    const count = (j) => appsOfJob(state.apps, j.id).length;
    const sorters = {
        updated: (a, b) => new Date(b.updated_at) - new Date(a.updated_at),
        applicants: (a, b) => count(b) - count(a),
        deadline: (a, b) => (a.expires_at ? new Date(a.expires_at) : Infinity) - (b.expires_at ? new Date(b.expires_at) : Infinity),
        title: (a, b) => a.title.localeCompare(b.title, 'id'),
    };
    return list.sort(sorters[state.sort]);
}

function deadlineText(job) {
    if (!job.expires_at) return '<span class="ws-meta">Tanpa batas</span>';
    const days = Math.ceil((new Date(job.expires_at) - Date.now()) / 86400000);
    const text = formatDate(job.expires_at);
    if (job.status !== 'open') return `<span class="ws-meta">${text}</span>`;
    if (days < 0) return `<span class="text-[var(--ws-danger)] font-medium">${text}</span><p class="ws-meta">sudah lewat</p>`;
    if (days <= 7) return `<span class="text-[var(--ws-warning)] font-medium">${text}</span><p class="ws-meta">${days} hari lagi</p>`;
    return `<span>${text}</span>`;
}

function actionsFor(job) {
    const rule = publishRule(state.ctx, state.jobs, job.id);
    const publishOk = rule.ok;
    const tip = rule.reason;
    const items = [{ label: 'Lihat detail', icon: icon('eye'), attrs: `data-act="view" data-id="${job.id}"` }];
    items.push({ label: 'Buka kandidat', icon: icon('users'), attrs: `data-act="pipeline" data-id="${job.id}"` });
    if (job.status !== 'archived') items.push({ label: 'Edit', icon: icon('pencil'), attrs: `data-act="edit" data-id="${job.id}"` });
    items.push({ label: 'Duplikat', icon: icon('copy'), attrs: `data-act="duplicate" data-id="${job.id}"` });
    items.push('-');
    if (job.status === 'draft' || job.status === 'closed') {
        items.push({ label: job.status === 'draft' ? 'Publikasikan' : 'Buka lagi', icon: icon('send'), attrs: `data-act="publish" data-id="${job.id}"`, disabled: !publishOk, tip });
    }
    if (job.status === 'open') items.push({ label: 'Tutup lowongan', icon: icon('lock'), attrs: `data-act="close" data-id="${job.id}"` });
    if (job.status === 'draft' || job.status === 'closed') items.push({ label: 'Arsipkan', icon: icon('archive'), attrs: `data-act="archive" data-id="${job.id}"` });
    if (job.status === 'archived') items.push({ label: 'Pulihkan dari arsip', icon: icon('rotate-ccw'), attrs: `data-act="restore" data-id="${job.id}"` });
    return menu(`Aksi untuk ${esc(job.title)}`, items, { icon: icon('ellipsis', 'w-5 h-5') });
}

function applicantsCell(list) {
    const fresh = list.filter((a) => stageOf(a) === 'screening').length;
    return `<span class="font-semibold text-[var(--ws-text)]">${list.length}</span>${fresh ? `<p class="ws-meta">${fresh} perlu ditinjau</p>` : ''}`;
}

function render() {
    const counts = { all: 0, open: 0, draft: 0, closed: 0, archived: 0 };
    state.jobs.forEach((j) => {
        counts[j.status] = (counts[j.status] || 0) + 1;
        if (j.status !== 'archived') counts.all += 1;
    });
    document.querySelectorAll('[data-count]').forEach((el) => { el.textContent = counts[el.dataset.count] || 0; });
    document.querySelectorAll('#jobs-tabs [data-status]').forEach((t) => t.setAttribute('aria-selected', String(t.dataset.status === state.status)));

    const list = visibleJobs();
    const box = $('jobs-list');

    if (!state.jobs.length) {
        box.innerHTML = emptyState({
            iconName: 'briefcase-business',
            title: 'Belum ada lowongan',
            text: publishRule(state.ctx, state.jobs).trial
                ? 'Buat lowongan pertama. Sebelum perusahaan terverifikasi, 1 lowongan boleh dipublikasikan sebagai uji coba.'
                : 'Buat lowongan pertama dengan syarat skill yang jelas agar skor kecocokan pelamar akurat.',
            action: '<button type="button" class="ws-btn ws-btn-primary" data-new-job>Buat lowongan</button>',
        });
        hydrateIcons(box);
        return;
    }
    if (!list.length) {
        box.innerHTML = emptyState({ iconName: 'search', title: 'Tidak ada lowongan yang cocok', text: state.q ? `Tidak ditemukan untuk "${esc(state.q)}". Coba kata kunci lain.` : 'Belum ada lowongan dengan status ini.' });
        return;
    }

    box.innerHTML = `
        <div class="hidden md:block ws-table-wrap">
            <table class="ws-table">
                <thead><tr>
                    <th>Lowongan</th><th>Status</th><th class="num">Pelamar</th><th class="num">Rata-rata skor</th><th>Batas lamaran</th><th>Diperbarui</th><th><span class="sr-only">Aksi</span></th>
                </tr></thead>
                <tbody>
                    ${list.map((j) => {
                        const apps = appsOfJob(state.apps, j.id);
                        const avg = averageScore(apps);
                        return `
                        <tr>
                            <td class="min-w-[240px]">
                                <a href="/recruiter/jobs/${j.id}" class="ws-row-link">${esc(j.title)}</a>
                                <p class="ws-meta">${esc(j.location || '-')} · ${esc(EMPLOYMENT_SHORT[j.employment_type] || j.employment_type || '-')}</p>
                            </td>
                            <td>${jobStatusBadge(j.status)}</td>
                            <td class="num">${applicantsCell(apps)}</td>
                            <td class="num">${avg === null ? '<span class="ws-meta">-</span>' : `<span class="font-semibold" style="color:${scoreInfo(avg).color}">${avg}%</span>`}</td>
                            <td class="whitespace-nowrap">${deadlineText(j)}</td>
                            <td class="whitespace-nowrap ws-meta">${relativeTime(j.updated_at)}</td>
                            <td class="text-right">${actionsFor(j)}</td>
                        </tr>`;
                    }).join('')}
                </tbody>
            </table>
        </div>
        <ul class="md:hidden space-y-3">
            ${list.map((j) => {
                const apps = appsOfJob(state.apps, j.id);
                return `
                <li class="rounded-xl border border-[var(--ws-border)] p-4">
                    <div class="flex items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="mb-1.5">${jobStatusBadge(j.status)}</div>
                            <a href="/recruiter/jobs/${j.id}" class="ws-row-link block">${esc(j.title)}</a>
                            <p class="ws-meta">${esc(j.location || '-')} · ${apps.length} pelamar · diperbarui ${relativeTime(j.updated_at)}</p>
                        </div>
                        ${actionsFor(j)}
                    </div>
                </li>`;
            }).join('')}
        </ul>`;
}

async function changeStatus(job, status, { confirmOpts } = {}) {
    if (confirmOpts && !(await confirm(confirmOpts))) return;
    try {
        if (status === 'closed' && job.status === 'open') await jobsApi.close(job.id);
        else await jobsApi.update(job.id, { status });
        job.status = status;
        job.updated_at = new Date().toISOString();
        const msg = { open: 'Lowongan dipublikasikan dan tampil di katalog.', closed: 'Lowongan ditutup.', archived: 'Lowongan diarsipkan.' }[status];
        toast(msg || 'Status lowongan diperbarui.', 'success');
        render();
    } catch (err) {
        toast(errorMessage(err, 'Status lowongan gagal diubah.'), 'error', 5000);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'jobs' });
    if (!ctx) return;
    state.ctx = ctx;
    initMenus();
    $('jobs-search').value = state.q;
    $('jobs-list').innerHTML = skeletonRows(4, 'h-14');

    initJobForm(ctx, {
        jobs: () => state.jobs,
        onSave: async (saved) => {
            data.invalidate('jobs');
            state.jobs = await data.jobs();
            if (saved?.status && state.status !== 'all' && saved.status !== state.status) state.status = 'all';
            render();
        },
    });

    $('jobs-tabs').addEventListener('click', (e) => {
        const tab = e.target.closest('[data-status]');
        if (!tab) return;
        state.status = tab.dataset.status;
        syncUrl();
        render();
        $('jobs-list').classList.remove('ws-panel-enter');
        void $('jobs-list').offsetWidth;
        $('jobs-list').classList.add('ws-panel-enter');
    });
    $('jobs-search').addEventListener('input', debounce((e) => { state.q = e.target.value; syncUrl(); render(); }, 180));
    $('jobs-sort').addEventListener('change', (e) => { state.sort = e.target.value; render(); });

    document.addEventListener('click', (e) => {
        if (e.target.closest('[data-new-job]')) {
            openJobForm(null, skillSuggestions(state.jobs, state.apps));
            return;
        }
        const act = e.target.closest('[data-act]');
        if (!act || act.getAttribute('aria-disabled') === 'true') return;
        const job = state.jobs.find((j) => String(j.id) === act.dataset.id);
        if (!job) return;
        const a = act.dataset.act;
        if (a === 'view') window.location.href = `/recruiter/jobs/${job.id}`;
        if (a === 'pipeline') window.location.href = `/recruiter/jobs/${job.id}`;
        if (a === 'edit') openJobForm(job, skillSuggestions(state.jobs, state.apps));
        if (a === 'duplicate') openJobForm(null, skillSuggestions(state.jobs, state.apps), { template: job });
        if (a === 'publish') changeStatus(job, 'open');
        if (a === 'close') changeStatus(job, 'closed', { confirmOpts: { title: 'Tutup lowongan ini?', message: `"${job.title}" berhenti menerima lamaran baru. Kandidat yang sudah melamar tetap bisa diproses.`, confirmText: 'Tutup lowongan', cancelText: 'Batal', danger: true } });
        if (a === 'archive') changeStatus(job, 'archived', { confirmOpts: { title: 'Arsipkan lowongan?', message: 'Lowongan disembunyikan dari daftar utama. Kamu bisa memulihkannya kapan saja.', confirmText: 'Arsipkan', cancelText: 'Batal' } });
        if (a === 'restore') changeStatus(job, 'closed');
    });

    try {
        [state.jobs, state.apps] = await Promise.all([data.jobs(), data.applications()]);
        setNavCounts(state.apps);
        render();
        if (params.get('new') === '1') {
            openJobForm(null, skillSuggestions(state.jobs, state.apps));
            syncUrl();
        }
    } catch (err) {
        $('jobs-list').innerHTML = emptyState({ iconName: 'circle-alert', title: 'Lowongan gagal dimuat', text: esc(errorMessage(err)), action: '<button type="button" class="ws-btn ws-btn-secondary" onclick="location.reload()">Muat ulang</button>' });
    }
});
