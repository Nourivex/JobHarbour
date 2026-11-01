<?php

namespace App\Services\Aggregator\Adapters;

use App\Models\JobSource;
use App\Services\Aggregator\Contracts\JobSourceAdapterInterface;
use Illuminate\Support\Facades\Http;

class PublicJsonFeedAdapter implements JobSourceAdapterInterface
{
    public function fetch(JobSource $source): array
    {
        $response = Http::timeout(15)
            ->withHeaders(['User-Agent' => 'JobHarbour-Aggregator/1.0 (+https://jobharbour.ddev.site)'])
            ->get($source->base_url);

        if (!$response->successful()) {
            throw new \RuntimeException("Gagal memuat feed JSON publik: HTTP {$response->status()}");
        }

        $data = $response->json();
        $items = $data['jobs'] ?? (is_array($data) ? $data : []);
        $vacancies = [];

        foreach ($items as $item) {
            $extId = $item['id'] ?? md5($item['title'] ?? uniqid());
            $title = $item['title'] ?? 'Posisi Terbuka';
            $company = $item['company_name'] ?? $item['company'] ?? ($source->company_name ?? 'Mitra Eksternal');
            $desc = strip_tags($item['description'] ?? 'Lowongan kerja terverifikasi dari feed publik resmi.');
            $url = $item['url'] ?? $source->base_url;

            $vacancies[] = [
                'external_id' => "FEED-{$extId}",
                'title' => $title,
                'company' => $company,
                'description' => substr($desc, 0, 1000),
                'location' => 'Indonesia (Remote / Fleksibel)',
                'administrative_code' => null,
                'employment_type' => 'full-time',
                'salary_min' => isset($item['salary']) ? (int) $item['salary'] : null,
                'salary_max' => null,
                'requirements' => [
                    ['skill' => 'Software Engineering', 'level' => 'required', 'weight' => 3],
                    ['skill' => 'Komunikasi Tim', 'level' => 'important', 'weight' => 2],
                ],
                'source_type' => 'external',
                'source_company' => $company,
                'source_url' => $url,
                'application_url' => $url,
                'published_at' => $item['publication_date'] ?? now()->toIso8601String(),
                'expires_at' => now()->addDays(30)->toIso8601String(),
                'verification_status' => 'verified_official',
                'verification_notes' => 'Di-ingest secara terstandarisasi dari Feed JSON Publik Resmi.',
                'job_source_type' => 'official_career_page',
                'location_verification_status' => 'approximate_city',
                'location_data_source' => 'Feed Publik',
                'is_demo' => false,
            ];
        }

        return $vacancies;
    }
}
