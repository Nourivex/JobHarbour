<x-layouts.app title="Dasbor Rekrutmen & Manajemen Pipeline - JobHarbour">

    <div class="max-w-[1360px] mx-auto px-4 sm:px-6 py-8 sm:py-10 space-y-8">
        
        <!-- ========================================================== -->
        <!-- HEADER DASBOR REKRUTER                                     -->
        <!-- ========================================================== -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-[#E2E7ED]">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full text-xs font-bold bg-[#133150] text-[#8CC8F2] mb-1">
                    <span>Recruiter Workspace</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0B1B2B] font-heading">
                    Papan Rekrutmen & Pipeline Seleksi
                </h1>
                <p class="text-sm text-[#627080] mt-0.5">
                    Kelola tahapan kandidat dan lowongan pekerjaan dengan verifikasi kualifikasi terpadu.
                </p>
            </div>

            <!-- Pemilih Lowongan Kerja -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-2">
                    <label for="recruiter-job-selector" class="text-xs font-semibold text-[#627080]">Filter Lowongan:</label>
                    <select
                        id="recruiter-job-selector"
                        class="min-h-[42px] px-3.5 py-2 bg-white text-[#0B1B2B] text-xs font-bold rounded-xl border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] shadow-xs cursor-pointer max-w-xs truncate"
                    >
                        <option value="all">Semua Lowongan</option>
                    </select>
                </div>

                <button
                    type="button"
                    id="btn-open-create-job-modal"
                    class="min-h-[42px] px-4 py-2 bg-[#C8460B] hover:bg-[#993406] text-white font-bold text-xs rounded-xl transition-all btn-press shadow-xs flex items-center gap-1.5 cursor-pointer"
                >
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Buat Lowongan</span>
                </button>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- RINGKASAN METRIK REKRUTMEN                                 -->
        <!-- ========================================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            
            <div class="bg-white rounded-2xl p-5 border border-[#E2E7ED] shadow-xs text-left">
                <span class="text-xs font-semibold text-[#627080] block">Kandidat 80%+ Menunggu</span>
                <span id="metric-waiting-screening" class="text-3xl font-extrabold text-[#157F3B] font-heading num-tabular mt-1 block">
                    0
                </span>
                <span class="text-[11px] text-[#157F3B] mt-1 block">Lolos otomatis di tahap screening</span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E7ED] shadow-xs text-left">
                <span class="text-xs font-semibold text-[#627080] block">Total Kandidat Masuk</span>
                <span id="metric-total-candidates" class="text-3xl font-extrabold text-[#0B1B2B] font-heading num-tabular mt-1 block">
                    0
                </span>
                <span class="text-[11px] text-[#627080] mt-1 block">Di seluruh tahapan pipeline</span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E7ED] shadow-xs text-left">
                <span class="text-xs font-semibold text-[#627080] block">Wawancara Terjadwal</span>
                <span id="metric-scheduled-interviews" class="text-3xl font-extrabold text-[#1B65A6] font-heading num-tabular mt-1 block">
                    0
                </span>
                <span class="text-[11px] text-[#1B65A6] mt-1 block">Memiliki agenda interview aktif</span>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-[#E2E7ED] shadow-xs text-left">
                <span class="text-xs font-semibold text-[#627080] block">Lowongan Dibuka</span>
                <span id="metric-active-jobs" class="text-3xl font-extrabold text-[#C8460B] font-heading num-tabular mt-1 block">
                    0
                </span>
                <span class="text-[11px] text-[#C8460B] mt-1 block">Status terbuka menerima berkas</span>
            </div>

        </div>

        <!-- ========================================================== -->
        <!-- TAB NAVIGASI: PIPELINE (KANBAN) VS KELOLA LOWONGAN         -->
        <!-- ========================================================== -->
        <div class="space-y-6">
            <div class="flex items-center gap-6 border-b border-[#E2E7ED]">
                <button
                    type="button"
                    id="recruiter-tab-pipeline-btn"
                    class="pb-3 text-sm font-bold border-b-2 border-[#C8460B] text-[#C8460B] transition-colors cursor-pointer flex items-center gap-2"
                >
                    <x-icon name="users" class="w-4 h-4" />
                    <span>Papan Kanban Pipeline</span>
                </button>
                <button
                    type="button"
                    id="recruiter-tab-jobs-btn"
                    class="pb-3 text-sm font-bold border-b-2 border-transparent text-[#627080] hover:text-[#0B1B2B] transition-colors cursor-pointer flex items-center gap-2"
                >
                    <x-icon name="briefcase" class="w-4 h-4" />
                    <span>Kelola Lowongan Saya</span>
                </button>
            </div>

            <!-- Loading Spinner Global -->
            <div id="recruiter-loading" class="py-12 space-y-4">
                <x-skeleton :lines="3" />
            </div>

            <!-- ====================================================== -->
            <!-- VIEW 1: PAPAN KANBAN PIPELINE (5 KOLOM)               -->
            <!-- ====================================================== -->
            <div id="recruiter-pipeline-view" class="space-y-4">
                
                <div class="flex items-center justify-between text-xs text-[#627080]">
                    <span>Geser kartu (drag and drop) ke kanan untuk memajukan kandidat, atau klik kartu untuk rincian lengkap.</span>
                    <span class="font-semibold text-[#0B1B2B] hidden sm:inline">Aturan: Tahapan hanya bergerak maju atau ke Ditolak.</span>
                </div>

                <!-- Kanban Columns Grid (Horizontal Scrollable on Mobile) -->
                <div class="overflow-x-auto pb-4">
                    <div class="grid grid-cols-5 gap-4 min-w-[1100px] items-start">
                        
                        <!-- 1. Kolom Screening -->
                        <div class="bg-[#F4F6F8] rounded-2xl p-3.5 border border-[#E2E7ED] space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-[#E2E7ED]">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#627080]"></span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#0B1B2B]">Screening</h3>
                                </div>
                                <span id="kanban-count-screening" class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-white text-[#3D4B5C] border border-[#E2E7ED] num-tabular">
                                    0
                                </span>
                            </div>
                            <div id="kanban-col-screening" class="space-y-3 min-h-[350px] p-1 rounded-xl transition-colors">
                                <!-- Kartu diisi via JS -->
                            </div>
                        </div>

                        <!-- 2. Kolom Assessment -->
                        <div class="bg-[#F4F6F8] rounded-2xl p-3.5 border border-[#E2E7ED] space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-[#E2E7ED]">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#1B65A6]"></span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#0B1B2B]">Assessment</h3>
                                </div>
                                <span id="kanban-count-assessment" class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-white text-[#3D4B5C] border border-[#E2E7ED] num-tabular">
                                    0
                                </span>
                            </div>
                            <div id="kanban-col-assessment" class="space-y-3 min-h-[350px] p-1 rounded-xl transition-colors">
                                <!-- Kartu diisi via JS -->
                            </div>
                        </div>

                        <!-- 3. Kolom Interview -->
                        <div class="bg-[#F4F6F8] rounded-2xl p-3.5 border border-[#E2E7ED] space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-[#E2E7ED]">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#C8460B]"></span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#0B1B2B]">Wawancara</h3>
                                </div>
                                <span id="kanban-count-interview" class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-white text-[#3D4B5C] border border-[#E2E7ED] num-tabular">
                                    0
                                </span>
                            </div>
                            <div id="kanban-col-interview" class="space-y-3 min-h-[350px] p-1 rounded-xl transition-colors">
                                <!-- Kartu diisi via JS -->
                            </div>
                        </div>

                        <!-- 4. Kolom Offering -->
                        <div class="bg-[#F4F6F8] rounded-2xl p-3.5 border border-[#E2E7ED] space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-[#E2E7ED]">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#157F3B]"></span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#0B1B2B]">Penawaran</h3>
                                </div>
                                <span id="kanban-count-offering" class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-white text-[#3D4B5C] border border-[#E2E7ED] num-tabular">
                                    0
                                </span>
                            </div>
                            <div id="kanban-col-offering" class="space-y-3 min-h-[350px] p-1 rounded-xl transition-colors">
                                <!-- Kartu diisi via JS -->
                            </div>
                        </div>

                        <!-- 5. Kolom Ditolak (Rejected) -->
                        <div class="bg-[#F4F6F8] rounded-2xl p-3.5 border border-[#E2E7ED] space-y-3">
                            <div class="flex items-center justify-between pb-2 border-b border-[#E2E7ED]">
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#B42318]"></span>
                                    <h3 class="text-xs font-bold uppercase tracking-wider text-[#0B1B2B]">Ditolak</h3>
                                </div>
                                <span id="kanban-count-rejected" class="text-xs font-extrabold px-2 py-0.5 rounded-full bg-white text-[#3D4B5C] border border-[#E2E7ED] num-tabular">
                                    0
                                </span>
                            </div>
                            <div id="kanban-col-rejected" class="space-y-3 min-h-[350px] p-1 rounded-xl transition-colors">
                                <!-- Kartu diisi via JS -->
                            </div>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ====================================================== -->
            <!-- VIEW 2: KELOLA LOWONGAN SAYA                          -->
            <!-- ====================================================== -->
            <div id="recruiter-jobs-view" class="space-y-6 hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-[#0B1B2B] font-heading">
                            Daftar Lowongan yang Dikelola
                        </h2>
                        <p class="text-xs text-[#627080]">
                            Perbarui kualifikasi keahlian, status buka/tutup, atau buat posisi baru.
                        </p>
                    </div>
                </div>

                <!-- Grid Kartu Lowongan Dikelola -->
                <div id="recruiter-managed-jobs-list" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Diisi via JS -->
                </div>
            </div>

        </div>

    </div>

    <!-- ============================================================== -->
    <!-- DRAWER DETAIL KANDIDAT & ATUR TAHAPAN                          -->
    <!-- ============================================================== -->
    <div
        id="candidate-drawer"
        class="fixed inset-0 z-50 bg-[#0C2238]/60 backdrop-blur-xs hidden overflow-y-auto"
    >
        <div class="min-h-full flex items-center justify-end">
            <div class="w-full max-w-xl min-h-screen bg-white p-6 sm:p-8 shadow-2xl space-y-6 flex flex-col justify-between animate-slide-drawer">
                
                <div class="space-y-6">
                    <!-- Drawer Header -->
                    <div class="flex items-start justify-between pb-4 border-b border-[#E2E7ED]">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-bold uppercase tracking-wider text-[#C8460B]">Detail Kandidat</span>
                                <span id="cand-score" class="text-xs font-bold">-</span>
                            </div>
                            <h2 id="cand-name" class="text-2xl font-extrabold text-[#0B1B2B] font-heading">
                                -
                            </h2>
                            <p id="cand-job-title" class="text-xs text-[#627080] mt-0.5">
                                -
                            </p>
                        </div>
                        <button
                            type="button"
                            id="close-candidate-drawer"
                            class="p-1.5 rounded-lg text-[#627080] hover:text-[#0B1B2B] hover:bg-[#F4F6F8] cursor-pointer"
                            aria-label="Tutup detail kandidat"
                        >
                            <x-icon name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <!-- Loading State -->
                    <div id="candidate-drawer-loading" class="py-12 space-y-3">
                        <x-skeleton :lines="3" />
                    </div>

                    <!-- Content State -->
                    <div id="candidate-drawer-content" class="hidden space-y-6 text-left text-xs">
                        
                        <!-- Biodata Singkat -->
                        <div class="p-4 rounded-xl bg-[#F4F6F8] border border-[#E2E7ED] space-y-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#3D4B5C] block">Kontak Pelamar:</span>
                            <div class="grid grid-cols-2 gap-2 text-[#3D4B5C]">
                                <div><span class="text-[#627080]">Email:</span> <span id="cand-email" class="font-bold text-[#0B1B2B] block">-</span></div>
                                <div><span class="text-[#627080]">Telepon:</span> <span id="cand-phone" class="font-bold text-[#0B1B2B] block">-</span></div>
                                <div><span class="text-[#627080]">Lokasi:</span> <span id="cand-location" class="font-bold text-[#0B1B2B] block">-</span></div>
                            </div>
                            <div class="pt-2 border-t border-[#E2E7ED]">
                                <span class="text-[#627080] block mb-0.5">Bio Ringkas:</span>
                                <p id="cand-bio" class="text-[#3D4B5C] leading-relaxed">-</p>
                            </div>
                        </div>

                        <!-- Matched vs Missing Skills Report -->
                        <div class="space-y-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#3D4B5C] block">Analisis Pemadanan Keahlian:</span>
                            
                            <div class="space-y-1">
                                <span class="text-[11px] font-semibold text-[#157F3B] block">Keahlian yang Cocok (Matched):</span>
                                <div id="cand-matched-skills" class="flex flex-wrap gap-1.5">
                                    <!-- Diisi via JS -->
                                </div>
                            </div>

                            <div class="space-y-1 pt-1">
                                <span class="text-[11px] font-semibold text-[#B42318] block">Keahlian Belum Dimiliki (Missing):</span>
                                <div id="cand-missing-skills" class="flex flex-wrap gap-1.5">
                                    <!-- Diisi via JS -->
                                </div>
                            </div>
                        </div>

                        <!-- Surat Lamaran (Cover Letter) -->
                        <div class="space-y-1.5 pt-2 border-t border-[#E2E7ED]">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#3D4B5C] block">Surat Pengantar (Cover Letter):</span>
                            <div id="cand-cover-letter" class="p-3.5 rounded-xl bg-[#F4F6F8] text-[#3D4B5C] leading-relaxed max-h-36 overflow-y-auto">
                                -
                            </div>
                        </div>

                        <!-- Tombol Unduh CV -->
                        <div class="pt-1">
                            <button
                                type="button"
                                id="cand-btn-download-cv"
                                class="w-full py-2.5 px-4 bg-[#F4F6F8] hover:bg-[#E2E7ED] text-[#0B1B2B] font-bold rounded-xl border border-[#C5CFDA] transition-colors flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <x-icon name="download" class="w-4 h-4 text-[#1B65A6]" />
                                <span>Unduh Berkas CV Pelamar (PDF)</span>
                            </button>
                        </div>

                        <!-- ============================================== -->
                        <!-- FORM TINDAKAN: PERBARUI TAHAPAN SELEKSI        -->
                        <!-- ============================================== -->
                        <div class="pt-4 border-t border-[#E2E7ED] space-y-4">
                            <span class="text-xs font-bold uppercase tracking-wider text-[#C8460B] block">Tindakan Seleksi Rekruter:</span>

                            <!-- Pilih Tahap Baru -->
                            <div>
                                <label for="cand-select-stage" class="block font-semibold text-[#0B1B2B] mb-1">
                                    Pindahkan ke Tahapan:
                                </label>
                                <select
                                    id="cand-select-stage"
                                    class="w-full min-h-[42px] px-3.5 py-2 bg-white text-[#0B1B2B] font-semibold rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] cursor-pointer"
                                >
                                    <!-- Diisi via JS -->
                                </select>
                            </div>

                            <!-- Bidang Khusus Jadwal Wawancara (Tampil jika tahap interview) -->
                            <div id="cand-interview-schedule-fields" class="p-4 rounded-xl bg-[#EBF3FB] border border-[#1B65A6]/30 space-y-3 hidden">
                                <span class="font-bold text-[#1B65A6] block">Atur Jadwal Wawancara Resmi:</span>
                                
                                <div>
                                    <label class="block text-[11px] font-semibold text-[#3D4B5C] mb-1">Waktu Wawancara:</label>
                                    <input
                                        type="datetime-local"
                                        id="cand-interview-datetime"
                                        class="w-full min-h-[38px] px-3 py-1.5 bg-white text-xs rounded-lg border border-[#C5CFDA]"
                                    />
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-[#3D4B5C] mb-1">Tipe:</label>
                                        <select id="cand-interview-type" class="w-full min-h-[38px] px-3 py-1.5 bg-white text-xs rounded-lg border border-[#C5CFDA]">
                                            <option value="online">Online (Daring)</option>
                                            <option value="offline">Offline (Tatap Muka)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-[#3D4B5C] mb-1">Nama Pewawancara:</label>
                                        <input
                                            type="text"
                                            id="cand-interview-interviewer"
                                            placeholder="Nama HR / Tech Lead"
                                            class="w-full min-h-[38px] px-3 py-1.5 bg-white text-xs rounded-lg border border-[#C5CFDA]"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[11px] font-semibold text-[#3D4B5C] mb-1">Tautan Pertemuan / Ruang Rapat:</label>
                                    <input
                                        type="text"
                                        id="cand-interview-url"
                                        placeholder="https://meet.google.com/... atau Ruang Rapat Lt. 2"
                                        class="w-full min-h-[38px] px-3 py-1.5 bg-white text-xs rounded-lg border border-[#C5CFDA]"
                                    />
                                </div>
                            </div>

                            <!-- Catatan Rekruter -->
                            <div>
                                <label for="cand-stage-notes" class="block font-semibold text-[#0B1B2B] mb-1">
                                    Catatan Rekruter untuk Kandidat (Opsional):
                                </label>
                                <textarea
                                    id="cand-stage-notes"
                                    rows="3"
                                    placeholder="Tuliskan catatan kemajuan atau evaluasi teknis..."
                                    class="w-full p-3 bg-white text-xs rounded-lg border border-[#C5CFDA] focus:border-[#C8460B]"
                                ></textarea>
                            </div>

                            <!-- Submit Button -->
                            <button
                                type="button"
                                id="cand-btn-save-stage"
                                class="w-full py-3 px-4 bg-[#C8460B] hover:bg-[#993406] text-white font-bold text-sm rounded-xl transition-all btn-press shadow-xs cursor-pointer"
                            >
                                Simpan & Perbarui Tahap
                            </button>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL BUAT / EDIT LOWONGAN PEKERJAAN                           -->
    <!-- ============================================================== -->
    <x-modal id="modal-job-form" title="Terbitkan Lowongan Pekerjaan Baru" maxWidth="max-w-2xl">
        <form id="job-form" class="space-y-4 text-left">
            <div>
                <label for="job-title" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                    Judul Posisi Pekerjaan <span class="text-[#C8460B]">*</span>
                </label>
                <input
                    type="text"
                    id="job-title"
                    required
                    placeholder="misal: Senior Backend Engineer"
                    class="w-full min-h-[42px] px-3.5 py-2 bg-[#F4F6F8] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B]"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="job-company" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                        Nama Perusahaan / Instansi <span class="text-[#C8460B]">*</span>
                    </label>
                    <input
                        type="text"
                        id="job-company"
                        required
                        placeholder="contoh: PT Maritim Digital Nusantara"
                        class="w-full min-h-[42px] px-3.5 py-2 bg-[#F4F6F8] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B]"
                    />
                </div>
                <div>
                    <label for="job-location" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                        Kota Penempatan <span class="text-[#C8460B]">*</span>
                    </label>
                    <input
                        type="text"
                        id="job-location"
                        required
                        placeholder="contoh: Tegal, Jawa Tengah (atau Remote)"
                        class="w-full min-h-[42px] px-3.5 py-2 bg-[#F4F6F8] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B]"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="job-type" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                        Jenis Ikatan Kerja
                    </label>
                    <select
                        id="job-type"
                        class="w-full min-h-[42px] px-3.5 py-2 bg-[#F4F6F8] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] cursor-pointer"
                    >
                        <option value="full-time">Full-time (Purna Waktu)</option>
                        <option value="part-time">Part-time (Paruh Waktu)</option>
                        <option value="contract">Kontrak</option>
                        <option value="internship">Magang</option>
                        <option value="remote">Remote (Jarak Jauh)</option>
                    </select>
                </div>
                <div>
                    <label for="job-quota" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                        Kuota Rekrutmen (Kandidat)
                    </label>
                    <input
                        type="number"
                        id="job-quota"
                        min="1"
                        value="1"
                        class="w-full min-h-[42px] px-3.5 py-2 bg-[#F4F6F8] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B]"
                    />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <label for="job-salary-min" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                        Gaji Minimal (Rp, Opsional)
                    </label>
                    <input
                        type="number"
                        id="job-salary-min"
                        placeholder="contoh: 8000000"
                        class="w-full min-h-[42px] px-3.5 py-2 bg-[#F4F6F8] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B]"
                    />
                </div>
                <div>
                    <label for="job-salary-max" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                        Gaji Maksimal (Rp, Opsional)
                    </label>
                    <input
                        type="number"
                        id="job-salary-max"
                        placeholder="contoh: 15000000"
                        class="w-full min-h-[42px] px-3.5 py-2 bg-[#F4F6F8] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B]"
                    />
                </div>
            </div>

            <!-- Dynamic Skills Requirements Builder -->
            <div class="pt-2 border-t border-[#E2E7ED] space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-bold uppercase tracking-wider text-[#0B1B2B]">
                        Kualifikasi Syarat Keahlian (Requirements) <span class="text-[#C8460B]">*</span>
                    </label>
                    <button
                        type="button"
                        id="btn-add-requirement-row"
                        class="text-xs font-bold text-[#C8460B] hover:underline cursor-pointer"
                    >
                        + Tambah Syarat
                    </button>
                </div>
                <p class="text-[11px] text-[#627080]">
                    Pilih bobot: Wajib (bobot 3x), Penting (bobot 2x), atau Nilai Tambah (bobot 1x) untuk mesin kalkulasi.
                </p>

                <!-- Requirements Rows Container -->
                <div id="job-requirements-rows" class="space-y-2 max-h-48 overflow-y-auto p-1">
                    <!-- Diisi via JS -->
                </div>
            </div>

            <!-- Deskripsi Lowongan -->
            <div class="pt-2">
                <label for="job-description" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                    Deskripsi & Kriteria Posisi <span class="text-[#C8460B]">*</span>
                </label>
                <textarea
                    id="job-description"
                    rows="4"
                    required
                    placeholder="Uraikan tanggung jawab posisi, kualifikasi teknis, dan keuntungan bergabung..."
                    class="w-full p-3 bg-[#F4F6F8] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B]"
                ></textarea>
            </div>

            <div class="pt-3 border-t border-[#E2E7ED] flex items-center justify-end gap-2">
                <button
                    type="button"
                    onclick="window.JHModal && window.JHModal.close('modal-job-form')"
                    class="px-4 py-2.5 rounded-lg text-xs font-semibold text-[#3D4B5C] bg-[#F4F6F8] hover:bg-[#E2E7ED] transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    id="btn-save-job"
                    class="px-5 py-2.5 rounded-lg text-xs font-bold text-white bg-[#C8460B] hover:bg-[#993406] transition-all btn-press shadow-xs cursor-pointer"
                >
                    Simpan & Terbitkan
                </button>
            </div>
        </form>
    </x-modal>

    @push('scripts')
        @vite('resources/js/pages/recruiter.js')
    @endpush
</x-layouts.app>
