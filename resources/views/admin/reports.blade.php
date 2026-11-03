<x-layouts.admin title="Laporan - Admin Portal JobHarbour" active="reports" crumb="Laporan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Laporan dan banding</h1>
            <p class="ws-lead">Laporan pengguna atas lowongan bermasalah dan pengajuan pemulihan dari recruiter. Yang paling lama menunggu tampil paling atas.</p>
        </div>
    </div>

    <div class="ws-card">
        <div class="ws-card-body !pb-0 space-y-3">
            <div class="ws-tabs" role="tablist" aria-label="Status laporan" id="rp-tabs">
                <button type="button" role="tab" class="ws-tab" data-status="active" aria-selected="true">Perlu ditangani <span class="ws-count" data-count="active">0</span></button>
                <button type="button" role="tab" class="ws-tab" data-status="resolved" aria-selected="false">Selesai <span class="ws-count" data-count="resolved">0</span></button>
                <button type="button" role="tab" class="ws-tab" data-status="dismissed" aria-selected="false">Ditolak <span class="ws-count" data-count="dismissed">0</span></button>
            </div>
            <div class="adm-filters">
                <label for="rp-kind" class="sr-only">Jenis</label>
                <select id="rp-kind" class="ws-input w-auto">
                    <option value="">Laporan dan banding</option>
                    <option value="report">Hanya laporan pengguna</option>
                    <option value="appeal">Hanya banding recruiter</option>
                </select>
            </div>
        </div>
        <ul id="rp-list" class="divide-y divide-[var(--ws-border)] mt-3" aria-live="polite"></ul>
        <div id="rp-pager" class="px-4 pb-4"></div>
    </div>

    @push('modals')
        <dialog id="rp-drawer" class="ws-drawer" aria-labelledby="rp-d-title">
            <header class="ws-dialog-head">
                <div class="min-w-0">
                    <div id="rp-d-badges" class="flex flex-wrap gap-1.5 mb-1"></div>
                    <h2 id="rp-d-title" class="text-lg font-bold truncate">Laporan</h2>
                    <p id="rp-d-sub" class="ws-meta truncate"></p>
                </div>
                <button type="button" class="ws-icon-btn -mr-2" data-dialog-close aria-label="Tutup"><i data-lucide="x"></i></button>
            </header>
            <div id="rp-d-body" class="ws-dialog-body space-y-5"></div>
            <div id="rp-d-foot" class="ws-dialog-foot"></div>
        </dialog>
    @endpush

    @push('scripts')
        @vite('resources/js/pages/admin/reports.js')
    @endpush
</x-layouts.admin>
