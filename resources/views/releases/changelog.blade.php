@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Semantic Changelog Rilis v{{ $release->version }}</h1>
            <p class="text-xs text-gray-500 mt-1">Dirilis oleh: {{ $release->released_by }} | Tanggal: {{ $release->released_at ? $release->released_at->format('d M Y H:i') : now()->format('d M Y H:i') }}</p>
        </div>
        <a href="{{ route('deploy.releases.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 space-y-4">
        <h3 class="text-base font-black text-gray-900 dark:text-white">Ringkasan Perubahan Versi:</h3>
        <p class="text-sm text-gray-700 dark:text-gray-300 font-medium">{{ $release->changelog_summary }}</p>
    </div>
</div>
@endsection
