<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Billing;
use App\Models\CounselingSession;
use App\Models\Grade;
use App\Models\ParentMessage;
use App\Models\Payment;
use App\Models\ReportCard;
use App\Models\Schedule;
use App\Models\SchedulePreset;
use App\Models\Student;
use App\Models\WadiahAccount;
use App\Services\WadiahService;
use App\Models\UksVisit;
use App\Models\User;
use App\Events\ChatMessageSent;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParentPortalController extends Controller
{
    /**
     * Parent Portal Dashboard.
     */
    public function dashboard(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $children = Student::where('parent_id', $user->id)
            ->with(['user', 'classroom.teacher'])
            ->get();

        if ($children->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki anak terdaftar di sistem.');
        }

        // Child Switcher logic
        $selectedChildId = $request->input('child_id') ?? session('parent_active_child_id');
        $student = null;

        if ($selectedChildId) {
            $student = $children->firstWhere('id', $selectedChildId);
        }

        if (!$student) {
            $student = $children->first();
        }

        // Save active child ID to session
        session(['parent_active_child_id' => $student->id]);

        // 1. Profile Widget
        $classroom = $student->classroom;
        $teacher = $classroom?->teacher;

        // 2. Academic Widget: Average score, class rank, and running grades trend
        $avgScore = round($student->grades()->avg('score') ?? 0, 1);
        
        // Calculate class rank dynamically
        $rank = 1;
        $totalClassStudents = 1;
        if ($classroom) {
            $classroomStudents = Student::where('classroom_id', $classroom->id)->get();
            $totalClassStudents = $classroomStudents->count();
            $averages = [];
            foreach ($classroomStudents as $cs) {
                $averages[$cs->id] = (float) $cs->grades()->avg('score');
            }
            arsort($averages);
            foreach ($averages as $studentId => $avg) {
                if ($studentId === $student->id) {
                    break;
                }
                $rank++;
            }
        }

        // Running grades trend for ApexCharts
        $gradesForChart = $student->grades()
            ->with('subject')
            ->orderBy('created_at', 'asc')
            ->take(10)
            ->get();

        $chartLabels = $gradesForChart->map(fn($g) => $g->subject->name . ' (' . $g->type . ')')->toArray();
        $chartData = $gradesForChart->map(fn($g) => (int) $g->score)->toArray();

        // 3. Attendance Widget
        $attendanceStats = $student->attendances()
            ->selectRaw("status, count(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $totalAttendance = array_sum($attendanceStats);
        $hadir = $attendanceStats['Hadir'] ?? 0;
        $sakit = $attendanceStats['Sakit'] ?? 0;
        $izin = $attendanceStats['Izin'] ?? 0;
        $alpa = $attendanceStats['Alpa'] ?? 0;
        $hadirPercentage = $totalAttendance > 0 ? round(($hadir / $totalAttendance) * 100, 1) : 100;

        // 4. Finance Widget
        $unpaidBillings = $student->billings()
            ->where('status', '!=', 'paid')
            ->with('category')
            ->orderBy('due_date', 'asc')
            ->get();
        $totalUnpaidAmount = $unpaidBillings->sum('remaining_balance');
        $nearestDueDate = $unpaidBillings->first()?->due_date;

        // 5. BK Points Widget
        $totalPoints = $student->total_points;
        $recentPointLogs = $student->pointLogs()
            ->with('creator')
            ->latest()
            ->take(5)
            ->get();

        // 6. UKS Widget
        $latestUksVisit = UksVisit::where('student_id', $student->id)
            ->with(['handler', 'medicines'])
            ->latest()
            ->first();

        // Counseling Widget (Filter out confidential sessions strictly)
        $counselingSessions = CounselingSession::where('student_id', $student->id)
            ->where('is_confidential', false)
            ->with('counselor')
            ->latest()
            ->take(5)
            ->get();

        // 7. Integrated Chronological Activity Timeline Feed
        $timeline = collect();

        // Add Grades
        $student->grades()->with('subject')->latest()->take(5)->get()->each(function ($grade) use ($timeline) {
            $timeline->push([
                'type' => 'grade',
                'date' => $grade->created_at,
                'title' => 'Nilai Akademik Baru',
                'description' => "Nilai mata pelajaran {$grade->subject->name} ({$grade->type}) baru saja ditambahkan dengan skor {$grade->score}. Keterangan: " . ($grade->description ?? '-'),
                'color' => 'indigo',
                'icon' => '<svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            ]);
        });

        // Add UKS Visits
        UksVisit::where('student_id', $student->id)->latest()->take(5)->get()->each(function ($visit) use ($timeline) {
            $timeline->push([
                'type' => 'uks',
                'date' => $visit->visit_time,
                'title' => 'Kunjungan UKS',
                'description' => "Anak berkunjung ke UKS dengan keluhan: {$visit->complaint}. Tindakan penanganan: {$visit->treatment} ({$visit->disposition})",
                'color' => 'teal',
                'icon' => '<svg class="w-4 h-4 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>',
            ]);
        });

        // Add BK Logs
        $student->pointLogs()->latest()->take(5)->get()->each(function ($log) use ($timeline) {
            $pointsStr = $log->points > 0 ? "+{$log->points}" : $log->points;
            $color = $log->points > 0 ? 'red' : 'emerald';
            $timeline->push([
                'type' => 'bk',
                'date' => $log->created_at,
                'title' => $log->points > 0 ? 'Pelanggaran Disiplin' : 'Prestasi Perilaku',
                'description' => "{$log->description} ({$pointsStr} poin)",
                'color' => $color,
                'icon' => '<svg class="w-4 h-4 text-' . ($log->points > 0 ? 'red' : 'emerald') . '-600 dark:text-' . ($log->points > 0 ? 'red' : 'emerald') . '-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>',
            ]);
        });

        // Add Attendances
        $student->attendances()->latest()->take(5)->get()->each(function ($att) use ($timeline) {
            $statusColors = [
                'Hadir' => 'emerald',
                'Sakit' => 'amber',
                'Izin' => 'blue',
                'Alpa' => 'red'
            ];
            $color = $statusColors[$att->status] ?? 'gray';
            $timeline->push([
                'type' => 'attendance',
                'date' => Carbon::parse($att->date . ' 07:00:00'),
                'title' => "Presensi: {$att->status}",
                'description' => "Siswa dicatat {$att->status} pada tanggal " . Carbon::parse($att->date)->isoFormat('D MMMM YYYY') . ($att->notes ? " dengan keterangan: {$att->notes}" : ""),
                'color' => $color,
                'icon' => '<svg class="w-4 h-4 text-' . $color . '-600 dark:text-' . $color . '-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
            ]);
        });

        // Add Billings
        $student->billings()->latest()->take(3)->get()->each(function ($bill) use ($timeline) {
            $timeline->push([
                'type' => 'billing',
                'date' => $bill->created_at,
                'title' => 'Tagihan Baru Dirilis',
                'description' => "Tagihan {$bill->category->name} sebesar Rp " . number_format($bill->amount, 0, ',', '.') . " dirilis. Jatuh tempo: " . Carbon::parse($bill->due_date)->isoFormat('D MMMM YYYY'),
                'color' => 'blue',
                'icon' => '<svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            ]);

            $bill->payments()->latest()->get()->each(function ($payment) use ($timeline, $bill) {
                $timeline->push([
                    'type' => 'payment',
                    'date' => $payment->created_at,
                    'title' => 'Pembayaran Berhasil',
                    'description' => "Pembayaran tagihan {$bill->category->name} sebesar Rp " . number_format($payment->amount_paid, 0, ',', '.') . " telah berhasil diterima.",
                    'color' => 'emerald',
                    'icon' => '<svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                ]);
            });
        });

        $sortedTimeline = $timeline->sortByDesc('date')->take(10);

        return view('parent-portal.dashboard', compact(
            'children', 'student', 'classroom', 'teacher',
            'avgScore', 'rank', 'totalClassStudents', 'chartLabels', 'chartData',
            'hadir', 'sakit', 'izin', 'alpa', 'hadirPercentage',
            'unpaidBillings', 'totalUnpaidAmount', 'nearestDueDate',
            'totalPoints', 'recentPointLogs', 'latestUksVisit',
            'counselingSessions', 'sortedTimeline'
        ));
    }

    /**
     * View Child's Report Card (Rapor).
     */
    public function viewReportCard(Student $student)
    {
        $this->validateChild($student);

        // Fetch grades grouped by subject
        $grades = Grade::where('student_id', $student->id)
            ->with(['subject', 'recorder'])
            ->get()
            ->groupBy('subject_id');

        $reportCard = ReportCard::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->first();

        // Calculate dynamic classroom statistics for the report card
        $classroom = $student->classroom;
        $rank = 1;
        $totalClassStudents = 1;
        if ($classroom) {
            $classroomStudents = Student::where('classroom_id', $classroom->id)->get();
            $totalClassStudents = $classroomStudents->count();
            $averages = [];
            foreach ($classroomStudents as $cs) {
                $averages[$cs->id] = (float) $cs->grades()->avg('score');
            }
            arsort($averages);
            foreach ($averages as $studentId => $avg) {
                if ($studentId === $student->id) {
                    break;
                }
                $rank++;
            }
        }

        return view('parent-portal.report-card', compact('student', 'grades', 'reportCard', 'rank', 'totalClassStudents'));
    }

    /**
     * View Child's Class Schedule (Jadwal Pelajaran).
     */
    public function viewSchedule(Student $student)
    {
        $this->validateChild($student);

        $activePreset = SchedulePreset::where('is_active', true)->with('times')->first();
        $schedules = Schedule::where('classroom_id', $student->classroom_id)
            ->where('academic_year_id', AcademicYear::current()?->id)
            ->with(['subject', 'teacher'])
            ->get();

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        return view('parent-portal.schedule', compact('student', 'schedules', 'activePreset', 'days'));
    }

    /**
     * View Child's Attendance Calendar (Riwayat Absensi).
     */
    public function viewAttendanceHistory(Student $student)
    {
        $this->validateChild($student);

        $attendances = $student->attendances()
            ->where('academic_year_id', AcademicYear::current()?->id)
            ->orderBy('date', 'desc')
            ->get();

        // Calculate statistics
        $attendanceStats = $student->attendances()
            ->selectRaw("status, count(*) as count")
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
        $totalAttendance = array_sum($attendanceStats);
        $hadir = $attendanceStats['Hadir'] ?? 0;
        $sakit = $attendanceStats['Sakit'] ?? 0;
        $izin = $attendanceStats['Izin'] ?? 0;
        $alpa = $attendanceStats['Alpa'] ?? 0;
        $hadirPercentage = $totalAttendance > 0 ? round(($hadir / $totalAttendance) * 100, 1) : 100;

        return view('parent-portal.attendance', compact('student', 'attendances', 'hadir', 'sakit', 'izin', 'alpa', 'hadirPercentage'));
    }

    /**
     * View Child's Billings (Daftar Tagihan & Status).
     */
    public function viewBillings(Student $student)
    {
        $this->validateChild($student);

        $billings = Billing::where('student_id', $student->id)
            ->with(['category', 'payments'])
            ->orderBy('due_date', 'asc')
            ->get();

        return view('parent-portal.billings', compact('student', 'billings'));
    }

    /**
     * Parent Portal billing checkout.
     */
    public function checkout(Billing $billing)
    {
        $student = Student::findOrFail($billing->student_id);
        $this->validateChild($student);

        $grossAmount = (int) $billing->remaining_balance;
        if ($grossAmount <= 0) {
            return redirect()->route('parent-portal.billings', $student->id)->with('error', 'Tagihan ini sudah lunas.');
        }

        $serverKey = config('services.midtrans.server_key');

        if (! $billing->snap_token || $billing->snap_token == '' || str_starts_with($billing->snap_token, 'TKN-DEMO-')) {
            if (! empty($serverKey)) {
                try {
                    $orderId = 'BILL-'.$billing->id.'-'.time();
                    $payload = [
                        'transaction_details' => [
                            'order_id' => $orderId,
                            'gross_amount' => $grossAmount,
                        ],
                        'customer_details' => [
                            'first_name' => auth()->user()->name ?? 'Orang Tua',
                            'email' => auth()->user()->email ?? 'parent@sekolah.sch.id',
                        ],
                        'item_details' => [
                            [
                                'id' => 'BILL-'.$billing->id,
                                'price' => $grossAmount,
                                'quantity' => 1,
                                'name' => $billing->category->name ?? 'Iuran Sekolah',
                            ],
                        ],
                    ];

                    $response = Http::withBasicAuth($serverKey, '')
                        ->timeout(5)
                        ->post('https://app.sandbox.midtrans.com/snap/v1/transactions', $payload);

                    if ($response->successful()) {
                        $result = $response->json();
                        $billing->update([
                            'snap_token' => $result['token'],
                            'payment_url' => $result['redirect_url'],
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('Gagal terhubung ke Midtrans API via Portal Orang Tua: '.$e->getMessage());
                }
            }

            if (! $billing->snap_token) {
                $billing->update([
                    'snap_token' => 'TKN-DEMO-'.strtoupper(Str::random(10)),
                    'payment_url' => route('parent-portal.billings.mock-gateway', $billing->id),
                ]);
            }
        }

        return view('parent-portal.checkout', compact('billing', 'student'));
    }

    /**
     * Parent Portal billing mock gateway.
     */
    public function mockGateway(Billing $billing)
    {
        $student = Student::findOrFail($billing->student_id);
        $this->validateChild($student);

        return view('parent-portal.mock-gateway', compact('billing', 'student'));
    }

    /**
     * Parent Portal billing mock callback.
     */
    public function callback(Request $request, Billing $billing)
    {
        $student = Student::findOrFail($billing->student_id);
        $this->validateChild($student);

        DB::transaction(function () use ($billing) {
            Payment::create([
                'billing_id' => $billing->id,
                'created_by' => 1, // System / Admin
                'amount_paid' => $billing->remaining_balance,
                'payment_date' => now(),
                'payment_method' => 'transfer',
                'receipt_number' => 'AUTO-'.strtoupper(Str::random(8)),
                'note' => 'Pembayaran Otomatis via Portal Orang Tua (Simulasi)',
            ]);

            $billing->updateStatus();
        });

        return redirect()->route('parent-portal.billings', $student->id)->with('success', 'Pembayaran berhasil dikonfirmasi secara otomatis!');
    }

    /**
     * Chat/Obrolan Room with Homeroom Teacher (Wali Kelas).
     */
    public function messages(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        $children = Student::where('parent_id', $user->id)
            ->with(['user', 'classroom.teacher'])
            ->get();

        if ($children->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'Anda tidak memiliki anak terdaftar.');
        }

        $selectedChildId = $request->input('child_id') ?? session('parent_active_child_id');
        $student = $children->firstWhere('id', $selectedChildId) ?? $children->first();
        session(['parent_active_child_id' => $student->id]);

        $teacher = $student->classroom?->teacher;
        $messages = collect();

        if ($teacher) {
            // Auto mark messages as read
            ParentMessage::betweenUsers($teacher->id, $user->id, $student->id)
                ->where('recipient_id', $user->id)
                ->unread()
                ->update(['is_read' => true]);

            $messages = ParentMessage::betweenUsers($user->id, $teacher->id, $student->id)
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return view('parent-portal.messages', compact('children', 'student', 'teacher', 'messages'));
    }

    /**
     * Send Message to Homeroom Teacher.
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'message' => 'required|string|max:5000',
        ]);

        $student = Student::findOrFail($request->student_id);
        $this->validateChild($student);

        $teacher = $student->classroom?->teacher;
        if (!$teacher) {
            return back()->with('error', 'Wali kelas tidak ditemukan untuk kelas anak Anda.');
        }

        $message = ParentMessage::create([
            'sender_id' => auth()->id(),
            'recipient_id' => $teacher->id,
            'student_id' => $student->id,
            'message' => $request->message,
            'is_read' => false,
        ]);

        // Sender di-attach langsung dari user yang sudah diautentikasi agar payload
        // broadcast punya sender_name yang nyata, bukan ditebak di sisi client.
        $message->setRelation('sender', auth()->user());
        broadcast(new ChatMessageSent($message))->toOthers();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return back()->with('success', 'Pesan berhasil dikirim.');
    }

    /**
     * Check if child belongs to authorized parent.
     */
    protected function validateChild(Student $student)
    {
        if ($student->parent_id !== auth()->id()) {
            abort(403, 'Akses Ditolak. Siswa ini bukan anak Anda.');
        }
    }

    /**
     * View child's daily assessments.
     */
    public function viewDailyAssessments(Student $student)
    {
        $this->validateChild($student);

        $activeYear = AcademicYear::current();

        $dailyScores = \App\Models\DailyAssessmentScore::where('student_id', $student->id)
            ->whereHas('dailyAssessment', function ($q) use ($activeYear) {
                if ($activeYear) {
                    $q->where('academic_year_id', $activeYear->id);
                }
            })
            ->with(['dailyAssessment.subject', 'dailyAssessment.teacher'])
            ->latest()
            ->get();

        return view('daily-assessments.parent-index', compact('student', 'dailyScores', 'activeYear'));
    }

    /**
     * View child's Wadiah savings mutations.
     */
    public function viewWadiahWallet(Student $student)
    {
        $this->validateChild($student);

        // Ensure wadiah account exists
        $wadiahService = app(WadiahService::class);
        $account = $wadiahService->createAccount($student);

        $transactions = $account->transactions()->with('processedBy')->latest()->paginate(15);

        return view('parent.wadiah', compact('student', 'account', 'transactions'));
    }

    /**
     * Set daily spending limit for child.
     */
    public function setWadiahLimit(Student $student, Request $request)
    {
        $this->validateChild($student);

        $request->validate([
            'daily_spending_limit' => 'required|numeric|min:0',
        ]);

        $account = WadiahAccount::where('student_id', $student->id)->first();
        if ($account) {
            $account->update([
                'daily_spending_limit' => $request->daily_spending_limit,
            ]);
        }

        return back()->with('success', 'Batasan limit jajan harian anak berhasil diperbarui.');
    }
}
