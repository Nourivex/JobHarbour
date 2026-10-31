<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>JobHarbour · My Dock (Dashboard Pelamar & Linimasa Seleksi)</title>
<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&family=Plus+Jakarta+Sans:wght@100..900&display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "secondary-container": "#5bb8fe",
            "primary-container": "#131b2e",
            "outline": "#76777d",
            "on-primary-fixed": "#131b2e",
            "surface-container": "#eceef0",
            "tertiary-fixed-dim": "#ffb95f",
            "surface-container-lowest": "#ffffff",
            "on-primary": "#ffffff",
            "surface-container-low": "#f2f4f6",
            "tertiary-fixed": "#ffddb8",
            "on-secondary-fixed-variant": "#004b73",
            "secondary": "#006398",
            "on-tertiary-fixed-variant": "#653e00",
            "tertiary-container": "#2a1700",
            "on-secondary-fixed": "#001d31",
            "error": "#ba1a1a",
            "surface-tint": "#565e74",
            "primary-fixed-dim": "#bec6e0",
            "on-error": "#ffffff",
            "tertiary": "#000000",
            "surface-container-high": "#e6e8ea",
            "surface-variant": "#e0e3e5",
            "surface": "#f7f9fb",
            "on-secondary": "#ffffff",
            "surface-container-highest": "#e0e3e5",
            "primary": "#000000",
            "outline-variant": "#c6c6cd",
            "inverse-on-surface": "#eff1f3",
            "secondary-fixed": "#cce5ff",
            "primary-fixed": "#dae2fd",
            "on-tertiary-fixed": "#2a1700",
            "inverse-surface": "#2d3133",
            "surface-dim": "#d8dadc",
            "on-error-container": "#93000a",
            "secondary-fixed-dim": "#93ccff",
            "inverse-primary": "#bec6e0",
            "error-container": "#ffdad6",
            "on-surface": "#191c1e",
            "on-surface-variant": "#45464d",
            "on-background": "#191c1e",
            "on-tertiary": "#ffffff",
            "surface-bright": "#f7f9fb",
            "background": "#f7f9fb",
            "on-primary-fixed-variant": "#3f465c",
            "on-secondary-container": "#00476e",
            "on-tertiary-container": "#b87500",
            "on-primary-container": "#7c839b"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "spacing": {
            "margin-desktop": "2.5rem",
            "space-3xl": "3rem",
            "gutter-desktop": "1.5rem",
            "margin": "1rem",
            "gutter": "1rem",
            "space-xs": "0.375rem",
            "space-md": "0.75rem",
            "space-xl": "1.5rem",
            "space-lg": "1rem",
            "margin-tablet": "1.5rem",
            "space-2xl": "2rem",
            "space-2xs": "0.25rem",
            "space-sm": "0.5rem"
          },
          "fontFamily": {
            "label-sm": ["Inter"],
            "label-md": ["Inter"],
            "display-lg": ["Plus Jakarta Sans"],
            "headline-lg": ["Plus Jakarta Sans"],
            "coordinate-tag": ["Inter"],
            "headline-sm": ["Plus Jakarta Sans"],
            "headline-lg-mobile": ["Plus Jakarta Sans"],
            "body-sm": ["Inter"],
            "display-lg-mobile": ["Plus Jakarta Sans"],
            "body-lg": ["Inter"],
            "headline-md": ["Plus Jakarta Sans"],
            "body-md": ["Inter"]
          },
          "fontSize": {
            "label-sm": ["0.75rem", { "lineHeight": "1.2", "letterSpacing": "0.025em", "fontWeight": "600" }],
            "label-md": ["0.875rem", { "lineHeight": "1.25", "letterSpacing": "0.01em", "fontWeight": "600" }],
            "display-lg": ["3rem", { "lineHeight": "1.15", "letterSpacing": "-0.025em", "fontWeight": "700" }],
            "headline-lg": ["2rem", { "lineHeight": "1.25", "letterSpacing": "-0.02em", "fontWeight": "700" }],
            "coordinate-tag": ["0.6875rem", { "lineHeight": "1.1", "letterSpacing": "0.08em", "fontWeight": "700" }],
            "headline-sm": ["1.125rem", { "lineHeight": "1.4", "letterSpacing": "-0.005em", "fontWeight": "600" }],
            "headline-lg-mobile": ["1.5rem", { "lineHeight": "1.3", "letterSpacing": "-0.015em", "fontWeight": "600" }],
            "body-sm": ["0.8125rem", { "lineHeight": "1.45", "letterSpacing": "0.005em", "fontWeight": "400" }],
            "display-lg-mobile": ["2.25rem", { "lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700" }],
            "body-lg": ["1.0625rem", { "lineHeight": "1.55", "letterSpacing": "0em", "fontWeight": "400" }],
            "headline-md": ["1.375rem", { "lineHeight": "1.35", "letterSpacing": "-0.01em", "fontWeight": "600" }],
            "body-md": ["0.9375rem", { "lineHeight": "1.5", "letterSpacing": "0em", "fontWeight": "400" }]
          }
        },
      },
    }
  </script>
</head>
<body class="bg-surface text-on-surface antialiased min-h-screen flex flex-col font-body-md selection:bg-secondary-fixed selection:text-on-secondary-fixed">
<!-- TOP NAVIGATION DOCK BAR -->
@include('layouts.header')
<!-- MAIN VIEWPORT CONTAINER -->
<main class="flex-grow pt-24 pb-space-3xl max-w-[1600px] w-full mx-auto px-margin-desktop">
<!-- BREADCRUMB & HARBOUR TELEMETRY METRIC BANNER -->
<div class="mb-space-xl flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-outline-variant/40 pb-5">
<div>
<div class="flex items-center gap-2 text-coordinate-tag font-coordinate-tag text-on-surface-variant uppercase tracking-widest text-[11px] mb-1">
<span class="flex items-center gap-1 font-bold text-secondary">
<span class="material-symbols-outlined text-[14px]">explore</span>
            PORT LOG
          </span>
<span>//</span>
<span>BUDI SANTOSO (SEAFARER ID #82910)</span>
<span>•</span>
<span class="text-on-surface font-semibold">STATUS: 4 ACTIVE EXPEDITIONS</span>
</div>
<h1 class="text-headline-lg font-headline-lg text-on-surface font-extrabold tracking-tight">
          My Dock: Dermaga Lamaran & Status Pelayaran Karier
        </h1>
<p class="text-body-sm font-body-sm text-on-surface-variant mt-1 max-w-3xl">
          Pantau setiap tahapan seleksi, hasil verifikasi berkas, jadwal wawancara, dan keputusan penerimaan kerja secara transparan.
        </p>
</div>
<!-- Quick Triage Action Pills -->
<div class="flex items-center gap-2 self-start md:self-auto">
<button class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant text-on-surface rounded-lg text-label-sm font-label-sm flex items-center gap-1.5 hover:bg-surface-container transition-colors shadow-sm">
<span class="material-symbols-outlined text-[16px]">tune</span>
<span>Kompak Tampilan</span>
</button>
<button class="px-3 py-1.5 bg-secondary-fixed text-on-secondary-fixed-variant rounded-lg text-label-sm font-label-sm font-semibold flex items-center gap-1.5 hover:bg-secondary-fixed-dim transition-colors">
<span class="material-symbols-outlined text-[16px]">sync</span>
<span>Sinkronisasi Data Port</span>
</button>
</div>
</div>
<!-- 4 NAUTICAL SUMMARY METRIC CARDS -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-gutter-desktop mb-space-2xl">
<!-- Card 1: Total Lamaran -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl border border-outline-variant/60 shadow-sm flex flex-col justify-between hover:border-secondary transition-all">
<div class="flex items-center justify-between mb-3">
<span class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider text-on-surface-variant text-[11px]">PORT REGISTRY</span>
<div class="w-8 h-8 rounded-lg bg-surface-container flex items-center justify-center text-on-surface">
<span class="material-symbols-outlined text-[18px]">folder_special</span>
</div>
</div>
<div>
<div class="flex items-baseline gap-2">
<span class="text-headline-lg font-headline-lg font-bold text-on-surface">4</span>
<span class="text-label-md font-label-md text-on-surface-variant">Berkas</span>
</div>
<div class="text-label-sm font-label-sm font-semibold text-on-surface mt-0.5">Total Lamaran Terkirim</div>
</div>
<div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag">
<span class="px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-semibold">Semua Armada Aktif</span>
<span class="text-on-surface-variant">100% Terdaftar</span>
</div>
</div>
<!-- Card 2: Screening & Review -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl border border-outline-variant/60 shadow-sm flex flex-col justify-between hover:border-secondary transition-all">
<div class="flex items-center justify-between mb-3">
<span class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider text-secondary text-[11px]">INSPECTION PHASE</span>
<div class="w-8 h-8 rounded-lg bg-secondary-fixed text-on-secondary-fixed-variant flex items-center justify-center">
<span class="material-symbols-outlined text-[18px]">fact_check</span>
</div>
</div>
<div>
<div class="flex items-baseline gap-2">
<span class="text-headline-lg font-headline-lg font-bold text-secondary">2</span>
<span class="text-label-md font-label-md text-on-surface-variant">Berkas</span>
</div>
<div class="text-label-sm font-label-sm font-semibold text-on-surface mt-0.5">Tahap Screening & Review</div>
</div>
<div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag">
<span class="px-2 py-0.5 rounded bg-secondary-fixed/50 text-on-secondary-fixed-variant font-semibold">Verifikasi Berkas Berjalan</span>
<span class="text-secondary font-bold">50% Queue</span>
</div>
</div>
<!-- Card 3: Jadwal Interview Aktif -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl border border-tertiary-fixed-dim/80 shadow-sm flex flex-col justify-between ring-1 ring-tertiary-fixed-dim/40">
<div class="flex items-center justify-between mb-3">
<span class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider text-on-tertiary-fixed-variant text-[11px]">SIGNAL RADAR</span>
<div class="w-8 h-8 rounded-lg bg-tertiary-fixed text-on-tertiary-fixed-variant flex items-center justify-center">
<span class="material-symbols-outlined text-[18px]">video_call</span>
</div>
</div>
<div>
<div class="flex items-baseline gap-2">
<span class="text-headline-lg font-headline-lg font-bold text-on-tertiary-fixed-variant">1</span>
<span class="text-label-md font-label-md text-on-surface-variant">Wawancara</span>
</div>
<div class="text-label-sm font-label-sm font-semibold text-on-surface mt-0.5">Jadwal Interview Aktif</div>
</div>
<div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag">
<span class="px-2 py-0.5 rounded bg-tertiary-fixed text-on-tertiary-fixed-variant font-semibold flex items-center gap-1.5">
<span class="w-1.5 h-1.5 rounded-full bg-error animate-ping"></span>
            10 Okt 2026, 10:00 WIB
          </span>
<span class="text-error font-bold">Prioritas</span>
</div>
</div>
<!-- Card 4: Tawaran Kerja (Offering) -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl border border-outline-variant/60 shadow-sm flex flex-col justify-between hover:border-secondary transition-all">
<div class="flex items-center justify-between mb-3">
<span class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider text-on-surface-variant text-[11px]">FINAL PORT HARBOUR</span>
<div class="w-8 h-8 rounded-lg bg-surface-container-high text-on-surface flex items-center justify-center">
<span class="material-symbols-outlined text-[18px]" data-weight="fill">verified</span>
</div>
</div>
<div>
<div class="flex items-baseline gap-2">
<span class="text-headline-lg font-headline-lg font-bold text-on-surface">1</span>
<span class="text-label-md font-label-md text-on-surface-variant">Tawaran</span>
</div>
<div class="text-label-sm font-label-sm font-semibold text-on-surface mt-0.5">Tawaran Kerja (Offering)</div>
</div>
<div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag">
<span class="px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-semibold">Menunggu Keputusan Kapten</span>
<span class="text-on-surface font-bold">1 Valid</span>
</div>
</div>
</section>
<!-- DUAL COMPONENT MAIN WORKSPACE (Desktop Split Grid: 62% Applications Table + 38% Voyage Pipeline Stepper) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-start">
<!-- LEFT SECTION: DAFTAR BERKAS LAMARAN AKTIF (62% => col-span-7 or 8, using 7.5 ratio => lg:col-span-7) -->
<section class="lg:col-span-7 flex flex-col gap-space-lg">
<!-- Header & Search Toolbar Panel -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl border border-outline-variant/60 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<div class="flex items-center gap-2">
<div class="w-2.5 h-2.5 rounded-full bg-secondary"></div>
<h2 class="text-headline-sm font-headline-sm text-on-surface font-bold">Daftar Berkas Lamaran Aktif</h2>
<span class="px-2 py-0.5 rounded-full bg-surface-container text-on-surface-variant text-label-sm font-label-sm font-bold">
              4 Ekspedisi
            </span>
</div>
<!-- Controls: Input Search & Dropdown -->
<div class="flex items-center gap-2">
<div class="relative">
<span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
<input class="pl-8 pr-3 py-1.5 text-body-sm font-body-sm bg-surface-container-low border border-outline-variant/60 rounded-lg text-on-surface focus:outline-none focus:border-secondary w-48 sm:w-56" placeholder="Cari perusahaan / posisi..." type="text" value="Backend Engineer"/>
</div>
<select class="py-1.5 px-3 text-body-sm font-body-sm bg-surface-container-low border border-outline-variant/60 rounded-lg text-on-surface focus:outline-none focus:border-secondary">
<option>Semua Status</option>
<option>Screening</option>
<option selected="">Interview</option>
<option>Offering</option>
</select>
</div>
</div>
<!-- APPLICATIONS LIST CONTAINER -->
<div class="space-y-3.5">
<!-- ITEM 1 (SELECTED / ACTIVE FOCUS STATE) -->
<article class="bg-surface-container-lowest rounded-xl border-2 border-secondary p-space-lg shadow-md relative overflow-hidden transition-all duration-150">
<!-- Left Accent Anchor Rail -->
<div class="absolute left-0 top-0 bottom-0 w-1.5 bg-secondary"></div>
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pl-2">
<div class="space-y-1">
<div class="flex items-center gap-2 flex-wrap">
<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag uppercase tracking-wider bg-secondary-fixed text-on-secondary-fixed-variant font-bold">
                    BERTH 04 • ACTIVE SELECTION
                  </span>
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant">DIDAFTARKAN 28 SEP 2026</span>
</div>
<h3 class="text-headline-sm font-headline-sm font-bold text-on-surface">
                  Backend Engineer (Microservices)
                </h3>
<p class="text-body-sm font-body-sm text-on-surface-variant flex items-center gap-2">
<span class="font-medium text-on-surface">PT Solusi Bahari Digital</span>
<span>•</span>
<span>Tegal (Hybrid)</span>
</p>
</div>
<!-- Anchor Fit Match Signal Score -->
<div class="flex sm:flex-col items-center sm:items-end justify-between gap-1 bg-surface-container-low sm:bg-transparent p-2 sm:p-0 rounded-lg">
<div class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-secondary-fixed text-on-secondary-fixed-variant font-bold text-label-sm border border-secondary/20">
<span class="material-symbols-outlined text-[15px]" data-weight="fill">anchor</span>
<span>92% Anchor Fit</span>
</div>
<span class="text-coordinate-tag font-coordinate-tag text-secondary font-bold text-[10px] uppercase tracking-wider">Sangat Cocok</span>
</div>
</div>
<!-- Telemetry Bar -->
<div class="my-3 pl-2">
<div class="w-full bg-surface-container rounded-full h-1.5 overflow-hidden flex">
<div class="bg-secondary w-[92%] h-full"></div>
<div class="bg-surface-container-high w-[8%] h-full"></div>
</div>
</div>
<!-- Bottom Row: Status Badge, CV Chip, Navigation Action -->
<div class="pt-3 border-t border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3 pl-2">
<div class="flex items-center gap-2 flex-wrap">
<!-- Status Badge -->
<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-tertiary-fixed text-on-tertiary-fixed-variant text-label-sm font-label-sm font-semibold border border-tertiary-fixed-dim/40">
<span class="material-symbols-outlined text-[15px]">event</span>
<span>Tahap 3: Wawancara Tim Teknis</span>
</div>
<!-- CV Verified Chip -->
<div class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-surface-container text-on-surface text-coordinate-tag font-coordinate-tag border border-outline-variant/60">
<span class="material-symbols-outlined text-[14px] text-error">picture_as_pdf</span>
<span>CV_Budi_Santoso_2026.pdf</span>
<span class="material-symbols-outlined text-[13px] text-secondary ml-0.5" data-weight="fill">verified</span>
</div>
</div>
<!-- Button CTA -->
<button class="px-3.5 py-1.5 rounded-lg bg-secondary text-on-secondary text-label-sm font-label-sm font-semibold flex items-center justify-center gap-1.5 shadow-sm hover:bg-on-secondary-fixed-variant transition-colors self-end sm:self-auto">
<span>Linimasa Terpilih</span>
<span class="material-symbols-outlined text-[16px]">arrow_forward</span>
</button>
</div>
</article>
<!-- ITEM 2: Fullstack Web Developer -->
<article class="bg-surface-container-lowest rounded-xl border border-outline-variant/60 p-space-lg shadow-sm hover:border-outline transition-all">
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
<div class="space-y-1">
<div class="flex items-center gap-2 flex-wrap">
<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag uppercase tracking-wider bg-surface-container text-on-surface-variant font-semibold">
                    BERTH 02
                  </span>
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant">DIDAFTARKAN 25 SEP 2026</span>
</div>
<h3 class="text-headline-sm font-headline-sm font-bold text-on-surface">
                  Fullstack Web Developer
                </h3>
<p class="text-body-sm font-body-sm text-on-surface-variant flex items-center gap-2">
<span class="font-medium text-on-surface">PT Merpati Logistik Bahari</span>
<span>•</span>
<span>Semarang (On-site)</span>
</p>
</div>
<div class="flex sm:flex-col items-center sm:items-end justify-between gap-1">
<div class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-surface-container text-on-surface-variant font-bold text-label-sm border border-outline-variant/60">
<span class="material-symbols-outlined text-[15px]">anchor</span>
<span>84% Match</span>
</div>
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant text-[10px] uppercase tracking-wider">Kompeten</span>
</div>
</div>
<div class="pt-3 mt-3 border-t border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
<div class="flex items-center gap-2 flex-wrap">
<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-secondary-fixed/40 text-on-secondary-fixed-variant text-label-sm font-label-sm font-semibold border border-outline-variant/40">
<span class="material-symbols-outlined text-[15px]">code_blocks</span>
<span>Tahap 2: Assessment Teknis</span>
</div>
<div class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-surface-container text-on-surface text-coordinate-tag font-coordinate-tag">
<span class="material-symbols-outlined text-[14px] text-error">picture_as_pdf</span>
<span>CV_Budi_Santoso_2026.pdf</span>
</div>
</div>
<button class="px-3.5 py-1.5 rounded-lg border border-outline-variant text-on-surface hover:bg-surface-container text-label-sm font-label-sm font-medium flex items-center justify-center gap-1.5 transition-colors self-end sm:self-auto">
<span>Lihat Detail</span>
<span class="material-symbols-outlined text-[16px]">visibility</span>
</button>
</div>
</article>
<!-- ITEM 3: Cloud DevOps & Infrastructure -->
<article class="bg-surface-container-lowest rounded-xl border border-outline-variant/60 p-space-lg shadow-sm hover:border-outline transition-all">
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
<div class="space-y-1">
<div class="flex items-center gap-2 flex-wrap">
<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag uppercase tracking-wider bg-surface-container-highest text-on-surface font-semibold">
                    BERTH 01 • OFFERING READY
                  </span>
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant">DIDAFTARKAN 15 SEP 2026</span>
</div>
<h3 class="text-headline-sm font-headline-sm font-bold text-on-surface">
                  Cloud DevOps & Infrastructure
                </h3>
<p class="text-body-sm font-body-sm text-on-surface-variant flex items-center gap-2">
<span class="font-medium text-on-surface">PT Bahari Solusi</span>
<span>•</span>
<span>Jakarta (Remote)</span>
</p>
</div>
<div class="flex sm:flex-col items-center sm:items-end justify-between gap-1">
<div class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-surface-container text-on-surface-variant font-bold text-label-sm border border-outline-variant/60">
<span class="material-symbols-outlined text-[15px]">anchor</span>
<span>88% Match</span>
</div>
<span class="text-coordinate-tag font-coordinate-tag text-on-surface font-bold text-[10px] uppercase tracking-wider">Prioritas</span>
</div>
</div>
<div class="pt-3 mt-3 border-t border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
<div class="flex items-center gap-2 flex-wrap">
<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-surface-container-high text-on-surface text-label-sm font-label-sm font-bold border border-outline-variant">
<span class="material-symbols-outlined text-[15px]" data-weight="fill">contract</span>
<span>Tahap 4: Penawaran Kerja (Offering)</span>
</div>
<div class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-surface-container text-on-surface text-coordinate-tag font-coordinate-tag">
<span class="material-symbols-outlined text-[14px] text-error">picture_as_pdf</span>
<span>CV_Budi_Santoso_2026.pdf</span>
</div>
</div>
<button class="px-3.5 py-1.5 rounded-lg bg-primary-container text-on-primary hover:bg-on-primary-fixed-variant text-label-sm font-label-sm font-semibold flex items-center justify-center gap-1.5 transition-colors self-end sm:self-auto">
<span class="material-symbols-outlined text-[16px]">rate_review</span>
<span>Review Penawaran</span>
</button>
</div>
</article>
<!-- ITEM 4: Frontend Developer (React/TypeScript) -->
<article class="bg-surface-container-lowest rounded-xl border border-outline-variant/60 p-space-lg shadow-sm hover:border-outline transition-all">
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
<div class="space-y-1">
<div class="flex items-center gap-2 flex-wrap">
<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag uppercase tracking-wider bg-surface-container text-on-surface-variant font-semibold">
                    BERTH 05
                  </span>
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant">DIDAFTARKAN 02 OKT 2026</span>
</div>
<h3 class="text-headline-sm font-headline-sm font-bold text-on-surface">
                  Frontend Developer (React/TypeScript)
                </h3>
<p class="text-body-sm font-body-sm text-on-surface-variant flex items-center gap-2">
<span class="font-medium text-on-surface">PT Armada Pelabuhan Mandiri</span>
<span>•</span>
<span>Tegal</span>
</p>
</div>
<div class="flex sm:flex-col items-center sm:items-end justify-between gap-1">
<div class="flex items-center gap-1.5 px-2.5 py-1 rounded bg-surface-container text-on-surface-variant font-bold text-label-sm border border-outline-variant/60">
<span class="material-symbols-outlined text-[15px]">anchor</span>
<span>71% Match</span>
</div>
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant text-[10px] uppercase tracking-wider">Cukup</span>
</div>
</div>
<div class="pt-3 mt-3 border-t border-outline-variant/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
<div class="flex items-center gap-2 flex-wrap">
<div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-surface-container text-on-surface-variant text-label-sm font-label-sm font-semibold border border-outline-variant/60">
<span class="material-symbols-outlined text-[15px]">hourglass_top</span>
<span>Tahap 1: Screening Berkas</span>
</div>
<div class="inline-flex items-center gap-1 px-2 py-1 rounded-md bg-surface-container text-on-surface text-coordinate-tag font-coordinate-tag">
<span class="material-symbols-outlined text-[14px] text-error">picture_as_pdf</span>
<span>CV_Budi_Santoso_2026.pdf</span>
</div>
</div>
<!-- Destructive Action -->
<button class="px-3.5 py-1.5 rounded-lg text-error hover:bg-error-container text-label-sm font-label-sm font-medium flex items-center justify-center gap-1 transition-colors self-end sm:self-auto">
<span class="material-symbols-outlined text-[15px]">shield</span>
<span>Batalkan Lamaran</span>
</button>
</div>
</article>
</div>
<!-- Pagination or Bottom Notice -->
<div class="p-3 bg-surface-container rounded-lg flex items-center justify-between text-body-sm font-body-sm text-on-surface-variant">
<span class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider">MENAMPILKAN 4 DARI 4 BERKAS AKTIF</span>
<span class="flex items-center gap-1 text-label-sm font-label-sm text-on-surface">
<span class="material-symbols-outlined text-[16px] text-secondary">security</span>
            Seluruh berkas dilindungi Protokol Pelaut IMO
          </span>
</div>
</section>
<!-- RIGHT SECTION: VOYAGE PIPELINE STEPPER DRAWER (38% => lg:col-span-5) -->
<aside class="lg:col-span-5 sticky top-20">
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/60 shadow-sm overflow-hidden">
<!-- Stepper Drawer Header -->
<div class="p-space-lg bg-primary-container text-on-primary">
<div class="flex items-center justify-between mb-2">
<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag uppercase tracking-wider bg-surface-container-lowest/15 text-primary-fixed font-bold flex items-center gap-1">
<span class="material-symbols-outlined text-[12px]">directions_boat</span>
                VOYAGE TRACKER // EXPRESS ENTRY
              </span>
<span class="text-coordinate-tag font-coordinate-tag text-primary-fixed-dim uppercase tracking-widest text-[11px]">BERTH 04</span>
</div>
<h3 class="text-headline-sm font-headline-sm font-bold text-on-primary">
              Backend Engineer (Microservices)
            </h3>
<p class="text-body-sm font-body-sm text-primary-fixed-dim mt-0.5">
              PT Solusi Bahari Digital • Tegal (Hybrid)
            </p>
<div class="mt-3 pt-3 border-t border-on-primary-fixed-variant/50 flex items-center justify-between">
<span class="text-coordinate-tag font-coordinate-tag text-primary-fixed uppercase tracking-wider">SKOR KECOCOKAN REGISTRI</span>
<span class="px-2 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed-variant font-bold text-label-sm">92% Anchor Fit</span>
</div>
</div>
<!-- VERTICAL STEPPER WITH NAUTICAL CONNECTING TRACK -->
<div class="p-space-lg relative space-y-6">
<!-- Continuous Line Rail -->
<div class="absolute left-[33px] top-6 bottom-10 w-0.5 bg-outline-variant/50 z-0"></div>
<!-- STEP 1: Screening Berkas (COMPLETED) -->
<div class="relative z-10 flex gap-3.5">
<div class="w-8 h-8 rounded-full bg-surface-container-highest border-2 border-outline flex items-center justify-center text-on-surface shrink-0">
<span class="material-symbols-outlined text-[16px] font-bold" data-weight="fill">check</span>
</div>
<div class="flex-grow pt-0.5">
<div class="flex items-center justify-between">
<h4 class="text-label-md font-label-md font-bold text-on-surface">Screening Berkas & Verifikasi CV</h4>
<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag bg-surface-container text-on-surface-variant font-bold">LOLOS</span>
</div>
<div class="text-coordinate-tag font-coordinate-tag text-on-surface-variant mt-0.5">
                  Selesai pada 01 Okt 2026 • 14:30 WIB
                </div>
<!-- Recruiter Note Quote -->
<div class="mt-2 p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/60 text-body-sm font-body-sm text-on-surface-variant italic">
                  “Kualifikasi teknis dan berkas CV terverifikasi lengkap sesuai standar IMO-Crew.”
                </div>
</div>
</div>
<!-- STEP 2: Review Teknis & Tes Koding (COMPLETED) -->
<div class="relative z-10 flex gap-3.5">
<div class="w-8 h-8 rounded-full bg-surface-container-highest border-2 border-outline flex items-center justify-center text-on-surface shrink-0">
<span class="material-symbols-outlined text-[16px] font-bold" data-weight="fill">check</span>
</div>
<div class="flex-grow pt-0.5">
<div class="flex items-center justify-between">
<h4 class="text-label-md font-label-md font-bold text-on-surface">Review Teknis & Tes Koding</h4>
<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag bg-surface-container text-on-surface-variant font-bold">SKOR 92/100</span>
</div>
<div class="text-coordinate-tag font-coordinate-tag text-on-surface-variant mt-0.5">
                  Lolos pada 04 Okt 2026 • Evaluator Lead System
                </div>
<!-- Recruiter Note Quote -->
<div class="mt-2 p-2.5 rounded-lg bg-surface-container-low border border-outline-variant/60 text-body-sm font-body-sm text-on-surface-variant italic">
                  “Hasil implementasi REST API dan pemahaman arsitektur database microservices memuaskan.”
                </div>
</div>
</div>
<!-- STEP 3: Wawancara Tim Teknis (ACTIVE / CURRENT PORT) -->
<div class="relative z-10 flex gap-3.5">
<!-- Pulsing Active Anchor Node -->
<div class="relative shrink-0">
<div class="w-8 h-8 rounded-full bg-tertiary-fixed border-2 border-tertiary-fixed-dim text-on-tertiary-fixed-variant flex items-center justify-center shadow-md">
<span class="material-symbols-outlined text-[18px]" data-weight="fill">anchor</span>
</div>
<span class="absolute -inset-1 rounded-full bg-tertiary-fixed-dim opacity-40 animate-ping"></span>
</div>
<div class="flex-grow pt-0.5">
<div class="flex items-center justify-between">
<h4 class="text-label-md font-label-md font-bold text-on-tertiary-fixed-variant">
                    Wawancara Tim Teknis & HR (Interview)
                  </h4>
<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag bg-tertiary-fixed text-on-tertiary-fixed-variant font-bold border border-tertiary-fixed-dim/60">
                    CURRENT PORT
                  </span>
</div>
<div class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-tertiary-fixed/60 text-on-tertiary-fixed-variant font-coordinate-tag font-bold text-[11px] mt-1.5">
<span class="material-symbols-outlined text-[13px]">alarm</span>
<span>Dijadwalkan: 10 Okt 2026, 10:00 WIB</span>
</div>
<!-- Video Call Box Card -->
<div class="mt-3 p-3 rounded-lg bg-surface border border-outline-variant/60 space-y-2.5">
<div class="flex items-center justify-between text-body-sm font-body-sm">
<span class="font-semibold text-on-surface flex items-center gap-1.5">
<span class="material-symbols-outlined text-[18px] text-secondary">videocam</span>
                      Google Meet Ruang Interview
                    </span>
<span class="text-coordinate-tag font-coordinate-tag text-secondary font-bold">ONLINE</span>
</div>
<!-- Meet link snippet -->
<div class="flex items-center justify-between p-2 rounded bg-surface-container border border-outline-variant/60 text-coordinate-tag font-coordinate-tag text-on-surface-variant">
<span class="truncate">meet.google.com/jobharbour-interview</span>
<button class="text-secondary hover:underline font-bold text-[11px] ml-2 shrink-0 flex items-center gap-0.5">
<span class="material-symbols-outlined text-[13px]">content_copy</span>
                      Salin
                    </button>
</div>
<a class="w-full py-2 rounded-lg bg-secondary text-on-secondary text-label-sm font-label-sm font-semibold flex items-center justify-center gap-1.5 hover:bg-on-secondary-fixed-variant transition-colors shadow-sm" href="/">
<span class="material-symbols-outlined text-[16px]">open_in_new</span>
<span>Buka Ruang Meet</span>
</a>
<!-- Interviewer Details -->
<div class="pt-2 border-t border-outline-variant/40 space-y-1 text-coordinate-tag font-coordinate-tag text-on-surface-variant text-[11px]">
<div class="flex items-center justify-between">
<span>PENGUJI MARITIM TECH:</span>
<span class="font-bold text-on-surface">Capt. Hendra Wijaya (VP Eng.)</span>
</div>
<div class="flex items-center justify-between">
<span>HARBOUR RECRUITER:</span>
<span class="font-bold text-on-surface">Sarah L. (Talent Acquisition)</span>
</div>
</div>
<!-- Note -->
<div class="p-2 bg-tertiary-fixed/30 rounded text-body-sm font-body-sm text-on-tertiary-fixed-variant text-[11px] flex items-start gap-1.5">
<span class="material-symbols-outlined text-[14px] shrink-0 mt-0.5">info</span>
<span>Harap siapkan demo repo GitHub microservice dan arsitektur Docker terkini.</span>
</div>
</div>
</div>
</div>
<!-- STEP 4: Offering (UPCOMING) -->
<div class="relative z-10 flex gap-3.5 opacity-60">
<div class="w-8 h-8 rounded-full bg-surface-container border-2 border-outline-variant flex items-center justify-center text-outline shrink-0">
<span class="material-symbols-outlined text-[16px]">description</span>
</div>
<div class="flex-grow pt-0.5">
<h4 class="text-label-md font-label-md font-semibold text-on-surface">
                  Penawaran Kerja & Negosiasi Kontrak (Offering)
                </h4>
<div class="text-coordinate-tag font-coordinate-tag text-on-surface-variant mt-0.5">
                  Estimasi: 14 - 16 Okt 2026
                </div>
</div>
</div>
<!-- STEP 5: Final Port Onboarding (UPCOMING) -->
<div class="relative z-10 flex gap-3.5 opacity-60">
<div class="w-8 h-8 rounded-full bg-surface-container border-2 border-outline-variant flex items-center justify-center text-outline shrink-0">
<span class="material-symbols-outlined text-[16px]">flag</span>
</div>
<div class="flex-grow pt-0.5">
<h4 class="text-label-md font-label-md font-semibold text-on-surface">
                  Keberangkatan / Resmi Bergabung (Onboarding)
                </h4>
<div class="text-coordinate-tag font-coordinate-tag text-on-surface-variant mt-0.5">
                  Target Pelayaran: 01 Nov 2026
                </div>
</div>
</div>
</div>
<!-- BOTTOM ACTION BAR OF DRAWER -->
<div class="p-space-lg bg-surface-container-low border-t border-outline-variant/60 flex flex-col gap-2">
<button class="w-full py-2.5 px-3 rounded-lg border border-outline-variant/80 bg-surface-container-lowest hover:bg-surface-container text-on-surface text-label-md font-label-md font-semibold flex items-center justify-center gap-2 transition-colors shadow-sm">
<span class="material-symbols-outlined text-[18px]">chat</span>
<span>Kirim Pesan ke HR Port Master</span>
</button>
<button class="w-full py-2 px-3 rounded-lg text-on-surface-variant hover:text-on-surface text-label-sm font-label-sm font-medium flex items-center justify-center gap-1.5 transition-colors">
<span class="material-symbols-outlined text-[16px]">download</span>
<span>Unduh Manifes Seleksi (PDF)</span>
</button>
</div>
</div>
</aside>
</div>
</main>
<!-- FOOTER COMPONENT -->
<footer class="w-full py-space-xl px-margin-desktop flex flex-col md:flex-row justify-between items-center gap-space-lg max-w-[1600px] mx-auto border-t border-outline-variant/60">
<div class="flex flex-col sm:flex-row items-center gap-3">
<span class="text-label-md font-label-md font-bold text-on-surface flex items-center gap-1">
<span class="material-symbols-outlined text-[16px]" data-weight="fill">anchor</span>
        JobHarbour
      </span>
<span class="hidden sm:inline text-outline-variant">|</span>
<p class="text-body-sm font-body-sm text-on-surface-variant text-center sm:text-left">
        © 2025 JobHarbour. Hak Cipta Dilindungi Undang-Undang. Pelabuhan Karir & Talenta Maritim Indonesia.
      </p>
</div>
<!-- Links from JSON -->
<div class="flex items-center gap-6 text-label-sm font-label-sm flex-wrap justify-center">
<a class="text-on-surface-variant hover:text-secondary transition-colors" href="/">
        Ketentuan Layanan
      </a>
<a class="text-on-surface-variant hover:text-secondary transition-colors" href="/">
        Kebijakan Privasi
      </a>
<a class="text-on-surface-variant hover:text-secondary transition-colors" href="/">
        Daftar Pelabuhan
      </a>
<a class="text-on-surface-variant hover:text-secondary transition-colors" href="/">
        Bantuan Kapten
      </a>
</div>
</footer>
</body></html>