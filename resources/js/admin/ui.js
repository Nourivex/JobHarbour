/**
 * Komponen tampilan khusus Admin Portal: label status, pager halaman server, dan dialog konfirmasi beralasan.
 * Komponen dasar (ikon, pill, dialog, tombol) memakai modul portal recruiter agar tampilannya konsisten.
 */
import { openDialog, bindDialog, setBusy } from '../recruiter/components.js';
import { esc, icon, hydrateIcons, errorMessage } from '../recruiter/ui.js';

export const ROLE = {
    applicant: { label: 'Pelamar', tone: 'info' },
    recruiter: { label: 'Recruiter', tone: 'neutral' },
    admin: { label: 'Admin', tone: 'warning' },
};

export function roleBadge(role) {
    const r = ROLE[role] || ROLE.applicant;
    return `<span class="ws-pill ws-pill-${r.tone}">${r.label}</span>`;
}

export function userStatusBadge(status) {
    return status === 'suspended'
        ? `<span class="ws-pill ws-pill-danger">${icon('ban', 'w-3.5 h-3.5')}Ditangguhkan</span>`
        : `<span class="ws-pill ws-pill-success">${icon('circle-check', 'w-3.5 h-3.5')}Aktif</span>`;
}

export function moderationBadge(job) {
    return job.moderation_status === 'removed'
        ? `<span class="ws-pill ws-pill-danger" title="${esc(job.moderation_reason || '')}">${icon('ban', 'w-3.5 h-3.5')}Diturunkan admin</span>`
        : '';
}

/** Pager untuk data yang dipaginasi di server (meta.pagination dari API). */
export function pager(p, noun) {
    if (!p || p.total === 0) return '';
    const from = (p.current_page - 1) * p.per_page + 1;
    const to = Math.min(p.current_page * p.per_page, p.total);
    return `
        <div class="ws-pager">
            <span>Menampilkan ${from} sampai ${to} dari ${p.total} ${noun}</span>
            ${p.last_page > 1 ? `<span class="ws-pager-btns">
                <button type="button" class="ws-btn ws-btn-secondary ws-btn-sm" data-goto-page="${p.current_page - 1}" ${p.current_page <= 1 ? 'disabled' : ''}>${icon('arrow-left')}Sebelumnya</button>
                <button type="button" class="ws-btn ws-btn-secondary ws-btn-sm" data-goto-page="${p.current_page + 1}" ${p.current_page >= p.last_page ? 'disabled' : ''}>Berikutnya${icon('arrow-right')}</button>
            </span>` : ''}
        </div>`;
}

/** Sorot sebentar elemen yang baru diubah (misalnya baris tabel) agar admin melihat hasil tindakannya. */
export function flash(el) {
    if (!el) return;
    el.classList.remove('adm-flash');
    void el.offsetWidth;
    el.classList.add('adm-flash');
    el.scrollIntoView({ block: 'nearest', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
}

/** Keterangan status pemuatan yang dibedakan: memuat, kosong, gagal. */
export function tableMessage(cols, html) {
    return `<tr><td colspan="${cols}" class="!py-10">${html}</td></tr>`;
}

/**
 * Dialog konfirmasi tindakan admin. Menyebut objek dan dampaknya, meminta alasan bila wajib,
 * lalu menjalankan onConfirm(reason). Error dari server ditampilkan di dialog tanpa menutupnya.
 */
export function confirmAction({ title, subtitle = '', impact, confirmText, tone = 'danger', reason = 'optional', minLength = 10, placeholder = '', onConfirm }) {
    const dialog = bindDialog(document.getElementById('adm-reason'));
    const form = dialog.querySelector('form');
    const text = document.getElementById('adm-reason-text');
    const error = dialog.querySelector('[data-error-for="reason"]');
    const submit = document.getElementById('adm-reason-submit');

    document.getElementById('adm-reason-title').textContent = title;
    document.getElementById('adm-reason-sub').textContent = subtitle;
    document.getElementById('adm-reason-impact').innerHTML = `${icon('info', 'w-4 h-4')}<p>${impact}</p>`;
    document.getElementById('adm-reason-field').hidden = reason === 'none';
    document.getElementById('adm-reason-opt').hidden = reason === 'required';
    text.value = '';
    text.placeholder = placeholder;
    text.classList.remove('is-invalid');
    error.textContent = '';
    submit.textContent = confirmText;
    submit.className = `ws-btn ${tone === 'danger' ? 'ws-btn-danger' : 'ws-btn-primary'}`;
    hydrateIcons(dialog);

    form.onsubmit = async (e) => {
        e.preventDefault();
        const value = text.value.trim();
        if (reason === 'required' && value.length < minLength) {
            error.textContent = value ? `Alasan minimal ${minLength} karakter agar keputusan bisa ditelusuri.` : 'Alasan wajib diisi.';
            text.classList.add('is-invalid');
            text.focus();
            return;
        }
        setBusy(submit, true, 'Menyimpan...');
        try {
            await onConfirm(value || null);
            setBusy(submit, false);
            dialog.close();
        } catch (err) {
            setBusy(submit, false);
            const fieldError = err?.errors?.reason?.[0];
            error.textContent = fieldError || errorMessage(err, 'Tindakan gagal disimpan.');
            if (fieldError) text.classList.add('is-invalid');
        }
    };

    openDialog(dialog);
    setTimeout(() => (reason === 'none' ? submit : text).focus(), 30);
}
