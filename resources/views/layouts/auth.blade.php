<!DOCTYPE html>
<html lang="id" class="h-full bg-[#0C2238]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Masuk atau Daftar - JobHarbour' }}</title>

    <!-- Google Fonts: Bricolage Grotesque (700, 800) & Inter (400, 500, 600, 700) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Favicon: logo perahu layar JobHarbour -->
    <link rel="icon" href="/images/favicon-jobharbour.ico" sizes="any">
    <link rel="apple-touch-icon" href="/images/logo-icon-256.png">
    <script>try { var t = localStorage.getItem('jh_theme'); if (t === 'dark') document.documentElement.classList.add('dark'); } catch (e) {}</script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-full flex items-center justify-center p-0 lg:p-6 bg-[#0C2238] text-[#0B1B2B]">

    <div class="relative w-full max-w-[1240px]
        <!-- Theme Toggle Absolute Position -->
        <div class="absolute top-4 right-4 z-20">
            <button type="button" data-theme-toggle class="theme-toggle w-9 h-9 rounded-full flex items-center justify-center text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" aria-label="Ganti mode tema">
                <svg class="icon-moon w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                <svg class="icon-sun w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
            </button>
        </div>
 min-h-[100vh] lg:min-h-[720px] bg-white lg:rounded-3xl shadow-2xl overflow-hidden grid grid-cols-1 lg:grid-cols-12 border-0 lg:border border-[#2B4A6B]">
        
        <!-- Kolom Kiri: Brand Navy Panel (Desktop Only) -->
        <div class="hidden lg:flex lg:col-span-5 bg-[#0C2238] text-white p-10 xl:p-12 flex-col justify-between relative overflow-hidden">
            <!-- Subtle Nautical Background Graphic -->
            <div class="absolute -right-16 -bottom-16 w-80 h-80 opacity-10 pointer-events-none">
                <svg viewBox="0 0 48 48" fill="none" stroke="#FFFFFF" stroke-width="2">
                    <circle cx="24" cy="24" r="20"/>
                    <line x1="24" y1="4" x2="24" y2="44"/>
                    <line x1="4" y1="24" x2="44" y2="24"/>
                </svg>
            </div>

            <!-- Brand Header -->
            <div>
                <a href="/" class="inline-flex items-center gap-3 group">
                    <img src="/images/logo-jobharbour.webp" width="256" height="239" alt="" class="w-11 h-auto transition-transform duration-300 group-hover:-rotate-6" aria-hidden="true">
                    <div class="flex flex-col">
                        <span class="font-heading font-extrabold text-2xl tracking-tight leading-none text-white">JobHarbour</span>
                        <span class="text-[11px] font-semibold text-[#8CC8F2] tracking-wider uppercase pt-0.5">Pelabuhan Talenta</span>
                    </div>
                </a>

                <div class="mt-12 space-y-4">
                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-[#133150] text-[#8CC8F2] border border-[#2B4A6B]">
                        Platform Rekrutmen Modern
                    </span>
                    <h1 class="text-3xl xl:text-4xl font-extrabold text-white leading-tight font-heading">
                        Berlabuh Menuju Karier Profesional Impian.
                    </h1>
                    <p class="text-[#C5CFDA] text-sm leading-relaxed">
                        Sistem pemadanan keahlian berbasis matematika objektif (Weighted Jaccard Similarity). Pelamar tahu potensi kecocokan sebelum melamar, rekruter menemukan talenta presisi.
                    </p>
                </div>
            </div>

            <!-- Harbour Illustration SVG -->
            <div class="my-6 py-6 border-y border-[#2B4A6B]/60">
                <svg viewBox="0 0 400 160" fill="none" class="w-full h-auto text-white" aria-hidden="true">
                    <!-- Crane 1 -->
                    <path d="M60 140 L90 50 L120 140" stroke="#8CC8F2" stroke-width="2.5"/>
                    <path d="M75 95 L105 95" stroke="#8CC8F2" stroke-width="2"/>
                    <path d="M90 50 L180 50 L200 65" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
                    <line x1="160" y1="50" x2="160" y2="90" stroke="#F26A1B" stroke-width="2" stroke-dasharray="3 3"/>
                    <rect x="145" y="90" width="30" height="20" rx="3" fill="#C8460B"/>
                    <!-- Container Stacks -->
                    <rect x="220" y="110" width="45" height="25" rx="2" fill="#133150" stroke="#2B4A6B" stroke-width="2"/>
                    <rect x="270" y="110" width="45" height="25" rx="2" fill="#F26A1B" stroke="#F26A1B" stroke-width="2"/>
                    <rect x="245" y="80" width="45" height="25" rx="2" fill="#8CC8F2" stroke="#8CC8F2" stroke-width="2"/>
                    <rect x="320" y="100" width="45" height="35" rx="2" fill="#133150" stroke="#2B4A6B" stroke-width="2"/>
                    <!-- Dock Ground -->
                    <line x1="20" y1="140" x2="380" y2="140" stroke="#2B4A6B" stroke-width="3" stroke-linecap="round"/>
                    <!-- Sea Waves -->
                    <path d="M20 152 Q 60 148 100 152 T 180 152 T 260 152 T 340 152 T 400 152" stroke="#8CC8F2" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>

            <!-- Footer Note -->
            <div class="text-xs text-[#8CC8F2] flex items-center justify-between">
                <span>Universitas Harkat Negeri - Tegal</span>
                <a href="/" class="hover:text-white transition-colors flex items-center gap-1">
                    <span>Kembali ke Beranda</span>
                    <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                </a>
            </div>
        </div>

        <!-- Kolom Kanan: Form Masuk / Daftar -->
        <div class="col-span-1 lg:col-span-7 bg-white p-6 sm:p-10 xl:p-12 flex flex-col justify-center">
            {{ $slot }}
        </div>

    </div>

    @stack('scripts')
</body>
</html>
