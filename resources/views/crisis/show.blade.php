@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-4">{{ $incident->title }}</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-4">{{ $incident->description }}</p>
        
        <div class="mt-8 border-t border-gray-200 dark:border-gray-700 pt-6">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Log Respon</h3>
            <div class="space-y-4">
                @foreach($timeline as $log)
                <div class="flex gap-4 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <div class="text-sm text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white">{{ Str::headline($log->action_type) }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-300">{{ $log->description }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection