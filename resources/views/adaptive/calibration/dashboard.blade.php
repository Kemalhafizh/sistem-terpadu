@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Dasbor Kalibrasi IRT - {{ school_name() }}
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-4">Statistik Soal Tervalibrasi</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">Total: 150 Soal</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-4">Distribusi Tingkat Kesulitan</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">Grafik distribusi (Placeholder)</p>
        </div>
    </div>
</div>
@endsection
