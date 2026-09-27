@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
            Profil Akomodasi Siswa
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ school_name() }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Profil: {{ $student->name ?? 'Siswa' }}</h2>
        <ul class="list-disc pl-5 text-sm text-gray-600 dark:text-gray-300">
            @forelse($accommodations ?? [] as $acc)
                <li>{{ $acc->accommodation_category }}: {{ $acc->specific_need }}</li>
            @empty
                <li>Belum ada akomodasi untuk siswa ini.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
