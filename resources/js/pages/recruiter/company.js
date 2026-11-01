import { company as companyApi, toast } from '../../api.js';
import { bootShell } from '../../recruiter/shell.js';
import { data } from '../../recruiter/data.js';
import { initMenus, setBusy } from '../../recruiter/components.js';
import { esc, icon, hydrateIcons, verifyBadge, companyLogo, showFieldErrors, clearFieldErrors, errorMessage, VERIFY } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const FIELDS = ['name', 'industry', 'size', 'founded_year', 'description', 'address', 'city', 'website', 'email', 'phone'];
const REQUIRED = { name: 'Nama perusahaan', industry: 'Industri', description: 'Deskripsi', address: 'Alamat kantor', city: 'Kota', email: 'Email rekrutmen', phone: 'Telepon' };

let current = null;
let saved = '';

function read() {
    const form = $('co-form');
    const out = {};
    FIELDS.forEach((f) => {
        const v = form.elements[f].value.trim();
        out[f] = v === '' ? null : (f === 'founded_year' ? Number(v) : v);
    });
    return out;
}

function fill(c) {
    const form = $('co-form');
    FIELDS.forEach((f) => { form.elements[f].value = c?.[f] ?? ''; });
    saved = JSON.stringify(read());
    $('co-desc-count').textContent = form.elements.description.value.length;
}

function renderSide() {
    const values = read();
    $('co-checklist').innerHTML = Object.entries(REQUIRED).map(([k, label]) => {
        const ok = Boolean(values[k]);
        return `<li class="flex items-center gap-2.5 text-sm ${ok ? '' : 'text-[var(--ws-muted)]'}">
            <span class="w-5 h-5 rounded-full inline-flex items-center justify-center ${ok ? 'bg-[var(--ws-success-soft)] text-[var(--ws-success)]' : 'bg-[var(--ws-surface-2)]'}">${icon(ok ? 'check' : 'circle-dashed', 'w-3.5 h-3.5')}</span>${label}
        </li>`;
    }).join('');
    const done = Object.keys(REQUIRED).filter((k) => values[k]).length;
    $('co-ready-title').innerHTML = `Kesiapan verifikasi <span class="ws-count ml-1">${done}/${Object.keys(REQUIRED).length}</span>`;

    const preview = { ...current, ...values };
    $('co-preview').innerHTML = `
        <div class="flex items-center gap-3">
            ${companyLogo(preview, 'w-12 h-12 text-base')}
            <div class="min-w-0">
                <p class="font-semibold truncate">${esc(preview.name || 'Nama perusahaan')}${current?.verification_status === 'verified' ? ` <span class="text-[var(--ws-success)] align-middle" title="Terverifikasi">${icon('badge-check', 'w-4 h-4 inline')}</span>` : ''}</p>
                <p class="ws-meta truncate">${esc([preview.industry, preview.city].filter(Boolean).join(' · ') || 'Industri · Kota')}</p>
            </div>
        </div>
        <p class="mt-3 text-sm text-[var(--ws-text-2)] line-clamp-4">${esc(preview.description || 'Deskripsi singkat perusahaan akan tampil di sini.')}</p>`;

    const dirty = JSON.stringify(values) !== saved;
    $('co-dirty').classList.toggle('hidden', !dirty);
}

function renderStatus() {
    const status = current?.verification_status;
    $('co-status').innerHTML = status ? `<a href="/recruiter/verification" class="inline-flex">${verifyBadge(status)}</a>` : '<span class="ws-pill ws-pill-warning">Belum dibuat</span>';
    const locked = status === 'verified';
    $('co-name').readOnly = locked;
    $('co-name-help').classList.toggle('hidden', !locked);
    $('co-name-help').textContent = locked ? 'Nama terkunci karena sudah diverifikasi. Hubungi admin bila perlu diubah.' : '';
    $('co-logo-preview').outerHTML = current?.logo_url
        ? `<img id="co-logo-preview" src="${esc(current.logo_url)}" alt="Logo ${esc(current.name)}" class="ws-logo w-16 h-16">`
        : `<span id="co-logo-preview" class="ws-logo ws-logo-fallback w-16 h-16 text-xl">${icon('building-2', 'w-7 h-7')}</span>`;
    $('co-verify-link').textContent = VERIFY[status]?.label && status !== 'unverified' ? 'Lihat status verifikasi' : 'Ajukan verifikasi';
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootShell({ page: 'company', roles: ['recruiter'] });
    if (!ctx) return;
    initMenus();

    try {
        current = await data.company();
    } catch (err) {
        toast(errorMessage(err, 'Profil perusahaan gagal dimuat.'), 'error');
    }
    fill(current);
    renderStatus();
    renderSide();
    hydrateIcons($('ws-main'));

    const form = $('co-form');
    form.addEventListener('input', (e) => {
        if (e.target.name === 'description') $('co-desc-count').textContent = e.target.value.length;
        if (e.target.classList.contains('is-invalid')) {
            e.target.classList.remove('is-invalid');
            const err = form.querySelector(`[data-error-for="${e.target.name}"]`);
            if (err) err.textContent = '';
        }
        renderSide();
    });

    window.addEventListener('beforeunload', (e) => {
        if (JSON.stringify(read()) !== saved) e.preventDefault();
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFieldErrors(form);
        $('co-alert').className = 'hidden';
        const payload = read();
        if (!payload.name) {
            showFieldErrors(form, { errors: { name: 'Nama perusahaan wajib diisi.' } });
            form.elements.name.focus();
            return;
        }
        const btn = $('co-submit');
        setBusy(btn, true, 'Menyimpan...');
        try {
            const res = await companyApi.update(payload);
            current = res.data;
            data.invalidate('company');
            fill(current);
            renderStatus();
            renderSide();
            toast(res.message || 'Profil perusahaan disimpan.', 'success');
            document.getElementById('ws-company-name').textContent = current.name;
        } catch (err) {
            if (!showFieldErrors(form, err)) {
                $('co-alert').className = 'rounded-xl p-3 text-sm flex gap-2 bg-[var(--ws-danger-soft)] text-[var(--ws-danger)]';
                $('co-alert').innerHTML = `${icon('circle-alert', 'w-5 h-5')}<span>${esc(errorMessage(err, 'Profil gagal disimpan.'))}</span>`;
            }
            form.querySelector('.is-invalid')?.focus();
        }
        setBusy(btn, false);
    });

    $('co-logo').addEventListener('change', async (e) => {
        const file = e.target.files[0];
        e.target.value = '';
        const errEl = form.querySelector('[data-error-for="logo"]');
        errEl.textContent = '';
        if (!file) return;
        if (!current) {
            errEl.textContent = 'Simpan profil terlebih dahulu, lalu unggah logo.';
            return;
        }
        if (file.size > 1024 * 1024) {
            errEl.textContent = 'Ukuran logo maksimal 1 MB.';
            return;
        }
        const label = document.querySelector('label[for="co-logo"]');
        label.classList.add('is-loading');
        try {
            const res = await companyApi.uploadLogo(file);
            current = res.data;
            data.invalidate('company');
            renderStatus();
            renderSide();
            toast('Logo perusahaan diperbarui.', 'success');
        } catch (err) {
            errEl.textContent = errorMessage(err, 'Logo gagal diunggah.');
        }
        label.classList.remove('is-loading');
    });
});
