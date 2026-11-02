<x-layouts.workspace title="Lowongan - Portal Recruiter JobHarbour" active="jobs" crumb="Lowongan">
    <div id="jd-root" data-job-id="{{ $jobId }}">
        <a href="/recruiter/jobs" class="ws-btn ws-btn-ghost ws-btn-sm -ml-3 mb-4"><i data-lucide="arrow-left"></i>Semua lowongan</a>

        <div id="jd-error" class="hidden ws-card"></div>

        <div id="jd-content">
            <header class="ws-page-head !mb-5">
                <div class="min-w-0">
                    <div id="jd-badges" class="flex flex-wrap items-center gap-2 mb-2"></div>
                    <h1 id="jd-title" class="ws-h1"><span class="inline-block h-8 w-72 rounded animate-skeleton ws-skel"></span></h1>
                    <p id="jd-sub" class="ws-lead"></p>
                </div>
                <div id="jd-actions" class="flex flex-wrap gap-2"></div>
            </header>

            <!-- Ringkasan cepat lowongan -->
            <dl id="jd-stats" class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6"></dl>

            <div class="ws-tabs mb-5" role="tablist" aria-label="Bagian lowongan">
                <button type="button" role="tab" class="ws-tab" id="jd-tab-candidates" aria-controls="jd-panel-candidates" aria-selected="true" data-panel="candidates"><i data-lucide="users" class="w-4 h-4"></i>Kandidat <span id="jd-count" class="ws-count">0</span></button>
                <button type="button" role="tab" class="ws-tab" id="jd-tab-detail" aria-controls="jd-panel-detail" aria-selected="false" data-panel="detail"><i data-lucide="file-text" class="w-4 h-4"></i>Detail lowongan</button>
            </div>

            <section id="jd-panel-candidates" role="tabpanel" aria-labelledby="jd-tab-candidates">
                <div id="jd-workspace"><div class="h-72 rounded-2xl animate-skeleton ws-skel"></div></div>
            </section>

            <section id="jd-panel-detail" role="tabpanel" aria-labelledby="jd-tab-detail" class="hidden">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] items-start">
                    <section class="ws-card ws-card-body" aria-labelledby="jd-desc-title">
                        <h2 id="jd-desc-title" class="ws-h2 mb-3">Deskripsi</h2>
                        <div id="jd-description" class="text-[15px] leading-relaxed text-[var(--ws-text-2)] whitespace-pre-line"></div>
                    </section>
                    <aside class="space-y-6">
                        <section class="ws-card ws-card-body" aria-labelledby="jd-req-title">
                            <h2 id="jd-req-title" class="ws-h2">Syarat skill</h2>
                            <p class="ws-meta mt-0.5 mb-4">Bobot (Wajib 3, Penting 2, Nilai tambah 1) dipakai untuk menghitung skor kecocokan.</p>
                            <div id="jd-reqs"></div>
                        </section>
                        <section class="ws-card ws-card-body" aria-labelledby="jd-info-title">
                            <h2 id="jd-info-title" class="ws-h2 mb-3">Informasi</h2>
                            <dl id="jd-info" class="space-y-3 text-sm"></dl>
                        </section>
                    </aside>
                </div>
            </section>
        </div>
    </div>

    @push('modals')
        @include('recruiter.partials.job-form')
    @endpush
    @push('scripts')
        @vite('resources/js/pages/recruiter/job-detail.js')
    @endpush
</x-layouts.workspace>
