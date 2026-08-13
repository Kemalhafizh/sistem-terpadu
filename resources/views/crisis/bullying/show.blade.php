@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Detail Kasus: {{ substr($report->ticket_number, 0, 8) }}</h1>
        <div class="text-gray-600 dark:text-gray-300">
            <p><strong>Deskripsi:</strong> {{ $report->description }}</p>
            <p><strong>Status:</strong> {{ Str::headline($report->status) }}</p>
        </div>
    </div>
</div>
@endsection