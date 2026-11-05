/**
 * JobHarbour - API Client & Core Utilities (ES Module)
 *
 * Mengelola komunikasi REST API JSON, autentikasi JWT di localStorage,
 * kalkulasi skor pemadanan keahlian (Weighted Jaccard Similarity),
 * notifikasi toast, dan dialog konfirmasi kustom.
 */

const API_BASE = '/api';
const TOKEN_KEY = 'jh_token';
const USER_KEY = 'jh_user';

export class ApiError extends Error {
    constructor(message, status, payload) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.payload = payload || null;
        this.errors = (payload && payload.errors) || null;
        this.data = (payload && payload.data) || null;
    }

    fullMessage() {
        if (!this.errors) return this.message;
        const lines = Object.values(this.errors).flat();
        return lines.length ? lines.join(' ') : this.message;
    }
}

export const session = {
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

export async function request(path, options = {}) {
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
        throw new ApiError('Tidak dapat terhubung ke server. Periksa koneksi internet Anda.', 0);
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
        const message = (payload && payload.message) || `Permintaan gagal (HTTP ${response.status}).`;

        if (response.status === 401 && options.auth !== false && token) {
            session.clear();
            const next = encodeURIComponent(window.location.pathname + window.location.search);
            toast(payload?.error_code === 'TOKEN_REVOKED'
                ? 'Sesi ini sudah dikeluarkan. Silakan masuk kembali.'
                : 'Sesi Anda telah berakhir. Silakan masuk kembali.', 'warning', 4000);
            setTimeout(() => {
                window.location.href = '/login?next=' + next;
            }, 800);
        }

        throw new ApiError(message, response.status, payload);
    }

    return payload || { status: 'success', data: null };
}

export const api = {
    get: (path, query, opts = {}) => request(path, { ...opts, method: 'GET', query }),
    post: (path, body, opts = {}) => request(path, { ...opts, method: 'POST', body }),
    put: (path, body, opts = {}) => request(path, { ...opts, method: 'PUT', body }),
    patch: (path, body, opts = {}) => request(path, { ...opts, method: 'PATCH', body }),
    del: (path, opts = {}) => request(path, { ...opts, method: 'DELETE' }),
    upload: (path, formData, opts = {}) => request(path, { ...opts, method: 'POST', formData }),
    request,
};

export const auth = {
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
    /** Membatalkan semua token akun ini di server, termasuk perangkat ini. */
    async logoutAll() {
        await api.post('/auth/logout-all');
        session.clear();
    },
};

export const jobs = {
    list: (filters = {}) => api.get('/jobs', filters),
    show: (id) => api.get('/jobs/' + id),
    mine: () => api.get('/jobs/my'),
    create: (payload) => api.post('/jobs', payload),
    update: (id, payload) => api.patch('/jobs/' + id, payload),
    close: (id) => api.del('/jobs/' + id),
    appeal: (id, payload) => api.post('/jobs/' + id + '/appeal', payload),
};

/** Lonceng pemberitahuan milik pengguna yang sedang masuk. */
export const notifications = {
    list: () => api.get('/notifications'),
    read: (id) => api.patch('/notifications/' + id + '/read'),
    readAll: () => api.post('/notifications/read-all'),
};

/** Pengumuman situs yang sedang berlaku (publik). */
export const announcements = {
    active: () => api.get('/announcements/active'),
};

/** Laporan pengguna atas lowongan atau perusahaan. */
export const reports = {
    create: (payload) => api.post('/reports', payload),
};

export const applications = {
    list: () => api.get('/applications'),
    show: (id) => api.get('/applications/' + id),
    submit(payload) {
        if (payload.cv_file instanceof File) {
            const fd = new FormData();
            fd.append('job_id', payload.job_id || payload.job_vacancy_id);
            fd.append('job_vacancy_id', payload.job_vacancy_id || payload.job_id);
            if (payload.cover_letter) fd.append('cover_letter', payload.cover_letter);
            if (payload.candidate_skills) {
                fd.append('candidate_skills', Array.isArray(payload.candidate_skills)
                    ? JSON.stringify(payload.candidate_skills)
                    : payload.candidate_skills
                );
            }
            fd.append('cv_file', payload.cv_file);
            return api.upload('/applications', fd);
        }
        return api.post('/applications', {
            job_vacancy_id: payload.job_vacancy_id || payload.job_id,
            job_id: payload.job_id || payload.job_vacancy_id,
            cover_letter: payload.cover_letter,
            candidate_skills: payload.candidate_skills,
        });
    },
    cancel: (id) => api.del('/applications/' + id),
    // Catatan internal recruiter (tidak terlihat oleh pelamar)
    notes: (id) => api.get('/applications/' + id + '/notes'),
    addNote: (id, body) => api.post('/applications/' + id + '/notes', { body }),
    deleteNote: (id, noteId) => api.del('/applications/' + id + '/notes/' + noteId),
    async downloadCv(id) {
        const res = await fetch(API_BASE + '/applications/' + id + '/cv', {
            headers: { Authorization: 'Bearer ' + session.token(), Accept: 'application/pdf' },
        });
        if (!res.ok) {
            let msg = 'Berkas CV tidak tersedia atau Anda tidak memiliki izin.';
            try { msg = (await res.json()).message || msg; } catch (e) { /* abaikan */ }
            throw new ApiError(msg, res.status);
        }
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const win = window.open(url, '_blank');
        if (!win) {
            const a = document.createElement('a');
            a.href = url;
            a.download = `CV_Pelamar_${id}.pdf`;
            document.body.appendChild(a);
            a.click();
            a.remove();
        }
        setTimeout(() => URL.revokeObjectURL(url), 60000);
    },
};

export const pipelines = {
    timeline: (applicationId) => api.get('/pipelines/' + applicationId),
    updateStatus: (applicationId, payload) => api.patch('/pipelines/' + applicationId + '/status', payload),
};

export const profile = {
    updateSkills: (skills) => api.put('/profile/skills', { skills }),
    update: (payload) => api.put('/profile', payload),
    // Server mengeluarkan perangkat lain dan memberi token baru untuk perangkat ini
    async changePassword(payload) {
        const res = await api.put('/auth/password', payload);
        if (res.data?.token) session.save(res.data.token);
        return res;
    },
};

/** Unduh berkas yang dilindungi token lalu buka di tab baru (atau simpan bila pop-up diblokir). */
export async function downloadProtected(url, filename = 'dokumen') {
    const res = await fetch(url, { headers: { Authorization: 'Bearer ' + session.token() } });
    if (!res.ok) {
        let msg = 'Berkas tidak tersedia atau Anda tidak memiliki izin.';
        try { msg = (await res.json()).message || msg; } catch (e) { /* abaikan */ }
        throw new ApiError(msg, res.status);
    }
    const blobUrl = URL.createObjectURL(await res.blob());
    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(blobUrl), 60000);
}

export const company = {
    show: () => api.get('/company'),
    update: (payload) => api.put('/company', payload),
    uploadLogo(file) {
        const fd = new FormData();
        fd.append('logo', file);
        return api.upload('/company/logo', fd);
    },
    verification: () => api.get('/company/verification'),
    submitVerification: (formData) => api.upload('/company/verification', formData),
};

export const admin = {
    verifications: (status) => api.get('/admin/verifications', status ? { status } : undefined),
    review: (id, payload) => api.patch('/admin/verifications/' + id, payload),
    setCompanyStatus: (id, payload) => api.patch('/admin/companies/' + id + '/status', payload),
    // Admin Portal
    overview: () => api.get('/admin/overview'),
    users: (query) => api.get('/admin/users', query),
    user: (id) => api.get('/admin/users/' + id),
    setUserStatus: (id, payload) => api.patch('/admin/users/' + id + '/status', payload),
    companies: (query) => api.get('/admin/companies', query),
    company: (id) => api.get('/admin/companies/' + id),
    jobs: (query) => api.get('/admin/jobs', query),
    job: (id) => api.get('/admin/jobs/' + id),
    moderateJob: (id, payload) => api.patch('/admin/jobs/' + id + '/moderation', payload),
    auditLogs: (query) => api.get('/admin/audit-logs', query),
    sources: () => api.get('/aggregator/summary'),
    syncSources: (slug) => api.post('/aggregator/sync', slug ? { source_slug: slug } : {}),
    health: () => api.get('/health'),
    reports: (query) => api.get('/admin/reports', query),
    updateReport: (id, payload) => api.patch('/admin/reports/' + id, payload),
    settings: () => api.get('/admin/settings'),
    updateSettings: (payload) => api.put('/admin/settings', payload),
    announcements: () => api.get('/admin/announcements'),
    createAnnouncement: (payload) => api.post('/admin/announcements', payload),
    endAnnouncement: (id) => api.patch('/admin/announcements/' + id + '/end'),
    toggleSource: (id, payload) => api.patch('/admin/sources/' + id, payload),
    sourceLogs: (id) => api.get('/admin/sources/' + id + '/logs'),
};

const SKILL_ALIASES = {
    js: 'javascript',
    ts: 'typescript',
    golang: 'go',
    postgres: 'postgresql',
    reactjs: 'react',
    'react.js': 'react',
    vuejs: 'vue',
    'vue.js': 'vue',
    nodejs: 'node',
    'node.js': 'node',
    nextjs: 'next',
    'next.js': 'next',
    nuxtjs: 'nuxt',
    'nuxt.js': 'nuxt',
    k8s: 'kubernetes',
    aws: 'amazon web services',
    gcp: 'google cloud platform',
    'restful api': 'rest api',
    restful: 'rest api',
    rest: 'rest api',
    'ui/ux': 'ui ux',
    'ui ux': 'ui ux',
};

const SKILL_STOP_WORDS = [
    'framework', 'library', 'development', 'developer', 'programming',
    'language', 'database', 'db', 'technologies', 'technology', 'service', 'services',
];

function normalizeSkillStr(str) {
    let s = String(str || '').toLowerCase().trim();
    s = s.replace(/\.([a-z]+)/gi, '$1');
    s = s.replace(/[^a-z0-9\s]/gi, ' ');
    s = s.replace(/\s+/g, ' ').trim();
    return SKILL_ALIASES[s] || s;
}

function tokenizeSkillStr(cleanStr) {
    const words = cleanStr.split(' ').filter(Boolean);
    const filtered = words.filter((w) => !SKILL_STOP_WORDS.includes(w));
    return filtered.length > 0 ? filtered : words;
}

function matchSkill(reqStr, candidates) {
    const reqClean = normalizeSkillStr(reqStr);
    if (!reqClean) return null;
    const reqTokens = tokenizeSkillStr(reqClean);

    for (const cand of candidates) {
        if (reqClean === cand.clean) return cand.original;

        const isSubset1 = reqTokens.every((t) => cand.tokens.includes(t));
        const isSubset2 = cand.tokens.every((t) => reqTokens.includes(t));
        if (isSubset1 || isSubset2) return cand.original;

        const reqBound = new RegExp('\\b' + reqClean.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'i');
        const candBound = new RegExp('\\b' + cand.clean.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'i');
        if (reqBound.test(cand.clean) || candBound.test(reqClean)) return cand.original;
    }
    return null;
}

export const matchmaking = {
    calculate(candidateSkills = [], jobRequirements = []) {
        const normalizedCandidate = (candidateSkills || []).map((s) => {
            const raw = s && typeof s === 'object' ? String(s.name || s.skill || '') : String(s || '');
            const clean = normalizeSkillStr(raw);
            return clean ? { original: raw, clean, tokens: tokenizeSkillStr(clean) } : null;
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

            const matched = matchSkill(reqSkill, normalizedCandidate);
            if (matched !== null) {
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

export function requireAuth(roles) {
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

export function homeFor(user) {
    if (!user) return '/catalog';
    // Admin belum punya dasbor sendiri, jadi memakai Papan Rekrutmen dengan cakupan semua lowongan
    if (user.role === 'admin') return '/admin';
    return user.role === 'recruiter' ? '/recruiter' : '/my-dock';
}

export const util = {
    escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    },

    formatRupiah(num, compact = true) {
        if (num === null || num === undefined || num === '') return '-';
        const n = Number(num);
        if (compact) {
            if (n >= 1e9) return 'Rp' + (n / 1e9).toFixed(1).replace('.0', '') + ' M';
            if (n >= 1e6) return 'Rp' + (n / 1e6).toFixed(1).replace('.0', '') + ' jt';
        }
        return 'Rp' + n.toLocaleString('id-ID');
    },

    formatSalaryRange(min, max) {
        if (!min && !max) return 'Gaji dirahasiakan';
        if (min && max) return this.formatRupiah(min) + ' - ' + this.formatRupiah(max);
        return this.formatRupiah(min || max);
    },

    formatDate(iso, withTime = false) {
        if (!iso) return '-';
        const d = new Date(iso);
        if (isNaN(d)) return '-';
        const opts = { day: 'numeric', month: 'short', year: 'numeric' };
        if (withTime) Object.assign(opts, { hour: '2-digit', minute: '2-digit' });
        return d.toLocaleDateString('id-ID', opts);
    },

    timeAgo(iso) {
        if (!iso) return '-';
        const diff = (Date.now() - new Date(iso).getTime()) / 1000;
        if (diff < 60) return 'baru saja';
        if (diff < 3600) return Math.floor(diff / 60) + ' menit lalu';
        if (diff < 86400) return Math.floor(diff / 3600) + ' jam lalu';
        if (diff < 2592000) return Math.floor(diff / 86400) + ' hari lalu';
        return this.formatDate(iso);
    },

    initials(name) {
        if (!name) return '?';
        return name.trim().split(/\s+/).slice(0, 2).map((p) => p[0].toUpperCase()).join('');
    },

    STAGES: ['screening', 'assessment', 'interview', 'offering'],

    STAGE_LABELS: {
        submitted: 'Terkirim',
        screening: 'Screening Berkas',
        assessment: 'Uji Teknis',
        interview: 'Wawancara',
        offering: 'Penawaran',
        rejected: 'Ditolak',
        canceled: 'Dibatalkan',
    },

    EMPLOYMENT_LABELS: {
        'full-time': 'Purna Waktu (Full-time)',
        'part-time': 'Paruh Waktu (Part-time)',
        contract: 'Kontrak',
        internship: 'Magang',
        remote: 'Jarak Jauh (Remote)',
    },

    stageLabel(stage) {
        return this.STAGE_LABELS[stage] || stage || '-';
    },

    nextStage(stage) {
        if (stage === 'submitted') return 'screening';
        const i = this.STAGES.indexOf(stage);
        return i >= 0 && i < this.STAGES.length - 1 ? this.STAGES[i + 1] : null;
    },

    scoreCategory(score) {
        const s = Number(score) || 0;
        if (s >= 80) return { label: 'Sangat Cocok', desc: 'Memenuhi mayoritas kualifikasi utama' };
        if (s >= 50) return { label: 'Cocok', desc: 'Memenuhi sebagian kualifikasi penting' };
        return { label: 'Belum Cocok', desc: 'Perlu peningkatan keahlian terkait' };
    },

    scoreTone(score) {
        const s = Number(score) || 0;
        if (s >= 80) {
            return {
                text: 'text-[#157F3B]',
                bg: 'bg-[#E7F5EC]',
                border: 'border-[#157F3B]/30',
                bar: 'bg-[#157F3B]',
                categoryText: 'Sangat Cocok',
            };
        }
        if (s >= 50) {
            return {
                text: 'text-[#1B65A6]',
                bg: 'bg-[#EBF3FB]',
                border: 'border-[#1B65A6]/30',
                bar: 'bg-[#1B65A6]',
                categoryText: 'Cocok',
            };
        }
        return {
            text: 'text-[#8A2E05]',
            bg: 'bg-[#FFF3EA]',
            border: 'border-[#C8460B]/30',
            bar: 'bg-[#C8460B]',
            categoryText: 'Belum Cocok',
        };
    },

    skillName(item) {
        if (!item) return '';
        if (typeof item === 'string') return item;
        return item.skill || item.name || '';
    },
};

/**
 * Notifikasi singkat. opts opsional: detail (kalimat kedua) dan action ({ label, onClick }) untuk satu tombol lanjutan.
 */
export function toast(message, type = 'success', timeout = 3800, opts = {}) {
    let wrap = document.getElementById('jh-toast-wrap');
    if (!wrap) {
        wrap = document.createElement('div');
        wrap.id = 'jh-toast-wrap';
        wrap.setAttribute('role', 'status');
        wrap.setAttribute('aria-live', 'polite');
        wrap.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:8px;max-width:400px;width:calc(100% - 32px);pointer-events:none;';
        document.body.appendChild(wrap);
    }

    const icons = {
        success: `<svg class="w-5 h-5 text-[#157F3B] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>`,
        error: `<svg class="w-5 h-5 text-[#B42318] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>`,
        warning: `<svg class="w-5 h-5 text-[#C8460B] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>`,
        info: `<svg class="w-5 h-5 text-[#1B65A6] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>`,
    };

    const bgs = {
        success: 'bg-white border-[#E2E7ED] border-l-4 border-l-[#157F3B] text-[#0B1B2B]',
        error: 'bg-white border-[#E2E7ED] border-l-4 border-l-[#B42318] text-[#0B1B2B]',
        warning: 'bg-white border-[#E2E7ED] border-l-4 border-l-[#C8460B] text-[#0B1B2B]',
        info: 'bg-white border-[#E2E7ED] border-l-4 border-l-[#1B65A6] text-[#0B1B2B]',
    };

    const el = document.createElement('div');
    el.className = `p-4 rounded-xl shadow-lg border text-sm flex items-start gap-3 pointer-events-auto transition-all duration-200 transform translate-y-4 opacity-0 ${bgs[type] || bgs.info}`;
    el.innerHTML = `
        ${icons[type] || icons.info}
        <div class="flex-1 leading-relaxed">
            <p class="font-medium">${util.escapeHtml(message)}</p>
            ${opts.detail ? `<p class="mt-0.5 text-[13px] text-[#627080]">${util.escapeHtml(opts.detail)}</p>` : ''}
            ${opts.action ? `<button type="button" data-toast-action class="mt-2 inline-flex items-center h-8 px-3 rounded-lg text-[13px] font-semibold bg-[#0C2238] text-white hover:bg-[#133150] cursor-pointer">${util.escapeHtml(opts.action.label)}</button>` : ''}
        </div>
        <button type="button" data-toast-close aria-label="Tutup notifikasi" class="text-[#627080] hover:text-[#0B1B2B] text-xs ml-1 shrink-0 cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    `;

    wrap.appendChild(el);

    requestAnimationFrame(() => {
        el.classList.remove('translate-y-4', 'opacity-0');
        el.classList.add('translate-y-0', 'opacity-100');
    });

    let timer;
    const dismiss = () => {
        el.classList.remove('translate-y-0', 'opacity-100');
        el.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => el.remove(), 220);
    };

    const startTimer = () => { timer = setTimeout(dismiss, timeout); };
    const stopTimer = () => { clearTimeout(timer); };

    el.addEventListener('mouseenter', stopTimer);
    el.addEventListener('mouseleave', startTimer);
    el.querySelector('[data-toast-close]').addEventListener('click', dismiss);
    el.querySelector('[data-toast-action]')?.addEventListener('click', () => {
        dismiss();
        opts.action.onClick?.();
    });

    startTimer();
}

export function showError(err, fallback = 'Terjadi kesalahan sistem.') {
    console.error(err);
    const msg = err instanceof ApiError ? err.fullMessage() : (err && err.message) || fallback;
    toast(msg, 'error', 5000);
}

export function setLoading(button, loading, loadingText = 'Memproses...') {
    if (!button) return;
    if (loading) {
        button.dataset.jhOriginal = button.innerHTML;
        button.disabled = true;
        button.classList.add('opacity-70', 'cursor-not-allowed');
        button.innerHTML = `
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            ${util.escapeHtml(loadingText)}
        `;
    } else {
        button.disabled = false;
        button.classList.remove('opacity-70', 'cursor-not-allowed');
        if (button.dataset.jhOriginal) {
            button.innerHTML = button.dataset.jhOriginal;
        }
    }
}

/** Dialog konfirmasi custom berbasis Promise pengganti window.confirm. */
export function confirmDialog({ title = 'Konfirmasi Tindakan', message = 'Apakah Anda yakin ingin melanjutkan?', confirmText = 'Lanjutkan', cancelText = 'Batal', danger = false }) {
    return new Promise((resolve) => {
        let modalWrap = document.getElementById('jh-confirm-modal');
        if (modalWrap) modalWrap.remove();

        modalWrap = document.createElement('div');
        modalWrap.id = 'jh-confirm-modal';
        modalWrap.className = 'fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-[#0C2238]/60 backdrop-blur-xs transition-opacity duration-200';
        modalWrap.setAttribute('role', 'dialog');
        modalWrap.setAttribute('aria-modal', 'true');
        modalWrap.innerHTML = `
            <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-[#E2E7ED] transform transition-all duration-200 scale-95 opacity-0">
                <h3 class="text-lg font-bold text-[#0B1B2B]">${util.escapeHtml(title)}</h3>
                <p class="mt-2 text-sm text-[#3D4B5C] leading-relaxed">${util.escapeHtml(message)}</p>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" id="jh-confirm-cancel" class="px-4 py-2 rounded-lg text-sm font-semibold text-[#3D4B5C] bg-[#F4F6F8] hover:bg-[#E2E7ED] transition-colors btn-press">
                        ${util.escapeHtml(cancelText)}
                    </button>
                    <button type="button" id="jh-confirm-ok" class="px-4 py-2 rounded-lg text-sm font-semibold text-white ${danger ? 'bg-[#B42318] hover:bg-[#991b1b]' : 'bg-[#C8460B] hover:bg-[#993406]'} transition-colors btn-press shadow-sm">
                        ${util.escapeHtml(confirmText)}
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(modalWrap);
        const card = modalWrap.querySelector('div');

        requestAnimationFrame(() => {
            card.classList.remove('scale-95', 'opacity-0');
            card.classList.add('scale-100', 'opacity-100');
        });

        const prevFocused = document.activeElement;
        const btnCancel = modalWrap.querySelector('#jh-confirm-cancel');
        const btnOk = modalWrap.querySelector('#jh-confirm-ok');
        btnCancel.focus();

        const close = (result) => {
            card.classList.remove('scale-100', 'opacity-100');
            card.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modalWrap.remove();
                if (prevFocused && typeof prevFocused.focus === 'function') prevFocused.focus();
                resolve(result);
            }, 180);
        };

        btnCancel.addEventListener('click', () => close(false));
        btnOk.addEventListener('click', () => close(true));

        const keyHandler = (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
                window.removeEventListener('keydown', keyHandler);
                close(false);
            }
        };
        window.addEventListener('keydown', keyHandler);
    });
}

export { confirmDialog as confirm };

// Pasang ke objek global window.JH agar komponen inline / event handler bisa langsung memanggil
const JH = {
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
    toast,
    showError,
    setLoading,
    confirm: confirmDialog,
    util,
};

window.JH = JH;
export default JH;
