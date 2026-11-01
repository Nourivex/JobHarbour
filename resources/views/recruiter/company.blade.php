<x-layouts.workspace title="Profil Perusahaan - Portal Recruiter JobHarbour" active="company" crumb="Profil Perusahaan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Profil Perusahaan</h1>
            <p class="ws-lead">Informasi ini tampil di lowonganmu dan diperiksa admin saat verifikasi.</p>
        </div>
        <div id="co-status"></div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px] items-start">
        <form id="co-form" class="ws-card ws-card-body ws-cq" novalidate>
            <div id="co-alert" class="hidden mb-5"></div>

            <div class="ws-form-section">
                <div>
                    <h2 class="ws-form-section-title">Identitas</h2>
                    <p class="ws-form-section-text">Nama dan logo yang dilihat pelamar.</p>
                </div>
                <div class="space-y-4">
                    <div class="flex items-center gap-4">
                        <span id="co-logo-preview" class="ws-logo ws-logo-fallback w-16 h-16 text-xl">?</span>
                        <div>
                            <label for="co-logo" class="ws-btn ws-btn-secondary ws-btn-sm cursor-pointer"><i data-lucide="image-up"></i>Unggah logo</label>
                            <input id="co-logo" type="file" accept="image/png,image/jpeg,image/webp" class="sr-only">
                            <p class="ws-help">PNG, JPG, atau WEBP, maksimal 1 MB. Logo bisa diunggah setelah profil disimpan.</p>
                            <p class="ws-error" data-error-for="logo"></p>
                        </div>
                    </div>
                    <div>
                        <label for="co-name" class="ws-label">Nama perusahaan</label>
                        <input id="co-name" name="name" type="text" maxlength="255" class="ws-input" placeholder="contoh: PT Pelabuhan Digital Nusantara" autocomplete="organization">
                        <p id="co-name-help" class="ws-help hidden"></p>
                        <p class="ws-error" data-error-for="name"></p>
                    </div>
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-1">
                            <label for="co-industry" class="ws-label">Industri</label>
                            <input id="co-industry" name="industry" type="text" maxlength="100" class="ws-input" list="co-industry-list" placeholder="Teknologi Informasi">
                            <datalist id="co-industry-list">
                                @foreach (['Teknologi Informasi', 'Keuangan dan Perbankan', 'Manufaktur', 'Logistik dan Maritim', 'Pendidikan', 'Kesehatan', 'Ritel', 'Agribisnis', 'Konstruksi', 'Media dan Kreatif', 'Pemerintahan', 'Lainnya'] as $industry)
                                    <option value="{{ $industry }}"></option>
                                @endforeach
                            </datalist>
                            <p class="ws-error" data-error-for="industry"></p>
                        </div>
                        <div>
                            <label for="co-size" class="ws-label">Jumlah karyawan</label>
                            <select id="co-size" name="size" class="ws-input">
                                <option value="">Pilih</option>
                                @foreach (['1-10', '11-50', '51-200', '201-500', '501-1000', '1000+'] as $size)
                                    <option value="{{ $size }}">{{ $size }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="co-founded" class="ws-label">Tahun berdiri</label>
                            <input id="co-founded" name="founded_year" type="number" min="1800" max="{{ now()->year }}" inputmode="numeric" class="ws-input ws-num" placeholder="2018">
                            <p class="ws-error" data-error-for="founded_year"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ws-form-section">
                <div>
                    <h2 class="ws-form-section-title">Tentang perusahaan</h2>
                    <p class="ws-form-section-text">Ceritakan apa yang dikerjakan perusahaan dan budaya kerjanya.</p>
                </div>
                <div>
                    <label for="co-description" class="ws-label">Deskripsi</label>
                    <textarea id="co-description" name="description" rows="6" maxlength="3000" class="ws-input" placeholder="Bidang usaha, produk atau layanan, dan suasana kerja."></textarea>
                    <p class="ws-help"><span id="co-desc-count">0</span>/3000 karakter</p>
                    <p class="ws-error" data-error-for="description"></p>
                </div>
            </div>

            <div class="ws-form-section">
                <div>
                    <h2 class="ws-form-section-title">Alamat dan kontak</h2>
                    <p class="ws-form-section-text">Dipakai admin untuk memastikan perusahaan benar ada.</p>
                </div>
                <div class="space-y-4">
                    <div>
                        <label for="co-address" class="ws-label">Alamat kantor</label>
                        <input id="co-address" name="address" type="text" maxlength="255" class="ws-input" autocomplete="street-address">
                        <p class="ws-error" data-error-for="address"></p>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="co-city" class="ws-label">Kota</label>
                            <input id="co-city" name="city" type="text" maxlength="100" class="ws-input" autocomplete="address-level2">
                            <p class="ws-error" data-error-for="city"></p>
                        </div>
                        <div>
                            <label for="co-website" class="ws-label">Website <span class="opt">(opsional)</span></label>
                            <input id="co-website" name="website" type="url" maxlength="255" class="ws-input" placeholder="https://" autocomplete="url">
                            <p class="ws-error" data-error-for="website"></p>
                        </div>
                        <div>
                            <label for="co-email" class="ws-label">Email rekrutmen</label>
                            <input id="co-email" name="email" type="email" maxlength="255" class="ws-input" placeholder="hr@perusahaan.id" autocomplete="email">
                            <p class="ws-error" data-error-for="email"></p>
                        </div>
                        <div>
                            <label for="co-phone" class="ws-label">Telepon</label>
                            <input id="co-phone" name="phone" type="tel" maxlength="30" class="ws-input" autocomplete="tel">
                            <p class="ws-error" data-error-for="phone"></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 pt-5 border-t border-[var(--ws-border)]">
                <p id="co-dirty" class="ws-meta mr-auto hidden">Ada perubahan yang belum disimpan.</p>
                <button type="submit" id="co-submit" class="ws-btn ws-btn-primary">Simpan profil</button>
            </div>
        </form>

        <aside class="space-y-6 lg:sticky lg:top-[5.5rem]">
            <section class="ws-card ws-card-body" aria-labelledby="co-ready-title">
                <h2 id="co-ready-title" class="ws-h2">Kesiapan verifikasi</h2>
                <p class="ws-meta mt-0.5 mb-4">Kolom berikut wajib terisi sebelum mengajukan verifikasi.</p>
                <ul id="co-checklist" class="space-y-2"></ul>
                <a id="co-verify-link" href="/recruiter/verification" class="ws-btn ws-btn-secondary w-full mt-5">Ke halaman verifikasi<i data-lucide="arrow-right"></i></a>
            </section>
            <section class="ws-card ws-card-body" aria-labelledby="co-preview-title">
                <h2 id="co-preview-title" class="ws-h2 mb-4">Pratinjau di lowongan</h2>
                <div id="co-preview"></div>
            </section>
        </aside>
    </div>

    @push('scripts')
        @vite('resources/js/pages/recruiter/company.js')
    @endpush
</x-layouts.workspace>
