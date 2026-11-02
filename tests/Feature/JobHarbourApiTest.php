<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class JobHarbourApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $recruiter;
    protected User $recruiterOther;
    protected User $applicant;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->recruiter = User::create([
            'name' => 'Recruiter Utama',
            'email' => 'recruiter@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'recruiter',
        ]);

        $this->recruiterOther = User::create([
            'name' => 'Recruiter Lain',
            'email' => 'recruiter2@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'recruiter',
        ]);

        $this->applicant = User::create([
            'name' => 'Applicant Test',
            'email' => 'applicant@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'applicant',
            'skills' => ['PHP', 'Laravel', 'MySQL'],
        ]);
    }

    public function test_auth_service_login_and_token_generation(): string
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'message',
                'data' => ['token', 'token_type', 'user'],
                'meta' => ['timestamp', 'version'],
            ]);

        return $response->json('data.token');
    }

    public function test_auth_service_update_skills(): void
    {
        $token = $this->test_auth_service_login_and_token_generation();

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->putJson('/api/profile/skills', [
            'skills' => ['PHP', 'Laravel', 'MySQL', 'Docker'],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.skills_count', 4);
    }

    public function test_job_catalog_jobs_list_and_filter(): void
    {
        JobVacancy::create([
            'title' => 'Backend Engineer',
            'company' => 'PT Maju Jaya',
            'description' => 'Test Desc',
            'location' => 'Tegal',
            'quota' => 2,
            'requirements' => [
                ['skill' => 'PHP', 'weight' => 3, 'level' => 'required'],
                ['skill' => 'Laravel', 'weight' => 3, 'level' => 'required'],
            ],
            'status' => 'open',
            'posted_by' => $this->recruiter->id,
        ]);

        // Test GET all
        $response = $this->getJson('/api/jobs');
        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data');

        // Test filter matching
        $filterMatch = $this->getJson('/api/jobs?location=Tegal');
        $filterMatch->assertStatus(200)->assertJsonCount(1, 'data');

        // Test filter non-matching
        $filterNone = $this->getJson('/api/jobs?location=Surabaya');
        $filterNone->assertStatus(200)->assertJsonCount(0, 'data');
    }

    public function test_job_catalog_recruiter_post_job(): void
    {
        $loginRes = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ]);
        $token = $loginRes->json('data.token');

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/jobs', [
            'title' => 'DevOps Engineer',
            'company' => 'PT Cloud Tech',
            'description' => 'Cloud management',
            'location' => 'Bandung',
            'quota' => 1,
            'requirements' => [
                ['skill' => 'Docker', 'level' => 'required', 'weight' => 3],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', 'DevOps Engineer');
    }

    public function test_job_catalog_recruiter_my_jobs_update_and_delete(): void
    {
        $recruiterToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $otherToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter2@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $job = JobVacancy::create([
            'title' => 'Mobile Developer',
            'company' => 'PT Karya Bahari',
            'description' => 'Flutter & Kotlin',
            'location' => 'Tegal',
            'posted_by' => $this->recruiter->id,
            'status' => 'open',
        ]);

        // 1. Recruiter melihat lowongan miliknya
        $myJobsRes = $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->getJson('/api/jobs/my');
        $myJobsRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data');

        // 2. Proteksi IDOR: Recruiter lain tidak boleh mengedit
        $forbiddenEdit = $this->withHeaders(['Authorization' => "Bearer {$otherToken}"])
            ->putJson("/api/jobs/{$job->id}", [
                'title' => 'Hacked Job Title',
            ]);
        $forbiddenEdit->assertStatus(403)
            ->assertJsonPath('error_code', 'IDOR_FORBIDDEN');

        // 3. Pemilik sah memperbarui lowongan
        $updateRes = $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->putJson("/api/jobs/{$job->id}", [
                'title' => 'Senior Mobile Developer',
                'salary_min' => 10000000,
                'salary_max' => 15000000,
            ]);
        $updateRes->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.title', 'Senior Mobile Developer');

        // 4. Pemilik menutup lowongan
        $deleteRes = $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->deleteJson("/api/jobs/{$job->id}");
        $deleteRes->assertStatus(200)
            ->assertJsonPath('data.status', 'closed');
    }

    public function test_application_matchmaking_apply_and_scoring(): void
    {
        $job = JobVacancy::create([
            'title' => 'PHP Developer',
            'company' => 'PT Maju Jaya',
            'description' => 'Test Desc',
            'location' => 'Tegal',
            'quota' => 1,
            'requirements' => [
                ['skill' => 'PHP', 'weight' => 3, 'level' => 'required'],
                ['skill' => 'Laravel', 'weight' => 3, 'level' => 'required'],
                ['skill' => 'Docker', 'weight' => 2, 'level' => 'important'],
            ],
            'status' => 'open',
            'posted_by' => $this->recruiter->id,
        ]);

        $token = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        // Score: (3 + 3) / (3 + 3 + 2) = 6/8 = 75%
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$token}",
        ])->postJson('/api/applications', [
            'job_id' => $job->id,
            'cover_letter' => 'Saya tertarik.',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.matchmaking_result.match_score', 75)
            ->assertJsonPath('data.matchmaking_result.match_category', 'good');
    }

    public function test_application_upload_cv_pdf_and_download(): void
    {
        $job = JobVacancy::create([
            'title' => 'Fullstack Engineer',
            'company' => 'PT Bahari Tegal',
            'description' => 'Fullstack Dev',
            'posted_by' => $this->recruiter->id,
            'status' => 'open',
        ]);

        $applicantToken = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $recruiterToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $otherToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter2@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        // Unggah dokumen PDF
        $fakeCv = UploadedFile::fake()->create('curriculum_vitae.pdf', 300, 'application/pdf');

        $applyRes = $this->withHeaders(['Authorization' => "Bearer {$applicantToken}"])
            ->post('/api/applications', [
                'job_id' => $job->id,
                'cover_letter' => 'Lampiran CV terlampir.',
                'cv_file' => $fakeCv,
            ]);

        $applyRes->assertStatus(201)
            ->assertJsonPath('status', 'success');

        $appId = $applyRes->json('data.application_id');
        $this->assertNotNull($applyRes->json('data.cv_download_url'));

        // 1. Recruiter pemilik lowongan mengunduh CV (Berhasil)
        $downloadRes = $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->get("/api/applications/{$appId}/cv");
        $downloadRes->assertStatus(200);

        // 2. Proteksi IDOR: Recruiter lain tidak boleh mengunduh CV kandidat (Ditolak 403)
        $forbiddenDownload = $this->withHeaders(['Authorization' => "Bearer {$otherToken}"])
            ->getJson("/api/applications/{$appId}/cv");
        $forbiddenDownload->assertStatus(403)
            ->assertJsonPath('error_code', 'IDOR_FORBIDDEN');
    }

    public function test_pipeline_idor_protection_blocks_unauthorized_recruiter(): void
    {
        $job = JobVacancy::create([
            'title' => 'Security Analyst',
            'company' => 'PT Siber Tegal',
            'description' => 'Penetration Testing',
            'posted_by' => $this->recruiter->id,
            'status' => 'open',
        ]);

        $applicantToken = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $recruiterToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $otherToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter2@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $appRes = $this->withHeaders(['Authorization' => "Bearer {$applicantToken}"])
            ->postJson('/api/applications', ['job_id' => $job->id]);
        $appId = $appRes->json('data.application_id');

        // UJI IDOR: Recruiter 2 mencoba memodifikasi status pelamar di lowongan Recruiter 1
        $attackRes = $this->withHeaders(['Authorization' => "Bearer {$otherToken}"])
            ->patchJson("/api/pipelines/{$appId}/status", [
                'stage' => 'interview',
                'status' => 'in_progress',
                'notes' => 'Coba bajak status',
            ]);

        $attackRes->assertStatus(403)
            ->assertJsonPath('error_code', 'IDOR_FORBIDDEN');

        // Pemilik sah (Recruiter 1) berhasil memajukan status
        $legitRes = $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->patchJson("/api/pipelines/{$appId}/status", [
                'stage' => 'interview',
                'status' => 'in_progress',
                'notes' => 'Lolos ke interview',
            ]);

        $legitRes->assertStatus(200)
            ->assertJsonPath('data.current_stage', 'interview');
    }

    public function test_application_cancellation_prevented_when_already_in_interview(): void
    {
        $job = JobVacancy::create([
            'title' => 'Data Analyst',
            'company' => 'PT Analitika Tegal',
            'description' => 'SQL & Python',
            'posted_by' => $this->recruiter->id,
            'status' => 'open',
        ]);

        $applicantToken = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $recruiterToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $appRes = $this->withHeaders(['Authorization' => "Bearer {$applicantToken}"])
            ->postJson('/api/applications', ['job_id' => $job->id]);
        $appId = $appRes->json('data.application_id');

        // Recruiter memajukan lamaran ke tahap interview
        $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->patchJson("/api/pipelines/{$appId}/status", [
                'stage' => 'interview',
                'status' => 'in_progress',
            ]);

        // Update status di application agar sinkron
        $app = Application::find($appId);
        $app->status = 'interview';
        $app->save();

        // Pelamar mencoba membatalkan lamaran saat sudah di tahap interview -> DITOLAK 409
        $cancelRes = $this->withHeaders(['Authorization' => "Bearer {$applicantToken}"])
            ->deleteJson("/api/applications/{$appId}");

        $cancelRes->assertStatus(409)
            ->assertJsonPath('error_code', 'CANCELLATION_NOT_ALLOWED');
    }

    public function test_jwt_expired_or_invalid_returns_standardized_error_code(): void
    {
        $fakeToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjEyMywiZXhwIjoxNTAwMDAwMDAwfQ.invalidSignatureSample';

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$fakeToken}",
        ])->getJson('/api/auth/me');

        $response->assertStatus(401)
            ->assertJsonPath('status', 'error')
            ->assertJsonStructure(['status', 'message', 'error_code']);
    }

    public function test_validation_error_returns_422_with_field_errors(): void
    {
        // Login tanpa email dan dengan format email salah
        $this->postJson('/api/auth/login', ['password' => 'secret123'])
            ->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['status', 'message', 'data', 'meta', 'errors' => ['email']]);

        // Recruiter membuat lowongan tanpa kolom wajib
        $token = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/jobs', ['title' => 'Tanpa syarat skill'])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['errors' => ['company', 'description', 'location', 'requirements']]);
    }

    public function test_register_requires_password_of_at_least_eight_characters(): void
    {
        // Sandi 7 karakter ditolak saat daftar
        $this->postJson('/api/auth/register', [
            'name' => 'Pelamar Baru',
            'email' => 'baru@test.com',
            'password' => 'abc1234',
        ])
            ->assertStatus(422)
            ->assertJsonPath('error_code', 'VALIDATION_ERROR')
            ->assertJsonStructure(['errors' => ['password']]);

        // Sandi 8 karakter diterima
        $this->postJson('/api/auth/register', [
            'name' => 'Pelamar Baru',
            'email' => 'baru@test.com',
            'password' => 'abcd1234',
        ])->assertStatus(201);
    }

    public function test_missing_resource_returns_404(): void
    {
        $this->getJson('/api/jobs/999999')
            ->assertStatus(404)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('error_code', 'RESOURCE_NOT_FOUND');

        $token = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->getJson('/api/applications/999999')
            ->assertStatus(404)
            ->assertJsonPath('error_code', 'RESOURCE_NOT_FOUND');
    }

    public function test_create_job_returns_201_with_location_header(): void
    {
        $token = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $response = $this->withHeaders(['Authorization' => "Bearer {$token}"])
            ->postJson('/api/jobs', [
                'title' => 'QA Engineer',
                'company' => 'PT Uji Mutu',
                'description' => 'Menguji API',
                'location' => 'Tegal',
                'requirements' => [['skill' => 'PHPUnit', 'level' => 'required']],
            ]);

        $id = $response->json('data.id');
        $response->assertStatus(201)
            ->assertHeader('Location', url("/api/jobs/{$id}"));

        // URL di header Location benar-benar menunjuk resource yang baru dibuat
        $this->getJson("/api/jobs/{$id}")
            ->assertStatus(200)
            ->assertJsonPath('data.title', 'QA Engineer');
    }

    /**
     * Test aturan transisi pipeline: tahapan hanya boleh maju atau ke rejected, tolak rollback (Issue #11).
     */
    public function test_pipeline_rejects_backward_stage_transitions(): void
    {
        $job = JobVacancy::create([
            'title' => 'DevOps Engineer',
            'company' => 'PT Awan Nusantara',
            'description' => 'CI/CD & Cloud',
            'posted_by' => $this->recruiter->id,
            'status' => 'open',
        ]);

        $applicantToken = $this->postJson('/api/auth/login', [
            'email' => 'applicant@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $recruiterToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $appRes = $this->withHeaders(['Authorization' => "Bearer {$applicantToken}"])
            ->postJson('/api/applications', ['job_id' => $job->id]);
        $appId = $appRes->json('data.application_id');

        // 1. Maju ke tahap interview (sah)
        $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->patchJson("/api/pipelines/{$appId}/status", [
                'stage' => 'interview',
                'status' => 'in_progress',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.current_stage', 'interview');

        // 2. Coba rollback mundur ke screening -> Ditolak 422
        $rollbackToScreening = $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->patchJson("/api/pipelines/{$appId}/status", [
                'stage' => 'screening',
                'status' => 'in_progress',
            ]);
        $rollbackToScreening->assertStatus(422)
            ->assertJsonPath('error_code', 'INVALID_STAGE_TRANSITION');

        // 3. Coba rollback mundur ke assessment -> Ditolak 422
        $rollbackToAssessment = $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->patchJson("/api/pipelines/{$appId}/status", [
                'stage' => 'assessment',
                'status' => 'in_progress',
            ]);
        $rollbackToAssessment->assertStatus(422)
            ->assertJsonPath('error_code', 'INVALID_STAGE_TRANSITION');

        // 4. Beralih ke rejected (sah dari tahap mana pun)
        $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->patchJson("/api/pipelines/{$appId}/status", [
                'stage' => 'rejected',
                'status' => 'rejected',
                'notes' => 'Kandidat belum memenuhi kualifikasi interview.',
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.current_stage', 'rejected');

        // 5. Coba ubah dari rejected kembali ke interview -> Ditolak 422 (status final terkunci)
        $reactivateRejected = $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->patchJson("/api/pipelines/{$appId}/status", [
                'stage' => 'interview',
                'status' => 'in_progress',
            ]);
        $reactivateRejected->assertStatus(422)
            ->assertJsonPath('error_code', 'INVALID_STAGE_TRANSITION');
    }
}
