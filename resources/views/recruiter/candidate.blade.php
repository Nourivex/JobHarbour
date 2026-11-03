<x-layouts.workspace title="Detail Kandidat - Portal Recruiter JobHarbour" active="pipeline" crumb="Detail Kandidat">
    <div id="cd-root" data-application-id="{{ $applicationId }}">
        <a href="/recruiter/pipeline" id="cd-back" class="ws-btn ws-btn-ghost ws-btn-sm -ml-3 mb-4"><i data-lucide="arrow-left"></i>Kembali ke lowongan</a>

        <div id="cd-error" class="hidden ws-card"></div>

        <div id="cd-content">
            <header class="flex flex-wrap items-start gap-4 mb-6">
                <span id="cd-avatar" class="ws-ava w-14 h-14 text-lg" aria-hidden="true">?</span>
                <div class="min-w-0 flex-1">
                    <h1 id="cd-name" class="ws-h1"><span class="inline-block h-8 w-56 rounded animate-skeleton ws-skel"></span></h1>
                    <p id="cd-sub" class="ws-lead"></p>
                </div>
            </header>

            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_320px] items-start">
                <div class="min-w-0">
                    <nav class="ws-tabs mb-6" role="tablist" aria-label="Bagian detail kandidat" id="cd-tabs">
                        <button type="button" role="tab" class="ws-tab" data-tab="summary" aria-selected="true">Ringkasan</button>
                        <button type="button" role="tab" class="ws-tab" data-tab="profile" aria-selected="false">Profil &amp; dokumen</button>
                        <button type="button" role="tab" class="ws-tab" data-tab="notes" aria-selected="false">Catatan</button>
                        <button type="button" role="tab" class="ws-tab" data-tab="history" aria-selected="false">Riwayat</button>
                    </nav>
                    <div id="cd-panel" class="space-y-8" aria-live="polite"><div class="h-48 rounded-xl animate-skeleton ws-skel"></div></div>
                </div>

                <aside class="space-y-6 lg:sticky lg:top-[5.5rem]" aria-label="Status dan tindakan">
                    <section class="ws-card ws-card-body space-y-4">
                        <h2 class="ws-h2">Status lamaran</h2>
                        <dl id="cd-facts" class="space-y-3 text-sm"></dl>
                        <div class="ws-divider"></div>
                        <div id="cd-actions"></div>
                    </section>
                </aside>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/recruiter/candidate.js')
    @endpush
</x-layouts.workspace>
