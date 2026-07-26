@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Laporan Analisis Variansi RAPBS</h1>
            <p class="text-xs text-gray-500 mt-1">{{ $plan->title }} ({{ $plan->fiscal_year }})</p>
        </div>
        <a href="{{ route('financial.budget.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <!-- Variance Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Revenue Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-black uppercase text-emerald-600">Realisasi Pendapatan (Revenue)</h3>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-black">
                    {{ $report['revenue']['realization_rate'] }}% Terealisasi
                </span>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Target Rencana:</span>
                    <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($report['revenue']['planned'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Aktual Diterima:</span>
                    <span class="font-bold text-emerald-600">Rp {{ number_format($report['revenue']['actual'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t pt-2 border-gray-100">
                    <span class="text-gray-500">Selisih Variansi:</span>
                    <span class="font-black text-teal-600">Rp {{ number_format($report['revenue']['variance'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Expense Card -->
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
            <div class="flex justify-between items-center border-b pb-3 border-gray-100 dark:border-gray-700">
                <h3 class="text-sm font-black uppercase text-rose-600">Penyerapan Anggaran (Expense)</h3>
                <span class="px-3 py-1 bg-rose-100 text-rose-800 rounded-full text-xs font-black">
                    {{ $report['expense']['absorption_rate'] }}% Terserap
                </span>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Pagu Anggaran:</span>
                    <span class="font-bold text-gray-900 dark:text-white">Rp {{ number_format($report['expense']['planned'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Aktual Belanja:</span>
                    <span class="font-bold text-rose-500">Rp {{ number_format($report['expense']['actual'], 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between border-t pt-2 border-gray-100">
                    <span class="text-gray-500">Sisa Anggaran Belanja:</span>
                    <span class="font-black text-emerald-600">Rp {{ number_format($report['expense']['variance'], 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
