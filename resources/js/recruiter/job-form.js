/**
 * Dialog buat/edit lowongan. Mendukung simpan draft dan publikasi.
 * Publikasi dinonaktifkan bila perusahaan belum terverifikasi; backend tetap menegakkan aturan yang sama.
 */
import { jobs, toast } from '../api.js';
import { util } from '../api.js';
import { bindDialog, openDialog, setBusy } from './components.js';
import { LEVEL, esc, icon, hydrateIcons, jobStatusBadge, showFieldErrors, clearFieldErrors, errorMessage } from './ui.js';

let ctxRef = null;
let onSaved = () => {};
let editing = null;
let lastSkills = [];

const TYPE_LABEL = { 'full-time': 'Full-time', 'part-time': 'Part-time', contract: 'Kontrak', internship: 'Magang' };

const $ = (id) => document.getElementById(id);

function reqRow(r = { skill: '', level: 'required' }) {
    const level = LEVEL[r.level] ? r.level : 'required';
    return `
        <div class="jf-req flex flex-col sm:flex-row gap-2">
            <label class="sr-only">Nama skill</label>
            <input type="text" data-req-skill list="jf-skill-list" maxlength="100" value="${esc(r.skill || util.skillName(r))}" placeholder="contoh: Laravel" class="ws-input flex-1">
            <div class="flex gap-2">
                <label class="sr-only">Tingkat syarat</label>
                <select data-req-level class="ws-input sm:w-[150px] flex-1">
                    ${Object.entries(LEVEL).map(([k, v]) => `<option value="${k}" ${k === level ? 'selected' : ''}>${v.label} · bobot ${v.weight}</option>`).join('')}
                </select>
                <button type="button" data-req-remove class="ws-icon-btn" aria-label="Hapus skill ini">${icon('trash-2')}</button>
            </div>
        </div>`;
}

function readReqs() {
    return [...$('jf-reqs').querySelectorAll('.jf-req')]
        .map((row) => ({ skill: row.querySelector('[data-req-skill]').value.trim(), level: row.querySelector('[data-req-level]').value }))
        .filter((r) => r.skill);
}

function updateWeights() {
    const reqs = readReqs();
    const total = reqs.reduce((s, r) => s + LEVEL[r.level].weight, 0);
    const by = Object.fromEntries(Object.keys(LEVEL).map((k) => [k, reqs.filter((r) => r.level === k).reduce((s, r) => s + LEVEL[r.level].weight, 0)]));
    $('jf-weight-text').textContent = total
        ? `Total bobot ${total}. Skill Wajib menentukan ${Math.round((by.required / total) * 100)}% skor kandidat.`
        : 'Tambahkan minimal satu skill.';
    $('jf-weight-bar').innerHTML = Object.entries(by).filter(([, w]) => w)
        .map(([k, w]) => `<span style="width:${(w / total) * 100}%;background:${LEVEL[k].color}" title="${LEVEL[k].label}: ${w}"></span>`).join('');
}

function setPublishState() {
    const btn = document.querySelector('#job-form [data-submit="open"]');
    const wrap = $('jf-publish-wrap');
    const verified = ctxRef?.isAdmin || ctxRef?.company?.verification_status === 'verified';
    const alreadyOpen = editing?.status === 'open';
    btn.innerHTML = `${icon(alreadyOpen ? 'check' : 'send')}${alreadyOpen ? 'Simpan perubahan' : 'Publikasikan'}`;
    btn.disabled = !verified && !alreadyOpen;
    wrap.toggleAttribute('data-tip', btn.disabled);
    if (btn.disabled) wrap.dataset.tip = 'Perusahaan harus terverifikasi sebelum bisa mempublikasikan lowongan.';
    // Lowongan yang sudah terbit tidak ditawari "Simpan draft" supaya tidak tidak sengaja ditarik dari katalog
    document.querySelector('#job-form [data-submit="draft"]').classList.toggle('hidden', alreadyOpen || ['closed', 'archived'].includes(editing?.status));
}

export function initJobForm(ctx, { onSave } = {}) {
    ctxRef = ctx;
    onSaved = onSave || onSaved;
    const dialog = bindDialog($('job-dialog'));
    const form = $('job-form');
    hydrateIcons(dialog);

    $('jf-add-req').addEventListener('click', () => {
        $('jf-reqs').insertAdjacentHTML('beforeend', reqRow({ skill: '', level: 'important' }));
        $('jf-reqs').lastElementChild.querySelector('input').focus();
        updateWeights();
    });
    $('jf-reqs').addEventListener('click', (e) => {
        const rm = e.target.closest('[data-req-remove]');
        if (!rm) return;
        const rows = $('jf-reqs').querySelectorAll('.jf-req');
        if (rows.length <= 1) rm.closest('.jf-req').querySelector('input').value = '';
        else rm.closest('.jf-req').remove();
        updateWeights();
    });
    $('jf-reqs').addEventListener('input', updateWeights);
    $('jf-reqs').addEventListener('change', updateWeights);

    form.addEventListener('input', (e) => {
        if (!e.target.classList.contains('is-invalid')) return;
        e.target.classList.remove('is-invalid');
        const err = form.querySelector(`[data-error-for="${e.target.name}"]`);
        if (err) err.textContent = '';
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submitter = e.submitter || form.querySelector('[data-submit="open"]');
        const mode = submitter.dataset.submit;
        clearFieldErrors(form);
        $('job-form-alert').className = 'hidden';

        const num = (id) => ($(id).value === '' ? null : Number($(id).value));
        const payload = {
            title: $('jf-title').value.trim(),
            company: $('jf-company').value.trim(),
            location: $('jf-location').value.trim(),
            employment_type: $('jf-type').value,
            quota: num('jf-quota') || 1,
            salary_min: num('jf-salary-min'),
            salary_max: num('jf-salary-max'),
            description: $('jf-description').value.trim(),
            requirements: readReqs().map((r) => ({ skill: r.skill, level: r.level, weight: LEVEL[r.level].weight })),
        };
        if ($('jf-expires').value) payload.expires_at = $('jf-expires').value;

        const problems = {};
        if (!payload.title) problems.title = 'Judul posisi wajib diisi.';
        if (!payload.company) problems.company = 'Nama perusahaan wajib diisi.';
        if (!payload.location) problems.location = 'Kota penempatan wajib diisi.';
        if (payload.salary_min !== null && payload.salary_max !== null && payload.salary_max < payload.salary_min) {
            problems.salary_max = 'Gaji maksimum tidak boleh lebih kecil dari minimum.';
        }
        if (!payload.requirements.length) problems.requirements = 'Isi minimal satu skill.';
        if (!payload.description) problems.description = 'Deskripsi wajib diisi.';
        if (Object.keys(problems).length) {
            showFieldErrors(form, { errors: problems }, { requirements: '[data-req-skill]' });
            form.querySelector('.is-invalid')?.focus();
            return;
        }

        const keepStatus = editing && ['open', 'closed', 'archived'].includes(editing.status);
        if (!keepStatus) payload.status = mode;

        setBusy(submitter, true, 'Menyimpan...');
        try {
            const res = editing ? await jobs.update(editing.id, payload) : await jobs.create(payload);
            setBusy(submitter, false);
            const saved = { ...payload, ...(res.data || {}) };
            onSaved(res.data);
            if (editing) {
                dialog.close();
                toast('Perubahan lowongan disimpan.', 'success');
            } else {
                // Lowongan baru: tampilkan konfirmasi di dialog yang sama, bukan sekadar toast
                showSuccess(saved);
            }
        } catch (err) {
            setBusy(submitter, false);
            if (!showFieldErrors(form, err)) {
                $('job-form-alert').className = 'ws-banner-danger rounded-xl p-3 text-sm flex gap-2';
                $('job-form-alert').innerHTML = `${icon('circle-alert', 'w-5 h-5 text-[var(--ws-danger)]')}<span>${esc(errorMessage(err, 'Lowongan gagal disimpan.'))}</span>`;
            }
        }
    });
}

/** Panel konfirmasi: centang beranimasi, ringkasan lowongan, dan langkah berikutnya. */
function showSuccess(job) {
    const published = job.status === 'open';
    const verified = ctxRef?.isAdmin || ctxRef?.company?.verification_status === 'verified';
    const reqs = job.requirements || [];
    const totalWeight = reqs.reduce((s, r) => s + (LEVEL[r.level]?.weight || 0), 0);
    const meta = [job.company, job.location, TYPE_LABEL[job.employment_type] || job.employment_type].filter(Boolean).map(esc).join(' · ');
    const note = published
        ? 'Lowongan sudah tampil di katalog. Setiap pelamar yang melamar langsung mendapat skor kecocokan.'
        : verified
            ? 'Belum tampil di katalog. Publikasikan kapan saja dari halaman lowongan.'
            : 'Belum tampil di katalog. Lowongan bisa dipublikasikan setelah perusahaan terverifikasi.';
    const facts = [
        [icon('list-checks'), `${reqs.length} skill, total bobot ${totalWeight}`],
        [icon('users'), `Kuota ${Number(job.quota) || 1} orang`],
        job.expires_at ? [icon('calendar-clock'), `Ditutup ${util.formatDate(job.expires_at)}`] : null,
    ].filter(Boolean);

    const panel = $('job-success');
    panel.innerHTML = `
        <div class="ws-success-body">
            <svg class="ws-success-check" viewBox="0 0 52 52" aria-hidden="true">
                <circle class="ws-success-ring" cx="26" cy="26" r="24" fill="none"/>
                <path class="ws-success-tick" fill="none" d="M15 27l7 7 15-16"/>
            </svg>
            <div role="status">
                <h2 id="job-success-title" class="text-xl font-bold">${published ? 'Lowongan dipublikasikan' : 'Draft lowongan disimpan'}</h2>
                <p class="ws-meta mt-1 max-w-[26rem] mx-auto">${note}</p>
            </div>
            <div class="ws-success-card">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold line-clamp-2">${esc(job.title)}</p>
                        <p class="ws-meta truncate">${meta}</p>
                    </div>
                    <span class="shrink-0">${jobStatusBadge(job.status)}</span>
                </div>
                <ul class="mt-3 flex flex-wrap gap-x-4 gap-y-1.5 ws-meta">${facts.map(([ic, t]) => `<li class="inline-flex items-center gap-1.5">${ic}${esc(t)}</li>`).join('')}</ul>
            </div>
        </div>
        <div class="ws-dialog-foot">
            ${published ? `<button type="button" class="ws-btn ws-btn-ghost" data-success-copy>${icon('link')}<span>Salin tautan</span></button>` : '<span></span>'}
            <button type="button" class="ws-btn ws-btn-secondary" data-success-again>${icon('plus')}Buat lowongan lain</button>
            ${job.id ? `<a href="/recruiter/jobs/${encodeURIComponent(job.id)}" class="ws-btn ws-btn-primary" data-success-open>Buka lowongan${icon('arrow-right')}</a>` : ''}
        </div>`;

    $('job-form').hidden = true;
    $('job-dialog').setAttribute('aria-labelledby', 'job-success-title');
    $('job-dialog').classList.add('is-success');
    panel.hidden = false;

    panel.querySelector('[data-success-again]').addEventListener('click', () => openJobForm(null, lastSkills));
    panel.querySelector('[data-success-copy]')?.addEventListener('click', async (e) => {
        const btn = e.currentTarget;
        const url = `${location.origin}/jobs/${job.id}`;
        try {
            await navigator.clipboard.writeText(url);
            btn.innerHTML = `${icon('check')}<span>Tautan disalin</span>`;
        } catch (err) {
            // Clipboard ditolak browser: tampilkan alamatnya agar bisa disalin manual
            btn.innerHTML = `${icon('link')}<span class="truncate max-w-[14rem]">${esc(url)}</span>`;
        }
    });
    (panel.querySelector('[data-success-open]') || panel.querySelector('[data-success-again]')).focus();
}

function resetSuccess() {
    $('job-success').hidden = true;
    $('job-success').innerHTML = '';
    $('job-form').hidden = false;
    $('job-dialog').classList.remove('is-success');
    $('job-dialog').setAttribute('aria-labelledby', 'job-dialog-title');
}

/**
 * Buka dialog. job = null untuk lowongan baru. skills = daftar saran nama skill.
 * template: lowongan yang disalin menjadi lowongan baru (duplikat), disimpan sebagai lowongan terpisah.
 */
export function openJobForm(job = null, skills = [], { template = null } = {}) {
    editing = job;
    lastSkills = skills;
    resetSuccess();
    const form = $('job-form');
    form.reset();
    clearFieldErrors(form);
    $('job-form-alert').className = 'hidden';

    $('job-dialog-title').textContent = job ? 'Edit lowongan' : template ? 'Duplikat lowongan' : 'Lowongan baru';
    $('job-dialog-sub').textContent = job
        ? 'Perubahan syarat skill memengaruhi skor kecocokan pelamar baru.'
        : template
            ? `Disalin dari "${template.title}". Periksa lagi isinya, lalu simpan sebagai lowongan baru.`
            : 'Isi detail posisi. Simpan sebagai draft bila belum siap dipublikasikan.';
    // Sumber isian: lowongan yang diedit, atau lowongan yang diduplikat (batas lamaran lama tidak ikut disalin)
    const src = job || template;

    const companyName = ctxRef?.company?.name || '';
    $('jf-title').value = src?.title || '';
    $('jf-company').value = src?.company || companyName;
    $('jf-company').readOnly = !ctxRef?.isAdmin && Boolean(companyName);
    $('jf-company-help').classList.toggle('hidden', !$('jf-company').readOnly);
    $('jf-location').value = src?.location || ctxRef?.company?.city || '';
    $('jf-type').value = src?.employment_type || 'full-time';
    $('jf-quota').value = src?.quota || 1;
    $('jf-salary-min').value = src?.salary_min || '';
    $('jf-salary-max').value = src?.salary_max || '';
    $('jf-description').value = src?.description || '';
    $('jf-expires').value = job?.expires_at ? String(job.expires_at).slice(0, 10) : '';
    $('jf-expires').min = job ? '' : new Date(Date.now() + 86400000).toISOString().slice(0, 10);

    $('jf-skill-list').innerHTML = [...new Set(skills)].filter(Boolean).sort((a, b) => a.localeCompare(b, 'id'))
        .map((s) => `<option value="${esc(s)}"></option>`).join('');
    const reqs = src?.requirements?.length ? src.requirements : [{ skill: '', level: 'required' }, { skill: '', level: 'important' }];
    $('jf-reqs').innerHTML = reqs.map(reqRow).join('');
    updateWeights();
    setPublishState();
    openDialog($('job-dialog'));
    setTimeout(() => $('jf-title').focus(), 30);
}

/** Kumpulkan nama skill dari lowongan dan profil pelamar untuk saran pengisian. */
export function skillSuggestions(jobList = [], apps = []) {
    const names = [];
    jobList.forEach((j) => (j.requirements || []).forEach((r) => names.push(util.skillName(r))));
    apps.forEach((a) => (a.applicant?.skills || []).forEach((s) => names.push(s)));
    return names;
}
