/**
 * JobHarbour - API Client bersama untuk semua halaman frontend.
 *
 * Isinya:
 * - Wrapper fetch() yang otomatis pasang header Accept, Content-Type, dan Authorization: Bearer <token>
 * - Parsing JSON envelope standar { status, message, data, meta, errors }
 * - Penyimpanan sesi (token JWT + data user) di localStorage
 * - Guard halaman (wajib login / wajib role tertentu)
 * - Helper tampilan: toast, format rupiah, format tanggal, escape HTML, label tahap pipeline
 *
 * Dipakai lewat objek global: window.JH
 */
(function (window, document) {
    'use strict';

    const API_BASE = '/api';
    const TOKEN_KEY = 'jh_token';
    const USER_KEY = 'jh_user';

    // ---------------------------------------------------------------------
    // Error khusus API, biar gampang dicek status code + error per field
    // ---------------------------------------------------------------------
    class ApiError extends Error {
        constructor(message, status, payload) {
            super(message);
            this.name = 'ApiError';
            this.status = status;
            this.payload = payload || null;
            this.errors = (payload && payload.errors) || null;
            this.data = (payload && payload.data) || null;
        }

        /** Gabungkan semua pesan error validasi jadi satu string. */
        fullMessage() {
            if (!this.errors) return this.message;
            const lines = Object.values(this.errors).flat();
            return lines.length ? lines.join(' ') : this.message;
        }
    }

    // ---------------------------------------------------------------------
    // Sesi (token + user)
    // ---------------------------------------------------------------------
    const session = {
        token() {
            return localStorage.getItem(TOKEN_KEY);
        },
        user() {
            try {
                return JSON.parse(localStorage.getItem(USER_KEY) || 'null');
            } catch (e) {
                return null;
            }
        },
        save(token, user) {
            if (token) localStorage.setItem(TOKEN_KEY, token);
            if (user) localStorage.setItem(USER_KEY, JSON.stringify(user));
        },
        setUser(user) {
            localStorage.setItem(USER_KEY, JSON.stringify(user));
        },
        clear() {
            localStorage.removeItem(TOKEN_KEY);
            localStorage.removeItem(USER_KEY);
        },
        isLoggedIn() {
            return !!localStorage.getItem(TOKEN_KEY);
        },
    };

    // ---------------------------------------------------------------------
    // Request inti
    // ---------------------------------------------------------------------
    /**
     * Kirim request ke REST API JobHarbour.
     *
     * @param {string} path    contoh: '/jobs', '/applications/3'
     * @param {object} options { method, body, query, auth, formData }
     * @returns {Promise<object>} envelope JSON lengkap (status, message, data, meta)
     */
    async function request(path, options = {}) {
        const method = (options.method || 'GET').toUpperCase();
        const headers = { Accept: 'application/json' };

        let url = API_BASE + path;
        if (options.query) {
            const params = new URLSearchParams();
            Object.entries(options.query).forEach(([k, v]) => {
                if (v !== undefined && v !== null && v !== '') params.append(k, v);
            });
            const qs = params.toString();
            if (qs) url += (url.includes('?') ? '&' : '?') + qs;
        }

        let body;
        if (options.formData instanceof FormData) {
            // multipart (misal upload CV) -> biarkan browser yang set boundary Content-Type
            body = options.formData;
        } else if (options.body !== undefined) {
            headers['Content-Type'] = 'application/json';
            body = JSON.stringify(options.body);
        }

        const token = session.token();
        if (options.auth !== false && token) {
            headers.Authorization = 'Bearer ' + token;
        }

        let response;
        try {
            response = await fetch(url, { method, headers, body });
        } catch (networkErr) {
            throw new ApiError('Tidak bisa terhubung ke server. Cek koneksi atau pastikan server Laravel jalan.', 0);
        }

        let payload = null;
        const text = await response.text();
        if (text) {
            try {
                payload = JSON.parse(text);
            } catch (e) {
                payload = null;
            }
        }

        if (!response.ok || (payload && payload.status === 'error')) {
            const message = (payload && payload.message) || `Request gagal (HTTP ${response.status}).`;

            // Token kedaluwarsa / tidak valid -> bersihkan sesi
            if (response.status === 401 && options.auth !== false && token) {
                session.clear();
            }
            throw new ApiError(message, response.status, payload);
        }

        return payload || { status: 'success', data: null };
    }

    const api = {
        get: (path, query, opts = {}) => request(path, { ...opts, method: 'GET', query }),
        post: (path, body, opts = {}) => request(path, { ...opts, method: 'POST', body }),
        put: (path, body, opts = {}) => request(path, { ...opts, method: 'PUT', body }),
        patch: (path, body, opts = {}) => request(path, { ...opts, method: 'PATCH', body }),
        del: (path, opts = {}) => request(path, { ...opts, method: 'DELETE' }),
        upload: (path, formData, opts = {}) => request(path, { ...opts, method: 'POST', formData }),
        request,
    };

    // ---------------------------------------------------------------------
    // Endpoint per service (biar halaman tinggal panggil)
    // ---------------------------------------------------------------------
    const auth = {
        async login(email, password) {
            const res = await api.post('/auth/login', { email, password }, { auth: false });
            session.save(res.data.token, res.data.user);
            return res.data.user;
        },
        async register(payload) {
            const res = await api.post('/auth/register', payload, { auth: false });
            session.save(res.data.token, res.data.user);
            return res.data.user;
        },
        async me() {
            const res = await api.get('/auth/me');
            session.setUser(res.data);
            return res.data;
        },
        logout(redirectTo = '/login') {
            session.clear();
            window.location.href = redirectTo;
        },
    };

    const jobs = {
        list: (filters = {}) => api.get('/jobs', filters),
        show: (id) => api.get('/jobs/' + id),
        mine: () => api.get('/jobs/my'),
        create: (payload) => api.post('/jobs', payload),
        update: (id, payload) => api.patch('/jobs/' + id, payload),
        close: (id) => api.del('/jobs/' + id),
    };

    const applications = {
        list: () => api.get('/applications'),
        show: (id) => api.get('/applications/' + id),
        /** payload: { job_id, cover_letter, candidate_skills[], cv_file(File) } */
        submit(payload) {
            if (payload.cv_file instanceof File) {
                const fd = new FormData();
                fd.append('job_id', payload.job_id);
                if (payload.cover_letter) fd.append('cover_letter', payload.cover_letter);
                if (payload.candidate_skills) fd.append('candidate_skills', JSON.stringify(payload.candidate_skills));
                fd.append('cv_file', payload.cv_file);
                return api.upload('/applications', fd);
            }
            return api.post('/applications', payload);
        },
        cancel: (id) => api.del('/applications/' + id),
        /** Download CV dengan token (tidak bisa pakai <a href> biasa karena butuh header Bearer). */
        async downloadCv(id) {
            const res = await fetch(API_BASE + '/applications/' + id + '/cv', {
                headers: { Authorization: 'Bearer ' + session.token(), Accept: 'application/pdf' },
            });
            if (!res.ok) {
                let msg = 'Berkas CV tidak tersedia.';
                try { msg = (await res.json()).message || msg; } catch (e) { /* abaikan */ }
                throw new ApiError(msg, res.status);
            }
            const blob = await res.blob();
            const url = URL.createObjectURL(blob);
            window.open(url, '_blank');
            setTimeout(() => URL.revokeObjectURL(url), 60000);
        },
    };

    const pipelines = {
        timeline: (applicationId) => api.get('/pipelines/' + applicationId),
        /** payload: { stage, status, notes, interview_schedule } */
        updateStatus: (applicationId, payload) => api.patch('/pipelines/' + applicationId + '/status', payload),
    };

    const profile = {
        updateSkills: (skills) => api.put('/profile/skills', { skills }),
        update: (payload) => api.put('/profile', payload),
    };

    // ---------------------------------------------------------------------
    // Mesin Pemadanan Keahlian (Weighted Jaccard Similarity) di sisi Client
    // Identik dengan App\Services\MatchmakingService::calculate()
    // ---------------------------------------------------------------------
    const matchmaking = {
        calculate(candidateSkills = [], jobRequirements = []) {
            const normalizedCandidate = (candidateSkills || []).map((s) => {
                if (s && typeof s === 'object') return String(s.name || s.skill || '').toLowerCase().trim();
                return String(s || '').toLowerCase().trim();
            }).filter(Boolean);

            if (!Array.isArray(jobRequirements) || jobRequirements.length === 0) {
                return {
                    score: 100.0,
                    category: 'excellent',
                    matched_skills: candidateSkills || [],
                    missing_skills: [],
                    calculation_details: {
                        matched_weight: 0,
                        total_weight: 0,
                    },
                };
            }

            let totalRequiredWeight = 0;
            let matchedWeight = 0;
            const matchedSkills = [];
            const missingSkills = [];

            jobRequirements.forEach((req) => {
                const reqSkill = typeof req === 'string' ? req : (req.skill || req.name || '');
                const level = (req && req.level ? String(req.level) : 'required').toLowerCase();
                let weight = 2;
                if (level === 'required') weight = 3;
                else if (level === 'important') weight = 2;
                else if (level === 'nice_to_have' || level === 'nice-to-have') weight = 1;

                if (req && req.weight !== undefined && Number(req.weight) > 0) {
                    weight = Number(req.weight);
                }

                totalRequiredWeight += weight;

                if (normalizedCandidate.includes(reqSkill.toLowerCase().trim())) {
                    matchedWeight += weight;
                    matchedSkills.push(reqSkill);
                } else {
                    missingSkills.push(reqSkill);
                }
            });

            const score = totalRequiredWeight > 0
                ? Math.round(((matchedWeight / totalRequiredWeight) * 100) * 100) / 100
                : 0.0;

            let category = 'under_qualified';
            if (score >= 80.0) category = 'excellent';
            else if (score >= 50.0) category = 'good';

            return {
                score,
                category,
                matched_skills: matchedSkills,
                missing_skills: missingSkills,
                calculation_details: {
                    matched_weight: matchedWeight,
                    total_weight: totalRequiredWeight,
                },
            };
        },
    };

    // ---------------------------------------------------------------------
    // Guard halaman
    // ---------------------------------------------------------------------
    /**
     * Pastikan user sudah login (dan opsional punya role tertentu).
     * Kalau belum, redirect ke /login?next=<halaman sekarang>.
     * @returns {object|null} data user jika lolos
     */
    function requireAuth(roles) {
        const user = session.user();
        if (!session.isLoggedIn() || !user) {
            const next = encodeURIComponent(window.location.pathname + window.location.search);
            window.location.replace('/login?next=' + next);
            return null;
        }
        if (roles) {
            const allowed = Array.isArray(roles) ? roles : [roles];
            if (!allowed.includes(user.role) && user.role !== 'admin') {
                window.location.replace(homeFor(user));
                return null;
            }
        }
        return user;
    }

    /** Halaman utama sesuai role user. */
    function homeFor(user) {
        if (!user) return '/catalog';
        return user.role === 'recruiter' ? '/recruiter' : '/my-dock';
    }

    // ---------------------------------------------------------------------
    // Helper tampilan
    // ---------------------------------------------------------------------
    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatRupiah(num, compact = true) {
        if (num === null || num === undefined || num === '') return '-';
        const n = Number(num);
        if (compact) {
            if (n >= 1e9) return 'Rp' + (n / 1e9).toFixed(1).replace('.0', '') + 'M';
            if (n >= 1e6) return 'Rp' + (n / 1e6).toFixed(1).replace('.0', '') + 'jt';
        }
        return 'Rp' + n.toLocaleString('id-ID');
    }

    function formatSalaryRange(min, max) {
        if (!min && !max) return 'Gaji dirahasiakan';
        if (min && max) return formatRupiah(min) + ' - ' + formatRupiah(max);
        return formatRupiah(min || max);
    }

    function formatDate(iso, withTime = false) {
        if (!iso) return '-';
        const d = new Date(iso);
        if (isNaN(d)) return '-';
        const opts = { day: 'numeric', month: 'short', year: 'numeric' };
        if (withTime) Object.assign(opts, { hour: '2-digit', minute: '2-digit' });
        return d.toLocaleDateString('id-ID', opts);
    }

    function timeAgo(iso) {
        if (!iso) return '-';
        const diff = (Date.now() - new Date(iso).getTime()) / 1000;
        if (diff < 60) return 'baru saja';
        if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
        if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
        if (diff < 2592000) return Math.floor(diff / 86400) + ' hari lalu';
        return formatDate(iso);
    }

    function initials(name) {
        if (!name) return '?';
        return name.trim().split(/\s+/).slice(0, 2).map((p) => p[0].toUpperCase()).join('');
    }

    const STAGES = ['screening', 'assessment', 'interview', 'offering'];
    const STAGE_LABELS = {
        submitted: 'Terkirim',
        screening: 'Screening',
        assessment: 'Assessment',
        interview: 'Interview',
        offering: 'Offering',
        rejected: 'Ditolak',
        canceled: 'Dibatalkan',
    };
    const EMPLOYMENT_LABELS = {
        'full-time': 'Full-time',
        'part-time': 'Part-time',
        contract: 'Kontrak',
        internship: 'Magang',
        remote: 'Remote',
    };

    function stageLabel(stage) {
        return STAGE_LABELS[stage] || stage || '-';
    }

    /** Tahap berikutnya di pipeline (Screening -> Assessment -> Interview -> Offering). */
    function nextStage(stage) {
        if (stage === 'submitted') return 'screening';
        const i = STAGES.indexOf(stage);
        return i >= 0 && i < STAGES.length - 1 ? STAGES[i + 1] : null;
    }

    /** Warna badge berdasarkan skor match. */
    function scoreTone(score) {
        const s = Number(score) || 0;
        if (s >= 80) return { text: 'text-emerald-700', bg: 'bg-emerald-50', border: 'border-emerald-200', bar: 'bg-emerald-500' };
        if (s >= 60) return { text: 'text-sky-700', bg: 'bg-sky-50', border: 'border-sky-200', bar: 'bg-sky-500' };
        if (s >= 40) return { text: 'text-amber-700', bg: 'bg-amber-50', border: 'border-amber-200', bar: 'bg-amber-500' };
        return { text: 'text-rose-700', bg: 'bg-rose-50', border: 'border-rose-200', bar: 'bg-rose-500' };
    }

    /** Ambil nama skill dari requirement (bisa string atau object {skill, level, weight}). */
    function skillName(item) {
        if (!item) return '';
        if (typeof item === 'string') return item;
        return item.skill || item.name || '';
    }

    // ---------------------------------------------------------------------
    // Toast notifikasi
    // ---------------------------------------------------------------------
    function toast(message, type = 'success', timeout = 3500) {
        let wrap = document.getElementById('jh-toast-wrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.id = 'jh-toast-wrap';
            wrap.style.cssText = 'position:fixed;top:20px;right:20px;z-index:9999;display:flex;flex-direction:column;gap:10px;max-width:380px;';
            document.body.appendChild(wrap);
        }
        const colors = {
            success: ['#ecfdf5', '#065f46', '#10b981', 'check_circle'],
            error: ['#fef2f2', '#991b1b', '#ef4444', 'error'],
            info: ['#eff6ff', '#1e3a8a', '#3b82f6', 'info'],
            warning: ['#fffbeb', '#92400e', '#f59e0b', 'warning'],
        };
        const [bg, fg, accent, icon] = colors[type] || colors.info;
        const el = document.createElement('div');
        el.style.cssText = `background:${bg};color:${fg};border-left:4px solid ${accent};padding:12px 16px;border-radius:10px;box-shadow:0 10px 25px -5px rgba(0,0,0,.15);font:500 14px/1.4 Inter,system-ui,sans-serif;display:flex;gap:10px;align-items:flex-start;opacity:0;transform:translateX(20px);transition:all .25s ease;`;
        el.innerHTML = `<span class="material-symbols-outlined" style="font-size:20px;color:${accent}">${icon}</span><span>${escapeHtml(message)}</span>`;
        wrap.appendChild(el);
        requestAnimationFrame(() => {
            el.style.opacity = '1';
            el.style.transform = 'translateX(0)';
        });
        setTimeout(() => {
            el.style.opacity = '0';
            el.style.transform = 'translateX(20px)';
            setTimeout(() => el.remove(), 300);
        }, timeout);
    }

    /** Tampilkan error API sebagai toast (dengan pesan validasi kalau ada). */
    function showError(err, fallback = 'Terjadi kesalahan.') {
        console.error(err);
        const msg = err instanceof ApiError ? err.fullMessage() : (err && err.message) || fallback;
        toast(msg, 'error', 5000);
    }

    // ---------------------------------------------------------------------
    // Binding UI umum (navbar): pakai atribut data-jh-*
    //   data-jh-user-name     -> diisi nama user
    //   data-jh-user-role     -> diisi role user
    //   data-jh-user-initials -> diisi inisial nama
    //   data-jh-logout        -> klik untuk logout
    //   data-jh-guest-only    -> disembunyikan kalau sudah login
    //   data-jh-auth-only     -> disembunyikan kalau belum login
    // ---------------------------------------------------------------------
    function bindSessionUI(root = document) {
        const user = session.user();
        const loggedIn = session.isLoggedIn() && user;

        root.querySelectorAll('[data-jh-user-name]').forEach((el) => (el.textContent = loggedIn ? user.name : 'Tamu'));
        root.querySelectorAll('[data-jh-user-role]').forEach((el) => {
            el.textContent = loggedIn ? (user.role === 'recruiter' ? 'Recruiter' : user.role === 'admin' ? 'Admin' : 'Pelamar') : '';
        });
        root.querySelectorAll('[data-jh-user-initials]').forEach((el) => (el.textContent = loggedIn ? initials(user.name) : '?'));
        root.querySelectorAll('[data-jh-guest-only]').forEach((el) => (el.style.display = loggedIn ? 'none' : ''));
        root.querySelectorAll('[data-jh-auth-only]').forEach((el) => (el.style.display = loggedIn ? '' : 'none'));
        root.querySelectorAll('[data-jh-logout]').forEach((el) => {
            el.addEventListener('click', (e) => {
                e.preventDefault();
                auth.logout();
            });
        });
        root.querySelectorAll('[data-jh-home]').forEach((el) => {
            el.setAttribute('href', homeFor(user));
        });
    }

    /** Set state loading di tombol (disable + ganti teks). */
    function setLoading(button, loading, loadingText = 'Memproses...') {
        if (!button) return;
        if (loading) {
            button.dataset.jhOriginal = button.innerHTML;
            button.disabled = true;
            button.style.opacity = '0.7';
            button.innerHTML = `<span class="material-symbols-outlined animate-spin" style="font-size:18px;vertical-align:middle">progress_activity</span> ${escapeHtml(loadingText)}`;
        } else {
            button.disabled = false;
            button.style.opacity = '';
            if (button.dataset.jhOriginal) button.innerHTML = button.dataset.jhOriginal;
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => bindSessionUI());
    } else {
        bindSessionUI();
    }

    window.JH = {
        API_BASE,
        ApiError,
        session,
        api,
        auth,
        jobs,
        applications,
        pipelines,
        profile,
        matchmaking,
        requireAuth,
        homeFor,
        bindSessionUI,
        toast,
        showError,
        setLoading,
        util: {
            escapeHtml,
            formatRupiah,
            formatSalaryRange,
            formatDate,
            timeAgo,
            initials,
            stageLabel,
            nextStage,
            scoreTone,
            skillName,
            STAGES,
            STAGE_LABELS,
            EMPLOYMENT_LABELS,
        },
    };
})(window, document);

