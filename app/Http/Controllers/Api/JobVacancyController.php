<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobVacancy;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class JobVacancyController
 *
 * Mengelola fungsionalitas Job Catalog Service: Katalog Lowongan Kerja & Kualifikasi Keahlian.
 * Bagian dari Layanan Microservice: Job Catalog Service (Penanggung Jawab: MUHAMAD FAHREN ANDREAN RANGKUTI).
 *
 * Pokok Bahasan Pembelajaran Mahasiswa:
 * 1. Method GET (Idempotent & Safe): Mengambil dan memfilter data lowongan menggunakan Query Parameters.
 * 2. Fitur Pagination: Membagi data dalam beberapa halaman agar beban transfer jaringan tetap optimal.
 * 3. Method POST: Menambahkan resource baru ke server (Lowongan Kerja baru).
 * 4. Otorisasi Peran: Hanya akun dengan peran 'recruiter' yang diizinkan menerbitkan lowongan.
 * 5. Pengelolaan Bobot Kualifikasi Keahlian (Skill Weighting) untuk mesin pemadanan calon pelamar.
 */
class JobVacancyController extends Controller
{
    use ApiResponse;

    /**
     * Job Catalog Service: Menampilkan dan memfilter daftar lowongan pekerjaan yang tersedia.
     *
     * Method  : GET
     * Endpoint: /api/jobs
     * Query Parameters yang Didukung:
     * - search          : Kata kunci pencarian judul, nama perusahaan, atau deskripsi
     * - location        : Lokasi kota penempatan kerja
     * - skill           : Keahlian spesifik yang disyaratkan dalam lowongan
     * - employment_type : Tipe pekerjaan (full-time, part-time, contract, internship, remote)
     * - status          : Status lowongan (default: 'open')
     * - per_page        : Jumlah data per halaman (default: 10)
     *
     * @param  Request       $request  Objek HTTP Request yang membawa query parameters
     * @return JsonResponse           JSON envelope berisi array lowongan dan metadata pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = JobVacancy::query();

        // 1. Filter Status Lowongan: secara default hanya menampilkan lowongan yang masih dibuka ('open')
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            $query->where('status', 'open');
        }

        // 2. Filter Pencarian Keyword pada Judul, Nama Perusahaan, atau Deskripsi
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // 3. Filter Lokasi Kota
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // 4. Filter Jenis Ikatan Kerja
        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        // 5. Filter Keahlian (Skill): Mencari syarat kompetensi di dalam kolom JSON 'requirements'
        if ($request->filled('skill')) {
            $skillSearch = strtolower($request->skill);
            $query->where(function ($q) use ($skillSearch) {
                $q->whereRaw('LOWER(requirements) LIKE ?', ["%{$skillSearch}%"]);
            });
        }

        // 6. Pengurutan Data (Sorting)
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'oldest' => $query->oldest(),
            'salary_high', 'salary_desc' => $query->orderByDesc('salary_max'),
            'salary_low', 'salary_asc' => $query->orderBy('salary_min'),
            default => $query->latest(),
        };

        // 7. Eksekusi query dengan paginasi aman (dibatasi maksimum 50 item per halaman)
        $perPage = min(50, max(1, (int) $request->input('per_page', 10)));
        $paginated = $query->paginate($perPage);

        // 8. Kembalikan response HTTP 200 OK beserta rincian informasi pagination
        return $this->successResponse(
            $paginated->items(),
            'Daftar lowongan kerja berhasil diambil.',
            200,
            [
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'last_page' => $paginated->lastPage(),
                ],
                'filters_applied' => $request->only(['search', 'location', 'skill', 'employment_type', 'status', 'sort']),
            ]
        );
    }

    /**
     * Menampilkan detail satu lowongan kerja berdasarkan ID.
     *
     * Method  : GET
     * Endpoint: /api/jobs/{id}
     */
    public function show(int $id): JsonResponse
    {
        $job = JobVacancy::with('recruiter:id,name,email')->find($id);

        if (! $job) {
            return $this->notFoundResponse('Lowongan pekerjaan yang dicari tidak ditemukan.');
        }

        return $this->successResponse($job, 'Detail lowongan pekerjaan berhasil diambil.');
    }

    /**
     * Job Catalog Service: Recruiter memposting lowongan pekerjaan baru.
     *
     * Method  : POST
     * Endpoint: /api/jobs
     * Headers : Authorization: Bearer <token_recruiter>, Content-Type: application/json
     *
     * @param  Request       $request  Data payload lowongan baru dan syarat keahlian
     * @return JsonResponse           JSON envelope konfirmasi HTTP 201 Created
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Ambil data recruiter yang sedang terotentikasi
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        // 2. Pemeriksaan hak akses: Hanya akun Recruiter atau Admin yang berhak memposting
        if (! $currentUser || ($currentUser->role !== 'recruiter' && $currentUser->role !== 'admin')) {
            return $this->forbiddenResponse('Akses dilarang. Hanya akun dengan peran Recruiter yang dapat memposting lowongan baru.');
        }

        // 3. Validasi ketat terhadap form data lowongan baru
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string|max:100',
            'employment_type' => 'nullable|string|in:full-time,part-time,contract,internship,remote',
            'quota' => 'nullable|integer|min:1',
            'salary_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|gte:salary_min',
            'requirements' => 'required|array|min:1',
            'requirements.*.skill' => 'required|string|max:100',
            'requirements.*.weight' => 'nullable|integer|in:1,2,3',
            'requirements.*.level' => 'nullable|string|in:required,important,nice_to_have',
            'expires_at' => 'nullable|date|after:today',
        ], [
            'title.required' => 'Judul posisi lowongan pekerjaan wajib diisi.',
            'company.required' => 'Nama instansi/perusahaan wajib diisi.',
            'requirements.required' => 'Kriteria keahlian (requirements) wajib dicantumkan minimal satu keahlian.',
            'salary_max.gte' => 'Gaji maksimal harus lebih besar atau sama dengan gaji minimal.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Gagal memposting lowongan karena data tidak valid.');
        }

        // 4. Standarisasi bobot kualifikasi tiap keahlian:
        //    required = 3, important = 2, nice_to_have = 1
        $requirements = array_map(function ($req) {
            $level = $req['level'] ?? 'required';
            $weight = match ($level) {
                'required' => 3,
                'important' => 2,
                'nice_to_have' => 1,
                default => 2,
            };

            return [
                'skill' => trim($req['skill']),
                'level' => $level,
                'weight' => isset($req['weight']) && is_numeric($req['weight']) ? (int) $req['weight'] : $weight,
            ];
        }, $request->input('requirements'));

        // 5. Simpan record lowongan baru ke database
        $job = JobVacancy::create([
            'title' => $request->title,
            'company' => $request->company,
            'description' => $request->description,
            'location' => $request->location,
            'employment_type' => $request->employment_type ?? 'full-time',
            'salary_min' => $request->salary_min,
            'salary_max' => $request->salary_max,
            'quota' => $request->quota ?? 1,
            'filled' => 0,
            'requirements' => $requirements,
            'status' => 'open',
            'posted_by' => $currentUser->id,
            'expires_at' => $request->expires_at,
        ]);

        // 6. Kembalikan response HTTP 201 Created
        return $this->createdResponse([
            'id' => $job->id,
            'title' => $job->title,
            'company' => $job->company,
            'location' => $job->location,
            'employment_type' => $job->employment_type,
            'quota' => $job->quota,
            'requirements' => $job->requirements,
            'requirements_count' => count($job->requirements),
            'status' => $job->status,
            'posted_by' => [
                'id' => $currentUser->id,
                'name' => $currentUser->name,
                'email' => $currentUser->email,
            ],
            'created_at' => $job->created_at->toIso8601String(),
        ], 'Lowongan pekerjaan baru berhasil diterbitkan.');
    }

    /**
     * Job Catalog Service: Recruiter melihat daftar lowongan yang ia terbitkan sendiri.
     *
     * Method  : GET
     * Endpoint: /api/jobs/my
     * Headers : Authorization: Bearer <token_recruiter>
     */
    public function myJobs(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        if (! $currentUser || ($currentUser->role !== 'recruiter' && $currentUser->role !== 'admin')) {
            return $this->forbiddenResponse('Hanya recruiter yang dapat mengakses daftar lowongan miliknya.');
        }

        $jobs = JobVacancy::where('posted_by', $currentUser->id)
            ->withCount('applications')
            ->latest()
            ->get();

        return $this->successResponse(
            $jobs,
            'Daftar lowongan yang Anda kelola berhasil dimuat.',
            200,
            ['total_managed_jobs' => $jobs->count()]
        );
    }

    /**
     * Job Catalog Service: Recruiter memperbarui data lowongan pekerjaannya.
     * Proteksi IDOR: Memastikan hanya pemilik lowongan yang sah atau admin yang berhak mengedit.
     *
     * Method  : PUT / PATCH
     * Endpoint: /api/jobs/{id}
     * Headers : Authorization: Bearer <token_recruiter>
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        $job = JobVacancy::find($id);

        if (! $job) {
            return $this->notFoundResponse('Lowongan pekerjaan yang ingin diperbarui tidak ditemukan.');
        }

        // Proteksi IDOR: Verifikasi kepemilikan resource
        if ($currentUser->role !== 'admin' && $job->posted_by !== $currentUser->id) {
            return $this->forbiddenResponse('Akses ditolak. Anda bukan pemilik dari lowongan pekerjaan ini.', 'IDOR_FORBIDDEN');
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'company' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'location' => 'nullable|string|max:100',
            'employment_type' => 'nullable|string|in:full-time,part-time,contract,internship,remote',
            'quota' => 'nullable|integer|min:1',
            'salary_min' => 'nullable|integer|min:0',
            'salary_max' => 'nullable|integer|gte:salary_min',
            'requirements' => 'nullable|array',
            'requirements.*.skill' => 'required_with:requirements|string|max:100',
            'requirements.*.weight' => 'nullable|integer|in:1,2,3',
            'requirements.*.level' => 'nullable|string|in:required,important,nice_to_have',
            'status' => 'nullable|string|in:open,closed',
            'expires_at' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Gagal memperbarui lowongan karena data tidak valid.');
        }

        if ($request->has('requirements')) {
            $requirements = array_map(function ($req) {
                $level = $req['level'] ?? 'required';
                $weight = match ($level) {
                    'required' => 3,
                    'important' => 2,
                    'nice_to_have' => 1,
                    default => 2,
                };

                return [
                    'skill' => trim($req['skill']),
                    'level' => $level,
                    'weight' => isset($req['weight']) && is_numeric($req['weight']) ? (int) $req['weight'] : $weight,
                ];
            }, $request->input('requirements'));

            $job->requirements = $requirements;
        }

        $fillable = ['title', 'company', 'description', 'location', 'employment_type', 'salary_min', 'salary_max', 'quota', 'status', 'expires_at'];
        foreach ($fillable as $field) {
            if ($request->has($field)) {
                $job->{$field} = $request->input($field);
            }
        }

        $job->save();

        return $this->successResponse($job, 'Data lowongan pekerjaan berhasil diperbarui.');
    }

    /**
     * Job Catalog Service: Recruiter menutup atau menghapus lowongan pekerjaan.
     * Proteksi IDOR: Memastikan hanya pemilik lowongan yang sah atau admin yang berhak menghapus.
     *
     * Method  : DELETE
     * Endpoint: /api/jobs/{id}
     * Headers : Authorization: Bearer <token_recruiter>
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        $job = JobVacancy::find($id);

        if (! $job) {
            return $this->notFoundResponse('Lowongan pekerjaan yang ingin dihapus tidak ditemukan.');
        }

        // Proteksi IDOR: Verifikasi kepemilikan resource
        if ($currentUser->role !== 'admin' && $job->posted_by !== $currentUser->id) {
            return $this->forbiddenResponse('Akses ditolak. Anda bukan pemilik dari lowongan pekerjaan ini.', 'IDOR_FORBIDDEN');
        }

        $jobTitle = $job->title;
        // Kita ubah status menjadi closed (soft close) agar integritas relasi pelamar tetap aman
        $job->status = 'closed';
        $job->save();

        return $this->successResponse([
            'id' => $job->id,
            'title' => $jobTitle,
            'status' => 'closed',
            'closed_at' => now()->toIso8601String(),
        ], "Lowongan '{$jobTitle}' telah berhasil ditutup.");
    }
}
