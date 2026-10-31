<!DOCTYPE html>
<html lang="id" class="h-full bg-[#F4F6F8]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="description" content="JobHarbour - Platform Rekrutmen Terintegrasi Berbasis Microservice dengan Mesin Pemadanan Keahlian Otomatis.">
    <title>{{ $title ?? 'JobHarbour - Pelabuhan Talenta & Karier Profesional' }}</title>

    <!-- Google Fonts: Bricolage Grotesque (700, 800) & Inter (400, 500, 600, 700) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Favicon: logo perahu layar JobHarbour -->
    <link rel="icon" href="/images/favicon-jobharbour.ico" sizes="any">
    <link rel="apple-touch-icon" href="/images/logo-icon-256.png">

    <!-- Terapkan mode malam sebelum halaman dirender supaya tidak berkedip -->
    <script>try { var t = localStorage.getItem('jh_theme'); if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark'); } catch (e) {}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full flex flex-col text-[#0B1B2B] bg-[#F4F6F8] selection:bg-[#FFF3EA] selection:text-[#8A2E05]">

    <!-- Navbar: transparan di atas hero beranda, navy di halaman lain dan saat di-scroll -->
    @php $heroNav = $heroNav ?? false; @endphp
    <header id="site-nav" data-hero-nav="{{ $heroNav ? '1' : '0' }}" class="site-nav {{ $heroNav ? 'fixed is-hero' : 'sticky is-solid' }} top-0 inset-x-0 z-40 text-white">
        <div class="max-w-[1240px] mx-auto px-5 sm:px-8 h-[72px] flex items-center gap-6">

            <!-- Brand -->
            <a href="/" class="flex items-center gap-2.5 shrink-0 group" aria-label="JobHarbour, ke beranda">
                <img src="/images/logo-jobharbour.webp" width="256" height="239" alt="" class="w-9 h-auto transition-transform duration-300 group-hover:-rotate-6" aria-hidden="true">
                <span class="font-heading font-extrabold text-[22px] tracking-tight">JobHarbour</span>
            </a>

            <!-- Navigasi utama (desktop) -->
            <nav class="hidden lg:flex items-center gap-1 mx-auto" aria-label="Navigasi utama">
                <a href="/catalog" class="nav-link" @if (request()->is('catalog*')) aria-current="page" @endif>Lowongan</a>
                <a href="/my-dock" data-jh-role-applicant style="display:none;" class="nav-link" @if (request()->is('my-dock*')) aria-current="page" @endif>Lamaran Saya</a>
                <a href="/recruiter" data-jh-role-recruiter style="display:none;" class="nav-link" @if (request()->is('recruiter*')) aria-current="page" @endif>Rekrutmen</a>
                <a href="/#cara-kerja" class="nav-link">Cara Kerja</a>
                <a href="/login?tab=register" data-jh-guest-only class="nav-link">Untuk Recruiter</a>
                <a href="/docs" target="_blank" rel="noopener" class="nav-link">API Docs</a>
            </nav>

            <!-- Aksi -->
            <div class="flex items-center gap-2 ml-auto lg:ml-0">
                <button type="button" data-theme-toggle class="theme-toggle w-10 h-10 rounded-full flex items-center justify-center text-white/85 hover:text-white hover:bg-white/10 transition-colors cursor-pointer" aria-label="Aktifkan mode malam" aria-pressed="false">
                    <svg class="icon-moon w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                    <svg class="icon-sun w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                </button>

                <!-- Tamu -->
                <div data-jh-guest-only class="flex items-center gap-2">
                    <a href="/login" class="hidden sm:inline-flex items-center h-10 px-5 rounded-lg text-sm font-semibold text-white ring-1 ring-white/45 hover:ring-white hover:bg-white/8 transition">
                        Masuk
                    </a>
                    <a href="/login?tab=register" class="btn-press group whitespace-nowrap inline-flex items-center gap-1.5 h-10 px-4 sm:px-5 rounded-lg text-sm font-semibold text-white bg-[#F26A1B] hover:bg-[#FF7D33] transition-colors">
                        Daftar Gratis
                        <svg class="w-4 h-4 transition-transform group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </a>
                </div>

                <!-- Sudah login -->
                <div data-jh-auth-only style="display:none;" class="relative">
                    <button
                        id="user-menu-button"
                        type="button"
                        class="flex items-center gap-2.5 p-1 sm:pl-3 rounded-full ring-1 ring-white/35 hover:ring-white transition cursor-pointer"
                        aria-expanded="false"
                        aria-haspopup="true"
                    >
                        <span class="flex-col text-left pr-1 hidden sm:flex">
                            <span data-jh-user-name class="text-xs font-bold text-white leading-tight">Nama</span>
                            <span data-jh-user-role class="text-[10px] text-white/70">Role</span>
                        </span>
                        <span class="w-8 h-8 rounded-full bg-[#F26A1B] text-[#2A1205] flex items-center justify-center font-bold text-xs uppercase" data-jh-user-initials>JH</span>
                        <x-icon name="chevron-down" class="w-3.5 h-3.5 text-white/70 mr-1.5 hidden sm:block" />
                    </button>

                    <!-- User Dropdown Menu -->
                    <div
                        id="user-menu-dropdown"
                        class="hidden absolute right-0 mt-2 w-60 rounded-xl bg-white shadow-xl border border-[#E2E7ED] py-2 z-50 animate-fade-fast"
                        role="menu"
                    >
                        <div class="px-4 py-2.5 border-b border-[#E2E7ED]">
                            <p data-jh-user-name class="text-sm font-bold text-[#0B1B2B] truncate">-</p>
                            <p data-jh-user-email class="text-xs text-[#627080] truncate">-</p>
                            <span data-jh-user-role class="inline-block mt-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-[#F4F6F8] text-[#3D4B5C]">-</span>
                        </div>

                        <div class="py-1">
                            <a
                                href="/profile"
                                class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#3D4B5C] hover:bg-[#F4F6F8] hover:text-[#0B1B2B] transition-colors"
                                role="menuitem"
                            >
                                <x-icon name="user" class="w-4 h-4 text-[#627080]" />
                                <span>Profil Saya</span>
                            </a>

                            <a
                                href="/my-dock"
                                data-jh-role-applicant
                                class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#3D4B5C] hover:bg-[#F4F6F8] hover:text-[#0B1B2B] transition-colors"
                                role="menuitem"
                            >
                                <x-icon name="anchor" class="w-4 h-4 text-[#627080]" />
                                <span>Lamaran Saya</span>
                            </a>

                            <a
                                href="/recruiter"
                                data-jh-role-recruiter
                                class="flex items-center gap-2.5 px-4 py-2 text-sm text-[#3D4B5C] hover:bg-[#F4F6F8] hover:text-[#0B1B2B] transition-colors"
                                role="menuitem"
                            >
                                <x-icon name="briefcase" class="w-4 h-4 text-[#627080]" />
                                <span>Papan Rekrutmen</span>
                            </a>
                        </div>

                        <div class="pt-1 border-t border-[#E2E7ED]">
                            <button
                                type="button"
                                data-jh-logout
                                class="w-full flex items-center gap-2.5 px-4 py-2 text-sm font-medium text-[#B42318] hover:bg-[#FDEEEC] transition-colors cursor-pointer text-left"
                                role="menuitem"
                            >
                                <x-icon name="log-out" class="w-4 h-4" />
                                <span>Keluar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </header>

    <!-- Konten Halaman -->
    <main id="main-content" class="flex-1 pb-20 lg:pb-12">
        {{ $slot }}
    </main>

    <!-- Navigasi Bawah (Khusus Mobile < 1024px) -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-[#E2E7ED] px-2 py-1.5 flex items-center justify-around">
        <a
            href="/"
            class="flex flex-col items-center gap-1 py-1 px-3 text-xs font-semibold rounded-lg transition-colors {{ request()->is('/') ? 'text-[#C8460B]' : 'text-[#627080] hover:text-[#0B1B2B]' }}"
        >
            <x-icon name="anchor" class="w-5 h-5" />
            <span>Beranda</span>
        </a>

        <a
            href="/catalog"
            class="flex flex-col items-center gap-1 py-1 px-3 text-xs font-semibold rounded-lg transition-colors {{ request()->is('catalog*') ? 'text-[#C8460B]' : 'text-[#627080] hover:text-[#0B1B2B]' }}"
        >
            <x-icon name="briefcase" class="w-5 h-5" />
            <span>Lowongan</span>
        </a>

        <!-- Khusus Applicant -->
        <a
            href="/my-dock"
            data-jh-role-applicant
            class="flex flex-col items-center gap-1 py-1 px-3 text-xs font-semibold rounded-lg transition-colors {{ request()->is('my-dock*') ? 'text-[#C8460B]' : 'text-[#627080] hover:text-[#0B1B2B]' }}"
        >
            <x-icon name="file-text" class="w-5 h-5" />
            <span>Lamaran</span>
        </a>

        <!-- Khusus Recruiter -->
        <a
            href="/recruiter"
            data-jh-role-recruiter
            style="display:none;"
            class="flex flex-col items-center gap-1 py-1 px-3 text-xs font-semibold rounded-lg transition-colors {{ request()->is('recruiter*') ? 'text-[#C8460B]' : 'text-[#627080] hover:text-[#0B1B2B]' }}"
        >
            <x-icon name="users" class="w-5 h-5" />
            <span>Pipeline</span>
        </a>

        <!-- Auth state icon -->
        <a
            href="/profile"
            data-jh-auth-only
            style="display:none;"
            class="flex flex-col items-center gap-1 py-1 px-3 text-xs font-semibold rounded-lg transition-colors {{ request()->is('profile*') ? 'text-[#C8460B]' : 'text-[#627080] hover:text-[#0B1B2B]' }}"
        >
            <x-icon name="user" class="w-5 h-5" />
            <span>Profil</span>
        </a>

        <!-- Guest state icon -->
        <a
            href="/login"
            data-jh-guest-only
            class="flex flex-col items-center gap-1 py-1 px-3 text-xs font-semibold rounded-lg transition-colors {{ request()->is('login*') ? 'text-[#C8460B]' : 'text-[#627080] hover:text-[#0B1B2B]' }}"
        >
            <x-icon name="user" class="w-5 h-5" />
            <span>Masuk</span>
        </a>
    </nav>

    <!-- Footer Bersama -->
    <footer class="bg-[#0C2238] text-white border-t border-[#133150] pt-12 pb-24 lg:pb-12 mt-auto">
        <div class="max-w-[1360px] mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-10 border-b border-[#2B4A6B]">
                
                <!-- Identitas Brand -->
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <x-logo class="ring-1 ring-[#2B4A6B]" />
                        <span class="font-heading font-extrabold text-xl tracking-tight">JobHarbour</span>
                    </div>
                    <p class="text-sm text-[#C5CFDA] max-w-md leading-relaxed">
                        Platform rekrutmen terintegrasi berbasis microservice dengan mesin pemadanan keahlian otomatis (Weighted Jaccard Similarity). Menghubungkan talenta siap kerja dengan peluang profesional secara transparan.
                    </p>
                    <div class="text-xs text-[#8CC8F2] pt-1">
                        Universitas Harkat Negeri - Tegal, Jawa Tengah
                    </div>
                </div>

                <!-- Navigasi Cepat -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#8CC8F2] mb-3">Platform</h4>
                    <ul class="space-y-2 text-sm text-[#C5CFDA]">
                        <li><a href="/catalog" class="hover:text-white transition-colors">Katalog Lowongan</a></li>
                        <li><a href="/my-dock" class="hover:text-white transition-colors">Lamaran Saya</a></li>
                        <li><a href="/recruiter" class="hover:text-white transition-colors">Papan Rekruter</a></li>
                        <li><a href="/profile" class="hover:text-white transition-colors">Kalibrasi Keahlian</a></li>
                    </ul>
                </div>

                <!-- Dokumentasi & Teknis -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-[#8CC8F2] mb-3">Teknis & Layanan</h4>
                    <ul class="space-y-2 text-sm text-[#C5CFDA]">
                        <li><a href="/docs" target="_blank" class="hover:text-white transition-colors">Spesifikasi OpenAPI</a></li>
                        <li><a href="/#cara-kerja" class="hover:text-white transition-colors">Algoritma Pemadanan</a></li>
                        <li><span class="text-[#8C98A8]">REST API Laravel + JWT</span></li>
                        <li><span class="text-[#8C98A8]">4 modul layanan, 1 database MySQL/MariaDB</span></li>
                    </ul>
                </div>
            </div>

            <!-- Titimangsa & Hak Cipta (Tegal, Jawa Tengah - No Em-Dash) -->
            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-[#8CC8F2]">
                <p>Hak Cipta (C) 2026 JobHarbour. Mata Kuliah Pemrograman Web Service - Kelompok 4.</p>
                <p>Tegal, Jawa Tengah - Versi 1.0.0</p>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
