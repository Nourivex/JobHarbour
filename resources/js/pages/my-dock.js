import JH, { applications, pipelines, session, util, toast, showError, confirm } from '../api.js';

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Guard Autentikasi: khusus Pelamar (admin melihat semua lamaran lewat Papan Rekrutmen)
    const currentUser = JH.requireAuth('applicant');
    if (!currentUser) return;
    if (currentUser.role === 'admin') {
        window.location.replace(JH.homeFor(currentUser));
        return;
    }

    const state = {
        applications: [],
        filterTab: 'all', // all | in_progress | completed
        selectedAppId: null,
    };

    // 2. Muat Daftar Lamaran dari API
    await fetchApplications();

    async function fetchApplications() {
        const loadingEl = document.getElementById('dock-loading');
        const emptyEl = document.getElementById('dock-empty');
        const listEl = document.getElementById('dock-applications-list');

        if (loadingEl) loadingEl.classList.remove('hidden');
        if (emptyEl) emptyEl.classList.add('hidden');

        try {
            const res = await applications.list();
            state.applications = Array.isArray(res.data) ? res.data : [];

            if (loadingEl) loadingEl.classList.add('hidden');

            renderStats(state.applications);
            renderNextAgenda(state.applications);
            applyFilterAndRender();
        } catch (err) {
            console.error('Gagal memuat lamaran saya:', err);
            if (loadingEl) loadingEl.classList.add('hidden');
            if (emptyEl) emptyEl.classList.remove('hidden');
            showError(err, 'Gagal memuat data lamaran saya.');
        }
    }

    // 3. Ringkasan Angka Statistik
    function renderStats(apps) {
        const statTotal = document.getElementById('stat-total-apps');
        const statProcess = document.getElementById('stat-in-progress');
        const statOffering = document.getElementById('stat-offering');
        const statDone = document.getElementById('stat-completed');

        const activeCount = apps.filter((a) => !['rejected', 'canceled', 'offering'].includes(a.status)).length;
        const offeringCount = apps.filter((a) => a.status === 'offering').length;
        const doneCount = apps.filter((a) => ['rejected', 'canceled'].includes(a.status)).length;

        if (statTotal) statTotal.textContent = apps.length;
        if (statProcess) statProcess.textContent = activeCount;
        if (statOffering) statOffering.textContent = offeringCount;
        if (statDone) statDone.textContent = doneCount;
    }

    // 4. Kartu Agenda Berikutnya (Wawancara Terdekat)
    function renderNextAgenda(apps) {
        const agendaCard = document.getElementById('dock-next-agenda-card');
        const agendaTitle = document.getElementById('agenda-job-title');
        const agendaDate = document.getElementById('agenda-datetime');
        const agendaInterviewer = document.getElementById('agenda-interviewer');
        const agendaMeetingLink = document.getElementById('agenda-meeting-link');

        if (!agendaCard) return;

        // Cari interview terjadwal yang akan datang dari timeline stages
        let upcomingInterview = null;
        for (const app of apps) {
            const stages = app.pipeline_timeline?.stages || [];
            for (let i = stages.length - 1; i >= 0; i--) {
                const s = stages[i];
                if (s.stage === 'interview' && s.interview_schedule) {
                    upcomingInterview = {
                        app,
                        schedule: s.interview_schedule,
                    };
                    break;
                }
            }
            if (upcomingInterview) break;
        }

        if (upcomingInterview) {
            const sch = upcomingInterview.schedule;
            agendaCard.classList.remove('hidden');
            if (agendaTitle) agendaTitle.textContent = `${upcomingInterview.app.job_vacancy?.title} - ${upcomingInterview.app.job_vacancy?.company}`;
            if (agendaDate) agendaDate.textContent = util.formatDate(sch.datetime, true);
            if (agendaInterviewer) agendaInterviewer.textContent = sch.interviewer ? `Pewawancara: ${sch.interviewer}` : 'Wawancara Tim Rekruter';

            if (agendaMeetingLink) {
                if (sch.meeting_url) {
                    agendaMeetingLink.href = sch.meeting_url;
                    agendaMeetingLink.classList.remove('hidden');
                } else {
                    agendaMeetingLink.classList.add('hidden');
                }
            }
        } else {
            agendaCard.classList.add('hidden');
        }
    }

    // 5. Filter Tab: Semua, Diproses, Selesai
    const tabAll = document.getElementById('dock-tab-all');
    const tabProcess = document.getElementById('dock-tab-process');
    const tabDone = document.getElementById('dock-tab-done');

    function setActiveTab(tab) {
        state.filterTab = tab;
        [tabAll, tabProcess, tabDone].forEach((t) => {
            t?.classList.remove('border-[#C8460B]', 'text-[#C8460B]');
            t?.classList.add('border-transparent', 'text-[#627080]');
        });

        if (tab === 'in_progress') {
            tabProcess?.classList.add('border-[#C8460B]', 'text-[#C8460B]');
            tabProcess?.classList.remove('border-transparent', 'text-[#627080]');
        } else if (tab === 'completed') {
            tabDone?.classList.add('border-[#C8460B]', 'text-[#C8460B]');
            tabDone?.classList.remove('border-transparent', 'text-[#627080]');
        } else {
            tabAll?.classList.add('border-[#C8460B]', 'text-[#C8460B]');
            tabAll?.classList.remove('border-transparent', 'text-[#627080]');
        }

        applyFilterAndRender();
    }

    tabAll?.addEventListener('click', () => setActiveTab('all'));
    tabProcess?.addEventListener('click', () => setActiveTab('in_progress'));
    tabDone?.addEventListener('click', () => setActiveTab('completed'));

    function applyFilterAndRender() {
        const listEl = document.getElementById('dock-applications-list');
        const emptyEl = document.getElementById('dock-empty');
        if (!listEl) return;

        let filtered = state.applications;
        if (state.filterTab === 'in_progress') {
            filtered = state.applications.filter((a) => !['rejected', 'canceled'].includes(a.status));
        } else if (state.filterTab === 'completed') {
            filtered = state.applications.filter((a) => ['rejected', 'canceled', 'offering'].includes(a.status));
        }

        if (!filtered.length) {
            listEl.innerHTML = '';
            if (emptyEl) emptyEl.classList.remove('hidden');
            return;
        }

        if (emptyEl) emptyEl.classList.add('hidden');
        listEl.innerHTML = filtered.map((app) => renderApplicationCard(app)).join('');

        // Pasang event listener aksi pada kartu
        attachCardListeners(listEl);
    }

    // 6. Template Kartu Lamaran
    function renderApplicationCard(app) {
        const esc = util.escapeHtml;
        const job = app.job_vacancy || {};
        const score = Math.round(app.match_score || 0);
        const tone = util.scoreTone(score);
        const currentStage = (app.pipeline_timeline?.current_stage || app.status || 'screening').toLowerCase();
        const canCancel = ['submitted', 'screening'].includes(app.status);

        // Stage label badge
        let stageBadgeHtml = '';
        if (app.status === 'canceled') {
            stageBadgeHtml = `<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#F4F6F8] text-[#627080] border border-[#C5CFDA]">Dibatalkan</span>`;
        } else if (app.status === 'rejected' || currentStage === 'rejected') {
            stageBadgeHtml = `<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FDEEEC] text-[#B42318] border border-[#B42318]/20">Tidak Lolos Seleksi</span>`;
        } else if (currentStage === 'offering' || app.status === 'offering') {
            stageBadgeHtml = `<span class="px-2.5 py-1 rounded-full text-xs font-bold bg-[#E7F5EC] text-[#157F3B] border border-[#157F3B]/30">Tahap Penawaran (Offering)</span>`;
        } else {
            stageBadgeHtml = `<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#FFF3EA] text-[#8A2E05] border border-[#C8460B]/20">Tahap: ${util.stageLabel(currentStage)}</span>`;
        }

        // Stepper Visual 4 Tahap
        const stepperHtml = createStepperHtml(currentStage, app.status);

        return `
            <div class="bg-white rounded-2xl border border-[#E2E7ED] p-5 sm:p-6 shadow-xs space-y-4 text-left">
                <!-- Header Kartu -->
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-3 border-b border-[#E2E7ED]">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            ${stageBadgeHtml}
                            <span class="text-xs text-[#627080]">Diajukan: ${util.formatDate(app.created_at)}</span>
                        </div>
                        <h3 class="text-lg font-bold text-[#0B1B2B] font-heading leading-snug">
                            ${esc(job.title || 'Posisi Pekerjaan')}
                        </h3>
                        <p class="text-sm font-semibold text-[#3D4B5C]">
                            ${esc(job.company || 'Nama Perusahaan')} • <span class="font-normal text-[#627080]">${esc(job.location || 'Indonesia')}</span>
                        </p>
                    </div>

                    <!-- Skor Matchmaking Badge -->
                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <div class="px-3 py-1.5 rounded-xl border ${tone.border} ${tone.bg} text-right">
                            <span class="text-[10px] uppercase font-bold text-[#627080] block">Skor Kecocokan</span>
                            <span class="text-lg font-extrabold font-heading ${tone.text} num-tabular">${score}%</span>
                        </div>
                    </div>
                </div>

                <!-- Progres Stepper 4 Tahap -->
                ${stepperHtml}

                <!-- Aksi Tombol -->
                <div class="pt-3 border-t border-[#E2E7ED] flex flex-wrap items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            data-view-timeline="${app.id}"
                            class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-[#0C2238] hover:bg-[#133150] transition-colors btn-press flex items-center gap-1.5 cursor-pointer"
                        >
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            <span>Pantau Linimasa</span>
                        </button>

                        ${app.cv_file_path ? `
                            <button
                                type="button"
                                data-download-cv="${app.id}"
                                class="px-3 py-2 rounded-lg text-xs font-semibold text-[#3D4B5C] bg-[#F4F6F8] hover:bg-[#E2E7ED] transition-colors flex items-center gap-1.5 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5 text-[#1B65A6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                <span>Unduh CV</span>
                            </button>
                        ` : ''}
                    </div>

                    ${canCancel ? `
                        <button
                            type="button"
                            data-cancel-app="${app.id}"
                            data-job-name="${esc(job.title || 'posisi ini')}"
                            class="px-3 py-2 rounded-lg text-xs font-semibold text-[#B42318] hover:bg-[#FDEEEC] transition-colors cursor-pointer"
                        >
                            Batalkan Lamaran
                        </button>
                    ` : ''}
                </div>
            </div>
        `;
    }

    function createStepperHtml(currentStage, status) {
        const stages = [
            { key: 'screening', label: 'Screening' },
            { key: 'assessment', label: 'Assessment' },
            { key: 'interview', label: 'Wawancara' },
            { key: 'offering', label: 'Penawaran' },
        ];

        const isCanceled = status === 'canceled';
        const isRejected = status === 'rejected' || currentStage === 'rejected';

        if (isCanceled) {
            return `<div class="p-2.5 rounded-lg bg-[#F4F6F8] text-xs text-[#627080]">Lamaran ini telah dibatalkan oleh pelamar.</div>`;
        }

        const stageIndex = stages.findIndex((s) => s.key === currentStage);

        return `
            <div class="py-2">
                <div class="flex items-center justify-between relative">
                    <div class="absolute top-1/2 left-4 right-4 -translate-y-1/2 h-0.5 bg-[#E2E7ED] z-0"></div>
                    ${stages.map((stg, idx) => {
                        const isPassed = !isRejected && (idx < stageIndex || (idx === stageIndex && status === 'passed'));
                        const isCurrent = !isRejected && (idx === stageIndex && status !== 'passed');
                        
                        let dotCls = 'bg-white text-[#627080] border-2 border-[#E2E7ED]';
                        let textCls = 'text-[#627080]';

                        if (isPassed) {
                            dotCls = 'bg-[#157F3B] text-white shadow-xs';
                            textCls = 'text-[#157F3B] font-semibold';
                        } else if (isCurrent) {
                            dotCls = 'bg-[#C8460B] text-white ring-4 ring-[#FFF3EA] shadow-xs';
                            textCls = 'text-[#C8460B] font-bold';
                        }

                        return `
                            <div class="flex flex-col items-center gap-1 relative z-10">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold ${dotCls}">
                                    ${isPassed ? '<svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>' : (idx + 1)}
                                </div>
                                <span class="text-[11px] ${textCls}">${stg.label}</span>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    }

    // 7. Event Listener Aksi Kartu
    function attachCardListeners(container) {
        // Pantau Linimasa Drawer
        container.querySelectorAll('[data-view-timeline]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const appId = Number(btn.getAttribute('data-view-timeline'));
                openTimelineDrawer(appId);
            });
        });

        // Unduh CV
        container.querySelectorAll('[data-download-cv]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const appId = Number(btn.getAttribute('data-download-cv'));
                try {
                    await applications.downloadCv(appId);
                } catch (err) {
                    showError(err, 'Gagal mengunduh berkas CV.');
                }
            });
        });

        // Batalkan Lamaran (dengan dialog konfirmasi custom)
        container.querySelectorAll('[data-cancel-app]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const appId = Number(btn.getAttribute('data-cancel-app'));
                const jobName = btn.getAttribute('data-job-name') || 'lowongan ini';

                const confirmed = await confirm({
                    title: 'Batalkan Pengajuan Lamaran',
                    message: `Apakah Anda yakin ingin membatalkan berkas lamaran untuk ${jobName}? Tindakan ini tidak dapat diurungkan.`,
                    confirmText: 'Ya, Batalkan Lamaran',
                    cancelText: 'Kembali',
                    danger: true,
                });

                if (confirmed) {
                    try {
                        await applications.cancel(appId);
                        toast('Pengajuan lamaran berhasil dibatalkan.', 'success');
                        await fetchApplications();
                    } catch (err) {
                        showError(err, 'Gagal membatalkan lamaran.');
                    }
                }
            });
        });
    }

    // 8. Drawer Linimasa Rinci
    const timelineDrawer = document.getElementById('timeline-drawer');
    const closeDrawerBtn = document.getElementById('close-timeline-drawer');

    async function openTimelineDrawer(appId) {
        if (!timelineDrawer) return;
        state.selectedAppId = appId;
        timelineDrawer.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        const contentWrap = document.getElementById('timeline-drawer-content');
        const loadingWrap = document.getElementById('timeline-drawer-loading');

        if (loadingWrap) loadingWrap.classList.remove('hidden');
        if (contentWrap) contentWrap.classList.add('hidden');

        try {
            const res = await pipelines.timeline(appId);
            const data = res.data;

            if (loadingWrap) loadingWrap.classList.add('hidden');
            if (contentWrap) {
                contentWrap.classList.remove('hidden');
                renderTimelineDetails(data, contentWrap);
            }
        } catch (err) {
            showError(err, 'Gagal memuat rincian linimasa.');
            closeTimelineDrawer();
        }
    }

    function renderTimelineDetails(data, container) {
        const esc = util.escapeHtml;
        const jobTitleEl = container.querySelector('#timeline-job-title');
        const jobCompanyEl = container.querySelector('#timeline-job-company');
        const daysEl = container.querySelector('#timeline-days-active');
        const stagesListEl = container.querySelector('#timeline-stages-list');

        if (jobTitleEl) jobTitleEl.textContent = data.job?.title || 'Posisi';
        if (jobCompanyEl) jobCompanyEl.textContent = `${data.job?.company || ''} • ${data.job?.location || ''}`;
        if (daysEl) daysEl.textContent = `${data.timeline_summary?.days_active || 1} Hari Berjalan`;

        const stages = data.stages || [];
        if (stagesListEl) {
            stagesListEl.innerHTML = stages.map((stg, idx) => {
                const isPassed = stg.status === 'passed';
                const isInProgress = stg.status === 'in_progress';
                const isRejected = stg.stage === 'rejected' || stg.status === 'rejected';

                let dotColor = 'bg-[#157F3B]';
                if (isInProgress) dotColor = 'bg-[#C8460B] ring-4 ring-[#FFF3EA]';
                if (isRejected) dotColor = 'bg-[#B42318]';

                return `
                    <div class="relative pl-6 pb-6 last:pb-0">
                        <!-- Vertical line -->
                        ${idx < stages.length - 1 ? '<div class="absolute left-2.5 top-3 bottom-0 w-0.5 bg-[#E2E7ED]"></div>' : ''}
                        
                        <!-- Dot -->
                        <div class="absolute left-1 top-1.5 w-3.5 h-3.5 rounded-full ${dotColor}"></div>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-[#0B1B2B] font-heading text-sm uppercase tracking-wide">
                                    Tahap: ${util.stageLabel(stg.stage)}
                                </span>
                                <span class="text-[#627080] text-[11px]">${util.formatDate(stg.entered_at, true)}</span>
                            </div>

                            <p class="text-xs text-[#3D4B5C] leading-relaxed">
                                ${esc(stg.notes || 'Dalam proses peninjauan berkas.')}
                            </p>

                            ${stg.interview_schedule ? `
                                <div class="mt-2 p-3 rounded-xl bg-[#EBF3FB] border border-[#1B65A6]/30 text-xs text-[#0B1B2B] space-y-1">
                                    <div class="font-bold text-[#1B65A6] flex items-center gap-1.5">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                        <span>Jadwal Wawancara Resmi</span>
                                    </div>
                                    <div><strong>Waktu:</strong> ${util.formatDate(stg.interview_schedule.datetime, true)}</div>
                                    <div><strong>Tipe:</strong> ${stg.interview_schedule.type === 'online' ? 'Online (Daring)' : 'Offline (Tatap Muka)'}</div>
                                    ${stg.interview_schedule.interviewer ? `<div><strong>Pewawancara:</strong> ${esc(stg.interview_schedule.interviewer)}</div>` : ''}
                                    ${stg.interview_schedule.meeting_url ? `
                                        <div class="pt-1.5">
                                            <a href="${esc(stg.interview_schedule.meeting_url)}" target="_blank" class="inline-flex items-center gap-1 font-bold text-[#1B65A6] hover:underline">
                                                <span>Buka Tautan Pertemuan</span>
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                            </a>
                                        </div>
                                    ` : ''}
                                </div>
                            ` : ''}
                        </div>
                    </div>
                `;
            }).join('');
        }
    }

    function closeTimelineDrawer() {
        if (!timelineDrawer) return;
        timelineDrawer.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    closeDrawerBtn?.addEventListener('click', closeTimelineDrawer);
});
