<x-layouts.app title="Katalog Lowongan - JobHarbour">

    <!-- ============================================================== -->
    <!-- HEADER: judul + ringkasan, banner mercusuar di kanan           -->
    <!-- ============================================================== -->
    <section class="relative overflow-hidden bg-[#F4F6F8] border-b border-[#E2E7ED]">
        <picture class="absolute inset-y-0 right-0 w-full lg:w-[62%] pointer-events-none select-none" aria-hidden="true">
            <source srcset="/images/catalog-banner.webp" type="image/webp">
            <img src="/images/catalog-banner.jpg" alt="" width="1800" height="600" class="catalog-banner w-full h-full object-cover object-right">
        </picture>
        <div class="catalog-banner-fade absolute inset-0 pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-[1240px] mx-auto px-5 sm:px-8 pt-7 sm:pt-12 pb-6 sm:pb-10 grid lg:grid-cols-[minmax(0,1fr)_auto] gap-5 sm:gap-8 items-end">
            <div class="hero-in" style="--d: 0ms">
                <p class="text-xs sm:text-sm font-extrabold uppercase tracking-[0.12em] text-[#C8460B]">Job Discovery</p>
                <h1 class="mt-2 text-[1.85rem] sm:text-[2.6rem] leading-[1.08] font-extrabold text-[#0B1B2B]">
                    Temukan lowongan<br>yang <span class="text-[#F26A1B]">paling sesuai</span> denganmu.
                </h1>
                <p id="catalog-subtitle" class="mt-2 sm:mt-3 text-sm sm:text-[15px] text-[#3D4B5C] max-w-[34rem]">Lowongan diurutkan dan dinilai berdasarkan skill di profilmu.</p>
            </div>

            <!-- Ringkasan: isi diatur oleh catalog.js sesuai status login -->
            <div id="catalog-summary" class="hero-in bg-white/95 dark:bg-[#121A28]/95 backdrop-blur rounded-2xl border border-[#E2E7ED] dark:border-[#243045] shadow-[0_18px_40px_-24px_rgba(12,34,56,0.35)] px-4 py-3 sm:px-5 sm:py-4 flex flex-wrap items-center gap-x-6 gap-y-3 sm:gap-y-4" style="--d: 120ms">
                <div class="h-12 w-[420px] max-w-full rounded-xl animate-skeleton bg-[#F4F6F8]"></div>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- ISI: filter (kiri) + daftar lowongan (kanan)                   -->
    <!-- ============================================================== -->
    <div class="max-w-[1240px] mx-auto px-5 sm:px-8 py-8 grid lg:grid-cols-[272px_minmax(0,1fr)] gap-6 items-start">

        <!-- Sidebar filter. Di ponsel dan tablet tampil sebagai panel bawah yang dibuka lewat tombol Filter,
             supaya daftar lowongan langsung terlihat tanpa menggulir melewati semua pilihan filter. -->
        <div id="filter-backdrop" class="hidden lg:hidden fixed inset-0 z-40 bg-[#0C2238]/45" data-filter-close></div>
        <aside id="filter-panel" class="hidden lg:block lg:sticky lg:top-[88px]" aria-label="Filter lowongan">
            <div class="bg-white rounded-2xl border border-[#E2E7ED] p-5">
                <div class="flex items-center justify-between gap-2 pb-4 border-b border-[#E2E7ED]">
                    <h2 class="flex items-center gap-2.5 text-lg font-extrabold text-[#0B1B2B]">
                        <svg class="w-5 h-5 text-[#3D4B5C]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                        Filter Lowongan
                    </h2>
                    <span id="filter-active-count" class="hidden text-xs font-bold px-2 py-0.5 rounded-full bg-[#FFF3EA] text-[#8A2E05]"></span>
                    <button type="button" class="lg:hidden ml-auto w-9 h-9 rounded-full inline-flex items-center justify-center text-[#3D4B5C] hover:bg-[#F4F6F8]" data-filter-close aria-label="Tutup filter">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                <div id="catalog-filters" class="divide-y divide-[#E2E7ED]">
                    <!-- Tingkat kecocokan: hanya untuk pelamar yang login -->
                    <details id="filter-match-group" class="filter-group hidden" open>
                        <summary class="filter-summary">Tingkat Kecocokan</summary>
                        <div class="filter-body">
                            <label class="filter-option"><input type="checkbox" name="match" value="excellent"> <span>&ge; 80% (Sangat Cocok)</span></label>
                            <label class="filter-option"><input type="checkbox" name="match" value="good"> <span>50% - 79% (Cukup Cocok)</span></label>
                            <label class="filter-option"><input type="checkbox" name="match" value="under"> <span>&lt; 50% (Belum Cocok)</span></label>
                        </div>
                    </details>

                    <details class="filter-group" open>
                        <summary class="filter-summary">Tipe Pekerjaan</summary>
                        <div id="filter-types" class="filter-body"></div>
                    </details>

                    <details class="filter-group" open>
                        <summary class="filter-summary">Rentang Gaji</summary>
                        <div class="filter-body space-y-2">
                            <label class="filter-option"><input type="radio" name="salary" value="0" checked> <span>Semua gaji</span></label>
                            <label class="filter-option"><input type="radio" name="salary" value="5000000"> <span>Mulai Rp5.000.000 / bln</span></label>
                            <label class="filter-option"><input type="radio" name="salary" value="10000000"> <span>Mulai Rp10.000.000 / bln</span></label>
                            <label class="filter-option"><input type="radio" name="salary" value="15000000"> <span>Mulai Rp15.000.000 / bln</span></label>
                            <label class="filter-option"><input type="radio" name="salary" value="20000000"> <span>Mulai Rp20.000.000 / bln</span></label>

                            <div class="pt-2 mt-2 border-t border-[#E2E7ED] space-y-1.5">
                                <span class="text-[11px] font-bold text-[#627080] uppercase tracking-wider block">Batas Kustom (Rp)</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <input type="number" id="filter-salary-min" placeholder="Min" min="0" step="500000" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-[#C5CFDA] bg-[#F4F6F8] focus:bg-white focus:border-[#0C2238] outline-none">
                                    <input type="number" id="filter-salary-max" placeholder="Maks" min="0" step="500000" class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-[#C5CFDA] bg-[#F4F6F8] focus:bg-white focus:border-[#0C2238] outline-none">
                                </div>
                            </div>
                        </div>
                    </details>

                    <details class="filter-group" open>
                        <summary class="filter-summary">Lokasi Kota & Wilayah</summary>
                        <div class="filter-body space-y-2 pt-1">
                            <div class="relative">
                                <input type="text" id="filter-city-search" placeholder="Cari kota..." class="w-full text-xs pl-7 pr-2.5 py-1.5 rounded-lg border border-[#C5CFDA] bg-[#F4F6F8] focus:bg-white focus:border-[#0C2238] outline-none">
                                <svg class="w-3.5 h-3.5 text-[#8C98A8] absolute left-2 top-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            </div>
                            <div id="filter-cities" class="space-y-1.5 max-h-52 overflow-y-auto pr-1"></div>
                        </div>
                    </details>
                </div>

                <button type="button" id="filter-reset" class="btn-press mt-5 w-full inline-flex items-center justify-center gap-2 h-11 rounded-xl border border-[#C8460B] text-[#C8460B] font-semibold hover:bg-[#FFF3EA] transition-colors cursor-pointer">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
                    Reset Filter
                </button>
                <button type="button" class="lg:hidden btn-press mt-3 w-full inline-flex items-center justify-center h-11 rounded-xl bg-[#0C2238] text-white font-semibold" data-filter-close>
                    Tampilkan <span id="filter-result-count" class="mx-1">hasil</span>
                </button>
            </div>
        </aside>

        <!-- Daftar lowongan -->
        <main class="min-w-0">
            <div class="flex flex-col sm:flex-row gap-3">
                <label class="flex-1 flex items-center gap-3 h-12 px-4 rounded-xl bg-white border border-[#C5CFDA] focus-within:border-[#0C2238] transition-colors">
                    <svg class="w-5 h-5 text-[#627080] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    <span class="sr-only">Cari lowongan</span>
                    <input id="catalog-search" type="search" placeholder="Cari posisi, perusahaan, atau keahlian..." class="w-full bg-transparent border-0 outline-none text-[15px] text-[#0B1B2B] placeholder:text-[#8C98A8]">
                </label>
                <div class="flex gap-3">
                <button type="button" id="filter-toggle" class="lg:hidden btn-press flex-1 sm:flex-none inline-flex items-center justify-center gap-2 h-12 px-4 rounded-xl bg-white border border-[#C5CFDA] text-sm font-bold text-[#0B1B2B]" aria-controls="filter-panel" aria-expanded="false">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4"/></svg>
                    Filter <span id="filter-toggle-count" class="hidden min-w-5 h-5 px-1.5 rounded-full bg-[#0C2238] text-white text-[11px] leading-5"></span>
                </button>
                <label class="flex-1 min-w-0 sm:flex-none flex items-center gap-2 h-12 pl-4 pr-2 rounded-xl bg-white border border-[#C5CFDA] text-sm text-[#3D4B5C]">
                    <span class="shrink-0 hidden min-[380px]:inline">Urutkan:</span>
                    <select id="catalog-sort" class="min-w-0 flex-1 sm:flex-none bg-transparent border-0 outline-none font-bold text-[#0B1B2B] pr-1 cursor-pointer text-ellipsis">
                        <option value="match" data-applicant-only>Kecocokan tertinggi</option>
                        <option value="latest">Terbaru</option>
                        <option value="salary_high">Gaji tertinggi</option>
                        <option value="salary_low">Gaji terendah</option>
                    </select>
                </label>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-2 min-h-7">
                <p id="catalog-count" class="font-bold text-[#0B1B2B]" aria-live="polite">Memuat lowongan...</p>
                <div id="catalog-active-chips" class="flex flex-wrap gap-2"></div>
            </div>

            <div id="catalog-list" class="mt-4 flex flex-col gap-3">
                @for ($i = 0; $i < 4; $i++)
                    <div class="h-[132px] rounded-2xl bg-white border border-[#E2E7ED] animate-skeleton"></div>
                @endfor
            </div>

            <div id="catalog-empty" class="hidden mt-4 rounded-2xl border border-dashed border-[#C5CFDA] bg-white p-10 text-center">
                <p class="font-bold text-[#0B1B2B]">Tidak ada lowongan yang cocok dengan filter ini</p>
                <p class="mt-1 text-sm text-[#3D4B5C]">Coba kurangi filter atau ubah kata kunci pencarian.</p>
                <button type="button" data-reset-filters class="btn-press mt-4 px-4 py-2 rounded-lg bg-[#0C2238] text-white text-sm font-semibold cursor-pointer">Reset filter</button>
            </div>

            <div id="catalog-error" class="hidden mt-4 rounded-2xl border border-[#F3C5BF] bg-[#FDEEEC] p-8 text-center">
                <p class="font-bold text-[#B42318]">Lowongan gagal dimuat</p>
                <p class="mt-1 text-sm text-[#3D4B5C]">Periksa koneksi ke API, lalu coba lagi.</p>
                <button type="button" onclick="location.reload()" class="btn-press mt-4 px-4 py-2 rounded-lg bg-[#0C2238] text-white text-sm font-semibold cursor-pointer">Coba lagi</button>
            </div>
        </main>
    </div>

    @push('scripts')
        @vite('resources/js/pages/catalog.js')
    @endpush
</x-layouts.app>
