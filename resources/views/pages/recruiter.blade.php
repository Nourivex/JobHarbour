<!DOCTYPE html>

<html class="light" lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Harbour Master Deck — JobHarbour Recruiter Command</title>
<!-- Fonts & Material Symbols -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Theme Configuration -->
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "on-error-container": "#93000a",
            "tertiary-fixed": "#ffddb8",
            "outline": "#76777d",
            "on-tertiary": "#ffffff",
            "surface-bright": "#f7f9fb",
            "on-error": "#ffffff",
            "on-background": "#191c1e",
            "primary-fixed": "#dae2fd",
            "surface-variant": "#e0e3e5",
            "surface-container-highest": "#e0e3e5",
            "on-secondary": "#ffffff",
            "on-surface-variant": "#45464d",
            "tertiary-fixed-dim": "#ffb95f",
            "on-tertiary-container": "#b87500",
            "secondary-container": "#5bb8fe",
            "error-container": "#ffdad6",
            "on-secondary-fixed-variant": "#004b73",
            "on-secondary-fixed": "#001d31",
            "inverse-surface": "#2d3133",
            "secondary-fixed": "#cce5ff",
            "on-primary-container": "#7c839b",
            "outline-variant": "#c6c6cd",
            "on-tertiary-fixed": "#2a1700",
            "primary-fixed-dim": "#bec6e0",
            "primary": "#000000",
            "error": "#ba1a1a",
            "on-primary-fixed-variant": "#3f465c",
            "surface-container-high": "#e6e8ea",
            "surface-tint": "#565e74",
            "on-tertiary-fixed-variant": "#653e00",
            "surface-container": "#eceef0",
            "surface-container-lowest": "#ffffff",
            "surface": "#f7f9fb",
            "inverse-on-surface": "#eff1f3",
            "on-surface": "#191c1e",
            "surface-dim": "#d8dadc",
            "surface-container-low": "#f2f4f6",
            "tertiary": "#000000",
            "primary-container": "#131b2e",
            "inverse-primary": "#bec6e0",
            "secondary": "#006398",
            "secondary-fixed-dim": "#93ccff",
            "tertiary-container": "#2a1700",
            "on-primary-fixed": "#131b2e",
            "on-secondary-container": "#00476e",
            "on-primary": "#ffffff",
            "background": "#f7f9fb"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "spacing": {
            "space-sm": "0.5rem",
            "gutter": "1rem",
            "margin": "1rem",
            "space-xs": "0.375rem",
            "space-md": "0.75rem",
            "space-xl": "1.5rem",
            "space-3xl": "3rem",
            "space-2xs": "0.25rem",
            "space-lg": "1rem",
            "space-2xl": "2rem",
            "gutter-desktop": "1.5rem",
            "margin-desktop": "2.5rem",
            "margin-tablet": "1.5rem"
          },
          "fontFamily": {
            "headline-md": ["Plus Jakarta Sans"],
            "body-lg": ["Inter"],
            "headline-sm": ["Plus Jakarta Sans"],
            "headline-lg": ["Plus Jakarta Sans"],
            "label-md": ["Inter"],
            "body-sm": ["Inter"],
            "headline-lg-mobile": ["Plus Jakarta Sans"],
            "display-lg": ["Plus Jakarta Sans"],
            "body-md": ["Inter"],
            "coordinate-tag": ["Inter"],
            "label-sm": ["Inter"],
            "display-lg-mobile": ["Plus Jakarta Sans"]
          },
          "fontSize": {
            "headline-md": ["1.375rem", { "lineHeight": "1.35", "letterSpacing": "-0.01em", "fontWeight": "600" }],
            "body-lg": ["1.0625rem", { "lineHeight": "1.55", "letterSpacing": "0em", "fontWeight": "400" }],
            "headline-sm": ["1.125rem", { "lineHeight": "1.4", "letterSpacing": "-0.005em", "fontWeight": "600" }],
            "headline-lg": ["2rem", { "lineHeight": "1.25", "letterSpacing": "-0.02em", "fontWeight": "700" }],
            "label-md": ["0.875rem", { "lineHeight": "1.25", "letterSpacing": "0.01em", "fontWeight": "600" }],
            "body-sm": ["0.8125rem", { "lineHeight": "1.45", "letterSpacing": "0.005em", "fontWeight": "400" }],
            "headline-lg-mobile": ["1.5rem", { "lineHeight": "1.3", "letterSpacing": "-0.015em", "fontWeight": "600" }],
            "display-lg": ["3rem", { "lineHeight": "1.15", "letterSpacing": "-0.025em", "fontWeight": "700" }],
            "body-md": ["0.9375rem", { "lineHeight": "1.5", "letterSpacing": "0em", "fontWeight": "400" }],
            "coordinate-tag": ["0.6875rem", { "lineHeight": "1.1", "letterSpacing": "0.08em", "fontWeight": "700" }],
            "label-sm": ["0.75rem", { "lineHeight": "1.2", "letterSpacing": "0.025em", "fontWeight": "600" }],
            "display-lg-mobile": ["2.25rem", { "lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700" }]
          }
        }
      }
    }
  </script>
</head>
<body class="bg-background text-on-surface antialiased min-h-screen flex flex-col font-body-md text-body-md selection:bg-secondary-fixed selection:text-on-secondary-fixed">
<!-- TOP NAVIGATION BAR (Shared Component Source of Truth) -->
@include('layouts.header')
<!-- MAIN OPERATIONAL WORKSPACE -->
<main class="flex-1 mt-16 pb-12 w-full max-w-[1600px] mx-auto px-margin-desktop">
<!-- COMMAND TOOLBAR & DOSSIER HEADER -->
<section class="pt-6 pb-4 border-b border-outline-variant/50">
<!-- Coordinate Telemetry Breadcrumb -->
<div class="flex flex-wrap items-center justify-between gap-2 mb-2">
<div class="flex items-center gap-2">
<span class="inline-flex items-center gap-1 font-coordinate-tag text-coordinate-tag bg-surface-container-high text-on-surface-variant px-2 py-0.5 rounded tracking-widest border border-outline-variant/60">
<span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
            HARBOUR MASTER COMMAND // BERTH PIPELINE DISPATCH // PT SOLUSI BAHARI DIGITAL
          </span>
<span class="text-label-sm font-label-sm text-outline">LAT: -6.1021° S | LON: 106.8833° E (Tanjung Priok Node)</span>
</div>
<div class="flex items-center gap-2 text-label-sm font-label-sm text-on-surface-variant">
<span class="material-symbols-outlined text-sm" data-icon="sync">sync</span>
<span>Sinkronisasi Otomatis: <strong class="text-on-surface">Aktif (Live)</strong></span>
</div>
</div>
<!-- Title & Operational Purpose -->
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
<div>
<h1 class="text-headline-lg font-headline-lg text-primary tracking-tight">
            Harbour Master Deck: Manajemen Seleksi &amp; Pipeline Kandidat
          </h1>
<p class="text-body-md font-body-md text-on-surface-variant mt-1 max-w-4xl">
            Kelola alur penyeleksian talenta maritim tech secara visual, pantau skor kecocokan algoritma, jadwalkan wawancara teknis, dan kirim penawaran kerja dalam satu dermaga kerja.
          </p>
</div>
<!-- Quick Switch Vacancy Selector Dock -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-2.5 flex items-center gap-3 shadow-xs">
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-secondary">
<span class="material-symbols-outlined text-2xl" data-icon="directions_boat">directions_boat</span>
</div>
<div class="flex flex-col pr-2">
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant uppercase tracking-wider">Lowongan Terpilih Aktif</span>
<div class="flex items-center gap-2 font-headline-sm text-headline-sm text-on-surface">
<span class="font-bold">Backend Engineer (Microservices)</span>
<span class="bg-secondary-fixed text-on-secondary-fixed text-label-sm font-label-sm px-2 py-0.5 rounded-full font-semibold">3 Kuota</span>
</div>
<span class="text-label-sm font-label-sm text-outline">Dibuka: 01 Okt 2026 • Total 8 Kandidat Terdaftar</span>
</div>
<div class="flex items-center gap-1 border-l border-outline-variant/60 pl-2">
<button class="p-2 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-on-surface transition-colors flex items-center gap-1 text-label-sm font-label-sm font-semibold" title="Ganti Lowongan">
<span class="material-symbols-outlined text-lg" data-icon="swap_horiz">swap_horiz</span>
<span class="hidden md:inline">Ganti</span>
</button>
<button class="p-2 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-on-surface transition-colors" title="Ubah Parameter Lowongan">
<span class="material-symbols-outlined text-lg" data-icon="edit_note">edit_note</span>
</button>
</div>
</div>
</div>
</section>
<!-- METRICS TELEMETRY BAR & QUICK FILTER CLUSTERS -->
<section class="py-4 space-y-4">
<!-- 5-Metric Quick Stats Summary Cards -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-space-md">
<!-- 1. Total Pelamar -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-3.5 flex items-center justify-between">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant block uppercase">Total Masuk</span>
<span class="text-headline-md font-headline-md font-bold text-on-surface">8 Pelamar</span>
<span class="text-body-sm font-body-sm text-outline block text-[11px] mt-0.5">Semua Berkas Masuk</span>
</div>
<div class="w-9 h-9 rounded-lg bg-surface-container-low flex items-center justify-center text-outline">
<span class="material-symbols-outlined text-xl" data-icon="inbox">inbox</span>
</div>
</div>
<!-- 2. Review Teknis -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-3.5 flex items-center justify-between">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-secondary block uppercase">Tahap Uji</span>
<span class="text-headline-md font-headline-md font-bold text-secondary">3 di Review</span>
<span class="text-body-sm font-body-sm text-outline block text-[11px] mt-0.5">Code &amp; REST API</span>
</div>
<div class="w-9 h-9 rounded-lg bg-secondary-fixed/50 flex items-center justify-center text-secondary">
<span class="material-symbols-outlined text-xl" data-icon="terminal">terminal</span>
</div>
</div>
<!-- 3. Interview Radar -->
<div class="bg-surface-container-lowest border border-tertiary-fixed-dim/60 rounded-xl p-3.5 flex items-center justify-between bg-gradient-to-br from-surface-container-lowest to-tertiary-fixed/10">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-on-tertiary-container block uppercase font-bold">Signal Radar</span>
<span class="text-headline-md font-headline-md font-bold text-on-tertiary-container">2 Wawancara</span>
<span class="text-body-sm font-body-sm text-on-tertiary-container block text-[11px] mt-0.5">1 Jadwal Hari Ini</span>
</div>
<div class="w-9 h-9 rounded-lg bg-tertiary-fixed flex items-center justify-center text-on-tertiary-fixed">
<span class="material-symbols-outlined text-xl" data-icon="video_camera_front">video_camera_front</span>
</div>
</div>
<!-- 4. Penawaran Kerja -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-3.5 flex items-center justify-between">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-emerald-700 block uppercase">Siap Berlayar</span>
<span class="text-headline-md font-headline-md font-bold text-emerald-800">1 Offering</span>
<span class="text-body-sm font-body-sm text-outline block text-[11px] mt-0.5">Menunggu Tanda Tangan</span>
</div>
<div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-800">
<span class="material-symbols-outlined text-xl" data-icon="draft">draft</span>
</div>
</div>
<!-- 5. Ditolak / Belum Memenuhi -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-3.5 flex items-center justify-between">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-outline block uppercase">Arsip / Hold</span>
<span class="text-headline-md font-headline-md font-bold text-on-surface-variant">1 Ditolak</span>
<span class="text-body-sm font-body-sm text-outline block text-[11px] mt-0.5">Notifikasi Terkirim</span>
</div>
<div class="w-9 h-9 rounded-lg bg-surface-container-high flex items-center justify-center text-on-surface-variant">
<span class="material-symbols-outlined text-xl" data-icon="archive">archive</span>
</div>
</div>
</div>
<!-- Search & Filter Pill Controls Bar -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-3 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 shadow-xs">
<!-- Search Input -->
<div class="relative flex-1 max-w-md">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg" data-icon="search">search</span>
<input class="w-full pl-9 pr-4 py-2 bg-surface-container-low border border-outline-variant/60 rounded-lg text-body-md font-body-md text-on-surface placeholder:text-outline focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/15 transition-all" placeholder="Cari nama kandidat, skill, atau kata kunci CV..." type="text"/>
</div>
<!-- Quick Filter Pills -->
<div class="flex items-center gap-1.5 flex-wrap overflow-x-auto pb-1 md:pb-0">
<button class="bg-primary-container text-white text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 font-semibold">
            Semua Pelamar (8)
          </button>
<button class="bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface border border-outline-variant/60 text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
<span class="w-2 h-2 rounded-full bg-secondary"></span>
            Hanya Skor &gt; 80% (Anchor Fit - 5)
          </button>
<button class="bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface border border-outline-variant/60 text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
            Belum Ditinjau (3)
          </button>
<button class="bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface border border-outline-variant/60 text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
            Sudah Dijadwalkan (2)
          </button>
</div>
</div>
</section>
<!-- 5-COLUMN CANDIDATE SELECTION KANBAN BOARD -->
<section class="mt-2 overflow-x-auto pb-6">

    <!-- DYNAMIC LIVE KANBAN BOARD CONTAINER -->
    <div id="liveKanbanContainer" class="grid grid-cols-1 md:grid-cols-5 gap-4 min-w-[1240px] items-start">
        <!-- Will be populated dynamically by loadRecruiterPipeline() -->
        <div class="col-span-5 p-12 text-center bg-white rounded-2xl border border-slate-200">
            <span class="material-symbols-outlined text-4xl text-sky-600 animate-spin mb-2">sync</span>
            <p class="text-sm font-semibold text-slate-700">Menghubungkan ke Database Recruitment Pipeline...</p>
        </div>
    </div>
    </main>
<!-- FOOTER (Shared Component Source of Truth) -->
<footer class="bg-surface-container dark:bg-inverse-surface border-t border-outline-variant/60 dark:border-outline/40 flat no shadows mt-auto">
<div class="w-full py-space-xl px-margin-desktop flex flex-col md:flex-row justify-between items-center gap-space-lg max-w-[1600px] mx-auto">
<!-- Brand & Copyright -->
<div class="flex flex-col sm:flex-row items-center gap-2 sm:gap-4 text-center sm:text-left">
<span class="text-label-md font-label-md font-bold text-on-surface dark:text-inverse-on-surface flex items-center gap-1.5">
<span class="material-symbols-outlined text-secondary text-base" data-icon="anchor">anchor</span>
          JobHarbour
        </span>
<span class="text-body-sm font-body-sm text-on-surface-variant dark:text-surface-variant">
          © 2025 JobHarbour. Hak Cipta Dilindungi Undang-Undang. Pelabuhan Karir &amp; Talenta Maritim Indonesia.
        </span>
</div>
<!-- Footer Policy Links -->
<div class="flex items-center gap-space-lg flex-wrap justify-center text-label-sm font-label-sm">
<a class="text-on-surface-variant dark:text-surface-variant hover:text-on-surface dark:hover:text-inverse-on-surface hover:text-secondary dark:hover:text-secondary-fixed transition-colors" href="/">
          Ketentuan Layanan
        </a>
<a class="text-on-surface-variant dark:text-surface-variant hover:text-on-surface dark:hover:text-inverse-on-surface hover:text-secondary dark:hover:text-secondary-fixed transition-colors" href="/">
          Kebijakan Privasi
        </a>
<a class="text-on-surface-variant dark:text-surface-variant hover:text-on-surface dark:hover:text-inverse-on-surface hover:text-secondary dark:hover:text-secondary-fixed transition-colors" href="/">
          Daftar Pelabuhan
        </a>
<a class="text-secondary dark:text-secondary-fixed font-medium hover:underline transition-colors flex items-center gap-1" href="/">
<span class="material-symbols-outlined text-xs" data-icon="help">help</span>
          Bantuan Kapten
        </a>
</div>
</div>
</footer>

