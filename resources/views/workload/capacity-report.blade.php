@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Laporan Eksekutif Kapasitas & Distribusi Beban Guru</h1>
            <p class="text-xs text-gray-500 mt-1">Monitoring pemerataan beban jam mengajar & tugas tambahan</p>
        </div>
        <a href="{{ route('hc.workload.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Total Guru Evaluasi</span>
            <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $stats['total'] }} Guru</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Underload (< 24 JP)</span>
            <p class="text-2xl font-black text-amber-500 mt-1">{{ $stats['under'] }} Guru</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Balanced (Ideal)</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $stats['balanced'] }} Guru</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Overload (> 36 JP)</span>
            <p class="text-2xl font-black text-rose-600 mt-1">{{ $stats['over'] }} Guru</p>
        </div>
    </div>
</div>
@endsection
