@php
    $nav = [
        ['key' => 'dashboard', 'href' => '/recruiter', 'icon' => 'layout-dashboard', 'label' => 'Dasbor'],
        ['key' => 'jobs', 'href' => '/recruiter/jobs', 'icon' => 'briefcase-business', 'label' => 'Lowongan'],
        ['key' => 'pipeline', 'href' => '/recruiter/pipeline', 'icon' => 'users', 'label' => 'Kandidat'],
        ['key' => 'interviews', 'href' => '/recruiter/interviews', 'icon' => 'calendar-days', 'label' => 'Wawancara'],
        ['key' => 'analytics', 'href' => '/recruiter/analytics', 'icon' => 'chart-column', 'label' => 'Analitik'],
    ];
    $companyNav = [
        ['key' => 'company', 'href' => '/recruiter/company', 'icon' => 'building-2', 'label' => 'Profil Perusahaan'],
        ['key' => 'verification', 'href' => '/recruiter/verification', 'icon' => 'shield-check', 'label' => 'Verifikasi', 'badge' => true],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Portal Recruiter - JobHarbour' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/images/favicon-jobharbour.ico" sizes="any">
    <script>try { var t = localStorage.getItem('jh_theme'); if (t === 'dark') document.documentElement.classList.add('dark'); if (localStorage.getItem('jh_ws_collapsed') === '1') document.documentElement.classList.add('ws-collapsed'); } catch (e) {}</script>

    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="ws-body h-full">
    <a href="#ws-main" class="ws-skip">Lewati ke konten</a>

    <div class="ws-shell">
        <!-- ============================================================ -->
        <!-- SIDEBAR                                                      -->
        <!-- ============================================================ -->
        <aside id="ws-sidebar" class="ws-sidebar" aria-label="Navigasi portal recruiter">
            <div class="ws-brand">
                <a href="/recruiter" class="flex items-center gap-2.5 min-w-0" aria-label="JobHarbour, ke dasbor">
                    <img src="/images/logo-icon-256.png" alt="" class="w-8 h-8 rounded-lg">
                    <span class="ws-brand-text min-w-0">
                        <span class="block font-heading font-extrabold text-[17px] text-white leading-tight">JobHarbour</span>
                        <span class="block text-[11px] font-semibold tracking-wide text-[#8CC8F2]">Portal Recruiter</span>
                    </span>
                </a>
                <button type="button" class="ws-icon-btn ws-collapse-btn w-8 h-8" data-sidebar-collapse aria-label="Ringkas sidebar" aria-pressed="false" title="Ringkas sidebar">
                    <i data-lucide="chevrons-left"></i>
                </button>
                <button type="button" class="ws-icon-btn ws-sidebar-close lg:hidden" data-sidebar-close aria-label="Tutup menu">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <!-- Kartu perusahaan dan status verifikasi -->
            <a href="/recruiter/company" id="ws-company-card" class="ws-company-card">
                <span id="ws-company-logo" class="ws-company-logo" aria-hidden="true">?</span>
                <span class="min-w-0 flex-1">
                    <span id="ws-company-name" class="block truncate text-sm font-semibold text-white">Memuat perusahaan...</span>
                    <span id="ws-company-status" class="ws-status-text">&nbsp;</span>
                </span>
            </a>

            <nav class="ws-nav">
                <p class="ws-nav-label">Rekrutmen</p>
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}" class="ws-nav-link" title="{{ $item['label'] }}" @if (($active ?? '') === $item['key']) aria-current="page" @endif>
                        <i data-lucide="{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                        @if ($item['key'] === 'pipeline')
                            <span id="ws-nav-review" class="ws-nav-count hidden" title="Lamaran perlu ditinjau"></span>
                        @endif
                        @if ($item['key'] === 'interviews')
                            <span id="ws-nav-interviews" class="ws-nav-count hidden" title="Wawancara 7 hari ke depan"></span>
                        @endif
                    </a>
                @endforeach

                <div id="ws-company-nav">
                <p class="ws-nav-label">Perusahaan</p>
                @foreach ($companyNav as $item)
                    <a href="{{ $item['href'] }}" class="ws-nav-link" title="{{ $item['label'] }}" @if (($active ?? '') === $item['key']) aria-current="page" @endif>
                        <i data-lucide="{{ $item['icon'] }}"></i>
                        <span>{{ $item['label'] }}</span>
                        @if (! empty($item['badge']))
                            <span id="ws-nav-verify" class="ws-nav-dot hidden" aria-label="Perlu tindakan"></span>
                        @endif
                    </a>
                @endforeach
                </div>

                <div id="ws-admin-nav" class="hidden">
                    <p class="ws-nav-label">Admin</p>
                    <a href="/admin" class="ws-nav-link">
                        <i data-lucide="shield-check"></i>
                        <span>Buka Admin Portal</span>
                    </a>
                </div>

                <p class="ws-nav-label">Akun</p>
                <a href="/recruiter/settings" class="ws-nav-link" @if (($active ?? '') === 'settings') aria-current="page" @endif>
                    <i data-lucide="settings"></i>
                    <span>Pengaturan</span>
                </a>
            </nav>

            <div class="ws-sidebar-foot">
                <a href="/" class="ws-nav-link">
                    <i data-lucide="external-link"></i>
                    <span>Lihat situs publik</span>
                </a>
                <button type="button" class="ws-nav-link w-full" data-jh-logout>
                    <i data-lucide="log-out"></i>
                    <span>Keluar</span>
                </button>
            </div>
        </aside>
        <div class="ws-backdrop" data-sidebar-close></div>

        <!-- ============================================================ -->
        <!-- AREA KONTEN                                                  -->
        <!-- ============================================================ -->
        <div class="ws-content">
            <header class="ws-topbar">
                <button type="button" class="ws-icon-btn lg:hidden" data-sidebar-open aria-label="Buka menu" aria-controls="ws-sidebar" aria-expanded="false">
                    <i data-lucide="menu"></i>
                </button>
                <nav aria-label="Breadcrumb" class="min-w-0 flex-1">
                    <ol class="ws-crumbs">
                        <li class="hidden sm:block"><a href="/recruiter">Portal Recruiter</a></li>
                        @isset($crumb)
                            <li class="hidden sm:block" aria-hidden="true"><i data-lucide="chevron-right"></i></li>
                            <li id="ws-crumb" class="truncate" aria-current="page">{{ $crumb }}</li>
                        @endisset
                    </ol>
                </nav>
                <div class="flex items-center gap-1.5">
                    <button type="button" class="ws-icon-btn" data-theme-toggle aria-label="Ganti mode tema">
                        <i data-lucide="moon" class="icon-moon"></i>
                        <i data-lucide="sun" class="icon-sun"></i>
                    </button>
                    <a href="/recruiter/settings" class="ws-user" aria-label="Pengaturan akun">
                        <span id="ws-user-avatar" class="ws-avatar">?</span>
                        <span class="hidden md:block min-w-0 text-left">
                            <span id="ws-user-name" class="block text-sm font-semibold text-[var(--ws-text)] truncate max-w-[160px]">-</span>
                            <span id="ws-user-role" class="block text-xs text-[var(--ws-muted)]">-</span>
                        </span>
                    </a>
                </div>
            </header>

            <!-- Pengumuman status verifikasi (diisi oleh shell.js) -->
            <div id="ws-banner" class="hidden"></div>

            <main id="ws-main" class="ws-main" tabindex="-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div id="ws-toast-region" class="sr-only" aria-live="polite"></div>
    @stack('modals')
    @stack('scripts')
</body>
</html>
