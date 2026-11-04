/**
 * Admin Portal: pengumuman situs. Formulir dengan pratinjau langsung (memakai kelas banner yang sama
 * dengan situs), daftar riwayat, dan mengakhiri pengumuman lebih awal.
 */
import { admin, toast } from '../../api.js';
import { bootAdmin } from '../../admin/shell.js';
import { confirmAction, flash } from '../../admin/ui.js';
import { setBusy } from '../../recruiter/components.js';
import { esc, hydrateIcons, formatDateTime, relativeTime, emptyState, skeletonRows, errorMessage, showFieldErrors, clearFieldErrors } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const STATE = { live: ['Sedang tampil', 'success'], scheduled: ['Terjadwal', 'info'], ended: ['Berakhir', 'muted'] };
const AUDIENCE = { all: 'Semua pengunjung', applicant: 'Pelamar', recruiter: 'Recruiter' };
let items = [];

function preview() {
    const form = $('an-form');
    const msg = form.elements.message.value.trim() || 'Isi pengumuman akan tampil di sini.';
    const level = form.elements.level.value;
    $('an-count').textContent = `${form.elements.message.value.length}/240`;
    $('an-preview').innerHTML = `<div class="jh-ann jh-ann-${level}" style="animation:none"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${level === 'warning' ? '<path d="m21.7 18-8-14a2 2 0 0 0-3.5 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.7-3z"/><path d="M12 9v4M12 17h.01"/>' : '<path d="M12 16v-4M12 8h.01"/><circle cx="12" cy="12" r="9"/>'}</svg><p>${esc(msg)}</p></div>`;
}

function render() {
    if (!items.length) {
        $('an-list').innerHTML = `<li class="p-5">${emptyState({ iconName: 'megaphone', title: 'Belum ada pengumuman', text: 'Pengumuman yang diterbitkan tercatat di sini.' })}</li>`;
        hydrateIcons($('an-list'));
        return;
    }
    $('an-list').innerHTML = items.map((a) => `
        <li class="px-5 py-3.5" data-row="${a.id}">
            <div class="flex flex-wrap items-center gap-1.5 mb-1">
                <span class="ws-pill ws-pill-${STATE[a.state][1]}">${STATE[a.state][0]}</span>
                <span class="ws-pill ws-pill-${a.level === 'warning' ? 'warning' : 'neutral'}">${a.level === 'warning' ? 'Peringatan' : 'Informasi'}</span>
                <span class="ws-meta">${AUDIENCE[a.audience] || a.audience}</span>
            </div>
            <p class="text-sm text-[var(--ws-text)]">${esc(a.message)}</p>
            <div class="flex flex-wrap items-center justify-between gap-2 mt-1.5">
                <span class="ws-meta">${esc(a.creator?.name || '-')} · ${relativeTime(a.created_at)}${a.ends_at ? ` · berakhir ${formatDateTime(a.ends_at)}` : ''}</span>
                ${a.state !== 'ended' ? `<button type="button" class="ws-btn ws-btn-ghost ws-btn-sm" data-end="${a.id}">Akhiri sekarang</button>` : ''}
            </div>
        </li>`).join('');
}

async function load() {
    try {
        items = (await admin.announcements()).data || [];
        render();
    } catch (err) {
        $('an-list').innerHTML = `<li class="p-5">${emptyState({ iconName: 'circle-alert', title: 'Riwayat gagal dimuat', text: esc(errorMessage(err)) })}</li>`;
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-announcements' });
    if (!ctx) return;
    const form = $('an-form');
    $('an-list').innerHTML = `<li class="p-4">${skeletonRows(2, 'h-16')}</li>`;
    preview();
    form.addEventListener('input', preview);
    form.addEventListener('change', preview);

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        clearFieldErrors(form);
        const message = form.elements.message.value.trim();
        if (message.length < 10) {
            showFieldErrors(form, { errors: { message: ['Pengumuman minimal 10 karakter.'] } });
            form.elements.message.focus();
            return;
        }
        const ends = form.elements.ends_at.value;
        const btn = form.querySelector('[type="submit"]');
        setBusy(btn, true, 'Menerbitkan...');
        try {
            const res = await admin.createAnnouncement({
                message,
                level: form.elements.level.value,
                audience: form.elements.audience.value,
                ends_at: ends ? new Date(ends).toISOString() : null,
            });
            setBusy(btn, false);
            form.reset();
            preview();
            toast('Pengumuman diterbitkan dan langsung tampil di situs.', 'success');
            await load();
            flash(document.querySelector(`#an-list [data-row="${res.data.id}"]`));
        } catch (err) {
            setBusy(btn, false);
            if (!showFieldErrors(form, err)) toast(errorMessage(err, 'Pengumuman gagal diterbitkan.'), 'error');
        }
    });

    $('an-list').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-end]');
        if (!btn) return;
        const a = items.find((x) => String(x.id) === btn.dataset.end);
        confirmAction({
            title: 'Akhiri pengumuman?',
            subtitle: a.message.slice(0, 80),
            impact: 'Banner langsung hilang dari situs untuk semua pengunjung.',
            confirmText: 'Akhiri',
            tone: 'danger',
            reason: 'none',
            onConfirm: async () => {
                await admin.endAnnouncement(a.id);
                toast('Pengumuman diakhiri.', 'success');
                await load();
                flash(document.querySelector(`#an-list [data-row="${a.id}"]`));
            },
        });
    });

    await load();
});
