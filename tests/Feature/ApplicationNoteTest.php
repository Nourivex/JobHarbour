<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Catatan internal recruiter per lamaran: hanya pemilik lowongan dan admin yang punya akses.
 */
class ApplicationNoteTest extends TestCase
{
    use RefreshDatabase;

    protected Application $application;

    protected function setUp(): void
    {
        parent::setUp();

        $recruiter = $this->makeUser('recruiter@test.com', 'recruiter');
        $this->makeUser('recruiter2@test.com', 'recruiter');
        $this->makeUser('admin@test.com', 'admin');
        $applicant = $this->makeUser('pelamar@test.com', 'applicant');

        $job = JobVacancy::create([
            'title' => 'Backend Engineer',
            'company' => 'PT Maju Jaya',
            'description' => 'Membangun REST API.',
            'location' => 'Tegal',
            'requirements' => [['skill' => 'PHP', 'weight' => 3, 'level' => 'required']],
            'status' => 'open',
            'posted_by' => $recruiter->id,
        ]);

        $this->application = Application::create([
            'user_id' => $applicant->id,
            'job_vacancy_id' => $job->id,
            'match_score' => 100,
            'match_category' => 'excellent',
            'matched_skills' => ['PHP'],
            'missing_skills' => [],
            'status' => 'submitted',
        ]);
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

    public function test_owner_recruiter_writes_reads_and_deletes_notes(): void
    {
        $headers = $this->auth('recruiter@test.com');
        $url = "/api/applications/{$this->application->id}/notes";

        $this->withHeaders($headers)->postJson($url, ['body' => ''])
            ->assertStatus(422)
            ->assertJsonStructure(['errors' => ['body']]);

        $created = $this->withHeaders($headers)->postJson($url, ['body' => 'Komunikasi baik, kuat di Laravel.'])
            ->assertStatus(201)
            ->assertHeader('Location')
            ->assertJsonPath('data.body', 'Komunikasi baik, kuat di Laravel.')
            ->assertJsonPath('data.can_delete', true);

        $this->withHeaders($headers)->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.author.name', 'Recruiter Test');

        $noteId = $created->json('data.id');
        $this->withHeaders($headers)->deleteJson("{$url}/{$noteId}")->assertStatus(200);
        $this->withHeaders($headers)->getJson($url)->assertJsonCount(0, 'data');
    }

    public function test_other_recruiter_and_applicant_cannot_access_notes(): void
    {
        $url = "/api/applications/{$this->application->id}/notes";
        $this->withHeaders($this->auth('recruiter@test.com'))->postJson($url, ['body' => 'Catatan rahasia']);

        // Recruiter lain: lamaran bukan untuk lowongannya
        $this->withHeaders($this->auth('recruiter2@test.com'))->getJson($url)
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'IDOR_FORBIDDEN');

        // Pelamar tidak boleh membaca catatan internal
        $this->withHeaders($this->auth('pelamar@test.com'))->getJson($url)->assertStatus(403);

        // Lamaran tidak ada
        $this->withHeaders($this->auth('recruiter@test.com'))->getJson('/api/applications/999999/notes')->assertStatus(404);
    }

    public function test_admin_can_read_but_only_author_can_delete(): void
    {
        $url = "/api/applications/{$this->application->id}/notes";
        $noteId = $this->withHeaders($this->auth('recruiter@test.com'))
            ->postJson($url, ['body' => 'Perlu cek referensi kerja.'])
            ->json('data.id');

        $admin = $this->auth('admin@test.com');
        $this->withHeaders($admin)->getJson($url)
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.can_delete', false);

        $this->withHeaders($admin)->deleteJson("{$url}/{$noteId}")
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'NOTE_FORBIDDEN');
    }
}
