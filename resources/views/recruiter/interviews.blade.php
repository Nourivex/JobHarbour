<x-layouts.workspace title="Wawancara - Portal Recruiter JobHarbour" active="interviews" crumb="Wawancara">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Wawancara</h1>
            <p id="iv-lead" class="ws-lead">Jadwal dibuat saat kandidat dimajukan ke tahap Interview.</p>
        </div>
        <div class="ws-seg" role="group" aria-label="Saring jadwal" id="iv-filter">
            <button type="button" data-filter="upcoming" aria-pressed="true">Akan datang</button>
            <button type="button" data-filter="attention" aria-pressed="false">Perlu tindak lanjut</button>
            <button type="button" data-filter="history" aria-pressed="false">Riwayat</button>
        </div>
    </div>

    <div id="iv-list" class="ws-card overflow-hidden" aria-live="polite">
        <div class="p-5 space-y-3"><div class="h-16 rounded-xl animate-skeleton ws-skel"></div><div class="h-16 rounded-xl animate-skeleton ws-skel"></div></div>
    </div>

    @push('modals')
        <dialog id="iv-dialog" class="ws-dialog" aria-labelledby="iv-dialog-title">
            <form novalidate>
                <div class="ws-dialog-head">
                    <div>
                        <h2 id="iv-dialog-title" class="ws-h2">Jadwal ulang wawancara</h2>
                        <p id="iv-dialog-sub" class="ws-meta mt-0.5"></p>
                    </div>
                    <button type="button" class="ws-icon-btn -mr-2" data-dialog-close aria-label="Tutup"><i data-lucide="x"></i></button>
                </div>
                <div class="ws-dialog-body grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="iv-datetime" class="ws-label">Waktu wawancara</label>
                        <input id="iv-datetime" name="datetime" type="datetime-local" class="ws-input">
                        <p class="ws-error" data-error-for="datetime"></p>
                    </div>
                    <div>
                        <label for="iv-type" class="ws-label">Bentuk</label>
                        <select id="iv-type" name="type" class="ws-input"><option value="online">Online</option><option value="offline">Tatap muka</option></select>
                    </div>
                    <div>
                        <label for="iv-interviewer" class="ws-label">Pewawancara <span class="opt">(opsional)</span></label>
                        <input id="iv-interviewer" name="interviewer" type="text" class="ws-input" maxlength="120">
                    </div>
                    <div>
                        <label for="iv-url" class="ws-label">Tautan atau lokasi <span class="opt">(opsional)</span></label>
                        <input id="iv-url" name="meeting_url" type="text" class="ws-input" placeholder="https://meet.google.com/...">
                    </div>
                    <p class="ws-help sm:col-span-2">Kandidat melihat jadwal terbaru di halaman Lamaran Saya.</p>
                </div>
                <div class="ws-dialog-foot">
                    <button type="button" class="ws-btn ws-btn-ghost" data-dialog-close>Batal</button>
                    <button type="submit" class="ws-btn ws-btn-primary">Simpan jadwal</button>
                </div>
            </form>
        </dialog>
    @endpush

    @push('scripts')
        @vite('resources/js/pages/recruiter/interviews.js')
    @endpush
</x-layouts.workspace>
