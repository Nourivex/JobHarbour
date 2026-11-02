/**
 * Ruang kerja kandidat yang dipakai halaman Kandidat (semua lowongan) dan halaman satu lowongan.
 *
 * Susunan dari atas ke bawah:
 * 1. Strip tahap: sebaran kandidat per tahap sekaligus saringan tahap.
 * 2. Satu baris toolbar: cari, Filter (skor, skill, tanggal melamar), urutkan, Tabel/Kanban.
 * 3. Daftar kandidat (tabel berpaginasi atau kanban).
 * 4. Laci kandidat bertab dengan navigasi ke kandidat sebelum/berikutnya.
 *
 * Semua angka berasal dari GET /applications dan GET /jobs/my; perpindahan tahap lewat moveCandidate()
 * yang mengikuti aturan backend (maju satu langkah atau ditolak).
 */
import { toast, confirm, util } from '../api.js';
import { jobById, moveCandidate } from './data.js';
import { setNavCounts } from './shell.js';
import { openDialog, setBusy, debounce, menu } from './components.js';
import {
    matchSection, profileSection, documentsSection, historySection, timelineSection, notesSection,
    renderActions, bindCvDownload, bindNotes,
} from './candidate-view.js';
import {
    esc, icon, hydrateIcons, STAGES, STAGE, stageOf, nextStageOf, stageEnteredAt, interviewOf, scoreInfo,
    matchBreakdown, relativeTime, formatDate, formatDateTime, daysSince, initials, emptyState, errorMessage,
} from './ui.js';

const VIEW_KEY = 'jh_ws_view';
const PAGE_SIZE = 25;
const desktop = window.matchMedia('(min-width: 1024px)');
const SORTS = {
    score: ['Skor tertinggi', (a, b) => (Number(b.match_score) || 0) - (Number(a.match_score) || 0)],
    newest: ['Lamaran terbaru', (a, b) => new Date(b.created_at) - new Date(a.created_at)],
    waiting: ['Terlama di tahap', (a, b) => new Date(stageEnteredAt(a)) - new Date(stageEnteredAt(b))],
    name: ['Nama (A sampai Z)', (a, b) => String(a.applicant?.name || '').localeCompare(String(b.applicant?.name || ''), 'id')],
};
const SCORE_BANDS = { excellent: '80% ke atas', good: '50 sampai 79%', under: 'Di bawah 50%' };
const APPLIED = { 7: '7 hari terakhir', 30: '30 hari terakhir' };

function readView() {
    try { return localStorage.getItem(VIEW_KEY) === 'board' ? 'board' : 'table'; } catch (e) { return 'table'; }
}
const norm = (s) => String(s || '').toLowerCase().trim();

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
        cat: SCORE_BANDS[params.get('cat')] ? params.get('cat') : 'all',
        skills: new Set((params.get('skill') || '').split(',').map(norm).filter(Boolean)),
        applied: APPLIED[params.get('applied')] ? params.get('applied') : 'all',
        q: params.get('q') || '',
        sort: SORTS[params.get('sort')] ? params.get('sort') : 'score',
        view: ['table', 'board'].includes(params.get('view')) ? params.get('view') : readView(),
        page: 1,
        open: { hired: false, rejected: false },
        selected: new Set(),
        dragFrom: null,
        filterOpen: false,
        drawerId: null,
        drawerTab: 'summary',
    };
    if (state.job !== 'all' && !jobById(jobs, state.job)) state.job = 'all';
    if (state.stage === 'hired' || state.stage === 'rejected') state.open[state.stage] = true;

    const showJob = !jobId;
    root.innerHTML = `
        <section aria-label="Sebaran kandidat per tahap" class="mb-5">
            <div id="cw-strip" class="ws-strip"></div>
            <div id="cw-prop" class="ws-proportion" aria-hidden="true"></div>
        </section>
        <div class="ws-wbar mb-3">
            ${showJob ? `<label for="cw-job" class="sr-only">Lowongan</label><select id="cw-job" class="ws-input w-full sm:w-[260px]"></select>` : ''}
            <div class="ws-search">
                ${icon('search')}
                <label for="cw-search" class="sr-only">Cari kandidat</label>
                <input id="cw-search" type="search" class="ws-input" placeholder="Cari nama atau skill" autocomplete="off">
            </div>
            <div class="ws-pop-wrap" id="cw-filter-wrap">
                <button type="button" id="cw-filter-btn" class="ws-btn ws-btn-secondary" aria-expanded="false" aria-controls="cw-filter-pop">${icon('list-filter')}Filter<span id="cw-filter-count" class="ws-count hidden"></span></button>
                <div id="cw-filter-pop" class="ws-pop hidden" role="dialog" aria-label="Saring kandidat"></div>
            </div>
            <label for="cw-sort" class="sr-only">Urutkan</label>
            <select id="cw-sort" class="ws-input w-auto">${Object.entries(SORTS).map(([k, [label]]) => `<option value="${k}">${label}</option>`).join('')}</select>
            <div class="ws-seg sm:ml-auto" role="group" aria-label="Tampilan">
                <button type="button" data-view="table" aria-pressed="false">${icon('table-2', 'w-4 h-4')}Tabel</button>
                <button type="button" data-view="board" aria-pressed="false">${icon('kanban', 'w-4 h-4')}Kanban</button>
            </div>
        </div>
        <div id="cw-chips" class="ws-fchips mb-3"></div>
        <div id="cw-view" aria-live="polite"></div>
        <div id="cw-bulk" class="ws-bulk hidden" role="region" aria-label="Aksi untuk kandidat terpilih"></div>`;

    const $ = (sel) => root.querySelector(sel);
    const drawer = ensureDrawer();
    const rejectDialog = ensureRejectDialog();
    const jobOf = (app) => jobById(jobs, app.job_vacancy_id) || app.job_vacancy;
    const findApp = (id) => apps.find((a) => String(a.id) === String(id));

    // ------------------------------------------------------------------ data turunan
    const scoped = () => (state.job === 'all' ? apps : apps.filter((a) => String(a.job_vacancy_id) === state.job));

    /** Lamaran setelah semua saringan kecuali tahap (dipakai strip supaya hitungan per tahap tetap terlihat). */
    function filteredNoStage() {
        const q = norm(state.q);
        const since = state.applied === 'all' ? null : Date.now() - Number(state.applied) * 86400000;
        return scoped().filter((a) => {
            if (state.cat !== 'all' && scoreInfo(a.match_score).key !== state.cat) return false;
            if (since && new Date(a.created_at).getTime() < since) return false;
            if (state.skills.size) {
                const have = new Set([...(a.matched_skills || []), ...(a.applicant?.skills || [])].map(norm));
                for (const s of state.skills) if (!have.has(s)) return false;
            }
            if (!q) return true;
            return [a.applicant?.name, a.job_vacancy?.title, ...(a.matched_skills || []), ...(a.applicant?.skills || [])]
                .filter(Boolean).join(' ').toLowerCase().includes(q);
        }).sort(SORTS[state.sort][1]);
    }
    const visibleList = () => {
        const list = filteredNoStage();
        return state.stage === 'all' ? list : list.filter((a) => stageOf(a) === state.stage);
    };

    /** Skill wajib: berapa yang dimiliki dari total, memakai bobot lowongan yang sama dengan MatchmakingService. */
    function requiredInfo(app) {
        const b = matchBreakdown(app, jobOf(app));
        const req = b.rows.filter((r) => r.level === 'required');
        if (!req.length) return null;
        return { ok: req.filter((r) => r.ok).length, total: req.length, missing: req.filter((r) => !r.ok).map((r) => r.name) };
    }

    function skillOptions() {
        const counts = new Map();
        scoped().forEach((a) => {
            const job = jobOf(a);
            (job?.requirements || []).forEach((r) => {
                const n = util.skillName(r);
                if (n) counts.set(n, (counts.get(n) || 0) + 1);
            });
        });
        return [...counts.keys()].sort((x, y) => x.localeCompare(y, 'id'));
    }

    function syncUrl() {
        const p = new URLSearchParams(window.location.search);
        const set = (k, v, def) => (v && v !== def ? p.set(k, v) : p.delete(k));
        if (!jobId) set('job', state.job, 'all');
        set('stage', state.stage, 'all');
        set('cat', state.cat, 'all');
        set('applied', state.applied, 'all');
        set('skill', [...state.skills].join(','), '');
        set('q', state.q.trim(), '');
        set('sort', state.sort, 'score');
        p.delete('view');
        if (state.drawerId) p.set('candidate', state.drawerId); else p.delete('candidate');
        history.replaceState(null, '', `${location.pathname}${p.toString() ? `?${p}` : ''}`);
    }

    // ------------------------------------------------------------------ strip tahap
    function renderStrip(list) {
        const counts = Object.fromEntries(STAGES.map((s) => [s, 0]));
        list.forEach((a) => { counts[stageOf(a)] += 1; });
        const items = [['all', 'Semua', list.length, null], ...STAGES.map((s) => [s, STAGE[s].label, counts[s], STAGE[s].color])];
        $('#cw-strip').innerHTML = items.map(([key, label, n, color]) => `
            <button type="button" class="ws-stage-btn ${n ? '' : 'is-empty'}" data-stage="${key}" aria-pressed="${state.stage === key}">
                <span class="ws-stage-label">${color ? `<span class="ws-dot" style="--c:${color}"></span>` : ''}${label}</span>
                <span class="ws-stage-num">${n}</span>
            </button>`).join('');
        $('#cw-prop').innerHTML = list.length
            ? STAGES.filter((s) => counts[s]).map((s) => `<span style="flex-grow:${counts[s]};background:${STAGE[s].color}" title="${STAGE[s].label}: ${counts[s]}"></span>`).join('')
            : '';
    }

    // ------------------------------------------------------------------ filter
    function activeFilterCount() {
        return (state.cat !== 'all') + (state.applied !== 'all') + state.skills.size;
    }

    function renderFilterPop() {
        const opts = skillOptions();
        $('#cw-filter-pop').innerHTML = `
            <div class="ws-pop-section">
                <p class="ws-pop-title">Skor kecocokan</p>
                ${[['all', 'Semua skor'], ...Object.entries(SCORE_BANDS)].map(([k, label]) => `
                    <label class="ws-opt"><input type="radio" name="cw-cat" value="${k}" ${state.cat === k ? 'checked' : ''}>${label}</label>`).join('')}
            </div>
            <div class="ws-pop-section">
                <p class="ws-pop-title">Tanggal melamar</p>
                ${[['all', 'Kapan saja'], ...Object.entries(APPLIED)].map(([k, label]) => `
                    <label class="ws-opt"><input type="radio" name="cw-applied" value="${k}" ${state.applied === k ? 'checked' : ''}>${label}</label>`).join('')}
            </div>
            <div class="ws-pop-section">
                <p class="ws-pop-title">Memiliki skill</p>
                ${opts.length ? `<div class="ws-skill-pick">${opts.map((s) => `<button type="button" data-skill="${esc(s)}" aria-pressed="${state.skills.has(norm(s))}">${esc(s)}</button>`).join('')}</div>`
                    : '<p class="ws-meta">Lowongan belum punya syarat skill.</p>'}
            </div>
            <div class="flex justify-between items-center mt-4 pt-3 border-t border-[var(--ws-border)]">
                <button type="button" class="ws-btn ws-btn-ghost ws-btn-sm" data-filter-reset ${activeFilterCount() ? '' : 'disabled'}>Hapus semua</button>
                <button type="button" class="ws-btn ws-btn-primary ws-btn-sm" data-filter-close>Selesai</button>
            </div>`;
    }

    function setFilterOpen(open) {
        state.filterOpen = open;
        $('#cw-filter-pop').classList.toggle('hidden', !open);
        $('#cw-filter-btn').setAttribute('aria-expanded', String(open));
        if (open) {
            renderFilterPop();
            $('#cw-filter-pop').querySelector('input:checked')?.focus();
        }
    }

    function renderChips() {
        const chips = [];
        if (state.cat !== 'all') chips.push(['cat', `Skor ${SCORE_BANDS[state.cat]}`]);
        if (state.applied !== 'all') chips.push(['applied', `Melamar ${APPLIED[state.applied]}`]);
        state.skills.forEach((s) => chips.push([`skill:${s}`, `Skill: ${s}`]));
        $('#cw-chips').innerHTML = chips.map(([k, label]) => `<span class="ws-fchip">${esc(label)}<button type="button" data-chip="${esc(k)}" aria-label="Hapus saringan ${esc(label)}">${icon('x', 'w-3.5 h-3.5')}</button></span>`).join('')
            + (chips.length > 1 ? '<button type="button" class="ws-btn ws-btn-ghost ws-btn-sm" data-filter-reset>Hapus semua</button>' : '');
        $('#cw-chips').classList.toggle('hidden', !chips.length);
        const n = activeFilterCount();
        $('#cw-filter-count').textContent = n;
        $('#cw-filter-count').classList.toggle('hidden', !n);
    }

    // ------------------------------------------------------------------ render utama
    function render() {
        const all = filteredNoStage();
        root.querySelectorAll('[data-view]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.view === state.view)));
        renderStrip(all);
        renderChips();
        setNavCounts(navApps);

        if (!scoped().length) {
            $('#cw-view').innerHTML = `<div class="ws-clist">${emptyState({
                iconName: 'users',
                title: 'Belum ada pelamar',
                text: jobs.some((j) => j.status === 'open') ? 'Kandidat muncul di sini setelah pelamar mengirim lamaran.' : 'Publikasikan lowongan agar pelamar bisa mulai melamar.',
                action: jobId ? '' : '<a href="/recruiter/jobs" class="ws-btn ws-btn-primary">Kelola lowongan</a>',
            })}</div>`;
        } else if (state.view === 'board') {
            renderBoard(all);
        } else {
            renderTable(visibleList());
        }
        renderBulk();
        hydrateIcons(root);
    }

    function noResults() {
        const filtered = state.q || activeFilterCount();
        return `<div class="ws-clist">${emptyState({
            iconName: 'search',
            title: filtered ? 'Tidak ada kandidat yang cocok' : `Belum ada kandidat di tahap ${STAGE[state.stage]?.label || ''}`,
            text: filtered ? 'Ubah kata kunci atau hapus sebagian saringan.' : 'Kandidat muncul di sini saat dipindahkan ke tahap ini.',
            action: filtered ? '<button type="button" class="ws-btn ws-btn-secondary" data-filter-reset>Hapus saringan</button>' : '',
        })}</div>`;
    }

    // ------------------------------------------------------------------ tabel
    function renderTable(rows) {
        const pages = Math.max(1, Math.ceil(rows.length / PAGE_SIZE));
        state.page = Math.min(state.page, pages);
        const start = (state.page - 1) * PAGE_SIZE;
        const pageRows = rows.slice(start, start + PAGE_SIZE);
        // Kandidat terpilih yang tidak lagi terlihat dilepas supaya aksi massal tidak mengenai baris tersembunyi
        const visibleIds = new Set(pageRows.map((a) => String(a.id)));
        [...state.selected].forEach((id) => { if (!visibleIds.has(id)) state.selected.delete(id); });

        if (!rows.length) {
            $('#cw-view').innerHTML = noResults();
            return;
        }
        const selectable = pageRows.filter((a) => !['hired', 'rejected'].includes(stageOf(a)));
        const allChecked = selectable.length > 0 && selectable.every((a) => state.selected.has(String(a.id)));
        $('#cw-view').innerHTML = `
            <div class="ws-clist">
                <table class="ws-ctable">
                    <thead><tr>
                        <th class="c-check"><input type="checkbox" class="ws-check" data-check-all ${allChecked ? 'checked' : ''} ${selectable.length ? '' : 'disabled'} aria-label="Pilih semua kandidat aktif di halaman ini"></th>
                        <th>Kandidat</th>
                        <th class="c-score">Skor</th>
                        <th class="c-req">Skill wajib</th>
                        <th class="c-stage">Tahap</th>
                        <th class="c-date hidden lg:table-cell">Melamar</th>
                        <th class="c-act"><span class="sr-only">Aksi</span></th>
                    </tr></thead>
                    <tbody>${pageRows.map(tableRow).join('')}</tbody>
                </table>
            </div>
            ${pages > 1 || rows.length > 10 ? `
            <div class="ws-pager">
                <span>Menampilkan ${start + 1} sampai ${start + pageRows.length} dari ${rows.length} kandidat</span>
                ${pages > 1 ? `<span class="ws-pager-btns">
                    <button type="button" class="ws-btn ws-btn-secondary ws-btn-sm" data-goto-page="${state.page - 1}" ${state.page === 1 ? 'disabled' : ''}>${icon('arrow-left')}Sebelumnya</button>
                    <button type="button" class="ws-btn ws-btn-secondary ws-btn-sm" data-goto-page="${state.page + 1}" ${state.page === pages ? 'disabled' : ''}>Berikutnya${icon('arrow-right')}</button>
                </span>` : ''}
            </div>` : ''}`;
    }

    function rowActions(app) {
        const stage = stageOf(app);
        const next = nextStageOf(stage);
        const final = stage === 'hired' || stage === 'rejected';
        const items = [{ label: 'Lihat detail', icon: icon('eye'), attrs: `data-open="${app.id}"` }];
        if (!final && next) {
            const label = next === 'interview' ? 'Jadwalkan wawancara' : next === 'hired' ? 'Tandai diterima' : `Majukan ke ${STAGE[next].label}`;
            items.push({ label, icon: icon(next === 'interview' ? 'calendar-clock' : next === 'hired' ? 'party-popper' : 'arrow-right'), attrs: `data-advance="${app.id}"` });
        }
        items.push({ label: 'Tulis catatan', icon: icon('sticky-note'), attrs: `data-open="${app.id}" data-tab="notes"` });
        if (!final) items.push('-', { label: 'Tolak kandidat', icon: icon('circle-x'), danger: true, attrs: `data-reject="${app.id}"` });
        return menu(`Aksi untuk ${esc(app.applicant?.name || 'kandidat')}`, items, { icon: icon('ellipsis', 'w-4 h-4') });
    }

    function scoreCell(app) {
        const info = scoreInfo(app.match_score);
        return `<span class="ws-score" title="${info.label}"><span class="ws-score-num">${info.score}%</span><span class="ws-score-bar" aria-hidden="true"><span style="width:${info.score}%;background:${info.color}"></span></span></span>`;
    }

    function stageSub(app, stage) {
        const final = stage === 'hired' || stage === 'rejected';
        const schedule = stage === 'interview' ? interviewOf(app) : null;
        if (schedule) return `${formatDateTime(schedule.datetime)}`;
        if (final) return formatDate(stageEnteredAt(app));
        const days = daysSince(stageEnteredAt(app));
        return days === null ? '' : days === 0 ? 'Masuk hari ini' : `${days} hari di tahap ini`;
    }

    function tableRow(app) {
        const a = app.applicant || {};
        const stage = stageOf(app);
        const final = stage === 'hired' || stage === 'rejected';
        const checked = state.selected.has(String(app.id));
        const req = requiredInfo(app);
        const reqText = req ? `<span class="ws-req"><strong>${req.ok}</strong>/${req.total}</span>` : '<span class="ws-meta">-</span>';
        const sub = showJob ? (app.job_vacancy?.title || '-') : (a.location || a.email || '');
        return `
            <tr data-app="${app.id}" class="${checked ? 'is-selected' : ''} ${final ? 'is-final' : ''}">
                <td class="c-check" data-stop><input type="checkbox" class="ws-check" data-check="${app.id}" ${checked ? 'checked' : ''} ${final ? 'disabled' : ''} aria-label="Pilih ${esc(a.name || 'kandidat')}"></td>
                <td class="min-w-0">
                    <div class="ws-cname">
                        <span class="ws-ava" aria-hidden="true">${esc(initials(a.name))}</span>
                        <span class="ws-cname-text">
                            <button type="button" class="ws-cname-title" data-open="${app.id}">${esc(a.name || 'Kandidat')}</button>
                            <span class="ws-cname-sub">${esc(sub)}</span>
                            <span class="ws-cmeta">${scoreCell(app)}<span class="ws-stage"><span class="ws-dot" style="--c:${STAGE[stage].color}"></span>${STAGE[stage].label}</span>${req ? `<span>${req.ok}/${req.total} skill wajib</span>` : ''}</span>
                        </span>
                    </div>
                </td>
                <td class="c-score">${scoreCell(app)}</td>
                <td class="c-req">${reqText}${req?.missing.length ? `<span class="ws-req-miss" title="Belum dimiliki: ${esc(req.missing.join(', '))}">Kurang: ${esc(req.missing.join(', '))}</span>` : ''}</td>
                <td class="c-stage"><span class="ws-stage"><span class="ws-dot" style="--c:${STAGE[stage].color}"></span>${STAGE[stage].label}</span><span class="ws-stage-sub">${esc(stageSub(app, stage))}</span></td>
                <td class="c-date hidden lg:table-cell"><span class="whitespace-nowrap">${formatDate(app.created_at)}</span><span class="ws-stage-sub">${relativeTime(app.created_at)}</span></td>
                <td class="c-act" data-stop><span class="ws-row-menu">${rowActions(app)}</span></td>
            </tr>`;
    }

    // ------------------------------------------------------------------ kanban
    function kanbanCard(app, stage) {
        const a = app.applicant || {};
        const final = stage === 'hired' || stage === 'rejected';
        const next = nextStageOf(stage);
        const req = requiredInfo(app);
        const info = scoreInfo(app.match_score);
        const schedule = stage === 'interview' ? interviewOf(app) : null;
        return `
            <article class="ws-kcard2" draggable="${final ? 'false' : 'true'}" data-app="${app.id}" data-stage="${stage}" tabindex="0"
                aria-label="${esc(a.name || 'Kandidat')}, skor ${info.score} persen. Tekan Enter untuk detail.">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="font-semibold text-[14px] leading-snug truncate">${esc(a.name || 'Kandidat')}</h3>
                        <p class="ws-meta truncate">${esc(showJob ? (app.job_vacancy?.title || '-') : (a.location || ''))}</p>
                    </div>
                    <span class="ws-score-num text-right" style="color:${info.color}">${info.score}%</span>
                </div>
                ${req ? `<p class="mt-2 text-[13px] ${req.missing.length ? 'text-[var(--ws-text-2)]' : 'text-[var(--ws-success)]'}">${req.missing.length ? `Kurang ${req.missing.length} skill wajib` : 'Semua skill wajib terpenuhi'}</p>` : ''}
                <div class="ws-kcard2-foot">
                    <span>${schedule ? `${icon('calendar-clock', 'w-3.5 h-3.5 inline -mt-0.5')} ${formatDateTime(schedule.datetime)}` : esc(stageSub(app, stage))}</span>
                    ${next && !final ? `<button type="button" class="ws-kcard-next" data-advance="${app.id}" aria-label="Majukan ${esc(a.name || 'kandidat')} ke ${STAGE[next].label}">${STAGE[next].label}${icon('arrow-right', 'w-3.5 h-3.5')}</button>` : ''}
                </div>
            </article>`;
    }

    function renderBoard(list) {
        state.selected.clear();
        if (!list.length) {
            $('#cw-view').innerHTML = noResults();
            return;
        }
        const grouped = Object.fromEntries(STAGES.map((s) => [s, []]));
        list.forEach((a) => grouped[stageOf(a)].push(a));
        const mobileStage = state.stage === 'all' ? 'screening' : state.stage;
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
                            ${folded ? '' : (items.length ? items.map((a) => kanbanCard(a, s)).join('') : `<p class="ws-col-empty">${state.q || activeFilterCount() ? 'Tidak ada yang cocok.' : 'Kosong'}</p>`)}
                        </div>
                    </section>`;
                }).join('')}
            </div>`;
    }

    // ------------------------------------------------------------------ aksi massal (tabel)
    const selectedApps = () => [...state.selected].map(findApp).filter(Boolean);

    function bulkAdvanceTarget(list) {
        if (!list.length) return null;
        const stages = new Set(list.map(stageOf));
        if (stages.size !== 1) return null;
        const next = nextStageOf([...stages][0]);
        // Wawancara butuh jadwal per kandidat dan penerimaan butuh konfirmasi tawaran: tetap satu per satu
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
            openDrawer(app, { mode: target === 'rejected' ? 'reject' : 'advance' });
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
        if (moved?.classList.contains('ws-kcard2')) moved.classList.add('is-arrived');
        moved?.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
    }

    // ------------------------------------------------------------------ laci kandidat
    const DRAWER_TABS = [['summary', 'Ringkasan'], ['profile', 'Profil & dokumen'], ['notes', 'Catatan'], ['history', 'Riwayat']];

    function otherSkills(app) {
        const known = new Set([...(app.matched_skills || []), ...(app.missing_skills || [])].map(norm));
        const other = (app.applicant?.skills || []).filter((s) => !known.has(norm(s)));
        return other.length ? `
            <section class="space-y-2">
                <h3 class="ws-h2">Skill lain di profil</h3>
                <p class="ws-meta">Tidak dihitung dalam skor karena tidak diminta lowongan ini.</p>
                <div class="flex flex-wrap gap-1.5">${other.map((s) => `<span class="ws-chip">${esc(s)}</span>`).join('')}</div>
            </section>` : '';
    }

    function drawerBody(app, tab) {
        const job = jobOf(app);
        if (tab === 'profile') return [profileSection(app), documentsSection(app)].join('<div class="ws-divider"></div>');
        if (tab === 'notes') return notesSection(app);
        if (tab === 'history') return [timelineSection(app), historySection(app, apps)].join('<div class="ws-divider"></div>');
        return [matchSection(app, job), otherSkills(app)].filter(Boolean).join('<div class="ws-divider"></div>');
    }

    function paintDrawerTab(app) {
        drawer.querySelectorAll('[data-dtab]').forEach((t) => t.setAttribute('aria-selected', String(t.dataset.dtab === state.drawerTab)));
        const body = drawer.querySelector('[data-d-body]');
        body.innerHTML = drawerBody(app, state.drawerTab);
        bindCvDownload(body);
        if (state.drawerTab === 'notes') bindNotes(body, app);
        hydrateIcons(body);
        body.scrollTop = 0;
    }

    function openDrawer(app, { mode = null, tab = 'summary' } = {}) {
        if (!app) return;
        state.drawerId = String(app.id);
        state.drawerTab = tab;
        const list = visibleList();
        const idx = list.findIndex((a) => String(a.id) === String(app.id));
        drawer.querySelector('[data-d-avatar]').textContent = initials(app.applicant?.name);
        drawer.querySelector('[data-d-name]').textContent = app.applicant?.name || 'Kandidat';
        drawer.querySelector('[data-d-job]').textContent = `${app.job_vacancy?.title || '-'} · melamar ${relativeTime(app.created_at)}`;
        const stage = stageOf(app);
        drawer.querySelector('[data-d-stage]').innerHTML = `<span class="ws-stage"><span class="ws-dot" style="--c:${STAGE[stage].color}"></span>${STAGE[stage].label}</span>`;
        drawer.querySelector('[data-d-full]').href = `/recruiter/candidates/${app.id}`;
        drawer.querySelector('[data-d-pos]').textContent = idx >= 0 ? `${idx + 1} / ${list.length}` : '';
        drawer.querySelector('[data-d-prev]').disabled = idx <= 0;
        drawer.querySelector('[data-d-next]').disabled = idx < 0 || idx >= list.length - 1;
        paintDrawerTab(app);
        renderActions(drawer.querySelector('[data-d-actions]'), app, {
            mode,
            onMoved: (moved, target) => {
                afterMove(moved, target);
                // Tetap di laci supaya recruiter bisa lanjut ke kandidat berikutnya
                openDrawer(moved, { tab: state.drawerTab });
            },
        });
        hydrateIcons(drawer);
        openDialog(drawer);
        syncUrl();
    }

    function stepDrawer(delta) {
        const list = visibleList();
        const idx = list.findIndex((a) => String(a.id) === state.drawerId);
        const next = list[idx + delta];
        if (next) openDrawer(next, { tab: state.drawerTab });
    }

    drawer.onclick = (e) => {
        const t = e.target.closest('[data-dtab]');
        if (t) {
            state.drawerTab = t.dataset.dtab;
            paintDrawerTab(findApp(state.drawerId));
            return;
        }
        if (e.target.closest('[data-d-prev]')) stepDrawer(-1);
        if (e.target.closest('[data-d-next]')) stepDrawer(1);
    };
    drawer.onkeydown = (e) => {
        if (e.target.closest('input, textarea, select')) return;
        if (e.key === 'j' || e.key === 'ArrowDown') { e.preventDefault(); stepDrawer(1); }
        if (e.key === 'k' || e.key === 'ArrowUp') { e.preventDefault(); stepDrawer(-1); }
    };
    drawer.onclose = () => {
        state.drawerId = null;
        syncUrl();
    };
    drawer.querySelector('[data-dtabs]').innerHTML = DRAWER_TABS.map(([k, label]) => `<button type="button" role="tab" class="ws-tab" data-dtab="${k}" aria-selected="false">${label}</button>`).join('');

    // ------------------------------------------------------------------ pendengar
    if (showJob) {
        const sel = $('#cw-job');
        const options = jobs.filter((j) => j.status !== 'archived' || apps.some((a) => String(a.job_vacancy_id) === String(j.id)));
        sel.innerHTML = `<option value="all">Semua lowongan (${apps.length})</option>` + options.map((j) => {
            const n = apps.filter((a) => String(a.job_vacancy_id) === String(j.id)).length;
            const tag = j.status === 'open' ? '' : ` · ${j.status === 'draft' ? 'draft' : j.status === 'closed' ? 'ditutup' : 'arsip'}`;
            return `<option value="${j.id}">${esc(j.title)} (${n})${tag}</option>`;
        }).join('');
        sel.value = state.job;
        sel.addEventListener('change', () => { state.job = sel.value; state.skills.clear(); state.page = 1; state.selected.clear(); syncUrl(); render(); });
    }

    $('#cw-search').value = state.q;
    $('#cw-sort').value = state.sort;
    $('#cw-search').addEventListener('input', debounce((e) => { state.q = e.target.value; state.page = 1; syncUrl(); render(); }, 180));
    $('#cw-sort').addEventListener('change', (e) => { state.sort = e.target.value; syncUrl(); render(); });

    const resetFilters = () => {
        state.cat = 'all';
        state.applied = 'all';
        state.skills.clear();
        state.q = '';
        $('#cw-search').value = '';
        state.page = 1;
        if (state.filterOpen) renderFilterPop();
        syncUrl();
        render();
    };

    $('#cw-filter-btn').addEventListener('click', () => setFilterOpen(!state.filterOpen));
    $('#cw-filter-pop').addEventListener('change', (e) => {
        if (e.target.name === 'cw-cat') state.cat = e.target.value;
        if (e.target.name === 'cw-applied') state.applied = e.target.value;
        state.page = 1;
        syncUrl();
        render();
    });
    document.addEventListener('click', (e) => {
        if (state.filterOpen && !e.target.closest('#cw-filter-wrap')) setFilterOpen(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && state.filterOpen) { setFilterOpen(false); $('#cw-filter-btn').focus(); }
    });

    root.addEventListener('click', (e) => {
        if (e.target.closest('[data-filter-reset]')) { resetFilters(); return; }
        if (e.target.closest('[data-filter-close]')) { setFilterOpen(false); $('#cw-filter-btn').focus(); return; }
        const sk = e.target.closest('[data-skill]');
        if (sk) {
            const s = norm(sk.dataset.skill);
            if (state.skills.has(s)) state.skills.delete(s); else state.skills.add(s);
            sk.setAttribute('aria-pressed', String(state.skills.has(s)));
            state.page = 1;
            syncUrl();
            render();
            return;
        }
        const chip = e.target.closest('[data-chip]');
        if (chip) {
            const k = chip.dataset.chip;
            if (k === 'cat') state.cat = 'all';
            else if (k === 'applied') state.applied = 'all';
            else if (k.startsWith('skill:')) state.skills.delete(k.slice(6));
            state.page = 1;
            syncUrl();
            render();
            return;
        }
        const view = e.target.closest('[data-view]');
        if (view) {
            state.view = view.dataset.view;
            try { localStorage.setItem(VIEW_KEY, state.view); } catch (err) { /* abaikan */ }
            render();
            return;
        }
        const st = e.target.closest('#cw-strip [data-stage]');
        if (st) {
            state.stage = st.dataset.stage;
            state.page = 1;
            if (state.stage === 'hired' || state.stage === 'rejected') state.open[state.stage] = true;
            syncUrl();
            render();
            return;
        }
        const pg = e.target.closest('[data-goto-page]');
        if (pg && !pg.disabled) { state.page = Number(pg.dataset.gotoPage) || 1; render(); $('#cw-strip').scrollIntoView({ block: 'nearest', behavior: 'smooth' }); return; }
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
        const rej = e.target.closest('[data-reject]');
        if (rej) { openDrawer(findApp(rej.dataset.reject), { mode: 'reject' }); return; }
        const toggle = e.target.closest('[data-toggle]');
        if (toggle) { state.open[toggle.dataset.toggle] = !state.open[toggle.dataset.toggle]; render(); return; }
        const opener = e.target.closest('[data-open]');
        if (opener) { openDrawer(findApp(opener.dataset.open), { tab: opener.dataset.tab || 'summary' }); return; }
        if (e.target.closest('[data-stop]')) return;
        const row = e.target.closest('tr[data-app], .ws-kcard2');
        if (row) openDrawer(findApp(row.dataset.app));
    });

    root.addEventListener('change', (e) => {
        if (e.target.matches('[data-check-all]')) {
            root.querySelectorAll('[data-check]:not(:disabled)').forEach((c) => (e.target.checked ? state.selected.add(c.dataset.check) : state.selected.delete(c.dataset.check)));
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
        const c = e.target.closest('.ws-kcard2');
        if (c && e.target === c && (e.key === 'Enter' || e.key === ' ')) {
            e.preventDefault();
            openDrawer(findApp(c.dataset.app));
        }
    });

    initDrag();
    desktop.addEventListener('change', () => render());

    function initDrag() {
        root.addEventListener('dragstart', (e) => {
            const c = e.target.closest('.ws-kcard2');
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
        <header class="px-5 pt-4 pb-3">
            <div class="flex items-center justify-between gap-3 mb-3">
                <span class="ws-dnav">
                    <button type="button" class="ws-icon-btn" data-d-prev aria-label="Kandidat sebelumnya" title="Sebelumnya (K)">${icon('arrow-left', 'w-4 h-4')}</button>
                    <button type="button" class="ws-icon-btn" data-d-next aria-label="Kandidat berikutnya" title="Berikutnya (J)">${icon('arrow-right', 'w-4 h-4')}</button>
                    <span data-d-pos class="ml-1"></span>
                </span>
                <span class="flex items-center gap-1">
                    <a href="#" class="ws-btn ws-btn-ghost ws-btn-sm" data-d-full>Halaman penuh${icon('arrow-up-right')}</a>
                    <button type="button" class="ws-icon-btn" data-dialog-close aria-label="Tutup">${icon('x', 'w-5 h-5')}</button>
                </span>
            </div>
            <div class="flex items-start gap-3">
                <span class="ws-ava w-11 h-11 text-[15px]" data-d-avatar aria-hidden="true">?</span>
                <div class="min-w-0 flex-1">
                    <h2 id="cw-d-name" class="text-[20px] leading-tight font-bold truncate" data-d-name>Kandidat</h2>
                    <p class="ws-meta truncate mt-0.5" data-d-job>-</p>
                </div>
                <div data-d-stage class="pt-1"></div>
            </div>
            <div class="mt-4" data-d-actions></div>
        </header>
        <nav class="ws-dtabs" role="tablist" aria-label="Bagian detail kandidat" data-dtabs></nav>
        <div class="flex-1 overflow-y-auto p-5 space-y-7" data-d-body></div>`;
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
