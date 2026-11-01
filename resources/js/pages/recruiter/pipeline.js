import { util, toast, confirm } from '../../api.js';
import { bootShell, setReviewCount } from '../../recruiter/shell.js';
import { data, jobById, moveCandidate } from '../../recruiter/data.js';
import { initMenus, openDialog, debounce } from '../../recruiter/components.js';
import { matchSection, skillsSection, profileSection, documentsSection, timelineSection, renderActions, bindCvDownload } from '../../recruiter/candidate-view.js';
import {
    esc, icon, hydrateIcons, STAGES, STAGE, stageOf, nextStageOf, stageEnteredAt, interviewOf, scoreInfo, scoreRing,
    stageBadge, relativeTime, formatDateTime, daysSince, initials, emptyState, errorMessage,
} from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const params = new URLSearchParams(window.location.search);
const desktop = window.matchMedia('(min-width: 1024px)');

const state = {
    jobs: [],
    apps: [],
    job: params.get('job') || 'all',
    cat: ['excellent', 'good', 'under'].includes(params.get('cat')) ? params.get('cat') : 'all',
    q: params.get('q') || '',
    sort: 'score',
    mobileStage: STAGES.includes(params.get('stage')) ? params.get('stage') : 'screening',
    open: { hired: false, rejected: false },
    dragFrom: null,
    drawerApp: null,
};

function syncUrl() {
    const p = new URLSearchParams();
    if (state.job !== 'all') p.set('job', state.job);
    if (state.cat !== 'all') p.set('cat', state.cat);
    if (state.q) p.set('q', state.q);
    history.replaceState(null, '', `${location.pathname}${p.toString() ? `?${p}` : ''}`);
}

function scoped() {
    return state.job === 'all' ? state.apps : state.apps.filter((a) => String(a.job_vacancy_id) === String(state.job));
}

function visible() {
    const q = state.q.trim().toLowerCase();
    const list = scoped().filter((a) => {
        if (state.cat !== 'all' && scoreInfo(a.match_score).key !== state.cat) return false;
        if (!q) return true;
        return [a.applicant?.name, a.job_vacancy?.title, ...(a.matched_skills || []), ...(a.applicant?.skills || [])]
            .filter(Boolean).join(' ').toLowerCase().includes(q);
    });
    const sorters = {
        score: (a, b) => (Number(b.match_score) || 0) - (Number(a.match_score) || 0),
        newest: (a, b) => new Date(b.created_at) - new Date(a.created_at),
        waiting: (a, b) => new Date(stageEnteredAt(a)) - new Date(stageEnteredAt(b)),
    };
    return list.sort(sorters[state.sort]);
}

function card(app, stage) {
    const a = app.applicant || {};
    const top = (app.matched_skills || []).slice(0, 3);
    const more = (app.matched_skills || []).length - top.length;
    const missing = (app.missing_skills || []).length;
    const days = daysSince(stageEnteredAt(app));
    const schedule = stage === 'interview' ? interviewOf(app) : null;
    const final = stage === 'hired' || stage === 'rejected';
    const ready = stage === 'screening' && Number(app.match_score) >= 80;
    const next = nextStageOf(stage);

    return `
        <article class="ws-kcard" draggable="${final ? 'false' : 'true'}" data-app="${app.id}" data-stage="${stage}" tabindex="0"
            aria-label="${esc(a.name || 'Kandidat')}, ${esc(app.job_vacancy?.title || '')}, skor ${Math.round(app.match_score || 0)} persen. Tekan Enter untuk detail.">
            <div class="flex items-start gap-3">
                <div class="min-w-0 flex-1">
                    <h3 class="font-semibold text-[15px] leading-snug truncate">${esc(a.name || 'Kandidat')}</h3>
                    <p class="ws-meta truncate">${esc(app.job_vacancy?.title || '-')}</p>
                </div>
                ${scoreRing(app.match_score, 'sm')}
            </div>
            ${top.length || missing ? `
            <div class="mt-3 flex flex-wrap gap-1">
                ${top.map((s) => `<span class="ws-chip">${esc(s)}</span>`).join('')}
                ${more > 0 ? `<span class="ws-chip">+${more}</span>` : ''}
                ${missing ? `<span class="ws-chip ws-chip-miss" title="Belum dimiliki: ${esc((app.missing_skills || []).join(', '))}">${missing} kurang</span>` : ''}
            </div>` : ''}
            ${ready ? `<p class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--ws-success)]">${icon('sparkles', 'w-3.5 h-3.5')}Siap dimajukan</p>` : ''}
            ${schedule ? `<p class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--ws-warning)]">${icon('calendar-clock', 'w-3.5 h-3.5')}${formatDateTime(schedule.datetime)}</p>` : ''}
            <div class="mt-3 pt-2.5 border-t border-[var(--ws-border)] flex items-center gap-2">
                <span class="ws-meta" title="Melamar ${relativeTime(app.created_at)}">${days === null ? '' : days === 0 ? 'Masuk hari ini' : `${days} hari di tahap ini`}</span>
                ${next && !final ? `<button type="button" class="ws-kcard-next ml-auto" data-advance="${app.id}" aria-label="Majukan ${esc(a.name || 'kandidat')} ke ${STAGE[next].label}">${STAGE[next].label}${icon('arrow-right', 'w-3.5 h-3.5')}</button>` : ''}
            </div>
        </article>`;
}

function emptyText(stage) {
    if (state.cat !== 'all' || state.q) return 'Tidak ada yang cocok dengan saringan.';
    return { screening: 'Belum ada lamaran baru.', assessment: 'Belum ada yang dinilai.', interview: 'Belum ada jadwal interview.', offering: 'Belum ada penawaran.', hired: 'Belum ada yang diterima.', rejected: 'Belum ada yang ditolak.' }[stage];
}

function render() {
    const list = visible();
    const grouped = Object.fromEntries(STAGES.map((s) => [s, []]));
    list.forEach((a) => grouped[stageOf(a)].push(a));
    setReviewCount(state.apps.filter((a) => stageOf(a) === 'screening').length);

    const jobTitle = state.job === 'all' ? null : jobById(state.jobs, state.job)?.title;
    $('pl-lead').textContent = `${scoped().length} kandidat${jobTitle ? ` untuk ${jobTitle}` : ' di semua lowongan'}. Tahap hanya bisa maju satu langkah atau ditolak.`;

    $('pl-stage-tabs').innerHTML = STAGES.map((s) => `
        <button type="button" role="tab" class="ws-tab" data-mstage="${s}" aria-selected="${state.mobileStage === s}">
            <span class="ws-dot" style="--c:${STAGE[s].color}"></span>${STAGE[s].label}<span class="ws-count">${grouped[s].length}</span>
        </button>`).join('');

    const board = $('pl-board');
    board.dataset.mobileStage = state.mobileStage;
    board.classList.toggle('show-hired', state.open.hired);
    board.classList.toggle('show-rejected', state.open.rejected);

    if (!state.apps.length) {
        board.innerHTML = `<div class="ws-card col-span-full">${emptyState({ iconName: 'users', title: 'Belum ada kandidat', text: state.jobs.some((j) => j.status === 'open') ? 'Kandidat muncul di sini setelah pelamar mengirim lamaran ke lowonganmu.' : 'Publikasikan lowongan agar pelamar bisa mulai melamar.', action: '<a href="/recruiter/jobs" class="ws-btn ws-btn-primary">Kelola lowongan</a>' })}</div>`;
        return;
    }

    board.innerHTML = STAGES.map((s) => {
        const items = grouped[s];
        const foldable = s === 'hired' || s === 'rejected';
        const folded = foldable && !state.open[s];
        return `
        <section class="ws-col ${folded ? 'is-folded' : ''}" data-stage="${s}" aria-label="${STAGE[s].label}, ${items.length} kandidat">
            ${folded ? `
                <button type="button" class="ws-col-fold" data-toggle="${s}" aria-expanded="false" aria-label="Tampilkan ${items.length} kandidat ${STAGE[s].label}">
                    <span class="ws-dot" style="--c:${STAGE[s].color}"></span>
                    <span class="ws-col-fold-label">${STAGE[s].label}</span>
                    <span class="ws-count">${items.length}</span>
                </button>` : `
                <header class="ws-col-head">
                    <span class="ws-dot" style="--c:${STAGE[s].color}"></span>
                    <h2 class="font-semibold text-sm">${STAGE[s].label}</h2>
                    <span class="ws-count">${items.length}</span>
                    ${foldable ? `<button type="button" class="ws-icon-btn w-8 h-8 hidden lg:inline-flex ml-auto" data-toggle="${s}" aria-expanded="true" aria-label="Lipat kolom ${STAGE[s].label}">${icon('chevron-right', 'w-4 h-4')}</button>` : ''}
                </header>`}
            <div class="ws-col-body">
                ${folded ? '' : (items.length ? items.map((a) => card(a, s)).join('') : `<p class="ws-col-empty">${emptyText(s)}</p>`)}
            </div>
        </section>`;
    }).join('');
}

// ------------------------------------------------------------------ perpindahan tahap
async function quickMove(app, target) {
    // Interview butuh jadwal, Hired dan Rejected butuh konfirmasi berisi catatan: semuanya lewat laci
    if (target === 'interview' || target === 'hired' || target === 'rejected') {
        openDrawer(app, target === 'rejected' ? 'reject' : 'advance');
        return;
    }
    const ok = await confirm({
        title: `Majukan ke ${STAGE[target].label}?`,
        message: `${app.applicant?.name || 'Kandidat'} pindah dari ${STAGE[stageOf(app)].label} ke ${STAGE[target].label}. Tahap tidak bisa dikembalikan.`,
        confirmText: 'Majukan',
        cancelText: 'Batal',
    });
    if (!ok) return;
    try {
        await moveCandidate(app, target);
        toast(`${app.applicant?.name || 'Kandidat'} maju ke ${STAGE[target].label}.`, 'success');
        afterMove(app, target);
    } catch (err) {
        toast(errorMessage(err, 'Tahap kandidat gagal diperbarui.'), 'error', 5000);
    }
}

function afterMove(app, target) {
    if (!desktop.matches) state.mobileStage = target;
    if (target === 'hired' || target === 'rejected') state.open[target] = true;
    render();
    const moved = $('pl-board').querySelector(`[data-app="${app.id}"]`);
    moved?.classList.add('is-arrived');
    moved?.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
}

// ------------------------------------------------------------------ laci kandidat
const drawer = $('pl-drawer');

function openDrawer(app, mode = null) {
    state.drawerApp = app;
    const job = jobById(state.jobs, app.job_vacancy_id) || app.job_vacancy;
    $('pl-d-avatar').textContent = initials(app.applicant?.name);
    $('pl-d-name').textContent = app.applicant?.name || 'Kandidat';
    $('pl-d-job').textContent = `${app.job_vacancy?.title || '-'} · melamar ${relativeTime(app.created_at)}`;
    $('pl-d-stage').innerHTML = stageBadge(stageOf(app));
    $('pl-d-full').href = `/recruiter/candidates/${app.id}`;
    $('pl-d-body').innerHTML = [matchSection(app, job), skillsSection(app), profileSection(app), documentsSection(app), timelineSection(app)].join('<div class="ws-divider"></div>');
    bindCvDownload($('pl-d-body'));
    renderActions($('pl-d-actions'), app, {
        mode,
        onMoved: (moved, target) => {
            drawer.close();
            afterMove(moved, target);
        },
    });
    hydrateIcons(drawer);
    openDialog(drawer);
    $('pl-d-body').scrollTop = 0;
}

// ------------------------------------------------------------------ drag & drop
function initDrag() {
    const board = $('pl-board');
    board.addEventListener('dragstart', (e) => {
        const c = e.target.closest('.ws-kcard');
        if (!c) return;
        state.dragFrom = c.dataset.stage;
        e.dataTransfer.setData('text/plain', c.dataset.app);
        e.dataTransfer.effectAllowed = 'move';
        c.classList.add('is-dragging');
        const next = nextStageOf(state.dragFrom);
        board.querySelectorAll('.ws-col').forEach((col) => {
            const s = col.dataset.stage;
            if (s === state.dragFrom) return;
            col.classList.add(s === next || s === 'rejected' ? 'is-drop-ok' : 'is-drop-no');
        });
    });
    board.addEventListener('dragend', () => {
        state.dragFrom = null;
        board.querySelectorAll('.is-dragging').forEach((el) => el.classList.remove('is-dragging'));
        board.querySelectorAll('.ws-col').forEach((col) => col.classList.remove('is-drop-ok', 'is-drop-no', 'is-drop-over'));
    });
    board.addEventListener('dragover', (e) => {
        const col = e.target.closest('.ws-col.is-drop-ok');
        if (!col) return;
        e.preventDefault();
        board.querySelectorAll('.is-drop-over').forEach((c) => c !== col && c.classList.remove('is-drop-over'));
        col.classList.add('is-drop-over');
    });
    board.addEventListener('drop', (e) => {
        const col = e.target.closest('.ws-col');
        if (!col) return;
        e.preventDefault();
        const app = state.apps.find((a) => String(a.id) === e.dataTransfer.getData('text/plain'));
        if (!app) return;
        if (!col.classList.contains('is-drop-ok')) {
            if (col.dataset.stage !== state.dragFrom) toast('Tahap hanya bisa maju satu langkah atau ditolak.', 'warning');
            return;
        }
        quickMove(app, col.dataset.stage);
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'pipeline' });
    if (!ctx) return;
    initMenus();
    initDrag();

    $('pl-search').value = state.q;
    document.querySelectorAll('#pl-cat [data-cat]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.cat === state.cat)));

    $('pl-search').addEventListener('input', debounce((e) => { state.q = e.target.value; syncUrl(); render(); }, 180));
    $('pl-sort').addEventListener('change', (e) => { state.sort = e.target.value; render(); });
    $('pl-job').addEventListener('change', (e) => { state.job = e.target.value; syncUrl(); render(); });
    $('pl-cat').addEventListener('click', (e) => {
        const b = e.target.closest('[data-cat]');
        if (!b) return;
        state.cat = b.dataset.cat;
        document.querySelectorAll('#pl-cat [data-cat]').forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
        syncUrl();
        render();
    });
    $('pl-stage-tabs').addEventListener('click', (e) => {
        const b = e.target.closest('[data-mstage]');
        if (!b) return;
        state.mobileStage = b.dataset.mstage;
        if (state.mobileStage === 'hired' || state.mobileStage === 'rejected') state.open[state.mobileStage] = true;
        render();
    });

    $('pl-board').addEventListener('click', (e) => {
        const adv = e.target.closest('[data-advance]');
        if (adv) {
            e.stopPropagation();
            const app = state.apps.find((a) => String(a.id) === adv.dataset.advance);
            if (app) quickMove(app, nextStageOf(stageOf(app)));
            return;
        }
        const t = e.target.closest('[data-toggle]');
        if (t) {
            state.open[t.dataset.toggle] = !state.open[t.dataset.toggle];
            render();
            return;
        }
        const c = e.target.closest('.ws-kcard');
        if (c) openDrawer(state.apps.find((a) => String(a.id) === c.dataset.app));
    });
    $('pl-board').addEventListener('keydown', (e) => {
        const c = e.target.closest('.ws-kcard');
        if (c && e.target === c && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            openDrawer(state.apps.find((a) => String(a.id) === c.dataset.app));
        }
    });

    try {
        [state.jobs, state.apps] = await Promise.all([data.jobs(), data.applications()]);
        $('pl-job').innerHTML = `<option value="all">Semua lowongan (${state.jobs.length})</option>` + state.jobs
            .filter((j) => j.status !== 'archived' || state.apps.some((a) => String(a.job_vacancy_id) === String(j.id)))
            .map((j) => `<option value="${j.id}">${esc(j.title)}${j.status === 'open' ? '' : ` (${j.status === 'draft' ? 'draft' : j.status === 'closed' ? 'ditutup' : 'arsip'})`}</option>`).join('');
        if (state.job !== 'all' && !jobById(state.jobs, state.job)) state.job = 'all';
        $('pl-job').value = state.job;
        render();
        const focusApp = params.get('candidate');
        if (focusApp) {
            const app = state.apps.find((a) => String(a.id) === focusApp);
            if (app) openDrawer(app);
        }
    } catch (err) {
        $('pl-board').innerHTML = `<div class="ws-card col-span-full">${emptyState({ iconName: 'circle-alert', title: 'Pipeline gagal dimuat', text: esc(errorMessage(err)), action: '<button type="button" class="ws-btn ws-btn-secondary" onclick="location.reload()">Muat ulang</button>' })}</div>`;
    }
});
