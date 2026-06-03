<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Announcement;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $cacheKey = 'dashboard_stats_general_'.$user->id;
        $stats = Cache::get($cacheKey);

        if ($stats === null || $stats instanceof \__PHP_Incomplete_Class || !is_array($stats)) {
            $stats = [
                'total_students' => Student::count(),
                'total_teachers' => User::where('role', 'guru')->count(),
                'total_classes' => Classroom::count(),
                'total_subjects' => Subject::count(),
                'active_year_id' => AcademicYear::where('is_active', true)->value('id'),
            ];
            Cache::put($cacheKey, $stats, 60 * 15);
        }

        $data = $stats;
        $data['active_year'] = AcademicYear::find($stats['active_year_id']);

        $data['announcements'] = $this->getAnnouncements($user);

        // Specific data for roles
        $data = array_merge($data, $this->getRoleSpecificData($user));

        // Load dashboard layout configuration
        $data['dashboard_layout'] = $user->dashboard_layout ?? $user->getDefaultDashboardLayout();

        return view('dashboard', $data);
    }

    private function getAnnouncements(User $user)
    {
        return Announcement::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->where(function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->whereNull('target_role')
                        ->orWhere('target_role', $user->role);
                });

                if (in_array($user->role, ['student', 'parent'])) {
                    $student = $user->role === 'student' ? $user->student : $user->children()->first();
                    $classId = $student?->classroom_id;

                    $query->where(function ($q) use ($classId) {
                        $q->whereNull('target_classroom_id')
                            ->when($classId, fn ($sq) => $sq->orWhere('target_classroom_id', $classId));
                    });
                }

                if (in_array($user->role, ['admin', 'superadmin', 'kepala_sekolah'])) {
                    $query->orWhereNotNull('id');
                }
            })
            ->with('creator')
            ->latest()
            ->take(5)
            ->get();
    }

    private function getRoleSpecificData(User $user)
    {
        $data = [];
        switch ($user->role) {
            case 'admin':
            case 'superadmin':
            case 'kepala_sekolah':
                $data = $this->getAdminStats();
                break;

            case 'waka_kurikulum':
                $data['recent_subjects'] = Subject::latest()->take(5)->get();
                break;

            case 'waka_kesiswaan':
                $data['recent_students'] = Student::with('classroom.user')->latest()->take(5)->get();
                break;

            case 'guru':
                $data = $this->getTeacherData($user);
                break;

            case 'student':
                $data = $this->getStudentData($user);
                break;

            case 'parent':
                $data = $this->getParentData($user);
                break;
        }

        return $data;
    }

    private function getAdminStats()
    {
        $cacheKey = 'admin_dashboard_stats_v2';
        $stats = Cache::get($cacheKey);

        if ($stats === null || $stats instanceof \__PHP_Incomplete_Class || !is_array($stats)) {
            // Data SPMB
            $spmb_stats = Registration::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            // Data Keuangan (Tren 7 Hari)
            $payment_trend = Payment::where('payment_date', '>=', now()->subDays(7)->format('Y-m-d'))
                ->selectRaw('payment_date as date, sum(amount_paid) as total')
                ->groupBy('payment_date')
                ->orderBy('payment_date')
                ->get();

            // 5 Pembayaran Terakhir
            $recent_payments = Payment::with(['billing.student.user', 'billing.category'])
                ->latest()
                ->take(5)
                ->get();

            $stats = [
                'spmb_stats' => $spmb_stats->toArray(),
                'pending_registrations' => $spmb_stats['pending'] ?? 0,
                'spmb_trend' => Registration::selectRaw('DATE(created_at) as date, count(*) as total')
                    ->where('created_at', '>=', now()->subDays(7))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get()
                    ->toArray(),
                'payment_trend' => $payment_trend->toArray(),
                'dashboard_recent_payments' => $recent_payments->toArray(),
            ];
            Cache::put($cacheKey, $stats, 60 * 15);
        }

        // Reconstruct collections and generic objects for the view
        $stats['spmb_stats'] = collect($stats['spmb_stats'] ?? []);
        $stats['payment_trend'] = collect($stats['payment_trend'] ?? [])->map(function ($item) {
            return (object) $item;
        });
        $stats['spmb_trend'] = collect($stats['spmb_trend'] ?? [])->map(function ($item) {
            return (object) $item;
        });
        $stats['dashboard_recent_payments'] = collect($stats['dashboard_recent_payments'] ?? [])->map(function ($item) {
            $obj = json_decode(json_encode($item));
            if (isset($obj->created_at)) {
                $obj->created_at = Carbon::parse($obj->created_at);
            }

            return $obj;
        });

        return $stats;
    }

    private function getTeacherData(User $user)
    {
        $data = [
            'isTahfidz' => $user->subjects()->whereIn('name', ['Tahfidz', 'PAI', 'Al-Quran Hadits'])->exists(),
            'isBK' => $user->subjects()->whereIn('name', ['BK', 'Bimbingan Konseling'])->exists(),
            'isWaliKelas' => $user->classrooms()->exists(),
        ];

        if ($data['isWaliKelas']) {
            $data['my_classrooms'] = $user->classrooms()->withCount('students')->get();
        }

        return $data;
    }

    private function getStudentData(User $user)
    {
        $studentProfile = Student::where('user_id', $user->id)
            ->with(['pointLogs' => fn ($q) => $q->latest()->take(3), 'pointLogs.creator'])
            ->first();

        $billingStats = Billing::where('student_id', $studentProfile?->id)
            ->where('status', '!=', 'paid')
            ->selectRaw('count(*) as count, sum(remaining_balance) as total')
            ->first();

        return [
            'student_profile' => $studentProfile,
            'total_points' => $studentProfile ? $studentProfile->total_points : 0,
            'recent_logs' => $studentProfile ? $studentProfile->pointLogs : [],
            'unpaid_billings_count' => $billingStats->count ?? 0,
            'total_unpaid_amount' => $billingStats->total ?? 0,
        ];
    }

    private function getParentData(User $user)
    {
        $children = Student::where('parent_id', $user->id)
            ->with(['user', 'classroom', 'pointLogs' => fn ($q) => $q->latest()->take(2)])
            ->withCount(['billings as unpaid_count' => fn ($q) => $q->where('status', '!=', 'paid')])
            ->withSum(['billings as total_unpaid' => fn ($q) => $q->where('status', '!=', 'paid')], 'remaining_balance')
            ->get();

        return [
            'children' => $children->map(function (Student $child) {
                return [
                    'profile' => $child,
                    'total_points' => $child->total_points,
                    'unpaid_billings_count' => $child->unpaid_count,
                    'total_unpaid_amount' => $child->total_unpaid ?? 0,
                    'recent_logs' => $child->pointLogs,
                ];
            }),
        ];
    }

    /**
     * Save customized dashboard layout.
     */
    public function saveLayout(Request $request)
    {
        $request->validate([
            'layout' => 'required|array',
            'layout.*.id' => 'required|string',
            'layout.*.name' => 'required|string',
            'layout.*.visible' => 'required|boolean',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->dashboard_layout = $request->layout;
        $user->save();

        // Clear dashboard-related caches
        Cache::forget('dashboard_stats_general_'.$user->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Tata letak dasbor berhasil disimpan!',
        ]);
    }
}
