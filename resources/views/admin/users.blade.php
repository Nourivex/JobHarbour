<x-layouts.admin title="Pengguna - Admin Portal JobHarbour" active="users" crumb="Pengguna">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Pengguna</h1>
            <p class="ws-lead">Cari akun, lihat aktivitasnya, dan tangguhkan bila menyalahgunakan platform. Password tidak pernah ditampilkan.</p>
        </div>
    </div>

    <div class="ws-card">
        <div class="ws-card-body !pb-0">
            <div class="adm-filters">
                <div class="ws-search">
                    <i data-lucide="search"></i>
                    <label for="us-q" class="sr-only">Cari pengguna</label>
                    <input id="us-q" type="search" class="ws-input" placeholder="Cari nama atau email" autocomplete="off">
                </div>
                <label for="us-role" class="sr-only">Peran</label>
                <select id="us-role" class="ws-input w-auto">
                    <option value="">Semua peran</option>
                    <option value="applicant">Pelamar</option>
                    <option value="recruiter">Recruiter</option>
                    <option value="admin">Admin</option>
                </select>
                <label for="us-status" class="sr-only">Status akun</label>
                <select id="us-status" class="ws-input w-auto">
                    <option value="">Semua status</option>
                    <option value="active">Aktif</option>
                    <option value="suspended">Ditangguhkan</option>
                </select>
            </div>
        </div>
        <div class="ws-table-wrap mt-3">
            <table class="ws-table">
                <thead>
                    <tr>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Peran</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aktivitas</th>
                        <th scope="col">Bergabung</th>
                    </tr>
                </thead>
                <tbody id="us-rows"></tbody>
            </table>
        </div>
        <div id="us-pager" class="px-4 pb-4"></div>
    </div>

    @push('modals')
        <dialog id="us-drawer" class="ws-drawer" aria-labelledby="us-d-name">
            <header class="ws-dialog-head">
                <div class="flex items-center gap-3 min-w-0">
                    <span id="us-d-avatar" class="ws-avatar w-11 h-11 text-[15px]" aria-hidden="true">?</span>
                    <div class="min-w-0">
                        <h2 id="us-d-name" class="text-lg font-bold truncate">Pengguna</h2>
                        <p id="us-d-email" class="ws-meta truncate"></p>
                    </div>
                </div>
                <button type="button" class="ws-icon-btn -mr-2" data-dialog-close aria-label="Tutup"><i data-lucide="x"></i></button>
            </header>
            <div id="us-d-body" class="ws-dialog-body space-y-5"></div>
            <div id="us-d-foot" class="ws-dialog-foot"></div>
        </dialog>
    @endpush

    @push('scripts')
        @vite('resources/js/pages/admin/users.js')
    @endpush
</x-layouts.admin>
