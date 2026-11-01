<x-layouts.workspace title="Pipeline Kandidat - Portal Recruiter JobHarbour" active="pipeline" crumb="Pipeline Kandidat">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Pipeline Kandidat</h1>
            <p id="pl-lead" class="ws-lead">Pindahkan kandidat antar tahap. Tahap hanya bisa maju satu langkah atau ditolak.</p>
        </div>
        <div class="w-full sm:w-auto">
            <label for="pl-job" class="sr-only">Saring lowongan</label>
            <select id="pl-job" class="ws-input sm:w-[300px]"><option value="all">Semua lowongan</option></select>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-4">
        <div class="ws-search flex-1 lg:max-w-sm">
            <i data-lucide="search"></i>
            <label for="pl-search" class="sr-only">Cari kandidat</label>
            <input id="pl-search" type="search" class="ws-input" placeholder="Cari nama, posisi, atau skill" autocomplete="off">
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="ws-seg" role="group" aria-label="Saring skor kecocokan" id="pl-cat">
                <button type="button" data-cat="all" aria-pressed="true">Semua skor</button>
                <button type="button" data-cat="excellent" aria-pressed="false"><span class="ws-dot" style="--c:#15803D"></span>80% ke atas</button>
                <button type="button" data-cat="good" aria-pressed="false"><span class="ws-dot" style="--c:#1B65A6"></span>50 sampai 79%</button>
                <button type="button" data-cat="under" aria-pressed="false"><span class="ws-dot" style="--c:#B45309"></span>Di bawah 50%</button>
            </div>
            <label for="pl-sort" class="sr-only">Urutkan</label>
            <select id="pl-sort" class="ws-input w-auto">
                <option value="score">Skor tertinggi</option>
                <option value="newest">Lamaran terbaru</option>
                <option value="waiting">Terlama di tahap</option>
            </select>
        </div>
    </div>

    <div id="pl-stage-tabs" class="ws-tabs lg:hidden mb-3" role="tablist" aria-label="Tahap"></div>

    <div id="pl-board" class="ws-board" aria-live="polite">
        @for ($i = 0; $i < 4; $i++)
            <div class="ws-col {{ $i > 0 ? 'hidden lg:block' : '' }}"><div class="p-3 space-y-3"><div class="h-5 w-24 rounded animate-skeleton ws-skel"></div><div class="h-32 rounded-xl animate-skeleton ws-skel"></div><div class="h-32 rounded-xl animate-skeleton ws-skel"></div></div></div>
        @endfor
    </div>

    <!-- Laci pratinjau kandidat -->
    @push('modals')
        <dialog id="pl-drawer" class="ws-drawer" aria-labelledby="pl-d-name">
            <header class="flex items-start gap-3 p-5 border-b border-[var(--ws-border)]">
                <span id="pl-d-avatar" class="ws-avatar w-12 h-12 text-base">?</span>
                <div class="min-w-0 flex-1">
                    <h2 id="pl-d-name" class="text-lg font-bold truncate">Kandidat</h2>
                    <p id="pl-d-job" class="ws-meta truncate">-</p>
                    <div id="pl-d-stage" class="mt-2 flex flex-wrap items-center gap-2"></div>
                </div>
                <button type="button" class="ws-icon-btn -mr-2 -mt-1" data-dialog-close aria-label="Tutup"><i data-lucide="x"></i></button>
            </header>
            <div id="pl-d-body" class="flex-1 overflow-y-auto p-5 space-y-7"></div>
            <footer class="border-t border-[var(--ws-border)] p-5 space-y-3">
                <div id="pl-d-actions"></div>
                <a id="pl-d-full" href="#" class="ws-btn ws-btn-ghost ws-btn-sm w-full">Buka halaman lengkap kandidat<i data-lucide="arrow-up-right"></i></a>
            </footer>
        </dialog>
    @endpush

    @push('scripts')
        @vite('resources/js/pages/recruiter/pipeline.js')
    @endpush
</x-layouts.workspace>
