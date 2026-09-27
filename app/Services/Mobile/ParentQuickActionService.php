<?php

declare(strict_types=1);

namespace App\Services\Mobile;

use App\Models\Attendance;
use App\Models\Billing;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;

class ParentQuickActionService
{
    /**
     * Get parent dashboard summary and quick actions
     */
    public function getParentSummary(User $user): array
    {
        $children = Student::where('parent_user_id', $user->id)->get();
        $childrenData = [];

        foreach ($children as $child) {
            $unpaidBills = class_exists(Billing::class) ? Billing::where('student_id', $child->id)
                ->where('status', 'unpaid')
                ->sum('amount') : 0;

            $todayAttendance = Attendance::where('student_id', $child->id)
                ->where('date', Carbon::today()->toDateString())
                ->first();

            $childrenData[] = [
                'id' => $child->id,
                'name' => $child->nama_lengkap ?? $child->name,
                'nis' => $child->nis ?? '-',
                'classroom' => $child->classroom->name ?? 'Kelas X',
                'today_status' => $todayAttendance ? $todayAttendance->status : 'Belum Presensi',
                'unpaid_bills_total' => (float) $unpaidBills,
            ];
        }

        return [
            'parent_name' => $user->name,
            'children' => $childrenData,
            'quick_actions' => [
                ['label' => 'Izin / Sakit Anak', 'action' => 'report_absence', 'icon' => 'calendar-x'],
                ['label' => 'Bayar Tagihan SPP', 'action' => 'quick_pay', 'icon' => 'credit-card'],
                ['label' => 'Hubungi Wali Kelas', 'action' => 'chat_teacher', 'icon' => 'message-circle'],
            ],
        ];
    }

    /**
     * Report child absence (Sakit / Izin) directly from parent mobile
     */
    public function reportAbsence(int $studentId, string $status, string $reason, ?string $attachmentUrl = null): array
    {
        $today = Carbon::today()->toDateString();

        $attendance = Attendance::updateOrCreate(
            [
                'student_id' => $studentId,
                'date' => $today,
            ],
            [
                'status' => in_array($status, ['sakit', 'izin']) ? $status : 'izin',
                'notes' => 'Laporan Orang Tua Mobile: '.$reason,
            ]
        );

        return [
            'success' => true,
            'message' => 'Laporan ketidakhadiran berhasil disampaikan ke wali kelas.',
            'attendance_id' => $attendance->id,
        ];
    }
}
