<x-layouts.app title="Profil & Kalibrasi Keahlian - JobHarbour">

    <div class="max-w-[1240px] mx-auto px-4 sm:px-6 py-8 sm:py-10 space-y-8">
        
        <!-- ========================================================== -->
        <!-- HEADER KARTU PROFIL PENGGUNA                               -->
        <!-- ========================================================== -->
        <div class="bg-white rounded-3xl border border-[#E2E7ED] p-6 sm:p-8 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-6">
            <div class="flex items-center gap-4 sm:gap-5">
                <div id="profile-header-initials" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-[#0C2238] text-white flex items-center justify-center font-extrabold text-2xl sm:text-3xl font-heading shadow-md uppercase">
                    JH
                </div>
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <h1 id="profile-header-name" class="text-xl sm:text-2xl font-extrabold text-[#0B1B2B] font-heading leading-tight">
                            Memuat Nama...
                        </h1>
                        <span id="profile-header-role" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-[#FFF3EA] text-[#8A2E05] border border-[#C8460B]/20">
                            -
                        </span>
                    </div>
                    <p id="profile-header-email" class="text-xs sm:text-sm text-[#627080]">
                        -
                    </p>
                    <div class="flex items-center gap-2 pt-1 text-xs text-[#1B65A6]">
                        <x-icon name="check-circle" class="w-3.5 h-3.5 text-[#157F3B]" />
                        <span>Akun Terverifikasi di JobHarbour Registry</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span id="profile-skills-count-badge" class="px-3.5 py-2 rounded-xl bg-[#F4F6F8] border border-[#E2E7ED] text-xs font-bold text-[#0B1B2B]">
                    0 Keahlian
                </span>
            </div>
        </div>

        <!-- ========================================================== -->
        <!-- DUA BAGIAN UTAMA: KELOLA KEAHLIAN & EDIT BIODATA           -->
        <!-- ========================================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- KOLOM KIRI: KALIBRASI KEAHLIAN & REKOMENDASI PASAR (7 Kolom) -->
            <div class="lg:col-span-7 space-y-6">
                
                <!-- Card 1: Kelola Keahlian (Skills Tag Editor) -->
                <div class="bg-white rounded-2xl border border-[#E2E7ED] p-6 sm:p-7 shadow-xs space-y-5 text-left">
                    <div class="flex items-center justify-between pb-4 border-b border-[#E2E7ED]">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#C8460B]">Matriks Kompetensi</span>
                            <h2 class="text-lg font-bold text-[#0B1B2B] font-heading mt-0.5">
                                Kelola Keahlian Profil Anda
                            </h2>
                        </div>
                        <button
                            type="button"
                            id="btn-save-skills"
                            class="px-4 py-2 rounded-lg text-xs font-bold text-white bg-[#C8460B] hover:bg-[#993406] transition-all btn-press shadow-xs cursor-pointer flex items-center gap-1.5"
                        >
                            <span>Simpan Keahlian</span>
                        </button>
                    </div>

                    <!-- Dirty Changes Banner -->
                    <div id="skills-dirty-banner" class="hidden p-3 rounded-xl bg-[#FFF3EA] border border-[#C8460B]/30 text-xs text-[#8A2E05] flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <x-icon name="alert-triangle" class="w-4 h-4 text-[#C8460B] shrink-0" />
                            <span>Ada perubahan keahlian yang belum disimpan ke server.</span>
                        </div>
                        <span class="font-bold underline cursor-pointer" onclick="document.getElementById('btn-save-skills').click()">Simpan Sekarang</span>
                    </div>

                    <!-- Input Tambah Tag Keahlian -->
                    <div class="space-y-1.5">
                        <label for="profile-skill-input" class="text-xs font-semibold text-[#0B1B2B]">
                            Tambah Keahlian Baru:
                        </label>
                        <div class="flex gap-2">
                            <input
                                type="text"
                                id="profile-skill-input"
                                placeholder="Ketik keahlian (contoh: PostgreSQL, Redis, REST API)..."
                                class="flex-1 min-h-[44px] px-3.5 py-2.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                            />
                            <button
                                type="button"
                                id="btn-add-profile-skill"
                                class="px-5 py-2.5 bg-[#0C2238] hover:bg-[#133150] text-white text-xs font-bold rounded-lg transition-colors cursor-pointer"
                            >
                                Tambah
                            </button>
                        </div>
                        <p class="text-[11px] text-[#627080]">
                            Tekan Enter atau klik tombol Tambah. Keahlian ini digunakan langsung oleh mesin pemadanan lowongan.
                        </p>
                    </div>

                    <!-- Daftar Chip Keahlian Aktif -->
                    <div class="space-y-2 pt-2">
                        <span class="text-xs font-semibold text-[#627080] block">Daftar Keahlian Aktif:</span>
                        <div id="profile-skills-chips-container" class="flex flex-wrap gap-2 min-h-[44px] p-3 rounded-xl bg-[#F4F6F8]/60 border border-[#E2E7ED]">
                            <!-- Diisi via JS -->
                        </div>
                    </div>
                </div>

                <!-- Card 2: Rekomendasi Keahlian Pasar (Skill Demand Insight) -->
                <div class="bg-white rounded-2xl border border-[#E2E7ED] p-6 sm:p-7 shadow-xs space-y-4 text-left">
                    <div class="flex items-center justify-between pb-3 border-b border-[#E2E7ED]">
                        <div>
                            <span class="text-xs font-bold uppercase tracking-wider text-[#1B65A6]">Wawasan Pasar Kerja</span>
                            <h3 class="text-base font-bold text-[#0B1B2B] font-heading mt-0.5">
                                Keahlian Paling Banyak Dicari Rekruter
                            </h3>
                        </div>
                        <span class="text-xs text-[#627080]">Dianalisis Otomatis</span>
                    </div>

                    <p class="text-xs text-[#627080] leading-relaxed">
                        Berdasarkan seluruh lowongan yang saat ini aktif dibuka, menambahkan keahlian berikut berpotensi mendongkrak skor kecocokan Anda secara signifikan:
                    </p>

                    <!-- List Rekomendasi Dinamis -->
                    <div id="profile-skill-recommendations" class="space-y-2.5 pt-1">
                        <!-- Diisi via JS -->
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: FORM EDIT BIODATA (5 Kolom) -->
            <div class="lg:col-span-5 bg-white rounded-2xl border border-[#E2E7ED] p-6 sm:p-7 shadow-xs space-y-5 text-left">
                <div class="pb-4 border-b border-[#E2E7ED]">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#627080]">Informasi Akun</span>
                    <h2 class="text-lg font-bold text-[#0B1B2B] font-heading mt-0.5">
                        Biodata Diri
                    </h2>
                </div>

                <form id="profile-biodata-form" class="space-y-4">
                    <!-- Nama Lengkap -->
                    <div>
                        <label for="bio-name" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                            Nama Lengkap <span class="text-[#C8460B]">*</span>
                        </label>
                        <input
                            type="text"
                            id="bio-name"
                            required
                            class="w-full min-h-[44px] px-3.5 py-2.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        />
                    </div>

                    <!-- Email (Readonly) -->
                    <div>
                        <label for="bio-email" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                            Alamat Email (Akun Utama)
                        </label>
                        <input
                            type="email"
                            id="bio-email"
                            disabled
                            class="w-full min-h-[44px] px-3.5 py-2.5 bg-[#E2E7ED]/50 text-[#627080] text-sm rounded-lg border border-[#C5CFDA] cursor-not-allowed"
                        />
                        <span class="text-[10px] text-[#627080] mt-1 block">Email terhubung dengan sesi autentikasi JWT.</span>
                    </div>

                    <!-- Nomor Telepon -->
                    <div>
                        <label for="bio-phone" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                            Nomor Telepon / WhatsApp
                        </label>
                        <input
                            type="tel"
                            id="bio-phone"
                            placeholder="081234567890"
                            class="w-full min-h-[44px] px-3.5 py-2.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        />
                    </div>

                    <!-- Kota Domisili -->
                    <div>
                        <label for="bio-location" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                            Kota Domisili
                        </label>
                        <input
                            type="text"
                            id="bio-location"
                            placeholder="contoh: Tegal, Jawa Tengah"
                            class="w-full min-h-[44px] px-3.5 py-2.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        />
                    </div>

                    <!-- Ringkasan Diri (Bio) -->
                    <div>
                        <label for="bio-bio" class="block text-xs font-semibold text-[#0B1B2B] mb-1">
                            Ringkasan Profil (Bio Singkat)
                        </label>
                        <textarea
                            id="bio-bio"
                            rows="4"
                            placeholder="Jelaskan spesialisasi teknis dan pengalaman utama Anda..."
                            class="w-full p-3.5 bg-[#F4F6F8] text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        ></textarea>
                    </div>

                    <!-- Tombol Simpan Biodata -->
                    <div class="pt-3 border-t border-[#E2E7ED]">
                        <button
                            type="submit"
                            id="btn-save-biodata"
                            class="w-full min-h-[44px] px-5 py-2.5 rounded-lg text-sm font-bold text-white bg-[#0C2238] hover:bg-[#133150] transition-all btn-press shadow-xs cursor-pointer flex items-center justify-center gap-2"
                        >
                            <span>Simpan Perubahan Biodata</span>
                        </button>
                    </div>
                </form>

            </div>

        </div>

    </div>

    @push('scripts')
        @vite('resources/js/pages/profile.js')
    @endpush
</x-layouts.app>
