<x-layouts.workspace title="Analitik - Portal Recruiter JobHarbour" active="analytics" crumb="Analitik">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Analitik rekrutmen</h1>
            <p class="ws-lead">Dihitung langsung dari lamaran dan linimasa seleksi di akunmu, bukan data contoh.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <label for="an-job" class="sr-only">Lowongan</label>
            <select id="an-job" class="ws-input w-full sm:w-[260px]"><option value="all">Semua lowongan</option></select>
            <label for="an-period" class="sr-only">Periode lamaran</label>
            <select id="an-period" class="ws-input w-auto">
                <option value="all">Semua waktu</option>
                <option value="30">30 hari terakhir</option>
                <option value="90">90 hari terakhir</option>
            </select>
        </div>
    </div>

    <div id="an-root" aria-live="polite">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
            @for ($i = 0; $i < 4; $i++)
                <div class="ws-stat"><div class="h-4 w-24 rounded animate-skeleton ws-skel"></div><div class="mt-3 h-8 w-14 rounded animate-skeleton ws-skel"></div></div>
            @endfor
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/recruiter/analytics.js')
    @endpush
</x-layouts.workspace>
