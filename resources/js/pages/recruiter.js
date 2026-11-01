/**
 * Papan Rekrutmen: pipeline kandidat (kanban) dan kelola lowongan.
 * Data hanya dari GET /api/jobs/my, GET /api/applications, GET /api/applications/{id},
 * PATCH /api/pipelines/{id}/status, POST/PUT /api/jobs, dan DELETE /api/jobs/{id} (tutup).
 */
import JH, { jobs, applications, pipelines, session, util, toast, showError, setLoading, confirm } from '../api.js';
import { scoreCategory, EMPLOYMENT_SHORT, salaryRange } from '../job-ui.js';

const esc = util.escapeHtml;
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const ACTIVE_STAGES = ['screening', 'assessment', 'interview', 'offering'];
const ALL_STAGES = [...ACTIVE_STAGES, 'rejected'];
const STAGE = {
    screening: { label: 'Screening', hint: 'Tinjau berkas', color: '#627080' },
    assessment: { label: 'Assessment', hint: 'Uji teknis', color: '#1B65A6' },
    interview: { label: 'Wawancara', hint: 'Jadwalkan dan nilai', color: '#C8460B' },
    offering: { label: 'Penawaran', hint: 'Tahap akhir', color: '#157F3B' },
    rejected: { label: 'Ditolak', hint: 'Tidak dilanjutkan', color: '#B42318' },
};
const LEVEL = {
    required: { label: 'Wajib', weight: 3, color: '#C8460B' },
    important: { label: 'Penting', weight: 2, color: '#1B65A6' },
    nice_to_have: { label: 'Bonus', weight: 1, color: '#627080' },
};

const ICON = {
    arrow: '<path d="M5 12h14M13 6l6 6-6 6"/>',
    x: '<path d="M18 6 6 18M6 6l12 12"/>',
    check: '<path d="m5 12 5 5 9-10"/>',
    bolt: '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>',
    calendar: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
    mail: '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
    phone: '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
    pin: '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    file: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M12 18v-6M9 15l3 3 3-3"/>',
    edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
    board: '<rect x="3" y="3" width="7" height="18" rx="1.5"/><rect x="14" y="3" width="7" height="11" rx="1.5"/>',
    lock: '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
    unlock: '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 7.5-2"/>',
    trash: '<path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/>',
    users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
    chevron: '<path d="m9 18 6-6-6-6"/>',
};

function icon(name, cls = 'w-4 h-4') {
    return `<svg class="${cls}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${ICON[name] || ''}</svg>`;
}

function scoreRing(score, size = 'sm') {
    const s = Math.round(Number(score) || 0);
    const cat = scoreCategory(s);
    const cls = size === 'lg' ? 'w-14 h-14 text-[14px] score-ring-md' : 'w-12 h-12 text-[11px] score-ring-sm';
    return `<span class="score-ring ${cls} font-extrabold num-tabular" style="--deg:${s * 3.6}deg;--ring:${cat.color}" title="Skor kecocokan ${s}% (${cat.label})"><span style="color:${cat.color}">${s}%</span></span>`;
}

function categoryKey(score) {
    return scoreCategory(Number(score) || 0).key;
}

function daysSince(iso) {
    if (!iso) return null;
    return Math.max(0, Math.floor((Date.now() - new Date(iso).getTime()) / 86400000));
}

function formatDateTime(iso) {
    if (!iso) return '-';
    const d = new Date(iso);
    if (Number.isNaN(d.getTime())) return '-';
    return d.toLocaleString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
}

function plural(n, word) {
    return `${n.toLocaleString('id-ID')} ${word}`;
}

document.addEventListener('DOMContentLoaded', () => {
    // ------------------------------------------------------------------ guard
    const currentUser = session.user();
    if (!session.isLoggedIn() || !currentUser) {
        window.location.replace('/login?next=' + encodeURIComponent('/recruiter'));
        return;
    }
    if (currentUser.role !== 'recruiter' && currentUser.role !== 'admin') {
        toast('Papan Rekrutmen hanya untuk akun recruiter.', 'warning', 3000);
        window.location.replace(JH.homeFor(currentUser));
        return;
    }
    const isAdmin = currentUser.role === 'admin';

    const state = {
        jobs: [],
        apps: [],
        jobFilter: 'all',
        search: '',
        cat: 'all',
        sort: 'score',
        focus: null, // review | ready | interview
        mobileStage: 'screening',
        rejectedOpen: false,
        tab: 'pipeline',
        drawerApp: null,
        drawerTab: 'summary',
        drawerAction: null, // advance | reject
        lastFocus: null,
        dragFrom: null,
        editingJobId: null,
    };

    const $ = (id) => document.getElementById(id);
    const board = $('rk-board');

    // ------------------------------------------------------------------ helpers data
    const jobById = (id) => state.jobs.find((j) => Number(j.id) === Number(id));

    function stageOf(app) {
        const raw = String(app.pipeline_timeline?.current_stage || app.status || 'screening').toLowerCase();
        if (raw === 'submitted') return 'screening';
        return ALL_STAGES.includes(raw) ? raw : 'screening';
    }

    function stageEnteredAt(app) {
        const stages = app.pipeline_timeline?.stages || [];
        return stages.length ? stages[stages.length - 1].entered_at : app.created_at;
    }

    function interviewOf(app) {
        const stages = app.pipeline_timeline?.stages || [];
        for (let i = stages.length - 1; i >= 0; i -= 1) {
            if (stages[i].interview_schedule?.datetime) return stages[i].interview_schedule;
        }
        return null;
    }

    function isUpcoming(schedule) {
        return schedule && new Date(schedule.datetime).getTime() >= Date.now() - 3600000;
    }

    const isReady = (app) => stageOf(app) === 'screening' && Number(app.match_score) >= 80;

    function appsForJob() {
        if (state.jobFilter === 'all') return state.apps;
        return state.apps.filter((a) => Number(a.job_vacancy_id) === Number(state.jobFilter));
    }

    function visibleApps() {
        const q = state.search.trim().toLowerCase();
        return appsForJob().filter((a) => {
            if (state.cat !== 'all' && categoryKey(a.match_score) !== state.cat) return false;
            if (state.focus === 'review' && stageOf(a) !== 'screening') return false;
            if (state.focus === 'ready' && !isReady(a)) return false;
            if (state.focus === 'interview' && !isUpcoming(interviewOf(a))) return false;
            if (q) {
                const hay = [a.applicant?.name, a.job_vacancy?.title, ...(a.matched_skills || []), ...(a.applicant?.skills || [])]
                    .filter(Boolean).join(' ').toLowerCase();
                if (!hay.includes(q)) return false;
            }
            return true;
        });
    }

    function sortApps(list) {
        const by = {
            score: (a, b) => (Number(b.match_score) || 0) - (Number(a.match_score) || 0),
            newest: (a, b) => new Date(b.created_at) - new Date(a.created_at),
            waiting: (a, b) => new Date(stageEnteredAt(a)) - new Date(stageEnteredAt(b)),
        };
        return [...list].sort(by[state.sort] || by.score);
    }

    /** Syarat lowongan dikelompokkan per tingkat dan ditandai cocok atau kurang. */
    function requirementBreakdown(app) {
        const job = jobById(app.job_vacancy_id) || app.job_vacancy || {};
        const matched = new Set((app.matched_skills || []).map((s) => String(s).toLowerCase().trim()));
        const groups = { required: [], important: [], nice_to_have: [] };
        (job.requirements || []).forEach((r) => {
            const name = util.skillName(r);
            if (!name) return;
            const level = groups[r.level] ? r.level : (Number(r.weight) === 2 ? 'important' : Number(r.weight) === 1 ? 'nice_to_have' : 'required');
            groups[level].push({ name, ok: matched.has(name.toLowerCase().trim()) });
        });
        const hasReq = Object.values(groups).some((g) => g.length);
        return hasReq ? groups : null;
    }

    // ------------------------------------------------------------------ load
    async function load() {
        $('rk-loading').classList.remove('hidden');
        $('rk-error').classList.add('hidden');
        board.classList.add('hidden');
        try {
            const [jobsRes, appsRes] = await Promise.all([jobs.mine(), applications.list()]);
            state.jobs = Array.isArray(jobsRes.data) ? jobsRes.data : [];
            state.apps = (Array.isArray(appsRes.data) ? appsRes.data : []).filter((a) => a.status !== 'canceled');
            if (state.jobFilter !== 'all' && !jobById(state.jobFilter)) state.jobFilter = 'all';
            $('rk-loading').classList.add('hidden');
            board.classList.remove('hidden');
            renderAll();
        } catch (err) {
            console.error('Gagal memuat papan rekrutmen:', err);
            $('rk-loading').classList.add('hidden');
            $('rk-error').classList.remove('hidden');
            $('rk-error-text').textContent = err?.message || 'Periksa koneksi ke server, lalu coba lagi.';
        }
    }

    function renderAll() {
        renderGreeting();
        renderJobFilter();
        renderMetrics();
        renderBoard();
        renderJobs();
        renderSkillSuggestions();
    }

    // ------------------------------------------------------------------ header & metrik
    function renderGreeting() {
        const first = esc((currentUser.name || 'Recruiter').split(/\s+/)[0]);
        const review = appsForJob().filter((a) => stageOf(a) === 'screening').length;
        const scope = isAdmin ? ' Kamu melihat semua lowongan sebagai admin.' : '';
        $('rk-greeting').innerHTML = review
            ? `Halo ${first}, <strong class="text-[#0B1B2B]">${plural(review, 'kandidat')}</strong> menunggu ditinjau.${scope}`
            : `Halo ${first}, tidak ada berkas yang menunggu ditinjau.${scope}`;
    }

    function renderJobFilter() {
        const select = $('rk-job-filter');
        const options = [`<option value="all">Semua lowongan (${state.jobs.length})</option>`];
        state.jobs.forEach((j) => {
            const count = state.apps.filter((a) => Number(a.job_vacancy_id) === Number(j.id)).length;
            const closed = j.status === 'open' ? '' : ' [ditutup]';
            options.push(`<option value="${j.id}">${esc(j.title)}${closed} · ${count} kandidat</option>`);
        });
        select.innerHTML = options.join('');
        select.value = String(state.jobFilter);
    }

    function renderMetrics() {
        const scoped = appsForJob();
        const review = scoped.filter((a) => stageOf(a) === 'screening');
        const ready = scoped.filter(isReady);
        const upcoming = scoped.map(interviewOf).filter(isUpcoming)
            .sort((a, b) => new Date(a.datetime) - new Date(b.datetime));
        const openJobs = state.jobs.filter((j) => j.status === 'open');
        const quota = openJobs.reduce((s, j) => s + (Number(j.quota) || 0), 0);
        const filled = openJobs.reduce((s, j) => s + (Number(j.filled) || 0), 0);

        $('rk-m-review').textContent = review.length;
        $('rk-m-review-sub').textContent = review.length ? 'di tahap Screening' : 'semua berkas sudah ditinjau';
        $('rk-m-ready').textContent = ready.length;
        $('rk-m-interview').textContent = upcoming.length;
        $('rk-m-interview-sub').textContent = upcoming.length ? `terdekat ${formatDateTime(upcoming[0].datetime)}` : 'belum ada jadwal';
        $('rk-m-jobs').textContent = openJobs.length;
        $('rk-m-jobs-sub').textContent = quota ? `kuota terisi ${filled}/${quota}` : 'belum ada kuota';

        document.querySelectorAll('.rk-metric[data-focus]').forEach((btn) => {
            btn.setAttribute('aria-pressed', String(state.focus === btn.dataset.focus));
        });
        $('rk-tab-pipeline-count').textContent = scoped.length;
        $('rk-tab-jobs-count').textContent = state.jobs.length;
    }

    // ------------------------------------------------------------------ papan
    function renderActiveFilter() {
        const el = $('rk-active-filter');
        const chips = [];
        const focusLabel = { review: 'Perlu ditinjau', ready: 'Siap dimajukan', interview: 'Wawancara mendatang' };
        if (state.focus) chips.push({ key: 'focus', label: focusLabel[state.focus] });
        if (state.jobFilter !== 'all') chips.push({ key: 'job', label: jobById(state.jobFilter)?.title || 'Lowongan' });
        if (state.cat !== 'all') chips.push({ key: 'cat', label: scoreCategory(state.cat === 'excellent' ? 90 : state.cat === 'good' ? 60 : 10).label });
        if (state.search.trim()) chips.push({ key: 'search', label: `"${state.search.trim()}"` });
        if (!chips.length) {
            el.classList.add('hidden');
            el.innerHTML = '';
            return;
        }
        el.classList.remove('hidden');
        el.innerHTML = `<span class="text-[#627080]">Disaring:</span>${chips.map((c) => `
            <button type="button" class="rk-chip" data-clear="${c.key}" aria-label="Hapus saringan ${esc(c.label)}">${esc(c.label)}${icon('x', 'w-3.5 h-3.5')}</button>
        `).join('')}<button type="button" class="text-sm font-semibold text-[#C8460B] hover:underline" data-clear="all">Hapus semua</button>`;
    }

    function renderStageTabs(grouped) {
        $('rk-stage-tabs').innerHTML = ALL_STAGES.map((s) => `
            <button type="button" role="tab" class="rk-stage-tab" data-mstage="${s}" aria-selected="${state.mobileStage === s}">
                <span class="rk-dot" style="background:${STAGE[s].color}"></span>${STAGE[s].label}
                <span class="rk-tab-count">${grouped[s].length}</span>
            </button>`).join('');
    }

    function renderBoard() {
        renderActiveFilter();
        const list = sortApps(visibleApps());
        const grouped = Object.fromEntries(ALL_STAGES.map((s) => [s, []]));
        list.forEach((a) => grouped[stageOf(a)].push(a));
        renderStageTabs(grouped);
        board.dataset.mobileStage = state.mobileStage;
        board.classList.toggle('is-rejected-open', state.rejectedOpen);

        if (!state.apps.length) {
            board.innerHTML = `
                <div class="rk-empty col-span-full">
                    <span class="rk-empty-icon">${icon('users', 'w-6 h-6')}</span>
                    <p class="font-bold text-[#0B1B2B]">Belum ada kandidat</p>
                    <p class="mt-1 text-sm text-[#3D4B5C] max-w-sm">${state.jobs.length ? 'Kandidat akan muncul di sini setelah pelamar mengirim lamaran ke lowonganmu.' : 'Pasang lowongan pertamamu supaya pelamar bisa mulai melamar.'}</p>
                    ${state.jobs.length ? '' : '<button type="button" class="btn-press mt-4 h-10 px-4 rounded-xl bg-[#C8460B] text-white text-sm font-semibold" data-new-job>Pasang lowongan</button>'}
                </div>`;
            return;
        }

        board.innerHTML = ALL_STAGES.map((s) => {
            const items = grouped[s];
            const isRejected = s === 'rejected';
            const collapsed = isRejected && !state.rejectedOpen;
            return `
            <section class="rk-col ${collapsed ? 'is-collapsed' : ''}" data-stage="${s}" aria-label="Tahap ${STAGE[s].label}, ${items.length} kandidat">
                ${collapsed ? `
                    <button type="button" class="rk-col-collapsed" data-toggle-rejected aria-expanded="false" aria-label="Tampilkan ${items.length} kandidat ditolak">
                        <span class="rk-dot" style="background:${STAGE[s].color}"></span>
                        <span class="rk-col-collapsed-label">Ditolak</span>
                        <span class="rk-tab-count">${items.length}</span>
                    </button>` : `
                    <header class="rk-col-head">
                        <span class="rk-dot" style="background:${STAGE[s].color}"></span>
                        <h2 class="font-bold text-[#0B1B2B] text-sm">${STAGE[s].label}</h2>
                        <span class="rk-tab-count">${items.length}</span>
                        ${isRejected ? `<button type="button" class="ml-auto hidden lg:inline-flex w-8 h-8 items-center justify-center rounded-lg text-[#627080] hover:bg-[#F4F6F8]" data-toggle-rejected aria-expanded="true" aria-label="Lipat kolom ditolak">${icon('chevron', 'w-4 h-4 rotate-180')}</button>` : `<span class="ml-auto text-xs text-[#627080] hidden xl:inline">${STAGE[s].hint}</span>`}
                    </header>`}
                <div class="rk-col-body" data-drop="${s}">
                    ${collapsed ? '' : (items.length ? items.map((a) => card(a, s)).join('') : `<p class="rk-col-empty">${emptyColumnText(s)}</p>`)}
                </div>
            </section>`;
        }).join('');
    }

    function emptyColumnText(stage) {
        if (state.focus || state.cat !== 'all' || state.search.trim()) return 'Tidak ada yang cocok dengan saringan.';
        return { screening: 'Belum ada berkas baru.', assessment: 'Belum ada yang diuji.', interview: 'Belum ada jadwal wawancara.', offering: 'Belum ada penawaran.', rejected: 'Tidak ada kandidat ditolak.' }[stage];
    }

    function card(app, stage) {
        const a = app.applicant || {};
        const next = util.nextStage(stage);
        const rejected = stage === 'rejected';
        const matched = app.matched_skills || [];
        const missing = app.missing_skills || [];
        const days = daysSince(stageEnteredAt(app));
        const schedule = interviewOf(app);
        const badges = [];
        if (isReady(app)) badges.push(`<span class="rk-badge bg-[#E7F5EC] text-[#157F3B]">${icon('bolt', 'w-3.5 h-3.5')}Siap dimajukan</span>`);
        if (stage === 'interview' && schedule) {
            const up = isUpcoming(schedule);
            badges.push(`<span class="rk-badge ${up ? 'bg-[#FFF3EA] text-[#8A2E05]' : 'bg-[#F4F6F8] text-[#627080]'}">${icon('calendar', 'w-3.5 h-3.5')}${formatDateTime(schedule.datetime)}</span>`);
        }

        return `
        <article class="rk-card" draggable="${rejected ? 'false' : 'true'}" data-app="${app.id}" data-stage="${stage}" tabindex="0" aria-label="${esc(a.name || 'Kandidat')}, skor ${Math.round(app.match_score || 0)}%. Tekan Enter untuk detail.">
            <div class="flex items-start gap-3">
                <span class="rk-avatar w-10 h-10 text-sm">${esc(util.initials(a.name))}</span>
                <div class="min-w-0 flex-1">
                    <h3 class="font-bold text-[#0B1B2B] text-[15px] leading-snug truncate">${esc(a.name || 'Kandidat')}</h3>
                    <p class="text-xs text-[#627080] truncate">${state.jobFilter === 'all' ? esc(app.job_vacancy?.title || '-') : esc(a.location || 'Lokasi belum diisi')}</p>
                </div>
                ${scoreRing(app.match_score)}
            </div>
            ${matched.length || missing.length ? `
            <div class="mt-3 flex flex-wrap gap-1.5">
                ${matched.slice(0, 3).map((s) => `<span class="rk-skill">${esc(s)}</span>`).join('')}
                ${matched.length > 3 ? `<span class="rk-skill rk-skill-more">+${matched.length - 3}</span>` : ''}
                ${missing.length ? `<span class="rk-skill rk-skill-miss" title="Kurang: ${esc(missing.join(', '))}">${missing.length} kurang</span>` : ''}
            </div>` : ''}
            ${badges.length ? `<div class="mt-3 flex flex-wrap gap-1.5">${badges.join('')}</div>` : ''}
            <div class="mt-3 pt-3 border-t border-[#E2E7ED] flex items-center gap-2">
                <span class="text-xs text-[#627080] inline-flex items-center gap-1">${icon('clock', 'w-3.5 h-3.5')}${days === null ? '-' : days === 0 ? 'Masuk hari ini' : `${days} hari di tahap ini`}</span>
                ${next && !rejected ? `
                <button type="button" class="rk-advance ml-auto" data-advance="${app.id}" aria-label="Majukan ${esc(a.name || 'kandidat')} ke ${STAGE[next].label}">
                    ${STAGE[next].label}${icon('arrow', 'w-3.5 h-3.5')}
                </button>` : ''}
            </div>
        </article>`;
    }

    // ------------------------------------------------------------------ perpindahan tahap
    async function moveCandidate(appId, target, extra = {}) {
        const app = state.apps.find((a) => Number(a.id) === Number(appId));
        if (!app) return false;
        const from = stageOf(app);
        if (target !== 'rejected' && util.nextStage(from) !== target) {
            toast('Tahap hanya bisa maju satu langkah atau ditolak.', 'warning');
            return false;
        }
        try {
            await pipelines.updateStatus(appId, {
                stage: target,
                status: target === 'rejected' ? 'rejected' : 'in_progress',
                ...extra,
            });
            // Ambil ulang supaya linimasa dan jadwal sesuai data server
            try {
                const fresh = await applications.show(appId);
                Object.assign(app, fresh.data);
            } catch (e) {
                app.status = target;
                app.pipeline_timeline = { ...(app.pipeline_timeline || {}), current_stage: target };
            }
            const name = app.applicant?.name || 'Kandidat';
            toast(target === 'rejected' ? `${name} dipindahkan ke Ditolak.` : `${name} maju ke ${STAGE[target].label}.`, target === 'rejected' ? 'info' : 'success');
            if (window.matchMedia('(max-width: 1023px)').matches && target !== 'rejected') state.mobileStage = target;
            renderGreeting();
            renderMetrics();
            renderBoard();
            renderJobs();
            const moved = board.querySelector(`[data-app="${appId}"]`);
            if (moved && !reduceMotion) moved.classList.add('is-arrived');
            return true;
        } catch (err) {
            showError(err, 'Gagal memperbarui tahap kandidat.');
            return false;
        }
    }

    /** Tombol cepat di kartu: wawancara butuh jadwal, jadi dibuka lewat panel detail. */
    async function quickAdvance(appId) {
        const app = state.apps.find((a) => Number(a.id) === Number(appId));
        if (!app) return;
        const next = util.nextStage(stageOf(app));
        if (next === 'interview' || next === 'offering') {
            openDrawer(appId, { action: 'advance' });
            return;
        }
        // Tahap tidak bisa dimundurkan, jadi minta konfirmasi supaya salah ketuk tidak berakibat permanen
        const ok = await confirm({
            title: `Majukan ke ${STAGE[next].label}?`,
            message: `${app.applicant?.name || 'Kandidat'} akan pindah dari ${STAGE[stageOf(app)].label} ke ${STAGE[next].label}. Tahap tidak bisa dikembalikan.`,
            confirmText: 'Majukan',
            cancelText: 'Batal',
        });
        if (!ok) return;
        const btn = board.querySelector(`[data-advance="${appId}"]`);
        btn?.setAttribute('disabled', '');
        await moveCandidate(appId, next);
        btn?.removeAttribute('disabled');
    }

    // ------------------------------------------------------------------ drag & drop
    board.addEventListener('dragstart', (e) => {
        const cardEl = e.target.closest('.rk-card');
        if (!cardEl) return;
        state.dragFrom = cardEl.dataset.stage;
        e.dataTransfer.setData('text/plain', cardEl.dataset.app);
        e.dataTransfer.effectAllowed = 'move';
        cardEl.classList.add('is-dragging');
        const next = util.nextStage(state.dragFrom);
        board.querySelectorAll('.rk-col').forEach((col) => {
            const s = col.dataset.stage;
            if (s === state.dragFrom) return;
            col.classList.add(s === next || s === 'rejected' ? 'is-drop-valid' : 'is-drop-invalid');
        });
    });

    board.addEventListener('dragend', () => {
        state.dragFrom = null;
        board.querySelectorAll('.is-dragging').forEach((el) => el.classList.remove('is-dragging'));
        board.querySelectorAll('.rk-col').forEach((col) => col.classList.remove('is-drop-valid', 'is-drop-invalid', 'is-drop-over'));
    });

    board.addEventListener('dragover', (e) => {
        const col = e.target.closest('.rk-col.is-drop-valid');
        if (!col) return;
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
        board.querySelectorAll('.is-drop-over').forEach((c) => c !== col && c.classList.remove('is-drop-over'));
        col.classList.add('is-drop-over');
    });

    board.addEventListener('drop', async (e) => {
        const col = e.target.closest('.rk-col');
        if (!col) return;
        e.preventDefault();
        const appId = Number(e.dataTransfer.getData('text/plain'));
        const target = col.dataset.stage;
        if (!col.classList.contains('is-drop-valid')) {
            if (target !== state.dragFrom) toast('Tahap hanya bisa maju satu langkah atau ditolak.', 'warning');
            return;
        }
        if (target === 'rejected' || target === 'interview' || target === 'offering') {
            openDrawer(appId, { action: target === 'rejected' ? 'reject' : 'advance' });
            return;
        }
        await quickAdvance(appId);
    });

    // ------------------------------------------------------------------ klik di papan
    board.addEventListener('click', (e) => {
        const adv = e.target.closest('[data-advance]');
        if (adv) {
            e.stopPropagation();
            quickAdvance(Number(adv.dataset.advance));
            return;
        }
        if (e.target.closest('[data-toggle-rejected]')) {
            state.rejectedOpen = !state.rejectedOpen;
            renderBoard();
            return;
        }
        if (e.target.closest('[data-new-job]')) {
            openJobForm();
            return;
        }
        const cardEl = e.target.closest('.rk-card');
        if (cardEl) openDrawer(Number(cardEl.dataset.app));
    });

    board.addEventListener('keydown', (e) => {
        const cardEl = e.target.closest('.rk-card');
        if (cardEl && (e.key === 'Enter' || e.key === ' ') && e.target === cardEl) {
            e.preventDefault();
            openDrawer(Number(cardEl.dataset.app));
        }
    });

    $('rk-stage-tabs').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-mstage]');
        if (!btn) return;
        state.mobileStage = btn.dataset.mstage;
        if (state.mobileStage === 'rejected') state.rejectedOpen = true;
        renderBoard();
    });

    // ------------------------------------------------------------------ saringan
    let searchTimer;
    $('rk-search').addEventListener('input', (e) => {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            state.search = e.target.value;
            renderBoard();
        }, 180);
    });

    document.querySelectorAll('[data-cat]').forEach((btn) => {
        btn.addEventListener('click', () => {
            state.cat = btn.dataset.cat;
            document.querySelectorAll('[data-cat]').forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
            renderBoard();
        });
    });

    $('rk-sort').addEventListener('change', (e) => {
        state.sort = e.target.value;
        renderBoard();
    });

    $('rk-job-filter').addEventListener('change', (e) => {
        state.jobFilter = e.target.value;
        renderGreeting();
        renderMetrics();
        renderBoard();
    });

    document.querySelectorAll('.rk-metric').forEach((btn) => {
        btn.addEventListener('click', () => {
            if (btn.dataset.goto === 'jobs') {
                switchTab('jobs');
                return;
            }
            state.focus = state.focus === btn.dataset.focus ? null : btn.dataset.focus;
            if (state.focus === 'interview') state.mobileStage = 'interview';
            else if (state.focus) state.mobileStage = 'screening';
            switchTab('pipeline');
            renderMetrics();
            renderBoard();
        });
    });

    $('rk-active-filter').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-clear]');
        if (!btn) return;
        const key = btn.dataset.clear;
        if (key === 'focus' || key === 'all') state.focus = null;
        if (key === 'job' || key === 'all') state.jobFilter = 'all';
        if (key === 'cat' || key === 'all') {
            state.cat = 'all';
            document.querySelectorAll('[data-cat]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.cat === 'all')));
        }
        if (key === 'search' || key === 'all') {
            state.search = '';
            $('rk-search').value = '';
        }
        $('rk-job-filter').value = String(state.jobFilter);
        renderGreeting();
        renderMetrics();
        renderBoard();
    });

    $('rk-retry').addEventListener('click', load);

    // ------------------------------------------------------------------ tab utama
    const tabs = { pipeline: $('rk-tab-pipeline'), jobs: $('rk-tab-jobs') };
    function switchTab(name) {
        state.tab = name;
        Object.entries(tabs).forEach(([key, btn]) => {
            const on = key === name;
            btn.setAttribute('aria-selected', String(on));
            btn.tabIndex = on ? 0 : -1;
            $(`rk-panel-${key}`).classList.toggle('hidden', !on);
        });
    }
    Object.entries(tabs).forEach(([key, btn]) => btn.addEventListener('click', () => switchTab(key)));
    $('rk-tab-pipeline').parentElement.addEventListener('keydown', (e) => {
        if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
        const next = state.tab === 'pipeline' ? 'jobs' : 'pipeline';
        switchTab(next);
        tabs[next].focus();
    });

    // ------------------------------------------------------------------ panel detail kandidat
    const drawer = $('rk-drawer');
    const drawerBody = $('rk-d-body');
    const drawerActions = $('rk-d-actions');

    async function openDrawer(appId, { action = null } = {}) {
        const base = state.apps.find((a) => Number(a.id) === Number(appId));
        if (!base) return;
        state.lastFocus = document.activeElement;
        state.drawerApp = base;
        state.drawerTab = 'summary';
        state.drawerAction = action;
        drawer.hidden = false;
        document.body.classList.add('overflow-hidden');
        requestAnimationFrame(() => drawer.classList.add('is-open'));
        setTimeout(() => drawer.classList.add('is-open'), 30);
        renderDrawer();
        drawer.querySelector('[data-drawer-close].shrink-0, .rk-drawer-panel [data-drawer-close]')?.focus();

        try {
            const fresh = await applications.show(appId);
            Object.assign(base, fresh.data);
            if (state.drawerApp === base) renderDrawer();
        } catch (e) {
            /* data dari daftar sudah cukup untuk ditampilkan */
        }
    }

    function closeDrawer() {
        drawer.classList.remove('is-open');
        document.body.classList.remove('overflow-hidden');
        setTimeout(() => {
            if (!drawer.classList.contains('is-open')) drawer.hidden = true;
        }, reduceMotion ? 0 : 260);
        state.drawerApp = null;
        state.lastFocus?.focus?.();
    }

    drawer.addEventListener('click', (e) => {
        if (e.target.closest('[data-drawer-close]')) closeDrawer();
        const dtab = e.target.closest('[data-dtab]');
        if (dtab) {
            state.drawerTab = dtab.dataset.dtab;
            renderDrawer();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !drawer.hidden) closeDrawer();
        if (e.key === 'Tab' && !drawer.hidden) {
            const focusables = [...drawer.querySelectorAll('button:not([disabled]), [href], input, select, textarea')].filter((el) => el.offsetParent);
            if (!focusables.length) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });

    function renderDrawer() {
        const app = state.drawerApp;
        if (!app) return;
        const a = app.applicant || {};
        const stage = stageOf(app);
        $('rk-d-avatar').textContent = util.initials(a.name);
        $('rk-d-name').textContent = a.name || 'Kandidat';
        $('rk-d-job').textContent = `${app.job_vacancy?.title || '-'} · ${app.job_vacancy?.company || ''}`.replace(/ · $/, '');
        $('rk-d-stage').innerHTML = `<span class="rk-badge bg-[#F4F6F8] text-[#3D4B5C]"><span class="rk-dot" style="background:${STAGE[stage].color}"></span>${STAGE[stage].label}</span>
            <span class="text-xs text-[#627080] ml-1">Melamar ${util.formatDate(app.created_at)}</span>`;
        $('rk-d-score').innerHTML = scoreRing(app.match_score, 'lg');

        drawer.querySelectorAll('[data-dtab]').forEach((t) => {
            const on = t.dataset.dtab === state.drawerTab;
            t.setAttribute('aria-selected', String(on));
            t.tabIndex = on ? 0 : -1;
        });

        if (state.drawerTab === 'summary') drawerBody.innerHTML = drawerSummary(app);
        if (state.drawerTab === 'application') drawerBody.innerHTML = drawerApplication(app);
        if (state.drawerTab === 'timeline') drawerBody.innerHTML = drawerTimeline(app);
        drawerBody.querySelector('[data-download-cv]')?.addEventListener('click', async (e) => {
            const btn = e.currentTarget;
            setLoading(btn, true, 'Mengunduh...');
            try {
                await applications.downloadCv(app.id);
            } catch (err) {
                showError(err, 'Gagal mengunduh CV.');
            }
            setLoading(btn, false);
        });

        renderDrawerActions(app, stage);
    }

    function drawerSummary(app) {
        const a = app.applicant || {};
        const cat = scoreCategory(Number(app.match_score) || 0);
        const groups = requirementBreakdown(app);
        const contact = [
            ['mail', a.email, a.email ? `mailto:${a.email}` : null],
            ['phone', a.phone || 'Telepon belum diisi', a.phone ? `tel:${a.phone}` : null],
            ['pin', a.location || 'Lokasi belum diisi', null],
        ];
        const breakdown = groups ? Object.entries(groups).filter(([, items]) => items.length).map(([lvl, items]) => `
            <div class="rk-req-row">
                <span class="rk-req-level" style="--lvl:${LEVEL[lvl].color}">${LEVEL[lvl].label}<span class="text-[#627080] font-medium"> ×${LEVEL[lvl].weight}</span></span>
                <div class="flex flex-wrap gap-1.5">
                    ${items.map((s) => `<span class="rk-skill ${s.ok ? 'rk-skill-ok' : 'rk-skill-miss'}">${icon(s.ok ? 'check' : 'x', 'w-3 h-3')}${esc(s.name)}</span>`).join('')}
                </div>
            </div>`).join('') : `
            <div class="flex flex-wrap gap-1.5">
                ${(app.matched_skills || []).map((s) => `<span class="rk-skill rk-skill-ok">${icon('check', 'w-3 h-3')}${esc(s)}</span>`).join('')}
                ${(app.missing_skills || []).map((s) => `<span class="rk-skill rk-skill-miss">${icon('x', 'w-3 h-3')}${esc(s)}</span>`).join('')}
            </div>`;
        const extra = (a.skills || []).filter((s) => !(app.matched_skills || []).map((m) => String(m).toLowerCase()).includes(String(s).toLowerCase()));

        return `
        <div class="space-y-6">
            <div class="rk-cat-box" style="--cat:${cat.color}">
                <p class="rk-cat-title text-sm font-bold">${cat.label} · ${Math.round(app.match_score || 0)}%</p>
                <p class="mt-0.5 text-sm text-[#3D4B5C]">${(app.matched_skills || []).length} skill cocok, ${(app.missing_skills || []).length} kurang dari syarat lowongan.${isReady(app) ? ' Skor di atas 80%, kandidat ini siap dimajukan.' : ''}</p>
            </div>

            <section>
                <h3 class="rk-d-h">Kecocokan per tingkat syarat</h3>
                <div class="mt-3 space-y-2.5">${breakdown}</div>
            </section>

            <section>
                <h3 class="rk-d-h">Kontak</h3>
                <ul class="mt-3 space-y-2 text-sm">
                    ${contact.map(([ic, text, href]) => `<li class="flex items-center gap-2.5 text-[#3D4B5C]"><span class="text-[#627080]">${icon(ic)}</span>${href ? `<a href="${esc(href)}" class="text-[#1B65A6] hover:underline break-all">${esc(text)}</a>` : `<span>${esc(text)}</span>`}</li>`).join('')}
                </ul>
            </section>

            ${a.bio ? `<section><h3 class="rk-d-h">Tentang kandidat</h3><p class="mt-2 text-sm text-[#3D4B5C] leading-relaxed">${esc(a.bio)}</p></section>` : ''}

            ${extra.length ? `<section><h3 class="rk-d-h">Skill lain di profil</h3><div class="mt-2.5 flex flex-wrap gap-1.5">${extra.map((s) => `<span class="rk-skill">${esc(s)}</span>`).join('')}</div></section>` : ''}
        </div>`;
    }

    function drawerApplication(app) {
        return `
        <div class="space-y-6">
            <section>
                <h3 class="rk-d-h">Surat lamaran</h3>
                ${app.cover_letter
                    ? `<p class="mt-2 text-sm text-[#3D4B5C] leading-relaxed whitespace-pre-line">${esc(app.cover_letter)}</p>`
                    : '<p class="mt-2 text-sm text-[#627080]">Kandidat tidak menyertakan surat lamaran.</p>'}
            </section>
            <section>
                <h3 class="rk-d-h">CV</h3>
                ${app.cv_file_path
                    ? `<button type="button" data-download-cv class="btn-press mt-2.5 inline-flex items-center gap-2 h-11 px-4 rounded-xl border border-[#C5CFDA] bg-white text-sm font-semibold text-[#0B1B2B] hover:border-[#0C2238]">${icon('file')}Unduh CV (PDF)</button>`
                    : '<p class="mt-2 text-sm text-[#627080]">Kandidat tidak mengunggah CV.</p>'}
            </section>
        </div>`;
    }

    function drawerTimeline(app) {
        const stages = app.pipeline_timeline?.stages || [];
        if (!stages.length) return '<p class="text-sm text-[#627080]">Belum ada riwayat tahap.</p>';
        return `
        <ol class="rk-timeline">
            ${stages.map((s, i) => {
                const meta = STAGE[s.stage] || { label: s.stage, color: '#627080' };
                const last = i === stages.length - 1;
                const status = s.status === 'passed' ? 'Lolos' : s.status === 'rejected' ? 'Ditolak' : 'Berjalan';
                const sch = s.interview_schedule;
                return `
                <li class="rk-tl-item ${last ? 'is-current' : ''}" style="--c:${meta.color}">
                    <div class="flex flex-wrap items-baseline gap-x-2">
                        <p class="font-bold text-[#0B1B2B] text-sm">${esc(meta.label)}</p>
                        <span class="text-xs text-[#627080]">${status} · ${util.formatDate(s.entered_at, true)}</span>
                    </div>
                    ${s.notes ? `<p class="mt-1 text-sm text-[#3D4B5C]">${esc(s.notes)}</p>` : ''}
                    ${sch?.datetime ? `
                    <div class="mt-2 rounded-xl border border-[#E2E7ED] bg-[#F4F6F8] p-3 text-sm text-[#3D4B5C] space-y-1">
                        <p class="flex items-center gap-2 font-semibold text-[#0B1B2B]">${icon('calendar')}${formatDateTime(sch.datetime)} · ${sch.type === 'offline' ? 'Tatap muka' : 'Online'}</p>
                        ${sch.interviewer ? `<p>Pewawancara: ${esc(sch.interviewer)}</p>` : ''}
                        ${sch.meeting_url ? `<a href="${esc(sch.meeting_url)}" target="_blank" rel="noopener" class="text-[#1B65A6] hover:underline break-all">${esc(sch.meeting_url)}</a>` : ''}
                    </div>` : ''}
                </li>`;
            }).join('')}
        </ol>`;
    }

    function renderDrawerActions(app, stage) {
        const next = util.nextStage(stage);
        if (stage === 'rejected') {
            drawerActions.innerHTML = '<p class="text-sm text-[#627080]">Kandidat sudah ditolak. Tahap tidak bisa dikembalikan.</p>';
            return;
        }
        if (stage === 'offering' && state.drawerAction !== 'reject') {
            drawerActions.innerHTML = `
                <div class="flex items-center gap-3">
                    <p class="text-sm text-[#3D4B5C] flex-1">Kandidat di tahap akhir. Penawaran sudah diberikan.</p>
                    <button type="button" class="h-11 px-4 rounded-xl text-sm font-semibold text-[#B42318] hover:bg-[#FDEEEC]" data-act="reject">Tolak</button>
                </div>`;
            bindActionButtons(app, next);
            return;
        }

        if (state.drawerAction === 'reject') {
            drawerActions.innerHTML = `
                <form class="space-y-3" data-act-form="reject">
                    <div>
                        <label for="rk-reject-note" class="rk-label">Alasan penolakan <span class="text-[#C8460B]">*</span></label>
                        <textarea id="rk-reject-note" rows="2" maxlength="1000" class="rk-input py-2" placeholder="contoh: Belum menguasai Redis yang wajib untuk posisi ini."></textarea>
                        <p class="rk-error" data-error-for="reject"></p>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="h-11 px-4 rounded-xl text-sm font-semibold text-[#3D4B5C] bg-[#F4F6F8] hover:bg-[#E2E7ED]" data-act="cancel">Batal</button>
                        <button type="submit" class="btn-press h-11 px-5 rounded-xl text-sm font-semibold text-white bg-[#B42318] hover:bg-[#912018]">Tolak kandidat</button>
                    </div>
                </form>`;
        } else if (state.drawerAction === 'advance' && next) {
            const needsSchedule = next === 'interview';
            drawerActions.innerHTML = `
                <form class="space-y-3" data-act-form="advance">
                    <p class="text-sm font-bold text-[#0B1B2B]">Majukan ke ${STAGE[next].label}</p>
                    ${needsSchedule ? `
                    <div class="grid sm:grid-cols-2 gap-3">
                        <div>
                            <label for="rk-iv-date" class="rk-label">Waktu wawancara <span class="text-[#C8460B]">*</span></label>
                            <input id="rk-iv-date" type="datetime-local" class="rk-input">
                            <p class="rk-error" data-error-for="iv-date"></p>
                        </div>
                        <div>
                            <label for="rk-iv-type" class="rk-label">Bentuk</label>
                            <select id="rk-iv-type" class="rk-input"><option value="online">Online</option><option value="offline">Tatap muka</option></select>
                        </div>
                        <div>
                            <label for="rk-iv-who" class="rk-label">Pewawancara</label>
                            <input id="rk-iv-who" type="text" class="rk-input" value="${esc(currentUser.name || '')}">
                        </div>
                        <div>
                            <label for="rk-iv-url" class="rk-label">Tautan atau lokasi</label>
                            <input id="rk-iv-url" type="text" class="rk-input" placeholder="https://meet.google.com/...">
                        </div>
                    </div>` : ''}
                    <div>
                        <label for="rk-adv-note" class="rk-label">Catatan <span class="font-normal text-[#627080]">(opsional)</span></label>
                        <textarea id="rk-adv-note" rows="2" maxlength="1000" class="rk-input py-2" placeholder="Catatan untuk tahap berikutnya"></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" class="h-11 px-4 rounded-xl text-sm font-semibold text-[#3D4B5C] bg-[#F4F6F8] hover:bg-[#E2E7ED]" data-act="cancel">Batal</button>
                        <button type="submit" class="btn-press h-11 px-5 rounded-xl text-sm font-semibold text-white bg-[#C8460B] hover:bg-[#A93A08] inline-flex items-center gap-2">Majukan${icon('arrow')}</button>
                    </div>
                </form>`;
        } else {
            drawerActions.innerHTML = `
                <div class="flex items-center gap-2">
                    <button type="button" class="h-11 px-4 rounded-xl text-sm font-semibold text-[#B42318] hover:bg-[#FDEEEC]" data-act="reject">Tolak</button>
                    ${next ? `<button type="button" class="btn-press ml-auto h-11 px-5 rounded-xl text-sm font-semibold text-white bg-[#C8460B] hover:bg-[#A93A08] inline-flex items-center gap-2" data-act="advance">Majukan ke ${STAGE[next].label}${icon('arrow')}</button>` : ''}
                </div>`;
        }
        bindActionButtons(app, next);
        drawerActions.querySelector('textarea, input')?.focus();
    }

    function bindActionButtons(app, next) {
        drawerActions.querySelectorAll('[data-act]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const act = btn.dataset.act;
                state.drawerAction = act === 'cancel' ? null : act;
                renderDrawerActions(app, stageOf(app));
            });
        });

        drawerActions.querySelector('[data-act-form]')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const form = e.currentTarget;
            const kind = form.dataset.actForm;
            const submit = form.querySelector('[type="submit"]');
            let target = next;
            const extra = {};

            if (kind === 'reject') {
                const note = form.querySelector('#rk-reject-note').value.trim();
                if (!note) {
                    form.querySelector('[data-error-for="reject"]').textContent = 'Tulis alasan singkat supaya kandidat dan tim tahu kenapa.';
                    form.querySelector('#rk-reject-note').focus();
                    return;
                }
                target = 'rejected';
                extra.notes = note;
            } else {
                const note = form.querySelector('#rk-adv-note').value.trim();
                if (note) extra.notes = note;
                if (next === 'interview') {
                    const date = form.querySelector('#rk-iv-date').value;
                    if (!date) {
                        form.querySelector('[data-error-for="iv-date"]').textContent = 'Pilih tanggal dan jam wawancara.';
                        form.querySelector('#rk-iv-date').focus();
                        return;
                    }
                    extra.interview_schedule = {
                        datetime: date,
                        type: form.querySelector('#rk-iv-type').value,
                        interviewer: form.querySelector('#rk-iv-who').value.trim() || null,
                        meeting_url: form.querySelector('#rk-iv-url').value.trim() || null,
                    };
                }
            }

            setLoading(submit, true, 'Menyimpan...');
            const ok = await moveCandidate(app.id, target, extra);
            setLoading(submit, false);
            if (ok) {
                state.drawerAction = null;
                if (target === 'rejected') closeDrawer();
                else renderDrawer();
            }
        });
    }

    // ------------------------------------------------------------------ lowongan saya
    function renderJobs() {
        const list = $('rk-jobs-list');
        if (!state.jobs.length) {
            list.innerHTML = `
                <div class="rk-empty">
                    <span class="rk-empty-icon">${icon('board', 'w-6 h-6')}</span>
                    <p class="font-bold text-[#0B1B2B]">Belum ada lowongan</p>
                    <p class="mt-1 text-sm text-[#3D4B5C]">Pasang lowongan dengan syarat skill yang jelas supaya skor kecocokan pelamar akurat.</p>
                    <button type="button" class="btn-press mt-4 h-10 px-4 rounded-xl bg-[#C8460B] text-white text-sm font-semibold" data-new-job>Pasang lowongan</button>
                </div>`;
            return;
        }
        const sorted = [...state.jobs].sort((a, b) => (a.status === 'open' ? 0 : 1) - (b.status === 'open' ? 0 : 1) || new Date(b.created_at) - new Date(a.created_at));
        list.innerHTML = sorted.map((job) => {
            const apps = state.apps.filter((a) => Number(a.job_vacancy_id) === Number(job.id));
            const open = job.status === 'open';
            const dist = ALL_STAGES.map((s) => [s, apps.filter((a) => stageOf(a) === s).length]).filter(([, n]) => n);
            const reqs = job.requirements || [];
            const salary = salaryRange(job);
            const expires = job.expires_at ? new Date(job.expires_at) : null;
            const expired = expires && expires.getTime() < Date.now();
            return `
            <article class="rk-job ${open ? '' : 'is-closed'}">
                <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rk-badge ${open ? 'bg-[#E7F5EC] text-[#157F3B]' : 'bg-[#F4F6F8] text-[#627080]'}">${open ? 'Dibuka' : 'Ditutup'}</span>
                            ${open && expired ? `<span class="rk-badge bg-[#FFF3EA] text-[#8A2E05]">Lewat batas lamaran</span>` : ''}
                            <span class="text-xs text-[#627080]">${esc(EMPLOYMENT_SHORT[job.employment_type] || job.employment_type || '-')} · ${esc(job.location || '-')}</span>
                        </div>
                        <h3 class="mt-1.5 text-[17px] font-bold text-[#0B1B2B] leading-snug">${esc(job.title)}</h3>
                        <p class="text-sm text-[#3D4B5C]">${esc(job.company)}${salary ? ` · ${esc(salary)}` : ''}</p>
                        <div class="mt-2.5 flex flex-wrap gap-1.5">
                            ${reqs.slice(0, 6).map((r) => `<span class="rk-skill" title="${esc(LEVEL[r.level]?.label || 'Wajib')}"><span class="rk-dot" style="background:${(LEVEL[r.level] || LEVEL.required).color}"></span>${esc(util.skillName(r))}</span>`).join('')}
                            ${reqs.length > 6 ? `<span class="rk-skill rk-skill-more">+${reqs.length - 6}</span>` : ''}
                        </div>
                    </div>

                    <div class="lg:w-[260px] shrink-0">
                        <div class="flex items-baseline justify-between text-sm">
                            <span class="font-bold text-[#0B1B2B] num-tabular">${plural(apps.length, 'pelamar')}</span>
                            <span class="text-xs text-[#627080] num-tabular">kuota ${Number(job.filled) || 0}/${Number(job.quota) || 0}${expires ? ` · tutup ${util.formatDate(job.expires_at)}` : ''}</span>
                        </div>
                        <div class="rk-dist mt-2" role="img" aria-label="${dist.length ? dist.map(([s, n]) => `${STAGE[s].label} ${n}`).join(', ') : 'Belum ada pelamar'}">
                            ${dist.length ? dist.map(([s, n]) => `<span style="flex:${n};background:${STAGE[s].color}" title="${STAGE[s].label}: ${n}"></span>`).join('') : '<span class="rk-dist-empty"></span>'}
                        </div>
                        <div class="mt-1.5 flex flex-wrap gap-x-3 gap-y-0.5 text-[11px] text-[#627080]">
                            ${dist.map(([s, n]) => `<span class="inline-flex items-center gap-1"><span class="rk-dot" style="background:${STAGE[s].color}"></span>${STAGE[s].label} ${n}</span>`).join('')}
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-[#E2E7ED] flex flex-wrap items-center gap-1.5">
                    <span class="text-xs text-[#627080] mr-auto">Dipasang ${util.formatDate(job.created_at)}</span>
                    <button type="button" class="rk-job-btn" data-job-pipeline="${job.id}" ${apps.length ? '' : 'disabled'}>${icon('board')}Lihat pipeline</button>
                    <button type="button" class="rk-job-btn" data-job-edit="${job.id}">${icon('edit')}Edit</button>
                    <button type="button" class="rk-job-btn ${open ? 'text-[#B42318]' : 'text-[#157F3B]'}" data-job-toggle="${job.id}">${icon(open ? 'lock' : 'unlock')}${open ? 'Tutup' : 'Buka lagi'}</button>
                </div>
            </article>`;
        }).join('');
    }

    $('rk-panel-jobs').addEventListener('click', async (e) => {
        if (e.target.closest('[data-new-job]')) {
            openJobForm();
            return;
        }
        const pipe = e.target.closest('[data-job-pipeline]');
        if (pipe) {
            state.jobFilter = pipe.dataset.jobPipeline;
            $('rk-job-filter').value = state.jobFilter;
            switchTab('pipeline');
            renderGreeting();
            renderMetrics();
            renderBoard();
            return;
        }
        const edit = e.target.closest('[data-job-edit]');
        if (edit) {
            openJobForm(Number(edit.dataset.jobEdit));
            return;
        }
        const toggle = e.target.closest('[data-job-toggle]');
        if (toggle) {
            const job = jobById(toggle.dataset.jobToggle);
            if (!job) return;
            if (job.status === 'open') {
                const ok = await confirm({
                    title: 'Tutup lowongan ini?',
                    message: `"${job.title}" tidak akan menerima lamaran baru. Kandidat yang sudah melamar tetap bisa diproses.`,
                    confirmText: 'Tutup lowongan',
                    cancelText: 'Batal',
                    danger: true,
                });
                if (!ok) return;
            }
            setLoading(toggle, true, job.status === 'open' ? 'Menutup...' : 'Membuka...');
            try {
                if (job.status === 'open') await jobs.close(job.id);
                else await jobs.update(job.id, { status: 'open' });
                job.status = job.status === 'open' ? 'closed' : 'open';
                toast(job.status === 'open' ? 'Lowongan dibuka lagi.' : 'Lowongan ditutup.', 'success');
                renderJobFilter();
                renderMetrics();
                renderJobs();
            } catch (err) {
                setLoading(toggle, false);
                showError(err, 'Gagal mengubah status lowongan.');
            }
        }
    });

    // ------------------------------------------------------------------ form lowongan
    const form = $('rk-job-form');
    const reqRows = $('rk-req-rows');
    const modalTitle = $('modal-job-form-title');

    function renderSkillSuggestions() {
        const names = new Set();
        state.jobs.forEach((j) => (j.requirements || []).forEach((r) => names.add(util.skillName(r))));
        state.apps.forEach((a) => (a.applicant?.skills || []).forEach((s) => names.add(s)));
        $('rk-skill-suggestions').innerHTML = [...names].filter(Boolean).sort((a, b) => a.localeCompare(b, 'id'))
            .map((n) => `<option value="${esc(n)}"></option>`).join('');
    }

    function reqRow(r = { skill: '', level: 'required' }) {
        const level = LEVEL[r.level] ? r.level : 'required';
        return `
        <div class="rk-req-input">
            <label class="sr-only">Nama skill</label>
            <input type="text" data-req-skill list="rk-skill-suggestions" maxlength="100" value="${esc(r.skill || util.skillName(r))}" placeholder="contoh: Laravel" class="rk-input">
            <label class="sr-only">Tingkat syarat</label>
            <select data-req-level class="rk-input sm:w-[150px]">
                ${Object.entries(LEVEL).map(([k, v]) => `<option value="${k}" ${k === level ? 'selected' : ''}>${v.label} (×${v.weight})</option>`).join('')}
            </select>
            <button type="button" data-req-remove class="w-11 h-11 shrink-0 inline-flex items-center justify-center rounded-xl text-[#627080] hover:text-[#B42318] hover:bg-[#FDEEEC]" aria-label="Hapus skill ini">${icon('trash')}</button>
        </div>`;
    }

    function readRequirements() {
        return [...reqRows.querySelectorAll('.rk-req-input')].map((row) => ({
            skill: row.querySelector('[data-req-skill]').value.trim(),
            level: row.querySelector('[data-req-level]').value,
        })).filter((r) => r.skill);
    }

    function updateWeightPreview() {
        const reqs = readRequirements();
        const total = reqs.reduce((s, r) => s + LEVEL[r.level].weight, 0);
        const by = Object.fromEntries(Object.keys(LEVEL).map((k) => [k, reqs.filter((r) => r.level === k).reduce((s, r) => s + LEVEL[r.level].weight, 0)]));
        $('rk-weight-preview').textContent = total
            ? `Total bobot ${total}. Skill Wajib menyumbang ${Math.round((by.required / total) * 100)}% skor.`
            : 'Tambahkan minimal satu skill.';
        $('rk-weight-bar').innerHTML = total
            ? Object.entries(by).filter(([, w]) => w).map(([k, w]) => `<span style="flex:${w};background:${LEVEL[k].color}" title="${LEVEL[k].label}: ${w}"></span>`).join('')
            : '';
    }

    function openJobForm(jobId = null) {
        const job = jobId ? jobById(jobId) : null;
        state.editingJobId = job ? job.id : null;
        form.reset();
        form.querySelectorAll('.rk-error').forEach((el) => { el.textContent = ''; });
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        if (modalTitle) modalTitle.textContent = job ? 'Edit lowongan' : 'Pasang lowongan baru';
        $('rk-job-submit').textContent = job ? 'Simpan perubahan' : 'Terbitkan lowongan';

        $('job-title').value = job?.title || '';
        $('job-company').value = job?.company || (state.jobs[0]?.company && !isAdmin ? state.jobs[0].company : '');
        $('job-location').value = job?.location || '';
        $('job-type').value = job?.employment_type || 'full-time';
        $('job-quota').value = job?.quota || 1;
        $('job-salary-min').value = job?.salary_min || '';
        $('job-salary-max').value = job?.salary_max || '';
        $('job-description').value = job?.description || '';
        $('job-expires').value = job?.expires_at ? String(job.expires_at).slice(0, 10) : '';
        $('job-expires').min = job ? '' : new Date(Date.now() + 86400000).toISOString().slice(0, 10);

        const reqs = job?.requirements?.length ? job.requirements : [{ skill: '', level: 'required' }, { skill: '', level: 'important' }];
        reqRows.innerHTML = reqs.map(reqRow).join('');
        updateWeightPreview();
        window.JHModal?.open('modal-job-form');
        setTimeout(() => $('job-title').focus(), 50);
    }

    $('rk-new-job').addEventListener('click', () => openJobForm());

    $('rk-add-req').addEventListener('click', () => {
        reqRows.insertAdjacentHTML('beforeend', reqRow({ skill: '', level: 'important' }));
        reqRows.lastElementChild.querySelector('input').focus();
        updateWeightPreview();
    });

    reqRows.addEventListener('click', (e) => {
        const rm = e.target.closest('[data-req-remove]');
        if (!rm) return;
        if (reqRows.children.length <= 1) {
            rm.closest('.rk-req-input').querySelector('input').value = '';
        } else {
            rm.closest('.rk-req-input').remove();
        }
        updateWeightPreview();
    });
    reqRows.addEventListener('input', updateWeightPreview);
    reqRows.addEventListener('change', updateWeightPreview);

    function fieldError(key, message, inputId) {
        const el = form.querySelector(`[data-error-for="${key}"]`);
        if (el) el.textContent = message;
        if (inputId) $(inputId)?.classList.add('is-invalid');
    }

    form.addEventListener('input', (e) => {
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const err = e.target.closest('div')?.querySelector('.rk-error');
            if (err) err.textContent = '';
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        form.querySelectorAll('.rk-error').forEach((el) => { el.textContent = ''; });
        const num = (id) => ($(id).value === '' ? null : Number($(id).value));
        const payload = {
            title: $('job-title').value.trim(),
            company: $('job-company').value.trim(),
            location: $('job-location').value.trim(),
            employment_type: $('job-type').value,
            quota: num('job-quota') || 1,
            salary_min: num('job-salary-min'),
            salary_max: num('job-salary-max'),
            description: $('job-description').value.trim(),
            requirements: readRequirements().map((r) => ({ skill: r.skill, level: r.level, weight: LEVEL[r.level].weight })),
        };
        if ($('job-expires').value) payload.expires_at = $('job-expires').value;

        const problems = [];
        if (!payload.title) problems.push(['title', 'Judul posisi wajib diisi.', 'job-title']);
        if (!payload.company) problems.push(['company', 'Nama perusahaan wajib diisi.', 'job-company']);
        if (!payload.location) problems.push(['location', 'Kota penempatan wajib diisi.', 'job-location']);
        if (payload.salary_min !== null && payload.salary_max !== null && payload.salary_max < payload.salary_min) {
            problems.push(['salary_max', 'Gaji maksimum tidak boleh lebih kecil dari minimum.', 'job-salary-max']);
        }
        if (!payload.requirements.length) problems.push(['requirements', 'Isi minimal satu skill.', null]);
        if (!payload.description) problems.push(['description', 'Deskripsi wajib diisi.', 'job-description']);
        if (problems.length) {
            problems.forEach(([k, m, id]) => fieldError(k, m, id));
            const first = problems.find(([, , id]) => id);
            (first ? $(first[2]) : reqRows.querySelector('input'))?.focus();
            return;
        }

        const submit = $('rk-job-submit');
        setLoading(submit, true, 'Menyimpan...');
        try {
            if (state.editingJobId) await jobs.update(state.editingJobId, payload);
            else await jobs.create(payload);
            setLoading(submit, false);
            window.JHModal?.close('modal-job-form');
            toast(state.editingJobId ? 'Perubahan lowongan disimpan.' : 'Lowongan diterbitkan.', 'success');
            await load();
            switchTab('jobs');
        } catch (err) {
            setLoading(submit, false);
            const errors = err?.errors || err?.payload?.errors;
            if (errors && typeof errors === 'object') {
                const map = { title: 'job-title', company: 'job-company', location: 'job-location', quota: 'job-quota', salary_min: 'job-salary-min', salary_max: 'job-salary-max', description: 'job-description', expires_at: 'job-expires' };
                Object.entries(errors).forEach(([k, msgs]) => {
                    const key = k.startsWith('requirements') ? 'requirements' : k;
                    fieldError(key, Array.isArray(msgs) ? msgs[0] : String(msgs), map[key]);
                });
            } else {
                showError(err, 'Gagal menyimpan lowongan.');
            }
        }
    });

    load();
});
