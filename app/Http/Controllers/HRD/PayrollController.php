<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\EmployeeAttendance;
use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $payrollService) {}

    /**
     * Display a listing of payrolls with filters.
     */
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('m'));
        $year = $request->input('year', Carbon::now()->format('Y'));

        $query = Payroll::with('employee.user')
            ->where('month', $month)
            ->where('year', $year);

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('employee.user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $payrolls = $query->paginate(15)->appends($request->all());

        // Calculate summary statistics
        $stats = [
            'total_expenditure' => Payroll::where('month', $month)->where('year', $year)->sum('net_salary'),
            'draft_count' => Payroll::where('month', $month)->where('year', $year)->where('status', 'draft')->count(),
            'approved_count' => Payroll::where('month', $month)->where('year', $year)->where('status', 'approved')->count(),
            'paid_count' => Payroll::where('month', $month)->where('year', $year)->where('status', 'paid')->count(),
        ];

        return view('hrd.payrolls.index', compact('payrolls', 'month', 'year', 'stats'));
    }

    /**
     * Show form to generate monthly payroll, with the rules it applies.
     */
    public function create()
    {
        return view('hrd.payrolls.create', [
            'rates' => $this->payrollService->rates(),
            'lateAfter' => substr((string) config('fingerprint.late_after'), 0, 5),
            'workDays' => collect(config('hrd.work_days'))->map(fn (int $day) => Carbon::now()->isoWeekday($day)->isoFormat('dddd'))->implode(', '),
        ]);
    }

    /**
     * Save the school's deduction per late day and per absent day.
     */
    public function updateRules(Request $request)
    {
        $validated = $request->validate([
            'late_deduction' => 'nullable|numeric|min:0',
            'absent_deduction' => 'nullable|numeric|min:0',
        ]);

        $this->payrollService->saveRates(
            isset($validated['late_deduction']) ? (float) $validated['late_deduction'] : null,
            isset($validated['absent_deduction']) ? (float) $validated['absent_deduction'] : null,
        );

        return redirect()->route('hrd.payrolls.create')->with('success', __('Aturan potongan gaji disimpan.'));
    }

    /**
     * Generate the month's draft slips for all employees.
     */
    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4',
        ]);

        $month = $request->month;
        $year = $request->year;
        $result = $this->payrollService->generateMonthlyPayroll($month, $year);
        $back = redirect()->route('hrd.payrolls.index', ['month' => $month, 'year' => $year]);

        if ($result['generated'] === 0) {
            return $back->with('error', $result['locked'] > 0
                ? __('Semua slip gaji periode ini sudah diterbitkan atau dibayar, jadi tidak dihitung ulang.')
                : __('Belum ada data pegawai untuk dibuatkan slip gaji.'));
        }

        $message = __('Slip gaji dihitung untuk :count pegawai.', ['count' => $result['generated']]);
        if ($result['locked'] > 0) {
            $message .= ' '.__(':count slip yang sudah diterbitkan atau dibayar tidak diubah.', ['count' => $result['locked']]);
        }

        $back->with('success', $message);

        return $result['has_attendance'] ? $back : $back->with('warning', __('Belum ada data presensi untuk periode ini, jadi tidak ada potongan kehadiran. Impor log mesin sidik jari lalu hitung ulang.'));
    }

    public function show(Payroll $payroll)
    {
        $payroll->load('employee.user');

        $startDate = Carbon::createFromDate($payroll->year, $payroll->month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($payroll->year, $payroll->month, 1)->endOfMonth();

        $attendances = EmployeeAttendance::where('employee_id', $payroll->employee_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date', 'desc')
            ->get();

        return view('hrd.payrolls.show', compact('payroll', 'attendances'));
    }

    /**
     * Update the allowances and other deductions TU types in.
     */
    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', __('Slip gaji yang sudah dibayar tidak dapat diubah.'));
        }

        $validated = $request->validate([
            'allowances' => 'required|numeric|min:0',
            'other_deductions' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $this->payrollService->applyAdjustments($payroll, (float) $validated['allowances'], (float) $validated['other_deductions'], $validated['notes'] ?? null);

        return redirect()->route('hrd.payrolls.show', $payroll->id)
            ->with('success', __('Rincian gaji pegawai diperbarui.'));
    }

    /**
     * Issue one slip to its employee.
     */
    public function approve(Payroll $payroll)
    {
        if ($payroll->status !== 'draft') {
            return back()->with('error', __('Hanya slip berstatus draf yang dapat diterbitkan.'));
        }

        $payroll->update(['status' => 'approved']);

        return redirect()->route('hrd.payrolls.show', $payroll->id)->with('success', __('Slip gaji diterbitkan.'));
    }

    /**
     * Mark one issued slip as paid.
     */
    public function pay(Payroll $payroll)
    {
        if ($payroll->status !== 'approved') {
            return back()->with('error', __('Hanya slip yang sudah diterbitkan yang dapat ditandai dibayar.'));
        }

        $payroll->update(['status' => 'paid', 'payment_date' => Carbon::now()->format('Y-m-d')]);

        return redirect()->route('hrd.payrolls.show', $payroll->id)->with('success', __('Slip gaji ditandai sudah dibayar.'));
    }

    /**
     * Mass approve payrolls for the selected month/year.
     */
    public function approveBulk(Request $request)
    {
        $request->validate([
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4',
        ]);

        $month = $request->month;
        $year = $request->year;

        $approved = Payroll::where('month', $month)
            ->where('year', $year)
            ->where('status', 'draft')
            ->update(['status' => 'approved']);

        return redirect()->route('hrd.payrolls.index', ['month' => $month, 'year' => $year])
            ->with('success', __(':count slip gaji diterbitkan.', ['count' => $approved]));
    }

    /**
     * Mass mark payrolls as paid.
     */
    public function payBulk(Request $request)
    {
        $request->validate([
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4',
        ]);

        $month = $request->month;
        $year = $request->year;

        $paid = Payroll::where('month', $month)
            ->where('year', $year)
            ->where('status', 'approved')
            ->update([
                'status' => 'paid',
                'payment_date' => Carbon::now()->format('Y-m-d'),
            ]);

        return redirect()->route('hrd.payrolls.index', ['month' => $month, 'year' => $year])
            ->with('success', __(':count slip gaji ditandai sudah dibayar.', ['count' => $paid]));
    }

    /**
     * Delete an individual payroll record.
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', __('Slip gaji yang sudah dibayar tidak dapat dihapus.'));
        }

        $month = $payroll->month;
        $year = $payroll->year;

        $payroll->delete();

        return redirect()->route('hrd.payrolls.index', ['month' => $month, 'year' => $year])
            ->with('success', __('Slip gaji pegawai dihapus.'));
    }
}
