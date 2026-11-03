import { jobs, applications, matchmaking, reports, session, util } from '../api.js';
import { EMPLOYMENT_SHORT, companyTile, jobCode, salaryRange, remainingQuota, scoreCategory, savedJobs, icon } from '../job-ui.js';

const esc = util.escapeHtml;
const root = document.getElementById('job-detail-root');
const jobId = Number(root?.dataset.jobId);

const user = session.user();
const loggedIn = session.isLoggedIn() && user;
const isApplicant = loggedIn && (user.role === 'applicant' || user.role === 'admin');
const isRecruiter = loggedIn && user.role === 'recruiter';

const LEVELS = [
    { key: 'required', label: 'Syarat wajib', short: 'Wajib', weight: 3 },
    { key: 'important', label: 'Syarat penting', short: 'Penting', weight: 2 },
    { key: 'nice_to_have', label: 'Nilai tambah', short: 'Nilai tambah', weight: 1 },
];

function levelOf(req) {
    const l = String(req.level || 'required').toLowerCase().replace('-', '_');
    return LEVELS.some((x) => x.key === l) ? l : 'required';
}

function weightOf(req) {
    const w = Number(req.weight);
    return w > 0 ? w : (LEVELS.find((x) => x.key === levelOf(req))?.weight || 2);
}

function isExpired(job) {
    return job.expires_at && new Date(job.expires_at).getTime() < Date.now();
}

// ------------------------------------------------------------------ header
function renderHeader(job) {
    const salary = salaryRange(job);
    const saved = savedJobs.has(job.id);
    document.getElementById('detail-header').innerHTML = `
        <div class="flex flex-col md:flex-row gap-5">
            ${companyTile(job.company, 'w-20 h-20 sm:w-24 sm:h-24 text-2xl')}
            <div class="flex-1 min-w-0">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <h1 class="text-2xl sm:text-[1.9rem] leading-tight font-extrabold text-[#0B1B2B]">${esc(job.title)}</h1>
                        <div class="mt-1.5 flex flex-wrap items-center gap-2.5 font-semibold text-[#3D4B5C]">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M8 10h.01M16 10h.01M12 10h.01"/></svg>
                                ${esc(job.company)}
                            </span>
                            ${job.job_source_type === 'official_career_page' ? `
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-sky-50 text-sky-800 border border-sky-200">
                                    <svg class="w-3 h-3 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
                                    Kurasi Web Karir Resmi (${esc(job.location_data_source || 'Portal Resmi')})
                                </span>
                            ` : `
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                    <svg class="w-3 h-3 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    Mitra Terverifikasi JobHarbour
                                </span>
                            `}
                        </div>
                    </div>
                    <div class="flex gap-2 shrink-0">
                        <button type="button" id="detail-share" class="btn-press w-11 h-11 rounded-xl border border-[#E2E7ED] flex items-center justify-center text-[#0B1B2B] hover:border-[#C5CFDA] cursor-pointer" aria-label="Bagikan lowongan">${icon('share', 'w-[18px] h-[18px]')}</button>
                        <button type="button" id="detail-save" aria-pressed="${saved}" class="save-btn btn-press inline-flex items-center gap-2 h-11 px-4 rounded-xl border border-[#E2E7ED] font-semibold text-[#0B1B2B] hover:border-[#C5CFDA] cursor-pointer">${icon('bookmark', 'w-[18px] h-[18px]')}<span>${saved ? 'Tersimpan' : 'Simpan'}</span></button>
                    </div>
                </div>
                <div class="mt-4 flex flex-col xl:flex-row xl:items-end justify-between gap-4">
                    <p class="flex flex-wrap gap-x-5 gap-y-2 text-[15px] text-[#3D4B5C]">
                        <span class="inline-flex items-center gap-1.5">${icon('pin')} ${esc(job.location || '-')}</span>
                        <span class="inline-flex items-center gap-1.5">${icon('briefcase')} ${esc(EMPLOYMENT_SHORT[job.employment_type] || job.employment_type || '-')}</span>
                        <span class="inline-flex items-center gap-1.5">${icon('calendar')} Dipublikasikan ${esc(util.timeAgo(job.created_at))}</span>
                    </p>
                    <div class="shrink-0 flex items-center gap-3 rounded-2xl bg-[#FFF3EA] px-4 py-3">
                        <span class="text-[#C8460B]">${icon('wallet', 'w-6 h-6')}</span>
                        <span>
                            <b class="block font-heading text-[17px] text-[#0B1B2B] num-tabular">${salary || 'Gaji tidak dicantumkan'}</b>
                            ${salary ? '<span class="text-[13px] text-[#627080]">per bulan</span>' : ''}
                        </span>
                    </div>
                </div>
            </div>
        </div>`;

    document.getElementById('detail-save').addEventListener('click', (e) => {
        const now = savedJobs.toggle(job.id);
        e.currentTarget.setAttribute('aria-pressed', String(now));
        e.currentTarget.querySelector('span').textContent = now ? 'Tersimpan' : 'Simpan';
        flash(now ? 'Lowongan disimpan di perangkat ini.' : 'Lowongan dihapus dari tersimpan.');
    });

    document.getElementById('detail-share').addEventListener('click', async () => {
        const data = { title: job.title, text: `${job.title} di ${job.company}`, url: location.href };
        try {
            if (navigator.share) await navigator.share(data);
            else {
                await navigator.clipboard.writeText(location.href);
                flash('Tautan lowongan disalin.');
            }
        } catch (e) {
            /* dibatalkan pengguna */
        }
    });
}

function flash(message) {
    const t = document.createElement('div');
    t.setAttribute('role', 'status');
    t.className = 'hero-in fixed z-50 left-1/2 -translate-x-1/2 bottom-24 lg:bottom-8 px-4 py-2.5 rounded-xl bg-[#0C2238] text-white text-sm font-semibold shadow-xl';
    t.textContent = message;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2200);
}

// ------------------------------------------------------------------ tab detail
function renderDetail(job) {
    const reqs = job.requirements || [];
    const groups = LEVELS.map((lv) => ({ ...lv, items: reqs.filter((r) => levelOf(r) === lv.key) })).filter((g) => g.items.length);
    const chipClass = {
        required: 'bg-[#2B4A6B] text-white',
        important: 'bg-[#E7F2FB] text-[#124B7D]',
        nice_to_have: 'bg-white text-[#3D4B5C] ring-1 ring-[#C5CFDA]',
    };
    const deadline = job.expires_at ? util.formatDate(job.expires_at) : 'Tidak ditentukan';
    const rows = [
        ['Kode lowongan', jobCode(job)],
        ['Jenis kerja', EMPLOYMENT_SHORT[job.employment_type] || job.employment_type || '-'],
        ['Lokasi', job.location || '-'],
        ['Kuota', `${job.quota ?? '-'} kandidat`],
        ['Sisa kuota', `${remainingQuota(job)} posisi`],
        ['Batas lamar', `<span class="font-bold ${isExpired(job) ? 'text-[#B42318]' : 'text-[#C8460B]'}">${esc(deadline)}</span>`, true],
        ['Status', job.status === 'open' && !isExpired(job) ? '<span class="font-semibold text-[#157F3B]">Dibuka</span>' : '<span class="font-semibold text-[#B42318]">Ditutup</span>', true],
        ['Recruiter', job.recruiter?.name || '-'],
    ];

    document.getElementById('panel-detail').innerHTML = `
        <div class="grid xl:grid-cols-[minmax(0,1fr)_300px] gap-5">
            <div class="space-y-7">
                <section>
                    <h2 class="detail-h2"><span class="detail-h2-icon">${icon('briefcase', 'w-[18px] h-[18px]')}</span>Deskripsi Pekerjaan</h2>
                    <p class="mt-3 text-[15px] leading-relaxed text-[#3D4B5C] whitespace-pre-line">${esc(job.description || '-')}</p>
                </section>
                <section>
                    <h2 class="detail-h2"><span class="detail-h2-icon"><svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m16 18 6-6-6-6M8 6l-6 6 6 6"/></svg></span>Keahlian yang Dibutuhkan</h2>
                    <div class="mt-3 space-y-4">
                        ${groups.map((g) => `
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-[#627080]">${g.short} <span class="font-semibold normal-case tracking-normal">(bobot ${g.weight})</span></p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    ${g.items.map((r) => `<span class="px-3.5 py-1.5 rounded-full text-sm font-semibold ${chipClass[g.key]}">${esc(util.skillName(r))}</span>`).join('')}
                                </div>
                            </div>`).join('') || '<p class="text-sm text-[#627080]">Lowongan ini tidak mencantumkan syarat keahlian khusus.</p>'}
                    </div>
                </section>
            </div>
            <section class="self-start rounded-2xl border border-[#E2E7ED] p-5">
                <h2 class="detail-h2 text-base"><span class="detail-h2-icon">${icon('briefcase', 'w-[18px] h-[18px]')}</span>Informasi Pekerjaan</h2>
                <dl class="mt-3 divide-y divide-[#E2E7ED] text-sm">
                    ${rows.map(([k, v, raw]) => `<div class="flex justify-between gap-4 py-2.5"><dt class="text-[#627080]">${k}</dt><dd class="text-right font-medium text-[#0B1B2B] num-tabular">${raw ? v : esc(v)}</dd></div>`).join('')}
                </dl>
            </section>
        </div>`;
}

// ------------------------------------------------------------------ tab proses rekrutmen
function renderProcess(job) {
    const steps = [
        ['Screening', 'Skor kecocokan dihitung otomatis saat kamu melamar. Skor 80% ke atas langsung lolos, skor 50% sampai 79% ditinjau recruiter.'],
        ['Assessment', 'Tes atau tugas teknis dari recruiter untuk menguji keahlian yang dibutuhkan.'],
        ['Wawancara', 'Jadwal, jenis (online atau offline), dan pewawancara dikirim lewat linimasa lamaranmu.'],
        ['Penawaran', 'Kandidat terpilih menerima penawaran kerja.'],
    ];
    document.getElementById('panel-process').innerHTML = `
        <p class="text-[15px] text-[#3D4B5C]">Semua lamaran di JobHarbour melewati tahap berikut. Tahap hanya bergerak maju, dan recruiter dapat menolak di tahap mana pun.</p>
        <ol class="mt-6 space-y-0">
            ${steps.map(([t, d], i) => `
                <li class="relative pl-14 pb-7 last:pb-0">
                    ${i < steps.length - 1 ? '<span class="absolute left-[19px] top-10 bottom-0 w-0.5 bg-[#E2E7ED]" aria-hidden="true"></span>' : ''}
                    <span class="absolute left-0 top-0 w-10 h-10 rounded-full bg-[#0C2238] text-white font-heading font-extrabold flex items-center justify-center">${i + 1}</span>
                    <h3 class="pt-1.5 text-lg font-extrabold text-[#0B1B2B]">${t}</h3>
                    <p class="mt-1 text-[15px] text-[#3D4B5C] leading-relaxed">${d}</p>
                </li>`).join('')}
        </ol>
        <p class="mt-6 rounded-xl bg-[#F4F6F8] px-4 py-3 text-sm text-[#3D4B5C]">Batas lamar: <b class="text-[#0B1B2B]">${esc(job.expires_at ? util.formatDate(job.expires_at) : 'tidak ditentukan')}</b></p>`;
}

function initTabs() {
    const tabs = [...document.querySelectorAll('[role="tab"]')];
    const select = (tab) => {
        tabs.forEach((t) => {
            const on = t === tab;
            t.setAttribute('aria-selected', String(on));
            t.tabIndex = on ? 0 : -1;
            document.getElementById(t.getAttribute('aria-controls')).classList.toggle('hidden', !on);
        });
    };
    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => select(tab));
        tab.addEventListener('keydown', (e) => {
            if (e.key !== 'ArrowRight' && e.key !== 'ArrowLeft') return;
            const next = tabs[(i + (e.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
            select(next);
            next.focus();
        });
    });
}

// ------------------------------------------------------------------ analisis kecocokan
function applyButton(job, applied) {
    const closed = job.status !== 'open' || isExpired(job);
    if (applied) {
        return `<a href="/my-dock" class="btn-press w-full inline-flex items-center justify-center gap-2 h-14 rounded-2xl bg-[#E7F5EC] text-[#157F3B] text-lg font-extrabold">${icon('check', 'w-5 h-5')} Sudah dilamar, lihat status</a>`;
    }
    if (closed) {
        return '<p class="w-full text-center h-14 leading-[3.5rem] rounded-2xl bg-[#F4F6F8] text-[#627080] font-bold">Lowongan sudah ditutup</p>';
    }
    if (isRecruiter) {
        return '<p class="rounded-2xl bg-[#F4F6F8] px-4 py-3 text-sm text-[#3D4B5C]">Kamu masuk sebagai recruiter. Hanya akun pelamar yang dapat melamar.</p>';
    }
    // Jika lowongan bersumber dari kurasi karir luar (External Ingestion)
    if (job.source_type === 'external' && job.application_url) {
        return `
            <div class="space-y-3">
                <a href="${job.application_url}" target="_blank" rel="noopener noreferrer" class="btn-press group w-full inline-flex items-center justify-center gap-2.5 h-14 rounded-2xl bg-[#1B65A6] hover:bg-[#134E82] text-white text-base sm:text-lg font-extrabold shadow-[0_14px_28px_-14px_rgba(27,101,166,0.9)]">
                    <svg class="w-5 h-5 transition-transform group-hover:translate-x-0.5 group-hover:-translate-y-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Lamar di Situs Resmi Perusahaan
                </a>
                <p class="text-[11px] text-[#627080] text-center leading-relaxed">
                    ℹ️ Anda akan diarahkan langsung ke portal rekrutmen resmi <strong class="text-[#0B1B2B]">${esc(job.company)}</strong>. JobHarbour tidak memungut biaya apa pun.
                </p>
            </div>
        `;
    }

    const href = loggedIn ? `/apply?job=${job.id}` : `/login?next=${encodeURIComponent('/jobs/' + job.id)}`;
    return `<a href="${href}" class="btn-press group w-full inline-flex items-center justify-center gap-2.5 h-14 rounded-2xl bg-orange-harbour hover:bg-orange-harbour-hover text-white text-lg font-extrabold shadow-[0_14px_28px_-14px_rgba(200,70,11,0.8)]">
        <svg class="w-5 h-5 transition-transform group-hover:-translate-y-0.5 group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2 11 13M22 2l-7 20-4-9-9-4z"/></svg>
        Lamar Sekarang</a>`;
}

function renderMatch(job, applied) {
    const box = document.getElementById('detail-match');
    const heading = `<h2 class="flex items-center gap-2.5 text-lg font-extrabold text-[#0B1B2B]"><svg class="w-6 h-6 text-[#F26A1B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/></svg>Analisis Kecocokan Profilmu</h2>`;

    if (!isApplicant) {
        box.innerHTML = `${heading}
            <div class="mt-5 rounded-2xl bg-[#F4F6F8] p-5 text-center">
                <p class="font-bold text-[#0B1B2B]">${isRecruiter ? 'Skor kecocokan khusus pelamar' : 'Masuk untuk melihat skor kecocokanmu'}</p>
                <p class="mt-1 text-sm text-[#3D4B5C]">Skor dihitung dari skill di profilmu terhadap ${(job.requirements || []).length} syarat lowongan ini.</p>
                ${loggedIn ? '' : `<a href="/login?next=${encodeURIComponent('/jobs/' + job.id)}" class="btn-press mt-4 inline-flex items-center gap-2 h-10 px-4 rounded-lg bg-[#0C2238] text-white text-sm font-semibold">Masuk ${icon('arrow')}</a>`}
            </div>
            <div class="mt-5">${applyButton(job, applied)}</div>`;
        return;
    }

    const skills = user.skills || [];
    const owned = new Set(skills.map((s) => String(typeof s === 'object' ? (s.name || s.skill) : s).toLowerCase().trim()));
    const reqs = job.requirements || [];
    const result = matchmaking.calculate(skills, reqs);
    const score = Math.round(result.score);
    const cat = scoreCategory(score);

    const levels = LEVELS.map((lv) => {
        const items = reqs.filter((r) => levelOf(r) === lv.key);
        const got = items.filter((r) => owned.has(util.skillName(r).toLowerCase().trim()));
        const max = items.reduce((s, r) => s + weightOf(r), 0);
        const have = got.reduce((s, r) => s + weightOf(r), 0);
        return { ...lv, total: items.length, matched: got.length, pct: max ? Math.round((have / max) * 100) : null };
    }).filter((l) => l.total);

    // Rekomendasi: skill yang belum dimiliki, diurutkan dari dampak terbesar ke skor
    const recs = reqs
        .filter((r) => !owned.has(util.skillName(r).toLowerCase().trim()))
        .map((r) => ({ name: util.skillName(r), level: levelOf(r), after: Math.round(matchmaking.calculate([...skills, util.skillName(r)], reqs).score) }))
        .sort((a, b) => b.after - a.after)
        .slice(0, 3);

    const desc = score >= 80
        ? 'Kamu memenuhi kriteria utama. Lamaranmu akan langsung lolos tahap screening.'
        : score >= 50
            ? 'Cukup sesuai. Lamaranmu akan ditinjau langsung oleh recruiter.'
            : 'Masih ada syarat penting yang belum terpenuhi. Lengkapi skill sebelum melamar.';

    box.innerHTML = `${heading}
        <div class="mt-5 flex items-center gap-5">
            <span class="score-ring score-ring-lg w-[124px] h-[124px] shrink-0" data-score="${score}" style="--deg:0deg;--ring:${cat.color}">
                <span class="text-[2rem] font-extrabold font-heading text-[#0B1B2B] num-tabular" data-score-num>0%</span>
            </span>
            <div>
                <p class="text-xl font-extrabold font-heading" style="color:${cat.color}">${cat.label}</p>
                <p class="mt-1 text-sm leading-relaxed text-[#3D4B5C]">${desc}</p>
            </div>
        </div>

        <div class="mt-6 space-y-3.5">
            ${levels.map((l) => `
                <div class="grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-3 gap-y-1.5">
                    <span class="text-sm text-[#3D4B5C]">${l.label} <span class="text-[#627080]">(${l.matched}/${l.total} skill)</span></span>
                    <b class="text-sm text-[#0B1B2B] num-tabular">${l.pct}%</b>
                    <span class="col-span-2 h-2 rounded-full bg-[#E8EDF2] overflow-hidden"><span class="match-bar block h-full rounded-full" style="--w:${l.pct}%;background:${l.pct >= 80 ? '#157F3B' : l.pct >= 50 ? '#1B65A6' : '#C8460B'}"></span></span>
                </div>`).join('')}
        </div>

        ${result.missing_skills.length ? `
        <div class="mt-6 rounded-2xl bg-[#FFF6E5] border border-[#F6DFB4] p-4">
            <p class="flex items-center gap-2 font-extrabold text-[#8A4B00]">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.7V17h8v-2.3A7 7 0 0 0 12 2z"/></svg>
                Rekomendasi Pengembangan
            </p>
            <ul class="mt-2 space-y-1.5 text-sm text-[#5C4A2E]">
                ${recs.map((r) => `<li>Tambahkan <b>${esc(r.name)}</b> ke profilmu, skor naik menjadi <b class="num-tabular">${r.after}%</b>.</li>`).join('')}
            </ul>
        </div>` : `
        <p class="mt-6 rounded-2xl bg-[#E7F5EC] px-4 py-3 text-sm font-semibold text-[#157F3B]">Semua skill yang diminta sudah ada di profilmu.</p>`}

        <div class="mt-6 flex flex-col gap-3">
            ${applyButton(job, applied)}
            <a href="/profile" class="btn-press w-full inline-flex items-center justify-center gap-2 h-12 rounded-2xl border border-[#C5CFDA] font-semibold text-[#0B1B2B] hover:border-[#0C2238]">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/></svg>
                Perbarui skill di profil
            </a>
        </div>
        <p class="mt-4 text-xs text-[#627080]">Skor dihitung dari ${skills.length} skill di profilmu dengan bobot wajib 3, penting 2, nilai tambah 1.</p>`;

    animateMatch(box, score);
}

/** Lingkaran skor dan bar terisi sekali saat tampil. */
function animateMatch(box, score) {
    const ring = box.querySelector('[data-score]');
    const num = box.querySelector('[data-score-num]');
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const finish = () => {
        ring.style.setProperty('--deg', `${Math.round((score / 100) * 360)}deg`);
        num.textContent = `${score}%`;
        box.querySelectorAll('.match-bar').forEach((b) => b.classList.add('is-filled'));
    };
    if (reduce || document.hidden) return finish();
    const start = performance.now();
    const tick = (now) => {
        const t = Math.min((now - start) / 900, 1);
        const v = score * (1 - Math.pow(1 - t, 3));
        ring.style.setProperty('--deg', `${(v / 100) * 360}deg`);
        num.textContent = `${Math.round(v)}%`;
        if (t < 1) requestAnimationFrame(tick);
    };
    requestAnimationFrame(tick);
    setTimeout(() => box.querySelectorAll('.match-bar').forEach((b) => b.classList.add('is-filled')), 60);
    setTimeout(finish, 950);
}

// ------------------------------------------------------------------ muat data
document.addEventListener('DOMContentLoaded', async () => {
    // Tombol kembali: pakai riwayat jika datang dari katalog
    const back = document.getElementById('detail-back');
    if (document.referrer && new URL(document.referrer).pathname.startsWith('/catalog')) {
        back.addEventListener('click', (e) => { e.preventDefault(); history.back(); });
    }

    initTabs();

    let job;
    try {
        const res = await jobs.show(jobId);
        job = res.data;
    } catch (err) {
        document.getElementById('detail-content').classList.add('hidden');
        document.getElementById('detail-error').classList.remove('hidden');
        document.getElementById('detail-crumb').textContent = 'Tidak ditemukan';
        if (err.status !== 404) {
            document.getElementById('detail-error-title').textContent = 'Lowongan gagal dimuat';
            document.getElementById('detail-error-text').textContent = 'Periksa koneksi ke API, lalu muat ulang halaman.';
        }
        return;
    }

    document.title = `${job.title} - ${job.company} | JobHarbour`;
    document.getElementById('detail-crumb').textContent = job.title;
    renderHeader(job);
    renderDetail(job);
    renderProcess(job);

    // Cek apakah pelamar sudah pernah melamar lowongan ini
    let applied = false;
    if (isApplicant) {
        try {
            const res = await applications.list();
            const list = Array.isArray(res.data) ? res.data : (res.data?.data || []);
            applied = list.some((a) => Number(a.job_vacancy_id) === job.id && a.status !== 'canceled');
        } catch (e) {
            /* daftar lamaran gagal dimuat: tombol lamar tetap tampil, backend menolak duplikat dengan 409 */
        }
    }
    renderMatch(job, applied);
    renderStickyApply(job, applied);
    initReport(job);
});

/**
 * Laporan lowongan oleh pengguna yang sudah masuk. Tamu diarahkan ke halaman masuk lalu kembali ke sini.
 * Error dari server (mis. sudah pernah melapor) ditampilkan di dialog tanpa menutupnya.
 */
function initReport(job) {
    const trigger = document.getElementById('detail-report');
    const dialog = document.getElementById('report-dialog');
    const form = document.getElementById('report-form');
    const done = document.getElementById('report-done');
    const error = document.getElementById('report-error');
    const submit = document.getElementById('report-submit');
    if (!trigger || !dialog) return;

    if (loggedIn && (user.role === 'recruiter' || user.role === 'admin')) {
        // Recruiter tidak melaporkan lowongannya sendiri; admin langsung memoderasi dari Admin Portal
        trigger.parentElement.classList.add('hidden');
        return;
    }

    const close = () => dialog.close();
    dialog.querySelectorAll('[data-report-close]').forEach((b) => b.addEventListener('click', close));
    dialog.addEventListener('click', (e) => { if (e.target === dialog) close(); });

    trigger.addEventListener('click', () => {
        if (!loggedIn) {
            window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
            return;
        }
        form.reset();
        form.classList.remove('hidden');
        done.classList.add('hidden');
        error.textContent = '';
        dialog.showModal();
        setTimeout(() => form.elements.description.focus(), 30);
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const description = form.elements.description.value.trim();
        if (description.length < 20) {
            error.textContent = 'Ceritakan minimal 20 karakter agar admin bisa memeriksanya.';
            form.elements.description.focus();
            return;
        }
        submit.disabled = true;
        submit.textContent = 'Mengirim...';
        try {
            await reports.create({ target_type: 'job', target_id: job.id, category: form.elements.category.value, description });
            form.classList.add('hidden');
            done.classList.remove('hidden');
            done.querySelector('[data-report-close]').focus();
        } catch (err) {
            error.textContent = err?.fullMessage?.() || err?.message || 'Laporan gagal dikirim. Coba lagi.';
        } finally {
            submit.disabled = false;
            submit.textContent = 'Kirim laporan';
        }
    });
}

/**
 * Bar lamar yang menempel di bawah layar (ponsel dan tablet saja). Di layar kecil panel skor dan tombol
 * Lamar berada jauh di bawah deskripsi, jadi aksi utama dibuat selalu terjangkau. Bar disembunyikan
 * selama tombol lamar utama sudah terlihat supaya tidak tampil dobel.
 */
function renderStickyApply(job, applied) {
    const main = document.getElementById('detail-match');
    if (!main || document.getElementById('detail-sticky-apply')) return;
    const salary = salaryRange(job);
    const bar = document.createElement('div');
    bar.id = 'detail-sticky-apply';
    bar.className = 'lg:hidden fixed left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-[#E2E7ED] px-4 py-3 flex items-center gap-3 transition-transform duration-200';
    bar.style.bottom = 'calc(var(--jh-bottom-nav, 61px) + env(safe-area-inset-bottom, 0px))';
    bar.innerHTML = `
        <div class="min-w-0 flex-1">
            <p class="text-sm font-bold text-[#0B1B2B] truncate">${esc(job.title)}</p>
            <p class="text-xs text-[#627080] truncate">${esc(salary || job.company || '')}</p>
        </div>
        <div class="shrink-0 w-[46%] max-w-[220px] [&_a]:h-11 [&_a]:text-sm [&_a]:rounded-xl [&_a]:shadow-none [&_p]:h-11 [&_p]:leading-[2.75rem] [&_p]:text-sm [&_p]:rounded-xl [&_p:not(:first-child)]:hidden [&_.space-y-3>p]:hidden">${applyButton(job, applied)}</div>`;
    document.body.appendChild(bar);

    // Sembunyikan bar saat panel kecocokan (berisi tombol lamar utama) ada di layar
    const target = main;
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            bar.style.transform = entry.isIntersecting ? 'translateY(150%)' : 'none';
            bar.setAttribute('aria-hidden', String(entry.isIntersecting));
        }).observe(target);
    }
}
