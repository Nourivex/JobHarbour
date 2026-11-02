<?php

namespace Tests\Feature;

use App\Models\JobSource;
use App\Models\JobVacancy;
use App\Services\Aggregator\JobAggregationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class JobAggregationEngineTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test normalisasi dan canonical hash kalkulasi untuk deduplikasi.
     */
    public function test_canonical_hash_deduplication(): void
    {
        $service = new JobAggregationService();

        $vac1 = [
            'title' => 'Senior Backend Engineer ',
            'company' => 'PT Teknologi Maju',
            'location' => 'Tegal',
        ];

        $vac2 = [
            'title' => 'senior backend engineer',
            'company' => 'PT. Teknologi Maju',
            'location' => 'tegal',
        ];

        $hash1 = $service->calculateCanonicalHash($vac1);
        $hash2 = $service->calculateCanonicalHash($vac2);

        $this->assertEquals($hash1, $hash2);
    }

    /**
     * Test endpoint ringkasan monitoring kesehatan sumber agregator memerlukan otorisasi admin.
     */
    public function test_aggregator_monitoring_summary_endpoint(): void
    {
        JobSource::create([
            'name' => 'Test Portal Karir',
            'slug' => 'test-portal',
            'source_type' => 'career_page',
            'adapter_class' => 'App\Services\Aggregator\Adapters\AlkindoCareerPageAdapter',
            'base_url' => 'https://example.com/careers',
            'is_enabled' => true,
        ]);

        // 1. Tanpa token otentikasi -> Ditolak 401
        $this->getJson('/api/aggregator/summary')
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'MISSING_BEARER_TOKEN');

        // 2. Akun non-admin (recruiter) -> Ditolak 403
        $recruiter = User::create([
            'name' => 'Recruiter Test',
            'email' => 'recruiter_test@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'recruiter',
        ]);
        $recruiterToken = $this->postJson('/api/auth/login', [
            'email' => 'recruiter_test@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $this->withHeaders(['Authorization' => "Bearer {$recruiterToken}"])
            ->getJson('/api/aggregator/summary')
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'INSUFFICIENT_ROLE');

        // 3. Akun Admin -> Diterima 200 OK
        $admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin_test@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);
        $adminToken = $this->postJson('/api/auth/login', [
            'email' => 'admin_test@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $response = $this->withHeaders(['Authorization' => "Bearer {$adminToken}"])
            ->getJson('/api/aggregator/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'total_sources',
                    'enabled_sources',
                    'vacancies_breakdown' => [
                        'total_in_db',
                        'active',
                        'expired',
                        'demo_records',
                        'real_verified',
                    ],
                    'sources',
                ],
            ]);
    }

    /**
     * Test endpoint sinkronisasi manual memvalidasi otentikasi admin.
     */
    public function test_aggregator_sync_requires_admin_auth(): void
    {
        // 1. Tanpa otentikasi -> 401
        $this->postJson('/api/aggregator/sync', [])
            ->assertStatus(401)
            ->assertJsonPath('error_code', 'MISSING_BEARER_TOKEN');

        // 2. Akun pelamar -> 403
        $applicant = User::create([
            'name' => 'Applicant Test',
            'email' => 'app_test@test.com',
            'password' => Hash::make('secret123'),
            'role' => 'applicant',
        ]);
        $applicantToken = $this->postJson('/api/auth/login', [
            'email' => 'app_test@test.com',
            'password' => 'secret123',
        ])->json('data.token');

        $this->withHeaders(['Authorization' => "Bearer {$applicantToken}"])
            ->postJson('/api/aggregator/sync', [])
            ->assertStatus(403)
            ->assertJsonPath('error_code', 'INSUFFICIENT_ROLE');
    }

    /**
     * Test filter sumber asal (source_type) dan demo isolation pada katalog.
     */
    public function test_catalog_source_type_and_demo_filtering(): void
    {
        // Buat 1 lowongan external nyata
        JobVacancy::create([
            'title' => 'Port Engineer',
            'company' => 'PT Hasnur Internasional Shipping Tbk',
            'description' => 'Operasional armada laut',
            'location' => 'Banjarmasin',
            'status' => 'open',
            'source_type' => 'external',
            'source_url' => 'https://pthis.id/career',
            'application_url' => 'https://pthis.id/career',
            'is_demo' => false,
            'verification_status' => 'verified_official',
            'quota' => 2,
        ]);

        // Buat 1 data demo
        JobVacancy::create([
            'title' => 'Senior Backend Engineer',
            'company' => 'PT Teknologi Maju',
            'description' => 'Demo data backend',
            'location' => 'Tegal',
            'status' => 'open',
            'source_type' => 'partner',
            'source_url' => 'https://jobharbour.ddev.site',
            'application_url' => null,
            'is_demo' => true,
            'verification_status' => 'demo_sample',
            'quota' => 3,
        ]);

        // 1. Filter lowongan nyata eksternal
        $respExternal = $this->getJson('/api/jobs?source_type=external');
        $respExternal->assertStatus(200);
        $this->assertCount(1, $respExternal->json('data'));
        $this->assertEquals('external', $respExternal->json('data.0.source_type'));
        $this->assertFalse((bool) $respExternal->json('data.0.is_demo'));

        // 2. Filter data demo
        $respDemo = $this->getJson('/api/jobs?is_demo=1');
        $respDemo->assertStatus(200);
        $this->assertCount(1, $respDemo->json('data'));
        $this->assertTrue((bool) $respDemo->json('data.0.is_demo'));
        $this->assertEquals('demo_sample', $respDemo->json('data.0.verification_status'));
    }

    private function feedSource(): JobSource
    {
        return JobSource::create([
            'name' => 'Feed Uji',
            'slug' => 'feed-uji',
            'source_type' => 'api',
            'adapter_class' => \App\Services\Aggregator\Adapters\PublicJsonFeedAdapter::class,
            'base_url' => 'https://feed.example.test/jobs',
            'company_name' => 'PT Mitra Feed',
            'is_enabled' => true,
        ]);
    }

    /**
     * API eksternal normal: lowongan dari feed tersimpan sebagai lowongan eksternal.
     * Tidak membutuhkan user ID 1 dan posted_by otomatis diisi admin jika ada, atau null jika tidak ada.
     */
    public function test_external_feed_success_creates_external_vacancies(): void
    {
        // Pengujian ini membuktikan tidak ada hardcoded posted_by = 1 (Issue #10 selesai).
        // Database kosong tanpa user ID 1 sama sekali.
        Http::preventStrayRequests();
        Http::fake([
            'feed.example.test/*' => Http::response(['jobs' => [
                ['id' => 77, 'title' => 'Data Engineer', 'company_name' => 'PT Mitra Feed', 'url' => 'https://feed.example.test/jobs/77'],
            ]], 200),
        ]);

        $log = app(JobAggregationService::class)->syncSource($this->feedSource());

        $this->assertSame('success', $log->status, (string) $log->error_details);
        $this->assertSame(1, (int) $log->vacancies_created);

        $savedVacancy = JobVacancy::where('title', 'Data Engineer')->first();
        $this->assertNotNull($savedVacancy);
        $this->assertNull($savedVacancy->posted_by); // Bebas dari error foreign key user ID 1

        $this->getJson('/api/jobs?source_type=external')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Data Engineer');
    }

    /**
     * API eksternal mati (fail-open): kegagalan dicatat, katalog tetap melayani lowongan yang sudah ada.
     */
    public function test_external_feed_down_is_logged_and_catalog_keeps_serving(): void
    {
        JobVacancy::create([
            'title' => 'Backend Engineer',
            'company' => 'PT Lama',
            'description' => 'Lowongan yang sudah ada sebelum sinkronisasi',
            'location' => 'Tegal',
            'status' => 'open',
        ]);

        Http::preventStrayRequests();
        Http::fake(['feed.example.test/*' => Http::response('Service Unavailable', 503)]);

        $source = $this->feedSource();
        $log = app(JobAggregationService::class)->syncSource($source);

        $this->assertSame('failed', $log->status);
        $this->assertStringContainsString('503', $log->error_details);
        $this->assertNotNull($source->fresh()->last_error_at);

        $this->getJson('/api/jobs')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Backend Engineer');
    }

    /**
     * Uji konfigurasi retry, timeout, dan caching feed eksternal (Soal 2d UTS PWS).
     */
    public function test_external_api_retry_and_cache_configuration(): void
    {
        // 1. Verifikasi konfigurasi di config/services.php
        $this->assertGreaterThan(0, (int) config('services.aggregator.timeout'));
        $this->assertGreaterThanOrEqual(1, (int) config('services.aggregator.retry_attempts'));
        $this->assertGreaterThan(0, (int) config('services.aggregator.cache_ttl_hours'));

        // 2. Verifikasi caching feed saat pengambilan berhasil
        $source = $this->feedSource();
        $cacheKey = "job_source_feed:{$source->id}";
        \Illuminate\Support\Facades\Cache::forget($cacheKey);

        Http::preventStrayRequests();
        Http::fake([
            'feed.example.test/*' => Http::sequence()
                ->push('Transient Error', 500) // retry pertama
                ->push(['jobs' => [
                    ['id' => 99, 'title' => 'DevOps Specialist', 'company_name' => 'PT Mitra Feed', 'url' => 'https://feed.example.test/jobs/99'],
                ]], 200),
        ]);

        $log = app(JobAggregationService::class)->syncSource($source);

        $this->assertSame('success', $log->status);
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has($cacheKey));
        $cachedData = \Illuminate\Support\Facades\Cache::get($cacheKey);
        $this->assertNotEmpty($cachedData);
        $this->assertSame('DevOps Specialist', $cachedData[0]['title']);
    }
}
