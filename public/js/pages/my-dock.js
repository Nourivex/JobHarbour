/**
 * JobHarbour - My Dock (Dashboard Pelamar & Linimasa Seleksi)
 *
 * Menghubungkan halaman /my-dock ke endpoint:
 * - GET /api/auth/me                  -> sapaan nama user & info profil pelamar
 * - GET /api/applications             -> daftar lamaran + statistik ringkas + skor match
 * - GET /api/pipelines/{applicationId}-> detail linimasa seleksi 4 tahap + jadwal interview
 * - DELETE /api/applications/{id}     -> batalkan lamaran (status submitted / screening)
 * - GET /api/applications/{id}/cv     -> lihat/unduh berkas CV PDF
 */
(function () {
    'use strict';

    // Guard autentikasi: wajib login sebagai applicant (atau admin)
    const sessionUser = JH.requireAuth('applicant');
    if (!sessionUser) return;

    const state = {
        user: sessionUser,
        applications: [],
        filteredApplications: [],
        selectedAppId: null,
        selectedTimeline: null,
        activeStatusFilter: 'all',
        searchQuery: '',
        loadingTimeline: false,
    };

    const STAGE_SEQUENCE = [
        {
            key: 'screening',
            title: 'Screening Berkas & Verifikasi CV',
            subtitle: 'Pemeriksaan kelengkapan dokumen & pemadanan keahlian awal',
            icon: 'fact_check',
        },
        {
            key: 'assessment',
            title: 'Review Teknis & Tes Kompetensi (Assessment)',
            subtitle: 'Evaluasi kemampuan teknis sesuai kebutuhan posisi',
            icon: 'code_blocks',
        },
        {
            key: 'interview',
            title: 'Wawancara Tim Teknis & HR (Interview)',
            subtitle: 'Sesi wawancara mendalam bersama tim rekrutmen',
            icon: 'video_call',
        },
        {
            key: 'offering',
            title: 'Penawaran Kerja & Keputusan Akhir (Offering)',
            subtitle: 'Tahap penawaran kontrak kerja dan konfirmasi bergabung',
            icon: 'verified',
        },
    ];

    function categoryLabel(category, score) {
        const cat = String(category || '').toLowerCase();
        if (cat === 'excellent' || Number(score) >= 80) return 'Sangat Cocok';
        if (cat === 'good' || Number(score) >= 50) return 'Kompeten';
        return 'Perlu Peningkatan';
    }

    function effectiveStage(app) {
        const timelineStage = app && app.pipeline_timeline && app.pipeline_timeline.current_stage;
        const raw = timelineStage || (app && app.status) || 'screening';
        if (raw === 'submitted') return 'screening';
        return raw;
    }

    function stageBadgeMeta(stage, appStatus) {
        const st = appStatus === 'canceled' ? 'canceled' : (appStatus === 'rejected' ? 'rejected' : stage);
        switch (st) {
            case 'screening':
                return {
                    label: 'Tahap 1: Screening Berkas',
                    icon: 'hourglass_top',
                    cls: 'bg-surface-container text-on-surface-variant border-outline-variant/60',
                };
            case 'assessment':
                return {
                    label: 'Tahap 2: Assessment Teknis',
                    icon: 'code_blocks',
                    cls: 'bg-secondary-fixed/50 text-on-secondary-fixed-variant border-secondary/30',
                };
            case 'interview':
                return {
                    label: 'Tahap 3: Wawancara (Interview)',
                    icon: 'event',
                    cls: 'bg-tertiary-fixed text-on-tertiary-fixed-variant border-tertiary-fixed-dim/50',
                };
            case 'offering':
                return {
                    label: 'Tahap 4: Penawaran Kerja (Offering)',
                    icon: 'workspace_premium',
                    cls: 'bg-emerald-100 text-emerald-900 border-emerald-300',
                };
            case 'rejected':
                return {
                    label: 'Tidak Lolos Seleksi (Rejected)',
                    icon: 'block',
                    cls: 'bg-error-container text-on-error-container border-error/30',
                };
            case 'canceled':
                return {
                    label: 'Lamaran Dibatalkan',
                    icon: 'cancel',
                    cls: 'bg-surface-container-high text-on-surface-variant border-outline-variant',
                };
            default:
                return {
                    label: 'Tahap: ' + JH.util.stageLabel(st),
                    icon: 'flag',
                    cls: 'bg-surface-container text-on-surface-variant border-outline-variant/60',
                };
        }
    }

    function canCancelApplication(app) {
        if (!app) return false;
        return app.status === 'submitted' || app.status === 'screening';
    }

    // -----------------------------------------------------------------
    // 1. Sapaan & Profil Pelamar dari JH.auth.me()
    // -----------------------------------------------------------------
    function renderUserGreeting(user) {
        if (!user) return;
        JH.bindSessionUI(document);

        const pillName = document.getElementById('dock-user-pill-name');
        const pillSub = document.getElementById('dock-user-pill-sub');
        const greetingMeta = document.getElementById('dock-greeting-meta');
        const greetingTitle = document.getElementById('dock-greeting-title');
        const greetingSubtitle = document.getElementById('dock-greeting-subtitle');

        const skillCount = Array.isArray(user.skills) ? user.skills.length : 0;
        const loc = user.location || 'Indonesia';

        if (pillName) pillName.textContent = user.name + ' - Pelamar Aktif';
        if (pillSub) pillSub.textContent = 'ID #' + String(user.id).padStart(4, '0') + ' • ' + skillCount + ' KEAHLIAN';
        if (greetingMeta) {
            greetingMeta.textContent = (user.name || 'PELAMAR').toUpperCase() + ' (' + loc.toUpperCase() + ' • ' + skillCount + ' SKILL TERDAFTAR)';
        }
        if (greetingTitle) {
            greetingTitle.textContent = 'Halo, ' + user.name + ' - Dermaga Lamaran & Linimasa Seleksi';
        }
        if (greetingSubtitle && user.bio) {
            greetingSubtitle.textContent = user.bio;
        }
    }

    async function loadUserProfile() {
        renderUserGreeting(state.user);
        try {
            const freshUser = await JH.auth.me();
            if (freshUser) {
                state.user = freshUser;
                renderUserGreeting(freshUser);
            }
        } catch (err) {
            // Gunakan data sesi lokal jika endpoint me() gagal sementara
            console.warn('Gagal menyegarkan profil:', err);
        }
    }

    // -----------------------------------------------------------------
    // 2. Statistik Ringkas Dinamis
    // -----------------------------------------------------------------
    function renderStats() {
        const apps = state.applications || [];
        const total = apps.length;

        const activeApps = apps.filter((a) => a.status !== 'canceled');
        const screeningAssessmentCount = apps.filter((a) => {
            const st = effectiveStage(a);
            return a.status !== 'canceled' && a.status !== 'rejected' && (st === 'screening' || st === 'assessment');
        }).length;

        const interviewApps = apps.filter((a) => {
            const st = effectiveStage(a);
            return a.status !== 'canceled' && a.status !== 'rejected' && st === 'interview';
        });

        const offeringCount = apps.filter((a) => {
            const st = effectiveStage(a);
            return a.status === 'offering' || st === 'offering';
        }).length;

        const rejectedOrCanceledCount = apps.filter((a) => {
            const st = effectiveStage(a);
            return a.status === 'rejected' || a.status === 'canceled' || st === 'rejected';
        }).length;

        const scores = apps
            .map((a) => Number(a.match_score))
            .filter((n) => !isNaN(n) && n >= 0);
        const avgScore = scores.length
            ? Math.round((scores.reduce((acc, v) => acc + v, 0) / scores.length) * 10) / 10
            : 0;

        // Banner status
        const activeExpEl = document.getElementById('dock-active-expeditions');
        if (activeExpEl) {
            activeExpEl.textContent = 'STATUS: ' + activeApps.length + ' EKSPEDISI AKTIF';
        }

        // Card 1: Total
        const elTotal = document.getElementById('stat-total-count');
        const elTotalBadge = document.getElementById('stat-total-badge');
        const elTotalSub = document.getElementById('stat-total-sub');
        if (elTotal) elTotal.textContent = String(total);
        if (elTotalBadge) elTotalBadge.textContent = activeApps.length + ' Armada Aktif';
        if (elTotalSub) elTotalSub.textContent = (total - activeApps.length) + ' Dibatalkan';

        // Card 2: Screening / Assessment
        const elScreen = document.getElementById('stat-screening-count');
        const elScreenBadge = document.getElementById('stat-screening-badge');
        const elScreenSub = document.getElementById('stat-screening-sub');
        const pctQueue = total > 0 ? Math.round((screeningAssessmentCount / total) * 100) : 0;
        if (elScreen) elScreen.textContent = String(screeningAssessmentCount);
        if (elScreenBadge) {
            elScreenBadge.textContent = screeningAssessmentCount > 0 ? 'Verifikasi Berjalan' : 'Tidak Ada Antrean';
        }
        if (elScreenSub) elScreenSub.textContent = pctQueue + '% dari Total';

        // Card 3: Interview
        const elInterview = document.getElementById('stat-interview-count');
        const elInterviewBadge = document.getElementById('stat-interview-badge');
        const elInterviewSub = document.getElementById('stat-interview-sub');
        if (elInterview) elInterview.textContent = String(interviewApps.length);

        let nextInterviewDate = null;
        interviewApps.forEach((app) => {
            const stages = (app.pipeline_timeline && app.pipeline_timeline.stages) || [];
            stages.forEach((s) => {
                if (s.stage === 'interview' && s.interview_schedule && s.interview_schedule.datetime) {
                    nextInterviewDate = s.interview_schedule.datetime;
                }
            });
        });

        if (elInterviewBadge) {
            if (nextInterviewDate) {
                elInterviewBadge.innerHTML =
                    '<span class="w-1.5 h-1.5 rounded-full bg-error animate-ping"></span>' +
                    '<span>' + JH.util.escapeHtml(JH.util.formatDate(nextInterviewDate, true)) + '</span>';
            } else if (interviewApps.length > 0) {
                elInterviewBadge.textContent = interviewApps.length + ' Tahap Wawancara';
            } else {
                elInterviewBadge.textContent = 'Belum Terjadwal';
            }
        }
        if (elInterviewSub) {
            elInterviewSub.textContent = interviewApps.length > 0 ? 'Prioritas' : 'Standby';
        }

        // Card 4: Offering & Ditolak
        const elOffering = document.getElementById('stat-offering-count');
        const elOfferingBadge = document.getElementById('stat-offering-badge');
        const elRejectedSub = document.getElementById('stat-rejected-sub');
        if (elOffering) elOffering.textContent = String(offeringCount);
        if (elOfferingBadge) elOfferingBadge.textContent = offeringCount + ' Offering';
        if (elRejectedSub) elRejectedSub.textContent = rejectedOrCanceledCount + ' Ditolak/Batal';

        // Card 5: Rata-rata Skor Match
        const elAvg = document.getElementById('stat-avg-score');
        const elAvgSub = document.getElementById('stat-avg-sub');
        if (elAvg) elAvg.textContent = avgScore + '%';
        if (elAvgSub) elAvgSub.textContent = total > 0 ? categoryLabel(null, avgScore) : 'Belum Ada Data';
    }

    // -----------------------------------------------------------------
    // 3. Filter & Render Daftar Lamaran
    // -----------------------------------------------------------------
    function applyFilters() {
        const statusFilter = state.activeStatusFilter;
        const q = state.searchQuery.trim().toLowerCase();

        state.filteredApplications = (state.applications || []).filter((app) => {
            const st = effectiveStage(app);
            const rawStatus = app.status || st;

            if (statusFilter !== 'all') {
                if (statusFilter === 'rejected') {
                    if (rawStatus !== 'rejected' && rawStatus !== 'canceled' && st !== 'rejected') return false;
                } else if (statusFilter === 'screening') {
                    if (rawStatus === 'canceled' || rawStatus === 'rejected') return false;
                    if (st !== 'screening' && rawStatus !== 'submitted') return false;
                } else {
                    if (rawStatus === 'canceled' && statusFilter !== 'canceled') return false;
                    if (st !== statusFilter && rawStatus !== statusFilter) return false;
                }
            }

            if (q) {
                const job = app.job_vacancy || {};
                const hay = [
                    job.title || '',
                    job.company || '',
                    job.location || '',
                    st,
                    rawStatus,
                ].join(' ').toLowerCase();
                if (!hay.includes(q)) return false;
            }

            return true;
        });

        renderApplicationsList();
    }

    function updateFilterControlsUI() {
        const select = document.getElementById('filter-status-select');
        if (select && select.value !== state.activeStatusFilter) {
            select.value = state.activeStatusFilter;
        }
        document.querySelectorAll('[data-status-tab]').forEach((btn) => {
            const tab = btn.getAttribute('data-status-tab');
            if (tab === state.activeStatusFilter) {
                btn.className = 'px-3 py-1.5 rounded-lg text-label-sm font-label-sm font-semibold bg-secondary text-on-secondary transition-colors shrink-0';
            } else {
                btn.className = 'px-3 py-1.5 rounded-lg text-label-sm font-label-sm font-medium bg-surface-container text-on-surface-variant hover:text-on-surface transition-colors shrink-0';
            }
        });
    }

    function renderApplicationsList() {
        const container = document.getElementById('applications-list-container');
        const countPill = document.getElementById('apps-count-pill');
        const footerCount = document.getElementById('applications-footer-count');
        if (!container) return;

        const total = state.applications.length;
        const list = state.filteredApplications;

        if (countPill) countPill.textContent = list.length + ' Ekspedisi';
        if (footerCount) {
            footerCount.textContent = 'MENAMPILKAN ' + list.length + ' DARI ' + total + ' BERKAS LAMARAN';
        }

        // Empty state ramah jika belum ada lamaran sama sekali
        if (total === 0) {
            container.innerHTML =
                '<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/60 p-space-2xl text-center space-y-3 shadow-sm">' +
                    '<div class="w-14 h-14 rounded-full bg-secondary-fixed text-on-secondary-fixed-variant flex items-center justify-center mx-auto">' +
                        '<span class="material-symbols-outlined text-[28px]">sailing</span>' +
                    '</div>' +
                    '<h3 class="text-headline-sm font-headline-sm font-bold text-on-surface">Dermaga Masih Kosong</h3>' +
                    '<p class="text-body-sm font-body-sm text-on-surface-variant max-w-md mx-auto">' +
                        'Kamu belum mengirimkan berkas lamaran kerja. Yuk jelajahi lowongan terbuka di The Anchorage dan uji kecocokan keahlianmu!' +
                    '</p>' +
                    '<div class="pt-2">' +
                        '<a href="/catalog" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-secondary text-on-secondary text-label-md font-label-md font-semibold shadow-sm hover:bg-on-secondary-fixed-variant transition-colors">' +
                            '<span class="material-symbols-outlined text-[18px]">explore</span>' +
                            '<span>Jelajahi Lowongan di The Anchorage</span>' +
                        '</a>' +
                    '</div>' +
                '</div>';
            return;
        }

        // Empty state jika filter tidak menemukan hasil
        if (list.length === 0) {
            container.innerHTML =
                '<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/60 p-space-xl text-center space-y-2">' +
                    '<span class="material-symbols-outlined text-[28px] text-outline">filter_alt_off</span>' +
                    '<p class="text-body-md font-semibold text-on-surface">Tidak ada lamaran yang cocok dengan filter ini.</p>' +
                    '<button type="button" id="btn-reset-dock-filter" class="mt-1 px-3.5 py-1.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-label-sm font-label-sm font-semibold text-secondary">' +
                        'Reset Filter' +
                    '</button>' +
                '</div>';
            const resetBtn = document.getElementById('btn-reset-dock-filter');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => {
                    state.activeStatusFilter = 'all';
                    state.searchQuery = '';
                    const searchInput = document.getElementById('filter-search-input');
                    if (searchInput) searchInput.value = '';
                    updateFilterControlsUI();
                    applyFilters();
                });
            }
            return;
        }

        container.innerHTML = list.map((app) => {
            const isSelected = Number(app.id) === Number(state.selectedAppId);
            const job = app.job_vacancy || {};
            const title = job.title || 'Lowongan Pekerjaan #' + app.job_vacancy_id;
            const company = job.company || 'Perusahaan Mitra JobHarbour';
            const location = job.location || 'Indonesia';
            const score = Math.round((Number(app.match_score) || 0) * 10) / 10;
            const scoreWidth = Math.max(0, Math.min(100, Math.round(score)));
            const catText = categoryLabel(app.match_category, score);
            const stage = effectiveStage(app);
            const badge = stageBadgeMeta(stage, app.status);
            const appliedDate = JH.util.formatDate(app.created_at);
            const appliedAgo = JH.util.timeAgo(app.created_at);
            const berthCode = 'BERTH #' + String(app.id).padStart(2, '0');
            const hasCv = Boolean(app.cv_file_path);
            const cvFileName = hasCv ? String(app.cv_file_path).split('/').pop() : '';
            const cancellable = canCancelApplication(app);

            const cardBorder = isSelected
                ? 'border-2 border-secondary shadow-md'
                : 'border border-outline-variant/60 shadow-sm hover:border-outline';

            return (
                '<article data-app-card="' + app.id + '" class="bg-surface-container-lowest rounded-xl ' + cardBorder + ' p-space-lg relative overflow-hidden transition-all duration-150 cursor-pointer">' +
                    (isSelected ? '<div class="absolute left-0 top-0 bottom-0 w-1.5 bg-secondary"></div>' : '') +
                    '<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 ' + (isSelected ? 'pl-2' : '') + '">' +
                        '<div class="space-y-1">' +
                            '<div class="flex items-center gap-2 flex-wrap">' +
                                '<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag uppercase tracking-wider ' +
                                    (isSelected ? 'bg-secondary-fixed text-on-secondary-fixed-variant font-bold' : 'bg-surface-container text-on-surface-variant font-semibold') + '">' +
                                    JH.util.escapeHtml(berthCode + (isSelected ? ' • DIPILIH' : '')) +
                                '</span>' +
                                '<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant">' +
                                    'MELAMAR ' + JH.util.escapeHtml(appliedDate.toUpperCase()) + ' (' + JH.util.escapeHtml(appliedAgo) + ')' +
                                '</span>' +
                            '</div>' +
                            '<h3 class="text-headline-sm font-headline-sm font-bold text-on-surface">' +
                                JH.util.escapeHtml(title) +
                            '</h3>' +
                            '<p class="text-body-sm font-body-sm text-on-surface-variant flex items-center gap-2 flex-wrap">' +
                                '<span class="font-medium text-on-surface">' + JH.util.escapeHtml(company) + '</span>' +
                                '<span>•</span>' +
                                '<span>' + JH.util.escapeHtml(location) + '</span>' +
                            '</p>' +
                        '</div>' +
                        '<div class="flex sm:flex-col items-center sm:items-end justify-between gap-1 bg-surface-container-low sm:bg-transparent p-2 sm:p-0 rounded-lg shrink-0">' +
                            '<div class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-secondary-fixed text-on-secondary-fixed-variant font-bold text-label-sm border border-secondary/20">' +
                                '<span class="material-symbols-outlined text-[15px]" data-weight="fill">anchor</span>' +
                                '<span>' + JH.util.escapeHtml(score + '% Match') + '</span>' +
                            '</div>' +
                            '<span class="text-coordinate-tag font-coordinate-tag text-secondary font-bold text-[10px] uppercase tracking-wider">' +
                                JH.util.escapeHtml(catText) +
                            '</span>' +
                        '</div>' +
                    '</div>' +
                    '<div class="my-3 ' + (isSelected ? 'pl-2' : '') + '">' +
                        '<div class="w-full bg-surface-container rounded-full h-1.5 overflow-hidden flex">' +
                            '<div class="bg-secondary h-full transition-all" style="width:' + scoreWidth + '%"></div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="pt-3 border-t border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 ' + (isSelected ? 'pl-2' : '') + '">' +
                        '<div class="flex items-center gap-2 flex-wrap">' +
                            '<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-label-sm font-label-sm font-semibold border ' + badge.cls + '">' +
                                '<span class="material-symbols-outlined text-[15px]">' + badge.icon + '</span>' +
                                '<span>' + JH.util.escapeHtml(badge.label) + '</span>' +
                            '</div>' +
                            (hasCv
                                ? '<button type="button" data-download-cv="' + app.id + '" class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-surface-container hover:bg-surface-container-high text-on-surface text-coordinate-tag font-coordinate-tag border border-outline-variant/60 transition-colors" title="Unduh / Lihat CV PDF">' +
                                    '<span class="material-symbols-outlined text-[14px] text-error">picture_as_pdf</span>' +
                                    '<span>' + JH.util.escapeHtml(cvFileName || 'Unduh CV.pdf') + '</span>' +
                                    '<span class="material-symbols-outlined text-[13px] text-secondary ml-0.5">download</span>' +
                                  '</button>'
                                : '<span class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-surface-container/60 text-on-surface-variant text-coordinate-tag font-coordinate-tag">' +
                                    '<span class="material-symbols-outlined text-[14px]">description</span>' +
                                    '<span>Tanpa Lampiran CV</span>' +
                                  '</span>'
                            ) +
                        '</div>' +
                        '<div class="flex items-center gap-2 self-end sm:self-auto">' +
                            (cancellable
                                ? '<button type="button" data-cancel-app="' + app.id + '" class="px-3 py-1.5 rounded-lg text-error hover:bg-error-container/70 border border-error/30 text-label-sm font-label-sm font-semibold flex items-center gap-1 transition-colors">' +
                                    '<span class="material-symbols-outlined text-[15px]">cancel</span>' +
                                    '<span>Batalkan</span>' +
                                  '</button>'
                                : ''
                            ) +
                            '<button type="button" data-select-app="' + app.id + '" class="px-3.5 py-1.5 rounded-lg ' +
                                (isSelected
                                    ? 'bg-secondary text-on-secondary hover:bg-on-secondary-fixed-variant'
                                    : 'border border-outline-variant text-on-surface hover:bg-surface-container') +
                                ' text-label-sm font-label-sm font-semibold flex items-center gap-1.5 transition-colors">' +
                                '<span>' + (isSelected ? 'Linimasa Terpilih' : 'Lihat Linimasa') + '</span>' +
                                '<span class="material-symbols-outlined text-[16px]">' + (isSelected ? 'arrow_forward' : 'visibility') + '</span>' +
                            '</button>' +
                        '</div>' +
                    '</div>' +
                '</article>'
            );
        }).join('');
    }

    // -----------------------------------------------------------------
    // 4 & 5. Detail Linimasa Pipeline + Matched/Missing Skills di Drawer
    // -----------------------------------------------------------------
    function getSelectedApplication() {
        return (state.applications || []).find((a) => Number(a.id) === Number(state.selectedAppId)) || null;
    }

    function renderDrawerHeaderAndSkills(app) {
        const berthEl = document.getElementById('drawer-berth-code');
        const titleEl = document.getElementById('drawer-job-title');
        const compEl = document.getElementById('drawer-job-company');
        const matchEl = document.getElementById('drawer-match-badge');
        const skillsSec = document.getElementById('drawer-skills-section');
        const catEl = document.getElementById('drawer-match-category');
        const matchedWrap = document.getElementById('drawer-matched-skills');
        const missingWrap = document.getElementById('drawer-missing-skills');
        const btnCv = document.getElementById('btn-drawer-download-cv');
        const btnCancel = document.getElementById('btn-drawer-cancel-app');

        if (!app) {
            if (berthEl) berthEl.textContent = 'BERTH --';
            if (titleEl) titleEl.textContent = 'Pilih Berkas Lamaran';
            if (compEl) compEl.textContent = 'Klik salah satu berkas di sebelah kiri untuk memantau linimasa seleksi.';
            if (matchEl) matchEl.textContent = '-';
            if (skillsSec) skillsSec.classList.add('hidden');
            if (btnCv) btnCv.classList.add('hidden');
            if (btnCancel) btnCancel.classList.add('hidden');
            return;
        }

        const job = app.job_vacancy || {};
        const score = Math.round((Number(app.match_score) || 0) * 10) / 10;

        if (berthEl) berthEl.textContent = 'BERTH #' + String(app.id).padStart(2, '0');
        if (titleEl) titleEl.textContent = job.title || 'Lowongan #' + app.job_vacancy_id;
        if (compEl) compEl.textContent = (job.company || 'Perusahaan') + ' • ' + (job.location || 'Indonesia');
        if (matchEl) matchEl.textContent = score + '% Anchor Fit';

        // Rincian matched & missing skills
        if (skillsSec) skillsSec.classList.remove('hidden');
        if (catEl) catEl.textContent = categoryLabel(app.match_category, score) + ' (' + score + '%)';

        const matched = Array.isArray(app.matched_skills) ? app.matched_skills : [];
        const missing = Array.isArray(app.missing_skills) ? app.missing_skills : [];

        if (matchedWrap) {
            matchedWrap.innerHTML = matched.length
                ? matched.map((s) =>
                    '<span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-900 border border-emerald-300 text-[11px] font-semibold">' +
                        JH.util.escapeHtml(JH.util.skillName(s)) +
                    '</span>'
                  ).join('')
                : '<span class="text-body-sm text-on-surface-variant italic">Belum ada keahlian yang cocok.</span>';
        }

        if (missingWrap) {
            missingWrap.innerHTML = missing.length
                ? missing.map((s) =>
                    '<span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 border border-amber-300 text-[11px] font-semibold">' +
                        JH.util.escapeHtml(JH.util.skillName(s)) +
                    '</span>'
                  ).join('')
                : '<span class="text-body-sm text-emerald-700 font-medium">Semua keahlian yang dibutuhkan telah terpenuhi!</span>';
        }

        // Action buttons di drawer bawah
        if (btnCv) {
            if (app.cv_file_path) {
                btnCv.classList.remove('hidden');
                btnCv.dataset.appId = String(app.id);
            } else {
                btnCv.classList.add('hidden');
            }
        }

        if (btnCancel) {
            if (canCancelApplication(app)) {
                btnCancel.classList.remove('hidden');
                btnCancel.dataset.appId = String(app.id);
            } else {
                btnCancel.classList.add('hidden');
            }
        }
    }

    function renderTimelineStepper(app, timelineData) {
        const bodyEl = document.getElementById('drawer-timeline-body');
        if (!bodyEl) return;

        if (!app) {
            bodyEl.innerHTML =
                '<div class="text-center py-8 text-on-surface-variant">' +
                    '<span class="material-symbols-outlined text-[32px] text-outline mb-2">timeline</span>' +
                    '<p class="text-body-sm font-body-sm">Pilih berkas lamaran untuk menampilkan tahapan seleksi.</p>' +
                '</div>';
            return;
        }

        const rawStages = (timelineData && Array.isArray(timelineData.stages))
            ? timelineData.stages
            : ((app.pipeline_timeline && Array.isArray(app.pipeline_timeline.stages)) ? app.pipeline_timeline.stages : []);

        const currentStage = (timelineData && timelineData.current_stage)
            || (app.pipeline_timeline && app.pipeline_timeline.current_stage)
            || effectiveStage(app);

        // Petakan stage history berdasarkan nama stage
        const stageMap = {};
        rawStages.forEach((entry) => {
            if (entry && entry.stage) {
                stageMap[entry.stage] = entry;
            }
        });

        const currentIdx = STAGE_SEQUENCE.findIndex((s) => s.key === currentStage);
        const isAppRejected = app.status === 'rejected' || currentStage === 'rejected';
        const isAppCanceled = app.status === 'canceled' || currentStage === 'canceled';

        const stepsHtml = STAGE_SEQUENCE.map((step, idx) => {
            const entry = stageMap[step.key] || null;
            let stateKind = 'upcoming'; // 'passed' | 'in_progress' | 'rejected' | 'upcoming'

            if (entry) {
                if (entry.status === 'rejected') stateKind = 'rejected';
                else if (entry.status === 'passed') stateKind = 'passed';
                else if (entry.status === 'in_progress') stateKind = 'in_progress';
                else if (entry.completed_at) stateKind = 'passed';
            } else if (currentIdx > idx && !isAppCanceled) {
                stateKind = 'passed';
            } else if (currentStage === step.key && !isAppCanceled) {
                stateKind = 'in_progress';
            }

            // Node circle & status badge
            let nodeHtml = '';
            let badgeHtml = '';
            let titleClass = 'text-on-surface';
            let wrapperOpacity = '';

            if (stateKind === 'passed') {
                nodeHtml =
                    '<div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm">' +
                        '<span class="material-symbols-outlined text-[16px] font-bold">check</span>' +
                    '</div>';
                badgeHtml =
                    '<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag bg-emerald-100 text-emerald-900 font-bold border border-emerald-300">PASSED</span>';
            } else if (stateKind === 'in_progress') {
                nodeHtml =
                    '<div class="relative shrink-0">' +
                        '<div class="w-8 h-8 rounded-full bg-tertiary-fixed border-2 border-tertiary-fixed-dim text-on-tertiary-fixed-variant flex items-center justify-center shadow-md">' +
                            '<span class="material-symbols-outlined text-[18px]" data-weight="fill">anchor</span>' +
                        '</div>' +
                        '<span class="absolute -inset-1 rounded-full bg-tertiary-fixed-dim opacity-40 animate-ping"></span>' +
                    '</div>';
                badgeHtml =
                    '<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag bg-tertiary-fixed text-on-tertiary-fixed-variant font-bold border border-tertiary-fixed-dim/60">IN PROGRESS</span>';
                titleClass = 'text-on-tertiary-fixed-variant';
            } else if (stateKind === 'rejected') {
                nodeHtml =
                    '<div class="w-8 h-8 rounded-full bg-error text-on-error flex items-center justify-center shrink-0 shadow-sm">' +
                        '<span class="material-symbols-outlined text-[16px]">close</span>' +
                    '</div>';
                badgeHtml =
                    '<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag bg-error-container text-on-error-container font-bold border border-error/30">REJECTED</span>';
                titleClass = 'text-error';
            } else {
                nodeHtml =
                    '<div class="w-8 h-8 rounded-full bg-surface-container border-2 border-outline-variant flex items-center justify-center text-outline shrink-0">' +
                        '<span class="material-symbols-outlined text-[16px]">' + step.icon + '</span>' +
                    '</div>';
                badgeHtml =
                    '<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag bg-surface-container text-on-surface-variant font-semibold">MENUNGGU</span>';
                wrapperOpacity = 'opacity-65';
            }

            // Timestamps (entered_at & completed_at)
            let timeInfoHtml = '';
            if (entry && (entry.entered_at || entry.completed_at)) {
                const parts = [];
                if (entry.entered_at) {
                    parts.push('Masuk: ' + JH.util.formatDate(entry.entered_at, true));
                }
                if (entry.completed_at) {
                    parts.push('Selesai: ' + JH.util.formatDate(entry.completed_at, true));
                }
                timeInfoHtml =
                    '<div class="text-coordinate-tag font-coordinate-tag text-on-surface-variant mt-1">' +
                        JH.util.escapeHtml(parts.join(' • ')) +
                    '</div>';
            } else {
                timeInfoHtml =
                    '<div class="text-coordinate-tag font-coordinate-tag text-on-surface-variant mt-0.5">' +
                        JH.util.escapeHtml(step.subtitle) +
                    '</div>';
            }

            // Recruiter notes
            let notesHtml = '';
            if (entry && entry.notes) {
                notesHtml =
                    '<div class="mt-2 p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/60 text-body-sm font-body-sm text-on-surface-variant italic">' +
                        '“' + JH.util.escapeHtml(entry.notes) + '”' +
                    '</div>';
            }

            // Interview schedule box (jika ada di tahap interview)
            let scheduleHtml = '';
            const sched = entry && entry.interview_schedule;
            if (sched && (sched.datetime || sched.meeting_url || sched.interviewer || sched.type)) {
                const isOnline = String(sched.type || 'online').toLowerCase() === 'online';
                const meetUrl = sched.meeting_url || '';
                const hrefUrl = meetUrl && (meetUrl.startsWith('http://') || meetUrl.startsWith('https://'))
                    ? meetUrl
                    : (meetUrl ? 'https://' + meetUrl : '');

                scheduleHtml =
                    '<div class="mt-3 p-3 rounded-lg bg-surface border border-outline-variant/60 space-y-2.5">' +
                        '<div class="flex items-center justify-between text-body-sm font-body-sm">' +
                            '<span class="font-semibold text-on-surface flex items-center gap-1.5">' +
                                '<span class="material-symbols-outlined text-[18px] text-secondary">' + (isOnline ? 'videocam' : 'location_on') + '</span>' +
                                '<span>Jadwal Wawancara Kandidat</span>' +
                            '</span>' +
                            '<span class="px-2 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed-variant text-coordinate-tag font-coordinate-tag font-bold uppercase">' +
                                JH.util.escapeHtml(sched.type || 'ONLINE') +
                            '</span>' +
                        '</div>' +
                        (sched.datetime
                            ? '<div class="inline-flex items-center gap-1.5 px-2 py-1 rounded bg-tertiary-fixed/60 text-on-tertiary-fixed-variant font-coordinate-tag font-bold text-[11px]">' +
                                '<span class="material-symbols-outlined text-[14px]">alarm</span>' +
                                '<span>Waktu: ' + JH.util.escapeHtml(JH.util.formatDate(sched.datetime, true)) + '</span>' +
                              '</div>'
                            : ''
                        ) +
                        (meetUrl
                            ? '<div class="flex items-center justify-between p-2 rounded bg-surface-container border border-outline-variant/60 text-coordinate-tag font-coordinate-tag text-on-surface-variant">' +
                                '<span class="truncate">' + JH.util.escapeHtml(meetUrl) + '</span>' +
                                '<button type="button" data-copy-url="' + JH.util.escapeHtml(meetUrl) + '" class="text-secondary hover:underline font-bold text-[11px] ml-2 shrink-0 flex items-center gap-0.5">' +
                                    '<span class="material-symbols-outlined text-[13px]">content_copy</span>' +
                                    '<span>Salin</span>' +
                                '</button>' +
                              '</div>' +
                              (isOnline
                                ? '<a href="' + JH.util.escapeHtml(hrefUrl) + '" target="_blank" rel="noopener noreferrer" class="w-full py-2 rounded-lg bg-secondary text-on-secondary text-label-sm font-label-sm font-semibold flex items-center justify-center gap-1.5 hover:bg-on-secondary-fixed-variant transition-colors shadow-sm">' +
                                    '<span class="material-symbols-outlined text-[16px]">open_in_new</span>' +
                                    '<span>Buka Tautan Wawancara</span>' +
                                  '</a>'
                                : ''
                              )
                            : ''
                        ) +
                        (sched.interviewer
                            ? '<div class="pt-2 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag text-on-surface-variant text-[11px]">' +
                                '<span>PEWAWANCARA (INTERVIEWER):</span>' +
                                '<span class="font-bold text-on-surface">' + JH.util.escapeHtml(sched.interviewer) + '</span>' +
                              '</div>'
                            : ''
                        ) +
                    '</div>';
            }

            return (
                '<div class="relative z-10 flex gap-3.5 ' + wrapperOpacity + '">' +
                    nodeHtml +
                    '<div class="flex-grow pt-0.5 min-w-0">' +
                        '<div class="flex items-center justify-between gap-2">' +
                            '<h4 class="text-label-md font-label-md font-bold ' + titleClass + '">' +
                                JH.util.escapeHtml(step.title) +
                            '</h4>' +
                            badgeHtml +
                        '</div>' +
                        timeInfoHtml +
                        notesHtml +
                        scheduleHtml +
                    '</div>' +
                '</div>'
            );
        }).join('');

        // Jika status akhir rejected atau canceled, tampilkan kartu catatan khusus di bawah stepper
        let terminalBannerHtml = '';
        const rejectedEntry = stageMap.rejected;
        const canceledEntry = stageMap.canceled;

        if (isAppRejected) {
            terminalBannerHtml =
                '<div class="relative z-10 p-3.5 rounded-xl bg-error-container/70 border border-error/40 text-on-error-container space-y-1">' +
                    '<div class="flex items-center gap-1.5 font-bold text-label-md">' +
                        '<span class="material-symbols-outlined text-[18px]">report</span>' +
                        '<span>Keputusan Seleksi: Belum Lolos (Rejected)</span>' +
                    '</div>' +
                    (rejectedEntry && rejectedEntry.completed_at
                        ? '<div class="text-coordinate-tag font-coordinate-tag">Ditetapkan pada ' + JH.util.escapeHtml(JH.util.formatDate(rejectedEntry.completed_at, true)) + '</div>'
                        : ''
                    ) +
                    (rejectedEntry && rejectedEntry.notes
                        ? '<p class="text-body-sm italic mt-1">“' + JH.util.escapeHtml(rejectedEntry.notes) + '”</p>'
                        : '<p class="text-body-sm">Tetap semangat! Tingkatkan keahlian di profilmu dan coba lamar lowongan lainnya.</p>'
                    ) +
                '</div>';
        } else if (isAppCanceled) {
            terminalBannerHtml =
                '<div class="relative z-10 p-3.5 rounded-xl bg-surface-container-high border border-outline-variant text-on-surface-variant space-y-1">' +
                    '<div class="flex items-center gap-1.5 font-bold text-label-md text-on-surface">' +
                        '<span class="material-symbols-outlined text-[18px]">cancel</span>' +
                        '<span>Lamaran Dibatalkan oleh Pelamar</span>' +
                    '</div>' +
                    (canceledEntry && canceledEntry.completed_at
                        ? '<div class="text-coordinate-tag font-coordinate-tag">Dibatalkan pada ' + JH.util.escapeHtml(JH.util.formatDate(canceledEntry.completed_at, true)) + '</div>'
                        : ''
                    ) +
                    (canceledEntry && canceledEntry.notes
                        ? '<p class="text-body-sm italic">“' + JH.util.escapeHtml(canceledEntry.notes) + '”</p>'
                        : ''
                    ) +
                '</div>';
        }

        bodyEl.innerHTML =
            '<div class="absolute left-[33px] top-6 bottom-10 w-0.5 bg-outline-variant/50 z-0"></div>' +
            stepsHtml +
            terminalBannerHtml;
    }

    async function selectApplication(appId) {
        state.selectedAppId = Number(appId);
        const app = getSelectedApplication();
        renderApplicationsList();
        renderDrawerHeaderAndSkills(app);

        if (!app) {
            renderTimelineStepper(null, null);
            return;
        }

        // Render awal dari relasi pipeline_timeline sambil memuat detail terbaru dari GET /api/pipelines/{id}
        renderTimelineStepper(app, app.pipeline_timeline || null);

        try {
            state.loadingTimeline = true;
            const res = await JH.pipelines.timeline(app.id);
            const timelineData = res && res.data ? res.data : null;
            if (Number(state.selectedAppId) === Number(app.id) && timelineData) {
                state.selectedTimeline = timelineData;
                app.pipeline_timeline = {
                    current_stage: timelineData.current_stage,
                    stages: timelineData.stages,
                };
                renderTimelineStepper(app, timelineData);
            }
        } catch (err) {
            JH.showError(err, 'Gagal memuat rincian linimasa seleksi.');
        } finally {
            state.loadingTimeline = false;
        }
    }

    // -----------------------------------------------------------------
    // 6 & 7. Batalkan Lamaran & Unduh CV
    // -----------------------------------------------------------------
    async function handleCancelApplication(appId, triggerBtn) {
        const app = (state.applications || []).find((a) => Number(a.id) === Number(appId));
        if (!app) return;

        if (!canCancelApplication(app)) {
            JH.toast('Lamaran ini sudah berada di tahap lanjut dan tidak dapat dibatalkan.', 'warning');
            return;
        }

        const jobTitle = (app.job_vacancy && app.job_vacancy.title) || ('Lowongan #' + app.job_vacancy_id);
        const confirmed = window.confirm(
            'Apakah kamu yakin ingin membatalkan pengajuan lamaran untuk posisi "' + jobTitle + '"? Tindakan ini tidak dapat dibatalkan.'
        );
        if (!confirmed) return;

        try {
            JH.setLoading(triggerBtn, true, 'Membatalkan...');
            const res = await JH.applications.cancel(app.id);
            JH.toast((res && res.message) || 'Berkas lamaran berhasil dibatalkan.', 'success');
            await loadApplications(app.id);
        } catch (err) {
            JH.showError(err, 'Gagal membatalkan berkas lamaran.');
        } finally {
            JH.setLoading(triggerBtn, false);
        }
    }

    async function handleDownloadCv(appId, triggerBtn) {
        try {
            JH.setLoading(triggerBtn, true, 'Mengunduh...');
            await JH.applications.downloadCv(appId);
        } catch (err) {
            JH.showError(err, 'Gagal mengunduh berkas CV.');
        } finally {
            JH.setLoading(triggerBtn, false);
        }
    }

    // -----------------------------------------------------------------
    // Load Daftar Lamaran dari GET /api/applications
    // -----------------------------------------------------------------
    async function loadApplications(preferredAppId) {
        const syncBtn = document.getElementById('btn-sync-dock');
        try {
            if (syncBtn) JH.setLoading(syncBtn, true, 'Sinkronisasi...');
            const res = await JH.applications.list();
            const list = Array.isArray(res && res.data) ? res.data : [];
            state.applications = list;

            renderStats();
            applyFilters();

            // Tentukan lamaran yang dipilih (default lamaran pertama)
            const targetId = preferredAppId || state.selectedAppId || (list[0] && list[0].id) || null;
            const exists = list.some((a) => Number(a.id) === Number(targetId));
            const finalId = exists ? targetId : (list[0] ? list[0].id : null);

            if (finalId) {
                await selectApplication(finalId);
            } else {
                state.selectedAppId = null;
                renderDrawerHeaderAndSkills(null);
                renderTimelineStepper(null, null);
            }
        } catch (err) {
            JH.showError(err, 'Gagal memuat daftar lamaran.');
            const container = document.getElementById('applications-list-container');
            if (container) {
                container.innerHTML =
                    '<div class="bg-surface-container-lowest rounded-xl border border-error/40 p-space-xl text-center space-y-2">' +
                        '<span class="material-symbols-outlined text-[28px] text-error">error</span>' +
                        '<p class="text-body-md font-semibold text-on-surface">Gagal memuat daftar berkas lamaran.</p>' +
                        '<p class="text-body-sm text-on-surface-variant">' + JH.util.escapeHtml(err.message || 'Coba muat ulang halaman.') + '</p>' +
                    '</div>';
            }
        } finally {
            if (syncBtn) JH.setLoading(syncBtn, false);
        }
    }

    // -----------------------------------------------------------------
    // Event Listeners
    // -----------------------------------------------------------------
    function bindEvents() {
        // Filter Tabs
        const tabsWrap = document.getElementById('status-filter-tabs');
        if (tabsWrap) {
            tabsWrap.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-status-tab]');
                if (!btn) return;
                state.activeStatusFilter = btn.getAttribute('data-status-tab') || 'all';
                updateFilterControlsUI();
                applyFilters();
            });
        }

        // Filter Select Dropdown
        const selectEl = document.getElementById('filter-status-select');
        if (selectEl) {
            selectEl.addEventListener('change', () => {
                state.activeStatusFilter = selectEl.value || 'all';
                updateFilterControlsUI();
                applyFilters();
            });
        }

        // Search Input
        const searchEl = document.getElementById('filter-search-input');
        if (searchEl) {
            searchEl.addEventListener('input', () => {
                state.searchQuery = searchEl.value || '';
                applyFilters();
            });
        }

        // Sync Button
        const syncBtn = document.getElementById('btn-sync-dock');
        if (syncBtn) {
            syncBtn.addEventListener('click', async () => {
                await loadApplications(state.selectedAppId);
                JH.toast('Data dermaga berhasil disinkronkan.', 'info', 2200);
            });
        }

        // Delegasi klik pada daftar kartu lamaran
        const listContainer = document.getElementById('applications-list-container');
        if (listContainer) {
            listContainer.addEventListener('click', (e) => {
                const cancelBtn = e.target.closest('[data-cancel-app]');
                if (cancelBtn) {
                    e.stopPropagation();
                    handleCancelApplication(cancelBtn.getAttribute('data-cancel-app'), cancelBtn);
                    return;
                }

                const cvBtn = e.target.closest('[data-download-cv]');
                if (cvBtn) {
                    e.stopPropagation();
                    handleDownloadCv(cvBtn.getAttribute('data-download-cv'), cvBtn);
                    return;
                }

                const selectBtn = e.target.closest('[data-select-app]');
                if (selectBtn) {
                    e.stopPropagation();
                    selectApplication(selectBtn.getAttribute('data-select-app'));
                    return;
                }

                const card = e.target.closest('[data-app-card]');
                if (card) {
                    selectApplication(card.getAttribute('data-app-card'));
                }
            });
        }

        // Delegasi klik pada drawer timeline (salin tautan meet, unduh CV, batalkan lamaran)
        const drawerCard = document.getElementById('pipeline-drawer-card');
        if (drawerCard) {
            drawerCard.addEventListener('click', (e) => {
                const copyBtn = e.target.closest('[data-copy-url]');
                if (copyBtn) {
                    const url = copyBtn.getAttribute('data-copy-url') || '';
                    if (navigator.clipboard && url) {
                        navigator.clipboard.writeText(url).then(() => {
                            JH.toast('Tautan wawancara berhasil disalin.', 'success', 2000);
                        });
                    }
                    return;
                }
            });
        }

        const btnDrawerCv = document.getElementById('btn-drawer-download-cv');
        if (btnDrawerCv) {
            btnDrawerCv.addEventListener('click', () => {
                const id = btnDrawerCv.dataset.appId || state.selectedAppId;
                if (id) handleDownloadCv(id, btnDrawerCv);
            });
        }

        const btnDrawerCancel = document.getElementById('btn-drawer-cancel-app');
        if (btnDrawerCancel) {
            btnDrawerCancel.addEventListener('click', () => {
                const id = btnDrawerCancel.dataset.appId || state.selectedAppId;
                if (id) handleCancelApplication(id, btnDrawerCancel);
            });
        }
    }

    const start = () => {
        bindEvents();
        loadUserProfile();
        loadApplications();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
