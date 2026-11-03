/**
 * Admin Portal: detail satu perusahaan. Profil, pemilik akun, riwayat verifikasi, lowongan,
 * serta tangguhkan/pulihkan dengan alasan (PATCH /api/admin/companies/{id}/status, tercatat di Log Audit).
 */
import { admin, toast } from '../../api.js';
import { bootAdmin } from '../../admin/shell.js';
import { confirmAction, userStatusBadge } from '../../admin/ui.js';
import {
    esc, icon, hydrateIcons, companyLogo, verifyBadge, jobStatusBadge, formatDate, emptyState, skeletonRows, errorMessage,
} from '../../recruiter/ui.js';

const root = document.getElementById('cd-root');
const id = root.dataset.companyId;
let company = null;

const DECISION = { approved: 'Disetujui', revision_required: 'Diminta revisi', rejected: 'Ditolak', pending: 'Menunggu pemeriksaan' };

function dl(rows) {
    return `<dl class="adm-dl">${rows.filter(([, v]) => v).map(([k, v]) => `<dt>${k}</dt><dd>${v}</dd>`).join('')}</dl>`;
}

function actions(c) {
    if (c.verification_status === 'verified') {
        return `<button type="button" class="ws-btn ws-btn-danger-ghost" data-act="suspend">${icon('ban')}Tangguhkan perusahaan</button>`;
    }
    if (c.verification_status === 'suspended') {
        return `<button type="button" class="ws-btn ws-btn-secondary" data-act="reinstate">${icon('rotate-ccw')}Cabut penangguhan</button>`;
    }
    if (c.verification_status === 'pending') {
        return `<a href="/admin/verifications" class="ws-btn ws-btn-primary">${icon('badge-check')}Periksa pengajuan</a>`;
    }
    return '';
}

function render(c) {
    company = c;
    document.getElementById('ws-crumb').textContent = c.name;
    const owner = c.owner;
    root.innerHTML = `
        <div class="ws-page-head">
            <div class="flex items-center gap-4 min-w-0">
                ${companyLogo(c, 'w-14 h-14 text-lg')}
                <div class="min-w-0">
                    <h1 class="ws-h1 truncate">${esc(c.name)}</h1>
                    <p class="ws-lead">${esc([c.industry, c.city, c.size ? `${c.size} karyawan` : ''].filter(Boolean).join(' · ') || 'Profil belum lengkap')}</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">${actions(c)}</div>
        </div>

        ${c.verification_note && ['suspended', 'revision_required', 'rejected'].includes(c.verification_status) ? `
            <div class="ws-callout ws-callout-warning mb-4">${icon('info', 'w-4 h-4')}<p><b>Catatan admin yang dilihat recruiter:</b> ${esc(c.verification_note)}</p></div>` : ''}

        <div class="adm-split">
            <div class="space-y-4 min-w-0">
                <section class="ws-card">
                    <div class="ws-card-head"><h2 class="ws-h2">Profil perusahaan</h2>${verifyBadge(c.verification_status)}</div>
                    <div class="ws-card-body space-y-4">
                        ${c.description ? `<p class="text-sm text-[var(--ws-text-2)] whitespace-pre-line">${esc(c.description)}</p>` : ''}
                        ${dl([
                            ['Alamat', esc(c.address || '')],
                            ['Website', c.website ? `<a href="${esc(c.website)}" target="_blank" rel="noopener noreferrer" class="text-[var(--ws-info)] hover:underline">${esc(c.website)}</a>` : ''],
                            ['Email', esc(c.email || '')],
                            ['Telepon', esc(c.phone || '')],
                            ['Berdiri', c.founded_year ? String(c.founded_year) : ''],
                            ['Terverifikasi', c.verified_at ? formatDate(c.verified_at, true) : ''],
                            ['Terdaftar', formatDate(c.created_at, true)],
                        ])}
                    </div>
                </section>

                <section class="ws-card">
                    <div class="ws-card-head"><h2 class="ws-h2">Lowongan</h2><span class="ws-meta">${c.jobs.length} lowongan</span></div>
                    ${c.jobs.length ? `
                    <div class="ws-table-wrap">
                        <table class="ws-table">
                            <thead><tr><th scope="col">Judul</th><th scope="col">Status</th><th scope="col" class="num">Pelamar</th><th scope="col">Dibuat</th></tr></thead>
                            <tbody>${c.jobs.map((j) => `
                                <tr>
                                    <td><span class="font-medium text-[var(--ws-text)]">${esc(j.title)}</span><span class="block ws-meta">${esc(j.location || '-')}</span></td>
                                    <td><span class="flex flex-wrap gap-1">${jobStatusBadge(j.status)}${j.moderation_status === 'removed' ? `<span class="ws-pill ws-pill-danger">${icon('ban', 'w-3.5 h-3.5')}Diturunkan</span>` : ''}</span></td>
                                    <td class="num">${j.applications_count}</td>
                                    <td class="whitespace-nowrap">${formatDate(j.created_at)}</td>
                                </tr>`).join('')}
                            </tbody>
                        </table>
                    </div>` : `<div class="ws-card-body">${emptyState({ iconName: 'briefcase-business', title: 'Belum ada lowongan' })}</div>`}
                </section>
            </div>

            <div class="space-y-4 min-w-0">
                <section class="ws-card">
                    <div class="ws-card-head"><h2 class="ws-h2">Pemilik akun</h2>${owner ? userStatusBadge(owner.status) : ''}</div>
                    <div class="ws-card-body">
                        ${owner ? `${dl([['Nama', esc(owner.name)], ['Email', esc(owner.email)], ['Bergabung', formatDate(owner.created_at)]])}
                        <a href="/admin/users?q=${encodeURIComponent(owner.email)}&open=${owner.id}" class="ws-btn ws-btn-ghost ws-btn-sm mt-3 -ml-2">Kelola akun${icon('arrow-right')}</a>` : '<p class="ws-meta">Tidak ada pemilik.</p>'}
                    </div>
                </section>

                <section class="ws-card">
                    <div class="ws-card-head"><h2 class="ws-h2">Riwayat verifikasi</h2></div>
                    <div class="ws-card-body">
                        ${c.verifications.length ? `<ol class="space-y-4">${c.verifications.map((v) => `
                            <li class="text-sm">
                                <p class="font-semibold">${DECISION[v.status] || v.status}</p>
                                <p class="ws-meta">Diajukan ${formatDate(v.submitted_at, true)}${v.reviewed_at ? ` · diperiksa ${formatDate(v.reviewed_at, true)}${v.reviewer ? ` oleh ${esc(v.reviewer.name)}` : ''}` : ''}</p>
                                ${v.admin_notes ? `<p class="mt-1 text-[var(--ws-text-2)]">"${esc(v.admin_notes)}"</p>` : ''}
                            </li>`).join('')}</ol>` : '<p class="ws-meta">Belum pernah mengajukan verifikasi.</p>'}
                    </div>
                </section>
            </div>
        </div>`;
    hydrateIcons(root);
}

async function load() {
    try {
        const res = await admin.company(id);
        render(res.data);
    } catch (err) {
        root.innerHTML = emptyState({ iconName: 'circle-alert', title: err?.status === 404 ? 'Perusahaan tidak ditemukan' : 'Detail perusahaan gagal dimuat', text: esc(errorMessage(err)) });
        hydrateIcons(root);
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-company' });
    if (!ctx) return;
    root.innerHTML = skeletonRows(3, 'h-24');

    root.addEventListener('click', (e) => {
        const act = e.target.closest('[data-act]')?.dataset.act;
        if (!act || !company) return;
        const suspend = act === 'suspend';
        confirmAction({
            title: suspend ? 'Tangguhkan perusahaan?' : 'Cabut penangguhan?',
            subtitle: company.name,
            impact: suspend
                ? 'Recruiter tidak dapat mempublikasikan lowongan baru. Lowongan yang sudah terbit tetap tampil sampai diturunkan. Alasan ditampilkan kepada recruiter.'
                : 'Perusahaan kembali berstatus terverifikasi dan bisa mempublikasikan lowongan.',
            confirmText: suspend ? 'Tangguhkan' : 'Cabut penangguhan',
            tone: suspend ? 'danger' : 'primary',
            reason: suspend ? 'required' : 'none',
            placeholder: 'contoh: Beberapa pelamar melaporkan permintaan biaya pendaftaran.',
            onConfirm: async (reason) => {
                await admin.setCompanyStatus(company.id, { status: suspend ? 'suspended' : 'verified', note: reason });
                toast(suspend ? 'Perusahaan ditangguhkan dan tercatat di Log Audit.' : 'Penangguhan dicabut.', 'success');
                await load();
            },
        });
    });
    await load();
});
