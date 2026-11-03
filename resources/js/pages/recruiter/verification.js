import { company as companyApi, session, toast, downloadProtected } from '../../api.js';
import { bootShell } from '../../recruiter/shell.js';
import { data } from '../../recruiter/data.js';
import { initMenus, setBusy } from '../../recruiter/components.js';
import { esc, icon, hydrateIcons, VERIFY, verifyBadge, formatDate, formatBytes, emptyState, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const MAX_FILES = 5;
const MAX_SIZE = 2 * 1024 * 1024;
const TYPES = { nib: 'NIB', npwp: 'NPWP', akta: 'Akta pendirian', surat_kuasa: 'Surat kuasa', lainnya: 'Lainnya' };
const FIELD_LABEL = { name: 'nama', industry: 'industri', description: 'deskripsi', address: 'alamat', city: 'kota', email: 'email', phone: 'telepon' };

let info = null;
let files = [];

function renderStatus() {
    const s = info.status;
    const v = VERIFY[s] || VERIFY.unverified;
    const last = info.submissions[0];
    const tone = { success: 'success', info: 'info', warning: 'warning', danger: 'danger', neutral: 'neutral' }[v.tone];
    const color = tone === 'neutral' ? 'var(--ws-muted)' : `var(--ws-${tone})`;
    const soft = tone === 'neutral' ? 'var(--ws-surface-2)' : `var(--ws-${tone}-soft)`;
    const detail = {
        unverified: info.missing_profile_fields.length ? 'Lengkapi profil perusahaan terlebih dahulu, lalu kirim dokumen di bawah.' : 'Profil sudah lengkap. Kirim dokumen legal untuk diperiksa admin.',
        pending: last ? `Dikirim ${formatDate(last.submitted_at, true)}. Pemeriksaan biasanya selesai dalam 1 sampai 2 hari kerja. Sementara itu, siapkan lowongan sebagai draft.` : '',
        verified: `Disetujui ${info.verified_at ? formatDate(info.verified_at) : ''}. Lowongan bisa langsung dipublikasikan.`,
        revision_required: 'Data pengajuan sebelumnya sudah terisi di formulir di bawah.',
        rejected: 'Perbaiki sesuai catatan admin di bawah, lalu ajukan ulang.',
        suspended: 'Lowongan baru tidak dapat dipublikasikan selama penangguhan. Hubungi admin JobHarbour untuk penjelasan.',
    }[s] || '';

    $('vf-status').innerHTML = `
        <div class="flex flex-wrap items-start gap-4">
            <span class="w-12 h-12 shrink-0 rounded-2xl inline-flex items-center justify-center" style="background:${soft};color:${color}">${icon(v.icon, 'w-6 h-6')}</span>
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2"><h2 class="text-lg font-bold">${v.label}</h2>${verifyBadge(s)}</div>
                <p class="mt-1 text-[var(--ws-text-2)]">${v.text} ${detail}</p>
                ${info.note && ['revision_required', 'rejected', 'suspended'].includes(s) ? `
                    <div class="mt-3 rounded-xl p-3 text-sm" style="background:${soft}">
                        <p class="font-semibold" style="color:${color}">Catatan admin</p>
                        <p class="mt-0.5 text-[var(--ws-text)]">${esc(info.note)}</p>
                    </div>` : ''}
            </div>
            ${s === 'verified' ? '<a href="/recruiter/jobs?new=1" class="ws-btn ws-btn-primary">Buat lowongan</a>' : ''}
            ${info.missing_profile_fields.length && ['unverified', 'revision_required', 'rejected'].includes(s) ? '<a href="/recruiter/company" class="ws-btn ws-btn-primary">Lengkapi profil</a>' : ''}
        </div>`;
}

function renderSteps() {
    const s = info.status;
    const profileDone = !info.missing_profile_fields.length;
    const submitted = ['pending', 'verified', 'suspended'].includes(s);
    const steps = [
        { title: 'Lengkapi profil', text: profileDone ? 'Semua kolom wajib terisi' : `${info.missing_profile_fields.length} kolom belum terisi`, state: profileDone ? 'done' : 'current' },
        { title: 'Kirim dokumen', text: s === 'rejected' || s === 'revision_required' ? 'Perlu dikirim ulang' : submitted ? 'Terkirim' : 'NIB dan dokumen pendukung', state: submitted ? 'done' : profileDone ? 'current' : '' },
        { title: 'Pemeriksaan admin', text: s === 'pending' ? 'Sedang diperiksa' : s === 'rejected' ? 'Ditolak' : s === 'revision_required' ? 'Perlu revisi' : ['verified', 'suspended'].includes(s) ? 'Selesai' : 'Menunggu dokumen', state: s === 'pending' ? 'current' : s === 'rejected' ? 'failed' : s === 'revision_required' ? 'current' : ['verified', 'suspended'].includes(s) ? 'done' : '' },
        { title: 'Terverifikasi', text: s === 'suspended' ? 'Sedang ditangguhkan' : s === 'verified' ? 'Bisa publikasi lowongan' : 'Publikasi lowongan terbuka', state: s === 'verified' ? 'done' : s === 'suspended' ? 'failed' : '' },
    ];
    $('vf-steps').innerHTML = steps.map((st, i) => `
        <li class="ws-step ${st.state === 'done' ? 'is-done' : st.state === 'current' ? 'is-current' : st.state === 'failed' ? 'is-failed' : ''}" ${st.state === 'current' ? 'aria-current="step"' : ''}>
            <span class="ws-step-mark">${st.state === 'done' ? icon('check') : st.state === 'failed' ? icon('x') : i + 1}</span>
            <div><p class="ws-step-title">${st.title}</p><p class="ws-step-text">${st.text}</p></div>
        </li>`).join('');
}

function submissionCard(sub, { compact = false } = {}) {
    const statusPill = { pending: '<span class="ws-pill ws-pill-info">Diperiksa</span>', approved: '<span class="ws-pill ws-pill-success">Disetujui</span>', revision_required: '<span class="ws-pill ws-pill-warning">Perlu revisi</span>', rejected: '<span class="ws-pill ws-pill-danger">Ditolak</span>' }[sub.status];
    return `
        <div class="${compact ? 'rounded-xl border border-[var(--ws-border)] p-3' : ''} space-y-3">
            <div class="flex items-center justify-between gap-2">
                <p class="text-sm"><span class="font-semibold">${formatDate(sub.submitted_at, true)}</span></p>
                ${statusPill}
            </div>
            ${compact ? '' : `
            <dl class="grid sm:grid-cols-2 gap-3 text-sm">
                <div><dt class="ws-meta">Nama badan usaha</dt><dd class="font-medium">${esc(sub.legal_name)}</dd></div>
                <div><dt class="ws-meta">NIB</dt><dd class="font-medium ws-num">${esc(sub.registration_number)}</dd></div>
                <div><dt class="ws-meta">NPWP</dt><dd class="font-medium ws-num">${esc(sub.tax_number || '-')}</dd></div>
                <div><dt class="ws-meta">Jabatan pengaju</dt><dd class="font-medium">${esc(sub.applicant_position)}</dd></div>
            </dl>`}
            <ul class="space-y-1.5">
                ${sub.documents.map((d) => `
                    <li><button type="button" class="ws-menu-item border border-[var(--ws-border)]" data-doc="${esc(d.url)}" data-name="${esc(d.name)}">${icon('file-text')}<span class="flex-1 min-w-0 truncate">${esc(d.name)}</span><span class="ws-meta">${TYPES[d.type] || 'Lainnya'}${d.size ? ` · ${formatBytes(d.size)}` : ''}</span>${icon('download')}</button></li>`).join('')}
            </ul>
            ${sub.admin_notes ? `<p class="text-sm"><span class="ws-meta">Catatan admin:</span> ${esc(sub.admin_notes)}</p>` : ''}
        </div>`;
}

function renderHistory() {
    $('vf-history').innerHTML = info.submissions.length
        ? `<div class="space-y-3">${info.submissions.map((s) => submissionCard(s, { compact: true })).join('')}</div>`
        : '<p class="ws-meta">Belum ada pengajuan.</p>';
}

function renderMain() {
    const s = info.status;
    const main = $('vf-main');
    if (s === 'pending' || s === 'verified' || s === 'suspended') {
        const last = info.submissions.find((x) => x.status === (s === 'pending' ? 'pending' : 'approved')) || info.submissions[0];
        main.innerHTML = last ? `
            <section class="ws-card ws-card-body">
                <h2 class="ws-h2 mb-4">${s === 'pending' ? 'Pengajuan yang sedang diperiksa' : 'Pengajuan yang disetujui'}</h2>
                ${submissionCard(last)}
            </section>` : '';
        return;
    }
    if (info.missing_profile_fields.length) {
        main.innerHTML = `<section class="ws-card">${emptyState({
            iconName: 'building-2',
            title: 'Lengkapi profil perusahaan dulu',
            text: `Kolom yang belum terisi: ${info.missing_profile_fields.map((f) => FIELD_LABEL[f] || f).join(', ')}.`,
            action: '<a href="/recruiter/company" class="ws-btn ws-btn-primary">Buka profil perusahaan</a>',
        })}</section>`;
        return;
    }
    const prev = info.submissions[0];
    main.innerHTML = `
        <form id="vf-form" class="ws-card ws-card-body space-y-5" novalidate>
            <div>
                <h2 class="ws-h2">${s === 'rejected' || s === 'revision_required' ? 'Ajukan ulang verifikasi' : 'Ajukan verifikasi'}</h2>
                <p class="ws-meta mt-0.5">${s === 'rejected' || s === 'revision_required' ? 'Data pengajuan sebelumnya sudah diisi ulang. Perbaiki bagian yang dicatat admin, lalu unggah dokumen yang benar.' : 'Isi data sesuai dokumen legal perusahaan.'}</p>
            </div>
            <div id="vf-alert" class="hidden"></div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label for="vf-legal" class="ws-label">Nama badan usaha sesuai dokumen</label>
                    <input id="vf-legal" name="legal_name" type="text" maxlength="255" class="ws-input" value="${esc(prev?.legal_name || '')}" placeholder="contoh: PT Pelabuhan Digital Nusantara">
                    <p class="ws-error" data-error-for="legal_name"></p>
                </div>
                <div>
                    <label for="vf-nib" class="ws-label">Nomor Induk Berusaha (NIB)</label>
                    <input id="vf-nib" name="registration_number" type="text" inputmode="numeric" maxlength="50" class="ws-input ws-num" value="${esc(prev?.registration_number || '')}" placeholder="13 digit">
                    <p class="ws-help">Tertera di dokumen NIB dari OSS.</p>
                    <p class="ws-error" data-error-for="registration_number"></p>
                </div>
                <div>
                    <label for="vf-npwp" class="ws-label">NPWP badan usaha <span class="opt">(opsional)</span></label>
                    <input id="vf-npwp" name="tax_number" type="text" maxlength="50" class="ws-input ws-num" value="${esc(prev?.tax_number || '')}">
                    <p class="ws-error" data-error-for="tax_number"></p>
                </div>
                <div class="sm:col-span-2">
                    <label for="vf-position" class="ws-label">Jabatanmu di perusahaan</label>
                    <input id="vf-position" name="applicant_position" type="text" maxlength="100" class="ws-input" value="${esc(prev?.applicant_position || '')}" placeholder="contoh: HR Manager">
                    <p class="ws-error" data-error-for="applicant_position"></p>
                </div>
            </div>

            <div>
                <p class="ws-label">Dokumen pendukung</p>
                <label class="ws-drop" id="vf-drop" for="vf-files">
                    ${icon('upload')}
                    <span class="font-semibold">Pilih file atau seret ke sini</span>
                    <span class="ws-meta">PDF, JPG, atau PNG. Maksimal 2 MB per file, ${MAX_FILES} file.</span>
                </label>
                <input id="vf-files" type="file" accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" multiple class="sr-only">
                <ul id="vf-file-list" class="mt-3 space-y-2"></ul>
                <p class="ws-error" data-error-for="documents"></p>
            </div>

            <div>
                <label for="vf-notes" class="ws-label">Catatan untuk admin <span class="opt">(opsional)</span></label>
                <textarea id="vf-notes" name="notes" rows="3" maxlength="1000" class="ws-input" placeholder="${s === 'rejected' || s === 'revision_required' ? 'Jelaskan apa yang sudah diperbaiki.' : 'Informasi tambahan yang membantu pemeriksaan.'}"></textarea>
            </div>

            <div id="vf-progress" class="hidden">
                <div class="flex justify-between text-sm mb-1"><span>Mengunggah dokumen</span><span id="vf-progress-text" class="ws-num">0%</span></div>
                <div class="h-2 rounded-full bg-[var(--ws-surface-2)] overflow-hidden"><div id="vf-progress-bar" class="h-full rounded-full bg-[var(--ws-accent)] transition-[width] duration-200" style="width:0"></div></div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 pt-4 border-t border-[var(--ws-border)]">
                <label class="flex items-start gap-2 text-sm mr-auto max-w-md">
                    <input type="checkbox" id="vf-agree" class="mt-0.5 w-4 h-4 accent-[var(--ws-primary)]">
                    <span>Saya menyatakan data dan dokumen ini benar dan saya berwenang mewakili perusahaan.</span>
                </label>
                <button type="submit" id="vf-submit" class="ws-btn ws-btn-primary">${icon('send')}Kirim pengajuan</button>
            </div>
        </form>`;
    files = [];
    bindForm();
}

function renderFiles() {
    $('vf-file-list').innerHTML = files.map((f, i) => `
        <li class="ws-file">
            ${icon(f.file.type === 'application/pdf' ? 'file-text' : 'image-up', 'w-5 h-5 text-[var(--ws-muted)]')}
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium truncate">${esc(f.file.name)}</p>
                <p class="ws-meta">${formatBytes(f.file.size)}</p>
            </div>
            <label class="sr-only" for="vf-type-${i}">Jenis dokumen ${esc(f.file.name)}</label>
            <select id="vf-type-${i}" class="ws-input w-auto min-h-[2.25rem] text-sm" data-type-index="${i}">
                ${Object.entries(TYPES).map(([k, v]) => `<option value="${k}" ${f.type === k ? 'selected' : ''}>${v}</option>`).join('')}
            </select>
            <button type="button" class="ws-icon-btn" data-remove="${i}" aria-label="Hapus ${esc(f.file.name)}">${icon('x')}</button>
        </li>`).join('');
}

function addFiles(list) {
    const errEl = document.querySelector('[data-error-for="documents"]');
    errEl.textContent = '';
    const problems = [];
    [...list].forEach((file) => {
        if (files.length >= MAX_FILES) { problems.push(`Maksimal ${MAX_FILES} file.`); return; }
        if (!/\.(pdf|jpe?g|png)$/i.test(file.name)) { problems.push(`${file.name}: format harus PDF, JPG, atau PNG.`); return; }
        if (file.size > MAX_SIZE) { problems.push(`${file.name}: lebih dari 2 MB.`); return; }
        const guess = /npwp/i.test(file.name) ? 'npwp' : /akta/i.test(file.name) ? 'akta' : /kuasa/i.test(file.name) ? 'surat_kuasa' : files.some((f) => f.type === 'nib') ? 'lainnya' : 'nib';
        files.push({ file, type: guess });
    });
    errEl.textContent = [...new Set(problems)].join(' ');
    renderFiles();
}

/** Unggah dengan XHR supaya progres unggahan bisa ditampilkan. */
function uploadWithProgress(fd) {
    return new Promise((resolve, reject) => {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '/api/company/verification');
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('Authorization', `Bearer ${session.token()}`);
        xhr.upload.onprogress = (e) => {
            if (!e.lengthComputable) return;
            const pct = Math.round((e.loaded / e.total) * 100);
            $('vf-progress-bar').style.width = `${pct}%`;
            $('vf-progress-text').textContent = `${pct}%`;
        };
        xhr.onload = () => {
            let body = null;
            try { body = JSON.parse(xhr.responseText); } catch (e) { /* abaikan */ }
            if (xhr.status >= 200 && xhr.status < 300) resolve(body);
            else reject(Object.assign(new Error(body?.message || 'Pengajuan gagal dikirim.'), { status: xhr.status, errors: body?.errors }));
        };
        xhr.onerror = () => reject(new Error('Koneksi ke server terputus. Coba lagi.'));
        xhr.send(fd);
    });
}

function bindForm() {
    const form = $('vf-form');
    const drop = $('vf-drop');
    $('vf-files').addEventListener('change', (e) => { addFiles(e.target.files); e.target.value = ''; });
    ['dragenter', 'dragover'].forEach((ev) => drop.addEventListener(ev, (e) => { e.preventDefault(); drop.classList.add('is-over'); }));
    ['dragleave', 'drop'].forEach((ev) => drop.addEventListener(ev, () => drop.classList.remove('is-over')));
    drop.addEventListener('drop', (e) => { e.preventDefault(); addFiles(e.dataTransfer.files); });
    $('vf-file-list').addEventListener('click', (e) => {
        const rm = e.target.closest('[data-remove]');
        if (!rm) return;
        files.splice(Number(rm.dataset.remove), 1);
        renderFiles();
    });
    $('vf-file-list').addEventListener('change', (e) => {
        const sel = e.target.closest('[data-type-index]');
        if (sel) files[Number(sel.dataset.typeIndex)].type = sel.value;
    });
    form.addEventListener('input', (e) => {
        if (!e.target.classList.contains('is-invalid')) return;
        e.target.classList.remove('is-invalid');
        const err = form.querySelector(`[data-error-for="${e.target.name}"]`);
        if (err) err.textContent = '';
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        form.querySelectorAll('[data-error-for]').forEach((el) => { el.textContent = ''; });
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        $('vf-alert').className = 'hidden';

        const problems = {};
        if (!form.legal_name.value.trim()) problems.legal_name = 'Nama badan usaha wajib diisi.';
        if (!form.registration_number.value.trim()) problems.registration_number = 'NIB wajib diisi.';
        if (!form.applicant_position.value.trim()) problems.applicant_position = 'Jabatan wajib diisi.';
        if (!files.length) problems.documents = 'Unggah minimal satu dokumen.';
        Object.entries(problems).forEach(([k, m]) => {
            form.querySelector(`[data-error-for="${k}"]`).textContent = m;
            form.elements[k]?.classList.add('is-invalid');
        });
        if (Object.keys(problems).length) {
            (form.querySelector('.is-invalid') || $('vf-drop')).focus?.();
            return;
        }
        if (!$('vf-agree').checked) {
            $('vf-alert').className = 'rounded-xl p-3 text-sm bg-[var(--ws-warning-soft)] text-[var(--ws-warning)]';
            $('vf-alert').textContent = 'Centang pernyataan kebenaran data sebelum mengirim.';
            $('vf-agree').focus();
            return;
        }

        const fd = new FormData();
        ['legal_name', 'registration_number', 'tax_number', 'applicant_position', 'notes'].forEach((k) => {
            const v = form.elements[k].value.trim();
            if (v) fd.append(k, v);
        });
        files.forEach((f) => {
            fd.append('documents[]', f.file);
            fd.append('document_types[]', f.type);
        });

        const btn = $('vf-submit');
        setBusy(btn, true, 'Mengirim...');
        $('vf-progress').classList.remove('hidden');
        try {
            const res = await uploadWithProgress(fd);
            info = res.data;
            data.invalidate('company', 'me');
            toast('Pengajuan terkirim. Admin akan memeriksanya.', 'success');
            renderAll();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (err) {
            setBusy(btn, false);
            $('vf-progress').classList.add('hidden');
            const errs = err.errors || {};
            let shown = false;
            Object.entries(errs).forEach(([k, msgs]) => {
                const key = k.startsWith('documents') ? 'documents' : k;
                const el = form.querySelector(`[data-error-for="${key}"]`);
                if (el) { el.textContent = Array.isArray(msgs) ? msgs[0] : msgs; shown = true; }
            });
            if (!shown) {
                $('vf-alert').className = 'rounded-xl p-3 text-sm bg-[var(--ws-danger-soft)] text-[var(--ws-danger)]';
                $('vf-alert').textContent = errorMessage(err, 'Pengajuan gagal dikirim.');
            }
        }
    });
}

function renderAll() {
    renderStatus();
    renderSteps();
    renderMain();
    renderHistory();
    hydrateIcons($('ws-main'));
    const dot = document.getElementById('ws-nav-verify');
    dot?.classList.toggle('hidden', info.status === 'verified');
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'verification', roles: ['recruiter'] });
    if (!ctx) return;
    initMenus();

    document.addEventListener('click', async (e) => {
        const doc = e.target.closest('[data-doc]');
        if (!doc) return;
        try {
            await downloadProtected(doc.dataset.doc, doc.dataset.name);
        } catch (err) {
            toast(errorMessage(err, 'Dokumen gagal diunduh.'), 'error');
        }
    });

    try {
        info = (await companyApi.verification()).data;
        renderAll();
    } catch (err) {
        $('vf-status').innerHTML = emptyState({ iconName: 'circle-alert', title: 'Status verifikasi gagal dimuat', text: esc(errorMessage(err)), action: '<button type="button" class="ws-btn ws-btn-secondary" onclick="location.reload()">Muat ulang</button>' });
    }
});
