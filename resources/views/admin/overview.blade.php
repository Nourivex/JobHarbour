<x-layouts.admin title="Ringkasan - Admin Portal JobHarbour" active="overview" crumb="Ringkasan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Ringkasan platform</h1>
            <p class="ws-lead" id="ov-lead">Hal yang menunggu keputusan admin, kondisi sumber lowongan, dan tindakan terbaru.</p>
        </div>
    </div>

    <div id="ov-health" class="mb-5" aria-live="polite"></div>

    <div class="adm-split">
        <section class="ws-card" aria-labelledby="ov-queue-title">
            <div class="ws-card-head">
                <div>
                    <h2 id="ov-queue-title" class="ws-h2">Perlu keputusan</h2>
                    <p class="ws-meta mt-0.5">Diurutkan dari yang paling mendesak.</p>
                </div>
            </div>
            <div id="ov-queue" class="adm-queue"></div>
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

    <section class="ws-card mt-4" aria-labelledby="ov-audit-title">
        <div class="ws-card-head">
            <div>
                <h2 id="ov-audit-title" class="ws-h2">Tindakan admin terbaru</h2>
                <p class="ws-meta mt-0.5">Dari Log Audit. Setiap keputusan tercatat beserta alasannya.</p>
            </div>
            <a href="/admin/audit" class="ws-btn ws-btn-ghost ws-btn-sm">Lihat semua<i data-lucide="arrow-right"></i></a>
        </div>
        <div id="ov-audit"></div>
    </section>

    @push('scripts')
        @vite('resources/js/pages/admin/overview.js')
    @endpush
</x-layouts.admin>
