@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m3-4h1m-1 4h1m-5 8h8"></path></svg>
            Tinjauan Kelas - SMA Islam Sultan Agung 1 Semarang
        </h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <h2 class="font-bold text-gray-900 dark:text-white mb-4">Statistik Kelas</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Rata-rata Kelas</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">82.5</p>
            </div>
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">KD Paling Menantang</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white">KD 3.2</p>
            </div>
        </div>
    </div>
</div>
@endsection
