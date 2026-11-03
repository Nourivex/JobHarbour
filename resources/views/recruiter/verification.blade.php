<x-layouts.workspace title="Verifikasi Perusahaan - Portal Recruiter JobHarbour" active="verification" crumb="Verifikasi">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Verifikasi Perusahaan</h1>
            <p class="ws-lead">Verifikasi memastikan pelamar berhadapan dengan perusahaan yang benar ada. Lowongan baru bisa dipublikasikan setelah perusahaan terverifikasi.</p>
        </div>
    </div>

    <section id="vf-status" class="ws-card ws-card-body mb-6" aria-live="polite">
        <div class="h-24 rounded-xl animate-skeleton ws-skel"></div>
    </section>

    <section class="ws-card ws-card-body mb-6" aria-label="Langkah verifikasi">
        <ol id="vf-steps" class="ws-steps"></ol>
    </section>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] items-start">
        <div id="vf-main" class="min-w-0 space-y-6"></div>

        <aside class="space-y-6">
            <section class="ws-card ws-card-body" aria-labelledby="vf-req-title">
                <h2 id="vf-req-title" class="ws-h2 mb-3">Persyaratan</h2>
                <ul class="space-y-3 text-sm text-[var(--ws-text-2)]">
                    <li class="flex gap-2.5"><i data-lucide="building-2" class="w-4 h-4 mt-0.5 text-[var(--ws-muted)]"></i><span>Profil perusahaan lengkap: nama, industri, deskripsi, alamat, kota, email, dan telepon.</span></li>
                    <li class="flex gap-2.5"><i data-lucide="file-text" class="w-4 h-4 mt-0.5 text-[var(--ws-muted)]"></i><span>Nomor Induk Berusaha (NIB) dari OSS. NPWP badan usaha bila ada.</span></li>
                    <li class="flex gap-2.5"><i data-lucide="upload" class="w-4 h-4 mt-0.5 text-[var(--ws-muted)]"></i><span>Dokumen pendukung: NIB, NPWP, akta pendirian, atau surat kuasa. PDF, JPG, atau PNG, maksimal 2 MB per file, paling banyak 5 file.</span></li>
                    <li class="flex gap-2.5"><i data-lucide="user-round" class="w-4 h-4 mt-0.5 text-[var(--ws-muted)]"></i><span>Pengaju adalah karyawan perusahaan, misalnya bagian HR atau pemilik usaha.</span></li>
                </ul>
                <div class="mt-4 rounded-xl bg-[var(--ws-surface-2)] p-3 text-[13px] text-[var(--ws-text-2)] flex gap-2">
                    <i data-lucide="lock" class="w-4 h-4 mt-0.5 shrink-0"></i>
                    <span>Dokumen hanya bisa dibuka olehmu dan admin JobHarbour. Dokumen tidak ditampilkan kepada pelamar.</span>
                </div>
            </section>

            <section class="ws-card ws-card-body" aria-labelledby="vf-history-title">
                <h2 id="vf-history-title" class="ws-h2 mb-3">Riwayat pengajuan</h2>
                <div id="vf-history"></div>
            </section>
        </aside>
    </div>

    @push('scripts')
        @vite('resources/js/pages/recruiter/verification.js')
    @endpush
</x-layouts.workspace>
