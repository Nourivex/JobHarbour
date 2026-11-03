<x-layouts.workspace title="Dasbor - Portal Recruiter JobHarbour" active="dashboard" crumb="Dasbor">
    <div class="ws-page-head">
        <div>
            <h1 id="dash-greeting" class="ws-h1">Selamat datang</h1>
            <p id="dash-lead" class="ws-lead">Ringkasan rekrutmen hari ini.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="/recruiter/pipeline" class="ws-btn ws-btn-secondary"><i data-lucide="users"></i>Lihat kandidat</a>
            <button type="button" class="ws-btn ws-btn-primary" data-new-job><i data-lucide="plus"></i>Lowongan baru</button>
        </div>
    </div>

    <!-- Langkah awal, hanya tampil bila belum siap merekrut -->
    <section id="dash-onboarding" class="hidden mb-6" aria-labelledby="dash-onboarding-title"></section>

    <section aria-label="Ringkasan" id="dash-kpis" class="ws-kpis">
        @for ($i = 0; $i < 4; $i++)
            <div class="ws-kpi"><div class="h-4 w-24 rounded animate-skeleton ws-skel"></div><div class="mt-3 h-8 w-14 rounded animate-skeleton ws-skel"></div></div>
        @endfor
    </section>

    <div class="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
        <div class="space-y-6 min-w-0">
            <section class="ws-card" aria-labelledby="dash-action-title">
                <div class="ws-card-head">
                    <div>
                        <h2 id="dash-action-title" class="ws-h2">Perlu tindakan</h2>
                        <p class="ws-meta mt-0.5">Hal yang menunggu keputusanmu.</p>
                    </div>
                </div>
                <div id="dash-actions" class="ws-card-body space-y-2"></div>
            </section>

            <section class="ws-card" aria-labelledby="dash-perf-title">
                <div class="ws-card-head">
                    <div>
                        <h2 id="dash-perf-title" class="ws-h2">Performa lowongan</h2>
                        <p class="ws-meta mt-0.5">Lowongan yang sedang dibuka, diurutkan dari pelamar terbanyak.</p>
                    </div>
                    <a href="/recruiter/jobs" class="ws-btn ws-btn-ghost ws-btn-sm">Semua lowongan<i data-lucide="arrow-right"></i></a>
                </div>
                <div id="dash-perf" class="ws-card-body"></div>
            </section>
        </div>

        <div class="space-y-6 min-w-0">
            <section class="ws-card" aria-labelledby="dash-iv-title">
                <div class="ws-card-head">
                    <h2 id="dash-iv-title" class="ws-h2">Jadwal wawancara</h2>
                    <a href="/recruiter/interviews" class="ws-btn ws-btn-ghost ws-btn-sm">Semua jadwal<i data-lucide="arrow-right"></i></a>
                </div>
                <div id="dash-interviews" class="ws-card-body"></div>
            </section>

            <section class="ws-card" aria-labelledby="dash-activity-title">
                <div class="ws-card-head">
                    <h2 id="dash-activity-title" class="ws-h2">Aktivitas terbaru</h2>
                </div>
                <div id="dash-activity" class="ws-card-body"></div>
            </section>
        </div>
    </div>

    @push('modals')
        @include('recruiter.partials.job-form')
    @endpush
    @push('scripts')
        @vite('resources/js/pages/recruiter/dashboard.js')
    @endpush
</x-layouts.workspace>
