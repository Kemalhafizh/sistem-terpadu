@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Dasbor NPS - SMA Islam Sultan Agung 1 Semarang
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 font-bold">Net Promoter Score</p>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm text-center mb-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Skor NPS Saat Ini</h2>
        <div class="text-6xl font-black text-emerald-600 dark:text-emerald-400 mt-4">+45</div>
    </div>
</div>
@endsection
