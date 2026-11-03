<!DOCTYPE html>
<html lang="id" class="h-full bg-[#18181b] text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postman API Client - JobHarbour Microservices Workspace</title>
    <link rel="icon" href="/images/favicon-jobharbour.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-mono-code { font-family: 'Fira Code', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .tab-active { border-bottom: 2px solid #f97316; color: #f97316 !important; font-weight: 600; }
        .method-get { color: #10b981; }
        .method-post { color: #f59e0b; }
        .method-put { color: #3b82f6; }
        .method-patch { color: #a855f7; }
        .method-delete { color: #ef4444; }
        .bg-method-get { background-color: rgba(16, 185, 129, 0.15); color: #10b981; }
        .bg-method-post { background-color: rgba(245, 158, 11, 0.15); color: #f59e0b; }
        .bg-method-put { background-color: rgba(59, 130, 246, 0.15); color: #3b82f6; }
        .bg-method-patch { background-color: rgba(168, 85, 247, 0.15); color: #a855f7; }
        .bg-method-delete { background-color: rgba(239, 68, 68, 0.15); color: #ef4444; }
        .json-key { color: #38bdf8; }
        .json-string { color: #a3e635; }
        .json-number { color: #fbbf24; }
        .json-boolean { color: #f472b6; }
        .json-null { color: #94a3b8; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #18181b; }
        ::-webkit-scrollbar-thumb { background: #27272a; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #3f3f46; }
    </style>
</head>
<body class="h-full flex flex-col bg-[#09090b] text-slate-200 antialiased overflow-hidden selection:bg-orange-500 selection:text-white">

    <!-- Top Workspace Header (Postman Style) -->
    <header class="h-12 border-b border-zinc-800 bg-[#121215] flex items-center justify-between px-3 gap-3 shrink-0 z-30">
        
        <!-- Left: Workspace & Logo -->
        <div class="flex items-center gap-3">
            <a href="/" class="flex items-center gap-2 group" title="Kembali ke Beranda JobHarbour">
                <div class="w-7 h-7 rounded-lg bg-orange-600 flex items-center justify-center shadow-md shadow-orange-600/30">
                    <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>
                <div class="flex flex-col">
                    <div class="flex items-center gap-1.5">
                        <span class="text-xs font-bold text-white tracking-wide">JobHarbour API Client</span>
                        <span class="text-[10px] font-semibold px-1.5 py-0.2 rounded bg-orange-500/20 text-orange-400 border border-orange-500/30">Postman Web</span>
                    </div>
                    <span class="text-[10px] text-zinc-400 -mt-0.5">Workspace: Pemrograman Web Service (Kelompok 4)</span>
                </div>
            </a>
        </div>

        <!-- Center: Quick Role Switcher (1-Click Login JWT) -->
        <div class="hidden md:flex items-center gap-1.5 bg-zinc-900 border border-zinc-800 rounded-lg p-1 text-xs">
            <span class="text-[11px] text-zinc-400 px-2 font-medium">Masuk Otomatis:</span>
            <button type="button" onclick="loginPreset('budi@jobharbour.id', 'password123', 'Budi Santoso', 'applicant')" class="px-2.5 py-1 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-200 hover:text-white transition cursor-pointer" title="Login sebagai Pelamar">
                👤 Budi (Pelamar)
            </button>
            <button type="button" onclick="loginPreset('recruiter@jobharbour.id', 'password123', 'PT Solusi Teknologi', 'recruiter')" class="px-2.5 py-1 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-200 hover:text-white transition cursor-pointer" title="Login sebagai Recruiter Resmi">
                🏢 Recruiter (Resmi)
            </button>
            <button type="button" onclick="loginPreset('recruiter.baru@jobharbour.id', 'password123', 'Recruiter Belum Verif', 'recruiter')" class="px-2.5 py-1 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-200 hover:text-white transition cursor-pointer" title="Login sebagai Recruiter Baru Belum Verifikasi">
                🆕 Recruiter (Trial)
            </button>
            <button type="button" onclick="loginPreset('admin@jobharbour.id', 'password123', 'Admin Evaluator', 'admin')" class="px-2.5 py-1 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-200 hover:text-white transition cursor-pointer" title="Login sebagai Administrator">
                🛡️ Admin
            </button>
        </div>

        <!-- Right: Environment & Active Token Indicator -->
        <div class="flex items-center gap-2">
            <div id="roleChip" class="flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-zinc-900 border border-zinc-800 text-xs">
                <span id="roleDot" class="w-2 h-2 rounded-full bg-zinc-500"></span>
                <span id="roleText" class="text-zinc-400 font-mono-code text-[11px]">Anonim</span>
                <button type="button" onclick="clearToken()" class="text-zinc-500 hover:text-rose-400 ml-1 text-xs" title="Hapus token">✕</button>
            </div>

            <div class="flex items-center gap-1 px-2 py-1 rounded bg-zinc-900 border border-zinc-800 text-[11px] text-zinc-400">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>DDEV Local</span>
            </div>

            <a href="/docs" target="_blank" class="px-2.5 py-1 rounded bg-zinc-900 hover:bg-zinc-800 border border-zinc-800 text-xs text-zinc-300 hover:text-white transition flex items-center gap-1" title="Buka Swagger UI Asli">
                <svg class="w-3.5 h-3.5 text-zinc-400" viewBox="0 0 24 24" fill="currentColor"><path d="M19 19H5V5h7V3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z"/></svg>
                <span>Swagger</span>
            </a>
        </div>
    </header>

    <!-- Postman Tabs Bar -->
    <div class="h-9 border-b border-zinc-800 bg-[#151518] flex items-center px-2 gap-1 shrink-0 overflow-x-auto">
        <div id="activeTabPill" class="flex items-center gap-2 px-3 py-1.5 rounded-t-md bg-[#1d1d21] border-t-2 border-orange-500 text-xs text-white font-medium shadow-sm">
            <span id="tabMethodBadge" class="font-mono-code font-bold text-[10px] text-emerald-400">GET</span>
            <span id="tabTitle" class="truncate max-w-[200px]">Katalog Lowongan Kerja</span>
        </div>
        <div class="text-xs text-zinc-500 px-2 ml-auto flex items-center gap-2">
            <span>Shortcut:</span>
            <kbd class="px-1.5 py-0.5 rounded bg-zinc-800 text-zinc-300 font-mono-code text-[10px]">Ctrl + Enter</kbd>
        </div>
    </div>

    <!-- Main App Grid: Left Sidebar (Collections) + Center (Request & Response) -->
    <div class="flex-1 flex overflow-hidden">
        
        <!-- Sidebar: Collections & Endpoints Explorer (Width: 320px) -->
        <aside class="w-80 border-r border-zinc-800 bg-[#121215] flex flex-col shrink-0 overflow-hidden">
            
            <!-- Sidebar Top: Search Collections -->
            <div class="p-2 border-b border-zinc-800">
                <div class="relative">
                    <input type="text" id="endpointSearch" oninput="filterEndpoints(this.value)" placeholder="Cari koleksi request API..." class="w-full bg-zinc-900 border border-zinc-800 rounded-md py-1.5 pl-8 pr-2 text-xs text-zinc-200 placeholder-zinc-500 outline-none focus:border-orange-500/80">
                    <svg class="w-3.5 h-3.5 text-zinc-500 absolute left-2.5 top-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
            </div>

            <!-- Collections Tree View -->
            <div id="collectionsList" class="flex-1 overflow-y-auto p-2 space-y-3">

                <!-- Collection 1: Auth & Profile Service -->
                <div class="collection-group">
                    <button type="button" onclick="toggleFolder('folder-auth')" class="w-full flex items-center justify-between py-1 text-xs font-semibold text-zinc-300 hover:text-white group">
                        <span class="flex items-center gap-1.5">
                            <span id="folder-auth-icon" class="text-zinc-500 text-[10px]">▼</span>
                            <span>📁 1. Auth & Profile (:8001)</span>
                        </span>
                        <span class="text-[10px] text-zinc-500">Nabe'ela</span>
                    </button>
                    <div id="folder-auth" class="space-y-0.5 mt-1 pl-3 border-l border-zinc-800">
                        <button type="button" onclick="selectRequest('auth_login_applicant')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-post">POST</span>
                            <span class="truncate">Login Pelamar</span>
                        </button>
                        <button type="button" onclick="selectRequest('auth_login_recruiter')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-post">POST</span>
                            <span class="truncate">Login Recruiter Resmi</span>
                        </button>
                        <button type="button" onclick="selectRequest('auth_register_validation')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-post">POST</span>
                            <span class="truncate">Register Validasi Sandi</span>
                        </button>
                        <button type="button" onclick="selectRequest('auth_me')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-get">GET</span>
                            <span class="truncate">Ambil Profil Pengguna</span>
                        </button>
                    </div>
                </div>

                <!-- Collection 2: Job Catalog Service -->
                <div class="collection-group">
                    <button type="button" onclick="toggleFolder('folder-catalog')" class="w-full flex items-center justify-between py-1 text-xs font-semibold text-zinc-300 hover:text-white group">
                        <span class="flex items-center gap-1.5">
                            <span id="folder-catalog-icon" class="text-zinc-500 text-[10px]">▼</span>
                            <span>📁 2. Job Catalog (:8002)</span>
                        </span>
                        <span class="text-[10px] text-zinc-500">Fahren</span>
                    </button>
                    <div id="folder-catalog" class="space-y-0.5 mt-1 pl-3 border-l border-zinc-800">
                        <button type="button" onclick="selectRequest('catalog_list_boost')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-get">GET</span>
                            <span class="truncate">Katalog (Priority Boost)</span>
                        </button>
                        <button type="button" onclick="selectRequest('catalog_verified_only')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-get">GET</span>
                            <span class="truncate">Filter Lowongan Terverifikasi</span>
                        </button>
                        <button type="button" onclick="selectRequest('catalog_detail')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-get">GET</span>
                            <span class="truncate">Detail Lowongan Kerja</span>
                        </button>
                        <button type="button" onclick="selectRequest('catalog_post_trial')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-post">POST</span>
                            <span class="truncate">Post 1 Lowongan Trial</span>
                        </button>
                        <button type="button" onclick="selectRequest('catalog_post_blocked')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-post">POST</span>
                            <span class="truncate">Cegat Lowongan Ke-2</span>
                        </button>
                    </div>
                </div>

                <!-- Collection 3: Application & Matchmaking -->
                <div class="collection-group">
                    <button type="button" onclick="toggleFolder('folder-matchmaking')" class="w-full flex items-center justify-between py-1 text-xs font-semibold text-zinc-300 hover:text-white group">
                        <span class="flex items-center gap-1.5">
                            <span id="folder-matchmaking-icon" class="text-zinc-500 text-[10px]">▼</span>
                            <span>📁 3. Matchmaking (:8003)</span>
                        </span>
                        <span class="text-[10px] text-zinc-500">Yasir</span>
                    </button>
                    <div id="folder-matchmaking" class="space-y-0.5 mt-1 pl-3 border-l border-zinc-800">
                        <button type="button" onclick="selectRequest('apply_submit')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-post">POST</span>
                            <span class="truncate">Apply + Hitung Skor Bobot</span>
                        </button>
                        <button type="button" onclick="selectRequest('apply_duplicate')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-post">POST</span>
                            <span class="truncate">Cegat Lamaran Ganda (409)</span>
                        </button>
                        <button type="button" onclick="selectRequest('apply_my_dock')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-get">GET</span>
                            <span class="truncate">Riwayat Pelamar (My Dock)</span>
                        </button>
                    </div>
                </div>

                <!-- Collection 4: Recruitment Pipeline ATS -->
                <div class="collection-group">
                    <button type="button" onclick="toggleFolder('folder-pipeline')" class="w-full flex items-center justify-between py-1 text-xs font-semibold text-zinc-300 hover:text-white group">
                        <span class="flex items-center gap-1.5">
                            <span id="folder-pipeline-icon" class="text-zinc-500 text-[10px]">▼</span>
                            <span>📁 4. Pipeline ATS (:8004)</span>
                        </span>
                        <span class="text-[10px] text-zinc-500">Affif</span>
                    </button>
                    <div id="folder-pipeline" class="space-y-0.5 mt-1 pl-3 border-l border-zinc-800">
                        <button type="button" onclick="selectRequest('pipeline_list')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-get">GET</span>
                            <span class="truncate">Daftar Pelamar di Pipeline</span>
                        </button>
                        <button type="button" onclick="selectRequest('pipeline_anti_idor')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-get">GET</span>
                            <span class="truncate">Uji Keamanan Anti-IDOR (403)</span>
                        </button>
                        <button type="button" onclick="selectRequest('pipeline_forward_valid')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-patch">PATCH</span>
                            <span class="truncate">Transisi Tahap Maju (Valid)</span>
                        </button>
                        <button type="button" onclick="selectRequest('pipeline_backward_invalid')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-patch">PATCH</span>
                            <span class="truncate">Transisi Mundur Ilegal (422)</span>
                        </button>
                        <button type="button" onclick="selectRequest('pipeline_add_note')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-post">POST</span>
                            <span class="truncate">Tambah Catatan Evaluator</span>
                        </button>
                    </div>
                </div>

                <!-- Collection 5: Aggregator & External -->
                <div class="collection-group">
                    <button type="button" onclick="toggleFolder('folder-aggregator')" class="w-full flex items-center justify-between py-1 text-xs font-semibold text-zinc-300 hover:text-white group">
                        <span class="flex items-center gap-1.5">
                            <span id="folder-aggregator-icon" class="text-zinc-500 text-[10px]">▼</span>
                            <span>📁 5. Aggregator & Resilience</span>
                        </span>
                        <span class="text-[10px] text-zinc-500">Eksternal</span>
                    </button>
                    <div id="folder-aggregator" class="space-y-0.5 mt-1 pl-3 border-l border-zinc-800">
                        <button type="button" onclick="selectRequest('aggregator_summary')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-get">GET</span>
                            <span class="truncate">Ringkasan Statistik Eksternal</span>
                        </button>
                        <button type="button" onclick="selectRequest('aggregator_sync')" class="req-item w-full text-left px-2 py-1.5 rounded hover:bg-zinc-800/80 text-xs text-zinc-400 hover:text-zinc-100 flex items-center gap-2 group transition">
                            <span class="font-mono-code font-bold text-[10px] method-post">POST</span>
                            <span class="truncate">Sinkronisasi Lowongan Luar</span>
                        </button>
                    </div>
                </div>

            </div>

            <!-- Sidebar Footer: Status -->
            <div class="p-2 border-t border-zinc-800 text-[11px] text-zinc-500 flex items-center justify-between">
                <span>18 Endpoint Siap Uji</span>
                <span class="text-emerald-400 font-mono-code">REST JSON</span>
            </div>
        </aside>

        <!-- Main Workspace (Center & Right) -->
        <main class="flex-1 flex flex-col overflow-hidden bg-[#18181b]">
            
            <!-- Request Builder Top Bar (Method + URL + Send + cURL) -->
            <div class="p-3 border-b border-zinc-800 bg-[#141417] flex items-center gap-2 shrink-0">
                <div class="relative w-28 shrink-0">
                    <select id="requestMethod" onchange="updateMethodColor(this.value)" class="w-full bg-zinc-900 border border-zinc-700 rounded-md py-2 px-2.5 text-xs font-mono-code font-bold text-emerald-400 outline-none focus:border-orange-500 cursor-pointer">
                        <option value="GET" class="method-get font-bold">GET</option>
                        <option value="POST" class="method-post font-bold">POST</option>
                        <option value="PUT" class="method-put font-bold">PUT</option>
                        <option value="PATCH" class="method-patch font-bold">PATCH</option>
                        <option value="DELETE" class="method-delete font-bold">DELETE</option>
                    </select>
                </div>

                <div class="flex-1 relative">
                    <input type="text" id="requestUrl" value="/api/jobs" placeholder="https://api.domain.com/v1/resource atau /api/jobs" class="w-full bg-zinc-900 border border-zinc-700 rounded-md py-2 px-3 text-xs font-mono-code text-zinc-100 outline-none focus:border-orange-500">
                </div>

                <button type="button" id="btnSendRequest" onclick="sendRequest()" class="px-5 py-2 bg-orange-600 hover:bg-orange-500 text-white font-bold text-xs rounded-md flex items-center gap-1.5 shadow-md shadow-orange-600/30 transition cursor-pointer">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                    <span>Send</span>
                </button>

                <button type="button" onclick="copyCurl()" id="btnCopyCurl" class="px-3 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white text-xs rounded-md border border-zinc-700 flex items-center gap-1 transition cursor-pointer" title="Salin perintah cURL">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                    <span>cURL</span>
                </button>
            </div>

            <!-- Two-Pane Split: Top Request Config (Params/Auth/Headers/Body/UTS) & Bottom Response Inspector -->
            <div class="flex-1 grid grid-rows-2 overflow-hidden">
                
                <!-- Upper Pane: Request Details -->
                <div class="flex flex-col border-b border-zinc-800 bg-[#16161a] overflow-hidden">
                    
                    <!-- Request Subtabs Navigation -->
                    <div class="h-9 border-b border-zinc-800 px-3 flex items-center gap-4 text-xs font-medium text-zinc-400 shrink-0 bg-[#131316]">
                        <button type="button" id="subtab-params-btn" onclick="openSubtab('params')" class="h-full px-1 hover:text-zinc-200 transition">Params</button>
                        <button type="button" id="subtab-auth-btn" onclick="openSubtab('auth')" class="h-full px-1 hover:text-zinc-200 transition">Authorization <span id="authTabDot" class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 ml-1"></span></button>
                        <button type="button" id="subtab-headers-btn" onclick="openSubtab('headers')" class="h-full px-1 hover:text-zinc-200 transition">Headers</button>
                        <button type="button" id="subtab-body-btn" onclick="openSubtab('body')" class="h-full px-1 tab-active transition">Body (JSON)</button>
                        <button type="button" id="subtab-rubric-btn" onclick="openSubtab('rubric')" class="h-full px-1 hover:text-zinc-200 text-orange-400 transition flex items-center gap-1">
                            <span>🎯 Panduan Rubrik UTS</span>
                        </button>
                    </div>

                    <!-- Subtab Content 1: Params -->
                    <div id="subtab-params" class="hidden flex-1 p-3 overflow-y-auto space-y-2">
                        <div class="text-[11px] text-zinc-400">Query Parameters (Otomatis ditambahkan ke URL query string):</div>
                        <div class="border border-zinc-800 rounded-md overflow-hidden text-xs">
                            <table class="w-full text-left font-mono-code">
                                <thead class="bg-zinc-900 text-zinc-400 text-[11px] border-b border-zinc-800">
                                    <tr>
                                        <th class="p-2 w-10 text-center">Aktif</th>
                                        <th class="p-2">Key</th>
                                        <th class="p-2">Value</th>
                                        <th class="p-2">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800/60 text-zinc-300">
                                    <tr>
                                        <td class="p-2 text-center"><input type="checkbox" checked onchange="toggleParam('verified_only', 'true', this.checked)"></td>
                                        <td class="p-2 text-sky-400">verified_only</td>
                                        <td class="p-2 text-amber-300">true</td>
                                        <td class="p-2 text-zinc-400 font-sans text-[11px]">Filter hanya lowongan dengan status verified_official</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 text-center"><input type="checkbox" onchange="toggleParam('limit', '10', this.checked)"></td>
                                        <td class="p-2 text-sky-400">limit</td>
                                        <td class="p-2 text-amber-300">10</td>
                                        <td class="p-2 text-zinc-400 font-sans text-[11px]">Batasan jumlah data per halaman</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Subtab Content 2: Authorization -->
                    <div id="subtab-auth" class="hidden flex-1 p-4 overflow-y-auto space-y-3">
                        <div class="flex items-center justify-between">
                            <div class="text-xs font-semibold text-zinc-300">Tipe Otorisasi: <span class="text-orange-400">Bearer Token (JWT)</span></div>
                            <span class="text-[11px] text-zinc-500">Token otomatis diperbarui saat memilih akun preset di atas</span>
                        </div>
                        <div class="space-y-1">
                            <label class="block text-xs text-zinc-400">Token Bearer:</label>
                            <input type="text" id="inputBearerToken" placeholder="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..." class="w-full bg-zinc-900 border border-zinc-700 rounded p-2 text-xs font-mono-code text-zinc-200 outline-none focus:border-orange-500">
                        </div>
                        <div class="p-3 rounded-lg bg-zinc-900/80 border border-zinc-800 text-xs text-zinc-400 space-y-1">
                            <div class="font-medium text-zinc-300">💡 Cara Kerja Token di JobHarbour:</div>
                            <p>Setiap microservice memverifikasi klaim JWT yang mencakup <code>user_id</code>, <code>email</code>, dan <code>role</code> (applicant, recruiter, admin). Token ini melindungi endpoint dari akses IDOR dan memastikan batasan posting lowongan.</p>
                        </div>
                    </div>

                    <!-- Subtab Content 3: Headers -->
                    <div id="subtab-headers" class="hidden flex-1 p-3 overflow-y-auto space-y-2">
                        <div class="border border-zinc-800 rounded-md overflow-hidden text-xs">
                            <table class="w-full text-left font-mono-code">
                                <thead class="bg-zinc-900 text-zinc-400 text-[11px] border-b border-zinc-800">
                                    <tr>
                                        <th class="p-2">Header Key</th>
                                        <th class="p-2">Value</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800/60 text-zinc-300 text-xs">
                                    <tr>
                                        <td class="p-2 text-sky-400">Accept</td>
                                        <td class="p-2 text-zinc-300">application/json</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 text-sky-400">Content-Type</td>
                                        <td class="p-2 text-zinc-300">application/json</td>
                                    </tr>
                                    <tr>
                                        <td class="p-2 text-sky-400">Authorization</td>
                                        <td class="p-2 text-zinc-400 truncate max-w-xs" id="headerAuthPreview">(Sesuai token Bearer di tab Auth)</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Subtab Content 4: Body (JSON) -->
                    <div id="subtab-body" class="flex-1 flex flex-col p-2 overflow-hidden">
                        <div class="flex items-center justify-between pb-1 px-1">
                            <span class="text-[11px] text-zinc-400">Raw JSON Payload</span>
                            <div class="flex items-center gap-2">
                                <span id="bodyFormatStatus" class="text-[11px] text-zinc-500">JSON OK</span>
                                <button type="button" onclick="beautifyJson()" class="text-[11px] text-orange-400 hover:text-orange-300 transition">Format / Rapikan</button>
                            </div>
                        </div>
                        <textarea id="requestBodyInput" spellcheck="false" class="flex-1 w-full bg-[#111114] border border-zinc-800 rounded p-2 text-xs font-mono-code text-zinc-200 outline-none focus:border-orange-500/80 resize-none" placeholder="{}"></textarea>
                    </div>

                    <!-- Subtab Content 5: Rubrik Penilaian UTS Info Box -->
                    <div id="subtab-rubric" class="hidden flex-1 p-4 overflow-y-auto space-y-3">
                        <div class="flex items-center justify-between">
                            <span id="rubricModuleTag" class="text-xs font-bold px-2.5 py-0.5 rounded bg-orange-500/20 text-orange-400 border border-orange-500/30">
                                Modul 2: Job Catalog Service
                            </span>
                            <span class="text-xs text-zinc-400">Owner: <strong id="rubricOwner" class="text-white">M. Fahren Andrean Rangkuti</strong></span>
                        </div>
                        <div class="space-y-1">
                            <h3 id="rubricTitle" class="text-sm font-bold text-white">Search Priority Boost & Tiered Trust Policy</h3>
                            <p id="rubricExplanation" class="text-xs text-zinc-300 leading-relaxed">
                                Sistem secara otomatis menaikkan peringkat lowongan dari perusahaan terverifikasi resmi (<code>verified_official</code>) agar muncul di bagian paling atas katalog publik pelamar, sehingga mencegah penipuan lowongan bodong.
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-zinc-900 border border-zinc-800 space-y-1.5 text-xs">
                            <div class="font-semibold text-zinc-300">Kriteria Pembuktian ke Dosen:</div>
                            <ul id="rubricPoints" class="list-disc pl-4 text-zinc-400 space-y-1">
                                <li>Status respon harus 200 OK dengan format envelope terstandarisasi.</li>
                                <li>Item index 0 berstatus <code>verified_official</code>.</li>
                            </ul>
                        </div>
                    </div>

                </div>

                <!-- Lower Pane: Response Inspector -->
                <div class="flex flex-col bg-[#121215] overflow-hidden">
                    
                    <!-- Response Header Bar -->
                    <div class="h-10 border-b border-zinc-800 px-3 flex items-center justify-between bg-[#151518] shrink-0">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-bold text-zinc-400">Response</span>
                            <span id="responseStatusBadge" class="px-2 py-0.5 rounded text-xs font-mono-code font-bold bg-zinc-800 text-zinc-400 border border-zinc-700">
                                Belum Dijalankan
                            </span>
                            <span id="responseTimeBadge" class="text-xs font-mono-code text-zinc-400">0 ms</span>
                            <span id="responseSizeBadge" class="text-xs font-mono-code text-zinc-400">0 B</span>
                        </div>

                        <!-- Envelope Verification Checkmark -->
                        <div class="flex items-center gap-2">
                            <div class="hidden sm:flex items-center gap-1.5 text-[11px] font-mono-code text-zinc-500 bg-zinc-900 px-2 py-0.5 rounded border border-zinc-800">
                                <span class="text-zinc-400 font-sans">Envelope:</span>
                                <span id="env-status">status</span> •
                                <span id="env-message">message</span> •
                                <span id="env-data">data</span> •
                                <span id="env-meta">meta</span>
                            </div>
                            <button type="button" onclick="copyResponse()" id="btnCopyResponse" class="px-2.5 py-1 rounded bg-zinc-800 hover:bg-zinc-700 text-zinc-300 hover:text-white text-xs border border-zinc-700 transition" title="Salin seluruh isi respon">
                                Copy Response
                            </button>
                        </div>
                    </div>

                    <!-- Response Body Viewer (Pretty JSON) -->
                    <div class="flex-1 p-3 overflow-auto bg-[#0d0d10] font-mono-code text-xs text-zinc-300 leading-relaxed">
                        <pre id="responseBodyViewer" class="whitespace-pre-wrap selection:bg-orange-500/30">
Pilih salah satu request dari menu kiri atau klik tombol "Send" di atas untuk menjalankan API request langsung.
                        </pre>
                    </div>

                </div>

            </div>

        </main>

    </div>

    <!-- Core Interactive Client Script -->
    <script>
        // Endpoint Presets Data for the 4 Microservices + Aggregator
        const ENDPOINTS = {
            'catalog_list_boost': {
                name: 'Katalog (Priority Boost)',
                method: 'GET',
                url: '/api/jobs',
                body: '',
                role: null,
                module: 'Modul 2: Job Catalog Service (:8002)',
                owner: 'M. Fahren Andrean Rangkuti',
                title: 'Search Priority Boost untuk Perusahaan Terverifikasi',
                desc: 'Mengambil seluruh lowongan aktif. Lowongan dari perusahaan berstatus verified_official otomatis diprioritaskan di baris teratas hasil katalog publik.',
                points: [
                    'Menguji kueri berbobot: ORDER BY verification_status = "verified_official" DESC',
                    'Mengembalikan struktur envelope baku { status, message, data, meta }',
                    'Aman diakses oleh publik tanpa otentikasi wajib'
                ]
            },
            'catalog_verified_only': {
                name: 'Filter Lowongan Terverifikasi',
                method: 'GET',
                url: '/api/jobs?verified_only=true',
                body: '',
                role: null,
                module: 'Modul 2: Job Catalog Service',
                owner: 'M. Fahren Andrean Rangkuti',
                title: 'Penyaringan Khusus Lowongan Terverifikasi Resmi',
                desc: 'Hanya menampilkan lowongan kerja dari perusahaan yang dokumen legalitas dan verifikasinya telah disetujui.',
                points: [
                    'Parameter verified_only=true memfilter lowongan trial recruiter baru',
                    'Setiap item yang dikembalikan memiliki lencana resmi'
                ]
            },
            'catalog_detail': {
                name: 'Detail Lowongan Kerja',
                method: 'GET',
                url: '/api/jobs/1',
                body: '',
                role: null,
                module: 'Modul 2: Job Catalog Service',
                owner: 'M. Fahren Andrean Rangkuti',
                title: 'Detail Lowongan Spesifik',
                desc: 'Melihat rincian deskripsi, kualifikasi, rentang gaji, dan daftar keahlian yang disyaratkan.',
                points: [
                    'Mengembalikan detail satu entitas lowongan',
                    'Menyertakan relasi skills yang dibutuhkan untuk matchmaking'
                ]
            },
            'catalog_post_trial': {
                name: 'Post 1 Lowongan Trial',
                method: 'POST',
                url: '/api/jobs',
                body: JSON.stringify({
                    title: 'Junior Cloud Administrator (Trial)',
                    description: 'Membantu pengelolaan server staging dan container JobHarbour.',
                    job_type: 'fulltime',
                    location: 'Tegal / Remote',
                    salary_range: 'Rp 5.500.000 - Rp 8.000.000',
                    deadline: '2026-12-31',
                    skills: [
                        { name: 'Docker', level: 'required' },
                        { name: 'Linux', level: 'important' }
                    ]
                }, null, 2),
                role: 'recruiter.baru@jobharbour.id',
                module: 'Modul 2: Tiered Trust & Anti-Spam Policy',
                owner: 'M. Fahren Andrean Rangkuti',
                title: 'Hak Coba 1 Lowongan Pertama (Trial)',
                desc: 'Recruiter baru yang belum verifikasi diizinkan mempublikasikan 1 lowongan kerja awal (regular_standard).',
                points: [
                    'Status respon harus 201 Created',
                    'Status verifikasi lowongan bertuliskan regular_standard',
                    'Membuktikan fitur kemudahan onboarding recruiter baru'
                ]
            },
            'catalog_post_blocked': {
                name: 'Cegat Lowongan Ke-2 Tanpa Verifikasi',
                method: 'POST',
                url: '/api/jobs',
                body: JSON.stringify({
                    title: 'Senior Enterprise Architect (Dicegat)',
                    description: 'Mencoba membuka lowongan kedua tanpa verifikasi perusahaan.',
                    job_type: 'fulltime',
                    location: 'Jakarta',
                    deadline: '2026-12-31',
                    skills: [{ name: 'Kubernetes', level: 'required' }]
                }, null, 2),
                role: 'recruiter.baru@jobharbour.id',
                module: 'Modul 2: Tiered Trust & Anti-Lowongan Bodong',
                owner: 'M. Fahren Andrean Rangkuti',
                title: 'Pencegahan Spam & Penipuan Lowongan Bodong',
                desc: 'Jika recruiter yang belum verifikasi mencoba membuka lowongan kedua, sistem langsung mencegat dengan HTTP 403 Forbidden.',
                points: [
                    'Harus mengembalikan HTTP 403 Forbidden',
                    'Error code: COMPANY_NOT_VERIFIED',
                    'Memenuhi aspek keamanan dan integritas platform rekrutmen'
                ]
            },
            'auth_login_applicant': {
                name: 'Login Pelamar (Budi)',
                method: 'POST',
                url: '/api/auth/login',
                body: JSON.stringify({
                    email: 'budi@jobharbour.id',
                    password: 'password123'
                }, null, 2),
                role: null,
                module: 'Modul 1: Auth & Profile Service (:8001)',
                owner: 'Nabe\'ela Ayu Ning Tyas Zahra',
                title: 'Otentikasi Pelamar & Penerbitan JWT',
                desc: 'Verifikasi kredensial pelamar dan mengembalikan token JWT untuk autorisasi endpoint terlindungi.',
                points: [
                    'Respons 200 OK dengan objek user dan string token',
                    'Masa berlaku token terverifikasi di header Authorization'
                ]
            },
            'auth_login_recruiter': {
                name: 'Login Recruiter Resmi',
                method: 'POST',
                url: '/api/auth/login',
                body: JSON.stringify({
                    email: 'recruiter@jobharbour.id',
                    password: 'password123'
                }, null, 2),
                role: null,
                module: 'Modul 1: Auth & Profile Service',
                owner: 'Nabe\'ela Ayu Ning Tyas Zahra',
                title: 'Otentikasi Akun Recruiter Terverifikasi',
                desc: 'Masuk sebagai recruiter resmi PT Solusi Teknologi Nusantara.',
                points: [
                    'Klaim role recruiter pada payload JWT',
                    'Memuat relasi ke company_id terkait'
                ]
            },
            'auth_register_validation': {
                name: 'Register Validasi Sandi',
                method: 'POST',
                url: '/api/auth/register',
                body: JSON.stringify({
                    name: 'Kandidat Uji',
                    email: 'uji.' + Date.now() + '@jobharbour.id',
                    password: '123',
                    role: 'applicant'
                }, null, 2),
                role: null,
                module: 'Modul 1: Auth & Profile Service',
                owner: 'Nabe\'ela Ayu Ning Tyas Zahra',
                title: 'Validasi Kata Sandi Minimal 8 Karakter',
                desc: 'Menguji ketahanan validasi server. Sandi kurang dari 8 karakter harus ditolak.',
                points: [
                    'Harus mengembalikan status 422 Unprocessable Content',
                    'Pesan error terperinci pada kunci errors.password'
                ]
            },
            'auth_me': {
                name: 'Ambil Profil Pengguna',
                method: 'GET',
                url: '/api/auth/me',
                body: '',
                role: 'budi@jobharbour.id',
                module: 'Modul 1: Auth & Profile Service',
                owner: 'Nabe\'ela Ayu Ning Tyas Zahra',
                title: 'Mendapatkan Data Profil via Token',
                desc: 'Membaca token JWT pada request header dan mengembalikan identitas pengguna yang sedang aktif.',
                points: [
                    'Respons 200 OK dengan detail pengguna',
                    'Menolak akses dengan 401 jika token tidak disertakan'
                ]
            },
            'apply_submit': {
                name: 'Apply + Hitung Skor Bobot',
                method: 'POST',
                url: '/api/applications',
                body: JSON.stringify({
                    job_id: 1,
                    portfolio_url: 'https://github.com/budi-dev/portofolio-lengkap',
                    cover_letter: 'Saya sangat antusias melamar posisi ini karena kesesuaian skill backend saya.'
                }, null, 2),
                role: 'budi@jobharbour.id',
                module: 'Modul 3: Matchmaking Service (:8003)',
                owner: 'Muhammad Yasir Ilham Nabil',
                title: 'Mesin Pemadanan Keahlian Berbobot Otomatis',
                desc: 'Menghitung persentase kecocokan pelamar secara otomatis menggunakan formula bobot: required=3, important=2, nice_to_have=1.',
                points: [
                    'Skor dihitung otomatis saat lamaran pertama kali dibuat',
                    'Respons 201 Created dengan skor matchmaking_score (0 - 100%)',
                    'Jika sudah melamar, server mencegah duplikasi dengan 409 Conflict'
                ]
            },
            'apply_duplicate': {
                name: 'Cegat Lamaran Ganda (409)',
                method: 'POST',
                url: '/api/applications',
                body: JSON.stringify({
                    job_id: 1,
                    portfolio_url: 'https://github.com/budi-dev/duplikat',
                    cover_letter: 'Mencoba mengirim lamaran kedua pada lowongan yang sama.'
                }, null, 2),
                role: 'budi@jobharbour.id',
                module: 'Modul 3: Matchmaking Service',
                owner: 'Muhammad Yasir Ilham Nabil',
                title: 'Pencegahan Lamaran Duplikat',
                desc: 'Pelamar dicegah mengirimkan lamaran ganda ke lowongan yang sama untuk menjaga integritas transaksi database.',
                points: [
                    'Harus mengembalikan HTTP 409 Conflict',
                    'Pesan kesalahan menerangkan bahwa lamaran sudah pernah diajukan'
                ]
            },
            'apply_my_dock': {
                name: 'Riwayat Pelamar (My Dock)',
                method: 'GET',
                url: '/api/applications/my',
                body: '',
                role: 'budi@jobharbour.id',
                module: 'Modul 3: Matchmaking Service',
                owner: 'Muhammad Yasir Ilham Nabil',
                title: 'Dasbor Pelacakan Status Lamaran Pelamar',
                desc: 'Menampilkan seluruh lowongan yang pernah dilamar oleh pengguna yang sedang login.',
                points: [
                    'Status 200 OK dengan riwayat tahap lamaran',
                    'Hanya menampilkan lamaran milik pelamar bersangkutan'
                ]
            },
            'pipeline_list': {
                name: 'Daftar Pelamar di Pipeline',
                method: 'GET',
                url: '/api/recruiter/applications',
                body: '',
                role: 'recruiter@jobharbour.id',
                module: 'Modul 4: Recruitment Pipeline (:8004)',
                owner: 'Muhammad Affif',
                title: 'Pipeline Pelamar ATS Recruiter',
                desc: 'Recruiter melihat daftar seluruh kandidat yang melamar pada lowongannya lengkap dengan skor matchmaking.',
                points: [
                    'Menampilkan pelamar terurut per tahap (screening, assessment, interview, offering)',
                    'Hanya menampilkan pelamar untuk lowongan milik recruiter aktif'
                ]
            },
            'pipeline_anti_idor': {
                name: 'Uji Keamanan Anti-IDOR (403)',
                method: 'GET',
                url: '/api/applications/1',
                body: '',
                role: 'recruiter.baru@jobharbour.id',
                module: 'Modul 4: Pipeline Security Guard',
                owner: 'Muhammad Affif',
                title: 'Perlindungan Insecure Direct Object Reference (Anti-IDOR)',
                desc: 'Recruiter dari perusahaan lain atau pelamar asing dicegat ketika mencoba mengakses data pelamar perusahaan rekanan.',
                points: [
                    'Harus mengembalikan HTTP 403 Forbidden',
                    'Mencegah pencurian data kandidat lintas perusahaan'
                ]
            },
            'pipeline_forward_valid': {
                name: 'Transisi Tahap Maju (Valid)',
                method: 'PATCH',
                url: '/api/recruiter/applications/1/stage',
                body: JSON.stringify({
                    stage: 'assessment',
                    reason: 'Lolos seleksi berkas awal dan portofolio'
                }, null, 2),
                role: 'recruiter@jobharbour.id',
                module: 'Modul 4: Pipeline Forward-Only Rules',
                owner: 'Muhammad Affif',
                title: 'Perpindahan Tahap Seleksi Maju',
                desc: 'Memindahkan tahap kandidat dari screening ke assessment sesuai urutan legal alur rekrutmen.',
                points: [
                    'Status 200 OK dengan tahap baru "assessment"',
                    'Tercatat di riwayat audit trail aplikasi'
                ]
            },
            'pipeline_backward_invalid': {
                name: 'Transisi Mundur Ilegal (422)',
                method: 'PATCH',
                url: '/api/recruiter/applications/1/stage',
                body: JSON.stringify({
                    stage: 'screening',
                    reason: 'Mencoba mengembalikan tahap yang sudah di-interview'
                }, null, 2),
                role: 'recruiter@jobharbour.id',
                module: 'Modul 4: Pipeline Integrity Engine',
                owner: 'Muhammad Affif',
                title: 'Pencegahan Transisi Mundur Tidak Sah',
                desc: 'Sistem menolak perpindahan tahap ke belakang demi menjaga integritas data pipeline.',
                points: [
                    'Harus mengembalikan HTTP 422 Unprocessable Content',
                    'Pencegahan manipulasi alur seleksi'
                ]
            },
            'pipeline_add_note': {
                name: 'Tambah Catatan Evaluator',
                method: 'POST',
                url: '/api/recruiter/applications/1/notes',
                body: JSON.stringify({
                    note: 'Kandidat menunjukkan pemahaman arsitektur microservices dan Docker yang sangat solid.'
                }, null, 2),
                role: 'recruiter@jobharbour.id',
                module: 'Modul 4: Recruitment Pipeline',
                owner: 'Muhammad Affif',
                title: 'Catatan Internal Evaluator Recruiter',
                desc: 'Menyimpan evaluasi internal tim hiring yang terlindungi dan tidak terlihat oleh pelamar.',
                points: [
                    'Status 201 Created dengan id catatan baru',
                    'Hanya dapat diakses oleh recruiter pemilik lowongan'
                ]
            },
            'aggregator_summary': {
                name: 'Ringkasan Statistik Eksternal',
                method: 'GET',
                url: '/api/aggregator/summary',
                body: '',
                role: 'admin@jobharbour.id',
                module: 'Modul 5: Eksternal Aggregator & Resilience',
                owner: 'Tim Terintegrasi',
                title: 'Pemantauan Sinkronisasi Lowongan Luar',
                desc: 'Administrator memantau metrik agregasi lowongan dari portal eksternal.',
                points: [
                    'Status 200 OK dengan statistik agregasi',
                    'Hanya dapat diakses oleh role admin'
                ]
            },
            'aggregator_sync': {
                name: 'Sinkronisasi Lowongan Luar',
                method: 'POST',
                url: '/api/aggregator/sync',
                body: JSON.stringify({
                    source: 'external_job_board',
                    force: true
                }, null, 2),
                role: 'admin@jobharbour.id',
                module: 'Modul 5: Ketahanan Sistem Fail-Open',
                owner: 'Tim Terintegrasi',
                title: 'Eksekusi Sinkronisasi Data Eksternal',
                desc: 'Menguji ketahanan sistem saat menghubungi API eksternal dengan mekanisme fallback aman (fail-open).',
                points: [
                    'Status 200 OK dengan metadata hasil sinkronisasi',
                    'Menerapkan pola ketahanan arsitektur microservice'
                ]
            }
        };

        // State Management
        let currentToken = localStorage.getItem('jh_token') || '';
        let currentUser = null;
        try {
            currentUser = JSON.parse(localStorage.getItem('jh_user') || 'null');
        } catch(e) {}
        let currentActiveReqKey = 'catalog_list_boost';

        window.addEventListener('DOMContentLoaded', () => {
            updateRoleUI();
            selectRequest('catalog_list_boost');

            // Global shortcut Ctrl + Enter
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    sendRequest();
                }
            });
        });

        function updateRoleUI() {
            const roleDot = document.getElementById('roleDot');
            const roleText = document.getElementById('roleText');
            const authInput = document.getElementById('inputBearerToken');
            const authPreview = document.getElementById('headerAuthPreview');
            const authDot = document.getElementById('authTabDot');

            if (currentToken) {
                roleDot.className = 'w-2 h-2 rounded-full bg-emerald-400 animate-pulse';
                const role = currentUser?.role || 'user';
                const name = currentUser?.name || currentUser?.email || 'User';
                roleText.textContent = `${name} (${role})`;
                roleText.className = 'text-emerald-300 font-mono-code text-[11px]';
                if (authInput) authInput.value = currentToken;
                if (authPreview) authPreview.textContent = `Bearer ${currentToken.substring(0, 24)}...`;
                if (authDot) authDot.className = 'inline-block w-1.5 h-1.5 rounded-full bg-emerald-400 ml-1';
            } else {
                roleDot.className = 'w-2 h-2 rounded-full bg-zinc-600';
                roleText.textContent = 'Anonim';
                roleText.className = 'text-zinc-500 font-mono-code text-[11px]';
                if (authInput) authInput.value = '';
                if (authPreview) authPreview.textContent = '(Tidak ada token aktif)';
                if (authDot) authDot.className = 'inline-block w-1.5 h-1.5 rounded-full bg-zinc-600 ml-1';
            }
        }

        async function loginPreset(email, password, label, roleHint) {
            const btn = event?.currentTarget;
            const originalText = btn ? btn.innerText : '';
            if (btn) btn.innerText = 'Login...';

            try {
                const res = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                const data = await res.json();
                if (res.ok && data.data?.token) {
                    currentToken = data.data.token;
                    currentUser = data.data.user;
                    localStorage.setItem('jh_token', currentToken);
                    localStorage.setItem('jh_user', JSON.stringify(currentUser));
                    updateRoleUI();
                    showToast(`Berhasil masuk sebagai ${label}! Token Bearer aktif terpasang.`, 'success');
                } else {
                    showToast(`Gagal login: ${data.message || 'Kredensial salah'}`, 'error');
                }
            } catch (err) {
                showToast(`Koneksi gagal: ${err.message}`, 'error');
            } finally {
                if (btn) btn.innerText = originalText;
            }
        }

        function clearToken() {
            currentToken = '';
            currentUser = null;
            localStorage.removeItem('jh_token');
            localStorage.removeItem('jh_user');
            updateRoleUI();
            showToast('Token dihapus. Mode anonim aktif.', 'info');
        }

        function selectRequest(key) {
            const req = ENDPOINTS[key];
            if (!req) return;
            currentActiveReqKey = key;

            // Update top bar
            document.getElementById('tabTitle').textContent = req.name;
            const tabMethodBadge = document.getElementById('tabMethodBadge');
            tabMethodBadge.textContent = req.method;
            tabMethodBadge.className = `font-mono-code font-bold text-[10px] ${getMethodColorClass(req.method)}`;

            // Update inputs
            const methodSelect = document.getElementById('requestMethod');
            methodSelect.value = req.method;
            updateMethodColor(req.method);

            document.getElementById('requestUrl').value = req.url;
            document.getElementById('requestBodyInput').value = req.body || '';

            // Update Rubric tab
            document.getElementById('rubricModuleTag').textContent = req.module;
            document.getElementById('rubricOwner').textContent = req.owner;
            document.getElementById('rubricTitle').textContent = req.title;
            document.getElementById('rubricExplanation').textContent = req.desc;

            const pointsUl = document.getElementById('rubricPoints');
            pointsUl.innerHTML = '';
            (req.points || []).forEach(p => {
                const li = document.createElement('li');
                li.textContent = p;
                pointsUl.appendChild(li);
            });

            // Auto-switch tabs based on method
            if (req.method === 'GET') {
                openSubtab('headers');
            } else {
                openSubtab('body');
            }

            // Highlight sidebar item
            document.querySelectorAll('.req-item').forEach(el => el.classList.remove('bg-zinc-800', 'text-white'));
            event?.currentTarget?.classList?.add('bg-zinc-800', 'text-white');
        }

        function getMethodColorClass(method) {
            switch(method) {
                case 'GET': return 'method-get';
                case 'POST': return 'method-post';
                case 'PUT': return 'method-put';
                case 'PATCH': return 'method-patch';
                case 'DELETE': return 'method-delete';
                default: return 'text-zinc-400';
            }
        }

        function updateMethodColor(method) {
            const select = document.getElementById('requestMethod');
            select.className = `w-full bg-zinc-900 border border-zinc-700 rounded-md py-2 px-2.5 text-xs font-mono-code font-bold outline-none cursor-pointer ${getMethodColorClass(method)}`;
            const tabBadge = document.getElementById('tabMethodBadge');
            if (tabBadge) {
                tabBadge.textContent = method;
                tabBadge.className = `font-mono-code font-bold text-[10px] ${getMethodColorClass(method)}`;
            }
        }

        function openSubtab(tab) {
            ['params', 'auth', 'headers', 'body', 'rubric'].forEach(t => {
                const pane = document.getElementById(`subtab-${t}`);
                const btn = document.getElementById(`subtab-${t}-btn`);
                if (t === tab) {
                    pane?.classList.remove('hidden');
                    pane?.classList.add('flex');
                    btn?.classList.add('tab-active');
                } else {
                    pane?.classList.add('hidden');
                    pane?.classList.remove('flex');
                    btn?.classList.remove('tab-active');
                }
            });
        }

        function toggleFolder(folderId) {
            const folder = document.getElementById(folderId);
            const icon = document.getElementById(`${folderId}-icon`);
            if (folder.classList.contains('hidden')) {
                folder.classList.remove('hidden');
                icon.textContent = '▼';
            } else {
                folder.classList.add('hidden');
                icon.textContent = '▶';
            }
        }

        function filterEndpoints(query) {
            const q = query.toLowerCase().trim();
            document.querySelectorAll('.collection-group').forEach(group => {
                let anyVisible = false;
                group.querySelectorAll('.req-item').forEach(item => {
                    const text = item.textContent.toLowerCase();
                    if (!q || text.includes(q)) {
                        item.classList.remove('hidden');
                        anyVisible = true;
                    } else {
                        item.classList.add('hidden');
                    }
                });
                if (!q || anyVisible) {
                    group.classList.remove('hidden');
                } else {
                    group.classList.add('hidden');
                }
            });
        }

        function toggleParam(key, val, isActive) {
            const urlInput = document.getElementById('requestUrl');
            let [base, qs] = urlInput.value.split('?');
            const params = new URLSearchParams(qs || '');

            if (isActive) {
                params.set(key, val);
            } else {
                params.delete(key);
            }

            const newQs = params.toString();
            urlInput.value = newQs ? `${base}?${newQs}` : base;
        }

        function beautifyJson() {
            const textarea = document.getElementById('requestBodyInput');
            const val = textarea.value.trim();
            if (!val) return;
            try {
                const parsed = JSON.parse(val);
                textarea.value = JSON.stringify(parsed, null, 2);
                document.getElementById('bodyFormatStatus').textContent = 'JSON OK';
                document.getElementById('bodyFormatStatus').className = 'text-[11px] text-emerald-400';
            } catch(e) {
                document.getElementById('bodyFormatStatus').textContent = 'JSON Error: ' + e.message;
                document.getElementById('bodyFormatStatus').className = 'text-[11px] text-rose-400';
            }
        }

        async function sendRequest() {
            const method = document.getElementById('requestMethod').value;
            const url = document.getElementById('requestUrl').value.trim();
            const bodyText = document.getElementById('requestBodyInput').value.trim();
            const btnSend = document.getElementById('btnSendRequest');
            const viewer = document.getElementById('responseBodyViewer');
            const statusBadge = document.getElementById('responseStatusBadge');
            const timeBadge = document.getElementById('responseTimeBadge');
            const sizeBadge = document.getElementById('responseSizeBadge');

            if (!url) {
                alert('Endpoint URL tidak boleh kosong.');
                return;
            }

            const headers = {
                'Accept': 'application/json'
            };

            const customAuth = document.getElementById('inputBearerToken').value.trim();
            if (customAuth) {
                headers['Authorization'] = customAuth.startsWith('Bearer ') ? customAuth : `Bearer ${customAuth}`;
            } else if (currentToken) {
                headers['Authorization'] = `Bearer ${currentToken}`;
            }

            const options = {
                method: method,
                headers: headers
            };

            if (['POST', 'PUT', 'PATCH'].includes(method) && bodyText) {
                try {
                    JSON.parse(bodyText);
                } catch(e) {
                    alert('Payload Body bukan format JSON yang valid:\n' + e.message);
                    return;
                }
                headers['Content-Type'] = 'application/json';
                options.body = bodyText;
            }

            // Loading state
            btnSend.disabled = true;
            btnSend.classList.add('opacity-50');
            statusBadge.textContent = 'Mengirim...';
            statusBadge.className = 'px-2 py-0.5 rounded text-xs font-mono-code font-bold bg-zinc-800 text-orange-400 border border-zinc-700';
            viewer.textContent = 'Menghubungi endpoint microservice...';

            const startTime = performance.now();

            try {
                const res = await fetch(url, options);
                const elapsed = Math.round(performance.now() - startTime);
                timeBadge.textContent = `${elapsed} ms`;

                // Status Code Colors (Postman Standard)
                let badgeClass = 'bg-zinc-800 text-zinc-300 border-zinc-700';
                if (res.status >= 200 && res.status < 300) {
                    badgeClass = 'bg-emerald-500/20 text-emerald-400 border-emerald-500/40';
                } else if (res.status === 403) {
                    badgeClass = 'bg-amber-500/20 text-amber-400 border-amber-500/40';
                } else if (res.status === 409 || res.status === 422) {
                    badgeClass = 'bg-purple-500/20 text-purple-400 border-purple-500/40';
                } else if (res.status >= 400) {
                    badgeClass = 'bg-rose-500/20 text-rose-400 border-rose-500/40';
                }
                statusBadge.className = `px-2 py-0.5 rounded text-xs font-mono-code font-bold border ${badgeClass}`;
                statusBadge.textContent = `${res.status} ${res.statusText || ''}`;

                const text = await res.text();
                sizeBadge.textContent = formatBytes(new Blob([text]).size);

                try {
                    const json = JSON.parse(text);
                    viewer.innerHTML = formatJsonHighlight(json);
                    auditEnvelope(json);
                } catch(e) {
                    viewer.textContent = text;
                    resetEnvelopeAudit();
                }

            } catch (err) {
                const elapsed = Math.round(performance.now() - startTime);
                timeBadge.textContent = `${elapsed} ms`;
                statusBadge.className = 'px-2 py-0.5 rounded text-xs font-mono-code font-bold bg-rose-500/20 text-rose-400 border border-rose-500/40';
                statusBadge.textContent = 'Error';
                viewer.textContent = `Gagal mengirim request:\n${err.message}\n\nPastikan web server JobHarbour sedang aktif di environment lokal.`;
                resetEnvelopeAudit();
            } finally {
                btnSend.disabled = false;
                btnSend.classList.remove('opacity-50');
            }
        }

        function auditEnvelope(data) {
            checkItem('env-status', data && typeof data.status !== 'undefined');
            checkItem('env-message', data && typeof data.message !== 'undefined');
            checkItem('env-data', data && typeof data.data !== 'undefined');
            checkItem('env-meta', data && typeof data.meta !== 'undefined');
        }

        function checkItem(id, ok) {
            const el = document.getElementById(id);
            if (!el) return;
            if (ok) {
                el.className = 'text-emerald-400 font-bold';
                el.innerHTML = `✓ ${id.replace('env-', '')}`;
            } else {
                el.className = 'text-zinc-600 line-through';
                el.innerHTML = `✗ ${id.replace('env-', '')}`;
            }
        }

        function resetEnvelopeAudit() {
            ['env-status', 'env-message', 'env-data', 'env-meta'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.className = 'text-zinc-600';
                    el.innerHTML = id.replace('env-', '');
                }
            });
        }

        function formatJsonHighlight(json) {
            if (typeof json !== 'string') {
                json = JSON.stringify(json, undefined, 2);
            }
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?)/g, function (match) {
                let cls = 'json-number';
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'json-key';
                    } else {
                        cls = 'json-string';
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'json-boolean';
                } else if (/null/.test(match)) {
                    cls = 'json-null';
                }
                return '<span class="' + cls + '">' + match + '</span>';
            });
        }

        function formatBytes(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
        }

        function copyResponse() {
            const viewer = document.getElementById('responseBodyViewer');
            const btn = document.getElementById('btnCopyResponse');
            navigator.clipboard.writeText(viewer.innerText).then(() => {
                const old = btn.innerText;
                btn.innerText = 'Copied!';
                btn.classList.add('text-emerald-400');
                setTimeout(() => {
                    btn.innerText = old;
                    btn.classList.remove('text-emerald-400');
                }, 1500);
            });
        }

        function copyCurl() {
            const method = document.getElementById('requestMethod').value;
            const url = window.location.origin + document.getElementById('requestUrl').value.trim();
            const body = document.getElementById('requestBodyInput').value.trim();
            const btn = document.getElementById('btnCopyCurl');

            let curl = `curl --location --request ${method} '${url}' \\\n  --header 'Accept: application/json'`;

            const customAuth = document.getElementById('inputBearerToken').value.trim();
            if (customAuth) {
                const token = customAuth.startsWith('Bearer ') ? customAuth : `Bearer ${customAuth}`;
                curl += ` \\\n  --header 'Authorization: ${token}'`;
            }

            if (['POST', 'PUT', 'PATCH'].includes(method) && body) {
                curl += ` \\\n  --header 'Content-Type: application/json' \\\n  --data-raw '${body.replace(/'/g, "'\\''")}'`;
            }

            navigator.clipboard.writeText(curl).then(() => {
                const old = btn.innerText;
                btn.innerText = 'cURL Copied!';
                btn.classList.add('text-emerald-400');
                setTimeout(() => {
                    btn.innerText = old;
                    btn.classList.remove('text-emerald-400');
                }, 1500);
            });
        }

        function showToast(msg, type = 'info') {
            const div = document.createElement('div');
            const bg = type === 'success' ? 'bg-emerald-600' : (type === 'error' ? 'bg-rose-600' : 'bg-zinc-700');
            div.className = `fixed bottom-5 right-5 z-50 ${bg} text-white text-xs px-4 py-2.5 rounded-lg shadow-xl transition transform duration-300 ease-out translate-y-4 opacity-0`;
            div.textContent = msg;
            document.body.appendChild(div);
            requestAnimationFrame(() => div.classList.remove('translate-y-4', 'opacity-0'));
            setTimeout(() => {
                div.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => div.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
