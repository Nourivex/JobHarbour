<x-layouts.workspace title="Detail Kandidat - Portal Recruiter JobHarbour" active="pipeline" crumb="Detail Kandidat">
    <div id="cd-root" data-application-id="{{ $applicationId }}">
        <a href="/recruiter/pipeline" id="cd-back" class="ws-btn ws-btn-ghost ws-btn-sm -ml-3 mb-4"><i data-lucide="arrow-left"></i>Kembali ke lowongan</a>

        <div id="cd-error" class="hidden ws-card"></div>

        <div id="cd-content">
            <header class="flex flex-wrap items-start gap-4 mb-7">
                <span id="cd-avatar" class="ws-avatar w-16 h-16 text-xl">?</span>
                <div class="min-w-0 flex-1">
                    <h1 id="cd-name" class="ws-h1"><span class="inline-block h-8 w-56 rounded animate-skeleton ws-skel"></span></h1>
                    <p id="cd-sub" class="ws-lead"></p>
                </div>
            </header>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_340px] items-start">
                <div class="space-y-6 min-w-0">
                    <div class="ws-card ws-card-body" id="cd-match"><div class="h-48 rounded-xl animate-skeleton ws-skel"></div></div>
                    <div class="ws-card ws-card-body" id="cd-skills"></div>
                    <div class="ws-card ws-card-body" id="cd-notes"></div>
                    <div class="grid gap-6 xl:grid-cols-2">
                        <div class="ws-card ws-card-body" id="cd-profile"></div>
                        <div class="ws-card ws-card-body" id="cd-docs"></div>
                    </div>
                    <div class="ws-card ws-card-body" id="cd-history"></div>
                </div>

                <aside class="space-y-6 lg:sticky lg:top-[5.5rem]" aria-label="Status dan tindakan">
                    <section class="ws-card ws-card-body space-y-4">
                        <h2 class="ws-h2">Status lamaran</h2>
                        <dl id="cd-facts" class="space-y-3 text-sm"></dl>
                        <div class="ws-divider"></div>
                        <div id="cd-actions"></div>
                    </section>
                    <section class="ws-card ws-card-body" id="cd-timeline"></section>
                </aside>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/recruiter/candidate.js')
    @endpush
</x-layouts.workspace>
