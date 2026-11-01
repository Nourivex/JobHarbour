<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobSourceRegistrySeeder extends Seeder
{
    /**
     * Daftarkan Sumber Agregasi Lowongan Resmi Lintas Sektor Industri Indonesia.
     */
    public function run(): void
    {
        $sources = [
            [
                'name' => 'PT Alkindo Naratama Tbk E-Recruitment',
                'slug' => 'alkindo-erecruitment',
                'source_type' => 'career_page',
                'adapter_class' => 'App\Services\Aggregator\Adapters\AlkindoCareerPageAdapter',
                'base_url' => 'https://career.alkindo.co.id/',
                'company_name' => 'PT Alkindo Naratama Tbk',
                'industry' => 'Manufaktur & Kemasan Kertas',
                'coverage_regions' => 'Bandung, Jawa Barat',
                'is_enabled' => true,
                'polling_interval_hours' => 12,
                'terms_url' => 'https://career.alkindo.co.id/',
                'robots_policy_status' => 'allowed',
            ],
            [
                'name' => 'PT Hasnur Internasional Shipping Tbk Careers',
                'slug' => 'hasnur-shipping-careers',
                'source_type' => 'career_page',
                'adapter_class' => 'App\Services\Aggregator\Adapters\HasnurCareerPageAdapter',
                'base_url' => 'https://pthis.id/career',
                'company_name' => 'PT Hasnur Internasional Shipping Tbk',
                'industry' => 'Maritim, Pelayaran & Logistik',
                'coverage_regions' => 'Banjarmasin, Jakarta, Kalimantan',
                'is_enabled' => true,
                'polling_interval_hours' => 12,
                'terms_url' => 'https://pthis.id/career',
                'robots_policy_status' => 'allowed',
            ],
            [
                'name' => 'Kawasan Industri Terpadu Batang (KITB Official)',
                'slug' => 'kitb-career-portal',
                'source_type' => 'partner',
                'adapter_class' => 'App\Services\Aggregator\Adapters\PublicJsonFeedAdapter',
                'base_url' => 'https://kitb.id/careers',
                'company_name' => 'PT Kawasan Industri Terpadu Batang',
                'industry' => 'Manufaktur EV & Industri Hijau',
                'coverage_regions' => 'Batang, Jawa Tengah',
                'is_enabled' => false, // Diaktifkan saat API feed publik live
                'polling_interval_hours' => 24,
                'terms_url' => 'https://kitb.id',
                'robots_policy_status' => 'allowed',
            ],
            [
                'name' => 'KEK Nongsa Digital Park Career Portal',
                'slug' => 'nongsa-digital-careers',
                'source_type' => 'partner',
                'adapter_class' => 'App\Services\Aggregator\Adapters\PublicJsonFeedAdapter',
                'base_url' => 'https://nongsadigital.com/careers',
                'company_name' => 'PT Taman Resor Internet (Citramas)',
                'industry' => 'Teknologi & Pusat Data Hyperscale',
                'coverage_regions' => 'Batam, Kepulauan Riau',
                'is_enabled' => false,
                'polling_interval_hours' => 24,
                'terms_url' => 'https://nongsadigital.com',
                'robots_policy_status' => 'allowed',
            ],
            [
                'name' => 'BUMN Pelindo Karir & Rekrutmen Bersama',
                'slug' => 'pelindo-maritime-careers',
                'source_type' => 'partner',
                'adapter_class' => 'App\Services\Aggregator\Adapters\PublicJsonFeedAdapter',
                'base_url' => 'https://pelindo.co.id/karir',
                'company_name' => 'PT Pelabuhan Indonesia (Pelindo)',
                'industry' => 'Pelabuhan & Tol Laut',
                'coverage_regions' => 'Nasional (Tanjung Priok, Tanjung Perak, Belawan, Makassar)',
                'is_enabled' => false,
                'polling_interval_hours' => 24,
                'terms_url' => 'https://pelindo.co.id',
                'robots_policy_status' => 'allowed',
            ]
        ];

        foreach ($sources as $s) {
            DB::table('job_sources')->updateOrInsert(
                ['slug' => $s['slug']],
                array_merge($s, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }
}
