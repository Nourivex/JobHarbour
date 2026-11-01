<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Company
 *
 * Profil perusahaan milik satu akun recruiter beserta status verifikasinya.
 * Status verifikasi: unverified, pending, verified, rejected, suspended.
 */
class Company extends Model
{
    public const STATUSES = ['unverified', 'pending', 'verified', 'rejected', 'suspended'];

    protected $fillable = [
        'owner_id',
        'name',
        'industry',
        'size',
        'founded_year',
        'description',
        'address',
        'city',
        'website',
        'email',
        'phone',
        'logo_path',
        'verification_status',
        'verification_note',
        'verified_at',
    ];

    protected $hidden = ['logo_path'];

    protected $appends = ['logo_url', 'is_verified'];

    protected function casts(): array
    {
        return [
            'founded_year' => 'integer',
            'verified_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function verifications(): HasMany
    {
        return $this->hasMany(CompanyVerification::class)->latest('id');
    }

    public function latestVerification(): HasOne
    {
        return $this->hasOne(CompanyVerification::class)->latestOfMany();
    }

    /** Hanya perusahaan terverifikasi yang boleh mempublikasikan lowongan. */
    public function canPublishJobs(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function getIsVerifiedAttribute(): bool
    {
        return $this->canPublishJobs();
    }

    /** Logo dilayani lewat endpoint API supaya tidak bergantung pada symlink storage publik. */
    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path ? url("/api/companies/{$this->id}/logo").'?v='.$this->updated_at?->timestamp : null;
    }
}
