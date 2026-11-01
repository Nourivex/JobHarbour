<?php

use Illuminate\Support\Facades\Route;

// Halaman Beranda (Home / Landing Page)
Route::get('/', function () {
    return view('pages.home');
})->name('home');

// Katalog Lowongan Kerja
Route::get('/catalog', function () {
    return view('pages.catalog');
})->name('page.catalog');

// Halaman Masuk & Pendaftaran Akun
Route::get('/login', function () {
    return view('pages.login');
})->name('login');

// Pengajuan Berkas Lamaran & Quick Apply
Route::get('/apply', function () {
    return view('pages.apply');
})->name('page.apply');

// Dasbor Pelamar & Pelacakan Lamaran Saya
Route::get('/my-dock', function () {
    return view('pages.my-dock');
})->name('page.my-dock');

// Profil Pengguna & Kalibrasi Keahlian
Route::get('/profile', function () {
    return view('pages.profile');
})->name('page.profile');

// Papan Kendali Rekruter & Manajemen Pipeline
Route::get('/recruiter', function () {
    return view('pages.recruiter');
})->name('page.recruiter');

// Dokumentasi Swagger / OpenAPI
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

// Stitch Screens Showcase & Previews (dipertahankan)
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
