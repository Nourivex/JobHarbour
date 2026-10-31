import JH, { jobs, applications, matchmaking, session, util, toast } from '../api.js';

document.addEventListener('DOMContentLoaded', async () => {
    const user = session.user();
    const isApplicant = session.isLoggedIn() && user && (user.role === 'applicant' || user.role === 'admin');
    const applicantSkills = isApplicant ? (user.skills || []) : [];

    // State halaman katalog
    const state = {
        search: '',
        location: '',
        employment_type: '',
        skill: '',
        sort: 'latest',
        highMatchOnly: false,
        page: 1,
        per_page: 20,
        jobs: [],
        pagination: null,
        selectedJobId: null,
        selectedJobDetail: null,
        appliedJobMap: new Map(), // jobId -> applicationId
        savedJobIds: new Set(JSON.parse(localStorage.getItem('jh_saved_jobs') || '[]')),
    };

    // Ambil parameter dari URL jika ada deep link
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('job')) state.selectedJobId = Number(urlParams.get('job'));
    if (urlParams.get('search')) state.search = urlParams.get('search');
    if (urlParams.get('location')) state.location = urlParams.get('location');
    if (urlParams.get('skill')) state.skill = urlParams.get('skill');
    if (urlParams.get('employment_type')) state.employment_type = urlParams.get('employment_type');

    // Pre-populate input filter dari state
    const inputSearch = document.getElementById('catalog-search');
    const inputLocation = document.getElementById('catalog-location');
    const selectType = document.getElementById('catalog-type');
    const selectSort = document.getElementById('catalog-sort');
    const checkHighMatch = document.getElementById('catalog-high-match');

    if (inputSearch && state.search) inputSearch.value = state.search;
    if (inputLocation && state.location) inputLocation.value = state.location;
    if (selectType && state.employment_type) selectType.value = state.employment_type;
    if (selectSort && state.sort) selectSort.value = state.sort;

    // Sembunyikan filter 80%+ jika bukan pelamar
    const highMatchWrap = document.getElementById('high-match-wrap');
    if (highMatchWrap && !isApplicant) {
        highMatchWrap.style.display = 'none';
    }

    // 1. Muat lamaran pengguna jika pelamar (untuk menandai "Sudah Dilamar")
    if (isApplicant) {
        try {
            const appsRes = await applications.list();
            const apps = Array.isArray(appsRes.data) ? appsRes.data : [];
            apps.forEach((app) => {
                const jid = Number(app.job_vacancy_id || (app.job_vacancy && app.job_vacancy.id));
                if (jid && app.status !== 'canceled') {
                    state.appliedJobMap.set(jid, app.id);
                }
            });
        } catch (e) {
            console.error('Tidak dapat memuat riwayat lamaran:', e);
        }
    }

    // 2. Muat lowongan dari server
    async function fetchJobs() {
        const listContainer = document.getElementById('catalog-jobs-list');
        const listLoading = document.getElementById('catalog-jobs-loading');
        const listEmpty = document.getElementById('catalog-jobs-empty');
        const countLabel = document.getElementById('catalog-results-count');

        if (listLoading) listLoading.classList.remove('hidden');
        if (listEmpty) listEmpty.classList.add('hidden');

        try {
            const query = {
                status: 'open',
                per_page: state.per_page,
                page: state.page,
                sort: state.sort,
            };
            if (state.search) query.search = state.search;
            if (state.location) query.location = state.location;
            if (state.employment_type) query.employment_type = state.employment_type;
            if (state.skill) query.skill = state.skill;

            const res = await jobs.list(query);
            let rawJobs = Array.isArray(res.data) ? res.data : [];
            state.pagination = (res.meta && res.meta.pagination) || null;

            // Hitung skor pemadanan di sisi klien jika pengguna pelamar
            let processedJobs = rawJobs.map((j) => {
                const reqs = Array.isArray(j.requirements) ? j.requirements : [];
                const match = isApplicant ? matchmaking.calculate(applicantSkills, reqs) : null;
                const isApplied = state.appliedJobMap.has(Number(j.id));
                const isSaved = state.savedJobIds.has(Number(j.id));
                return { ...j, _match: match, _isApplied: isApplied, _isSaved: isSaved };
            });

            // Filter checkbox "Hanya yang cocok 80%+"
            if (state.highMatchOnly && isApplicant) {
                processedJobs = processedJobs.filter((j) => j._match && j._match.score >= 80);
            }

            // Urutkan berdasarkan kecocokan jika dipilih
            if (state.sort === 'match' && isApplicant) {
                processedJobs.sort((a, b) => ((b._match?.score || 0) - (a._match?.score || 0)));
            }

            state.jobs = processedJobs;

            if (listLoading) listLoading.classList.add('hidden');

            if (countLabel) {
                countLabel.textContent = `${processedJobs.length} lowongan ditemukan`;
            }

            if (!processedJobs.length) {
                if (listEmpty) listEmpty.classList.remove('hidden');
                if (listContainer) listContainer.innerHTML = '';
                showDetailPlaceholder();
                return;
            }

            renderJobList(processedJobs);

            // Auto-select job: dari deep link ?job={id} atau lowongan pertama di desktop
            if (state.selectedJobId) {
                const found = processedJobs.find((j) => Number(j.id) === Number(state.selectedJobId));
                if (found) {
                    selectJob(found);
                } else {
                    // Coba muat single job jika di luar halaman pertama
                    try {
                        const singleRes = await jobs.show(state.selectedJobId);
                        if (singleRes.data) {
                            const reqs = Array.isArray(singleRes.data.requirements) ? singleRes.data.requirements : [];
                            const match = isApplicant ? matchmaking.calculate(applicantSkills, reqs) : null;
                            const jobObj = {
                                ...singleRes.data,
                                _match: match,
                                _isApplied: state.appliedJobMap.has(Number(singleRes.data.id)),
                                _isSaved: state.savedJobIds.has(Number(singleRes.data.id)),
                            };
                            selectJob(jobObj);
                        }
                    } catch (e) {
                        selectJob(processedJobs[0]);
                    }
                }
            } else if (window.innerWidth >= 1024 && processedJobs.length) {
                selectJob(processedJobs[0]);
            }

        } catch (err) {
            console.error('Gagal memuat katalog lowongan:', err);
            if (listLoading) listLoading.classList.add('hidden');
            if (listEmpty) listEmpty.classList.remove('hidden');
        }
    }

    // 3. Render kartu daftar di kolom kiri
    function renderJobList(items) {
        const listContainer = document.getElementById('catalog-jobs-list');
        if (!listContainer) return;

        const esc = util.escapeHtml;

        listContainer.innerHTML = items.map((job) => {
            const isSelected = state.selectedJobId && Number(state.selectedJobId) === Number(job.id);
            const empLabel = util.EMPLOYMENT_LABELS[job.employment_type] || job.employment_type || 'Full-time';
            const salary = util.formatSalaryRange(job.salary_min, job.salary_max);

            let scoreBadgeHtml = '';
            if (job._match) {
                const tone = util.scoreTone(job._match.score);
                scoreBadgeHtml = `
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold ${tone.bg} ${tone.text} border ${tone.border}">
                        ${Math.round(job._match.score)}%
                    </span>
                `;
            }

            let appliedBadgeHtml = '';
            if (job._isApplied) {
                appliedBadgeHtml = `
                    <span class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-md bg-[#E7F5EC] text-[#157F3B] border border-[#157F3B]/30">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                        Sudah Dilamar
                    </span>
                `;
            }

            return `
                <div
                    data-job-id="${job.id}"
                    class="p-4 sm:p-5 rounded-2xl border transition-all cursor-pointer text-left card-hover ${isSelected ? 'bg-white border-[#C8460B] shadow-md ring-1 ring-[#C8460B]' : 'bg-white border-[#E2E7ED] hover:border-[#C5CFDA]'}"
                >
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-[#F4F6F8] text-[#3D4B5C] border border-[#E2E7ED]">
                            ${esc(empLabel)}
                        </span>
                        <div class="flex items-center gap-2">
                            ${appliedBadgeHtml}
                            ${scoreBadgeHtml}
                        </div>
                    </div>

                    <h3 class="text-base font-bold text-[#0B1B2B] leading-snug font-heading">
                        ${esc(job.title)}
                    </h3>
                    <p class="text-sm font-semibold text-[#3D4B5C] mt-0.5">
                        ${esc(job.company)}
                    </p>

                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#627080] mt-3">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                            ${esc(job.location || 'Indonesia')}
                        </span>
                        <span class="font-semibold text-[#0B1B2B]">
                            ${salary}
                        </span>
                    </div>

                    <div class="flex flex-wrap gap-1 mt-3">
                        ${(job.requirements || []).slice(0, 3).map((r) => {
                            return `<span class="text-[10px] font-medium px-2 py-0.5 rounded bg-[#F4F6F8] text-[#3D4B5C]">${esc(util.skillName(r))}</span>`;
                        }).join('')}
                    </div>
                </div>
            `;
        }).join('');

        // Event listener klik kartu
        listContainer.querySelectorAll('[data-job-id]').forEach((el) => {
            el.addEventListener('click', () => {
                const id = Number(el.getAttribute('data-job-id'));
                const target = state.jobs.find((j) => Number(j.id) === id);
                if (target) {
                    selectJob(target);
                    // Pada mobile, buka detail drawer
                    if (window.innerWidth < 1024) {
                        openMobileDetail();
                    }
                }
            });
        });
    }

    // 4. Pilih lowongan dan render detail panel (cross-fade 150ms)
    function selectJob(job) {
        state.selectedJobId = Number(job.id);
        state.selectedJobDetail = job;

        // Update highlight kartu di list
        document.querySelectorAll('#catalog-jobs-list [data-job-id]').forEach((card) => {
            const cid = Number(card.getAttribute('data-job-id'));
            if (cid === Number(job.id)) {
                card.classList.add('border-[#C8460B]', 'shadow-md', 'ring-1', 'ring-[#C8460B]');
                card.classList.remove('border-[#E2E7ED]');
            } else {
                card.classList.remove('border-[#C8460B]', 'shadow-md', 'ring-1', 'ring-[#C8460B]');
                card.classList.add('border-[#E2E7ED]');
            }
        });

        // Sinkronkan ke URL query tanpa reload
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set('job', job.id);
        window.history.replaceState({}, '', newUrl.toString());

        renderJobDetail(job);
    }

    // 5. Render Panel Detail
    function renderJobDetail(job) {
        const detailWrap = document.getElementById('catalog-detail-content');
        const placeholderWrap = document.getElementById('catalog-detail-placeholder');

        if (!detailWrap) return;
        if (placeholderWrap) placeholderWrap.classList.add('hidden');
        detailWrap.classList.remove('hidden');

        // Cross fade animation
        detailWrap.classList.remove('opacity-100');
        detailWrap.classList.add('opacity-0');

        setTimeout(() => {
            populateDetailFields(job, detailWrap);
            detailWrap.classList.remove('opacity-0');
            detailWrap.classList.add('opacity-100');
        }, 120);
    }

    function populateDetailFields(job, container) {
        const esc = util.escapeHtml;
        const empLabel = util.EMPLOYMENT_LABELS[job.employment_type] || job.employment_type || 'Full-time';
        const salary = util.formatSalaryRange(job.salary_min, job.salary_max);

        // Header info
        const titleEl = container.querySelector('#detail-title');
        const companyEl = container.querySelector('#detail-company');
        const locationEl = container.querySelector('#detail-location');
        const typeEl = container.querySelector('#detail-type');
        const salaryEl = container.querySelector('#detail-salary');
        const quotaEl = container.querySelector('#detail-quota');
        const descEl = container.querySelector('#detail-description');

        if (titleEl) titleEl.textContent = job.title;
        if (companyEl) companyEl.textContent = job.company;
        if (locationEl) locationEl.textContent = job.location || 'Indonesia';
        if (typeEl) typeEl.textContent = empLabel;
        if (salaryEl) salaryEl.textContent = salary;
        if (quotaEl) quotaEl.textContent = `${job.quota || 1} Kuota Kandidat`;
        if (descEl) descEl.innerHTML = esc(job.description).replace(/\n/g, '<br>');

        // Bookmark button
        const btnBookmark = container.querySelector('#detail-btn-bookmark');
        if (btnBookmark) {
            const isSaved = state.savedJobIds.has(Number(job.id));
            updateBookmarkButton(btnBookmark, isSaved);
            btnBookmark.onclick = () => {
                if (state.savedJobIds.has(Number(job.id))) {
                    state.savedJobIds.delete(Number(job.id));
                    updateBookmarkButton(btnBookmark, false);
                    toast('Lowongan dihapus dari daftar simpan.', 'info', 2000);
                } else {
                    state.savedJobIds.add(Number(job.id));
                    updateBookmarkButton(btnBookmark, true);
                    toast('Lowongan berhasil disimpan!', 'success', 2000);
                }
                localStorage.setItem('jh_saved_jobs', JSON.stringify([...state.savedJobIds]));
            };
        }

        // Apply button
        const btnApply = container.querySelector('#detail-btn-apply');
        if (btnApply) {
            if (job._isApplied) {
                btnApply.disabled = true;
                btnApply.className = 'flex-1 min-h-[44px] px-5 py-2.5 rounded-lg text-sm font-bold bg-[#E7F5EC] text-[#157F3B] border border-[#157F3B]/30 cursor-not-allowed flex items-center justify-center gap-2';
                btnApply.innerHTML = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg> Sudah Dilamar`;
            } else {
                btnApply.disabled = false;
                btnApply.className = 'flex-1 min-h-[44px] px-5 py-2.5 rounded-lg text-sm font-bold bg-[#C8460B] hover:bg-[#993406] text-white transition-all btn-press shadow-xs flex items-center justify-center gap-2 cursor-pointer';
                btnApply.innerHTML = `Lamar Sekarang <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>`;
                btnApply.onclick = () => {
                    const applyPath = `/apply?job=${job.id}`;
                    if (!session.isLoggedIn()) {
                        window.location.href = `/login?next=${encodeURIComponent(applyPath)}`;
                    } else if (user && user.role === 'recruiter') {
                        toast('Akun rekruter tidak dapat melamar lowongan.', 'warning');
                    } else {
                        window.location.href = applyPath;
                    }
                };
            }
        }

        // PANEL KECOCOKAN (MATCHMAKING PANEL)
        const matchPanel = container.querySelector('#detail-match-panel');
        const guestPanel = container.querySelector('#detail-guest-prompt');

        if (!isApplicant) {
            if (matchPanel) matchPanel.classList.add('hidden');
            if (guestPanel) guestPanel.classList.remove('hidden');
        } else {
            if (guestPanel) guestPanel.classList.add('hidden');
            if (matchPanel) {
                matchPanel.classList.remove('hidden');
                renderMatchTelemetry(job, matchPanel);
            }
        }
    }

    function updateBookmarkButton(btn, isSaved) {
        if (isSaved) {
            btn.className = 'min-h-[44px] px-3.5 py-2 rounded-lg text-sm font-semibold bg-[#FFF3EA] text-[#8A2E05] border border-[#C8460B]/30 transition-colors flex items-center gap-1.5 cursor-pointer';
            btn.innerHTML = `<svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg> <span>Tersimpan</span>`;
        } else {
            btn.className = 'min-h-[44px] px-3.5 py-2 rounded-lg text-sm font-semibold bg-white text-[#3D4B5C] hover:text-[#0B1B2B] border border-[#C5CFDA] hover:border-[#0C2238] transition-colors flex items-center gap-1.5 cursor-pointer';
            btn.innerHTML = `<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/></svg> <span>Simpan</span>`;
        }
    }

    function renderMatchTelemetry(job, panel) {
        const match = job._match || matchmaking.calculate(applicantSkills, job.requirements || []);
        const score = Math.round(match.score);
        const tone = util.scoreTone(score);

        // 1. Animasi hitung angka (count up in 600ms)
        const scoreNumEl = panel.querySelector('#telemetry-score-num');
        const scoreBarEl = panel.querySelector('#telemetry-score-bar');
        const scoreCatEl = panel.querySelector('#telemetry-category-label');
        const scoreExplEl = panel.querySelector('#telemetry-explanation-text');

        if (scoreCatEl) {
            scoreCatEl.className = `text-xs font-bold px-2.5 py-0.5 rounded-full ${tone.bg} ${tone.text} border ${tone.border}`;
            scoreCatEl.textContent = tone.categoryText;
        }

        if (scoreExplEl) {
            if (score >= 80) {
                scoreExplEl.textContent = 'Profil Anda memenuhi mayoritas kualifikasi utama. Berkas lamaran Anda akan otomatis lolos screening awal.';
            } else if (score >= 50) {
                scoreExplEl.textContent = 'Profil Anda memenuhi sebagian kualifikasi penting. Berkas lamaran akan ditinjau secara langsung oleh tim rekruter.';
            } else {
                scoreExplEl.textContent = 'Terdapat beberapa keahlian utama yang belum terpenuhi di profil Anda.';
            }
        }

        if (scoreBarEl) {
            scoreBarEl.style.width = '0%';
            scoreBarEl.className = `h-full rounded-full transition-all duration-700 ease-out ${tone.bar}`;
            requestAnimationFrame(() => {
                setTimeout(() => {
                    scoreBarEl.style.width = `${score}%`;
                }, 50);
            });
        }

        if (scoreNumEl) {
            animateCount(scoreNumEl, 0, score, 600);
        }

        // 2. Kelompokkan syarat: Wajib, Penting, Nilai Tambah
        const normApplicant = applicantSkills.map((s) => String(s).toLowerCase().trim());
        const tiers = {
            required: { label: 'Wajib (Bobot 3x)', items: [] },
            important: { label: 'Penting (Bobot 2x)', items: [] },
            nice_to_have: { label: 'Nilai Tambah (Bobot 1x)', items: [] },
        };

        (job.requirements || []).forEach((r) => {
            const name = util.skillName(r);
            const level = String(r.level || 'required').toLowerCase().replace(/-/g, '_');
            const targetTier = tiers[level] || tiers.required;
            const hasSkill = normApplicant.includes(name.toLowerCase().trim());
            targetTier.items.push({ name, hasSkill });
        });

        const tiersContainer = panel.querySelector('#telemetry-tiers-container');
        if (tiersContainer) {
            tiersContainer.innerHTML = Object.entries(tiers).map(([key, tier]) => {
                if (!tier.items.length) return '';
                return `
                    <div class="space-y-1.5">
                        <span class="text-xs font-bold text-[#3D4B5C] uppercase tracking-wider">${tier.label}</span>
                        <div class="flex flex-wrap gap-2">
                            ${tier.items.map((it) => {
                                if (it.hasSkill) {
                                    return `
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#E7F5EC] text-[#157F3B] border border-[#157F3B]/20">
                                            <svg class="w-3.5 h-3.5 text-[#157F3B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                            ${util.escapeHtml(it.name)}
                                        </span>
                                    `;
                                }
                                return `
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#F4F6F8] text-[#627080] border border-[#C5CFDA] border-dashed">
                                        <svg class="w-3.5 h-3.5 text-[#B42318]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        ${util.escapeHtml(it.name)}
                                    </span>
                                `;
                            }).join('')}
                        </div>
                    </div>
                `;
            }).join('');
        }

        // 3. Saran konkret kenaikan skor
        const suggestionWrap = panel.querySelector('#telemetry-suggestion-wrap');
        const suggestionText = panel.querySelector('#telemetry-suggestion-text');

        if (suggestionWrap && suggestionText) {
            if (match.missing_skills && match.missing_skills.length && score < 100) {
                // Cari missing skill dengan bobot tertinggi
                const highestMissing = match.missing_skills[0];
                const simulatedSkills = [...applicantSkills, highestMissing];
                const simulatedMatch = matchmaking.calculate(simulatedSkills, job.requirements || []);
                const potentialScore = Math.round(simulatedMatch.score);

                suggestionWrap.classList.remove('hidden');
                suggestionText.innerHTML = `Tambahkan <strong class="text-[#0B1B2B]">${util.escapeHtml(highestMissing)}</strong> ke profil Anda untuk menaikkan skor menjadi <strong class="text-[#157F3B] font-heading font-extrabold">${potentialScore}%</strong>.`;
            } else {
                suggestionWrap.classList.add('hidden');
            }
        }
    }

    function animateCount(el, start, end, duration) {
        const startTime = performance.now();
        const step = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            // Ease out cubic
            const ease = 1 - Math.pow(1 - progress, 3);
            const current = Math.round(start + (end - start) * ease);
            el.textContent = `${current}%`;
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                el.textContent = `${end}%`;
            }
        };
        requestAnimationFrame(step);
    }

    function showDetailPlaceholder() {
        const detailWrap = document.getElementById('catalog-detail-content');
        const placeholderWrap = document.getElementById('catalog-detail-placeholder');
        if (detailWrap) detailWrap.classList.add('hidden');
        if (placeholderWrap) placeholderWrap.classList.remove('hidden');
    }

    // 6. Mobile Drawer Logic
    const mobileDrawer = document.getElementById('catalog-mobile-drawer');
    const closeMobileBtn = document.getElementById('catalog-close-mobile-drawer');

    function openMobileDetail() {
        if (!mobileDrawer) return;
        const detailContent = document.getElementById('catalog-detail-content');
        const mobileInner = document.getElementById('catalog-mobile-detail-inner');
        if (detailContent && mobileInner) {
            mobileInner.innerHTML = detailContent.innerHTML;
            const btnBookmark = mobileInner.querySelector('#detail-btn-bookmark');
            const btnApply = mobileInner.querySelector('#detail-btn-apply');
            if (btnBookmark) {
                btnBookmark.onclick = () => document.getElementById('detail-btn-bookmark')?.click();
            }
            if (btnApply) {
                btnApply.onclick = () => document.getElementById('detail-btn-apply')?.click();
            }
        }
        mobileDrawer.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeMobileDetail() {
        if (!mobileDrawer) return;
        mobileDrawer.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    closeMobileBtn?.addEventListener('click', closeMobileDetail);

    // 7. Event Listener Filter & Pencarian
    const filterForm = document.getElementById('catalog-filter-form');
    filterForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        state.search = inputSearch?.value.trim() || '';
        state.location = inputLocation?.value.trim() || '';
        state.employment_type = selectType?.value || '';
        state.sort = selectSort?.value || 'latest';
        state.highMatchOnly = checkHighMatch?.checked || false;
        state.page = 1;
        fetchJobs();
    });

    selectSort?.addEventListener('change', () => {
        state.sort = selectSort.value;
        fetchJobs();
    });

    selectType?.addEventListener('change', () => {
        state.employment_type = selectType.value;
        fetchJobs();
    });

    checkHighMatch?.addEventListener('change', () => {
        state.highMatchOnly = checkHighMatch.checked;
        fetchJobs();
    });

    const resetFiltersBtn = document.getElementById('catalog-reset-filters');
    resetFiltersBtn?.addEventListener('click', () => {
        if (inputSearch) inputSearch.value = '';
        if (inputLocation) inputLocation.value = '';
        if (selectType) selectType.value = '';
        if (selectSort) selectSort.value = 'latest';
        if (checkHighMatch) checkHighMatch.checked = false;
        state.search = '';
        state.location = '';
        state.employment_type = '';
        state.skill = '';
        state.sort = 'latest';
        state.highMatchOnly = false;
        fetchJobs();
    });

    // Mulai muat katalog
    fetchJobs();
});
