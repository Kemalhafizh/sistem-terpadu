@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-xl font-black text-gray-900 dark:text-white mb-1">Proyeksi RAPBS Multi-Tahun (Forecast)</h1>
        <p class="text-xs text-gray-500 mb-6">Simulasi pertumbuhan anggaran untuk tahun fiskal mendatang ({{ $forecast['forecast_fiscal_year'] }})</p>

        <div class="bg-emerald-50 dark:bg-emerald-950/30 p-4 rounded-2xl border border-emerald-200 text-sm space-y-3">
            <div class="flex justify-between">
                <span class="text-gray-600">Tahun Basis:</span>
                <span class="font-bold text-gray-900 dark:text-white">{{ $forecast['base_fiscal_year'] }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Asumsi Pertumbuhan Fiskal:</span>
                <span class="font-bold text-emerald-600">+{{ $forecast['growth_rate'] }}% / tahun</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Proyeksi Total Pendapatan:</span>
                <span class="font-bold text-emerald-600">Rp {{ number_format($forecast['forecast_revenue'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600">Proyeksi Total Belanja:</span>
                <span class="font-bold text-red-500">Rp {{ number_format($forecast['forecast_expense'], 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between border-t pt-2 border-emerald-200">
                <span class="font-bold text-gray-800">Proyeksi Net Surplus:</span>
                <span class="font-black text-teal-700">Rp {{ number_format($forecast['estimated_surplus'], 0, ',', '.') }}</span>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            <a href="{{ route('financial.budget.dashboard') }}" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali ke Dashboard</a>
        </div>
    </div>
</div>
@endsection
