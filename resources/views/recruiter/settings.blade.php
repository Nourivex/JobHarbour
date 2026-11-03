<x-layouts.workspace title="Pengaturan - Portal Recruiter JobHarbour" active="settings" crumb="Pengaturan">
    <div class="ws-page-head">
        <div>
            <h1 class="ws-h1">Pengaturan</h1>
            <p class="ws-lead">Kelola informasi akun, kata sandi, dan tampilan portal.</p>
        </div>
    </div>

    <div class="ws-tabs mb-6" role="tablist" aria-label="Bagian pengaturan" id="st-tabs">
        <button type="button" role="tab" class="ws-tab" id="st-tab-account" aria-controls="st-account" aria-selected="true"><i data-lucide="user-round" class="w-4 h-4"></i>Akun</button>
        <button type="button" role="tab" class="ws-tab" id="st-tab-security" aria-controls="st-security" aria-selected="false" tabindex="-1"><i data-lucide="key-round" class="w-4 h-4"></i>Keamanan</button>
        <button type="button" role="tab" class="ws-tab" id="st-tab-appearance" aria-controls="st-appearance" aria-selected="false" tabindex="-1"><i data-lucide="palette" class="w-4 h-4"></i>Tampilan</button>
    </div>

    <!-- Akun -->
    <section id="st-account" role="tabpanel" aria-labelledby="st-tab-account" class="max-w-3xl">
        <form id="st-account-form" class="ws-card ws-card-body space-y-5" novalidate>
            <div class="flex items-center gap-4">
                <span id="st-avatar" class="ws-avatar w-14 h-14 text-lg">?</span>
                <div>
                    <p id="st-display-name" class="font-semibold">-</p>
                    <p id="st-meta" class="ws-meta">-</p>
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="st-name" class="ws-label">Nama lengkap</label>
                    <input id="st-name" name="name" type="text" maxlength="255" class="ws-input" autocomplete="name">
                    <p class="ws-error" data-error-for="name"></p>
                </div>
                <div>
                    <label for="st-email" class="ws-label">Email login</label>
                    <input id="st-email" type="email" class="ws-input" readonly aria-describedby="st-email-help">
                    <p id="st-email-help" class="ws-help">Email dipakai untuk masuk dan tidak bisa diubah di sini.</p>
                </div>
                <div>
                    <label for="st-phone" class="ws-label">Telepon <span class="opt">(opsional)</span></label>
                    <input id="st-phone" name="phone" type="tel" maxlength="30" class="ws-input" autocomplete="tel">
                    <p class="ws-error" data-error-for="phone"></p>
                </div>
                <div>
                    <label for="st-location" class="ws-label">Kota <span class="opt">(opsional)</span></label>
                    <input id="st-location" name="location" type="text" maxlength="100" class="ws-input" autocomplete="address-level2">
                    <p class="ws-error" data-error-for="location"></p>
                </div>
                <div class="sm:col-span-2">
                    <label for="st-bio" class="ws-label">Jabatan atau ringkasan singkat <span class="opt">(opsional)</span></label>
                    <textarea id="st-bio" name="bio" rows="3" maxlength="1000" class="ws-input" placeholder="contoh: Talent Acquisition Lead"></textarea>
                    <p class="ws-error" data-error-for="bio"></p>
                </div>
            </div>
            <div class="flex justify-end pt-4 border-t border-[var(--ws-border)]">
                <button type="submit" class="ws-btn ws-btn-primary">Simpan perubahan</button>
            </div>
        </form>
    </section>

    <!-- Keamanan -->
    <section id="st-security" role="tabpanel" aria-labelledby="st-tab-security" class="max-w-3xl hidden">
        <form id="st-password-form" class="ws-card ws-card-body space-y-5" novalidate>
            <div>
                <h2 class="ws-h2">Ganti kata sandi</h2>
                <p class="ws-meta mt-0.5">Gunakan minimal 8 karakter, campurkan huruf besar, angka, atau simbol.</p>
            </div>
            <div>
                <label for="st-current" class="ws-label">Kata sandi saat ini</label>
                <input id="st-current" name="current_password" type="password" class="ws-input" autocomplete="current-password">
                <p class="ws-error" data-error-for="current_password"></p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label for="st-new" class="ws-label">Kata sandi baru</label>
                    <input id="st-new" name="password" type="password" class="ws-input" autocomplete="new-password">
                    <div class="mt-2 flex gap-1" aria-hidden="true" id="st-strength">
                        <span class="h-1.5 flex-1 rounded-full bg-[var(--ws-surface-2)]"></span><span class="h-1.5 flex-1 rounded-full bg-[var(--ws-surface-2)]"></span><span class="h-1.5 flex-1 rounded-full bg-[var(--ws-surface-2)]"></span><span class="h-1.5 flex-1 rounded-full bg-[var(--ws-surface-2)]"></span>
                    </div>
                    <p id="st-strength-text" class="ws-help" aria-live="polite"></p>
                    <p class="ws-error" data-error-for="password"></p>
                </div>
                <div>
                    <label for="st-confirm" class="ws-label">Ulangi kata sandi baru</label>
                    <input id="st-confirm" name="password_confirmation" type="password" class="ws-input" autocomplete="new-password">
                    <p class="ws-error" data-error-for="password_confirmation"></p>
                </div>
            </div>
            <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" id="st-show" class="w-4 h-4 accent-[var(--ws-primary)]">Tampilkan kata sandi</label>
            <div class="flex justify-end pt-4 border-t border-[var(--ws-border)]">
                <button type="submit" class="ws-btn ws-btn-primary">Ganti kata sandi</button>
            </div>
        </form>
    </section>

    <!-- Tampilan -->
    <section id="st-appearance" role="tabpanel" aria-labelledby="st-tab-appearance" class="max-w-3xl hidden">
        <div class="ws-card ws-card-body space-y-4">
            <div>
                <h2 class="ws-h2">Tema</h2>
                <p class="ws-meta mt-0.5">Disimpan di perangkat ini.</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-3" role="radiogroup" aria-label="Tema tampilan">
                <label class="flex items-center gap-3 rounded-xl border border-[var(--ws-border)] p-4 cursor-pointer has-[:checked]:border-[var(--ws-accent)] has-[:checked]:shadow-[0_0_0_3px_rgba(244,91,11,0.15)]">
                    <input type="radio" name="theme" value="light" class="w-4 h-4 accent-[var(--ws-primary)]">
                    <i data-lucide="sun" class="w-5 h-5"></i><span class="font-medium">Terang</span>
                </label>
                <label class="flex items-center gap-3 rounded-xl border border-[var(--ws-border)] p-4 cursor-pointer has-[:checked]:border-[var(--ws-accent)] has-[:checked]:shadow-[0_0_0_3px_rgba(244,91,11,0.15)]">
                    <input type="radio" name="theme" value="dark" class="w-4 h-4 accent-[var(--ws-primary)]">
                    <i data-lucide="moon" class="w-5 h-5"></i><span class="font-medium">Gelap</span>
                </label>
            </div>
        </div>
    </section>

    @push('scripts')
        @vite('resources/js/pages/recruiter/settings.js')
    @endpush
</x-layouts.workspace>
