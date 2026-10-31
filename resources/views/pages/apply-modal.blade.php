<!DOCTYPE html>

<html class="light" lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Embarkation Dock - JobHarbour</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "on-primary-fixed-variant": "#3f465c",
            "outline-variant": "#c6c6cd",
            "secondary-container": "#5bb8fe",
            "surface-container-highest": "#e0e3e5",
            "on-primary": "#ffffff",
            "on-tertiary-fixed": "#2a1700",
            "surface-bright": "#f7f9fb",
            "inverse-primary": "#bec6e0",
            "inverse-on-surface": "#eff1f3",
            "background": "#f7f9fb",
            "tertiary-container": "#2a1700",
            "error": "#ba1a1a",
            "on-surface": "#191c1e",
            "tertiary": "#000000",
            "on-surface-variant": "#45464d",
            "on-secondary-fixed": "#001d31",
            "on-secondary": "#ffffff",
            "outline": "#76777d",
            "primary-fixed": "#dae2fd",
            "on-error-container": "#93000a",
            "primary-fixed-dim": "#bec6e0",
            "secondary-fixed": "#cce5ff",
            "surface-dim": "#d8dadc",
            "surface": "#f7f9fb",
            "primary-container": "#131b2e",
            "tertiary-fixed-dim": "#ffb95f",
            "surface-tint": "#565e74",
            "secondary-fixed-dim": "#93ccff",
            "on-tertiary-fixed-variant": "#653e00",
            "on-primary-fixed": "#131b2e",
            "on-background": "#191c1e",
            "error-container": "#ffdad6",
            "surface-container-high": "#e6e8ea",
            "primary": "#000000",
            "on-error": "#ffffff",
            "on-tertiary": "#ffffff",
            "on-primary-container": "#7c839b",
            "surface-container": "#eceef0",
            "surface-variant": "#e0e3e5",
            "secondary": "#006398",
            "on-secondary-fixed-variant": "#004b73",
            "surface-container-low": "#f2f4f6",
            "tertiary-fixed": "#ffddb8",
            "surface-container-lowest": "#ffffff",
            "inverse-surface": "#2d3133",
            "on-secondary-container": "#00476e",
            "on-tertiary-container": "#b87500"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "spacing": {
            "margin-tablet": "1.5rem",
            "gutter": "1rem",
            "space-xs": "0.375rem",
            "space-lg": "1rem",
            "space-sm": "0.5rem",
            "space-xl": "1.5rem",
            "gutter-desktop": "1.5rem",
            "space-2xl": "2rem",
            "space-md": "0.75rem",
            "space-2xs": "0.25rem",
            "margin-desktop": "2.5rem",
            "margin": "1rem",
            "space-3xl": "3rem"
          },
          "fontFamily": {
            "headline-lg-mobile": ["Plus Jakarta Sans"],
            "display-lg": ["Plus Jakarta Sans"],
            "body-lg": ["Inter"],
            "body-sm": ["Inter"],
            "display-lg-mobile": ["Plus Jakarta Sans"],
            "label-sm": ["Inter"],
            "headline-lg": ["Plus Jakarta Sans"],
            "headline-sm": ["Plus Jakarta Sans"],
            "headline-md": ["Plus Jakarta Sans"],
            "label-md": ["Inter"],
            "coordinate-tag": ["Inter"],
            "body-md": ["Inter"]
          },
          "fontSize": {
            "headline-lg-mobile": ["1.5rem", { "lineHeight": "1.3", "letterSpacing": "-0.015em", "fontWeight": "600" }],
            "display-lg": ["3rem", { "lineHeight": "1.15", "letterSpacing": "-0.025em", "fontWeight": "700" }],
            "body-lg": ["1.0625rem", { "lineHeight": "1.55", "letterSpacing": "0em", "fontWeight": "400" }],
            "body-sm": ["0.8125rem", { "lineHeight": "1.45", "letterSpacing": "0.005em", "fontWeight": "400" }],
            "display-lg-mobile": ["2.25rem", { "lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700" }],
            "label-sm": ["0.75rem", { "lineHeight": "1.2", "letterSpacing": "0.025em", "fontWeight": "600" }],
            "headline-lg": ["2rem", { "lineHeight": "1.25", "letterSpacing": "-0.02em", "fontWeight": "700" }],
            "headline-sm": ["1.125rem", { "lineHeight": "1.4", "letterSpacing": "-0.005em", "fontWeight": "600" }],
            "headline-md": ["1.375rem", { "lineHeight": "1.35", "letterSpacing": "-0.01em", "fontWeight": "600" }],
            "label-md": ["0.875rem", { "lineHeight": "1.25", "letterSpacing": "0.01em", "fontWeight": "600" }],
            "coordinate-tag": ["0.6875rem", { "lineHeight": "1.1", "letterSpacing": "0.08em", "fontWeight": "700" }],
            "body-md": ["0.9375rem", { "lineHeight": "1.5", "letterSpacing": "0em", "fontWeight": "400" }]
          }
        }
      }
    }
  </script>
<style>
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
      display: inline-block;
      vertical-align: middle;
      line-height: 1;
    }
    .custom-scrollbar::-webkit-scrollbar {
      width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
      background: #f1f5f9;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 4px;
    }
  </style>
</head>
<body class="bg-surface text-on-surface antialiased font-body-md text-body-md min-h-screen relative overflow-hidden">
<!-- ==================== BACKGROUND: THE ANCHORAGE JOB PORTAL (BLURRED REALISTIC CATALOG CANVAS) ==================== -->
<div class="fixed inset-0 w-full h-full pointer-events-none filter blur-sm scale-[1.01] opacity-75 z-0 flex flex-col">
<!-- TopNavBar (Shared Component) -->
@include('partials.navbar', ['active' => 'catalog'])
<!-- Simulated Split View Page Content behind blur -->
<main class="flex-1 max-w-[1600px] w-full mx-auto px-margin-desktop py-space-xl flex gap-gutter-desktop overflow-hidden">
<!-- 40% Column Job Feed -->
<div class="w-2/5 space-y-space-md">
<div class="h-10 bg-surface-container-high rounded-lg w-3/4"></div>
<div class="p-space-lg bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-space-sm shadow-sm">
<div class="flex justify-between items-center">
<span class="text-coordinate-tag font-coordinate-tag text-secondary bg-secondary-fixed/50 px-2 py-0.5 rounded">ID: ID-TGL-882</span>
<span class="text-label-sm font-label-sm text-secondary font-semibold">Matchmaking Engine</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface">Backend Engineer</h3>
<p class="text-body-sm text-on-surface-variant">PT Solusi Bahari Digital • Pelabuhan Tegal</p>
<div class="flex gap-2 pt-2">
<span class="px-2 py-1 bg-surface-container text-xs rounded">Laravel</span>
<span class="px-2 py-1 bg-surface-container text-xs rounded">Docker</span>
<span class="px-2 py-1 bg-surface-container text-xs rounded">Microservices</span>
</div>
</div>
<div class="p-space-lg bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-space-sm">
<div class="flex justify-between items-center">
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">ID: ID-TGL-104</span>
<span class="text-label-sm font-label-sm text-on-surface-variant font-medium">Weighted Jaccard</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface">Fleet Telemetry Specialist</h3>
<p class="text-body-sm text-on-surface-variant">Samudera Lines Tech • Pelabuhan Tegal</p>
</div>
</div>
<!-- 60% Column Persistent Detail Preview -->
<div class="w-3/5 bg-surface-container-lowest border border-outline-variant/40 rounded-xl p-space-2xl space-y-space-lg">
<div class="h-8 bg-surface-container-high rounded w-1/2"></div>
<div class="h-4 bg-surface-container rounded w-1/3"></div>
<div class="h-32 bg-surface-container-low rounded-lg"></div>
<div class="h-24 bg-surface-container-low rounded-lg"></div>
</div>
</main>
</div>
<!-- ==================== DIMMED BACKDROP OVERLAY ==================== -->
<div class="fixed inset-0 bg-[#0F172A]/65 backdrop-blur-md z-40 transition-opacity"></div>
<!-- ==================== CENTERED MODAL DIALOG: EMBARKATION DOCK ==================== -->
<div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 overflow-y-auto">
<div class="relative w-full max-w-[840px] bg-white rounded-2xl shadow-2xl border border-slate-700/30 overflow-hidden flex flex-col my-auto animate-in fade-in zoom-in-95 duration-200">
<!-- 1. Modal Masthead -->
<div class="bg-[#0F172A] px-space-xl py-5 text-white flex items-center justify-between border-b border-slate-800 gap-4">
<div class="flex items-center gap-3.5 min-w-0">
<div class="w-10 h-10 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-[#38BDF8] shadow-inner flex-shrink-0">
<span class="material-symbols-outlined text-2xl" data-icon="explore" style="font-variation-settings: 'FILL' 1;">explore</span>
</div>
<div class="min-w-0">
<div class="flex flex-wrap items-center gap-2">
<span class="text-coordinate-tag font-coordinate-tag tracking-wider text-[#38BDF8] bg-sky-950/70 border border-sky-800/60 px-2 py-0.5 rounded text-[10px]" id="apply-job-badge">BERTH 04 • EXPRESS ENTRY</span>
<span class="text-xs text-slate-400 font-mono" id="apply-job-code">REG#----</span>
<span class="text-xs text-emerald-400 font-mono" id="apply-job-salary">-</span>
</div>
<h2 class="font-headline-sm text-headline-sm text-white font-bold leading-tight mt-0.5">
              Embarkation Dock <span class="text-slate-400 font-normal text-sm sm:text-base">(Formulir Pengajuan Lamaran)</span>
</h2>
<p class="text-body-sm font-body-sm text-slate-300 flex flex-wrap items-center gap-1.5 mt-0.5">
<span class="material-symbols-outlined text-[16px] text-sky-400" data-icon="business_center">business_center</span>
<span>Posisi: <strong id="apply-job-title">Memuat lowongan...</strong> - <span id="apply-job-company">-</span></span>
<span class="text-slate-500">•</span>
<span class="flex items-center gap-0.5 text-slate-300">
<span class="material-symbols-outlined text-[14px] text-emerald-400" data-icon="location_on">location_on</span>
<span id="apply-job-location">-</span>
</span>
</p>
</div>
</div>
<div class="flex items-center gap-2 flex-shrink-0">
<a class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-800/70 hover:bg-slate-700 text-slate-200 text-xs font-medium border border-slate-700/60 transition-colors" href="/catalog">
<span class="material-symbols-outlined text-sm">travel_explore</span>
<span>Katalog</span>
</a>
<button aria-label="Tutup Dialog" class="w-9 h-9 rounded-lg bg-slate-800/60 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-all border border-slate-700/40 focus:outline-none focus:ring-2 focus:ring-sky-500" id="btn-close-modal" type="button">
<span class="material-symbols-outlined text-xl" data-icon="close">close</span>
</button>
</div>
</div>

<!-- Pilihan Lowongan Cepat (muncul bila ada daftar lowongan atau ketika datang tanpa ?job=) -->
<div class="bg-slate-900/95 px-space-xl py-2.5 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs text-slate-300" id="apply-job-switcher-bar">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-sky-400 text-base">swap_horiz</span>
<span>Target Lowongan Pelayaran:</span>
</div>
<div class="flex items-center gap-2 w-full sm:w-auto">
<select class="w-full sm:w-80 bg-slate-800 text-white border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs focus:border-sky-400 focus:ring-1 focus:ring-sky-400 outline-none" id="apply-job-select">
<option value="">Memuat daftar lowongan...</option>
</select>
<a class="text-sky-400 hover:text-sky-300 whitespace-nowrap font-medium underline" href="/catalog">Lihat Semua</a>
</div>
</div>

<!-- Scrollable Modal Body -->
<div class="p-space-xl sm:p-7 space-y-5 overflow-y-auto max-h-[calc(88vh-175px)] custom-scrollbar bg-surface-bright" id="apply-modal-body">

<!-- State Loading Awal -->
<div class="py-12 text-center space-y-3" id="apply-loading-state">
<span class="material-symbols-outlined text-4xl text-secondary animate-spin">progress_activity</span>
<p class="text-body-md font-medium text-slate-700">Menyiapkan dermaga keberangkatan dan memuat detail lowongan...</p>
</div>

<!-- Banner Peringatan / Error Khusus (409 Sudah Melamar, 400 Ditutup, 422 Validasi) -->
<div class="hidden rounded-xl p-4 border shadow-xs" id="apply-status-alert"></div>

<!-- ==================== FORMULIR LAMARAN UTAMA ==================== -->
<form class="hidden space-y-5" id="apply-form" novalidate>
<!-- 2. Compatibility Match Score Banner (Telemetry Live Client) -->
<div class="bg-gradient-to-r from-sky-50/90 via-white to-slate-50 border border-sky-200/80 rounded-xl p-space-md shadow-sm" id="telemetry-card">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-sky-100">
<div class="flex items-center gap-3">
<div class="relative flex items-center justify-center w-14 h-14 flex-shrink-0">
<div class="w-14 h-14 rounded-full bg-sky-100 border-2 border-[#0284C7] flex flex-col items-center justify-center text-center shadow-sm transition-colors" id="live-score-circle">
<span class="font-headline-sm text-base font-extrabold text-[#0284C7] leading-none" id="live-score-value">0%</span>
<span class="text-[9px] font-coordinate-tag text-slate-500 uppercase mt-0.5">Jaccard</span>
</div>
</div>
<div>
<div class="flex flex-wrap items-center gap-2">
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300" id="live-category-badge">
<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse" id="live-category-dot"></span>
<span id="live-category-text">Menghitung Kecocokan...</span>
</span>
<span class="text-coordinate-tag font-coordinate-tag text-slate-400" id="live-telemetry-tag">LIVE TELEMETRY</span>
</div>
<p class="text-body-sm font-body-sm text-slate-600 mt-1" id="live-score-desc">
                  Kecocokan profil talenta dihitung secara langsung dengan rumus Weighted Jaccard Similarity.
                </p>
</div>
</div>
<div class="text-left sm:text-right flex-shrink-0">
<span class="text-[11px] font-mono text-slate-500 block" id="live-weight-info">Bobot: 0 / 0 poin</span>
<span class="text-[11px] text-slate-400 block">Wajib=3 • Penting=2 • Plus=1</span>
</div>
</div>

<!-- Ringkasan Matched vs Missing Skills -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 pt-3">
<div class="flex items-start gap-2 bg-emerald-50/90 border border-emerald-200 text-emerald-900 rounded-lg px-3 py-2.5">
<span class="material-symbols-outlined text-emerald-600 text-lg flex-shrink-0 mt-0.5" data-icon="check_circle" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div class="text-body-sm font-body-sm min-w-0">
<span class="font-semibold text-emerald-950 block" id="live-matched-summary">0 Keahlian Cocok</span>
<span class="text-emerald-700 text-xs block break-words" id="live-matched-list">-</span>
</div>
</div>
<div class="flex items-start gap-2 bg-amber-50/90 border border-amber-200 text-amber-950 rounded-lg px-3 py-2.5" id="live-missing-box">
<span class="material-symbols-outlined text-amber-600 text-lg flex-shrink-0 mt-0.5" data-icon="warning" id="live-missing-icon" style="font-variation-settings: 'FILL' 1;">warning</span>
<div class="text-body-sm font-body-sm min-w-0">
<span class="font-semibold text-slate-900 block" id="live-missing-summary">0 Keahlian Belum Terpenuhi</span>
<span class="text-slate-600 text-xs block break-words" id="live-missing-list">-</span>
</div>
</div>
</div>

<!-- Rincian Kriteria Keahlian Lowongan (Requirements + Bobot) -->
<div class="mt-3 pt-3 border-t border-sky-100">
<div class="flex items-center justify-between mb-2">
<span class="text-xs font-semibold text-slate-700 flex items-center gap-1">
<span class="material-symbols-outlined text-sm text-[#0284C7]">checklist</span>
                  Kebutuhan Keahlian Lowongan (Klik skill yang kurang untuk menambahkan):
                </span>
</div>
<div class="flex flex-wrap gap-1.5" id="job-requirements-list"></div>
</div>
</div>

<!-- 2B. Keahlian Pelamar untuk Lamaran Ini (Chip Input Live) -->
<div class="border border-slate-200 rounded-xl p-space-md bg-white space-y-3">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
<div>
<label class="text-label-md font-label-md font-semibold text-[#0F172A] flex items-center gap-1.5" for="skill-input">
<span class="material-symbols-outlined text-[#0284C7] text-[18px]">psychology</span>
                  Keahlian Pelamar yang Disertakan
                </label>
<p class="text-xs text-slate-500 mt-0.5">
                  Otomatis dimuat dari profil kamu. Tambah atau hapus keahlian di bawah untuk melihat simulasi skor kecocokan secara langsung.
                </p>
</div>
<button class="text-xs font-semibold text-secondary hover:underline self-start sm:self-center whitespace-nowrap" id="btn-reset-skills" type="button">
                Reset sesuai Profil
              </button>
</div>

<!-- Daftar Chip Skill Pelamar -->
<div class="flex flex-wrap gap-1.5 min-h-[36px] p-2.5 bg-slate-50 border border-slate-200/80 rounded-lg" id="candidate-skills-chips"></div>

<!-- Input Tambah Skill -->
<div class="flex gap-2">
<input autocomplete="off" class="flex-1 bg-white border border-outline-variant rounded-lg px-3 py-2 text-body-sm text-on-surface focus:border-[#0284C7] focus:ring-2 focus:ring-[#0284C7]/20 outline-none" id="skill-input" placeholder="Ketik keahlian (misal: Laravel, Docker, PostgreSQL) lalu tekan Enter..." type="text"/>
<button class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold transition-colors flex items-center gap-1 whitespace-nowrap" id="btn-add-skill" type="button">
<span class="material-symbols-outlined text-base">add</span>
<span>Tambah Skill</span>
</button>
</div>
</div>

<!-- 3. Drag-and-Drop CV / Resume Upload Box -->
<div class="space-y-2">
<div class="flex items-center justify-between">
<label class="text-label-md font-label-md font-semibold text-[#0F172A] flex items-center gap-1.5" for="cv-file-input">
<span class="material-symbols-outlined text-[#0284C7] text-[18px]" data-icon="badge">badge</span>
              Berkas CV / Manifes Maritim Terkini (PDF)
            </label>
<span class="text-coordinate-tag font-coordinate-tag text-slate-500 uppercase">FORMAT PDF • MAKS 2 MB</span>
</div>
<input accept=".pdf,application/pdf" class="hidden" id="cv-file-input" type="file"/>
<!-- Active Drag-Drop Zone with Loaded File Preview -->
<div class="bg-sky-50/50 border-2 border-dashed border-sky-300 rounded-xl p-4 sm:p-5 hover:bg-sky-50/80 transition-all flex flex-col gap-3.5 cursor-pointer" id="cv-dropzone">
<div class="flex flex-col sm:flex-row items-center sm:items-start justify-between gap-3">
<div class="flex items-center gap-3">
<div class="w-12 h-12 rounded-xl bg-white border border-sky-200 flex items-center justify-center text-[#0284C7] shadow-sm flex-shrink-0">
<span class="material-symbols-outlined text-3xl" data-icon="cloud_upload">cloud_upload</span>
</div>
<div>
<p class="font-body-md text-body-md font-medium text-slate-800">
                    Tarik berkas CV (PDF) kamu ke sini atau telusuri dari perangkat
                  </p>
<p class="text-body-sm font-body-sm text-slate-500">
                    Mendukung berkas PDF hingga maksimal 2 MB (Opsional namun sangat disarankan)
                  </p>
</div>
</div>
<button class="text-label-md font-label-md font-semibold text-[#0284C7] hover:text-[#0369a1] bg-white border border-sky-200 px-3.5 py-1.5 rounded-lg shadow-sm hover:border-sky-300 transition-colors whitespace-nowrap" id="btn-pick-cv" type="button">
                Pilih Berkas PDF
              </button>
</div>
<!-- File Component when Selected -->
<div class="hidden bg-white border border-emerald-200 rounded-lg p-3 flex items-center justify-between shadow-xs" id="cv-file-preview">
<div class="flex items-center gap-3 min-w-0">
<div class="w-9 h-9 rounded-md bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 flex-shrink-0">
<span class="material-symbols-outlined text-xl" data-icon="picture_as_pdf">picture_as_pdf</span>
</div>
<div class="truncate">
<div class="flex items-center gap-1.5">
<span class="font-body-md text-body-md font-semibold text-slate-900 truncate" id="cv-file-name">CV.pdf</span>
<span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
<span class="material-symbols-outlined text-[12px] text-emerald-700" data-icon="check" style="font-variation-settings: 'FILL' 1;">check</span>
                      Siap Diunggah
                    </span>
</div>
<span class="text-label-sm font-label-sm text-slate-500 block" id="cv-file-meta">0 KB • PDF</span>
</div>
</div>
<div class="flex items-center gap-2 flex-shrink-0">
<button class="text-label-sm font-label-sm font-semibold text-secondary hover:underline px-2 py-1" id="btn-change-cv" type="button">
                  Ganti Berkas
                </button>
<button aria-label="Hapus Berkas" class="p-1 text-slate-400 hover:text-rose-600 rounded" id="btn-remove-cv" type="button">
<span class="material-symbols-outlined text-lg" data-icon="delete">delete</span>
</button>
</div>
</div>
</div>
<p class="hidden text-xs text-rose-600 font-medium" id="cv-file-error"></p>
</div>

<!-- 4. Cover Letter & Voyage Notes -->
<div class="space-y-1.5">
<div class="flex items-center justify-between">
<label class="text-label-md font-label-md font-semibold text-[#0F172A] flex items-center gap-1.5" for="cover-letter">
<span class="material-symbols-outlined text-[#0284C7] text-[18px]" data-icon="edit_note">edit_note</span>
              Catatan Pengantar / Cover Letter (Opsional)
            </label>
<span class="text-label-sm font-label-sm text-slate-500 font-mono" id="cover-letter-count">0 / 1000</span>
</div>
<div class="relative">
<textarea class="w-full bg-white border border-outline-variant rounded-lg p-3 text-body-md font-body-md text-on-surface focus:border-[#0284C7] focus:ring-2 focus:ring-[#0284C7]/20 outline-none transition-all resize-none shadow-2xs placeholder:text-slate-400" id="cover-letter" maxlength="1000" placeholder="Tuliskan pengalaman relevan dan motivasi kamu melamar posisi ini..." rows="3"></textarea>
</div>
</div>

<!-- 5. Applicant Contact & Profile Quick Verification -->
<div class="border border-slate-200 rounded-xl p-space-md bg-white">
<div class="flex items-center justify-between mb-2.5">
<h3 class="text-label-md font-label-md font-bold text-[#0F172A] flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-lg" data-icon="how_to_reg" style="font-variation-settings: 'FILL' 1;">how_to_reg</span>
<span>Verifikasi Data Pelamar (<span id="applicant-verify-name">-</span>)</span>
</h3>
<span class="text-coordinate-tag font-coordinate-tag text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200" id="applicant-verify-id">
              SEAFARER ID #---
            </span>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-2.5">
<div class="bg-slate-50/90 border border-slate-200 rounded-lg p-2.5 flex items-center gap-2.5">
<span class="material-symbols-outlined text-slate-500 text-lg" data-icon="mail">mail</span>
<div class="truncate">
<span class="text-[10px] text-slate-400 font-coordinate-tag block uppercase">Surat Elektronik</span>
<span class="text-body-sm font-body-sm font-medium text-slate-800 truncate block" id="applicant-verify-email">-</span>
</div>
</div>
<div class="bg-slate-50/90 border border-slate-200 rounded-lg p-2.5 flex items-center gap-2.5">
<span class="material-symbols-outlined text-slate-500 text-lg" data-icon="call">call</span>
<div>
<span class="text-[10px] text-slate-400 font-coordinate-tag block uppercase">Kontak Radio / Tel</span>
<span class="text-body-sm font-body-sm font-medium text-slate-800 block" id="applicant-verify-phone">-</span>
</div>
</div>
<div class="bg-slate-50/90 border border-slate-200 rounded-lg p-2.5 flex items-center gap-2.5">
<span class="material-symbols-outlined text-slate-500 text-lg" data-icon="anchor">anchor</span>
<div>
<span class="text-[10px] text-slate-400 font-coordinate-tag block uppercase">Pangkalan / Domisili</span>
<span class="text-body-sm font-body-sm font-medium text-slate-800 block" id="applicant-verify-location">-</span>
</div>
</div>
</div>
<p class="text-label-sm font-label-sm text-slate-400 flex items-center justify-between gap-1 mt-2.5">
<span class="flex items-center gap-1 italic">
<span class="material-symbols-outlined text-[14px]" data-icon="info">info</span>
              Data diambil otomatis dari profil Captain's Ledger kamu.
            </span>
<a class="text-secondary font-semibold hover:underline not-italic" href="/profile">Perbarui Profil</a>
</p>
</div>
</form>

<!-- ==================== TAMPILAN SUKSES SETELAH SUBMIT ==================== -->
<div class="hidden space-y-5 py-2" id="apply-success-view">
<div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-6 text-center space-y-3">
<div class="w-16 h-16 rounded-full bg-emerald-600 text-white flex items-center justify-center mx-auto shadow-md">
<span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">sailing</span>
</div>
<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300" id="success-app-id-badge">
              LAMARAN TERKIRIM
            </span>
<h3 class="font-headline-md text-headline-md text-slate-900 font-bold" id="success-headline">
              Selamat Berlayar! Berkas Lamaran Berhasil Dikirim
            </h3>
<p class="text-body-sm text-slate-600 max-w-lg mx-auto" id="success-subtext">
              Mesin Pemadanan Keahlian Otomatis telah menghitung skor kompatibilitas kamu dan menginisialisasi linimasa rekrutmen.
            </p>
</div>

<!-- Ringkasan Skor Resmi Server & Status Pipeline -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="bg-white border border-slate-200 rounded-xl p-4 space-y-2 shadow-2xs">
<span class="text-coordinate-tag font-coordinate-tag text-slate-400 uppercase">SKOR RESMI SERVER (WEIGHTED JACCARD)</span>
<div class="flex items-baseline gap-3">
<span class="text-3xl font-extrabold text-[#0284C7]" id="success-score-value">0%</span>
<span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-800" id="success-score-category">-</span>
</div>
<p class="text-xs text-slate-500" id="success-weight-detail">Rincian bobot keahlian terverifikasi oleh server.</p>
</div>

<div class="bg-white border border-slate-200 rounded-xl p-4 space-y-2 shadow-2xs">
<span class="text-coordinate-tag font-coordinate-tag text-slate-400 uppercase">TAHAP PIPELINE AWAL</span>
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-emerald-600 text-2xl">timeline</span>
<div>
<span class="font-bold text-slate-900 block" id="success-pipeline-stage">Screening</span>
<span class="text-xs text-slate-500 block" id="success-pipeline-status">-</span>
</div>
</div>
</div>
</div>

<!-- Detail Matched & Missing dari Server -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-3">
<div class="bg-emerald-50/70 border border-emerald-200 rounded-xl p-3.5">
<span class="text-xs font-bold text-emerald-900 block mb-1">Keahlian Cocok (Matched Skills)</span>
<div class="flex flex-wrap gap-1.5" id="success-matched-skills"></div>
</div>
<div class="bg-amber-50/70 border border-amber-200 rounded-xl p-3.5">
<span class="text-xs font-bold text-amber-900 block mb-1">Keahlian Belum Terpenuhi (Missing Skills)</span>
<div class="flex flex-wrap gap-1.5" id="success-missing-skills"></div>
</div>
</div>

<!-- Tombol Aksi Setelah Sukses -->
<div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-2">
<button class="hidden w-full sm:w-auto px-4 py-2.5 rounded-lg border border-sky-300 bg-sky-50 text-[#0284C7] font-semibold text-xs hover:bg-sky-100 transition-colors flex items-center justify-center gap-1.5" id="btn-success-download-cv" type="button">
<span class="material-symbols-outlined text-base">picture_as_pdf</span>
<span>Lihat CV yang Diunggah</span>
</button>
<div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto sm:ml-auto">
<a class="w-full sm:w-auto px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-semibold text-label-md hover:bg-slate-100 transition-colors text-center" href="/catalog">
                Cari Lowongan Lain
              </a>
<a class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-[#0284C7] hover:bg-[#0369a1] text-white font-semibold text-label-md shadow-md transition-all flex items-center justify-center gap-2" href="/my-dock">
<span>Pantau Lamaran di My Dock</span>
<span class="material-symbols-outlined text-lg">arrow_forward</span>
</a>
</div>
</div>
</div>

</div>

<!-- 6. Modal Footer & Submission Action -->
<div class="border-t border-slate-200/80 bg-slate-50 px-6 sm:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4" id="apply-modal-footer">
<div class="flex items-start gap-2 max-w-sm text-left">
<span class="material-symbols-outlined text-slate-400 text-lg mt-0.5 flex-shrink-0" data-icon="shield">shield</span>
<p class="text-xs text-slate-500 leading-normal" id="apply-footer-note">
            Dengan menekan kirim, lamaranmu akan masuk ke tahap 1 (Screening) pada sistem rekrutmen perusahaan.
          </p>
</div>
<div class="flex items-center gap-3 w-full sm:w-auto justify-end">
<a class="w-full sm:w-auto px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-medium text-label-md font-label-md hover:bg-slate-100 hover:text-slate-900 transition-colors focus:outline-none text-center" href="/catalog" id="btn-cancel-apply">
            Batal / Kembali
          </a>
<button class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-[#0284C7] hover:bg-[#0369a1] text-white font-semibold text-label-md font-label-md shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2" id="btn-submit-apply" type="button">
<span>Kirim Berkas Lamaran (Berangkat)</span>
<span class="material-symbols-outlined text-lg" data-icon="sailing">sailing</span>
</button>
</div>
</div>
</div>
</div>
<script src="/js/jh-api.js"></script>
<script src="/js/pages/apply.js"></script>
</body></html>
