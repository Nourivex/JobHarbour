<x-layouts.app title="JobHarbour - Pelabuhan Talenta & Karier Profesional" :hero-nav="true">

    @push('styles')
        <link rel="preload" as="image" href="/images/hero-lighthouse.webp" type="image/webp" fetchpriority="high">
    @endpush

    <!-- ============================================================== -->
    <!-- HERO: foto mercusuar senja + pencarian                         -->
    <!-- ============================================================== -->
    <section class="relative isolate overflow-hidden bg-[#0C2238] text-white">
        <picture class="absolute inset-0 overflow-hidden pointer-events-none -z-20">
            <source srcset="/images/hero-lighthouse.webp" type="image/webp">
            <img
                src="/images/hero-lighthouse.jpg"
                alt=""
                width="1922"
                height="818"
                fetchpriority="high"
                class="hero-photo w-full h-full object-cover object-[72%_center]"
            >
        </picture>
        <!-- Overlay: gelap di kiri untuk teks, terbuka di kanan untuk mercusuar -->
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(8,22,40,0.94)_0%,rgba(8,22,40,0.82)_38%,rgba(8,22,40,0.25)_68%,rgba(8,22,40,0.05)_100%)] max-lg:bg-[linear-gradient(180deg,rgba(8,22,40,0.88)_0%,rgba(8,22,40,0.78)_100%)]"></div>
        <!-- Cahaya mercusuar yang berdenyut pelan -->
        <span class="hero-beacon hidden lg:block absolute -z-10 rounded-full pointer-events-none" aria-hidden="true"></span>

        <div class="max-w-[1240px] mx-auto px-5 sm:px-8 pt-[136px] sm:pt-[168px] pb-36 sm:pb-40">
            <div class="max-w-[640px]">
                <p class="hero-in inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white/10 text-[#E6EEF6] ring-1 ring-white/25 backdrop-blur-sm" style="--d: 0ms">
                    <x-icon name="sparkles" class="w-3.5 h-3.5 text-[#F26A1B]" />
                    Platform rekrutmen dengan pemadanan keahlian otomatis
                </p>

                <!-- Sapaan: tamu -->
                <h1 id="hero-title-guest" class="hero-in mt-6 text-[2.5rem] leading-[1.08] sm:text-5xl lg:text-[3.6rem] font-extrabold tracking-tight" style="--d: 80ms">
                    Temukan pekerjaan yang cocok dengan <span class="text-[#F26A1B]">keahlianmu.</span>
                </h1>
                <!-- Sapaan: pelamar yang sudah login -->
                <h1 id="hero-title-auth" class="hidden mt-6 text-[2.5rem] leading-[1.08] sm:text-5xl lg:text-[3.6rem] font-extrabold tracking-tight">
                    Halo, <span id="hero-auth-name" class="text-[#F26A1B]">-</span>. Siap berlayar hari ini?
                </h1>

                <p id="hero-subtitle" class="hero-in mt-5 text-base sm:text-lg leading-relaxed text-[#D5DFEA] max-w-[34rem]" style="--d: 160ms">
                    Setiap lowongan menampilkan skor kecocokan dengan profilmu, skill yang masih kurang, dan setiap tahap seleksi sampai penawaran.
                </p>

                <form id="hero-search-form" role="search" class="hero-in relative z-30 mt-8 bg-white rounded-2xl p-2 flex flex-col sm:flex-row gap-2 shadow-[0_20px_50px_-20px_rgba(0,0,0,0.6)]" style="--d: 240ms">
                    <label class="flex-[1.6] flex items-center gap-2.5 px-3.5 min-h-12 rounded-xl text-[#627080] focus-within:bg-[#F4F6F8] transition-colors">
                        <x-icon name="search" class="w-5 h-5 shrink-0" />
                        <span class="sr-only">Posisi atau keahlian</span>
                        <input id="hero-search-keyword" type="text" placeholder="Posisi atau skill, mis. Laravel" class="w-full bg-transparent border-0 outline-none text-[15px] text-[#0B1B2B] placeholder:text-[#8C98A8]">
                    </label>
                    <div class="relative flex-1" id="smart-location-wrapper">
                        <div class="w-full flex items-center gap-2.5 px-3.5 min-h-12 rounded-xl bg-white text-[#627080] focus-within:bg-[#F4F6F8] transition-colors sm:border-l sm:border-[#E2E7ED] sm:rounded-l-none">
                            <!-- SVG Map Pin Icon -->
                            <svg class="w-5 h-5 shrink-0 text-[#C8460B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>
                            </svg>
                            <span class="sr-only">Kota</span>
                            <input id="hero-search-location" type="text" autocomplete="off" placeholder="Pilih kota / pelabuhan..." class="w-full bg-transparent border-0 outline-none text-[15px] text-[#0B1B2B] placeholder:text-[#8C98A8] cursor-pointer">
                            <!-- SVG Geolocation Button -->
                            <button type="button" id="geo-detect-btn" title="Deteksi Lokasi Saya" class="p-1.5 rounded-lg text-slate-400 hover:text-[#C8460B] hover:bg-slate-100 transition-colors shrink-0 cursor-pointer">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><line x1="12" y1="2" x2="12" y2="5"/><line x1="12" y1="19" x2="12" y2="22"/><line x1="2" y1="12" x2="5" y2="12"/><line x1="19" y1="12" x2="22" y2="12"/>
                                </svg>
                            </button>
                        </div>

                        
                    </div>
                    <button type="submit" class="btn-press group inline-flex items-center justify-center gap-2 min-h-12 px-6 rounded-xl bg-[#C8460B] hover:bg-[#A93A08] text-white font-bold text-[15px] cursor-pointer">
                        Cari lowongan
                        <x-icon name="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-0.5" />
                    </button>
                </form>

                <div class="hero-in mt-5 flex flex-wrap items-center gap-2 text-sm" style="--d: 320ms">
                    <span class="text-[#B8C6D6] mr-1 flex items-center gap-1.5 text-xs font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#F26A1B] animate-pulse"></span>
                        Tren Keahlian Nyata:
                    </span>
                    <div id="hero-popular-skills-container" class="flex flex-wrap items-center gap-2">
                        <!-- Di-generate dinamis dari requirements lowongan database -->
                        <span class="text-xs text-slate-400 italic">Menganalisis tren keahlian database...</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- STATISTIK NYATA (dihitung dari GET /api/jobs)                  -->
    <!-- ============================================================== -->
    <section class="relative z-20 max-w-[1240px] mx-auto px-5 sm:px-8 -mt-20 sm:-mt-24">
        <div data-reveal class="bg-[#0F2A45]/95 backdrop-blur-md text-white rounded-2xl ring-1 ring-white/10 shadow-[0_24px_60px_-24px_rgba(8,22,40,0.55)] grid grid-cols-2 lg:grid-cols-4">
            @php
                $stats = [
                    ['id' => 'stat-jobs', 'icon' => 'briefcase', 'label' => 'Lowongan aktif'],
                    ['id' => 'stat-companies', 'icon' => 'building', 'label' => 'Perusahaan'],
                    ['id' => 'stat-positions', 'icon' => 'users', 'label' => 'Posisi dibuka'],
                    ['id' => 'stat-cities', 'icon' => 'map-pin', 'label' => 'Kota'],
                ];
            @endphp
            @foreach ($stats as $i => $stat)
                <div class="flex items-center gap-4 px-5 sm:px-7 py-5 sm:py-6 {{ $i % 2 ? 'border-l border-white/10' : '' }} {{ $i >= 2 ? 'max-lg:border-t max-lg:border-white/10' : '' }} {{ $i === 2 ? 'lg:border-l lg:border-white/10' : '' }}">
                    <span class="w-11 h-11 rounded-full bg-white/10 flex items-center justify-center shrink-0">
                        <x-icon name="{{ $stat['icon'] }}" class="w-5 h-5 text-[#F26A1B]" />
                    </span>
                    <div>
                        <p id="{{ $stat['id'] }}" class="text-2xl sm:text-[1.7rem] font-extrabold font-heading leading-none num-tabular">-</p>
                        <p class="mt-1.5 text-[13px] sm:text-sm text-[#C5D3E2]">{{ $stat['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-3 flex items-center justify-end gap-2 text-[12px]">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full font-semibold shadow-xs border bg-white text-[#0B1B2B] border-[#C5CFDA] transition-colors">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Sinkronisasi Otomatis Database JobHarbour</span>
            </span>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- LOWONGAN PALING COCOK (khusus pelamar yang login)              -->
    <!-- ============================================================== -->
    <section id="top-matched-section" class="hidden max-w-[1240px] mx-auto px-5 sm:px-8 pt-16">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-bold text-[#C8460B]">Untukmu</p>
                <h2 class="mt-1 text-3xl font-extrabold text-[#0B1B2B]">Paling cocok dengan profilmu</h2>
                <p id="hero-auth-skills-count" class="mt-2 text-[#3D4B5C]">-</p>
            </div>
            <a href="/profile" class="text-sm font-semibold text-[#1B65A6] hover:text-[#124B7D]">Perbarui skill di profil</a>
        </div>
        <div id="hero-top-matched-grid" class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-5"></div>
    </section>

    <!-- ============================================================== -->
    <!-- JELAJAH KEAHLIAN (dihitung dari syarat lowongan yang ada)      -->
    <!-- ============================================================== -->
    <section class="max-w-[1240px] mx-auto px-5 sm:px-8 pt-20 sm:pt-24">
        <div class="grid lg:grid-cols-[minmax(0,300px)_minmax(0,1fr)] gap-8 lg:gap-10 items-center">
            <div data-reveal>
                <p class="text-sm font-bold text-[#C8460B]">Jelajahi peluang</p>
                <h2 class="mt-2 text-3xl sm:text-[2.2rem] leading-tight font-extrabold text-[#0B1B2B]">Cari berdasarkan keahlian yang kamu punya</h2>
                <p class="mt-3 text-[#3D4B5C] leading-relaxed">Skill yang paling sering diminta di lowongan saat ini. Pilih satu untuk melihat lowongan yang membutuhkannya.</p>
                <a href="/catalog" class="group mt-5 inline-flex items-center gap-2 font-semibold text-[#C8460B] hover:text-[#A93A08]">
                    Lihat semua lowongan
                    <x-icon name="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1" />
                </a>
            </div>
            <div id="skill-grid" class="skill-grid grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
                @for ($i = 0; $i < 6; $i++)
                    <div class="h-[168px] rounded-2xl bg-white border border-[#E2E7ED] animate-skeleton"></div>
                @endfor
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- FITUR UNGGULAN + SIMULATOR SKOR                                -->
    <!-- ============================================================== -->
    <section id="cara-kerja" class="max-w-[1240px] mx-auto px-5 sm:px-8 pt-20 sm:pt-28 scroll-mt-20">
        <div class="grid lg:grid-cols-[minmax(0,1fr)_minmax(0,460px)] gap-10 lg:gap-14 items-center">
            <div>
                <div data-reveal>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-[#FFF3EA] text-[#8A2E05]">Fitur unggulan</span>
                    <h2 class="mt-3 text-3xl sm:text-[2.2rem] leading-tight font-extrabold text-[#0B1B2B]">Skor kecocokan yang bisa kamu pahami</h2>
                    <p class="mt-3 text-[#3D4B5C] leading-relaxed max-w-[36rem]">Syarat wajib bernilai 3, penting 2, dan nilai tambah 1. Skor adalah persentase nilai skill yang sudah kamu miliki.</p>
                </div>
                <div class="mt-8 grid sm:grid-cols-3 gap-6 sm:gap-0 sm:divide-x sm:divide-[#E2E7ED]">
                    @php
                        $features = [
                            ['icon' => 'target', 'title' => 'Skor transparan', 'desc' => 'Lihat skill mana yang cocok dan berapa bobotnya.'],
                            ['icon' => 'trending-up', 'title' => 'Tahu yang kurang', 'desc' => 'Skill yang belum ada ditandai beserta dampaknya ke skor.'],
                            ['icon' => 'check-circle', 'title' => 'Lolos otomatis', 'desc' => 'Skor 80% ke atas langsung lolos tahap screening.'],
                        ];
                    @endphp
                    @foreach ($features as $i => $f)
                        <div data-reveal class="sm:px-6 first:sm:pl-0">
                            <span class="w-11 h-11 rounded-xl bg-[#FFF3EA] flex items-center justify-center">
                                <x-icon name="{{ $f['icon'] }}" class="w-5 h-5 text-[#C8460B]" />
                            </span>
                            <h3 class="mt-4 text-lg font-bold text-[#0B1B2B]">{{ $f['title'] }}</h3>
                            <p class="mt-1.5 text-sm text-[#3D4B5C] leading-relaxed">{{ $f['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Simulator: klik skill untuk melihat skor berubah -->
            <div data-reveal class="bg-white rounded-3xl border border-[#E2E7ED] p-6 sm:p-7 shadow-[0_24px_60px_-30px_rgba(12,34,56,0.35)]">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-[#627080]">Coba simulasi</p>
                        <p class="mt-1 font-bold text-[#0B1B2B]">Backend Engineer (contoh)</p>
                    </div>
                    <span id="sandbox-score-badge" class="text-xs font-bold px-2.5 py-1 rounded-full bg-[#EBF3FB] text-[#1B65A6]">Cocok</span>
                </div>
                <div class="mt-5 flex items-end justify-between">
                    <span class="text-sm text-[#3D4B5C]">Skor kecocokan</span>
                    <span id="sandbox-score-num" class="text-5xl font-extrabold font-heading text-[#0B1B2B] num-tabular leading-none">0%</span>
                </div>
                <div class="relative mt-4 h-2.5 rounded-full bg-[#E8EDF2]">
                    <div id="sandbox-score-bar" class="h-full rounded-full bg-[#1B65A6] transition-[width] duration-500 ease-out" style="width: 0%"></div>
                    <span class="absolute left-1/2 -top-1 w-0.5 h-[18px] bg-[#8C98A8]" aria-hidden="true"></span>
                    <span class="absolute left-[80%] -top-1 w-0.5 h-[18px] bg-[#0B1B2B]" aria-hidden="true"></span>
                </div>
                <div class="relative mt-2 h-4 text-[11px] text-[#627080]">
                    <span class="absolute left-1/2 -translate-x-1/2">50%</span>
                    <span class="absolute left-[80%] -translate-x-1/2 font-semibold text-[#0B1B2B]">80%</span>
                </div>
                <p class="mt-5 text-xs font-bold uppercase tracking-wider text-[#627080]">Klik skill yang kamu punya</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @php
                        $sandbox = [
                            ['PHP', 'Wajib', true], ['Laravel', 'Wajib', true], ['Docker', 'Penting', false],
                            ['PostgreSQL', 'Penting', false], ['Redis', 'Nilai tambah', false],
                        ];
                    @endphp
                    @foreach ($sandbox as [$name, $level, $on])
                        <button type="button" data-sandbox-toggle-skill="{{ $name }}" aria-pressed="{{ $on ? 'true' : 'false' }}" class="sandbox-chip btn-press inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-semibold border cursor-pointer">
                            <span class="check-indicator w-4 text-center">{{ $on ? '✓' : '+' }}</span>
                            {{ $name }}
                            <span class="text-[11px] font-medium opacity-75">{{ $level }}</span>
                        </button>
                    @endforeach
                </div>
                <p id="sandbox-score-message" class="mt-5 p-3.5 rounded-xl bg-[#F4F6F8] text-sm leading-relaxed text-[#3D4B5C]" aria-live="polite">-</p>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- LOWONGAN TERBARU (GET /api/jobs)                              -->
    <!-- ============================================================== -->
    <section class="max-w-[1240px] mx-auto px-5 sm:px-8 pt-20 sm:pt-28">
        <div data-reveal class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <p class="text-sm font-bold text-[#C8460B]">Peluang terbuka</p>
                <h2 class="mt-2 text-3xl sm:text-[2.2rem] leading-tight font-extrabold text-[#0B1B2B]">Lowongan terbaru</h2>
            </div>
            <a href="/catalog" class="group inline-flex items-center gap-2 font-semibold text-[#C8460B] hover:text-[#A93A08]">
                Lihat semua lowongan
                <x-icon name="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1" />
            </a>
        </div>

        <div id="latest-jobs-loading" class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @for ($i = 0; $i < 3; $i++)
                <div class="h-[230px] rounded-2xl bg-white border border-[#E2E7ED] animate-skeleton"></div>
            @endfor
        </div>
        <div id="latest-jobs-grid" class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5"></div>
        <div id="latest-jobs-empty" class="hidden mt-8 rounded-2xl border border-dashed border-[#C5CFDA] bg-white p-10 text-center">
            <p class="font-bold text-[#0B1B2B]">Lowongan belum bisa dimuat</p>
            <p class="mt-1 text-sm text-[#3D4B5C]">Periksa koneksi ke API, lalu coba muat ulang halaman.</p>
            <button type="button" onclick="location.reload()" class="btn-press mt-4 px-4 py-2 rounded-lg bg-[#0C2238] text-white text-sm font-semibold cursor-pointer">Coba lagi</button>
        </div>
    </section>

    <!-- ============================================================== -->
        <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- RADAR PERKEMBANGAN INDUSTRI INDONESIA (GEOSPATIAL 2D)            -->
    <!-- ============================================================== -->
    <section class="max-w-[1240px] mx-auto px-5 sm:px-8 pt-20 sm:pt-28">
        <div data-reveal class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-6">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-[#EBF3FB] text-[#1B65A6] border border-[#1B65A6]/20 shadow-2xs">
                    <svg class="w-3.5 h-3.5 text-sky-600 dark:text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                    Sistem GIS Resmi: BIG (WGS84), Kemendagri & Kemenperin RI
                </span>
                <h2 class="mt-3 text-3xl sm:text-4xl font-extrabold text-[#0B1B2B] font-heading tracking-tight">
                    Radar Perkembangan Industri Indonesia
                </h2>
                <p class="mt-2 text-sm text-[#3D4B5C] max-w-2xl leading-relaxed">
                    Jelajahi persebaran kawasan industri, pusat manufaktur, koridor logistik, dan ekosistem teknologi di seluruh Indonesia berdasarkan sumber data yang dapat diverifikasi.
                </p>
            </div>

            <!-- Filter Kategori & Tombol Navigasi Reset -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" id="atlas-reset-btn" class="px-3.5 py-1.5 rounded-xl border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors flex items-center gap-1.5 shadow-2xs cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-[#C8460B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>
                    <span>Seluruh Nusantara</span>
                </button>
                <div class="flex items-center gap-1 max-w-full overflow-x-auto bg-white dark:bg-slate-800 p-1 rounded-xl border border-slate-300 dark:border-slate-700 text-xs shadow-2xs [&>button]:shrink-0 [&>button]:whitespace-nowrap">
                    <button type="button" data-atlas-filter="all" class="px-2.5 py-1 rounded-lg font-bold bg-[#C8460B] text-white transition-colors cursor-pointer">Semua</button>
                    <button type="button" data-atlas-filter="maritime_logistics" class="px-2.5 py-1 rounded-lg font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white transition-colors cursor-pointer flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#0284C7]"></span> Maritim
                    </button>
                    <button type="button" data-atlas-filter="manufacturing_ev" class="px-2.5 py-1 rounded-lg font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white transition-colors cursor-pointer flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#F59E0B]"></span> EV & Manufaktur
                    </button>
                    <button type="button" data-atlas-filter="technology_digital" class="px-2.5 py-1 rounded-lg font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white transition-colors cursor-pointer flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#10B981]"></span> Tech Hub
                    </button>
                    <button type="button" data-atlas-filter="energy_mining" class="px-2.5 py-1 rounded-lg font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white transition-colors cursor-pointer flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#EF4444]"></span> Hilirisasi
                    </button>
                    <button type="button" data-atlas-filter="agro_industry" class="px-2.5 py-1 rounded-lg font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-950 dark:hover:text-white transition-colors cursor-pointer flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-[#14B8A6]"></span> Agro
                    </button>
                </div>
            </div>
        </div>

        <!-- Frame Canvas GIS Utama -->
        <div class="relative w-full rounded-3xl overflow-hidden border border-[#2B4A6B] shadow-2xl bg-[#081628]">
            
            <!-- Breadcrumb Tingkat Administrasi Spasial (Kiri Atas Peta) -->
            <div class="absolute left-4 top-4 z-20 bg-slate-900/95 backdrop-blur-md px-3.5 py-1.5 rounded-xl border border-white/15 text-xs flex items-center gap-2 shadow-lg">
                <span class="text-slate-400 font-bold text-[10px] uppercase">Hierarki Spasial:</span>
                <div id="atlas-hierarchy-breadcrumb" class="font-semibold text-white flex items-center gap-1.5">
                    <span class="text-slate-300">Indonesia (Level 1)</span>
                </div>
            </div>

            <!-- Legenda Kategori Industri Berbasis Ikon Semantik Lucide -->
            <div class="hidden lg:flex absolute right-16 top-4 z-20 bg-slate-900/95 backdrop-blur-md px-3.5 py-1.5 rounded-xl border border-white/15 text-[11px] items-center gap-3.5 shadow-xl text-slate-200">
                <div class="flex items-center gap-1.5">
                    <div class="w-4 h-4 rounded bg-[#0284C7] flex items-center justify-center p-0.5">
                        <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="5" r="3"/><line x1="12" y1="22" x2="12" y2="8"/><path d="M5 12H2a10 10 0 0 0 20 0h-3"/></svg>
                    </div>
                    <span>Maritim</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-4 h-4 rounded bg-[#F59E0B] flex items-center justify-center p-0.5">
                        <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 20a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V8l-7 5V8l-7 5V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/></svg>
                    </div>
                    <span>Manufaktur</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-4 h-4 rounded bg-[#10B981] flex items-center justify-center p-0.5">
                        <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/></svg>
                    </div>
                    <span>Tech Hub</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-4 h-4 rounded bg-[#EF4444] flex items-center justify-center p-0.5">
                        <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m14 10 6.5-6.5a1.5 1.5 0 0 1 2 2L16 12"/><path d="M14 10 8 16a2.83 2.83 0 0 1-4 0 2.83 2.83 0 0 1 0-4l6-6"/></svg>
                    </div>
                    <span>Hilirisasi</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <div class="w-4 h-4 rounded bg-[#14B8A6] flex items-center justify-center p-0.5">
                        <svg class="w-3 h-3 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/></svg>
                    </div>
                    <span>Agro</span>
                </div>
            </div>

            <!-- Container Canvas MapLibre GL -->
            <div id="career-atlas-map" class="w-full h-[500px] sm:h-[580px]"></div>

            <!-- Floating Hover Tooltip -->
            <div id="atlas-hover-tooltip" class="absolute pointer-events-none hidden z-30 px-3 py-2 rounded-xl bg-slate-900/95 text-white shadow-2xl border border-white/20 backdrop-blur-sm -translate-x-1/2 -translate-y-full min-w-[140px]"></div>

            <!-- Industrial Intelligence Panel Profesional (Kiri Bawah) -->
            <div id="atlas-selected-panel" class="absolute left-4 bottom-4 z-20 max-w-md w-full bg-white/95 dark:bg-slate-900/95 backdrop-blur-md rounded-2xl p-4 sm:p-5 border border-slate-200 dark:border-slate-700 shadow-2xl hidden transition-all">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100 dark:border-slate-800">
                    <div class="flex items-center gap-1.5">
                        <span id="atlas-panel-level-badge" class="text-[10px] font-extrabold text-sky-800 dark:text-sky-300 bg-sky-100 dark:bg-sky-950/80 px-2.5 py-0.5 rounded-full uppercase tracking-wider">
                            Level Wilayah
                        </span>
                        <span id="atlas-verification-badge" class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                            ✓ Terverifikasi Resmi
                        </span>
                    </div>
                    <button type="button" onclick="document.getElementById('atlas-selected-panel').classList.add('hidden')" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 cursor-pointer">✕</button>
                </div>

                <div class="mt-2.5">
                    <h4 id="atlas-selected-title" class="font-extrabold text-slate-900 dark:text-white text-base leading-snug">-</h4>
                    <div class="flex items-center gap-2 mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                        <span id="atlas-panel-code" class="bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded">Kode: -</span>
                        <span id="atlas-panel-parent" class="truncate">Induk: -</span>
                    </div>
                </div>

                <div id="atlas-selected-desc" class="mt-2.5 text-xs text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/60 p-3 rounded-xl border border-slate-200/80 dark:border-slate-700/80">
                    Memuat data spasial...
                </div>

                <!-- Industrial Intelligence Specs Container -->
                <div id="atlas-zone-specs-container" class="mt-3 space-y-2 hidden">
                    <div class="grid grid-cols-2 gap-2 text-xs py-2 px-2.5 bg-slate-50 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700">
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase">Luas Wilayah Resmi:</span>
                            <span id="atlas-spec-area" class="font-extrabold text-slate-800 dark:text-white">-</span>
                        </div>
                        <div>
                            <span class="text-[10px] text-slate-400 block uppercase">Status Operasional:</span>
                            <span id="atlas-spec-status" class="font-extrabold text-emerald-600 capitalize">-</span>
                        </div>
                    </div>
                    <div id="atlas-spec-management-wrap" class="text-[11px] text-slate-600 dark:text-slate-300">
                        <span class="font-bold text-slate-800 dark:text-white block text-[10px] uppercase text-slate-400">Pengelola Kawasan:</span>
                        <span id="atlas-spec-management">-</span>
                    </div>
                    <div id="atlas-spec-tenants-wrap" class="text-[11px] text-slate-600 dark:text-slate-300">
                        <span class="font-bold text-slate-800 dark:text-white block text-[10px] uppercase text-slate-400 mb-1">Tenant Terverifikasi:</span>
                        <div id="atlas-spec-tenants" class="space-y-0.5 font-medium"></div>
                    </div>
                </div>

                <!-- Data Provenance Card -->
                <div class="mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800 text-[10px] text-slate-400 flex items-center justify-between">
                    <span id="atlas-provenance-text">Dataset: BIG BATAS 2026 & SIINas Kemenperin</span>
                    <button type="button" onclick="window.careerAtlasInstance?.resetView()" class="text-[#C8460B] font-bold hover:underline cursor-pointer">Reset Kamera ↺</button>
                </div>
            </div>

            <!-- Atribusi Geospasial Resmi (Kanan Bawah) -->
            <div class="absolute right-4 bottom-3 z-10 text-[11px] text-slate-400 bg-slate-900/85 px-3 py-1.5 rounded-xl border border-white/10 backdrop-blur-xs flex items-center gap-2 shadow-md">
                <span>Geometri: BIG WGS84 & Ditjen Bina Adwil</span>
                <span>•</span>
                <span>SIINas Kemenperin</span>
            </div>
        </div>
    </section>

    <!-- ============================================================== -->
    <!-- 4 LANGKAH                                                      -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- 4 LANGKAH                                                      -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- 4 LANGKAH                                                      -->
    <!-- ============================================================== -->
    <section class="max-w-[1240px] mx-auto px-5 sm:px-8 pt-20 sm:pt-28 pb-6">
        <div data-reveal class="relative overflow-hidden rounded-3xl bg-[#0C2238] text-white px-6 sm:px-10 py-10 sm:py-12">
            <div class="grid lg:grid-cols-[minmax(0,300px)_minmax(0,1fr)] gap-10 items-center">
                <div>
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-bold text-[#F26A1B] ring-1 ring-[#F26A1B]/60">Mulai perjalanan kariermu</span>
                    <h2 class="mt-4 text-3xl sm:text-[2.2rem] leading-tight font-extrabold">4 langkah sampai berlabuh</h2>
                    <p class="mt-3 text-[#C5D3E2] leading-relaxed">Dari mengisi profil sampai menerima penawaran, semuanya terpantau di satu tempat.</p>
                    <a href="/login?tab=register" data-jh-guest-only class="btn-press mt-6 inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-[#F26A1B] hover:bg-[#FF7D33] text-[#2A1205] font-bold">
                        Daftar gratis
                        <x-icon name="arrow-right" class="w-4 h-4" />
                    </a>
                </div>
                <ol class="grid sm:grid-cols-2 xl:grid-cols-4 gap-6 xl:gap-4">
                    @php
                        $steps = [
                            ['icon' => 'user', 'title' => 'Isi profil', 'desc' => 'Tambahkan skill, lokasi, dan pengalamanmu.'],
                            ['icon' => 'search', 'title' => 'Cari lowongan', 'desc' => 'Filter berdasarkan skill, kota, dan jenis kerja.'],
                            ['icon' => 'target', 'title' => 'Lihat skor', 'desc' => 'Ketahui skill yang cocok dan yang masih kurang.'],
                            ['icon' => 'check', 'title' => 'Lamar & pantau', 'desc' => 'Ikuti tahap screening sampai penawaran.'],
                        ];
                    @endphp
                    @foreach ($steps as $i => $s)
                        <li data-reveal class="step relative">
                            <div class="flex items-center gap-3">
                                <span class="relative w-12 h-12 rounded-full bg-white/10 ring-1 ring-white/20 flex items-center justify-center">
                                    <x-icon name="{{ $s['icon'] }}" class="w-5 h-5" />
                                    <span class="absolute -bottom-1 -left-1 w-5 h-5 rounded-full bg-[#F26A1B] text-[#2A1205] text-[11px] font-extrabold flex items-center justify-center">{{ $i + 1 }}</span>
                                </span>
                                @if ($i < 3)
                                    <span class="hidden xl:block flex-1 h-px bg-[linear-gradient(90deg,rgba(255,255,255,0.35)_50%,transparent_50%)] bg-[length:8px_1px]" aria-hidden="true"></span>
                                @endif
                            </div>
                            <h3 class="mt-4 font-bold text-lg">{{ $s['title'] }}</h3>
                            <p class="mt-1 text-sm text-[#C5D3E2] leading-relaxed">{{ $s['desc'] }}</p>
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>
    </section>

    @push('scripts')
        @vite('resources/js/pages/home.js')
    @endpush

    <!-- FLOATING SMART LOCATION DROPDOWN (Teleported to root body level) -->
    <div id="smart-location-dropdown" style="z-index: 99999;" class="fixed bg-white rounded-2xl border border-slate-200 shadow-2xl p-3 hidden text-[#0B1B2B] w-[340px] sm:w-[360px]">
        <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1.5">
                <svg class="w-4 h-4 text-[#C8460B]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>
                Pilih Kota / Pelabuhan
            </span>
            <span class="text-[10px] text-sky-700 font-bold bg-sky-50 px-2 py-0.5 rounded-full border border-sky-100">Database Live</span>
        </div>
        <div class="space-y-1.5" id="smart-location-options">
            <button type="button" data-val="" class="w-full px-3 py-2 rounded-xl text-left text-xs font-semibold text-slate-700 hover:bg-slate-100 flex items-center justify-between transition-colors cursor-pointer">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                    Semua Kota (Seluruh Indonesia)
                </span>
                <span class="text-[11px] text-slate-400 shrink-0">Semua Loker</span>
            </button>
            <button type="button" data-val="Tegal" class="w-full px-3 py-2.5 rounded-xl text-left text-xs font-bold text-slate-900 bg-sky-50 hover:bg-sky-100 flex items-center justify-between transition-colors group cursor-pointer border border-sky-200">
                <span class="flex items-center gap-2 text-sky-950">
                    <svg class="w-4 h-4 text-sky-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="5" r="3"/><line x1="12" y1="22" x2="12" y2="8"/><path d="M5 12H2a10 10 0 0 0 20 0h-3"/></svg>
                    Tegal, Jawa Tengah
                </span>
                <span class="text-[10px] px-2 py-0.5 rounded-full bg-sky-600 text-white font-bold shrink-0">Dermaga Utama (2)</span>
            </button>
            <button type="button" data-val="Semarang" class="w-full px-3 py-2 rounded-xl text-left text-xs font-semibold text-slate-800 hover:bg-slate-100 flex items-center justify-between transition-colors cursor-pointer">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Semarang, Jawa Tengah
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium shrink-0">1 Lowongan</span>
            </button>
            <button type="button" data-val="Bandung" class="w-full px-3 py-2 rounded-xl text-left text-xs font-semibold text-slate-800 hover:bg-slate-100 flex items-center justify-between transition-colors cursor-pointer">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                    Bandung, Jawa Barat
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 font-medium shrink-0">1 Lowongan</span>
            </button>
        </div>
    </div>

</x-layouts.app>
