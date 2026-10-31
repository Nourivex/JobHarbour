/**
 * Logic Halaman Harbour Master Deck (Dashboard Recruiter) - JobHarbour
 *
 * Fitur utama yang disambungkan ke REST API:
 * 1. Guard autentikasi wajib role 'recruiter' (atau 'admin')
 * 2. Daftar lowongan milik recruiter (GET /api/jobs/my) + filter pipeline per lowongan
 * 3. Papan Kanban 5 tahapan (GET /api/applications) diurutkan berdasarkan match_score tertinggi
 * 4. Statistik ringkas (total pelamar, jumlah per tahap, rata-rata match score, lowongan aktif)
 * 5. Modal detail kandidat: biodata, cover letter, matched vs missing skills, linimasa, unduh CV PDF
 * 6. Form update tahapan seleksi (PATCH /api/pipelines/{id}/status) + jadwal interview
 * 7. Modal buat/edit/tutup lowongan kerja (POST /api/jobs, PATCH /api/jobs/{id}, DELETE /api/jobs/{id})
 */
(function (window, document) {
    'use strict';

    const JH = window.JH;
    if (!JH) return;

    // 1. Pastikan pengguna login sebagai recruiter
    const currentUser = JH.requireAuth('recruiter');
    if (!currentUser) return;

    // State utama halaman
    const state = {
        jobs: [],
        applications: [],
        selectedJobId: 'all',
        quickFilter: 'all',
        searchQuery: '',
        activeCandidateId: null,
        editingJobId: null,
    };

    const COLUMNS = ['screening', 'assessment', 'interview', 'offering', 'rejected'];

    // Helper ambil tahap aktif dari objek lamaran
    function getStage(app) {
        const timelineStage = app && app.pipeline_timeline && app.pipeline_timeline.current_stage;
        const raw = (timelineStage || (app && app.status) || 'screening').toLowerCase();
        if (raw === 'submitted' || raw === 'screening') return 'screening';
        if (COLUMNS.includes(raw)) return raw;
        return 'screening';
    }

    // Ambil entri tahap terakhir dari pipeline_timeline.stages
    function getLatestStageEntry(app) {
        const stages = app && app.pipeline_timeline && Array.isArray(app.pipeline_timeline.stages)
            ? app.pipeline_timeline.stages
            : [];
        return stages.length ? stages[stages.length - 1] : null;
    }

    // Ambil jadwal interview dari stages jika ada
    function getInterviewSchedule(app) {
        const stages = app && app.pipeline_timeline && Array.isArray(app.pipeline_timeline.stages)
            ? app.pipeline_timeline.stages
            : [];
        for (let i = stages.length - 1; i >= 0; i--) {
            if (stages[i] && stages[i].interview_schedule) {
                return stages[i].interview_schedule;
            }
        }
        return null;
    }

    // ---------------------------------------------------------------------
    // Fetch data dari backend
    // ---------------------------------------------------------------------
    async function loadDashboardData(showToastOnRefresh = false) {
        const syncEl = document.getElementById('sync-status-label');
        if (syncEl) syncEl.textContent = 'Menyinkronkan...';

        try {
            const [jobsRes, appsRes] = await Promise.all([
                JH.jobs.mine(),
                JH.applications.list(),
            ]);

            state.jobs = Array.isArray(jobsRes.data) ? jobsRes.data : [];
            // Filter lamaran yang tidak dibatalkan oleh pelamar
            const rawApps = Array.isArray(appsRes.data) ? appsRes.data : [];
            state.applications = rawApps.filter((a) => a.status !== 'canceled');

            renderJobSelector();
            renderManagedJobs();
            renderStatsAndBoard();

            if (syncEl) syncEl.textContent = 'Aktif (Live)';
            if (showToastOnRefresh) {
                JH.toast('Data pipeline dan lowongan berhasil diperbarui.', 'info', 2200);
            }
        } catch (err) {
            if (syncEl) syncEl.textContent = 'Gagal Sinkronisasi';
            JH.showError(err, 'Gagal memuat data dashboard recruiter.');
        }
    }

    // ---------------------------------------------------------------------
    // Render Dropdown Filter Lowongan & Daftar Lowongan yang Dikelola
    // ---------------------------------------------------------------------
    function renderJobSelector() {
        const select = document.getElementById('job-filter-select');
        const metaEl = document.getElementById('selected-job-meta');
        const editBtn = document.getElementById('btn-edit-selected-job');
        if (!select) return;

        const currentVal = state.selectedJobId;
        const totalApps = state.applications.length;
        const openJobsCount = state.jobs.filter((j) => j.status === 'open').length;

        let optionsHtml = `<option value="all">Semua Lowongan (${state.jobs.length} Lowongan - ${totalApps} Pelamar)</option>`;
        state.jobs.forEach((job) => {
            const count = job.applications_count !== undefined
                ? job.applications_count
                : state.applications.filter((a) => Number(a.job_vacancy_id) === Number(job.id)).length;
            const statusText = job.status === 'open' ? 'OPEN' : 'CLOSED';
            optionsHtml += `<option value="${job.id}">[${statusText}] ${JH.util.escapeHtml(job.title)} (${count} Pelamar)</option>`;
        });

        select.innerHTML = optionsHtml;
        if (currentVal !== 'all' && state.jobs.some((j) => String(j.id) === String(currentVal))) {
            select.value = String(currentVal);
        } else {
            state.selectedJobId = 'all';
            select.value = 'all';
        }

        if (state.selectedJobId === 'all') {
            if (metaEl) {
                metaEl.textContent = `${openJobsCount} Lowongan Aktif - Total ${totalApps} Kandidat Terdaftar`;
            }
            if (editBtn) editBtn.disabled = true;
        } else {
            const job = state.jobs.find((j) => String(j.id) === String(state.selectedJobId));
            if (job && metaEl) {
                const count = state.applications.filter((a) => Number(a.job_vacancy_id) === Number(job.id)).length;
                metaEl.textContent = `${job.location || '-'} - Kuota ${job.quota || 1} - Status: ${job.status === 'open' ? 'Dibuka' : 'Ditutup'} - ${count} Kandidat`;
            }
            if (editBtn) editBtn.disabled = false;
        }
    }

    function renderManagedJobs() {
        const grid = document.getElementById('managed-jobs-grid');
        const badge = document.getElementById('managed-jobs-badge');
        if (!grid) return;

        const openCount = state.jobs.filter((j) => j.status === 'open').length;
        if (badge) {
            badge.textContent = `${state.jobs.length} Lowongan (${openCount} Aktif)`;
        }

        if (state.jobs.length === 0) {
            grid.innerHTML = `
                <div class="col-span-full bg-surface-container-lowest border border-dashed border-outline-variant rounded-xl p-6 text-center">
                    <span class="material-symbols-outlined text-3xl text-outline mb-1">work_off</span>
                    <p class="text-label-md font-bold text-on-surface">Belum ada lowongan pekerjaan yang diterbitkan</p>
                    <p class="text-body-sm text-outline mt-0.5 mb-3">Klik tombol "Pasang Lowongan Baru" untuk mulai menerima berkas lamaran kandidat.</p>
                </div>
            `;
            return;
        }

        grid.innerHTML = state.jobs.map((job) => {
            const isSelected = String(state.selectedJobId) === String(job.id);
            const isOpen = job.status === 'open';
            const appCount = job.applications_count !== undefined
                ? job.applications_count
                : state.applications.filter((a) => Number(a.job_vacancy_id) === Number(job.id)).length;
            const reqs = Array.isArray(job.requirements) ? job.requirements : [];

            const statusBadge = isOpen
                ? '<span class="bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">OPEN</span>'
                : '<span class="bg-surface-container-high text-on-surface-variant border border-outline-variant text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">CLOSED</span>';

            const reqTags = reqs.slice(0, 4).map((r) => {
                const name = JH.util.escapeHtml(JH.util.skillName(r));
                return `<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">${name}</span>`;
            }).join('');

            return `
                <div class="bg-surface-container-lowest border ${isSelected ? 'border-2 border-secondary shadow-md' : 'border-outline-variant/80'} rounded-xl p-3.5 flex flex-col justify-between gap-2.5 transition-all hover:border-secondary">
                    <div>
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <h3 class="font-headline-sm text-label-md font-bold text-on-surface truncate" title="${JH.util.escapeHtml(job.title)}">${JH.util.escapeHtml(job.title)}</h3>
                                <p class="text-body-sm text-outline truncate">${JH.util.escapeHtml(job.company || '-')} - ${JH.util.escapeHtml(job.location || '-')}</p>
                            </div>
                            ${statusBadge}
                        </div>
                        <div class="flex items-center gap-3 mt-2 text-[11px] text-on-surface-variant">
                            <span class="flex items-center gap-1 font-semibold text-secondary">
                                <span class="material-symbols-outlined text-sm">group</span>
                                ${appCount} Pelamar
                            </span>
                            <span>Kuota: ${job.quota || 1}</span>
                            <span>${JH.util.formatSalaryRange(job.salary_min, job.salary_max)}</span>
                        </div>
                        <div class="flex flex-wrap gap-1 mt-2">
                            ${reqTags}
                            ${reqs.length > 4 ? `<span class="text-[10px] text-outline px-1">+${reqs.length - 4}</span>` : ''}
                        </div>
                    </div>
                    <div class="pt-2 border-t border-outline-variant/50 flex items-center justify-between gap-1.5">
                        <button type="button" data-action="filter-job" data-job-id="${job.id}" class="${isSelected ? 'bg-secondary text-white' : 'bg-surface-container-low hover:bg-surface-container text-on-surface'} text-label-sm font-semibold px-2.5 py-1 rounded-lg transition-colors flex-1 text-center">
                            ${isSelected ? 'Sedang Difilter' : 'Filter Pipeline'}
                        </button>
                        <button type="button" data-action="edit-job" data-job-id="${job.id}" class="p-1.5 rounded-lg border border-outline-variant/60 hover:bg-surface-container text-on-surface-variant hover:text-secondary transition-colors" title="Edit Lowongan">
                            <span class="material-symbols-outlined text-base">edit</span>
                        </button>
                        ${isOpen ? `
                        <button type="button" data-action="close-job" data-job-id="${job.id}" class="p-1.5 rounded-lg border border-outline-variant/60 hover:bg-error-container/50 text-on-surface-variant hover:text-error transition-colors" title="Tutup Lowongan">
                            <span class="material-symbols-outlined text-base">lock</span>
                        </button>` : ''}
                    </div>
                </div>
            `;
        }).join('');
    }

    // ---------------------------------------------------------------------
    // Filter kandidat & Render Statistik + Kanban Board
    // ---------------------------------------------------------------------
    function getAppsFilteredByJob() {
        if (state.selectedJobId === 'all') return state.applications;
        return state.applications.filter((a) => String(a.job_vacancy_id) === String(state.selectedJobId));
    }

    function matchesSearchAndQuickFilter(app) {
        // Quick filter
        const stage = getStage(app);
        const score = Number(app.match_score) || 0;
        if (state.quickFilter === 'high_score' && score < 80) return false;
        if (state.quickFilter === 'needs_review' && stage !== 'screening') return false;
        if (state.quickFilter === 'has_cv' && !app.cv_file_path) return false;

        // Search query
        if (state.searchQuery) {
            const q = state.searchQuery.toLowerCase();
            const name = (app.applicant && app.applicant.name ? app.applicant.name : '').toLowerCase();
            const email = (app.applicant && app.applicant.email ? app.applicant.email : '').toLowerCase();
            const jobTitle = (app.job_vacancy && app.job_vacancy.title ? app.job_vacancy.title : '').toLowerCase();
            const matched = Array.isArray(app.matched_skills) ? app.matched_skills.join(' ').toLowerCase() : '';
            const bio = (app.applicant && app.applicant.bio ? app.applicant.bio : '').toLowerCase();
            if (!name.includes(q) && !email.includes(q) && !jobTitle.includes(q) && !matched.includes(q) && !bio.includes(q)) {
                return false;
            }
        }
        return true;
    }

    function renderStatsAndBoard() {
        const jobApps = getAppsFilteredByJob();

        // Hitung statistik pada cakupan lowongan terpilih
        const total = jobApps.length;
        const avgScore = total > 0
            ? Math.round((jobApps.reduce((sum, a) => sum + (Number(a.match_score) || 0), 0) / total) * 10) / 10
            : 0;
        const highFitCount = jobApps.filter((a) => (Number(a.match_score) || 0) >= 80).length;
        const cvCount = jobApps.filter((a) => !!a.cv_file_path).length;

        const stageCounts = {
            screening: 0,
            assessment: 0,
            interview: 0,
            offering: 0,
            rejected: 0,
        };
        jobApps.forEach((a) => {
            const st = getStage(a);
            stageCounts[st] = (stageCounts[st] || 0) + 1;
        });

        const openJobsCount = state.jobs.filter((j) => j.status === 'open').length;

        // Update kartu statistik atas
        setText('stat-total-applicants', `${total} Pelamar`);
        setText('stat-active-jobs-sub', `${openJobsCount} Lowongan Aktif dari ${state.jobs.length}`);
        setText('stat-avg-score', `${avgScore}%`);
        setText('stat-high-fit-sub', `${highFitCount} Kandidat Skor >= 80%`);
        setText('stat-screening-assessment', `${stageCounts.screening + stageCounts.assessment} Kandidat`);
        setText('stat-screening-sub', `${stageCounts.screening} Screening - ${stageCounts.assessment} Assessment`);
        setText('stat-interview-count', `${stageCounts.interview} Wawancara`);
        setText('stat-offering-count', `${stageCounts.offering} Offering`);
        setText('stat-rejected-sub', `${stageCounts.rejected} Ditolak`);

        // Update angka pada pill filter
        setText('pill-count-all', total);
        setText('pill-count-high', highFitCount);
        setText('pill-count-screening', stageCounts.screening);
        setText('pill-count-cv', cvCount);

        // Kelompokkan kandidat yang lolos filter pencarian & quick filter ke masing-masing kolom
        const filteredApps = jobApps.filter(matchesSearchAndQuickFilter);

        COLUMNS.forEach((colStage) => {
            const colList = document.getElementById(`col-list-${colStage}`);
            const colCount = document.getElementById(`col-count-${colStage}`);

            // Urutkan berdasarkan match_score tertinggi
            const items = filteredApps
                .filter((a) => getStage(a) === colStage)
                .sort((a, b) => (Number(b.match_score) || 0) - (Number(a.match_score) || 0));

            if (colCount) colCount.textContent = items.length;
            if (!colList) return;

            if (items.length === 0) {
                colList.innerHTML = `
                    <div class="border-2 border-dashed border-outline-variant/60 rounded-xl p-5 text-center text-outline text-label-sm flex flex-col items-center justify-center gap-1.5 bg-surface-container/20">
                        <span class="material-symbols-outlined text-xl">inbox</span>
                        <span>Tidak ada kandidat di tahap ${JH.util.stageLabel(colStage)}</span>
                    </div>
                `;
                return;
            }

            colList.innerHTML = items.map((app) => renderCandidateCard(app, colStage)).join('');
        });
    }

    function renderCandidateCard(app, stage) {
        const applicant = app.applicant || {};
        const job = app.job_vacancy || {};
        const score = Math.round(Number(app.match_score) || 0);
        const tone = JH.util.scoreTone(score);
        const matchedSkills = Array.isArray(app.matched_skills) ? app.matched_skills : [];
        const latestEntry = getLatestStageEntry(app);
        const schedule = getInterviewSchedule(app);
        const nextSt = JH.util.nextStage(stage);

        const isStar = score >= 85 && stage !== 'rejected';
        const ribbonHtml = isStar ? `
            <div class="bg-amber-100 border-b border-amber-300 text-amber-900 font-coordinate-tag text-[10px] font-bold py-1 px-3 -mx-3.5 -mt-3.5 mb-2.5 flex items-center justify-between">
                <span class="flex items-center gap-1">
                    <span class="material-symbols-outlined text-xs text-amber-800" style="font-variation-settings: 'FILL' 1;">anchor</span>
                    ${score}% ANCHOR FIT
                </span>
                <span class="text-amber-800 tracking-wider">TOP MATCH</span>
            </div>
        ` : '';

        // Info jadwal interview jika ada
        let stageBannerHtml = '';
        if (stage === 'interview' && schedule && (schedule.datetime || schedule.meeting_url || schedule.interviewer)) {
            stageBannerHtml = `
                <div class="bg-secondary-fixed/30 border border-secondary/30 rounded-lg p-2.5 my-2 text-[11px] space-y-0.5">
                    <div class="flex items-center gap-1 font-bold text-on-secondary-fixed-variant">
                        <span class="material-symbols-outlined text-sm text-secondary">calendar_today</span>
                        <span>${JH.util.escapeHtml(schedule.datetime ? JH.util.formatDate(schedule.datetime, true) : 'Jadwal Wawancara')}</span>
                        <span class="bg-secondary text-white text-[9px] px-1.5 py-0.2 rounded uppercase ml-auto">${JH.util.escapeHtml(schedule.type || 'online')}</span>
                    </div>
                    ${schedule.interviewer ? `<div class="text-on-surface-variant">Pewawancara: <strong>${JH.util.escapeHtml(schedule.interviewer)}</strong></div>` : ''}
                    ${schedule.meeting_url ? `<div class="text-secondary truncate">${JH.util.escapeHtml(schedule.meeting_url)}</div>` : ''}
                </div>
            `;
        } else if (latestEntry && latestEntry.notes) {
            stageBannerHtml = `
                <div class="bg-surface-container-low p-2 rounded-lg border border-outline-variant/40 mb-2.5 text-[11px] text-on-surface-variant line-clamp-2">
                    <span class="font-semibold text-outline">Catatan:</span> ${JH.util.escapeHtml(latestEntry.notes)}
                </div>
            `;
        }

        const skillsHtml = matchedSkills.length > 0
            ? matchedSkills.slice(0, 5).map((s) => `<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">${JH.util.escapeHtml(JH.util.skillName(s))}</span>`).join('')
            : '<span class="text-[11px] text-outline italic">Belum ada skill cocok</span>';

        const cvBtnHtml = app.cv_file_path ? `
            <button type="button" data-action="download-cv" data-app-id="${app.id}" class="text-on-surface-variant hover:text-secondary text-label-sm font-label-sm flex items-center gap-1 py-1 px-1.5 rounded hover:bg-surface-container transition-colors" title="Unduh CV PDF">
                <span class="material-symbols-outlined text-base">description</span>
                <span>CV</span>
            </button>
        ` : `
            <span class="text-[11px] text-outline py-1">Tanpa CV</span>
        `;

        const quickAdvanceHtml = (nextSt && stage !== 'rejected') ? `
            <button type="button" data-action="quick-next-stage" data-app-id="${app.id}" data-next-stage="${nextSt}" class="bg-secondary hover:bg-on-secondary-container text-white text-label-sm font-label-sm px-2.5 py-1 rounded-lg transition-colors font-semibold">
                Ke ${JH.util.stageLabel(nextSt)} &rarr;
            </button>
        ` : `
            <button type="button" data-action="open-candidate" data-app-id="${app.id}" class="bg-surface-container-high hover:bg-surface-variant text-on-surface text-label-sm font-label-sm px-2.5 py-1 rounded-lg transition-colors font-semibold">
                Detail Evaluasi
            </button>
        `;

        return `
            <div data-card-app-id="${app.id}" class="candidate-card cursor-pointer bg-surface-container-lowest border ${isStar ? 'border-2 border-secondary shadow-md' : 'border-outline-variant/80 shadow-xs'} rounded-xl p-3.5 hover:border-secondary hover:shadow-md transition-all duration-150 relative overflow-hidden group">
                ${ribbonHtml}
                <div class="flex items-start justify-between gap-2 mb-1.5">
                    <div class="min-w-0">
                        <h3 class="font-headline-sm text-headline-sm font-bold text-on-surface group-hover:text-secondary transition-colors truncate ${stage === 'rejected' ? 'line-through decoration-outline' : ''}">
                            ${JH.util.escapeHtml(applicant.name || 'Kandidat #' + app.user_id)}
                        </h3>
                        <p class="text-body-sm font-body-sm text-outline truncate" title="${JH.util.escapeHtml(job.title || '')}">
                            ${JH.util.escapeHtml(job.title || 'Lowongan #' + app.job_vacancy_id)}
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1 ${tone.bg} ${tone.text} border ${tone.border} font-coordinate-tag text-coordinate-tag px-2 py-0.5 rounded font-bold shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full ${tone.bar}"></span>
                        ${score}% MATCH
                    </span>
                </div>

                <div class="text-[11px] text-on-surface-variant flex items-center gap-1.5 mb-2.5 bg-surface-container-low px-2 py-1 rounded">
                    <span class="material-symbols-outlined text-xs text-outline">schedule</span>
                    <span>${JH.util.escapeHtml(JH.util.timeAgo(app.created_at))}</span>
                    <span class="text-outline-variant">&bull;</span>
                    <span class="truncate">${JH.util.escapeHtml(applicant.location || job.location || 'Indonesia')}</span>
                </div>

                ${stageBannerHtml}

                <div class="flex flex-wrap gap-1 mb-3">
                    ${skillsHtml}
                </div>

                <div class="pt-2 border-t border-outline-variant/50 flex items-center justify-between gap-2">
                    ${cvBtnHtml}
                    ${quickAdvanceHtml}
                </div>
            </div>
        `;
    }

    // ---------------------------------------------------------------------
    // Modal Detail Kandidat & Update Tahapan Pipeline
    // ---------------------------------------------------------------------
    function openCandidateModal(appId) {
        const app = state.applications.find((a) => Number(a.id) === Number(appId));
        if (!app) return;

        state.activeCandidateId = app.id;
        const applicant = app.applicant || {};
        const job = app.job_vacancy || {};
        const score = Math.round(Number(app.match_score) || 0);
        const tone = JH.util.scoreTone(score);
        const currentStage = getStage(app);

        setText('modal-cand-initials', JH.util.initials(applicant.name || 'K'));
        setText('modal-cand-name', applicant.name || 'Kandidat #' + app.user_id);
        setText('modal-cand-job', `Melamar: ${job.title || '-'} (${job.company || '-'})`);

        const scoreBadge = document.getElementById('modal-cand-score-badge');
        if (scoreBadge) {
            scoreBadge.className = `px-3 py-1 rounded-full text-label-sm font-bold border ${tone.bg} ${tone.text} ${tone.border}`;
            scoreBadge.textContent = `${score}% MATCH (${(app.match_category || 'good').toUpperCase()})`;
        }

        setText('modal-cand-email', applicant.email || '-');
        setText('modal-cand-phone', applicant.phone || 'Nomor telepon belum diisi');
        setText('modal-cand-location', applicant.location || '-');
        setText('modal-cand-applied-at', `Dikirim: ${JH.util.formatDate(app.created_at, true)}`);
        setText('modal-cand-bio', applicant.bio || 'Kandidat belum menuliskan bio singkat di profilnya.');
        setText('modal-cand-cover-letter', app.cover_letter || 'Tidak ada surat pengantar (cover letter) yang dilampirkan.');

        // Tombol CV
        const dlBtn = document.getElementById('btn-modal-download-cv');
        const noCvNote = document.getElementById('modal-no-cv-note');
        if (app.cv_file_path) {
            if (dlBtn) dlBtn.classList.remove('hidden');
            if (noCvNote) noCvNote.classList.add('hidden');
        } else {
            if (dlBtn) dlBtn.classList.add('hidden');
            if (noCvNote) noCvNote.classList.remove('hidden');
        }

        // Matched vs Missing Skills
        const matchedContainer = document.getElementById('modal-cand-matched-skills');
        const missingContainer = document.getElementById('modal-cand-missing-skills');
        const matched = Array.isArray(app.matched_skills) ? app.matched_skills : [];
        const missing = Array.isArray(app.missing_skills) ? app.missing_skills : [];

        if (matchedContainer) {
            matchedContainer.innerHTML = matched.length
                ? matched.map((s) => `<span class="bg-emerald-100 text-emerald-900 border border-emerald-300 text-label-sm font-semibold px-2.5 py-0.5 rounded-lg">${JH.util.escapeHtml(JH.util.skillName(s))}</span>`).join('')
                : '<span class="text-body-sm text-outline italic">Tidak ada keahlian yang cocok.</span>';
        }
        if (missingContainer) {
            missingContainer.innerHTML = missing.length
                ? missing.map((s) => `<span class="bg-rose-100 text-rose-900 border border-rose-300 text-label-sm font-semibold px-2.5 py-0.5 rounded-lg">${JH.util.escapeHtml(JH.util.skillName(s))}</span>`).join('')
                : '<span class="text-body-sm text-emerald-700 font-medium">Semua keahlian utama terpenuhi (100% komplit).</span>';
        }

        // Riwayat Linimasa (Pipeline Timeline)
        const timelineEl = document.getElementById('modal-cand-timeline');
        const stages = app.pipeline_timeline && Array.isArray(app.pipeline_timeline.stages)
            ? app.pipeline_timeline.stages
            : [];

        if (timelineEl) {
            if (stages.length === 0) {
                timelineEl.innerHTML = '<p class="text-body-sm text-outline italic">Belum ada riwayat tahapan.</p>';
            } else {
                timelineEl.innerHTML = stages.map((st, idx) => {
                    const stStatus = st.status || 'in_progress';
                    const badgeClass = stStatus === 'passed'
                        ? 'bg-emerald-100 text-emerald-800'
                        : stStatus === 'rejected'
                            ? 'bg-error-container text-on-error-container'
                            : 'bg-secondary-fixed text-on-secondary-fixed';

                    const sched = st.interview_schedule;
                    const schedInfo = sched ? `
                        <div class="mt-1.5 bg-secondary-fixed/30 border border-secondary/30 rounded-lg p-2 text-body-sm">
                            <strong>Jadwal Wawancara (${JH.util.escapeHtml(sched.type || 'online')}):</strong>
                            ${JH.util.escapeHtml(sched.datetime ? JH.util.formatDate(sched.datetime, true) : '-')}
                            ${sched.interviewer ? ` | Pewawancara: ${JH.util.escapeHtml(sched.interviewer)}` : ''}
                            ${sched.meeting_url ? ` | Info/Link: ${JH.util.escapeHtml(sched.meeting_url)}` : ''}
                        </div>
                    ` : '';

                    return `
                        <div class="flex items-start gap-3 p-3 rounded-xl bg-surface-container-low border border-outline-variant/50">
                            <div class="w-6 h-6 rounded-full bg-primary-container text-white text-xs font-bold flex items-center justify-center shrink-0 mt-0.5">
                                ${idx + 1}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <span class="font-headline-sm text-label-md font-bold text-on-surface uppercase">${JH.util.escapeHtml(JH.util.stageLabel(st.stage))}</span>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-full ${badgeClass}">${JH.util.escapeHtml(stStatus)}</span>
                                        <span class="text-[11px] text-outline">${JH.util.escapeHtml(JH.util.formatDate(st.entered_at, true))}</span>
                                    </div>
                                </div>
                                ${st.notes ? `<p class="text-body-sm text-on-surface-variant mt-1">${JH.util.escapeHtml(st.notes)}</p>` : ''}
                                ${schedInfo}
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Set nilai form update pipeline
        const stageSelect = document.getElementById('pipeline-stage-select');
        const statusSelect = document.getElementById('pipeline-status-select');
        const notesInput = document.getElementById('pipeline-notes');
        const latest = getLatestStageEntry(app);

        if (stageSelect) stageSelect.value = currentStage;
        if (statusSelect) statusSelect.value = (latest && latest.status) || 'in_progress';
        if (notesInput) notesInput.value = '';

        // Pre-fill jadwal interview jika sudah ada
        const existingSched = getInterviewSchedule(app) || {};
        const dtInput = document.getElementById('interview-datetime');
        const typeInput = document.getElementById('interview-type');
        const urlInput = document.getElementById('interview-url');
        const interviewerInput = document.getElementById('interview-interviewer');

        if (dtInput) dtInput.value = existingSched.datetime ? String(existingSched.datetime).slice(0, 16) : '';
        if (typeInput) typeInput.value = existingSched.type || 'online';
        if (urlInput) urlInput.value = existingSched.meeting_url || '';
        if (interviewerInput) interviewerInput.value = existingSched.interviewer || '';

        toggleInterviewScheduleFields();

        // Tombol aksi cepat "Majukan Tahap"
        const nextSt = JH.util.nextStage(currentStage);
        const quickAdvBtn = document.getElementById('btn-quick-advance');
        const quickAdvLabel = document.getElementById('btn-quick-advance-label');
        if (quickAdvBtn && quickAdvLabel) {
            if (nextSt && currentStage !== 'rejected') {
                quickAdvBtn.classList.remove('hidden');
                quickAdvBtn.dataset.nextStage = nextSt;
                quickAdvLabel.textContent = `Majukan ke ${JH.util.stageLabel(nextSt)}`;
            } else {
                quickAdvBtn.classList.add('hidden');
            }
        }

        const modal = document.getElementById('candidate-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeCandidateModal() {
        const modal = document.getElementById('candidate-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        state.activeCandidateId = null;
    }

    function toggleInterviewScheduleFields() {
        const stageSelect = document.getElementById('pipeline-stage-select');
        const box = document.getElementById('interview-schedule-fields');
        if (!stageSelect || !box) return;
        if (stageSelect.value === 'interview') {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }

    async function submitPipelineUpdate(applicationId, payload, triggerBtn) {
        try {
            if (triggerBtn) JH.setLoading(triggerBtn, true, 'Menyimpan...');
            const res = await JH.pipelines.updateStatus(applicationId, payload);
            JH.toast(res.message || 'Tahapan seleksi berhasil diperbarui.', 'success');
            await loadDashboardData(false);
            if (state.activeCandidateId === applicationId) {
                openCandidateModal(applicationId);
            }
        } catch (err) {
            JH.showError(err, 'Gagal memperbarui tahapan seleksi.');
        } finally {
            if (triggerBtn) JH.setLoading(triggerBtn, false);
        }
    }

    // ---------------------------------------------------------------------
    // Modal Buat / Edit Lowongan Kerja
    // ---------------------------------------------------------------------
    function addRequirementRow(skill = '', level = 'required') {
        const container = document.getElementById('requirements-rows');
        if (!container) return;

        const row = document.createElement('div');
        row.className = 'req-row flex items-center gap-2';
        row.innerHTML = `
            <input type="text" placeholder="Nama keahlian (contoh: Laravel, Docker, REST API)" value="${JH.util.escapeHtml(skill)}" required class="req-skill-input flex-1 rounded-lg border-outline-variant bg-surface-container-lowest text-body-sm focus:border-secondary focus:ring-secondary"/>
            <select class="req-level-select rounded-lg border-outline-variant bg-surface-container-lowest text-body-sm focus:border-secondary focus:ring-secondary">
                <option value="required" ${level === 'required' ? 'selected' : ''}>Wajib (Bobot 3)</option>
                <option value="important" ${level === 'important' ? 'selected' : ''}>Penting (Bobot 2)</option>
                <option value="nice_to_have" ${level === 'nice_to_have' ? 'selected' : ''}>Nilai Tambah (Bobot 1)</option>
            </select>
            <button type="button" class="btn-remove-req p-2 rounded-lg text-outline hover:text-error hover:bg-error-container/40 transition-colors" title="Hapus Skill">
                <span class="material-symbols-outlined text-base">delete</span>
            </button>
        `;
        row.querySelector('.btn-remove-req').addEventListener('click', () => {
            if (container.querySelectorAll('.req-row').length > 1) {
                row.remove();
            } else {
                JH.toast('Minimal harus ada 1 syarat keahlian.', 'warning');
            }
        });
        container.appendChild(row);
    }

    function openJobModal(jobId = null) {
        state.editingJobId = jobId ? Number(jobId) : null;
        const modal = document.getElementById('job-modal');
        const titleEl = document.getElementById('job-modal-title');
        const submitLabel = document.getElementById('btn-submit-job-label');
        const closeVacancyBtn = document.getElementById('btn-close-vacancy');
        const reqContainer = document.getElementById('requirements-rows');

        if (reqContainer) reqContainer.innerHTML = '';

        if (state.editingJobId) {
            const job = state.jobs.find((j) => Number(j.id) === state.editingJobId);
            if (!job) return;

            if (titleEl) titleEl.textContent = `Edit Lowongan: ${job.title}`;
            if (submitLabel) submitLabel.textContent = 'Simpan Perubahan';
            if (closeVacancyBtn) {
                if (job.status === 'open') {
                    closeVacancyBtn.classList.remove('hidden');
                    closeVacancyBtn.classList.add('inline-flex');
                } else {
                    closeVacancyBtn.classList.add('hidden');
                    closeVacancyBtn.classList.remove('inline-flex');
                }
            }

            setVal('job-id-input', job.id);
            setVal('job-title', job.title || '');
            setVal('job-company', job.company || '');
            setVal('job-location', job.location || '');
            setVal('job-employment-type', job.employment_type || 'full-time');
            setVal('job-quota', job.quota || 1);
            setVal('job-salary-min', job.salary_min || '');
            setVal('job-salary-max', job.salary_max || '');
            setVal('job-expires-at', job.expires_at ? String(job.expires_at).slice(0, 10) : '');
            setVal('job-description', job.description || '');

            const reqs = Array.isArray(job.requirements) ? job.requirements : [];
            if (reqs.length > 0) {
                reqs.forEach((r) => addRequirementRow(JH.util.skillName(r), r.level || 'required'));
            } else {
                addRequirementRow('', 'required');
            }
        } else {
            if (titleEl) titleEl.textContent = 'Pasang Lowongan Pekerjaan Baru';
            if (submitLabel) submitLabel.textContent = 'Terbitkan Lowongan';
            if (closeVacancyBtn) {
                closeVacancyBtn.classList.add('hidden');
                closeVacancyBtn.classList.remove('inline-flex');
            }

            setVal('job-id-input', '');
            setVal('job-title', '');
            setVal('job-company', 'PT Solusi Bahari Digital');
            setVal('job-location', 'Tegal');
            setVal('job-employment-type', 'full-time');
            setVal('job-quota', 2);
            setVal('job-salary-min', '');
            setVal('job-salary-max', '');
            setVal('job-expires-at', '');
            setVal('job-description', '');

            addRequirementRow('PHP', 'required');
            addRequirementRow('Laravel', 'required');
            addRequirementRow('REST API', 'important');
        }

        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeJobModal() {
        const modal = document.getElementById('job-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        state.editingJobId = null;
    }

    async function handleSaveJob(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btn-submit-job');
        const reqRows = document.querySelectorAll('#requirements-rows .req-row');

        const requirements = [];
        reqRows.forEach((row) => {
            const skill = row.querySelector('.req-skill-input').value.trim();
            const level = row.querySelector('.req-level-select').value;
            if (skill) {
                requirements.push({ skill, level });
            }
        });

        if (requirements.length === 0) {
            JH.toast('Tambahkan minimal satu syarat keahlian.', 'warning');
            return;
        }

        const payload = {
            title: getVal('job-title').trim(),
            company: getVal('job-company').trim(),
            location: getVal('job-location').trim(),
            employment_type: getVal('job-employment-type'),
            quota: Number(getVal('job-quota')) || 1,
            description: getVal('job-description').trim(),
            requirements,
        };

        const salMin = getVal('job-salary-min');
        const salMax = getVal('job-salary-max');
        const expAt = getVal('job-expires-at');
        if (salMin !== '') payload.salary_min = Number(salMin);
        if (salMax !== '') payload.salary_max = Number(salMax);
        if (expAt !== '') payload.expires_at = expAt;

        try {
            JH.setLoading(submitBtn, true, 'Menyimpan...');
            if (state.editingJobId) {
                const res = await JH.jobs.update(state.editingJobId, payload);
                JH.toast(res.message || 'Data lowongan berhasil diperbarui.', 'success');
            } else {
                const res = await JH.jobs.create(payload);
                JH.toast(res.message || 'Lowongan baru berhasil diterbitkan.', 'success');
            }
            closeJobModal();
            await loadDashboardData(false);
        } catch (err) {
            JH.showError(err, 'Gagal menyimpan data lowongan.');
        } finally {
            JH.setLoading(submitBtn, false);
        }
    }

    async function handleCloseVacancy(jobId) {
        const job = state.jobs.find((j) => Number(j.id) === Number(jobId));
        if (!job) return;
        if (!window.confirm(`Tutup lowongan "${job.title}"? Pelamar baru tidak akan bisa mengirim berkas ke lowongan ini.`)) {
            return;
        }
        try {
            const res = await JH.jobs.close(job.id);
            JH.toast(res.message || 'Lowongan berhasil ditutup.', 'info');
            closeJobModal();
            await loadDashboardData(false);
        } catch (err) {
            JH.showError(err, 'Gagal menutup lowongan.');
        }
    }

    // ---------------------------------------------------------------------
    // Helper DOM
    // ---------------------------------------------------------------------
    function setText(id, text) {
        const el = document.getElementById(id);
        if (el) el.textContent = text;
    }

    function setVal(id, val) {
        const el = document.getElementById(id);
        if (el) el.value = val;
    }

    function getVal(id) {
        const el = document.getElementById(id);
        return el ? el.value : '';
    }

    // ---------------------------------------------------------------------
    // Event Listeners
    // ---------------------------------------------------------------------
    function bindEvents() {
        // Filter dropdown lowongan
        const jobSelect = document.getElementById('job-filter-select');
        if (jobSelect) {
            jobSelect.addEventListener('change', () => {
                state.selectedJobId = jobSelect.value;
                renderJobSelector();
                renderManagedJobs();
                renderStatsAndBoard();
            });
        }

        // Tombol edit lowongan terpilih di header dock
        const editSelectedBtn = document.getElementById('btn-edit-selected-job');
        if (editSelectedBtn) {
            editSelectedBtn.addEventListener('click', () => {
                if (state.selectedJobId !== 'all') {
                    openJobModal(state.selectedJobId);
                }
            });
        }

        // Toggle panel daftar lowongan
        const toggleJobsBtn = document.getElementById('btn-toggle-jobs-panel');
        const managedSection = document.getElementById('managed-jobs-section');
        if (toggleJobsBtn && managedSection) {
            toggleJobsBtn.addEventListener('click', () => {
                managedSection.classList.toggle('hidden');
            });
        }

        // Refresh board
        const refreshBtn = document.getElementById('btn-refresh-board');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', () => loadDashboardData(true));
        }

        // Delegasi klik pada daftar lowongan yang dikelola
        const managedGrid = document.getElementById('managed-jobs-grid');
        if (managedGrid) {
            managedGrid.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-action]');
                if (!btn) return;
                const action = btn.dataset.action;
                const jobId = btn.dataset.jobId;
                if (action === 'filter-job') {
                    state.selectedJobId = String(state.selectedJobId) === String(jobId) ? 'all' : String(jobId);
                    renderJobSelector();
                    renderManagedJobs();
                    renderStatsAndBoard();
                } else if (action === 'edit-job') {
                    openJobModal(jobId);
                } else if (action === 'close-job') {
                    handleCloseVacancy(jobId);
                }
            });
        }

        // Pencarian kandidat
        const searchInput = document.getElementById('candidate-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                state.searchQuery = searchInput.value.trim();
                renderStatsAndBoard();
            });
        }

        // Quick filter pills
        const pillsWrap = document.getElementById('quick-filter-pills');
        if (pillsWrap) {
            pillsWrap.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-quick-filter]');
                if (!btn) return;
                state.quickFilter = btn.dataset.quickFilter;
                pillsWrap.querySelectorAll('[data-quick-filter]').forEach((b) => {
                    if (b.dataset.quickFilter === state.quickFilter) {
                        b.className = 'bg-primary-container text-white text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 font-semibold transition-colors';
                    } else {
                        b.className = 'bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface border border-outline-variant/60 text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors';
                    }
                });
                renderStatsAndBoard();
            });
        }

        // Delegasi klik pada kartu kandidat di Kanban board
        document.addEventListener('click', async (e) => {
            const actionBtn = e.target.closest('[data-action]');
            if (actionBtn) {
                const action = actionBtn.dataset.action;
                const appId = Number(actionBtn.dataset.appId);
                if (action === 'download-cv' && appId) {
                    e.stopPropagation();
                    try {
                        await JH.applications.downloadCv(appId);
                    } catch (err) {
                        JH.showError(err, 'Gagal mengunduh CV kandidat.');
                    }
                    return;
                }
                if (action === 'quick-next-stage' && appId) {
                    e.stopPropagation();
                    const nextStage = actionBtn.dataset.nextStage;
                    if (nextStage === 'interview') {
                        // Jika menuju tahap interview, buka modal agar recruiter bisa mengisi jadwal wawancara
                        openCandidateModal(appId);
                        const stageSelect = document.getElementById('pipeline-stage-select');
                        if (stageSelect) {
                            stageSelect.value = 'interview';
                            toggleInterviewScheduleFields();
                        }
                        JH.toast('Silakan lengkapi jadwal wawancara lalu klik Simpan Perubahan Tahap.', 'info');
                        return;
                    }
                    await submitPipelineUpdate(appId, {
                        stage: nextStage,
                        status: 'in_progress',
                        notes: `Kandidat diloloskan ke tahap ${JH.util.stageLabel(nextStage)} oleh recruiter.`,
                    }, actionBtn);
                    return;
                }
                if (action === 'open-candidate' && appId) {
                    e.stopPropagation();
                    openCandidateModal(appId);
                    return;
                }
            }

            const card = e.target.closest('[data-card-app-id]');
            if (card) {
                openCandidateModal(Number(card.dataset.cardAppId));
            }
        });

        // Event di dalam Modal Detail Kandidat
        const closeCandBtn = document.getElementById('btn-close-candidate-modal');
        const cancelCandBtn = document.getElementById('btn-cancel-candidate-modal');
        if (closeCandBtn) closeCandBtn.addEventListener('click', closeCandidateModal);
        if (cancelCandBtn) cancelCandBtn.addEventListener('click', closeCandidateModal);

        const modalDlCvBtn = document.getElementById('btn-modal-download-cv');
        if (modalDlCvBtn) {
            modalDlCvBtn.addEventListener('click', async () => {
                if (!state.activeCandidateId) return;
                try {
                    await JH.applications.downloadCv(state.activeCandidateId);
                } catch (err) {
                    JH.showError(err, 'Gagal mengunduh berkas CV.');
                }
            });
        }

        const stageSelect = document.getElementById('pipeline-stage-select');
        if (stageSelect) {
            stageSelect.addEventListener('change', toggleInterviewScheduleFields);
        }

        // Tombol cepat "Majukan Tahap" di dalam modal
        const quickAdvBtn = document.getElementById('btn-quick-advance');
        if (quickAdvBtn) {
            quickAdvBtn.addEventListener('click', async () => {
                if (!state.activeCandidateId) return;
                const nextStage = quickAdvBtn.dataset.nextStage;
                if (!nextStage) return;

                if (nextStage === 'interview') {
                    if (stageSelect) stageSelect.value = 'interview';
                    toggleInterviewScheduleFields();
                    const dtInput = document.getElementById('interview-datetime');
                    if (dtInput) dtInput.focus();
                    JH.toast('Isi jadwal wawancara di bawah lalu klik Simpan Perubahan Tahap.', 'info');
                    return;
                }

                const notes = getVal('pipeline-notes').trim() || `Kandidat dimajukan ke tahap ${JH.util.stageLabel(nextStage)}.`;
                await submitPipelineUpdate(state.activeCandidateId, {
                    stage: nextStage,
                    status: 'in_progress',
                    notes,
                }, quickAdvBtn);
            });
        }

        // Tombol cepat "Tolak Kandidat" di dalam modal
        const quickRejectBtn = document.getElementById('btn-quick-reject');
        if (quickRejectBtn) {
            quickRejectBtn.addEventListener('click', async () => {
                if (!state.activeCandidateId) return;
                const notes = getVal('pipeline-notes').trim() || 'Belum memenuhi kualifikasi tahapan seleksi saat ini.';
                await submitPipelineUpdate(state.activeCandidateId, {
                    stage: 'rejected',
                    status: 'rejected',
                    notes,
                }, quickRejectBtn);
            });
        }

        // Submit form update tahapan pipeline
        const pipelineForm = document.getElementById('form-update-pipeline');
        if (pipelineForm) {
            pipelineForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (!state.activeCandidateId) return;

                const stage = getVal('pipeline-stage-select');
                const status = getVal('pipeline-status-select');
                const notes = getVal('pipeline-notes').trim();
                const payload = {
                    stage,
                    status: stage === 'rejected' ? 'rejected' : status,
                };
                if (notes) payload.notes = notes;

                if (stage === 'interview') {
                    const dt = getVal('interview-datetime');
                    const type = getVal('interview-type');
                    const url = getVal('interview-url').trim();
                    const interviewer = getVal('interview-interviewer').trim();
                    const schedule = {};
                    if (dt) schedule.datetime = dt;
                    if (type) schedule.type = type;
                    if (url) schedule.meeting_url = url;
                    if (interviewer) schedule.interviewer = interviewer;
                    if (Object.keys(schedule).length > 0) {
                        payload.interview_schedule = schedule;
                    }
                }

                const submitBtn = document.getElementById('btn-submit-pipeline');
                await submitPipelineUpdate(state.activeCandidateId, payload, submitBtn);
            });
        }

        // Event Modal Buat / Edit Lowongan
        const openCreateBtn = document.getElementById('btn-open-create-job');
        const openCreateInlineBtn = document.getElementById('btn-create-job-inline');
        const closeJobBtn = document.getElementById('btn-close-job-modal');
        const cancelJobBtn = document.getElementById('btn-cancel-job-modal');
        const addReqBtn = document.getElementById('btn-add-requirement');
        const jobForm = document.getElementById('form-job');
        const closeVacancyBtn = document.getElementById('btn-close-vacancy');

        if (openCreateBtn) openCreateBtn.addEventListener('click', () => openJobModal(null));
        if (openCreateInlineBtn) openCreateInlineBtn.addEventListener('click', () => openJobModal(null));
        if (closeJobBtn) closeJobBtn.addEventListener('click', closeJobModal);
        if (cancelJobBtn) cancelJobBtn.addEventListener('click', closeJobModal);
        if (addReqBtn) addReqBtn.addEventListener('click', () => addRequirementRow('', 'required'));
        if (jobForm) jobForm.addEventListener('submit', handleSaveJob);
        if (closeVacancyBtn) {
            closeVacancyBtn.addEventListener('click', () => {
                if (state.editingJobId) handleCloseVacancy(state.editingJobId);
            });
        }
    }

    const start = () => {
        JH.bindSessionUI();
        bindEvents();
        loadDashboardData(false);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})(window, document);
