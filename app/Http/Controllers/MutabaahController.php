<?php

namespace App\Http\Controllers;

use App\Models\MutabaahItem;
use App\Models\MutabaahLog;
use App\Models\Student;
use Illuminate\Http\Request;

class MutabaahController extends Controller
{
    /**
     * Display the mutabaah grid for a specific date.
     */
    public function index(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        $user = auth()->user();

        $student = null;
        if ($user->role === 'student') {
            $student = Student::where('user_id', $user->id)->first();
        } elseif ($request->has('student_id')) {
            $student = Student::find($request->student_id);

            // Ownership Check for Non-Admin
            if (! in_array($user->role, ['admin', 'superadmin', 'guru', 'bk'])) {
                if ($user->role === 'parent') {
                    if ($student->parent_id !== $user->id) {
                        abort(403, 'Anda tidak memiliki akses ke data mutabaah anak ini.');
                    }
                } else {
                    abort(403, 'Akses ditolak.');
                }
            }
        } else {
            // Fallback for Admin, Superadmin, Guru, BK, Parent (load first student or parent's first child)
            if ($user->role === 'parent') {
                $student = Student::where('parent_id', $user->id)->first();
            } elseif (in_array($user->role, ['admin', 'superadmin', 'guru', 'bk', 'kepala_sekolah'])) {
                $student = Student::first();
            }
        }

        if (! $student) {
            return back()->with('error', 'Data siswa tidak ditemukan.');
        }

        $items = MutabaahItem::where('is_active', true)->get();
        $logs = MutabaahLog::where('student_id', $student->id)
            ->where('date', $date)
            ->get()
            ->keyBy('mutabaah_item_id');

        return view('mutabaah.index', compact('student', 'items', 'logs', 'date'));
    }

    /**
     * Toggle a mutabaah item status.
     */
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'item_id' => 'required|exists:mutabaah_items,id',
            'date' => 'required|date',
            'status' => 'required|boolean',
        ]);

        $user = auth()->user();
        $student = Student::find($validated['student_id']);

        // Ownership Check
        if ($user->role === 'student') {
            if ($student->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
        } elseif (! in_array($user->role, ['admin', 'superadmin', 'guru', 'bk'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $log = MutabaahLog::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'mutabaah_item_id' => $validated['item_id'],
                'date' => $validated['date'],
            ],
            [
                'is_completed' => $validated['status'],
            ]
        );

        return response()->json(['success' => true, 'log' => $log]);
    }

    /**
     * Verify a whole day's logs (for parents).
     */
    public function verify(Request $request, Student $student)
    {
        $date = $request->date;
        $user = auth()->user();

        // Ownership Check
        if ($user->role === 'parent' && $student->parent_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses untuk verifikasi data ini.');
        } elseif (! in_array($user->role, ['admin', 'superadmin', 'guru', 'bk', 'parent'])) {
            abort(403, 'Akses ditolak.');
        }

        MutabaahLog::where('student_id', $student->id)
            ->where('date', $date)
            ->update(['verified_by' => $user->id]);

        return back()->with('success', "Amalan harian anak pada tanggal {$date} telah diverifikasi.");
    }
}
