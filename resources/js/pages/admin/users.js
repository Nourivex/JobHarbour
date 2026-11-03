/**
 * Admin Portal: pengguna. Daftar dari server (cari, filter peran/status, halaman), detail di laci,
 * dan tangguhkan/aktifkan dengan alasan. Akun admin tidak bisa diubah statusnya dari sini.
 */
import { admin, toast } from '../../api.js';
import { bootAdmin } from '../../admin/shell.js';
import { confirmAction, pager, roleBadge, userStatusBadge, tableMessage, ROLE } from '../../admin/ui.js';
import { debounce, openDialog, bindDialog } from '../../recruiter/components.js';
import { esc, icon, hydrateIcons, initials, verifyBadge, jobStatusBadge, formatDate, relativeTime, emptyState, skeletonRows, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const params = new URLSearchParams(location.search);
const state = {
    q: params.get('q') || '',
    role: ROLE[params.get('role')] ? params.get('role') : '',
    status: ['active', 'suspended'].includes(params.get('status')) ? params.get('status') : '',
    page: Number(params.get('page')) || 1,
    open: params.get('open'),
    me: null,
    current: null,
};

function syncUrl() {
    const p = new URLSearchParams();
    ['q', 'role', 'status'].forEach((k) => state[k] && p.set(k, state[k]));
    if (state.page > 1) p.set('page', state.page);
    history.replaceState(null, '', p.toString() ? `?${p}` : location.pathname);
}

function activity(u) {
    if (u.role === 'applicant') return `${u.applications_count || 0} lamaran`;
    if (u.role === 'recruiter') return `${u.jobs_count || 0} lowongan${u.company ? ` · ${esc(u.company.name)}` : ''}`;
    return '-';
}

async function load() {
    syncUrl();
    $('us-rows').innerHTML = tableMessage(5, '<div class="ws-skel h-10"></div>');
    try {
        const res = await admin.users({ q: state.q || undefined, role: state.role || undefined, status: state.status || undefined, page: state.page, per_page: 20 });
        const list = res.data || [];
        if (!list.length) {
            $('us-rows').innerHTML = tableMessage(5, emptyState({ iconName: 'users', title: 'Tidak ada pengguna yang cocok', text: 'Ubah kata kunci, peran, atau status.' }));
            $('us-pager').innerHTML = '';
            return;
        }
        $('us-rows').innerHTML = list.map((u) => `
            <tr class="cursor-pointer" data-user="${u.id}">
                <td>
                    <button type="button" class="adm-row-btn flex items-center gap-3 min-w-0" data-user="${u.id}">
                        <span class="ws-avatar w-9 h-9 text-xs shrink-0">${esc(initials(u.name))}</span>
                        <span class="min-w-0">
                            <span class="block font-semibold text-[var(--ws-text)] truncate">${esc(u.name)}</span>
                            <span class="block ws-meta truncate">${esc(u.email)}</span>
                        </span>
                    </button>
                </td>
                <td data-label="Peran">${roleBadge(u.role)}</td>
                <td data-label="Status">${userStatusBadge(u.status)}</td>
                <td data-label="Aktivitas" class="text-sm">${activity(u)}</td>
                <td data-label="Bergabung" class="whitespace-nowrap">${formatDate(u.created_at)}</td>
            </tr>`).join('');
        $('us-pager').innerHTML = pager(res.meta?.pagination, 'pengguna');
        hydrateIcons($('us-pager'));
    } catch (err) {
        $('us-rows').innerHTML = tableMessage(5, emptyState({ iconName: 'circle-alert', title: 'Daftar pengguna gagal dimuat', text: esc(errorMessage(err)) }));
    }
}

function renderDrawer(u) {
    state.current = u;
    $('us-d-avatar').textContent = initials(u.name);
    $('us-d-name').textContent = u.name;
    $('us-d-email').textContent = u.email;
    const list = (items, fn, empty) => (items.length ? `<ul class="divide-y divide-[var(--ws-border)] text-sm">${items.map(fn).join('')}</ul>` : `<p class="ws-meta">${empty}</p>`);
    $('us-d-body').innerHTML = `
        <div class="flex flex-wrap gap-2">${roleBadge(u.role)}${userStatusBadge(u.status)}</div>
        ${u.status === 'suspended' ? `<div class="ws-callout ws-callout-warning">${icon('ban', 'w-4 h-4')}<p>Ditangguhkan ${u.status_changed_at ? formatDate(u.status_changed_at, true) : ''}${u.status_changed_by ? ` oleh ${esc(u.status_changed_by.name)}` : ''}.${u.status_reason ? ` Alasan: "${esc(u.status_reason)}"` : ''}</p></div>` : ''}
        <dl class="adm-dl">
            <dt>Bergabung</dt><dd>${formatDate(u.created_at, true)}</dd>
            ${u.phone ? `<dt>Telepon</dt><dd>${esc(u.phone)}</dd>` : ''}
            ${u.location ? `<dt>Lokasi</dt><dd>${esc(u.location)}</dd>` : ''}
            ${u.role === 'applicant' ? `<dt>Skill di profil</dt><dd>${u.skills_count}</dd>` : ''}
            ${u.company ? `<dt>Perusahaan</dt><dd><a href="/admin/companies/${u.company.id}" class="text-[var(--ws-info)] hover:underline">${esc(u.company.name)}</a> ${verifyBadge(u.company.verification_status)}</dd>` : ''}
        </dl>
        ${u.role === 'applicant' ? `<section><h3 class="ws-h3 mb-2">Lamaran terbaru</h3>${list(u.recent_applications, (a) => `<li class="py-2 flex justify-between gap-3"><span class="min-w-0 truncate">${esc(a.job_title || '-')} <span class="ws-meta">· ${esc(a.company || '')}</span></span><span class="ws-meta whitespace-nowrap">${relativeTime(a.created_at)}</span></li>`, 'Belum pernah melamar.')}</section>` : ''}
        ${u.role === 'recruiter' ? `<section><h3 class="ws-h3 mb-2">Lowongan terbaru</h3>${list(u.recent_jobs, (j) => `<li class="py-2 flex items-center justify-between gap-3"><span class="min-w-0 truncate">${esc(j.title)}</span>${jobStatusBadge(j.status)}</li>`, 'Belum membuat lowongan.')}</section>` : ''}
        <section><h3 class="ws-h3 mb-2">Riwayat tindakan admin</h3>${list(u.history, (h) => `<li class="py-2"><span class="block">${esc(h.action_label)} oleh ${esc(h.actor?.name || '-')}</span><span class="block ws-meta">${formatDate(h.created_at, true)}${h.reason ? ` · "${esc(h.reason)}"` : ''}</span></li>`, 'Belum ada.')}</section>`;

    const protectedAccount = u.role === 'admin' || u.id === state.me?.id;
    $('us-d-foot').innerHTML = `
        <button type="button" class="ws-btn ws-btn-ghost mr-auto" data-dialog-close>Tutup</button>
        ${protectedAccount
            ? '<span class="ws-meta">Status akun admin tidak dapat diubah dari Admin Portal.</span>'
            : u.status === 'suspended'
                ? `<button type="button" class="ws-btn ws-btn-primary" data-status-act="active">${icon('rotate-ccw')}Aktifkan kembali</button>`
                : `<button type="button" class="ws-btn ws-btn-danger" data-status-act="suspended">${icon('ban')}Tangguhkan akun</button>`}`;
    hydrateIcons($('us-drawer'));
}

async function openUser(id) {
    const drawer = bindDialog($('us-drawer'));
    $('us-d-body').innerHTML = skeletonRows(3, 'h-14');
    $('us-d-foot').innerHTML = '';
    openDialog(drawer);
    try {
        const res = await admin.user(id);
        renderDrawer(res.data);
    } catch (err) {
        $('us-d-body').innerHTML = emptyState({ iconName: 'circle-alert', title: 'Detail pengguna gagal dimuat', text: esc(errorMessage(err)) });
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-users' });
    if (!ctx) return;
    state.me = ctx.user;

    $('us-q').value = state.q;
    $('us-role').value = state.role;
    $('us-status').value = state.status;
    $('us-q').addEventListener('input', debounce((e) => { state.q = e.target.value.trim(); state.page = 1; load(); }, 250));
    $('us-role').addEventListener('change', (e) => { state.role = e.target.value; state.page = 1; load(); });
    $('us-status').addEventListener('change', (e) => { state.status = e.target.value; state.page = 1; load(); });

    document.addEventListener('click', (e) => {
        const pg = e.target.closest('[data-goto-page]');
        if (pg && !pg.disabled) { state.page = Number(pg.dataset.gotoPage); load(); return; }
        const userEl = e.target.closest('[data-user]');
        if (userEl && !e.target.closest('dialog')) { openUser(userEl.dataset.user); return; }
        const act = e.target.closest('[data-status-act]');
        if (!act || !state.current) return;
        const u = state.current;
        const suspend = act.dataset.statusAct === 'suspended';
        confirmAction({
            title: suspend ? 'Tangguhkan akun?' : 'Aktifkan kembali akun?',
            subtitle: `${u.name} · ${u.email}`,
            impact: suspend
                ? 'Pengguna langsung keluar dari semua sesi dan tidak bisa masuk atau memakai API sampai diaktifkan kembali. Data lamaran dan lowongannya tidak dihapus.'
                : 'Pengguna bisa masuk dan memakai JobHarbour seperti biasa.',
            confirmText: suspend ? 'Tangguhkan' : 'Aktifkan kembali',
            tone: suspend ? 'danger' : 'primary',
            reason: suspend ? 'required' : 'optional',
            placeholder: suspend ? 'contoh: Mengirim pesan spam ke banyak recruiter.' : '',
            onConfirm: async (reason) => {
                await admin.setUserStatus(u.id, { status: suspend ? 'suspended' : 'active', reason });
                toast(suspend ? 'Akun ditangguhkan dan tercatat di Log Audit.' : 'Akun diaktifkan kembali.', 'success');
                await Promise.all([load(), admin.user(u.id).then((r) => renderDrawer(r.data))]);
            },
        });
    });

    await load();
    // Tautan langsung dari detail perusahaan atau Log Audit: buka laci akun yang dimaksud
    if (state.open) openUser(state.open);
});
