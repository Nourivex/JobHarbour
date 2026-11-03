<x-layouts.admin title="Log Audit - Admin Portal JobHarbour" active="audit" crumb="Log Audit">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Log audit</h1>
            <p class="ws-lead">Semua keputusan admin tercatat otomatis oleh server: siapa, apa, kapan, dan alasannya. Catatan tidak bisa diubah atau dihapus.</p>
        </div>
    </div>

    <div class="ws-card">
        <div class="ws-card-body !pb-0">
            <div class="adm-filters">
                <label for="au-action" class="sr-only">Jenis tindakan</label>
                <select id="au-action" class="ws-input w-auto"><option value="">Semua tindakan</option></select>
            </div>
        </div>
        <div class="ws-table-wrap mt-3">
            <table class="ws-table">
                <thead>
                    <tr>
                        <th scope="col">Waktu</th>
                        <th scope="col">Admin</th>
                        <th scope="col">Tindakan</th>
                        <th scope="col">Objek</th>
                        <th scope="col">Alasan</th>
                    </tr>
                </thead>
                <tbody id="au-rows"></tbody>
            </table>
        </div>
        <div id="au-pager" class="px-4 pb-4"></div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/admin/audit.js')
    @endpush
</x-layouts.admin>
