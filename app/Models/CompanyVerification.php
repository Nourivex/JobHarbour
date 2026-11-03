<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CompanyVerification
 *
 * Satu pengajuan verifikasi perusahaan beserta dokumen pendukung dan hasil pemeriksaan admin.
 * Status pengajuan: pending, approved, rejected.
 */
class CompanyVerification extends Model
{
    protected $fillable = [
        'company_id',
        'legal_name',
        'registration_number',
        'tax_number',
        'applicant_position',
        'documents',
        'notes',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'documents' => 'array',
            'reviewed_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Daftar dokumen untuk respons API: nama asli dan ukuran, tanpa path penyimpanan internal. */
    public function publicDocuments(): array
    {
        return collect($this->documents ?? [])->values()->map(fn ($doc, $index) => [
            'index' => $index,
            'type' => $doc['type'] ?? 'lainnya',
            'name' => $doc['name'] ?? 'dokumen',
            'size' => $doc['size'] ?? null,
            'url' => url("/api/verifications/{$this->id}/documents/{$index}"),
        ])->all();
    }

    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'legal_name' => $this->legal_name,
            'registration_number' => $this->registration_number,
            'tax_number' => $this->tax_number,
            'applicant_position' => $this->applicant_position,
            'notes' => $this->notes,
            'status' => $this->status,
            'admin_notes' => $this->admin_notes,
            'documents' => $this->publicDocuments(),
            'submitted_at' => $this->created_at?->toIso8601String(),
            'reviewed_at' => $this->reviewed_at?->toIso8601String(),
            'reviewer' => $this->reviewer ? ['id' => $this->reviewer->id, 'name' => $this->reviewer->name] : null,
        ];
    }
}
