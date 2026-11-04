<x-layouts.admin title="Pengaturan platform - Admin Portal JobHarbour" active="platform" crumb="Pengaturan platform">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Pengaturan platform</h1>
            <p class="ws-lead">Pengaturan yang berlaku untuk seluruh pengunjung JobHarbour. Setiap perubahan tercatat di Log Audit. Pengaturan akunmu sendiri ada di <a href="/admin/settings" class="text-[var(--ws-accent-text)] font-semibold hover:underline">Akun saya</a>.</p>
        </div>
    </div>

    <div class="max-w-3xl space-y-6">
        <section aria-labelledby="pf-catalog">
            <h2 id="pf-catalog" class="ws-h2">Katalog publik</h2>
            <p class="ws-meta mt-0.5 mb-3">Apa yang dilihat pelamar di halaman lowongan.</p>
            <div id="pf-demo" class="adm-health" aria-live="polite"><span class="ws-meta">Memuat pengaturan...</span></div>
        </section>

        <section aria-labelledby="pf-comm">
            <h2 id="pf-comm" class="ws-h2">Komunikasi dan sumber data</h2>
            <p class="ws-meta mt-0.5 mb-3">Dikelola di halaman masing-masing.</p>
            <div class="ws-card">
                <a href="/admin/announcements" class="adm-queue-item">
                    <span class="adm-queue-icon"><i data-lucide="megaphone"></i></span>
                    <span class="adm-queue-text"><span class="adm-queue-label">Pengumuman situs</span><span class="adm-queue-sub" id="pf-ann">Banner di bagian atas situs, misalnya jadwal pemeliharaan.</span></span>
                    <i data-lucide="chevron-right" class="adm-queue-go"></i>
                </a>
                <a href="/admin/sources" class="adm-queue-item">
                    <span class="adm-queue-icon"><i data-lucide="globe"></i></span>
                    <span class="adm-queue-text"><span class="adm-queue-label">Sumber lowongan otomatis</span><span class="adm-queue-sub" id="pf-src">Aktifkan, nonaktifkan, dan lihat riwayat sinkronisasi.</span></span>
                    <i data-lucide="chevron-right" class="adm-queue-go"></i>
                </a>
            </div>
        </section>

        <section aria-labelledby="pf-next" class="adm-panel">
            <div class="adm-panel-head">
                <h2 id="pf-next" class="ws-h2">Belum tersedia</h2>
                <p class="ws-meta mt-0.5">Direncanakan untuk tahap berikutnya. Saat ini nilainya masih diatur di kode dan konfigurasi server.</p>
            </div>
            <ul class="adm-panel-body grid sm:grid-cols-2 gap-x-6 gap-y-2 text-sm text-[var(--ws-text-2)]">
                <li class="flex gap-2"><i data-lucide="circle-dashed" class="w-4 h-4 mt-0.5 shrink-0 text-[var(--ws-muted)]"></i>Identitas situs, logo, dan favicon</li>
                <li class="flex gap-2"><i data-lucide="circle-dashed" class="w-4 h-4 mt-0.5 shrink-0 text-[var(--ws-muted)]"></i>SEO dan metadata halaman publik</li>
                <li class="flex gap-2"><i data-lucide="circle-dashed" class="w-4 h-4 mt-0.5 shrink-0 text-[var(--ws-muted)]"></i>Informasi kontak dan konten halaman publik</li>
                <li class="flex gap-2"><i data-lucide="circle-dashed" class="w-4 h-4 mt-0.5 shrink-0 text-[var(--ws-muted)]"></i>Buka atau tutup pendaftaran akun baru</li>
                <li class="flex gap-2"><i data-lucide="circle-dashed" class="w-4 h-4 mt-0.5 shrink-0 text-[var(--ws-muted)]"></i>Mode pemeliharaan</li>
                <li class="flex gap-2"><i data-lucide="circle-dashed" class="w-4 h-4 mt-0.5 shrink-0 text-[var(--ws-muted)]"></i>Jenis pemberitahuan yang dikirim ke pengguna</li>
            </ul>
        </section>
    </div>

    @push('scripts')
        @vite('resources/js/pages/admin/platform.js')
    @endpush
</x-layouts.admin>
