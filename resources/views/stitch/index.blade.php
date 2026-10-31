<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JobHarbour - Stitch Screens Gallery</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6 md:p-10">
    <div class="max-w-7xl mx-auto">
        <header class="mb-10 pb-6 border-b border-slate-800 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="px-3 py-1 bg-cyan-500/20 text-cyan-400 text-xs font-semibold rounded-full border border-cyan-500/30">Google Stitch MCP</span>
                    <span class="text-xs text-slate-400">Project ID: 9362254443119260640</span>
                </div>
                <h1 class="text-3xl font-bold mt-2 text-white">JobHarbour Screen Assets Gallery</h1>
                <p class="text-slate-400 text-sm mt-1">Daftar layar, kode HTML, dan screenshot desain yang berhasil ditarik dari Google Stitch.</p>
            </div>
            <div class="flex gap-3">
                <a href="/" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-sm font-medium rounded-lg transition">Halaman Utama</a>
                <a href="/catalog" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-sm font-medium rounded-lg text-white transition">Katalog Lowongan</a>
            </div>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($screens as $screen)
            <div class="bg-slate-800/80 rounded-2xl border border-slate-700 overflow-hidden flex flex-col hover:border-cyan-500/50 transition duration-200">
                <div class="aspect-video bg-slate-950 overflow-hidden relative group">
                    @if (!empty($screen['imagePath']))
                    <img src="/{{ $screen['imagePath'] }}" alt="{{ $screen['title'] }}" class="w-full h-full object-cover object-top transition duration-300 group-hover:scale-105">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-slate-600 text-sm">Tidak ada screenshot</div>
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent opacity-60"></div>
                    <span class="absolute top-3 left-3 px-2.5 py-1 bg-slate-900/80 backdrop-blur text-xs font-mono text-cyan-300 rounded border border-slate-700">
                        {{ $screen['deviceType'] ?? 'DESKTOP' }}
                    </span>
                </div>

                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-mono text-slate-400 mb-1 truncate">ID: {{ $screen['id'] }}</div>
                        <h2 class="text-lg font-semibold text-white leading-snug">{{ $screen['requestedTitle'] ?? $screen['title'] }}</h2>
                        <p class="text-xs text-slate-400 mt-1">Judul Asli Stitch: {{ $screen['title'] }}</p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-700/60 flex flex-wrap gap-2">
                        <a href="/stitch/{{ $screen['slug'] }}" target="_blank" class="flex-1 text-center px-3 py-2 bg-cyan-600 hover:bg-cyan-500 text-xs font-medium text-white rounded-lg transition">
                            Buka Render Blade
                        </a>
                        <a href="/{{ $screen['htmlPath'] }}" target="_blank" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-xs font-medium text-slate-200 rounded-lg transition">
                            Raw HTML
                        </a>
                        @if (!empty($screen['imagePath']))
                        <a href="/{{ $screen['imagePath'] }}" target="_blank" class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-xs font-medium text-slate-200 rounded-lg transition">
                            Gambar
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>

