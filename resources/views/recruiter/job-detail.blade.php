<x-layouts.workspace title="Detail Lowongan - Portal Recruiter JobHarbour" active="jobs" crumb="Detail Lowongan">
    <div id="jd-root" data-job-id="{{ $jobId }}">
        <a href="/recruiter/jobs" class="ws-btn ws-btn-ghost ws-btn-sm -ml-3 mb-4"><i data-lucide="arrow-left"></i>Semua lowongan</a>

        <div id="jd-error" class="hidden ws-card"></div>

        <div id="jd-content">
            <header class="ws-page-head">
                <div class="min-w-0">
                    <div id="jd-badges" class="flex flex-wrap items-center gap-2 mb-2"></div>
                    <h1 id="jd-title" class="ws-h1"><span class="inline-block h-8 w-72 rounded animate-skeleton ws-skel"></span></h1>
                    <p id="jd-sub" class="ws-lead"></p>
                </div>
                <div id="jd-actions" class="flex flex-wrap gap-2"></div>
            </header>

            <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_360px] items-start">
                <div class="space-y-6 min-w-0">
                    <section class="ws-card" aria-labelledby="jd-app-title">
                        <div class="ws-card-head">
                            <h2 id="jd-app-title" class="ws-h2">Pelamar</h2>
                            <a id="jd-pipeline-link" href="#" class="ws-btn ws-btn-ghost ws-btn-sm">Buka di pipeline<i data-lucide="arrow-right"></i></a>
                        </div>
                        <div id="jd-applicants" class="ws-card-body"></div>
                    </section>

                    <section class="ws-card ws-card-body" aria-labelledby="jd-desc-title">
                        <h2 id="jd-desc-title" class="ws-h2 mb-3">Deskripsi</h2>
                        <div id="jd-description" class="text-[15px] leading-relaxed text-[var(--ws-text-2)] whitespace-pre-line"></div>
                    </section>
                </div>

                <aside class="space-y-6">
                    <section class="ws-card ws-card-body" aria-labelledby="jd-funnel-title">
                        <h2 id="jd-funnel-title" class="ws-h2 mb-4">Tahapan</h2>
                        <div id="jd-funnel" class="space-y-3"></div>
                    </section>
                    <section class="ws-card ws-card-body" aria-labelledby="jd-req-title">
                        <h2 id="jd-req-title" class="ws-h2">Syarat skill</h2>
                        <p class="ws-meta mt-0.5 mb-4">Bobot dipakai untuk menghitung skor kecocokan.</p>
                        <div id="jd-reqs"></div>
                    </section>
                    <section class="ws-card ws-card-body" aria-labelledby="jd-info-title">
                        <h2 id="jd-info-title" class="ws-h2 mb-3">Informasi</h2>
                        <dl id="jd-info" class="space-y-3 text-sm"></dl>
                    </section>
                </aside>
            </div>
        </div>
    </div>

    @push('modals')
        @include('recruiter.partials.job-form')
    @endpush
    @push('scripts')
        @vite('resources/js/pages/recruiter/job-detail.js')
    @endpush
</x-layouts.workspace>
