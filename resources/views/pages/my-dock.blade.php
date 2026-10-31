<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>JobHarbour - My Dock (Dashboard Pelamar & Linimasa Seleksi)</title>
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
@include('partials.navbar', ['active' => 'my-dock'])
<!-- MAIN VIEWPORT CONTAINER -->
<main class="flex-grow pt-6 pb-space-3xl max-w-[1600px] w-full mx-auto px-margin-desktop">
<!-- BREADCRUMB & HARBOUR TELEMETRY METRIC BANNER -->
<div class="mb-space-xl flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-outline-variant/40 pb-5">
<div>
<div class="flex items-center gap-2 text-coordinate-tag font-coordinate-tag text-on-surface-variant uppercase tracking-widest text-[11px] mb-1 flex-wrap">
<span class="flex items-center gap-1 font-bold text-secondary">
<span class="material-symbols-outlined text-[14px]">explore</span>
            PORT LOG
          </span>
<span>//</span>
<span id="dock-greeting-meta">MEMUAT PROFIL PELAMAR...</span>
<span>•</span>
<span id="dock-active-expeditions" class="text-on-surface font-semibold">STATUS: 0 LAMARAN</span>
</div>
<h1 id="dock-greeting-title" class="text-headline-lg font-headline-lg text-on-surface font-extrabold tracking-tight">
          My Dock: Dermaga Lamaran & Status Pelayaran Karier
        </h1>
<p id="dock-greeting-subtitle" class="text-body-sm font-body-sm text-on-surface-variant mt-1 max-w-3xl">
          Pantau setiap tahapan seleksi, hasil verifikasi berkas, jadwal wawancara, dan keputusan penerimaan kerja secara transparan.
        </p>
</div>
<!-- Quick Triage Action Pills -->
<div class="flex items-center gap-2 self-start md:self-auto">
<a href="/profile" class="px-3 py-1.5 bg-surface-container-lowest border border-outline-variant text-on-surface rounded-lg text-label-sm font-label-sm flex items-center gap-1.5 hover:bg-surface-container transition-colors shadow-sm">
<span class="material-symbols-outlined text-[16px]">person</span>
<span>Kelola Keahlian</span>
</a>
<button id="btn-sync-dock" type="button" class="px-3 py-1.5 bg-secondary-fixed text-on-secondary-fixed-variant rounded-lg text-label-sm font-label-sm font-semibold flex items-center gap-1.5 hover:bg-secondary-fixed-dim transition-colors">
<span class="material-symbols-outlined text-[16px]">sync</span>
<span>Sinkronisasi Data Port</span>
</button>
</div>
</div>
<!-- NAUTICAL SUMMARY METRIC CARDS -->
<section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-gutter-desktop mb-space-2xl">
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
<span id="stat-total-count" class="text-headline-lg font-headline-lg font-bold text-on-surface">0</span>
<span class="text-label-md font-label-md text-on-surface-variant">Berkas</span>
</div>
<div class="text-label-sm font-label-sm font-semibold text-on-surface mt-0.5">Total Lamaran Terkirim</div>
</div>
<div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag">
<span id="stat-total-badge" class="px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-semibold">Armada Siap</span>
<span id="stat-total-sub" class="text-on-surface-variant">0 Aktif</span>
</div>
</div>
<!-- Card 2: Screening & Assessment Aktif -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl border border-outline-variant/60 shadow-sm flex flex-col justify-between hover:border-secondary transition-all">
<div class="flex items-center justify-between mb-3">
<span class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider text-secondary text-[11px]">INSPECTION PHASE</span>
<div class="w-8 h-8 rounded-lg bg-secondary-fixed text-on-secondary-fixed-variant flex items-center justify-center">
<span class="material-symbols-outlined text-[18px]">fact_check</span>
</div>
</div>
<div>
<div class="flex items-baseline gap-2">
<span id="stat-screening-count" class="text-headline-lg font-headline-lg font-bold text-secondary">0</span>
<span class="text-label-md font-label-md text-on-surface-variant">Berkas</span>
</div>
<div class="text-label-sm font-label-sm font-semibold text-on-surface mt-0.5">Screening & Assessment</div>
</div>
<div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag">
<span id="stat-screening-badge" class="px-2 py-0.5 rounded bg-secondary-fixed/50 text-on-secondary-fixed-variant font-semibold">Evaluasi Berkas</span>
<span id="stat-screening-sub" class="text-secondary font-bold">0% Queue</span>
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
<span id="stat-interview-count" class="text-headline-lg font-headline-lg font-bold text-on-tertiary-fixed-variant">0</span>
<span class="text-label-md font-label-md text-on-surface-variant">Wawancara</span>
</div>
<div class="text-label-sm font-label-sm font-semibold text-on-surface mt-0.5">Jadwal Interview Aktif</div>
</div>
<div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag">
<span id="stat-interview-badge" class="px-2 py-0.5 rounded bg-tertiary-fixed text-on-tertiary-fixed-variant font-semibold flex items-center gap-1.5">
            Belum Terjadwal
          </span>
<span id="stat-interview-sub" class="text-on-tertiary-fixed-variant font-bold">Standby</span>
</div>
</div>
<!-- Card 4: Tawaran Kerja (Offering) & Ditolak -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl border border-outline-variant/60 shadow-sm flex flex-col justify-between hover:border-secondary transition-all">
<div class="flex items-center justify-between mb-3">
<span class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider text-on-surface-variant text-[11px]">FINAL PORT HARBOUR</span>
<div class="w-8 h-8 rounded-lg bg-surface-container-high text-on-surface flex items-center justify-center">
<span class="material-symbols-outlined text-[18px]" data-weight="fill">verified</span>
</div>
</div>
<div>
<div class="flex items-baseline gap-2">
<span id="stat-offering-count" class="text-headline-lg font-headline-lg font-bold text-on-surface">0</span>
<span class="text-label-md font-label-md text-on-surface-variant">Tawaran</span>
</div>
<div class="text-label-sm font-label-sm font-semibold text-on-surface mt-0.5">Offering & Keputusan Akhir</div>
</div>
<div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag">
<span id="stat-offering-badge" class="px-2 py-0.5 rounded bg-surface-container text-on-surface-variant font-semibold">0 Offering</span>
<span id="stat-rejected-sub" class="text-on-surface-variant font-bold">0 Ditolak</span>
</div>
</div>
<!-- Card 5: Rata-rata Skor Kecocokan -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl border border-outline-variant/60 shadow-sm flex flex-col justify-between hover:border-secondary transition-all">
<div class="flex items-center justify-between mb-3">
<span class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider text-secondary text-[11px]">ANCHOR FIT ENGINE</span>
<div class="w-8 h-8 rounded-lg bg-secondary-fixed text-on-secondary-fixed-variant flex items-center justify-center">
<span class="material-symbols-outlined text-[18px]" data-weight="fill">anchor</span>
</div>
</div>
<div>
<div class="flex items-baseline gap-2">
<span id="stat-avg-score" class="text-headline-lg font-headline-lg font-bold text-secondary">0%</span>
<span class="text-label-md font-label-md text-on-surface-variant">Match</span>
</div>
<div class="text-label-sm font-label-sm font-semibold text-on-surface mt-0.5">Rata-Rata Skor Kecocokan</div>
</div>
<div class="mt-4 pt-3 border-t border-outline-variant/40 flex items-center justify-between text-coordinate-tag font-coordinate-tag">
<span id="stat-avg-badge" class="px-2 py-0.5 rounded bg-secondary-fixed/50 text-on-secondary-fixed-variant font-semibold">Weighted Jaccard</span>
<span id="stat-avg-sub" class="text-on-surface-variant font-bold">-</span>
</div>
</div>
</section>
<!-- DUAL COMPONENT MAIN WORKSPACE (Desktop Split Grid: 62% Applications Table + 38% Voyage Pipeline Stepper) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop items-start">
<!-- LEFT SECTION: DAFTAR BERKAS LAMARAN AKTIF -->
<section class="lg:col-span-7 flex flex-col gap-space-lg">
<!-- Header, Search & Filter Tabs Panel -->
<div class="bg-surface-container-lowest p-space-lg rounded-xl border border-outline-variant/60 shadow-sm flex flex-col gap-4">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
<div class="flex items-center gap-2">
<div class="w-2.5 h-2.5 rounded-full bg-secondary"></div>
<h2 class="text-headline-sm font-headline-sm text-on-surface font-bold">Daftar Berkas Lamaran</h2>
<span id="apps-count-pill" class="px-2 py-0.5 rounded-full bg-surface-container text-on-surface-variant text-label-sm font-label-sm font-bold">
              0 Ekspedisi
            </span>
</div>
<!-- Controls: Input Search & Dropdown -->
<div class="flex items-center gap-2">
<div class="relative">
<span class="material-symbols-outlined absolute left-2.5 top-1/2 -translate-y-1/2 text-outline text-[18px]">search</span>
<input id="filter-search-input" class="pl-8 pr-3 py-1.5 text-body-sm font-body-sm bg-surface-container-low border border-outline-variant/60 rounded-lg text-on-surface focus:outline-none focus:border-secondary w-48 sm:w-56" placeholder="Cari perusahaan / posisi..." type="text" value=""/>
</div>
<select id="filter-status-select" class="py-1.5 px-3 text-body-sm font-body-sm bg-surface-container-low border border-outline-variant/60 rounded-lg text-on-surface focus:outline-none focus:border-secondary">
<option value="all">Semua Status</option>
<option value="screening">Screening</option>
<option value="assessment">Assessment</option>
<option value="interview">Interview</option>
<option value="offering">Offering</option>
<option value="rejected">Ditolak</option>
<option value="canceled">Dibatalkan</option>
</select>
</div>
</div>
<!-- Filter Tabs per Status -->
<div id="status-filter-tabs" class="flex items-center gap-1.5 overflow-x-auto pb-1 border-t border-outline-variant/40 pt-3">
<button type="button" data-status-tab="all" class="px-3 py-1.5 rounded-lg text-label-sm font-label-sm font-semibold bg-secondary text-on-secondary transition-colors shrink-0">
            Semua
          </button>
<button type="button" data-status-tab="screening" class="px-3 py-1.5 rounded-lg text-label-sm font-label-sm font-medium bg-surface-container text-on-surface-variant hover:text-on-surface transition-colors shrink-0">
            Screening
          </button>
<button type="button" data-status-tab="assessment" class="px-3 py-1.5 rounded-lg text-label-sm font-label-sm font-medium bg-surface-container text-on-surface-variant hover:text-on-surface transition-colors shrink-0">
            Assessment
          </button>
<button type="button" data-status-tab="interview" class="px-3 py-1.5 rounded-lg text-label-sm font-label-sm font-medium bg-surface-container text-on-surface-variant hover:text-on-surface transition-colors shrink-0">
            Interview
          </button>
<button type="button" data-status-tab="offering" class="px-3 py-1.5 rounded-lg text-label-sm font-label-sm font-medium bg-surface-container text-on-surface-variant hover:text-on-surface transition-colors shrink-0">
            Offering
          </button>
<button type="button" data-status-tab="rejected" class="px-3 py-1.5 rounded-lg text-label-sm font-label-sm font-medium bg-surface-container text-on-surface-variant hover:text-on-surface transition-colors shrink-0">
            Ditolak / Batal
          </button>
</div>
</div>
<!-- APPLICATIONS LIST CONTAINER -->
<div id="applications-list-container" class="space-y-3.5">
<!-- Diisi secara dinamis oleh public/js/pages/my-dock.js -->
<div class="bg-surface-container-lowest rounded-xl border border-outline-variant/60 p-space-xl text-center text-on-surface-variant">
<span class="material-symbols-outlined animate-spin text-[28px] text-secondary mb-2">progress_activity</span>
<p class="text-body-md font-medium">Memuat daftar berkas lamaran dari pelabuhan...</p>
</div>
</div>
<!-- Pagination or Bottom Notice -->
<div class="p-3 bg-surface-container rounded-lg flex flex-col sm:flex-row items-center justify-between gap-2 text-body-sm font-body-sm text-on-surface-variant">
<span id="applications-footer-count" class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider">MENAMPILKAN 0 DARI 0 BERKAS LAMARAN</span>
<span class="flex items-center gap-1 text-label-sm font-label-sm text-on-surface">
<span class="material-symbols-outlined text-[16px] text-secondary">security</span>
            Transparansi Seleksi Berbasis Weighted Jaccard Similarity
          </span>
</div>
</section>
<!-- RIGHT SECTION: VOYAGE PIPELINE STEPPER DRAWER (38% => lg:col-span-5) -->
<aside class="lg:col-span-5 sticky top-20">
<div id="pipeline-drawer-card" class="bg-surface-container-lowest rounded-xl border border-outline-variant/60 shadow-sm overflow-hidden">
<!-- Stepper Drawer Header -->
<div id="pipeline-drawer-header" class="p-space-lg bg-primary-container text-on-primary">
<div class="flex items-center justify-between mb-2">
<span class="px-2 py-0.5 rounded text-[10px] font-coordinate-tag uppercase tracking-wider bg-surface-container-lowest/15 text-primary-fixed font-bold flex items-center gap-1">
<span class="material-symbols-outlined text-[12px]">directions_boat</span>
                VOYAGE TRACKER // PIPELINE SERVICE
              </span>
<span id="drawer-berth-code" class="text-coordinate-tag font-coordinate-tag text-primary-fixed-dim uppercase tracking-widest text-[11px]">BERTH --</span>
</div>
<h3 id="drawer-job-title" class="text-headline-sm font-headline-sm font-bold text-on-primary">
              Pilih Berkas Lamaran
            </h3>
<p id="drawer-job-company" class="text-body-sm font-body-sm text-primary-fixed-dim mt-0.5">
              Klik salah satu berkas di sebelah kiri untuk memantau linimasa seleksi.
            </p>
<div class="mt-3 pt-3 border-t border-on-primary-fixed-variant/50 flex items-center justify-between">
<span class="text-coordinate-tag font-coordinate-tag text-primary-fixed uppercase tracking-wider">SKOR KECOCOKAN REGISTRI</span>
<span id="drawer-match-badge" class="px-2 py-0.5 rounded bg-secondary-fixed text-on-secondary-fixed-variant font-bold text-label-sm">-</span>
</div>
</div>
<!-- MATCHED & MISSING SKILLS BREAKDOWN -->
<div id="drawer-skills-section" class="p-space-lg border-b border-outline-variant/50 bg-surface-container-low/60 hidden">
<div class="flex items-center justify-between mb-2">
<span class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider text-on-surface-variant text-[11px]">RINCIAN PEMADANAN KEAHLIAN</span>
<span id="drawer-match-category" class="text-coordinate-tag font-coordinate-tag uppercase tracking-wider text-secondary font-bold text-[10px]">-</span>
</div>
<div class="space-y-2.5">
<div>
<div class="text-label-sm font-label-sm text-on-surface font-semibold mb-1 flex items-center gap-1">
<span class="material-symbols-outlined text-[14px] text-emerald-600">check_circle</span>
<span>Keahlian Cocok (Matched Skills)</span>
</div>
<div id="drawer-matched-skills" class="flex flex-wrap gap-1.5"></div>
</div>
<div>
<div class="text-label-sm font-label-sm text-on-surface font-semibold mb-1 flex items-center gap-1">
<span class="material-symbols-outlined text-[14px] text-amber-600">error</span>
<span>Keahlian Belum Terpenuhi (Missing Skills)</span>
</div>
<div id="drawer-missing-skills" class="flex flex-wrap gap-1.5"></div>
</div>
</div>
</div>
<!-- VERTICAL STEPPER WITH NAUTICAL CONNECTING TRACK -->
<div id="drawer-timeline-body" class="p-space-lg relative space-y-6">
<div class="text-center py-8 text-on-surface-variant">
<span class="material-symbols-outlined text-[32px] text-outline mb-2">timeline</span>
<p class="text-body-sm font-body-sm">Pilih berkas lamaran untuk menampilkan tahapan seleksi.</p>
</div>
</div>
<!-- BOTTOM ACTION BAR OF DRAWER -->
<div id="drawer-actions-bar" class="p-space-lg bg-surface-container-low border-t border-outline-variant/60 flex flex-col gap-2">
<button id="btn-drawer-download-cv" type="button" class="w-full py-2.5 px-3 rounded-lg border border-outline-variant/80 bg-surface-container-lowest hover:bg-surface-container text-on-surface text-label-md font-label-md font-semibold flex items-center justify-center gap-2 transition-colors shadow-sm hidden">
<span class="material-symbols-outlined text-[18px] text-error">picture_as_pdf</span>
<span>Lihat / Unduh CV Lamaran (PDF)</span>
</button>
<button id="btn-drawer-cancel-app" type="button" class="w-full py-2 px-3 rounded-lg text-error hover:bg-error-container/60 border border-error/30 text-label-sm font-label-sm font-semibold flex items-center justify-center gap-1.5 transition-colors hidden">
<span class="material-symbols-outlined text-[16px]">cancel</span>
<span>Batalkan Pengajuan Lamaran</span>
</button>
<a href="/catalog" class="w-full py-2 px-3 rounded-lg text-on-surface-variant hover:text-on-surface text-label-sm font-label-sm font-medium flex items-center justify-center gap-1.5 transition-colors">
<span class="material-symbols-outlined text-[16px]">travel_explore</span>
<span>Cari Lowongan Lain di The Anchorage</span>
</a>
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
        © 2026 JobHarbour - Universitas Harkat Negeri, Tegal. Pelabuhan Karir & Talenta Indonesia.
      </p>
</div>
<!-- Links -->
<div class="flex items-center gap-6 text-label-sm font-label-sm flex-wrap justify-center">
<a class="text-on-surface-variant hover:text-secondary transition-colors" href="/catalog">
        The Anchorage (Lowongan)
      </a>
<a class="text-on-surface-variant hover:text-secondary transition-colors" href="/my-dock">
        My Dock (Lamaran)
      </a>
<a class="text-on-surface-variant hover:text-secondary transition-colors" href="/profile">
        Captain's Ledger (Profil)
      </a>
</div>
</footer>
<script src="/js/jh-api.js"></script>
<script src="/js/pages/my-dock.js"></script>
</body></html>
