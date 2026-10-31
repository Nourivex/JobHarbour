/**
 * JobHarbour - Script Halaman Captain's Ledger (Profil Talenta & Kalibrasi Keahlian)
 *
 * Fitur utama:
 * 1. Guard autentikasi & muat profil asli dari GET /api/auth/me
 * 2. Form edit biodata (nama, telepon, lokasi, bio) ke PUT /api/profile
 * 3. Editor kalibrasi keahlian (tambah/hapus chip, anti-duplikat case-insensitive, min 1 skill) ke PUT /api/profile/skills
 * 4. Simulator & rekomendasi lowongan real-time berbasis Weighted Jaccard Similarity (JH.matchmaking.calculate)
 * 5. Statistik profil (jumlah keahlian, jumlah lamaran dari GET /api/applications, rata-rata match score)
 */
(function () {
    'use strict';

    // Pastikan user sudah login sebelum membuka halaman profil
    const sessionUser = JH.requireAuth();
    if (!sessionUser) return;

    // State halaman
    let currentUser = { ...sessionUser };
    let currentSkills = [];
    let savedSkillsSnapshot = '[]';
    let availableJobs = [];
    let userApplications = [];

    // Referensi elemen DOM
    const el = {
        dossierBreadcrumb: document.getElementById('dossier-breadcrumb'),
        navSeafarerBadge: document.getElementById('nav-seafarer-badge'),
        profileRoleBadge: document.getElementById('profile-role-badge'),
        profileDisplayName: document.getElementById('profile-display-name'),
        profileDisplayEmail: document.getElementById('profile-display-email'),
        profileDisplayId: document.getElementById('profile-display-id'),
        profileDisplayLocation: document.getElementById('profile-display-location'),

        statSkillsCount: document.getElementById('stat-skills-count'),
        statApplicationsCount: document.getElementById('stat-applications-count'),
        statAvgScore: document.getElementById('stat-avg-score'),

        biodataForm: document.getElementById('profile-biodata-form'),
        inputName: document.getElementById('input-name'),
        inputEmail: document.getElementById('input-email'),
        inputPhone: document.getElementById('input-phone'),
        inputLocation: document.getElementById('input-location'),
        inputBio: document.getElementById('input-bio'),
        btnSaveBiodata: document.getElementById('btn-save-biodata'),
        btnSaveAll: document.getElementById('btn-save-all'),

        previewLocation: document.getElementById('preview-location'),
        previewEmail: document.getElementById('preview-email'),
        previewPhone: document.getElementById('preview-phone'),
        previewBio: document.getElementById('preview-bio'),

        radarAvgScore: document.getElementById('radar-avg-score'),
        radarHighMatchLabel: document.getElementById('radar-high-match-label'),
        radarTelemetryBar: document.getElementById('radar-telemetry-bar'),
        radarSummaryText: document.getElementById('radar-summary-text'),
        radarRecommendedSkills: document.getElementById('radar-recommended-skills'),

        skillsCountBadge: document.getElementById('skills-count-badge'),
        skillInput: document.getElementById('skill-input'),
        btnAddSkill: document.getElementById('btn-add-skill'),
        btnSaveSkills: document.getElementById('btn-save-skills'),
        btnSaveSkillsInline: document.getElementById('btn-save-skills-inline'),
        skillsDirtyBanner: document.getElementById('skills-dirty-banner'),
        skillsChipsContainer: document.getElementById('skills-chips-container'),

        recommendedJobsContainer: document.getElementById('recommended-jobs-container'),
    };

    /** Normalisasi daftar skill agar selalu array string bersih. */
    function normalizeSkillsList(raw) {
        if (!Array.isArray(raw)) return [];
        const seen = new Set();
        const result = [];
        raw.forEach((item) => {
            const name = String(JH.util.skillName(item) || '').trim();
            if (!name) return;
            const key = name.toLowerCase();
            if (!seen.has(key)) {
                seen.add(key);
                result.push(name);
            }
        });
        return result;
    }

    /** Cek apakah daftar skill di simulator berbeda dengan yang terakhir disimpan di server. */
    function isSkillsDirty() {
        return JSON.stringify(currentSkills) !== savedSkillsSnapshot;
    }

    /** Update tampilan banner indikator perubahan skill. */
    function updateSkillsDirtyUI() {
        if (!el.skillsDirtyBanner) return;
        if (isSkillsDirty()) {
            el.skillsDirtyBanner.classList.remove('hidden');
            el.skillsDirtyBanner.classList.add('flex');
        } else {
            el.skillsDirtyBanner.classList.add('hidden');
            el.skillsDirtyBanner.classList.remove('flex');
        }
    }

    /** Render data profil user ke seluruh elemen header, form, dan pratinjau. */
    function renderProfileDetails(user) {
        if (!user) return;
        const paddedId = String(user.id || 1).padStart(5, '0');
        const roleLabel = user.role === 'recruiter'
            ? 'Recruiter / Harbour Master'
            : user.role === 'admin'
                ? 'Administrator Pelabuhan'
                : 'Siap Berlayar (Pelamar Aktif)';

        if (el.dossierBreadcrumb) {
            el.dossierBreadcrumb.textContent = `SEAFARER DOSSIER // ${(user.name || 'TALENTA').toUpperCase()} (ID #${paddedId}) - VERIFIED TALENT REGISTRY`;
        }
        if (el.navSeafarerBadge) {
            el.navSeafarerBadge.textContent = `SEAFARER #${paddedId} - IMO TECH VERIFIED`;
        }
        if (el.profileRoleBadge) {
            el.profileRoleBadge.textContent = roleLabel;
        }
        if (el.profileDisplayName) {
            el.profileDisplayName.textContent = user.name || '-';
        }
        if (el.profileDisplayEmail) {
            el.profileDisplayEmail.textContent = user.email || '-';
        }
        if (el.profileDisplayId) {
            el.profileDisplayId.textContent = `SEAFARER ID: #${paddedId}`;
        }
        if (el.profileDisplayLocation) {
            el.profileDisplayLocation.textContent = (user.location || 'Lokasi Belum Diatur').toUpperCase();
        }

        // Isi form biodata
        if (el.inputName) el.inputName.value = user.name || '';
        if (el.inputEmail) el.inputEmail.value = user.email || '';
        if (el.inputPhone) el.inputPhone.value = user.phone || '';
        if (el.inputLocation) el.inputLocation.value = user.location || '';
        if (el.inputBio) el.inputBio.value = user.bio || '';

        updateLivePreview();
        JH.bindSessionUI();
    }

    /** Sinkronkan kartu pratinjau dengan nilai di form saat ini. */
    function updateLivePreview() {
        const nameVal = (el.inputName && el.inputName.value.trim()) || currentUser.name || '-';
        const locVal = (el.inputLocation && el.inputLocation.value.trim()) || currentUser.location || 'Belum diatur';
        const phoneVal = (el.inputPhone && el.inputPhone.value.trim()) || currentUser.phone || 'Belum diatur';
        const bioVal = (el.inputBio && el.inputBio.value.trim()) || currentUser.bio || 'Belum ada ringkasan eksekutif. Tambahkan deskripsi singkat mengenai pengalaman dan fokus keahlianmu.';

        if (el.profileDisplayName) el.profileDisplayName.textContent = nameVal;
        if (el.profileDisplayLocation) el.profileDisplayLocation.textContent = locVal.toUpperCase();
        if (el.previewLocation) el.previewLocation.textContent = locVal;
        if (el.previewEmail) el.previewEmail.textContent = currentUser.email || '-';
        if (el.previewPhone) el.previewPhone.textContent = phoneVal;
        if (el.previewBio) el.previewBio.textContent = bioVal;
    }

    /** Render daftar chip keahlian aktif. */
    function renderSkillsChips() {
        const count = currentSkills.length;
        if (el.statSkillsCount) el.statSkillsCount.textContent = String(count);
        if (el.skillsCountBadge) el.skillsCountBadge.textContent = `${count} Skills Terdaftar`;
        updateSkillsDirtyUI();

        if (!el.skillsChipsContainer) return;

        if (count === 0) {
            el.skillsChipsContainer.innerHTML = `
                <div class="w-full p-4 rounded-lg bg-surface-container-low border border-outline-variant/60 text-body-sm text-on-surface-variant">
                    Belum ada keahlian terdaftar. Tambahkan minimal 1 keahlian menggunakan kolom input di atas atau klik saran keahlian dari radar di atas.
                </div>
            `;
            return;
        }

        el.skillsChipsContainer.innerHTML = currentSkills.map((skill, idx) => {
            const safeSkill = JH.util.escapeHtml(skill);
            return `
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-surface-container-low border border-secondary/40 text-body-sm font-body-sm text-on-surface group hover:border-secondary transition-colors">
                    <span class="material-symbols-outlined text-[15px] text-secondary">verified</span>
                    <span class="font-medium">${safeSkill}</span>
                    <button
                        type="button"
                        data-remove-index="${idx}"
                        aria-label="Hapus ${safeSkill}"
                        title="Hapus ${safeSkill}"
                        class="text-outline hover:text-error transition-colors flex items-center ml-0.5"
                    >
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </button>
                </span>
            `;
        }).join('');
    }

    /** Tambah satu atau beberapa skill baru (dipisah koma) ke state currentSkills. */
    function addSkillFromString(rawInput) {
        const parts = String(rawInput || '')
            .split(',')
            .map((s) => s.trim())
            .filter(Boolean);

        if (parts.length === 0) {
            JH.toast('Masukkan nama keahlian terlebih dahulu.', 'warning');
            return false;
        }

        let addedCount = 0;
        let duplicateName = null;

        parts.forEach((candidate) => {
            const exists = currentSkills.some((s) => s.toLowerCase() === candidate.toLowerCase());
            if (exists) {
                duplicateName = candidate;
            } else {
                currentSkills.push(candidate);
                addedCount++;
            }
        });

        if (addedCount === 0 && duplicateName) {
            JH.toast(`Keahlian "${duplicateName}" sudah ada di daftar kamu.`, 'warning');
            return false;
        }

        if (addedCount > 0) {
            renderSkillsChips();
            recalculateMatchmakingAndRender();
            if (parts.length === 1) {
                JH.toast(`Skill "${parts[0]}" ditambahkan ke simulator. Klik Simpan Skills untuk menyimpan permanen.`, 'info', 2500);
            } else {
                JH.toast(`${addedCount} keahlian baru ditambahkan ke simulator.`, 'info', 2500);
            }
        }
        return true;
    }

    /** Hapus skill berdasarkan index, cegah jika tinggal 1 skill. */
    function removeSkillByIndex(index) {
        if (currentSkills.length <= 1) {
            JH.toast('Minimal harus ada 1 keahlian aktif di profil kamu.', 'warning');
            return;
        }
        const removed = currentSkills[index];
        if (removed === undefined) return;
        currentSkills.splice(index, 1);
        renderSkillsChips();
        recalculateMatchmakingAndRender();
    }

    /**
     * Hitung ulang skor kecocokan seluruh lowongan dengan currentSkills secara live,
     * lalu perbarui Radar Telemetry, Statistik Rata-rata Match, Saran Skill, dan Daftar Lowongan.
     */
    function recalculateMatchmakingAndRender() {
        if (!Array.isArray(availableJobs) || availableJobs.length === 0) {
            if (el.statAvgScore) el.statAvgScore.textContent = '0%';
            if (el.radarAvgScore) el.radarAvgScore.textContent = '0%';
            if (el.radarHighMatchLabel) el.radarHighMatchLabel.textContent = '0 Lowongan Aktif';
            if (el.radarSummaryText) {
                el.radarSummaryText.textContent = 'Belum ada lowongan aktif di katalog untuk disimulasikan saat ini.';
            }
            if (el.radarRecommendedSkills) {
                el.radarRecommendedSkills.innerHTML = '<span class="text-body-sm text-on-surface-variant">Tidak ada saran skill tambahan.</span>';
            }
            if (el.recommendedJobsContainer) {
                el.recommendedJobsContainer.innerHTML = `
                    <div class="p-6 text-center text-on-surface-variant bg-surface-container-low/50 rounded-lg border border-outline-variant/50">
                        Belum ada lowongan kerja yang dibuka saat ini.
                    </div>
                `;
            }
            return;
        }

        // Hitung matchmaking tiap lowongan
        const evaluatedJobs = availableJobs.map((job) => {
            const match = JH.matchmaking.calculate(currentSkills, job.requirements || []);
            return {
                job,
                match,
                score: match.score || 0,
            };
        });

        // Urutkan dari skor tertinggi ke terendah
        evaluatedJobs.sort((a, b) => b.score - a.score);

        // Hitung rata-rata skor dan jumlah high match (>= 80%)
        const totalScore = evaluatedJobs.reduce((acc, item) => acc + item.score, 0);
        const avgScore = Math.round(totalScore / evaluatedJobs.length);
        const highMatchCount = evaluatedJobs.filter((item) => item.score >= 80).length;
        const goodMatchCount = evaluatedJobs.filter((item) => item.score >= 50).length;

        if (el.statAvgScore) el.statAvgScore.textContent = `${avgScore}%`;
        if (el.radarAvgScore) el.radarAvgScore.textContent = `${avgScore}%`;
        if (el.radarHighMatchLabel) {
            el.radarHighMatchLabel.textContent = `${highMatchCount} Lowongan Excellent (>=80%) dari ${evaluatedJobs.length} Lowongan`;
        }

        // Update 5-segment telemetry bar berdasarkan avgScore (tiap segmen mewakili 20%)
        if (el.radarTelemetryBar) {
            const segments = el.radarTelemetryBar.querySelectorAll('div > div');
            segments.forEach((bar, i) => {
                const segMin = i * 20;
                const fillPct = Math.max(0, Math.min(100, ((avgScore - segMin) / 20) * 100));
                bar.style.width = `${fillPct}%`;
            });
        }

        // Update teks ringkasan radar
        const topJob = evaluatedJobs[0];
        if (el.radarSummaryText) {
            if (topJob) {
                el.radarSummaryText.innerHTML = `Dengan <strong>${currentSkills.length} keahlian aktif</strong> dan <strong>${userApplications.length} riwayat lamaran</strong>, kecocokan tertinggimu saat ini adalah <strong>${JH.util.escapeHtml(topJob.job.title)}</strong> di <em>${JH.util.escapeHtml(topJob.job.company)}</em> (<strong>${topJob.score}% Match</strong>, ${goodMatchCount} lowongan skor &ge;50%).`;
            } else {
                el.radarSummaryText.textContent = `Dengan ${currentSkills.length} keahlian aktif, rata-rata kecocokanmu adalah ${avgScore}%.`;
            }
        }

        // Kumpulkan saran missing_skills paling berdampak di seluruh lowongan
        renderRadarMissingSkillSuggestions(avgScore);

        // Render daftar kartu rekomendasi lowongan
        renderRecommendedJobs(evaluatedJobs);
    }

    /**
     * Hitung potensi kenaikan rata-rata skor untuk setiap missing skill,
     * lalu tampilkan sebagai tombol interaktif di Radar Simulator.
     */
    function renderRadarMissingSkillSuggestions(currentAvgScore) {
        if (!el.radarRecommendedSkills) return;

        // Kumpulkan semua missing skill unik dari seluruh lowongan
        const missingMap = new Map();
        availableJobs.forEach((job) => {
            const m = JH.matchmaking.calculate(currentSkills, job.requirements || []);
            (m.missing_skills || []).forEach((skill) => {
                const key = String(skill).toLowerCase().trim();
                if (!key) return;
                if (!missingMap.has(key)) {
                    missingMap.set(key, { name: skill, count: 0 });
                }
                missingMap.get(key).count += 1;
            });
        });

        if (missingMap.size === 0) {
            el.radarRecommendedSkills.innerHTML = `
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-200 text-label-sm text-emerald-800">
                    <span class="material-symbols-outlined text-[16px]">verified</span>
                    Luar biasa! Keahlianmu sudah mencakup seluruh kualifikasi lowongan aktif di katalog.
                </span>
            `;
            return;
        }

        // Hitung simulasi kenaikan skor jika skill tersebut ditambahkan
        const suggestions = Array.from(missingMap.values()).map((item) => {
            const simulatedSkills = [...currentSkills, item.name];
            const simTotal = availableJobs.reduce((acc, job) => {
                return acc + (JH.matchmaking.calculate(simulatedSkills, job.requirements || []).score || 0);
            }, 0);
            const simAvg = Math.round(simTotal / availableJobs.length);
            const delta = Math.max(1, simAvg - currentAvgScore);
            return {
                name: item.name,
                jobCount: item.count,
                delta,
            };
        });

        suggestions.sort((a, b) => b.delta - a.delta || b.jobCount - a.jobCount);
        const topSuggestions = suggestions.slice(0, 6);

        el.radarRecommendedSkills.innerHTML = topSuggestions.map((item) => {
            const safeName = JH.util.escapeHtml(item.name);
            return `
                <button
                    type="button"
                    data-add-skill="${safeName}"
                    class="group inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-surface-container-low hover:bg-secondary-fixed/50 border border-outline-variant hover:border-secondary transition-all text-label-sm font-label-sm text-on-surface active:scale-95"
                >
                    <span class="material-symbols-outlined text-[15px] text-secondary group-hover:rotate-90 transition-transform">add</span>
                    <span>${safeName}</span>
                    <span class="text-[11px] font-bold text-secondary bg-surface-container-lowest px-1.5 py-0.5 rounded border border-secondary/30">+${item.delta}% Avg (${item.jobCount} Job)</span>
                </button>
            `;
        }).join('');
    }

    /** Render kartu lowongan terbaik beserta rincian matched_skills, missing_skills, dan tombol Lamar. */
    function renderRecommendedJobs(evaluatedJobs) {
        if (!el.recommendedJobsContainer) return;

        // Tampilkan hingga 6 lowongan teratas
        const topList = evaluatedJobs.slice(0, 6);

        // Cek apakah user sudah pernah melamar lowongan tertentu
        const appliedJobIds = new Set(
            (userApplications || [])
                .filter((a) => a.current_stage !== 'canceled')
                .map((a) => Number(a.job_id))
        );

        el.recommendedJobsContainer.innerHTML = topList.map(({ job, match, score }) => {
            const tone = JH.util.scoreTone(score);
            const empLabel = JH.util.EMPLOYMENT_LABELS[job.employment_type] || job.employment_type || 'Full-time';
            const salaryText = JH.util.formatSalaryRange(job.salary_min, job.salary_max);
            const alreadyApplied = appliedJobIds.has(Number(job.id));

            const categoryLabel = match.category === 'excellent'
                ? 'Excellent Match'
                : match.category === 'good'
                    ? 'Good Match'
                    : 'Perlu Kalibrasi Skill';

            const matchedHtml = (match.matched_skills || []).length > 0
                ? match.matched_skills.map((s) => `
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-medium">
                        <span class="material-symbols-outlined text-[13px]">check_circle</span>
                        ${JH.util.escapeHtml(s)}
                    </span>
                `).join('')
                : '<span class="text-body-sm text-outline italic">Belum ada skill yang cocok</span>';

            const missingHtml = (match.missing_skills || []).length > 0
                ? match.missing_skills.map((s) => {
                    const safeSkill = JH.util.escapeHtml(s);
                    return `
                        <button
                            type="button"
                            data-add-skill="${safeSkill}"
                            title="Klik untuk menambahkan ${safeSkill} ke profilmu"
                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-50 hover:bg-secondary-fixed/60 border border-amber-300 hover:border-secondary text-amber-900 hover:text-on-secondary-fixed text-[11px] font-medium transition-colors"
                        >
                            <span class="material-symbols-outlined text-[13px]">add_circle</span>
                            <span>${safeSkill}</span>
                        </button>
                    `;
                }).join('')
                : '<span class="text-[11px] font-semibold text-emerald-700">Semua syarat keahlian terpenuhi (100%)!</span>';

            const actionBtnHtml = alreadyApplied
                ? `
                    <a href="/my-dock" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-surface-container-low border border-outline-variant text-on-surface text-label-sm font-semibold hover:border-secondary transition-colors shrink-0">
                        <span class="material-symbols-outlined text-[16px] text-secondary">task_alt</span>
                        <span>Sudah Dilamar (Cek Dock)</span>
                    </a>
                `
                : `
                    <a href="/apply?job=${encodeURIComponent(job.id)}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-secondary hover:bg-on-secondary-container text-on-secondary text-label-sm font-semibold transition-all active:scale-95 shadow-sm shrink-0">
                        <span>Lamar Sekarang</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                `;

            return `
                <div class="p-4 rounded-xl bg-surface-container-low/40 border border-outline-variant/70 hover:border-secondary/60 transition-all space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="text-coordinate-tag font-coordinate-tag uppercase px-2 py-0.5 rounded bg-surface-container-highest text-on-surface-variant">${JH.util.escapeHtml(empLabel)}</span>
                                <span class="text-coordinate-tag font-coordinate-tag text-outline">${JH.util.escapeHtml(job.location || '-')}</span>
                                <span class="text-coordinate-tag font-coordinate-tag text-secondary font-semibold">${JH.util.escapeHtml(salaryText)}</span>
                            </div>
                            <h4 class="text-headline-sm font-headline-sm text-on-surface font-bold truncate">${JH.util.escapeHtml(job.title)}</h4>
                            <p class="text-body-sm text-on-surface-variant font-medium">${JH.util.escapeHtml(job.company)}</p>
                        </div>

                        <div class="flex sm:flex-col items-center sm:items-end justify-between gap-1 shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border ${tone.bg} ${tone.text} ${tone.border} text-label-md font-bold">
                                <span class="material-symbols-outlined text-[16px]">analytics</span>
                                ${score}% Match
                            </span>
                            <span class="text-coordinate-tag font-coordinate-tag text-outline uppercase">${categoryLabel}</span>
                        </div>
                    </div>

                    <!-- Progress bar skor -->
                    <div class="w-full h-2 bg-surface-container-highest rounded-full overflow-hidden">
                        <div class="h-full ${tone.bar} transition-all duration-300" style="width: ${Math.min(100, Math.max(0, score))}%"></div>
                    </div>

                    <!-- Rincian Skill Cocok & Skill Kurang -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                        <div class="bg-surface-container-lowest p-2.5 rounded-lg border border-outline-variant/50">
                            <p class="text-coordinate-tag font-coordinate-tag text-outline uppercase mb-1.5">
                                SKILL COCOK (${(match.matched_skills || []).length}/${(job.requirements || []).length})
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                ${matchedHtml}
                            </div>
                        </div>
                        <div class="bg-surface-container-lowest p-2.5 rounded-lg border border-outline-variant/50">
                            <p class="text-coordinate-tag font-coordinate-tag text-outline uppercase mb-1.5">
                                SARAN SKILL TAMBAHAN (KLIK UNTUK TAMBAH)
                            </p>
                            <div class="flex flex-wrap gap-1.5">
                                ${missingHtml}
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-3 pt-2 border-t border-outline-variant/40">
                        <span class="text-coordinate-tag font-coordinate-tag text-outline">
                            BOBOT TERPENUHI: ${match.calculation_details.matched_weight} / ${match.calculation_details.total_weight} POIN
                        </span>
                        ${actionBtnHtml}
                    </div>
                </div>
            `;
        }).join('');
    }

    /** Simpan perubahan biodata ke PUT /api/profile. */
    async function saveBiodata(e) {
        if (e) e.preventDefault();
        const name = el.inputName ? el.inputName.value.trim() : '';
        const phone = el.inputPhone ? el.inputPhone.value.trim() : '';
        const location = el.inputLocation ? el.inputLocation.value.trim() : '';
        const bio = el.inputBio ? el.inputBio.value.trim() : '';

        if (!name) {
            JH.toast('Nama lengkap wajib diisi.', 'warning');
            if (el.inputName) el.inputName.focus();
            return false;
        }

        JH.setLoading(el.btnSaveBiodata, true, 'Menyimpan...');
        try {
            const res = await JH.profile.update({
                name,
                phone,
                location,
                bio,
                skills: currentSkills,
            });
            currentUser = { ...currentUser, ...(res.data || {}) };
            currentSkills = normalizeSkillsList(currentUser.skills || currentSkills);
            savedSkillsSnapshot = JSON.stringify(currentSkills);
            JH.session.setUser(currentUser);
            renderProfileDetails(currentUser);
            renderSkillsChips();
            recalculateMatchmakingAndRender();
            JH.toast('Biodata dan profil berhasil diperbarui!', 'success');
            return true;
        } catch (err) {
            JH.showError(err, 'Gagal memperbarui biodata profil.');
            return false;
        } finally {
            JH.setLoading(el.btnSaveBiodata, false);
        }
    }

    /** Simpan daftar keahlian ke PUT /api/profile/skills. */
    async function saveSkills() {
        if (currentSkills.length < 1) {
            JH.toast('Minimal harus ada 1 keahlian sebelum disimpan.', 'warning');
            return false;
        }

        JH.setLoading(el.btnSaveSkills, true, 'Menyimpan...');
        try {
            const res = await JH.profile.updateSkills(currentSkills);
            const updatedSkills = normalizeSkillsList((res.data && res.data.skills) || currentSkills);
            currentSkills = updatedSkills;
            savedSkillsSnapshot = JSON.stringify(currentSkills);

            currentUser = { ...currentUser, skills: currentSkills };
            JH.session.setUser(currentUser);

            renderSkillsChips();
            recalculateMatchmakingAndRender();
            JH.toast('Matriks keahlian (skills) berhasil disimpan ke server!', 'success');
            return true;
        } catch (err) {
            JH.showError(err, 'Gagal menyimpan daftar keahlian.');
            return false;
        } finally {
            JH.setLoading(el.btnSaveSkills, false);
        }
    }

    /** Simpan biodata + keahlian sekaligus saat tombol utama di header diklik. */
    async function saveAllProfile() {
        if (currentSkills.length < 1) {
            JH.toast('Minimal harus ada 1 keahlian aktif di profil kamu.', 'warning');
            return;
        }
        JH.setLoading(el.btnSaveAll, true, 'Menyimpan Profil...');
        try {
            await JH.profile.updateSkills(currentSkills);
            const res = await JH.profile.update({
                name: el.inputName ? el.inputName.value.trim() : currentUser.name,
                phone: el.inputPhone ? el.inputPhone.value.trim() : currentUser.phone,
                location: el.inputLocation ? el.inputLocation.value.trim() : currentUser.location,
                bio: el.inputBio ? el.inputBio.value.trim() : currentUser.bio,
                skills: currentSkills,
            });
            currentUser = { ...currentUser, ...(res.data || {}), skills: currentSkills };
            savedSkillsSnapshot = JSON.stringify(currentSkills);
            JH.session.setUser(currentUser);

            renderProfileDetails(currentUser);
            renderSkillsChips();
            recalculateMatchmakingAndRender();
            JH.toast('Seluruh pembaruan profil dan matriks keahlian berhasil disimpan!', 'success');
        } catch (err) {
            JH.showError(err, 'Gagal menyimpan pembaruan profil.');
        } finally {
            JH.setLoading(el.btnSaveAll, false);
        }
    }

    /** Muat seluruh data awal halaman dari backend REST API. */
    async function initPage() {
        // Render awal dari session cache supaya responsif
        currentSkills = normalizeSkillsList(currentUser.skills || []);
        savedSkillsSnapshot = JSON.stringify(currentSkills);
        renderProfileDetails(currentUser);
        renderSkillsChips();

        // Ambil data terbaru secara paralel: GET /api/auth/me, GET /api/jobs, GET /api/applications
        const [meResult, jobsResult, appsResult] = await Promise.allSettled([
            JH.auth.me(),
            JH.jobs.list({ per_page: 20 }),
            JH.applications.list(),
        ]);

        if (meResult.status === 'fulfilled' && meResult.value) {
            currentUser = meResult.value;
            currentSkills = normalizeSkillsList(currentUser.skills || []);
            savedSkillsSnapshot = JSON.stringify(currentSkills);
            renderProfileDetails(currentUser);
            renderSkillsChips();
        } else if (meResult.status === 'rejected') {
            JH.showError(meResult.reason, 'Gagal memuat profil pengguna dari server.');
        }

        if (jobsResult.status === 'fulfilled' && jobsResult.value) {
            availableJobs = Array.isArray(jobsResult.value.data) ? jobsResult.value.data : [];
        } else if (jobsResult.status === 'rejected') {
            JH.showError(jobsResult.reason, 'Gagal memuat daftar lowongan untuk simulasi.');
        }

        if (appsResult.status === 'fulfilled' && appsResult.value) {
            userApplications = Array.isArray(appsResult.value.data) ? appsResult.value.data : [];
            if (el.statApplicationsCount) {
                el.statApplicationsCount.textContent = String(userApplications.length);
            }
        }

        recalculateMatchmakingAndRender();
    }

    // Event Listeners
    if (el.biodataForm) {
        el.biodataForm.addEventListener('submit', saveBiodata);
        ['input', 'change'].forEach((evt) => {
            el.biodataForm.addEventListener(evt, updateLivePreview);
        });
    }

    if (el.btnAddSkill && el.skillInput) {
        el.btnAddSkill.addEventListener('click', () => {
            if (addSkillFromString(el.skillInput.value)) {
                el.skillInput.value = '';
                el.skillInput.focus();
            }
        });

        el.skillInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (addSkillFromString(el.skillInput.value)) {
                    el.skillInput.value = '';
                }
            }
        });
    }

    if (el.skillsChipsContainer) {
        el.skillsChipsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-remove-index]');
            if (!btn) return;
            const idx = Number(btn.getAttribute('data-remove-index'));
            if (!Number.isNaN(idx)) {
                removeSkillByIndex(idx);
            }
        });
    }

    // Delegasi klik untuk tombol saran skill di Radar maupun di Kartu Rekomendasi Lowongan
    document.addEventListener('click', (e) => {
        const addBtn = e.target.closest('[data-add-skill]');
        if (!addBtn) return;
        const skillToAdd = addBtn.getAttribute('data-add-skill');
        if (skillToAdd) {
            addSkillFromString(skillToAdd);
        }
    });

    if (el.btnSaveSkills) {
        el.btnSaveSkills.addEventListener('click', saveSkills);
    }
    if (el.btnSaveSkillsInline) {
        el.btnSaveSkillsInline.addEventListener('click', saveSkills);
    }
    if (el.btnSaveAll) {
        el.btnSaveAll.addEventListener('click', saveAllProfile);
    }

    // Jalankan inisialisasi
    initPage();
})();
