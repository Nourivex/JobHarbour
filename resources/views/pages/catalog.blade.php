<x-layouts.app title="Katalog Lowongan Kerja - JobHarbour">

    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 py-6 sm:py-8">
        
        <!-- ========================================================== -->
        <!-- HEADER & BILAH FILTER                                      -->
        <!-- ========================================================== -->
        <div class="bg-white rounded-2xl p-5 sm:p-6 border border-[#E2E7ED] shadow-xs mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5 pb-5 border-b border-[#E2E7ED]">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0B1B2B] font-heading tracking-tight">
                        Katalog Lowongan Kerja
                    </h1>
                    <p class="text-sm text-[#627080] mt-0.5" id="catalog-results-count">
                        Memuat daftar lowongan pekerjaan aktif...
                    </p>
                </div>

                <!-- Opsi Urutan & Reset Filter -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="catalog-sort" class="text-xs font-semibold text-[#627080]">Urutan:</label>
                        <select
                            id="catalog-sort"
                            class="min-h-[40px] px-3 py-1.5 bg-[#F4F6F8] text-[#0B1B2B] text-xs font-semibold rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] cursor-pointer"
                        >
                            <option value="latest">Terbaru</option>
                            <option value="match">Kecocokan Tertinggi</option>
                            <option value="salary_high">Gaji Tertinggi</option>
                            <option value="salary_low">Gaji Terendah</option>
                            <option value="oldest">Terlama</option>
                        </select>
                    </div>

                    <button
                        type="button"
                        id="catalog-reset-filters"
                        class="min-h-[40px] px-3 py-1.5 text-xs font-semibold text-[#627080] hover:text-[#0B1B2B] hover:bg-[#F4F6F8] rounded-lg transition-colors cursor-pointer border border-[#E2E7ED]"
                    >
                        Reset Filter
                    </button>
                </div>
            </div>

            <!-- Form Pencarian & Filter Input -->
            <form id="catalog-filter-form" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-center">
                <!-- Keyword Input -->
                <div class="lg:col-span-4 relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-[#627080]">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input
                        type="text"
                        id="catalog-search"
                        placeholder="Cari judul posisi atau keahlian..."
                        class="w-full min-h-[44px] pl-9 pr-3.5 py-2.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                    />
                </div>

                <!-- Location Input -->
                <div class="lg:col-span-3 relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2 text-[#627080]">
                        <x-icon name="map-pin" class="w-4 h-4" />
                    </div>
                    <input
                        type="text"
                        id="catalog-location"
                        placeholder="Kota / Penempatan..."
                        class="w-full min-h-[44px] pl-9 pr-3.5 py-2.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                    />
                </div>

                <!-- Employment Type -->
                <div class="lg:col-span-3">
                    <select
                        id="catalog-type"
                        class="w-full min-h-[44px] px-3.5 py-2.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors cursor-pointer"
                    >
                        <option value="">Semua Jenis Kerja</option>
                        <option value="full-time">Full-time (Purna Waktu)</option>
                        <option value="part-time">Part-time (Paruh Waktu)</option>
                        <option value="contract">Kontrak</option>
                        <option value="internship">Magang</option>
                        <option value="remote">Remote (Jarak Jauh)</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="lg:col-span-2">
                    <button
                        type="submit"
                        class="w-full min-h-[44px] px-4 py-2.5 bg-[#0C2238] hover:bg-[#133150] text-white font-bold text-sm rounded-lg transition-all btn-press cursor-pointer flex items-center justify-center gap-2 shadow-xs"
                    >
                        <x-icon name="filter" class="w-4 h-4" />
                        <span>Terapkan</span>
                    </button>
                </div>

                <!-- Checkbox 80%+ Filter (Pelamar Only) -->
                <div id="high-match-wrap" class="sm:col-span-2 lg:col-span-12 pt-2 flex items-center gap-2">
                    <label class="inline-flex items-center gap-2 text-xs font-semibold text-[#0B1B2B] cursor-pointer">
                        <input
                            type="checkbox"
                            id="catalog-high-match"
                            class="w-4 h-4 rounded text-[#C8460B] focus:ring-[#C8460B] border-[#C5CFDA] cursor-pointer"
                        />
                        <span>Hanya tampilkan lowongan yang sangat cocok (Skor 80%+ auto-screening)</span>
                    </label>
                </div>
            </form>
        </div>

        <!-- ========================================================== -->
        <!-- DUA KOLOM: DAFTAR KIRI & DETAIL KANAN                      -->
        <!-- ========================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- KOLOM KIRI: DAFTAR KARTU LOWONGAN (5 Kolom di LG) -->
            <div class="lg:col-span-5 space-y-3">
                
                <!-- Loading State Skeletons -->
                <div id="catalog-jobs-loading" class="space-y-3">
                    <x-skeleton :lines="2" />
                    <x-skeleton :lines="2" />
                    <x-skeleton :lines="2" />
                </div>

                <!-- Daftar Lowongan -->
                <div id="catalog-jobs-list" class="space-y-3">
                    <!-- Diisi lewat JavaScript -->
                </div>

                <!-- Empty State -->
                <div id="catalog-jobs-empty" class="hidden">
                    <x-empty-state
                        title="Tidak ada lowongan ditemukan"
                        description="Coba ubah kata kunci pencarian, filter kota, atau reset seluruh filter pencarian Anda."
                    />
                </div>
            </div>

            <!-- KOLOM KANAN: DETAIL LOWONGAN & PANEL KECOCOKAN (7 Kolom di LG - Sticky) -->
            <div class="hidden lg:block lg:col-span-7 sticky top-22">
                <div class="bg-white rounded-2xl border border-[#E2E7ED] p-6 sm:p-8 shadow-xs max-h-[calc(100vh-110px)] overflow-y-auto">
                    
                    <!-- Placeholder State (Belum ada lowongan dipilih) -->
                    <div id="catalog-detail-placeholder" class="py-16 text-center text-[#627080] space-y-3">
                        <div class="w-14 h-14 rounded-2xl bg-[#F4F6F8] text-[#627080] flex items-center justify-center mx-auto">
                            <x-icon name="briefcase" class="w-7 h-7" />
                        </div>
                        <h3 class="text-base font-bold text-[#0B1B2B]">Pilih Lowongan untuk Melihat Rincian</h3>
                        <p class="text-sm max-w-sm mx-auto">Klik salah satu lowongan di kolom sebelah kiri untuk meninjau kualifikasi, rincian gaji, dan hasil analisis pemadanan keahlian.</p>
                    </div>

                    <!-- Detail Konten (Tampil saat lowongan dipilih) -->
                    <div id="catalog-detail-content" class="hidden space-y-6 transition-opacity duration-150">
                        
                        <!-- Header Detail Lowongan -->
                        <div class="pb-6 border-b border-[#E2E7ED]">
                            <div class="flex items-start justify-between gap-4 mb-2">
                                <div>
                                    <h2 id="detail-title" class="text-2xl font-extrabold text-[#0B1B2B] leading-tight font-heading">
                                        -
                                    </h2>
                                    <p id="detail-company" class="text-base font-bold text-[#3D4B5C] mt-1">
                                        -
                                    </p>
                                </div>

                                <button
                                    type="button"
                                    id="detail-btn-bookmark"
                                    class="min-h-[44px] px-3.5 py-2 rounded-lg text-sm font-semibold bg-white text-[#3D4B5C] border border-[#C5CFDA] hover:border-[#0C2238] transition-colors flex items-center gap-1.5 cursor-pointer shrink-0"
                                >
                                    <x-icon name="anchor" class="w-4 h-4" />
                                    <span>Simpan</span>
                                </button>
                            </div>

                            <!-- Meta Chips -->
                            <div class="flex flex-wrap items-center gap-y-2 gap-x-4 text-xs text-[#627080] mt-4">
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="map-pin" class="w-4 h-4 text-[#C8460B]" />
                                    <span id="detail-location">-</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 font-bold text-[#0B1B2B]">
                                    <x-icon name="dollar-sign" class="w-4 h-4 text-[#157F3B]" />
                                    <span id="detail-salary">-</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5">
                                    <x-icon name="briefcase" class="w-4 h-4 text-[#1B65A6]" />
                                    <span id="detail-type">-</span>
                                </span>
                                <span class="inline-flex items-center gap-1.5 text-[#3D4B5C]">
                                    <x-icon name="users" class="w-4 h-4 text-[#627080]" />
                                    <span id="detail-quota">-</span>
                                </span>
                            </div>

                            <!-- Tombol Lamar Utama -->
                            <div class="mt-6 flex items-center gap-3">
                                <button
                                    type="button"
                                    id="detail-btn-apply"
                                    class="flex-1 min-h-[46px] px-6 py-3 rounded-xl text-sm font-bold bg-[#C8460B] hover:bg-[#993406] text-white transition-all btn-press shadow-xs flex items-center justify-center gap-2 cursor-pointer"
                                >
                                    <span>Lamar Sekarang</span>
                                    <x-icon name="arrow-right" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- ================================================== -->
                        <!-- PANEL KECOCOKAN KEAHLIAN (MATCHMAKING TELEMETRY)   -->
                        <!-- ================================================== -->
                        <div id="detail-match-panel" class="p-5 sm:p-6 rounded-2xl bg-[#F4F6F8] border border-[#E2E7ED] space-y-5">
                            
                            <!-- Header Skor -->
                            <div class="flex items-center justify-between pb-3 border-b border-[#E2E7ED]">
                                <div>
                                    <span class="text-xs font-bold uppercase tracking-wider text-[#627080]">Kecocokan Profil Pelamar</span>
                                    <div class="flex items-center gap-2.5 mt-1">
                                        <span id="telemetry-score-num" class="text-3xl font-extrabold text-[#0B1B2B] font-heading num-tabular">
                                            0%
                                        </span>
                                        <span id="telemetry-category-label" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-[#EBF3FB] text-[#1B65A6]">
                                            Memuat...
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[11px] text-[#627080] block">Ambang Kelulusan:</span>
                                    <span class="text-xs font-bold text-[#157F3B]">80%+ Otomatis Lolos</span>
                                </div>
                            </div>

                            <p id="telemetry-explanation-text" class="text-xs text-[#3D4B5C] leading-relaxed">
                                -
                            </p>

                            <!-- Bar Meter dengan Penanda 50% dan 80% -->
                            <div class="space-y-1">
                                <div class="relative w-full h-3 bg-[#E2E7ED] rounded-full overflow-hidden">
                                    <div id="telemetry-score-bar" class="h-full rounded-full transition-all duration-700 ease-out bg-[#1B65A6]" style="width: 0%;"></div>
                                    <!-- 50% marker -->
                                    <div class="absolute top-0 bottom-0 left-1/2 w-0.5 bg-white shadow-xs" title="Ambang 50% (Cocok)"></div>
                                    <!-- 80% marker -->
                                    <div class="absolute top-0 bottom-0 left-[80%] w-0.5 bg-white shadow-xs" title="Ambang 80% (Sangat Cocok)"></div>
                                </div>
                                <div class="flex items-center justify-between text-[10px] text-[#627080] font-semibold pt-0.5">
                                    <span>0%</span>
                                    <span class="pl-4">50% (Ditinjau)</span>
                                    <span>80% (Auto-Screening)</span>
                                    <span>100%</span>
                                </div>
                            </div>

                            <!-- Pengelompokan Syarat Keahlian (Wajib, Penting, Nilai Tambah) -->
                            <div id="telemetry-tiers-container" class="space-y-4 pt-2">
                                <!-- Diisi via JavaScript -->
                            </div>

                            <!-- Saran Konkret Peningkatan Skor -->
                            <div id="telemetry-suggestion-wrap" class="p-3.5 rounded-xl bg-[#FFF3EA] border border-[#C8460B]/20 text-xs text-[#8A2E05] flex items-start gap-2.5 hidden">
                                <x-icon name="sparkles" class="w-4 h-4 text-[#C8460B] shrink-0 mt-0.5" />
                                <div id="telemetry-suggestion-text" class="leading-relaxed">
                                    -
                                </div>
                            </div>

                            <!-- Accordion: Bagaimana Skor Dihitung -->
                            <details class="text-xs text-[#3D4B5C] border-t border-[#E2E7ED] pt-3 group">
                                <summary class="font-bold text-[#0B1B2B] hover:text-[#C8460B] transition-colors cursor-pointer flex items-center justify-between">
                                    <span>Bagaimana skor ini dihitung secara matematis?</span>
                                    <x-icon name="chevron-down" class="w-4 h-4 text-[#627080] group-open:rotate-180 transition-transform" />
                                </summary>
                                <div class="pt-2 text-[#627080] leading-relaxed space-y-1.5">
                                    <p>
                                        Mesin pemadanan JobHarbour menggunakan formula <strong>Weighted Jaccard Similarity</strong>:
                                    </p>
                                    <div class="p-2.5 rounded bg-white font-mono text-[11px] text-[#0B1B2B] border border-[#E2E7ED]">
                                        Skor = (Σ Bobot Skill Cocok / Σ Seluruh Bobot Syarat) × 100%
                                    </div>
                                    <p>
                                        Bobot: <strong>Wajib (Required) = 3</strong>, <strong>Penting (Important) = 2</strong>, dan <strong>Nilai Tambah (Nice-to-have) = 1</strong>. Algoritma tidak peka terhadap huruf besar/kecil.
                                    </p>
                                </div>
                            </details>

                        </div>

                        <!-- AJAKAN LOGIN UNTUK TAMU (GUEST PROMPT) -->
                        <div id="detail-guest-prompt" class="p-5 rounded-2xl bg-[#EBF3FB] border border-[#1B65A6]/20 text-left space-y-3 hidden">
                            <div class="flex items-center gap-2 text-sm font-bold text-[#1B65A6]">
                                <x-icon name="info" class="w-4 h-4" />
                                <span>Ingin Tahu Skor Kecocokan Anda?</span>
                            </div>
                            <p class="text-xs text-[#3D4B5C] leading-relaxed">
                                Masuk ke akun pelamar Anda untuk mengaktifkan perhitungan otomatis kecocokan profil Anda terhadap lowongan ini secara instan.
                            </p>
                            <a
                                href="/login"
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold bg-[#1B65A6] hover:bg-[#134e82] text-white transition-colors"
                            >
                                <span>Masuk Sekarang</span>
                                <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                            </a>
                        </div>

                        <!-- Deskripsi Lengkap Lowongan -->
                        <div class="space-y-3 pt-2">
                            <h3 class="text-base font-bold text-[#0B1B2B] font-heading">
                                Deskripsi & Tanggung Jawab Posisi
                            </h3>
                            <div id="detail-description" class="text-sm text-[#3D4B5C] leading-relaxed space-y-2">
                                -
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- ========================================================== -->
    <!-- DRAWER DETAIL KHUSUS MOBILE (< 1024px)                     -->
    <!-- ========================================================== -->
    <div
        id="catalog-mobile-drawer"
        class="lg:hidden fixed inset-0 z-50 bg-[#0C2238]/60 backdrop-blur-xs hidden overflow-y-auto"
    >
        <div class="min-h-full flex items-end sm:items-center justify-center p-0 sm:p-4">
            <div class="w-full max-w-2xl bg-white rounded-t-3xl sm:rounded-2xl p-6 shadow-2xl max-h-[90vh] overflow-y-auto space-y-6">
                <!-- Close Button Bar -->
                <div class="flex items-center justify-between pb-3 border-b border-[#E2E7ED]">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#627080]">Detail Lowongan</span>
                    <button
                        type="button"
                        id="catalog-close-mobile-drawer"
                        class="p-1.5 rounded-lg text-[#627080] hover:text-[#0B1B2B] hover:bg-[#F4F6F8] cursor-pointer"
                        aria-label="Tutup detail lowongan"
                    >
                        <x-icon name="x" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Duplicate Detail Container for Mobile Screen -->
                <div id="catalog-mobile-detail-inner" class="space-y-6">
                    <!-- Dinamis diduplikasi dari detail-content jika dibuka -->
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/catalog.js')
    @endpush
</x-layouts.app>
