@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
            Progres IEP
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang - Pemantauan Progres</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <p class="text-gray-600 dark:text-gray-300">Memantau progres siswa: {{ $iep->student->name ?? 'Siswa' }}</p>
        <div class="mt-4">
            <h3 class="font-bold text-gray-900 dark:text-white">Tujuan Tahunan:</h3>
            <ul class="list-disc pl-5 text-sm text-gray-600 dark:text-gray-300 mt-2">
                @foreach($iep->annual_goals ?? [] as $goal)
                    <li>{{ $goal['description'] ?? 'Tujuan' }} - Progres: {{ $goal['progress'] ?? '0%' }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
