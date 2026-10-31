<x-layouts.app title="Lamaran Saya - JobHarbour">

    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 py-8 sm:py-10 space-y-8">
        
        <!-- ========================================================== -->
        <!-- HEADER DASBOR PELAMAR                                      -->
        <!-- ========================================================== -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-[#E2E7ED]">
            <div>
                <span class="text-xs font-bold uppercase tracking-wider text-[#C8460B]">Dasbor Pelamar</span>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0B1B2B] font-heading mt-1">
                    Lamaran Saya (My Dock)
                </h1>
                <p class="text-sm text-[#627080] mt-0.5">
                    Pantau tahapan seleksi, linimasa proses, dan jadwal wawancara untuk seluruh lamaran Anda.
                </p>
            </div>

            <a
                href="/catalog"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-bold bg-[#C8460B] hover:bg-[#993406] text-white transition-all btn-press shadow-xs self-start md:self-auto"
            >
                <x-icon name="plus" class="w-4 h-4" />
                <span>Cari Lowongan Baru</span>
            </a>
        </div>

        <!-- ========================================================== -->
        <!-- KARTU AGENDA BERIKUTNYA (INTERVIEW SCHEDULED)              -->
        <!-- ========================================================== -->
        <div id="dock-next-agenda-card" class="hidden bg-[#0C2238] rounded-2xl p-6 sm:p-7 text-white shadow-lg border border-[#133150] relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 relative z-10">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#F26A1B] text-[#0B1B2B]">
                        <x-icon name="video" class="w-3.5 h-3.5" />
                        <span>Agenda Wawancara Mendatang</span>
                    </div>
                    <h3 id="agenda-job-title" class="text-lg sm:text-xl font-bold font-heading text-white">
                        -
                    </h3>
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-[#8CC8F2]">
                        <span class="flex items-center gap-1.5">
                            <x-icon name="calendar" class="w-4 h-4" />
                            <span id="agenda-datetime" class="font-bold text-white">-</span>
                        </span>
                        <span id="agenda-interviewer" class="text-[#C5CFDA]">-</span>
                    </div>
                </div>

                <div class="shrink-0">
                    <a
                        id="agenda-meeting-link"
                        href="#"
                        target="_blank"
                        class="px-5 py-2.5 bg-[#F26A1B] hover:bg-[#ff7b2e] text-[#0B1B2B] font-bold text-sm rounded-xl transition-all btn-press inline-flex items-center gap-2 shadow-xs"
                    >
                        <span>Buka Ruang Wawancara</span>
                        <x-icon name="external-link" class="w-4 h-4" />
                    </a>
                </div>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- RINGKASAN ANGKA STATISTIK                                  -->
        <!-- ========================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="bg-white rounded-2xl p-5 border border-[#E2E7ED] shadow-xs text-left">
                <span class="text-xs font-semibold text-[#627080] block">Total Berkas Lamaran</span>
                <span id="stat-total-apps" class="text-3xl font-extrabold text-[#0B1B2B] font-heading num-tabular mt-1 block">
                    0
                </span>
                <span class="text-[11px] text-[#627080] mt-1 block">Tercatat di sistem</span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E7ED] shadow-xs text-left">
                <span class="text-xs font-semibold text-[#627080] block">Sedang Diproses</span>
                <span id="stat-in-progress" class="text-3xl font-extrabold text-[#C8460B] font-heading num-tabular mt-1 block">
                    0
                </span>
                <span class="text-[11px] text-[#C8460B] mt-1 block">Screening / Tes / Interview</span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E7ED] shadow-xs text-left">
                <span class="text-xs font-semibold text-[#627080] block">Tahap Penawaran (Offering)</span>
                <span id="stat-offering" class="text-3xl font-extrabold text-[#157F3B] font-heading num-tabular mt-1 block">
                    0
                </span>
                <span class="text-[11px] text-[#157F3B] mt-1 block">Lolos tahap akhir</span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E7ED] shadow-xs text-left">
                <span class="text-xs font-semibold text-[#627080] block">Selesai / Dibatalkan</span>
                <span id="stat-completed" class="text-3xl font-extrabold text-[#627080] font-heading num-tabular mt-1 block">
                    0
                </span>
                <span class="text-[11px] text-[#627080] mt-1 block">Riwayat arsip</span>
            </div>

        </div>

        <!-- ========================================================== -->
        <!-- TAB FILTER & DAFTAR LAMARAN                                -->
        <!-- ========================================================== -->
        <div class="space-y-5">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-6 border-b border-[#E2E7ED]">
                <button
                    type="button"
                    id="dock-tab-all"
                    class="pb-3 text-sm font-bold border-b-2 border-[#C8460B] text-[#C8460B] transition-colors cursor-pointer"
                >
                    Semua Lamaran
                </button>
                <button
                    type="button"
                    id="dock-tab-process"
                    class="pb-3 text-sm font-bold border-b-2 border-transparent text-[#627080] hover:text-[#0B1B2B] transition-colors cursor-pointer"
                >
                    Sedang Diproses
                </button>
                <button
                    type="button"
                    id="dock-tab-done"
                    class="pb-3 text-sm font-bold border-b-2 border-transparent text-[#627080] hover:text-[#0B1B2B] transition-colors cursor-pointer"
                >
                    Selesai / Riwayat
                </button>
            </div>

            <!-- Loading State Skeletons -->
            <div id="dock-loading" class="space-y-4">
                <x-skeleton :lines="2" />
                <x-skeleton :lines="2" />
            </div>

            <!-- Applications List Container -->
            <div id="dock-applications-list" class="space-y-4">
                <!-- Diisi via JS -->
            </div>

            <!-- Empty State -->
            <div id="dock-empty" class="hidden">
                <x-empty-state
                    title="Belum ada riwayat lamaran"
                    description="Anda belum mengajukan lamaran pekerjaan untuk kategori ini. Buka katalog lowongan untuk menemukan posisi yang sesuai dengan keahlian Anda."
                >
                    <x-button href="/catalog" variant="primary" size="md">
                        Jelajahi Lowongan
                    </x-button>
                </x-empty-state>
            </div>
        </div>

    </div>

    <!-- ========================================================== -->
    <!-- DRAWER DETAIL LINIMASA PIPELINE (SLIDE DRAWER)             -->
    <!-- ========================================================== -->
    <div
        id="timeline-drawer"
        class="fixed inset-0 z-50 bg-[#0C2238]/60 backdrop-blur-xs hidden overflow-y-auto"
    >
        <div class="min-h-full flex items-center justify-end">
            <div class="w-full max-w-lg min-h-screen bg-white p-6 sm:p-8 shadow-2xl space-y-6 flex flex-col justify-between animate-slide-drawer">
                
                <div class="space-y-6">
                    <!-- Drawer Header -->
                    <div class="flex items-start justify-between pb-4 border-b border-[#E2E7ED]">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#C8460B]">Linimasa Seleksi</span>
                            <h2 id="timeline-job-title" class="text-xl font-extrabold text-[#0B1B2B] font-heading mt-1">
                                Memuat...
                            </h2>
                            <p id="timeline-job-company" class="text-xs font-semibold text-[#627080] mt-0.5">
                                -
                            </p>
                        </div>
                        <button
                            type="button"
                            id="close-timeline-drawer"
                            class="p-1.5 rounded-lg text-[#627080] hover:text-[#0B1B2B] hover:bg-[#F4F6F8] cursor-pointer"
                            aria-label="Tutup linimasa"
                        >
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div id="timeline-drawer-loading" class="py-12 space-y-3">
                        <x-skeleton :lines="2" />
                        <x-skeleton :lines="2" />
                    </div>

                    <!-- Content State -->
                    <div id="timeline-drawer-content" class="hidden space-y-6">
                        <div class="flex items-center justify-between p-3 rounded-xl bg-[#F4F6F8] border border-[#E2E7ED] text-xs">
                            <span class="text-[#627080]">Status Durasi:</span>
                            <span id="timeline-days-active" class="font-bold text-[#0B1B2B]">-</span>
                        </div>

                        <!-- Chronological Stages List -->
                        <div class="space-y-1">
                            <h4 class="text-xs font-bold uppercase tracking-wider text-[#627080] mb-4">
                                Riwayat Tahapan Kronologis
                            </h4>
                            <div id="timeline-stages-list" class="space-y-0">
                                <!-- Diisi via JS -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Drawer -->
                <div class="pt-4 border-t border-[#E2E7ED] text-center">
                    <p class="text-[11px] text-[#627080]">
                        Linimasa ini dicatat secara otomatis oleh sistem Recruitment Pipeline Service.
                    </p>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
        @vite('resources/js/pages/my-dock.js')
    @endpush
</x-layouts.app>
