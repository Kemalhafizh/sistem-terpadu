<?php

namespace App\Http\Controllers;

use App\Models\OsisTransaction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class OsisController extends Controller
{
    public function dashboard()
    {
        $transactions = OsisTransaction::with('recorder')
            ->orderBy('transaction_date', 'desc')
            ->paginate(15);

        // Agregasi Keuangan OSIS
        $totalIncome = OsisTransaction::where('type', 'income')->sum('amount');
        $totalExpense = OsisTransaction::where('type', 'expense')->sum('amount');
        $currentBalance = $totalIncome - $totalExpense;

        // Distribusi Kategori Pemasukan
        $categoriesIncome = OsisTransaction::where('type', 'income')
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // Distribusi Kategori Pengeluaran
        $categoriesExpense = OsisTransaction::where('type', 'expense')
            ->selectRaw('category, sum(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        return view('extracurricular.osis.dashboard', compact(
            'transactions',
            'totalIncome',
            'totalExpense',
            'currentBalance',
            'categoriesIncome',
            'categoriesExpense'
        ));
    }

    public function storeTransaction(Request $request)
    {
        $request->validate([
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'category' => 'required|string|in:Iuran,Sponsor,Kegiatan,Perlengkapan,Lainnya',
            'transaction_date' => 'required|date',
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240', // Maks 10MB
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt')) {
            $receiptPath = $request->file('receipt')->store('uploads/osis/receipts', 'public');
        }

        OsisTransaction::create([
            'type' => $request->type,
            'amount' => $request->amount,
            'description' => $request->description,
            'category' => $request->category,
            'transaction_date' => $request->transaction_date,
            'receipt_path' => $receiptPath,
            'recorded_by' => auth()->id(),
        ]);

        return back()->with('success', 'Transaksi kas OSIS berhasil dicatat.');
    }

    public function exportReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $transactions = OsisTransaction::with('recorder')
            ->whereBetween('transaction_date', [$request->start_date, $request->end_date])
            ->orderBy('transaction_date', 'asc')
            ->get();

        $totalIncome = $transactions->where('type', 'income')->sum('amount');
        $totalExpense = $transactions->where('type', 'expense')->sum('amount');
        $netBalance = $totalIncome - $totalExpense;

        $pdf = Pdf::loadView('extracurricular.osis.pdf_report', [
            'transactions' => $transactions,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netBalance' => $netBalance,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        return $pdf->setPaper('a4', 'portrait')->download('Laporan_Kas_OSIS_' . $request->start_date . '_to_' . $request->end_date . '.pdf');
    }
}
