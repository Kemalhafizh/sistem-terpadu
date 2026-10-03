<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'title' => $this->title,
            // Android's AnnouncementItem needs a short preview distinct from
            // the full body — there's no separate `summary` column, so this
            // derives one from the real content rather than fabricating text.
            'summary' => Str::limit(strip_tags((string) $this->content), 150),
            'content' => $this->content,
            'category' => $this->category ?: 'Umum',
            'author' => $this->creator->name ?? 'Admin',
            'date' => $this->created_at->diffForHumans(),
            'priority' => $this->priority ?: 'normal',
            'cover_image_url' => $this->image_path ? Storage::url($this->image_path) : null,
            'attachment_url' => $this->attachment_path ? Storage::url($this->attachment_path) : null,
            'published_at' => $this->created_at?->toIso8601String(),
            'expires_at' => $this->expires_at?->toIso8601String(),
            'is_pinned' => (bool) $this->is_pinned,
            'audience' => $this->audienceLabel(),
            'require_acknowledgement' => (bool) $this->require_acknowledgement,
            // Only when the caller loaded this account's own read row.
            'is_read' => $this->whenLoaded('reads', fn () => $this->reads->isNotEmpty()),
            'acknowledged_at' => $this->whenLoaded('reads', fn () => $this->reads->first()?->acknowledged_at?->toIso8601String()),
        ];
    }

    /** "Semua", "Siswa", "Wali murid · XI MIPA 2", … as the web form names them. */
    private function audienceLabel(): string
    {
        $role = match ($this->target_role) {
            null, '' => 'Semua',
            'student', 'siswa' => 'Siswa',
            'parent', 'orang_tua', 'orangtua', 'wali_murid' => 'Wali murid',
            'guru', 'teacher' => 'Guru & staf',
            default => ucfirst(str_replace('_', ' ', (string) $this->target_role)),
        };
        $classroom = $this->target_classroom_id ? $this->targetClassroom?->name : null;

        return $classroom ? "{$role} · {$classroom}" : $role;
    }
}
