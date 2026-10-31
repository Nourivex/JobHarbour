<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Landing page = katalog lowongan (sudah tersambung ke API /api/jobs)
    return view('pages.catalog');
});

Route::get('/docs', function () {
    return view('swagger');
})->name('swagger.docs');

Route::get('/api/documentation', function () {
    return view('swagger');
})->name('swagger.docs.alias');

Route::get('/api/docs/openapi.yaml', function () {
    $path = base_path('docs/openapi.yaml');

    abort_unless(file_exists($path), 404, 'Spesifikasi OpenAPI tidak ditemukan.');

    return response()->file($path, [
        'Content-Type' => 'text/yaml; charset=utf-8',
    ]);
})->name('swagger.spec');

// Halaman-halaman Frontend Modern Harbour Tech
// Catatan: semua halaman ini ngobrol ke backend lewat REST API (/api/*) pakai public/js/jh-api.js
Route::get('/login', function () {
    return view('pages.login');
})->name('login');

Route::get('/catalog', function () {
    return view('pages.catalog');
})->name('page.catalog');

Route::get('/apply', function () {
    return view('pages.apply-modal');
})->name('page.apply');

Route::get('/my-dock', function () {
    return view('pages.my-dock');
})->name('page.my-dock');

Route::get('/profile', function () {
    return view('pages.profile');
})->name('page.profile');

Route::get('/recruiter', function () {
    return view('pages.recruiter');
})->name('page.recruiter');

// Stitch Screens Showcase & Previews
Route::get('/stitch', function () {
    $manifestPath = public_path('stitch/stitch_manifest.json');
    $screens = file_exists($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : [];
    return view('stitch.index', compact('screens'));
})->name('stitch.index');

Route::get('/stitch/{slug}', function ($slug) {
    if (view()->exists("stitch.{$slug}")) {
        return view("stitch.{$slug}");
    }
    abort(404, "Screen {$slug} tidak ditemukan.");
})->name('stitch.screen');

// Fallback melayani file statis JS di public/js jika server meneruskan request ke router Laravel
Route::get('/js/{path}', function (string $path) {
    $fullPath = public_path('js/'.$path);
    abort_unless(is_file($fullPath), 404);

    return response(file_get_contents($fullPath), 200, [
        'Content-Type' => 'application/javascript; charset=utf-8',
        'Cache-Control' => 'no-store, no-cache, must-revalidate',
    ]);
})->where('path', '.*');



