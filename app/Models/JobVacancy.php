<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model JobVacancy
 *
 * Entitas data lowongan pekerjaan beserta kriteria keahlian dan bobotnya.
 * Bagian dari Layanan Microservice: Job Catalog Service.
 */
class JobVacancy extends Model
{
    use HasFactory;

    /**
     * Kolom-kolom yang dapat diisi secara massal (Mass Assignable).
     */
    protected $fillable = [
        'title',
        'company',
        'description',
        'location',
        'employment_type',
        'salary_min',
        'salary_max',
        'quota',
        'filled',
        'requirements', // Kriteria keahlian dan bobot kualifikasi (JSON)
        'status',       // open, closed
        'posted_by',    // ID Recruiter pembuat lowongan
        'expires_at',   // Batas akhir pendaftaran
        'job_source_id',
        'administrative_code',
        'industrial_zone_id',
        'source_type',
        'source_company',
        'source_url',
        'application_url',
        'external_reference',
        'canonical_hash',
        'published_at',
        'first_seen_at',
        'last_seen_at',
        'last_checked_at',
        'verification_status',
        'verification_notes',
        'is_demo',
        'job_source_type',
        'location_verification_status',
        'location_data_source',
        'lifecycle_status',
    ];

    /**
     * Tipe konversi tipe data otomatis (Type Casting).
     */
    protected function casts(): array
    {
        return [
            'moderated_at' => 'datetime',
            'requirements' => 'array', // Otomatis mengubah JSON requirements menjadi array PHP
            'expires_at' => 'datetime',
            'salary_min' => 'integer',
            'salary_max' => 'integer',
            'quota' => 'integer',
            'filled' => 'integer',
            'is_demo' => 'boolean',
            'published_at' => 'datetime',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'last_checked_at' => 'datetime',
        ];
    }

    /**
     * Relasi: Lowongan ini dimiliki/diterbitkan oleh satu Recruiter (Belongs-to).
     */
    public function recruiter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    /**
     * Relasi: Satu lowongan dapat menerima banyak berkas lamaran (One-to-Many).
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_vacancy_id');
    }

    /**
     * Data demo: is_demo = true atau verification_status = demo_sample. Definisi ini sama dengan
     * lencana "Data Demo" di katalog (job-ui.js) agar admin dan pelamar melihat hal yang sama.
     */
    public function scopeDemo(Builder $query, bool $demo = true): Builder
    {
        return $demo
            ? $query->where(fn ($q) => $q->where('is_demo', true)->orWhere('verification_status', 'demo_sample'))
            : $query->where('is_demo', false)
                ->where(fn ($q) => $q->whereNull('verification_status')->orWhere('verification_status', '!=', 'demo_sample'));
    }

    public function isDemoData(): bool
    {
        return (bool) $this->is_demo || $this->verification_status === 'demo_sample';
    }

    /**
     * Relasi: banding recruiter atas lowongan ini (laporan dengan kind = appeal).
     */
    public function appeals(): HasMany
    {
        return $this->hasMany(Report::class, 'target_id')->where('target_type', 'job')->where('kind', 'appeal');
    }
}
