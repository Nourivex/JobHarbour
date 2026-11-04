<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

/**
 * Catatan tindakan admin. Hanya ditulis lewat AdminAuditLog::record() di server;
 * tidak ada endpoint untuk mengubah atau menghapusnya.
 * Jangan menyimpan password, token, isi dokumen, atau isi CV di kolom meta.
 */
class AdminAuditLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_id', 'action', 'target_type', 'target_id', 'target_label', 'reason', 'meta', 'ip_address',
    ];

    protected $casts = [
        'meta' => 'array',
        'created_at' => 'datetime',
    ];

    /** Label tindakan yang ditampilkan di Admin Portal. */
    public const ACTIONS = [
        'user.suspended' => 'Menangguhkan akun',
        'user.reactivated' => 'Mengaktifkan kembali akun',
        'company.verification_approved' => 'Menyetujui verifikasi perusahaan',
        'company.verification_revision' => 'Meminta revisi verifikasi',
        'company.verification_rejected' => 'Menolak verifikasi perusahaan',
        'company.suspended' => 'Menangguhkan perusahaan',
        'company.reinstated' => 'Memulihkan perusahaan',
        'company.document_viewed' => 'Membuka dokumen verifikasi',
        'job.removed' => 'Menurunkan lowongan',
        'job.restored' => 'Memulihkan lowongan',
        'source.synced' => 'Menjalankan sinkronisasi sumber lowongan',
        'report.in_review' => 'Meninjau laporan',
        'report.resolved' => 'Menyelesaikan laporan',
        'report.dismissed' => 'Menolak laporan',
        'settings.updated' => 'Mengubah pengaturan platform',
        'announcement.published' => 'Menerbitkan pengumuman',
        'announcement.ended' => 'Mengakhiri pengumuman',
        'source.enabled' => 'Mengaktifkan sumber lowongan',
        'source.disabled' => 'Menonaktifkan sumber lowongan',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public static function record(Request $request, string $action, ?Model $target = null, ?string $label = null, ?string $reason = null, array $meta = []): self
    {
        $actor = $request->attributes->get('auth_user') ?? $request->user();

        return self::create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'target_type' => $target ? strtolower(class_basename($target)) : null,
            'target_id' => $target?->getKey(),
            'target_label' => $label,
            'reason' => $reason,
            'meta' => $meta ?: null,
            'ip_address' => $request->ip(),
        ]);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'action_label' => self::ACTIONS[$this->action] ?? $this->action,
            'actor' => $this->actor ? ['id' => $this->actor->id, 'name' => $this->actor->name] : null,
            'target_type' => $this->target_type,
            'target_id' => $this->target_id,
            'target_label' => $this->target_label,
            'reason' => $this->reason,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
