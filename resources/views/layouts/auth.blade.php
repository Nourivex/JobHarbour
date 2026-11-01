<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title ?? 'Masuk atau Daftar - JobHarbour' }}</title>

    <!-- Google Fonts: Bricolage Grotesque (700, 800) & Inter (400, 500, 600, 700) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,700;12..96,800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Favicon: logo perahu layar JobHarbour -->
    <link rel="icon" href="/images/favicon-jobharbour.ico" sizes="any">
    <link rel="apple-touch-icon" href="/images/logo-icon-256.png">
    <script>try { var t = localStorage.getItem('jh_theme'); if (t === 'dark') document.documentElement.classList.add('dark'); } catch (e) {}</script>
    <link rel="preload" as="image" href="/images/auth-lighthouse.webp" type="image/webp">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="auth-body min-h-full bg-[#EEF2F6] text-[#0B1B2B]">
    {{ $slot }}
    @stack('scripts')
</body>
</html>
