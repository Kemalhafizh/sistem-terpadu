<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\LeaveRequest;
use App\Models\Payroll;
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

        // No employee record: say so. (Administrators used to get one made up on
        // the spot, with an invented Rp 4.500.000 base salary.)
        if (! $employee) {
            return view('hrd.my_portal.not_registered');
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

        return view('hrd.my_portal.index', compact(
            'employee',
            'todayAttendance',
            'recentAttendances',
            'recentLeaves',
            'payslips'
        ));
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

        $schoolName = school_name();
        $schoolAddress = school_address();
        $schoolPhone = school_phone();
        $schoolEmail = school_email();

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
}
