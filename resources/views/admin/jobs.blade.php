<x-layouts.admin title="Lowongan - Admin Portal JobHarbour" active="jobs" crumb="Lowongan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Moderasi lowongan</h1>
            <p class="ws-lead">Turunkan lowongan yang menyesatkan atau melanggar aturan. Isi lowongan tetap tanggung jawab recruiter; admin tidak mengeditnya.</p>
        </div>
    </div>

    <div id="jb-demo-setting" class="adm-health mb-4" aria-live="polite"></div>

    <div class="ws-card">
        <div class="ws-card-body !pb-0">
            <div class="adm-filters">
                <div class="ws-search">
                    <i data-lucide="search"></i>
                    <label for="jb-q" class="sr-only">Cari lowongan</label>
                    <input id="jb-q" type="search" class="ws-input" placeholder="Cari judul atau perusahaan" autocomplete="off">
                </div>
                <label for="jb-moderation" class="sr-only">Moderasi</label>
                <select id="jb-moderation" class="ws-input w-auto">
                    <option value="">Semua moderasi</option>
                    <option value="none">Tampil normal</option>
                    <option value="removed">Diturunkan admin</option>
                </select>
                <label for="jb-status" class="sr-only">Status dari recruiter</label>
                <select id="jb-status" class="ws-input w-auto">
                    <option value="">Semua status</option>
                    <option value="open">Dipublikasikan</option>
                    <option value="draft">Draft</option>
                    <option value="closed">Ditutup</option>
                    <option value="archived">Diarsipkan</option>
                </select>
                <label for="jb-source" class="sr-only">Sumber</label>
                <select id="jb-source" class="ws-input w-auto">
                    <option value="">Semua sumber</option>
                    <option value="direct">Dari recruiter</option>
                    <option value="external">Situs karier resmi</option>
                    <option value="partner">Mitra</option>
                </select>
                <label for="jb-demo" class="sr-only">Jenis data</label>
                <select id="jb-demo" class="ws-input w-auto">
                    <option value="">Data nyata dan demo</option>
                    <option value="false">Hanya data nyata</option>
                    <option value="true">Hanya data demo</option>
                </select>
            </div>
            <div id="jb-only" class="ws-fchips mt-2"></div>
        </div>
        <div class="ws-table-wrap mt-3">
            <table class="ws-table adm-stack">
                <thead>
                    <tr>
                        <th scope="col">Lowongan</th>
                        <th scope="col">Status</th>
                        <th scope="col">Sumber</th>
                        <th scope="col" class="num">Pelamar</th>
                        <th scope="col"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody id="jb-rows"></tbody>
            </table>
        </div>
        <div id="jb-pager" class="px-4 pb-4"></div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/admin/jobs.js')
    @endpush
</x-layouts.admin>
