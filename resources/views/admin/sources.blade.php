<x-layouts.admin title="Sumber Lowongan - Admin Portal JobHarbour" active="sources" crumb="Sumber Lowongan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Sumber lowongan</h1>
            <p class="ws-lead">Situs karier resmi yang lowongannya diambil otomatis. Halaman ini hanya memantau; konfigurasi dan kredensial tetap dikelola developer.</p>
        </div>
        <button type="button" id="src-sync-all" class="ws-btn ws-btn-secondary"><i data-lucide="refresh-cw"></i>Sinkron semua</button>
    </div>

    <div id="src-summary" class="mb-4"></div>
    <div id="src-list" aria-live="polite"></div>

    @push('scripts')
        @vite('resources/js/pages/admin/sources.js')
    @endpush
</x-layouts.admin>
