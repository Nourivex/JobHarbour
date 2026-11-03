<x-layouts.workspace title="Periksa Verifikasi - Admin JobHarbour" active="admin-verifications" crumb="Periksa Verifikasi">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Periksa Verifikasi</h1>
            <p class="ws-lead">Setujui perusahaan yang dokumennya valid. Penolakan wajib disertai alasan agar recruiter tahu apa yang perlu diperbaiki.</p>
        </div>
    </div>

    <div class="ws-card">
        <div class="px-4 sm:px-5 pt-2">
            <div class="ws-tabs" role="tablist" aria-label="Status pengajuan" id="av-tabs">
                <button type="button" role="tab" class="ws-tab" data-status="pending" aria-selected="true">Menunggu <span class="ws-count" data-count="pending">0</span></button>
                <button type="button" role="tab" class="ws-tab" data-status="approved" aria-selected="false">Disetujui <span class="ws-count" data-count="approved">0</span></button>
                <button type="button" role="tab" class="ws-tab" data-status="revision_required" aria-selected="false">Perlu revisi <span class="ws-count" data-count="revision_required">0</span></button>
                <button type="button" role="tab" class="ws-tab" data-status="rejected" aria-selected="false">Ditolak <span class="ws-count" data-count="rejected">0</span></button>
            </div>
        </div>
        <div id="av-list" class="p-4 sm:p-5" aria-live="polite"></div>
    </div>

    @push('modals')
        <dialog id="av-dialog" class="ws-dialog ws-dialog-lg" aria-labelledby="av-d-title">
            <div class="ws-dialog-head">
                <div class="flex items-center gap-3 min-w-0">
                    <span id="av-d-logo"></span>
                    <div class="min-w-0">
                        <h2 id="av-d-title" class="text-lg font-bold truncate">Perusahaan</h2>
                        <p id="av-d-sub" class="ws-meta truncate"></p>
                    </div>
                </div>
                <button type="button" class="ws-icon-btn -mr-2" data-dialog-close aria-label="Tutup"><i data-lucide="x"></i></button>
            </div>
            <div id="av-d-body" class="ws-dialog-body space-y-5"></div>
            <div id="av-d-foot" class="ws-dialog-foot"></div>
        </dialog>
    @endpush

    @push('scripts')
        @vite('resources/js/pages/recruiter/admin-verifications.js')
    @endpush
</x-layouts.workspace>
