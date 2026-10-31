<!DOCTYPE html>

<html lang="id"><head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Captain's Ledger: Profil Talenta &amp; Kalibrasi Keahlian - JobHarbour</title>
<!-- Google Fonts: Plus Jakarta Sans & Inter -->
<link href="https://fonts.googleapis.com" rel="preconnect"/>
<link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Plus Jakarta Sans:wght@600;700;800&amp;display=swap" rel="stylesheet"/>
<!-- Material Symbols Outlined -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<!-- Tailwind CSS CDN -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- Tailwind Configuration -->
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          "colors": {
            "surface-container": "#eceef0",
            "on-primary-fixed-variant": "#3f465c",
            "on-surface": "#191c1e",
            "on-error-container": "#93000a",
            "surface-container-lowest": "#ffffff",
            "outline-variant": "#c6c6cd",
            "on-surface-variant": "#45464d",
            "surface-container-low": "#f2f4f6",
            "surface-bright": "#f7f9fb",
            "on-tertiary-container": "#b87500",
            "primary-fixed-dim": "#bec6e0",
            "inverse-on-surface": "#eff1f3",
            "on-tertiary-fixed": "#2a1700",
            "on-secondary": "#ffffff",
            "on-error": "#ffffff",
            "secondary": "#006398",
            "tertiary-fixed-dim": "#ffb95f",
            "surface-container-highest": "#e0e3e5",
            "primary": "#000000",
            "tertiary-fixed": "#ffddb8",
            "on-background": "#191c1e",
            "background": "#f7f9fb",
            "surface": "#f7f9fb",
            "on-secondary-container": "#00476e",
            "on-primary-fixed": "#131b2e",
            "outline": "#76777d",
            "error": "#ba1a1a",
            "inverse-surface": "#2d3133",
            "on-primary-container": "#7c839b",
            "on-secondary-fixed": "#001d31",
            "on-tertiary-fixed-variant": "#653e00",
            "on-tertiary": "#ffffff",
            "surface-variant": "#e0e3e5",
            "surface-tint": "#565e74",
            "secondary-fixed-dim": "#93ccff",
            "secondary-fixed": "#cce5ff",
            "on-primary": "#ffffff",
            "inverse-primary": "#bec6e0",
            "secondary-container": "#5bb8fe",
            "primary-fixed": "#dae2fd"
          },
          "borderRadius": {
            "DEFAULT": "0.25rem",
            "lg": "0.5rem",
            "xl": "0.75rem",
            "full": "9999px"
          },
          "spacing": {
            "margin-tablet": "1.5rem",
            "space-lg": "1rem",
            "gutter-desktop": "1.5rem",
            "margin": "1rem",
            "space-3xl": "3rem",
            "space-sm": "0.5rem",
            "space-2xs": "0.25rem",
            "gutter": "1rem",
            "space-2xl": "2rem",
            "space-md": "0.75rem",
            "space-xl": "1.5rem",
            "margin-desktop": "2.5rem",
            "space-xs": "0.375rem"
          },
          "fontFamily": {
            "display-lg": ["Plus Jakarta Sans"],
            "headline-sm": ["Plus Jakarta Sans"],
            "coordinate-tag": ["Inter"],
            "headline-lg-mobile": ["Plus Jakarta Sans"],
            "label-sm": ["Inter"],
            "display-lg-mobile": ["Plus Jakarta Sans"],
            "body-lg": ["Inter"],
            "body-md": ["Inter"],
            "label-md": ["Inter"],
            "body-sm": ["Inter"],
            "headline-md": ["Plus Jakarta Sans"],
            "headline-lg": ["Plus Jakarta Sans"]
          },
          "fontSize": {
            "display-lg": ["3rem", { "lineHeight": "1.15", "letterSpacing": "-0.025em", "fontWeight": "700" }],
            "headline-sm": ["1.125rem", { "lineHeight": "1.4", "letterSpacing": "-0.005em", "fontWeight": "600" }],
            "coordinate-tag": ["0.6875rem", { "lineHeight": "1.1", "letterSpacing": "0.08em", "fontWeight": "700" }],
            "headline-lg-mobile": ["1.5rem", { "lineHeight": "1.3", "letterSpacing": "-0.015em", "fontWeight": "600" }],
            "label-sm": ["0.75rem", { "lineHeight": "1.2", "letterSpacing": "0.025em", "fontWeight": "600" }],
            "display-lg-mobile": ["2.25rem", { "lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700" }],
            "body-lg": ["1.0625rem", { "lineHeight": "1.55", "letterSpacing": "0em", "fontWeight": "400" }],
            "body-md": ["0.9375rem", { "lineHeight": "1.5", "letterSpacing": "0em", "fontWeight": "400" }],
            "label-md": ["0.875rem", { "lineHeight": "1.25", "letterSpacing": "0.01em", "fontWeight": "600" }],
            "body-sm": ["0.8125rem", { "lineHeight": "1.45", "letterSpacing": "0.005em", "fontWeight": "400" }],
            "headline-md": ["1.375rem", { "lineHeight": "1.35", "letterSpacing": "-0.01em", "fontWeight": "600" }],
            "headline-lg": ["2rem", { "lineHeight": "1.25", "letterSpacing": "-0.02em", "fontWeight": "700" }]
          }
        },
      },
    }
  </script>
</head>
<body class="bg-background text-on-surface antialiased min-h-screen flex flex-col font-body-md text-body-md selection:bg-secondary-fixed selection:text-on-secondary-fixed">
<!-- ==================== SHARED COMPONENT: TopNavBar ==================== -->
@include('partials.navbar', ['active' => 'profile'])
<!-- ==================== MAIN CONTENT WRAPPER ==================== -->
<main class="flex-1 pt-6 pb-space-3xl max-w-[1600px] w-full mx-auto px-margin-desktop">
<!-- Page Dossier Breadcrumb & Title Section -->
<section class="mb-space-xl">
<!-- Coordinate Breadcrumb -->
<div class="inline-flex items-center gap-2 px-3 py-1 bg-surface-container-low border border-outline-variant/70 rounded text-coordinate-tag font-coordinate-tag text-on-surface-variant mb-space-sm uppercase">
<span class="material-symbols-outlined text-[14px] text-secondary" style="font-variation-settings: 'FILL' 1;">anchor</span>
<span id="dossier-breadcrumb">SEAFARER DOSSIER // MEMUAT PROFIL...</span>
</div>
<div class="flex flex-col lg:flex-row lg:items-end justify-between gap-space-lg">
<div>
<h1 class="text-headline-lg font-headline-lg text-on-surface tracking-tight">
            Captain's Ledger: Profil Talenta &amp; Kalibrasi Keahlian
          </h1>
<p class="text-body-md font-body-md text-on-surface-variant mt-1.5 max-w-3xl">
            Kelola portofolio, keahlian teknis, dan preferensi pelayaran kariermu. Setiap pembaruan skill akan otomatis mengkalibrasi ulang skor matchmaking pada seluruh katalog lowongan secara real-time.
          </p>
</div>
<!-- Top Header Action CTAs -->
<div class="flex items-center gap-3 shrink-0">
<a class="inline-flex items-center gap-2 bg-surface-container-lowest border border-outline-variant/80 hover:bg-surface-container-low hover:border-secondary text-on-surface text-label-md font-label-md h-10 px-4 rounded-lg transition-all active:scale-95 shadow-sm" href="/my-dock">
<span class="material-symbols-outlined text-[18px] text-on-surface-variant">dock_to_right</span>
<span>Pantau Lamaran (My Dock)</span>
</a>
<button class="inline-flex items-center gap-2 bg-secondary hover:bg-on-secondary-container text-on-secondary text-label-md font-label-md font-semibold h-10 px-5 rounded-lg transition-all active:scale-95 shadow-sm" id="btn-save-all" type="button">
<span class="material-symbols-outlined text-[18px]">verified</span>
<span>Simpan Pembaruan Profil</span>
</button>
</div>
</div>
</section>
<!-- Two-Column Operational Layout (38% Left / 62% Right) -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter-desktop">
<!-- ==================== LEFT COLUMN (38% -> 5 cols lg) ==================== -->
<div class="lg:col-span-5 space-y-gutter-desktop">
<!-- Profile Header Dossier Card + Statistik Profil -->
<div class="bg-surface-container-lowest border border-outline-variant/60 rounded-xl p-space-xl shadow-none space-y-space-lg">
<div class="flex items-start gap-4">
<div class="relative shrink-0">
<div class="w-16 h-16 rounded-xl bg-secondary text-on-secondary font-headline-md font-bold flex items-center justify-center border-2 border-outline-variant/40 shadow-sm text-xl" data-jh-user-initials="" id="profile-avatar-initials">
                JH
              </div>
<span class="absolute -bottom-1.5 -right-1.5 bg-secondary text-on-secondary w-6 h-6 rounded-full flex items-center justify-center border-2 border-surface-container-lowest" title="Verified Maritime Seafarer">
<span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">anchor</span>
</span>
</div>
<div class="flex-1 min-w-0">
<div class="flex items-center gap-2 mb-1">
<span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full bg-secondary-fixed/50 text-on-secondary-fixed text-label-sm font-label-sm">
<span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
<span id="profile-role-badge">Siap Berlayar (Open to Work)</span>
</span>
</div>
<h2 class="text-headline-sm font-headline-sm text-on-surface truncate" id="profile-display-name">Memuat...</h2>
<p class="text-body-sm font-body-sm text-on-surface-variant font-medium truncate" id="profile-display-email">-</p>
<div class="flex items-center gap-2 mt-2 pt-2 border-t border-outline-variant/40 text-coordinate-tag font-coordinate-tag text-outline">
<span id="profile-display-id">SEAFARER ID: #-</span>
<span>•</span>
<span class="text-secondary font-semibold" id="profile-display-location">LOKASI BELUM DIATUR</span>
</div>
</div>
</div>
<!-- Statistik Profil (Jumlah Skill, Jumlah Lamaran, Rata-rata Match Score) -->
<div class="grid grid-cols-3 gap-2.5 pt-2 border-t border-outline-variant/40">
<div class="bg-surface-container-low/70 border border-outline-variant/60 rounded-lg p-3 text-center">
<p class="text-headline-sm font-headline-sm font-bold text-secondary" id="stat-skills-count">0</p>
<p class="text-coordinate-tag font-coordinate-tag text-outline uppercase mt-0.5">KEAHLIAN AKTIF</p>
</div>
<div class="bg-surface-container-low/70 border border-outline-variant/60 rounded-lg p-3 text-center">
<p class="text-headline-sm font-headline-sm font-bold text-on-surface" id="stat-applications-count">0</p>
<p class="text-coordinate-tag font-coordinate-tag text-outline uppercase mt-0.5">TOTAL LAMARAN</p>
</div>
<div class="bg-surface-container-low/70 border border-outline-variant/60 rounded-lg p-3 text-center">
<p class="text-headline-sm font-headline-sm font-bold text-secondary" id="stat-avg-score">0%</p>
<p class="text-coordinate-tag font-coordinate-tag text-outline uppercase mt-0.5">RATA-RATA MATCH</p>
</div>
</div>
</div>

<!-- Form Edit Biodata & Ringkasan Eksekutif (PUT /api/profile) -->
<form class="bg-surface-container-lowest border border-outline-variant/60 rounded-xl p-space-xl space-y-space-md" id="profile-biodata-form">
<div class="flex items-center justify-between pb-2 border-b border-outline-variant/40">
<h3 class="text-label-md font-label-md text-on-surface flex items-center gap-2">
<span class="material-symbols-outlined text-[18px] text-secondary">badge</span>
              Biodata &amp; Koordinat Kontak
            </h3>
<span class="text-coordinate-tag font-coordinate-tag text-outline">PUT /API/PROFILE</span>
</div>

<div class="space-y-3.5">
<div>
<label class="block text-label-sm font-label-sm text-on-surface mb-1" for="input-name">Nama Lengkap</label>
<div class="relative">
<span class="material-symbols-outlined text-[18px] text-outline absolute left-3 top-1/2 -translate-y-1/2">person</span>
<input class="w-full h-10 pl-9 pr-3 bg-surface-container-lowest border border-outline-variant rounded-lg text-body-sm font-body-sm text-on-surface focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/15" id="input-name" name="name" placeholder="Nama lengkap kamu" required="" type="text"/>
</div>
</div>

<div>
<label class="block text-label-sm font-label-sm text-outline mb-1" for="input-email">Email Operasional (Read-Only)</label>
<div class="relative">
<span class="material-symbols-outlined text-[18px] text-outline absolute left-3 top-1/2 -translate-y-1/2">alternate_email</span>
<input class="w-full h-10 pl-9 pr-3 bg-surface-container-low border border-outline-variant/60 rounded-lg text-body-sm font-body-sm text-on-surface-variant cursor-not-allowed" disabled="" id="input-email" type="email"/>
</div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
<div>
<label class="block text-label-sm font-label-sm text-on-surface mb-1" for="input-phone">Komunikasi Seluler (Telepon)</label>
<div class="relative">
<span class="material-symbols-outlined text-[18px] text-outline absolute left-3 top-1/2 -translate-y-1/2">call</span>
<input class="w-full h-10 pl-9 pr-3 bg-surface-container-lowest border border-outline-variant rounded-lg text-body-sm font-body-sm text-on-surface focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/15" id="input-phone" name="phone" placeholder="0812xxxx" type="text"/>
</div>
</div>

<div>
<label class="block text-label-sm font-label-sm text-on-surface mb-1" for="input-location">Homebase / Pangkalan (Kota)</label>
<div class="relative">
<span class="material-symbols-outlined text-[18px] text-outline absolute left-3 top-1/2 -translate-y-1/2">location_on</span>
<input class="w-full h-10 pl-9 pr-3 bg-surface-container-lowest border border-outline-variant rounded-lg text-body-sm font-body-sm text-on-surface focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/15" id="input-location" name="location" placeholder="Tegal, Jawa Tengah" type="text"/>
</div>
</div>
</div>

<div>
<label class="block text-label-sm font-label-sm text-on-surface mb-1" for="input-bio">Ringkasan Eksekutif &amp; Visi Karier (Bio)</label>
<textarea class="w-full p-3 bg-surface-container-lowest border border-outline-variant rounded-lg text-body-sm font-body-sm text-on-surface focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/15 leading-relaxed" id="input-bio" name="bio" placeholder="Ceritakan pengalaman kerja, fokus keahlian, dan aspirasi kariermu..." rows="4"></textarea>
</div>
</div>

<div class="pt-2 flex items-center justify-between gap-3 border-t border-outline-variant/40">
<span class="text-coordinate-tag font-coordinate-tag text-outline" id="biodata-sync-status">TERHUBUNG KE AUTH &amp; PROFILE SERVICE</span>
<button class="inline-flex items-center gap-1.5 bg-secondary hover:bg-on-secondary-container text-on-secondary text-label-sm font-label-sm font-semibold h-9 px-4 rounded-lg transition-all active:scale-95 shadow-sm" id="btn-save-biodata" type="submit">
<span class="material-symbols-outlined text-[16px]">save</span>
<span>Simpan Biodata</span>
</button>
</div>
</form>

<!-- Ringkasan Koordinat & Kontak Wilayah (Live Preview Card) -->
<div class="bg-surface-container-lowest border border-outline-variant/60 rounded-xl p-space-xl space-y-space-md">
<div class="flex items-center justify-between pb-2 border-b border-outline-variant/40">
<h3 class="text-label-md font-label-md text-on-surface flex items-center gap-2">
<span class="material-symbols-outlined text-[18px] text-secondary">explore</span>
              Pratinjau Dossier Publik
            </h3>
<span class="text-coordinate-tag font-coordinate-tag text-outline">LIVE DOSSIER</span>
</div>
<div class="space-y-3">
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-[18px] text-outline mt-0.5">location_on</span>
<div>
<p class="text-label-sm font-label-sm text-outline">Homebase / Pangkalan</p>
<p class="text-body-sm font-body-sm text-on-surface font-medium" id="preview-location">-</p>
</div>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-[18px] text-outline mt-0.5">alternate_email</span>
<div>
<p class="text-label-sm font-label-sm text-outline">Email Operasional</p>
<p class="text-body-sm font-body-sm text-on-surface font-medium" id="preview-email">-</p>
</div>
</div>
<div class="flex items-start gap-3">
<span class="material-symbols-outlined text-[18px] text-outline mt-0.5">call</span>
<div>
<p class="text-label-sm font-label-sm text-outline">Komunikasi Seluler</p>
<p class="text-body-sm font-body-sm text-on-surface font-medium" id="preview-phone">-</p>
</div>
</div>
<div class="flex items-start gap-3 pt-2 border-t border-outline-variant/40">
<span class="material-symbols-outlined text-[18px] text-secondary mt-0.5">description</span>
<div>
<p class="text-label-sm font-label-sm text-outline">Ringkasan Eksekutif</p>
<p class="text-body-sm font-body-sm text-on-surface-variant leading-relaxed" id="preview-bio">-</p>
</div>
</div>
</div>
</div>
</div>

<!-- ==================== RIGHT COLUMN (62% -> 7 cols lg) ==================== -->
<div class="lg:col-span-7 space-y-gutter-desktop">
<!-- Hero Card: The Lighthouse Radar Simulator -->
<div class="bg-surface-container-lowest border-2 border-secondary/30 rounded-xl p-space-xl relative overflow-hidden shadow-sm">
<!-- Subtle radial maritime gradient backdrop -->
<div class="absolute -right-20 -top-20 w-80 h-80 bg-secondary/5 rounded-full blur-3xl pointer-events-none"></div>
<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-space-md mb-space-md relative z-10">
<div>
<div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full bg-secondary-fixed/50 text-on-secondary-fixed text-coordinate-tag font-coordinate-tag uppercase tracking-wider mb-2">
<span class="material-symbols-outlined text-[14px] text-secondary animate-spin" style="animation-duration: 6s;">radar</span>
                REAL-TIME MATCHMAKING TELEMETRY
              </div>
<h2 class="text-headline-md font-headline-md text-on-surface">The Lighthouse Radar Simulator</h2>
<p class="text-body-sm font-body-sm text-on-surface-variant mt-1">
                Kalibrasi otomatis matriks kecocokan dengan armada pelabuhan berdasarkan algoritma Weighted Jaccard Similarity.
              </p>
</div>
<div class="shrink-0 bg-surface-container-low border border-secondary/40 rounded-xl p-3 text-center sm:text-right">
<span class="text-display-lg-mobile font-display-lg-mobile text-secondary font-extrabold leading-none" id="radar-avg-score">0%</span>
<p class="text-coordinate-tag font-coordinate-tag text-outline uppercase tracking-wider mt-1">RATA-RATA MATCH SCORE</p>
</div>
</div>
<!-- Segmented Telemetry Match Bar -->
<div class="space-y-1.5 relative z-10 mb-space-lg">
<div class="flex justify-between items-center text-label-sm font-label-sm">
<span class="text-on-surface font-semibold flex items-center gap-1.5">
<span class="w-2 h-2 rounded-full bg-secondary"></span>
                Tingkat Kecocokan Portfolio Saat Ini
              </span>
<span class="text-secondary font-bold" id="radar-high-match-label">Memuat lowongan...</span>
</div>
<!-- 5-segment telemetry bar -->
<div class="grid grid-cols-5 gap-1.5 h-3" id="radar-telemetry-bar">
<div class="bg-surface-container-highest rounded-l overflow-hidden"><div class="h-full bg-secondary transition-all duration-300" style="width: 0%"></div></div>
<div class="bg-surface-container-highest overflow-hidden"><div class="h-full bg-secondary transition-all duration-300" style="width: 0%"></div></div>
<div class="bg-surface-container-highest overflow-hidden"><div class="h-full bg-secondary transition-all duration-300" style="width: 0%"></div></div>
<div class="bg-surface-container-highest overflow-hidden"><div class="h-full bg-secondary transition-all duration-300" style="width: 0%"></div></div>
<div class="bg-surface-container-highest rounded-r overflow-hidden"><div class="h-full bg-secondary transition-all duration-300" style="width: 0%"></div></div>
</div>
<p class="text-body-sm font-body-sm text-on-surface-variant pt-1" id="radar-summary-text">
              Memuat simulasi kecocokan keahlian dengan katalog lowongan aktif...
            </p>
</div>
<!-- Radar Recommendations Pill Cluster -->
<div class="pt-space-md border-t border-outline-variant/50 relative z-10">
<div class="flex items-center justify-between mb-2">
<span class="text-label-sm font-label-sm text-on-surface font-semibold flex items-center gap-1.5">
<span class="material-symbols-outlined text-[16px] text-secondary">trending_up</span>
                Rekomendasi Skill Pelayaran Berikutnya (Klik untuk Tambah &amp; Tingkatkan Skor)
              </span>
<span class="text-coordinate-tag font-coordinate-tag text-outline">AUTO-SIMULATE</span>
</div>
<div class="flex flex-wrap gap-2" id="radar-recommended-skills">
<span class="text-body-sm text-on-surface-variant">Menganalisis kebutuhan skill dari lowongan aktif...</span>
</div>
</div>
</div>

<!-- Interactive Technical Skills Matrix Box -->
<div class="bg-surface-container-lowest border border-outline-variant/60 rounded-xl p-space-xl space-y-space-lg">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-outline-variant/40">
<div>
<h3 class="text-headline-sm font-headline-sm text-on-surface flex items-center gap-2">
<span class="material-symbols-outlined text-[20px] text-secondary">hub</span>
                Kumpulan Keahlian Aktif (Technical Skills Matrix)
              </h3>
<p class="text-body-sm font-body-sm text-on-surface-variant">Klik tanda silang untuk melepas skill atau tambahkan skill baru di bawah. Skor kecocokan dihitung ulang otomatis.</p>
</div>
<div class="flex items-center gap-2 shrink-0">
<span class="inline-flex items-center px-2.5 py-1 rounded-full bg-surface-container-low border border-outline-variant text-label-sm font-label-sm text-on-surface font-semibold" id="skills-count-badge">
                0 Skills Terdaftar
              </span>
<button class="inline-flex items-center gap-1.5 bg-secondary hover:bg-on-secondary-container text-on-secondary text-label-sm font-label-sm font-semibold h-9 px-3.5 rounded-lg transition-all active:scale-95 shadow-sm" id="btn-save-skills" type="button">
<span class="material-symbols-outlined text-[16px]">cloud_upload</span>
<span>Simpan Skills</span>
</button>
</div>
</div>
<!-- Add Skill Bar -->
<div class="flex items-center gap-2">
<div class="relative flex-1">
<span class="material-symbols-outlined text-[18px] text-outline absolute left-3.5 top-1/2 -translate-y-1/2">add_Moderator</span>
<input class="w-full h-11 pl-10 pr-4 bg-surface-container-lowest border border-outline-variant rounded-lg text-body-md font-body-md text-on-surface placeholder:text-outline focus:outline-none focus:border-secondary focus:ring-2 focus:ring-secondary/15 transition-all" id="skill-input" placeholder="+ Ketik skill baru lalu tekan Enter (misal: Docker, Go, PostgreSQL)..." type="text"/>
</div>
<button class="h-11 px-5 bg-secondary hover:bg-on-secondary-container text-on-secondary font-label-md text-label-md rounded-lg transition-all shrink-0 flex items-center gap-1.5 shadow-sm active:scale-95" id="btn-add-skill" type="button">
<span class="material-symbols-outlined text-[18px]">add_circle</span>
              Tambah
            </button>
</div>
<!-- Unsaved Skills Notice -->
<div class="hidden items-center justify-between gap-2 px-3.5 py-2 rounded-lg bg-amber-50 border border-amber-200 text-amber-800 text-body-sm" id="skills-dirty-banner">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-[18px]">info</span>
<span>Daftar keahlian telah diubah di simulator. Klik <strong>Simpan Skills</strong> untuk menyimpan permanen ke server.</span>
</div>
<button class="text-label-sm font-semibold underline hover:no-underline shrink-0" id="btn-save-skills-inline" type="button">Simpan Sekarang</button>
</div>
<!-- Active Skills Chips List -->
<div class="space-y-2">
<div class="flex items-center justify-between">
<span class="text-coordinate-tag font-coordinate-tag text-outline uppercase tracking-wider flex items-center gap-1.5">
<span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                DAFTAR KEAHLIAN AKTIF PENGGUNA (PUT /API/PROFILE/SKILLS)
              </span>
<span class="text-coordinate-tag font-coordinate-tag text-outline">MINIMAL 1 SKILL</span>
</div>
<div class="flex flex-wrap gap-2 pt-1" id="skills-chips-container">
<span class="text-body-sm text-on-surface-variant">Memuat keahlian...</span>
</div>
</div>
</div>

<!-- Rekomendasi Lowongan Terbaik (Live Matchmaking Feed) -->
<div class="bg-surface-container-lowest border border-outline-variant/60 rounded-xl p-space-xl space-y-space-lg">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 border-b border-outline-variant/40">
<div>
<h3 class="text-headline-sm font-headline-sm text-on-surface flex items-center gap-2">
<span class="material-symbols-outlined text-[20px] text-secondary">explore</span>
                Rekomendasi Lowongan Terbaik (Live Matchmaking)
              </h3>
<p class="text-body-sm font-body-sm text-on-surface-variant">
                Diurutkan otomatis berdasarkan skor Weighted Jaccard tertinggi terhadap keahlianmu saat ini. Klik chip skill yang kurang untuk langsung menambahkannya!
              </p>
</div>
<a class="text-label-sm font-label-sm text-secondary hover:underline shrink-0 flex items-center gap-1" href="/catalog">
<span>Buka Katalog Lengkap</span>
<span class="material-symbols-outlined text-[16px]">open_in_new</span>
</a>
</div>

<div class="space-y-3" id="recommended-jobs-container">
<div class="p-6 text-center text-on-surface-variant bg-surface-container-low/50 rounded-lg border border-outline-variant/50">
              Memuat rekomendasi lowongan dan menghitung skor kecocokan...
            </div>
</div>
</div>
</div>
</div>
</main>
<!-- ==================== SHARED COMPONENT: Footer ==================== -->
<footer class="w-full bg-surface-container border-t border-outline-variant/60 mt-auto">
<div class="w-full py-space-xl px-margin-desktop flex flex-col md:flex-row justify-between items-center gap-space-lg max-w-[1600px] mx-auto">
<!-- Brand & Copyright -->
<div class="flex flex-col sm:flex-row items-center gap-3 text-center sm:text-left">
<span class="text-label-md font-label-md font-bold text-on-surface flex items-center gap-1.5">
<span class="material-symbols-outlined text-[18px] text-secondary">anchor</span>
          JobHarbour
        </span>
<span class="hidden sm:inline text-outline">•</span>
<p class="text-body-sm font-body-sm text-on-surface-variant">
          © 2026 JobHarbour - Kelompok 4 Universitas Harkat Negeri (Tegal). Pelabuhan Karir &amp; Talenta Indonesia.
        </p>
</div>
<!-- Links -->
<div class="flex items-center flex-wrap justify-center gap-6">
<a class="text-label-sm font-label-sm text-on-surface-variant hover:text-on-surface transition-colors" href="/catalog">
          The Anchorage (Katalog)
        </a>
<a class="text-label-sm font-label-sm text-on-surface-variant hover:text-on-surface transition-colors" href="/my-dock">
          My Dock (Lamaran)
        </a>
<a class="text-label-sm font-label-sm text-on-surface-variant hover:text-on-surface transition-colors" href="/profile">
          Captain's Ledger (Profil)
        </a>
<a class="text-label-sm font-label-sm text-on-surface-variant hover:text-on-surface transition-colors" href="/recruiter">
          Harbour Master (Recruiter)
        </a>
</div>
</div>
</footer>
<script src="/js/jh-api.js"></script>
<script src="/js/pages/profile.js"></script>
</body></html>
