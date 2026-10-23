<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Dokumentasi API - {{ config('app.name', 'PaduKerja') }}</title>

    <link
        rel="icon"
        href="{{ asset('logo.png') }}"
        type="image/png"
    >

    {{-- Swagger UI v5 resmi --}}
    <link
        rel="stylesheet"
        href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css"
    >

    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            background: #f8fafc;
            color: #0f172a;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }

        .pk-topbar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 16px 24px;
            border-bottom: 1px solid #e2e8f0;
            background: #0f172a;
            color: #f8fafc;
        }

        .pk-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .01em;
        }

        .pk-brand span.pk-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: 999px;
            background: rgba(34, 211, 238, .15);
            color: #67e8f9;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .pk-links {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .pk-links a {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid rgba(148, 163, 184, .35);
            color: #e2e8f0;
            text-decoration: none;
            transition: background .15s ease, border-color .15s ease;
        }

        .pk-links a:hover {
            background: rgba(148, 163, 184, .15);
            border-color: rgba(148, 163, 184, .6);
        }

        .pk-main {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px 16px 64px;
        }

        .pk-hint {
            margin: 0 0 16px;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #ffffff;
            font-size: 13px;
            line-height: 1.6;
            color: #475569;
        }

        .pk-hint code {
            padding: 2px 6px;
            border-radius: 6px;
            background: #f1f5f9;
            color: #0f172a;
            font-size: 12px;
        }

        #swagger-ui {
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            background: #ffffff;
            overflow: hidden;
        }

        /* Sembunyikan topbar bawaan Swagger UI, kita pakai topbar sendiri */
        .swagger-ui .topbar {
            display: none;
        }

        .swagger-ui .info {
            margin: 28px 0 12px;
        }

        .swagger-ui .info .title {
            font-size: 28px;
        }

        .swagger-ui .scheme-container {
            box-shadow: none;
            border-bottom: 1px solid #e2e8f0;
        }

        @media (max-width: 640px) {
            .pk-topbar {
                padding: 14px 16px;
            }

            .pk-brand {
                font-size: 14px;
            }

            .pk-main {
                padding: 14px 10px 48px;
            }

            .swagger-ui .info .title {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <header class="pk-topbar">
        <div class="pk-brand">
            <span>PaduKerja API Docs</span>
            <span class="pk-badge">OpenAPI 3.1</span>
        </div>

        <nav class="pk-links">
            <a href="{{ url('/') }}">Beranda</a>
            <a href="{{ url('/api/docs/openapi.yaml') }}">openapi.yaml</a>
        </nav>
    </header>

    <main class="pk-main">
        <p class="pk-hint">
            Spesifikasi dimuat langsung dari rute lokal
            <code>/api/docs/openapi.yaml</code>. Gunakan tombol <strong>Authorize</strong>
            untuk menempelkan token Bearer, lalu coba endpoint langsung dari halaman ini.
        </p>

        <div id="swagger-ui"></div>
    </main>

    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js" crossorigin></script>
    <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-standalone-preset.js" crossorigin></script>

    <script>
        window.addEventListener('load', function () {
            window.ui = SwaggerUIBundle({
                url: "{{ url('/api/docs/openapi.yaml') }}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                docExpansion: "list",
                defaultModelsExpandDepth: -1,
                displayRequestDuration: true,
                tryItOutEnabled: true,
                persistAuthorization: true
            });
        });
    </script>
</body>
</html>
