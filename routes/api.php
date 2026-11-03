<?php

use App\Http\Controllers\Api\AdminJobController;
use App\Http\Controllers\Api\AdminOverviewController;
use App\Http\Controllers\Api\AdminReportController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AdminVerificationController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\ApplicationNoteController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\JobVacancyController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PipelineController;
use App\Http\Controllers\Api\ProfileSkillController;
use App\Http\Controllers\Api\ReportController;
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
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('/register', [AuthController::class, 'register']);

    // Rute Terproteksi (Wajib Header: Authorization: Bearer <token>)
    Route::get('/me', [AuthController::class, 'me'])->middleware('jwt.auth');
    Route::put('/password', [AuthController::class, 'changePassword'])->middleware('jwt.auth');
});

// Profil perusahaan dan pengajuan verifikasi (khusus recruiter)
Route::middleware('jwt.auth:recruiter')->group(function () {
    Route::get('/company', [CompanyController::class, 'show']);
    Route::put('/company', [CompanyController::class, 'update']);
    Route::post('/company/logo', [CompanyController::class, 'uploadLogo']);
    Route::get('/company/verification', [CompanyController::class, 'verification']);
    Route::post('/company/verification', [CompanyController::class, 'submitVerification']);
});

// Logo perusahaan bersifat publik karena tampil di kartu lowongan
Route::get('/companies/{id}/logo', [CompanyController::class, 'logo'])->whereNumber('id');

// Dokumen verifikasi: hanya pemilik perusahaan atau admin (diperiksa di controller)
Route::get('/verifications/{id}/documents/{index}', [CompanyController::class, 'document'])
    ->whereNumber(['id', 'index'])
    ->middleware('jwt.auth');

// Pemeriksaan verifikasi perusahaan oleh admin
Route::middleware('jwt.auth:admin')->prefix('admin')->group(function () {
    Route::get('/verifications', [AdminVerificationController::class, 'index']);
    Route::patch('/verifications/{id}', [AdminVerificationController::class, 'review'])->whereNumber('id');
    Route::patch('/companies/{id}/status', [AdminVerificationController::class, 'setCompanyStatus'])->whereNumber('id');

    // Admin Portal: ringkasan, pengguna, perusahaan, moderasi lowongan, dan log audit
    Route::get('/overview', [AdminOverviewController::class, 'overview']);
    Route::get('/audit-logs', [AdminOverviewController::class, 'auditLogs']);
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show'])->whereNumber('id');
    Route::patch('/users/{id}/status', [AdminUserController::class, 'updateStatus'])->whereNumber('id');
    Route::get('/companies', [AdminVerificationController::class, 'companies']);
    Route::get('/companies/{id}', [AdminVerificationController::class, 'company'])->whereNumber('id');
    Route::get('/jobs', [AdminJobController::class, 'index']);
    Route::patch('/jobs/{id}/moderation', [AdminJobController::class, 'moderate'])->whereNumber('id');
    Route::get('/reports', [AdminReportController::class, 'index']);
    Route::patch('/reports/{id}', [AdminReportController::class, 'update'])->whereNumber('id');
});

// Lonceng pemberitahuan milik pengguna yang sedang masuk
Route::middleware('jwt.auth')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::post('/read-all', [NotificationController::class, 'markAllRead']);
    Route::patch('/{id}/read', [NotificationController::class, 'markRead'])->whereNumber('id');
});

// Laporan pengguna dan banding recruiter (dibatasi agar tidak dipakai untuk spam)
Route::middleware(['jwt.auth', 'throttle:10,60'])->post('/reports', [ReportController::class, 'store']);
Route::middleware(['jwt.auth:recruiter', 'throttle:10,60'])->post('/jobs/{id}/appeal', [ReportController::class, 'appeal'])->whereNumber('id');

Route::middleware('jwt.auth')->group(function () {
    // Method PUT/PATCH: Memperbarui biodata profil & keahlian pengguna
    Route::put('/profile', [ProfileSkillController::class, 'updateProfile']);
    Route::patch('/profile', [ProfileSkillController::class, 'updateProfile']);

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

// Catatan internal recruiter per lamaran (tidak terlihat oleh pelamar)
Route::middleware('jwt.auth:recruiter')->group(function () {
    Route::get('/applications/{id}/notes', [ApplicationNoteController::class, 'index'])->whereNumber('id');
    Route::post('/applications/{id}/notes', [ApplicationNoteController::class, 'store'])->whereNumber('id');
    Route::delete('/applications/{id}/notes/{noteId}', [ApplicationNoteController::class, 'destroy'])->whereNumber(['id', 'noteId']);
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

// =========================================================================

// =========================================================================
// 6. Radar Perkembangan Industri Indonesia (Geospatial API)
// =========================================================================
Route::prefix('geo')->group(function () {
    Route::get('/provinces', [\App\Http\Controllers\Api\GeospatialAtlasController::class, 'getProvincesGeoJson']);
    Route::get('/regencies', [\App\Http\Controllers\Api\GeospatialAtlasController::class, 'getRegenciesGeoJson']);
    Route::get('/industry-zones', [\App\Http\Controllers\Api\GeospatialAtlasController::class, 'getIndustryRadarData']);
    Route::get('/atlas', [\App\Http\Controllers\Api\GeospatialAtlasController::class, 'getIndustryRadarData']); // Alias kompatibilitas
});

Route::get('/geo/districts', [\App\Http\Controllers\Api\GeospatialAtlasController::class, 'getDistrictsGeoJson']);

Route::get('/geo/villages', [\App\Http\Controllers\Api\GeospatialAtlasController::class, 'getVillagesGeoJson']);

// =========================================================================
// 7. Mesin Agregasi & Pemantauan Kesehatan Sumber (Admin Monitoring)
// =========================================================================
Route::prefix('aggregator')->middleware('jwt.auth:admin')->group(function () {
    Route::get('/summary', [\App\Http\Controllers\Api\JobSourceMonitoringController::class, 'getMonitoringSummary']);
    Route::post('/sync', [\App\Http\Controllers\Api\JobSourceMonitoringController::class, 'triggerSync'])->middleware('throttle:6,1');
});
