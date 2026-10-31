<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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

