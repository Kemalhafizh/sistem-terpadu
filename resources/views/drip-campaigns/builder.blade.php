@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
            Pembuat Kampanye Drip - SMA Islam Sultan Agung 1 Semarang
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2 font-bold">{{ $campaign->campaign_name ?? 'Kampanye Baru' }}</p>
    </div>
    
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
        <p class="text-sm text-gray-600 dark:text-gray-300">Alur Kampanye akan ditampilkan di sini.</p>
    </div>
</div>
@endsection
