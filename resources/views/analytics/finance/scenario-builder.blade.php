@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Form Skenario Simulasi Arus Kas</h1>
            <p class="text-xs text-gray-500 mt-1">Estimasi Parameter Pendapatan & Pengeluaran RAPBS Jangka Panjang</p>
        </div>
        <a href="{{ route('analytics.finance.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <form action="{{ route('analytics.finance.store_scenario') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Skenario</label>
                <input type="text" name="scenario_name" required placeholder="Proyeksi RAPBS 2026/2027 Optimis" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estimasi Pendapatan (Rp)</label>
                    <input type="number" step="0.01" name="projected_revenue" required placeholder="1500000000" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Estimasi Pengeluaran (Rp)</label>
                    <input type="number" step="0.01" name="projected_expense" required placeholder="1200000000" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 rounded-xl p-3 text-sm font-semibold">
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl font-bold text-xs shadow-md">+ Jalankan Simulation Engine</button>
            </div>
        </form>
    </div>
</div>
@endsection
