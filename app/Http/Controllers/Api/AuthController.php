<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\JwtService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Class AuthController
 *
 * Mengelola fungsionalitas Auth & Profile Service: Autentikasi Pengguna & Sesi Akses Token.
 * Bagian dari Layanan Microservice: Auth & Profile Service (Penanggung Jawab: Nabe'ela Ayu Ning Tyas Zahra - 23215052).
 *
 * Pokok Bahasan Pembelajaran Mahasiswa:
 * 1. Menerima payload JSON dari body HTTP Request.
 * 2. Melakukan validasi input menggunakan class Validator Laravel.
 * 3. Memeriksa kecocokan hash sandi pengguna (Bcrypt).
 * 4. Menerbitkan tanda pengenal digital stateless (JWT Access Token).
 * 5. Mengembalikan envelope format JSON standar.
 */
class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(protected JwtService $jwtService)
    {
    }

    /**
     * Auth & Profile Service: Login pengguna & generate token akses JWT.
     *
     * Method  : POST
     * Endpoint: /api/auth/login
     * Headers : Content-Type: application/json, Accept: application/json
     *
     * @param  Request       $request  Data HTTP Request (email & password)
     * @return JsonResponse           JSON envelope berisi token dan profil dasar
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Validasi input: email wajib berformat valid, password wajib diisi.
        //    Panjang minimal tidak dicek saat login agar akun lama dengan sandi pendek tetap bisa masuk.
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Format alamat email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // Jika validasi gagal, kembalikan HTTP 422 Unprocessable Entity
        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Validasi login gagal.');
        }

        // 2. Cari data pengguna di database berdasarkan alamat email
        $user = User::where('email', $request->email)->first();

        // 3. Verifikasi apakah user ditemukan dan apakah hash password cocok
        if (! $user || ! Hash::check($request->password, $user->password)) {
            // Mengembalikan HTTP 401 Unauthorized jika kredensial salah
            return $this->errorResponse('Kredensial tidak valid. Alamat email atau kata sandi salah.', 401);
        }

        // 4. Buat token akses JWT menggunakan JwtService
        $token = $this->jwtService->generateToken($user);

        // 5. Kembalikan response sukses HTTP 200 OK dengan format envelope
        return $this->successResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 86400 * 7, // Berlaku selama 7 hari (dalam detik)
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'location' => $user->location,
                'skills' => $user->skills ?? [],
            ],
        ], 'Login berhasil. Token otentikasi JWT berhasil diterbitkan.');
    }

    /**
     * Mendaftarkan akun pengguna baru (Pelamar atau Recruiter).
     *
     * Method  : POST
     * Endpoint: /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            // Akun admin hanya dibuat lewat seeder, tidak lewat pendaftaran publik
            'role' => 'nullable|in:applicant,recruiter',
            'phone' => 'nullable|string|max:20',
            'location' => 'nullable|string|max:100',
            'skills' => 'nullable|array',
        ], [
            'name.required' => 'Nama lengkap pengguna wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email ini sudah terdaftar dalam sistem.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'role.in' => 'Pilihan peran yang diperbolehkan: applicant atau recruiter.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Pendaftaran akun gagal karena data tidak valid.');
        }

        // Buat record pengguna baru dengan password yang dienkripsi (Hash Bcrypt)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role ?? 'applicant',
            'phone' => $request->phone,
            'location' => $request->location,
            'skills' => $request->skills ?? [],
        ]);

        // Terbitkan token akses langsung setelah pendaftaran berhasil
        $token = $this->jwtService->generateToken($user);

        // Kembalikan HTTP 201 Created
        return $this->createdResponse([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'skills' => $user->skills,
            ],
        ], 'Pendaftaran akun pengguna baru berhasil.');
    }

    /**
     * Menampilkan data profil lengkap pengguna yang sedang aktif (terotentikasi).
     *
     * Method  : GET
     * Endpoint: /api/auth/me
     * Headers : Authorization: Bearer <token>
     */
    public function me(Request $request): JsonResponse
    {
        // Ambil data user yang telah dilampirkan oleh Middleware JwtAuth
        $user = $request->attributes->get('auth_user') ?? $request->user();

        return $this->successResponse([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'phone' => $user->phone,
            'bio' => $user->bio,
            'location' => $user->location,
            'skills' => $user->skills ?? [],
            'created_at' => $user->created_at?->toIso8601String(),
            'company' => $user->role === 'recruiter' && $user->company ? [
                'id' => $user->company->id,
                'name' => $user->company->name,
                'logo_url' => $user->company->logo_url,
                'verification_status' => $user->company->verification_status,
            ] : null,
        ], 'Data profil pengguna berhasil dimuat.');
    }

    /**
     * Mengganti kata sandi pengguna yang sedang login.
     *
     * Method  : PUT
     * Endpoint: /api/auth/password
     * Body    : { "current_password", "password", "password_confirmation" }
     */
    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->attributes->get('auth_user') ?? $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed|different:current_password',
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required' => 'Kata sandi baru wajib diisi.',
            'password.min' => 'Kata sandi baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak sama.',
            'password.different' => 'Kata sandi baru harus berbeda dari kata sandi saat ini.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Kata sandi gagal diganti.');
        }

        if (! Hash::check($request->current_password, $user->password)) {
            return $this->validationErrorResponse(
                ['current_password' => ['Kata sandi saat ini salah.']],
                'Kata sandi gagal diganti.'
            );
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return $this->successResponse(null, 'Kata sandi berhasil diganti.');
    }
}
