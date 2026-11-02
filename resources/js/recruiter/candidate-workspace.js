/**
 * Ruang kerja kandidat yang dipakai halaman Kandidat (semua lowongan) dan halaman satu lowongan.
 * Dua tampilan atas data yang sama:
 * - Tabel: mencari, menyortir, membandingkan, dan memproses banyak kandidat sekaligus.
 * - Kanban: melihat alur seleksi dan memindahkan kandidat dengan seret dan lepas.
 * Klik kandidat membuka laci berisi penjelasan skor, catatan, linimasa, dan aksi tahap.
 */
import { toast, confirm } from '../api.js';
import { jobById, moveCandidate } from './data.js';
import { setNavCounts } from './shell.js';
import { openDialog, setBusy, debounce } from './components.js';
import {
    matchSection, skillsSection, profileSection, documentsSection, timelineSection, notesSection,
    renderActions, bindCvDownload, bindNotes,
} from './candidate-view.js';
import {
    esc, icon, hydrateIcons, STAGES, STAGE, stageOf, nextStageOf, stageEnteredAt, interviewOf, scoreInfo, scoreRing,
    stageBadge, relativeTime, formatDateTime, daysSince, initials, emptyState, errorMessage,
} from './ui.js';

const VIEW_KEY = 'jh_ws_view';
const desktop = window.matchMedia('(min-width: 1024px)');
const SORTERS = {
    score: (a, b) => (Number(b.match_score) || 0) - (Number(a.match_score) || 0),
    newest: (a, b) => new Date(b.created_at) - new Date(a.created_at),
    waiting: (a, b) => new Date(stageEnteredAt(a)) - new Date(stageEnteredAt(b)),
};

function readView() {
    try { return localStorage.getItem(VIEW_KEY) === 'board' ? 'board' : 'table'; } catch (e) { return 'table'; }
}

/**
 * @param {object} opts
 * @param {HTMLElement} opts.root      wadah kosong untuk ruang kerja
 * @param {Array} opts.jobs            lowongan milik recruiter (dengan requirements)
 * @param {Array} opts.apps            lamaran (dari GET /applications)
 * @param {string|number|null} opts.jobId  kunci ke satu lowongan, atau null untuk semua
 * @param {Array} [opts.navApps]      semua lamaran untuk lencana menu (bawaan: apps)
 * @param {Function} [opts.onChange]   dipanggil setelah tahap kandidat berubah
 */
export function mountCandidateWorkspace({ root, jobs, apps, jobId = null, navApps = apps, onChange = () => {} }) {
    const params = new URLSearchParams(window.location.search);
    const state = {
        job: jobId ? String(jobId) : (params.get('job') || 'all'),
        stage: STAGES.includes(params.get('stage')) ? params.get('stage') : 'all',
        cat: ['excellent', 'good', 'under'].includes(params.get('cat')) ? params.get('cat') : 'all',
        q: params.get('q') || '',
        sort: SORTERS[params.get('sort')] ? params.get('sort') : 'score',
        view: ['table', 'board'].includes(params.get('view')) ? params.get('view') : readView(),
        open: { hired: false, rejected: false },
        selected: new Set(),
        dragFrom: null,
    };
    if (state.job !== 'all' && !jobById(jobs, state.job)) state.job = 'all';
    if (state.stage === 'hired' || state.stage === 'rejected') state.open[state.stage] = true;

    const showJobColumn = !jobId;
    root.innerHTML = `
        <div class="ws-toolbar mb-3">
            ${showJobColumn ? `
            <label for="cw-job" class="sr-only">Saring lowongan</label>
            <select id="cw-job" class="ws-input w-full sm:w-[280px]"></select>` : ''}
            <div class="ws-search flex-1 min-w-[200px] sm:max-w-sm">
                ${icon('search')}
                <label for="cw-search" class="sr-only">Cari kandidat</label>
                <input id="cw-search" type="search" class="ws-input" placeholder="Cari nama atau skill" autocomplete="off">
            </div>
            <label for="cw-sort" class="sr-only">Urutkan</label>
            <select id="cw-sort" class="ws-input w-auto">
                <option value="score">Skor tertinggi</option>
                <option value="newest">Lamaran terbaru</option>
                <option value="waiting">Terlama di tahap</option>
            </select>
            <div class="ws-seg ml-auto" role="group" aria-label="Tampilan">
                <button type="button" data-view="table" aria-pressed="false">${icon('table-2', 'w-4 h-4')}Tabel</button>
                <button type="button" data-view="board" aria-pressed="false">${icon('kanban', 'w-4 h-4')}Kanban</button>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <div class="ws-seg" role="group" aria-label="Saring skor kecocokan" id="cw-cat">
                <button type="button" data-cat="all">Semua skor</button>
                <button type="button" data-cat="excellent"><span class="ws-dot" style="--c:#15803D"></span>80% ke atas</button>
                <button type="button" data-cat="good"><span class="ws-dot" style="--c:#1B65A6"></span>50 sampai 79%</button>
                <button type="button" data-cat="under"><span class="ws-dot" style="--c:#B45309"></span>Di bawah 50%</button>
            </div>
        </div>
        <div id="cw-tabs" class="ws-tabs mb-4" role="tablist" aria-label="Tahap seleksi"></div>
        <div id="cw-view" aria-live="polite"></div>
        <div id="cw-bulk" class="ws-bulk hidden" role="region" aria-label="Aksi untuk kandidat terpilih"></div>`;

    const $ = (sel) => root.querySelector(sel);
    const drawer = ensureDrawer();
    const rejectDialog = ensureRejectDialog();

    // ------------------------------------------------------------------ data turunan
    const scoped = () => (state.job === 'all' ? apps : apps.filter((a) => String(a.job_vacancy_id) === state.job));
    const filtered = () => {
        const q = state.q.trim().toLowerCase();
        return scoped().filter((a) => {
            if (state.cat !== 'all' && scoreInfo(a.match_score).key !== state.cat) return false;
            if (!q) return true;
            return [a.applicant?.name, a.job_vacancy?.title, ...(a.matched_skills || []), ...(a.applicant?.skills || [])]
                .filter(Boolean).join(' ').toLowerCase().includes(q);
        }).sort(SORTERS[state.sort]);
    };
    const findApp = (id) => apps.find((a) => String(a.id) === String(id));

    function syncUrl() {
        const p = new URLSearchParams(window.location.search);
        const set = (k, v, def) => (v && v !== def ? p.set(k, v) : p.delete(k));
        if (!jobId) set('job', state.job, 'all');
        set('stage', state.stage, 'all');
        set('cat', state.cat, 'all');
        set('q', state.q.trim(), '');
        set('sort', state.sort, 'score');
        p.delete('view');
        history.replaceState(null, '', `${location.pathname}${p.toString() ? `?${p}` : ''}`);
    }

    // ------------------------------------------------------------------ render
    function renderTabs(list) {
        const counts = Object.fromEntries(STAGES.map((s) => [s, 0]));
        list.forEach((a) => { counts[stageOf(a)] += 1; });
        const tabs = [['all', 'Semua', list.length, null], ...STAGES.map((s) => [s, STAGE[s].label, counts[s], STAGE[s].color])];
        $('#cw-tabs').innerHTML = tabs.map(([key, label, n, color]) => `
            <button type="button" role="tab" class="ws-tab" data-stage="${key}" aria-selected="${state.stage === key}">
                ${color ? `<span class="ws-dot" style="--c:${color}"></span>` : ''}${label}<span class="ws-count">${n}</span>
            </button>`).join('');
        // Di Kanban desktop semua kolom terlihat, tab tahap hanya dibutuhkan di layar kecil
        $('#cw-tabs').classList.toggle('lg:hidden', state.view === 'board');
    }

    function render() {
        const list = filtered();
        root.querySelectorAll('[data-view]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.view === state.view)));
        root.querySelectorAll('#cw-cat [data-cat]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.cat === state.cat)));
        renderTabs(list);
        setNavCounts(navApps);

        if (!scoped().length) {
            $('#cw-view').innerHTML = `<div class="ws-card">${emptyState({
                iconName: 'users',
                title: 'Belum ada kandidat',
                text: jobs.some((j) => j.status === 'open') ? 'Kandidat muncul di sini setelah pelamar mengirim lamaran.' : 'Publikasikan lowongan agar pelamar bisa mulai melamar.',
                action: jobId ? '' : '<a href="/recruiter/jobs" class="ws-btn ws-btn-primary">Kelola lowongan</a>',
            })}</div>`;
            renderBulk();
            hydrateIcons(root);
            return;
        }
        if (state.view === 'board') renderBoard(list);
        else renderTable(list);
        renderBulk();
        hydrateIcons(root);
    }

    function renderTable(list) {
        const rows = state.stage === 'all' ? list : list.filter((a) => stageOf(a) === state.stage);
        // Kandidat terpilih yang tidak lagi terlihat dilepas supaya aksi massal tidak mengenai baris tersembunyi
        const visibleIds = new Set(rows.map((a) => String(a.id)));
        [...state.selected].forEach((id) => { if (!visibleIds.has(id)) state.selected.delete(id); });

        if (!rows.length) {
            $('#cw-view').innerHTML = `<div class="ws-card">${emptyState({ iconName: 'search', title: 'Tidak ada kandidat', text: state.q || state.cat !== 'all' ? 'Tidak ada yang cocok dengan saringan. Ubah kata kunci atau rentang skor.' : `Belum ada kandidat di tahap ${STAGE[state.stage]?.label || ''}.` })}</div>`;
            return;
        }
        const selectable = rows.filter((a) => !['hired', 'rejected'].includes(stageOf(a)));
        const allChecked = selectable.length > 0 && selectable.every((a) => state.selected.has(String(a.id)));
        $('#cw-view').innerHTML = `
            <div class="ws-card">
                <div class="ws-table-wrap">
                    <table class="ws-table">
                        <thead><tr>
                            <th class="cell-check"><input type="checkbox" class="ws-check" data-check-all ${allChecked ? 'checked' : ''} ${selectable.length ? '' : 'disabled'} aria-label="Pilih semua kandidat aktif di daftar"></th>
                            <th>Kandidat</th>
                            <th>Skor kecocokan</th>
                            <th class="hidden md:table-cell">Skill</th>
                            <th>Tahap</th>
                            <th class="hidden lg:table-cell">Melamar</th>
                            <th class="num"><span class="sr-only">Aksi</span></th>
                        </tr></thead>
                        <tbody>${rows.map(tableRow).join('')}</tbody>
                    </table>
                </div>
            </div>`;
    }

    function tableRow(app) {
        const a = app.applicant || {};
        const stage = stageOf(app);
        const final = stage === 'hired' || stage === 'rejected';
        const info = scoreInfo(app.match_score);
        const next = nextStageOf(stage);
        const matched = app.matched_skills || [];
        const missing = app.missing_skills || [];
        const days = daysSince(stageEnteredAt(app));
        const schedule = stage === 'interview' ? interviewOf(app) : null;
        const checked = state.selected.has(String(app.id));
        return `
            <tr data-app="${app.id}" class="${checked ? 'is-selected' : ''}">
                <td class="cell-check" data-stop><input type="checkbox" class="ws-check" data-check="${app.id}" ${checked ? 'checked' : ''} ${final ? 'disabled' : ''} aria-label="Pilih ${esc(a.name || 'kandidat')}"></td>
                <td class="min-w-[200px]">
                    <button type="button" class="ws-row-link text-left" data-open="${app.id}">${esc(a.name || 'Kandidat')}</button>
                    <p class="ws-meta truncate max-w-[260px]">${showJobColumn ? esc(app.job_vacancy?.title || '-') : esc(a.location || a.email || '')}</p>
                </td>
                <td>
                    <span class="inline-flex items-center gap-2.5">${scoreRing(app.match_score, 'sm')}<span class="text-sm font-semibold whitespace-nowrap" style="color:${info.color}">${info.label}</span></span>
                </td>
                <td class="hidden md:table-cell min-w-[200px]">
                    <div class="ws-skill-line">
                        ${matched.slice(0, 3).map((s) => `<span class="ws-chip ws-chip-ok">${esc(s)}</span>`).join('')}
                        ${matched.length > 3 ? `<span class="ws-chip">+${matched.length - 3}</span>` : ''}
                        ${missing.length ? `<span class="ws-chip ws-chip-miss" title="Belum dimiliki: ${esc(missing.join(', '))}">${missing.length} kurang</span>` : ''}
                        ${!matched.length && !missing.length ? '<span class="ws-meta">-</span>' : ''}
                    </div>
                </td>
                <td class="whitespace-nowrap">
                    ${stageBadge(stage)}
                    <p class="ws-meta mt-1">${schedule ? `${icon('calendar-clock', 'w-3.5 h-3.5 inline -mt-0.5')} ${formatDateTime(schedule.datetime)}` : days === null || final ? '' : days === 0 ? 'Masuk hari ini' : `${days} hari di tahap ini`}</p>
                </td>
                <td class="hidden lg:table-cell whitespace-nowrap ws-meta">${relativeTime(app.created_at)}</td>
                <td class="num whitespace-nowrap" data-stop>
                    ${next && !final ? `<button type="button" class="ws-kcard-next" data-advance="${app.id}" aria-label="Majukan ${esc(a.name || 'kandidat')} ke ${STAGE[next].label}">${STAGE[next].label}${icon('arrow-right', 'w-3.5 h-3.5')}</button>` : ''}
                </td>
            </tr>`;
    }

    function kanbanCard(app, stage) {
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
                aria-label="${esc(a.name || 'Kandidat')}, skor ${Math.round(app.match_score || 0)} persen. Tekan Enter untuk detail.">
                <div class="flex items-start gap-3">
                    <div class="min-w-0 flex-1">
                        <h3 class="font-semibold text-[15px] leading-snug truncate">${esc(a.name || 'Kandidat')}</h3>
                        <p class="ws-meta truncate">${showJobColumn ? esc(app.job_vacancy?.title || '-') : esc(a.location || '')}</p>
                    </div>
                    ${scoreRing(app.match_score, 'sm')}
                </div>
                ${top.length || missing ? `
                <div class="mt-3 flex flex-wrap gap-1">
                    ${top.map((s) => `<span class="ws-chip">${esc(s)}</span>`).join('')}
                    ${more > 0 ? `<span class="ws-chip">+${more}</span>` : ''}
                    ${missing ? `<span class="ws-chip ws-chip-miss" title="Belum dimiliki: ${esc((app.missing_skills || []).join(', '))}">${missing} kurang</span>` : ''}
                </div>` : ''}
                ${ready ? `<p class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--ws-success)]">${icon('sparkles', 'w-3.5 h-3.5')}Skor tinggi, siap ditinjau</p>` : ''}
                ${schedule ? `<p class="mt-2.5 inline-flex items-center gap-1.5 text-xs font-semibold text-[var(--ws-warning)]">${icon('calendar-clock', 'w-3.5 h-3.5')}${formatDateTime(schedule.datetime)}</p>` : ''}
                <div class="mt-3 pt-2.5 border-t border-[var(--ws-border)] flex items-center gap-2">
                    <span class="ws-meta">${days === null || final ? '' : days === 0 ? 'Masuk hari ini' : `${days} hari di tahap ini`}</span>
                    ${next && !final ? `<button type="button" class="ws-kcard-next ml-auto" data-advance="${app.id}" aria-label="Majukan ${esc(a.name || 'kandidat')} ke ${STAGE[next].label}">${STAGE[next].label}${icon('arrow-right', 'w-3.5 h-3.5')}</button>` : ''}
                </div>
            </article>`;
    }

    function renderBoard(list) {
        state.selected.clear();
        const grouped = Object.fromEntries(STAGES.map((s) => [s, []]));
        list.forEach((a) => grouped[stageOf(a)].push(a));
        const mobileStage = state.stage === 'all' ? 'screening' : state.stage;
        const emptyText = (s) => (state.cat !== 'all' || state.q ? 'Tidak ada yang cocok dengan saringan.' : {
            screening: 'Belum ada lamaran baru.', assessment: 'Belum ada yang dinilai.', interview: 'Belum ada jadwal wawancara.',
            offering: 'Belum ada penawaran.', hired: 'Belum ada yang diterima.', rejected: 'Belum ada yang ditolak.',
        }[s]);

        $('#cw-view').innerHTML = `
            <div class="ws-board ${state.open.hired ? 'show-hired' : ''} ${state.open.rejected ? 'show-rejected' : ''}" data-mobile-stage="${mobileStage}">
                ${STAGES.map((s) => {
                    const items = grouped[s];
                    const foldable = s === 'hired' || s === 'rejected';
                    const folded = foldable && !state.open[s] && desktop.matches;
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
                            ${folded ? '' : (items.length ? items.map((a) => kanbanCard(a, s)).join('') : `<p class="ws-col-empty">${emptyText(s)}</p>`)}
                        </div>
                    </section>`;
                }).join('')}
            </div>`;
    }

    // ------------------------------------------------------------------ aksi massal (tampilan tabel)
    function selectedApps() {
        return [...state.selected].map(findApp).filter(Boolean);
    }

    function bulkAdvanceTarget(list) {
        if (!list.length) return null;
        const stages = new Set(list.map(stageOf));
        if (stages.size !== 1) return null;
        const next = nextStageOf([...stages][0]);
        // Interview butuh jadwal per kandidat dan Hired butuh konfirmasi tawaran: tetap satu per satu
        return next === 'assessment' || next === 'offering' ? next : null;
    }

    function renderBulk() {
        const bar = $('#cw-bulk');
        const list = state.view === 'table' ? selectedApps() : [];
        if (!list.length) {
            bar.classList.add('hidden');
            bar.innerHTML = '';
            return;
        }
        const target = bulkAdvanceTarget(list);
        const reason = target ? '' : new Set(list.map(stageOf)).size > 1
            ? 'Pilih kandidat dari tahap yang sama untuk memajukan bersama.'
            : 'Wawancara dan penerimaan diproses satu per satu karena butuh jadwal atau konfirmasi.';
        bar.classList.remove('hidden');
        bar.innerHTML = `
            <span class="text-sm font-semibold">${list.length} kandidat dipilih</span>
            <span class="flex-1"></span>
            <span ${reason ? `data-tip="${esc(reason)}"` : ''}><button type="button" class="ws-btn ws-btn-sm ws-btn-secondary" data-bulk="advance" ${target ? '' : 'disabled'}>${icon('arrow-right')}${target ? `Majukan ke ${STAGE[target].label}` : 'Majukan'}</button></span>
            <button type="button" class="ws-btn ws-btn-sm ws-btn-secondary" data-bulk="reject">${icon('circle-x')}Tolak</button>
            <button type="button" class="ws-btn ws-btn-sm ws-btn-ghost" data-bulk="clear">Batal pilih</button>`;
        hydrateIcons(bar);
    }

    async function runBulk(list, target, extra) {
        let ok = 0;
        const failed = [];
        for (const app of list) {
            try {
                await moveCandidate(app, target, extra);
                ok += 1;
            } catch (err) {
                failed.push(`${app.applicant?.name || 'Kandidat'}: ${errorMessage(err)}`);
            }
        }
        state.selected.clear();
        render();
        onChange();
        if (ok) toast(target === 'rejected' ? `${ok} kandidat ditolak.` : `${ok} kandidat maju ke ${STAGE[target].label}.`, 'success');
        if (failed.length) toast(`${failed.length} gagal diproses. ${failed[0]}`, 'error', 6000);
    }

    async function bulkAdvance() {
        const list = selectedApps();
        const target = bulkAdvanceTarget(list);
        if (!target) return;
        const ok = await confirm({
            title: `Majukan ${list.length} kandidat ke ${STAGE[target].label}?`,
            message: 'Tahap seleksi hanya bisa maju. Keputusan ini tidak dapat dibatalkan.',
            confirmText: 'Majukan',
            cancelText: 'Batal',
        });
        if (ok) await runBulk(list, target, {});
    }

    function bulkReject() {
        const list = selectedApps();
        if (!list.length) return;
        const form = rejectDialog.querySelector('form');
        form.reset();
        form.querySelector('[data-error-for="notes"]').textContent = '';
        rejectDialog.querySelector('[data-reject-count]').textContent = `${list.length} kandidat akan ditandai tidak dilanjutkan.`;
        openDialog(rejectDialog);
        form.notes.focus();
        form.onsubmit = async (e) => {
            e.preventDefault();
            const notes = form.notes.value.trim();
            if (!notes) {
                form.querySelector('[data-error-for="notes"]').textContent = 'Tulis alasan singkat supaya keputusan bisa ditelusuri.';
                form.notes.focus();
                return;
            }
            const btn = form.querySelector('[type="submit"]');
            setBusy(btn, true, 'Memproses...');
            await runBulk(list, 'rejected', { notes });
            setBusy(btn, false);
            rejectDialog.close();
        };
    }

    // ------------------------------------------------------------------ perpindahan satu kandidat
    async function quickMove(app, target) {
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
        if (target === 'hired' || target === 'rejected') state.open[target] = true;
        if (!desktop.matches && state.view === 'board') state.stage = target;
        render();
        onChange();
        const moved = $(`#cw-view [data-app="${app.id}"]`);
        if (moved?.classList.contains('ws-kcard')) moved.classList.add('is-arrived');
        moved?.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
    }

    // ------------------------------------------------------------------ laci kandidat
    function openDrawer(app, mode = null) {
        if (!app) return;
        const job = jobById(jobs, app.job_vacancy_id) || app.job_vacancy;
        drawer.querySelector('[data-d-avatar]').textContent = initials(app.applicant?.name);
        drawer.querySelector('[data-d-name]').textContent = app.applicant?.name || 'Kandidat';
        drawer.querySelector('[data-d-job]').textContent = `${app.job_vacancy?.title || '-'} · melamar ${relativeTime(app.created_at)}`;
        drawer.querySelector('[data-d-stage]').innerHTML = stageBadge(stageOf(app));
        drawer.querySelector('[data-d-full]').href = `/recruiter/candidates/${app.id}`;
        const body = drawer.querySelector('[data-d-body]');
        body.innerHTML = [matchSection(app, job), skillsSection(app), notesSection(app), profileSection(app), documentsSection(app), timelineSection(app)]
            .join('<div class="ws-divider"></div>');
        bindCvDownload(body);
        bindNotes(body, app);
        renderActions(drawer.querySelector('[data-d-actions]'), app, {
            mode,
            onMoved: (moved, target) => {
                drawer.close();
                afterMove(moved, target);
            },
        });
        hydrateIcons(drawer);
        openDialog(drawer);
        body.scrollTop = 0;
        const p = new URLSearchParams(window.location.search);
        p.set('candidate', app.id);
        history.replaceState(null, '', `${location.pathname}?${p}`);
    }

    drawer.addEventListener('close', () => {
        const p = new URLSearchParams(window.location.search);
        p.delete('candidate');
        history.replaceState(null, '', `${location.pathname}${p.toString() ? `?${p}` : ''}`);
    });

    // ------------------------------------------------------------------ pendengar
    if (showJobColumn) {
        const sel = $('#cw-job');
        const options = jobs.filter((j) => j.status !== 'archived' || apps.some((a) => String(a.job_vacancy_id) === String(j.id)));
        sel.innerHTML = `<option value="all">Semua lowongan (${apps.length} kandidat)</option>` + options.map((j) => {
            const n = apps.filter((a) => String(a.job_vacancy_id) === String(j.id)).length;
            const tag = j.status === 'open' ? '' : ` · ${j.status === 'draft' ? 'draft' : j.status === 'closed' ? 'ditutup' : 'arsip'}`;
            return `<option value="${j.id}">${esc(j.title)} (${n})${tag}</option>`;
        }).join('');
        sel.value = state.job;
        sel.addEventListener('change', () => { state.job = sel.value; state.selected.clear(); syncUrl(); render(); });
    }

    $('#cw-search').value = state.q;
    $('#cw-sort').value = state.sort;
    $('#cw-search').addEventListener('input', debounce((e) => { state.q = e.target.value; syncUrl(); render(); }, 180));
    $('#cw-sort').addEventListener('change', (e) => { state.sort = e.target.value; syncUrl(); render(); });

    root.addEventListener('click', (e) => {
        const view = e.target.closest('[data-view]');
        if (view) {
            state.view = view.dataset.view;
            try { localStorage.setItem(VIEW_KEY, state.view); } catch (err) { /* abaikan */ }
            render();
            return;
        }
        const cat = e.target.closest('#cw-cat [data-cat]');
        if (cat) { state.cat = cat.dataset.cat; syncUrl(); render(); return; }
        const tab = e.target.closest('#cw-tabs [data-stage]');
        if (tab) {
            state.stage = tab.dataset.stage;
            if (state.stage === 'hired' || state.stage === 'rejected') state.open[state.stage] = true;
            syncUrl();
            render();
            return;
        }
        const bulk = e.target.closest('[data-bulk]');
        if (bulk) {
            if (bulk.dataset.bulk === 'clear') { state.selected.clear(); render(); }
            if (bulk.dataset.bulk === 'advance') bulkAdvance();
            if (bulk.dataset.bulk === 'reject') bulkReject();
            return;
        }
        const adv = e.target.closest('[data-advance]');
        if (adv) {
            e.stopPropagation();
            const app = findApp(adv.dataset.advance);
            if (app) quickMove(app, nextStageOf(stageOf(app)));
            return;
        }
        const toggle = e.target.closest('[data-toggle]');
        if (toggle) { state.open[toggle.dataset.toggle] = !state.open[toggle.dataset.toggle]; render(); return; }
        if (e.target.closest('[data-stop]')) return;
        const row = e.target.closest('[data-open], tr[data-app], .ws-kcard');
        if (row) openDrawer(findApp(row.dataset.open || row.dataset.app));
    });

    root.addEventListener('change', (e) => {
        if (e.target.matches('[data-check-all]')) {
            const rows = [...root.querySelectorAll('[data-check]:not(:disabled)')];
            rows.forEach((c) => (e.target.checked ? state.selected.add(c.dataset.check) : state.selected.delete(c.dataset.check)));
            render();
        } else if (e.target.matches('[data-check]')) {
            if (e.target.checked) state.selected.add(e.target.dataset.check);
            else state.selected.delete(e.target.dataset.check);
            e.target.closest('tr')?.classList.toggle('is-selected', e.target.checked);
            const all = root.querySelector('[data-check-all]');
            const boxes = [...root.querySelectorAll('[data-check]:not(:disabled)')];
            if (all) all.checked = boxes.length > 0 && boxes.every((c) => c.checked);
            renderBulk();
        }
    });

    root.addEventListener('keydown', (e) => {
        const c = e.target.closest('.ws-kcard');
        if (c && e.target === c && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            openDrawer(findApp(c.dataset.app));
        }
    });

    initDrag();
    desktop.addEventListener('change', () => render());

    function initDrag() {
        root.addEventListener('dragstart', (e) => {
            const c = e.target.closest('.ws-kcard');
            if (!c) return;
            state.dragFrom = c.dataset.stage;
            e.dataTransfer.setData('text/plain', c.dataset.app);
            e.dataTransfer.effectAllowed = 'move';
            c.classList.add('is-dragging');
            const next = nextStageOf(state.dragFrom);
            root.querySelectorAll('.ws-col').forEach((col) => {
                const s = col.dataset.stage;
                if (s === state.dragFrom) return;
                col.classList.add(s === next || s === 'rejected' ? 'is-drop-ok' : 'is-drop-no');
            });
        });
        root.addEventListener('dragend', () => {
            state.dragFrom = null;
            root.querySelectorAll('.is-dragging').forEach((el) => el.classList.remove('is-dragging'));
            root.querySelectorAll('.ws-col').forEach((col) => col.classList.remove('is-drop-ok', 'is-drop-no', 'is-drop-over'));
        });
        root.addEventListener('dragover', (e) => {
            const col = e.target.closest('.ws-col.is-drop-ok');
            if (!col) return;
            e.preventDefault();
            root.querySelectorAll('.is-drop-over').forEach((c) => c !== col && c.classList.remove('is-drop-over'));
            col.classList.add('is-drop-over');
        });
        root.addEventListener('drop', (e) => {
            const col = e.target.closest('.ws-col');
            if (!col) return;
            e.preventDefault();
            const app = findApp(e.dataTransfer.getData('text/plain'));
            if (!app) return;
            if (!col.classList.contains('is-drop-ok')) {
                if (col.dataset.stage !== state.dragFrom) toast('Tahap hanya bisa maju satu langkah atau ditolak.', 'warning');
                return;
            }
            quickMove(app, col.dataset.stage);
        });
    }

    render();
    const focus = params.get('candidate');
    if (focus) openDrawer(findApp(focus));

    return { render, openDrawer };
}

// ------------------------------------------------------------------ elemen dialog bersama
function ensureDrawer() {
    let d = document.getElementById('cw-drawer');
    if (d) return d;
    d = document.createElement('dialog');
    d.id = 'cw-drawer';
    d.className = 'ws-drawer';
    d.setAttribute('aria-labelledby', 'cw-d-name');
    d.innerHTML = `
        <header class="flex items-start gap-3 p-5 border-b border-[var(--ws-border)]">
            <span class="ws-avatar w-12 h-12 text-base" data-d-avatar>?</span>
            <div class="min-w-0 flex-1">
                <h2 id="cw-d-name" class="text-lg font-bold truncate" data-d-name>Kandidat</h2>
                <p class="ws-meta truncate" data-d-job>-</p>
                <div class="mt-2 flex flex-wrap items-center gap-2" data-d-stage></div>
            </div>
            <button type="button" class="ws-icon-btn -mr-2 -mt-1" data-dialog-close aria-label="Tutup">${icon('x', 'w-5 h-5')}</button>
        </header>
        <div class="flex-1 overflow-y-auto p-5 space-y-7" data-d-body></div>
        <footer class="border-t border-[var(--ws-border)] p-5 space-y-3">
            <div data-d-actions></div>
            <a href="#" class="ws-btn ws-btn-ghost ws-btn-sm w-full" data-d-full>Buka halaman lengkap kandidat${icon('arrow-up-right')}</a>
        </footer>`;
    document.body.appendChild(d);
    return d;
}

function ensureRejectDialog() {
    let d = document.getElementById('cw-reject');
    if (d) return d;
    d = document.createElement('dialog');
    d.id = 'cw-reject';
    d.className = 'ws-dialog';
    d.setAttribute('aria-labelledby', 'cw-reject-title');
    d.innerHTML = `
        <form novalidate>
            <div class="ws-dialog-head">
                <div>
                    <h2 id="cw-reject-title" class="ws-h2">Tolak kandidat terpilih</h2>
                    <p class="ws-meta mt-0.5" data-reject-count></p>
                </div>
                <button type="button" class="ws-icon-btn -mr-2" data-dialog-close aria-label="Tutup">${icon('x', 'w-5 h-5')}</button>
            </div>
            <div class="ws-dialog-body">
                <label for="cw-reject-notes" class="ws-label">Alasan penolakan</label>
                <textarea id="cw-reject-notes" name="notes" rows="3" maxlength="1000" class="ws-input" placeholder="contoh: Posisi sudah terisi oleh kandidat lain."></textarea>
                <p class="ws-error" data-error-for="notes"></p>
                <p class="ws-help">Alasan yang sama dicatat di linimasa setiap kandidat. Keputusan tidak dapat dibatalkan.</p>
            </div>
            <div class="ws-dialog-foot">
                <button type="button" class="ws-btn ws-btn-ghost" data-dialog-close>Batal</button>
                <button type="submit" class="ws-btn ws-btn-danger">Tolak kandidat</button>
            </div>
        </form>`;
    document.body.appendChild(d);
    return d;
}
