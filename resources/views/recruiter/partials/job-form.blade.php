{{-- Dialog buat/edit lowongan, dipakai di Dasbor, Lowongan, dan Detail Lowongan --}}
<dialog id="job-dialog" class="ws-dialog ws-dialog-lg" aria-labelledby="job-dialog-title">
    <form id="job-form" class="contents" novalidate>
        <div class="ws-dialog-head">
            <div>
                <h2 id="job-dialog-title" class="text-lg font-bold">Lowongan baru</h2>
                <p id="job-dialog-sub" class="ws-meta mt-0.5">Isi detail posisi. Simpan sebagai draft bila belum siap dipublikasikan.</p>
            </div>
            <button type="button" class="ws-icon-btn -mr-2" data-dialog-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>

        <div class="ws-dialog-body space-y-7">
            <div id="job-form-alert" class="hidden"></div>

            <section class="space-y-4">
                <h3 class="ws-h3">Informasi dasar</h3>
                <div>
                    <label for="jf-title" class="ws-label">Judul posisi</label>
                    <input id="jf-title" name="title" type="text" maxlength="255" class="ws-input" placeholder="contoh: Backend Engineer (Laravel)" autocomplete="off">
                    <p class="ws-error" data-error-for="title"></p>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="jf-company" class="ws-label">Perusahaan</label>
                        <input id="jf-company" name="company" type="text" maxlength="255" class="ws-input">
                        <p class="ws-help" id="jf-company-help">Diambil dari profil perusahaan.</p>
                        <p class="ws-error" data-error-for="company"></p>
                    </div>
                    <div>
                        <label for="jf-location" class="ws-label">Kota penempatan</label>
                        <input id="jf-location" name="location" type="text" maxlength="100" class="ws-input" placeholder="contoh: Tegal">
                        <p class="ws-error" data-error-for="location"></p>
                    </div>
                    <div>
                        <label for="jf-type" class="ws-label">Tipe pekerjaan</label>
                        <select id="jf-type" name="employment_type" class="ws-input">
                            <option value="full-time">Full-time</option>
                            <option value="part-time">Part-time</option>
                            <option value="contract">Kontrak</option>
                            <option value="internship">Magang</option>
                            <option value="remote">Remote</option>
                        </select>
                    </div>
                    <div>
                        <label for="jf-expires" class="ws-label">Batas lamaran <span class="opt">(opsional)</span></label>
                        <input id="jf-expires" name="expires_at" type="date" class="ws-input">
                        <p class="ws-error" data-error-for="expires_at"></p>
                    </div>
                </div>
            </section>

            <section class="space-y-4">
                <h3 class="ws-h3">Gaji dan kuota</h3>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label for="jf-salary-min" class="ws-label">Gaji minimum <span class="opt">(Rp)</span></label>
                        <input id="jf-salary-min" name="salary_min" type="number" min="0" step="100000" inputmode="numeric" class="ws-input ws-num" placeholder="8000000">
                        <p class="ws-error" data-error-for="salary_min"></p>
                    </div>
                    <div>
                        <label for="jf-salary-max" class="ws-label">Gaji maksimum <span class="opt">(Rp)</span></label>
                        <input id="jf-salary-max" name="salary_max" type="number" min="0" step="100000" inputmode="numeric" class="ws-input ws-num" placeholder="15000000">
                        <p class="ws-error" data-error-for="salary_max"></p>
                    </div>
                    <div>
                        <label for="jf-quota" class="ws-label">Kuota</label>
                        <input id="jf-quota" name="quota" type="number" min="1" value="1" inputmode="numeric" class="ws-input ws-num">
                        <p class="ws-error" data-error-for="quota"></p>
                    </div>
                </div>
            </section>

            <section class="space-y-3">
                <div>
                    <h3 class="ws-h3">Syarat skill</h3>
                    <p class="ws-meta mt-0.5">Tingkat menentukan bobot dalam skor kecocokan: Wajib 3, Penting 2, Bonus 1.</p>
                </div>
                <div id="jf-reqs" class="space-y-2"></div>
                <datalist id="jf-skill-list"></datalist>
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <button type="button" id="jf-add-req" class="ws-btn ws-btn-ghost ws-btn-sm"><i data-lucide="plus"></i>Tambah skill</button>
                    <p id="jf-weight-text" class="ws-meta ws-num"></p>
                </div>
                <div id="jf-weight-bar" class="ws-weight" aria-hidden="true"></div>
                <p class="ws-error" data-error-for="requirements"></p>
            </section>

            <section class="space-y-2">
                <label for="jf-description" class="ws-h3 block">Deskripsi pekerjaan</label>
                <textarea id="jf-description" name="description" rows="6" class="ws-input" placeholder="Tanggung jawab, kualifikasi, dan benefit posisi ini."></textarea>
                <p class="ws-error" data-error-for="description"></p>
            </section>
        </div>

        <div class="ws-dialog-foot">
            <p id="jf-trial-hint" class="ws-meta w-full hidden"></p>
            <button type="button" class="ws-btn ws-btn-ghost mr-auto" data-dialog-close>Batal</button>
            <button type="submit" class="ws-btn ws-btn-secondary" data-submit="draft">Simpan draft</button>
            <span id="jf-publish-wrap">
                <button type="submit" class="ws-btn ws-btn-primary" data-submit="open"><i data-lucide="send"></i>Publikasikan</button>
            </span>
        </div>
    </form>

    {{-- Konfirmasi setelah lowongan baru tersimpan; diisi oleh job-form.js --}}
    <div id="job-success" class="ws-success" hidden></div>
</dialog>
