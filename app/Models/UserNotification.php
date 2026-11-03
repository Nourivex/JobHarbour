<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pemberitahuan dalam aplikasi. Dibuat oleh server lewat UserNotification::send() saat ada kejadian
 * yang menyangkut pengguna; pengguna hanya bisa membaca dan menandainya sudah dibaca.
 * Isi pesan tidak memuat catatan internal recruiter atau data pribadi pihak lain.
 */
class UserNotification extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'type', 'title', 'body', 'link', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public static function send(?int $userId, string $type, string $title, ?string $body = null, ?string $link = null): ?self
    {
        if (! $userId) {
            return null;
        }

        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => mb_substr($title, 0, 255),
            'body' => $body,
            'link' => $link,
        ]);
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'body' => $this->body,
            'link' => $this->link,
            'read' => $this->read_at !== null,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
