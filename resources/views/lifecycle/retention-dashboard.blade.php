@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Cohort Retention Analytics Dashboard</h1>
            <p class="text-xs text-gray-500 mt-1">Monitoring Tingkat Retensi Siswa & Pencegahan Dropout SMA ISA 1 Semarang</p>
        </div>
        <a href="{{ route('growth.lifecycle.at_risk') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow-md">Daftar Siswa At-Risk &rarr;</a>
    </div>

    <!-- Retention Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Total Siswa Terdaftar</span>
            <p class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $retention['total_students'] }} Siswa</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Siswa Aktif Saat Ini</span>
            <p class="text-2xl font-black text-emerald-600 mt-1">{{ $retention['active_students'] }} Siswa</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Lulus (Graduated Alumni)</span>
            <p class="text-2xl font-black text-indigo-600 mt-1">{{ $retention['graduated_count'] }} Alumni</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 shadow-sm">
            <span class="text-xs font-bold text-gray-400 uppercase">Tingkat Retensi Cohort</span>
            <p class="text-2xl font-black text-teal-600 mt-1">{{ $retention['retention_rate'] }}%</p>
        </div>
    </div>
</div>
@endsection
