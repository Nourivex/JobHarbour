<x-layouts.auth title="Masuk atau Daftar - JobHarbour">

    @php
        $ico = [
            'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
            'lock' => '<rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
            'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/>',
            'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1.9.4 1.8.7 2.7a2 2 0 0 1-.5 2.1L8 9.8a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.7.7a2 2 0 0 1 1.7 2z"/>',
            'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
            'tag' => '<path d="M20.6 13.4 13.4 20.6a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8z"/><circle cx="7" cy="7" r="1.5"/>',
            'eye' => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
            'eye-off' => '<path d="M9.9 4.2A10.4 10.4 0 0 1 12 4c6.4 0 10 8 10 8a17.8 17.8 0 0 1-2.2 3.2M6.6 6.6A17.6 17.6 0 0 0 2 12s3.6 8 10 8a10 10 0 0 0 5.4-1.6M2 2l20 20M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
            'check' => '<path d="m5 12 5 5 9-10"/>',
            'login' => '<path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/>',
            'user-plus' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c1.3-3.5 4-5.5 7-5.5s5.7 2 7 5.5M19 8v6M16 11h6"/>',
            'flask' => '<path d="M9 3h6M10 3v6L4.5 18.5A2 2 0 0 0 6.2 21h11.6a2 2 0 0 0 1.7-2.5L14 9V3"/>',
            'building' => '<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M9 22v-4h6v4M8 6h.01M16 6h.01M12 6h.01M8 10h.01M16 10h.01M12 10h.01M8 14h.01M16 14h.01M12 14h.01"/>',
            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/>',
            'back' => '<path d="M19 12H5M11 18l-6-6 6-6"/>',
        ];
        $svg = fn ($name, $cls = 'w-5 h-5') => '<svg class="'.$cls.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$ico[$name].'</svg>';
    @endphp

    <main class="min-h-screen flex items-center justify-center sm:p-6 lg:p-8">
        <div id="auth-shell" data-mode="login" class="auth-shell w-full max-w-[1120px] min-h-screen sm:min-h-0 bg-white sm:rounded-[28px] sm:shadow-[0_30px_70px_-35px_rgba(12,34,56,0.45)] overflow-hidden grid lg:grid-cols-[minmax(0,440px)_minmax(0,1fr)]">

            <!-- ====================================================== -->
            <!-- PANEL VISUAL (desktop)                                 -->
            <!-- ====================================================== -->
            <aside class="auth-visual relative hidden lg:flex flex-col text-white p-10 isolate overflow-hidden min-h-[680px] bg-[#0B2660]">
                <!-- Gambar menempel di bawah selebar panel; area atas diisi warna langit yang sama -->
                <picture class="absolute inset-x-0 bottom-0 -z-20">
                    <source srcset="/images/auth-lighthouse.webp" type="image/webp">
                    <img src="/images/auth-lighthouse.jpg" alt="" width="960" height="1050" class="auth-photo block w-full h-auto">
                </picture>
                <div class="absolute inset-0 -z-10 bg-[linear-gradient(180deg,rgba(8,20,48,0.94)_0%,rgba(8,24,60,0.82)_38%,rgba(8,24,60,0.35)_58%,rgba(8,24,60,0)_72%)]"></div>
                <span class="auth-beacon absolute -z-10 rounded-full pointer-events-none" aria-hidden="true"></span>

                <a href="/" class="inline-flex items-center gap-3 group self-start">
                    <img src="/images/logo-jobharbour.webp" width="256" height="239" alt="" class="w-11 h-auto transition-transform duration-300 group-hover:-rotate-6" aria-hidden="true">
                    <span class="flex flex-col">
                        <span class="font-heading font-extrabold text-2xl leading-none tracking-tight">JobHarbour</span>
                        <span class="text-[11px] font-semibold tracking-[0.14em] uppercase text-[#8CC8F2] pt-1">Pelabuhan Talenta</span>
                    </span>
                </a>

                <span class="mt-10 self-start px-3 py-1 rounded-full text-xs font-semibold bg-[#1B65A6]/35 text-[#CDE6F8] ring-1 ring-[#8CC8F2]/40">Platform Rekrutmen Modern</span>

                <!-- Dua set teks bertumpuk; yang aktif ditentukan oleh data-mode -->
                <div class="mt-4 grid">
                    @php
                        $visuals = [
                            'login' => [
                                'title' => 'Selamat Datang Kembali di <span class="text-[#F26A1B]">JobHarbour</span>',
                                'text' => 'Lanjutkan perjalanan kariermu, temukan lowongan yang sesuai, dan pantau perkembangan lamaranmu di satu tempat.',
                                'items' => [['target', 'Rekomendasi lowongan sesuai skill'], ['chart', 'Pencocokan skill otomatis'], ['briefcase', 'Pantau proses lamaran']],
                            ],
                            'register' => [
                                'title' => 'Mulai Perjalanan Kariermu Bersama <span class="text-[#F26A1B]">JobHarbour</span>',
                                'text' => 'Buat akun, lengkapi skill di profilmu, dan temukan lowongan yang paling sesuai dengan kemampuanmu.',
                                'items' => [['user-plus', 'Bergabung sebagai pelamar atau recruiter'], ['layers', 'Bangun profil skill'], ['zap', 'Lihat skor kecocokan setiap lowongan']],
                            ],
                        ];
                        $featureIco = [
                            'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
                            'chart' => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
                            'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>',
                            'user-plus' => '<circle cx="9" cy="8" r="4"/><path d="M2 21c1.3-3.5 4-5.5 7-5.5s5.7 2 7 5.5M19 8v6M16 11h6"/>',
                            'layers' => '<path d="m12 3 9 5-9 5-9-5 9-5z"/><path d="m3 13 9 5 9-5"/>',
                            'zap' => '<path d="M13 2 3 14h9l-1 8 10-12h-9z"/>',
                        ];
                    @endphp
                    @foreach ($visuals as $mode => $v)
                        <div class="auth-visual-copy [grid-area:1/1]" data-visual="{{ $mode }}" @if ($mode !== 'login') aria-hidden="true" @endif>
                            <h1 class="text-[2.15rem] leading-[1.12] font-extrabold tracking-tight">{!! $v['title'] !!}</h1>
                            <p class="mt-4 text-[15px] leading-relaxed text-[#D5DFEA]">{{ $v['text'] }}</p>
                            <ul class="mt-7 space-y-3.5">
                                @foreach ($v['items'] as [$icon, $label])
                                    <li class="flex items-center gap-3 text-[15px] font-medium">
                                        <span class="w-8 h-8 rounded-lg bg-[#F26A1B]/15 ring-1 ring-[#F26A1B]/35 flex items-center justify-center text-[#FF8A4C]">
                                            <svg class="w-[18px] h-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $featureIco[$icon] !!}</svg>
                                        </span>
                                        {{ $label }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </aside>

            <!-- ====================================================== -->
            <!-- FORM                                                   -->
            <!-- ====================================================== -->
            <section class="flex flex-col px-5 sm:px-10 lg:px-14 pt-6 sm:pt-8 pb-10">

                <!-- Header ponsel: tombol kembali + logo -->
                <div class="lg:hidden relative flex items-center justify-center min-h-11">
                    <a href="/" id="mobile-back" class="absolute left-0 w-11 h-11 -ml-2 rounded-full flex items-center justify-center text-[#0B1B2B] hover:bg-[#F4F6F8]" aria-label="Kembali">{!! $svg('back') !!}</a>
                    <a href="/" class="inline-flex items-center gap-2.5">
                        <img src="/images/logo-jobharbour.webp" width="256" height="239" alt="" class="w-9 h-auto" aria-hidden="true">
                        <span class="flex flex-col">
                            <span class="font-heading font-extrabold text-xl leading-none tracking-tight text-[#0C2238]">JobHarbour</span>
                            <span class="text-[9px] font-semibold tracking-[0.14em] uppercase text-[#1B65A6] pt-0.5">Pelabuhan Talenta</span>
                        </span>
                    </a>
                </div>

                <!-- Tab: penanda bergeser mengikuti tab aktif -->
                <div role="tablist" aria-label="Masuk atau daftar" class="auth-tabs relative hidden sm:grid grid-cols-2 max-w-[460px] w-full mx-auto border-b border-[#E2E7ED]">
                    <button type="button" role="tab" id="tab-login-btn" aria-selected="true" aria-controls="panel-login" class="auth-tab">Masuk ke Akun</button>
                    <button type="button" role="tab" id="tab-register-btn" aria-selected="false" aria-controls="panel-register" tabindex="-1" class="auth-tab">Daftar Baru</button>
                    <span class="auth-tab-indicator" aria-hidden="true"></span>
                </div>

                <div id="auth-panels" class="auth-panels relative w-full max-w-[540px] mx-auto mt-6 sm:mt-8">

                    <!-- ================= MASUK ================= -->
                    <div id="panel-login" role="tabpanel" aria-labelledby="tab-login-btn" class="auth-panel">
                        <div class="text-center sm:text-left">
                            <h2 class="text-[1.9rem] leading-tight font-extrabold text-[#0B1B2B]">Masuk ke JobHarbour</h2>
                            <p class="mt-1.5 text-[15px] text-[#627080]">Gunakan kredensial akunmu untuk melanjutkan.</p>
                        </div>

                        <form id="login-form" class="mt-7 space-y-5" novalidate>
                            <div class="auth-field" data-validate="email">
                                <label for="login-email" class="auth-label">Alamat Email <span class="text-[#C8460B]">*</span></label>
                                <div class="auth-input">
                                    {!! $svg('mail', 'auth-input-icon w-5 h-5') !!}
                                    <input id="login-email" name="email" type="email" autocomplete="email" inputmode="email" placeholder="nama@jobharbour.id" required>
                                    {!! $svg('check', 'auth-valid w-5 h-5') !!}
                                </div>
                                <p data-field-error="email" class="auth-error" role="alert"></p>
                            </div>

                            <div class="auth-field" data-validate="password">
                                <div class="flex items-center justify-between">
                                    <label for="login-password" class="auth-label">Kata Sandi <span class="text-[#C8460B]">*</span></label>
                                    <button type="button" onclick="window.JHModal && window.JHModal.open('modal-forgot-password')" class="text-sm font-semibold text-[#1B65A6] hover:text-[#124B7D] cursor-pointer">Lupa kata sandi?</button>
                                </div>
                                <div class="auth-input">
                                    {!! $svg('lock', 'auth-input-icon w-5 h-5') !!}
                                    <input id="login-password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan kata sandi" required>
                                    <button type="button" class="auth-eye" data-toggle-password="login-password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                                        {!! $svg('eye-off', 'eye-closed w-5 h-5') !!}{!! $svg('eye', 'eye-open w-5 h-5') !!}
                                    </button>
                                </div>
                                <p data-field-error="password" class="auth-error" role="alert"></p>
                            </div>

                            <button type="submit" class="auth-submit btn-press">
                                <span class="auth-submit-label inline-flex items-center gap-2.5">{!! $svg('login') !!} Masuk ke JobHarbour</span>
                            </button>
                        </form>

                        <div class="my-6 flex items-center gap-4 text-sm text-[#627080]"><span class="flex-1 h-px bg-[#E2E7ED]"></span>atau<span class="flex-1 h-px bg-[#E2E7ED]"></span></div>

                        <!-- Akun demo -->
                        <div class="rounded-2xl border border-[#E2E7ED] bg-[#F8FAFC] p-4">
                            <div class="flex flex-col md:flex-row md:items-center gap-4">
                                <button type="button" id="demo-toggle" aria-expanded="false" aria-controls="demo-options" class="md:pointer-events-none flex-1 min-w-0 text-left flex items-center gap-3 cursor-pointer md:cursor-default">
                                    <span class="text-[#5B3FD1]">{!! $svg('flask') !!}</span>
                                    <span class="min-w-0">
                                        <b class="block text-[15px] text-[#0B1B2B]">Coba Akun Demo</b>
                                        <span class="block text-[13px] text-[#627080]">Jelajahi fitur dengan akun pengujian. Sandi: password123</span>
                                    </span>
                                    <svg class="demo-chevron md:hidden ml-auto w-5 h-5 text-[#627080] transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                                </button>
                                <div id="demo-options" class="demo-options grid grid-cols-3 gap-2 md:w-[270px] shrink-0">
                                    @foreach ([['budi@jobharbour.id', 'user', 'Pelamar'], ['recruiter@jobharbour.id', 'building', 'Rekruter'], ['admin@jobharbour.id', 'shield', 'Admin']] as [$email, $icon, $label])
                                        <button type="button" data-demo-email="{{ $email }}" class="demo-btn btn-press flex flex-col items-center gap-1.5 py-3 rounded-xl bg-white border border-[#E2E7ED] text-[13px] font-semibold text-[#0B1B2B] cursor-pointer">
                                            {!! $svg($icon, 'w-5 h-5 text-[#3D4B5C]') !!}{{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <p class="mt-6 text-center text-[15px] text-[#3D4B5C]">Belum punya akun? <button type="button" data-switch="register" class="font-bold text-[#C8460B] hover:text-orange-harbour-hover cursor-pointer">Daftar Baru</button></p>
                    </div>

                    <!-- ================= DAFTAR ================= -->
                    <div id="panel-register" role="tabpanel" aria-labelledby="tab-register-btn" class="auth-panel" hidden>
                        <div class="text-center sm:text-left">
                            <h2 class="text-[1.9rem] leading-tight font-extrabold text-[#0B1B2B]">Buat Akun Baru</h2>
                            <p class="mt-1.5 text-[15px] text-[#627080]">Bergabung sebagai pelamar talenta atau recruiter perusahaan.</p>
                        </div>

                        <form id="register-form" class="mt-6 space-y-4" novalidate>
                            <fieldset>
                                <legend class="auth-label">Pilih Peran Akun <span class="text-[#C8460B]">*</span></legend>
                                <div class="grid grid-cols-2 gap-3">
                                    @foreach ([['applicant', 'user', 'Pelamar Kerja', 'Mencari kerja & kembangkan karier'], ['recruiter', 'building', 'Rekruter', 'Pasang lowongan & rekrut talenta']] as [$value, $icon, $title, $desc])
                                        <label class="role-card">
                                            <input type="radio" name="role" value="{{ $value }}" class="sr-only" @checked($value === 'applicant')>
                                            <span class="role-dot" aria-hidden="true"></span>
                                            <span class="role-icon">{!! $svg($icon) !!}</span>
                                            <span class="min-w-0">
                                                <b class="block text-[15px] leading-tight">{{ $title }}</b>
                                                <span class="hidden sm:block text-xs text-[#627080] mt-0.5">{{ $desc }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                                <p data-field-error="role" class="auth-error" role="alert"></p>
                            </fieldset>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="auth-field" data-validate="required">
                                    <label for="reg-name" class="auth-label">Nama Lengkap <span class="text-[#C8460B]">*</span></label>
                                    <div class="auth-input">
                                        {!! $svg('user', 'auth-input-icon w-5 h-5') !!}
                                        <input id="reg-name" name="name" type="text" autocomplete="name" placeholder="Budi Wicaksono" required>
                                        {!! $svg('check', 'auth-valid w-5 h-5') !!}
                                    </div>
                                    <p data-field-error="name" class="auth-error" role="alert"></p>
                                </div>
                                <div class="auth-field" data-validate="email">
                                    <label for="reg-email" class="auth-label">Alamat Email <span class="text-[#C8460B]">*</span></label>
                                    <div class="auth-input">
                                        {!! $svg('mail', 'auth-input-icon w-5 h-5') !!}
                                        <input id="reg-email" name="email" type="email" autocomplete="email" inputmode="email" placeholder="budi@domain.com" required>
                                        {!! $svg('check', 'auth-valid w-5 h-5') !!}
                                    </div>
                                    <p data-field-error="email" class="auth-error" role="alert"></p>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4 items-start">
                                <div class="auth-field" data-validate="newPassword">
                                    <label for="reg-password" class="auth-label">Kata Sandi <span class="text-[#C8460B]">*</span></label>
                                    <div class="auth-input">
                                        {!! $svg('lock', 'auth-input-icon w-5 h-5') !!}
                                        <input id="reg-password" name="password" type="password" autocomplete="new-password" placeholder="Minimal 8 karakter" required minlength="8" aria-describedby="pw-strength-label">
                                        <button type="button" class="auth-eye" data-toggle-password="reg-password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                                            {!! $svg('eye-off', 'eye-closed w-5 h-5') !!}{!! $svg('eye', 'eye-open w-5 h-5') !!}
                                        </button>
                                    </div>
                                    <p data-field-error="password" class="auth-error" role="alert"></p>
                                </div>
                                <div>
                                    <p class="auth-label">Kekuatan Kata Sandi</p>
                                    <div class="flex items-center gap-3 h-12">
                                        <div id="pw-strength" class="pw-strength flex-1 grid grid-cols-4 gap-1.5" data-level="0">
                                            <span></span><span></span><span></span><span></span>
                                        </div>
                                        <span id="pw-strength-label" class="w-[84px] text-right text-sm font-bold text-[#8C98A8]" aria-live="polite">Belum diisi</span>
                                    </div>
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div class="auth-field" data-validate="phone">
                                    <label for="reg-phone" class="auth-label">Nomor HP <span class="font-normal text-[#627080]">(Opsional)</span></label>
                                    <div class="auth-input">
                                        {!! $svg('phone', 'auth-input-icon w-5 h-5') !!}
                                        <input id="reg-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="081234567890">
                                    </div>
                                    <p data-field-error="phone" class="auth-error" role="alert"></p>
                                </div>
                                <div class="auth-field">
                                    <label for="reg-location" class="auth-label">Kota Domisili <span class="font-normal text-[#627080]">(Opsional)</span></label>
                                    <div class="auth-input">
                                        {!! $svg('pin', 'auth-input-icon w-5 h-5') !!}
                                        <input id="reg-location" name="location" type="text" list="city-suggestions" autocomplete="address-level2" placeholder="Contoh: Tegal">
                                    </div>
                                    <datalist id="city-suggestions"></datalist>
                                    <p data-field-error="location" class="auth-error" role="alert"></p>
                                </div>
                            </div>

                            <div class="auth-field">
                                <label for="reg-skill-input" class="auth-label">Keahlian Utama <span class="font-normal text-[#627080]">(Opsional, tekan Enter untuk menambah)</span></label>
                                <div class="auth-input auth-input-chips flex-wrap py-1.5 min-h-12 h-auto" id="reg-skills-box">
                                    {!! $svg('tag', 'auth-input-icon w-5 h-5') !!}
                                    <div id="reg-skills-chips" class="contents"></div>
                                    <input id="reg-skill-input" type="text" list="skill-suggestions" placeholder="Tambah skill, mis. Laravel" class="min-w-[160px] flex-1">
                                </div>
                                <datalist id="skill-suggestions"></datalist>
                                <p data-field-error="skills" class="auth-error" role="alert"></p>
                            </div>

                            <label class="flex items-start gap-3 text-sm text-[#3D4B5C] cursor-pointer">
                                <input id="reg-terms" type="checkbox" class="auth-check mt-0.5" required>
                                <span>Saya menyetujui <b class="text-[#0B1B2B]">syarat penggunaan</b> dan pemrosesan data JobHarbour untuk keperluan rekrutmen.</span>
                            </label>
                            <p data-field-error="terms" class="auth-error -mt-2" role="alert"></p>

                            <button type="submit" class="auth-submit btn-press">
                                <span class="auth-submit-label inline-flex items-center gap-2.5">{!! $svg('user-plus') !!} Daftar Sekarang</span>
                            </button>
                        </form>

                        <p class="mt-6 text-center text-[15px] text-[#3D4B5C]">Sudah punya akun? <button type="button" data-switch="login" class="font-bold text-[#C8460B] hover:text-orange-harbour-hover cursor-pointer">Masuk</button></p>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Modal Informasi Lupa Kata Sandi -->
    <x-modal id="modal-forgot-password" title="Informasi Pemulihan Kata Sandi">
        <div class="space-y-3 text-sm text-[#3D4B5C] leading-relaxed">
            <p>
                Fitur reset kata sandi mandiri lewat email belum tersedia pada REST API layanan ini.
            </p>
            <div class="p-3.5 rounded-xl bg-[#FFF3EA] border border-[#C8460B]/20 text-[#8A2E05] text-xs">
                <strong>Tips Pengujian:</strong> Untuk keperluan evaluasi, gunakan akun demo dengan kata sandi <code class="font-bold bg-white px-1.5 py-0.5 rounded border border-[#C8460B]/30">password123</code>.
            </div>
            <p class="text-xs text-[#627080]">
                Jika membutuhkan reset manual, hubungi administrator di <span class="font-semibold text-[#0B1B2B]">admin@jobharbour.id</span>.
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
