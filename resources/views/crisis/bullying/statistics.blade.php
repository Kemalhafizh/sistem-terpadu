@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-150 dark:border-gray-700 shadow-sm">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white mb-6">Statistik Perundungan</h1>
        <div class="text-gray-600 dark:text-gray-300">
            <ul>
                @foreach($stats as $type => $count)
                <li>{{ Str::headline($type) }}: {{ $count }} Kasus</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection