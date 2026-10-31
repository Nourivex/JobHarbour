/**
 * JobHarbour - Script Halaman Katalog Lowongan (The Anchorage)
 * Menghubungkan UI catalog.blade.php dengan endpoint GET /api/jobs dan GET /api/jobs/{id}
 * serta mesin pemadanan keahlian Weighted Jaccard Similarity (JH.matchmaking.calculate).
 */
(function (window, document) {
    'use strict';

    const JH = window.JH;
    if (!JH) return;

    const { escapeHtml, formatSalaryRange, formatDate, timeAgo, initials, EMPLOYMENT_LABELS } = JH.util;

    // State halaman katalog
    const state = {
        search: '',
        location: '',
        employment_type: '',
        skill: '',
        sort: 'latest',
        highMatchOnly: false,
        page: 1,
        per_page: 10,
        jobs: [],
        pagination: null,
        selectedJobId: null,
        selectedJobDetail: null,
        loadingList: false,
        loadingDetail: false,
    };

    // Label level requirement dan bobotnya
    const LEVEL_META = {
        required: { label: 'Wajib (required)', shortLabel: 'required', weight: 3, badgeClass: 'bg-secondary-fixed/70 text-on-secondary-fixed border-secondary/30' },
        important: { label: 'Penting (important)', shortLabel: 'important', weight: 2, badgeClass: 'bg-surface-container-high text-on-surface border-outline-variant/60' },
        nice_to_have: { label: 'Nilai Tambah (nice_to_have)', shortLabel: 'nice_to_have', weight: 1, badgeClass: 'bg-surface-container-low text-on-surface-variant border-outline-variant/40' },
    };

    function normalizeLevel(rawLevel) {
        const lv = String(rawLevel || 'required').toLowerCase().replace(/-/g, '_');
        if (lv === 'important') return 'important';
        if (lv === 'nice_to_have') return 'nice_to_have';
        return 'required';
    }

    function getCurrentApplicantSkills() {
        const user = JH.session.user();
        if (!user || ! JH.session.isLoggedIn()) return null;
        if (user.role !== 'applicant' && user.role !== 'admin') return null;
        return Array.isArray(user.skills) ? user.skills : [];
    }

    /**
     * Arahkan aksi tombol Lamar sesuai status login dan role user.
     */
    function handleApplyJob(jobId) {
        if (!jobId) {
            JH.toast('Pilih salah satu lowongan terlebih dahulu.', 'info');
            return;
        }
        const applyPath = '/apply?job=' + encodeURIComponent(jobId);
        const loggedIn = JH.session.isLoggedIn() && JH.session.user();

        if (!loggedIn) {
            window.location.href = '/login?next=' + encodeURIComponent(applyPath);
            return;
        }

        const user = JH.session.user();
        if (user.role === 'recruiter') {
            JH.toast('Akun Recruiter tidak dapat melamar lowongan. Mengarahkan ke dasbor Recruiter...', 'info', 2500);
            setTimeout(() => {
                window.location.href = '/recruiter';
            }, 900);
            return;
        }

        window.location.href = applyPath;
    }

    /**
     * Hitung rincian kecocokan per tier (required, important, nice_to_have)
     * untuk ditampilkan pada panel telemetri detail lowongan.
     */
    function calculateTierBreakdown(candidateSkills, requirements) {
        const normalizedCandidate = (candidateSkills || []).map((s) => {
            if (s && typeof s === 'object') return String(s.name || s.skill || '').toLowerCase().trim();
            return String(s || '').toLowerCase().trim();
        }).filter(Boolean);

        const tiers = {
            required: { total: 0, matched: 0, items: [] },
            important: { total: 0, matched: 0, items: [] },
            nice_to_have: { total: 0, matched: 0, items: [] },
        };

        (requirements || []).forEach((req) => {
            const skill = typeof req === 'string' ? req : (req.skill || req.name || '');
            const level = normalizeLevel(req && req.level);
            const weight = (req && Number(req.weight) > 0) ? Number(req.weight) : LEVEL_META[level].weight;
            const isMatched = normalizedCandidate.includes(skill.toLowerCase().trim());

            tiers[level].total += 1;
            if (isMatched) tiers[level].matched += 1;
            tiers[level].items.push({ skill, level, weight, isMatched });
        });

        return tiers;
    }

    /**
     * Render daftar kartu lowongan di kolom kiri.
     */
    function renderJobList() {
        const listEl = document.getElementById('catalog-job-list');
        const countHeading = document.getElementById('catalog-count-heading');
        const filterBadge = document.getElementById('catalog-active-filter-badge');
        const resetBtn = document.getElementById('reset-filters-btn');

        if (!listEl) return;

        const applicantSkills = getCurrentApplicantSkills();
        const user = JH.session.user();
        const isApplicant = JH.session.isLoggedIn() && user && (user.role === 'applicant' || user.role === 'admin');

        // Hitung skor match tiap lowongan bila user adalah applicant
        let processedJobs = state.jobs.map((job) => {
            const reqs = Array.isArray(job.requirements) ? job.requirements : [];
            const match = isApplicant ? JH.matchmaking.calculate(applicantSkills || [], reqs) : null;
            return { ...job, _match: match };
        });

        // Filter hanya yang cocok >= 80% bila checkbox dicentang
        if (state.highMatchOnly) {
            if (isApplicant) {
                processedJobs = processedJobs.filter((j) => j._match && j._match.score >= 80);
            }
        }

        // Urutkan berdasarkan kecocokan tertinggi bila dipilih
        if (state.sort === 'match' && isApplicant) {
            processedJobs.sort((a, b) => ((b._match ? b._match.score : 0) - (a._match ? a._match.score : 0)));
        }

        // Update indikator jumlah hasil
        const totalCount = state.highMatchOnly && isApplicant
            ? processedJobs.length
            : (state.pagination ? state.pagination.total : processedJobs.length);

        if (countHeading) {
            countHeading.textContent = `${totalCount} Lowongan Aktif di Dermaga`;
        }

        // Tampilkan chip filter aktif
        const activeFilters = [];
        if (state.search) activeFilters.push(`Cari: "${state.search}"`);
        if (state.location) activeFilters.push(`Lokasi: ${state.location}`);
        if (state.employment_type) activeFilters.push(`Tipe: ${EMPLOYMENT_LABELS[state.employment_type] || state.employment_type}`);
        if (state.skill) activeFilters.push(`Skill: ${state.skill}`);
        if (state.highMatchOnly) activeFilters.push('Match >= 80%');

        if (filterBadge) {
            filterBadge.textContent = activeFilters.length ? activeFilters.join(' · ') : 'Live Telemetry';
        }
        if (resetBtn) {
            resetBtn.classList.toggle('hidden', activeFilters.length === 0);
        }

        // Update highlight pada quick skill chips
        document.querySelectorAll('[data-skill-chip]').forEach((btn) => {
            const chipSkill = btn.getAttribute('data-skill-chip') || '';
            const isSelected = state.skill.toLowerCase() === chipSkill.toLowerCase();
            btn.className = isSelected
                ? 'px-2.5 py-1 rounded-md bg-secondary text-on-secondary font-semibold transition-all flex items-center gap-1 border border-secondary-container shadow-xs'
                : 'px-2.5 py-1 rounded-md bg-white/10 hover:bg-white/20 text-white transition-all flex items-center gap-1 border border-white/10';
        });

        if (processedJobs.length === 0) {
            listEl.innerHTML = `
                <div class="p-space-xl rounded-xl bg-surface-container-lowest border border-outline-variant/60 text-center space-y-3">
                    <span class="material-symbols-outlined text-4xl text-outline">travel_explore</span>
                    <h3 class="text-headline-sm font-headline-sm font-bold text-on-surface">Tidak Ada Lowongan yang Cocok</h3>
                    <p class="text-body-sm text-on-surface-variant">
                        Belum ada lowongan di dermaga yang sesuai dengan filter pencarianmu. Coba ubah kata kunci atau reset filter.
                    </p>
                    <button type="button" id="empty-reset-btn" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-label-sm font-semibold hover:bg-on-secondary-container transition-all">
                        Tampilkan Semua Lowongan
                    </button>
                </div>
            `;
            const emptyReset = document.getElementById('empty-reset-btn');
            if (emptyReset) emptyReset.addEventListener('click', resetAllFilters);
            renderEmptyDetailPane();
            return;
        }

        // Jika belum ada job yang dipilih atau job yang dipilih tidak ada di list, pilih job pertama
        const stillInList = processedJobs.some((j) => Number(j.id) === Number(state.selectedJobId));
        if (!state.selectedJobId || !stillInList) {
            state.selectedJobId = processedJobs[0].id;
            loadJobDetail(state.selectedJobId, false);
        }

        listEl.innerHTML = processedJobs.map((job) => {
            const isSelected = Number(job.id) === Number(state.selectedJobId);
            const empLabel = EMPLOYMENT_LABELS[job.employment_type] || job.employment_type || 'Full-time';
            const salaryText = formatSalaryRange(job.salary_min, job.salary_max);
            const postedAgo = timeAgo(job.created_at);
            const reqs = Array.isArray(job.requirements) ? job.requirements : [];
            const match = job._match;

            let matchBadgeHtml = '';
            if (match) {
                const scoreRounded = Math.round(match.score);
                const badgeTone = scoreRounded >= 80
                    ? 'bg-tertiary-fixed text-on-tertiary-container border-tertiary-fixed-dim/40'
                    : scoreRounded >= 50
                        ? 'bg-secondary-fixed text-on-secondary-fixed border-secondary/30'
                        : 'bg-surface-container-high text-on-surface-variant border-outline-variant/50';
                matchBadgeHtml = `
                    <div class="ml-auto inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full border text-label-sm font-label-sm font-bold ${badgeTone}">
                        <span class="w-2 h-2 rounded-full bg-current"></span>
                        <span>★ ${scoreRounded}% Cocok</span>
                    </div>
                `;
            }

            const matchedLower = match ? (match.matched_skills || []).map((s) => String(s).toLowerCase()) : [];

            const skillChipsHtml = reqs.map((req) => {
                const sName = typeof req === 'string' ? req : (req.skill || req.name || '');
                const level = normalizeLevel(req && req.level);
                const meta = LEVEL_META[level];
                const isSkillMatched = matchedLower.includes(sName.toLowerCase());
                const chipStyle = isSkillMatched
                    ? 'bg-secondary-fixed/60 text-on-secondary-fixed border-secondary/40 font-semibold'
                    : 'bg-surface-container-low text-on-surface-variant border-outline-variant/40';

                return `
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded border text-[11px] ${chipStyle}" title="Level: ${escapeHtml(meta.label)} (Bobot ${meta.weight})">
                        <span>${escapeHtml(sName)}</span>
                        <span class="text-[9px] uppercase opacity-75 px-1 rounded bg-black/5">${escapeHtml(meta.shortLabel)}</span>
                    </span>
                `;
            }).join('');

            const cardClasses = isSelected
                ? 'relative p-space-lg rounded-xl bg-surface-container-lowest border-2 border-secondary shadow-[0_4px_16px_-4px_rgba(2,132,199,0.12)] cursor-pointer transition-all'
                : 'relative p-space-lg rounded-xl bg-surface-container-lowest border border-outline-variant/60 hover:border-secondary transition-all hover:shadow-md cursor-pointer';

            return `
                <article class="${cardClasses}" data-job-card-id="${escapeHtml(job.id)}">
                    ${isSelected ? '<div class="absolute left-0 top-0 bottom-0 w-1.5 bg-secondary rounded-l-xl"></div>' : ''}
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg ${isSelected ? 'bg-primary-container text-on-primary shadow-inner' : 'bg-surface-container-high text-on-surface border border-outline-variant'} font-headline-sm font-bold flex items-center justify-center shrink-0">
                                ${escapeHtml(initials(job.company))}
                            </div>
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="text-label-md font-label-md text-on-surface font-semibold">${escapeHtml(job.company)}</span>
                                    <span class="material-symbols-outlined text-secondary text-sm" data-icon="verified" style="font-variation-settings: 'FILL' 1;">verified</span>
                                </div>
                                <span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant flex items-center gap-1 mt-0.5 uppercase">
                                    <span class="material-symbols-outlined text-xs" data-icon="pin_drop">pin_drop</span>
                                    ${escapeHtml(job.location || '-')} • ${escapeHtml(empLabel)}
                                </span>
                            </div>
                        </div>
                        <span class="text-label-sm font-label-sm text-outline shrink-0">${escapeHtml(postedAgo)}</span>
                    </div>
                    <div class="mt-3">
                        <h3 class="text-headline-sm font-headline-sm ${isSelected ? 'text-secondary' : 'text-on-surface'} font-bold hover:underline">
                            ${escapeHtml(job.title)}
                        </h3>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <span class="text-label-md font-label-md font-bold text-on-surface bg-surface-container-low px-2 py-0.5 rounded border border-outline-variant/60">
                                ${escapeHtml(salaryText)}${(job.salary_min || job.salary_max) ? ' / bln' : ''}
                            </span>
                        </div>
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-1.5">
                        <span class="px-2 py-0.5 rounded bg-surface-container text-on-surface-variant text-label-sm font-label-sm">${escapeHtml(empLabel)}</span>
                        <span class="px-2 py-0.5 rounded bg-surface-container text-on-surface-variant text-label-sm font-label-sm">Kuota: ${escapeHtml(job.quota || 1)} posisi</span>
                        ${matchBadgeHtml}
                    </div>
                    <div class="mt-3 pt-3 border-t border-outline-variant/40 flex flex-wrap items-center justify-between gap-2">
                        <div class="flex flex-wrap gap-1">
                            ${skillChipsHtml || '<span class="text-xs text-outline">Tidak ada syarat spesifik</span>'}
                        </div>
                        <button type="button" data-open-detail-modal="${escapeHtml(job.id)}" class="text-xs font-semibold text-secondary hover:underline flex items-center gap-0.5 shrink-0 ml-auto">
                            <span>Detail</span>
                            <span class="material-symbols-outlined text-sm">chevron_right</span>
                        </button>
                    </div>
                </article>
            `;
        }).join('');

        // Pasang event listener klik kartu lowongan
        listEl.querySelectorAll('[data-job-card-id]').forEach((card) => {
            card.addEventListener('click', (e) => {
                const modalBtn = e.target.closest('[data-open-detail-modal]');
                const jobId = Number(card.getAttribute('data-job-card-id'));
                state.selectedJobId = jobId;
                renderJobList();
                // Buka modal jika di layar kecil atau tombol Detail diklik langsung
                const shouldOpenModal = Boolean(modalBtn) || window.innerWidth < 1024;
                loadJobDetail(jobId, shouldOpenModal);
            });
        });
    }

    /**
     * Render kontrol pagination berdasarkan meta.pagination dari API.
     */
    function renderPagination() {
        const pagEl = document.getElementById('catalog-pagination');
        if (!pagEl) return;

        const pag = state.pagination;
        if (!pag || pag.last_page <= 1) {
            pagEl.innerHTML = pag
                ? `<span class="text-xs text-on-surface-variant">Menampilkan ${escapeHtml(state.jobs.length)} dari ${escapeHtml(pag.total)} lowongan (Halaman 1/1)</span>`
                : '';
            return;
        }

        const current = pag.current_page;
        const last = pag.last_page;

        let pageButtons = '';
        for (let p = 1; p <= last; p++) {
            const activeClass = p === current
                ? 'bg-secondary text-on-secondary font-bold border-secondary'
                : 'bg-surface-container-lowest text-on-surface hover:border-secondary border-outline-variant/80';
            pageButtons += `
                <button type="button" data-page="${p}" class="w-8 h-8 rounded-lg border text-xs transition-all ${activeClass}">
                    ${p}
                </button>
            `;
        }

        pagEl.innerHTML = `
            <span class="text-xs text-on-surface-variant">
                Halaman <strong>${escapeHtml(current)}</strong> dari <strong>${escapeHtml(last)}</strong> (${escapeHtml(pag.total)} lowongan)
            </span>
            <div class="flex items-center gap-1">
                <button type="button" data-page="${current - 1}" ${current <= 1 ? 'disabled' : ''} class="px-2.5 h-8 rounded-lg border border-outline-variant/80 bg-surface-container-lowest text-xs font-semibold text-on-surface disabled:opacity-40 hover:border-secondary transition-all">
                    Prev
                </button>
                ${pageButtons}
                <button type="button" data-page="${current + 1}" ${current >= last ? 'disabled' : ''} class="px-2.5 h-8 rounded-lg border border-outline-variant/80 bg-surface-container-lowest text-xs font-semibold text-on-surface disabled:opacity-40 hover:border-secondary transition-all">
                    Next
                </button>
            </div>
        `;

        pagEl.querySelectorAll('[data-page]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const targetPage = Number(btn.getAttribute('data-page'));
                if (!targetPage || targetPage < 1 || targetPage > last || targetPage === state.page) return;
                state.page = targetPage;
                fetchJobs();
            });
        });
    }

    function renderEmptyDetailPane() {
        const detailCard = document.getElementById('catalog-detail-card');
        if (!detailCard) return;
        detailCard.innerHTML = `
            <div class="py-12 text-center space-y-2 text-on-surface-variant">
                <span class="material-symbols-outlined text-4xl text-outline">anchor</span>
                <h3 class="text-headline-sm font-bold text-on-surface">Pilih Lowongan di Sisi Kiri</h3>
                <p class="text-body-sm">Klik salah satu kartu lowongan untuk melihat rincian deskripsi, bobot keahlian, dan skor kecocokan.</p>
            </div>
        `;
    }

    /**
     * Buat HTML lengkap untuk tampilan detail lowongan (dipakai di panel kanan & modal).
     */
    function buildJobDetailHtml(job, isModal = false) {
        const user = JH.session.user();
        const loggedIn = JH.session.isLoggedIn() && user;
        const isApplicant = loggedIn && (user.role === 'applicant' || user.role === 'admin');
        const isRecruiter = loggedIn && user.role === 'recruiter';
        const applicantSkills = getCurrentApplicantSkills() || [];

        const reqs = Array.isArray(job.requirements) ? job.requirements : [];
        const match = isApplicant ? JH.matchmaking.calculate(applicantSkills, reqs) : null;
        const tiers = calculateTierBreakdown(isApplicant ? applicantSkills : [], reqs);

        const empLabel = EMPLOYMENT_LABELS[job.employment_type] || job.employment_type || 'Full-time';
        const salaryText = formatSalaryRange(job.salary_min, job.salary_max);
        const postedAgo = timeAgo(job.created_at);
        const remainingQuota = Math.max(0, Number(job.quota || 1) - Number(job.filled || 0));

        // Tombol Lamar sesuai role
        let applyButtonLabel = 'Lamar Sekarang (Quick Apply)';
        let applyButtonIcon = 'send';
        if (!loggedIn) {
            applyButtonLabel = 'Masuk untuk Melamar Lowongan Ini';
            applyButtonIcon = 'login';
        } else if (isRecruiter) {
            applyButtonLabel = 'Kelola di Dasbor Recruiter';
            applyButtonIcon = 'dashboard';
        }

        // Bagian Telemetri Kecocokan (Weighted Jaccard)
        let telemetryHtml = '';
        if (isApplicant && match) {
            const scoreRounded = Math.round(match.score);
            const categoryLabel = match.category === 'excellent'
                ? 'COCOK TINGGI (EXCELLENT)'
                : match.category === 'good'
                    ? 'CUKUP COCOK (GOOD)'
                    : 'PERLU PENINGKATAN SKILL';

            const reqPct = tiers.required.total > 0 ? Math.round((tiers.required.matched / tiers.required.total) * 100) : 100;
            const impPct = tiers.important.total > 0 ? Math.round((tiers.important.matched / tiers.important.total) * 100) : 100;
            const nicePct = tiers.nice_to_have.total > 0 ? Math.round((tiers.nice_to_have.matched / tiers.nice_to_have.total) * 100) : 100;

            telemetryHtml = `
                <div class="mt-space-lg p-space-md rounded-xl bg-surface-container-low border border-secondary/30 relative overflow-hidden">
                    <div class="flex flex-wrap items-start justify-between gap-3 mb-2">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-secondary" data-icon="radar">radar</span>
                            <h4 class="text-headline-sm font-headline-sm text-on-surface font-bold">
                                Analisis Algoritma Kecocokan (Weighted Jaccard): ${escapeHtml(scoreRounded)}%
                            </h4>
                        </div>
                        <span class="text-coordinate-tag font-coordinate-tag bg-tertiary-fixed text-on-tertiary-container px-2 py-0.5 rounded font-bold uppercase">
                            ${escapeHtml(categoryLabel)}
                        </span>
                    </div>
                    <p class="text-body-sm font-body-sm text-on-surface-variant leading-relaxed">
                        Berdasarkan profil <strong>${escapeHtml(user.name)}</strong>, diperoleh bobot terpenuhi
                        <strong>${escapeHtml(match.calculation_details.matched_weight)}</strong> dari total bobot
                        <strong>${escapeHtml(match.calculation_details.total_weight)}</strong>
                        (${escapeHtml(match.matched_skills.length)} dari ${escapeHtml(reqs.length)} keahlian cocok).
                    </p>
                    <div class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-outline-variant/40">
                        <div>
                            <div class="flex justify-between text-label-sm font-label-sm mb-1">
                                <span class="text-on-surface-variant">Wajib (Bobot 3)</span>
                                <span class="font-bold text-on-surface">${tiers.required.matched}/${tiers.required.total} (${reqPct}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
                                <div class="h-full bg-secondary rounded-full" style="width: ${reqPct}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-label-sm font-label-sm mb-1">
                                <span class="text-on-surface-variant">Penting (Bobot 2)</span>
                                <span class="font-bold text-on-surface">${tiers.important.matched}/${tiers.important.total} (${impPct}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
                                <div class="h-full bg-secondary rounded-full" style="width: ${impPct}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-label-sm font-label-sm mb-1">
                                <span class="text-on-surface-variant">Nilai Tambah (Bobot 1)</span>
                                <span class="font-bold text-on-surface">${tiers.nice_to_have.matched}/${tiers.nice_to_have.total} (${nicePct}%)</span>
                            </div>
                            <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
                                <div class="h-full bg-secondary rounded-full" style="width: ${nicePct}%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else if (!loggedIn) {
            telemetryHtml = `
                <div class="mt-space-lg p-space-md rounded-xl bg-surface-container-low border border-outline-variant/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="text-label-md font-bold text-on-surface flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-secondary text-lg">radar</span>
                            Simulasi Skor Kecocokan (Weighted Jaccard Similarity)
                        </h4>
                        <p class="text-body-sm text-on-surface-variant mt-0.5">
                            Masuk sebagai pelamar untuk melihat persentase kecocokan keahlianmu secara otomatis di setiap lowongan.
                        </p>
                    </div>
                    <a href="/login?next=${encodeURIComponent('/catalog?job=' + job.id)}" class="px-3.5 py-2 rounded-lg bg-secondary text-on-secondary text-label-sm font-semibold hover:bg-on-secondary-container transition-all shrink-0">
                        Masuk Akun
                    </a>
                </div>
            `;
        } else if (isRecruiter) {
            telemetryHtml = `
                <div class="mt-space-lg p-space-md rounded-xl bg-surface-container-low border border-outline-variant/60 flex items-center justify-between gap-3">
                    <div class="text-body-sm text-on-surface-variant">
                        <strong class="text-on-surface">Mode Recruiter Aktif:</strong> Mesin Weighted Jaccard akan menghitung skor kandidat secara otomatis saat pelamar mengirimkan lamaran ke lowongan ini.
                    </div>
                    <a href="/recruiter" class="px-3 py-1.5 rounded-lg bg-primary-container text-on-primary text-xs font-semibold shrink-0">
                        Buka Harbour Master
                    </a>
                </div>
            `;
        }

        // Helper untuk render grup tier keahlian
        function renderTierGroup(levelKey, titleText) {
            const group = tiers[levelKey];
            if (!group || group.items.length === 0) return '';

            const chips = group.items.map((item) => {
                if (isApplicant) {
                    if (item.isMatched) {
                        return `
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container-low border border-secondary/50 text-body-sm font-body-sm text-on-surface">
                                <span class="material-symbols-outlined text-secondary text-sm" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                                <span>${escapeHtml(item.skill)}</span>
                                <span class="text-xs text-secondary font-semibold">✓ Cocok (Bobot ${escapeHtml(item.weight)})</span>
                            </span>
                        `;
                    }
                    return `
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-tertiary-fixed/30 border border-tertiary-fixed-dim/50 text-body-sm font-body-sm text-on-surface">
                            <span class="material-symbols-outlined text-on-tertiary-container text-sm">warning</span>
                            <span>${escapeHtml(item.skill)}</span>
                            <span class="text-xs text-on-tertiary-container font-semibold">Belum di Profil (Bobot ${escapeHtml(item.weight)})</span>
                        </span>
                    `;
                }

                return `
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-surface-container-low border border-outline-variant/60 text-body-sm font-body-sm text-on-surface">
                        <span class="material-symbols-outlined text-secondary text-sm">verified</span>
                        <span>${escapeHtml(item.skill)}</span>
                        <span class="text-xs text-on-surface-variant font-medium">(${escapeHtml(item.level)} · Bobot ${escapeHtml(item.weight)})</span>
                    </span>
                `;
            }).join('');

            const statusCounter = isApplicant
                ? `${group.matched}/${group.total} Terpenuhi`
                : `${group.total} Keahlian`;

            return `
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-label-sm font-label-sm text-on-surface font-semibold uppercase tracking-wider text-secondary">
                            ${escapeHtml(titleText)}
                        </span>
                        <span class="text-coordinate-tag font-coordinate-tag text-outline">${escapeHtml(statusCounter)}</span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        ${chips}
                    </div>
                </div>
            `;
        }

        const descriptionParagraphs = String(job.description || 'Belum ada deskripsi rincian untuk lowongan ini.')
            .split(/\n+/)
            .filter(Boolean)
            .map((p) => `<p class="text-body-md font-body-md text-on-surface-variant leading-relaxed">${escapeHtml(p)}</p>`)
            .join('');

        return `
            <!-- Detail Header Section -->
            <div class="border-b border-outline-variant/60 pb-space-lg">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 rounded-xl bg-primary-container text-on-primary flex items-center justify-center font-display-lg-mobile font-bold shadow-md shrink-0">
                            ${escapeHtml(initials(job.company))}
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-surface-container text-secondary text-coordinate-tag font-coordinate-tag uppercase font-bold mb-1">
                                <span class="material-symbols-outlined text-[13px]" data-icon="anchor">anchor</span>
                                ID LOWONGAN #${escapeHtml(job.id)} • ${escapeHtml((job.status || 'open').toUpperCase())}
                            </div>
                            <h2 class="text-headline-lg-mobile md:text-headline-lg font-headline-lg font-bold text-on-surface leading-snug">
                                ${escapeHtml(job.title)}
                            </h2>
                            <div class="flex flex-wrap items-center gap-2 mt-1 text-body-sm font-body-sm text-on-surface-variant">
                                <span class="text-secondary font-semibold flex items-center gap-1">
                                    ${escapeHtml(job.company)}
                                </span>
                                <span>•</span>
                                <span class="flex items-center gap-1">
                                    <span class="material-symbols-outlined text-sm" data-icon="location_on">location_on</span>
                                    ${escapeHtml(job.location || '-')} (${escapeHtml(empLabel)})
                                </span>
                                <span>•</span>
                                <span class="text-outline">Ditayangkan ${escapeHtml(postedAgo)}</span>
                            </div>
                        </div>
                    </div>
                    ${!isModal ? `
                        <button type="button" data-expand-modal="${escapeHtml(job.id)}" class="p-2 rounded-lg border border-outline-variant/60 text-on-surface-variant hover:text-secondary hover:border-secondary transition-all" title="Buka di Modal Layar Penuh">
                            <span class="material-symbols-outlined text-lg">open_in_full</span>
                        </button>
                    ` : ''}
                </div>
                <!-- Salary & Remuneration Bar -->
                <div class="mt-4 p-3 rounded-xl bg-surface-container-low border border-outline-variant/40 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <span class="text-label-sm font-label-sm text-on-surface-variant block">Penawaran Kompensasi Utama:</span>
                        <span class="text-headline-md font-headline-md text-on-surface font-extrabold tracking-tight">
                            ${escapeHtml(salaryText)} ${(job.salary_min || job.salary_max) ? '<span class="text-body-sm font-normal text-on-surface-variant">/ bulan</span>' : ''}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="px-2.5 py-1 rounded-md bg-surface-container-lowest text-on-surface font-medium border border-outline-variant/60 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-secondary">group</span>
                            Kuota: ${escapeHtml(remainingQuota)} dari ${escapeHtml(job.quota || 1)} tersedia
                        </span>
                        <span class="px-2.5 py-1 rounded-md bg-surface-container-lowest text-on-surface font-medium border border-outline-variant/60 flex items-center gap-1">
                            <span class="material-symbols-outlined text-sm text-secondary">event</span>
                            Batas: ${escapeHtml(job.expires_at ? formatDate(job.expires_at) : 'Selama masih dibuka')}
                        </span>
                    </div>
                </div>
                <!-- Primary Action Buttons Bar -->
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <button type="button" data-apply-job-id="${escapeHtml(job.id)}" class="flex-1 min-w-[200px] h-11 px-6 rounded-xl bg-secondary text-on-secondary font-headline-sm text-sm font-semibold flex items-center justify-center gap-2 hover:bg-on-secondary-container transition-all active:scale-95 shadow-md">
                        <span class="material-symbols-outlined text-lg">${escapeHtml(applyButtonIcon)}</span>
                        <span>${escapeHtml(applyButtonLabel)}</span>
                    </button>
                    <button type="button" data-filter-by-company="${escapeHtml(job.company)}" class="h-11 px-4 rounded-xl bg-surface-container-lowest text-on-surface border border-outline-variant hover:border-secondary hover:bg-surface-container-low transition-all flex items-center gap-1.5 text-label-md font-label-md">
                        <span class="material-symbols-outlined text-lg text-secondary">apartment</span>
                        <span>Lowongan Serupa</span>
                    </button>
                </div>
            </div>

            ${telemetryHtml}

            <!-- REQUIRED SKILLS MATRIX (Weighted Breakdown) -->
            <div class="mt-space-lg space-y-space-md">
                <h4 class="text-headline-sm font-headline-sm text-on-surface font-bold flex items-center gap-2">
                    <span class="material-symbols-outlined text-secondary text-xl" data-icon="checklist">checklist</span>
                    Matriks Persyaratan Keahlian &amp; Bobot
                </h4>
                ${renderTierGroup('required', 'Wajib (required - Bobot 3)')}
                ${renderTierGroup('important', 'Penting (important - Bobot 2)')}
                ${renderTierGroup('nice_to_have', 'Nilai Tambah (nice_to_have - Bobot 1)')}
                ${reqs.length === 0 ? '<p class="text-body-sm text-on-surface-variant">Tidak ada syarat keahlian khusus.</p>' : ''}
            </div>

            <!-- JOB DESCRIPTION & RESPONSIBILITIES -->
            <div class="mt-space-xl pt-space-lg border-t border-outline-variant/60 space-y-space-md">
                <div>
                    <h4 class="text-headline-sm font-headline-sm text-on-surface font-bold mb-2">
                        Deskripsi Pekerjaan &amp; Tanggung Jawab Operasional
                    </h4>
                    <div class="space-y-2">
                        ${descriptionParagraphs}
                    </div>
                </div>
                ${job.recruiter ? `
                    <div class="p-3 rounded-xl bg-surface-container-low border border-outline-variant/50 flex items-center justify-between gap-3 text-body-sm">
                        <div class="flex items-center gap-2.5">
                            <span class="material-symbols-outlined text-secondary">verified_user</span>
                            <div>
                                <span class="font-semibold text-on-surface">Diposting oleh Recruiter: ${escapeHtml(job.recruiter.name)}</span>
                                <span class="block text-xs text-on-surface-variant">${escapeHtml(job.recruiter.email || '')}</span>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded bg-surface-container-high text-on-surface-variant font-mono">Terverifikasi</span>
                    </div>
                ` : ''}
            </div>
        `;
    }

    /**
     * Pasang handler tombol di dalam container detail (baik panel kanan maupun modal).
     */
    function bindDetailActions(container) {
        if (!container) return;

        container.querySelectorAll('[data-apply-job-id]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const jobId = btn.getAttribute('data-apply-job-id');
                handleApplyJob(jobId);
            });
        });

        container.querySelectorAll('[data-expand-modal]').forEach((btn) => {
            btn.addEventListener('click', () => {
                if (state.selectedJobDetail) {
                    openDetailModal(state.selectedJobDetail);
                }
            });
        });

        container.querySelectorAll('[data-filter-by-company]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const company = btn.getAttribute('data-filter-by-company') || '';
                const searchInput = document.getElementById('filter-search');
                if (searchInput) searchInput.value = company;
                state.search = company;
                state.page = 1;
                closeDetailModal();
                fetchJobs();
            });
        });
    }

    function openDetailModal(job) {
        const modal = document.getElementById('catalog-detail-modal');
        const modalBody = document.getElementById('catalog-modal-body');
        if (!modal || !modalBody) return;

        modalBody.innerHTML = buildJobDetailHtml(job, true);
        bindDetailActions(modalBody);
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeDetailModal() {
        const modal = document.getElementById('catalog-detail-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
    }

    /**
     * Ambil detail satu lowongan dari GET /api/jobs/{id} (JH.jobs.show)
     */
    async function loadJobDetail(jobId, openModal = false) {
        if (!jobId) return;
        const detailCard = document.getElementById('catalog-detail-card');

        if (detailCard) {
            detailCard.innerHTML = `
                <div class="py-12 text-center text-on-surface-variant">
                    <span class="material-symbols-outlined text-4xl text-secondary animate-spin">progress_activity</span>
                    <p class="mt-2 text-body-md font-medium">Memuat rincian lowongan #${escapeHtml(jobId)}...</p>
                </div>
            `;
        }

        try {
            const res = await JH.jobs.show(jobId);
            const job = res.data;
            if (!job) throw new Error('Data lowongan kosong.');

            state.selectedJobDetail = job;

            if (detailCard) {
                detailCard.innerHTML = buildJobDetailHtml(job, false);
                bindDetailActions(detailCard);
            }

            if (openModal) {
                openDetailModal(job);
            }
        } catch (err) {
            JH.showError(err, 'Gagal memuat rincian lowongan.');
            if (detailCard) {
                detailCard.innerHTML = `
                    <div class="py-10 text-center space-y-3">
                        <span class="material-symbols-outlined text-4xl text-error">error</span>
                        <p class="text-body-md text-on-surface font-semibold">Gagal memuat detail lowongan.</p>
                        <button type="button" id="retry-detail-btn" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-xs font-semibold">
                            Coba Lagi
                        </button>
                    </div>
                `;
                const retryBtn = document.getElementById('retry-detail-btn');
                if (retryBtn) retryBtn.addEventListener('click', () => loadJobDetail(jobId, openModal));
            }
        }
    }

    /**
     * Ambil daftar lowongan dari GET /api/jobs (JH.jobs.list) sesuai state filter.
     */
    async function fetchJobs() {
        const listEl = document.getElementById('catalog-job-list');
        if (listEl) {
            listEl.innerHTML = `
                <div class="p-space-lg rounded-xl bg-surface-container-lowest border border-outline-variant/60 text-center py-10">
                    <span class="material-symbols-outlined text-3xl text-secondary animate-spin">progress_activity</span>
                    <p class="mt-2 text-body-sm text-on-surface-variant">Menyinkronkan daftar lowongan dari dermaga...</p>
                </div>
            `;
        }

        try {
            // Untuk sort === 'match', kita kirim sort='latest' ke API lalu urutkan di client memakai JH.matchmaking.calculate
            const apiSort = state.sort === 'match' ? 'latest' : state.sort;
            const res = await JH.jobs.list({
                search: state.search || undefined,
                location: state.location || undefined,
                employment_type: state.employment_type || undefined,
                skill: state.skill || undefined,
                sort: apiSort || undefined,
                page: state.page,
                per_page: state.per_page,
            });

            state.jobs = Array.isArray(res.data) ? res.data : [];
            state.pagination = (res.meta && res.meta.pagination) || null;

            renderJobList();
            renderPagination();
        } catch (err) {
            JH.showError(err, 'Gagal memuat daftar lowongan.');
            if (listEl) {
                listEl.innerHTML = `
                    <div class="p-space-xl rounded-xl bg-error-container/40 border border-error/40 text-center space-y-2">
                        <span class="material-symbols-outlined text-3xl text-error">error</span>
                        <h3 class="text-label-md font-bold text-on-surface">Gagal Memuat Katalog</h3>
                        <p class="text-body-sm text-on-surface-variant">${escapeHtml(err.message || 'Terjadi gangguan jaringan.')}</p>
                        <button type="button" id="retry-list-btn" class="px-4 py-2 rounded-lg bg-secondary text-on-secondary text-xs font-semibold">
                            Muat Ulang
                        </button>
                    </div>
                `;
                const retryBtn = document.getElementById('retry-list-btn');
                if (retryBtn) retryBtn.addEventListener('click', fetchJobs);
            }
        }
    }

    function resetAllFilters() {
        state.search = '';
        state.location = '';
        state.employment_type = '';
        state.skill = '';
        state.sort = 'latest';
        state.highMatchOnly = false;
        state.page = 1;

        const searchInput = document.getElementById('filter-search');
        const locSelect = document.getElementById('filter-location');
        const empSelect = document.getElementById('filter-employment-type');
        const sortSelect = document.getElementById('filter-sort');
        const highMatchCheck = document.getElementById('filter-high-match');

        if (searchInput) searchInput.value = '';
        if (locSelect) locSelect.value = '';
        if (empSelect) empSelect.value = '';
        if (sortSelect) sortSelect.value = 'latest';
        if (highMatchCheck) highMatchCheck.checked = false;

        fetchJobs();
    }

    /**
     * Inisialisasi event listener dan sinkronisasi data user pada saat halaman dimuat.
     */
    async function init() {
        // Baca parameter awal dari URL bila ada
        const params = new URLSearchParams(window.location.search);
        if (params.get('search')) state.search = params.get('search');
        if (params.get('location')) state.location = params.get('location');
        if (params.get('employment_type')) state.employment_type = params.get('employment_type');
        if (params.get('skill')) state.skill = params.get('skill');
        if (params.get('job')) state.selectedJobId = Number(params.get('job')) || null;

        const form = document.getElementById('catalog-filter-form');
        const searchInput = document.getElementById('filter-search');
        const locSelect = document.getElementById('filter-location');
        const empSelect = document.getElementById('filter-employment-type');
        const sortSelect = document.getElementById('filter-sort');
        const highMatchCheck = document.getElementById('filter-high-match');
        const resetBtn = document.getElementById('reset-filters-btn');
        const navApplyBtn = document.getElementById('nav-quick-apply-btn');
        const modalCloseBtn = document.getElementById('catalog-modal-close');
        const modalOverlay = document.getElementById('catalog-detail-modal');

        if (searchInput && state.search) searchInput.value = state.search;
        if (locSelect && state.location) locSelect.value = state.location;
        if (empSelect && state.employment_type) empSelect.value = state.employment_type;

        // Segarkan profil user jika sedang login supaya daftar skill selalu paling baru
        if (JH.session.isLoggedIn()) {
            try {
                await JH.auth.me();
                JH.bindSessionUI();
            } catch (e) {
                // Abaikan bila sesi kedaluwarsa, JH.request otomatis membersihkan token
                JH.bindSessionUI();
            }
        }

        // Sesuaikan tombol di navbar bila user adalah recruiter
        const currentUser = JH.session.user();
        if (navApplyBtn) {
            if (currentUser && currentUser.role === 'recruiter') {
                navApplyBtn.textContent = 'Dasbor Recruiter';
            }
            navApplyBtn.addEventListener('click', () => {
                if (currentUser && currentUser.role === 'recruiter') {
                    window.location.href = '/recruiter';
                    return;
                }
                handleApplyJob(state.selectedJobId);
            });
        }

        // Live filter dengan debounce pada input search
        let searchTimer = null;
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    state.search = searchInput.value.trim();
                    state.page = 1;
                    fetchJobs();
                }, 300);
            });
        }

        if (locSelect) {
            locSelect.addEventListener('change', () => {
                state.location = locSelect.value;
                state.page = 1;
                fetchJobs();
            });
        }

        if (empSelect) {
            empSelect.addEventListener('change', () => {
                state.employment_type = empSelect.value;
                state.page = 1;
                fetchJobs();
            });
        }

        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                clearTimeout(searchTimer);
                state.search = searchInput ? searchInput.value.trim() : '';
                state.location = locSelect ? locSelect.value : '';
                state.employment_type = empSelect ? empSelect.value : '';
                state.page = 1;
                fetchJobs();
            });
        }

        // Quick skill chips
        document.querySelectorAll('[data-skill-chip]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const clickedSkill = btn.getAttribute('data-skill-chip') || '';
                state.skill = state.skill.toLowerCase() === clickedSkill.toLowerCase() ? '' : clickedSkill;
                state.page = 1;
                fetchJobs();
            });
        });

        if (resetBtn) {
            resetBtn.addEventListener('click', resetAllFilters);
        }

        if (sortSelect) {
            sortSelect.addEventListener('change', () => {
                state.sort = sortSelect.value;
                state.page = 1;
                fetchJobs();
            });
        }

        if (highMatchCheck) {
            highMatchCheck.addEventListener('change', () => {
                const user = JH.session.user();
                if (highMatchCheck.checked && (!JH.session.isLoggedIn() || !user || user.role === 'recruiter')) {
                    JH.toast('Masuk sebagai Pelamar terlebih dahulu untuk memfilter berdasarkan skor kecocokan.', 'info');
                    highMatchCheck.checked = false;
                    return;
                }
                state.highMatchOnly = highMatchCheck.checked;
                renderJobList();
            });
        }

        // Modal close events
        if (modalCloseBtn) modalCloseBtn.addEventListener('click', closeDetailModal);
        if (modalOverlay) {
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) closeDetailModal();
            });
        }

        await fetchJobs();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})(window, document);
