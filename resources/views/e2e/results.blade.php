@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Laporan Hasil Eksekusi E2E Run #{{ $run->id }}</h1>
            <p class="text-xs text-gray-500 mt-1">Status: <strong class="text-emerald-600 font-bold uppercase">{{ $run->status }}</strong> | Durasi: {{ $run->duration_seconds }}s</p>
        </div>
        <a href="{{ route('deploy.e2e.dashboard') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
        <h3 class="text-base font-black text-gray-900 dark:text-white">Statistik Hasil Pengujian E2E:</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
            <div class="p-4 bg-emerald-50 rounded-2xl border border-emerald-100">
                <span class="text-xs font-bold text-emerald-700 uppercase">Passed</span>
                <p class="text-2xl font-black text-emerald-800 mt-1">{{ $run->passed }}</p>
            </div>
            <div class="p-4 bg-rose-50 rounded-2xl border border-rose-100">
                <span class="text-xs font-bold text-rose-700 uppercase">Failed</span>
                <p class="text-2xl font-black text-rose-800 mt-1">{{ $run->failed }}</p>
            </div>
            <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                <span class="text-xs font-bold text-gray-500 uppercase">Skipped</span>
                <p class="text-2xl font-black text-gray-700 mt-1">{{ $run->skipped }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
