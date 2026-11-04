<?php

namespace Tests\Feature;

use App\Models\JobVacancy;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Lonceng pemberitahuan: dibuat server saat tahap lamaran berubah atau admin mengambil keputusan,
 * dan hanya bisa dibaca pemiliknya.
 */
class NotificationTest extends TestCase
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
        $this->applicant = $this->makeUser('applicant@test.com', 'applicant', ['PHP']);
        $this->job = JobVacancy::create([
            'title' => 'Backend Engineer', 'company' => 'PT Uji', 'description' => 'Deskripsi.', 'location' => 'Tegal',
            'requirements' => [['skill' => 'PHP', 'level' => 'required', 'weight' => 3]],
            'status' => 'open', 'posted_by' => $this->recruiter->id, 'is_demo' => false,
        ]);
    }

    private function makeUser(string $email, string $role, array $skills = []): User
    {
        return User::create(['name' => ucfirst($role).' Test', 'email' => $email, 'password' => Hash::make('secret123'), 'role' => $role, 'skills' => $skills]);
    }

    private function auth(string $email): array
    {
        $this->flushHeaders();
        $token = $this->postJson('/api/auth/login', ['email' => $email, 'password' => 'secret123'])->json('data.token');

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_stage_changes_notify_applicant_and_owner_can_mark_read(): void
    {
        $appId = $this->withHeaders($this->auth('applicant@test.com'))
            ->postJson('/api/applications', ['job_vacancy_id' => $this->job->id])->assertStatus(201)->json('data.application_id');

        $recruiter = $this->auth('recruiter@test.com');
        $this->withHeaders($recruiter)->patchJson("/api/applications/{$appId}/stage", ['stage' => 'assessment'])->assertStatus(200);
        $this->withHeaders($recruiter)->patchJson("/api/applications/{$appId}/stage", [
            'stage' => 'interview', 'interview_schedule' => ['datetime' => '2026-11-20T09:00:00+07:00', 'type' => 'online'],
        ])->assertStatus(200);

        $applicant = $this->auth('applicant@test.com');
        $res = $this->withHeaders($applicant)->getJson('/api/notifications')
            ->assertStatus(200)
            ->assertJsonPath('meta.unread_count', 2)
            ->assertJsonPath('data.0.title', 'Kamu diundang wawancara')
            ->assertJsonPath('data.1.title', 'Lamaranmu lanjut ke tahap Assessment');
        $this->assertStringContainsString('Backend Engineer', $res->json('data.0.body'));

        $first = $res->json('data.0.id');
        $this->withHeaders($applicant)->patchJson("/api/notifications/{$first}/read")->assertStatus(200)->assertJsonPath('data.read', true);
        $this->withHeaders($applicant)->getJson('/api/notifications')->assertJsonPath('meta.unread_count', 1);

        // Pemberitahuan orang lain tidak bisa disentuh
        $this->withHeaders($this->auth('recruiter@test.com'))->patchJson("/api/notifications/{$first}/read")->assertStatus(404);

        $this->withHeaders($this->auth('applicant@test.com'))->postJson('/api/notifications/read-all')->assertStatus(200)->assertJsonPath('data.marked', 1);
        $this->assertSame(0, UserNotification::where('user_id', $this->applicant->id)->whereNull('read_at')->count());
    }

    public function test_admin_decisions_notify_recruiter_and_reporter(): void
    {
        $this->withHeaders($this->auth('applicant@test.com'))->postJson('/api/reports', [
            'target_type' => 'job', 'target_id' => $this->job->id, 'category' => 'misleading',
            'description' => 'Gaji yang tertulis berbeda jauh dengan yang disampaikan saat dihubungi.',
        ])->assertStatus(201);

        $admin = $this->auth('admin@test.com');
        $this->withHeaders($admin)->patchJson("/api/admin/jobs/{$this->job->id}/moderation", ['action' => 'remove', 'reason' => 'Informasi gaji menyesatkan pelamar.'])->assertStatus(200);
        $reportId = $this->withHeaders($admin)->getJson('/api/admin/reports')->json('data.0.id');
        $this->withHeaders($admin)->patchJson("/api/admin/reports/{$reportId}", ['status' => 'resolved', 'resolution' => 'Lowongan diturunkan sampai gaji diperbaiki.'])->assertStatus(200);

        $this->withHeaders($this->auth('recruiter@test.com'))->getJson('/api/notifications')
            ->assertJsonPath('data.0.type', 'job.removed')
            ->assertJsonPath('data.0.link', "/recruiter/jobs/{$this->job->id}");

        $this->withHeaders($this->auth('applicant@test.com'))->getJson('/api/notifications')
            ->assertJsonPath('data.0.type', 'report.resolved')
            ->assertJsonPath('data.0.title', 'Laporanmu sudah ditinjau');

        $this->flushHeaders()->getJson('/api/notifications')->assertStatus(401);
    }
}
