import JH, { jobs, applications, matchmaking, session, util, toast, showError, setLoading } from '../api.js';

document.addEventListener('DOMContentLoaded', async () => {
    // 1. Guard Autentikasi
    const currentUser = JH.requireAuth('applicant');
    if (!currentUser) return;

    const urlParams = new URLSearchParams(window.location.search);
    const jobId = urlParams.get('job');

    if (!jobId) {
        toast('Pilih lowongan yang ingin dilamar terlebih dahulu.', 'warning');
        setTimeout(() => { window.location.href = '/catalog'; }, 1200);
        return;
    }

    // State Halaman Apply
    const state = {
        user: currentUser,
        job: null,
        candidateSkills: Array.isArray(currentUser.skills) ? [...currentUser.skills] : [],
        cvFile: null,
        submitting: false,
    };

    // 2. Prefill info profil pelamar
    const applicantName = document.getElementById('applicant-name');
    const applicantEmail = document.getElementById('applicant-email');
    const applicantLocation = document.getElementById('applicant-location');

    if (applicantName) applicantName.textContent = currentUser.name || '-';
    if (applicantEmail) applicantEmail.textContent = currentUser.email || '-';
    if (applicantLocation) applicantLocation.textContent = currentUser.location || 'Indonesia';

    // 3. Muat Detail Lowongan dari API
    try {
        const res = await jobs.show(jobId);
        state.job = res.data;
        if (!state.job) {
            throw new Error('Lowongan tidak ditemukan');
        }
        renderJobSummary(state.job);
        renderSkillsEditor();
        recalculateMatch();
    } catch (err) {
        showError(err, 'Gagal memuat informasi lowongan.');
        setTimeout(() => { window.location.href = '/catalog'; }, 1500);
        return;
    }

    function renderJobSummary(job) {
        const esc = util.escapeHtml;
        const titleEl = document.getElementById('summary-job-title');
        const companyEl = document.getElementById('summary-job-company');
        const locationEl = document.getElementById('summary-job-location');
        const salaryEl = document.getElementById('summary-job-salary');
        const typeEl = document.getElementById('summary-job-type');

        if (titleEl) titleEl.textContent = job.title;
        if (companyEl) companyEl.textContent = job.company;
        if (locationEl) locationEl.textContent = job.location || 'Indonesia';
        if (salaryEl) salaryEl.textContent = util.formatSalaryRange(job.salary_min, job.salary_max);
        if (typeEl) typeEl.textContent = util.EMPLOYMENT_LABELS[job.employment_type] || job.employment_type || 'Full-time';

        // Render daftar syarat lowongan di kartu ringkasan
        const reqsList = document.getElementById('summary-requirements-list');
        if (reqsList) {
            reqsList.innerHTML = (job.requirements || []).map((r) => {
                const name = util.skillName(r);
                const level = String(r.level || 'required').toLowerCase();
                const badge = level === 'required'
                    ? 'bg-[#FFF3EA] text-[#8A2E05] border-[#C8460B]/20'
                    : level === 'important'
                        ? 'bg-[#EBF3FB] text-[#1B65A6] border-[#1B65A6]/20'
                        : 'bg-[#F4F6F8] text-[#3D4B5C] border-[#E2E7ED]';
                const levelText = level === 'required' ? 'Wajib' : level === 'important' ? 'Penting' : 'Nilai Tambah';

                return `
                    <div class="flex items-center justify-between text-xs py-1.5 border-b border-[#E2E7ED] last:border-0">
                        <span class="font-semibold text-[#0B1B2B]">${esc(name)}</span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border ${badge}">${levelText}</span>
                    </div>
                `;
            }).join('');
        }
    }

    // 4. Kalkulasi Live Match Score
    function recalculateMatch() {
        if (!state.job) return;
        const match = matchmaking.calculate(state.candidateSkills, state.job.requirements || []);
        const score = Math.round(match.score);
        const tone = util.scoreTone(score);

        const scoreNumEl = document.getElementById('live-score-num');
        const scoreBarEl = document.getElementById('live-score-bar');
        const scoreCatEl = document.getElementById('live-score-category');
        const scoreDescEl = document.getElementById('live-score-desc');

        if (scoreNumEl) scoreNumEl.textContent = `${score}%`;
        if (scoreBarEl) {
            scoreBarEl.style.width = `${score}%`;
            scoreBarEl.className = `h-full rounded-full transition-all duration-300 ease-out ${tone.bar}`;
        }
        if (scoreCatEl) {
            scoreCatEl.className = `text-xs font-bold px-2.5 py-0.5 rounded-full ${tone.bg} ${tone.text} border ${tone.border}`;
            scoreCatEl.textContent = tone.categoryText;
        }
        if (scoreDescEl) {
            if (score >= 80) {
                scoreDescEl.innerHTML = '<span class="text-[#157F3B] font-bold">Lolos Otomatis (Screening Pass)</span>: Berkas lamaran Anda akan otomatis memenuhi ambang seleksi awal.';
            } else if (score >= 50) {
                scoreDescEl.innerHTML = '<span class="text-[#1B65A6] font-bold">Ditinjau Rekruter (Manual Review)</span>: Profil Anda memenuhi sebagian besar kompetensi dan akan ditinjau tim HR.';
            } else {
                scoreDescEl.innerHTML = '<span class="text-[#8A2E05] font-bold">Di Bawah Ambang Kualifikasi</span>: Disarankan memperkuat keahlian terkait agar peluang lolos lebih tinggi.';
            }
        }
    }

    // 5. Kelola Tag Keahlian Pelamar (Editor Skills)
    const skillsChips = document.getElementById('apply-skills-chips');
    const skillInput = document.getElementById('apply-skill-input');
    const btnAddSkill = document.getElementById('apply-btn-add-skill');

    function renderSkillsEditor() {
        if (!skillsChips) return;
        if (!state.candidateSkills.length) {
            skillsChips.innerHTML = '<span class="text-xs text-[#627080]">Belum ada keahlian terdaftar. Masukkan keahlian Anda di atas.</span>';
            return;
        }

        const normJobReqs = (state.job?.requirements || []).map((r) => util.skillName(r).toLowerCase().trim());

        skillsChips.innerHTML = state.candidateSkills.map((s, idx) => {
            const isMatched = normJobReqs.includes(String(s).toLowerCase().trim());
            const badgeCls = isMatched
                ? 'bg-[#E7F5EC] text-[#157F3B] border-[#157F3B]/30'
                : 'bg-[#F4F6F8] text-[#3D4B5C] border-[#C5CFDA]';

            return `
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border ${badgeCls}">
                    <span>${util.escapeHtml(s)}</span>
                    <button type="button" data-remove-skill="${idx}" class="hover:opacity-75 cursor-pointer" aria-label="Hapus skill ${util.escapeHtml(s)}">
                        &times;
                    </button>
                </span>
            `;
        }).join('');

        skillsChips.querySelectorAll('[data-remove-skill]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const i = Number(btn.getAttribute('data-remove-skill'));
                state.candidateSkills.splice(i, 1);
                renderSkillsEditor();
                recalculateMatch();
            });
        });
    }

    function addSkill() {
        if (!skillInput) return;
        const val = skillInput.value.trim();
        if (!val) return;
        const lower = val.toLowerCase();
        if (!state.candidateSkills.some((s) => s.toLowerCase() === lower)) {
            state.candidateSkills.push(val);
            renderSkillsEditor();
            recalculateMatch();
        }
        skillInput.value = '';
        skillInput.focus();
    }

    btnAddSkill?.addEventListener('click', addSkill);
    skillInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            addSkill();
        }
    });

    // 6. Unggah CV PDF Drag & Drop
    const dropZone = document.getElementById('cv-dropzone');
    const fileInput = document.getElementById('cv-file-input');
    const filePreview = document.getElementById('cv-file-preview');
    const fileNameEl = document.getElementById('cv-filename');
    const fileSizeEl = document.getElementById('cv-filesize');
    const btnRemoveCv = document.getElementById('cv-remove-btn');
    const cvErrorEl = document.querySelector('[data-field-error="cv_file"]');

    function handleFile(file) {
        if (cvErrorEl) cvErrorEl.classList.add('hidden');
        if (!file) return;

        // Validasi tipe file harus PDF
        if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
            showCvError('Berkas CV/Resume harus berformat PDF.');
            return;
        }

        // Validasi ukuran maks 2 MB (2048 KB)
        if (file.size > 2 * 1024 * 1024) {
            showCvError('Ukuran berkas CV melebihi batas maksimal 2 MB (2048 KB).');
            return;
        }

        state.cvFile = file;
        if (fileNameEl) fileNameEl.textContent = file.name;
        if (fileSizeEl) {
            const kb = Math.round(file.size / 1024);
            fileSizeEl.textContent = `${kb} KB`;
        }
        if (dropZone) dropZone.classList.add('hidden');
        if (filePreview) filePreview.classList.remove('hidden');
    }

    function showCvError(msg) {
        if (cvErrorEl) {
            cvErrorEl.textContent = msg;
            cvErrorEl.classList.remove('hidden');
        }
        toast(msg, 'error');
    }

    function removeFile() {
        state.cvFile = null;
        if (fileInput) fileInput.value = '';
        if (filePreview) filePreview.classList.add('hidden');
        if (dropZone) dropZone.classList.remove('hidden');
    }

    fileInput?.addEventListener('change', (e) => {
        if (e.target.files && e.target.files[0]) {
            handleFile(e.target.files[0]);
        }
    });

    btnRemoveCv?.addEventListener('click', removeFile);

    // Drag and drop event listeners
    ['dragenter', 'dragover'].forEach((eventName) => {
        dropZone?.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('border-[#C8460B]', 'bg-[#FFF3EA]/30');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        dropZone?.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-[#C8460B]', 'bg-[#FFF3EA]/30');
        });
    });

    dropZone?.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        if (dt && dt.files && dt.files[0]) {
            handleFile(dt.files[0]);
        }
    });

    // 7. Submit Form Lamaran
    const applyForm = document.getElementById('apply-form');
    applyForm?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btnSubmit = document.getElementById('apply-submit-btn');
        const coverLetter = document.getElementById('apply-cover-letter')?.value.trim();

        const payload = {
            job_vacancy_id: Number(jobId),
            job_id: Number(jobId),
            cover_letter: coverLetter || null,
            candidate_skills: state.candidateSkills,
            cv_file: state.cvFile,
        };

        setLoading(btnSubmit, true, 'Mengirimkan Berkas...');
        try {
            const res = await applications.submit(payload);
            const appData = res.data;

            // Tampilkan Layar Sukses
            showSuccessScreen(appData);
        } catch (err) {
            setLoading(btnSubmit, false);
            if (err.status === 409) {
                // Konflik duplikasi lamaran
                toast('Kamu sudah melamar lowongan ini sebelumnya.', 'warning', 4500);
                showConflictBanner();
            } else if (err.errors) {
                Object.entries(err.errors).forEach(([field, msgs]) => {
                    const el = document.querySelector(`[data-field-error="${field}"]`);
                    if (el) {
                        el.textContent = Array.isArray(msgs) ? msgs[0] : String(msgs);
                        el.classList.remove('hidden');
                    }
                });
            } else {
                showError(err, 'Gagal mengirimkan berkas lamaran.');
            }
        }
    });

    function showSuccessScreen(appData) {
        const formContainer = document.getElementById('apply-form-container');
        const successContainer = document.getElementById('apply-success-container');
        if (!formContainer || !successContainer) return;

        formContainer.classList.add('hidden');
        successContainer.classList.remove('hidden');

        const score = appData?.matchmaking_result?.match_score || 0;
        const scoreEl = document.getElementById('success-score-label');
        const statusEl = document.getElementById('success-status-message');

        if (scoreEl) scoreEl.textContent = `${Math.round(score)}%`;

        if (statusEl) {
            if (score >= 80) {
                statusEl.innerHTML = `
                    <div class="p-4 rounded-xl bg-[#E7F5EC] border border-[#157F3B]/30 text-[#157F3B] text-sm">
                        <strong class="block font-heading text-base mb-1">Selamat! Lolos Screening Otomatis</strong>
                        Skor kecocokan profil Anda mencapai <strong>${Math.round(score)}%</strong> (ambang kelulusan &ge; 80%). Berkas Anda telah diteruskan ke tahap seleksi berikutnya secara instan.
                    </div>
                `;
            } else {
                statusEl.innerHTML = `
                    <div class="p-4 rounded-xl bg-[#EBF3FB] border border-[#1B65A6]/30 text-[#1B65A6] text-sm">
                        <strong class="block font-heading text-base mb-1">Berkas Lamaran Berhasil Diterima</strong>
                        Skor kecocokan profil Anda adalah <strong>${Math.round(score)}%</strong>. Berkas lamaran Anda masuk ke antrean peninjauan oleh tim rekruter.
                    </div>
                `;
            }
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function showConflictBanner() {
        const conflictBox = document.getElementById('apply-conflict-banner');
        if (conflictBox) {
            conflictBox.classList.remove('hidden');
            window.scrollTo({ top: conflictBox.offsetTop - 80, behavior: 'smooth' });
        }
    }
});
