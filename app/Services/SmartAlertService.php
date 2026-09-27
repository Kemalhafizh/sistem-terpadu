<?php

namespace App\Services;

use App\Events\NewNotificationEvent;
use App\Models\AcademicYear;
use App\Models\AssetLoan;
use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Grade;
use App\Models\Setting;
use App\Models\SmartAlert;
use App\Models\Student;
use App\Models\TeachingJournal;
use App\Models\UksMedicine;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SmartAlertService
{
    /**
     * Detect at-risk students based on grades, attendance, and BK points.
     */
    public function detectAtRiskStudents(): Collection
    {
        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            return collect();
        }

        $students = Student::with(['user', 'pointLogs', 'classroom'])->get();
        $alerts = collect();

        foreach ($students as $student) {
            $riskFactors = [];

            // 1. Academic: check average grade
            $gpaThreshold = (float) Setting::get('smart_alert_at_risk_gpa_threshold', 70);
            $avgGrade = $student->grades()
                ->where('academic_year_id', $activeYear->id)
                ->avg('score');

            if ($avgGrade !== null && $avgGrade < $gpaThreshold) {
                $riskFactors[] = 'Akademik (Rerata nilai: '.number_format($avgGrade, 1).')';
            }

            // 2. Attendance percentage
            $attendanceThreshold = (float) Setting::get('smart_alert_at_risk_attendance_threshold', 80);
            $attendances = $student->attendances()
                ->where('academic_year_id', $activeYear->id)
                ->get();

            $attendanceRate = 100.0;
            if ($attendances->count() > 0) {
                $presentCount = $attendances->where('status', 'H')->count();
                $attendanceRate = ($presentCount / $attendances->count()) * 100;
                if ($attendanceRate < $attendanceThreshold) {
                    $riskFactors[] = 'Kehadiran (Tingkat kehadiran: '.number_format($attendanceRate, 1).'%)';
                }
            }

            // 3. BK Points
            $bkPointsThreshold = (int) Setting::get('smart_alert_at_risk_bk_points_threshold', 50);
            $bkPoints = $student->total_points;
            if ($bkPoints >= $bkPointsThreshold) {
                $riskFactors[] = 'Tata Tertib BK (Akumulasi poin: '.$bkPoints.')';
            }

            // If any risk factor met, create alert
            if (count($riskFactors) > 0) {
                $severity = 'warning';
                $bkPointsCriticalThreshold = (int) Setting::get('smart_alert_at_risk_bk_points_critical_threshold', 75);
                if (count($riskFactors) >= 2 || $bkPoints >= $bkPointsCriticalThreshold) {
                    $severity = 'critical';
                }

                $title = 'Siswa Berisiko: '.$student->name;
                $description = 'Siswa terdeteksi berisiko tinggi karena memenuhi faktor: '.implode(', ', $riskFactors).'.';

                $alert = SmartAlert::updateOrCreate(
                    [
                        'type' => 'at_risk_student',
                        'alertable_type' => Student::class,
                        'alertable_id' => $student->id,
                        'status' => 'active',
                    ],
                    [
                        'severity' => $severity,
                        'title' => $title,
                        'description' => $description,
                        'context_data' => [
                            'student_id' => $student->id,
                            'student_name' => $student->name,
                            'classroom' => $student->classroom?->name ?? 'Tanpa Kelas',
                            'average_grade' => $avgGrade !== null ? round($avgGrade, 2) : null,
                            'attendance_rate' => round($attendanceRate, 2),
                            'bk_points' => $bkPoints,
                            'risk_factors' => $riskFactors,
                        ],
                    ]
                );

                $alerts->push($alert);
            }
        }

        return $alerts;
    }

    /**
     * Detect attendance decline over 3 consecutive weeks.
     */
    public function detectAttendanceDecline(): Collection
    {
        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            return collect();
        }

        $students = Student::with(['user', 'classroom'])->get();
        $alerts = collect();

        foreach ($students as $student) {
            // Define three weekly periods
            $week1Start = Carbon::now()->subDays(7);
            $week2Start = Carbon::now()->subDays(14);
            $week3Start = Carbon::now()->subDays(21);

            $rate1 = $this->getAttendanceRate($student->id, $week1Start, Carbon::now(), $activeYear->id);
            $rate2 = $this->getAttendanceRate($student->id, $week2Start, $week1Start, $activeYear->id);
            $rate3 = $this->getAttendanceRate($student->id, $week3Start, $week2Start, $activeYear->id);

            // Decline detected if Rate3 > Rate2 AND Rate2 > Rate1 (only if we have records in all 3 weeks)
            if ($rate1 !== null && $rate2 !== null && $rate3 !== null) {
                if ($rate3 > $rate2 && $rate2 > $rate1) {
                    $severity = $rate1 < 50 ? 'critical' : 'warning';
                    $title = 'Penurunan Kehadiran: '.$student->name;
                    $description = 'Tingkat kehadiran harian siswa menurun berturut-turut dalam 3 minggu terakhir (M3: '.round($rate3, 1).'%, M2: '.round($rate2, 1).'%, M1: '.round($rate1, 1).'%).';

                    $alert = SmartAlert::updateOrCreate(
                        [
                            'type' => 'attendance_decline',
                            'alertable_type' => Student::class,
                            'alertable_id' => $student->id,
                            'status' => 'active',
                        ],
                        [
                            'severity' => $severity,
                            'title' => $title,
                            'description' => $description,
                            'context_data' => [
                                'student_name' => $student->name,
                                'classroom' => $student->classroom?->name ?? 'Tanpa Kelas',
                                'weekly_rates' => [
                                    'week3' => round($rate3, 2),
                                    'week2' => round($rate2, 2),
                                    'week1' => round($rate1, 2),
                                ],
                            ],
                        ]
                    );

                    $alerts->push($alert);
                }
            }
        }

        return $alerts;
    }

    private function getAttendanceRate($studentId, $startDate, $endDate, $academicYearId)
    {
        $records = Attendance::where('student_id', $studentId)
            ->where('academic_year_id', $academicYearId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        if ($records->isEmpty()) {
            return null;
        }

        $present = $records->where('status', 'H')->count();

        return ($present / $records->count()) * 100;
    }

    /**
     * Predict payment default (unpaid monthly billing for >= 2 months past due).
     */
    public function predictPaymentDefault(): Collection
    {
        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            return collect();
        }

        // Get monthly payment categories
        $monthlyCategoryIds = DB::table('payment_categories')
            ->where('is_monthly', true)
            ->pluck('id');

        $students = Student::with(['user', 'classroom'])->get();
        $alerts = collect();

        foreach ($students as $student) {
            // Find past-due billings (>2 months ago) that are monthly
            $overdueBillings = Billing::where('student_id', $student->id)
                ->where('academic_year_id', $activeYear->id)
                ->whereIn('payment_category_id', $monthlyCategoryIds)
                ->where('remaining_balance', '>', 0)
                ->where('due_date', '<', Carbon::now()->subMonths(2)->toDateString())
                ->get();

            $totalOverdueCount = $overdueBillings->count();
            if ($totalOverdueCount >= 2) {
                $totalUnpaidAmount = $overdueBillings->sum('remaining_balance');
                $severity = $totalOverdueCount >= 3 ? 'critical' : 'warning';

                $title = 'Prediksi Tunggakan SPP: '.$student->name;
                $description = 'Siswa memiliki '.$totalOverdueCount.' tagihan bulanan tertunggak melewati batas jatuh tempo (Total Tunggakan: Rp '.number_format($totalUnpaidAmount, 0, ',', '.').').';

                $alert = SmartAlert::updateOrCreate(
                    [
                        'type' => 'payment_default',
                        'alertable_type' => Student::class,
                        'alertable_id' => $student->id,
                        'status' => 'active',
                    ],
                    [
                        'severity' => $severity,
                        'title' => $title,
                        'description' => $description,
                        'context_data' => [
                            'student_name' => $student->name,
                            'classroom' => $student->classroom?->name ?? 'Tanpa Kelas',
                            'overdue_count' => $totalOverdueCount,
                            'total_unpaid' => $totalUnpaidAmount,
                            'billings' => $overdueBillings->map(fn ($b) => [
                                'month' => $b->month,
                                'amount' => $b->amount,
                                'remaining' => $b->remaining_balance,
                                'due_date' => $b->due_date,
                            ])->toArray(),
                        ],
                    ]
                );

                $alerts->push($alert);
            }
        }

        return $alerts;
    }

    /**
     * Detect teacher journal gaps (not filling teaching journal for >5 working days).
     */
    public function detectTeacherJournalGap(): Collection
    {
        $activeYear = AcademicYear::current();
        if (! $activeYear) {
            return collect();
        }

        $teachers = User::where('role', 'guru')->get();
        $alerts = collect();

        foreach ($teachers as $teacher) {
            // Find latest submitted/reviewed journal
            $latestJournal = TeachingJournal::where('user_id', $teacher->id)
                ->where('academic_year_id', $activeYear->id)
                ->whereIn('status', ['submitted', 'reviewed'])
                ->orderBy('teaching_date', 'desc')
                ->first();

            $gapDays = null;
            $lastDateString = 'Belum pernah mengisi';

            if ($latestJournal) {
                $lastDateString = Carbon::parse($latestJournal->teaching_date)->format('d-m-Y');
                $gapDays = $this->getWorkingDaysDifference($latestJournal->teaching_date);
            } else {
                // If never filled, check when user was created
                $gapDays = $this->getWorkingDaysDifference($teacher->created_at);
            }

            $journalGapDays = (int) Setting::get('smart_alert_journal_gap_days', 5);
            $journalGapCriticalDays = (int) Setting::get('smart_alert_journal_gap_critical_days', 10);
            if ($gapDays > $journalGapDays) {
                $severity = $gapDays > $journalGapCriticalDays ? 'critical' : 'warning';
                $title = 'Kesenjangan Jurnal Mengajar: '.$teacher->name;
                $description = 'Guru belum mengisikan/mengirimkan jurnal mengajar selama '.$gapDays.' hari kerja terakhir (Jurnal terakhir: '.$lastDateString.').';

                $alert = SmartAlert::updateOrCreate(
                    [
                        'type' => 'journal_gap',
                        'alertable_type' => User::class,
                        'alertable_id' => $teacher->id,
                        'status' => 'active',
                    ],
                    [
                        'severity' => $severity,
                        'title' => $title,
                        'description' => $description,
                        'context_data' => [
                            'teacher_name' => $teacher->name,
                            'teacher_email' => $teacher->email,
                            'gap_days' => $gapDays,
                            'last_journal_date' => $lastDateString,
                        ],
                    ]
                );

                $alerts->push($alert);
            }
        }

        return $alerts;
    }

    private function getWorkingDaysDifference($date)
    {
        $startDate = Carbon::parse($date);
        $endDate = Carbon::now();

        if ($startDate->greaterThanOrEqualTo($endDate)) {
            return 0;
        }

        return $startDate->diffInDaysFiltered(function (Carbon $d) {
            return ! $d->isWeekend();
        }, $endDate);
    }

    /**
     * Detect UKS Medicine Critical Stock (stock < min_stock or expired).
     */
    public function detectCriticalMedicineStock(): Collection
    {
        $medicines = UksMedicine::all();
        $alerts = collect();

        foreach ($medicines as $med) {
            $isCritical = false;
            $isWarning = false;
            $reasons = [];

            // Check stock
            if ($med->stock == 0) {
                $isCritical = true;
                $reasons[] = 'Stok habis (0)';
            } elseif ($med->stock < $med->min_stock) {
                $isWarning = true;
                $reasons[] = 'Stok menipis ('.$med->stock.' < '.$med->min_stock.')';
            }

            // Check expiry
            if ($med->expiry_date) {
                $expiry = Carbon::parse($med->expiry_date);
                if ($expiry->isPast()) {
                    $isCritical = true;
                    $reasons[] = 'Obat kedaluwarsa pada '.$expiry->format('d-m-Y');
                } else {
                    $expiryDays = (int) Setting::get('smart_alert_medicine_expiry_days', 30);
                    if ($expiry->diffInDays(Carbon::now()) <= $expiryDays) {
                        $isWarning = true;
                        $reasons[] = 'Obat mendekati kedaluwarsa ('.$expiry->format('d-m-Y').')';
                    }
                }
            }

            if ($isCritical || $isWarning) {
                $severity = $isCritical ? 'critical' : 'warning';
                $title = ($isCritical && str_contains(implode(', ', $reasons), 'kedaluwarsa'))
                    ? 'Obat Kedaluwarsa: '.$med->name
                    : 'Stok Kritis Obat UKS: '.$med->name;
                $description = 'Inventaris UKS ['.$med->name.'] bermasalah: '.implode(' & ', $reasons).'.';

                $alert = SmartAlert::updateOrCreate(
                    [
                        'type' => 'critical_medicine',
                        'alertable_type' => UksMedicine::class,
                        'alertable_id' => $med->id,
                        'status' => 'active',
                    ],
                    [
                        'severity' => $severity,
                        'title' => $title,
                        'description' => $description,
                        'context_data' => [
                            'medicine_name' => $med->name,
                            'category' => $med->category,
                            'stock' => $med->stock,
                            'min_stock' => $med->min_stock,
                            'expiry_date' => $med->expiry_date ? Carbon::parse($med->expiry_date)->format('d-m-Y') : null,
                            'reasons' => $reasons,
                        ],
                    ]
                );

                $alerts->push($alert);
            }
        }

        return $alerts;
    }

    /**
     * Detect overdue asset loans.
     */
    public function detectOverdueAssets(): Collection
    {
        $overdueLoans = AssetLoan::with(['asset', 'borrower'])
            ->where('status', 'borrowed')
            ->where('expected_return_at', '<', Carbon::now())
            ->get();

        $alerts = collect();

        foreach ($overdueLoans as $loan) {
            $expectedReturn = Carbon::parse($loan->expected_return_at);
            $overdueDays = $expectedReturn->diffInDays(Carbon::now());

            $overdueAssetDays = (int) Setting::get('smart_alert_overdue_asset_days', 7);
            $severity = $overdueDays > $overdueAssetDays ? 'critical' : 'warning';
            $title = 'Peminjaman Aset Terlambat: '.$loan->asset->name;
            $description = 'Barang ['.$loan->asset->name.'] dipinjam oleh '.$loan->borrower->name.' melewati batas waktu pengembalian ('.$expectedReturn->format('d-m-Y H:i').'). Terlambat '.$overdueDays.' hari.';

            $alert = SmartAlert::updateOrCreate(
                [
                    'type' => 'overdue_asset',
                    'alertable_type' => AssetLoan::class,
                    'alertable_id' => $loan->id,
                    'status' => 'active',
                ],
                [
                    'severity' => $severity,
                    'title' => $title,
                    'description' => $description,
                    'context_data' => [
                        'asset_name' => $loan->asset->name,
                        'borrower_name' => $loan->borrower->name,
                        'expected_return' => $expectedReturn->format('d-m-Y H:i'),
                        'overdue_days' => $overdueDays,
                    ],
                ]
            );

            // Update the asset loan status to overdue in database
            $loan->update(['status' => 'overdue']);

            $alerts->push($alert);
        }

        return $alerts;
    }

    /**
     * Generate all alerts and dispatch notifications.
     */
    public function generateAll(): array
    {
        $generated = [
            'at_risk_students' => $this->detectAtRiskStudents(),
            'attendance_decline' => $this->detectAttendanceDecline(),
            'payment_defaults' => $this->predictPaymentDefault(),
            'journal_gaps' => $this->detectTeacherJournalGap(),
            'critical_medicine' => $this->detectCriticalMedicineStock(),
            'overdue_assets' => $this->detectOverdueAssets(),
        ];

        // Dispatch Reverb notifications for newly created/updated active CRITICAL alerts
        $totalCount = 0;
        foreach ($generated as $collection) {
            foreach ($collection as $alert) {
                if ($alert->wasRecentlyCreated && $alert->severity === 'critical') {
                    $this->dispatchNotification($alert);
                }
                $totalCount++;
            }
        }

        return [
            'total' => $totalCount,
            'details' => collect($generated)->map(fn ($col) => $col->count())->toArray(),
        ];
    }

    /**
     * Dispatch notification through Reverb WebSockets.
     */
    private function dispatchNotification(SmartAlert $alert): void
    {
        // Find users to notify based on alert type
        $roles = ['admin', 'superadmin', 'kepala_sekolah'];

        if (in_array($alert->type, ['at_risk_student', 'attendance_decline'])) {
            $roles[] = 'bk';
            $roles[] = 'waka_kesiswaan';
        } elseif ($alert->type === 'journal_gap') {
            $roles[] = 'waka_kurikulum';
        }

        $users = User::whereIn('role', $roles)->get();

        foreach ($users as $user) {
            event(new NewNotificationEvent($user, [
                'id' => $alert->id,
                'type' => 'smart_alert',
                'title' => $alert->title,
                'description' => $alert->description,
                'severity' => $alert->severity,
                'created_at' => $alert->created_at->toDateTimeString(),
            ]));

            // Send WhatsApp if recipient has phone number and it is a critical alert
            if ($user->phone_number && $alert->severity === 'critical') {
                try {
                    $message = " *SMART ALERT CRITICAL (SISTA)* \n\n".
                               "Judul: *{$alert->title}*\n".
                               "Deskripsi: {$alert->description}\n".
                               'Waktu: '.now()->format('d-m-Y H:i')."\n\n".
                               'Mohon segera tindak lanjuti melalui portal SISTA.';
                    app(WhatsappService::class)->sendMessage($user->phone_number, $message, 'smart_alert_critical');
                } catch (\Exception $e) {
                    Log::error("Failed to send WhatsApp Alert to {$user->name}: ".$e->getMessage());
                }
            }
        }
    }

    /**
     * Escalate an alert to a supervisor.
     */
    public function escalateAlert(SmartAlert $alert, User $supervisor, string $notes): void
    {
        $alert->update([
            'severity' => 'critical',
            'escalated_to_id' => $supervisor->id,
            'escalation_notes' => $notes,
            'escalated_at' => now(),
            'status' => 'active',
        ]);

        // Send Reverb Notification
        event(new NewNotificationEvent($supervisor, [
            'id' => $alert->id,
            'type' => 'smart_alert',
            'title' => ' Eskalasi Alert: '.$alert->title,
            'description' => 'Alert telah dieskalasikan kepada Anda: '.$notes,
            'severity' => 'critical',
            'created_at' => now()->toDateTimeString(),
        ]));

        // Send WhatsApp Notification to Supervisor
        if ($supervisor->phone_number) {
            try {
                $message = " *ESKALASI SMART ALERT (SISTA)* \n\n".
                           "Sebuah alert telah dieskalasikan kepada Anda:\n".
                           "Judul: *{$alert->title}*\n".
                           "Catatan Eskalasi: _{$notes}_\n".
                           'Waktu: '.now()->format('d-m-Y H:i')."\n\n".
                           'Silakan login ke portal SISTA untuk mengambil tindakan.';
                app(WhatsappService::class)->sendMessage($supervisor->phone_number, $message, 'smart_alert_escalation');
            } catch (\Exception $e) {
                Log::error("Failed to send WhatsApp Escalation to {$supervisor->name}: ".$e->getMessage());
            }
        }
    }
}
