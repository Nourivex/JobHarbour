import { CareerAtlas } from '../components/career-atlas.js';
import { jobs, matchmaking, session, util } from '../api.js';

const esc = util.escapeHtml;
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

/** Angka naik dari 0 ke nilai akhir, hanya sekali saat elemen terlihat. */
function countUp(el, target, duration = 900) {
    if (!el) return;
    const final = target.toLocaleString('id-ID');
    if (reduceMotion || document.hidden || target <= 0) {
        el.textContent = final;
        return;
    }
    const start = performance.now();
    const tick = (now) => {
        const t = Math.min((now - start) / duration, 1);
        el.textContent = Math.round(target * (1 - Math.pow(1 - t, 3))).toLocaleString('id-ID');
        if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
    // Pengaman: requestAnimationFrame berhenti di tab latar belakang
    setTimeout(() => { el.textContent = final; }, duration + 50);
}

function whenVisible(el, callback) {
    if (!el) return;
    if (!('IntersectionObserver' in window)) return callback();
    const io = new IntersectionObserver((entries) => {
        if (entries.some((e) => e.isIntersecting)) {
            io.disconnect();
            callback();
        }
    }, { threshold: 0.3 });
    io.observe(el);
}

/** Kartu yang dirender dari API ikut muncul bertahap. */
function revealChildren(container) {
    if (!container) return;
    [...container.children].forEach((child, i) => {
        child.setAttribute('data-reveal', '');
        child.style.setProperty('--reveal-delay', `${Math.min(i, 5) * 60}ms`);
        // setTimeout (bukan requestAnimationFrame) supaya tetap jalan walau tab sedang di latar belakang
        setTimeout(() => child.classList.add('is-visible'), 30);
        // Setelah selesai muncul, lepas atribut reveal agar efek hover kartu bebas memakai transform
        setTimeout(() => {
            child.removeAttribute('data-reveal');
            child.classList.remove('is-visible');
            child.style.removeProperty('--reveal-delay');
        }, 1000);
    });
}

/** Letakkan cahaya mercusuar tepat di lampu, mengikuti cara foto di-crop (object-cover). */
function positionBeacon() {
    const beacon = document.querySelector('.hero-beacon');
    const section = beacon?.parentElement;
    if (!beacon || !section) return;
    const imgW = 1922;
    const imgH = 818;
    const lampX = 1630 / imgW;
    const lampY = 205 / imgH;
    const w = section.clientWidth;
    const h = section.clientHeight;
    const scale = Math.max(w / imgW, h / imgH);
    const rw = imgW * scale;
    const rh = imgH * scale;
    const offsetX = (w - rw) * 0.72; // sama dengan object-position 72%
    const offsetY = (h - rh) * 0.5;
    beacon.style.setProperty('--beacon-x', `${offsetX + lampX * rw}px`);
    beacon.style.setProperty('--beacon-y', `${offsetY + lampY * rh}px`);
}

document.addEventListener('DOMContentLoaded', async () => {
    // Inisialisasi Real Geospatial Career Atlas (MapLibre GL)
    let atlasInstance = null;
    const atlasMapEl = document.getElementById('career-atlas-map');
    if (atlasMapEl) {
        atlasInstance = new CareerAtlas('career-atlas-map');
        window.careerAtlasInstance = atlasInstance;
        atlasInstance.init();
    }

    // Window helper untuk klik beacon distrik
    window.filterByDistrict = function(districtName) {
        window.location.href = '/catalog?location=' + encodeURIComponent(districtName);
    };

    // Render Dinamis Chip Keahlian Terpopuler dari Database
    function renderDynamicPopularSkills(jobsList) {
        const container = document.getElementById('hero-popular-skills-container');
        if (!container || !Array.isArray(jobsList)) return;

        const skillsTally = {};
        jobsList.forEach(job => {
            (job.requirements || []).forEach(req => {
                const s = req.skill;
                if (s) {
                    skillsTally[s] = (skillsTally[s] || 0) + 1;
                }
            });
        });

        // Urutkan berdasarkan frekuensi terbanyak
        const topSkills = Object.entries(skillsTally)
            .sort((a, b) => b[1] - a[1])
            .slice(0, 6)
            .map(entry => entry[0]);

        if (topSkills.length === 0) {
            container.innerHTML = '<span class="text-xs text-slate-400">Belum ada data keahlian</span>';
            return;
        }

        container.innerHTML = topSkills.map(skill => `
            <a href="/catalog?search=${encodeURIComponent(skill)}" class="px-3 py-1.5 rounded-full text-[13px] font-medium text-white bg-white/10 ring-1 ring-white/25 hover:bg-white/20 hover:ring-white/45 transition-colors cursor-pointer flex items-center gap-1">
                <span>${skill}</span>
            </a>
        `).join('');
    }

    const user = session.user();
    const isApplicant = session.isLoggedIn() && user && (user.role === 'applicant' || user.role === 'admin');
    const applicantSkills = isApplicant ? (user.skills || []) : [];

    positionBeacon();
    window.addEventListener('resize', positionBeacon, { passive: true });

    // 1. Pencarian hero
    document.getElementById('hero-search-form')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const keyword = document.getElementById('hero-search-keyword')?.value.trim();
        const location = document.getElementById('hero-search-location')?.value.trim();
        const params = new URLSearchParams();
        if (keyword) params.append('search', keyword);
        if (location) params.append('location', location);
        window.location.href = '/catalog' + (params.toString() ? '?' + params.toString() : '');
    });

    // Smart Location Dropdown (Floating Positioner) & Geolocation
    const locInput = document.getElementById('hero-search-location');
    const locDropdown = document.getElementById('smart-location-dropdown');
    const geoBtn = document.getElementById('geo-detect-btn');
    const locOptions = document.getElementById('smart-location-options');

    function positionFloatingDropdown() {
        if (!locInput || !locDropdown || locDropdown.classList.contains('hidden')) return;
        const rect = locInput.getBoundingClientRect();
        locDropdown.style.top = (rect.bottom + 8) + 'px';
        locDropdown.style.left = rect.left + 'px';
    }

    if (locInput && locDropdown) {
        locInput.addEventListener('focus', () => {
            locDropdown.classList.remove('hidden');
            positionFloatingDropdown();
        });

        window.addEventListener('scroll', positionFloatingDropdown, { passive: true });
        window.addEventListener('resize', positionFloatingDropdown, { passive: true });

        document.addEventListener('click', (e) => {
            const isClickInside = locInput.contains(e.target) || 
                                  locDropdown.contains(e.target) || 
                                  geoBtn?.contains(e.target);
            if (!isClickInside) {
                locDropdown.classList.add('hidden');
            }
        });

        locOptions?.querySelectorAll('button[data-val]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                locInput.value = btn.getAttribute('data-val');
                locDropdown.classList.add('hidden');
            });
        });

        geoBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            locInput.value = 'Tegal';
            locDropdown.classList.add('hidden');
        });
    }

    // 2. Sapaan untuk pelamar yang sudah login
    if (isApplicant) {
        document.getElementById('hero-title-guest')?.classList.add('hidden');
        const authTitle = document.getElementById('hero-title-auth');
        authTitle?.classList.remove('hidden');
        authTitle?.classList.add('hero-in');
        const name = document.getElementById('hero-auth-name');
        if (name) name.textContent = (user.name || '').split(' ')[0];
        const count = document.getElementById('hero-auth-skills-count');
        if (count) count.textContent = `Dihitung dari ${applicantSkills.length} skill di profilmu.`;
    }

    // 3. Simulator skor
    initSandbox();

    // 4. Data lowongan dari API: statistik, keahlian, lowongan terbaru
    const loading = document.getElementById('latest-jobs-loading');
    const grid = document.getElementById('latest-jobs-grid');
    const empty = document.getElementById('latest-jobs-empty');

    try {
        const res = await jobs.list({ per_page: 50, sort: 'latest', status: 'open' });
        const list = Array.isArray(res.data) ? res.data : [];
        loading?.classList.add('hidden');

        renderStats(list, res.meta?.pagination?.total ?? list.length);
        renderSkills(list);
        renderDynamicPopularSkills(list);

        if (!list.length) {
            empty?.classList.remove('hidden');
            return;
        }

        const withMatch = list.map((job) => ({
            ...job,
            _match: isApplicant ? matchmaking.calculate(applicantSkills, job.requirements || []) : null,
        }));

        if (isApplicant) {
            const top = [...withMatch].sort((a, b) => (b._match?.score || 0) - (a._match?.score || 0)).slice(0, 3);
            const topGrid = document.getElementById('hero-top-matched-grid');
            if (topGrid) {
                topGrid.innerHTML = top.map(jobCard).join('');
                document.getElementById('top-matched-section')?.classList.remove('hidden');
                revealChildren(topGrid);
            }
        }

        if (grid) {
            grid.innerHTML = withMatch.slice(0, 6).map(jobCard).join('');
            revealChildren(grid);
        }
    } catch (err) {
        console.error('Gagal memuat lowongan beranda:', err);
        loading?.classList.add('hidden');
        empty?.classList.remove('hidden');
        ['stat-jobs', 'stat-companies', 'stat-positions', 'stat-cities'].forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.textContent = '-';
        });
        const skillGrid = document.getElementById('skill-grid');
        if (skillGrid) skillGrid.innerHTML = '';
    }
});

function renderStats(list, total) {
    const companies = new Set(list.map((j) => (j.company || '').trim()).filter(Boolean)).size;
    const cities = new Set(list.map((j) => (j.location || '').trim().toLowerCase()).filter(Boolean)).size;
    const positions = list.reduce((sum, j) => sum + Math.max((Number(j.quota) || 0) - (Number(j.filled) || 0), 0), 0);
    const values = { 'stat-jobs': total, 'stat-companies': companies, 'stat-positions': positions, 'stat-cities': cities };
    const band = document.getElementById('stat-jobs')?.closest('[data-reveal]');
    whenVisible(band, () => {
        Object.entries(values).forEach(([id, value]) => countUp(document.getElementById(id), value));
    });
}

function renderSkills(list) {
    const skillGrid = document.getElementById('skill-grid');
    if (!skillGrid) return;
    const counts = new Map();
    list.forEach((job) => {
        const seen = new Set();
        (job.requirements || []).forEach((req) => {
            const name = util.skillName(req).trim();
            const key = name.toLowerCase();
            if (!name || seen.has(key)) return;
            seen.add(key);
            const entry = counts.get(key) || { name, jobs: 0 };
            entry.jobs += 1;
            counts.set(key, entry);
        });
    });
    const top = [...counts.values()].sort((a, b) => b.jobs - a.jobs || a.name.localeCompare(b.name)).slice(0, 6);
    if (!top.length) {
        skillGrid.innerHTML = '';
        return;
    }
    skillGrid.innerHTML = top.map((s, i) => `
        <button type="button" data-skill-tile="${esc(s.name)}" class="skill-tile flex flex-col items-start text-left rounded-2xl p-4 min-h-[168px] cursor-pointer">
            <span class="tile-icon w-10 h-10 rounded-xl flex items-center justify-center">${skillIcon(s.name)}</span>
            <span class="mt-4 block w-full text-[15px] font-bold font-heading leading-tight [overflow-wrap:break-word]">${esc(s.name)}</span>
            <span class="tile-meta block mt-1 text-[13px]">${s.jobs} lowongan</span>
            <span class="tile-arrow mt-auto pt-0 w-8 h-8 rounded-full flex items-center justify-center">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
        </button>
    `).join('');
    revealChildren(skillGrid);
    initTileGlow(skillGrid);
}

/** Ikon garis sederhana sesuai jenis skill. */
function skillIcon(name) {
    const n = name.toLowerCase();
    const paths = {
        container: '<path d="M21 8 12 3 3 8v8l9 5 9-5V8z"/><path d="M3 8l9 5 9-5M12 13v8"/>',
        database: '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v14c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 12c0 1.7 3.6 3 8 3s8-1.3 8-3"/>',
        code: '<path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/>',
        chart: '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
        layout: '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/>',
        cloud: '<path d="M17.5 19a4.5 4.5 0 1 0-1.4-8.8A6 6 0 0 0 4 12a4 4 0 0 0 .5 7z"/>',
        terminal: '<path d="m4 17 6-5-6-5M12 19h8"/>',
    };
    let key = 'code';
    if (/docker|kubernetes|container/.test(n)) key = 'container';
    else if (/sql|maria|mongo|redis|database/.test(n)) key = 'database';
    else if (/python|data|analytics|excel/.test(n)) key = 'chart';
    else if (/react|vue|tailwind|css|html|figma|ui/.test(n)) key = 'layout';
    else if (/aws|cloud|gcp|azure/.test(n)) key = 'cloud';
    else if (/linux|git|bash|shell/.test(n)) key = 'terminal';
    return `<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[key]}</svg>`;
}

/**
 * Kartu menyala mengikuti kursor: tepi kartu yang paling dekat dengan kursor ikut bercahaya,
 * diklik memberi efek tekan lalu membuka katalog yang sudah difilter.
 */
function initTileGlow(grid) {
    const tiles = [...grid.querySelectorAll('.skill-tile')];
    const finePointer = window.matchMedia('(pointer: fine)').matches;
    let frame = 0;
    let last = null;

    if (finePointer && !reduceMotion) {
        grid.addEventListener('pointermove', (e) => {
            last = e;
            if (frame) return;
            frame = requestAnimationFrame(() => {
                frame = 0;
                tiles.forEach((tile) => {
                    const r = tile.getBoundingClientRect();
                    const x = last.clientX - r.left;
                    const y = last.clientY - r.top;
                    // Jarak kursor ke kartu: 1 saat di atas kartu, memudar sampai 0 pada jarak 160px
                    const dx = Math.max(r.left - last.clientX, 0, last.clientX - r.right);
                    const dy = Math.max(r.top - last.clientY, 0, last.clientY - r.bottom);
                    const glow = Math.max(0, 1 - Math.hypot(dx, dy) / 160);
                    tile.style.setProperty('--mx', `${x}px`);
                    tile.style.setProperty('--my', `${y}px`);
                    tile.style.setProperty('--glow', glow.toFixed(2));
                });
            });
        });
        grid.addEventListener('pointerleave', () => {
            tiles.forEach((tile) => tile.style.setProperty('--glow', '0'));
        });
    }

    // Kembali lewat tombol Back (bfcache): matikan lagi kartu yang tadi diklik
    window.addEventListener('pageshow', () => {
        tiles.forEach((t) => {
            t.classList.remove('is-active');
            t.style.setProperty('--glow', '0');
        });
    });

    tiles.forEach((tile) => {
        tile.addEventListener('click', () => {
            tiles.forEach((t) => t.classList.remove('is-active'));
            tile.classList.add('is-active');
            const go = () => { window.location.href = '/catalog?skill=' + encodeURIComponent(tile.dataset.skillTile); };
            if (reduceMotion) go();
            else setTimeout(go, 220);
        });
    });
}

function jobCard(job) {
    const empLabel = util.EMPLOYMENT_LABELS[job.employment_type] || job.employment_type || 'Full-time';
    const salary = util.formatSalaryRange(job.salary_min, job.salary_max);
    const reqs = job.requirements || [];
    let badge = '';
    if (job._match) {
        const score = Math.round(job._match.score);
        const tone = util.scoreTone(score);
        badge = score > 0
            ? `<span class="shrink-0 px-2.5 py-1 rounded-full text-xs font-bold ${tone.bg} ${tone.text}">${score}% cocok</span>`
            : `<span class="shrink-0 px-2.5 py-1 rounded-full text-xs font-semibold bg-[#F4F6F8] text-[#627080]">Belum cocok</span>`;
    }
    return `
        <a href="/catalog?job=${job.id}" class="lift group flex flex-col bg-white rounded-2xl border border-[#E2E7ED] p-5 sm:p-6">
            <div class="flex items-start justify-between gap-3">
                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#F4F6F8] text-[#3D4B5C]">${esc(empLabel)}</span>
                ${badge}
            </div>
            <h3 class="mt-4 text-lg font-bold font-heading leading-snug text-[#0B1B2B] group-hover:text-[#C8460B] transition-colors">${esc(job.title)}</h3>
            <p class="mt-1 text-sm font-medium text-[#3D4B5C]">${esc(job.company)}</p>
            <p class="mt-3 text-sm text-[#627080]">${esc(job.location || '-')} &middot; <span class="font-semibold text-[#0B1B2B] num-tabular">${salary}</span></p>
            <div class="mt-4 flex flex-wrap gap-1.5">
                ${reqs.slice(0, 3).map((r) => `<span class="px-2 py-0.5 rounded-md text-xs font-medium bg-[#F4F6F8] text-[#3D4B5C]">${esc(util.skillName(r))}</span>`).join('')}
                ${reqs.length > 3 ? `<span class="px-1 text-xs text-[#627080]">+${reqs.length - 3}</span>` : ''}
            </div>
            <span class="mt-auto pt-5 inline-flex items-center gap-1.5 text-sm font-semibold text-[#C8460B]">
                Lihat detail
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </span>
        </a>
    `;
}

function initSandbox() {
    const requirements = [
        { skill: 'PHP', level: 'required', weight: 3 },
        { skill: 'Laravel', level: 'required', weight: 3 },
        { skill: 'Docker', level: 'important', weight: 2 },
        { skill: 'PostgreSQL', level: 'important', weight: 2 },
        { skill: 'Redis', level: 'nice_to_have', weight: 1 },
    ];
    const chips = document.querySelectorAll('[data-sandbox-toggle-skill]');
    if (!chips.length) return;
    const selected = new Set([...chips].filter((c) => c.getAttribute('aria-pressed') === 'true').map((c) => c.dataset.sandboxToggleSkill));
    const num = document.getElementById('sandbox-score-num');
    const bar = document.getElementById('sandbox-score-bar');
    const badge = document.getElementById('sandbox-score-badge');
    const message = document.getElementById('sandbox-score-message');
    let shown = 0;

    const update = (animate = true) => {
        const score = Math.round(matchmaking.calculate([...selected], requirements).score);
        const tone = util.scoreTone(score);
        if (bar) {
            bar.style.width = `${score}%`;
            bar.className = `h-full rounded-full transition-[width] duration-500 ease-out ${tone.bar}`;
        }
        if (badge) {
            badge.className = `text-xs font-bold px-2.5 py-1 rounded-full ${tone.bg} ${tone.text}`;
            badge.textContent = tone.categoryText;
        }
        if (message) {
            if (score >= 80) message.innerHTML = '<b class="text-[#157F3B]">Lolos screening otomatis.</b> Lamaranmu langsung lanjut ke tahap berikutnya.';
            else if (score >= 50) message.innerHTML = '<b class="text-[#1B65A6]">Ditinjau recruiter.</b> Tambah skill penting untuk mencapai 80%.';
            else message.innerHTML = '<b class="text-[#8A2E05]">Belum cocok.</b> Pilih skill wajib terlebih dahulu.';
        }
        if (num) {
            const from = shown;
            shown = score;
            if (!animate || reduceMotion || document.hidden) {
                num.textContent = `${score}%`;
                return;
            }
            const start = performance.now();
            const tick = (now) => {
                if (shown !== score) return; // klik baru sudah mengambil alih
                const t = Math.min((now - start) / 400, 1);
                num.textContent = `${Math.round(from + (score - from) * (1 - Math.pow(1 - t, 3)))}%`;
                if (t < 1) requestAnimationFrame(tick);
            };
            requestAnimationFrame(tick);
            setTimeout(() => { if (shown === score) num.textContent = `${score}%`; }, 450);
        }
    };

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            const name = chip.dataset.sandboxToggleSkill;
            const on = !selected.has(name);
            if (on) selected.add(name);
            else selected.delete(name);
            chip.setAttribute('aria-pressed', String(on));
            const icon = chip.querySelector('.check-indicator');
            if (icon) icon.textContent = on ? '✓' : '+';
            update();
        });
    });

    // Skor awal ikut "terisi" saat simulator pertama kali terlihat
    whenVisible(num, () => update(true));
}
