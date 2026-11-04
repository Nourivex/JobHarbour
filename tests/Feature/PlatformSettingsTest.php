<?php

namespace Tests\Feature;

use App\Models\JobSource;
use App\Models\JobVacancy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Pengaturan platform (sembunyikan data demo), pengumuman situs, dan pengelolaan sumber lowongan.
 */
class PlatformSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        foreach (['admin', 'recruiter'] as $role) {
            User::create(['name' => ucfirst($role), 'email' => "{$role}@test.com", 'password' => Hash::make('secret123'), 'role' => $role]);
        }
    }

    private function auth(string $email): array
    {
        $this->flushHeaders();
        $token = $this->postJson('/api/auth/login', ['email' => $email, 'password' => 'secret123'])->json('data.token');

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_admin_can_hide_demo_jobs_from_public_catalog(): void
    {
        $owner = User::where('role', 'recruiter')->first();
        $base = ['company' => 'PT Uji', 'description' => 'Deskripsi.', 'location' => 'Tegal', 'requirements' => [], 'status' => 'open', 'posted_by' => $owner->id];
        JobVacancy::create($base + ['title' => 'Lowongan Nyata', 'is_demo' => false, 'verification_status' => 'unverified']);
        $demo = JobVacancy::create($base + ['title' => 'Lowongan Demo', 'is_demo' => true]);
        // Data seeder ditandai lewat verification_status, bukan is_demo; keduanya harus ikut tersembunyi.
        $sample = JobVacancy::create($base + ['title' => 'Lowongan Contoh', 'is_demo' => false, 'verification_status' => 'demo_sample']);

        $this->getJson('/api/jobs')->assertJsonCount(3, 'data');

        $this->withHeaders($this->auth('recruiter@test.com'))->putJson('/api/admin/settings', ['hide_demo_jobs' => true])->assertStatus(403);

        $admin = $this->auth('admin@test.com');
        $this->withHeaders($admin)->putJson('/api/admin/settings', ['hide_demo_jobs' => true])
            ->assertStatus(200)->assertJsonPath('data.hide_demo_jobs', true);

        $this->flushHeaders()->getJson('/api/jobs')->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Lowongan Nyata');
        $this->getJson("/api/jobs/{$demo->id}")->assertStatus(404);
        $this->getJson("/api/jobs/{$sample->id}")->assertStatus(404);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'settings.updated']);

        $this->withHeaders($this->auth('admin@test.com'))->putJson('/api/admin/settings', ['hide_demo_jobs' => false])->assertStatus(200);
        $this->flushHeaders()->getJson('/api/jobs')->assertJsonCount(3, 'data');
    }

    public function test_announcement_lifecycle(): void
    {
        $admin = $this->auth('admin@test.com');

        $this->withHeaders($admin)->postJson('/api/admin/announcements', ['message' => 'Pendek', 'level' => 'info', 'audience' => 'all'])
            ->assertStatus(422)->assertJsonStructure(['errors' => ['message']]);

        $id = $this->withHeaders($admin)->postJson('/api/admin/announcements', [
            'message' => 'Pemeliharaan sistem Sabtu pukul 22.00 sampai 23.00 WIB.',
            'level' => 'warning', 'audience' => 'all', 'ends_at' => now()->addDay()->toIso8601String(),
        ])->assertStatus(201)->assertJsonPath('data.state', 'live')->json('data.id');

        $this->flushHeaders()->getJson('/api/announcements/active')
            ->assertStatus(200)->assertJsonCount(1, 'data')->assertJsonPath('data.0.level', 'warning');

        $this->withHeaders($this->auth('admin@test.com'))->patchJson("/api/admin/announcements/{$id}/end")->assertStatus(200)->assertJsonPath('data.state', 'ended');
        $this->flushHeaders()->getJson('/api/announcements/active')->assertJsonCount(0, 'data');
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'announcement.ended']);
    }

    public function test_admin_disables_source_with_reason_and_reads_logs(): void
    {
        $source = JobSource::create([
            'name' => 'Karier Contoh', 'slug' => 'contoh', 'source_type' => 'career_page',
            'adapter_class' => 'App\\Services\\Aggregator\\Adapters\\PublicJsonFeedAdapter', 'base_url' => 'https://contoh.test',
            'company_name' => 'PT Contoh', 'is_enabled' => true, 'polling_interval_hours' => 6,
        ]);
        $admin = $this->auth('admin@test.com');

        $this->withHeaders($admin)->patchJson("/api/admin/sources/{$source->id}", ['is_enabled' => false])
            ->assertStatus(422)->assertJsonStructure(['errors' => ['reason']]);
        $this->withHeaders($admin)->patchJson("/api/admin/sources/{$source->id}", ['is_enabled' => false, 'reason' => 'Situs sumber sedang tidak bisa diakses.'])
            ->assertStatus(200)->assertJsonPath('data.is_enabled', false);
        $this->withHeaders($admin)->patchJson("/api/admin/sources/{$source->id}", ['is_enabled' => false, 'reason' => 'Mencoba menonaktifkan lagi.'])
            ->assertStatus(409);

        $this->withHeaders($admin)->getJson("/api/admin/sources/{$source->id}/logs")->assertStatus(200)->assertJsonCount(0, 'data');
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'source.disabled', 'target_id' => $source->id]);
    }
}
