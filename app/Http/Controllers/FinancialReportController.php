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
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

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

    /**
     * Laporan Tunggakan Per Kelas.
     */
    public function arrearsReport(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->year_id ?? ($activeYear ? $activeYear->id : null);

        $classrooms = Classroom::all()->map(function ($class) use ($yearId) {
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

            $outstandingStudentsCount = Student::where('classroom_id', $class->id)
                ->whereHas('billings', function ($q) use ($yearId) {
                    $q->when($yearId, fn ($sq) => $sq->where('academic_year_id', $yearId))
                      ->where('remaining_balance', '>', 0);
                })
                ->count();

            return [
                'id' => $class->id,
                'name' => $class->name,
                'total_bill' => $billSum,
                'total_paid' => $paidSum,
                'outstanding' => $billSum - $paidSum,
                'outstanding_students_count' => $outstandingStudentsCount,
            ];
        })->sortByDesc('outstanding');

        $selectedClassroom = null;
        $studentArrears = collect();
        if ($request->filled('classroom_id')) {
            $selectedClassroom = Classroom::findOrFail($request->classroom_id);
            $studentArrears = Student::with(['user'])
                ->where('classroom_id', $selectedClassroom->id)
                ->whereHas('billings', function ($q) use ($yearId) {
                    $q->when($yearId, fn ($sq) => $sq->where('academic_year_id', $yearId))
                      ->where('remaining_balance', '>', 0);
                })
                ->get()
                ->map(function ($student) use ($yearId) {
                    $studentBillings = $student->billings()
                        ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                        ->where('remaining_balance', '>', 0)
                        ->with('category')
                        ->get();

                    return [
                        'id' => $student->id,
                        'name' => $student->user->name ?? $student->nis,
                        'nis' => $student->nis,
                        'unpaid_billings_count' => $studentBillings->count(),
                        'outstanding' => $studentBillings->sum('remaining_balance'),
                        'billings' => $studentBillings,
                    ];
                })->sortByDesc('outstanding');
        }

        $years = AcademicYear::all();
        $classroomsList = Classroom::all();

        return view('finance.reports.arrears', compact('classrooms', 'selectedClassroom', 'studentArrears', 'years', 'yearId', 'classroomsList'));
    }

    /**
     * Proyeksi Pendapatan Keuangan (Prediksi Kas Masuk).
     */
    public function revenueProjection(Request $request)
    {
        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->year_id ?? ($activeYear ? $activeYear->id : null);

        $sixMonthsAgo = now()->subMonths(6)->startOfMonth();
        $today = now();

        $pastBillings = Billing::whereBetween('due_date', [$sixMonthsAgo->toDateString(), $today->toDateString()])
            ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->sum('amount');

        $pastPayments = Payment::whereBetween('payment_date', [$sixMonthsAgo->toDateString(), $today->toDateString()])
            ->whereHas('billing', function ($q) use ($yearId) {
                $q->when($yearId, fn ($sq) => $sq->where('academic_year_id', $yearId));
            })
            ->sum('amount_paid');

        $collectionRate = $pastBillings > 0 ? ($pastPayments / $pastBillings) * 100 : 85.0;
        $collectionRate = min(100.0, max(0.0, $collectionRate));

        $months = [];
        $billingAmounts = [];
        $projectedCollections = [];
        $projectedOutstandings = [];

        $avgMonthlyBilling = Billing::when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
            ->get()
            ->groupBy(fn ($b) => Carbon::parse($b->due_date)->format('Y-m'))
            ->avg(fn ($group) => $group->sum('amount')) ?? 15000000.0;

        for ($i = 1; $i <= 6; $i++) {
            $monthDate = now()->addMonths($i);
            $start = $monthDate->copy()->startOfMonth()->toDateString();
            $end = $monthDate->copy()->endOfMonth()->toDateString();

            $monthBilling = Billing::whereBetween('due_date', [$start, $end])
                ->when($yearId, fn ($q) => $q->where('academic_year_id', $yearId))
                ->sum('amount');

            if ($monthBilling <= 0) {
                $monthBilling = $avgMonthlyBilling;
            }

            $projectedCollection = $monthBilling * ($collectionRate / 100);
            $projectedOutstanding = $monthBilling - $projectedCollection;

            $months[] = $monthDate->translatedFormat('F Y');
            $billingAmounts[] = (float) $monthBilling;
            $projectedCollections[] = (float) $projectedCollection;
            $projectedOutstandings[] = (float) $projectedOutstanding;
        }

        $years = AcademicYear::all();

        return view('finance.reports.projection', compact(
            'collectionRate', 'months', 'billingAmounts', 'projectedCollections', 'projectedOutstandings', 'years', 'yearId'
        ));
    }

    /**
     * Rekapitulasi Laporan Keuangan Bulanan.
     */
    public function monthlySummary(Request $request)
    {
        $month = $request->month ?? now()->month;
        $year = $request->year ?? now()->year;

        $startDate = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth()->toDateString();

        $revenueAccounts = Account::where('type', 'revenue')->get()->map(function ($account) use ($startDate, $endDate) {
            $total = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entry_lines.account_id', $account->id)
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->sum(DB::raw('credit - debit'));

            return (object) [
                'code' => $account->code,
                'name' => $account->name,
                'total' => $total,
            ];
        })->filter(fn($a) => $a->total != 0)->values();

        $totalRevenue = $revenueAccounts->sum('total');

        $expenseAccounts = Account::where('type', 'expense')->get()->map(function ($account) use ($startDate, $endDate) {
            $total = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entry_lines.account_id', $account->id)
                ->whereBetween('journal_entries.entry_date', [$startDate, $endDate])
                ->sum(DB::raw('debit - credit'));

            return (object) [
                'code' => $account->code,
                'name' => $account->name,
                'total' => $total,
            ];
        })->filter(fn($a) => $a->total != 0)->values();

        $totalExpense = $expenseAccounts->sum('total');
        $netIncome = $totalRevenue - $totalExpense;

        $monthsList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $yearsList = range(now()->year - 3, now()->year + 2);

        return view('finance.reports.monthly', compact(
            'revenueAccounts', 'totalRevenue', 'expenseAccounts', 'totalExpense', 'netIncome',
            'month', 'year', 'monthsList', 'yearsList'
        ));
    }

    /**
     * Cetak Surat Tagihan SPP Otomatis (PDF).
     */
    public function generateInvoice(Billing $billing)
    {
        $billing->load(['student.user', 'student.classroom', 'student.parent', 'category', 'academicYear']);

        $pdf = Pdf::loadView('finance.reports.invoice_pdf', compact('billing'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('Surat_Tagihan_'.str_replace(' ', '_', $billing->student->user->name ?? $billing->student_id).'_'.$billing->id.'.pdf');
    }

    /**
     * Ekspor Laporan Keuangan Neraca, Laba Rugi, Buku Besar, Tunggakan ke PDF/Excel.
     */
    public function exportReport(Request $request)
    {
        $type = $request->type;
        $format = $request->format ?? 'excel';
        $activeYear = AcademicYear::where('is_active', true)->first();
        $yearId = $request->year_id ?? ($activeYear ? $activeYear->id : null);

        if ($format === 'excel') {
            if ($type === 'arrears') {
                $classrooms = Classroom::all()->map(function ($class) use ($yearId) {
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

                    $outstandingStudentsCount = Student::where('classroom_id', $class->id)
                        ->whereHas('billings', function ($q) use ($yearId) {
                            $q->when($yearId, fn ($sq) => $sq->where('academic_year_id', $yearId))
                              ->where('remaining_balance', '>', 0);
                        })
                        ->count();

                    return (object) [
                        'name' => $class->name,
                        'total_bill' => $billSum,
                        'total_paid' => $paidSum,
                        'outstanding' => $billSum - $paidSum,
                        'outstanding_students' => $outstandingStudentsCount,
                    ];
                });

                $headings = ['Nama Kelas', 'Total Tagihan (Rp)', 'Total Terbayar (Rp)', 'Total Tunggakan (Rp)', 'Jumlah Siswa Menunggak'];
                $mapCallback = function ($row) {
                    return [
                        $row->name,
                        $row->total_bill,
                        $row->total_paid,
                        $row->outstanding,
                        $row->outstanding_students,
                    ];
                };

                return Excel::download(new \App\Exports\GenericExport($classrooms, $headings, $mapCallback), 'laporan_tunggakan_kelas_'.date('Ymd').'.xlsx');
            }

            if ($type === 'income_statement') {
                $revenueAccounts = Account::where('type', 'revenue')->with(['lines'])->get()->map(function ($account) {
                    $total = $account->lines->sum('credit') - $account->lines->sum('debit');
                    return (object) [
                        'code' => $account->code,
                        'name' => $account->name,
                        'total' => $total,
                    ];
                });
                $expenseAccounts = Account::where('type', 'expense')->with(['lines'])->get()->map(function ($account) {
                    $total = $account->lines->sum('debit') - $account->lines->sum('credit');
                    return (object) [
                        'code' => $account->code,
                        'name' => $account->name,
                        'total' => $total,
                    ];
                });

                $rows = collect();
                $rows->push((object)['code' => 'REVENUE (PENDAPATAN)', 'name' => '', 'total' => '']);
                foreach ($revenueAccounts as $r) {
                    $rows->push($r);
                }
                $rows->push((object)['code' => 'TOTAL PENDAPATAN', 'name' => '', 'total' => $revenueAccounts->sum('total')]);
                $rows->push((object)['code' => '', 'name' => '', 'total' => '']);
                $rows->push((object)['code' => 'EXPENSE (BEBAN OPERASIONAL)', 'name' => '', 'total' => '']);
                foreach ($expenseAccounts as $e) {
                    $rows->push($e);
                }
                $rows->push((object)['code' => 'TOTAL BEBAN', 'name' => '', 'total' => $expenseAccounts->sum('total')]);
                $rows->push((object)['code' => '', 'name' => '', 'total' => '']);
                $rows->push((object)['code' => 'LABA/RUGI BERSIH', 'name' => '', 'total' => $revenueAccounts->sum('total') - $expenseAccounts->sum('total')]);

                $headings = ['Kode/Deskripsi', 'Nama Akun', 'Nominal (Rp)'];
                $mapCallback = function ($row) {
                    return [$row->code, $row->name, $row->total];
                };

                return Excel::download(new \App\Exports\GenericExport($rows, $headings, $mapCallback), 'laporan_laba_rugi_'.date('Ymd').'.xlsx');
            }

            if ($type === 'balance_sheet') {
                $assetAccounts = Account::where('type', 'asset')->with(['lines'])->get()->map(fn($a) => (object)['code' => $a->code, 'name' => $a->name, 'balance' => $a->lines->sum('debit') - $a->lines->sum('credit')]);
                $liabilityAccounts = Account::where('type', 'liability')->with(['lines'])->get()->map(fn($a) => (object)['code' => $a->code, 'name' => $a->name, 'balance' => $a->lines->sum('credit') - $a->lines->sum('debit')]);
                $equityAccounts = Account::where('type', 'equity')->with(['lines'])->get()->map(fn($a) => (object)['code' => $a->code, 'name' => $a->name, 'balance' => $a->lines->sum('credit') - $a->lines->sum('debit')]);

                $rev = Account::where('type', 'revenue')->with(['lines'])->get()->sum(fn ($a) => $a->lines->sum('credit') - $a->lines->sum('debit'));
                $exp = Account::where('type', 'expense')->with(['lines'])->get()->sum(fn ($a) => $a->lines->sum('debit') - $a->lines->sum('credit'));
                $net = $rev - $exp;

                $rows = collect();
                $rows->push((object)['code' => 'ASSETS (AKTIVA)', 'name' => '', 'balance' => '']);
                foreach ($assetAccounts as $a) { $rows->push($a); }
                $rows->push((object)['code' => 'TOTAL ASSETS', 'name' => '', 'balance' => $assetAccounts->sum('balance')]);
                $rows->push((object)['code' => '', 'name' => '', 'balance' => '']);
                $rows->push((object)['code' => 'LIABILITIES (KEWAJIBAN)', 'name' => '', 'balance' => '']);
                foreach ($liabilityAccounts as $l) { $rows->push($l); }
                $rows->push((object)['code' => 'TOTAL LIABILITIES', 'name' => '', 'balance' => $liabilityAccounts->sum('balance')]);
                $rows->push((object)['code' => '', 'name' => '', 'balance' => '']);
                $rows->push((object)['code' => 'EQUITY (EKUITAS)', 'name' => '', 'balance' => '']);
                foreach ($equityAccounts as $e) { $rows->push($e); }
                $rows->push((object)['code' => 'LABA RUGI BERJALAN', 'name' => '', 'balance' => $net]);
                $rows->push((object)['code' => 'TOTAL EQUITY', 'name' => '', 'balance' => $equityAccounts->sum('balance') + $net]);

                $headings = ['Kode/Kategori', 'Nama Akun', 'Saldo (Rp)'];
                $mapCallback = function ($row) {
                    return [$row->code, $row->name, $row->balance];
                };

                return Excel::download(new \App\Exports\GenericExport($rows, $headings, $mapCallback), 'neraca_keuangan_'.date('Ymd').'.xlsx');
            }

            if ($type === 'general_ledger') {
                $entries = JournalEntry::with(['lines.account'])->orderBy('entry_date', 'desc')->get();
                $rows = collect();
                foreach ($entries as $e) {
                    foreach ($e->lines as $line) {
                        $rows->push((object)[
                            'date' => $e->entry_date,
                            'ref' => $e->reference,
                            'desc' => $e->description,
                            'code' => $line->account->code,
                            'name' => $line->account->name,
                            'debit' => $line->debit,
                            'credit' => $line->credit,
                        ]);
                    }
                }

                $headings = ['Tanggal', 'Referensi', 'Keterangan Jurnal', 'Kode Akun', 'Nama Akun', 'Debit (Rp)', 'Kredit (Rp)'];
                $mapCallback = function ($row) {
                    return [$row->date, $row->ref, $row->desc, $row->code, $row->name, $row->debit, $row->credit];
                };

                return Excel::download(new \App\Exports\GenericExport($rows, $headings, $mapCallback), 'buku_besar_'.date('Ymd').'.xlsx');
            }
        }

        if ($format === 'pdf') {
            if ($type === 'income_statement') {
                $revenueAccounts = Account::where('type', 'revenue')->with(['lines'])->get()->map(function ($account) {
                    return (object) ['code' => $account->code, 'name' => $account->name, 'total' => $account->lines->sum('credit') - $account->lines->sum('debit')];
                });
                $totalRevenue = $revenueAccounts->sum('total');

                $expenseAccounts = Account::where('type', 'expense')->with(['lines'])->get()->map(function ($account) {
                    return (object) ['code' => $account->code, 'name' => $account->name, 'total' => $account->lines->sum('debit') - $account->lines->sum('credit')];
                });
                $totalExpense = $expenseAccounts->sum('total');
                $netIncome = $totalRevenue - $totalExpense;

                $pdf = Pdf::loadView('finance.reports.income_statement', compact('revenueAccounts', 'totalRevenue', 'expenseAccounts', 'totalExpense', 'netIncome'));
                $pdf->setPaper('A4', 'portrait');
                return $pdf->download('laporan_laba_rugi_'.date('Ymd').'.pdf');
            }

            if ($type === 'balance_sheet') {
                $assetAccounts = Account::where('type', 'asset')->with(['lines'])->get()->map(fn($a) => (object)['code' => $a->code, 'name' => $a->name, 'balance' => $a->lines->sum('debit') - $a->lines->sum('credit')]);
                $totalAssets = $assetAccounts->sum('balance');

                $liabilityAccounts = Account::where('type', 'liability')->with(['lines'])->get()->map(fn($a) => (object)['code' => $a->code, 'name' => $a->name, 'balance' => $a->lines->sum('credit') - $a->lines->sum('debit')]);
                $totalLiabilities = $liabilityAccounts->sum('balance');

                $revenueTotal = Account::where('type', 'revenue')->with(['lines'])->get()->sum(fn ($a) => $a->lines->sum('credit') - $a->lines->sum('debit'));
                $expenseTotal = Account::where('type', 'expense')->with(['lines'])->get()->sum(fn ($a) => $a->lines->sum('debit') - $a->lines->sum('credit'));
                $currentNetIncome = $revenueTotal - $expenseTotal;

                $equityAccounts = Account::where('type', 'equity')->with(['lines'])->get()->map(fn($a) => (object)['code' => $a->code, 'name' => $a->name, 'balance' => $a->lines->sum('credit') - $a->lines->sum('debit')]);
                $totalEquityBase = $equityAccounts->sum('balance');
                $totalEquity = $totalEquityBase + $currentNetIncome;
                $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

                $pdf = Pdf::loadView('finance.reports.balance_sheet', compact(
                    'assetAccounts', 'totalAssets',
                    'liabilityAccounts', 'totalLiabilities',
                    'equityAccounts', 'totalEquityBase', 'currentNetIncome', 'totalEquity',
                    'totalLiabilitiesAndEquity'
                ));
                $pdf->setPaper('A4', 'portrait');
                return $pdf->download('neraca_keuangan_'.date('Ymd').'.pdf');
            }

            if ($type === 'general_ledger') {
                $entries = JournalEntry::with(['lines.account'])->orderBy('entry_date', 'desc')->get();
                $pdf = Pdf::loadView('finance.reports.general_ledger', compact('entries'));
                $pdf->setPaper('A4', 'portrait');
                return $pdf->download('buku_besar_'.date('Ymd').'.pdf');
            }
        }

        return back()->with('error', 'Jenis laporan atau format tidak didukung.');
    }
}

