@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.684a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                Student Mental Health & Emotional Wellbeing Screening
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Platform Skrining Kesehatan Emosional Terukur (SRQ-20 & PHQ-9) SMA ISA 1 Semarang</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('growth.wellbeing.mood_journal') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow-md">
                Private Mood Journal
            </a>
            <a href="{{ route('growth.wellbeing.results') }}" class="px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">
                Counselor Heatmap BK
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <!-- Screenings Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @forelse($screenings as $sc)
        <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4 flex flex-col justify-between">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-rose-100 text-rose-700">
                        {{ strtoupper($sc->screening_type) }}
                    </span>
                    <span class="text-xs font-bold text-gray-400">{{ $sc->academic_year }}</span>
                </div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $sc->title }}</h3>
                <p class="text-xs text-gray-500">Target: <strong class="text-gray-700 dark:text-gray-300">{{ $sc->target_grade ?? 'Semua Kelas' }}</strong></p>
                <div class="border-t pt-3 border-gray-100 dark:border-gray-700 text-xs font-semibold text-gray-500">
                    Total Responden: <strong class="text-rose-600 text-sm font-bold">{{ $sc->responses_count }} Siswa</strong>
                </div>
            </div>

            <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <a href="{{ route('growth.wellbeing.fill', $sc->id) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow">Isi Skrining &rarr;</a>
            </div>
        </div>
        @empty
        <div class="col-span-3 py-12 text-center text-gray-400">Belum ada instrumen skrining kesehatan mental yang aktif.</div>
        @endforelse
    </div>
</div>
@endsection
