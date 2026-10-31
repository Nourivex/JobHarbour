<!DOCTYPE html>

<html class="light" lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Harbour Master Deck - JobHarbour Recruiter Command</title>
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
@include('partials.navbar', ['active' => 'recruiter'])

<!-- MAIN OPERATIONAL WORKSPACE -->
<main class="flex-1 pb-12 w-full max-w-[1600px] mx-auto px-margin-desktop">
<!-- COMMAND TOOLBAR & DOSSIER HEADER -->
<section class="pt-6 pb-4 border-b border-outline-variant/50">
<!-- Coordinate Telemetry Breadcrumb -->
<div class="flex flex-wrap items-center justify-between gap-2 mb-2">
<div class="flex items-center gap-2">
<span class="inline-flex items-center gap-1 font-coordinate-tag text-coordinate-tag bg-surface-container-high text-on-surface-variant px-2 py-0.5 rounded tracking-widest border border-outline-variant/60">
<span class="w-1.5 h-1.5 rounded-full bg-secondary animate-pulse"></span>
            HARBOUR MASTER COMMAND // BERTH PIPELINE DISPATCH
          </span>
<span class="text-label-sm font-label-sm text-outline">NODE: PELABUHAN NUSANTARA - TEGAL COMMAND</span>
</div>
<div class="flex items-center gap-2 text-label-sm font-label-sm text-on-surface-variant">
<span class="material-symbols-outlined text-sm" data-icon="sync">sync</span>
<span>Sinkronisasi API: <strong id="sync-status-label" class="text-on-surface">Aktif (Live)</strong></span>
</div>
</div>
<!-- Title & Operational Purpose -->
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4">
<div>
<h1 class="text-headline-lg font-headline-lg text-primary tracking-tight">
            Harbour Master Deck: Manajemen Seleksi &amp; Pipeline Kandidat
          </h1>
<p class="text-body-md font-body-md text-on-surface-variant mt-1 max-w-4xl">
            Kelola alur penyeleksian talenta maritim tech secara visual, pantau skor kecocokan algoritma Weighted Jaccard Similarity, jadwalkan wawancara teknis, dan kirim penawaran kerja dalam satu dermaga kerja.
          </p>
</div>
<!-- Quick Switch Vacancy Selector Dock -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-2.5 flex items-center gap-3 shadow-xs min-w-[320px]">
<div class="w-10 h-10 rounded-lg bg-surface-container flex items-center justify-center text-secondary shrink-0">
<span class="material-symbols-outlined text-2xl" data-icon="directions_boat">directions_boat</span>
</div>
<div class="flex flex-col pr-2 flex-1 min-w-0">
<label for="job-filter-select" class="text-coordinate-tag font-coordinate-tag text-on-surface-variant uppercase tracking-wider">Filter Pipeline Berdasarkan Lowongan</label>
<select id="job-filter-select" class="mt-0.5 w-full bg-transparent border-0 p-0 pr-6 font-headline-sm text-headline-sm font-bold text-on-surface focus:ring-0 cursor-pointer truncate">
<option value="all">Semua Lowongan Dikelola</option>
</select>
<span id="selected-job-meta" class="text-label-sm font-label-sm text-outline truncate">Memuat daftar lowongan...</span>
</div>
<div class="flex items-center gap-1 border-l border-outline-variant/60 pl-2">
<button id="btn-edit-selected-job" class="p-2 rounded-lg hover:bg-surface-container text-on-surface-variant hover:text-secondary transition-colors disabled:opacity-40 disabled:pointer-events-none" title="Edit Lowongan Terpilih" type="button" disabled>
<span class="material-symbols-outlined text-lg" data-icon="edit_note">edit_note</span>
</button>
</div>
</div>
</div>
</section>

<!-- COLLAPSIBLE MANAGED JOBS LIST SECTION -->
<section id="managed-jobs-section" class="py-4 border-b border-outline-variant/50">
<div class="flex items-center justify-between mb-3">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-secondary text-xl" data-icon="business_center">business_center</span>
<h2 class="text-headline-sm font-headline-sm font-bold text-on-surface">Daftar Lowongan yang Dikelola</h2>
<span id="managed-jobs-badge" class="bg-secondary-fixed text-on-secondary-fixed text-label-sm font-label-sm px-2.5 py-0.5 rounded-full font-bold">0 Lowongan</span>
</div>
<button id="btn-create-job-inline" class="text-secondary hover:underline text-label-sm font-label-sm font-semibold flex items-center gap-1" type="button">
<span class="material-symbols-outlined text-base" data-icon="add_circle">add_circle</span>
<span>Tambah Lowongan Baru</span>
</button>
</div>
<div id="managed-jobs-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
<!-- Diisi dinamis oleh recruiter.js -->
<div class="col-span-full bg-surface-container-lowest border border-outline-variant/60 rounded-xl p-4 text-center text-outline text-body-sm">
        Memuat daftar lowongan dari server...
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
<span id="stat-total-applicants" class="text-headline-md font-headline-md font-bold text-on-surface">0 Pelamar</span>
<span id="stat-active-jobs-sub" class="text-body-sm font-body-sm text-outline block text-[11px] mt-0.5">0 Lowongan Aktif</span>
</div>
<div class="w-9 h-9 rounded-lg bg-surface-container-low flex items-center justify-center text-outline">
<span class="material-symbols-outlined text-xl" data-icon="inbox">inbox</span>
</div>
</div>
<!-- 2. Rata-Rata Skor Kecocokan -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-3.5 flex items-center justify-between">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-secondary block uppercase">Rata-Rata Match</span>
<span id="stat-avg-score" class="text-headline-md font-headline-md font-bold text-secondary">0%</span>
<span id="stat-high-fit-sub" class="text-body-sm font-body-sm text-outline block text-[11px] mt-0.5">0 Kandidat Skor &gt;= 80%</span>
</div>
<div class="w-9 h-9 rounded-lg bg-secondary-fixed/50 flex items-center justify-center text-secondary">
<span class="material-symbols-outlined text-xl" data-icon="analytics">analytics</span>
</div>
</div>
<!-- 3. Screening & Assessment -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-3.5 flex items-center justify-between">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant block uppercase">Screening &amp; Uji</span>
<span id="stat-screening-assessment" class="text-headline-md font-headline-md font-bold text-on-surface">0 Kandidat</span>
<span id="stat-screening-sub" class="text-body-sm font-body-sm text-outline block text-[11px] mt-0.5">0 Screening - 0 Assessment</span>
</div>
<div class="w-9 h-9 rounded-lg bg-surface-container-low flex items-center justify-center text-secondary">
<span class="material-symbols-outlined text-xl" data-icon="terminal">terminal</span>
</div>
</div>
<!-- 4. Interview Radar -->
<div class="bg-surface-container-lowest border border-tertiary-fixed-dim/60 rounded-xl p-3.5 flex items-center justify-between bg-gradient-to-br from-surface-container-lowest to-tertiary-fixed/10">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-on-tertiary-container block uppercase font-bold">Signal Radar</span>
<span id="stat-interview-count" class="text-headline-md font-headline-md font-bold text-on-tertiary-container">0 Wawancara</span>
<span class="text-body-sm font-body-sm text-on-tertiary-container block text-[11px] mt-0.5">Tahap Interview Teknis</span>
</div>
<div class="w-9 h-9 rounded-lg bg-tertiary-fixed flex items-center justify-center text-on-tertiary-fixed">
<span class="material-symbols-outlined text-xl" data-icon="video_camera_front">video_camera_front</span>
</div>
</div>
<!-- 5. Offering & Ditolak -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-3.5 flex items-center justify-between">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-emerald-700 block uppercase">Offering / Arsip</span>
<span id="stat-offering-count" class="text-headline-md font-headline-md font-bold text-emerald-800">0 Offering</span>
<span id="stat-rejected-sub" class="text-body-sm font-body-sm text-outline block text-[11px] mt-0.5">0 Ditolak</span>
</div>
<div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-800">
<span class="material-symbols-outlined text-xl" data-icon="draft">draft</span>
</div>
</div>
</div>

<!-- Search & Filter Pill Controls Bar -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-3 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 shadow-xs">
<!-- Search Input -->
<div class="relative flex-1 max-w-md">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-outline text-lg" data-icon="search">search</span>
<input id="candidate-search-input" class="w-full pl-9 pr-4 py-2 bg-surface-container-low border border-outline-variant/60 rounded-lg text-body-md font-body-md text-on-surface placeholder:text-outline focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/15 transition-all" placeholder="Cari nama kandidat, skill, atau lowongan..." type="text"/>
</div>
<!-- Quick Filter Pills -->
<div id="quick-filter-pills" class="flex items-center gap-1.5 flex-wrap overflow-x-auto pb-1 md:pb-0">
<button data-quick-filter="all" class="bg-primary-container text-white text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 font-semibold transition-colors" type="button">
            Semua Pelamar (<span id="pill-count-all">0</span>)
          </button>
<button data-quick-filter="high_score" class="bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface border border-outline-variant/60 text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors" type="button">
<span class="w-2 h-2 rounded-full bg-emerald-500"></span>
            Skor &gt;= 80% (Anchor Fit - <span id="pill-count-high">0</span>)
          </button>
<button data-quick-filter="needs_review" class="bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface border border-outline-variant/60 text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors" type="button">
            Tahap Awal / Screening (<span id="pill-count-screening">0</span>)
          </button>
<button data-quick-filter="has_cv" class="bg-surface-container-low hover:bg-surface-container text-on-surface-variant hover:text-on-surface border border-outline-variant/60 text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors" type="button">
            Ada Berkas CV (<span id="pill-count-cv">0</span>)
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
<div class="bg-surface-container-low/70 border border-outline-variant/70 rounded-xl p-3 flex flex-col gap-3 min-h-[580px]">
<div class="flex items-center justify-between px-1 pb-2 border-b border-outline-variant/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-outline"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">1. Masuk (Screening)</h2>
</div>
<span id="col-count-screening" class="bg-surface-container-highest text-on-surface-variant font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">0</span>
</div>
<div id="col-list-screening" class="space-y-3"></div>
</div>

<!-- ============================================== -->
<!-- COLUMN 2: REVIEW TEKNIS (ASSESSMENT) -->
<!-- ============================================== -->
<div class="bg-surface-container-low/70 border border-outline-variant/70 rounded-xl p-3 flex flex-col gap-3 min-h-[580px]">
<div class="flex items-center justify-between px-1 pb-2 border-b border-outline-variant/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-secondary"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">2. Uji Teknis (Assessment)</h2>
</div>
<span id="col-count-assessment" class="bg-secondary-fixed text-on-secondary-fixed font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">0</span>
</div>
<div id="col-list-assessment" class="space-y-3"></div>
</div>

<!-- ============================================== -->
<!-- COLUMN 3: WAWANCARA (INTERVIEW) -->
<!-- ============================================== -->
<div class="bg-surface-container-low/90 border-2 border-tertiary-fixed-dim/70 shadow-sm rounded-xl p-3 flex flex-col gap-3 min-h-[580px] relative">
<div class="flex items-center justify-between px-1 pb-2 border-b border-tertiary-fixed-dim/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">3. Wawancara (Interview)</h2>
</div>
<span id="col-count-interview" class="bg-tertiary-fixed text-on-tertiary-fixed font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">0</span>
</div>
<div id="col-list-interview" class="space-y-3"></div>
</div>

<!-- ============================================== -->
<!-- COLUMN 4: PENAWARAN KERJA (OFFERING) -->
<!-- ============================================== -->
<div class="bg-surface-container-low/70 border border-outline-variant/70 rounded-xl p-3 flex flex-col gap-3 min-h-[580px]">
<div class="flex items-center justify-between px-1 pb-2 border-b border-outline-variant/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">4. Penawaran (Offering)</h2>
</div>
<span id="col-count-offering" class="bg-emerald-100 text-emerald-800 font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">0</span>
</div>
<div id="col-list-offering" class="space-y-3"></div>
</div>

<!-- ============================================== -->
<!-- COLUMN 5: DITOLAK (REJECTED) -->
<!-- ============================================== -->
<div class="bg-surface-container-low/70 border border-outline-variant/70 rounded-xl p-3 flex flex-col gap-3 min-h-[580px]">
<div class="flex items-center justify-between px-1 pb-2 border-b border-outline-variant/60">
<div class="flex items-center gap-2">
<span class="w-2.5 h-2.5 rounded-full bg-error"></span>
<h2 class="text-label-md font-label-md font-bold text-on-surface">5. Ditolak (Rejected)</h2>
</div>
<span id="col-count-rejected" class="bg-error-container text-on-error-container font-bold text-label-sm font-label-sm px-2 py-0.5 rounded-full">0</span>
</div>
<div id="col-list-rejected" class="space-y-3"></div>
</div>
</div>
</section>
</main>

<!-- ===================================================================== -->
<!-- MODAL 1: DETAIL KANDIDAT & UPDATE TAHAPAN SELEKSI                     -->
<!-- ===================================================================== -->
<div id="candidate-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto">
<div class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] flex flex-col overflow-hidden my-auto">
<!-- Modal Header -->
<div class="px-6 py-4 bg-primary-container text-white flex items-center justify-between">
<div class="flex items-center gap-3">
<div id="modal-cand-initials" class="w-11 h-11 rounded-xl bg-secondary text-white font-headline-sm font-bold flex items-center justify-center">
          --
        </div>
<div>
<h3 id="modal-cand-name" class="text-headline-sm font-headline-sm font-bold">Nama Kandidat</h3>
<p id="modal-cand-job" class="text-body-sm text-inverse-primary">Melamar: -</p>
</div>
</div>
<div class="flex items-center gap-2">
<span id="modal-cand-score-badge" class="px-3 py-1 rounded-full text-label-sm font-bold bg-emerald-100 text-emerald-800">0% MATCH</span>
<button id="btn-close-candidate-modal" class="p-1.5 rounded-lg text-inverse-primary hover:text-white hover:bg-white/10 transition-colors" type="button">
<span class="material-symbols-outlined">close</span>
</button>
</div>
</div>

<!-- Modal Body -->
<div class="p-6 overflow-y-auto space-y-6 flex-1">
<!-- Biodata & CV Row -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-surface-container-low p-4 rounded-xl border border-outline-variant/60">
<div>
<span class="text-coordinate-tag font-coordinate-tag text-outline uppercase block">Email &amp; Kontak</span>
<p id="modal-cand-email" class="text-label-md font-semibold text-on-surface mt-0.5">-</p>
<p id="modal-cand-phone" class="text-body-sm text-on-surface-variant">-</p>
</div>
<div>
<span class="text-coordinate-tag font-coordinate-tag text-outline uppercase block">Lokasi &amp; Waktu Daftar</span>
<p id="modal-cand-location" class="text-label-md font-semibold text-on-surface mt-0.5">-</p>
<p id="modal-cand-applied-at" class="text-body-sm text-on-surface-variant">-</p>
</div>
<div class="flex flex-col justify-center items-start md:items-end">
<button id="btn-modal-download-cv" class="bg-secondary hover:bg-on-secondary-container text-white text-label-sm font-label-sm px-4 py-2 rounded-lg flex items-center gap-1.5 shadow-xs transition-colors" type="button">
<span class="material-symbols-outlined text-base">description</span>
<span>Unduh CV (PDF)</span>
</button>
<span id="modal-no-cv-note" class="text-body-sm text-outline italic hidden">Kandidat tidak melampirkan PDF CV</span>
</div>
</div>

<!-- Bio & Cover Letter -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-4">
<h4 class="text-label-md font-label-md font-bold text-on-surface mb-1.5 flex items-center gap-1.5">
<span class="material-symbols-outlined text-base text-secondary">badge</span>
            Profil Singkat Pelamar
          </h4>
<p id="modal-cand-bio" class="text-body-sm text-on-surface-variant whitespace-pre-line">-</p>
</div>
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-4">
<h4 class="text-label-md font-label-md font-bold text-on-surface mb-1.5 flex items-center gap-1.5">
<span class="material-symbols-outlined text-base text-secondary">mail</span>
            Surat Pengantar (Cover Letter)
          </h4>
<p id="modal-cand-cover-letter" class="text-body-sm text-on-surface-variant whitespace-pre-line">-</p>
</div>
</div>

<!-- Matched vs Missing Skills -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="bg-emerald-50/60 border border-emerald-200 rounded-xl p-4">
<h4 class="text-label-sm font-label-sm font-bold text-emerald-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
<span class="material-symbols-outlined text-base text-emerald-600">check_circle</span>
            Keahlian Cocok (Matched Skills)
          </h4>
<div id="modal-cand-matched-skills" class="flex flex-wrap gap-1.5"></div>
</div>
<div class="bg-rose-50/60 border border-rose-200 rounded-xl p-4">
<h4 class="text-label-sm font-label-sm font-bold text-rose-900 uppercase tracking-wider mb-2 flex items-center gap-1.5">
<span class="material-symbols-outlined text-base text-rose-600">cancel</span>
            Keahlian Belum Terpenuhi (Missing Skills)
          </h4>
<div id="modal-cand-missing-skills" class="flex flex-wrap gap-1.5"></div>
</div>
</div>

<!-- Riwayat Linimasa Pipeline -->
<div class="bg-surface-container-lowest border border-outline-variant/70 rounded-xl p-4">
<h4 class="text-label-md font-label-md font-bold text-on-surface mb-3 flex items-center gap-1.5">
<span class="material-symbols-outlined text-base text-secondary">timeline</span>
          Riwayat Linimasa Seleksi (Pipeline Audit Trail)
        </h4>
<div id="modal-cand-timeline" class="space-y-2.5"></div>
</div>

<!-- Form Update Tahapan Seleksi -->
<form id="form-update-pipeline" class="bg-surface-container-low border-2 border-secondary/30 rounded-xl p-4 space-y-4">
<div class="flex flex-wrap items-center justify-between gap-2 border-b border-outline-variant/60 pb-3">
<div>
<h4 class="text-headline-sm font-headline-sm font-bold text-on-surface flex items-center gap-1.5">
<span class="material-symbols-outlined text-secondary">alt_route</span>
              Perbarui Tahapan Seleksi Kandidat
            </h4>
<p class="text-body-sm text-on-surface-variant">Pilih tahap secara manual atau gunakan tombol aksi cepat di kanan.</p>
</div>
<div class="flex items-center gap-2">
<button id="btn-quick-advance" type="button" class="bg-emerald-700 hover:bg-emerald-800 text-white text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 font-semibold transition-colors">
<span class="material-symbols-outlined text-sm">fast_forward</span>
<span id="btn-quick-advance-label">Majukan Tahap</span>
</button>
<button id="btn-quick-reject" type="button" class="bg-error hover:bg-on-error-container text-white text-label-sm font-label-sm px-3 py-1.5 rounded-lg flex items-center gap-1 font-semibold transition-colors">
<span class="material-symbols-outlined text-sm">block</span>
<span>Tolak Kandidat</span>
</button>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div>
<label for="pipeline-stage-select" class="block text-label-sm font-label-sm text-on-surface mb-1">Tahap Seleksi Tujuan</label>
<select id="pipeline-stage-select" class="w-full rounded-lg border-outline-variant bg-surface-container-lowest text-body-md font-medium focus:border-secondary focus:ring-secondary">
<option value="screening">1. Screening Awal</option>
<option value="assessment">2. Uji Teknis (Assessment)</option>
<option value="interview">3. Wawancara (Interview)</option>
<option value="offering">4. Penawaran Kerja (Offering)</option>
<option value="rejected">5. Ditolak (Rejected)</option>
</select>
</div>
<div>
<label for="pipeline-status-select" class="block text-label-sm font-label-sm text-on-surface mb-1">Status Tahap</label>
<select id="pipeline-status-select" class="w-full rounded-lg border-outline-variant bg-surface-container-lowest text-body-md font-medium focus:border-secondary focus:ring-secondary">
<option value="in_progress">Sedang Berjalan (in_progress)</option>
<option value="passed">Lolos Tahap Ini (passed)</option>
<option value="rejected">Tidak Lolos / Ditolak (rejected)</option>
</select>
</div>
</div>

<!-- Sub-form khusus tahap Interview -->
<div id="interview-schedule-fields" class="hidden bg-surface-container-lowest border border-tertiary-fixed-dim rounded-xl p-3.5 space-y-3">
<div class="flex items-center gap-1.5 text-label-sm font-bold text-on-tertiary-container">
<span class="material-symbols-outlined text-base">event_available</span>
<span>Detail Jadwal Wawancara (Opsional / Direkomendasikan)</span>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
<div>
<label for="interview-datetime" class="block text-label-sm text-on-surface-variant mb-1">Waktu Wawancara</label>
<input id="interview-datetime" type="datetime-local" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
<div>
<label for="interview-type" class="block text-label-sm text-on-surface-variant mb-1">Tipe Pertemuan</label>
<select id="interview-type" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary">
<option value="online">Online (Video Conference)</option>
<option value="offline">Offline (Tatap Muka di Kantor/Dermaga)</option>
</select>
</div>
<div>
<label for="interview-url" class="block text-label-sm text-on-surface-variant mb-1">Tautan Meeting / Lokasi Ruangan</label>
<input id="interview-url" type="text" placeholder="https://meet.google.com/... atau Ruang Rapat Lt. 2" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
<div>
<label for="interview-interviewer" class="block text-label-sm text-on-surface-variant mb-1">Nama Pewawancara (Interviewer)</label>
<input id="interview-interviewer" type="text" placeholder="Contoh: Capt. Hendra (VP Engineering)" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
</div>
</div>

<div>
<label for="pipeline-notes" class="block text-label-sm font-label-sm text-on-surface mb-1">Catatan Evaluator / Recruiter</label>
<textarea id="pipeline-notes" rows="2" placeholder="Tulis umpan balik evaluasi teknis, alasan kelulusan/penolakan, atau instruksi tahap berikutnya..." class="w-full rounded-lg border-outline-variant bg-surface-container-lowest text-body-sm focus:border-secondary focus:ring-secondary"></textarea>
</div>

<div class="flex items-center justify-end gap-2 pt-2">
<button id="btn-cancel-candidate-modal" type="button" class="px-4 py-2 rounded-lg border border-outline-variant text-label-sm font-semibold text-on-surface-variant hover:bg-surface-container transition-colors">
            Tutup
          </button>
<button id="btn-submit-pipeline" type="submit" class="bg-secondary hover:bg-on-secondary-container text-white text-label-md font-semibold px-5 py-2 rounded-lg shadow-sm flex items-center gap-1.5 transition-colors">
<span class="material-symbols-outlined text-base">save</span>
<span>Simpan Perubahan Tahap</span>
</button>
</div>
</form>
</div>
</div>
</div>

<!-- ===================================================================== -->
<!-- MODAL 2: BUAT / EDIT LOWONGAN KERJA                                   -->
<!-- ===================================================================== -->
<div id="job-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-xs p-4 overflow-y-auto">
<div class="bg-surface-container-lowest border border-outline-variant rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col overflow-hidden my-auto">
<!-- Modal Header -->
<div class="px-6 py-4 bg-primary-container text-white flex items-center justify-between">
<div class="flex items-center gap-2.5">
<span class="material-symbols-outlined text-secondary-container">work_history</span>
<h3 id="job-modal-title" class="text-headline-sm font-headline-sm font-bold">Pasang Lowongan Pekerjaan Baru</h3>
</div>
<button id="btn-close-job-modal" class="p-1.5 rounded-lg text-inverse-primary hover:text-white hover:bg-white/10 transition-colors" type="button">
<span class="material-symbols-outlined">close</span>
</button>
</div>

<!-- Modal Form -->
<form id="form-job" class="p-6 overflow-y-auto space-y-4 flex-1">
<input type="hidden" id="job-id-input" value=""/>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div>
<label for="job-title" class="block text-label-sm font-semibold text-on-surface mb-1">Judul Posisi Lowongan *</label>
<input id="job-title" type="text" required placeholder="Contoh: Senior Backend Engineer (Laravel)" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
<div>
<label for="job-company" class="block text-label-sm font-semibold text-on-surface mb-1">Nama Perusahaan / Instansi *</label>
<input id="job-company" type="text" required placeholder="Contoh: PT Solusi Bahari Digital" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div>
<label for="job-location" class="block text-label-sm font-semibold text-on-surface mb-1">Lokasi Penempatan *</label>
<input id="job-location" type="text" required placeholder="Contoh: Tegal / Hybrid" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
<div>
<label for="job-employment-type" class="block text-label-sm font-semibold text-on-surface mb-1">Tipe Ikatan Kerja</label>
<select id="job-employment-type" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary">
<option value="full-time">Full-time</option>
<option value="part-time">Part-time</option>
<option value="contract">Kontrak</option>
<option value="internship">Magang</option>
<option value="remote">Remote</option>
</select>
</div>
<div>
<label for="job-quota" class="block text-label-sm font-semibold text-on-surface mb-1">Kuota Penerimaan</label>
<input id="job-quota" type="number" min="1" value="1" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
<div>
<label for="job-salary-min" class="block text-label-sm font-semibold text-on-surface mb-1">Gaji Minimal (Rp)</label>
<input id="job-salary-min" type="number" min="0" step="100000" placeholder="7000000" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
<div>
<label for="job-salary-max" class="block text-label-sm font-semibold text-on-surface mb-1">Gaji Maksimal (Rp)</label>
<input id="job-salary-max" type="number" min="0" step="100000" placeholder="12000000" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
<div>
<label for="job-expires-at" class="block text-label-sm font-semibold text-on-surface mb-1">Batas Akhir Pendaftaran</label>
<input id="job-expires-at" type="date" class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"/>
</div>
</div>

<div>
<label for="job-description" class="block text-label-sm font-semibold text-on-surface mb-1">Deskripsi Pekerjaan &amp; Tanggung Jawab *</label>
<textarea id="job-description" rows="3" required placeholder="Jelaskan rincian tugas, ruang lingkup proyek, dan kualifikasi umum..." class="w-full rounded-lg border-outline-variant text-body-sm focus:border-secondary focus:ring-secondary"></textarea>
</div>

<!-- Daftar Requirements Dinamis -->
<div class="bg-surface-container-low border border-outline-variant/70 rounded-xl p-4 space-y-3">
<div class="flex items-center justify-between">
<div>
<span class="text-label-md font-bold text-on-surface block">Syarat Keahlian &amp; Bobot Matchmaking *</span>
<span class="text-body-sm text-outline">Wajib (bobot 3), Penting (bobot 2), Nilai Tambah (bobot 1)</span>
</div>
<button id="btn-add-requirement" type="button" class="bg-surface-container-lowest hover:bg-surface-container border border-outline-variant text-secondary text-label-sm font-semibold px-3 py-1.5 rounded-lg flex items-center gap-1 transition-colors">
<span class="material-symbols-outlined text-base">add</span>
<span>Tambah Skill</span>
</button>
</div>
<div id="requirements-rows" class="space-y-2"></div>
</div>

<div class="flex items-center justify-between pt-3 border-t border-outline-variant/60">
<div>
<button id="btn-close-vacancy" type="button" class="hidden bg-error-container hover:bg-error hover:text-white text-on-error-container text-label-sm font-semibold px-3.5 py-2 rounded-lg items-center gap-1 transition-colors">
<span class="material-symbols-outlined text-base">lock</span>
<span>Tutup Lowongan Ini</span>
</button>
</div>
<div class="flex items-center gap-2">
<button id="btn-cancel-job-modal" type="button" class="px-4 py-2 rounded-lg border border-outline-variant text-label-sm font-semibold text-on-surface-variant hover:bg-surface-container transition-colors">
            Batal
          </button>
<button id="btn-submit-job" type="submit" class="bg-secondary hover:bg-on-secondary-container text-white text-label-md font-semibold px-5 py-2 rounded-lg shadow-sm flex items-center gap-1.5 transition-colors">
<span class="material-symbols-outlined text-base">publish</span>
<span id="btn-submit-job-label">Terbitkan Lowongan</span>
</button>
</div>
</div>
</form>
</div>
</div>

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
          © 2026 JobHarbour - Kelompok 4 Universitas Harkat Negeri Tegal. Pelabuhan Karir &amp; Talenta Maritim Indonesia.
        </span>
</div>
<!-- Footer Policy Links -->
<div class="flex items-center gap-space-lg flex-wrap justify-center text-label-sm font-label-sm">
<a class="text-on-surface-variant dark:text-surface-variant hover:text-secondary transition-colors" href="/catalog">
          The Anchorage (Katalog)
        </a>
<a class="text-on-surface-variant dark:text-surface-variant hover:text-secondary transition-colors" href="/my-dock">
          My Dock
        </a>
<a class="text-on-surface-variant dark:text-surface-variant hover:text-secondary transition-colors" href="/profile">
          Captain's Ledger
        </a>
</div>
</div>
</footer>

<script src="/js/jh-api.js"></script>
<script src="/js/pages/recruiter.js"></script>
</body></html>
