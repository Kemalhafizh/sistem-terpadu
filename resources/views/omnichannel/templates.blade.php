@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
            Template Pesan - {{ school_name() }}
        </h1>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($templates ?? [] as $template)
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $template->template_code }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">{{ $template->subject }}</p>
        </div>
        @empty
        <div class="col-span-2 bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm text-center">
            <p class="text-sm text-gray-600 dark:text-gray-300">Belum ada template.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
