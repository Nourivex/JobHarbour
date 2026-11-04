<x-layouts.admin title="Ringkasan - Admin Portal JobHarbour" active="overview" crumb="Ringkasan">
    {{-- Hero: judul, waktu pembaruan, status layanan; ilustrasi kapal layar di kanan --}}
    <section class="adm-hero" aria-labelledby="ov-title">
        <img src="/images/admin-hero-sailboat.webp" alt="" class="adm-hero-art" aria-hidden="true" decoding="async" width="1100" height="466">
        <div class="adm-hero-body">
            <h1 id="ov-title" class="ws-h1">Pusat kendali platform</h1>
            <p class="adm-hero-lead">Pantau kondisi, ambil keputusan, dan kelola ekosistem JobHarbour.</p>
            <div class="adm-hero-meta">
                <span class="inline-flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i><span id="ov-updated" aria-live="polite">Memuat data...</span></span>
                <button type="button" class="ws-btn ws-btn-secondary ws-btn-sm" id="ov-refresh"><i data-lucide="refresh-cw"></i>Muat ulang</button>
            </div>
            <div id="ov-status" class="adm-hero-status" aria-live="polite"></div>
        </div>
    </section>

    <section id="ov-kpis" class="adm-kpis" aria-label="Isi platform"></section>

    <div class="adm-main">
        <section class="ws-card" aria-labelledby="ov-queue-title">
            <div class="adm-card-head">
                <span class="adm-card-icon is-orange"><i data-lucide="triangle-alert"></i></span>
                <div class="min-w-0">
                    <h2 id="ov-queue-title" class="adm-card-title">Tindakan yang perlu diperhatikan</h2>
                    <p class="ws-meta">Yang paling mendesak di atas. Pilih untuk menangani.</p>
                </div>
            </div>
            <div id="ov-queue" class="adm-queue"></div>
        </section>

        <section class="ws-card" aria-labelledby="ov-market-title">
            <div class="adm-card-head">
                <span class="adm-card-icon"><i data-lucide="activity"></i></span>
                <div class="min-w-0">
                    <h2 id="ov-market-title" class="adm-card-title">Kesehatan pasar kerja</h2>
                    <p class="ws-meta" id="ov-market-sub">Lowongan terbuka yang tidak diturunkan.</p>
                </div>
            </div>
            <div class="px-4 pb-4">
                <div id="ov-market-note"></div>
                <div id="ov-market" class="adm-metrics"></div>
            </div>
        </section>
    </div>

    <div class="adm-split mt-3">
        <section class="ws-card" aria-labelledby="ov-growth-title">
            <div class="adm-card-head">
                <span class="adm-card-icon"><i data-lucide="chart-column"></i></span>
                <div class="min-w-0">
                    <h2 id="ov-growth-title" class="adm-card-title">Pertumbuhan per minggu</h2>
                    <p class="ws-meta">8 minggu terakhir, Senin sampai Minggu.</p>
                </div>
            </div>
            <div id="ov-growth" class="px-4 pb-4"></div>
        </section>

        <section class="ws-card" aria-labelledby="ov-skills-title">
            <div class="adm-card-head">
                <span class="adm-card-icon"><i data-lucide="layers"></i></span>
                <div class="min-w-0 flex-1">
                    <h2 id="ov-skills-title" class="adm-card-title">Skill dicari vs dimiliki</h2>
                    <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1">
                        <p class="ws-meta" id="ov-skills-sub">Skill yang paling banyak diminta lowongan terbuka.</p>
                        <div class="adm-legend">
                            <span><i style="background:var(--ws-chart-2)"></i>Diminta lowongan</span>
                            <span><i style="background:var(--ws-chart-1)"></i>Dimiliki pelamar</span>
                        </div>
                    </div>
                </div>
            </div>
            <div id="ov-skills" class="px-4 pb-4"></div>
        </section>
    </div>

    <section class="ws-card mt-3" aria-labelledby="ov-audit-title">
        <div class="adm-card-head">
            <span class="adm-card-icon"><i data-lucide="scroll-text"></i></span>
            <div class="min-w-0 flex-1">
                <h2 id="ov-audit-title" class="adm-card-title">Aktivitas admin terbaru</h2>
                <p class="ws-meta">Dari Log Audit, beserta alasannya.</p>
            </div>
            <a href="/admin/audit" class="ws-btn ws-btn-ghost ws-btn-sm shrink-0">Lihat semua<i data-lucide="arrow-right"></i></a>
        </div>
        <div id="ov-audit"></div>
    </section>

    @push('scripts')
        @vite('resources/js/pages/admin/overview.js')
    @endpush
</x-layouts.admin>
