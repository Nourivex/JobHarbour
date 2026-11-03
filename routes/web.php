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

// Detail Lowongan (halaman penuh)
Route::get('/jobs/{id}', function (int $id) {
    return view('pages.job-detail', ['jobId' => $id]);
})->whereNumber('id')->name('page.job-detail');

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

// Portal Recruiter (workspace dengan sidebar). Hak akses diperiksa di sisi klien lewat token JWT,
// dan setiap data tetap dilindungi middleware jwt.auth di API.
Route::prefix('recruiter')->name('recruiter.')->group(function () {
    Route::view('/', 'recruiter.dashboard')->name('dashboard');
    Route::view('/jobs', 'recruiter.jobs')->name('jobs');
    Route::get('/jobs/{id}', fn (int $id) => view('recruiter.job-detail', ['jobId' => $id]))->whereNumber('id')->name('job');
    Route::view('/pipeline', 'recruiter.pipeline')->name('pipeline');
    Route::view('/interviews', 'recruiter.interviews')->name('interviews');
    Route::view('/analytics', 'recruiter.analytics')->name('analytics');
    Route::get('/candidates/{id}', fn (int $id) => view('recruiter.candidate', ['applicationId' => $id]))->whereNumber('id')->name('candidate');
    Route::view('/company', 'recruiter.company')->name('company');
    Route::view('/verification', 'recruiter.verification')->name('verification');
    Route::view('/settings', 'recruiter.settings')->name('settings');
});

// Pemeriksaan verifikasi perusahaan oleh admin
Route::view('/admin/verifications', 'recruiter.admin-verifications')->name('admin.verifications');

// Dokumentasi Swagger / OpenAPI
Route::get('/docs', function () {
    return view('swagger');
})->name('swagger.docs');

Route::get('/api/documentation', function () {
    return view('swagger');
})->name('swagger.docs.alias');

// API Studio & Interactive Demo Console (kembaran modern Swagger UI untuk demonstrasi UTS)
Route::view('/api-studio', 'api-studio')->name('api.studio');
Route::view('/docs/studio', 'api-studio')->name('api.studio.docs');

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
