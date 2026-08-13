@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Jalur Belajar - SMA Islam Sultan Agung 1 Semarang
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-2">Matematika</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">Penguatan Kompetensi Dasar</p>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-2">
                <div class="bg-emerald-600 h-2.5 rounded-full" style="width: 70%"></div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 text-right">70% Selesai</p>
        </div>
    </div>
</div>
@endsection
