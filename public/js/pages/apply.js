/**
 * Halaman Embarkation Dock / Quick Apply (/apply?job=<id>)
 *
 * Fitur utama:
 * 1. Guard autentikasi khusus role applicant.
 * 2. Ambil parameter ?job=<id>, atau otomatis pilih lowongan pertama dari daftar lowongan aktif.
 * 3. Muat detail lowongan (GET /api/jobs/{id}) dan tampilkan syarat keahlian beserta bobotnya.
 * 4. Prefill skill pelamar dari profil (GET /api/auth/me) dengan fitur tambah/hapus chip skill.
 * 5. Simulasi skor kecocokan LIVE di sisi client memakai Weighted Jaccard Similarity (JH.matchmaking.calculate).
 * 6. Input cover letter dan unggah berkas CV PDF (klik & drag-drop, validasi PDF maks 2MB).
 * 7. Kirim lamaran via POST /api/applications dan tangani status 201, 409, 400, serta 422.
 */
(function (window, document) {
    'use strict';

    const MAX_CV_BYTES = 2 * 1024 * 1024; // 2 MB

    const state = {
        user: null,
        profileSkills: [],
        candidateSkills: [],
        currentJob: null,
        jobsList: [],
        cvFile: null,
        submitting: false,
        lastSubmittedAppId: null,
    };

    const CATEGORY_META = {
        excellent: {
            label: 'Anchor Fit - Sangat Cocok (Excellent)',
            badgeClass: 'bg-emerald-100 text-emerald-800 border-emerald-300',
            dotClass: 'bg-emerald-500',
            circleClass: 'bg-emerald-50 border-emerald-600 text-emerald-700',
            telemetry: 'TELEMETRY PASS (AUTO-SCREENING)',
        },
        good: {
            label: 'Sea Ready - Cukup Cocok (Good)',
            badgeClass: 'bg-sky-100 text-sky-800 border-sky-300',
            dotClass: 'bg-sky-500',
            circleClass: 'bg-sky-50 border-[#0284C7] text-[#0284C7]',
            telemetry: 'TELEMETRY REVIEW',
        },
        under_qualified: {
            label: 'Perlu Penguatan Keahlian (Under Qualified)',
            badgeClass: 'bg-amber-100 text-amber-800 border-amber-300',
            dotClass: 'bg-amber-500',
            circleClass: 'bg-amber-50 border-amber-500 text-amber-700',
            telemetry: 'MANUAL REVIEW',
        },
    };

    const LEVEL_META = {
        required: { label: 'Wajib', weight: 3, badge: 'bg-rose-50 text-rose-700 border-rose-200' },
        important: { label: 'Penting', weight: 2, badge: 'bg-sky-50 text-sky-700 border-sky-200' },
        nice_to_have: { label: 'Nilai Tambah', weight: 1, badge: 'bg-slate-100 text-slate-700 border-slate-200' },
    };

    function el(id) {
        return document.getElementById(id);
    }

    function normalizeSkills(rawSkills) {
        if (!Array.isArray(rawSkills)) return [];
        const seen = new Set();
        const result = [];
        rawSkills.forEach((item) => {
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

    function formatFileSize(bytes) {
        const n = Number(bytes) || 0;
        if (n >= 1024 * 1024) {
            return (n / (1024 * 1024)).toFixed(2) + ' MB';
        }
        return Math.max(1, Math.round(n / 1024)) + ' KB';
    }

    function setAlertBanner(type, htmlContent) {
        const box = el('apply-status-alert');
        if (!box) return;
        if (!htmlContent) {
            box.className = 'hidden rounded-xl p-4 border shadow-xs';
            box.innerHTML = '';
            return;
        }
        const styles = {
            conflict: 'bg-amber-50 border-amber-300 text-amber-950',
            error: 'bg-rose-50 border-rose-300 text-rose-950',
            info: 'bg-sky-50 border-sky-300 text-sky-950',
        };
        box.className = `rounded-xl p-4 border shadow-xs ${styles[type] || styles.info}`;
        box.innerHTML = htmlContent;
    }

    function renderApplicantInfo(user) {
        if (!user) return;
        if (el('applicant-verify-name')) el('applicant-verify-name').textContent = user.name || '-';
        if (el('applicant-verify-id')) el('applicant-verify-id').textContent = 'SEAFARER ID #' + String(user.id || 0).padStart(4, '0');
        if (el('applicant-verify-email')) el('applicant-verify-email').textContent = user.email || '-';
        if (el('applicant-verify-phone')) el('applicant-verify-phone').textContent = user.phone || 'Belum diisi di profil';
        if (el('applicant-verify-location')) el('applicant-verify-location').textContent = user.location || 'Tegal, Jawa Tengah';
    }

    function renderJobSwitcher() {
        const select = el('apply-job-select');
        if (!select) return;

        if (!state.jobsList.length) {
            select.innerHTML = '<option value="">Tidak ada lowongan tersedia</option>';
            return;
        }

        const esc = JH.util.escapeHtml;
        select.innerHTML = state.jobsList.map((job) => {
            const selected = state.currentJob && Number(state.currentJob.id) === Number(job.id) ? 'selected' : '';
            const statusText = job.status === 'open' ? '' : ' (Ditutup)';
            return `<option value="${esc(job.id)}" ${selected}>${esc(job.title)} - ${esc(job.company)}${statusText}</option>`;
        }).join('');
    }

    function renderJobHeader(job) {
        if (!job) return;
        const empLabel = JH.util.EMPLOYMENT_LABELS[job.employment_type] || job.employment_type || 'Full-time';
        const statusBadge = job.status === 'open' ? `${empLabel.toUpperCase()} • OPEN` : 'LOWONGAN DITUTUP';

        if (el('apply-job-badge')) el('apply-job-badge').textContent = statusBadge;
        if (el('apply-job-code')) el('apply-job-code').textContent = 'REG#' + String(job.id).padStart(4, '0') + '-JH';
        if (el('apply-job-salary')) el('apply-job-salary').textContent = JH.util.formatSalaryRange(job.salary_min, job.salary_max);
        if (el('apply-job-title')) el('apply-job-title').textContent = job.title || '-';
        if (el('apply-job-company')) el('apply-job-company').textContent = job.company || '-';
        if (el('apply-job-location')) el('apply-job-location').textContent = job.location || '-';
        if (el('apply-footer-note')) {
            el('apply-footer-note').textContent = `Dengan menekan kirim, lamaranmu akan masuk ke tahap 1 (Screening) pada sistem rekrutmen ${job.company || 'perusahaan'}.`;
        }
    }

    function renderCandidateSkillChips() {
        const container = el('candidate-skills-chips');
        if (!container) return;

        const esc = JH.util.escapeHtml;
        if (!state.candidateSkills.length) {
            container.innerHTML = '<span class="text-xs text-slate-400 italic py-1">Belum ada keahlian dipilih. Tambahkan keahlian di bawah atau klik dari daftar syarat lowongan.</span>';
            return;
        }

        container.innerHTML = state.candidateSkills.map((skill, idx) => `
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-sky-100 text-[#0284C7] border border-sky-200">
                <span>${esc(skill)}</span>
                <button type="button" data-remove-skill-index="${idx}" class="text-sky-600 hover:text-rose-600 focus:outline-none leading-none" title="Hapus keahlian">
                    <span class="material-symbols-outlined text-[14px]">close</span>
                </button>
            </span>
        `).join('');
    }

    function updateLiveMatchmaking() {
        const job = state.currentJob;
        if (!job) return;

        const requirements = Array.isArray(job.requirements) ? job.requirements : [];
        const result = JH.matchmaking.calculate(state.candidateSkills, requirements);
        const scoreNum = Math.round(Number(result.score) || 0);
        const meta = CATEGORY_META[result.category] || CATEGORY_META.under_qualified;
        const esc = JH.util.escapeHtml;

        if (el('live-score-value')) el('live-score-value').textContent = `${result.score}%`;
        if (el('live-score-circle')) {
            el('live-score-circle').className = `w-14 h-14 rounded-full border-2 flex flex-col items-center justify-center text-center shadow-sm transition-colors ${meta.circleClass}`;
            el('live-score-value').className = 'font-headline-sm text-base font-extrabold leading-none';
        }

        if (el('live-category-badge')) {
            el('live-category-badge').className = `inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold border ${meta.badgeClass}`;
        }
        if (el('live-category-dot')) {
            el('live-category-dot').className = `w-2 h-2 rounded-full animate-pulse ${meta.dotClass}`;
        }
        if (el('live-category-text')) el('live-category-text').textContent = meta.label;
        if (el('live-telemetry-tag')) el('live-telemetry-tag').textContent = meta.telemetry;

        const applicantName = (state.user && state.user.name) ? state.user.name : 'Pelamar';
        if (el('live-score-desc')) {
            if (scoreNum >= 80) {
                el('live-score-desc').innerHTML = `Profil keahlian <strong>${esc(applicantName)}</strong> memenuhi standar utama <strong>${esc(job.company)}</strong> dan berhak lolos otomatis (passed) di tahap Screening awal.`;
            } else if (scoreNum >= 50) {
                el('live-score-desc').innerHTML = `Profil keahlian <strong>${esc(applicantName)}</strong> cukup relevan untuk <strong>${esc(job.company)}</strong> dan akan masuk antrean ulasan rekruter (in_progress).`;
            } else {
                el('live-score-desc').innerHTML = `Skor kecocokan saat ini masih di bawah 50%. Tambahkan keahlian yang sesuai bila kamu memilikinya sebelum mengirim lamaran.`;
            }
        }

        const matchedWeight = result.calculation_details ? result.calculation_details.matched_weight : 0;
        const totalWeight = result.calculation_details ? result.calculation_details.total_weight : 0;
        if (el('live-weight-info')) {
            el('live-weight-info').textContent = `Bobot: ${matchedWeight} / ${totalWeight} poin`;
        }

        const matched = result.matched_skills || [];
        const missing = result.missing_skills || [];

        if (el('live-matched-summary')) {
            el('live-matched-summary').textContent = `${matched.length} dari ${requirements.length} Keahlian Cocok`;
        }
        if (el('live-matched-list')) {
            el('live-matched-list').textContent = matched.length ? matched.join(', ') : 'Belum ada keahlian yang cocok';
        }

        if (el('live-missing-summary')) {
            el('live-missing-summary').textContent = missing.length
                ? `${missing.length} Keahlian Belum Terpenuhi`
                : 'Semua Keahlian Lowongan Terpenuhi!';
        }
        if (el('live-missing-list')) {
            el('live-missing-list').textContent = missing.length ? missing.join(', ') : 'Tidak ada keahlian yang kurang';
        }

        // Render daftar requirement beserta bobot & status cocok/tidaknya
        const reqContainer = el('job-requirements-list');
        if (reqContainer) {
            if (!requirements.length) {
                reqContainer.innerHTML = '<span class="text-xs text-slate-400 italic">Lowongan ini tidak mencantumkan syarat keahlian khusus.</span>';
            } else {
                const candidateLower = state.candidateSkills.map((s) => s.toLowerCase().trim());
                reqContainer.innerHTML = requirements.map((req) => {
                    const skillName = JH.util.skillName(req);
                    const rawLevel = (req && req.level ? String(req.level) : 'required').toLowerCase().replace(/-/g, '_');
                    const lvl = LEVEL_META[rawLevel] || LEVEL_META.required;
                    const weight = (req && req.weight) ? Number(req.weight) : lvl.weight;
                    const isMatched = candidateLower.includes(skillName.toLowerCase().trim());

                    if (isMatched) {
                        return `
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-800 border border-emerald-300">
                                <span class="material-symbols-outlined text-[14px] text-emerald-600" style="font-variation-settings:'FILL' 1;">check_circle</span>
                                <span class="font-semibold">${esc(skillName)}</span>
                                <span class="text-[10px] opacity-75">(${esc(lvl.label)} • ${weight}p)</span>
                            </span>
                        `;
                    }
                    return `
                        <button type="button" data-quick-add-skill="${esc(skillName)}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-white hover:bg-sky-50 text-slate-700 border border-dashed border-slate-300 hover:border-sky-400 transition-colors" title="Klik untuk menambahkan ke keahlian kamu">
                            <span class="material-symbols-outlined text-[14px] text-slate-400">add_circle</span>
                            <span class="font-semibold">${esc(skillName)}</span>
                            <span class="text-[10px] text-slate-500">(${esc(lvl.label)} • ${weight}p)</span>
                        </button>
                    `;
                }).join('');
            }
        }
    }

    function addCandidateSkill(rawInput) {
        if (!rawInput) return;
        const parts = String(rawInput).split(',').map((s) => s.trim()).filter(Boolean);
        if (!parts.length) return;

        const existingLower = new Set(state.candidateSkills.map((s) => s.toLowerCase()));
        let added = 0;
        parts.forEach((part) => {
            if (!existingLower.has(part.toLowerCase())) {
                existingLower.add(part.toLowerCase());
                state.candidateSkills.push(part);
                added++;
            }
        });

        if (added > 0) {
            renderCandidateSkillChips();
            updateLiveMatchmaking();
        }
    }

    function removeCandidateSkillAt(index) {
        const i = Number(index);
        if (isNaN(i) || i < 0 || i >= state.candidateSkills.length) return;
        state.candidateSkills.splice(i, 1);
        renderCandidateSkillChips();
        updateLiveMatchmaking();
    }

    function handleCvFileSelection(file) {
        const errorEl = el('cv-file-error');
        const previewEl = el('cv-file-preview');
        const inputEl = el('cv-file-input');

        if (errorEl) {
            errorEl.classList.add('hidden');
            errorEl.textContent = '';
        }

        if (!file) {
            state.cvFile = null;
            if (previewEl) previewEl.classList.add('hidden');
            if (inputEl) inputEl.value = '';
            return;
        }

        const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name || '');
        if (!isPdf) {
            const msg = 'Berkas CV harus berformat PDF (.pdf).';
            if (errorEl) {
                errorEl.textContent = msg;
                errorEl.classList.remove('hidden');
            }
            JH.toast(msg, 'error');
            if (inputEl) inputEl.value = '';
            return;
        }

        if (file.size > MAX_CV_BYTES) {
            const msg = `Ukuran berkas CV (${formatFileSize(file.size)}) melebihi batas maksimal 2 MB.`;
            if (errorEl) {
                errorEl.textContent = msg;
                errorEl.classList.remove('hidden');
            }
            JH.toast(msg, 'error');
            if (inputEl) inputEl.value = '';
            return;
        }

        state.cvFile = file;
        if (el('cv-file-name')) el('cv-file-name').textContent = file.name;
        if (el('cv-file-meta')) el('cv-file-meta').textContent = `${formatFileSize(file.size)} • Siap dikirim bersama lamaran`;
        if (previewEl) previewEl.classList.remove('hidden');
    }

    async function loadJobDetail(jobId) {
        const loadingEl = el('apply-loading-state');
        const formEl = el('apply-form');
        const successEl = el('apply-success-view');
        const footerEl = el('apply-modal-footer');
        const submitBtn = el('btn-submit-apply');

        setAlertBanner(null, '');
        if (successEl) successEl.classList.add('hidden');
        if (footerEl) footerEl.classList.remove('hidden');

        if (!jobId) {
            if (loadingEl) loadingEl.classList.add('hidden');
            if (formEl) formEl.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = true;
            setAlertBanner('info', `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="font-bold text-sm">Pilih Lowongan Terlebih Dahulu</h4>
                        <p class="text-xs mt-0.5">Silakan pilih lowongan dari menu di atas atau telusuri katalog lowongan aktif di The Anchorage.</p>
                    </div>
                    <a href="/catalog" class="px-4 py-2 rounded-lg bg-[#0284C7] text-white text-xs font-semibold whitespace-nowrap text-center">Buka Katalog Lowongan</a>
                </div>
            `);
            return;
        }

        if (loadingEl) loadingEl.classList.remove('hidden');
        if (formEl) formEl.classList.add('hidden');

        try {
            const res = await JH.jobs.show(jobId);
            const job = res.data;
            state.currentJob = job;

            // Sinkronkan URL query ?job=<id> tanpa reload halaman
            const nextUrl = new URL(window.location.href);
            nextUrl.searchParams.set('job', job.id);
            window.history.replaceState({}, '', nextUrl.toString());

            renderJobSwitcher();
            renderJobHeader(job);
            renderCandidateSkillChips();
            updateLiveMatchmaking();

            // Jika cover letter masih kosong, isi template ramah
            const coverInput = el('cover-letter');
            if (coverInput && !coverInput.dataset.userEdited) {
                coverInput.value = `Halo Tim Rekrutmen ${job.company}, saya tertarik melamar posisi ${job.title} dan siap berkontribusi sesuai keahlian teknis yang saya miliki.`;
                if (el('cover-letter-count')) {
                    el('cover-letter-count').textContent = `${coverInput.value.length} / 1000`;
                }
            }

            if (job.status !== 'open') {
                setAlertBanner('error', `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h4 class="font-bold text-sm">Lowongan Ini Sudah Ditutup</h4>
                            <p class="text-xs mt-0.5">Posisi <strong>${JH.util.escapeHtml(job.title)}</strong> sudah tidak menerima lamaran baru. Silakan pilih lowongan lain.</p>
                        </div>
                        <a href="/catalog" class="px-4 py-2 rounded-lg bg-rose-700 text-white text-xs font-semibold whitespace-nowrap text-center">Cari Lowongan Lain</a>
                    </div>
                `);
                if (submitBtn) submitBtn.disabled = true;
            } else {
                if (submitBtn) submitBtn.disabled = false;
            }

            if (loadingEl) loadingEl.classList.add('hidden');
            if (formEl) formEl.classList.remove('hidden');
        } catch (err) {
            if (loadingEl) loadingEl.classList.add('hidden');
            JH.showError(err, 'Gagal memuat detail lowongan.');
            setAlertBanner('error', `
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h4 class="font-bold text-sm">Lowongan Tidak Ditemukan</h4>
                        <p class="text-xs mt-0.5">${JH.util.escapeHtml(err.message || 'Data lowongan tidak dapat dimuat.')}</p>
                    </div>
                    <a href="/catalog" class="px-4 py-2 rounded-lg bg-[#0284C7] text-white text-xs font-semibold whitespace-nowrap text-center">Kembali ke Katalog</a>
                </div>
            `);
        }
    }

    function renderSuccessView(data) {
        const formEl = el('apply-form');
        const footerEl = el('apply-modal-footer');
        const successEl = el('apply-success-view');
        if (formEl) formEl.classList.add('hidden');
        if (footerEl) footerEl.classList.add('hidden');
        setAlertBanner(null, '');

        const match = data.matchmaking_result || {};
        const pipeline = data.pipeline_status || {};
        const job = data.job || state.currentJob || {};
        const esc = JH.util.escapeHtml;

        state.lastSubmittedAppId = data.application_id;

        if (el('success-app-id-badge')) {
            el('success-app-id-badge').textContent = `LAMARAN #${data.application_id || '-'} TERKIRIM`;
        }
        if (el('success-headline')) {
            el('success-headline').textContent = `Lamaran untuk ${job.title || 'Posisi'} Berhasil Dikirim!`;
        }
        if (el('success-subtext')) {
            el('success-subtext').textContent = `Berkas lamaran kamu telah diterima oleh ${job.company || 'perusahaan'} dan dianalisis oleh Mesin Pemadanan Keahlian Otomatis.`;
        }

        const score = match.match_score !== undefined ? match.match_score : 0;
        const category = match.match_category || 'under_qualified';
        const catMeta = CATEGORY_META[category] || CATEGORY_META.under_qualified;

        if (el('success-score-value')) el('success-score-value').textContent = `${score}%`;
        if (el('success-score-category')) {
            el('success-score-category').textContent = catMeta.label;
            el('success-score-category').className = `px-2.5 py-0.5 rounded-full text-xs font-semibold border ${catMeta.badgeClass}`;
        }

        const details = match.calculation_details || {};
        if (el('success-weight-detail')) {
            if (details.total_weight !== undefined) {
                el('success-weight-detail').textContent = `Skor bobot keahlian: ${details.matched_weight || 0} dari ${details.total_weight || 0} poin (${category}).`;
            } else {
                el('success-weight-detail').textContent = `Kategori kecocokan: ${category}.`;
            }
        }

        if (el('success-pipeline-stage')) {
            el('success-pipeline-stage').textContent = JH.util.stageLabel(pipeline.current_stage || 'screening');
        }
        if (el('success-pipeline-status')) {
            const st = pipeline.stage_status === 'passed'
                ? 'Lolos Otomatis (Skor >= 80%)'
                : 'Sedang Diulas Rekruter (In Progress)';
            el('success-pipeline-status').textContent = `Status tahap awal: ${st}`;
        }

        const matchedContainer = el('success-matched-skills');
        if (matchedContainer) {
            const list = Array.isArray(match.matched_skills) ? match.matched_skills : [];
            matchedContainer.innerHTML = list.length
                ? list.map((s) => `<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-300">${esc(s)}</span>`).join('')
                : '<span class="text-xs text-slate-500 italic">Tidak ada keahlian yang cocok</span>';
        }

        const missingContainer = el('success-missing-skills');
        if (missingContainer) {
            const list = Array.isArray(match.missing_skills) ? match.missing_skills : [];
            missingContainer.innerHTML = list.length
                ? list.map((s) => `<span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">${esc(s)}</span>`).join('')
                : '<span class="text-xs text-emerald-700 font-medium">Semua keahlian yang dibutuhkan terpenuhi!</span>';
        }

        const dlBtn = el('btn-success-download-cv');
        if (dlBtn) {
            if (data.cv_download_url && data.application_id) {
                dlBtn.classList.remove('hidden');
            } else {
                dlBtn.classList.add('hidden');
            }
        }

        if (successEl) successEl.classList.remove('hidden');
    }

    async function handleSubmitApplication() {
        if (state.submitting) return;
        if (!state.currentJob || !state.currentJob.id) {
            JH.toast('Pilih lowongan terlebih dahulu.', 'warning');
            return;
        }

        const submitBtn = el('btn-submit-apply');
        const coverLetter = el('cover-letter') ? el('cover-letter').value.trim() : '';

        state.submitting = true;
        JH.setLoading(submitBtn, true, 'Mengirim & Menghitung Skor...');
        setAlertBanner(null, '');

        try {
            const res = await JH.applications.submit({
                job_id: state.currentJob.id,
                cover_letter: coverLetter,
                candidate_skills: state.candidateSkills,
                cv_file: state.cvFile || undefined,
            });

            JH.toast(res.message || 'Berkas lamaran berhasil dikirim!', 'success');
            renderSuccessView(res.data || {});
        } catch (err) {
            const esc = JH.util.escapeHtml;
            if (err && err.status === 409) {
                // Sudah pernah melamar pada lowongan ini
                const existingId = err.data && err.data.application_id ? `#${err.data.application_id}` : '';
                const existingStage = err.data && err.data.status ? JH.util.stageLabel(err.data.status) : 'Screening';
                JH.toast(err.message || 'Kamu sudah pernah melamar di lowongan ini.', 'warning');
                setAlertBanner('conflict', `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <div class="flex items-center gap-1.5 font-bold text-sm text-amber-950">
                                <span class="material-symbols-outlined text-amber-600 text-lg">info</span>
                                <span>Lamaran Sudah Terdaftar (${esc(existingId)})</span>
                            </div>
                            <p class="text-xs text-amber-900">
                                ${esc(err.message || 'Kamu telah mengajukan lamaran untuk lowongan ini sebelumnya.')}
                                Status saat ini: <strong>${esc(existingStage)}</strong>.
                            </p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <a href="/my-dock" class="px-4 py-2 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold whitespace-nowrap">Lihat di My Dock</a>
                            <a href="/catalog" class="px-3 py-2 rounded-lg bg-white border border-amber-300 text-amber-900 text-xs font-semibold whitespace-nowrap">Lowongan Lain</a>
                        </div>
                    </div>
                `);
                const body = el('apply-modal-body');
                if (body) body.scrollTo({ top: 0, behavior: 'smooth' });
            } else if (err && err.status === 400) {
                // Lowongan ditutup
                JH.toast(err.message || 'Lowongan ini telah ditutup.', 'error');
                setAlertBanner('error', `
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h4 class="font-bold text-sm">Lowongan Telah Ditutup</h4>
                            <p class="text-xs mt-0.5">${esc(err.message || 'Lowongan ini tidak lagi menerima berkas lamaran baru.')}</p>
                        </div>
                        <a href="/catalog" class="px-4 py-2 rounded-lg bg-rose-700 text-white text-xs font-semibold whitespace-nowrap">Pilih Lowongan Lain</a>
                    </div>
                `);
                if (submitBtn) submitBtn.disabled = true;
            } else if (err && err.status === 422) {
                // Error validasi input
                const fullMsg = err.fullMessage ? err.fullMessage() : err.message;
                JH.toast(fullMsg || 'Periksa kembali isian formulir lamaran.', 'error');
                setAlertBanner('error', `
                    <div>
                        <h4 class="font-bold text-sm">Validasi Formulir Gagal (422)</h4>
                        <p class="text-xs mt-0.5">${esc(fullMsg)}</p>
                    </div>
                `);
            } else {
                JH.showError(err, 'Gagal mengirim berkas lamaran.');
            }
        } finally {
            state.submitting = false;
            JH.setLoading(submitBtn, false);
        }
    }

    function bindEvents() {
        // Tombol tutup modal
        const closeBtn = el('btn-close-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                window.location.href = '/catalog';
            });
        }

        // Dropdown ganti lowongan
        const jobSelect = el('apply-job-select');
        if (jobSelect) {
            jobSelect.addEventListener('change', (e) => {
                const selectedId = e.target.value;
                if (selectedId) loadJobDetail(selectedId);
            });
        }

        // Tambah skill via tombol & Enter
        const skillInput = el('skill-input');
        const addSkillBtn = el('btn-add-skill');
        if (addSkillBtn && skillInput) {
            addSkillBtn.addEventListener('click', () => {
                addCandidateSkill(skillInput.value);
                skillInput.value = '';
                skillInput.focus();
            });
            skillInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addCandidateSkill(skillInput.value);
                    skillInput.value = '';
                }
            });
        }

        // Reset skill ke profil asli
        const resetSkillsBtn = el('btn-reset-skills');
        if (resetSkillsBtn) {
            resetSkillsBtn.addEventListener('click', () => {
                state.candidateSkills = [...state.profileSkills];
                renderCandidateSkillChips();
                updateLiveMatchmaking();
                JH.toast('Daftar keahlian dikembalikan sesuai profil kamu.', 'info');
            });
        }

        // Delegasi klik hapus chip skill
        const chipsContainer = el('candidate-skills-chips');
        if (chipsContainer) {
            chipsContainer.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-remove-skill-index]');
                if (!btn) return;
                removeCandidateSkillAt(btn.getAttribute('data-remove-skill-index'));
            });
        }

        // Delegasi klik tambah skill cepat dari daftar requirement lowongan
        const reqContainer = el('job-requirements-list');
        if (reqContainer) {
            reqContainer.addEventListener('click', (e) => {
                const btn = e.target.closest('[data-quick-add-skill]');
                if (!btn) return;
                addCandidateSkill(btn.getAttribute('data-quick-add-skill'));
            });
        }

        // Upload CV: klik & drag-drop
        const cvInput = el('cv-file-input');
        const dropzone = el('cv-dropzone');
        const pickBtn = el('btn-pick-cv');
        const changeBtn = el('btn-change-cv');
        const removeBtn = el('btn-remove-cv');

        if (pickBtn && cvInput) {
            pickBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                cvInput.click();
            });
        }
        if (changeBtn && cvInput) {
            changeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                cvInput.click();
            });
        }
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                handleCvFileSelection(null);
            });
        }
        if (dropzone && cvInput) {
            dropzone.addEventListener('click', (e) => {
                if (e.target.closest('button')) return;
                cvInput.click();
            });
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('border-[#0284C7]', 'bg-sky-100/70');
            });
            dropzone.addEventListener('dragleave', () => {
                dropzone.classList.remove('border-[#0284C7]', 'bg-sky-100/70');
            });
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('border-[#0284C7]', 'bg-sky-100/70');
                const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                if (file) handleCvFileSelection(file);
            });
            cvInput.addEventListener('change', () => {
                const file = cvInput.files && cvInput.files[0];
                if (file) handleCvFileSelection(file);
            });
        }

        // Counter karakter cover letter
        const coverInput = el('cover-letter');
        const coverCount = el('cover-letter-count');
        if (coverInput && coverCount) {
            coverInput.addEventListener('input', () => {
                coverInput.dataset.userEdited = '1';
                coverCount.textContent = `${coverInput.value.length} / 1000`;
            });
        }

        // Submit lamaran
        const submitBtn = el('btn-submit-apply');
        const formEl = el('apply-form');
        if (submitBtn) {
            submitBtn.addEventListener('click', handleSubmitApplication);
        }
        if (formEl) {
            formEl.addEventListener('submit', (e) => {
                e.preventDefault();
                handleSubmitApplication();
            });
        }

        // Unduh CV dari tampilan sukses
        const dlBtn = el('btn-success-download-cv');
        if (dlBtn) {
            dlBtn.addEventListener('click', async () => {
                if (!state.lastSubmittedAppId) return;
                try {
                    await JH.applications.downloadCv(state.lastSubmittedAppId);
                } catch (err) {
                    JH.showError(err, 'Gagal mengunduh berkas CV.');
                }
            });
        }
    }

    async function init() {
        // 1. Guard wajib login sebagai applicant
        const sessionUser = JH.requireAuth('applicant');
        if (!sessionUser) return;

        state.user = sessionUser;
        state.profileSkills = normalizeSkills(sessionUser.skills || []);
        state.candidateSkills = [...state.profileSkills];
        renderApplicantInfo(sessionUser);

        bindEvents();

        // 2. Segarkan profil dari /api/auth/me secara paralel dengan daftar lowongan
        const params = new URLSearchParams(window.location.search);
        let targetJobId = params.get('job');

        try {
            const [freshUserRes, jobsRes] = await Promise.allSettled([
                JH.auth.me(),
                JH.jobs.list({ status: 'open' }),
            ]);

            if (freshUserRes.status === 'fulfilled' && freshUserRes.value) {
                state.user = freshUserRes.value;
                state.profileSkills = normalizeSkills(freshUserRes.value.skills || []);
                state.candidateSkills = [...state.profileSkills];
                renderApplicantInfo(freshUserRes.value);
                JH.bindSessionUI();
            }

            if (jobsRes.status === 'fulfilled' && jobsRes.value && Array.isArray(jobsRes.value.data)) {
                state.jobsList = jobsRes.value.data;
            }
        } catch (e) {
            // Lanjutkan dengan data sesi bila gagal memuat daftar tambahan
        }

        // Kalau tidak ada ?job=<id>, ambil lowongan pertama yang open dari daftar
        if (!targetJobId && state.jobsList.length > 0) {
            targetJobId = state.jobsList[0].id;
        }

        renderJobSwitcher();
        await loadJobDetail(targetJobId);
    }

    document.addEventListener('DOMContentLoaded', init);
})(window, document);
