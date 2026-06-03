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
    protected $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

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
     * Show form to generate monthly payroll.
     */
    public function create()
    {
        return view('hrd.payrolls.create');
    }

    /**
     * Generate payroll for all active employees.
     */
    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|string|size:2',
            'year' => 'required|string|size:4',
        ]);

        $month = $request->month;
        $year = $request->year;

        // Perform calculation
        $generated = $this->payrollService->generateMonthlyPayroll($month, $year);

        if ($generated === 0) {
            return redirect()->route('hrd.payrolls.index', ['month' => $month, 'year' => $year])
                ->with('error', 'Tidak ada pegawai aktif untuk dibuatkan payroll.');
        }

        return redirect()->route('hrd.payrolls.index', ['month' => $month, 'year' => $year])
            ->with('success', "Berhasil men-generate payroll untuk {$generated} pegawai.");
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
     * Update dynamic allowances/deductions of an individual payroll.
     */
    public function update(Request $request, Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Payroll yang sudah dibayarkan tidak dapat disunting kembali.');
        }

        $request->validate([
            'allowances' => 'required|numeric|min:0',
            'deductions' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $allowances = (float) $request->allowances;
        $deductions = (float) $request->deductions;
        $baseSalary = (float) $payroll->base_salary;

        $netSalary = $baseSalary + $allowances - $deductions;
        if ($netSalary < 0) {
            $netSalary = 0;
        }

        $payroll->update([
            'allowances' => $allowances,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
            'notes' => $request->notes,
        ]);

        return redirect()->route('hrd.payrolls.show', $payroll->id)
            ->with('success', 'Rincian gaji pegawai berhasil diperbarui.');
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
            ->with('success', "Berhasil menerbitkan (approve) {$approved} slip gaji bulanan.");
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
            ->with('success', "Berhasil memproses pembayaran untuk {$paid} pegawai.");
    }

    /**
     * Delete an individual payroll record.
     */
    public function destroy(Payroll $payroll)
    {
        if ($payroll->status === 'paid') {
            return back()->with('error', 'Payroll yang sudah dibayarkan tidak dapat dihapus.');
        }

        $month = $payroll->month;
        $year = $payroll->year;

        $payroll->delete();

        return redirect()->route('hrd.payrolls.index', ['month' => $month, 'year' => $year])
            ->with('success', 'Data payroll pegawai berhasil dihapus.');
    }
}
