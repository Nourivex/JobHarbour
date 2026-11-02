<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ApplicationNote
 *
 * Catatan internal recruiter pada satu lamaran (hasil screening, kesan interview, dan sejenisnya).
 * Tidak pernah ditampilkan kepada pelamar.
 */
class ApplicationNote extends Model
{
    protected $fillable = ['application_id', 'author_id', 'body'];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function toApiArray(?int $viewerId = null): array
    {
        return [
            'id' => $this->id,
            'application_id' => $this->application_id,
            'body' => $this->body,
            'author' => $this->author ? ['id' => $this->author->id, 'name' => $this->author->name] : null,
            'can_delete' => $viewerId !== null && $viewerId === $this->author_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
