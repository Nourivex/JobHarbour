<?php

namespace Tests\Feature;

use App\Models\JobVacancy;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Laporan pengguna atas lowongan dan banding recruiter, beserta penanganannya oleh admin.
 */
class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $recruiter;

    protected User $applicant;

    protected JobVacancy $job;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->makeUser('admin@test.com', 'admin');
        $this->recruiter = $this->makeUser('recruiter@test.com', 'recruiter');
        $this->applicant = $this->makeUser('applicant@test.com', 'applicant');
        $this->job = JobVacancy::create([
            'title' => 'Admin Gudang', 'company' => 'PT Uji', 'description' => 'Deskripsi.', 'location' => 'Tegal',
            'requirements' => [['skill' => 'Excel', 'level' => 'required', 'weight' => 3]],
            'status' => 'open', 'posted_by' => $this->recruiter->id, 'is_demo' => false,
        ]);
    }

    private function makeUser(string $email, string $role): User
    {
        return User::create(['name' => ucfirst($role).' Test', 'email' => $email, 'password' => Hash::make('secret123'), 'role' => $role]);
    }

    private function auth(string $email): array
    {
        $token = $this->postJson('/api/auth/login', ['email' => $email, 'password' => 'secret123'])->json('data.token');

        return ['Authorization' => "Bearer {$token}"];
    }

    private function reportBody(array $extra = []): array
    {
        return array_merge([
            'target_type' => 'job',
            'target_id' => $this->job->id,
            'category' => 'scam',
            'description' => 'Recruiter meminta biaya pelatihan Rp500.000 sebelum wawancara.',
        ], $extra);
    }

    public function test_applicant_reports_job_once_and_cannot_report_own_listing(): void
    {
        $headers = $this->auth('applicant@test.com');

        $this->withHeaders($headers)->postJson('/api/reports', $this->reportBody(['description' => 'Pendek']))
            ->assertStatus(422)->assertJsonStructure(['errors' => ['description']]);

        $this->withHeaders($headers)->postJson('/api/reports', $this->reportBody())
            ->assertStatus(201)->assertHeader('Location')
            ->assertJsonPath('data.status', 'open')
            ->assertJsonPath('data.category_label', 'Penipuan atau meminta biaya');

        $this->withHeaders($headers)->postJson('/api/reports', $this->reportBody())
            ->assertStatus(409)->assertJsonPath('error_code', 'DUPLICATE_REPORT');

        $this->flushHeaders()->withHeaders($this->auth('recruiter@test.com'))->postJson('/api/reports', $this->reportBody())
            ->assertStatus(403)->assertJsonPath('error_code', 'SELF_REPORT_FORBIDDEN');

        $this->flushHeaders()->postJson('/api/reports', $this->reportBody())->assertStatus(401);
    }

    public function test_admin_reviews_and_resolves_report_with_audit(): void
    {
        $this->withHeaders($this->auth('applicant@test.com'))->postJson('/api/reports', $this->reportBody())->assertStatus(201);
        $report = Report::first();
        $admin = $this->flushHeaders()->auth('admin@test.com');

        $this->withHeaders($admin)->getJson('/api/admin/overview')->assertJsonPath('data.queue.reports_open', 1);

        $this->withHeaders($admin)->getJson('/api/admin/reports')
            ->assertStatus(200)
            ->assertJsonPath('data.0.reporter.email', 'applicant@test.com')
            ->assertJsonPath('data.0.target.title', 'Admin Gudang');

        $this->withHeaders($admin)->patchJson("/api/admin/reports/{$report->id}", ['status' => 'in_review'])
            ->assertStatus(200)->assertJsonPath('data.status', 'in_review');

        $this->withHeaders($admin)->patchJson("/api/admin/reports/{$report->id}", ['status' => 'resolved'])
            ->assertStatus(422)->assertJsonStructure(['errors' => ['resolution']]);

        $this->withHeaders($admin)->patchJson("/api/admin/reports/{$report->id}", ['status' => 'resolved', 'resolution' => 'Lowongan diturunkan, recruiter diperingatkan.'])
            ->assertStatus(200)->assertJsonPath('data.status', 'resolved');

        $this->withHeaders($admin)->patchJson("/api/admin/reports/{$report->id}", ['status' => 'dismissed', 'resolution' => 'Coba ubah lagi setelah ditutup.'])
            ->assertStatus(409)->assertJsonPath('error_code', 'ALREADY_CLOSED');

        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'report.resolved', 'target_type' => 'report', 'target_id' => $report->id]);

        // Non-admin tidak bisa membuka antrean laporan
        $this->flushHeaders()->withHeaders($this->auth('applicant@test.com'))->getJson('/api/admin/reports')->assertStatus(403);
    }

    public function test_recruiter_appeals_removed_job_once(): void
    {
        $recruiter = $this->auth('recruiter@test.com');

        $this->withHeaders($recruiter)->postJson("/api/jobs/{$this->job->id}/appeal", ['description' => 'Gaji sudah kami sesuaikan dengan kontrak resmi.'])
            ->assertStatus(409)->assertJsonPath('error_code', 'NOT_REMOVED');

        $this->job->forceFill(['moderation_status' => 'removed', 'moderation_reason' => 'Gaji menyesatkan.'])->save();

        $this->withHeaders($recruiter)->postJson("/api/jobs/{$this->job->id}/appeal", ['description' => 'Gaji sudah kami sesuaikan dengan kontrak resmi.'])
            ->assertStatus(201)->assertJsonPath('data.kind', 'appeal');

        $this->withHeaders($recruiter)->postJson("/api/jobs/{$this->job->id}/appeal", ['description' => 'Mengajukan lagi untuk lowongan yang sama.'])
            ->assertStatus(409)->assertJsonPath('error_code', 'DUPLICATE_APPEAL');

        $this->withHeaders($recruiter)->getJson('/api/jobs/my')->assertJsonPath('data.0.has_open_appeal', true);

        $other = $this->makeUser('recruiter2@test.com', 'recruiter');
        $this->flushHeaders()->withHeaders($this->auth('recruiter2@test.com'))->postJson("/api/jobs/{$this->job->id}/appeal", ['description' => 'Bukan lowongan saya tetapi mencoba banding.'])
            ->assertStatus(403)->assertJsonPath('error_code', 'IDOR_FORBIDDEN');

        $this->flushHeaders()->withHeaders($this->auth('admin@test.com'))->getJson('/api/admin/overview')->assertJsonPath('data.queue.appeals_open', 1);
        $this->assertNotNull($other);
    }
}
