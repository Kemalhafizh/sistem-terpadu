@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 p-8 text-center">
        <svg class="w-16 h-16 text-emerald-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        <h1 class="text-3xl font-black text-gray-900 dark:text-white mb-2">Pelacak Nutrisi Harian</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">Informasi gizi untuk siswa</p>
        
        <div class="inline-block bg-emerald-50 dark:bg-emerald-900/20 rounded-full px-8 py-6 border border-emerald-100 dark:border-emerald-800">
            <div class="text-sm font-bold text-emerald-800 dark:text-emerald-400 mb-1">Total Kalori Hari Ini</div>
            <div class="text-5xl font-black text-emerald-600 dark:text-emerald-500">{{ $dailyCalories ?? 0 }}</div>
            <div class="text-sm text-emerald-600 dark:text-emerald-400 mt-1">kcal</div>
        </div>
    </div>
</div>
@endsection
