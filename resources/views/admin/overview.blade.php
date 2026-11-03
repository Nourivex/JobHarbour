<x-layouts.admin title="Ringkasan - Admin Portal JobHarbour" active="overview" crumb="Ringkasan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Pusat kendali platform</h1>
            <p class="ws-lead">Keputusan yang menunggu, kesehatan pasar kerja, dan pertumbuhan JobHarbour. Semua angka dihitung langsung dari database.</p>
        </div>
    </div>

    <div id="ov-health" aria-live="polite"></div>

    <div class="adm-section-title"><h2>Perlu keputusan</h2><span class="ws-meta">Klik untuk menangani</span></div>
    <div id="ov-queue" class="adm-tiles is-5"></div>

    <div class="adm-section-title"><h2>Kesehatan pasar kerja</h2><span class="ws-meta" id="ov-market-sub">Lowongan terbuka yang tidak diturunkan</span></div>
    <div id="ov-market" class="adm-tiles is-4"></div>

    <div class="adm-split mt-5">
        <section class="ws-card" aria-labelledby="ov-growth-title">
            <div class="ws-card-head">
                <div>
                    <h2 id="ov-growth-title" class="ws-h2">Pertumbuhan per minggu</h2>
                    <p class="ws-meta mt-0.5">8 minggu terakhir, Senin sampai Minggu.</p>
                </div>
            </div>
            <div id="ov-growth" class="ws-card-body"></div>
        </section>

        <section class="ws-card" aria-labelledby="ov-skills-title">
            <div class="ws-card-head">
                <div>
                    <h2 id="ov-skills-title" class="ws-h2">Skill dicari vs dimiliki</h2>
                    <p class="ws-meta mt-0.5" id="ov-skills-sub">Skill yang paling banyak diminta lowongan terbuka.</p>
                </div>
            </div>
            <div id="ov-skills" class="ws-card-body"></div>
        </section>
    </div>

    <div class="adm-split mt-4">
        <section class="ws-card" aria-labelledby="ov-audit-title">
            <div class="ws-card-head">
                <div>
                    <h2 id="ov-audit-title" class="ws-h2">Tindakan admin terbaru</h2>
                    <p class="ws-meta mt-0.5">Dari Log Audit, beserta alasannya.</p>
                </div>
                <a href="/admin/audit" class="ws-btn ws-btn-ghost ws-btn-sm">Lihat semua<i data-lucide="arrow-right"></i></a>
            </div>
            <div id="ov-audit"></div>
        </section>

        <section class="ws-card" aria-labelledby="ov-data-title">
            <div class="ws-card-head">
                <div>
                    <h2 id="ov-data-title" class="ws-h2">Isi platform</h2>
                    <p class="ws-meta mt-0.5" id="ov-data-sub">Dihitung langsung dari database.</p>
                </div>
            </div>
            <div id="ov-data" class="ws-card-body"></div>
        </section>
    </div>

    @push('scripts')
        @vite('resources/js/pages/admin/overview.js')
    @endpush
</x-layouts.admin>
