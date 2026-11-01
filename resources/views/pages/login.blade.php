<x-layouts.auth title="Masuk atau Daftar - JobHarbour">

    <div class="w-full max-w-md mx-auto">
        <!-- Tab Pemilih: Masuk vs Daftar -->
        <div class="flex items-center border-b border-[#E2E7ED] mb-8">
            <button
                type="button"
                id="tab-login-btn"
                class="flex-1 pb-3 text-center text-sm font-bold border-b-2 border-[#C8460B] text-[#C8460B] transition-colors cursor-pointer"
            >
                Masuk ke Akun
            </button>
            <button
                type="button"
                id="tab-register-btn"
                class="flex-1 pb-3 text-center text-sm font-bold border-b-2 border-transparent text-[#627080] hover:text-[#0B1B2B] transition-colors cursor-pointer"
            >
                Daftar Baru
            </button>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 1: FORM MASUK (LOGIN)                                      -->
        <!-- ============================================================== -->
        <div id="login-form-wrap" class="space-y-6">
            <div>
                <h2 class="text-2xl font-bold text-[#0B1B2B] tracking-tight font-heading">Selamat Datang di Harbour</h2>
                <p class="text-sm text-[#627080] mt-1">Gunakan kredensial akun terdaftar untuk mengakses platform.</p>
            </div>

            <!-- Tombol Cepat Akun Demo -->
            <div class="p-3.5 rounded-xl bg-[#F4F6F8] border border-[#E2E7ED] space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider text-[#3D4B5C]">Akun Demo Pengujian</span>
                    <span class="text-[11px] text-[#627080]">Sandi: password123</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <button
                        type="button"
                        data-demo-email="budi@jobharbour.id"
                        class="px-2 py-1.5 rounded-lg text-xs font-semibold bg-white border border-[#C5CFDA] hover:border-[#0C2238] text-[#0B1B2B] transition-colors btn-press truncate text-center"
                    >
                        Budi (Pelamar)
                    </button>
                    <button
                        type="button"
                        data-demo-email="recruiter@jobharbour.id"
                        class="px-2 py-1.5 rounded-lg text-xs font-semibold bg-white border border-[#C5CFDA] hover:border-[#0C2238] text-[#0B1B2B] transition-colors btn-press truncate text-center"
                    >
                        Recruiter
                    </button>
                    <button
                        type="button"
                        data-demo-email="admin@jobharbour.id"
                        class="px-2 py-1.5 rounded-lg text-xs font-semibold bg-white border border-[#C5CFDA] hover:border-[#0C2238] text-[#0B1B2B] transition-colors btn-press truncate text-center"
                    >
                        Admin
                    </button>
                </div>
            </div>

            <form id="login-form" class="space-y-4">
                <!-- Input Email -->
                <div>
                    <label for="login-email" class="block text-sm font-semibold text-[#0B1B2B] mb-1">
                        Alamat Email <span class="text-[#C8460B]">*</span>
                    </label>
                    <input
                        type="email"
                        id="login-email"
                        name="email"
                        required
                        placeholder="contoh: nama@jobharbour.id"
                        class="w-full min-h-[44px] px-3.5 py-2.5 bg-white text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                    />
                    <p data-field-error="email" class="text-xs font-medium text-[#B42318] mt-1 hidden"></p>
                </div>

                <!-- Input Password -->
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <label for="login-password" class="text-sm font-semibold text-[#0B1B2B]">
                            Kata Sandi <span class="text-[#C8460B]">*</span>
                        </label>
                        <button
                            type="button"
                            onclick="window.JHModal && window.JHModal.open('modal-forgot-password')"
                            class="text-xs font-semibold text-[#1B65A6] hover:underline cursor-pointer"
                        >
                            Lupa kata sandi?
                        </button>
                    </div>
                    <div class="relative">
                        <input
                            type="password"
                            id="login-password"
                            name="password"
                            required
                            placeholder="Minimal 6 karakter"
                            class="w-full min-h-[44px] px-3.5 py-2.5 pr-10 bg-white text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        />
                        <button
                            type="button"
                            data-toggle-password="login-password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#627080] hover:text-[#0B1B2B] cursor-pointer"
                            aria-label="Tampilkan atau sembunyikan kata sandi"
                        >
                            <span data-icon-eye class="hidden">
                                <x-icon name="eye" class="w-4 h-4" />
                            </span>
                            <span data-icon-eye-off>
                                <x-icon name="eye-off" class="w-4 h-4" />
                            </span>
                        </button>
                    </div>
                    <p data-field-error="password" class="text-xs font-medium text-[#B42318] mt-1 hidden"></p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full min-h-[44px] px-5 py-3 rounded-lg text-sm font-bold text-white bg-[#C8460B] hover:bg-[#993406] transition-all shadow-xs btn-press mt-2 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>Masuk ke JobHarbour</span>
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </button>
            </form>
        </div>

        <!-- ============================================================== -->
        <!-- TAB 2: FORM DAFTAR (REGISTER)                                  -->
        <!-- ============================================================== -->
        <div id="register-form-wrap" class="space-y-6 hidden">
            <div>
                <h2 class="text-2xl font-bold text-[#0B1B2B] tracking-tight font-heading">Buat Akun Baru</h2>
                <p class="text-sm text-[#627080] mt-1">Bergabung sebagai pelamar talenta atau rekruter perusahaan.</p>
            </div>

            <form id="register-form" class="space-y-4">
                <!-- Pilihan Peran Akun -->
                <div>
                    <label class="block text-sm font-semibold text-[#0B1B2B] mb-1.5">
                        Pilih Peran Akun <span class="text-[#C8460B]">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-[#C5CFDA] hover:border-[#0C2238] bg-white cursor-pointer has-checked:border-[#C8460B] has-checked:bg-[#FFF3EA]/40">
                            <input type="radio" name="role" value="applicant" checked class="text-[#C8460B] focus:ring-[#C8460B]">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-[#0B1B2B]">Pelamar Kerja</span>
                                <span class="text-[10px] text-[#627080]">Mencari karir & pantau skor</span>
                            </div>
                        </label>
                        <label class="flex items-center gap-2.5 p-3 rounded-xl border border-[#C5CFDA] hover:border-[#0C2238] bg-white cursor-pointer has-checked:border-[#C8460B] has-checked:bg-[#FFF3EA]/40">
                            <input type="radio" name="role" value="recruiter" class="text-[#C8460B] focus:ring-[#C8460B]">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-[#0B1B2B]">Rekruter</span>
                                <span class="text-[10px] text-[#627080]">Terbitkan lowongan & pipeline</span>
                            </div>
                        </label>
                    </div>
                    <p data-field-error="role" class="text-xs font-medium text-[#B42318] mt-1 hidden"></p>
                </div>

                <!-- Nama Lengkap -->
                <div>
                    <label for="reg-name" class="block text-sm font-semibold text-[#0B1B2B] mb-1">
                        Nama Lengkap <span class="text-[#C8460B]">*</span>
                    </label>
                    <input
                        type="text"
                        id="reg-name"
                        name="name"
                        required
                        placeholder="contoh: Budi Wicaksono"
                        class="w-full min-h-[44px] px-3.5 py-2.5 bg-white text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                    />
                    <p data-field-error="name" class="text-xs font-medium text-[#B42318] mt-1 hidden"></p>
                </div>

                <!-- Alamat Email -->
                <div>
                    <label for="reg-email" class="block text-sm font-semibold text-[#0B1B2B] mb-1">
                        Alamat Email <span class="text-[#C8460B]">*</span>
                    </label>
                    <input
                        type="email"
                        id="reg-email"
                        name="email"
                        required
                        placeholder="contoh: nama@domain.com"
                        class="w-full min-h-[44px] px-3.5 py-2.5 bg-white text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                    />
                    <p data-field-error="email" class="text-xs font-medium text-[#B42318] mt-1 hidden"></p>
                </div>

                <!-- Kata Sandi -->
                <div>
                    <label for="reg-password" class="block text-sm font-semibold text-[#0B1B2B] mb-1">
                        Kata Sandi <span class="text-[#C8460B]">*</span>
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="reg-password"
                            name="password"
                            required
                            minlength="6"
                            placeholder="Minimal 6 karakter"
                            class="w-full min-h-[44px] px-3.5 py-2.5 pr-10 bg-white text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        />
                        <button
                            type="button"
                            data-toggle-password="reg-password"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#627080] hover:text-[#0B1B2B] cursor-pointer"
                            aria-label="Tampilkan atau sembunyikan kata sandi"
                        >
                            <span data-icon-eye class="hidden">
                                <x-icon name="eye" class="w-4 h-4" />
                            </span>
                            <span data-icon-eye-off>
                                <x-icon name="eye-off" class="w-4 h-4" />
                            </span>
                        </button>
                    </div>
                    <p data-field-error="password" class="text-xs font-medium text-[#B42318] mt-1 hidden"></p>
                </div>

                <!-- Nomor Telepon & Lokasi -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="reg-phone" class="block text-sm font-semibold text-[#0B1B2B] mb-1">
                            Nomor HP (Opsional)
                        </label>
                        <input
                            type="tel"
                            id="reg-phone"
                            name="phone"
                            placeholder="081234567890"
                            class="w-full min-h-[44px] px-3.5 py-2.5 bg-white text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        />
                    </div>
                    <div>
                        <label for="reg-location" class="block text-sm font-semibold text-[#0B1B2B] mb-1">
                            Kota Domisili (Opsional)
                        </label>
                        <input
                            type="text"
                            id="reg-location"
                            name="location"
                            placeholder="contoh: Tegal, Jawa Tengah"
                            class="w-full min-h-[44px] px-3.5 py-2.5 bg-white text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        />
                    </div>
                </div>

                <!-- Input Keahlian Awal -->
                <div class="space-y-1.5 pt-1">
                    <label class="block text-sm font-semibold text-[#0B1B2B]">
                        Keahlian Utama (Skills Awal)
                    </label>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            id="reg-skill-input"
                            placeholder="Ketik nama skill lalu tekan Enter (misal: PHP, Laravel)"
                            class="flex-1 min-h-[44px] px-3.5 py-2.5 bg-white text-[#0B1B2B] text-sm rounded-lg border border-[#C5CFDA] focus:border-[#C8460B] focus:ring-1 focus:ring-[#C8460B] transition-colors"
                        />
                        <button
                            type="button"
                            id="reg-btn-add-skill"
                            class="px-4 py-2.5 bg-[#F4F6F8] hover:bg-[#E2E7ED] text-[#0B1B2B] text-xs font-bold rounded-lg border border-[#C5CFDA] transition-colors cursor-pointer"
                        >
                            Tambah
                        </button>
                    </div>
                    <!-- Chips Container -->
                    <div id="reg-skills-chips" class="flex flex-wrap gap-1.5 pt-1">
                        <span class="text-xs text-[#627080]">Belum ada keahlian ditambahkan (opsional).</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    class="w-full min-h-[44px] px-5 py-3 rounded-lg text-sm font-bold text-white bg-[#C8460B] hover:bg-[#993406] transition-all shadow-xs btn-press mt-3 cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>Daftar Sekarang</span>
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Informasi Lupa Kata Sandi -->
    <x-modal id="modal-forgot-password" title="Informasi Pemulihan Kata Sandi">
        <div class="space-y-3 text-sm text-[#3D4B5C] leading-relaxed">
            <p>
                Fitur otomatisasi reset kata sandi mandiri via tautan email belum diaktifkan pada spesifikasi REST API layanan ini.
            </p>
            <div class="p-3.5 rounded-xl bg-[#FFF3EA] border border-[#C8460B]/20 text-[#8A2E05] text-xs">
                <strong>Tips Pengujian:</strong> Untuk keperluan evaluasi praktikum, Anda dapat menggunakan akun demo resmi dengan kata sandi baku: <code class="font-bold bg-white px-1.5 py-0.5 rounded border border-[#C8460B]/30">password123</code>.
            </div>
            <p class="text-xs text-[#627080]">
                Jika membutuhkan bantuan reset manual basis data, silakan hubungi tim administrator di <span class="font-semibold text-[#0B1B2B]">admin@jobharbour.id</span>.
            </p>
        </div>
        <x-slot:footer>
            <button
                type="button"
                onclick="window.JHModal && window.JHModal.close('modal-forgot-password')"
                class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#0C2238] hover:bg-[#133150] transition-colors cursor-pointer"
            >
                Mengerti
            </button>
        </x-slot:footer>
    </x-modal>

    @push('scripts')
        @vite('resources/js/pages/auth.js')
    @endpush
</x-layouts.auth>
