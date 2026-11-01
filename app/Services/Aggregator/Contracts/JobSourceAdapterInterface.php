<?php

namespace App\Services\Aggregator\Contracts;

use App\Models\JobSource;

interface JobSourceAdapterInterface
{
    /**
     * Ambil data lowongan dari sumber dan kembalikan array record yang sudah dinormalisasi.
     *
     * @param JobSource $source
     * @return array Daftar raw record ternormalisasi
     */
    public function fetch(JobSource $source): array;
}
