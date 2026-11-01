<x-layouts.app title="Papan Rekrutmen - JobHarbour">

    <div id="rk-root" class="max-w-[1360px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">

        <!-- ========================================================== -->
        <!-- HEADER                                                     -->
        <!-- ========================================================== -->
        <header class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-[1.75rem] sm:text-[2rem] font-extrabold font-heading text-[#0B1B2B] leading-tight">Papan Rekrutmen</h1>
                <p id="rk-greeting" class="mt-1 text-[15px] text-[#3D4B5C]">Memuat ringkasan kandidat...</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2.5 sm:items-center shrink-0">
                <label for="rk-job-filter" class="sr-only">Saring berdasarkan lowongan</label>
                <div class="rk-select">
                    <svg class="rk-select-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                    <select id="rk-job-filter" class="sm:w-[300px]">
                        <option value="all">Semua lowongan</option>
                    </select>
                </div>
                <button type="button" id="rk-new-job" class="btn-press shrink-0 whitespace-nowrap inline-flex items-center justify-center gap-2 h-11 px-5 rounded-xl bg-[#C8460B] hover:bg-[#A93A08] text-white text-sm font-semibold">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                    Pasang Lowongan
                </button>
            </div>
        </header>

        <!-- ========================================================== -->
        <!-- RINGKASAN: setiap kartu bisa diklik untuk menyaring papan   -->
        <!-- ========================================================== -->
        <section aria-label="Ringkasan rekrutmen" class="mt-6 grid grid-cols-2 xl:grid-cols-4 gap-3">
            <button type="button" class="rk-metric" data-focus="review" aria-pressed="false">
                <span class="rk-metric-icon bg-[#EBF3FB] text-[#1B65A6]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M9 15l2 2 4-4"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="rk-metric-label">Perlu ditinjau</span>
                    <span id="rk-m-review" class="rk-metric-value">-</span>
                    <span id="rk-m-review-sub" class="rk-metric-sub">di tahap Screening</span>
                </span>
            </button>
            <button type="button" class="rk-metric" data-focus="ready" aria-pressed="false">
                <span class="rk-metric-icon bg-[#E7F5EC] text-[#157F3B]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="rk-metric-label">Siap dimajukan</span>
                    <span id="rk-m-ready" class="rk-metric-value">-</span>
                    <span class="rk-metric-sub">skor 80% ke atas, masih Screening</span>
                </span>
            </button>
            <button type="button" class="rk-metric" data-focus="interview" aria-pressed="false">
                <span class="rk-metric-icon bg-[#FFF3EA] text-[#C8460B]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="rk-metric-label">Wawancara mendatang</span>
                    <span id="rk-m-interview" class="rk-metric-value">-</span>
                    <span id="rk-m-interview-sub" class="rk-metric-sub">belum ada jadwal</span>
                </span>
            </button>
            <button type="button" class="rk-metric" data-goto="jobs">
                <span class="rk-metric-icon bg-[#E8EDF2] text-[#0C2238]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/></svg>
                </span>
                <span class="min-w-0">
                    <span class="rk-metric-label">Lowongan aktif</span>
                    <span id="rk-m-jobs" class="rk-metric-value">-</span>
                    <span id="rk-m-jobs-sub" class="rk-metric-sub">kuota terisi -</span>
                </span>
            </button>
        </section>

        <!-- ========================================================== -->
        <!-- TAB                                                        -->
        <!-- ========================================================== -->
        <div class="mt-7 border-b border-[#E2E7ED]">
            <div role="tablist" aria-label="Bagian papan rekrutmen" class="rk-noscroll flex gap-2 -mb-px overflow-x-auto">
                <button type="button" role="tab" id="rk-tab-pipeline" aria-selected="true" aria-controls="rk-panel-pipeline" class="detail-tab">
                    Pipeline Kandidat <span id="rk-tab-pipeline-count" class="rk-tab-count">0</span>
                </button>
                <button type="button" role="tab" id="rk-tab-jobs" aria-selected="false" aria-controls="rk-panel-jobs" tabindex="-1" class="detail-tab">
                    Lowongan Saya <span id="rk-tab-jobs-count" class="rk-tab-count">0</span>
                </button>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- PANEL 1: PIPELINE                                          -->
        <!-- ========================================================== -->
        <section id="rk-panel-pipeline" role="tabpanel" aria-labelledby="rk-tab-pipeline" class="pt-5">

            <!-- Bilah alat -->
            <div class="flex flex-col xl:flex-row xl:items-center gap-3">
                <div class="relative flex-1 xl:max-w-[320px]">
                    <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#627080] pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <label for="rk-search" class="sr-only">Cari kandidat</label>
                    <input id="rk-search" type="search" autocomplete="off" placeholder="Cari nama kandidat atau skill" class="w-full h-11 pl-10 pr-3 rounded-xl bg-white border border-[#E2E7ED] text-sm text-[#0B1B2B] focus:border-[#C8460B] focus:outline-none focus:ring-2 focus:ring-[#C8460B]/20">
                </div>

                <div class="flex flex-wrap items-center gap-2.5">
                    <div class="rk-seg rk-noscroll max-w-full" role="group" aria-label="Saring kategori skor">
                        <button type="button" data-cat="all" aria-pressed="true">Semua</button>
                        <button type="button" data-cat="excellent" aria-pressed="false"><span class="rk-dot bg-[#157F3B]"></span>Sangat cocok</button>
                        <button type="button" data-cat="good" aria-pressed="false"><span class="rk-dot bg-[#1B65A6]"></span>Cukup</button>
                        <button type="button" data-cat="under" aria-pressed="false"><span class="rk-dot bg-[#C8460B]"></span>Belum cocok</button>
                    </div>

                    <label for="rk-sort" class="sr-only">Urutkan kandidat</label>
                    <div class="rk-select rk-select-sm">
                        <svg class="rk-select-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M3 6h18M6 12h12M10 18h4"/></svg>
                        <select id="rk-sort">
                            <option value="score">Skor tertinggi</option>
                            <option value="newest">Lamaran terbaru</option>
                            <option value="waiting">Terlama di tahap ini</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Penanda saringan aktif -->
            <div id="rk-active-filter" class="hidden mt-3 flex flex-wrap items-center gap-2 text-sm text-[#3D4B5C]"></div>

            <!-- Tab tahap untuk layar kecil -->
            <div id="rk-stage-tabs" class="rk-noscroll lg:hidden mt-4 flex gap-2 overflow-x-auto pb-1" role="tablist" aria-label="Tahap pipeline"></div>

            <!-- Memuat -->
            <div id="rk-loading" class="mt-4 grid grid-cols-1 lg:grid-cols-4 gap-3" aria-hidden="true">
                @for ($i = 0; $i < 4; $i++)
                    <div class="rounded-2xl bg-white border border-[#E2E7ED] p-3 space-y-3 {{ $i > 0 ? 'hidden lg:block' : '' }}">
                        <div class="h-5 w-28 rounded-md animate-skeleton bg-[#E8EDF2]"></div>
                        <div class="h-28 rounded-xl animate-skeleton bg-[#F4F6F8]"></div>
                        <div class="h-28 rounded-xl animate-skeleton bg-[#F4F6F8]"></div>
                    </div>
                @endfor
            </div>

            <!-- Gagal memuat -->
            <div id="rk-error" class="hidden mt-4 rounded-2xl border border-dashed border-[#C5CFDA] bg-white p-10 text-center">
                <p class="font-bold text-[#0B1B2B]">Papan rekrutmen gagal dimuat</p>
                <p id="rk-error-text" class="mt-1 text-sm text-[#3D4B5C]">Periksa koneksi ke server, lalu coba lagi.</p>
                <button type="button" id="rk-retry" class="btn-press mt-4 h-10 px-4 rounded-xl bg-[#0C2238] text-white text-sm font-semibold">Coba lagi</button>
            </div>

            <!-- Papan -->
            <div id="rk-board" class="rk-board hidden mt-4" data-mobile-stage="screening"></div>

            <p class="hidden lg:block mt-3 text-xs text-[#627080]">Seret kartu ke kolom berikutnya untuk memajukan kandidat. Tahap hanya bisa maju satu langkah atau ditolak.</p>
        </section>

        <!-- ========================================================== -->
        <!-- PANEL 2: LOWONGAN SAYA                                     -->
        <!-- ========================================================== -->
        <section id="rk-panel-jobs" role="tabpanel" aria-labelledby="rk-tab-jobs" class="hidden pt-5">
            <div id="rk-jobs-list" class="grid gap-3"></div>
        </section>
    </div>

    <!-- ============================================================== -->
    <!-- PANEL DETAIL KANDIDAT                                          -->
    <!-- ============================================================== -->
    <div id="rk-drawer" class="rk-drawer" hidden>
        <div class="rk-drawer-backdrop" data-drawer-close></div>
        <aside class="rk-drawer-panel" role="dialog" aria-modal="true" aria-labelledby="rk-d-name">
            <header class="px-5 sm:px-6 pt-5 pb-0 border-b border-[#E2E7ED]">
                <div class="flex items-start gap-3.5">
                    <span id="rk-d-avatar" class="rk-avatar w-12 h-12 text-base" aria-hidden="true">?</span>
                    <div class="min-w-0 flex-1">
                        <h2 id="rk-d-name" class="text-lg font-extrabold font-heading text-[#0B1B2B] leading-snug truncate">Memuat...</h2>
                        <p id="rk-d-job" class="text-sm text-[#3D4B5C] truncate">-</p>
                        <p id="rk-d-stage" class="mt-1.5"></p>
                    </div>
                    <div id="rk-d-score" class="shrink-0"></div>
                    <button type="button" class="shrink-0 -mr-1.5 -mt-1 w-10 h-10 inline-flex items-center justify-center rounded-xl text-[#627080] hover:text-[#0B1B2B] hover:bg-[#F4F6F8]" data-drawer-close aria-label="Tutup detail kandidat">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
                <div role="tablist" aria-label="Bagian detail kandidat" class="rk-noscroll mt-4 flex gap-1 -mb-px overflow-x-auto">
                    <button type="button" role="tab" class="detail-tab" data-dtab="summary" aria-selected="true">Ringkasan</button>
                    <button type="button" role="tab" class="detail-tab" data-dtab="application" aria-selected="false" tabindex="-1">Lamaran</button>
                    <button type="button" role="tab" class="detail-tab" data-dtab="timeline" aria-selected="false" tabindex="-1">Linimasa</button>
                </div>
            </header>

            <div id="rk-d-body" class="flex-1 overflow-y-auto px-5 sm:px-6 py-5">
                <div class="space-y-3" aria-hidden="true">
                    <div class="h-20 rounded-xl animate-skeleton bg-[#F4F6F8]"></div>
                    <div class="h-40 rounded-xl animate-skeleton bg-[#F4F6F8]"></div>
                </div>
            </div>

            <footer id="rk-d-actions" class="border-t border-[#E2E7ED] px-5 sm:px-6 py-4 bg-white"></footer>
        </aside>
    </div>

    <!-- ============================================================== -->
    <!-- FORM PASANG / EDIT LOWONGAN                                    -->
    <!-- ============================================================== -->
    <x-modal id="modal-job-form" title="Pasang Lowongan Baru" maxWidth="max-w-2xl">
        <form id="rk-job-form" class="space-y-6" novalidate>
            <fieldset class="space-y-3.5">
                <legend class="rk-legend">Informasi dasar</legend>
                <div>
                    <label for="job-title" class="rk-label">Judul posisi <span class="text-[#C8460B]">*</span></label>
                    <input id="job-title" type="text" required maxlength="255" placeholder="contoh: Backend Engineer (Laravel)" class="rk-input">
                    <p class="rk-error" data-error-for="title"></p>
                </div>
                <div class="grid sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="job-company" class="rk-label">Perusahaan <span class="text-[#C8460B]">*</span></label>
                        <input id="job-company" type="text" required maxlength="255" placeholder="contoh: PT Pelabuhan Digital" class="rk-input">
                        <p class="rk-error" data-error-for="company"></p>
                    </div>
                    <div>
                        <label for="job-location" class="rk-label">Kota penempatan <span class="text-[#C8460B]">*</span></label>
                        <input id="job-location" type="text" required maxlength="100" placeholder="contoh: Tegal" class="rk-input">
                        <p class="rk-error" data-error-for="location"></p>
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-3.5">
                    <div>
                        <label for="job-type" class="rk-label">Tipe pekerjaan</label>
                        <select id="job-type" class="rk-input">
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Kontrak</option>
                            <option value="internship">Magang</option>
                            <option value="remote">Remote</option>
                        </select>
                    </div>
                    <div>
                        <label for="job-expires" class="rk-label">Batas lamaran <span class="font-normal text-[#627080]">(opsional)</span></label>
                        <input id="job-expires" type="date" class="rk-input">
                        <p class="rk-error" data-error-for="expires_at"></p>
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-3.5">
                <legend class="rk-legend">Gaji dan kuota</legend>
                <div class="grid sm:grid-cols-3 gap-3.5">
                    <div>
                        <label for="job-salary-min" class="rk-label">Gaji minimum (Rp)</label>
                        <input id="job-salary-min" type="number" min="0" step="100000" inputmode="numeric" placeholder="8000000" class="rk-input num-tabular">
                        <p class="rk-error" data-error-for="salary_min"></p>
                    </div>
                    <div>
                        <label for="job-salary-max" class="rk-label">Gaji maksimum (Rp)</label>
                        <input id="job-salary-max" type="number" min="0" step="100000" inputmode="numeric" placeholder="15000000" class="rk-input num-tabular">
                        <p class="rk-error" data-error-for="salary_max"></p>
                    </div>
                    <div>
                        <label for="job-quota" class="rk-label">Kuota</label>
                        <input id="job-quota" type="number" min="1" value="1" inputmode="numeric" class="rk-input num-tabular">
                        <p class="rk-error" data-error-for="quota"></p>
                    </div>
                </div>
            </fieldset>

            <fieldset class="space-y-3">
                <legend class="rk-legend">Syarat skill <span class="text-[#C8460B]">*</span></legend>
                <p class="text-sm text-[#3D4B5C] -mt-1">Bobot menentukan skor kecocokan: Wajib 3, Penting 2, Bonus 1.</p>
                <div id="rk-req-rows" class="space-y-2"></div>
                <datalist id="rk-skill-suggestions"></datalist>
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <button type="button" id="rk-add-req" class="inline-flex items-center gap-1.5 h-9 px-3 rounded-lg text-sm font-semibold text-[#C8460B] hover:bg-[#FFF3EA]">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
                        Tambah skill
                    </button>
                    <p id="rk-weight-preview" class="text-xs text-[#627080] num-tabular"></p>
                </div>
                <div id="rk-weight-bar" class="rk-weight-bar" aria-hidden="true"></div>
                <p class="rk-error" data-error-for="requirements"></p>
            </fieldset>

            <fieldset class="space-y-2">
                <legend class="rk-legend">Deskripsi <span class="text-[#C8460B]">*</span></legend>
                <label for="job-description" class="sr-only">Deskripsi lowongan</label>
                <textarea id="job-description" rows="5" required placeholder="Tanggung jawab, kualifikasi, dan benefit posisi ini." class="rk-input min-h-[120px] py-2.5"></textarea>
                <p class="rk-error" data-error-for="description"></p>
            </fieldset>

            <div class="pt-4 border-t border-[#E2E7ED] flex flex-col-reverse sm:flex-row sm:justify-end gap-2.5">
                <button type="button" onclick="window.JHModal && window.JHModal.close('modal-job-form')" class="h-11 px-5 rounded-xl text-sm font-semibold text-[#3D4B5C] bg-[#F4F6F8] hover:bg-[#E2E7ED]">Batal</button>
                <button type="submit" id="rk-job-submit" class="btn-press h-11 px-6 rounded-xl text-sm font-semibold text-white bg-[#C8460B] hover:bg-[#A93A08]">Terbitkan lowongan</button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        @vite('resources/js/pages/recruiter.js')
    @endpush
</x-layouts.app>
