<x-layouts.app title="Kirim Berkas Lamaran - JobHarbour">

    <div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-8 sm:py-10">
        
        <!-- Breadcrumb & Navigasi Balik -->
        <div class="mb-6 flex items-center justify-between">
            <a
                href="/catalog"
                class="inline-flex items-center gap-1.5 text-sm font-semibold text-[#627080] hover:text-[#0B1B2B] transition-colors"
            >
                <x-icon name="arrow-left" class="w-4 h-4" />
                <span>Kembali ke Katalog Lowongan</span>
            </a>
            <span class="text-xs text-[#627080] font-medium hidden sm:inline">Pengajuan Berkas Resmi</span>
        </div>

        <!-- Banner Peringatan jika 409 Conflict (Sudah Dilamar) -->
        <div id="apply-conflict-banner" class="hidden mb-6 p-4 rounded-2xl bg-[#FFF3EA] border border-[#C8460B]/30 text-[#8A2E05] flex items-start gap-3">
            <x-icon name="alert-triangle" class="w-5 h-5 text-[#C8460B] shrink-0 mt-0.5" />
            <div class="text-sm">
                <strong class="font-bold block">Kamu sudah melamar lowongan ini sebelumnya!</strong>
                Berkas lamaran Anda untuk posisi ini telah tercatat dalam sistem. Anda dapat memantau status perkembangan seleksi atau jadwal wawancara di halaman Lamaran Saya.
                <div class="mt-2.5">
                    <a href="/my-dock" class="inline-flex items-center gap-1 font-bold text-[#C8460B] hover:underline">
                        <span>Buka Lamaran Saya</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                    </a>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- STATE 1: FORM PENGAJUAN (Default)                              -->
        <!-- ============================================================== -->
        <div id="apply-form-container" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- KOLOM KIRI: FORM DATA PELAMAR & BERKAS (7 Kolom di LG) -->
            <div class="lg:col-span-7 bg-white rounded-2xl border border-[#E2E7ED] p-6 sm:p-8 shadow-xs">
                
                <div class="pb-6 border-b border-[#E2E7ED] mb-6">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#C8460B]">Formulir Pelamar</span>
                    <h1 class="text-2xl font-extrabold text-[#0B1B2B] font-heading mt-1">
                        Kirim Berkas Lamaran Kerja
                    </h1>
                    <p class="text-sm text-[#627080] mt-1">
                        Pastikan keahlian profil dan berkas CV Anda mutakhir agar pemadanan skor optimal.
                    </p>
                </div>

                <form id="apply-form" action="javascript:void(0);" method="POST" class="space-y-6">
                    
                    <!-- 1. Ringkasan Data Diri Terverifikasi -->
                    <div class="p-4 rounded-xl bg-[#F4F6F8] border border-[#E2E7ED] space-y-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-[#3D4B5C] block">
                            Data Pelamar Terverifikasi
                        </span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-[#3D4B5C]">
                            <div>
                                <span class="text-[#627080] block">Nama Lengkap:</span>
                                <span id="applicant-name" class="font-bold text-[#0B1B2B]">-</span>
                            </div>
                            <div>
                                <span class="text-[#627080] block">Email Akun:</span>
                                <span id="applicant-email" class="font-bold text-[#0B1B2B]">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Editor Keahlian yang Disertakan -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-semibold text-[#0B1B2B]">
                                Keahlian yang Dikirimkan (Skills)
                            </label>
                            <span class="text-xs text-[#627080]">Mempengaruhi Skor Pemadanan</span>
                        </div>
                        <p class="text-xs text-[#627080] leading-relaxed">
                            Secara baku mengambil dari profil Anda. Anda dapat menambahkan keahlian lain yang relevan khusus untuk lamaran posisi ini:
                        </p>

                        <div class="flex gap-2">
                            <input
                                type="text"
                                id="apply-skill-input"
                                placeholder="Ketik nama skill lalu klik Tambah..."
                                class="flex-1 min-h-[44px] px-3.5 py-2.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                            />
                            <button
                                type="button"
                                id="apply-btn-add-skill"
                                class="px-4 py-2.5 bg-[#0C2238] hover:bg-[#133150] text-white text-xs font-bold rounded-lg transition-colors cursor-pointer"
                            >
                                Tambah
                            </button>
                        </div>

                        <!-- Tag Chips -->
                        <div id="apply-skills-chips" class="flex flex-wrap gap-1.5 pt-1">
                            <!-- Diisi via JS -->
                        </div>
                    </div>

                    <!-- 3. Unggah Berkas CV PDF (Drag & Drop) -->
                    <div class="space-y-2 pt-2">
                        <label class="text-sm font-semibold text-[#0B1B2B] flex items-center justify-between">
                            <span>Unggah Berkas Resume / CV (PDF)</span>
                            <span class="text-xs text-[#627080]">Maksimal 2 MB</span>
                        </label>

                        <!-- Dropzone Area -->
                        <div
                            id="cv-dropzone"
                            class="border-2 border-dashed border-[#C5CFDA] hover:border-[#C8460B] rounded-2xl p-6 text-center cursor-pointer transition-colors bg-[#F4F6F8]/60"
                            onclick="document.getElementById('cv-file-input').click()"
                        >
                            <input
                                type="file"
                                id="cv-file-input"
                                accept="application/pdf,.pdf"
                                class="hidden"
                            />
                            <div class="w-12 h-12 rounded-xl bg-white text-[#C8460B] flex items-center justify-center mx-auto mb-3 shadow-xs">
                                <x-icon name="upload" class="w-6 h-6" />
                            </div>
                            <p class="text-sm font-bold text-[#0B1B2B]">
                                Klik untuk memilih berkas atau seret PDF ke area ini
                            </p>
                            <p class="text-xs text-[#627080] mt-1">
                                Format file wajib .PDF dengan ukuran maksimal 2048 KB (2 MB).
                            </p>
                        </div>

                        <!-- File Preview Area (Tampil saat file dipilih) -->
                        <div id="cv-file-preview" class="hidden p-4 rounded-xl bg-white border border-[#157F3B]/30 flex items-center justify-between shadow-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-[#E7F5EC] text-[#157F3B] flex items-center justify-center">
                                    <x-icon name="file-text" class="w-5 h-5" />
                                </div>
                                <div>
                                    <p id="cv-filename" class="text-sm font-bold text-[#0B1B2B] truncate max-w-xs">-</p>
                                    <p id="cv-filesize" class="text-xs text-[#627080]">-</p>
                                </div>
                            </div>
                            <button
                                type="button"
                                id="cv-remove-btn"
                                class="text-xs font-semibold text-[#B42318] hover:underline p-1.5 cursor-pointer"
                            >
                                Ganti Berkas
                            </button>
                        </div>

                        <p data-field-error="cv_file" class="text-xs font-medium text-[#B42318] hidden"></p>
                    </div>

                    <!-- 4. Surat Lamaran / Cover Letter -->
                    <div class="space-y-1.5 pt-2">
                        <label for="apply-cover-letter" class="text-sm font-semibold text-[#0B1B2B] flex items-center justify-between">
                            <span>Surat Pengantar / Catatan untuk Rekruter (Opsional)</span>
                        </label>
                        <textarea
                            id="apply-cover-letter"
                            rows="4"
                            placeholder="Tuliskan motivasi, keunggulan kompetensi, atau alasan Anda tertarik melamar posisi ini..."
                            class="w-full p-3.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        ></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t border-[#E2E7ED]">
                        <button
                            type="submit"
                            id="apply-submit-btn"
                            class="w-full min-h-[48px] px-6 py-3 rounded-xl text-sm font-bold bg-[#C8460B] hover:bg-[#993406] text-white transition-all btn-press shadow-xs flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span>Kirim Berkas Lamaran</span>
                            <x-icon name="arrow-right" class="w-4 h-4" />
                        </button>
                        <p class="text-[11px] text-center text-[#627080] mt-2">
                            Skor pemadanan kompetensi akan dihitung secara langsung setelah berkas diterima.
                        </p>
                    </div>

                </form>

            </div>

            <!-- KOLOM KANAN: RINGKASAN LOWONGAN & LIVE MATCH PREVIEW (5 Kolom di LG) -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- Live Match Preview Card -->
                <div class="bg-white rounded-2xl border border-[#E2E7ED] p-6 shadow-xs space-y-4">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#627080]">Kalkulasi Skor Live</span>
                    
                    <div class="flex items-baseline justify-between pt-1">
                        <span id="live-score-num" class="text-4xl font-extrabold text-[#0B1B2B] font-heading num-tabular">
                            0%
                        </span>
                        <span id="live-score-category" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-[#EBF3FB] text-[#1B65A6]">
                            Memuat...
                        </span>
                    </div>

                    <!-- Bar Meter -->
                    <div class="relative w-full h-3 bg-[#E2E7ED] rounded-full overflow-hidden">
                        <div id="live-score-bar" class="h-full rounded-full transition-all duration-500 ease-out bg-[#1B65A6]" style="width: 0%;"></div>
                        <div class="absolute top-0 bottom-0 left-1/2 w-0.5 bg-white shadow-xs"></div>
                        <div class="absolute top-0 bottom-0 left-[80%] w-0.5 bg-white shadow-xs"></div>
                    </div>

                    <p id="live-score-desc" class="text-xs text-[#3D4B5C] leading-relaxed pt-1">
                        -
                    </p>
                </div>

                <!-- Info Lowongan Ringkas -->
                <div class="bg-white rounded-2xl border border-[#E2E7ED] p-6 shadow-xs space-y-4 text-left">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#627080]">Lowongan yang Dituju</span>
                    
                    <div>
                        <h3 id="summary-job-title" class="text-lg font-bold text-[#0B1B2B] font-heading leading-snug">
                            Memuat...
                        </h3>
                        <p id="summary-job-company" class="text-sm font-semibold text-[#3D4B5C] mt-0.5">
                            -
                        </p>
                    </div>

                    <div class="space-y-2 text-xs text-[#627080] pt-2 border-t border-[#E2E7ED]">
                        <div class="flex items-center justify-between">
                            <span>Lokasi Penempatan:</span>
                            <span id="summary-job-location" class="font-bold text-[#0B1B2B]">-</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Jenis Kerja:</span>
                            <span id="summary-job-type" class="font-bold text-[#0B1B2B]">-</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Estimasi Gaji:</span>
                            <span id="summary-job-salary" class="font-bold text-[#0B1B2B]">-</span>
                        </div>
                    </div>

                    <!-- Syarat Lowongan List -->
                    <div class="pt-3 border-t border-[#E2E7ED] space-y-2">
                        <span class="text-xs font-bold text-[#3D4B5C] block">Syarat Kualifikasi Posisi:</span>
                        <div id="summary-requirements-list" class="space-y-1">
                            <!-- Diisi via JS -->
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ============================================================== -->
        <!-- STATE 2: LAYAR SUKSES PENGIRIMAN (Success Screen)              -->
        <!-- ============================================================== -->
        <div id="apply-success-container" class="hidden max-w-xl mx-auto bg-white rounded-3xl border border-[#E2E7ED] p-8 sm:p-12 text-center shadow-lg space-y-6">
            <div class="w-16 h-16 rounded-2xl bg-[#E7F5EC] text-[#157F3B] flex items-center justify-center mx-auto shadow-xs">
                <x-icon name="check" class="w-8 h-8" />
            </div>

            <div class="space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider text-[#157F3B]">Lamaran Berhasil Terkirim</span>
                <h2 class="text-2xl font-extrabold text-[#0B1B2B] font-heading">
                    Berkas Siap Diproses di Pelabuhan Karir
                </h2>
                <p class="text-sm text-[#627080] max-w-md mx-auto">
                    Berkas pengajuan dan dokumen CV Anda telah diterima oleh server JobHarbour.
                </p>
            </div>

            <!-- Status Kelulusan Screening Otomatis -->
            <div id="success-status-message" class="text-left">
                <!-- Diisi via JS berdasarkan skor -->
            </div>

            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a
                    href="/my-dock"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-bold bg-[#C8460B] hover:bg-[#993406] text-white transition-all btn-press shadow-xs flex items-center justify-center gap-2"
                >
                    <x-icon name="anchor" class="w-4 h-4" />
                    <span>Lihat di Lamaran Saya</span>
                </a>
                <a
                    href="/catalog"
                    class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-semibold bg-[#F4F6F8] hover:bg-[#E2E7ED] text-[#0B1B2B] transition-colors flex items-center justify-center gap-2"
                >
                    <span>Kembali ke Katalog</span>
                </a>
            </div>
        </div>

    </div>

    @push('scripts')
        @vite('resources/js/pages/apply.js')
    @endpush
</x-layouts.app>
