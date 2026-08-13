@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Hasil Ujian CAT - SMA Islam Sultan Agung 1 Semarang
        </h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 text-center">
        <h2 class="font-bold text-gray-900 dark:text-white mb-4 text-xl">Estimasi Kemampuan (Theta)</h2>
        <p class="text-4xl font-black text-emerald-600 dark:text-emerald-400">1.25</p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Interval Kepercayaan: [0.95 - 1.55]</p>
        
        <div class="mt-8">
            <h3 class="font-bold text-gray-900 dark:text-white mb-4">Lintasan Adaptif</h3>
            <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center text-sm text-gray-600 dark:text-gray-300">
                [Placeholder Grafik Lintasan]
            </div>
        </div>
    </div>
</div>
@endsection
