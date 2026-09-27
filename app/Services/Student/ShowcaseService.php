<?php

namespace App\Services\Student;

use App\Models\ShowcaseEndorsement;
use App\Models\StudentShowcase;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Student showcase ("Galeri Karya"). The controller used to answer
 * "Karya berhasil dipublikasi" / "Dukungan berhasil diberikan" without saving
 * anything; every method here persists or throws.
 *
 * These rules hold for everyone, including admins who pass every policy
 * check: only a student publishes (as themselves), nobody endorses their own
 * work, and each person endorses an entry at most once.
 */
class ShowcaseService
{
    public const COVER_DIRECTORY = 'showcase-covers';

    /** @param array{project_title: string, description: string, is_public?: bool, media_urls?: array<int, string>} $data */
    public function publish(User $student, array $data, ?UploadedFile $cover = null): StudentShowcase
    {
        if (! in_array($student->role, StudentShowcase::STUDENT_ROLES, true)) {
            throw ValidationException::withMessages(['project_title' => 'Hanya akun siswa yang dapat mempublikasikan karya.']);
        }

        $path = $cover?->store(self::COVER_DIRECTORY, 'public');

        try {
            return StudentShowcase::create([
                'student_id' => $student->id,
                'project_title' => $data['project_title'],
                'description' => $data['description'],
                'is_public' => (bool) ($data['is_public'] ?? true),
                'media_urls' => $this->cleanLinks($data['media_urls'] ?? []),
                'cover_image_url' => $path,
                'view_count' => 0,
            ]);
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path); // don't leave an orphan upload behind
            }
            throw $e;
        }
    }

    /** @param array{project_title: string, description: string, is_public?: bool, media_urls?: array<int, string>, remove_cover?: bool} $data */
    public function update(StudentShowcase $showcase, array $data, ?UploadedFile $cover = null): StudentShowcase
    {
        $oldStoredCover = $showcase->hasStoredCover() ? $showcase->cover_image_url : null;
        $newPath = $cover?->store(self::COVER_DIRECTORY, 'public');

        $attributes = [
            'project_title' => $data['project_title'],
            'description' => $data['description'],
            'is_public' => (bool) ($data['is_public'] ?? false),
            'media_urls' => $this->cleanLinks($data['media_urls'] ?? []),
        ];
        if ($newPath) {
            $attributes['cover_image_url'] = $newPath;
        } elseif (! empty($data['remove_cover'])) {
            $attributes['cover_image_url'] = null;
        }

        $showcase->update($attributes);

        $coverChanged = array_key_exists('cover_image_url', $attributes);
        if ($coverChanged && $oldStoredCover) {
            Storage::disk('public')->delete($oldStoredCover);
        }

        return $showcase->refresh();
    }

    public function delete(StudentShowcase $showcase): void
    {
        $storedCover = $showcase->hasStoredCover() ? $showcase->cover_image_url : null;
        $showcase->delete(); // endorsements cascade in the database
        if ($storedCover) {
            Storage::disk('public')->delete($storedCover);
        }
    }

    public function endorse(StudentShowcase $showcase, User $endorser, string $text): ShowcaseEndorsement
    {
        if ($showcase->isOwnedBy($endorser)) {
            throw ValidationException::withMessages(['endorsement_text' => 'Anda tidak dapat memberi dukungan untuk karya sendiri.']);
        }
        if (! $showcase->is_public) {
            throw ValidationException::withMessages(['endorsement_text' => 'Karya ini tidak dipublikasikan.']);
        }

        $alreadyEndorsed = fn () => ValidationException::withMessages(['endorsement_text' => 'Anda sudah memberi dukungan untuk karya ini.']);
        if ($showcase->endorsements()->where('endorser_id', $endorser->id)->exists()) {
            throw $alreadyEndorsed();
        }

        try {
            return $showcase->endorsements()->create([
                'endorser_id' => $endorser->id,
                'endorsement_text' => trim($text),
            ]);
        } catch (UniqueConstraintViolationException) {
            // Two requests at once: the unique index let only one through.
            throw $alreadyEndorsed();
        }
    }

    /** Counts a view by someone other than the author. */
    public function recordView(StudentShowcase $showcase, ?User $viewer): void
    {
        if (! $showcase->isOwnedBy($viewer)) {
            // Query-level increment: no lost updates, no model events.
            StudentShowcase::whereKey($showcase->id)->update(['view_count' => DB::raw('view_count + 1')]);
            $showcase->view_count++;
        }
    }

    public function getPopularShowcases()
    {
        return StudentShowcase::published()->with('student')->orderByDesc('view_count')->take(10)->get();
    }

    /** @return array<int, string> */
    private function cleanLinks(array $links): array
    {
        return array_values(array_unique(array_filter(array_map(fn ($l) => trim((string) $l), $links))));
    }
}
