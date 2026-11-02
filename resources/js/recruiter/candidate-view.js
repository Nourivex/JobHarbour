/**
 * Tampilan kandidat yang dipakai di laci Pipeline dan halaman Detail Kandidat:
 * penjelasan skor Weighted Jaccard, skill, profil, dokumen, riwayat, linimasa, dan aksi tahap.
 */
import { applications, toast, confirm } from '../api.js';
import { moveCandidate } from './data.js';
import { setBusy } from './components.js';
import {
    esc, icon, STAGE, STAGE_ID, LEVEL, stageOf, nextStageOf, interviewOf, scoreInfo, scoreRing, matchBreakdown,
    formatDate, formatDateTime, relativeTime, stageBadge, errorMessage,
} from './ui.js';

export function matchSection(app, job) {
    const info = scoreInfo(app.match_score);
    const b = matchBreakdown(app, job);
    const missingRequired = b.rows.filter((r) => !r.ok && r.level === 'required');

    const headline = !b.rows.length
        ? 'Syarat skill lowongan tidak tersedia untuk dijelaskan.'
        : missingRequired.length
            ? `Belum memiliki ${missingRequired.length} skill wajib: ${missingRequired.map((r) => esc(r.name)).join(', ')}.`
            : 'Semua skill wajib terpenuhi.';

    return `
        <section aria-labelledby="cv-match-${app.id}">
            <div class="flex items-center gap-4">
                ${scoreRing(app.match_score, 'lg')}
                <div class="min-w-0">
                    <h3 id="cv-match-${app.id}" class="text-[17px] font-bold" style="color:${info.color}">${info.label}</h3>
                    <p class="ws-meta">${info.advice}</p>
                    <p class="text-sm mt-1 text-[var(--ws-text-2)]">${headline}</p>
                </div>
            </div>

            ${b.rows.length ? `
            <div class="mt-5 rounded-xl border border-[var(--ws-border)] p-4">
                <p class="text-sm font-semibold">Cara skor dihitung</p>
                <p class="ws-meta mt-0.5">Setiap syarat punya bobot (Wajib 3, Penting 2, Bonus 1). Skor adalah bobot skill yang dimiliki dibagi total bobot syarat.</p>
                <div class="ws-weight mt-3" role="img" aria-label="${b.earned} dari ${b.total} bobot terpenuhi">
                    ${b.rows.map((r) => `<span style="width:${(r.weight / b.total) * 100}%;background:${r.ok ? '#16A34A' : 'transparent'};border-right:2px solid var(--ws-surface)" title="${esc(r.name)} (${LEVEL[r.level].label}, bobot ${r.weight}): ${r.ok ? 'dimiliki' : 'belum dimiliki'}"></span>`).join('')}
                </div>
                <p class="mt-2 text-sm ws-num"><strong>${b.earned}</strong> dari <strong>${b.total}</strong> bobot terpenuhi &nbsp;→&nbsp; ${b.earned} ÷ ${b.total} × 100 = <strong>${b.total ? Math.round((b.earned / b.total) * 100) : 0}%</strong></p>
                ${Math.round((b.earned / (b.total || 1)) * 100) !== info.score ? `<p class="ws-meta mt-1">Skor tersimpan ${info.score}% dihitung saat melamar; syarat lowongan mungkin berubah sesudahnya.</p>` : ''}
                ${missingRequired.length ? `<p class="mt-3 text-sm text-[var(--ws-text-2)]"><strong>Perlu digali saat seleksi:</strong> ${missingRequired.map((r) => esc(r.name)).join(', ')}. Skill bisa saja dimiliki tetapi belum ditulis di profil.</p>` : ''}

                <ul class="mt-4 divide-y divide-[var(--ws-border)]">
                    ${b.rows.map((r) => `
                        <li class="flex items-center gap-3 py-2">
                            <span class="w-6 h-6 shrink-0 rounded-full inline-flex items-center justify-center ${r.ok ? 'bg-[var(--ws-success-soft)] text-[var(--ws-success)]' : 'bg-[var(--ws-danger-soft)] text-[var(--ws-danger)]'}">${icon(r.ok ? 'check' : 'x', 'w-3.5 h-3.5')}</span>
                            <span class="flex-1 min-w-0 truncate text-sm font-medium">${esc(r.name)}</span>
                            <span class="ws-meta whitespace-nowrap">${LEVEL[r.level].label}</span>
                            <span class="w-12 text-right text-sm ws-num ${r.ok ? 'font-semibold text-[var(--ws-success)]' : 'text-[var(--ws-muted)]'}">${r.ok ? '+' : ''}${r.ok ? r.weight : 0}/${r.weight}</span>
                        </li>`).join('')}
                </ul>
            </div>` : ''}
            <p class="ws-callout mt-4">${icon('info', 'w-4 h-4 shrink-0')}<span>Skor dihitung dengan Weighted Jaccard dari skill yang ditulis kandidat. Gunakan sebagai alat bantu menyaring, bukan penentu otomatis diterima atau ditolak.</span></p>
        </section>`;
}

// ------------------------------------------------------------------ catatan recruiter
const noteDate = (iso) => (iso ? new Date(iso).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '');

function noteItem(n) {
    return `
        <li class="ws-note" data-note="${n.id}">
            <div class="ws-note-meta">
                <span class="font-semibold text-[var(--ws-text)]">${esc(n.author?.name || 'Recruiter')}</span>
                <span>${noteDate(n.created_at)}</span>
                ${n.can_delete ? `<button type="button" class="ws-icon-btn w-7 h-7 ml-auto" data-note-delete="${n.id}" aria-label="Hapus catatan">${icon('trash-2', 'w-3.5 h-3.5')}</button>` : ''}
            </div>
            <p class="ws-note-body">${esc(n.body)}</p>
        </li>`;
}

/** Kerangka bagian catatan. Isi dimuat oleh bindNotes() supaya tidak memperlambat tampilan awal. */
export function notesSection(app) {
    return `
        <section aria-labelledby="cv-notes-${app.id}" data-notes-root="${app.id}">
            <div class="flex items-center justify-between gap-3">
                <h3 id="cv-notes-${app.id}" class="ws-h2">Catatan recruiter</h3>
                <span class="inline-flex items-center gap-1.5 ws-meta">${icon('lock', 'w-3.5 h-3.5')}Tidak terlihat oleh pelamar</span>
            </div>
            <form class="mt-3 space-y-2" data-note-form novalidate>
                <label for="cv-note-new-${app.id}" class="sr-only">Tulis catatan</label>
                <textarea id="cv-note-new-${app.id}" name="body" rows="2" maxlength="2000" class="ws-input" placeholder="Contoh: Komunikasi baik, perlu dites ulang di SQL."></textarea>
                <p class="ws-error" data-error-for="body"></p>
                <div class="flex justify-end"><button type="submit" class="ws-btn ws-btn-secondary ws-btn-sm">${icon('sticky-note')}Simpan catatan</button></div>
            </form>
            <ul class="mt-3 space-y-2" data-note-list><li>${'<div class="h-14 rounded-xl animate-skeleton ws-skel"></div>'}</li></ul>
        </section>`;
}

/** Muat, tambah, dan hapus catatan untuk satu lamaran di dalam root. */
export async function bindNotes(root, app) {
    const box = root.querySelector(`[data-notes-root="${app.id}"]`);
    if (!box) return;
    const list = box.querySelector('[data-note-list]');
    const form = box.querySelector('[data-note-form]');
    const empty = '<li class="ws-meta">Belum ada catatan. Catat kesan screening atau hasil wawancara agar keputusan mudah ditelusuri.</li>';

    const paint = (notes) => { list.innerHTML = notes.length ? notes.map(noteItem).join('') : empty; };
    let notes = [];
    try {
        notes = (await applications.notes(app.id)).data || [];
        paint(notes);
    } catch (err) {
        list.innerHTML = `<li class="ws-error">${esc(errorMessage(err, 'Catatan gagal dimuat.'))}</li>`;
    }

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const err = form.querySelector('[data-error-for="body"]');
        err.textContent = '';
        const body = form.body.value.trim();
        if (!body) {
            err.textContent = 'Tulis isi catatan terlebih dahulu.';
            form.body.focus();
            return;
        }
        const btn = form.querySelector('[type="submit"]');
        setBusy(btn, true, 'Menyimpan...');
        try {
            const res = await applications.addNote(app.id, body);
            notes = [res.data, ...notes];
            paint(notes);
            form.reset();
            toast('Catatan tersimpan.', 'success');
        } catch (ex) {
            err.textContent = ex?.errors?.body?.[0] || errorMessage(ex, 'Catatan gagal disimpan.');
        }
        setBusy(btn, false);
    });

    list.addEventListener('click', async (e) => {
        const del = e.target.closest('[data-note-delete]');
        if (!del) return;
        if (!(await confirm({ title: 'Hapus catatan?', message: 'Catatan yang dihapus tidak bisa dikembalikan.', confirmText: 'Hapus', cancelText: 'Batal', danger: true }))) return;
        try {
            await applications.deleteNote(app.id, del.dataset.noteDelete);
            notes = notes.filter((n) => String(n.id) !== del.dataset.noteDelete);
            paint(notes);
            toast('Catatan dihapus.', 'success');
        } catch (ex) {
            toast(errorMessage(ex, 'Catatan gagal dihapus.'), 'error');
        }
    });
}

export function skillsSection(app) {
    const matched = app.matched_skills || [];
    const missing = app.missing_skills || [];
    const lower = new Set(matched.map((s) => String(s).toLowerCase()));
    const other = (app.applicant?.skills || []).filter((s) => !lower.has(String(s).toLowerCase()));
    const group = (title, list, cls, ic) => (list.length ? `
        <div>
            <p class="ws-meta mb-2">${title}</p>
            <div class="flex flex-wrap gap-1.5">${list.map((s) => `<span class="ws-chip ${cls}">${ic ? icon(ic, 'w-3 h-3') : ''}${esc(s)}</span>`).join('')}</div>
        </div>` : '');
    return `
        <section class="space-y-4">
            <h3 class="ws-h2">Skill</h3>
            ${group(`Cocok dengan syarat (${matched.length})`, matched, 'ws-chip-ok', 'check')}
            ${group(`Belum dimiliki (${missing.length})`, missing, 'ws-chip-miss', 'x')}
            ${group(`Skill lain di profil (${other.length})`, other, '', '')}
            ${!matched.length && !missing.length && !other.length ? '<p class="ws-meta">Kandidat belum mencantumkan skill.</p>' : ''}
        </section>`;
}

/** Skill di profil kandidat yang tidak diminta lowongan, jadi tidak ikut dihitung dalam skor. */
export function otherSkillsSection(app) {
    const norm = (x) => String(x || '').toLowerCase().trim();
    const known = new Set([...(app.matched_skills || []), ...(app.missing_skills || [])].map(norm));
    const other = (app.applicant?.skills || []).filter((s) => !known.has(norm(s)));
    return other.length ? `
        <section class="space-y-2">
            <h3 class="ws-h2">Skill lain di profil</h3>
            <p class="ws-meta">Tidak dihitung dalam skor karena tidak diminta lowongan ini.</p>
            <div class="flex flex-wrap gap-1.5">${other.map((s) => `<span class="ws-chip">${esc(s)}</span>`).join('')}</div>
        </section>` : '';
}

export function profileSection(app) {
    const a = app.applicant || {};
    const rows = [
        ['mail', a.email, a.email ? `mailto:${a.email}` : null],
        ['phone', a.phone, a.phone ? `tel:${a.phone}` : null],
        ['map-pin', a.location, null],
    ];
    return `
        <section class="space-y-3">
            <h3 class="ws-h2">Profil</h3>
            ${a.bio ? `<p class="text-sm leading-relaxed text-[var(--ws-text-2)]">${esc(a.bio)}</p>` : '<p class="ws-meta">Kandidat belum menulis ringkasan profil.</p>'}
            <ul class="space-y-2 text-sm">
                ${rows.map(([ic, v, href]) => `
                    <li class="flex items-center gap-2.5 text-[var(--ws-text-2)]">
                        <span class="text-[var(--ws-muted)]">${icon(ic)}</span>
                        ${v ? (href ? `<a href="${esc(href)}" class="text-[var(--ws-info)] hover:underline break-all">${esc(v)}</a>` : esc(v)) : '<span class="ws-meta">Belum diisi</span>'}
                    </li>`).join('')}
            </ul>
        </section>`;
}

export function documentsSection(app) {
    return `
        <section class="space-y-3">
            <h3 class="ws-h2">Dokumen lamaran</h3>
            <div class="rounded-xl border border-[var(--ws-border)] p-4">
                <p class="text-sm font-semibold mb-1.5">Surat lamaran</p>
                ${app.cover_letter
                    ? `<p class="text-sm leading-relaxed text-[var(--ws-text-2)] whitespace-pre-line">${esc(app.cover_letter)}</p>`
                    : '<p class="ws-meta">Tidak menyertakan surat lamaran.</p>'}
            </div>
            ${app.cv_file_path
                ? `<button type="button" class="ws-btn ws-btn-secondary w-full sm:w-auto" data-download-cv="${app.id}">${icon('file-text')}Unduh CV (PDF)</button>`
                : '<p class="ws-meta">Kandidat tidak mengunggah CV.</p>'}
        </section>`;
}

export function historySection(app, allApps = []) {
    const others = allApps.filter((x) => Number(x.user_id) === Number(app.user_id) && Number(x.id) !== Number(app.id));
    return `
        <section class="space-y-3">
            <h3 class="ws-h2">Riwayat lamaran</h3>
            ${others.length ? `<ul class="space-y-2">${others.map((o) => `
                <li><a href="/recruiter/candidates/${o.id}" class="flex items-center gap-3 rounded-xl border border-[var(--ws-border)] p-3 hover:border-[var(--ws-border-strong)]">
                    <span class="flex-1 min-w-0"><span class="block text-sm font-semibold truncate">${esc(o.job_vacancy?.title || '-')}</span><span class="ws-meta">Melamar ${formatDate(o.created_at)} · skor ${Math.round(o.match_score || 0)}%</span></span>
                    ${stageBadge(stageOf(o))}
                </a></li>`).join('')}</ul>`
                : '<p class="ws-meta">Ini satu-satunya lamaran kandidat ke lowonganmu.</p>'}
        </section>`;
}

export function timelineSection(app) {
    const stages = app.pipeline_timeline?.stages || [];
    if (!stages.length) return '<section><h3 class="ws-h2 mb-3">Linimasa</h3><p class="ws-meta">Belum ada riwayat tahap.</p></section>';
    const isHired = stageOf(app) === 'hired';
    return `
        <section>
            <h3 class="ws-h2 mb-3">Linimasa</h3>
            <ol class="ws-timeline">
                ${stages.map((s, i) => {
                    const meta = STAGE[s.stage] || STAGE.screening;
                    const last = i === stages.length - 1;
                    const status = s.status === 'passed' ? 'Lolos' : s.status === 'rejected' ? 'Ditolak' : 'Berjalan';
                    const sch = s.interview_schedule;
                    return `
                    <li class="ws-tl ${last && !isHired ? 'is-current' : ''}" style="--c:${meta.color}">
                        <p class="text-sm"><strong>${meta.label}</strong> <span class="ws-meta">· ${status} · ${formatDate(s.entered_at, true)}</span></p>
                        ${s.notes ? `<p class="text-sm mt-1 text-[var(--ws-text-2)]">${esc(s.notes)}</p>` : ''}
                        ${sch?.datetime ? `
                            <div class="mt-2 rounded-xl bg-[var(--ws-surface-2)] p-3 text-sm space-y-1">
                                <p class="flex items-center gap-2 font-semibold">${icon('calendar-clock')}${formatDateTime(sch.datetime)} · ${sch.type === 'offline' ? 'Tatap muka' : 'Online'}</p>
                                ${sch.interviewer ? `<p class="text-[var(--ws-text-2)]">Pewawancara: ${esc(sch.interviewer)}</p>` : ''}
                                ${sch.meeting_url ? (/^https?:\/\//.test(sch.meeting_url) ? `<a href="${esc(sch.meeting_url)}" target="_blank" rel="noopener" class="text-[var(--ws-info)] hover:underline break-all">${esc(sch.meeting_url)}</a>` : `<p>${esc(sch.meeting_url)}</p>`) : ''}
                            </div>` : ''}
                    </li>`;
                }).join('')}
                ${isHired ? `<li class="ws-tl is-current" style="--c:${STAGE.hired.color}"><p class="text-sm"><strong>Hired</strong> <span class="ws-meta">· ${formatDate(stages[stages.length - 1].completed_at, true)}</span></p></li>` : ''}
            </ol>
        </section>`;
}

/**
 * Panel aksi tahap. mode: null (tombol), 'advance', atau 'reject'.
 * onMoved(app) dipanggil setelah tahap berhasil diubah.
 */
export function renderActions(container, app, { mode = null, onMoved = () => {} } = {}) {
    const stage = stageOf(app);
    const next = nextStageOf(stage);

    if (stage === 'hired' || stage === 'rejected') {
        container.innerHTML = `
            <div class="flex items-center gap-3 text-sm">
                ${icon(stage === 'hired' ? 'party-popper' : 'circle-x', 'w-5 h-5')}
                <p class="flex-1">${stage === 'hired' ? 'Kandidat sudah diterima bekerja.' : 'Kandidat tidak dilanjutkan.'} Tahap akhir tidak dapat diubah.</p>
            </div>`;
        return;
    }

    const rerender = (m) => renderActions(container, app, { mode: m, onMoved });

    if (!mode) {
        container.innerHTML = `
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" class="ws-btn ws-btn-danger-ghost" data-mode="reject">${icon('circle-x')}Tolak</button>
                <button type="button" class="ws-btn ws-btn-primary ml-auto" data-mode="advance">${next === 'hired' ? icon('party-popper') : icon('arrow-right')}${next === 'hired' ? 'Tandai Hired' : `Majukan ke ${STAGE[next].label}`}</button>
            </div>
            <p class="ws-meta mt-2">Tahap hanya bisa maju. Keputusan tidak dapat dibatalkan.</p>`;
        container.querySelectorAll('[data-mode]').forEach((b) => b.addEventListener('click', () => rerender(b.dataset.mode)));
        return;
    }

    const isReject = mode === 'reject';
    const target = isReject ? 'rejected' : next;
    const needsSchedule = target === 'interview';
    container.innerHTML = `
        <form class="space-y-3" novalidate>
            <p class="font-semibold">${isReject ? 'Tolak kandidat' : target === 'hired' ? 'Tandai kandidat sebagai Hired' : `Majukan ke ${STAGE[target].label}`}</p>
            ${target === 'hired' ? '<p class="ws-meta">Gunakan setelah kandidat menerima tawaran kerja.</p>' : ''}
            ${needsSchedule ? `
                <div class="grid sm:grid-cols-2 gap-3">
                    <div><label class="ws-label" for="cv-iv-date-${app.id}">Waktu interview</label><input id="cv-iv-date-${app.id}" name="datetime" type="datetime-local" class="ws-input"><p class="ws-error" data-error-for="datetime"></p></div>
                    <div><label class="ws-label" for="cv-iv-type-${app.id}">Bentuk</label><select id="cv-iv-type-${app.id}" name="type" class="ws-input"><option value="online">Online</option><option value="offline">Tatap muka</option></select></div>
                    <div><label class="ws-label" for="cv-iv-who-${app.id}">Pewawancara</label><input id="cv-iv-who-${app.id}" name="interviewer" type="text" class="ws-input"></div>
                    <div><label class="ws-label" for="cv-iv-url-${app.id}">Tautan atau lokasi</label><input id="cv-iv-url-${app.id}" name="meeting_url" type="text" class="ws-input" placeholder="https://meet.google.com/..."></div>
                </div>` : ''}
            <div>
                <label class="ws-label" for="cv-note-${app.id}">${isReject ? 'Alasan penolakan' : 'Catatan'} ${isReject ? '' : '<span class="opt">(opsional)</span>'}</label>
                <textarea id="cv-note-${app.id}" name="notes" rows="2" maxlength="1000" class="ws-input" placeholder="${isReject ? 'contoh: Belum menguasai Redis yang wajib untuk posisi ini.' : 'Catatan untuk tim atau tahap berikutnya'}"></textarea>
                <p class="ws-error" data-error-for="notes"></p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" class="ws-btn ws-btn-ghost" data-cancel>Batal</button>
                <button type="submit" class="ws-btn ${isReject ? 'ws-btn-danger' : 'ws-btn-primary'}">${isReject ? 'Tolak kandidat' : target === 'hired' ? 'Tandai Hired' : 'Majukan'}</button>
            </div>
        </form>`;

    const form = container.querySelector('form');
    form.querySelector('[data-cancel]').addEventListener('click', () => rerender(null));
    form.querySelector(needsSchedule ? 'input' : 'textarea').focus();
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        form.querySelectorAll('.ws-error').forEach((el) => { el.textContent = ''; });
        const notes = form.notes.value.trim();
        const extra = {};
        if (isReject && !notes) {
            form.querySelector('[data-error-for="notes"]').textContent = 'Tulis alasan singkat supaya keputusan bisa ditelusuri.';
            form.notes.focus();
            return;
        }
        if (notes) extra.notes = notes;
        if (needsSchedule) {
            if (!form.datetime.value) {
                form.querySelector('[data-error-for="datetime"]').textContent = 'Pilih tanggal dan jam interview.';
                form.datetime.focus();
                return;
            }
            extra.interview_schedule = {
                datetime: form.datetime.value,
                type: form.type.value,
                interviewer: form.interviewer.value.trim() || null,
                meeting_url: form.meeting_url.value.trim() || null,
            };
        }
        const btn = form.querySelector('[type="submit"]');
        setBusy(btn, true, 'Menyimpan...');
        try {
            await moveCandidate(app, target, extra);
            const name = app.applicant?.name || 'Kandidat';
            toast(target === 'rejected' ? `${name} ditandai ${STAGE_ID.rejected.toLowerCase()}.` : target === 'hired' ? `${name} resmi diterima.` : `${name} maju ke ${STAGE[target].label}.`, 'success');
            onMoved(app, target);
        } catch (err) {
            setBusy(btn, false);
            toast(errorMessage(err, 'Tahap kandidat gagal diperbarui.'), 'error', 5000);
        }
    });
}

/** Pasang unduh CV pada tombol [data-download-cv] di dalam root. */
export function bindCvDownload(root) {
    root.querySelectorAll('[data-download-cv]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            setBusy(btn, true, 'Mengunduh...');
            try {
                await applications.downloadCv(btn.dataset.downloadCv);
            } catch (err) {
                toast(errorMessage(err, 'CV gagal diunduh.'), 'error');
            }
            setBusy(btn, false);
        });
    });
}

export { interviewOf, relativeTime };
