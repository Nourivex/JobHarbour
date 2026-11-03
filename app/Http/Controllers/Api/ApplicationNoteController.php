<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationNote;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Class ApplicationNoteController
 *
 * Catatan internal recruiter per lamaran. Hanya pemilik lowongan dan admin yang boleh
 * membaca atau menulis; pelamar tidak memiliki akses (rute dijaga jwt.auth:recruiter).
 */
class ApplicationNoteController extends Controller
{
    use ApiResponse;

    /**
     * Method  : GET
     * Endpoint: /api/applications/{id}/notes
     */
    public function index(Request $request, int $id): JsonResponse
    {
        $user = $this->currentUser($request);
        $application = $this->findOwnedApplication($user, $id);

        if ($application instanceof JsonResponse) {
            return $application;
        }

        $notes = ApplicationNote::with('author:id,name')
            ->where('application_id', $application->id)
            ->latest('id')
            ->get()
            ->map(fn (ApplicationNote $note) => $note->toApiArray($user->id));

        return $this->successResponse($notes, 'Catatan lamaran berhasil dimuat.');
    }

    /**
     * Method  : POST
     * Endpoint: /api/applications/{id}/notes
     * Body    : { "body": "..." }
     */
    public function store(Request $request, int $id): JsonResponse
    {
        $user = $this->currentUser($request);
        $application = $this->findOwnedApplication($user, $id);

        if ($application instanceof JsonResponse) {
            return $application;
        }

        $validator = Validator::make($request->all(), [
            'body' => 'required|string|max:2000',
        ], [
            'body.required' => 'Isi catatan tidak boleh kosong.',
            'body.max' => 'Catatan maksimal 2000 karakter.',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator->errors(), 'Catatan gagal disimpan karena data tidak valid.');
        }

        $note = ApplicationNote::create([
            'application_id' => $application->id,
            'author_id' => $user->id,
            'body' => trim($request->body),
        ])->load('author:id,name');

        return $this->createdResponse($note->toApiArray($user->id), 'Catatan berhasil disimpan.')
            ->header('Location', url("/api/applications/{$application->id}/notes/{$note->id}"));
    }

    /**
     * Hanya penulis catatan yang boleh menghapus catatannya sendiri.
     *
     * Method  : DELETE
     * Endpoint: /api/applications/{id}/notes/{noteId}
     */
    public function destroy(Request $request, int $id, int $noteId): JsonResponse
    {
        $user = $this->currentUser($request);
        $application = $this->findOwnedApplication($user, $id);

        if ($application instanceof JsonResponse) {
            return $application;
        }

        $note = ApplicationNote::where('application_id', $application->id)->find($noteId);

        if (! $note) {
            return $this->notFoundResponse('Catatan tidak ditemukan.');
        }

        if ($note->author_id !== $user->id) {
            return $this->forbiddenResponse('Catatan hanya bisa dihapus oleh penulisnya.', 'NOTE_FORBIDDEN');
        }

        $note->delete();

        return $this->successResponse(null, 'Catatan berhasil dihapus.');
    }

    private function currentUser(Request $request)
    {
        return $request->attributes->get('auth_user') ?? $request->user();
    }

    /** Lamaran harus ada dan lowongannya milik recruiter yang sedang login, kecuali admin. */
    private function findOwnedApplication($user, int $id): Application|JsonResponse
    {
        $application = Application::with('jobVacancy:id,posted_by')->find($id);

        if (! $application) {
            return $this->notFoundResponse("Data lamaran dengan nomor ID {$id} tidak ditemukan.");
        }

        $isOwner = $application->jobVacancy && $application->jobVacancy->posted_by === $user->id;

        if (! $isOwner && $user->role !== 'admin') {
            return $this->forbiddenResponse('Akses ditolak. Lamaran ini bukan untuk lowongan milik Anda.', 'IDOR_FORBIDDEN');
        }

        return $application;
    }
}
