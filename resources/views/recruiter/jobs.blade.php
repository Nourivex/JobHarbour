<x-layouts.workspace title="Lowongan - Portal Recruiter JobHarbour" active="jobs" crumb="Lowongan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Lowongan</h1>
            <p class="ws-lead">Kelola draft, lowongan yang sedang dibuka, dan arsip. Lowongan hanya tampil di katalog setelah dipublikasikan.</p>
        </div>
        <button type="button" class="ws-btn ws-btn-primary" data-new-job><i data-lucide="plus"></i>Lowongan baru</button>
    </div>

    <div class="ws-card">
        <div class="px-4 sm:px-5 pt-2">
            <div class="ws-tabs" role="tablist" aria-label="Status lowongan" id="jobs-tabs">
                <button type="button" role="tab" class="ws-tab" data-status="all" aria-selected="true">Semua <span class="ws-count" data-count="all">0</span></button>
                <button type="button" role="tab" class="ws-tab" data-status="open" aria-selected="false">Dipublikasikan <span class="ws-count" data-count="open">0</span></button>
                <button type="button" role="tab" class="ws-tab" data-status="draft" aria-selected="false">Draft <span class="ws-count" data-count="draft">0</span></button>
                <button type="button" role="tab" class="ws-tab" data-status="closed" aria-selected="false">Ditutup <span class="ws-count" data-count="closed">0</span></button>
                <button type="button" role="tab" class="ws-tab" data-status="archived" aria-selected="false">Diarsipkan <span class="ws-count" data-count="archived">0</span></button>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 p-4 sm:p-5">
            <div class="ws-search flex-1 sm:max-w-sm">
                <i data-lucide="search"></i>
                <label for="jobs-search" class="sr-only">Cari lowongan</label>
                <input id="jobs-search" type="search" class="ws-input" placeholder="Cari judul, kota, atau skill" autocomplete="off">
            </div>
            <div class="flex items-center gap-2 sm:ml-auto">
                <label for="jobs-sort" class="ws-meta whitespace-nowrap">Urutkan</label>
                <select id="jobs-sort" class="ws-input sm:w-[200px]">
                    <option value="updated">Terakhir diperbarui</option>
                    <option value="applicants">Pelamar terbanyak</option>
                    <option value="deadline">Batas lamaran terdekat</option>
                    <option value="title">Judul A sampai Z</option>
                </select>
            </div>
        </div>

        <div id="jobs-list" class="px-4 sm:px-5 pb-5" aria-live="polite"></div>
    </div>

    @push('modals')
        @include('recruiter.partials.job-form')
    @endpush
    @push('scripts')
        @vite('resources/js/pages/recruiter/jobs.js')
    @endpush
</x-layouts.workspace>
