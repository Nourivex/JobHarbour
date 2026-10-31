<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ProfileSkillController
 *
 * Mengelola fungsionalitas Auth & Profile Service: Matriks Keahlian (Skills) Profil Pengguna.
 * Bagian dari Layanan Microservice: Auth & Profile Service (Penanggung Jawab: Nabe'ela Ayu Ning Tyas Zahra - 23215052).
 *
 * Pokok Bahasan Pembelajaran Mahasiswa:
 * 1. Penggunaan Method HTTP "PUT" untuk memperbarui seluruh kumpulan data keahlian (replace/update array).
 * 2. Otorisasi kepemilikan akun (User hanya boleh mengubah keahlian profilnya sendiri, kecuali Admin).
 * 3. Sanitasi dan pembersihan data array (menghapus duplikasi nama skill secara case-insensitive).
 */
class ProfileSkillController extends Controller
{
    use ApiResponse;

    /**
     * Auth & Profile Service: Menyimpan atau memperbarui data daftar keahlian (skills) di profil pengguna.
     *
     * Method  : PUT
     * Endpoint: /api/users/{id}/skills  atau  /api/profile/skills
     * Headers : Authorization: Bearer <token>, Content-Type: application/json
     *
     * @param  Request   $request  Objek HTTP Request yang berisi payload array skills
     * @param  int|null  $id       ID pengguna spesifik (opsional)
     * @return JsonResponse        JSON envelope konfirmasi pembaruan keahlian
     */
    public function updateSkills(Request $request, ?int $id = null): JsonResponse
    {
        // 1. Ambil data pengguna yang sedang login dari JWT Token
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        // 2. Cek apakah request menargetkan profil pengguna lain
        $targetUser = $currentUser;
        if ($id !== null && $currentUser && $currentUser->id !== $id) {
            // Hanya pengguna dengan peran 'admin' yang berhak mengubah skill orang lain
            if ($currentUser->role !== 'admin') {
                return $this->forbiddenResponse('Akses dilarang. Anda tidak memiliki wewenang untuk mengubah data profil milik pengguna lain.');
            }

            $targetUser = User::find($id);
            if (! $targetUser) {
                return $this->notFoundResponse('Pengguna target tidak ditemukan di dalam sistem.');
            }
        }

        // 3. Validasi struktur payload input
        $validator = Validator::make($request->all(), [
            'skills' => 'required|array|min:1',
            'skills.*' => 'required',
        ], [
            'skills.required' => 'Daftar keahlian (skills) wajib disertakan.',
            'skills.array' => 'Format data skills harus berupa array daftar keahlian.',
            'skills.min' => 'Minimal masukkan satu nama keahlian.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Format data keahlian tidak valid.');
        }

        // 4. Normalisasi dan sanitasi data keahlian dari masukan pengguna
        $rawSkills = $request->input('skills');
        $formattedSkills = [];

        foreach ($rawSkills as $skill) {
            if (is_string($skill)) {
                $trimmed = trim($skill);
                if ($trimmed !== '') {
                    $formattedSkills[] = $trimmed;
                }
            } elseif (is_array($skill) && ! empty($skill['name'])) {
                // Menangani jika input dikirim dalam format objek: [{"name": "PHP"}]
                $formattedSkills[] = trim($skill['name']);
            }
        }

        // 5. Hapus duplikasi nama keahlian yang serupa
        $uniqueSkills = array_values(array_unique($formattedSkills, SORT_REGULAR));

        // 6. Simpan array keahlian baru ke dalam kolom JSON di tabel database
        $targetUser->skills = $uniqueSkills;
        $targetUser->save();

        // 7. Kembalikan response HTTP 200 OK dengan format envelope standar
        return $this->successResponse([
            'user_id' => $targetUser->id,
            'name' => $targetUser->name,
            'email' => $targetUser->email,
            'skills' => $targetUser->skills,
            'skills_count' => count($targetUser->skills),
            'updated_at' => $targetUser->updated_at->toIso8601String(),
        ], 'Data keahlian (skills) pada profil pengguna berhasil diperbarui.');
    }

    /**
     * Auth & Profile Service: Memperbarui biodata profil pengguna (nama, telepon, bio, lokasi, skills).
     *
     * Method  : PUT / PATCH
     * Endpoint: /api/profile
     * Headers : Authorization: Bearer <token>, Content-Type: application/json
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $currentUser = $request->attributes->get('auth_user') ?? $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:30',
            'bio' => 'nullable|string|max:1000',
            'location' => 'nullable|string|max:100',
            'skills' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Data profil tidak valid.');
        }

        if ($request->has('name')) {
            $currentUser->name = $request->input('name');
        }
        if ($request->has('phone')) {
            $currentUser->phone = $request->input('phone');
        }
        if ($request->has('bio')) {
            $currentUser->bio = $request->input('bio');
        }
        if ($request->has('location')) {
            $currentUser->location = $request->input('location');
        }
        if ($request->has('skills') && is_array($request->input('skills'))) {
            $rawSkills = $request->input('skills');
            $formattedSkills = [];
            foreach ($rawSkills as $skill) {
                if (is_string($skill) && trim($skill) !== '') {
                    $formattedSkills[] = trim($skill);
                } elseif (is_array($skill) && ! empty($skill['name'])) {
                    $formattedSkills[] = trim($skill['name']);
                }
            }
            $currentUser->skills = array_values(array_unique($formattedSkills, SORT_REGULAR));
        }

        $currentUser->save();

        return $this->successResponse([
            'id' => $currentUser->id,
            'name' => $currentUser->name,
            'email' => $currentUser->email,
            'role' => $currentUser->role,
            'phone' => $currentUser->phone,
            'bio' => $currentUser->bio,
            'location' => $currentUser->location,
            'skills' => $currentUser->skills ?? [],
            'updated_at' => $currentUser->updated_at->toIso8601String(),
        ], 'Profil pengguna berhasil diperbarui.');
    }
}
