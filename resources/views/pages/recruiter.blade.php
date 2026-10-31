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
<header class="bg-surface-container-lowest/90 dark:bg-inverse-surface/90 backdrop-blur-md border-b border-outline-variant/60 dark:border-outline/40 shadow-sm dark:shadow-none fixed top-0 left-0 right-0 w-full z-50 h-16">
<div class="max-w-[1600px] mx-auto h-full px-margin-desktop flex items-center justify-between">
<!-- Brand & Pro Recruiter Deck Identifier -->
<div class="flex items-center gap-space-xl">
<a class="text-headline-sm font-headline-sm font-bold tracking-tight text-on-surface dark:text-inverse-on-surface flex items-center gap-2" href="/">
<span class="w-8 h-8 rounded-lg bg-primary-container text-white flex items-center justify-center shadow-inner">
<span class="material-symbols-outlined text-lg" data-icon="anchor">anchor</span>
</span>
<span>JobHarbour</span>
</a>
<!-- Desktop Navigation Items -->
<nav class="hidden md:flex items-center gap-space-lg">
<a class="text-on-surface-variant dark:text-surface-variant hover:text-on-surface dark:hover:text-inverse-on-surface transition-colors pb-1 text-label-md font-label-md" href="/">
            The Anchorage
          </a>
<a class="text-on-surface-variant dark:text-surface-variant hover:text-on-surface dark:hover:text-inverse-on-surface transition-colors pb-1 text-label-md font-label-md" href="/">
            My Dock
          </a>
<a class="text-on-surface-variant dark:text-surface-variant hover:text-on-surface dark:hover:text-inverse-on-surface transition-colors pb-1 text-label-md font-label-md" href="/">
            Captain's Ledger
          </a>
<!-- Active Nav Link: Harbour Master -->
<a class="text-secondary dark:text-secondary-fixed border-b-2 border-secondary dark:border-secondary-fixed font-semibold pb-1 text-label-md font-label-md flex items-center gap-1.5" href="/">
<span>Harbour Master</span>
<span class="bg-secondary text-white text-[10px] font-bold px-1.5 py-0.5 rounded tracking-wider uppercase">PRO</span>
</a>
</nav>
</div>
<!-- Trailing Recruiter Profile & Operational CTAs -->
<div class="flex items-center gap-space-md">
<!-- Switch View & Export -->
<div class="hidden lg:flex items-center gap-1 border-r border-outline-variant/60 pr-space-md mr-1">
<button class="p-2 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high rounded-lg transition-colors flex items-center gap-1 text-label-sm font-label-sm" title="Ganti Tampilan Pipeline">
<span class="material-symbols-outlined text-base" data-icon="view_kanban">view_kanban</span>
<span class="hidden xl:inline">Board</span>
</button>
<button class="p-2 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high rounded-lg transition-colors flex items-center gap-1 text-label-sm font-label-sm" title="Unduh Laporan Pipeline">
<span class="material-symbols-outlined text-base" data-icon="download">download</span>
<span class="hidden xl:inline">Ekspor</span>
</button>
</div>
<!-- Notification Bell -->
<button aria-label="Notifikasi" class="relative p-2 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high rounded-lg transition-colors">
<span class="material-symbols-outlined text-xl" data-icon="notifications">notifications</span>
<span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-error ring-2 ring-surface-container-lowest"></span>
</button>
<!-- Recruiter Profile Badge -->
<div class="hidden sm:flex items-center gap-2 pl-space-xs pr-space-sm py-1 rounded-full border border-outline-variant/60 bg-surface-container-low hover:bg-surface-container transition-colors">
<div class="w-7 h-7 rounded-full bg-primary-container text-white flex items-center justify-center font-bold text-xs uppercase">
            SB
          </div>
<div class="flex flex-col text-left">
<div class="flex items-center gap-1">
<span class="text-label-sm font-label-sm text-on-surface font-semibold">Capt. HR Lead</span>
<span class="material-symbols-outlined text-secondary text-xs" data-icon="verified" style="font-variation-settings: 'FILL' 1;" title="Verified Maritime Operator">verified</span>
</div>
<span class="text-[10px] text-on-surface-variant leading-none">PT Solusi Bahari Digital</span>
</div>
</div>
<!-- Primary Action: Pasang Lowongan Baru -->
<button class="bg-secondary hover:bg-on-secondary-container text-white text-label-md font-label-md px-3.5 py-2 rounded-lg flex items-center gap-1.5 shadow-sm transition-all duration-150 active:scale-95">
<span class="material-symbols-outlined text-lg" data-icon="add">add</span>
<span class="hidden sm:inline">Pasang Lowongan Baru</span>
</button>
</div>
</div>
</header>
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
<div class="grid grid-cols-1 md:grid-cols-5 gap-space-lg min-w-[1240px] items-start">
<!-- ============================================== -->
<!-- COLUMN 1: MASUK (SCREENING AWAL) -->
<!-- ============================================== -->
<div class="bg-surface-container-low/70 border border-outline-variant/70 rounded-xl p-3 flex flex-col gap-3 min-h-[640px]">
<!-- Column Header -->
<div class="flex items-center justify-between px-1 pb-2 border-b border-outline-variant/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-outline"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">1. Masuk (Screening Awal)</h2>
</div>
<span class="bg-surface-container-highest text-on-surface-variant font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">3</span>
</div>
<!-- Candidates Container -->
<div class="space-y-3">
<!-- Card 1: Ahmad Fauzi -->
<div class="bg-surface-container-lowest border border-outline-variant/80 rounded-xl p-3.5 shadow-xs hover:border-secondary hover:shadow-md transition-all duration-150 group">
<!-- Header & Match Score -->
<div class="flex items-start justify-between gap-2 mb-2">
<div>
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface group-hover:text-secondary transition-colors">Ahmad Fauzi</h3>
<p class="text-body-sm font-body-sm text-outline">Target: Backend Engineer</p>
</div>
<!-- 78% Amber Badge -->
<span class="inline-flex items-center gap-1 bg-amber-50 text-amber-800 border border-amber-300 font-coordinate-tag text-coordinate-tag px-2 py-0.5 rounded font-bold">
<span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                  78% MATCH
                </span>
</div>
<!-- Telemetry Snippet -->
<div class="text-[11px] text-on-surface-variant flex items-center gap-2 mb-3 bg-surface-container-low px-2 py-1 rounded">
<span class="material-symbols-outlined text-xs text-outline" data-icon="schedule">schedule</span>
<span>Masuk: 4 jam lalu</span>
<span class="text-outline-variant">•</span>
<span class="text-secondary font-medium">Portofolio Pelabuhan</span>
</div>
<!-- Skills Tags -->
<div class="flex flex-wrap gap-1 mb-3">
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">PHP</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Laravel</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">MySQL</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Postman</span>
</div>
<!-- Quick Actions -->
<div class="pt-2 border-t border-outline-variant/50 flex items-center justify-between gap-2">
<button class="text-on-surface-variant hover:text-secondary text-label-sm font-label-sm flex items-center gap-1 py-1 transition-colors">
<span class="material-symbols-outlined text-base" data-icon="description">description</span>
<span>Unduh CV (PDF)</span>
</button>
<button class="bg-surface-container-high hover:bg-secondary hover:text-white text-on-surface text-label-sm font-label-sm px-2.5 py-1 rounded-lg transition-colors font-semibold">
                  Loloskan ke Assessment →
                </button>
</div>
</div>
<!-- Card 2: Rian Pratama -->
<div class="bg-surface-container-lowest border border-outline-variant/80 rounded-xl p-3.5 shadow-xs hover:border-secondary hover:shadow-md transition-all duration-150 group">
<!-- Header & Match Score -->
<div class="flex items-start justify-between gap-2 mb-2">
<div>
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface group-hover:text-secondary transition-colors">Rian Pratama</h3>
<p class="text-body-sm font-body-sm text-outline">Target: Backend Engineer</p>
</div>
<!-- 65% Slate Badge -->
<span class="inline-flex items-center gap-1 bg-surface-container-high text-on-surface-variant border border-outline-variant/80 font-coordinate-tag text-coordinate-tag px-2 py-0.5 rounded font-bold">
<span class="w-1.5 h-1.5 rounded-full bg-outline"></span>
                  65% MATCH
                </span>
</div>
<!-- Telemetry Snippet -->
<div class="text-[11px] text-on-surface-variant flex items-center gap-2 mb-3 bg-surface-container-low px-2 py-1 rounded">
<span class="material-symbols-outlined text-xs text-outline" data-icon="schedule">schedule</span>
<span>Masuk: 1 hari lalu</span>
<span class="text-outline-variant">•</span>
<span>Pernah di Logistik</span>
</div>
<!-- Skills Tags -->
<div class="flex flex-wrap gap-1 mb-3">
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">PHP</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">CodeIgniter</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">MySQL</span>
</div>
<!-- Quick Actions -->
<div class="pt-2 border-t border-outline-variant/50 flex items-center justify-between gap-2">
<button class="text-on-surface-variant hover:text-secondary text-label-sm font-label-sm flex items-center gap-1 py-1 transition-colors">
<span class="material-symbols-outlined text-base" data-icon="description">description</span>
<span>Unduh CV</span>
</button>
<button class="bg-surface-container-high hover:bg-secondary hover:text-white text-on-surface text-label-sm font-label-sm px-2.5 py-1 rounded-lg transition-colors font-semibold">
                  Review Berkas
                </button>
</div>
</div>
<!-- Quick Berth Drop Zone Indicator -->
<div class="border-2 border-dashed border-outline-variant/60 rounded-xl p-3 text-center text-outline text-label-sm font-label-sm flex items-center justify-center gap-1.5 bg-surface-container/20">
<span class="material-symbols-outlined text-base" data-icon="move_to_inbox">move_to_inbox</span>
<span>Tarik pelamar ke dermaga ini</span>
</div>
</div>
</div>
<!-- ============================================== -->
<!-- COLUMN 2: REVIEW TEKNIS (ASSESSMENT) -->
<!-- ============================================== -->
<div class="bg-surface-container-low/70 border border-outline-variant/70 rounded-xl p-3 flex flex-col gap-3 min-h-[640px]">
<!-- Column Header -->
<div class="flex items-center justify-between px-1 pb-2 border-b border-outline-variant/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-secondary"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">2. Review Teknis (Assessment)</h2>
</div>
<span class="bg-secondary-fixed text-on-secondary-fixed font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">2</span>
</div>
<!-- Candidates Container -->
<div class="space-y-3">
<!-- Card 3: Dewi Lestari -->
<div class="bg-surface-container-lowest border border-outline-variant/80 rounded-xl p-3.5 shadow-xs hover:border-secondary hover:shadow-md transition-all duration-150 group">
<!-- Header & Match Score -->
<div class="flex items-start justify-between gap-2 mb-2">
<div>
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface group-hover:text-secondary transition-colors">Dewi Lestari</h3>
<p class="text-body-sm font-body-sm text-outline">Backend &amp; API Specialist</p>
</div>
<!-- 88% Cyan/Teal Verified Badge -->
<span class="inline-flex items-center gap-1 bg-cyan-50 text-cyan-800 border border-cyan-300 font-coordinate-tag text-coordinate-tag px-2 py-0.5 rounded font-bold">
<span class="material-symbols-outlined text-xs text-cyan-600" data-icon="anchor" style="font-variation-settings: 'FILL' 1;">anchor</span>
                  88% ANCHOR FIT
                </span>
</div>
<!-- Assessment Telemetry Metric Bar -->
<div class="bg-surface-container-low p-2 rounded-lg border border-outline-variant/50 mb-2.5">
<div class="flex justify-between items-center text-[11px] mb-1">
<span class="font-semibold text-on-surface">Nilai Tes REST API</span>
<span class="font-bold text-secondary">88/100</span>
</div>
<div class="w-full bg-surface-container-high rounded-full h-1.5 overflow-hidden">
<div class="bg-secondary h-1.5 rounded-full" style="width: 88%"></div>
</div>
<p class="text-[11px] text-on-surface-variant mt-1.5 italic">
                  "Implementasi caching Redis rapi, test unit 90% coverage."
                </p>
</div>
<!-- Skills Tags -->
<div class="flex flex-wrap gap-1 mb-3">
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">PHP</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Laravel</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Redis</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">MySQL</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Docker</span>
</div>
<!-- Quick Actions -->
<div class="pt-2 border-t border-outline-variant/50 flex items-center justify-between gap-2">
<button class="text-on-surface-variant hover:text-secondary text-label-sm font-label-sm flex items-center gap-1 py-1 transition-colors">
<span class="material-symbols-outlined text-base" data-icon="description">description</span>
<span>Unduh CV</span>
</button>
<button class="bg-secondary hover:bg-on-secondary-container text-white text-label-sm font-label-sm px-2.5 py-1 rounded-lg transition-colors font-semibold">
                  Jadwalkan Wawancara →
                </button>
</div>
</div>
<!-- Card 4: Eko Wicaksono -->
<div class="bg-surface-container-lowest border border-outline-variant/80 rounded-xl p-3.5 shadow-xs hover:border-secondary hover:shadow-md transition-all duration-150 group">
<!-- Header & Match Score -->
<div class="flex items-start justify-between gap-2 mb-2">
<div>
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface group-hover:text-secondary transition-colors">Eko Wicaksono</h3>
<p class="text-body-sm font-body-sm text-outline">Software Engineer</p>
</div>
<!-- 82% Match -->
<span class="inline-flex items-center gap-1 bg-surface-container text-on-surface-variant border border-outline-variant/80 font-coordinate-tag text-coordinate-tag px-2 py-0.5 rounded font-bold">
                  82% MATCH
                </span>
</div>
<!-- Assessment Status In Progress -->
<div class="bg-amber-50/70 border border-amber-200/80 p-2 rounded-lg mb-2.5 flex items-start gap-2">
<span class="material-symbols-outlined text-amber-700 text-sm mt-0.5" data-icon="pending">pending</span>
<div>
<span class="text-label-sm font-label-sm font-semibold text-amber-900 block leading-tight">Tugas Dalam Pengerjaan</span>
<span class="text-[11px] text-amber-800">Batas Waktu Pengumpulan: <strong>Besok, 23:59 WIB</strong></span>
</div>
</div>
<!-- Skills Tags -->
<div class="flex flex-wrap gap-1 mb-3">
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Laravel</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">MariaDB</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Docker</span>
</div>
<!-- Quick Actions -->
<div class="pt-2 border-t border-outline-variant/50 flex items-center justify-between gap-2">
<button class="w-full bg-surface-container-high hover:bg-surface-variant text-on-surface text-label-sm font-label-sm py-1 rounded-lg transition-colors flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-base" data-icon="send">send</span>
<span>Kirim Pengingat Tugas</span>
</button>
</div>
</div>
</div>
</div>
<!-- ============================================== -->
<!-- COLUMN 3: WAWANCARA (INTERVIEW) - HIGHLIGHTED BERTH -->
<!-- ============================================== -->
<div class="bg-surface-container-low/90 border-2 border-tertiary-fixed-dim/70 shadow-sm rounded-xl p-3 flex flex-col gap-3 min-h-[640px] relative">
<!-- Column Header with Signal Accent -->
<div class="flex items-center justify-between px-1 pb-2 border-b border-tertiary-fixed-dim/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">3. Wawancara (Interview)</h2>
</div>
<span class="bg-tertiary-fixed text-on-tertiary-fixed font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">2</span>
</div>
<!-- Candidates Container -->
<div class="space-y-3">
<!-- Card 5: Budi Santoso (TOP CANDIDATE - STAR HIGHLIGHT) -->
<div class="bg-surface-container-lowest border-2 border-secondary shadow-md rounded-xl p-3.5 relative overflow-hidden group">
<!-- Top Anchor Fit Ribbon -->
<div class="bg-amber-100 border-b border-amber-300 text-amber-900 font-coordinate-tag text-[10px] font-bold py-1 px-3 -mx-3.5 -mt-3.5 mb-2.5 flex items-center justify-between">
<span class="flex items-center gap-1">
<span class="material-symbols-outlined text-xs text-amber-800" data-icon="anchor" style="font-variation-settings: 'FILL' 1;">anchor</span>
                  92% TOP MATCH (ANCHOR FIT)
                </span>
<span class="text-amber-800 tracking-wider">STAR CANDIDATE</span>
</div>
<!-- Header -->
<div class="flex items-start justify-between gap-2 mb-1">
<div>
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface">Budi Santoso</h3>
<p class="text-label-sm font-label-sm text-secondary font-semibold">Senior Backend &amp; Microservices Engineer</p>
</div>
</div>
<!-- Live Schedule Banner -->
<div class="bg-secondary-fixed/30 border border-secondary/30 rounded-lg p-2.5 my-2.5">
<div class="flex items-center gap-1.5 text-label-sm font-label-sm font-bold text-on-secondary-fixed-variant">
<span class="material-symbols-outlined text-base text-secondary" data-icon="calendar_today">calendar_today</span>
<span>10 Okt 2026, 10:00 WIB</span>
<span class="bg-secondary text-white text-[10px] px-1.5 py-0.2 rounded uppercase">Segera</span>
</div>
<div class="flex items-center gap-1 text-[11px] text-on-surface-variant mt-1">
<span class="material-symbols-outlined text-xs" data-icon="videocam">videocam</span>
<span>Platform: <strong>Google Meet</strong></span>
</div>
<div class="text-[11px] text-outline mt-0.5 flex items-center gap-1">
<span class="material-symbols-outlined text-xs" data-icon="person">person</span>
<span>Pewawancara: Capt. Hendra Wijaya (VP Eng.) &amp; HR Lead</span>
</div>
</div>
<!-- Skills Tags -->
<div class="flex flex-wrap gap-1 mb-3">
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">PHP</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Laravel</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">MySQL</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Docker</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">Redis</span>
<span class="bg-surface-container text-on-surface-variant font-coordinate-tag text-[10px] px-1.5 py-0.5 rounded border border-outline-variant/50">REST API</span>
</div>
<!-- Action Stack -->
<div class="pt-2 border-t border-outline-variant/50 space-y-1.5">
<div class="flex items-center gap-1.5">
<a class="flex-1 bg-surface-container-high hover:bg-surface-variant text-on-surface text-label-sm font-label-sm py-1.5 px-2 rounded-lg transition-colors flex items-center justify-center gap-1 font-medium" href="/">
<span class="material-symbols-outlined text-sm text-secondary" data-icon="video_call">video_call</span>
<span>Buka Tautan Meet</span>
</a>
<button class="bg-surface-container-high hover:bg-surface-variant text-on-surface-variant hover:text-on-surface p-1.5 rounded-lg transition-colors" title="Unduh CV Lengkap">
<span class="material-symbols-outlined text-base" data-icon="description">description</span>
</button>
</div>
<!-- Prominent Fast-Track Dispatch Action -->
<button class="w-full bg-secondary hover:bg-on-secondary-container text-white text-label-sm font-label-sm py-2 px-3 rounded-lg transition-all flex items-center justify-center gap-1 font-bold shadow-sm">
<span>Kirim Offering (Penawaran) →</span>
</button>
</div>
</div>
<!-- Card 6: Siti Rahmawati -->
<div class="bg-surface-container-lowest border border-outline-variant/80 rounded-xl p-3.5 shadow-xs hover:border-secondary hover:shadow-md transition-all duration-150 group">
<!-- Header & Match Score -->
<div class="flex items-start justify-between gap-2 mb-2">
<div>
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface group-hover:text-secondary transition-colors">Siti Rahmawati</h3>
<p class="text-body-sm font-body-sm text-outline">Backend Engineer</p>
</div>
<!-- 85% Match -->
<span class="inline-flex items-center gap-1 bg-surface-container-high text-on-surface-variant border border-outline-variant/80 font-coordinate-tag text-coordinate-tag px-2 py-0.5 rounded font-bold">
                  85% MATCH
                </span>
</div>
<!-- Schedule Banner -->
<div class="bg-surface-container-low rounded-lg p-2.5 my-2 border border-outline-variant/50">
<div class="flex items-center gap-1.5 text-label-sm font-label-sm font-semibold text-on-surface">
<span class="material-symbols-outlined text-sm text-outline" data-icon="calendar_today">calendar_today</span>
<span>11 Okt 2026, 14:00 WIB</span>
</div>
<div class="text-[11px] text-outline mt-0.5">
                  Interviewer: Tech Lead Backend • Google Meet
                </div>
</div>
<!-- Quick Actions -->
<div class="pt-2 border-t border-outline-variant/50 flex items-center justify-between gap-2">
<button class="text-on-surface-variant hover:text-on-surface text-label-sm font-label-sm py-1 transition-colors">
                  Detail Jadwal
                </button>
<button class="text-secondary hover:underline text-label-sm font-label-sm py-1 font-semibold">
                  Reschedule
                </button>
</div>
</div>
</div>
</div>
<!-- ============================================== -->
<!-- COLUMN 4: PENAWARAN KERJA (OFFERING) -->
<!-- ============================================== -->
<div class="bg-surface-container-low/70 border border-outline-variant/70 rounded-xl p-3 flex flex-col gap-3 min-h-[640px]">
<!-- Column Header -->
<div class="flex items-center justify-between px-1 pb-2 border-b border-outline-variant/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">4. Penawaran Kerja (Offering)</h2>
</div>
<span class="bg-emerald-100 text-emerald-800 font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">1</span>
</div>
<!-- Candidates Container -->
<div class="space-y-3">
<!-- Card 7: Fajar Nugraha -->
<div class="bg-surface-container-lowest border border-emerald-300 rounded-xl p-3.5 shadow-xs hover:border-emerald-500 hover:shadow-md transition-all duration-150 group">
<!-- Top Tier Badge -->
<div class="flex items-start justify-between gap-2 mb-2">
<div>
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface">Fajar Nugraha</h3>
<p class="text-body-sm font-body-sm text-outline">Lead Backend Engineer</p>
</div>
<span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-800 border border-emerald-300 font-coordinate-tag text-coordinate-tag px-2 py-0.5 rounded font-bold">
<span class="material-symbols-outlined text-xs text-emerald-600" data-icon="anchor" style="font-variation-settings: 'FILL' 1;">anchor</span>
                  95% ANCHOR FIT
                </span>
</div>
<!-- Offering Package Dossier -->
<div class="bg-emerald-50/50 border border-emerald-200/80 rounded-lg p-2.5 mb-2.5 space-y-1">
<span class="text-coordinate-tag font-coordinate-tag text-emerald-800 block uppercase">Paket Penawaran Kerja</span>
<p class="text-label-sm font-label-sm font-bold text-emerald-950">
                  Gaji Pokok: Rp 15.000.000 / bln + Tunjangan Pelabuhan
                </p>
<div class="flex items-center gap-1 text-[11px] text-emerald-800 pt-1">
<span class="material-symbols-outlined text-xs" data-icon="history_edu">history_edu</span>
<span>Menunggu tanda tangan kontrak kandidat</span>
</div>
<span class="text-[10px] text-outline block">Status: Terkirim 2 hari lalu ke fajar.nugraha@maritimtech.id</span>
</div>
<!-- Quick Actions -->
<div class="pt-2 border-t border-outline-variant/50 space-y-1.5">
<button class="w-full bg-emerald-700 hover:bg-emerald-800 text-white text-label-sm font-label-sm py-1.5 rounded-lg transition-colors flex items-center justify-center gap-1 font-semibold">
<span class="material-symbols-outlined text-base" data-icon="notifications_active">notifications_active</span>
<span>Kirim Pengingat Kontrak</span>
</button>
<button class="w-full bg-surface-container-high hover:bg-surface-variant text-on-surface text-label-sm font-label-sm py-1 rounded-lg transition-colors">
                  Lihat Dokumen Kontrak
                </button>
</div>
</div>
</div>
</div>
<!-- ============================================== -->
<!-- COLUMN 5: DITOLAK (REJECTED) -->
<!-- ============================================== -->
<div class="bg-surface-container-low/70 border border-outline-variant/70 rounded-xl p-3 flex flex-col gap-3 min-h-[640px]">
<!-- Column Header -->
<div class="flex items-center justify-between px-1 pb-2 border-b border-outline-variant/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-error"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">5. Ditolak (Rejected)</h2>
</div>
<span class="bg-error-container text-on-error-container font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">1</span>
</div>
<!-- Candidates Container -->
<div class="space-y-3">
<!-- Card 8: Kurniawan -->
<div class="bg-surface-container-lowest/80 border border-outline-variant/60 rounded-xl p-3.5 shadow-none opacity-85 hover:opacity-100 transition-opacity">
<!-- Header -->
<div class="flex items-start justify-between gap-2 mb-2">
<div>
<h3 class="font-headline-sm text-headline-sm font-bold text-on-surface line-through decoration-outline">Kurniawan</h3>
<p class="text-body-sm font-body-sm text-outline">Junior Programmer</p>
</div>
<!-- 42% Under Qualified Badge -->
<span class="inline-flex items-center gap-1 bg-red-50 text-red-800 border border-red-200 font-coordinate-tag text-coordinate-tag px-2 py-0.5 rounded font-bold">
                  42% UNDER QUALIFIED
                </span>
</div>
<!-- Reason of Rejection -->
<div class="bg-surface-container-low p-2 rounded-lg border border-outline-variant/40 mb-2.5 text-[11px] text-on-surface-variant">
<span class="font-semibold text-outline block mb-0.5">Catatan Evaluator:</span>
                Belum menguasai Laravel dan REST API standar mikroservis pelabuhan.
              </div>
<!-- Telemetry Dispatch Notification -->
<div class="text-[11px] text-outline flex items-center gap-1 mb-3">
<span class="material-symbols-outlined text-xs text-emerald-600" data-icon="check_circle">check_circle</span>
<span>Email notifikasi penolakan sopan telah dikirim.</span>
</div>
<!-- Quick Action -->
<div class="pt-2 border-t border-outline-variant/50">
<button class="w-full text-on-surface-variant hover:text-on-surface bg-surface-container-low hover:bg-surface-container text-label-sm font-label-sm py-1 rounded-lg transition-colors flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-base" data-icon="folder_zip">folder_zip</span>
<span>Buka Arsip Kandidat</span>
</button>
</div>
</div>
</div>
</div>
</div>
</section>
<!-- HARBOUR DOCK BAR (Floating Batch Management Dock) -->
<div class="mt-8 bg-surface-container-lowest/90 backdrop-blur-md border border-outline-variant/80 rounded-xl p-3.5 shadow-md flex flex-col md:flex-row items-center justify-between gap-4">
<div class="flex items-center gap-3">
<div class="w-8 h-8 rounded-lg bg-primary-container text-white flex items-center justify-center">
<span class="material-symbols-outlined text-lg" data-icon="anchor">anchor</span>
</div>
<div>
<span class="text-label-md font-label-md text-on-surface font-semibold block">Aksi Dermaga Kolektif (Batch Operations)</span>
<span class="text-body-sm font-body-sm text-outline">Tandai kandidat untuk mengirim jadwal serentak atau ekspor berkas zip IMO.</span>
</div>
</div>
<div class="flex items-center gap-2">
<button class="border border-outline-variant/70 hover:bg-surface-container text-on-surface text-label-sm font-label-sm px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
<span class="material-symbols-outlined text-base" data-icon="checklist">checklist</span>
<span>Pilih Semua (8)</span>
</button>
<button class="border border-outline-variant/70 hover:bg-surface-container text-on-surface text-label-sm font-label-sm px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
<span class="material-symbols-outlined text-base" data-icon="mail">mail</span>
<span>Kirim Broadcast Email</span>
</button>
<button class="bg-primary-container hover:bg-on-primary-fixed text-white text-label-sm font-label-sm px-3.5 py-1.5 rounded-lg transition-colors flex items-center gap-1 font-semibold">
<span class="material-symbols-outlined text-base" data-icon="archive">archive</span>
<span>Arsipkan Batch</span>
</button>
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
</body></html>