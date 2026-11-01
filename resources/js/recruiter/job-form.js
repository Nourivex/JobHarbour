/**
 * Dialog buat/edit lowongan. Mendukung simpan draft dan publikasi.
 * Publikasi dinonaktifkan bila perusahaan belum terverifikasi; backend tetap menegakkan aturan yang sama.
 */
import { jobs, toast } from '../api.js';
import { util } from '../api.js';
import { bindDialog, openDialog, setBusy } from './components.js';
import { LEVEL, esc, icon, hydrateIcons, showFieldErrors, clearFieldErrors, errorMessage } from './ui.js';

let ctxRef = null;
let onSaved = () => {};
let editing = null;

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
            dialog.close();
            const published = (res.data?.status || payload.status) === 'open';
            toast(editing ? 'Perubahan lowongan disimpan.' : (published ? 'Lowongan dipublikasikan.' : 'Draft lowongan disimpan.'), 'success');
            onSaved(res.data);
        } catch (err) {
            setBusy(submitter, false);
            if (!showFieldErrors(form, err)) {
                $('job-form-alert').className = 'ws-banner-danger rounded-xl p-3 text-sm flex gap-2';
                $('job-form-alert').innerHTML = `${icon('circle-alert', 'w-5 h-5 text-[var(--ws-danger)]')}<span>${esc(errorMessage(err, 'Lowongan gagal disimpan.'))}</span>`;
            }
        }
    });
}

/** Buka dialog. job = null untuk lowongan baru. skills = daftar saran nama skill. */
export function openJobForm(job = null, skills = []) {
    editing = job;
    const form = $('job-form');
    form.reset();
    clearFieldErrors(form);
    $('job-form-alert').className = 'hidden';

    $('job-dialog-title').textContent = job ? 'Edit lowongan' : 'Lowongan baru';
    $('job-dialog-sub').textContent = job
        ? 'Perubahan syarat skill memengaruhi skor kecocokan pelamar baru.'
        : 'Isi detail posisi. Simpan sebagai draft bila belum siap dipublikasikan.';

    const companyName = ctxRef?.company?.name || '';
    $('jf-title').value = job?.title || '';
    $('jf-company').value = job?.company || companyName;
    $('jf-company').readOnly = !ctxRef?.isAdmin && Boolean(companyName);
    $('jf-company-help').classList.toggle('hidden', !$('jf-company').readOnly);
    $('jf-location').value = job?.location || ctxRef?.company?.city || '';
    $('jf-type').value = job?.employment_type || 'full-time';
    $('jf-quota').value = job?.quota || 1;
    $('jf-salary-min').value = job?.salary_min || '';
    $('jf-salary-max').value = job?.salary_max || '';
    $('jf-description').value = job?.description || '';
    $('jf-expires').value = job?.expires_at ? String(job.expires_at).slice(0, 10) : '';
    $('jf-expires').min = job ? '' : new Date(Date.now() + 86400000).toISOString().slice(0, 10);

    $('jf-skill-list').innerHTML = [...new Set(skills)].filter(Boolean).sort((a, b) => a.localeCompare(b, 'id'))
        .map((s) => `<option value="${esc(s)}"></option>`).join('');
    const reqs = job?.requirements?.length ? job.requirements : [{ skill: '', level: 'required' }, { skill: '', level: 'important' }];
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
