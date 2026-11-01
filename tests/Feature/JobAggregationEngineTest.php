<?php

namespace Tests\Feature;

use App\Models\JobSource;
use App\Models\JobVacancy;
use App\Services\Aggregator\JobAggregationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
     * Test endpoint ringkasan monitoring kesehatan sumber agregator.
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

        $response = $this->getJson('/api/aggregator/summary');

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
}
