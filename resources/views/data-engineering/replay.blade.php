@extends('layouts.app')
@section('content')
<div class="container mx-auto p-6">
    <div class="flex items-center space-x-3 mb-6">
        <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        <h1 class="text-2xl font-black text-gray-900 dark:text-white">{{ school_name() }} - Event Replay</h1>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-sm text-gray-600 dark:text-gray-300">Pemutaran ulang peristiwa masa lalu.</p>
    </div>
</div>
@endsection