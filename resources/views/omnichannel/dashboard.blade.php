@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
            Dasbor Omnichannel - SMA Islam Sultan Agung 1 Semarang
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 font-bold">Ringkasan Komunikasi Terpadu</p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Pesan Terkirim</h3>
            <p class="text-3xl font-black text-indigo-600 dark:text-indigo-400 mt-4">12,450</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Tingkat Pengiriman</h3>
            <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-4">98.5%</p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Kampanye Aktif</h3>
            <p class="text-3xl font-black text-amber-600 dark:text-amber-400 mt-4">5</p>
        </div>
    </div>
</div>
@endsection
