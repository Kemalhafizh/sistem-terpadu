@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Profil Penguasaan Siswa - SMA Islam Sultan Agung 1 Semarang
        </h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 text-center">
            <h2 class="font-bold text-gray-900 dark:text-white mb-4">Grafik Radar Individu</h2>
            <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center text-sm text-gray-600 dark:text-gray-300">
                [Placeholder Grafik]
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h2 class="font-bold text-gray-900 dark:text-white mb-4">Tabel Kemajuan KD</h2>
            <ul class="space-y-2">
                <li class="flex justify-between text-sm text-gray-600 dark:text-gray-300">
                    <span>KD 1.1 Aljabar</span>
                    <span class="text-emerald-600 font-bold">Mahir</span>
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection
