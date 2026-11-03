<x-layouts.admin title="Perusahaan - Admin Portal JobHarbour" active="companies" crumb="Perusahaan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Perusahaan</h1>
            <p class="ws-lead">Semua perusahaan yang terdaftar beserta status verifikasinya. Yang menunggu tindakan tampil paling atas.</p>
        </div>
    </div>

    <div class="ws-card">
        <div class="ws-card-body !pb-0 space-y-3">
            <div class="ws-tabs" role="tablist" aria-label="Status verifikasi" id="co-tabs"></div>
            <div class="adm-filters">
                <div class="ws-search">
                    <i data-lucide="search"></i>
                    <label for="co-q" class="sr-only">Cari perusahaan</label>
                    <input id="co-q" type="search" class="ws-input" placeholder="Cari nama atau kota" autocomplete="off">
                </div>
            </div>
        </div>
        <div class="ws-table-wrap mt-3">
            <table class="ws-table">
                <thead>
                    <tr>
                        <th scope="col">Perusahaan</th>
                        <th scope="col">Pemilik</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="num">Lowongan aktif</th>
                        <th scope="col">Terdaftar</th>
                    </tr>
                </thead>
                <tbody id="co-rows"></tbody>
            </table>
        </div>
        <div id="co-pager" class="px-4 pb-4"></div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/admin/companies.js')
    @endpush
</x-layouts.admin>
