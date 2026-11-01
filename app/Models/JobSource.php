<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobSource extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'source_type',
        'adapter_class',
        'base_url',
        'company_name',
        'industry',
        'coverage_regions',
        'is_enabled',
        'polling_interval_hours',
        'last_fetched_at',
        'last_success_at',
        'last_error_at',
        'last_error_message',
        'terms_url',
        'robots_policy_status',
        'total_vacancies_discovered',
        'total_vacancies_active',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'last_fetched_at' => 'datetime',
            'last_success_at' => 'datetime',
            'last_error_at' => 'datetime',
        ];
    }

    public function vacancies(): HasMany
    {
        return $this->hasMany(JobVacancy::class, 'job_source_id');
    }

    public function syncLogs(): HasMany
    {
        return $this->hasMany(JobSyncLog::class, 'job_source_id');
    }
}
