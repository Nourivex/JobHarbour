<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Throwable;

/**
 * Class JwtService
 *
 * Layanan penghasil dan pemverifikasi JSON Web Token (JWT) standar RFC 7519.
 *
 * Penjelasan Konsep untuk Pembelajaran:
 * JWT terdiri dari 3 bagian yang dipisahkan oleh tanda titik (header.payload.signature):
 * 1. Header    : Algoritma kriptografi yang digunakan (HS256 / HMAC SHA-256)
 * 2. Payload   : Klaim identitas data pengguna (ID pengguna, nama, role, masa berlaku token)
 * 3. Signature : Tanda tangan digital untuk menjamin data payload tidak dimanipulasi oleh pihak ketiga
 */
class JwtService
{
    /**
     * Kunci rahasia (secret key) yang digunakan untuk menandatangani token.
     * Menggunakan nilai dari APP_KEY aplikasi Laravel.
     */
    protected string $secret;

    /**
     * Algoritma hashing enkripsi token.
     */
    protected string $algorithm = 'HS256';

    /**
     * Masa berlaku token akses (TTL) dalam detik (7 hari = 604800 detik).
     */
    protected int $ttl = 86400 * 7;

    public function __construct()
    {
        // Ambil kunci rahasia dari konfigurasi Laravel (APP_KEY)
        $appKey = (string) config('app.key', 'base64:JobHarbourSecretKeyForJwtAuth32CharsLong');

        // Jika diawali prefix base64:, lakukan decode terlebih dahulu
        if (str_starts_with($appKey, 'base64:')) {
            $this->secret = base64_decode(substr($appKey, 7));
        } else {
            $this->secret = $appKey;
        }
    }

    /**
     * Menghasilkan token JWT baru untuk pengguna yang berhasil diautentikasi.
     *
     * @param  User  $user  Instance model User yang sedang login
     * @return string       String JWT token (cth: eyJhbGciOiJIUzI1NiIs...)
     */
    public function generateToken(User $user): string
    {
        $waktuSekarang = time();

        // Menyusun klaim payload JWT
        $payload = [
            'iss' => config('app.url', 'http://localhost:8000'), // Issuer: penerbit token
            'sub' => $user->id,                                  // Subject: ID unik pengguna
            'name' => $user->name,                               // Nama pengguna
            'email' => $user->email,                             // Alamat email pengguna
            'role' => $user->role,                               // Peran pengguna (applicant/recruiter/admin)
            'iat' => $waktuSekarang,                             // Issued At: waktu token diterbitkan
            'exp' => $waktuSekarang + $this->ttl,                // Expiration Time: batas akhir berlakunya token
        ];

        // Lakukan encode menggunakan algoritma HS256 dan kunci rahasia
        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Memverifikasi token JWT dan mengembalikan status terperinci (valid, payload, atau kode error).
     *
     * @param  string  $token
     * @return array{valid: bool, payload: object|null, error_code: string|null, message: string|null}
     */
    public function verifyToken(string $token): array
    {
        try {
            $payload = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return [
                'valid' => true,
                'payload' => $payload,
                'error_code' => null,
                'message' => null,
            ];
        } catch (ExpiredException) {
            return [
                'valid' => false,
                'payload' => null,
                'error_code' => 'TOKEN_EXPIRED',
                'message' => 'Akses ditolak. Token autentikasi telah kedaluwarsa (expired). Silakan lakukan login ulang.',
            ];
        } catch (SignatureInvalidException) {
            return [
                'valid' => false,
                'payload' => null,
                'error_code' => 'TOKEN_INVALID',
                'message' => 'Akses ditolak. Tanda tangan digital token tidak valid.',
            ];
        } catch (BeforeValidException) {
            return [
                'valid' => false,
                'payload' => null,
                'error_code' => 'TOKEN_NOT_YET_VALID',
                'message' => 'Akses ditolak. Token belum dapat digunakan.',
            ];
        } catch (Throwable $e) {
            return [
                'valid' => false,
                'payload' => null,
                'error_code' => 'TOKEN_MALFORMED',
                'message' => 'Akses ditolak. Format token akses tidak valid atau rusak.',
            ];
        }
    }

    /**
     * Memverifikasi dan mendekode string token JWT yang dikirimkan oleh client.
     *
     * @param  string       $token  String token JWT dari header Authorization: Bearer <token>
     * @return object|null          Payload data jika token valid, atau null jika tidak valid/kedaluwarsa
     */
    public function decodeToken(string $token): ?object
    {
        $res = $this->verifyToken($token);
        return $res['valid'] ? $res['payload'] : null;
    }
}
