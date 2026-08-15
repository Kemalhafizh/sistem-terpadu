@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
    <div class="flex items-center space-x-4 mb-8">
        <svg class="w-8 h-8 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">Galeri Karya Siswa - SMA Islam Sultan Agung 1 Semarang</h1>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-gray-200 mb-2">Proyek Sains Terapan</h2>
            <p class="text-sm text-gray-600 dark:text-gray-300">Oleh: Ahmad Dahlan</p>
            <div class="mt-4 flex space-x-2">
                <span class="bg-amber-100 text-amber-800 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-amber-200 dark:text-amber-900">Sains</span>
            </div>
        </div>
    </div>
</div>
@endsection
