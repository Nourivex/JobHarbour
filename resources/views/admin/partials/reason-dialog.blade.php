{{-- Dialog konfirmasi tindakan admin yang berdampak; diisi oleh resources/js/admin/ui.js (confirmAction) --}}
<dialog id="adm-reason" class="ws-dialog" aria-labelledby="adm-reason-title">
    <form novalidate>
        <div class="ws-dialog-head">
            <div class="min-w-0">
                <h2 id="adm-reason-title" class="text-lg font-bold">Konfirmasi</h2>
                <p id="adm-reason-sub" class="ws-meta mt-0.5"></p>
            </div>
            <button type="button" class="ws-icon-btn -mr-2" data-dialog-close aria-label="Tutup"><i data-lucide="x"></i></button>
        </div>
        <div class="ws-dialog-body space-y-3">
            <div id="adm-reason-impact" class="ws-callout"></div>
            <div id="adm-reason-field">
                <label for="adm-reason-text" class="ws-label">Alasan <span id="adm-reason-opt" class="opt">(opsional)</span></label>
                <textarea id="adm-reason-text" name="reason" rows="3" maxlength="1000" class="ws-input"></textarea>
                <p class="ws-error" data-error-for="reason"></p>
                <p class="ws-help">Dicatat di Log Audit beserta nama admin dan waktunya.</p>
            </div>
        </div>
        <div class="ws-dialog-foot">
            <button type="button" class="ws-btn ws-btn-ghost" data-dialog-close>Batal</button>
            <button type="submit" id="adm-reason-submit" class="ws-btn ws-btn-danger">Lanjutkan</button>
        </div>
    </form>
</dialog>
