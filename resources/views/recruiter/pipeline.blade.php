<x-layouts.workspace title="Kandidat - Portal Recruiter JobHarbour" active="pipeline" crumb="Kandidat">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Kandidat</h1>
            <p id="pl-lead" class="ws-lead">Semua lamaran ke lowonganmu. Tahap seleksi hanya bisa maju satu langkah atau ditolak.</p>
        </div>
    </div>

    <div id="pl-workspace">
        <div class="space-y-3">
            <div class="h-11 w-full max-w-xl rounded-xl animate-skeleton ws-skel"></div>
            <div class="h-10 w-80 rounded-xl animate-skeleton ws-skel"></div>
            <div class="h-72 rounded-2xl animate-skeleton ws-skel"></div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/recruiter/pipeline.js')
    @endpush
</x-layouts.workspace>
