import JH, { auth, profile, jobs, matchmaking, session, util, toast, showError, setLoading } from '../api.js';

async function initProfilePage() {
    // 1. Guard Autentikasi
    const sessionUser = JH.requireAuth();
    if (!sessionUser) return;

    // DOM Elements diinisialisasi di awal agar tidak memicu Temporal Dead Zone (TDZ) ReferenceError
    const biodataForm = document.getElementById('profile-biodata-form');
    const inputName = document.getElementById('bio-name');
    const inputEmail = document.getElementById('bio-email');
    const inputPhone = document.getElementById('bio-phone');
    const inputLocation = document.getElementById('bio-location');
    const inputBio = document.getElementById('bio-bio');
    const btnSaveBiodata = document.getElementById('btn-save-biodata');

    const headerName = document.getElementById('profile-header-name');
    const headerEmail = document.getElementById('profile-header-email');
    const headerRole = document.getElementById('profile-header-role');
    const headerInitials = document.getElementById('profile-header-initials');
    const countBadge = document.getElementById('profile-skills-count-badge');

    const skillInput = document.getElementById('profile-skill-input');
    const btnAddSkill = document.getElementById('btn-add-profile-skill');
    const skillsContainer = document.getElementById('profile-skills-chips-container');
    const btnSaveSkills = document.getElementById('btn-save-skills');
    const dirtyBanner = document.getElementById('skills-dirty-banner');
    const recoContainer = document.getElementById('profile-skill-recommendations');

    function normalizeSkills(skills) {
        if (!Array.isArray(skills)) return [];
        return skills.map((s) => {
            if (typeof s === 'string') return s.trim();
            if (s && typeof s === 'object' && s.name) return String(s.name).trim();
            return '';
        }).filter(Boolean);
    }

    let currentUser = { ...sessionUser };
    let currentSkills = normalizeSkills(currentUser.skills);
    let savedSkillsSnapshot = JSON.stringify(currentSkills);
    let allOpenJobs = [];

    function isSkillsDirty() {
        return JSON.stringify(currentSkills) !== savedSkillsSnapshot;
    }

    function updateSkillsDirtyUI() {
        if (!dirtyBanner) return;
        if (isSkillsDirty()) {
            dirtyBanner.classList.remove('hidden');
        } else {
            dirtyBanner.classList.add('hidden');
        }
    }

    // Prefill Form Biodata & Header
    function populateBiodataForm(user) {
        if (inputName) inputName.value = user.name || '';
        if (inputEmail) inputEmail.value = user.email || '';
        if (inputPhone) inputPhone.value = user.phone || '';
        if (inputLocation) inputLocation.value = user.location || '';
        if (inputBio) inputBio.value = user.bio || '';

        if (headerName) headerName.textContent = user.name || '-';
        if (headerEmail) headerEmail.textContent = user.email || '-';
        if (headerInitials) headerInitials.textContent = util.initials(user.name);
        if (headerRole) {
            const roleLabels = { applicant: 'Pelamar Kerja', recruiter: 'Recruiter', admin: 'Administrator' };
            headerRole.textContent = roleLabels[user.role] || user.role;
        }

        if (countBadge) countBadge.textContent = `${currentSkills.length} Keahlian`;
    }

    // Render Chip Keahlian
    function renderSkillsChips() {
        if (!skillsContainer) return;
        if (!currentSkills.length) {
            skillsContainer.innerHTML = '<span class="text-xs text-[#627080]">Belum ada keahlian ditambahkan. Tambahkan minimal satu keahlian.</span>';
            if (countBadge) countBadge.textContent = '0 Keahlian';
            updateSkillsDirtyUI();
            return;
        }

        skillsContainer.innerHTML = currentSkills.map((s, idx) => `
            <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-[#FFF3EA] text-[#8A2E05] border border-[#C8460B]/30 shadow-2xs">
                <span>${util.escapeHtml(s)}</span>
                <button
                    type="button"
                    data-remove-skill="${idx}"
                    class="text-[#C8460B] hover:text-[#B42318] hover:bg-[#ffe5d3] rounded-full w-4 h-4 inline-flex items-center justify-center font-bold text-sm cursor-pointer"
                    aria-label="Hapus keahlian ${util.escapeHtml(s)}"
                >
                    &times;
                </button>
            </span>
        `).join('');

        skillsContainer.querySelectorAll('[data-remove-skill]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const i = Number(btn.getAttribute('data-remove-skill'));
                currentSkills.splice(i, 1);
                renderSkillsChips();
                updateSkillsDirtyUI();
                analyzeSkillRecommendations();
            });
        });

        if (countBadge) countBadge.textContent = `${currentSkills.length} Keahlian`;
        updateSkillsDirtyUI();
    }

    // Tambah Keahlian Baru dari Input
    function addSkill() {
        if (!skillInput) return;
        const val = skillInput.value.trim();
        if (!val) return;
        const lower = val.toLowerCase();
        if (!currentSkills.some((s) => s.toLowerCase() === lower)) {
            currentSkills.push(val);
            renderSkillsChips();
            analyzeSkillRecommendations();
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

    // Simpan Perubahan Keahlian ke Server (PUT /api/profile/skills)
    btnSaveSkills?.addEventListener('click', async (e) => {
        e?.preventDefault();
        if (!currentSkills.length) {
            toast('Daftar keahlian tidak boleh kosong (minimal 1 keahlian).', 'warning');
            return;
        }

        setLoading(btnSaveSkills, true, 'Menyimpan Keahlian...');
        try {
            const res = await profile.updateSkills(currentSkills);
            currentUser.skills = res.data.skills;
            currentSkills = normalizeSkills(res.data.skills);
            savedSkillsSnapshot = JSON.stringify(currentSkills);
            session.setUser(currentUser);
            if (typeof JH.bindSessionUI === 'function') {
                JH.bindSessionUI(document);
            }
            setLoading(btnSaveSkills, false);
            renderSkillsChips();
            updateSkillsDirtyUI();
            toast('Daftar keahlian berhasil disimpan ke server!', 'success');
            analyzeSkillRecommendations();
        } catch (err) {
            setLoading(btnSaveSkills, false);
            showError(err, 'Gagal menyimpan keahlian.');
        }
    });

    // Simpan Perubahan Biodata ke Server (PUT /api/profile)
    biodataForm?.addEventListener('submit', async (e) => {
        e.preventDefault();

        const btn = btnSaveBiodata || document.getElementById('btn-save-biodata');
        const name = inputName?.value.trim();
        const phone = inputPhone?.value.trim();
        const location = inputLocation?.value.trim();
        const bio = inputBio?.value.trim();

        if (!name) {
            toast('Nama lengkap wajib diisi.', 'warning');
            return;
        }

        const payload = {
            name,
            phone: phone || null,
            location: location || null,
            bio: bio || null,
        };

        setLoading(btn, true, 'Menyimpan Biodata...');
        try {
            const res = await profile.update(payload);
            currentUser = { ...currentUser, ...res.data };
            session.setUser(currentUser);
            if (typeof JH.bindSessionUI === 'function') {
                JH.bindSessionUI(document);
            }
            populateBiodataForm(currentUser);
            setLoading(btn, false);
            toast('Biodata profil berhasil diperbarui!', 'success');
        } catch (err) {
            setLoading(btn, false);
            showError(err, 'Gagal memperbarui biodata.');
        }
    });

    // Analisis Rekomendasi Keahlian dari Pasar Lowongan Aktif
    function analyzeSkillRecommendations() {
        if (!recoContainer || !allOpenJobs.length) return;

        const normUserSkills = currentSkills.map((s) => s.toLowerCase().trim());
        const skillDemandMap = new Map();

        allOpenJobs.forEach((job) => {
            (job.requirements || []).forEach((req) => {
                const name = util.skillName(req);
                if (!name) return;
                const lower = name.toLowerCase().trim();
                if (!normUserSkills.includes(lower)) {
                    const weight = Number(req.weight) || 2;
                    const existing = skillDemandMap.get(name) || { count: 0, totalWeight: 0, originalName: name };
                    existing.count += 1;
                    existing.totalWeight += weight;
                    skillDemandMap.set(name, existing);
                }
            });
        });

        const sortedDemands = [...skillDemandMap.values()]
            .sort((a, b) => b.totalWeight - a.totalWeight || b.count - a.count)
            .slice(0, 6);

        if (!sortedDemands.length) {
            recoContainer.innerHTML = `
                <div class="p-4 rounded-xl bg-[#E7F5EC] border border-[#157F3B]/30 text-xs text-[#157F3B]">
                    Profil Anda telah mencakup hampir seluruh kualifikasi keahlian yang disyaratkan lowongan aktif saat ini!
                </div>
            `;
            return;
        }

        recoContainer.innerHTML = sortedDemands.map((item) => {
            const jobsDemanding = allOpenJobs.filter((j) => (j.requirements || []).some((r) => util.skillName(r).toLowerCase().trim() === item.originalName.toLowerCase().trim()));
            let totalGain = 0;
            jobsDemanding.forEach((j) => {
                const before = matchmaking.calculate(currentSkills, j.requirements || []).score;
                const after = matchmaking.calculate([...currentSkills, item.originalName], j.requirements || []).score;
                totalGain += (after - before);
            });
            const avgGain = jobsDemanding.length ? Math.round(totalGain / jobsDemanding.length) : 15;

            return `
                <div class="p-4 rounded-xl bg-white border border-[#E2E7ED] hover:border-[#1B65A6] transition-all flex items-center justify-between gap-3 shadow-2xs">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-[#0B1B2B]">${util.escapeHtml(item.originalName)}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#EBF3FB] text-[#1B65A6]">
                                Dicari di ${item.count} Lowongan
                            </span>
                        </div>
                        <p class="text-xs text-[#627080] mt-1">
                            Berpotensi menaikkan skor Anda hingga <strong class="text-[#157F3B]">+${avgGain}%</strong> pada posisi terkait.
                        </p>
                    </div>

                    <button
                        type="button"
                        data-quick-add-skill="${util.escapeHtml(item.originalName)}"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-[#0C2238] hover:bg-[#133150] transition-colors btn-press shrink-0 cursor-pointer flex items-center gap-1"
                    >
                        <span>+ Tambah</span>
                    </button>
                </div>
            `;
        }).join('');

        recoContainer.querySelectorAll('[data-quick-add-skill]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const sName = btn.getAttribute('data-quick-add-skill');
                if (sName && !currentSkills.some((s) => s.toLowerCase() === sName.toLowerCase())) {
                    currentSkills.push(sName);
                    renderSkillsChips();
                    analyzeSkillRecommendations();
                    toast(`Keahlian ${sName} ditambahkan ke editor! Klik 'Simpan Keahlian' untuk menyimpan.`, 'info');
                }
            });
        });
    }

    // 2. Muat Profil Segar dari Server
    try {
        const freshUser = await auth.me();
        currentUser = freshUser;
        currentSkills = normalizeSkills(freshUser.skills);
        savedSkillsSnapshot = JSON.stringify(currentSkills);
        populateBiodataForm(currentUser);
        renderSkillsChips();
        updateSkillsDirtyUI();
    } catch (e) {
        console.error('Gagal memuat profil auth me:', e);
        populateBiodataForm(currentUser);
        renderSkillsChips();
        updateSkillsDirtyUI();
    }

    // 3. Muat Data Lowongan untuk Analisis Rekomendasi Keahlian
    try {
        const jobsRes = await jobs.list({ per_page: 50, status: 'open' });
        allOpenJobs = Array.isArray(jobsRes.data) ? jobsRes.data : [];
        analyzeSkillRecommendations();
    } catch (e) {
        console.error('Gagal memuat lowongan untuk rekomendasi:', e);
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initProfilePage);
} else {
    initProfilePage();
}
