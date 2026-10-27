<?php

use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobVacancyController;
use App\Http\Controllers\Api\PipelineController;
use App\Http\Controllers\Api\ProfileSkillController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dokumentasi & Peta Rute REST API - Sistem JobHarbour
|--------------------------------------------------------------------------
|
| Filosofi    : Pelabuhan para talenta & pencari kerja sebelum berlayar ke dunia kerja
| Mata Kuliah : Pemrograman Web Service (5537344 / 3 SKS) - Kelompok 4 UHN Tegal
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
| 3. Application & Matchmaking Service (MUHAMMAD YASIR ILHAM NABIL - 23215040)
|    - GET   /api/applications        : Menampilkan daftar lamaran kerja
|    - GET   /api/applications/{id}   : Menampilkan detail berkas lamaran
|    - POST  /api/applications        : Mengirim lamaran + otomatis hitung skor kecocokan
|    - DELETE /api/applications/{id}  : Membatalkan pengajuan berkas lamaran
|
| 4. Recruitment Pipeline Service (MUHAMMAD AFFIF - 24225046)
|    - GET   /api/pipelines/{id}      : Pelamar melihat riwayat linimasa proses lamaran
|    - PATCH /api/pipelines/{id}/status : Recruiter update status / tahap seleksi pelamar
|
*/

// =========================================================================
// 1. Pemeriksaan Status Kesehatan Layanan API (Health Check Endpoint)
// =========================================================================
Route::get('/health', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'Layanan REST API JobHarbour beroperasi normal dan siap menerima request.',
        'data' => [
            'aplikasi' => 'JobHarbour Core API',
            'filosofi' => 'Pelabuhan Para Pekerja Sebelum Keberangkatan',
            'versi' => '1.0.0',
            'modul_tersedia' => [
                'Auth & Profile Service' => ['POST /api/auth/login', 'PUT /api/profile/skills'],
                'Job Catalog Service' => [
                    'GET /api/jobs',
                    'GET /api/jobs/my',
                    'GET /api/jobs/{id}',
                    'POST /api/jobs',
                    'PUT /api/jobs/{id}',
                    'DELETE /api/jobs/{id}'
                ],
                'Application & Matchmaking Service' => [
                    'GET /api/applications',
                    'GET /api/applications/{id}',
                    'GET /api/applications/{id}/cv',
                    'POST /api/applications',
                    'DELETE /api/applications/{id}'
                ],
                'Recruitment Pipeline Service' => ['PATCH /api/pipelines/{id}/status', 'GET /api/pipelines/{id}'],
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

// Method GET: Khusus recruiter untuk melihat daftar lowongan yang ia kelola sendiri
Route::get('/jobs/my', [JobVacancyController::class, 'myJobs'])->middleware('jwt.auth:recruiter');

Route::get('/jobs/{id}', [JobVacancyController::class, 'show']);

// Method POST/PUT/PATCH/DELETE: Manajemen lowongan oleh Recruiter (dengan verifikasi kepemilikan / anti-IDOR)
Route::post('/jobs', [JobVacancyController::class, 'store'])->middleware('jwt.auth:recruiter');
Route::put('/jobs/{id}', [JobVacancyController::class, 'update'])->middleware('jwt.auth:recruiter');
Route::patch('/jobs/{id}', [JobVacancyController::class, 'update'])->middleware('jwt.auth:recruiter');
Route::delete('/jobs/{id}', [JobVacancyController::class, 'destroy'])->middleware('jwt.auth:recruiter');

// =========================================================================
// 4. Service 3: Application & Matchmaking Service
// =========================================================================
Route::middleware('jwt.auth')->group(function () {
    // Melihat daftar lamaran milik sendiri
    Route::get('/applications', [ApplicationController::class, 'index']);
    Route::get('/applications/{id}', [ApplicationController::class, 'show']);

    // Mengunduh / melihat berkas CV PDF lamaran
    Route::get('/applications/{id}/cv', [ApplicationController::class, 'downloadCv']);

    // Method POST: Mengirim berkas lamaran + memicu kalkulasi skor kecocokan secara otomatis (mendukung multipart CV)
    Route::post('/applications', [ApplicationController::class, 'store']);

    // Method DELETE: Membatalkan pengajuan lamaran yang belum disetujui/ditolak
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy']);
});

// =========================================================================
// 5. Service 4: Recruitment Pipeline Service
// =========================================================================
Route::middleware('jwt.auth')->group(function () {
    // Method GET: Pelamar melihat riwayat linimasa dan status tahapan seleksinya
    Route::get('/pipelines/{applicationId}', [PipelineController::class, 'showTimeline']);

    // Method PATCH: Khusus Recruiter untuk memajukan tahapan (Screening -> Assessment -> Interview -> Offering)
    Route::patch('/pipelines/{applicationId}/status', [PipelineController::class, 'updateStatus'])->middleware('jwt.auth:recruiter');
    Route::patch('/applications/{id}/stage', [PipelineController::class, 'updateStatus'])->middleware('jwt.auth:recruiter');
});
