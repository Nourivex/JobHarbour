<x-layouts.admin title="Pengumuman - Admin Portal JobHarbour" active="announcements" crumb="Pengumuman">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Pengumuman situs</h1>
            <p class="ws-lead">Banner singkat di bagian atas situs dan portal, misalnya jadwal pemeliharaan. Pengguna bisa menutupnya, jadi tulis yang penting saja.</p>
        </div>
    </div>

    <div class="adm-split">
        <section class="ws-card" aria-labelledby="an-form-title">
            <div class="ws-card-head"><h2 id="an-form-title" class="ws-h2">Pengumuman baru</h2></div>
            <form id="an-form" class="ws-card-body space-y-4" novalidate>
                <div>
                    <label for="an-message" class="ws-label">Isi pengumuman</label>
                    <textarea id="an-message" name="message" rows="3" maxlength="240" class="ws-input" placeholder="contoh: Pemeliharaan sistem Sabtu, 8 November pukul 22.00 sampai 23.00 WIB. Login mungkin terganggu sebentar."></textarea>
                    <p class="ws-help flex justify-between"><span>Minimal 10 karakter.</span><span id="an-count" class="ws-num">0/240</span></p>
                    <p class="ws-error" data-error-for="message"></p>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <fieldset>
                        <legend class="ws-label">Jenis</legend>
                        <div class="ws-seg" role="radiogroup">
                            <label class="cursor-pointer"><input type="radio" name="level" value="info" class="sr-only peer" checked><span class="ws-seg-opt">Informasi</span></label>
                            <label class="cursor-pointer"><input type="radio" name="level" value="warning" class="sr-only peer"><span class="ws-seg-opt">Peringatan</span></label>
                        </div>
                    </fieldset>
                    <div>
                        <label for="an-audience" class="ws-label">Ditampilkan untuk</label>
                        <select id="an-audience" name="audience" class="ws-input">
                            <option value="all">Semua pengunjung</option>
                            <option value="applicant">Pelamar yang masuk</option>
                            <option value="recruiter">Recruiter</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="an-ends" class="ws-label">Berakhir otomatis <span class="opt">(opsional)</span></label>
                    <input id="an-ends" name="ends_at" type="datetime-local" class="ws-input">
                    <p class="ws-error" data-error-for="ends_at"></p>
                </div>
                <div>
                    <p class="ws-label">Pratinjau</p>
                    <div id="an-preview" class="rounded-xl overflow-hidden border border-[var(--ws-border)]"></div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="ws-btn ws-btn-primary"><i data-lucide="megaphone"></i>Terbitkan</button>
                </div>
            </form>
        </section>

        <section class="ws-card" aria-labelledby="an-list-title">
            <div class="ws-card-head"><h2 id="an-list-title" class="ws-h2">Riwayat pengumuman</h2></div>
            <ul id="an-list" class="divide-y divide-[var(--ws-border)]" aria-live="polite"></ul>
        </section>
    </div>

    @push('scripts')
        @vite('resources/js/pages/admin/announcements.js')
    @endpush
</x-layouts.admin>
