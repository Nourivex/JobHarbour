<!DOCTYPE html>

<html class="light" lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Dashboard Rekrutmen — JobHarbour ATS</title>
<!-- Fonts & Material Symbols -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
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
            "tertiary-fixed": "#6ffbbe",
            "secondary-fixed-dim": "#93ccff",
            "on-primary-fixed": "#131b2e",
            "on-primary": "#ffffff",
            "surface-container": "#e5eeff",
            "on-primary-fixed-variant": "#3f465c",
            "on-secondary-fixed-variant": "#004b73",
            "on-secondary-container": "#00476e",
            "surface-tint": "#565e74",
            "on-tertiary-container": "#009668",
            "inverse-primary": "#bec6e0",
            "inverse-surface": "#213145",
            "error": "#ba1a1a",
            "on-secondary": "#ffffff",
            "surface-bright": "#f8f9ff",
            "primary": "#0F172A",
            "surface-variant": "#d3e4fe",
            "on-surface": "#0b1c30",
            "surface-container-lowest": "#ffffff",
            "on-tertiary-fixed": "#002113",
            "on-primary-container": "#7c839b",
            "secondary-fixed": "#cce5ff",
            "outline-variant": "#e2e8f0",
            "primary-fixed": "#dae2fd",
            "inverse-on-surface": "#eaf1ff",
            "outline": "#64748b",
            "primary-container": "#131b2e",
            "on-error": "#ffffff",
            "on-background": "#0b1c30",
            "secondary": "#0284c7",
            "error-container": "#ffdad6",
            "surface": "#f8f9ff",
            "background": "#f8fafc",
            "surface-container-low": "#f1f5f9",
            "tertiary": "#000000",
            "tertiary-fixed-dim": "#4edea3",
            "surface-dim": "#cbdbf5",
            "on-tertiary-fixed-variant": "#005236",
            "secondary-container": "#e0f2fe",
            "primary-fixed-dim": "#bec6e0",
            "on-secondary-fixed": "#001d31",
            "surface-container-highest": "#e2e8f0",
            "tertiary-container": "#002113",
            "on-error-container": "#93000a",
            "surface-container-high": "#e2e8f0",
            "on-surface-variant": "#475569",
            "on-tertiary": "#ffffff"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "spacing": {
            "space-xl": "1.5rem",
            "space-lg": "1rem",
            "space-md": "0.75rem",
            "margin-lg": "2rem",
            "gutter": "1rem",
            "space-xs": "0.25rem",
            "space-sm": "0.5rem",
            "margin-md": "1.5rem",
            "gutter-lg": "1.5rem",
            "margin": "1rem"
          },
          "fontFamily": {
            "body-md": ["Inter"],
            "label-xs": ["Inter"],
            "body-sm": ["Inter"],
            "headline-lg": ["Plus Jakarta Sans"],
            "headline-sm": ["Plus Jakarta Sans"],
            "label-sm": ["Inter"],
            "title-md": ["Plus Jakarta Sans"],
            "display-lg": ["Plus Jakarta Sans"],
            "display-lg-mobile": ["Plus Jakarta Sans"],
            "label-md": ["Inter"],
            "headline-lg-mobile": ["Plus Jakarta Sans"],
            "body-lg": ["Inter"],
            "headline-md": ["Plus Jakarta Sans"]
          }
        }
      }
    }
  </script>
</head>
<body class="bg-background text-on-surface antialiased min-h-screen flex flex-col font-body-md text-sm selection:bg-sky-100 selection:text-sky-900">
<!-- 1. COMPACT GLOBAL TOP NAVIGATION BAR -->
<header class="bg-white/95 backdrop-blur-md border-b border-slate-200 sticky top-0 left-0 right-0 w-full z-40 h-14">
<div class="max-w-[1720px] mx-auto h-full px-5 flex items-center justify-between">
<!-- Brand & Navigation -->
<div class="flex items-center gap-7">
<a class="text-base font-headline-sm font-bold tracking-tight text-slate-900 flex items-center gap-2" href="#">
<span class="w-7 h-7 rounded-lg bg-slate-900 text-white flex items-center justify-center font-bold">
<span class="material-symbols-outlined text-base" data-icon="work">work</span>
</span>
<span>JobHarbour</span>
</a>
<nav class="hidden md:flex items-center gap-5 text-xs font-medium">
<a class="text-slate-600 hover:text-slate-900 transition-colors" href="#">Cari Kerja</a>
<a class="text-slate-600 hover:text-slate-900 transition-colors" href="#">Kelola Lowongan</a>
<a class="text-sky-600 border-b-2 border-sky-600 font-semibold py-4 flex items-center gap-1.5" href="#">
<span>Dashboard Rekrutmen</span>
<span class="bg-sky-100 text-sky-700 text-[10px] font-bold px-1.5 py-0.2 rounded uppercase">ATS</span>
</a>
<a class="text-slate-600 hover:text-slate-900 transition-colors" href="#">Laporan &amp; Analitik</a>
</nav>
</div>
<!-- Actions & Profile -->
<div class="flex items-center gap-3">
<!-- Mode Badge -->
<div class="hidden xl:flex items-center gap-1.5 bg-slate-100 px-2.5 py-1 rounded-md text-[11px] font-semibold text-slate-700">
<span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
<span>Mode Rekruter</span>
</div>
<!-- Info / API help popover trigger -->
<button class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-md transition-colors" title="Informasi Alur Rekrutmen &amp; API">
<span class="material-symbols-outlined text-lg" data-icon="info">info</span>
</button>
<!-- Notifications -->
<button aria-label="Notifikasi Rekrutmen" class="relative p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-md transition-colors">
<span class="material-symbols-outlined text-lg" data-icon="notifications">notifications</span>
<span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-rose-500 ring-2 ring-white"></span>
</button>
<!-- Recruiter Profile -->
<div class="flex items-center gap-2 pl-1 pr-2 py-0.5 rounded-full border border-slate-200 bg-slate-50 hover:bg-slate-100 transition-colors">
<div class="w-6 h-6 rounded-full bg-sky-600 text-white flex items-center justify-center font-bold text-[11px] uppercase">
          SL
        </div>
<div class="hidden sm:flex flex-col text-left leading-tight">
<div class="flex items-center gap-1">
<span class="text-xs font-semibold text-slate-800">Sarah L.</span>
<span class="material-symbols-outlined text-sky-600 text-[13px]" data-icon="verified" style="font-variation-settings: 'FILL' 1;">verified</span>
</div>
<span class="text-[10px] text-slate-500">HR Lead</span>
</div>
</div>
<!-- Primary CTA -->
<button class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-3 py-1.5 rounded-md flex items-center gap-1.5 shadow-sm transition-colors">
<span class="material-symbols-outlined text-base" data-icon="add">add</span>
<span class="hidden sm:inline">Pasang Lowongan Baru</span>
</button>
</div>
</div>
</header>
<!-- MAIN WORKSPACE -->
<main class="flex-1 pb-16 w-full max-w-[1720px] mx-auto px-5">
<!-- 2. CLEAN HEADER & COMPACT JOB SELECTOR -->
<section class="pt-5 pb-4 border-b border-slate-200 flex flex-col lg:flex-row lg:items-center justify-between gap-3">
<div>
<div class="flex items-center gap-2">
<h1 class="text-xl lg:text-2xl font-bold font-headline-lg text-slate-900 tracking-tight">
          Dashboard Rekrutmen <span class="text-slate-400 font-normal text-lg">/ Recruitment Pipeline</span>
</h1>
</div>
<p class="text-xs text-slate-500 mt-0.5">Kelola pipeline pelamar dan seleksi kandidat berdasarkan tahapan seleksi resmi.</p>
</div>
<!-- Compact Job Context Selector -->
<div class="bg-white border border-slate-200 rounded-lg p-2.5 flex items-center justify-between gap-4 shadow-xs">
<div class="flex items-center gap-2.5">
<div class="w-8 h-8 rounded-md bg-sky-50 text-sky-600 flex items-center justify-center flex-shrink-0">
<span class="material-symbols-outlined text-lg" data-icon="business_center">business_center</span>
</div>
<div class="flex flex-col">
<div class="flex items-center gap-1.5">
<span class="text-xs font-bold text-slate-900">Backend Developer (Microservices)</span>
<span class="text-xs text-slate-400 font-normal">• PT Teknologi Maju</span>
<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold px-1.5 py-0.2 rounded uppercase">Open</span>
</div>
<div class="text-[11px] text-slate-500 flex items-center gap-2">
<span>Kuota: <strong class="text-slate-700">3 Posisi</strong></span>
<span>•</span>
<span>Total: <strong class="text-slate-700">8 Pelamar</strong></span>
<span>•</span>
<span>Dibuka: 01 Okt 2026</span>
</div>
</div>
</div>
<div class="flex items-center gap-1 border-l border-slate-200 pl-3">
<button class="px-2 py-1 rounded text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors flex items-center gap-1" title="Ubah Posisi Lowongan">
<span class="material-symbols-outlined text-sm" data-icon="swap_horiz">swap_horiz</span>
<span class="hidden sm:inline">Ganti Lowongan</span>
</button>
</div>
</div>
</section>
<!-- 3. COMPACT KPI SUMMARY (EXACTLY 4 CARDS) -->
<section class="py-4">
<div class="grid grid-cols-2 md:grid-cols-4 gap-3">
<!-- Card 1: Total Pelamar -->
<div class="bg-white border border-slate-200 rounded-lg p-3 flex items-center justify-between shadow-xs">
<div>
<span class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 block">Total Pelamar</span>
<div class="flex items-baseline gap-1 mt-0.5">
<span class="text-xl font-bold font-headline-md text-slate-900">8</span>
<span class="text-xs text-slate-500">Kandidat</span>
</div>
<span class="text-[10px] text-slate-400">Semua aplikasi masuk</span>
</div>
<div class="w-8 h-8 rounded-md bg-slate-100 text-slate-600 flex items-center justify-center">
<span class="material-symbols-outlined text-lg" data-icon="group">group</span>
</div>
</div>
<!-- Card 2: Perlu Ditinjau / In Review -->
<div class="bg-white border border-slate-200 rounded-lg p-3 flex items-center justify-between shadow-xs">
<div>
<span class="text-[11px] font-semibold uppercase tracking-wider text-amber-700 block">Perlu Ditinjau</span>
<div class="flex items-baseline gap-1 mt-0.5">
<span class="text-xl font-bold font-headline-md text-amber-900">5</span>
<span class="text-xs text-amber-700">Kandidat</span>
</div>
<span class="text-[10px] text-slate-400">Screening &amp; Assessment</span>
</div>
<div class="w-8 h-8 rounded-md bg-amber-50 text-amber-600 flex items-center justify-center">
<span class="material-symbols-outlined text-lg" data-icon="rate_review">rate_review</span>
</div>
</div>
<!-- Card 3: Wawancara Aktif -->
<div class="bg-white border border-slate-200 rounded-lg p-3 flex items-center justify-between shadow-xs">
<div>
<span class="text-[11px] font-semibold uppercase tracking-wider text-sky-700 block">Wawancara Aktif</span>
<div class="flex items-baseline gap-1 mt-0.5">
<span class="text-xl font-bold font-headline-md text-sky-900">2</span>
<span class="text-xs text-sky-700">Terjadwal</span>
</div>
<span class="text-[10px] text-slate-400">Tahap interview</span>
</div>
<div class="w-8 h-8 rounded-md bg-sky-50 text-sky-600 flex items-center justify-center">
<span class="material-symbols-outlined text-lg" data-icon="calendar_month">calendar_month</span>
</div>
</div>
<!-- Card 4: Penawaran Terkirim -->
<div class="bg-white border border-slate-200 rounded-lg p-3 flex items-center justify-between shadow-xs">
<div>
<span class="text-[11px] font-semibold uppercase tracking-wider text-emerald-700 block">Penawaran Terkirim</span>
<div class="flex items-baseline gap-1 mt-0.5">
<span class="text-xl font-bold font-headline-md text-emerald-900">1</span>
<span class="text-xs text-emerald-700">Offering</span>
</div>
<span class="text-[10px] text-slate-400">Menunggu konfirmasi kandidat</span>
</div>
<div class="w-8 h-8 rounded-md bg-emerald-50 text-emerald-600 flex items-center justify-center">
<span class="material-symbols-outlined text-lg" data-icon="verified">verified</span>
</div>
</div>
</div>
</section>
<!-- 4. CONSOLIDATED FILTER & SEARCH TOOLBAR -->
<section class="bg-white border border-slate-200 rounded-lg p-2.5 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5 mb-4 shadow-xs">
<div class="flex items-center gap-2 flex-1">
<!-- Search Input -->
<div class="relative flex-1 max-w-sm">
<span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-base" data-icon="search">search</span>
<input class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-md text-xs text-slate-800 placeholder:text-slate-400 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 transition-all" placeholder="Cari nama kandidat, keahlian..." type="text"/>
</div>
<!-- Stage Filter -->
<select class="bg-slate-50 border border-slate-200 rounded-md text-xs text-slate-700 py-1.5 px-2.5 focus:outline-none focus:border-sky-500">
<option>Semua Tahap</option>
<option>Screening</option>
<option>Assessment</option>
<option>Wawancara</option>
<option>Offering</option>
<option>Rejected</option>
</select>
<!-- Match Score Filter -->
<select class="bg-slate-50 border border-slate-200 rounded-md text-xs text-slate-700 py-1.5 px-2.5 focus:outline-none focus:border-sky-500">
<option>Semua Skor</option>
<option selected="">Hanya Skor &gt; 80% (Excellent)</option>
<option>Skor 60% - 80% (Good)</option>
</select>
<!-- Sort Dropdown -->
<select class="bg-slate-50 border border-slate-200 rounded-md text-xs text-slate-700 py-1.5 px-2.5 focus:outline-none focus:border-sky-500 hidden sm:block">
<option>Skor Tertinggi</option>
<option>Terbaru</option>
<option>Nama A-Z</option>
</select>
</div>
<!-- Active Filter Chips -->
<div class="flex items-center gap-1.5 flex-wrap">
<span class="inline-flex items-center gap-1 bg-sky-50 text-sky-700 border border-sky-200 text-[11px] font-semibold px-2 py-0.5 rounded">
        Skor &gt; 80% (Excellent)
        <button class="hover:text-sky-900"><span class="material-symbols-outlined text-[12px]" data-icon="close">close</span></button>
</span>
<button class="text-[11px] text-slate-500 hover:text-slate-800 underline ml-1">Reset</button>
</div>
</section>
<!-- 5 & 6. WORKSPACE GRID WITH 5-COLUMN KANBAN BOARD + CANDIDATE DETAIL DRAWER -->
<div class="flex flex-col xl:flex-row gap-4 items-start">
<!-- 5-STAGE KANBAN BOARD (Scrollable horizontally if needed) -->
<section class="flex-1 w-full overflow-x-auto pb-4">
<div class="grid grid-cols-1 md:grid-cols-5 gap-3 min-w-[1080px] xl:min-w-0">
<!-- ============================================== -->
<!-- COLUMN 1: SCREENING (3) -->
<!-- ============================================== -->
<div class="bg-slate-100/70 border border-slate-200 rounded-lg p-2.5 flex flex-col gap-2 min-h-[580px]">
<!-- Column Header -->
<div class="flex items-center justify-between px-1 pb-1.5 border-b border-slate-200">
<div class="flex items-center gap-1.5">
<span class="w-2 h-2 rounded-full bg-slate-400"></span>
<h2 class="text-xs font-bold text-slate-800">1. Screening</h2>
</div>
<span class="bg-slate-200 text-slate-700 font-bold text-[11px] px-1.5 py-0.2 rounded-full">3</span>
</div>
<!-- Cards List -->
<div class="space-y-2">
<!-- Candidate 1: Ahmad Fauzi -->
<div class="bg-white border border-slate-200 rounded-lg p-2.5 shadow-xs hover:border-sky-500 hover:shadow-sm transition-all cursor-pointer">
<div class="flex items-start justify-between gap-1.5 mb-1.5">
<div class="flex items-center gap-2">
<input class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5" type="checkbox"/>
<div>
<h3 class="text-xs font-bold text-slate-900 hover:text-sky-600 transition-colors">Ahmad Fauzi</h3>
<p class="text-[11px] text-slate-500">Backend • 3 Thn</p>
</div>
</div>
<span class="inline-flex items-center bg-amber-50 text-amber-800 border border-amber-200 text-[10px] font-bold px-1.5 py-0.2 rounded">
                  78% Good
                </span>
</div>
<div class="flex flex-wrap gap-1 mb-2">
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">PHP</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Laravel</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">MySQL</span>
</div>
<button class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-semibold py-1 rounded transition-colors flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-xs" data-icon="description">description</span>
                Review Berkas
              </button>
</div>
<!-- Candidate 2: Rian Pratama -->
<div class="bg-white border border-slate-200 rounded-lg p-2.5 shadow-xs hover:border-sky-500 hover:shadow-sm transition-all cursor-pointer">
<div class="flex items-start justify-between gap-1.5 mb-1.5">
<div class="flex items-center gap-2">
<input class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5" type="checkbox"/>
<div>
<h3 class="text-xs font-bold text-slate-900">Rian Pratama</h3>
<p class="text-[11px] text-slate-500">Backend • 2 Thn</p>
</div>
</div>
<span class="inline-flex items-center bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-bold px-1.5 py-0.2 rounded">
                  65% Good
                </span>
</div>
<div class="flex flex-wrap gap-1 mb-2">
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">PHP</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">CodeIgniter</span>
</div>
<button class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-semibold py-1 rounded transition-colors flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-xs" data-icon="description">description</span>
                Review Berkas
              </button>
</div>
<!-- Candidate 3: Hendri K. -->
<div class="bg-white border border-slate-200 rounded-lg p-2.5 shadow-xs hover:border-sky-500 hover:shadow-sm transition-all cursor-pointer">
<div class="flex items-start justify-between gap-1.5 mb-1.5">
<div class="flex items-center gap-2">
<input class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5" type="checkbox"/>
<div>
<h3 class="text-xs font-bold text-slate-900">Hendri Kusuma</h3>
<p class="text-[11px] text-slate-500">Backend • 2 Thn</p>
</div>
</div>
<span class="inline-flex items-center bg-slate-100 text-slate-700 border border-slate-200 text-[10px] font-bold px-1.5 py-0.2 rounded">
                  70% Good
                </span>
</div>
<div class="flex flex-wrap gap-1 mb-2">
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Go</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">PostgreSQL</span>
</div>
<button class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-semibold py-1 rounded transition-colors flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-xs" data-icon="description">description</span>
                Review Berkas
              </button>
</div>
</div>
</div>
<!-- ============================================== -->
<!-- COLUMN 2: ASSESSMENT (2) -->
<!-- ============================================== -->
<div class="bg-slate-100/70 border border-slate-200 rounded-lg p-2.5 flex flex-col gap-2 min-h-[580px]">
<div class="flex items-center justify-between px-1 pb-1.5 border-b border-slate-200">
<div class="flex items-center gap-1.5">
<span class="w-2 h-2 rounded-full bg-sky-500"></span>
<h2 class="text-xs font-bold text-slate-800">2. Assessment</h2>
</div>
<span class="bg-sky-100 text-sky-700 font-bold text-[11px] px-1.5 py-0.2 rounded-full">2</span>
</div>
<div class="space-y-2">
<!-- Candidate 4: Dewi Lestari -->
<div class="bg-white border border-slate-200 rounded-lg p-2.5 shadow-xs hover:border-sky-500 hover:shadow-sm transition-all cursor-pointer">
<div class="flex items-start justify-between gap-1.5 mb-1.5">
<div class="flex items-center gap-2">
<input class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5" type="checkbox"/>
<div>
<h3 class="text-xs font-bold text-slate-900">Dewi Lestari</h3>
<p class="text-[11px] text-slate-500">API Specialist • 4 Thn</p>
</div>
</div>
<span class="inline-flex items-center bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold px-1.5 py-0.2 rounded">
                  88% Excellent
                </span>
</div>
<div class="flex flex-wrap gap-1 mb-2">
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">PHP</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Laravel</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Redis</span>
</div>
<button class="w-full bg-sky-600 hover:bg-sky-700 text-white text-[11px] font-semibold py-1 rounded transition-colors flex items-center justify-center gap-1">
                Jadwalkan Wawancara →
              </button>
</div>
<!-- Candidate 5: Eko Wicaksono -->
<div class="bg-white border border-slate-200 rounded-lg p-2.5 shadow-xs hover:border-sky-500 hover:shadow-sm transition-all cursor-pointer">
<div class="flex items-start justify-between gap-1.5 mb-1.5">
<div class="flex items-center gap-2">
<input class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5" type="checkbox"/>
<div>
<h3 class="text-xs font-bold text-slate-900">Eko Wicaksono</h3>
<p class="text-[11px] text-slate-500">Software Eng. • 3 Thn</p>
</div>
</div>
<span class="inline-flex items-center bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold px-1.5 py-0.2 rounded">
                  82% Excellent
                </span>
</div>
<div class="flex flex-wrap gap-1 mb-2">
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Laravel</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Docker</span>
</div>
<button class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-700 text-[11px] font-semibold py-1 rounded transition-colors flex items-center justify-center gap-1">
                Lihat Hasil Tes
              </button>
</div>
</div>
</div>
<!-- ============================================== -->
<!-- COLUMN 3: WAWANCARA / INTERVIEW (2) - ACTIVE SELECTION -->
<!-- ============================================== -->
<div class="bg-sky-50/50 border border-sky-200 rounded-lg p-2.5 flex flex-col gap-2 min-h-[580px]">
<div class="flex items-center justify-between px-1 pb-1.5 border-b border-sky-200">
<div class="flex items-center gap-1.5">
<span class="w-2 h-2 rounded-full bg-sky-600"></span>
<h2 class="text-xs font-bold text-sky-900">3. Wawancara</h2>
</div>
<span class="bg-sky-200 text-sky-800 font-bold text-[11px] px-1.5 py-0.2 rounded-full">2</span>
</div>
<div class="space-y-2">
<!-- Candidate 6: Budi Santoso (ACTIVE CARD) -->
<div class="bg-white border-2 border-sky-600 rounded-lg p-2.5 shadow-sm ring-2 ring-sky-100 relative cursor-pointer">
<div class="flex items-start justify-between gap-1.5 mb-1.5">
<div class="flex items-center gap-2">
<input checked="" class="rounded border-sky-400 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5" type="checkbox"/>
<div>
<h3 class="text-xs font-bold text-sky-950 flex items-center gap-1">
                      Budi Santoso
                      <span class="w-1.5 h-1.5 rounded-full bg-sky-600 inline-block"></span>
</h3>
<p class="text-[11px] text-slate-500">Sr. Backend • 5 Thn</p>
</div>
</div>
<span class="inline-flex items-center bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold px-1.5 py-0.2 rounded">
                  92% Excellent
                </span>
</div>
<div class="flex flex-wrap gap-1 mb-2">
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">PHP</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Laravel</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Microservices</span>
</div>
<div class="text-[10px] text-sky-700 bg-sky-50 px-2 py-0.5 rounded font-medium mb-2 flex items-center justify-between">
<span>Jadwal: 25 Okt, 14:00</span>
<span class="font-bold">Aktif Dipilih</span>
</div>
<button class="w-full bg-sky-600 hover:bg-sky-700 text-white text-[11px] font-semibold py-1 rounded transition-colors flex items-center justify-center gap-1">
                Beri Penawaran (Offering) →
              </button>
</div>
<!-- Candidate 7: Siti Rahmawati -->
<div class="bg-white border border-slate-200 rounded-lg p-2.5 shadow-xs hover:border-sky-500 hover:shadow-sm transition-all cursor-pointer">
<div class="flex items-start justify-between gap-1.5 mb-1.5">
<div class="flex items-center gap-2">
<input class="rounded border-slate-300 text-sky-600 focus:ring-sky-500 h-3.5 w-3.5" type="checkbox"/>
<div>
<h3 class="text-xs font-bold text-slate-900">Siti Rahmawati</h3>
<p class="text-[11px] text-slate-500">Backend • 3 Thn</p>
</div>
</div>
<span class="inline-flex items-center bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold px-1.5 py-0.2 rounded">
                  85% Excellent
                </span>
</div>
<div class="flex flex-wrap gap-1 mb-2">
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Laravel</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">MySQL</span>
</div>
<button class="w-full bg-sky-600 hover:bg-sky-700 text-white text-[11px] font-semibold py-1 rounded transition-colors flex items-center justify-center gap-1">
                Beri Penawaran (Offering) →
              </button>
</div>
</div>
</div>
<!-- ============================================== -->
<!-- COLUMN 4: PENAWARAN / OFFERING (1) -->
<!-- ============================================== -->
<div class="bg-slate-100/70 border border-slate-200 rounded-lg p-2.5 flex flex-col gap-2 min-h-[580px]">
<div class="flex items-center justify-between px-1 pb-1.5 border-b border-slate-200">
<div class="flex items-center gap-1.5">
<span class="w-2 h-2 rounded-full bg-emerald-600"></span>
<h2 class="text-xs font-bold text-slate-800">4. Penawaran</h2>
</div>
<span class="bg-emerald-100 text-emerald-800 font-bold text-[11px] px-1.5 py-0.2 rounded-full">1</span>
</div>
<div class="space-y-2">
<!-- Candidate 8: Fajar Nugraha -->
<div class="bg-white border border-emerald-200 rounded-lg p-2.5 shadow-xs hover:border-emerald-500 hover:shadow-sm transition-all cursor-pointer">
<div class="flex items-start justify-between gap-1.5 mb-1.5">
<div class="flex items-center gap-2">
<input checked="" class="rounded border-emerald-400 text-emerald-600 focus:ring-emerald-500 h-3.5 w-3.5" type="checkbox"/>
<div>
<h3 class="text-xs font-bold text-slate-900">Fajar Nugraha</h3>
<p class="text-[11px] text-slate-500">Lead Backend • 6 Thn</p>
</div>
</div>
<span class="inline-flex items-center bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-bold px-1.5 py-0.2 rounded">
                  95% Excellent
                </span>
</div>
<div class="flex flex-wrap gap-1 mb-2">
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">PHP</span>
<span class="bg-slate-100 text-slate-600 text-[10px] px-1.5 py-0.2 rounded">Microservices</span>
</div>
<div class="text-[10px] text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-medium mb-2 flex items-center justify-between">
<span>Rp 14.5jt / bln</span>
<span class="font-bold">Sent</span>
</div>
<button class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-[11px] font-semibold py-1 rounded transition-colors flex items-center justify-center gap-1">
                Lihat Penawaran
              </button>
</div>
</div>
</div>
<!-- ============================================== -->
<!-- COLUMN 5: REJECTED (1) -->
<!-- ============================================== -->
<div class="bg-slate-100/70 border border-slate-200 rounded-lg p-2.5 flex flex-col gap-2 min-h-[580px]">
<div class="flex items-center justify-between px-1 pb-1.5 border-b border-slate-200">
<div class="flex items-center gap-1.5">
<span class="w-2 h-2 rounded-full bg-rose-500"></span>
<h2 class="text-xs font-bold text-slate-800">5. Ditolak</h2>
</div>
<span class="bg-rose-100 text-rose-800 font-bold text-[11px] px-1.5 py-0.2 rounded-full">1</span>
</div>
<div class="space-y-2">
<!-- Candidate 9: Kurniawan -->
<div class="bg-white/80 border border-slate-200 rounded-lg p-2.5 shadow-xs opacity-75 hover:opacity-100 transition-opacity cursor-pointer">
<div class="flex items-start justify-between gap-1.5 mb-1.5">
<div class="flex items-center gap-2">
<input class="rounded border-slate-300 text-slate-500 focus:ring-slate-400 h-3.5 w-3.5" type="checkbox"/>
<div>
<h3 class="text-xs font-bold text-slate-600 line-through">Kurniawan</h3>
<p class="text-[11px] text-slate-400">Junior Dev • 1 Thn</p>
</div>
</div>
<span class="inline-flex items-center bg-rose-50 text-rose-800 border border-rose-200 text-[10px] font-bold px-1.5 py-0.2 rounded">
                  42% Low
                </span>
</div>
<p class="text-[10px] text-slate-500 mb-2 italic">Belum memenuhi syarat minimal technical skill.</p>
<button class="w-full bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 text-[11px] font-semibold py-1 rounded transition-colors flex items-center justify-center gap-1">
                Buka Arsip
              </button>
</div>
</div>
</div>
</div>
</section>
<!-- 6. CANDIDATE DETAIL DRAWER (SLIDE-OVER PANEL ON THE RIGHT) -->
<aside class="w-full xl:w-[420px] flex-shrink-0 bg-white border border-slate-200 rounded-xl shadow-lg flex flex-col overflow-hidden">
<!-- Drawer Header -->
<div class="p-4 border-b border-slate-200 bg-slate-50/75 flex items-start justify-between">
<div class="flex items-center gap-3">
<div class="w-10 h-10 rounded-full bg-sky-600 text-white font-bold text-sm flex items-center justify-center shadow-xs">
            BS
          </div>
<div>
<div class="flex items-center gap-2">
<h2 class="text-sm font-bold text-slate-900">Budi Santoso</h2>
<span class="bg-sky-100 text-sky-800 text-[10px] font-bold px-2 py-0.2 rounded">Interview</span>
</div>
<p class="text-xs text-slate-500">Senior Backend Engineer • Melamar 4 hari lalu</p>
</div>
</div>
<button class="text-slate-400 hover:text-slate-700 p-1 rounded-md hover:bg-slate-100" title="Tutup Drawer">
<span class="material-symbols-outlined text-base" data-icon="close">close</span>
</button>
</div>
<!-- Quick Action Buttons -->
<div class="px-4 py-2.5 bg-white border-b border-slate-200 flex items-center gap-2">
<button class="flex-1 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold py-1.5 px-3 rounded-md transition-colors flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-sm" data-icon="fast_forward">fast_forward</span>
          Pindahkan Tahap
        </button>
<button class="bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold py-1.5 px-3 rounded-md transition-colors flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-sm" data-icon="download">download</span>
          Unduh CV
        </button>
</div>
<!-- Tab Navigation -->
<div class="px-4 border-b border-slate-200 flex gap-4 text-xs font-semibold text-slate-500">
<button class="py-2.5 text-sky-600 border-b-2 border-sky-600">Detail &amp; Match</button>
<button class="py-2.5 hover:text-slate-800">Assessment</button>
<button class="py-2.5 hover:text-slate-800">Jadwal Wawancara</button>
<button class="py-2.5 hover:text-slate-800">Penawaran</button>
</div>
<!-- Tab Content Area -->
<div class="p-4 space-y-4 max-h-[580px] overflow-y-auto text-xs">
<!-- Match Score Breakdown -->
<div class="bg-sky-50/70 border border-sky-200 rounded-lg p-3">
<div class="flex items-center justify-between mb-1.5">
<span class="font-bold text-sky-950 flex items-center gap-1">
<span class="material-symbols-outlined text-sm text-sky-600" data-icon="auto_awesome">auto_awesome</span>
              Match Score: 92% Excellent
            </span>
<span class="text-[10px] text-sky-800 font-semibold uppercase">Weighted Jaccard</span>
</div>
<p class="text-[11px] text-slate-600 mb-2">Profil kandidat sangat sesuai dengan spesifikasi lowongan Backend Developer.</p>
<div class="space-y-1">
<div class="text-[11px] flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-sm" data-icon="check_circle">check_circle</span>
<span class="text-slate-700"><strong>Cocok:</strong> PHP, Laravel, MySQL, Docker, Microservices</span>
</div>
<div class="text-[11px] flex items-center gap-1.5">
<span class="material-symbols-outlined text-slate-400 text-sm" data-icon="info">info</span>
<span class="text-slate-500"><strong>Belum tertera:</strong> Redis (Optional bonus skill)</span>
</div>
</div>
</div>
<!-- Technical Assessment Record -->
<div class="border border-slate-200 rounded-lg p-3 space-y-2">
<div class="flex items-center justify-between">
<span class="font-bold text-slate-900">Hasil Technical Assessment</span>
<span class="bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded text-[11px]">88 / 100</span>
</div>
<div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
<div class="bg-emerald-600 h-1.5 rounded-full" style="width: 88%"></div>
</div>
<p class="text-[11px] text-slate-600 italic bg-slate-50 p-2 rounded border border-slate-100">
            "Sangat menguasai arsitektur RESTful API &amp; query optimization. Test coverage tugas mencapai 92%."
          </p>
</div>
<!-- Interview Details -->
<div class="border border-slate-200 rounded-lg p-3 space-y-2.5">
<span class="font-bold text-slate-900 block">Jadwal Wawancara Aktif</span>
<div class="grid grid-cols-2 gap-2 text-[11px]">
<div class="bg-slate-50 p-2 rounded">
<span class="text-slate-400 block text-[10px]">Waktu Wawancara</span>
<span class="font-semibold text-slate-800">25 Okt 2026, 14:00 WIB</span>
</div>
<div class="bg-slate-50 p-2 rounded">
<span class="text-slate-400 block text-[10px]">Pewawancara</span>
<span class="font-semibold text-slate-800">Sarah L. &amp; Hendra</span>
</div>
</div>
<div class="flex items-center justify-between bg-sky-50 border border-sky-100 p-2 rounded text-[11px]">
<div class="flex items-center gap-1.5 text-sky-800 font-medium truncate">
<span class="material-symbols-outlined text-sm" data-icon="videocam">videocam</span>
<span class="truncate">https://meet.jobharbour.id/interview-bs92</span>
</div>
<button class="text-sky-700 hover:text-sky-900 font-bold ml-2 text-[10px]">Salin</button>
</div>
</div>
<!-- State Transition Workflow -->
<div class="border-t border-slate-200 pt-3">
<span class="text-[11px] font-semibold text-slate-500 uppercase block mb-2">Transisi Pipeline (OAS State Machine)</span>
<div class="flex items-center gap-2">
<button class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-1.5 px-2 rounded-md transition-colors text-xs flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-sm" data-icon="forward_to_inbox">forward_to_inbox</span>
              Loloskan ke Offering
            </button>
<button class="bg-rose-50 hover:bg-rose-100 border border-rose-200 text-rose-700 font-semibold py-1.5 px-3 rounded-md transition-colors text-xs flex items-center justify-center gap-1">
<span class="material-symbols-outlined text-sm" data-icon="cancel">cancel</span>
              Tolak (Reject)
            </button>
</div>
</div>
</div>
</aside>
</div>
<!-- 7. REALISTIC BULK ACTION TOOLBAR (VISIBLE WHEN CANDIDATES ARE SELECTED) -->
<div class="fixed bottom-4 left-1/2 -translate-x-1/2 bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-xl flex items-center gap-4 z-50 border border-slate-800 animate-in fade-in slide-in-from-bottom-2">
<div class="flex items-center gap-2">
<span class="w-5 h-5 rounded-full bg-sky-500 text-white text-[11px] font-bold flex items-center justify-center">2</span>
<span class="text-xs font-semibold">kandidat dipilih</span>
</div>
<div class="h-4 w-px bg-slate-700"></div>
<div class="flex items-center gap-2">
<button class="bg-white/10 hover:bg-white/20 text-white text-xs font-medium px-2.5 py-1 rounded transition-colors flex items-center gap-1">
<span class="material-symbols-outlined text-sm" data-icon="download">download</span>
        Ekspor Data Pelamar
      </button>
<button class="text-slate-400 hover:text-white text-xs font-medium px-2 py-1 transition-colors">
        Batal Pilihan
      </button>
</div>
</div>
</main>
<!-- GLOBAL FOOTER -->
<footer class="bg-white border-t border-slate-200 mt-auto py-4">
<div class="max-w-[1720px] mx-auto px-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
<div class="flex items-center gap-2">
<span class="font-bold text-slate-900 flex items-center gap-1">
<span class="material-symbols-outlined text-sky-600 text-sm" data-icon="work">work</span>
        JobHarbour ATS
      </span>
<span>• © 2026 Hak Cipta Dilindungi Undang-Undang.</span>
</div>
<div class="flex items-center gap-4">
<a class="hover:text-slate-900" href="#">Ketentuan Layanan</a>
<a class="hover:text-slate-900" href="#">Kebijakan Privasi</a>
<a class="hover:text-slate-900" href="#">Panduan API</a>
<a class="text-sky-600 hover:underline font-medium" href="#">Pusat Bantuan</a>
</div>
</div>
</footer>
</body></html>