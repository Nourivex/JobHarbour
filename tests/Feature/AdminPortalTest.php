<?php

namespace Tests\Feature;

use App\Models\AdminAuditLog;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Admin Portal: ringkasan, pengguna (tangguhkan/aktifkan), perusahaan, moderasi lowongan, dan log audit.
 */
class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $recruiter;

    protected User $applicant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->makeUser('admin@test.com', 'admin');
        $this->recruiter = $this->makeUser('recruiter@test.com', 'recruiter');
        $this->applicant = $this->makeUser('applicant@test.com', 'applicant');
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

    private function auth(string $email): array
    {
        $token = $this->postJson('/api/auth/login', ['email' => $email, 'password' => 'secret123'])->json('data.token');

        return ['Authorization' => "Bearer {$token}"];
    }

    private function openJob(): JobVacancy
    {
        return JobVacancy::create([
            'title' => 'Lowongan Uji Moderasi',
            'company' => 'PT Uji',
            'description' => 'Deskripsi.',
            'location' => 'Tegal',
            'requirements' => [['skill' => 'PHP', 'level' => 'required', 'weight' => 3]],
            'status' => 'open',
            'posted_by' => $this->recruiter->id,
            'source_type' => 'direct',
            'verification_status' => 'unverified',
            'is_demo' => false,
        ]);
    }

    public function test_admin_endpoints_reject_non_admin_users(): void
    {
        foreach (['recruiter@test.com', 'applicant@test.com'] as $email) {
            $headers = $this->auth($email);
            foreach (['/api/admin/overview', '/api/admin/users', '/api/admin/companies', '/api/admin/jobs', '/api/admin/audit-logs'] as $url) {
                $this->withHeaders($headers)->getJson($url)
                    ->assertStatus(403)
                    ->assertJsonPath('error_code', 'INSUFFICIENT_ROLE');
            }
        }

        $this->flushHeaders()->getJson('/api/admin/overview')->assertStatus(401);
    }

    public function test_overview_counts_real_data_and_separates_demo(): void
    {
        $this->openJob();
        JobVacancy::create([
            'title' => 'Lowongan Demo', 'company' => 'PT Demo', 'description' => 'Demo.', 'location' => 'Tegal',
            'requirements' => [], 'status' => 'open', 'posted_by' => $this->recruiter->id, 'is_demo' => true,
        ]);

        $this->withHeaders($this->auth('admin@test.com'))->getJson('/api/admin/overview')
            ->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.users.by_role.recruiter', 1)
            ->assertJsonPath('data.jobs.open_real', 1)
            ->assertJsonPath('data.jobs.open_demo', 1)
            ->assertJsonPath('data.queue.users_suspended', 0)
            ->assertJsonStructure(['data' => ['queue', 'users', 'companies', 'jobs', 'applications', 'sources', 'market', 'growth', 'skills', 'recent_actions'], 'meta' => ['window_days']])
            // Kesehatan pasar: 2 lowongan terbuka tanpa lamaran, skor rata-rata belum ada (null, bukan 0)
            ->assertJsonPath('data.market.open_jobs', 2)
            ->assertJsonPath('data.market.open_without_applicants', 2)
            ->assertJsonPath('data.market.avg_match_score', null)
            ->assertJsonCount(8, 'data.growth.week_starts')
            ->assertJsonPath('data.growth.jobs.7', 2)
            // Skill PHP diminta 1 lowongan; pelamar belum punya skill
            ->assertJsonPath('data.skills.items.0.skill', 'PHP')
            ->assertJsonPath('data.skills.items.0.jobs', 1)
            ->assertJsonPath('data.skills.items.0.applicants', 0);
    }

    public function test_users_list_supports_search_filter_and_pagination(): void
    {
        $response = $this->withHeaders($this->auth('admin@test.com'))->getJson('/api/admin/users?role=recruiter&per_page=5');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.email', 'recruiter@test.com')
            ->assertJsonPath('meta.pagination.total', 1)
            ->assertJsonMissingPath('data.0.password');

        $this->withHeaders($this->auth('admin@test.com'))->getJson('/api/admin/users?q=applicant')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.role', 'applicant');
    }

    public function test_suspending_user_blocks_login_and_existing_token_immediately(): void
    {
        $adminHeaders = $this->auth('admin@test.com');
        $applicantHeaders = $this->auth('applicant@test.com');

        // Alasan wajib dan minimal 10 karakter
        $this->withHeaders($adminHeaders)->patchJson("/api/admin/users/{$this->applicant->id}/status", ['status' => 'suspended'])
            ->assertStatus(422)->assertJsonStructure(['errors' => ['reason']]);

        $this->withHeaders($adminHeaders)->patchJson("/api/admin/users/{$this->applicant->id}/status", [
            'status' => 'suspended',
            'reason' => 'Mengirim lamaran spam ke banyak lowongan.',
        ])->assertStatus(200)->assertJsonPath('data.status', 'suspended');

        // Token lama langsung ditolak, dan login baru juga ditolak
        $this->withHeaders($applicantHeaders)->getJson('/api/auth/me')
            ->assertStatus(403)->assertJsonPath('error_code', 'ACCOUNT_SUSPENDED');
        $this->postJson('/api/auth/login', ['email' => 'applicant@test.com', 'password' => 'secret123'])
            ->assertStatus(403)->assertJsonPath('error_code', 'ACCOUNT_SUSPENDED');

        // Aktifkan kembali
        $this->withHeaders($adminHeaders)->patchJson("/api/admin/users/{$this->applicant->id}/status", ['status' => 'active'])
            ->assertStatus(200)->assertJsonPath('data.status', 'active');
        $this->withHeaders($applicantHeaders)->getJson('/api/auth/me')->assertStatus(200);

        $this->assertSame(['user.reactivated', 'user.suspended'], AdminAuditLog::where('target_id', $this->applicant->id)
            ->where('target_type', 'user')->latest('id')->pluck('action')->all());
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'user.suspended', 'actor_id' => $this->admin->id, 'reason' => 'Mengirim lamaran spam ke banyak lowongan.']);
    }

    public function test_admin_cannot_suspend_self_or_other_admin(): void
    {
        $other = $this->makeUser('admin2@test.com', 'admin');
        $headers = $this->auth('admin@test.com');
        $body = ['status' => 'suspended', 'reason' => 'Uji perlindungan akun admin.'];

        $this->withHeaders($headers)->patchJson("/api/admin/users/{$this->admin->id}/status", $body)
            ->assertStatus(403)->assertJsonPath('error_code', 'SELF_ACTION_FORBIDDEN');
        $this->withHeaders($headers)->patchJson("/api/admin/users/{$other->id}/status", $body)
            ->assertStatus(403)->assertJsonPath('error_code', 'ADMIN_PROTECTED');

        $this->assertSame(0, AdminAuditLog::count());
    }

    public function test_removed_job_is_hidden_and_cannot_be_reopened_until_restored(): void
    {
        $job = $this->openJob();
        $adminHeaders = $this->auth('admin@test.com');

        $this->withHeaders($adminHeaders)->patchJson("/api/admin/jobs/{$job->id}/moderation", ['action' => 'remove'])
            ->assertStatus(422)->assertJsonStructure(['errors' => ['reason']]);

        $this->withHeaders($adminHeaders)->patchJson("/api/admin/jobs/{$job->id}/moderation", [
            'action' => 'remove',
            'reason' => 'Gaji yang dicantumkan menyesatkan pelamar.',
        ])->assertStatus(200)->assertJsonPath('data.moderation_status', 'removed');

        // Hilang dari katalog publik dan tidak bisa dilamar
        $this->getJson('/api/jobs')->assertJsonCount(0, 'data');
        $this->getJson("/api/jobs/{$job->id}")->assertStatus(404);
        $this->withHeaders($this->auth('applicant@test.com'))->postJson('/api/applications', ['job_vacancy_id' => $job->id])
            ->assertStatus(400);

        // Recruiter tetap melihatnya di daftar miliknya beserta alasan, tetapi tidak bisa membukanya lagi
        $recruiterHeaders = $this->auth('recruiter@test.com');
        $this->withHeaders($recruiterHeaders)->getJson('/api/jobs/my')
            ->assertJsonPath('data.0.moderation_status', 'removed')
            ->assertJsonPath('data.0.moderation_reason', 'Gaji yang dicantumkan menyesatkan pelamar.');
        $this->withHeaders($recruiterHeaders)->patchJson("/api/jobs/{$job->id}", ['status' => 'closed'])->assertStatus(200);
        $this->withHeaders($recruiterHeaders)->patchJson("/api/jobs/{$job->id}", ['status' => 'open'])
            ->assertStatus(403)->assertJsonPath('error_code', 'JOB_REMOVED_BY_ADMIN');

        // Setelah dipulihkan, recruiter bisa membukanya kembali
        $this->withHeaders($adminHeaders)->patchJson("/api/admin/jobs/{$job->id}/moderation", ['action' => 'restore'])
            ->assertStatus(200)->assertJsonPath('data.moderation_status', 'none');
        $this->withHeaders($recruiterHeaders)->patchJson("/api/jobs/{$job->id}", ['status' => 'open'])->assertStatus(200);
        $this->getJson("/api/jobs/{$job->id}")->assertStatus(200);

        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'job.removed', 'target_type' => 'jobvacancy', 'target_id' => $job->id]);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'job.restored', 'target_id' => $job->id]);
    }

    public function test_companies_list_and_detail_and_audit_of_suspension(): void
    {
        $company = Company::create([
            'owner_id' => $this->recruiter->id,
            'name' => 'PT Pelabuhan Digital',
            'city' => 'Tegal',
            'verification_status' => 'verified',
        ]);
        $this->openJob();
        $headers = $this->auth('admin@test.com');

        $this->withHeaders($headers)->getJson('/api/admin/companies?q=pelabuhan')
            ->assertStatus(200)
            ->assertJsonPath('data.0.name', 'PT Pelabuhan Digital')
            ->assertJsonPath('data.0.jobs_open', 1)
            ->assertJsonPath('meta.pagination.total', 1);

        $this->withHeaders($headers)->getJson("/api/admin/companies/{$company->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.owner.email', 'recruiter@test.com')
            ->assertJsonCount(1, 'data.jobs');

        $this->withHeaders($headers)->patchJson("/api/admin/companies/{$company->id}/status", [
            'status' => 'suspended',
            'note' => 'Laporan penipuan dari beberapa pelamar.',
        ])->assertStatus(200);

        $this->withHeaders($headers)->getJson('/api/admin/audit-logs?action=company.suspended')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.action_label', 'Menangguhkan perusahaan')
            ->assertJsonPath('data.0.actor.id', $this->admin->id)
            ->assertJsonPath('data.0.target_label', 'PT Pelabuhan Digital');
    }
}
