<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobVacancyController;
use App\Http\Controllers\Api\ProfileSkillController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dokumentasi & Peta Rute REST API - Sistem PaduKerja
|--------------------------------------------------------------------------
|
| Mata Kuliah : Pemrograman Web Service (5537344 / 3 SKS) - Kelompok 4 UHN
| Standar     : RESTful JSON API (RFC 8259), HTTP Methods Semantics, JWT Bearer Token
|
| Pembagian Modul Microservices:
| 1. Auth & Profile Service (NABE'ELA AYU NING TYAS ZAHRA - 23215052)
|    - POST  /api/auth/login          : Login akun pengguna & generate token akses JWT
|    - POST  /api/auth/register       : Registrasi akun baru (applicant / recruiter)
|    - GET   /api/auth/me             : Mengambil profil lengkap user saat ini
|    - PUT   /api/profile/skills      : Menyimpan / update data keahlian (skills) di profil
|    - PUT   /api/users/{id}/skills   : Update keahlian pengguna berdasarkan ID
|
| 2. Job Catalog Service (M. FAHREN ANDREAN RANGKUTI - 23215030)
|    - GET   /api/jobs                : Menampilkan & filter daftar lowongan kerja
|    - GET   /api/jobs/{id}           : Menampilkan detail satu lowongan kerja
|    - POST  /api/jobs                : Recruiter memposting lowongan kerja baru
|
*/

// =========================================================================
// 1. Pemeriksaan Status Kesehatan Layanan API (Health Check Endpoint)
// =========================================================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Layanan REST API PaduKerja beroperasi normal dan siap menerima request.',
        'data' => [
            'aplikasi' => 'PaduKerja Core API',
            'versi' => '1.0.0',
            'modul_tersedia' => [
                'Auth & Profile Service' => ['POST /api/auth/login', 'PUT /api/profile/skills'],
                'Job Catalog Service' => ['GET /api/jobs', 'POST /api/jobs'],
            ],
        ],
        'meta' => [
            'timestamp' => now()->toIso8601String(),
            'version' => '1.0.0',
        ],
    ]);
});

// =========================================================================
// 2. Service 1: Auth & Profile Service
// =========================================================================
Route::prefix('auth')->group(function () {
    // Rute Publik (Tanpa Token JWT)
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);

    // Rute Terproteksi (Wajib Header: Authorization: Bearer <token>)
    Route::get('/me', [AuthController::class, 'me'])->middleware('jwt.auth');
});

Route::middleware('jwt.auth')->group(function () {
    // Method PUT: Menyimpan atau memperbarui kumpulan data keahlian (skills) di profil pengguna
    Route::put('/profile/skills', [ProfileSkillController::class, 'updateSkills']);
    Route::put('/users/{id}/skills', [ProfileSkillController::class, 'updateSkills']);
});

// =========================================================================
// 3. Service 2: Job Catalog Service
// =========================================================================
// Method GET: Menampilkan dan memfilter lowongan (Dapat diakses publik atau terotentikasi)
Route::get('/jobs', [JobVacancyController::class, 'index']);
Route::get('/jobs/{id}', [JobVacancyController::class, 'show']);

// Method POST: Khusus peran 'recruiter' untuk menerbitkan lowongan pekerjaan baru
Route::post('/jobs', [JobVacancyController::class, 'store'])->middleware('jwt.auth:recruiter');
