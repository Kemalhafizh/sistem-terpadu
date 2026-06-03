<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Account;
use App\Models\Billing;
use App\Models\Classroom;
use App\Models\JournalEntry;
use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->year_id ?? ($activeYear ? $activeYear->id : null);

        // 1. Core KPIs
        $totalProjected = Billing::when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))->sum('amount');
        $totalCollected = Payment::whereHas('billing', function ($q) use ($yearId) {
            $q->when($yearId, fn ($sq) => $sq->where('academic_year_id', $yearId));
        })->sum('amount_paid');

        $totalOutstanding = $totalProjected - $totalCollected;
        $collectionRate = $totalProjected > 0 ? ($totalCollected / $totalProjected) * 100 : 0;

        // 2. Monthly Revenue Trend (Last 6 Months)
        // Using PHP-level grouping instead of MySQL DATE_FORMAT for SQLite compatibility
        $monthlyRevenue = Payment::whereHas('billing', function ($q) use ($yearId) {
            $q->when($yearId, fn ($sq) => $sq->where('academic_year_id', $yearId));
        })
            ->orderBy('payment_date', 'asc')
            ->get()
            ->groupBy(fn ($p) => Carbon::parse($p->payment_date)->translatedFormat('F Y'))
            ->map(fn ($group, $month) => (object) [
            'month' => $month,
            'total' => $group->sum('amount_paid'),
        ])
            ->values()
            ->take(6);

        // 3. Arrears by Classroom
        // We calculate sum of (billing.amount - sum(payments.amount_paid)) per class
        $classArrears = Classroom::with(['students.billings'])
            ->get()
            ->map(function ($class) use ($yearId) {
                $classTotalBill = 0;
                $classTotalPaid = 0;

                foreach ($class->students as $student) {
                    $studentBillings = $student->billings->when($yearId, fn ($c) => $c->where('academic_year_id', $yearId));
                    $classTotalBill += $studentBillings->sum('amount');

                    // Since billings doesn't directly have amount_paid (it's in payments),
                    // we can get it from the status/remaining logic or query payments.
                    // For accuracy, let's use the Payments relation.
                }

                // Subquery approach is better for large data
                $billSum = DB::table('billings')
                    ->join('students', 'billings.student_id', '=', 'students.id')
                    ->where('students.classroom_id', $class->id)
                    ->when($yearId, fn ($q) => $q->where('billings.academic_year_id', $yearId))
                    ->sum('billings.amount');

                $paidSum = DB::table('payments')
                    ->join('billings', 'payments.billing_id', '=', 'billings.id')
                    ->join('students', 'billings.student_id', '=', 'students.id')
                    ->where('students.classroom_id', $class->id)
                    ->when($yearId, fn ($q) => $q->where('billings.academic_year_id', $yearId))
                    ->sum('payments.amount_paid');

                return [
                    'name' => $class->name,
                    'total_bill' => $billSum,
                    'total_paid' => $paidSum,
                    'outstanding' => $billSum - $paidSum,
                ];
            })
            ->sortByDesc('outstanding')
            ->take(5);

        // 4. Top Debtors
        $topDebtors = Student::with(['user', 'classroom'])
            ->get()
            ->map(function ($student) use ($yearId) {
                $billings = $student->billings()->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))->get();
                $totalBill = $billings->sum('amount');

                $totalPaid = DB::table('payments')
                    ->whereIn('billing_id', $billings->pluck('id'))
                    ->sum('amount_paid');

                return [
                    'student' => $student,
                    'outstanding' => $totalBill - $totalPaid,
                ];
            })
            ->where('outstanding', '>', 0)
            ->sortByDesc('outstanding')
            ->take(10);

        $years = AcademicYear::all();

        return view('finance.reports.executive', compact(
            'totalProjected', 'totalCollected', 'totalOutstanding', 'collectionRate',
            'monthlyRevenue', 'classArrears', 'topDebtors', 'years', 'yearId'
        ));
    }

    /**
     * Laporan Laba Rugi (Income Statement).
     */
    public function incomeStatement(Request $request)
    {
        // Hitung total pendapatan secara dinamis dari jurnal
        $revenueAccounts = Account::where('type', 'revenue')->with(['lines'])->get()->map(function ($account) {
            // Normal balance kredit: Kredit - Debit
            $total = $account->lines->sum('credit') - $account->lines->sum('debit');

            return (object) [
                'code' => $account->code,
                'name' => $account->name,
                'total' => $total,
            ];
        });

        $totalRevenue = $revenueAccounts->sum('total');

        // Hitung total beban secara dinamis
        $expenseAccounts = Account::where('type', 'expense')->with(['lines'])->get()->map(function ($account) {
            // Normal balance debit: Debit - Kredit
            $total = $account->lines->sum('debit') - $account->lines->sum('credit');

            return (object) [
                'code' => $account->code,
                'name' => $account->name,
                'total' => $total,
            ];
        });

        $totalExpense = $expenseAccounts->sum('total');
        $netIncome = $totalRevenue - $totalExpense;

        return view('finance.reports.income_statement', compact('revenueAccounts', 'totalRevenue', 'expenseAccounts', 'totalExpense', 'netIncome'));
    }

    /**
     * Neraca Keuangan (Balance Sheet).
     */
    public function balanceSheet(Request $request)
    {
        // 1. Aset
        $assetAccounts = Account::where('type', 'asset')->with(['lines'])->get()->map(function ($account) {
            // Normal balance debit: Debit - Kredit
            $balance = $account->lines->sum('debit') - $account->lines->sum('credit');

            return (object) [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $balance,
            ];
        });
        $totalAssets = $assetAccounts->sum('balance');

        // 2. Kewajiban
        $liabilityAccounts = Account::where('type', 'liability')->with(['lines'])->get()->map(function ($account) {
            // Normal balance kredit: Kredit - Debit
            $balance = $account->lines->sum('credit') - $account->lines->sum('debit');

            return (object) [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $balance,
            ];
        });
        $totalLiabilities = $liabilityAccounts->sum('balance');

        // 3. Ekuitas (Termasuk Laba Rugi Berjalan)
        $revenueTotal = Account::where('type', 'revenue')->with(['lines'])->get()->sum(fn ($a) => $a->lines->sum('credit') - $a->lines->sum('debit'));
        $expenseTotal = Account::where('type', 'expense')->with(['lines'])->get()->sum(fn ($a) => $a->lines->sum('debit') - $a->lines->sum('credit'));
        $currentNetIncome = $revenueTotal - $expenseTotal;

        $equityAccounts = Account::where('type', 'equity')->with(['lines'])->get()->map(function ($account) {
            // Normal balance kredit: Kredit - Debit
            $balance = $account->lines->sum('credit') - $account->lines->sum('debit');

            return (object) [
                'code' => $account->code,
                'name' => $account->name,
                'balance' => $balance,
            ];
        });

        $totalEquityBase = $equityAccounts->sum('balance');
        $totalEquity = $totalEquityBase + $currentNetIncome;

        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        return view('finance.reports.balance_sheet', compact(
            'assetAccounts', 'totalAssets',
            'liabilityAccounts', 'totalLiabilities',
            'equityAccounts', 'totalEquityBase', 'currentNetIncome', 'totalEquity',
            'totalLiabilitiesAndEquity'
        ));
    }

    /**
     * Log Jurnal Umum (General Ledger).
     */
    public function generalLedger(Request $request)
    {
        $entries = JournalEntry::with(['lines.account'])
            ->orderBy('entry_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return view('finance.reports.general_ledger', compact('entries'));
    }
}
