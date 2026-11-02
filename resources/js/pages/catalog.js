import { jobs, matchmaking, session, util } from '../api.js';
import { companyTile, icon, rupiah, salaryRange, savedJobs, jobSourceBadge, EMPLOYMENT_SHORT, scoreCategory } from '../job-ui.js';

const esc = util.escapeHtml;

// Redirect tautan lama /catalog?job=ID ke halaman detail penuh
const legacyJob = new URLSearchParams(location.search).get('job');
if (legacyJob && /^\d+$/.test(legacyJob)) location.replace('/jobs/' + legacyJob);

const user = session.user();
const isApplicant = session.isLoggedIn() && user && (user.role === 'applicant' || user.role === 'admin');
const skills = isApplicant ? (user.skills || []) : [];

const params = new URLSearchParams(location.search);
const state = {
    all: [],                 // semua lowongan terbuka (untuk opsi filter & ringkasan)
    results: [],             // hasil pencarian API saat ini
    search: params.get('search') || '',
    skill: params.get('skill') || '',
    sort: isApplicant ? 'match' : 'latest',
    match: new Set(),
    types: new Set(params.get('employment_type') ? [params.get('employment_type')] : []),
    cities: new Set(params.get('location') ? [params.get('location').toLowerCase()] : []),
    salary: 0,
};

const el = (id) => document.getElementById(id);

function withMatch(list) {
    return list.map((job) => {
        if (!isApplicant) return { ...job, _match: null };
        const m = matchmaking.calculate(skills, job.requirements || []);
        return { ...job, _match: { ...m, score: Math.round(m.score) } };
    });
}

async function fetchJobs(query = {}) {
    const res = await jobs.list({ per_page: 50, status: 'open', ...query });
    return withMatch(Array.isArray(res.data) ? res.data : []);
}

// ------------------------------------------------------------------ ringkasan header
function renderSummary() {
    const box = el('catalog-summary');
    if (!box) return;
    const total = state.all.length;
    if (isApplicant) {
        const scores = state.all.map((j) => j._match.score);
        const top = scores.length ? Math.max(...scores) : 0;
        const excellent = scores.filter((s) => s >= 80).length;
        const good = scores.filter((s) => s >= 50 && s < 80).length;
        const cat = scoreCategory(top);
        const deg = Math.round((top / 100) * 360);
        box.innerHTML = `
            <div class="flex items-center gap-3 pr-6 sm:border-r sm:border-[#E2E7ED]">
                <span class="score-ring w-14 h-14" style="--deg:${deg}deg;--ring:${cat.color}"><span class="text-sm font-extrabold text-[#0B1B2B] num-tabular">${top}%</span></span>
                <span><b class="block text-sm text-[#0B1B2B]">Skor tertinggimu</b><span class="text-[13px] text-[#627080]">dari ${skills.length} skill di profil</span></span>
            </div>
            ${statItem(excellent, 'Sangat Cocok', 'target', '#C8460B')}
            ${statItem(good, 'Cukup Cocok', 'layers', '#1B65A6')}
            ${statItem(total, 'Total Lowongan', 'briefcase', '#0C2238')}
        `;
    } else {
        const companies = new Set(state.all.map((j) => j.company)).size;
        const cities = new Set(state.all.map((j) => (j.location || '').toLowerCase())).size;
        box.innerHTML = `
            ${statItem(total, 'Total Lowongan', 'briefcase', '#0C2238')}
            ${statItem(companies, 'Perusahaan', 'building', '#1B65A6')}
            ${statItem(cities, 'Kota', 'pin', '#C8460B')}
            ${session.isLoggedIn() ? '' : `<a href="/login?next=${encodeURIComponent('/catalog')}" class="btn-press inline-flex items-center gap-1.5 h-10 px-4 rounded-lg bg-orange-harbour hover:bg-orange-harbour-hover text-white text-sm font-semibold">Masuk untuk lihat skor ${icon('arrow')}</a>`}
        `;
    }
}

function statItem(value, label, iconName, color) {
    const paths = {
        target: '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
        layers: '<path d="m12 3 9 5-9 5-9-5 9-5z"/><path d="m3 13 9 5 9-5"/>',
        briefcase: '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
        building: '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M8 10h.01M16 10h.01M12 10h.01M8 14h.01M16 14h.01M12 14h.01"/>',
        pin: '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    };
    return `
        <div class="flex items-center gap-2.5">
            <svg class="w-6 h-6 shrink-0" style="color:${color}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[iconName]}</svg>
            <span><b class="block text-xl leading-none font-extrabold font-heading text-[#0B1B2B] num-tabular">${value}</b><span class="text-[13px] text-[#627080]">${label}</span></span>
        </div>`;
}

// ------------------------------------------------------------------ opsi filter dari data
function renderFilterOptions() {
    const typeCounts = new Map();
    const cityCounts = new Map();
    state.all.forEach((j) => {
        typeCounts.set(j.employment_type, (typeCounts.get(j.employment_type) || 0) + 1);
        const key = (j.location || '').toLowerCase();
        if (key) cityCounts.set(key, { name: j.location, n: (cityCounts.get(key)?.n || 0) + 1 });
    });
    el('filter-types').innerHTML = [...typeCounts.entries()].map(([t, n]) => `
        <label class="filter-option"><input type="checkbox" name="type" value="${esc(t)}" ${state.types.has(t) ? 'checked' : ''}> <span>${esc(EMPLOYMENT_SHORT[t] || t)}</span><span class="filter-count">${n}</span></label>
    `).join('') || '<p class="text-sm text-[#627080]">-</p>';
    el('filter-cities').innerHTML = [...cityCounts.entries()].map(([key, c]) => `
        <label class="filter-option"><input type="checkbox" name="city" value="${esc(key)}" ${state.cities.has(key) ? 'checked' : ''}> <span>${esc(c.name)}</span><span class="filter-count">${c.n}</span></label>
    `).join('') || '<p class="text-sm text-[#627080]">-</p>';
    if (isApplicant) el('filter-match-group')?.classList.remove('hidden');
}

// ------------------------------------------------------------------ penyaringan & urutan
function filtered() {
    let list = state.results.filter((j) => {
        if (state.types.size && !state.types.has(j.employment_type)) return false;
        if (state.cities.size && !state.cities.has((j.location || '').toLowerCase())) return false;
        if (state.salary && Number(j.salary_max || j.salary_min || 0) < state.salary) return false;
        if (state.match.size && j._match && !state.match.has(scoreCategory(j._match.score).key)) return false;
        return true;
    });
    if (state.sort === 'match' && isApplicant) list = [...list].sort((a, b) => b._match.score - a._match.score);
    return list;
}

function renderList() {
    const list = filtered();
    const listEl = el('catalog-list');
    el('catalog-count').textContent = `${list.length} lowongan ditemukan`;
    el('catalog-empty').classList.toggle('hidden', list.length > 0);
    listEl.innerHTML = list.map(card).join('');
    renderChips();
    const activeCount = state.types.size + state.cities.size + state.match.size + (state.salary ? 1 : 0);
    const counter = el('filter-active-count');
    counter.textContent = `${activeCount} aktif`;
    counter.classList.toggle('hidden', !activeCount);
    const toggleCount = el('filter-toggle-count');
    toggleCount.textContent = activeCount;
    toggleCount.classList.toggle('hidden', !activeCount);
    el('filter-result-count').textContent = `${list.length} lowongan`;
    // Animasi masuk hanya saat daftar pertama kali tampil, bukan setiap filter berubah
    if (!state.animated) {
        state.animated = true;
        [...listEl.children].forEach((c, i) => {
            c.style.setProperty('--d', `${Math.min(i, 6) * 40}ms`);
            c.classList.add('hero-in');
        });
    }
}

function card(job) {
    const salary = salaryRange(job);
    const reqs = job.requirements || [];
    const saved = savedJobs.has(job.id);
    let badge = '';
    if (job._match && job._match.score === 0) {
        badge = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-semibold bg-[#F4F6F8] text-[#627080]">Belum cocok</span>';
    } else if (job._match) {
        const cat = scoreCategory(job._match.score);
        badge = `<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-bold" style="background:${cat.soft};color:${cat.color}">
            <span class="w-3.5 h-3.5 rounded-full flex items-center justify-center text-white" style="background:${cat.color}">${icon('check', 'w-2 h-2')}</span>
            ${job._match.score}% Match</span>`;
    }
    return `
        <article class="job-row group relative bg-white rounded-2xl border border-[#E2E7ED] p-4 sm:p-5 flex flex-col md:flex-row items-stretch md:items-start gap-4 sm:gap-5 transition-all">
            <div class="flex items-start gap-4 flex-1 min-w-0">
                ${companyTile(job.company)}
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        ${jobSourceBadge(job)}
                        ${badge || ''}
                    </div>
                    <h3 class="text-base sm:text-lg font-extrabold font-heading leading-snug text-[#0B1B2B]">
                        <a href="/jobs/${job.id}" class="job-row-link after:absolute after:inset-0 after:rounded-2xl focus-visible:outline-none hover:text-[#C8460B] transition-colors">${esc(job.title)}</a>
                    </h3>
                    <p class="mt-1 text-sm sm:text-[15px] font-medium text-[#3D4B5C]">${esc(job.company)}</p>
                    <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs sm:text-sm text-[#627080]">
                        <span class="inline-flex items-center gap-1.5">${icon('pin', 'w-4 h-4 text-[#8C98A8]')} ${esc(job.location || '-')}</span>
                        <span class="inline-flex items-center gap-1.5">${icon('briefcase', 'w-4 h-4 text-[#8C98A8]')} ${esc(EMPLOYMENT_SHORT[job.employment_type] || job.employment_type || '-')}</span>
                        <span class="inline-flex items-center gap-1.5">${icon('clock', 'w-4 h-4 text-[#8C98A8]')} ${esc(util.timeAgo(job.created_at))}</span>
                    </p>
                    ${reqs.length > 0 ? `
                        <div class="mt-3 flex flex-wrap items-center gap-1.5">
                            ${reqs.slice(0, 3).map((r) => `<span class="px-2.5 py-1 rounded-lg text-xs font-semibold bg-[#F4F6F8] text-[#3D4B5C] border border-[#E2E7ED]">${esc(util.skillName(r))}</span>`).join('')}
                            ${reqs.length > 3 ? `<span class="px-2 py-1 rounded-lg text-xs font-semibold bg-[#F4F6F8] text-[#627080] border border-[#E2E7ED]">+${reqs.length - 3}</span>` : ''}
                        </div>
                    ` : ''}
                </div>
            </div>

            <div class="md:w-52 shrink-0 flex md:flex-col justify-between md:items-end items-center border-t md:border-t-0 pt-3 md:pt-0 border-[#F0F3F6] gap-3">
                <div class="flex md:flex-col md:items-end items-center gap-2">
                    <p class="text-sm sm:text-[15px] font-semibold text-right">
                        ${salary ? `<b class="text-[#C8460B] num-tabular font-bold">${salary}</b> <span class="text-xs text-[#627080] font-normal">/ bulan</span>` : '<span class="text-xs text-[#8C98A8]">Gaji tidak dicantumkan</span>'}
                    </p>
                </div>
                <div class="relative z-10 flex items-center gap-2">
                    <button type="button" data-save-job="${job.id}" aria-pressed="${saved}" aria-label="${saved ? 'Hapus dari tersimpan' : 'Simpan lowongan'}" class="save-btn w-9 h-9 rounded-lg flex items-center justify-center text-[#627080] hover:text-[#C8460B] hover:bg-[#F4F6F8] transition-colors cursor-pointer">${icon('bookmark', 'w-4 h-4')}</button>
                    <span class="row-arrow w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center border border-[#E2E7ED] text-[#0B1B2B] group-hover:border-[#C8460B] group-hover:bg-[#C8460B] group-hover:text-white transition-all" aria-hidden="true">${icon('arrow', 'w-4 h-4')}</span>
                </div>
            </div>
        </article>`;
}

function renderChips() {
    const chips = [];
    if (state.search) chips.push({ label: `"${state.search}"`, clear: () => { state.search = ''; el('catalog-search').value = ''; return true; } });
    if (state.skill) chips.push({ label: `Skill: ${state.skill}`, clear: () => { state.skill = ''; return true; } });
    el('catalog-active-chips').innerHTML = chips.map((c, i) => `
        <button type="button" data-chip="${i}" class="inline-flex items-center gap-1.5 h-7 px-3 rounded-full text-xs font-semibold bg-[#0C2238] text-white cursor-pointer hover:bg-[#133150]">
            ${esc(c.label)} <span aria-hidden="true">&times;</span><span class="sr-only">hapus</span>
        </button>`).join('');
    el('catalog-active-chips').querySelectorAll('[data-chip]').forEach((b) => {
        b.addEventListener('click', () => { if (chips[b.dataset.chip].clear()) reload(); });
    });
}

// ------------------------------------------------------------------ pemuatan
async function reload() {
    const apiSort = state.sort === 'match' ? 'latest' : state.sort;
    try {
        state.results = await fetchJobs({ search: state.search, skill: state.skill, sort: apiSort });
        el('catalog-error').classList.add('hidden');
        renderList();
    } catch (err) {
        console.error(err);
        el('catalog-list').innerHTML = '';
        el('catalog-error').classList.remove('hidden');
        el('catalog-count').textContent = '';
    }
}

function resetFilters() {
    state.match.clear();
    state.types.clear();
    state.cities.clear();
    state.salary = 0;
    document.querySelectorAll('#catalog-filters input[type="checkbox"]').forEach((i) => { i.checked = false; });
    const all = document.querySelector('#catalog-filters input[name="salary"][value="0"]');
    if (all) all.checked = true;
    renderList();
}

/** Panel filter di ponsel dan tablet: dibuka sebagai panel bawah, ditutup lewat tombol, latar, atau Escape. */
function initMobileFilters() {
    const panel = el('filter-panel');
    const backdrop = el('filter-backdrop');
    const toggle = el('filter-toggle');
    const sheet = ['fixed', 'inset-x-0', 'bottom-0', 'z-50', 'max-h-[85vh]', 'overflow-y-auto', 'rounded-t-2xl'];
    const setOpen = (open) => {
        panel.classList.toggle('hidden', !open);
        sheet.forEach((c) => panel.classList.toggle(c, open));
        backdrop.classList.toggle('hidden', !open);
        document.body.classList.toggle('overflow-hidden', open);
        toggle.setAttribute('aria-expanded', String(open));
        if (open) panel.querySelector('summary, input')?.focus();
        else toggle.focus();
    };
    toggle.addEventListener('click', () => setOpen(true));
    document.querySelectorAll('[data-filter-close]').forEach((b) => b.addEventListener('click', () => setOpen(false)));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') setOpen(false);
    });
    // Saat layar dilebarkan ke desktop, kembalikan sidebar ke bentuk biasa
    window.matchMedia('(min-width: 1024px)').addEventListener('change', (m) => {
        if (m.matches && toggle.getAttribute('aria-expanded') === 'true') setOpen(false);
    });
}

document.addEventListener('DOMContentLoaded', async () => {
    initMobileFilters();
    const sortSel = el('catalog-sort');
    if (!isApplicant) sortSel.querySelector('[data-applicant-only]')?.remove();
    if (!isApplicant) el('catalog-subtitle').textContent = 'Cari dan bandingkan lowongan yang sedang dibuka. Masuk sebagai pelamar untuk melihat skor kecocokan dengan skill di profilmu.';
    sortSel.value = state.sort;
    el('catalog-search').value = state.search;

    try {
        state.all = await fetchJobs({ sort: 'latest' });
        renderSummary();
        renderFilterOptions();
    } catch (err) {
        console.error(err);
    }
    await reload();

    // Filter di sidebar
    el('catalog-filters').addEventListener('change', (e) => {
        const t = e.target;
        if (t.name === 'match') t.checked ? state.match.add(t.value) : state.match.delete(t.value);
        if (t.name === 'type') t.checked ? state.types.add(t.value) : state.types.delete(t.value);
        if (t.name === 'city') t.checked ? state.cities.add(t.value) : state.cities.delete(t.value);
        if (t.name === 'salary') state.salary = Number(t.value);
        renderList();
    });
    el('filter-reset').addEventListener('click', resetFilters);
    document.querySelector('[data-reset-filters]')?.addEventListener('click', () => {
        state.search = '';
        state.skill = '';
        el('catalog-search').value = '';
        resetFilters();
        reload();
    });

    // Pencarian (debounce) dan urutan
    let timer;
    el('catalog-search').addEventListener('input', (e) => {
        clearTimeout(timer);
        timer = setTimeout(() => { state.search = e.target.value.trim(); reload(); }, 300);
    });
    sortSel.addEventListener('change', () => { state.sort = sortSel.value; reload(); });

    // Simpan lowongan (di browser)
    el('catalog-list').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-save-job]');
        if (!btn) return;
        e.preventDefault();
        const saved = savedJobs.toggle(btn.dataset.saveJob);
        btn.setAttribute('aria-pressed', String(saved));
        btn.setAttribute('aria-label', saved ? 'Hapus dari tersimpan' : 'Simpan lowongan');
    });
});