<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Laporan pengguna atas lowongan/perusahaan, atau banding recruiter atas lowongan yang diturunkan admin.
 */
class Report extends Model
{
    public const CATEGORIES = [
        'scam' => 'Penipuan atau meminta biaya',
        'misleading' => 'Informasi menyesatkan',
        'discrimination' => 'Diskriminatif',
        'spam' => 'Spam atau duplikat',
        'other' => 'Lainnya',
        'appeal' => 'Banding pemulihan',
    ];

    public const STATUSES = ['open', 'in_review', 'resolved', 'dismissed'];

    /** Status yang masih menunggu keputusan admin. */
    public const ACTIVE = ['open', 'in_review'];

    protected $fillable = [
        'kind', 'reporter_id', 'target_type', 'target_id', 'category', 'description',
        'status', 'resolution', 'handled_by', 'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    /** Objek yang dilaporkan (lowongan atau perusahaan); null bila sudah dihapus. */
    public function target(): ?Model
    {
        return match ($this->target_type) {
            'job' => JobVacancy::find($this->target_id),
            'company' => Company::find($this->target_id),
            default => null,
        };
    }

    public function toApiArray(bool $forAdmin = false): array
    {
        $data = [
            'id' => $this->id,
            'kind' => $this->kind,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'category' => $this->category,
            'category_label' => self::CATEGORIES[$this->category] ?? $this->category,
            'description' => $this->description,
            'status' => $this->status,
            'resolution' => $this->resolution,
            'handled_at' => $this->handled_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];

        if ($forAdmin) {
            $target = $this->target();
            $data['reporter'] = $this->reporter?->only(['id', 'name', 'email', 'role']);
            $data['handler'] = $this->handler?->only(['id', 'name']);
            $data['target'] = match (true) {
                $target instanceof JobVacancy => [
                    'title' => $target->title,
                    'subtitle' => $target->company,
                    'status' => $target->status,
                    'moderation_status' => $target->moderation_status,
                ],
                $target instanceof Company => [
                    'title' => $target->name,
                    'subtitle' => $target->city,
                    'status' => $target->verification_status,
                ],
                default => null,
            };
            $data['related_reports'] = self::where('target_type', $this->target_type)->where('target_id', $this->target_id)
                ->whereIn('status', self::ACTIVE)->where('id', '!=', $this->id)->count();
        }

        return $data;
    }
}
