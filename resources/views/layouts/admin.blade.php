@php
    $groups = [
        ['label' => null, 'items' => [
            ['key' => 'overview', 'href' => '/admin', 'icon' => 'layout-dashboard', 'label' => 'Ringkasan'],
        ]],
        ['label' => 'Kepercayaan', 'items' => [
            ['key' => 'reports', 'href' => '/admin/reports', 'icon' => 'flag', 'label' => 'Laporan', 'count' => 'adm-nav-reports'],
            ['key' => 'verifications', 'href' => '/admin/verifications', 'icon' => 'badge-check', 'label' => 'Verifikasi', 'count' => 'adm-nav-verifications'],
            ['key' => 'companies', 'href' => '/admin/companies', 'icon' => 'building-2', 'label' => 'Perusahaan'],
            ['key' => 'users', 'href' => '/admin/users', 'icon' => 'users', 'label' => 'Pengguna'],
            ['key' => 'jobs', 'href' => '/admin/jobs', 'icon' => 'briefcase-business', 'label' => 'Lowongan'],
        ]],
        ['label' => 'Sistem', 'items' => [
            ['key' => 'sources', 'href' => '/admin/sources', 'icon' => 'globe', 'label' => 'Sumber Lowongan', 'dot' => 'adm-nav-sources'],
            ['key' => 'audit', 'href' => '/admin/audit', 'icon' => 'scroll-text', 'label' => 'Log Audit'],
        ]],
        ['label' => 'Akun', 'items' => [
            ['key' => 'settings', 'href' => '/admin/settings', 'icon' => 'settings', 'label' => 'Pengaturan'],
        ]],
    ];
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title ?? 'Admin Portal - JobHarbour' }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="/images/favicon-jobharbour.ico" sizes="any">
    <script>try { var t = localStorage.getItem('jh_theme'); if (t === 'dark') document.documentElement.classList.add('dark'); if (localStorage.getItem('jh_ws_collapsed') === '1') document.documentElement.classList.add('ws-collapsed'); } catch (e) {}</script>

    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="ws-body ws-admin h-full">
    <a href="#ws-main" class="ws-skip">Lewati ke konten</a>

    <div class="ws-shell">
        <aside id="ws-sidebar" class="ws-sidebar" aria-label="Navigasi Admin Portal">
            <div class="ws-brand">
                <a href="/admin" class="flex items-center gap-2.5 min-w-0" aria-label="JobHarbour Admin, ke ringkasan">
                    <img src="/images/logo-icon-256.png" alt="" class="w-8 h-8 rounded-lg">
                    <span class="ws-brand-text min-w-0">
                        <span class="block font-heading font-extrabold text-[17px] text-white leading-tight">JobHarbour</span>
                        <span class="ws-admin-tag">Admin Portal</span>
                    </span>
                </a>
                <button type="button" class="ws-icon-btn ws-collapse-btn w-8 h-8" data-sidebar-collapse aria-label="Ringkas sidebar" aria-pressed="false" title="Ringkas sidebar">
                    <i data-lucide="chevrons-left"></i>
                </button>
                <button type="button" class="ws-icon-btn ws-sidebar-close lg:hidden" data-sidebar-close aria-label="Tutup menu">
                    <i data-lucide="x"></i>
                </button>
            </div>

            <nav class="ws-nav">
                @foreach ($groups as $group)
                    @if ($group['label'])
                        <p class="ws-nav-label">{{ $group['label'] }}</p>
                    @endif
                    @foreach ($group['items'] as $item)
                        <a href="{{ $item['href'] }}" class="ws-nav-link" title="{{ $item['label'] }}" @if (($active ?? '') === $item['key']) aria-current="page" @endif>
                            <i data-lucide="{{ $item['icon'] }}"></i>
                            <span>{{ $item['label'] }}</span>
                            @isset($item['count'])
                                <span id="{{ $item['count'] }}" class="ws-nav-count hidden"></span>
                            @endisset
                            @isset($item['dot'])
                                <span id="{{ $item['dot'] }}" class="ws-nav-dot hidden" aria-label="Ada sumber bermasalah"></span>
                            @endisset
                        </a>
                    @endforeach
                @endforeach
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

        <div class="ws-content">
            <header class="ws-topbar">
                <button type="button" class="ws-icon-btn lg:hidden" data-sidebar-open aria-label="Buka menu" aria-controls="ws-sidebar" aria-expanded="false">
                    <i data-lucide="menu"></i>
                </button>
                <nav aria-label="Breadcrumb" class="min-w-0 flex-1">
                    <ol class="ws-crumbs">
                        <li class="hidden sm:block"><a href="/admin">Admin Portal</a></li>
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
                    <a href="/admin/settings" class="ws-user" aria-label="Pengaturan akun admin">
                        <span id="ws-user-avatar" class="ws-avatar">?</span>
                        <span class="hidden md:block min-w-0 text-left">
                            <span id="ws-user-name" class="block text-sm font-semibold text-[var(--ws-text)] truncate max-w-[160px]">-</span>
                            <span id="ws-user-role" class="block text-xs text-[var(--ws-muted)]">-</span>
                        </span>
                    </a>
                </div>
            </header>

            <div class="adm-modebar" role="note">
                <i data-lucide="shield-check"></i>
                <span><b>Mode admin</b><span class="hidden sm:inline"> · Tindakan di sini berlaku untuk seluruh platform dan tercatat di Log Audit.</span></span>
            </div>

            <main id="ws-main" class="ws-main" tabindex="-1">
                {{ $slot }}
            </main>
        </div>
    </div>

    @include('admin.partials.reason-dialog')
    <div id="ws-toast-region" class="sr-only" aria-live="polite"></div>
    @stack('modals')
    @stack('scripts')
</body>
</html>
