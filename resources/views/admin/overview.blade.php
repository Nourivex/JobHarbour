<x-layouts.admin title="Ringkasan - Admin Portal JobHarbour" active="overview" crumb="Ringkasan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Pusat kendali platform</h1>
            <p class="ws-lead">Keputusan yang menunggu, kesehatan pasar kerja, dan pertumbuhan JobHarbour. Semua angka dihitung langsung dari database.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="ws-meta" id="ov-updated" aria-live="polite"></span>
            <button type="button" class="ws-btn ws-btn-secondary ws-btn-sm" id="ov-refresh"><i data-lucide="refresh-cw"></i>Muat ulang</button>
        </div>
    </div>

    {{-- Pita kendali: status layanan dan isi platform --}}
    <section id="ov-command" class="adm-command" aria-label="Kondisi platform"></section>

    <div class="adm-main mt-5">
        <section class="ws-card" aria-labelledby="ov-queue-title">
            <div class="ws-card-head">
                <div>
                    <h2 id="ov-queue-title" class="ws-h2">Perlu keputusan</h2>
                    <p class="ws-meta mt-0.5">Yang paling mendesak di atas. Pilih untuk menangani.</p>
                </div>
            </div>
            <div id="ov-queue" class="adm-queue mt-3"></div>
        </section>

        <section class="adm-panel" aria-labelledby="ov-market-title">
            <div class="adm-panel-head">
                <h2 id="ov-market-title" class="ws-h2">Kesehatan pasar kerja</h2>
                <p class="ws-meta mt-0.5" id="ov-market-sub">Lowongan terbuka yang tidak diturunkan.</p>
            </div>
            <div class="adm-panel-body">
                <div id="ov-market-note"></div>
                <div id="ov-market" class="adm-metrics"></div>
            </div>
        </section>
    </div>

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

    <section class="ws-card mt-5" aria-labelledby="ov-audit-title">
        <div class="ws-card-head">
            <div>
                <h2 id="ov-audit-title" class="ws-h2">Tindakan admin terbaru</h2>
                <p class="ws-meta mt-0.5">Dari Log Audit, beserta alasannya.</p>
            </div>
            <a href="/admin/audit" class="ws-btn ws-btn-ghost ws-btn-sm">Lihat semua<i data-lucide="arrow-right"></i></a>
        </div>
        <div id="ov-audit" class="mt-2"></div>
    </section>

    @push('scripts')
        @vite('resources/js/pages/admin/overview.js')
    @endpush
</x-layouts.admin>
