<?php

namespace App\Http\Controllers;

use App\Http\Middleware\RoleMiddleware;
use App\Models\IncomingLetter;
use App\Models\LetterDisposition;
use App\Models\User;
use App\Services\IncomingLetterService;
use App\Support\Access\Roles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class IncomingLetterController extends Controller
{
    public function __construct(
        private readonly IncomingLetterService $service
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        $baseQuery = IncomingLetter::query()->visibleTo($user);

        $query = (clone $baseQuery)->with(['creator', 'dispositions.toUser']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('agenda_number', 'like', "%{$search}%")
                    ->orWhere('reference_number', 'like', "%{$search}%")
                    ->orWhere('sender', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        $letters = $query->orderByDesc('received_date')->orderByDesc('id')->paginate(15)->withQueryString();

        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $stats = [
            'total' => (int) $statusCounts->sum(),
            'diterima' => (int) ($statusCounts[IncomingLetter::STATUS_RECEIVED] ?? 0),
            'didisposisikan' => (int) ($statusCounts[IncomingLetter::STATUS_DISPOSED] ?? 0),
            'ditindaklanjuti' => (int) ($statusCounts[IncomingLetter::STATUS_FOLLOWED_UP] ?? 0),
            'selesai' => (int) ($statusCounts[IncomingLetter::STATUS_COMPLETED] ?? 0),
        ];

        $canRegister = RoleMiddleware::allowsUser($user, ['superadmin', 'admin', 'staf_tu']);

        return view('incoming-letters.index', compact('letters', 'stats', 'canRegister'));
    }

    public function create()
    {
        if (! RoleMiddleware::allowsUser(auth()->user(), ['superadmin', 'admin', 'staf_tu'])) {
            abort(403, 'Hanya Staf TU atau Admin yang berwenang mencatat surat masuk.');
        }

        return view('incoming-letters.create');
    }

    public function store(Request $request)
    {
        if (! RoleMiddleware::allowsUser(auth()->user(), ['superadmin', 'admin', 'staf_tu'])) {
            abort(403, 'Hanya Staf TU atau Admin yang berwenang mencatat surat masuk.');
        }

        $validated = $request->validate([
            'reference_number' => 'required|string|max:120',
            'sender' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'letter_date' => 'required|date',
            'received_date' => 'required|date|after_or_equal:letter_date',
            'category' => 'nullable|string|max:64',
            'summary' => 'nullable|string|max:2000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $letter = $this->service->create($validated, $request->user(), $request->file('attachment'));

        return redirect()->route('incoming-letters.show', $letter)
            ->with('success', 'Surat masuk berhasil dicatat dengan nomor agenda: '.$letter->agenda_number);
    }

    public function show(IncomingLetter $incomingLetter)
    {
        $user = auth()->user();
        if (! $incomingLetter->isVisibleTo($user)) {
            abort(403, 'Anda tidak memiliki akses ke surat masuk ini.');
        }

        $incomingLetter->load(['creator', 'archiver', 'dispositions.fromUser', 'dispositions.toUser']);

        $canDispose = RoleMiddleware::allowsUser($user, ['superadmin', 'admin', ...Roles::PRINCIPAL]);
        $canArchive = RoleMiddleware::allowsUser($user, ['superadmin', 'admin', 'staf_tu', ...Roles::PRINCIPAL]);

        $recipients = User::query()
            ->where(fn ($q) => $q->whereNull('account_status')->orWhere('account_status', '!=', 'nonaktif'))
            ->where(function ($q) {
                $q->whereIn('role', [
                    'waka_kurikulum', 'waka_kesiswaan', 'waka_sarpras', 'waka_humas',
                    'staf_tu', 'guru', 'teacher', 'bk', 'wali_kelas', 'bendahara', 'pustakawan', 'petugas_uks',
                ])->orWhereHas('positionAssignments', fn ($pq) => $pq->activeOn());
            })
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        return view('incoming-letters.show', compact('incomingLetter', 'canDispose', 'canArchive', 'recipients'));
    }

    public function dispose(Request $request, IncomingLetter $incomingLetter)
    {
        if (! RoleMiddleware::allowsUser(auth()->user(), ['superadmin', 'admin', ...Roles::PRINCIPAL])) {
            abort(403, 'Hanya Kepala Sekolah atau Admin yang berwenang memberikan disposisi.');
        }

        if ($incomingLetter->status === IncomingLetter::STATUS_COMPLETED) {
            return redirect()->back()->with('error', 'Surat masuk yang sudah selesai dan diarsipkan tidak dapat didisposisikan ulang.');
        }

        $validated = $request->validate([
            'to_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->whereNotIn('role', [
                    'student', 'siswa', 'parent', 'orang_tua', 'orangtua', 'wali_murid', 'alumni', 'merchant',
                ])),
            ],
            'instruction' => 'required|string|max:2000',
            'priority' => ['required', Rule::in(LetterDisposition::PRIORITIES)],
            'due_at' => 'nullable|date',
        ]);

        $this->service->createDisposition($incomingLetter, $request->user(), $validated);

        return redirect()->route('incoming-letters.show', $incomingLetter)
            ->with('success', 'Disposisi surat masuk berhasil dikirim ke penerima tugas.');
    }

    public function updateDisposition(Request $request, LetterDisposition $disposition)
    {
        if ((int) $disposition->to_user_id !== (int) auth()->id()) {
            abort(403, 'Hanya penerima disposisi yang bersangkutan yang dapat memperbarui tindak lanjut.');
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in([LetterDisposition::STATUS_IN_PROGRESS, LetterDisposition::STATUS_DONE])],
            'follow_up_notes' => 'nullable|string|max:2000',
        ]);

        $this->service->updateDispositionStatus(
            $disposition,
            $validated['status'],
            $validated['follow_up_notes'] ?? null
        );

        return redirect()->route('incoming-letters.show', $disposition->incomingLetter)
            ->with('success', 'Laporan tindak lanjut disposisi berhasil disimpan.');
    }

    public function archive(IncomingLetter $incomingLetter)
    {
        if (! RoleMiddleware::allowsUser(auth()->user(), ['superadmin', 'admin', 'staf_tu', ...Roles::PRINCIPAL])) {
            abort(403, 'Hanya Staf TU atau Pimpinan yang berwenang mengarsipkan surat masuk.');
        }

        $this->service->archive($incomingLetter, auth()->user());

        return redirect()->route('incoming-letters.show', $incomingLetter)
            ->with('success', 'Surat masuk telah ditandai selesai dan diarsipkan.');
    }

    public function downloadAttachment(IncomingLetter $incomingLetter)
    {
        if (! $incomingLetter->isVisibleTo(auth()->user())) {
            abort(403, 'Anda tidak memiliki akses mengunduh lampiran surat masuk ini.');
        }

        if (! $incomingLetter->attachment_path || ! Storage::disk('local')->exists($incomingLetter->attachment_path)) {
            abort(404, 'Lampiran pindaian surat tidak ditemukan.');
        }

        return Storage::disk('local')->response($incomingLetter->attachment_path);
    }
}
