<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Company;
use App\Models\CompanyVerification;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class CompanyController
 *
 * Profil perusahaan dan pengajuan verifikasi milik recruiter.
 * Bagian dari Layanan Auth & Profile Service.
 *
 * Aturan bisnis:
 * 1. Satu recruiter memiliki satu profil perusahaan.
 * 2. Recruiter yang belum terverifikasi tetap boleh menyimpan draft lowongan,
 *    tetapi baru boleh mempublikasikannya setelah perusahaan berstatus 'verified'.
 * 3. Pengajuan verifikasi hanya bisa dikirim saat status unverified atau rejected (pengajuan ulang).
 */
class CompanyController extends Controller
{
    use ApiResponse;

    /** Kolom profil yang wajib terisi sebelum perusahaan boleh mengajukan verifikasi. */
    private const REQUIRED_FOR_VERIFICATION = ['name', 'industry', 'description', 'address', 'city', 'email', 'phone'];

    private const DOCUMENT_TYPES = ['nib', 'npwp', 'akta', 'surat_kuasa', 'lainnya'];

    /**
     * Method  : GET
     * Endpoint: /api/company
     * Headers : Authorization: Bearer <token_recruiter>
     */
    public function show(Request $request): JsonResponse
    {
        $company = $this->currentUser($request)->company;

        return $this->successResponse(
            $company ? $this->companyPayload($company) : null,
            $company ? 'Profil perusahaan berhasil dimuat.' : 'Profil perusahaan belum dibuat.'
        );
    }

    /**
     * Membuat atau memperbarui profil perusahaan milik recruiter.
     *
     * Method  : PUT
     * Endpoint: /api/company
     */
    public function update(Request $request): JsonResponse
    {
        $user = $this->currentUser($request);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'size' => 'nullable|string|in:1-10,11-50,51-200,201-500,501-1000,1000+',
            'founded_year' => 'nullable|integer|min:1800|max:'.now()->year,
            'description' => 'nullable|string|max:3000',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
        ], [
            'name.required' => 'Nama perusahaan wajib diisi.',
            'website.url' => 'Alamat website harus diawali http:// atau https://.',
            'email.email' => 'Format email perusahaan tidak valid.',
            'size.in' => 'Pilihan jumlah karyawan tidak valid.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Profil perusahaan gagal disimpan karena data tidak valid.');
        }

        $company = $user->company;
        $fields = $validator->validated();

        if (! $company) {
            $company = Company::create($fields + ['owner_id' => $user->id, 'verification_status' => 'unverified']);

            return $this->createdResponse($this->companyPayload($company), 'Profil perusahaan berhasil dibuat.');
        }

        // Nama legal yang sudah diverifikasi tidak boleh diganti diam-diam
        if ($company->verification_status === 'verified' && isset($fields['name']) && $fields['name'] !== $company->name) {
            return $this->errorResponse(
                'Nama perusahaan yang sudah terverifikasi tidak dapat diubah. Hubungi admin bila perlu perubahan nama.',
                409,
                ['name' => ['Nama perusahaan terverifikasi terkunci.']],
                'COMPANY_NAME_LOCKED'
            );
        }

        $company->fill($fields)->save();

        return $this->successResponse($this->companyPayload($company->fresh()), 'Profil perusahaan berhasil diperbarui.');
    }

    /**
     * Method  : POST (multipart/form-data, field: logo)
     * Endpoint: /api/company/logo
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $company = $this->currentUser($request)->company;

        if (! $company) {
            return $this->notFoundResponse('Buat profil perusahaan terlebih dahulu sebelum mengunggah logo.');
        }

        $validator = Validator::make($request->all(), [
            'logo' => 'required|file|mimes:jpg,jpeg,png,webp|max:1024',
        ], [
            'logo.required' => 'File logo wajib dipilih.',
            'logo.mimes' => 'Logo harus berformat JPG, PNG, atau WEBP.',
            'logo.max' => 'Ukuran logo maksimal 1 MB.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Logo gagal diunggah.');
        }

        if ($company->logo_path) {
            Storage::disk('local')->delete($company->logo_path);
        }

        $company->logo_path = $request->file('logo')->store("company-logos/{$company->id}", 'local');
        $company->save();

        return $this->successResponse($this->companyPayload($company->fresh()), 'Logo perusahaan berhasil diperbarui.');
    }

    /**
     * Logo bersifat publik karena tampil di kartu lowongan.
     *
     * Method  : GET
     * Endpoint: /api/companies/{id}/logo
     */
    public function logo(int $id): StreamedResponse|JsonResponse
    {
        $company = Company::find($id);

        if (! $company || ! $company->logo_path || ! Storage::disk('local')->exists($company->logo_path)) {
            return $this->notFoundResponse('Logo perusahaan tidak ditemukan.');
        }

        return Storage::disk('local')->response($company->logo_path, null, [
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Status verifikasi saat ini beserta riwayat pengajuan.
     *
     * Method  : GET
     * Endpoint: /api/company/verification
     */
    public function verification(Request $request): JsonResponse
    {
        $company = $this->currentUser($request)->company;

        if (! $company) {
            return $this->successResponse([
                'status' => 'unverified',
                'note' => null,
                'can_submit' => false,
                'missing_profile_fields' => self::REQUIRED_FOR_VERIFICATION,
                'submissions' => [],
            ], 'Profil perusahaan belum dibuat.');
        }

        $company->load('verifications.reviewer');

        return $this->successResponse($this->verificationPayload($company), 'Status verifikasi perusahaan berhasil dimuat.');
    }

    /**
     * Mengirim (atau mengirim ulang) pengajuan verifikasi beserta dokumen pendukung.
     *
     * Method  : POST (multipart/form-data)
     * Endpoint: /api/company/verification
     * Fields  : legal_name, registration_number, tax_number?, applicant_position, notes?,
     *           documents[] (PDF/JPG/PNG, maks 2 MB per file, 1 sampai 5 file), document_types[]
     */
    public function submitVerification(Request $request): JsonResponse
    {
        $company = $this->currentUser($request)->company;

        if (! $company) {
            return $this->notFoundResponse('Buat profil perusahaan terlebih dahulu sebelum mengajukan verifikasi.');
        }

        if ($company->verification_status === 'suspended') {
            return $this->forbiddenResponse('Perusahaan sedang ditangguhkan oleh admin. Pengajuan verifikasi tidak dapat dikirim.', 'COMPANY_SUSPENDED');
        }

        if (in_array($company->verification_status, ['pending', 'verified'], true)) {
            $message = $company->verification_status === 'pending'
                ? 'Pengajuan verifikasi sebelumnya masih diperiksa admin.'
                : 'Perusahaan sudah terverifikasi.';

            return $this->errorResponse($message, 409, null, 'VERIFICATION_NOT_ALLOWED');
        }

        $missing = $this->missingProfileFields($company);
        if ($missing) {
            return $this->validationErrorResponse(
                ['profile' => ['Lengkapi profil perusahaan terlebih dahulu: '.implode(', ', $missing).'.']],
                'Profil perusahaan belum lengkap.'
            );
        }

        $validator = Validator::make($request->all(), [
            'legal_name' => 'required|string|max:255',
            'registration_number' => 'required|string|max:50',
            'tax_number' => 'nullable|string|max:50',
            'applicant_position' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'documents' => 'required|array|min:1|max:5',
            'documents.*' => 'file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_types' => 'nullable|array',
            'document_types.*' => 'nullable|string|in:'.implode(',', self::DOCUMENT_TYPES),
        ], [
            'legal_name.required' => 'Nama badan usaha sesuai dokumen legal wajib diisi.',
            'registration_number.required' => 'Nomor Induk Berusaha (NIB) wajib diisi.',
            'applicant_position.required' => 'Jabatan pengaju wajib diisi.',
            'documents.required' => 'Unggah minimal satu dokumen pendukung.',
            'documents.max' => 'Maksimal 5 dokumen per pengajuan.',
            'documents.*.mimes' => 'Dokumen harus berformat PDF, JPG, atau PNG.',
            'documents.*.max' => 'Ukuran setiap dokumen maksimal 2 MB.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Pengajuan verifikasi gagal dikirim karena data tidak valid.');
        }

        $types = $request->input('document_types', []);
        $documents = [];
        foreach ($request->file('documents') as $index => $file) {
            $documents[] = [
                'type' => $types[$index] ?? 'lainnya',
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'path' => $file->store("company-verifications/{$company->id}", 'local'),
            ];
        }

        $submission = CompanyVerification::create([
            'company_id' => $company->id,
            'legal_name' => $request->legal_name,
            'registration_number' => $request->registration_number,
            'tax_number' => $request->tax_number,
            'applicant_position' => $request->applicant_position,
            'notes' => $request->notes,
            'documents' => $documents,
            'status' => 'pending',
        ]);

        $company->update(['verification_status' => 'pending', 'verification_note' => null]);
        $company->load('verifications.reviewer');

        return $this->createdResponse(
            $this->verificationPayload($company) + ['submission_id' => $submission->id],
            'Pengajuan verifikasi berhasil dikirim dan menunggu pemeriksaan admin.'
        );
    }

    /**
     * Unduh dokumen verifikasi. Hanya pemilik perusahaan atau admin.
     *
     * Method  : GET
     * Endpoint: /api/verifications/{id}/documents/{index}
     */
    public function document(Request $request, int $id, int $index): StreamedResponse|JsonResponse
    {
        $user = $this->currentUser($request);
        $submission = CompanyVerification::with('company')->find($id);

        if (! $submission) {
            return $this->notFoundResponse('Pengajuan verifikasi tidak ditemukan.');
        }

        if ($user->role !== 'admin' && $submission->company?->owner_id !== $user->id) {
            return $this->forbiddenResponse('Akses ditolak. Dokumen ini bukan milik perusahaan Anda.', 'IDOR_FORBIDDEN');
        }

        $doc = ($submission->documents ?? [])[$index] ?? null;
        if (! $doc || ! Storage::disk('local')->exists($doc['path'])) {
            return $this->notFoundResponse('Dokumen tidak ditemukan.');
        }

        // Akses admin ke dokumen legal perusahaan dicatat; isi dokumen tidak disimpan di log
        if ($user->role === 'admin') {
            AdminAuditLog::record($request, 'company.document_viewed', $submission->company, $submission->company?->name, null,
                ['verification_id' => $submission->id, 'document' => $doc['name']]);
        }

        return Storage::disk('local')->download($doc['path'], $doc['name']);
    }

    // ------------------------------------------------------------------ helper

    private function currentUser(Request $request)
    {
        return $request->attributes->get('auth_user') ?? $request->user();
    }

    private function missingProfileFields(Company $company): array
    {
        return array_values(array_filter(self::REQUIRED_FOR_VERIFICATION, fn ($field) => blank($company->{$field})));
    }

    private function companyPayload(Company $company): array
    {
        return $company->toArray() + [
            'missing_profile_fields' => $this->missingProfileFields($company),
        ];
    }

    private function verificationPayload(Company $company): array
    {
        $missing = $this->missingProfileFields($company);

        return [
            'status' => $company->verification_status,
            'note' => $company->verification_note,
            'verified_at' => $company->verified_at?->toIso8601String(),
            'can_submit' => in_array($company->verification_status, ['unverified', 'revision_required', 'rejected'], true) && ! $missing,
            'missing_profile_fields' => $missing,
            'submissions' => $company->verifications->map->toApiArray()->values()->all(),
        ];
    }
}
