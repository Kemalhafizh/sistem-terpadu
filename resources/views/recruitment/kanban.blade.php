@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                Kanban Rekrutmen
            </h1>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ school_name() }}</p>
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Columns -->
        <div class="bg-gray-50 dark:bg-gray-800 rounded-3xl p-4 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h2 class="font-bold text-gray-700 dark:text-gray-200 mb-4">Pelamar Baru</h2>
            <!-- Cards -->
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-3xl p-4 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h2 class="font-bold text-gray-700 dark:text-gray-200 mb-4">Wawancara</h2>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-3xl p-4 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h2 class="font-bold text-gray-700 dark:text-gray-200 mb-4">Micro Teaching</h2>
        </div>
        <div class="bg-gray-50 dark:bg-gray-800 rounded-3xl p-4 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h2 class="font-bold text-gray-700 dark:text-gray-200 mb-4">Penawaran</h2>
        </div>
    </div>
</div>
@endsection