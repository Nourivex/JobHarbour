@php
    $currentActive = $active ?? '';
@endphp

<!-- ========================================================================= -->
<!-- SHARED COMPONENT: TopNavBar (Konsisten di Seluruh Halaman JobHarbour)      -->
<!-- ========================================================================= -->
<header class="fixed top-0 left-0 right-0 w-full z-50 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs transition-all duration-150 h-16">
    <div class="max-w-[1600px] mx-auto h-full px-4 sm:px-6 lg:px-10 flex items-center justify-between">
        
        <!-- Brand Anchor & Identity -->
        <div class="flex items-center gap-6 lg:gap-8">
            <a class="flex items-center gap-2.5 transition-all duration-150 active:scale-95 group shrink-0" href="/catalog" title="JobHarbour - Career Launchpad">
                <div class="w-9 h-9 rounded-lg bg-slate-900 text-white flex items-center justify-center shadow-xs group-hover:bg-sky-700 transition-colors">
                    <span class="material-symbols-outlined text-[20px] text-sky-400" style="font-variation-settings: 'FILL' 1;">anchor</span>
                </div>
                <div class="flex flex-col text-left">
                    <span class="leading-none text-base font-extrabold text-slate-900 tracking-tight font-headline">JobHarbour</span>
                    <span class="text-[9px] tracking-widest text-sky-700 font-bold uppercase mt-0.5">Career Launchpad</span>
                </div>
            </a>

            <!-- Desktop Navigation Links (4 Pilar Utama Sistem) -->
            <nav class="hidden md:flex items-center gap-6">
                <!-- 1. The Anchorage (Katalog Lowongan) -->
                <a class="{{ $currentActive === 'catalog' ? 'text-sky-700 border-b-2 border-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:border-b-2 hover:border-slate-300' }} transition-colors pb-1 text-sm flex items-center gap-1.5" href="/catalog">
                    <span>The Anchorage</span>
                    @if($currentActive === 'catalog')
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-600 inline-block"></span>
                    @endif
                </a>

                <!-- 2. My Dock (Dashboard Pelamar & Linimasa Seleksi) -->
                <a class="{{ $currentActive === 'my-dock' ? 'text-sky-700 border-b-2 border-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:border-b-2 hover:border-slate-300' }} transition-colors pb-1 text-sm flex items-center gap-1.5" href="/my-dock">
                    <span>My Dock</span>
                    @if($currentActive === 'my-dock')
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-600 inline-block"></span>
                    @endif
                </a>

                <!-- 3. Captain's Ledger (Profil Talenta & Kalibrasi Keahlian) -->
                <a class="{{ $currentActive === 'profile' ? 'text-sky-700 border-b-2 border-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:border-b-2 hover:border-slate-300' }} transition-colors pb-1 text-sm flex items-center gap-1.5" href="/profile">
                    <span>Captain's Ledger</span>
                    @if($currentActive === 'profile')
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-600 inline-block"></span>
                    @endif
                </a>

                <!-- 4. Harbour Master (Dashboard Recruiter & Pipeline Seleksi) -->
                <a class="{{ $currentActive === 'recruiter' ? 'text-sky-700 border-b-2 border-sky-700 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:border-b-2 hover:border-slate-300' }} transition-colors pb-1 text-sm flex items-center gap-1.5" href="/recruiter">
                    <span>Harbour Master</span>
                    <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase tracking-wider {{ $currentActive === 'recruiter' ? 'bg-sky-700 text-white' : 'bg-slate-200 text-slate-700' }}">PRO</span>
                    @if($currentActive === 'recruiter')
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-600 inline-block"></span>
                    @endif
                </a>
            </nav>
        </div>

        <!-- Trailing Action Cluster (Desktop) -->
        <div class="hidden md:flex items-center gap-3">
            
            <!-- Guest State: Masuk / Daftar -->
            <div class="flex items-center gap-2" data-jh-guest-only>
                <a class="px-3.5 py-1.5 rounded-lg border border-slate-300 text-slate-700 text-sm font-semibold hover:border-sky-700 hover:text-sky-700 hover:bg-sky-50 transition-all flex items-center gap-1.5" href="/login">
                    <span class="material-symbols-outlined text-[18px]">login</span>
                    <span>Masuk / Daftar</span>
                </a>
                <a class="px-3.5 py-1.5 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-sky-800 transition-all shadow-xs flex items-center gap-1.5 active:scale-95" href="/catalog">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    <span>Cari Lowongan</span>
                </a>
            </div>

            <!-- Auth State: User Identity Pill, Dashboard CTA, Logout -->
            <div class="flex items-center gap-2.5" data-jh-auth-only style="display:none;">
                
                @if($currentActive === 'recruiter')
                    <!-- Khusus Recruiter Controls -->
                    <div class="flex items-center gap-1 border-r border-slate-200 pr-2 mr-1">
                        <button id="btn-toggle-jobs-panel" class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-1 text-xs font-semibold cursor-pointer" title="Daftar Lowongan Saya" type="button">
                            <span class="material-symbols-outlined text-[18px]">work</span>
                            <span class="hidden xl:inline">Lowongan Saya</span>
                        </button>
                        <button id="btn-refresh-board" class="p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors flex items-center gap-1 text-xs font-semibold cursor-pointer" title="Segarkan Pipeline" type="button">
                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                            <span class="hidden xl:inline">Segarkan</span>
                        </button>
                        <button id="btn-open-create-job" class="px-3 py-1.5 rounded-lg bg-sky-700 hover:bg-sky-800 text-white text-xs font-semibold flex items-center gap-1 shadow-xs transition-all active:scale-95 cursor-pointer" type="button">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span class="hidden sm:inline">Pasang Lowongan</span>
                        </button>
                    </div>
                @endif

                <!-- User Profile Pill -->
                <a class="flex items-center gap-2.5 pl-2 pr-3 py-1 bg-slate-100 hover:bg-slate-200/80 border border-slate-200 rounded-full transition-colors group cursor-pointer" href="/profile" title="Buka Profil & Kalibrasi Keahlian">
                    <div class="relative">
                        <div class="w-7 h-7 rounded-full bg-sky-700 text-white flex items-center justify-center text-xs font-bold ring-2 ring-white shadow-xs" data-jh-user-initials>
                            JH
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-2 h-2 rounded-full bg-emerald-500 ring-2 ring-white"></span>
                    </div>
                    <div class="flex flex-col text-left">
                        <span id="dock-user-pill-name" class="text-xs font-semibold text-slate-900 leading-tight max-w-[130px] truncate" data-jh-user-name>Memuat...</span>
                        <span id="dock-user-pill-sub" class="text-[10px] text-sky-700 font-medium leading-none capitalize">
                            <span data-jh-user-role>Pelamar</span>
                            <span id="nav-seafarer-badge" class="hidden"></span>
                        </span>
                    </div>
                </a>

                <!-- Quick Action CTA -->
                @if($currentActive === 'catalog')
                    <button class="px-3.5 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-semibold hover:bg-sky-800 transition-all shadow-xs hidden lg:flex items-center gap-1 active:scale-95 cursor-pointer" id="nav-quick-apply-btn" type="button">
                        <span class="material-symbols-outlined text-[16px]">send</span>
                        <span>Lamar Sekarang</span>
                    </button>
                @elseif($currentActive === 'my-dock')
                    <a class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-sky-800 text-white text-xs font-semibold transition-all shadow-xs flex items-center gap-1 active:scale-95" href="/catalog">
                        <span class="material-symbols-outlined text-[16px]">search</span>
                        <span>Cari Lowongan</span>
                    </a>
                @elseif($currentActive === 'profile')
                    <a class="px-3 py-1.5 rounded-lg bg-slate-900 hover:bg-sky-800 text-white text-xs font-semibold transition-all shadow-xs flex items-center gap-1 active:scale-95" href="/catalog">
                        <span class="material-symbols-outlined text-[16px]">explore</span>
                        <span>Jelajahi Lowongan</span>
                    </a>
                @endif

                <!-- Logout Button -->
                <button class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white hover:bg-red-50 hover:text-red-600 hover:border-red-200 text-slate-600 text-xs font-semibold transition-all flex items-center gap-1 active:scale-95 cursor-pointer" data-jh-logout type="button" title="Keluar dari Sesi">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span class="hidden xl:inline">Keluar</span>
                </button>
            </div>
        </div>

        <!-- Mobile Menu Toggle Button (< md) -->
        <div class="flex items-center gap-2 md:hidden">
            <button id="mobile-nav-toggle-btn" class="p-2 rounded-lg text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" type="button" aria-label="Toggle Menu Navigasi">
                <span class="material-symbols-outlined text-2xl" id="mobile-nav-icon">menu</span>
            </button>
        </div>
    </div>

    <!-- Mobile Dropdown Menu -->
    <div id="mobile-nav-menu" class="hidden md:hidden border-t border-slate-200 bg-white px-4 py-4 space-y-3 shadow-lg">
        <nav class="flex flex-col space-y-1">
            <a class="px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-between {{ $currentActive === 'catalog' ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}" href="/catalog">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">explore</span>
                    <span>The Anchorage (Katalog)</span>
                </span>
                @if($currentActive === 'catalog')
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                @endif
            </a>

            <a class="px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-between {{ $currentActive === 'my-dock' ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}" href="/my-dock">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">dashboard</span>
                    <span>My Dock (Lamaran Saya)</span>
                </span>
                @if($currentActive === 'my-dock')
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                @endif
            </a>

            <a class="px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-between {{ $currentActive === 'profile' ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}" href="/profile">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">person</span>
                    <span>Captain's Ledger (Profil)</span>
                </span>
                @if($currentActive === 'profile')
                    <span class="w-1.5 h-1.5 rounded-full bg-sky-600"></span>
                @endif
            </a>

            <a class="px-3 py-2 rounded-lg text-sm font-medium flex items-center justify-between {{ $currentActive === 'recruiter' ? 'bg-sky-50 text-sky-700 font-semibold' : 'text-slate-700 hover:bg-slate-50' }}" href="/recruiter">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">badge</span>
                    <span>Harbour Master (Recruiter)</span>
                </span>
                <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase bg-sky-700 text-white">PRO</span>
            </a>
        </nav>

        <!-- Mobile Auth State -->
        <div class="pt-3 border-t border-slate-100 flex flex-col gap-2">
            <!-- Guest -->
            <div data-jh-guest-only class="flex flex-col gap-2">
                <a class="w-full py-2.5 text-center rounded-lg bg-sky-700 text-white text-sm font-semibold hover:bg-sky-800 transition" href="/login">
                    Masuk ke Akun
                </a>
                <a class="w-full py-2 text-center rounded-lg border border-slate-300 text-slate-700 text-sm font-medium hover:bg-slate-50 transition" href="/login?mode=register">
                    Daftar Akun Baru
                </a>
            </div>

            <!-- Authenticated -->
            <div data-jh-auth-only style="display:none;" class="flex flex-col gap-2.5">
                <div class="flex items-center gap-3 p-2 bg-slate-50 rounded-lg">
                    <div class="w-8 h-8 rounded-full bg-sky-700 text-white flex items-center justify-center font-bold text-xs" data-jh-user-initials>
                        JH
                    </div>
                    <div class="flex flex-col text-left">
                        <span class="text-xs font-semibold text-slate-900" data-jh-user-name>Memuat...</span>
                        <span class="text-[10px] text-sky-700 font-medium capitalize" data-jh-user-role>Pelamar</span>
                    </div>
                </div>
                <button class="w-full py-2 rounded-lg border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 text-sm font-semibold transition flex items-center justify-center gap-1.5 cursor-pointer" data-jh-logout type="button">
                    <span class="material-symbols-outlined text-base">logout</span>
                    <span>Keluar dari Sesi</span>
                </button>
            </div>
        </div>
    </div>
</header>

<!-- Spacer setinggi 64px (h-16) agar konten tidak tertutup fixed header -->
<div class="h-16 w-full" aria-hidden="true"></div>

<script>
    // Toggle Mobile Navigation Menu
    (function() {
        const toggleBtn = document.getElementById('mobile-nav-toggle-btn');
        const menu = document.getElementById('mobile-nav-menu');
        const icon = document.getElementById('mobile-nav-icon');
        if (toggleBtn && menu) {
            toggleBtn.addEventListener('click', function() {
                const isHidden = menu.classList.contains('hidden');
                menu.classList.toggle('hidden', !isHidden);
                if (icon) {
                    icon.textContent = isHidden ? 'close' : 'menu';
                }
            });
        }
    })();
</script>
