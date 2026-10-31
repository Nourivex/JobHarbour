<!DOCTYPE html>

<html class="light" lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Embarkation Dock - JobHarbour</title>
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus+Jakarta+Sans:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
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
<body class="bg-surface text-on-surface antialiased font-body-md text-body-md min-h-screen relative overflow-hidden select-none">
<!-- ==================== BACKGROUND: THE ANCHORAGE JOB PORTAL (BLURRED REALISTIC CATALOG CANVAS) ==================== -->
<div class="fixed inset-0 w-full h-full pointer-events-none filter blur-sm scale-[1.01] opacity-75 z-0 flex flex-col">
<!-- TopNavBar (Shared Component implementation) -->
<header class="bg-surface-container-lowest/90 dark:bg-inverse-surface/90 backdrop-blur-md shadow-sm dark:shadow-none border-b border-outline-variant/60 dark:border-outline/40 h-16 w-full px-margin-desktop max-w-[1600px] mx-auto flex items-center justify-between z-10">
<div class="flex items-center gap-space-xl">
<a class="text-headline-sm font-headline-sm font-bold tracking-tight text-on-surface dark:text-inverse-on-surface flex items-center gap-2" href="/">
<span class="material-symbols-outlined text-secondary text-2xl" data-icon="anchor" style="font-variation-settings: 'FILL' 1;">anchor</span>
<span>JobHarbour</span>
</a>
<nav class="hidden md:flex items-center gap-space-lg text-label-md font-label-md">
<span class="text-secondary dark:text-secondary-fixed border-b-2 border-secondary dark:border-secondary-fixed font-semibold pb-1">The Anchorage</span>
<span class="text-on-surface-variant dark:text-surface-variant hover:text-on-surface transition-colors pb-1">My Dock</span>
<span class="text-on-surface-variant dark:text-surface-variant hover:text-on-surface transition-colors pb-1">Captain's Ledger</span>
<span class="text-on-surface-variant dark:text-surface-variant hover:text-on-surface transition-colors pb-1">Harbour Master</span>
</nav>
</div>
<div class="flex items-center gap-space-md">
<button class="p-2 text-on-surface-variant rounded-lg hover:bg-surface-container-high transition-colors">
<span class="material-symbols-outlined" data-icon="notifications">notifications</span>
</button>
<div class="h-6 w-px bg-outline-variant/60"></div>
<div class="flex items-center gap-space-sm text-label-md font-label-md">
<div class="w-8 h-8 rounded-full bg-secondary text-white font-bold flex items-center justify-center text-xs">BS</div>
<span class="text-on-surface font-medium">Budi Santoso - Pelamar Aktif</span>
</div>
</div>
</header>
<!-- Simulated Split View Page Content behind blur -->
<main class="flex-1 max-w-[1600px] w-full mx-auto px-margin-desktop py-space-xl flex gap-gutter-desktop overflow-hidden">
<!-- 40% Column Job Feed -->
<div class="w-2/5 space-y-space-md">
<div class="h-10 bg-surface-container-high rounded-lg w-3/4"></div>
<div class="p-space-lg bg-surface-container-lowest border border-outline-variant/40 rounded-xl space-y-space-sm shadow-sm">
<div class="flex justify-between items-center">
<span class="text-coordinate-tag font-coordinate-tag text-secondary bg-secondary-fixed/50 px-2 py-0.5 rounded">ID: ID-TGL-882</span>
<span class="text-label-sm font-label-sm text-secondary font-semibold">92% Match</span>
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
<span class="text-coordinate-tag font-coordinate-tag text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">ID: ID-JKT-104</span>
<span class="text-label-sm font-label-sm text-on-surface-variant font-medium">78% Match</span>
</div>
<h3 class="font-headline-sm text-headline-sm text-on-surface">Fleet Telemetry Specialist</h3>
<p class="text-body-sm text-on-surface-variant">Samudera Lines Tech • Tanjung Priok</p>
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
<div class="relative w-full max-w-[820px] bg-white rounded-2xl shadow-2xl border border-slate-700/30 overflow-hidden flex flex-col my-auto animate-in fade-in zoom-in-95 duration-200">
<!-- 1. Modal Masthead -->
<div class="bg-[#0F172A] px-space-xl py-5 text-white flex items-center justify-between border-b border-slate-800">
<div class="flex items-center gap-3.5">
<div class="w-10 h-10 rounded-xl bg-slate-800/80 border border-slate-700/60 flex items-center justify-center text-[#38BDF8] shadow-inner">
<span class="material-symbols-outlined text-2xl" data-icon="explore" style="font-variation-settings: 'FILL' 1;">explore</span>
</div>
<div>
<div class="flex items-center gap-2">
<span class="text-coordinate-tag font-coordinate-tag tracking-wider text-[#38BDF8] bg-sky-950/70 border border-sky-800/60 px-2 py-0.5 rounded text-[10px]">BERTH 04 • EXPRESS ENTRY</span>
<span class="text-xs text-slate-400 font-mono">REG#7702-SBD</span>
</div>
<h2 class="font-headline-sm text-headline-sm text-white font-bold leading-tight mt-0.5">
              Embarkation Dock <span class="text-slate-400 font-normal text-sm sm:text-base">(Formulir Pengajuan Lamaran)</span>
</h2>
<p class="text-body-sm font-body-sm text-slate-300 flex items-center gap-1.5 mt-0.5">
<span class="material-symbols-outlined text-[16px] text-sky-400" data-icon="business_center">business_center</span>
<span>Posisi: <strong>Backend Engineer</strong> - PT Solusi Bahari Digital</span>
<span class="text-slate-500">•</span>
<span class="flex items-center gap-0.5 text-slate-300">
<span class="material-symbols-outlined text-[14px] text-emerald-400" data-icon="location_on">location_on</span>
                Dermaga Tegal
              </span>
</p>
</div>
</div>
<button aria-label="Tutup Dialog" class="w-9 h-9 rounded-lg bg-slate-800/60 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center transition-all border border-slate-700/40 focus:outline-none focus:ring-2 focus:ring-sky-500" type="button">
<span class="material-symbols-outlined text-xl" data-icon="close">close</span>
</button>
</div>
<!-- Scrollable Modal Body -->
<div class="p-space-xl sm:p-7 space-y-5 overflow-y-auto max-h-[calc(88vh-140px)] custom-scrollbar bg-surface-bright">
<!-- 2. Compatibility Match Score Banner (Telemetry) -->
<div class="bg-gradient-to-r from-sky-50/90 via-white to-slate-50 border border-sky-200/80 rounded-xl p-space-md shadow-sm">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-sky-100">
<div class="flex items-center gap-3">
<div class="relative flex items-center justify-center w-13 h-13">
<div class="w-12 h-12 rounded-full bg-sky-100 border-2 border-[#0284C7] flex flex-col items-center justify-center text-center shadow-sm">
<span class="font-headline-sm text-headline-sm font-extrabold text-[#0284C7] leading-none">92%</span>
<span class="text-[9px] font-coordinate-tag text-slate-500 uppercase">Jaccard</span>
</div>
</div>
<div>
<div class="flex items-center gap-2">
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300">
<span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    Anchor Fit - Rekomendasi Sangat Tinggi
                  </span>
<span class="text-coordinate-tag font-coordinate-tag text-slate-400">TELEMETRY PASS</span>
</div>
<p class="text-body-sm font-body-sm text-slate-600 mt-1">
                  Kecocokan profil kapal talenta <strong>Budi Santoso</strong> memenuhi ambang kelayakan standar operasional PT Solusi Bahari Digital.
                </p>
</div>
</div>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-3">
<div class="flex items-center gap-2 bg-emerald-50/90 border border-emerald-200 text-emerald-900 rounded-lg px-3 py-2">
<span class="material-symbols-outlined text-emerald-600 text-lg flex-shrink-0" data-icon="check_circle" style="font-variation-settings: 'FILL' 1;">check_circle</span>
<div class="text-body-sm font-body-sm">
<span class="font-semibold text-emerald-950">4 dari 4 Keahlian Wajib Cocok</span>
<span class="text-emerald-700 text-xs block">(PHP, Laravel, MySQL, REST API)</span>
</div>
</div>
<div class="flex items-center gap-2 bg-sky-50/90 border border-sky-200 text-sky-950 rounded-lg px-3 py-2">
<span class="material-symbols-outlined text-[#0284C7] text-lg flex-shrink-0" data-icon="verified" style="font-variation-settings: 'FILL' 1;">verified</span>
<div class="text-body-sm font-body-sm">
<span class="font-semibold text-slate-900">3 Keahlian Penting Cocok</span>
<span class="text-slate-600 text-xs block">(Docker, Redis, Git Versioning)</span>
</div>
</div>
</div>
</div>
<!-- 3. Drag-and-Drop CV / Resume Upload Box -->
<div class="space-y-2">
<div class="flex items-center justify-between">
<label class="text-label-md font-label-md font-semibold text-[#0F172A] flex items-center gap-1.5">
<span class="material-symbols-outlined text-[#0284C7] text-[18px]" data-icon="badge">badge</span>
              Berkas CV / Manifes Maritim Terkini
              <span class="text-error font-bold">*</span>
</label>
<span class="text-coordinate-tag font-coordinate-tag text-slate-500 uppercase">STANDAR IMO-CREW CV</span>
</div>
<!-- Active Drag-Drop Zone with Loaded File Preview -->
<div class="bg-sky-50/50 border-2 border-dashed border-sky-300 rounded-xl p-4 sm:p-5 hover:bg-sky-50/80 transition-all flex flex-col gap-3.5">
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
                    Mendukung berkas PDF hingga maksimal 2 MB
                  </p>
</div>
</div>
<button class="text-label-md font-label-md font-semibold text-[#0284C7] hover:text-[#0369a1] bg-white border border-sky-200 px-3.5 py-1.5 rounded-lg shadow-sm hover:border-sky-300 transition-colors whitespace-nowrap" type="button">
                Pilih Berkas
              </button>
</div>
<!-- Pre-attached / Active File Component -->
<div class="bg-white border border-emerald-200 rounded-lg p-3 flex items-center justify-between shadow-xs">
<div class="flex items-center gap-3 min-w-0">
<div class="w-9 h-9 rounded-md bg-rose-50 border border-rose-200 flex items-center justify-center text-rose-600 flex-shrink-0">
<span class="material-symbols-outlined text-xl" data-icon="picture_as_pdf">picture_as_pdf</span>
</div>
<div class="truncate">
<div class="flex items-center gap-1.5">
<span class="font-body-md text-body-md font-semibold text-slate-900 truncate">CV_Budi_Santoso_Backend_2026.pdf</span>
<span class="inline-flex items-center gap-0.5 px-1.5 py-0.2 rounded text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
<span class="material-symbols-outlined text-[12px] text-emerald-700" data-icon="check" style="font-variation-settings: 'FILL' 1;">check</span>
                      Terverifikasi
                    </span>
</div>
<span class="text-label-sm font-label-sm text-slate-500 block">1.4 MB • Diperbarui via Ledger Maritim</span>
</div>
</div>
<div class="flex items-center gap-2 flex-shrink-0">
<button class="text-label-sm font-label-sm font-semibold text-secondary hover:underline px-2 py-1" type="button">
                  Ganti Berkas
                </button>
<button aria-label="Hapus Berkas" class="p-1 text-slate-400 hover:text-rose-600 rounded" type="button">
<span class="material-symbols-outlined text-lg" data-icon="delete">delete</span>
</button>
</div>
</div>
</div>
</div>
<!-- 4. Cover Letter & Voyage Notes -->
<div class="space-y-1.5">
<div class="flex items-center justify-between">
<label class="text-label-md font-label-md font-semibold text-[#0F172A] flex items-center gap-1.5" for="cover-letter">
<span class="material-symbols-outlined text-[#0284C7] text-[18px]" data-icon="edit_note">edit_note</span>
              Catatan Pengantar / Cover Letter (Opsional)
            </label>
<span class="text-label-sm font-label-sm text-slate-500 font-mono">248 / 1000</span>
</div>
<div class="relative">
<textarea class="w-full bg-white border border-outline-variant rounded-lg p-3 text-body-md font-body-md text-on-surface focus:border-[#0284C7] focus:ring-2 focus:ring-[#0284C7]/20 outline-none transition-all resize-none shadow-2xs placeholder:text-slate-400" id="cover-letter" placeholder="Tuliskan pengalaman relevan Anda dalam operasional teknologi pelabuhan..." rows="3">Saya sangat antusias untuk berlabuh dan berkontribusi sebagai Backend Engineer di PT Solusi Bahari Digital. Dengan pengalaman lebih dari 4 tahun mengelola arsitektur microservices berbasis Laravel dan Docker untuk logistik pelabuhan, saya siap mengoptimalkan sinkronisasi data kapal dan armada darat.</textarea>
</div>
</div>
<!-- 5. Applicant Contact & Profile Quick Verification -->
<div class="border border-slate-200 rounded-xl p-space-md bg-white">
<div class="flex items-center justify-between mb-2.5">
<h3 class="text-label-md font-label-md font-bold text-[#0F172A] flex items-center gap-1.5">
<span class="material-symbols-outlined text-emerald-600 text-lg" data-icon="how_to_reg" style="font-variation-settings: 'FILL' 1;">how_to_reg</span>
              Verifikasi Data Pelamar (Budi Santoso)
            </h3>
<span class="text-coordinate-tag font-coordinate-tag text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
              SEAFARER ID #82910
            </span>
</div>
<div class="grid grid-cols-1 md:grid-cols-3 gap-2.5">
<div class="bg-slate-50/90 border border-slate-200 rounded-lg p-2.5 flex items-center gap-2.5">
<span class="material-symbols-outlined text-slate-500 text-lg" data-icon="mail">mail</span>
<div class="truncate">
<span class="text-[10px] text-slate-400 font-coordinate-tag block uppercase">Surat Elektronik</span>
<span class="text-body-sm font-body-sm font-medium text-slate-800 truncate block">budi@jobharbour.id</span>
</div>
</div>
<div class="bg-slate-50/90 border border-slate-200 rounded-lg p-2.5 flex items-center gap-2.5">
<span class="material-symbols-outlined text-slate-500 text-lg" data-icon="call">call</span>
<div>
<span class="text-[10px] text-slate-400 font-coordinate-tag block uppercase">Kontak Radio / Tel</span>
<span class="text-body-sm font-body-sm font-medium text-slate-800 block">+62 812-3456-7890</span>
</div>
</div>
<div class="bg-slate-50/90 border border-slate-200 rounded-lg p-2.5 flex items-center gap-2.5">
<span class="material-symbols-outlined text-slate-500 text-lg" data-icon="anchor">anchor</span>
<div>
<span class="text-[10px] text-slate-400 font-coordinate-tag block uppercase">Pangkalan / Domisili</span>
<span class="text-body-sm font-body-sm font-medium text-slate-800 block">Tegal, Jawa Tengah</span>
</div>
</div>
</div>
<p class="text-label-sm font-label-sm text-slate-400 flex items-center gap-1 mt-2.5 italic">
<span class="material-symbols-outlined text-[14px]" data-icon="info">info</span>
            Data diambil otomatis dari profil Captain's Ledger Anda.
          </p>
</div>
</div>
<!-- 6. Modal Footer & Submission Action -->
<div class="border-t border-slate-200/80 bg-slate-50 px-6 sm:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
<div class="flex items-start gap-2 max-w-sm text-left">
<span class="material-symbols-outlined text-slate-400 text-lg mt-0.5 flex-shrink-0" data-icon="shield">shield</span>
<p class="text-xs text-slate-500 leading-normal">
            Dengan menekan kirim, lamaranmu akan masuk ke tahap 1 (Screening) pada sistem rekrutmen PT Solusi Bahari Digital.
          </p>
</div>
<div class="flex items-center gap-3 w-full sm:w-auto justify-end">
<button class="w-full sm:w-auto px-5 py-2.5 rounded-lg border border-slate-300 text-slate-700 font-medium text-label-md font-label-md hover:bg-slate-100 hover:text-slate-900 transition-colors focus:outline-none" type="button">
            Batal / Kembali
          </button>
<button class="w-full sm:w-auto px-6 py-2.5 rounded-lg bg-[#0284C7] hover:bg-[#0369a1] text-white font-semibold text-label-md font-label-md shadow-md hover:shadow-lg transition-all flex items-center justify-center gap-2 active:scale-95 focus:outline-none focus:ring-2 focus:ring-[#0284C7] focus:ring-offset-2" type="button">
<span>Kirim Berkas Lamaran (Berangkat)</span>
<span class="material-symbols-outlined text-lg" data-icon="sailing">sailing</span>
</button>
</div>
</div>
</div>
</div>

<!-- LIVE APPLICATION INTEGRATION (Muhammad Yasir Ilham Nabil) -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const jobId = urlParams.get('job_id') || 1;
    
    const fileInput = document.getElementById('cvFileInput');
    const submitBtn = document.getElementById('submitApplyBtn');
    const coverLetter = document.getElementById('coverLetterInput');
    let selectedFile = null;

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                selectedFile = e.target.files[0];
                alert('Berkas CV terpilih: ' + selectedFile.name + ' (' + (selectedFile.size / 1024).toFixed(1) + ' KB)');
            }
        });
    }

    if (submitBtn) {
        submitBtn.addEventListener('click', async function() {
            const token = localStorage.getItem('jh_token');
            if (!token) {
                alert('Perhatian: Anda belum login! Silakan login terlebih dahulu untuk mendapatkan Bearer Token JWT.');
                window.location.href = '/profile';
                return;
            }

            if (!selectedFile) {
                alert('Silakan pilih berkas CV PDF Anda terlebih dahulu sebelum melamar!');
                return;
            }

            const formData = new FormData();
            formData.append('job_id', jobId);
            formData.append('cv_file', selectedFile);
            if (coverLetter && coverLetter.value.trim()) {
                formData.append('cover_letter', coverLetter.value.trim());
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="material-symbols-outlined animate-spin text-sm">sync</span> Memproses Lamaran...';

            try {
                const res = await fetch('/api/applications', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Authorization': 'Bearer ' + token
                    },
                    body: formData
                });
                const result = await res.json();

                if (res.status === 201 || result.status === 'success') {
                    alert('🎉 Selamat! Lamaran Anda berhasil dikirim ke dermaga! Skor Kecocokan Keahlian (Weighted Jaccard): ' + (result.data.match_score || 0) + '%');
                    window.location.href = '/my-dock';
                } else if (res.status === 409) {
                    alert('⚠️ Perhatian: Anda sudah pernah melamar lowongan pekerjaan ini sebelumnya (409 Conflict).');
                } else if (res.status === 422) {
                    const errs = result.errors ? Object.values(result.errors).flat().join('\n') : result.message;
                    alert('Validasi Gagal (422):\n' + errs);
                } else {
                    alert('Gagal mengirim lamaran: ' + (result.message || 'Error ' + res.status));
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan saat menghubungi API backend.');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<span class="material-symbols-outlined text-lg">sailing</span> Luncurkan Lamaran ke Dermaga';
            }
        });
    }
});
</script>

</body></html>