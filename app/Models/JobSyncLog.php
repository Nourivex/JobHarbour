<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobSyncLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_source_id',
        'status',
        'vacancies_fetched',
        'vacancies_created',
        'vacancies_updated',
        'vacancies_expired',
        'duplicates_detected',
        'error_details',
        'duration_seconds',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'duration_seconds' => 'float',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(JobSource::class, 'job_source_id');
    }
}
