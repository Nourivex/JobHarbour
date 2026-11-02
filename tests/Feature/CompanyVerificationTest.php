<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Profil perusahaan, verifikasi, aturan publikasi lowongan, dan ganti kata sandi.
 */
class CompanyVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $recruiter;

    protected User $otherRecruiter;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');

        $this->recruiter = $this->makeUser('recruiter@test.com', 'recruiter');
        $this->otherRecruiter = $this->makeUser('recruiter2@test.com', 'recruiter');
        $this->admin = $this->makeUser('admin@test.com', 'admin');
    }

    private function makeUser(string $email, string $role): User
    {
        return User::create([
            'name' => ucfirst($role).' Test',
            'email' => $email,
            'password' => Hash::make('secret123'),
            'role' => $role,
        ]);
    }

    private function auth(string $email, string $password = 'secret123'): array
    {
        $token = $this->postJson('/api/auth/login', ['email' => $email, 'password' => $password])->json('data.token');

        return ['Authorization' => "Bearer {$token}"];
    }

    private function completeProfile(): array
    {
        return [
            'name' => 'PT Pelabuhan Digital',
            'industry' => 'Teknologi Informasi',
            'size' => '11-50',
            'description' => 'Perusahaan pengembang perangkat lunak di Tegal.',
            'address' => 'Jl. Pelabuhan No. 1',
            'city' => 'Tegal',
            'email' => 'hr@pelabuhan.id',
            'phone' => '0283123456',
            'website' => 'https://pelabuhan.id',
        ];
    }

    private function jobPayload(array $extra = []): array
    {
        return array_merge([
            'title' => 'Backend Engineer',
            'description' => 'Membangun REST API.',
            'location' => 'Tegal',
            'requirements' => [['skill' => 'PHP', 'level' => 'required']],
        ], $extra);
    }

    private function submitVerification(array $headers)
    {
        return $this->withHeaders($headers)->post('/api/company/verification', [
            'legal_name' => 'PT Pelabuhan Digital Nusantara',
            'registration_number' => '1234567890123',
            'applicant_position' => 'HR Manager',
            'documents' => [UploadedFile::fake()->create('nib.pdf', 200, 'application/pdf')],
            'document_types' => ['nib'],
        ], ['Accept' => 'application/json']);
    }

    public function test_recruiter_creates_and_updates_company_profile(): void
    {
        $headers = $this->auth('recruiter@test.com');

        $this->withHeaders($headers)->getJson('/api/company')
            ->assertStatus(200)
            ->assertJsonPath('data', null);

        $this->withHeaders($headers)->putJson('/api/company', ['name' => 'PT Awal'])
            ->assertStatus(201)
            ->assertJsonPath('data.name', 'PT Awal')
            ->assertJsonPath('data.verification_status', 'unverified');

        $this->withHeaders($headers)->putJson('/api/company', ['name' => 'PT Awal', 'website' => 'bukan-url'])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['website']]);

        $this->withHeaders($headers)->putJson('/api/company', $this->completeProfile())
            ->assertStatus(200)
            ->assertJsonPath('data.city', 'Tegal')
            ->assertJsonPath('data.missing_profile_fields', []);

        $this->withHeaders($headers)
            ->post('/api/company/logo', ['logo' => UploadedFile::fake()->image('logo.png', 200, 200)], ['Accept' => 'application/json'])
            ->assertStatus(200);

        $company = Company::first();
        $this->get("/api/companies/{$company->id}/logo")->assertStatus(200);
    }

    public function test_unverified_recruiter_can_save_draft_but_cannot_publish(): void
    {
        $headers = $this->auth('recruiter@test.com');
        $this->withHeaders($headers)->putJson('/api/company', $this->completeProfile());

        // Tanpa status berarti publikasi langsung: ditolak
        $this->withHeaders($headers)->postJson('/api/jobs', $this->jobPayload())
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'COMPANY_NOT_VERIFIED');

        // Draft boleh, nama perusahaan diambil dari profil
        $draft = $this->withHeaders($headers)->postJson('/api/jobs', $this->jobPayload(['status' => 'draft']))
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.company', 'PT Pelabuhan Digital');

        $jobId = $draft->json('data.id');

        // Draft tidak tampil di katalog publik maupun detail publik
        $this->getJson('/api/jobs')->assertJsonCount(0, 'data');
        $this->getJson('/api/jobs?status=draft')->assertJsonCount(0, 'data');
        $this->getJson("/api/jobs/{$jobId}")->assertStatus(404);

        // Tetap terlihat oleh pemiliknya
        $this->withHeaders($headers)->getJson('/api/jobs/my')->assertJsonCount(1, 'data');

        // Membuka draft juga ditolak sebelum terverifikasi
        $this->withHeaders($headers)->putJson("/api/jobs/{$jobId}", ['status' => 'open'])
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'COMPANY_NOT_VERIFIED');
    }

    public function test_verification_flow_approve_then_publish(): void
    {
        $headers = $this->auth('recruiter@test.com');

        // Belum punya profil lengkap: pengajuan ditolak
        $this->withHeaders($headers)->putJson('/api/company', ['name' => 'PT Setengah']);
        $this->submitVerification($headers)->assertStatus(422)->assertJsonStructure(['errors' => ['profile']]);

        $this->withHeaders($headers)->putJson('/api/company', $this->completeProfile());

        $this->withHeaders($headers)->getJson('/api/company/verification')
            ->assertJsonPath('data.status', 'unverified')
            ->assertJsonPath('data.can_submit', true);

        $this->submitVerification($headers)
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.submissions.0.documents.0.name', 'nib.pdf');

        // Pengajuan ganda saat masih diperiksa ditolak
        $this->submitVerification($headers)->assertStatus(409);

        // Recruiter lain tidak bisa mengunduh dokumen milik perusahaan ini
        $submissionId = Company::first()->verifications()->first()->id;
        $this->withHeaders($this->auth('recruiter2@test.com'))
            ->getJson("/api/verifications/{$submissionId}/documents/0")
            ->assertStatus(403);

        // Recruiter tidak boleh memakai endpoint admin
        $this->withHeaders($headers)->getJson('/api/admin/verifications')->assertStatus(403);

        $adminHeaders = $this->auth('admin@test.com');
        $this->withHeaders($adminHeaders)->getJson('/api/admin/verifications?status=pending')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.company.name', 'PT Pelabuhan Digital');

        $this->withHeaders($adminHeaders)->get("/api/verifications/{$submissionId}/documents/0")->assertStatus(200);

        $this->withHeaders($adminHeaders)->patchJson("/api/admin/verifications/{$submissionId}", ['decision' => 'approve'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'approved');

        // Pengajuan yang sudah diperiksa tidak bisa diputus ulang
        $this->withHeaders($adminHeaders)->patchJson("/api/admin/verifications/{$submissionId}", ['decision' => 'reject', 'admin_notes' => 'x'])
            ->assertStatus(409);

        $this->withHeaders($headers)->postJson('/api/jobs', $this->jobPayload())
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'open');

        // Nama legal terkunci setelah terverifikasi
        $this->withHeaders($headers)->putJson('/api/company', ['name' => 'PT Lain'])
            ->assertStatus(409)
            ->assertJsonPath('error_code', 'COMPANY_NAME_LOCKED');
    }

    public function test_rejection_requires_note_and_allows_resubmission(): void
    {
        $headers = $this->auth('recruiter@test.com');
        $this->withHeaders($headers)->putJson('/api/company', $this->completeProfile());
        $this->submitVerification($headers);
        $submissionId = Company::first()->verifications()->first()->id;

        $adminHeaders = $this->auth('admin@test.com');
        $this->withHeaders($adminHeaders)->patchJson("/api/admin/verifications/{$submissionId}", ['decision' => 'reject'])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['admin_notes']]);

        $this->withHeaders($adminHeaders)->patchJson("/api/admin/verifications/{$submissionId}", [
            'decision' => 'reject',
            'admin_notes' => 'Dokumen NIB tidak terbaca.',
        ])->assertStatus(200);

        $this->withHeaders($headers)->getJson('/api/company/verification')
            ->assertJsonPath('data.status', 'rejected')
            ->assertJsonPath('data.note', 'Dokumen NIB tidak terbaca.')
            ->assertJsonPath('data.can_submit', true);

        $this->submitVerification($headers)->assertStatus(201)->assertJsonCount(2, 'data.submissions');
    }

    public function test_suspended_company_cannot_publish_and_can_be_reinstated(): void
    {
        $headers = $this->auth('recruiter@test.com');
        $this->withHeaders($headers)->putJson('/api/company', $this->completeProfile());
        $company = Company::first();
        $adminHeaders = $this->auth('admin@test.com');

        // Hanya perusahaan terverifikasi yang bisa ditangguhkan
        $this->withHeaders($adminHeaders)->patchJson("/api/admin/companies/{$company->id}/status", ['status' => 'suspended', 'note' => 'x'])
            ->assertStatus(409);

        $company->update(['verification_status' => 'verified']);

        $this->withHeaders($adminHeaders)->patchJson("/api/admin/companies/{$company->id}/status", ['status' => 'suspended'])
            ->assertStatus(422);

        $this->withHeaders($adminHeaders)->patchJson("/api/admin/companies/{$company->id}/status", [
            'status' => 'suspended',
            'note' => 'Laporan lowongan palsu.',
        ])->assertStatus(200);

        $this->withHeaders($headers)->postJson('/api/jobs', $this->jobPayload())->assertStatus(403);
        $this->submitVerification($headers)->assertStatus(403)->assertJsonPath('error_code', 'COMPANY_SUSPENDED');

        $this->withHeaders($adminHeaders)->patchJson("/api/admin/companies/{$company->id}/status", ['status' => 'verified'])
            ->assertStatus(200);

        $this->withHeaders($headers)->postJson('/api/jobs', $this->jobPayload())->assertStatus(201);
    }

    public function test_job_can_be_archived_and_hidden_from_public(): void
    {
        Company::create(['owner_id' => $this->recruiter->id, 'name' => 'PT Arsip', 'verification_status' => 'verified']);
        $job = JobVacancy::create([
            'title' => 'QA Engineer',
            'company' => 'PT Arsip',
            'description' => 'Uji perangkat lunak',
            'location' => 'Tegal',
            'status' => 'closed',
            'posted_by' => $this->recruiter->id,
        ]);

        $this->withHeaders($this->auth('recruiter@test.com'))->putJson("/api/jobs/{$job->id}", ['status' => 'archived'])
            ->assertStatus(200)
            ->assertJsonPath('data.status', 'archived');

        $this->getJson("/api/jobs/{$job->id}")->assertStatus(404);
    }

    public function test_change_password(): void
    {
        $headers = $this->auth('recruiter@test.com');

        $this->withHeaders($headers)->putJson('/api/auth/password', [
            'current_password' => 'salah123',
            'password' => 'barubaru123',
            'password_confirmation' => 'barubaru123',
        ])->assertStatus(422)->assertJsonStructure(['errors' => ['current_password']]);

        $this->withHeaders($headers)->putJson('/api/auth/password', [
            'current_password' => 'secret123',
            'password' => 'barubaru123',
            'password_confirmation' => 'beda',
        ])->assertStatus(422)->assertJsonStructure(['errors' => ['password']]);

        $this->withHeaders($headers)->putJson('/api/auth/password', [
            'current_password' => 'secret123',
            'password' => 'barubaru123',
            'password_confirmation' => 'barubaru123',
        ])->assertStatus(200);

        $this->postJson('/api/auth/login', ['email' => 'recruiter@test.com', 'password' => 'barubaru123'])->assertStatus(200);
    }

    public function test_public_registration_cannot_create_admin(): void
    {
        $this->postJson('/api/auth/register', [
            'name' => 'Penyusup',
            'email' => 'penyusup@test.com',
            'password' => 'secret123',
            'role' => 'admin',
        ])->assertStatus(422)->assertJsonStructure(['errors' => ['role']]);
    }

    public function test_admin_requests_revision_then_recruiter_resubmits(): void
    {
        $headers = $this->auth('recruiter@test.com');
        $this->withHeaders($headers)->putJson('/api/company', $this->completeProfile());
        $this->submitVerification($headers)->assertStatus(201);
        $first = Company::first()->verifications()->first()->id;
        $admin = $this->auth('admin@test.com');

        // Revisi wajib disertai catatan
        $this->withHeaders($admin)->patchJson("/api/admin/verifications/{$first}", ['decision' => 'revision'])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['admin_notes']]);

        $this->withHeaders($admin)->patchJson("/api/admin/verifications/{$first}", [
            'decision' => 'revision',
            'admin_notes' => 'Dokumen NIB buram, unggah ulang dengan resolusi lebih tinggi.',
        ])->assertStatus(200)->assertJsonPath('data.status', 'revision_required');

        $this->withHeaders($headers)->getJson('/api/company/verification')
            ->assertJsonPath('data.status', 'revision_required')
            ->assertJsonPath('data.note', 'Dokumen NIB buram, unggah ulang dengan resolusi lebih tinggi.')
            ->assertJsonPath('data.can_submit', true);

        // Selama perlu revisi, lowongan tetap tidak bisa dipublikasikan
        $this->withHeaders($headers)->postJson('/api/jobs', $this->jobPayload())
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'COMPANY_NOT_VERIFIED');

        // Recruiter mengajukan ulang, admin menyetujui
        $this->submitVerification($headers)->assertStatus(201)->assertJsonPath('data.status', 'pending');
        $second = Company::first()->verifications()->first()->id;
        $this->withHeaders($admin)->getJson('/api/admin/verifications?status=revision_required')->assertJsonCount(1, 'data');
        $this->withHeaders($admin)->patchJson("/api/admin/verifications/{$second}", ['decision' => 'approve'])->assertStatus(200);

        $this->withHeaders($headers)->getJson('/api/company')->assertJsonPath('data.verification_status', 'verified');
    }
}
