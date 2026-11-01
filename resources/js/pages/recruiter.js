import JH, { jobs, applications, pipelines, session, util, toast, showError, setLoading, confirm } from '../api.js';

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Guard Autentikasi: Khusus Recruiter & Admin
    const currentUser = session.user();
    if (!session.isLoggedIn() || !currentUser) {
        window.location.replace('/login?next=' + encodeURIComponent('/recruiter'));
        return;
    }
    if (currentUser.role !== 'recruiter' && currentUser.role !== 'admin') {
        toast('Halaman rekrutmen hanya dapat diakses oleh akun Recruiter.', 'warning', 3000);
        window.location.replace('/catalog');
        return;
    }

    const COLUMNS = ['screening', 'assessment', 'interview', 'offering', 'rejected'];

    const state = {
        user: currentUser,
        jobs: [],
        applications: [],
        selectedJobId: 'all',
        activeTab: 'pipeline', // pipeline | jobs
        activeCandidate: null,
        editingJobId: null,
        draggedAppId: null,
    };

    // 2. Tab Navigasi: Pipeline vs Kelola Lowongan
    const tabPipelineBtn = document.getElementById('recruiter-tab-pipeline-btn');
    const tabJobsBtn = document.getElementById('recruiter-tab-jobs-btn');
    const pipelineView = document.getElementById('recruiter-pipeline-view');
    const jobsView = document.getElementById('recruiter-jobs-view');

    function switchView(tab) {
        state.activeTab = tab;
        if (tab === 'jobs') {
            tabJobsBtn?.classList.add('border-[#C8460B]', 'text-[#C8460B]');
            tabJobsBtn?.classList.remove('border-transparent', 'text-[#627080]');
            tabPipelineBtn?.classList.remove('border-[#C8460B]', 'text-[#C8460B]');
            tabPipelineBtn?.classList.add('border-transparent', 'text-[#627080]');

            pipelineView?.classList.add('hidden');
            jobsView?.classList.remove('hidden');
        } else {
            tabPipelineBtn?.classList.add('border-[#C8460B]', 'text-[#C8460B]');
            tabPipelineBtn?.classList.remove('border-transparent', 'text-[#627080]');
            tabJobsBtn?.classList.remove('border-[#C8460B]', 'text-[#C8460B]');
            tabJobsBtn?.classList.add('border-transparent', 'text-[#627080]');

            pipelineView?.classList.remove('hidden');
            jobsView?.classList.add('hidden');
        }
    }

    tabPipelineBtn?.addEventListener('click', () => switchView('pipeline'));
    tabJobsBtn?.addEventListener('click', () => switchView('jobs'));

    // 3. Muat Data Dashboard (dipanggil di akhir, setelah semua elemen tab dan form dideklarasikan)
    async function loadDashboardData() {
        const loadingEl = document.getElementById('recruiter-loading');
        if (loadingEl) loadingEl.classList.remove('hidden');

        try {
            const [jobsRes, appsRes] = await Promise.all([
                jobs.mine(),
                applications.list(),
            ]);

            state.jobs = Array.isArray(jobsRes.data) ? jobsRes.data : [];
            const rawApps = Array.isArray(appsRes.data) ? appsRes.data : [];
            state.applications = rawApps.filter((a) => a.status !== 'canceled');

            if (loadingEl) loadingEl.classList.add('hidden');

            renderJobSelector();
            renderTopMetrics();
            renderKanbanBoard();
            renderManagedJobs();
        } catch (err) {
            console.error('Gagal memuat dashboard recruiter:', err);
            if (loadingEl) loadingEl.classList.add('hidden');
            showError(err, 'Gagal memuat data dasbor rekruter.');
        }
    }

    // 4. Render Ringkasan Metrik
    function renderTopMetrics() {
        const waitingScreeningEl = document.getElementById('metric-waiting-screening');
        const totalCandidatesEl = document.getElementById('metric-total-candidates');
        const scheduledInterviewsEl = document.getElementById('metric-scheduled-interviews');
        const activeJobsCountEl = document.getElementById('metric-active-jobs');

        const filteredApps = getFilteredApplications();

        // Hitung kandidat 80%+ yang menunggu di tahap screening
        const autoPassWaiting = filteredApps.filter((a) => {
            const stage = getEffectiveStage(a);
            return stage === 'screening' && (a.match_score || 0) >= 80;
        }).length;

        // Hitung wawancara terjadwal
        let interviewCount = 0;
        filteredApps.forEach((a) => {
            const stages = a.pipeline_timeline?.stages || [];
            if (stages.some((s) => s.stage === 'interview' && s.interview_schedule)) {
                interviewCount++;
            }
        });

        const activeJobs = state.jobs.filter((j) => j.status === 'open').length;

        if (waitingScreeningEl) waitingScreeningEl.textContent = autoPassWaiting;
        if (totalCandidatesEl) totalCandidatesEl.textContent = filteredApps.length;
        if (scheduledInterviewsEl) scheduledInterviewsEl.textContent = interviewCount;
        if (activeJobsCountEl) activeJobsCountEl.textContent = activeJobs;
    }

    // 5. Dropdown Pemilih Lowongan
    function renderJobSelector() {
        const select = document.getElementById('recruiter-job-selector');
        if (!select) return;

        let options = `<option value="all">Semua Lowongan (${state.jobs.length} Lowongan)</option>`;
        state.jobs.forEach((j) => {
            const statusLabel = j.status === 'open' ? 'Buka' : 'Tutup';
            const count = state.applications.filter((a) => Number(a.job_vacancy_id) === Number(j.id)).length;
            options += `<option value="${j.id}">[${statusLabel}] ${util.escapeHtml(j.title)} (${count} Kandidat)</option>`;
        });
        select.innerHTML = options;
        select.value = state.selectedJobId;

        select.onchange = () => {
            state.selectedJobId = select.value;
            renderTopMetrics();
            renderKanbanBoard();
        };
    }

    function getFilteredApplications() {
        if (state.selectedJobId === 'all') {
            return state.applications;
        }
        return state.applications.filter((a) => Number(a.job_vacancy_id) === Number(state.selectedJobId));
    }

    function getEffectiveStage(app) {
        const timelineStage = app.pipeline_timeline?.current_stage;
        const raw = (timelineStage || app.status || 'screening').toLowerCase();
        if (raw === 'submitted') return 'screening';
        if (COLUMNS.includes(raw)) return raw;
        return 'screening';
    }

    // 6. Render Papan Kanban 5 Kolom
    function renderKanbanBoard() {
        const filteredApps = getFilteredApplications();

        // Urutkan kandidat berdasarkan skor kecocokan tertinggi
        filteredApps.sort((a, b) => (b.match_score || 0) - (a.match_score || 0));

        // Kelompokkan per kolom
        const grouped = {
            screening: [],
            assessment: [],
            interview: [],
            offering: [],
            rejected: [],
        };

        filteredApps.forEach((app) => {
            const stage = getEffectiveStage(app);
            if (grouped[stage]) {
                grouped[stage].push(app);
            } else {
                grouped.screening.push(app);
            }
        });

        COLUMNS.forEach((colKey) => {
            const colContainer = document.getElementById(`kanban-col-${colKey}`);
            const countBadge = document.getElementById(`kanban-count-${colKey}`);
            if (!colContainer) return;

            const items = grouped[colKey] || [];
            if (countBadge) countBadge.textContent = items.length;

            if (!items.length) {
                colContainer.innerHTML = `
                    <div class="p-6 text-center text-xs text-[#627080] border border-dashed border-[#C5CFDA] rounded-xl bg-white/40">
                        Tidak ada kandidat di tahap ini.
                    </div>
                `;
            } else {
                colContainer.innerHTML = items.map((app) => renderCandidateCard(app, colKey)).join('');
            }

            // Pasang event listener drag & drop per kolom
            setupColumnDropEvents(colContainer, colKey);
        });

        // Pasang event listener klik kartu & tombol aksi di kartu
        attachCardClickEvents();
    }

    function renderCandidateCard(app, currentColumn) {
        const esc = util.escapeHtml;
        const applicant = app.applicant || {};
        const job = app.job_vacancy || {};
        const score = Math.round(app.match_score || 0);
        const tone = util.scoreTone(score);
        const nextStageKey = util.nextStage(currentColumn);
        const isRejected = currentColumn === 'rejected';

        // Missing skills preview
        const missing = Array.isArray(app.missing_skills) ? app.missing_skills : [];

        return `
            <div
                draggable="true"
                data-candidate-card="${app.id}"
                data-current-stage="${currentColumn}"
                class="bg-white rounded-xl border border-[#E2E7ED] hover:border-[#0C2238] p-4 shadow-2xs card-hover cursor-grab active:cursor-grabbing text-left space-y-3 transition-all"
            >
                <!-- Card Header -->
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h4 class="text-sm font-bold text-[#0B1B2B] leading-snug font-heading">
                            ${esc(applicant.name || 'Pelamar')}
                        </h4>
                        <p class="text-xs text-[#627080] truncate max-w-[180px]">
                            ${esc(job.title || 'Posisi')}
                        </p>
                    </div>

                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-extrabold ${tone.bg} ${tone.text} border ${tone.border} num-tabular shrink-0">
                        ${score}%
                    </span>
                </div>

                <!-- Missing Skills Tags -->
                ${missing.length ? `
                    <div class="space-y-1">
                        <span class="text-[10px] uppercase font-bold text-[#627080] block">Keahlian Belum Dimiliki:</span>
                        <div class="flex flex-wrap gap-1">
                            ${missing.slice(0, 2).map((m) => `
                                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-[#FDEEEC] text-[#B42318] border border-[#B42318]/20 truncate max-w-[120px]">
                                    ${esc(m)}
                                </span>
                            `).join('')}
                            ${missing.length > 2 ? `<span class="text-[10px] text-[#627080]">+${missing.length - 2}</span>` : ''}
                        </div>
                    </div>
                ` : `
                    <div class="text-[11px] font-semibold text-[#157F3B] flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>Seluruh kualifikasi cocok</span>
                    </div>
                `}

                <!-- Card Footer & Quick Action Buttons -->
                <div class="pt-2 border-t border-[#E2E7ED] flex items-center justify-between text-xs gap-2">
                    <span class="text-[11px] text-[#627080]">${util.timeAgo(app.created_at)}</span>

                    <div class="flex items-center gap-1">
                        ${nextStageKey && !isRejected ? `
                            <button
                                type="button"
                                data-quick-advance="${app.id}"
                                data-next-stage="${nextStageKey}"
                                class="px-2 py-1 rounded bg-[#FFF3EA] hover:bg-[#ffe5d3] text-[#8A2E05] font-bold text-[11px] border border-[#C8460B]/20 transition-colors btn-press cursor-pointer flex items-center gap-0.5"
                                title="Pindahkan ke ${util.stageLabel(nextStageKey)}"
                            >
                                <span>Lanjut</span>
                                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
                            </button>
                        ` : ''}

                        ${!isRejected ? `
                            <button
                                type="button"
                                data-quick-reject="${app.id}"
                                class="p-1 rounded text-[#B42318] hover:bg-[#FDEEEC] transition-colors cursor-pointer"
                                title="Tolak Kandidat"
                            >
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;
    }

    // 7. Drag & Drop Logika Kanban
    function setupColumnDropEvents(columnEl, columnKey) {
        columnEl.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            columnEl.classList.add('bg-[#FFF3EA]/40', 'border-[#C8460B]');
        });

        columnEl.addEventListener('dragleave', () => {
            columnEl.classList.remove('bg-[#FFF3EA]/40', 'border-[#C8460B]');
        });

        columnEl.addEventListener('drop', async (e) => {
            e.preventDefault();
            columnEl.classList.remove('bg-[#FFF3EA]/40', 'border-[#C8460B]');

            const appId = Number(e.dataTransfer.getData('text/plain'));
            const sourceStage = e.dataTransfer.getData('source-stage');

            if (!appId || !sourceStage) return;
            if (sourceStage === columnKey) return; // Drop di kolom yang sama

            // Validasi aturan transisi: hanya boleh MAJU atau ke REJECTED!
            const validNext = util.nextStage(sourceStage);
            const isValid = columnKey === 'rejected' || columnKey === validNext;

            if (!isValid) {
                toast(`Tahapan seleksi hanya boleh bergerak maju atau ditolak. Tidak diizinkan mundur.`, 'warning', 3000);
                return;
            }

            await advanceCandidateStage(appId, columnKey);
        });
    }

    function attachCardClickEvents() {
        // Dragstart pada kartu
        document.querySelectorAll('[data-candidate-card]').forEach((card) => {
            card.addEventListener('dragstart', (e) => {
                const id = card.getAttribute('data-candidate-card');
                const stage = card.getAttribute('data-current-stage');
                e.dataTransfer.setData('text/plain', id);
                e.dataTransfer.setData('source-stage', stage);
                e.dataTransfer.effectAllowed = 'move';
                card.classList.add('opacity-50', 'rotate-1');
            });

            card.addEventListener('dragend', () => {
                card.classList.remove('opacity-50', 'rotate-1');
            });

            // Klik kartu membuka Drawer Detail Kandidat
            card.addEventListener('click', (e) => {
                // Hindari membuka drawer jika yang diklik tombol aksi cepat
                if (e.target.closest('button')) return;
                const appId = Number(card.getAttribute('data-candidate-card'));
                openCandidateDrawer(appId);
            });
        });

        // Tombol Quick Advance
        document.querySelectorAll('[data-quick-advance]').forEach((btn) => {
            btn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const appId = Number(btn.getAttribute('data-quick-advance'));
                const nextStg = btn.getAttribute('data-next-stage');
                await advanceCandidateStage(appId, nextStg);
            });
        });

        // Tombol Quick Reject
        document.querySelectorAll('[data-quick-reject]').forEach((btn) => {
            btn.addEventListener('click', async (e) => {
                e.stopPropagation();
                const appId = Number(btn.getAttribute('data-quick-reject'));
                const confirmed = await confirm({
                    title: 'Tolak Kandidat Ini',
                    message: 'Apakah Anda yakin ingin memindahkan kandidat ini ke tahap Ditolak (Rejected)?',
                    confirmText: 'Ya, Tolak',
                    cancelText: 'Batal',
                    danger: true,
                });
                if (confirmed) {
                    await advanceCandidateStage(appId, 'rejected');
                }
            });
        });
    }

    // 8. Kirim Pembaruan Tahap ke API (PATCH /api/pipelines/{id}/status)
    async function advanceCandidateStage(applicationId, newStage, extraPayload = {}) {
        try {
            const payload = {
                stage: newStage,
                status: newStage === 'rejected' ? 'rejected' : 'in_progress',
                ...extraPayload,
            };

            await pipelines.updateStatus(applicationId, payload);
            toast(`Kandidat berhasil dipindahkan ke tahap '${util.stageLabel(newStage)}'.`, 'success');

            // Perbarui data lokal secara instan
            const targetApp = state.applications.find((a) => Number(a.id) === Number(applicationId));
            if (targetApp) {
                targetApp.status = newStage;
                if (!targetApp.pipeline_timeline) {
                    targetApp.pipeline_timeline = { current_stage: newStage, stages: [] };
                }
                targetApp.pipeline_timeline.current_stage = newStage;
            }

            renderTopMetrics();
            renderKanbanBoard();
        } catch (err) {
            showError(err, 'Gagal memperbarui tahapan seleksi.');
        }
    }

    // 9. Drawer Detail Kandidat
    const candidateDrawer = document.getElementById('candidate-drawer');
    const closeCandidateBtn = document.getElementById('close-candidate-drawer');

    async function openCandidateDrawer(applicationId) {
        if (!candidateDrawer) return;
        candidateDrawer.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        const loadingWrap = document.getElementById('candidate-drawer-loading');
        const contentWrap = document.getElementById('candidate-drawer-content');
        if (loadingWrap) loadingWrap.classList.remove('hidden');
        if (contentWrap) contentWrap.classList.add('hidden');

        try {
            const res = await applications.show(applicationId);
            const app = res.data;
            state.activeCandidate = app;

            if (loadingWrap) loadingWrap.classList.add('hidden');
            if (contentWrap) {
                contentWrap.classList.remove('hidden');
                populateCandidateDrawer(app, contentWrap);
            }
        } catch (err) {
            showError(err, 'Gagal memuat detail kandidat.');
            closeCandidateDrawer();
        }
    }

    function populateCandidateDrawer(app, container) {
        const esc = util.escapeHtml;
        const applicant = app.applicant || {};
        const job = app.job_vacancy || {};
        const score = Math.round(app.match_score || 0);
        const tone = util.scoreTone(score);
        const currentStage = getEffectiveStage(app);

        // Header
        const nameEl = container.querySelector('#cand-name');
        const jobTitleEl = container.querySelector('#cand-job-title');
        const scoreEl = container.querySelector('#cand-score');
        const emailEl = container.querySelector('#cand-email');
        const phoneEl = container.querySelector('#cand-phone');
        const locationEl = container.querySelector('#cand-location');
        const bioEl = container.querySelector('#cand-bio');
        const coverEl = container.querySelector('#cand-cover-letter');
        const btnDownloadCv = container.querySelector('#cand-btn-download-cv');

        if (nameEl) nameEl.textContent = applicant.name || 'Nama Pelamar';
        if (jobTitleEl) jobTitleEl.textContent = `${job.title || 'Posisi'} • Dilamar ${util.formatDate(app.created_at)}`;
        if (scoreEl) {
            scoreEl.className = `text-lg font-extrabold px-3 py-1 rounded-xl ${tone.bg} ${tone.text} border ${tone.border} num-tabular font-heading`;
            scoreEl.textContent = `${score}% Cocok`;
        }
        if (emailEl) emailEl.textContent = applicant.email || '-';
        if (phoneEl) phoneEl.textContent = applicant.phone || 'Belum diisi';
        if (locationEl) locationEl.textContent = applicant.location || 'Indonesia';
        if (bioEl) bioEl.textContent = applicant.bio || 'Tidak ada ringkasan bio.';
        if (coverEl) coverEl.textContent = app.cover_letter || 'Tidak menyertakan surat lamaran khusus.';

        // Download CV
        if (btnDownloadCv) {
            if (app.cv_file_path) {
                btnDownloadCv.classList.remove('hidden');
                btnDownloadCv.onclick = async () => {
                    try {
                        await applications.downloadCv(app.id);
                    } catch (e) {
                        showError(e, 'Gagal mengunduh CV.');
                    }
                };
            } else {
                btnDownloadCv.classList.add('hidden');
            }
        }

        // Matched vs Missing Skills
        const matchedWrap = container.querySelector('#cand-matched-skills');
        const missingWrap = container.querySelector('#cand-missing-skills');

        if (matchedWrap) {
            const matched = Array.isArray(app.matched_skills) ? app.matched_skills : [];
            matchedWrap.innerHTML = matched.length ? matched.map((s) => `
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-md bg-[#E7F5EC] text-[#157F3B] border border-[#157F3B]/30">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                    ${esc(s)}
                </span>
            `).join('') : '<span class="text-xs text-[#627080]">Tidak ada kualifikasi yang cocok.</span>';
        }

        if (missingWrap) {
            const missing = Array.isArray(app.missing_skills) ? app.missing_skills : [];
            missingWrap.innerHTML = missing.length ? missing.map((s) => `
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2 py-0.5 rounded-md bg-[#FDEEEC] text-[#B42318] border border-[#B42318]/30">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    ${esc(s)}
                </span>
            `).join('') : '<span class="text-xs text-[#157F3B] font-semibold">Semua keahlian terpenuhi!</span>';
        }

        // Form Perbarui Tahap di Drawer
        const selectStage = container.querySelector('#cand-select-stage');
        const interviewScheduleWrap = container.querySelector('#cand-interview-schedule-fields');
        const notesTextarea = container.querySelector('#cand-stage-notes');
        const btnSaveStage = container.querySelector('#cand-btn-save-stage');

        if (selectStage) {
            // Hanya izinkan opsi tahap yang valid (maju atau rejected)
            let stageOptions = '';
            const nextKey = util.nextStage(currentStage);

            stageOptions += `<option value="${currentStage}" selected>Tetap di Tahap: ${util.stageLabel(currentStage)}</option>`;
            if (nextKey) {
                stageOptions += `<option value="${nextKey}">Maju ke Tahap: ${util.stageLabel(nextKey)}</option>`;
            }
            if (currentStage !== 'rejected') {
                stageOptions += `<option value="rejected">Pindahkan ke: Ditolak (Rejected)</option>`;
            }

            selectStage.innerHTML = stageOptions;

            const toggleInterviewFields = () => {
                const isInterview = selectStage.value === 'interview' || currentStage === 'interview';
                if (interviewScheduleWrap) {
                    interviewScheduleWrap.classList.toggle('hidden', !isInterview);
                }
            };

            selectStage.onchange = toggleInterviewFields;
            toggleInterviewFields();
        }

        if (btnSaveStage) {
            btnSaveStage.onclick = async () => {
                const targetStage = selectStage ? selectStage.value : currentStage;
                const notes = notesTextarea ? notesTextarea.value.trim() : null;

                const extra = { notes };

                // Sertakan jadwal wawancara jika tahap adalah interview
                if (targetStage === 'interview') {
                    const dt = container.querySelector('#cand-interview-datetime')?.value;
                    const type = container.querySelector('#cand-interview-type')?.value;
                    const url = container.querySelector('#cand-interview-url')?.value.trim();
                    const interviewer = container.querySelector('#cand-interview-interviewer')?.value.trim();

                    if (dt) {
                        extra.interview_schedule = {
                            datetime: dt,
                            type: type || 'online',
                            meeting_url: url || null,
                            interviewer: interviewer || null,
                        };
                    }
                }

                setLoading(btnSaveStage, true, 'Menyimpan...');
                await advanceCandidateStage(app.id, targetStage, extra);
                setLoading(btnSaveStage, false);
                closeCandidateDrawer();
            };
        }
    }

    function closeCandidateDrawer() {
        if (!candidateDrawer) return;
        candidateDrawer.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    closeCandidateBtn?.addEventListener('click', closeCandidateDrawer);

    // =================================================================
    // TAB 2: KELOLA LOWONGAN SAYA (CRUD LOWONGAN)
    // =================================================================
    const managedJobsList = document.getElementById('recruiter-managed-jobs-list');
    const btnCreateJob = document.getElementById('btn-open-create-job-modal');
    const jobModal = document.getElementById('modal-job-form');
    const jobForm = document.getElementById('job-form');
    const jobModalTitle = document.getElementById('job-modal-title');
    const reqRowsContainer = document.getElementById('job-requirements-rows');
    const btnAddReqRow = document.getElementById('btn-add-requirement-row');

    function renderManagedJobs() {
        if (!managedJobsList) return;
        const esc = util.escapeHtml;

        if (!state.jobs.length) {
            managedJobsList.innerHTML = `
                <div class="col-span-full p-8 text-center bg-white rounded-2xl border border-[#E2E7ED] space-y-3">
                    <p class="text-sm text-[#627080]">Anda belum memiliki lowongan pekerjaan yang diterbitkan.</p>
                    <button type="button" onclick="document.getElementById('btn-open-create-job-modal').click()" class="px-4 py-2 bg-[#C8460B] hover:bg-[#993406] text-white text-xs font-bold rounded-lg transition-colors cursor-pointer">
                        Terbitkan Lowongan Pertama
                    </button>
                </div>
            `;
            return;
        }

        managedJobsList.innerHTML = state.jobs.map((job) => {
            const isOpen = job.status === 'open';
            const count = state.applications.filter((a) => Number(a.job_vacancy_id) === Number(job.id)).length;
            const empLabel = util.EMPLOYMENT_LABELS[job.employment_type] || job.employment_type || 'Full-time';

            return `
                <div class="bg-white rounded-2xl border border-[#E2E7ED] p-5 sm:p-6 shadow-xs space-y-4 text-left">
                    <div class="flex items-start justify-between gap-3 pb-3 border-b border-[#E2E7ED]">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold ${isOpen ? 'bg-[#E7F5EC] text-[#157F3B]' : 'bg-[#FDEEEC] text-[#B42318]'}">
                                    ${isOpen ? 'Lowongan Dibuka' : 'Ditutup'}
                                </span>
                                <span class="text-xs text-[#627080]">${esc(empLabel)}</span>
                            </div>
                            <h3 class="text-base font-bold text-[#0B1B2B] font-heading leading-snug">
                                ${esc(job.title)}
                            </h3>
                            <p class="text-xs font-semibold text-[#3D4B5C] mt-0.5">
                                ${esc(job.company)} • ${esc(job.location || 'Indonesia')}
                            </p>
                        </div>

                        <div class="text-right">
                            <span class="text-2xl font-extrabold font-heading text-[#0B1B2B] block num-tabular">${count}</span>
                            <span class="text-[10px] text-[#627080] uppercase tracking-wider font-semibold block">Pelamar</span>
                        </div>
                    </div>

                    <!-- Skills requirement preview -->
                    <div class="flex flex-wrap gap-1.5 text-xs">
                        ${(job.requirements || []).map((r) => {
                            const name = util.skillName(r);
                            const lvl = r.level === 'required' ? 'Wajib' : r.level === 'important' ? 'Penting' : 'Bonus';
                            return `<span class="px-2 py-0.5 rounded bg-[#F4F6F8] text-[#3D4B5C] text-[11px] font-medium">${esc(name)} (${lvl})</span>`;
                        }).join('')}
                    </div>

                    <!-- Action buttons -->
                    <div class="pt-3 border-t border-[#E2E7ED] flex items-center justify-between text-xs">
                        <span class="text-[#627080]">Dibuat: ${util.formatDate(job.created_at)}</span>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                data-edit-job="${job.id}"
                                class="px-3 py-1.5 rounded-lg bg-[#F4F6F8] hover:bg-[#E2E7ED] text-[#0B1B2B] font-bold text-xs transition-colors cursor-pointer"
                            >
                                Edit Lowongan
                            </button>

                            <button
                                type="button"
                                data-toggle-job-status="${job.id}"
                                data-current-status="${job.status}"
                                class="px-3 py-1.5 rounded-lg ${isOpen ? 'text-[#B42318] hover:bg-[#FDEEEC]' : 'text-[#157F3B] hover:bg-[#E7F5EC]'} font-bold text-xs transition-colors cursor-pointer"
                            >
                                ${isOpen ? 'Tutup Lowongan' : 'Buka Kembali'}
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        // Pasang event listener edit & toggle status lowongan
        managedJobsList.querySelectorAll('[data-edit-job]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = Number(btn.getAttribute('data-edit-job'));
                openEditJobModal(id);
            });
        });

        managedJobsList.querySelectorAll('[data-toggle-job-status]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const id = Number(btn.getAttribute('data-toggle-job-status'));
                const cur = btn.getAttribute('data-current-status');
                if (cur === 'open') {
                    const confirmed = await confirm({
                        title: 'Tutup Lowongan Pekerjaan',
                        message: 'Apakah Anda yakin ingin menutup lowongan ini? Pelamar tidak lagi dapat mengirimkan berkas baru.',
                        confirmText: 'Tutup Lowongan',
                        cancelText: 'Batal',
                        danger: true,
                    });
                    if (confirmed) {
                        try {
                            await jobs.close(id);
                            toast('Lowongan berhasil ditutup.', 'info');
                            await loadDashboardData();
                        } catch (e) {
                            showError(e, 'Gagal menutup lowongan.');
                        }
                    }
                } else {
                    try {
                        await jobs.update(id, { status: 'open' });
                        toast('Lowongan dibuka kembali.', 'success');
                        await loadDashboardData();
                    } catch (e) {
                        showError(e, 'Gagal membuka lowongan.');
                    }
                }
            });
        });
    }

    // Modal Create / Edit Job
    btnCreateJob?.addEventListener('click', () => {
        state.editingJobId = null;
        if (jobModalTitle) jobModalTitle.textContent = 'Terbitkan Lowongan Pekerjaan Baru';
        jobForm?.reset();
        renderRequirementRows([
            { skill: '', level: 'required' },
            { skill: '', level: 'important' },
        ]);
        window.JHModal && window.JHModal.open('modal-job-form');
    });

    function openEditJobModal(jobId) {
        const job = state.jobs.find((j) => Number(j.id) === Number(jobId));
        if (!job) return;

        state.editingJobId = jobId;
        if (jobModalTitle) jobModalTitle.textContent = `Edit Lowongan: ${job.title}`;

        const f = jobForm;
        if (f) {
            f.querySelector('#job-title').value = job.title || '';
            f.querySelector('#job-company').value = job.company || '';
            f.querySelector('#job-location').value = job.location || '';
            f.querySelector('#job-type').value = job.employment_type || 'full-time';
            f.querySelector('#job-quota').value = job.quota || 1;
            f.querySelector('#job-salary-min').value = job.salary_min || '';
            f.querySelector('#job-salary-max').value = job.salary_max || '';
            f.querySelector('#job-description').value = job.description || '';
        }

        renderRequirementRows(job.requirements || [{ skill: '', level: 'required' }]);
        window.JHModal && window.JHModal.open('modal-job-form');
    }

    function renderRequirementRows(reqs = []) {
        if (!reqRowsContainer) return;
        reqRowsContainer.innerHTML = reqs.map((r, idx) => `
            <div class="flex items-center gap-2 req-row">
                <input
                    type="text"
                    name="req_skill[]"
                    value="${util.escapeHtml(r.skill || '')}"
                    placeholder="Nama keahlian (misal: Docker)"
                    required
                    class="flex-1 min-h-[40px] px-3 py-2 bg-white text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B]"
                />
                <select
                    name="req_level[]"
                    class="min-h-[40px] px-3 py-2 bg-white text-xs font-semibold rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] cursor-pointer"
                >
                    <option value="required" ${r.level === 'required' ? 'selected' : ''}>Wajib (Bobot 3x)</option>
                    <option value="important" ${r.level === 'important' ? 'selected' : ''}>Penting (Bobot 2x)</option>
                    <option value="nice_to_have" ${r.level === 'nice_to_have' ? 'selected' : ''}>Nilai Tambah (1x)</option>
                </select>
                <button
                    type="button"
                    class="p-2 text-[#B42318] hover:bg-[#FDEEEC] rounded-lg cursor-pointer btn-remove-row"
                    title="Hapus baris"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        `).join('');

        reqRowsContainer.querySelectorAll('.btn-remove-row').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                if (reqRowsContainer.querySelectorAll('.req-row').length > 1) {
                    btn.closest('.req-row').remove();
                } else {
                    toast('Minimal harus mencantumkan satu kualifikasi keahlian.', 'warning');
                }
            });
        });
    }

    btnAddReqRow?.addEventListener('click', () => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-2 req-row';
        div.innerHTML = `
            <input
                type="text"
                name="req_skill[]"
                placeholder="Nama keahlian..."
                required
                class="flex-1 min-h-[40px] px-3 py-2 bg-white text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B]"
            />
            <select
                name="req_level[]"
                class="min-h-[40px] px-3 py-2 bg-white text-xs font-semibold rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] cursor-pointer"
            >
                <option value="required">Wajib (Bobot 3x)</option>
                <option value="important" selected>Penting (Bobot 2x)</option>
                <option value="nice_to_have">Nilai Tambah (1x)</option>
            </select>
            <button
                type="button"
                class="p-2 text-[#B42318] hover:bg-[#FDEEEC] rounded-lg cursor-pointer btn-remove-row"
                title="Hapus baris"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        `;
        div.querySelector('.btn-remove-row').addEventListener('click', () => {
            if (reqRowsContainer.querySelectorAll('.req-row').length > 1) {
                div.remove();
            }
        });
        reqRowsContainer?.appendChild(div);
        div.querySelector('input').focus();
    });

    jobForm?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const btnSave = document.getElementById('btn-save-job');

        const title = document.getElementById('job-title')?.value.trim();
        const company = document.getElementById('job-company')?.value.trim();
        const location = document.getElementById('job-location')?.value.trim();
        const type = document.getElementById('job-type')?.value;
        const quota = Number(document.getElementById('job-quota')?.value) || 1;
        const salaryMin = document.getElementById('job-salary-min')?.value ? Number(document.getElementById('job-salary-min').value) : null;
        const salaryMax = document.getElementById('job-salary-max')?.value ? Number(document.getElementById('job-salary-max').value) : null;
        const description = document.getElementById('job-description')?.value.trim();

        // Kumpulkan requirements
        const skillsInputs = reqRowsContainer.querySelectorAll('input[name="req_skill[]"]');
        const levelSelects = reqRowsContainer.querySelectorAll('select[name="req_level[]"]');
        const requirements = [];

        skillsInputs.forEach((inp, idx) => {
            const skillName = inp.value.trim();
            if (skillName) {
                const lvl = levelSelects[idx] ? levelSelects[idx].value : 'required';
                requirements.push({ skill: skillName, level: lvl });
            }
        });

        if (!requirements.length) {
            toast('Kriteria keahlian wajib diisi minimal 1 keahlian.', 'warning');
            return;
        }

        const payload = {
            title,
            company,
            description,
            location,
            employment_type: type,
            quota,
            salary_min: salaryMin,
            salary_max: salaryMax,
            requirements,
        };

        setLoading(btnSave, true, 'Menyimpan Lowongan...');
        try {
            if (state.editingJobId) {
                await jobs.update(state.editingJobId, payload);
                toast('Lowongan pekerjaan berhasil diperbarui!', 'success');
            } else {
                await jobs.create(payload);
                toast('Lowongan pekerjaan baru berhasil diterbitkan!', 'success');
            }
            window.JHModal && window.JHModal.close('modal-job-form');
            setLoading(btnSave, false);
            await loadDashboardData();
        } catch (err) {
            setLoading(btnSave, false);
            showError(err, 'Gagal menyimpan data lowongan.');
        }
    });

    await loadDashboardData();
});
