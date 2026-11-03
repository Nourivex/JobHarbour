<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Studio & Demo Console - JobHarbour Microservices</title>
    <link rel="icon" href="/images/favicon-jobharbour.ico" sizes="any">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=JetBrains+Mono:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .font-mono-code { font-family: 'JetBrains Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; }
        .token-badge { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
        .json-key { color: #38bdf8; }
        .json-string { color: #a3e635; }
        .json-number { color: #f59e0b; }
        .json-boolean { color: #ec4899; }
        .json-null { color: #94a3b8; }
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="h-full flex flex-col bg-[#0b1329] text-slate-100 antialiased selection:bg-cyan-500 selection:text-slate-900">

    <!-- Top Navigation Header -->
    <header class="border-b border-slate-800 bg-[#0f172a]/90 backdrop-blur sticky top-0 z-40 px-4 lg:px-6 py-3">
        <div class="max-w-[1700px] mx-auto flex flex-wrap items-center justify-between gap-4">
            <!-- Brand & Tag -->
            <div class="flex items-center gap-3">
                <a href="/" class="flex items-center gap-2.5 group">
                    <img src="/images/logo-jobharbour.webp" width="32" height="30" alt="JobHarbour" class="w-8 h-auto transition-transform group-hover:scale-105">
                    <span class="font-heading font-extrabold text-lg tracking-tight text-white">JobHarbour</span>
                </a>
                <span class="hidden sm:inline-block text-xs font-semibold px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                    API Studio v1.0
                </span>
                <span class="hidden md:inline-block text-xs font-medium px-2 py-0.5 rounded bg-indigo-500/10 text-indigo-300 border border-indigo-500/20">
                    UTS Demo Console
                </span>
            </div>

            <!-- Role Switcher & Live Token Indicator -->
            <div class="flex items-center flex-wrap gap-2">
                <span class="text-xs text-slate-400 hidden xl:inline">Role Cepat:</span>
                <div class="inline-flex rounded-lg bg-slate-800/80 p-1 border border-slate-700/60 text-xs">
                    <button type="button" onclick="loginPreset('budi@jobharbour.id', 'password123', 'Pelamar (Budi)', 'applicant')" class="px-2.5 py-1 rounded-md text-slate-300 hover:text-white hover:bg-slate-700 transition">
                        👤 Budi (Pelamar)
                    </button>
                    <button type="button" onclick="loginPreset('recruiter@jobharbour.id', 'password123', 'Recruiter Resmi (PT Solusi)', 'recruiter')" class="px-2.5 py-1 rounded-md text-slate-300 hover:text-white hover:bg-slate-700 transition">
                        🏢 Recruiter (Resmi)
                    </button>
                    <button type="button" onclick="loginPreset('recruiter.baru@jobharbour.id', 'password123', 'Recruiter Baru (Belum Verif)', 'recruiter')" class="px-2.5 py-1 rounded-md text-slate-300 hover:text-white hover:bg-slate-700 transition">
                        🆕 Recruiter (Unverified)
                    </button>
                    <button type="button" onclick="loginPreset('admin@jobharbour.id', 'password123', 'Administrator', 'admin')" class="px-2.5 py-1 rounded-md text-slate-300 hover:text-white hover:bg-slate-700 transition">
                        🛡️ Admin
                    </button>
                </div>

                <!-- Active Role Status Badge -->
                <div id="activeRoleBadge" class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-800 border border-slate-700 text-xs text-slate-300">
                    <span id="activeDot" class="w-2 h-2 rounded-full bg-slate-500"></span>
                    <span id="activeRoleName">Anonim (Tanpa Token)</span>
                    <button type="button" onclick="clearActiveToken()" title="Hapus Token" class="text-slate-400 hover:text-rose-400 ml-1">✕</button>
                </div>

                <!-- Classic Swagger & Back Links -->
                <a href="/docs" class="inline-flex items-center gap-1 text-xs text-slate-300 hover:text-white bg-slate-800/60 hover:bg-slate-800 border border-slate-700 px-2.5 py-1.5 rounded-lg transition" title="Buka Swagger UI Classic">
                    <span>📖 Swagger UI</span>
                </a>
                <a href="/" class="inline-flex items-center gap-1 text-xs text-slate-300 hover:text-white bg-slate-800/60 hover:bg-slate-800 border border-slate-700 px-2.5 py-1.5 rounded-lg transition">
                    <span>🏠 Web</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Workspace: 3 Column Layout -->
    <main class="flex-1 max-w-[1700px] w-full mx-auto grid grid-cols-1 lg:grid-cols-12 gap-0 overflow-hidden">

        <!-- Column 1: Scenarios Sidebar (3 cols) -->
        <aside class="lg:col-span-3 border-r border-slate-800 bg-[#0c1427] flex flex-col overflow-y-auto max-h-[calc(100vh-61px)] p-3 space-y-4">
            
            <div class="px-2 pt-1 flex items-center justify-between">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-wider text-slate-400">Skenario Ujian UTS</h2>
                    <p class="text-[11px] text-slate-500 mt-0.5">Pilih skenario untuk muat otomatis parameter & body</p>
                </div>
            </div>

            <!-- Group 1: Auth & Profile Service -->
            <div class="space-y-1">
                <div class="flex items-center justify-between px-2 text-[11px] font-semibold text-cyan-400 uppercase tracking-wider">
                    <span>1. Auth & Profile (Nabe'ela)</span>
                    <span class="text-[10px] text-slate-500">:8001</span>
                </div>
                <button type="button" onclick="loadScenario('login_budi')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400">POST</span>
                        <span class="truncate">Login Pelamar (Budi)</span>
                    </span>
                    <span class="text-[10px] text-slate-500 group-hover:text-cyan-400">200 OK</span>
                </button>
                <button type="button" onclick="loadScenario('login_recruiter')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400">POST</span>
                        <span class="truncate">Login Recruiter Resmi</span>
                    </span>
                    <span class="text-[10px] text-slate-500 group-hover:text-cyan-400">200 OK</span>
                </button>
                <button type="button" onclick="loadScenario('register_validation')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400">TEST</span>
                        <span class="truncate">Validasi Sandi &lt; 8 Karakter</span>
                    </span>
                    <span class="text-[10px] text-rose-400">422 Err</span>
                </button>
                <button type="button" onclick="loadScenario('get_profile')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">GET</span>
                        <span class="truncate">Ambil Profil Pengguna Aktif</span>
                    </span>
                    <span class="text-[10px] text-slate-500 group-hover:text-cyan-400">Auth</span>
                </button>
            </div>

            <!-- Group 2: Job Catalog Service -->
            <div class="space-y-1 pt-2 border-t border-slate-800/80">
                <div class="flex items-center justify-between px-2 text-[11px] font-semibold text-emerald-400 uppercase tracking-wider">
                    <span>2. Job Catalog (Fahren)</span>
                    <span class="text-[10px] text-slate-500">:8002</span>
                </div>
                <button type="button" onclick="loadScenario('catalog_boost')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">GET</span>
                        <span class="truncate">Katalog + Search Priority Boost</span>
                    </span>
                    <span class="text-[10px] text-amber-400 font-semibold">★ Boost</span>
                </button>
                <button type="button" onclick="loadScenario('catalog_verified_only')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">GET</span>
                        <span class="truncate">Filter Lowongan Terverifikasi Resmi</span>
                    </span>
                    <span class="text-[10px] text-slate-500 group-hover:text-cyan-400">Filter</span>
                </button>
                <button type="button" onclick="loadScenario('post_job_unverified_trial')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400">POST</span>
                        <span class="truncate">Post Job Trial (Recruiter Baru)</span>
                    </span>
                    <span class="text-[10px] text-blue-400">201 Trial</span>
                </button>
                <button type="button" onclick="loadScenario('post_job_blocked_unverified')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400">TEST</span>
                        <span class="truncate">Post Lowongan Ke-2 Tanpa Verif</span>
                    </span>
                    <span class="text-[10px] text-amber-400 font-bold">403 Cegat</span>
                </button>
            </div>

            <!-- Group 3: Application & Matchmaking Service -->
            <div class="space-y-1 pt-2 border-t border-slate-800/80">
                <div class="flex items-center justify-between px-2 text-[11px] font-semibold text-purple-400 uppercase tracking-wider">
                    <span>3. Matchmaking (Yasir)</span>
                    <span class="text-[10px] text-slate-500">:8003</span>
                </div>
                <button type="button" onclick="loadScenario('submit_application')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400">POST</span>
                        <span class="truncate">Apply Lamaran + Hitung Skor</span>
                    </span>
                    <span class="text-[10px] text-purple-400 font-semibold">Σ Bobot</span>
                </button>
                <button type="button" onclick="loadScenario('duplicate_application')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400">TEST</span>
                        <span class="truncate">Validasi Lamaran Ganda (Duplikat)</span>
                    </span>
                    <span class="text-[10px] text-rose-400">409 Conflict</span>
                </button>
                <button type="button" onclick="loadScenario('my_applications')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">GET</span>
                        <span class="truncate">Riwayat Pelamar (My Dock)</span>
                    </span>
                    <span class="text-[10px] text-slate-500 group-hover:text-cyan-400">200 OK</span>
                </button>
            </div>

            <!-- Group 4: Recruitment Pipeline Service -->
            <div class="space-y-1 pt-2 border-t border-slate-800/80">
                <div class="flex items-center justify-between px-2 text-[11px] font-semibold text-amber-400 uppercase tracking-wider">
                    <span>4. Pipeline ATS (Affif)</span>
                    <span class="text-[10px] text-slate-500">:8004</span>
                </div>
                <button type="button" onclick="loadScenario('recruiter_pipeline')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">GET</span>
                        <span class="truncate">Daftar Pelamar di Pipeline</span>
                    </span>
                    <span class="text-[10px] text-slate-500 group-hover:text-cyan-400">Kanban</span>
                </button>
                <button type="button" onclick="loadScenario('anti_idor_test')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400">TEST</span>
                        <span class="truncate">Uji Keamanan Anti-IDOR</span>
                    </span>
                    <span class="text-[10px] text-rose-400 font-bold">403 IDOR</span>
                </button>
                <button type="button" onclick="loadScenario('stage_transition_valid')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-400">PATCH</span>
                        <span class="truncate">Transisi Tahap Maju (Valid)</span>
                    </span>
                    <span class="text-[10px] text-emerald-400">Screen→Assess</span>
                </button>
                <button type="button" onclick="loadScenario('stage_transition_invalid')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-rose-500/20 text-rose-400">TEST</span>
                        <span class="truncate">Transisi Mundur (Ilegal)</span>
                    </span>
                    <span class="text-[10px] text-rose-400">422 Ilegal</span>
                </button>
                <button type="button" onclick="loadScenario('add_recruiter_note')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400">POST</span>
                        <span class="truncate">Tambah Catatan Internal Lamaran</span>
                    </span>
                    <span class="text-[10px] text-blue-400">201 Note</span>
                </button>
            </div>

            <!-- Group 5: External Aggregator Service -->
            <div class="space-y-1 pt-2 border-t border-slate-800/80">
                <div class="flex items-center justify-between px-2 text-[11px] font-semibold text-rose-400 uppercase tracking-wider">
                    <span>5. Eksternal Aggregator</span>
                    <span class="text-[10px] text-slate-500">Resilience</span>
                </div>
                <button type="button" onclick="loadScenario('aggregator_summary')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400">GET</span>
                        <span class="truncate">Ringkasan Statistik Eksternal</span>
                    </span>
                    <span class="text-[10px] text-slate-500 group-hover:text-cyan-400">Admin</span>
                </button>
                <button type="button" onclick="loadScenario('aggregator_sync')" class="scenario-btn w-full text-left px-2.5 py-2 rounded-lg text-xs hover:bg-slate-800 text-slate-300 flex items-center justify-between group transition">
                    <span class="flex items-center gap-2">
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-400">POST</span>
                        <span class="truncate">Sinkronisasi Lowongan Luar</span>
                    </span>
                    <span class="text-[10px] text-amber-400">Fail-Open</span>
                </button>
            </div>

        </aside>

        <!-- Column 2: Request Console & UTS Rubric Info (5 cols) -->
        <section class="lg:col-span-5 border-r border-slate-800 bg-[#090f1f] flex flex-col overflow-y-auto max-h-[calc(100vh-61px)] p-4 space-y-4">
            
            <!-- URL & Method Bar -->
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-400">Endpoint Request</label>
                <div class="flex items-stretch rounded-xl bg-slate-900 border border-slate-700/80 p-1 focus-within:border-cyan-500 focus-within:ring-1 focus-within:ring-cyan-500">
                    <select id="reqMethod" class="bg-slate-800 text-white font-mono-code font-bold text-xs rounded-lg px-2.5 py-2 border-0 outline-none cursor-pointer">
                        <option value="GET">GET</option>
                        <option value="POST">POST</option>
                        <option value="PUT">PUT</option>
                        <option value="PATCH">PATCH</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                    <input type="text" id="reqUrl" value="/api/jobs" placeholder="/api/endpoint" class="flex-1 bg-transparent px-3 text-xs font-mono-code text-slate-200 outline-none">
                    <button type="button" id="btnSend" onclick="executeRequest()" class="px-4 py-2 bg-gradient-to-r from-cyan-500 to-blue-600 hover:from-cyan-400 hover:to-blue-500 text-slate-950 font-bold text-xs rounded-lg flex items-center gap-1.5 shadow-lg shadow-cyan-500/20 transition cursor-pointer">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/></svg>
                        <span>Kirim</span>
                    </button>
                </div>
            </div>

            <!-- UTS Rubric Guidance Card (Live Explainer) -->
            <div id="rubricBox" class="p-3.5 rounded-xl bg-slate-800/60 border border-slate-700/80 space-y-2">
                <div class="flex items-center justify-between">
                    <span id="rubricTag" class="text-[10px] font-bold px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-300 border border-cyan-500/20 uppercase tracking-wider">
                        Katalog Publik & Search Priority Boost
                    </span>
                    <span class="text-[11px] text-slate-400 font-medium">Rubrik UTS Soal 2</span>
                </div>
                <p id="rubricDesc" class="text-xs text-slate-300 leading-relaxed">
                    Menampilkan daftar lowongan dengan peringkat teratas diprioritaskan bagi perusahaan yang memiliki lencana <code>verified_official</code>.
                </p>
                <div class="flex items-center gap-2 pt-1 text-[11px] text-slate-400 border-t border-slate-700/50">
                    <span class="font-semibold text-slate-300">Target Response:</span>
                    <span id="rubricExpectation" class="font-mono-code text-emerald-400">HTTP 200 OK dengan format standar envelope</span>
                </div>
            </div>

            <!-- Tabs: Headers & Body -->
            <div class="flex-1 flex flex-col space-y-2">
                <div class="flex items-center justify-between border-b border-slate-800">
                    <div class="flex gap-4">
                        <button type="button" id="tabBtnBody" onclick="switchTab('body')" class="pb-2 text-xs font-semibold text-cyan-400 border-b-2 border-cyan-400 transition">Request Body (JSON)</button>
                        <button type="button" id="tabBtnHeaders" onclick="switchTab('headers')" class="pb-2 text-xs font-semibold text-slate-400 hover:text-slate-200 border-b-2 border-transparent transition">Request Headers</button>
                    </div>
                    <button type="button" onclick="formatBodyJson()" class="text-[11px] text-slate-400 hover:text-cyan-400 transition">Rapikan JSON</button>
                </div>

                <!-- Tab 1: Body JSON -->
                <div id="tabBody" class="flex-1 flex flex-col">
                    <textarea id="reqBody" rows="12" spellcheck="false" placeholder="{\n  // Request Body JSON\n}" class="w-full h-full p-3 bg-slate-900 border border-slate-800 rounded-xl font-mono-code text-xs text-slate-200 outline-none focus:border-cyan-500/80 resize-none"></textarea>
                </div>

                <!-- Tab 2: Headers -->
                <div id="tabHeaders" class="hidden flex-1 space-y-2">
                    <div class="space-y-1">
                        <label class="block text-[11px] text-slate-400">Authorization Token (Bearer):</label>
                        <input type="text" id="reqHeaderAuth" placeholder="Bearer eyJhbGciOi..." class="w-full p-2 bg-slate-900 border border-slate-800 rounded-lg font-mono-code text-xs text-slate-200 outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[11px] text-slate-400">Accept:</label>
                            <input type="text" id="reqHeaderAccept" value="application/json" class="w-full p-2 bg-slate-900 border border-slate-800 rounded-lg font-mono-code text-xs text-slate-200 outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-slate-400">Content-Type:</label>
                            <input type="text" id="reqHeaderContentType" value="application/json" class="w-full p-2 bg-slate-900 border border-slate-800 rounded-lg font-mono-code text-xs text-slate-200 outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Footer Hint -->
            <div class="text-[11px] text-slate-500 flex items-center justify-between">
                <span>Tip: Tekan <kbd class="px-1.5 py-0.5 rounded bg-slate-800 text-slate-300 font-mono-code">Ctrl + Enter</kbd> untuk kirim langsung</span>
                <span id="validationHint" class="text-cyan-400">JSON Valid</span>
            </div>

        </section>

        <!-- Column 3: Live Response Inspector (4 cols) -->
        <section class="lg:col-span-4 bg-[#080d1a] flex flex-col overflow-y-auto max-h-[calc(100vh-61px)] p-4 space-y-3">
            
            <!-- Response Meta Bar -->
            <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-400">Response</span>
                    <span id="resStatusBadge" class="px-2 py-0.5 rounded-full text-xs font-mono-code font-bold bg-slate-800 text-slate-400 border border-slate-700">
                        Belum Dijalankan
                    </span>
                </div>
                <div class="flex items-center gap-3">
                    <span id="resTimeBadge" class="text-xs font-mono-code text-slate-400">0 ms</span>
                    <button type="button" onclick="copyResponseJson()" id="btnCopyJson" class="text-xs text-slate-400 hover:text-white px-2 py-1 rounded bg-slate-800/80 hover:bg-slate-800 border border-slate-700 transition">
                        Salin JSON
                    </button>
                </div>
            </div>

            <!-- Envelope Compliance Banner -->
            <div id="envelopeCheck" class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 text-xs flex items-center justify-between">
                <span class="text-slate-400">Kesesuaian Envelope:</span>
                <div class="flex items-center gap-2 font-mono-code text-[11px]">
                    <span id="chkStatus" class="text-slate-600">status</span>
                    <span id="chkMessage" class="text-slate-600">message</span>
                    <span id="chkData" class="text-slate-600">data</span>
                    <span id="chkMeta" class="text-slate-600">meta</span>
                </div>
            </div>

            <!-- Response Body Viewer (Pretty & Highlighted) -->
            <div class="flex-1 flex flex-col min-h-[420px] bg-slate-950 rounded-xl border border-slate-800/90 overflow-hidden">
                <div class="bg-slate-900/80 px-3 py-1.5 border-b border-slate-800 flex items-center justify-between text-[11px] text-slate-400 font-mono-code">
                    <span>application/json</span>
                    <span id="resSizeBadge">0 B</span>
                </div>
                <pre id="resViewer" class="flex-1 p-3 overflow-auto font-mono-code text-xs text-slate-300 leading-relaxed selection:bg-cyan-500/30">
Klik tombol "Kirim" atau pilih salah satu "Skenario Ujian UTS" di sebelah kiri untuk melihat respons langsung dari server.
                </pre>
            </div>

            <!-- Lecturer Verification Checklist -->
            <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/80 text-[11px] text-slate-400 space-y-1.5">
                <div class="font-semibold text-slate-300 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-cyan-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                    <span>Checklist Pengujian Dosen</span>
                </div>
                <p class="leading-relaxed">
                    Sistem mengimplementasikan <strong class="text-slate-200">Database-per-Service</strong> dengan 4 microservices independen, proteksi <strong class="text-slate-200">Anti-IDOR</strong>, <strong class="text-slate-200">Search Priority Boost</strong> untuk perusahaan terverifikasi, dan <strong class="text-slate-200">Matchmaking Engine</strong> berbobot otomatis.
                </p>
            </div>

        </section>

    </main>

    <!-- Scenarios Database & Client-side Script -->
    <script>
        // Preset Scenarios Data
        const SCENARIOS = {
            'login_budi': {
                name: 'Login Pelamar (Budi)',
                method: 'POST',
                url: '/api/auth/login',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: 'budi@jobharbour.id',
                    password: 'password123'
                }, null, 2),
                rubricTag: 'Modul 1: Auth & Profile (Nabe\'ela)',
                rubricTitle: 'Otentikasi & Penerbitan Token JWT',
                rubricDesc: 'Memverifikasi kredensial pelamar dan mengembalikan token JWT yang berisi klaim user_id, email, dan role applicant.',
                rubricExpectation: 'HTTP 200 OK dengan token JWT dan user object',
                requiresAuth: false
            },
            'login_recruiter': {
                name: 'Login Recruiter Resmi',
                method: 'POST',
                url: '/api/auth/login',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: 'recruiter@jobharbour.id',
                    password: 'password123'
                }, null, 2),
                rubricTag: 'Modul 1: Auth & Profile (Nabe\'ela)',
                rubricTitle: 'Login Recruiter Perusahaan Terverifikasi',
                rubricDesc: 'Otentikasi akun recruiter resmi (PT Solusi Teknologi Nusantara) dengan status verifikasi resmi.',
                rubricExpectation: 'HTTP 200 OK dengan role recruiter dan data perusahaan',
                requiresAuth: false
            },
            'register_validation': {
                name: 'Validasi Kata Sandi Minimal 8 Karakter',
                method: 'POST',
                url: '/api/auth/register',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: 'Pengguna Baru',
                    email: 'tester.' + Date.now() + '@jobharbour.id',
                    password: '123',
                    role: 'applicant'
                }, null, 2),
                rubricTag: 'Modul 1: Keamanan & Validasi Input',
                rubricTitle: 'Uji Validasi Form Pendaftaran',
                rubricDesc: 'Menguji ketahanan validasi server. Sandi kurang dari 8 karakter harus ditolak dengan kode validasi 422.',
                rubricExpectation: 'HTTP 422 Unprocessable Content (Password min 8 karakter)',
                requiresAuth: false
            },
            'get_profile': {
                name: 'Ambil Profil Pengguna Aktif',
                method: 'GET',
                url: '/api/auth/me',
                headers: { 'Accept': 'application/json' },
                body: '',
                rubricTag: 'Modul 1: Auth & Profile',
                rubricTitle: 'Pemeriksaan Profil via Token JWT',
                rubricDesc: 'Mengekstrak profil dan keahlian pelamar dari token yang sedang terpasang di header Authorization.',
                rubricExpectation: 'HTTP 200 OK dengan data profil lengkap & keahlian',
                requiresAuth: true
            },
            'catalog_boost': {
                name: 'Katalog Lowongan + Search Priority Boost',
                method: 'GET',
                url: '/api/jobs',
                headers: { 'Accept': 'application/json' },
                body: '',
                rubricTag: 'Modul 2: Job Catalog (Fahren)',
                rubricTitle: 'Algoritma Pemeringkatan Katalog & Prioritas',
                rubricDesc: 'Lowongan dari perusahaan dengan lencana verified_official otomatis ditempatkan di urutan teratas katalog untuk mencegah lowongan bodong.',
                rubricExpectation: 'HTTP 200 OK dengan lowongan terverifikasi di index 0',
                requiresAuth: false
            },
            'catalog_verified_only': {
                name: 'Filter Lowongan Khusus Terverifikasi',
                method: 'GET',
                url: '/api/jobs?verified_only=true',
                headers: { 'Accept': 'application/json' },
                body: '',
                rubricTag: 'Modul 2: Job Catalog Filter',
                rubricTitle: 'Penyaringan Lowongan Terverifikasi Resmi',
                rubricDesc: 'Hanya mengembalikan lowongan dengan verification_status = "verified_official".',
                rubricExpectation: 'HTTP 200 OK dengan seluruh lowongan berstatus terverifikasi',
                requiresAuth: false
            },
            'post_job_unverified_trial': {
                name: 'Buka 1 Lowongan Trial oleh Recruiter Baru',
                method: 'POST',
                url: '/api/jobs',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    title: 'Junior DevOps Engineer (Trial)',
                    description: 'Membantu otomatisasi deployment dan CI/CD pipeline tim JobHarbour.',
                    job_type: 'fulltime',
                    location: 'Tegal / Remote',
                    salary_range: 'Rp 6.000.000 - Rp 9.000.000',
                    deadline: '2026-12-31',
                    skills: [
                        { name: 'Docker', level: 'required' },
                        { name: 'Linux', level: 'important' }
                    ]
                }, null, 2),
                rubricTag: 'Modul 2: Tiered Trust Policy',
                rubricTitle: 'Hak Coba 1 Lowongan Pertama (Trial)',
                rubricDesc: 'Recruiter baru yang belum verifikasi diizinkan mempublikasikan 1 lowongan kerja awal untuk uji coba.',
                rubricExpectation: 'HTTP 201 Created dengan verification_status "regular_standard"',
                requiresAuth: true,
                preferredRole: 'recruiter.baru@jobharbour.id'
            },
            'post_job_blocked_unverified': {
                name: 'Pencegahan Lowongan Ke-2 Tanpa Verifikasi',
                method: 'POST',
                url: '/api/jobs',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    title: 'Senior Software Architect',
                    description: 'Mendesain sistem berskala tinggi.',
                    job_type: 'fulltime',
                    location: 'Jakarta',
                    deadline: '2026-12-31',
                    skills: [{ name: 'Go', level: 'required' }]
                }, null, 2),
                rubricTag: 'Modul 2: Anti-Lowongan Bodong',
                rubricTitle: 'Pencegahan Spam & Penipuan Lowongan',
                rubricDesc: 'Jika recruiter yang belum verifikasi mencoba membuka lowongan kedua, sistem langsung mengembalikan 403 Forbidden dengan pesan wajib verifikasi perusahaan.',
                rubricExpectation: 'HTTP 403 Forbidden (COMPANY_NOT_VERIFIED)',
                requiresAuth: true,
                preferredRole: 'recruiter.baru@jobharbour.id'
            },
            'submit_application': {
                name: 'Ajukan Lamaran + Hitung Skor Otomatis',
                method: 'POST',
                url: '/api/applications',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    job_id: 1,
                    portfolio_url: 'https://github.com/budi-dev/microservices-showcase',
                    cover_letter: 'Saya sangat tertarik bergabung di PT Solusi Teknologi Nusantara karena relevansi keahlian backend saya.'
                }, null, 2),
                rubricTag: 'Modul 3: Matchmaking Engine (Yasir)',
                rubricTitle: 'Formula Pemadanan Keahlian Berbobot',
                rubricDesc: 'Sistem menghitung persentase kecocokan pelamar terhadap lowongan menggunakan formula bobot: required=3, important=2, nice_to_have=1.',
                rubricExpectation: 'HTTP 201 Created atau 409 Conflict jika sudah pernah melamar',
                requiresAuth: true,
                preferredRole: 'budi@jobharbour.id'
            },
            'duplicate_application': {
                name: 'Pencegahan Lamaran Duplikat',
                method: 'POST',
                url: '/api/applications',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    job_id: 1,
                    portfolio_url: 'https://github.com/budi-dev/duplicate-test',
                    cover_letter: 'Mencoba mengirim lamaran kedua pada lowongan yang sama.'
                }, null, 2),
                rubricTag: 'Modul 3: Integritas Data & Transaksi',
                rubricTitle: 'Uji Validasi Lamaran Ganda (Conflict)',
                rubricDesc: 'Pelamar dilarang melamar dua kali pada lowongan yang sama. Server harus merespons 409 Conflict.',
                rubricExpectation: 'HTTP 409 Conflict (Sudah pernah melamar lowongan ini)',
                requiresAuth: true,
                preferredRole: 'budi@jobharbour.id'
            },
            'my_applications': {
                name: 'Riwayat Lamaran Saya (Pelamar)',
                method: 'GET',
                url: '/api/applications/my',
                headers: { 'Accept': 'application/json' },
                body: '',
                rubricTag: 'Modul 3: Dasbor Pelamar (My Dock)',
                rubricTitle: 'Pelacakan Tahap Seleksi Pelamar',
                rubricDesc: 'Menampilkan seluruh lowongan yang pernah dilamar oleh pelamar beserta status tahap terkini.',
                rubricExpectation: 'HTTP 200 OK dengan daftar riwayat lamaran',
                requiresAuth: true,
                preferredRole: 'budi@jobharbour.id'
            },
            'recruiter_pipeline': {
                name: 'Daftar Pelamar di Pipeline Seleksi',
                method: 'GET',
                url: '/api/recruiter/applications',
                headers: { 'Accept': 'application/json' },
                body: '',
                rubricTag: 'Modul 4: Recruitment Pipeline (Affif)',
                rubricTitle: 'Kanban & Manajemen Pelamar Recruiter',
                rubricDesc: 'Recruiter melihat daftar seluruh kandidat yang melamar pada lowongan perusahaannya, lengkap dengan skor matchmaking.',
                rubricExpectation: 'HTTP 200 OK dengan array kandidat terurut',
                requiresAuth: true,
                preferredRole: 'recruiter@jobharbour.id'
            },
            'anti_idor_test': {
                name: 'Uji Keamanan Anti-IDOR (Akses Ilegal)',
                method: 'GET',
                url: '/api/applications/1',
                headers: { 'Accept': 'application/json' },
                body: '',
                rubricTag: 'Modul 4: Keamanan API & Anti-IDOR (Affif)',
                rubricTitle: 'Pencegahan Insecure Direct Object Reference',
                rubricDesc: 'Recruiter dari perusahaan lain dicegat ketika mencoba mengakses data pelamar milik perusahaan rekanan.',
                rubricExpectation: 'HTTP 403 Forbidden saat dicoba oleh recruiter lain',
                requiresAuth: true,
                preferredRole: 'recruiter.baru@jobharbour.id'
            },
            'stage_transition_valid': {
                name: 'Transisi Tahap Seleksi Maju (Valid)',
                method: 'PATCH',
                url: '/api/recruiter/applications/1/stage',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    stage: 'assessment',
                    reason: 'Lolos review berkas portofolio dan CV awal'
                }, null, 2),
                rubricTag: 'Modul 4: Pipeline Forward-Only Rules',
                rubricTitle: 'Perpindahan Tahap Seleksi Rekrutmen',
                rubricDesc: 'Tahap seleksi berpindah dari screening menuju assessment sesuai urutan legal alur ATS.',
                rubricExpectation: 'HTTP 200 OK dengan status baru stage: "assessment"',
                requiresAuth: true,
                preferredRole: 'recruiter@jobharbour.id'
            },
            'stage_transition_invalid': {
                name: 'Transisi Mundur Ilegal (Pencegahan)',
                method: 'PATCH',
                url: '/api/recruiter/applications/1/stage',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    stage: 'screening',
                    reason: 'Mencoba mengembalikan tahap yang sudah di-interview'
                }, null, 2),
                rubricTag: 'Modul 4: Pipeline Integrity Engine',
                rubricTitle: 'Pencegahan Transisi Mundur Tidak Sah',
                rubricDesc: 'Sistem menolak perpindahan tahap ke belakang untuk menjaga konsistensi audit trail rekrutmen.',
                rubricExpectation: 'HTTP 422 Unprocessable Content (Transisi tidak valid)',
                requiresAuth: true,
                preferredRole: 'recruiter@jobharbour.id'
            },
            'add_recruiter_note': {
                name: 'Tambah Catatan Internal Recruiter',
                method: 'POST',
                url: '/api/recruiter/applications/1/notes',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    note: 'Kandidat memiliki penguasaan arsitektur microservices dan Docker yang sangat solid saat demonstrasi kode.'
                }, null, 2),
                rubricTag: 'Modul 4: Kolaborasi Evaluator',
                rubricTitle: 'Pencatatan Evaluasi Internal',
                rubricDesc: 'Menyimpan catatan internal khusus tim recruiter yang tidak terlihat oleh pelamar.',
                rubricExpectation: 'HTTP 201 Created dengan id catatan baru',
                requiresAuth: true,
                preferredRole: 'recruiter@jobharbour.id'
            },
            'aggregator_summary': {
                name: 'Statistik Aggregator Eksternal',
                method: 'GET',
                url: '/api/aggregator/summary',
                headers: { 'Accept': 'application/json' },
                body: '',
                rubricTag: 'Modul 5: External Aggregator & Resilience',
                rubricTitle: 'Pemantauan Sinkronisasi Lowongan Luar',
                rubricDesc: 'Administrator memantau metrik agregasi lowongan dari portal eksternal.',
                rubricExpectation: 'HTTP 200 OK dengan ringkasan status agregasi',
                requiresAuth: true,
                preferredRole: 'admin@jobharbour.id'
            },
            'aggregator_sync': {
                name: 'Sinkronisasi Aggregator (Fail-Open)',
                method: 'POST',
                url: '/api/aggregator/sync',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    source: 'external_job_board',
                    force: true
                }, null, 2),
                rubricTag: 'Modul 5: Pola Ketahanan Sistem (Circuit/Fail-Open)',
                rubricTitle: 'Eksekusi Sinkronisasi Data Eksternal',
                rubricDesc: 'Menguji ketahanan sistem saat menghubungi API eksternal dengan mekanisme fallback aman.',
                rubricExpectation: 'HTTP 200 OK dengan metadata hasil sinkronisasi',
                requiresAuth: true,
                preferredRole: 'admin@jobharbour.id'
            }
        };

        // State
        let activeToken = localStorage.getItem('jh_token') || '';
        let activeUser = null;
        try {
            activeUser = JSON.parse(localStorage.getItem('jh_user') || 'null');
        } catch(e) {}

        // Initialization
        window.addEventListener('DOMContentLoaded', () => {
            updateTokenUI();

            // Shortcut Ctrl + Enter
            window.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    executeRequest();
                }
            });

            // Auto-load default catalog scenario
            loadScenario('catalog_boost');
        });

        function updateTokenUI() {
            const roleBadge = document.getElementById('activeRoleBadge');
            const dot = document.getElementById('activeDot');
            const name = document.getElementById('activeRoleName');
            const authInput = document.getElementById('reqHeaderAuth');

            if (activeToken) {
                dot.className = 'w-2 h-2 rounded-full bg-emerald-400 token-badge';
                const role = activeUser?.role || 'pengguna';
                const email = activeUser?.email || 'terotentikasi';
                name.textContent = `${email} (${role})`;
                name.className = 'text-emerald-300 font-medium font-mono-code text-[11px]';
                if (authInput) authInput.value = `Bearer ${activeToken}`;
            } else {
                dot.className = 'w-2 h-2 rounded-full bg-slate-500';
                name.textContent = 'Anonim (Tanpa Token)';
                name.className = 'text-slate-400';
                if (authInput) authInput.value = '';
            }
        }

        async function loginPreset(email, password, label, roleHint) {
            const btn = event?.currentTarget;
            const originalText = btn ? btn.innerText : '';
            if (btn) btn.innerText = 'Masuk...';

            try {
                const res = await fetch('/api/auth/login', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                const data = await res.json();
                if (res.ok && data.data?.token) {
                    activeToken = data.data.token;
                    activeUser = data.data.user;
                    localStorage.setItem('jh_token', activeToken);
                    localStorage.setItem('jh_user', JSON.stringify(activeUser));
                    updateTokenUI();
                    
                    showNotification(`Berhasil masuk sebagai ${label}! Token JWT aktif dipasang.`, 'success');
                } else {
                    showNotification(`Gagal masuk: ${data.message || 'Kredensial salah'}`, 'error');
                }
            } catch (err) {
                showNotification(`Koneksi error: ${err.message}`, 'error');
            } finally {
                if (btn) btn.innerText = originalText;
            }
        }

        function clearActiveToken() {
            activeToken = '';
            activeUser = null;
            localStorage.removeItem('jh_token');
            localStorage.removeItem('jh_user');
            updateTokenUI();
            showNotification('Token aktif dihapus. Sekarang berstatus anonim.', 'info');
        }

        function loadScenario(key) {
            const sc = SCENARIOS[key];
            if (!sc) return;

            document.getElementById('reqMethod').value = sc.method;
            document.getElementById('reqUrl').value = sc.url;
            document.getElementById('reqBody').value = sc.body || '';

            // Update Rubric Box
            document.getElementById('rubricTag').textContent = sc.rubricTag;
            document.getElementById('rubricDesc').innerHTML = `<strong>${sc.rubricTitle}:</strong> ${sc.rubricDesc}`;
            document.getElementById('rubricExpectation').textContent = sc.rubricExpectation;

            // Update Headers Tab
            if (activeToken && sc.requiresAuth) {
                document.getElementById('reqHeaderAuth').value = `Bearer ${activeToken}`;
            }

            // Highlight Scenario button
            document.querySelectorAll('.scenario-btn').forEach(b => {
                b.classList.remove('bg-slate-800', 'border', 'border-cyan-500/30');
            });
            event?.currentTarget?.classList?.add('bg-slate-800', 'border', 'border-cyan-500/30');

            // Switch to appropriate tab
            if (sc.method === 'GET') {
                switchTab('headers');
            } else {
                switchTab('body');
            }
        }

        function switchTab(tab) {
            const tabBody = document.getElementById('tabBody');
            const tabHeaders = document.getElementById('tabHeaders');
            const btnBody = document.getElementById('tabBtnBody');
            const btnHeaders = document.getElementById('tabBtnHeaders');

            if (tab === 'body') {
                tabBody.classList.remove('hidden');
                tabHeaders.classList.add('hidden');
                btnBody.className = 'pb-2 text-xs font-semibold text-cyan-400 border-b-2 border-cyan-400 transition';
                btnHeaders.className = 'pb-2 text-xs font-semibold text-slate-400 hover:text-slate-200 border-b-2 border-transparent transition';
            } else {
                tabBody.classList.add('hidden');
                tabHeaders.classList.remove('hidden');
                btnBody.className = 'pb-2 text-xs font-semibold text-slate-400 hover:text-slate-200 border-b-2 border-transparent transition';
                btnHeaders.className = 'pb-2 text-xs font-semibold text-cyan-400 border-b-2 border-cyan-400 transition';
            }
        }

        function formatBodyJson() {
            const area = document.getElementById('reqBody');
            const val = area.value.trim();
            if (!val) return;
            try {
                const parsed = JSON.parse(val);
                area.value = JSON.stringify(parsed, null, 2);
                document.getElementById('validationHint').textContent = 'JSON Valid';
                document.getElementById('validationHint').className = 'text-cyan-400';
            } catch (e) {
                document.getElementById('validationHint').textContent = 'JSON Tidak Valid: ' + e.message;
                document.getElementById('validationHint').className = 'text-rose-400';
            }
        }

        async function executeRequest() {
            const method = document.getElementById('reqMethod').value;
            const url = document.getElementById('reqUrl').value.trim();
            const bodyText = document.getElementById('reqBody').value.trim();
            const btnSend = document.getElementById('btnSend');
            const resViewer = document.getElementById('resViewer');
            const statusBadge = document.getElementById('resStatusBadge');
            const timeBadge = document.getElementById('resTimeBadge');
            const sizeBadge = document.getElementById('resSizeBadge');

            if (!url) {
                alert('Silakan masukkan endpoint URL.');
                return;
            }

            // Headers
            const headers = {
                'Accept': document.getElementById('reqHeaderAccept').value || 'application/json'
            };
            
            const customAuth = document.getElementById('reqHeaderAuth').value.trim();
            if (customAuth) {
                headers['Authorization'] = customAuth;
            } else if (activeToken) {
                headers['Authorization'] = `Bearer ${activeToken}`;
            }

            const fetchOptions = {
                method: method,
                headers: headers
            };

            if (['POST', 'PUT', 'PATCH'].includes(method) && bodyText) {
                try {
                    JSON.parse(bodyText);
                } catch(e) {
                    alert('Request Body bukan format JSON yang valid:\n' + e.message);
                    return;
                }
                headers['Content-Type'] = 'application/json';
                fetchOptions.body = bodyText;
            }

            // UI Loading state
            btnSend.disabled = true;
            btnSend.classList.add('opacity-50');
            statusBadge.className = 'px-2 py-0.5 rounded-full text-xs font-mono-code font-bold bg-slate-800 text-cyan-300';
            statusBadge.textContent = 'Memuat...';
            resViewer.textContent = 'Menghubungi layanan microservice...';

            const startTime = performance.now();

            try {
                const res = await fetch(url, fetchOptions);
                const duration = Math.round(performance.now() - startTime);
                timeBadge.textContent = `${duration} ms`;

                // Status styling
                let badgeClass = 'bg-slate-800 text-slate-300';
                if (res.status >= 200 && res.status < 300) {
                    badgeClass = 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/40';
                } else if (res.status === 403) {
                    badgeClass = 'bg-amber-500/20 text-amber-400 border border-amber-500/40';
                } else if (res.status === 409 || res.status === 422) {
                    badgeClass = 'bg-purple-500/20 text-purple-400 border border-purple-500/40';
                } else if (res.status >= 400) {
                    badgeClass = 'bg-rose-500/20 text-rose-400 border border-rose-500/40';
                }
                statusBadge.className = `px-2 py-0.5 rounded-full text-xs font-mono-code font-bold ${badgeClass}`;
                statusBadge.textContent = `${res.status} ${res.statusText || ''}`;

                const rawText = await res.text();
                sizeBadge.textContent = formatBytes(new Blob([rawText]).size);

                try {
                    const json = JSON.parse(rawText);
                    resViewer.innerHTML = syntaxHighlightJson(json);
                    verifyEnvelope(json);
                } catch(e) {
                    resViewer.textContent = rawText;
                    resetEnvelope();
                }

            } catch (err) {
                const duration = Math.round(performance.now() - startTime);
                timeBadge.textContent = `${duration} ms`;
                statusBadge.className = 'px-2 py-0.5 rounded-full text-xs font-mono-code font-bold bg-rose-500/20 text-rose-400';
                statusBadge.textContent = 'Network Error';
                resViewer.textContent = `Gagal mengirim request ke server:\n${err.message}\n\nPastikan server lokal JobHarbour sedang aktif.`;
                resetEnvelope();
            } finally {
                btnSend.disabled = false;
                btnSend.classList.remove('opacity-50');
            }
        }

        function verifyEnvelope(data) {
            const hasStatus = data && typeof data.status !== 'undefined';
            const hasMessage = data && typeof data.message !== 'undefined';
            const hasData = data && typeof data.data !== 'undefined';
            const hasMeta = data && typeof data.meta !== 'undefined';

            setCheckTag('chkStatus', hasStatus);
            setCheckTag('chkMessage', hasMessage);
            setCheckTag('chkData', hasData);
            setCheckTag('chkMeta', hasMeta);
        }

        function setCheckTag(elementId, isValid) {
            const el = document.getElementById(elementId);
            if (!el) return;
            if (isValid) {
                el.className = 'text-emerald-400 font-bold';
                el.innerHTML = `✓ ${elementId.replace('chk', '').toLowerCase()}`;
            } else {
                el.className = 'text-slate-600 line-through';
                el.innerHTML = `✗ ${elementId.replace('chk', '').toLowerCase()}`;
            }
        }

        function resetEnvelope() {
            ['chkStatus', 'chkMessage', 'chkData', 'chkMeta'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.className = 'text-slate-600';
                    el.innerHTML = id.replace('chk', '').toLowerCase();
                }
            });
        }

        function syntaxHighlightJson(json) {
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

        function copyResponseJson() {
            const viewer = document.getElementById('resViewer');
            const btn = document.getElementById('btnCopyJson');
            navigator.clipboard.writeText(viewer.innerText).then(() => {
                const old = btn.innerText;
                btn.innerText = 'Tersalin!';
                btn.classList.add('text-emerald-400');
                setTimeout(() => {
                    btn.innerText = old;
                    btn.classList.remove('text-emerald-400');
                }, 1500);
            });
        }

        function showNotification(msg, type = 'info') {
            const div = document.createElement('div');
            const color = type === 'success' ? 'bg-emerald-600' : (type === 'error' ? 'bg-rose-600' : 'bg-slate-700');
            div.className = `fixed bottom-5 right-5 z-50 ${color} text-white text-xs px-4 py-2.5 rounded-xl shadow-2xl transition transform duration-300 ease-out translate-y-4 opacity-0`;
            div.textContent = msg;
            document.body.appendChild(div);
            requestAnimationFrame(() => {
                div.classList.remove('translate-y-4', 'opacity-0');
            });
            setTimeout(() => {
                div.classList.add('translate-y-4', 'opacity-0');
                setTimeout(() => div.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
