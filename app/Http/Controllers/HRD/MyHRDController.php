<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MyHRDController extends Controller
{
    /**
     * Dashboard for the logged-in employee.
     */
    public function index()
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            if ($user->isAdmin()) {
                // Auto-create for Admin/Superadmin so they don't get blocked
                $employee = Employee::create([
                    'user_id' => $user->id,
                    'employee_number' => 'ADM-'.date('Y').'-'.str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    'position' => 'Administrator',
                    'employment_status' => 'staf_tetap',
                    'hire_date' => now(),
                    'base_salary' => 4500000, // default base salary for admin
                    'hourly_rate' => 0,
                ]);
            } else {
                return view('hrd.my_portal.not_registered');
            }
        }

        $today = Carbon::today();

        $todayAttendance = EmployeeAttendance::where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        $recentAttendances = EmployeeAttendance::where('employee_id', $employee->id)
            ->latest('date')
            ->take(5)
            ->get();

        $recentLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();

        // Get personal payslip history (only approved or paid ones)
        $payslips = Payroll::where('employee_id', $employee->id)
            ->whereIn('status', ['approved', 'paid'])
            ->latest('year')
            ->latest('month')
            ->get();

        // School coordinates for frontend geofencing feedback
        $schoolLat = Setting::get('school_latitude', '-6.9535');
        $schoolLong = Setting::get('school_longitude', '110.4355');
        $schoolRadius = Setting::get('school_attendance_radius', '100');

        return view('hrd.my_portal.index', compact(
            'employee',
            'todayAttendance',
            'recentAttendances',
            'recentLeaves',
            'payslips',
            'schoolLat',
            'schoolLong',
            'schoolRadius'
        ));
    }

    /**
     * Handle check-in or check-out with GPS Geofencing and Camera Selfie.
     */
    public function recordAttendance(Request $request)
    {
        $user = auth()->user();
        $employee = $user->employee;

        if (! $employee) {
            return back()->with('error', 'Akun Anda belum terdaftar sebagai pegawai.');
        }

        $type = $request->input('type'); // 'in' or 'out'
        $today = Carbon::today();
        $currentTime = Carbon::now()->format('H:i:s');

        $attendance = EmployeeAttendance::firstOrNew([
            'employee_id' => $employee->id,
            'date' => $today,
        ]);

        // Get school settings for geofencing
        $schoolLat = (float) Setting::get('school_latitude', '-6.9535');
        $schoolLong = (float) Setting::get('school_longitude', '110.4355');
        $schoolRadius = (float) Setting::get('school_attendance_radius', '100');

        // Extract user coordinates from request
        $userLat = $request->input('lat');
        $userLong = $request->input('long');

        if (! $userLat || ! $userLong) {
            return back()->with('error', 'Gagal mendeteksi lokasi GPS Anda. Pastikan izin lokasi aktif.');
        }

        // Calculate distance from school using Haversine
        $distance = $this->calculateDistance((float) $userLat, (float) $userLong, $schoolLat, $schoolLong);

        if ($distance > $schoolRadius) {
            return back()->with('error', sprintf(
                'Check-in ditolak. Anda berada %.1f meter di luar jangkauan sekolah (Batas Maksimal: %d meter).',
                $distance,
                $schoolRadius
            ));
        }

        if ($type == 'in') {
            if ($attendance->exists && $attendance->check_in_time) {
                return back()->with('error', 'Anda sudah melakukan Check-in hari ini.');
            }

            // Require selfie photo
            if (! $request->hasFile('photo')) {
                return back()->with('error', 'Foto selfie check-in wajib diunggah.');
            }

            $photoPath = $request->file('photo')->store('hrd/attendance', 'public');

            // Logic keterlambatan (masuk jam 07:15)
            $status = (Carbon::now()->format('H:i') > '07:15') ? 'terlambat' : 'hadir';

            $attendance->check_in_time = $currentTime;
            $attendance->status = $status;
            $attendance->location_lat = $userLat;
            $attendance->location_long = $userLong;
            $attendance->distance_meters = $distance;
            $attendance->photo_path = $photoPath;
            $attendance->save();

            return back()->with('success', 'Berhasil Check-in pada '.$currentTime.' dengan foto selfie.');

        } elseif ($type == 'out') {
            if (! $attendance->exists || ! $attendance->check_in_time) {
                return back()->with('error', 'Anda belum Check-in hari ini.');
            }
            if ($attendance->check_out_time) {
                return back()->with('error', 'Anda sudah melakukan Check-out hari ini.');
            }

            // Photo optional for checkout, but let's handle if uploaded
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('hrd/attendance', 'public');
                $attendance->photo_path = $photoPath; // update to check-out photo
            }

            $attendance->check_out_time = $currentTime;
            // Also store GPS check-out verification
            $attendance->location_lat = $userLat;
            $attendance->location_long = $userLong;
            $attendance->distance_meters = $distance;
            $attendance->save();

            return back()->with('success', 'Berhasil Check-out pada '.$currentTime);
        }

        return back()->with('error', 'Aksi tidak valid.');
    }

    /**
     * Download professional payslip PDF.
     */
    public function downloadPayslipPdf(Payroll $payroll)
    {
        $user = auth()->user();
        $employee = $user->employee ?: $payroll->employee;

        // Security check: Employee can only download their own payslip (bypass for Admin/Superadmin)
        if (($user->role !== 'admin' && $user->role !== 'superadmin') && (! $user->employee || $payroll->employee_id !== $user->employee->id)) {
            abort(403, 'Akses ditolak.');
        }

        // Only approved or paid payslips can be downloaded
        if (! in_array($payroll->status, ['approved', 'paid']) && $user->role !== 'admin' && $user->role !== 'superadmin') {
            abort(403, 'Slip gaji belum diterbitkan.');
        }

        $payroll->load('employee.user');

        $schoolName = Setting::get('school_name', 'SMA Islam Sultan Agung 1');
        $schoolAddress = Setting::get('school_address', 'Jl. Mataram No.657, Wonodri, Kec. Semarang Sel., Kota Semarang, Jawa Tengah 50242');
        $schoolPhone = Setting::get('school_phone', '(024) 8313922');
        $schoolEmail = Setting::get('school_email', 'info@smaisla1smg.sch.id');

        $pdf = Pdf::loadView('hrd.payrolls.pdf', compact('payroll', 'schoolName', 'schoolAddress', 'schoolPhone', 'schoolEmail'));

        return $pdf->download('payslip-'.$payroll->month.'-'.$payroll->year.'-'.$employee->employee_number.'.pdf');
    }

    /**
     * Submit a leave request.
     */
    public function storeLeaveRequest(Request $request)
    {
        $employee = auth()->user()->employee;

        if (! $employee) {
            return back()->with('error', 'Akun Anda belum terdaftar sebagai pegawai.');
        }

        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:sakit,izin,cuti_tahunan,cuti_melahirkan,umroh_haji,dinas_luar',
            'reason' => 'required|string|max:1000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('hrd/leaves', 'public');
        }

        $validated['employee_id'] = $employee->id;
        $validated['status'] = 'pending';

        LeaveRequest::create($validated);

        return back()->with('success', 'Pengajuan cuti/izin berhasil dikirim dan menunggu persetujuan.');
    }

    /**
     * Haversine formula to calculate distance between two coordinates in meters.
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Earth's radius in meters

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // distance in meters
    }
}
