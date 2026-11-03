import { admin, toast, downloadProtected } from '../../api.js';
import { bootAdmin } from '../../admin/shell.js';
import { initMenus, openDialog, setBusy } from '../../recruiter/components.js';
import { confirmAction } from '../../admin/ui.js';
import { esc, icon, hydrateIcons, companyLogo, verifyBadge, formatDate, relativeTime, formatBytes, emptyState, skeletonRows, errorMessage } from '../../recruiter/ui.js';

const $ = (id) => document.getElementById(id);
const TYPES = { nib: 'NIB', npwp: 'NPWP', akta: 'Akta pendirian', surat_kuasa: 'Surat kuasa', lainnya: 'Lainnya' };
const state = { status: 'pending', items: [], counts: {}, current: null };

function renderList() {
    document.querySelectorAll('#av-tabs [data-status]').forEach((t) => t.setAttribute('aria-selected', String(t.dataset.status === state.status)));
    document.querySelectorAll('[data-count]').forEach((el) => { el.textContent = state.counts[el.dataset.count] || 0; });
    const navCount = document.getElementById('adm-nav-verifications');
    if (navCount) {
        navCount.textContent = state.counts.pending || 0;
        navCount.classList.toggle('hidden', !state.counts.pending);
    }

    const list = state.items.filter((i) => i.status === state.status);
    if (!list.length) {
        $('av-list').innerHTML = emptyState({
            iconName: state.status === 'pending' ? 'check-check' : 'inbox',
            title: state.status === 'pending' ? 'Tidak ada pengajuan yang menunggu' : 'Belum ada data',
            text: state.status === 'pending' ? 'Pengajuan baru dari recruiter akan muncul di sini.' : '',
        });
        return;
    }
    $('av-list').innerHTML = `<ul class="divide-y divide-[var(--ws-border)] -my-2">${list.map((v) => `
        <li>
            <button type="button" class="w-full text-left flex items-center gap-3 py-3.5 px-2 -mx-2 rounded-xl hover:bg-[var(--ws-surface-2)] transition-colors" data-open="${v.id}">
                ${companyLogo(v.company, 'w-11 h-11 text-sm')}
                <span class="min-w-0 flex-1">
                    <span class="block font-semibold truncate">${esc(v.company?.name || '-')}</span>
                    <span class="block ws-meta truncate">${esc(v.legal_name)} · NIB ${esc(v.registration_number)} · ${v.documents.length} dokumen</span>
                </span>
                <span class="hidden sm:block text-right">
                    <span class="block text-sm">${relativeTime(v.submitted_at)}</span>
                    <span class="block ws-meta">${esc(v.company?.owner?.name || '')}</span>
                </span>
                ${icon('chevron-right', 'w-4 h-4 text-[var(--ws-muted)]')}
            </button>
        </li>`).join('')}</ul>`;
}

function renderDialog(v) {
    const c = v.company || {};
    $('av-d-logo').innerHTML = companyLogo(c, 'w-11 h-11 text-sm');
    $('av-d-title').textContent = c.name || '-';
    $('av-d-sub').textContent = [c.industry, c.city, c.website].filter(Boolean).join(' · ');
    $('av-d-body').innerHTML = `
        <div class="flex flex-wrap items-center gap-2">${verifyBadge(c.verification_status)}<span class="ws-meta">Diajukan ${formatDate(v.submitted_at, true)} oleh ${esc(c.owner?.name || '-')} (${esc(c.owner?.email || '-')})</span>${c.id ? `<a href="/admin/companies/${c.id}" class="ws-btn ws-btn-ghost ws-btn-sm ml-auto">Detail perusahaan${icon('arrow-right')}</a>` : ''}</div>
        <dl class="grid sm:grid-cols-2 gap-4 text-sm">
            <div><dt class="ws-meta">Nama badan usaha</dt><dd class="font-medium">${esc(v.legal_name)}</dd></div>
            <div><dt class="ws-meta">Nama di profil</dt><dd class="font-medium">${esc(c.name || '-')}</dd></div>
            <div><dt class="ws-meta">NIB</dt><dd class="font-medium ws-num">${esc(v.registration_number)}</dd></div>
            <div><dt class="ws-meta">NPWP</dt><dd class="font-medium ws-num">${esc(v.tax_number || '-')}</dd></div>
            <div><dt class="ws-meta">Jabatan pengaju</dt><dd class="font-medium">${esc(v.applicant_position)}</dd></div>
            <div><dt class="ws-meta">Website</dt><dd class="font-medium break-all">${c.website ? `<a href="${esc(c.website)}" target="_blank" rel="noopener noreferrer" class="text-[var(--ws-info)] hover:underline">${esc(c.website)}</a>` : '-'}</dd></div>
        </dl>
        ${v.notes ? `<div class="rounded-xl bg-[var(--ws-surface-2)] p-3 text-sm"><p class="ws-meta mb-0.5">Catatan dari recruiter</p>${esc(v.notes)}</div>` : ''}
        <div>
            <p class="ws-h3 mb-2">Dokumen</p>
            <ul class="space-y-1.5">${v.documents.map((d) => `
                <li><button type="button" class="ws-menu-item border border-[var(--ws-border)]" data-doc="${esc(d.url)}" data-name="${esc(d.name)}">${icon('file-text')}<span class="flex-1 min-w-0 truncate">${esc(d.name)}</span><span class="ws-meta">${TYPES[d.type] || 'Lainnya'}${d.size ? ` · ${formatBytes(d.size)}` : ''}</span>${icon('download')}</button></li>`).join('')}
            </ul>
        </div>
        ${v.status !== 'pending' ? `<div class="rounded-xl border border-[var(--ws-border)] p-3 text-sm"><p class="font-semibold">${{ approved: 'Disetujui', revision_required: 'Diminta revisi' }[v.status] || 'Ditolak'} ${v.reviewed_at ? formatDate(v.reviewed_at, true) : ''}${v.reviewer ? ` oleh ${esc(v.reviewer.name)}` : ''}</p>${v.admin_notes ? `<p class="mt-1 text-[var(--ws-text-2)]">${esc(v.admin_notes)}</p>` : ''}</div>` : `
        <div>
            <label for="av-note" class="ws-label">Catatan untuk recruiter <span class="opt">(wajib bila meminta revisi atau menolak)</span></label>
            <textarea id="av-note" rows="3" maxlength="1000" class="ws-input" placeholder="contoh: Dokumen NIB tidak terbaca, unggah ulang dalam resolusi lebih tinggi."></textarea>
            <p class="ws-error" id="av-note-error"></p>
        </div>`}`;

    const foot = $('av-d-foot');
    if (v.status === 'pending') {
        foot.innerHTML = `
            <button type="button" class="ws-btn ws-btn-ghost mr-auto" data-dialog-close>Tutup</button>
            <button type="button" class="ws-btn ws-btn-danger-ghost" data-decide="reject" title="Pengajuan tidak valid atau mencurigakan">${icon('circle-x')}Tolak</button>
            <button type="button" class="ws-btn ws-btn-secondary" data-decide="revision" title="Dokumen kurang atau tidak terbaca, recruiter memperbaiki lalu mengajukan ulang">${icon('file-pen-line')}Minta revisi</button>
            <button type="button" class="ws-btn ws-btn-primary" data-decide="approve">${icon('badge-check')}Setujui</button>`;
    } else if (c.verification_status === 'verified' || c.verification_status === 'suspended') {
        const suspended = c.verification_status === 'suspended';
        foot.innerHTML = `
            <button type="button" class="ws-btn ws-btn-ghost mr-auto" data-dialog-close>Tutup</button>
            <button type="button" class="ws-btn ${suspended ? 'ws-btn-secondary' : 'ws-btn-danger-ghost'}" data-company-status="${suspended ? 'verified' : 'suspended'}">${icon(suspended ? 'rotate-ccw' : 'ban')}${suspended ? 'Cabut penangguhan' : 'Tangguhkan perusahaan'}</button>`;
    } else {
        foot.innerHTML = '<button type="button" class="ws-btn ws-btn-secondary" data-dialog-close>Tutup</button>';
    }
    hydrateIcons($('av-dialog'));
}

async function load() {
    $('av-list').innerHTML = skeletonRows(3, 'h-16');
    try {
        const res = await admin.verifications();
        state.items = res.data || [];
        state.counts = res.meta?.counts || {};
        renderList();
    } catch (err) {
        $('av-list').innerHTML = emptyState({ iconName: 'circle-alert', title: 'Data gagal dimuat', text: esc(errorMessage(err)) });
    }
}

document.addEventListener('DOMContentLoaded', async () => {
    const ctx = await bootAdmin({ page: 'admin-verifications' });
    if (!ctx) return;
    initMenus();

    $('av-tabs').addEventListener('click', (e) => {
        const t = e.target.closest('[data-status]');
        if (!t) return;
        state.status = t.dataset.status;
        renderList();
    });

    document.addEventListener('click', async (e) => {
        const open = e.target.closest('[data-open]');
        if (open) {
            state.current = state.items.find((i) => String(i.id) === open.dataset.open);
            renderDialog(state.current);
            openDialog($('av-dialog'));
            return;
        }
        const doc = e.target.closest('[data-doc]');
        if (doc) {
            try { await downloadProtected(doc.dataset.doc, doc.dataset.name); } catch (err) { toast(errorMessage(err, 'Dokumen gagal diunduh.'), 'error'); }
            return;
        }
        const decide = e.target.closest('[data-decide]');
        if (decide) {
            const note = $('av-note').value.trim();
            if (decide.dataset.decide !== 'approve' && !note) {
                $('av-note-error').textContent = decide.dataset.decide === 'revision' ? 'Tulis bagian yang perlu diperbaiki.' : 'Tulis alasan penolakan.';
                $('av-note').focus();
                return;
            }
            setBusy(decide, true, 'Menyimpan...');
            try {
                await admin.review(state.current.id, { decision: decide.dataset.decide, admin_notes: note || null });
                toast({ approve: 'Perusahaan diverifikasi.', revision: 'Recruiter diminta memperbaiki pengajuan.' }[decide.dataset.decide] || 'Pengajuan ditolak.', 'success');
                $('av-dialog').close();
                await load();
            } catch (err) {
                setBusy(decide, false);
                toast(errorMessage(err, 'Keputusan gagal disimpan.'), 'error');
            }
            return;
        }
        const cs = e.target.closest('[data-company-status]');
        if (cs) {
            // Dialog konfirmasi yang sama dengan halaman Perusahaan: menyebut dampak dan meminta alasan
            const suspend = cs.dataset.companyStatus === 'suspended';
            const company = state.current.company;
            $('av-dialog').close();
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
                    await admin.setCompanyStatus(company.id, { status: cs.dataset.companyStatus, note: reason });
                    toast(suspend ? 'Perusahaan ditangguhkan dan tercatat di Log Audit.' : 'Penangguhan dicabut.', 'success');
                    await load();
                },
            });
        }
    });

    await load();
});
