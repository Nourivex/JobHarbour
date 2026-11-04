<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Pengumuman situs (mis. jadwal pemeliharaan) yang tampil sebagai banner.
 * audience: all | applicant | recruiter.
 */
class Announcement extends Model
{
    public const LEVELS = ['info', 'warning'];

    public const AUDIENCES = ['all', 'applicant', 'recruiter'];

    protected $fillable = ['message', 'level', 'audience', 'starts_at', 'ends_at', 'is_active', 'created_by'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Pengumuman yang sedang berlaku sekarang. */
    public function scopeLive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function state(): string
    {
        if (! $this->is_active || ($this->ends_at && $this->ends_at->isPast())) {
            return 'ended';
        }

        return $this->starts_at && $this->starts_at->isFuture() ? 'scheduled' : 'live';
    }

    public function toApiArray(bool $forAdmin = false): array
    {
        $data = [
            'id' => $this->id,
            'message' => $this->message,
            'level' => $this->level,
            'audience' => $this->audience,
            'ends_at' => $this->ends_at?->toIso8601String(),
        ];

        if ($forAdmin) {
            $data += [
                'state' => $this->state(),
                'starts_at' => $this->starts_at?->toIso8601String(),
                'creator' => $this->creator?->only(['id', 'name']),
                'created_at' => $this->created_at?->toIso8601String(),
            ];
        }

        return $data;
    }
}
