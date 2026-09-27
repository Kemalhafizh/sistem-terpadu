<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\StudentShowcase;
use App\Models\User;
use App\Services\Student\ShowcaseService;
use Illuminate\Http\Request;

/**
 * Galeri Karya Siswa. This was a stub: store() and endorse() reported success
 * without saving anything, the gallery was one hard-coded card by an invented
 * student, and the editor/profile pages were static text. Only gallery and
 * "my" even had routes.
 */
class ShowcaseController extends Controller
{
    public function __construct(protected ShowcaseService $service) {}

    /** Published entries by every student, newest first. */
    public function gallery(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $showcases = StudentShowcase::published()
            ->with(['student.student.classroom'])
            ->withCount('endorsements')
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('project_title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhereHas('student', fn ($s) => $s->where('name', 'like', "%{$search}%"))))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('showcase.gallery', compact('showcases', 'search'));
    }

    /** The logged-in student's own entries, public and private. */
    public function myShowcase(Request $request)
    {
        $showcases = StudentShowcase::where('student_id', $request->user()->id)
            ->withCount('endorsements')
            ->latest()
            ->paginate(12);

        return view('showcase.my', [
            'showcases' => $showcases,
            'canCreate' => $request->user()->can('create', StudentShowcase::class),
        ]);
    }

    public function create()
    {
        $this->authorize('create', StudentShowcase::class);

        return view('showcase.editor', ['showcase' => new StudentShowcase(['is_public' => true])]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', StudentShowcase::class);

        $showcase = $this->service->publish($request->user(), $this->validated($request), $request->file('cover_image'));

        return redirect()->route('showcase.show', $showcase)
            ->with('success', $showcase->is_public
                ? 'Karya berhasil dipublikasikan.'
                : 'Karya tersimpan sebagai privat (hanya Anda dan guru yang dapat melihatnya).');
    }

    public function show(Request $request, StudentShowcase $showcase)
    {
        $this->authorize('view', $showcase);

        $this->service->recordView($showcase, $request->user());
        $showcase->load(['student.student.classroom', 'endorsements' => fn ($q) => $q->with('endorser')->latest()]);

        $user = $request->user();
        $alreadyEndorsed = $showcase->endorsements->contains('endorser_id', $user->id);

        return view('showcase.show', [
            'showcase' => $showcase,
            'canEdit' => $user->can('update', $showcase),
            'canEndorse' => $user->can('endorse', $showcase) && ! $alreadyEndorsed && ! $showcase->isOwnedBy($user),
            'alreadyEndorsed' => $alreadyEndorsed,
        ]);
    }

    public function edit(StudentShowcase $showcase)
    {
        $this->authorize('update', $showcase);

        return view('showcase.editor', compact('showcase'));
    }

    public function update(Request $request, StudentShowcase $showcase)
    {
        $this->authorize('update', $showcase);

        $data = $this->validated($request) + ['remove_cover' => $request->boolean('remove_cover')];
        $this->service->update($showcase, $data, $request->file('cover_image'));

        return redirect()->route('showcase.show', $showcase)->with('success', 'Perubahan karya tersimpan.');
    }

    public function destroy(StudentShowcase $showcase)
    {
        $this->authorize('delete', $showcase);

        $this->service->delete($showcase);

        return redirect()->route('showcase.my')->with('success', 'Karya dihapus.');
    }

    public function endorse(Request $request, StudentShowcase $showcase)
    {
        $this->authorize('endorse', $showcase);

        $validated = $request->validate([
            'endorsement_text' => 'required|string|min:5|max:500',
        ], [
            'endorsement_text.required' => 'Tulis dukungan Anda.',
            'endorsement_text.min' => 'Dukungan minimal 5 karakter.',
        ]);

        $this->service->endorse($showcase, $request->user(), $validated['endorsement_text']);

        return redirect()->route('showcase.show', $showcase)->with('success', 'Dukungan Anda tersimpan.');
    }

    /** A student's public profile: their published entries only. */
    public function publicProfile(User $user)
    {
        abort_unless(in_array($user->role, StudentShowcase::STUDENT_ROLES, true), 404);

        $user->load('student.classroom');
        $showcases = StudentShowcase::published()
            ->where('student_id', $user->id)
            ->withCount('endorsements')
            ->latest()
            ->paginate(12);

        $totals = [
            'entries' => $showcases->total(),
            'endorsements' => StudentShowcase::published()->where('student_id', $user->id)->withCount('endorsements')->get()->sum('endorsements_count'),
        ];

        return view('showcase.public-profile', compact('user', 'showcases', 'totals'));
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'project_title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'is_public' => 'nullable|boolean',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'media_urls' => 'nullable|array|max:5',
            'media_urls.*' => 'nullable|url:http,https|max:500',
        ], [
            'media_urls.*.url' => 'Tautan karya harus berupa alamat http(s) yang valid.',
            'cover_image.max' => 'Ukuran gambar sampul maksimal 5 MB.',
        ]);
        $validated['is_public'] = $request->boolean('is_public');
        unset($validated['cover_image']);

        return $validated;
    }
}
