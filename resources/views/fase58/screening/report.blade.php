@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Laporan Skrining
        </h1>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">SMA Islam Sultan Agung 1 Semarang</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Hasil Skrining: {{ $result->student->name ?? 'Siswa' }}</h2>
        <p class="text-sm text-gray-600 dark:text-gray-300">Skor Total: {{ $result->total_score }}</p>
        <p class="text-sm text-gray-600 dark:text-gray-300 mt-2">Rekomendasi Sistem: {{ $result->system_recommendation }}</p>
        @if($result->requires_professional_referral)
            <div class="mt-4 p-4 bg-rose-50 border border-rose-200 rounded-md">
                <p class="text-rose-700 text-sm font-bold">Memerlukan rujukan ke profesional/spesialis segera.</p>
            </div>
        @endif
    </div>
</div>
@endsection
