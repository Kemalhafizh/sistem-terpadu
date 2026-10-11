@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Hasil Konsolidasi Evaluasi 360°: {{ $teacher->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Skor Overall Performance Kinerja Guru: <strong class="text-sky-600 font-black text-base">{{ $summary?->overall_score === null ? '–' : $summary->overall_score.' / 5.0' }}</strong></p>
        </div>
        <a href="{{ route('hc.evaluation.cycles') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <!-- 5 Perspectives Grid -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Supervisor</span>
            <p class="text-xl font-black text-indigo-600">{{ $summary?->supervisor_avg ?? '–' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Peer (Rekan)</span>
            <p class="text-xl font-black text-teal-600">{{ $summary?->peer_avg ?? '–' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Siswa (EDOM)</span>
            <p class="text-xl font-black text-sky-600">{{ $summary?->student_avg ?? '–' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Orang Tua</span>
            <p class="text-xl font-black text-amber-500">{{ $summary?->parent_avg ?? '–' }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Self</span>
            <p class="text-xl font-black text-rose-500">{{ $summary?->self_score ?? '–' }}</p>
        </div>
    </div>

    <!-- What raters wrote (no generated strengths/improvements: the same two sentences were stored for every teacher) -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-base font-black text-gray-900 dark:text-white uppercase tracking-wider">{{ __('Komentar Penilai') }}</h3>
        @forelse($comments as $comment)
            <p class="text-sm text-gray-700 dark:text-gray-300 border-l-4 border-sky-200 pl-3">{{ $comment }}</p>
        @empty
            <p class="text-sm text-gray-400">{{ __('Belum ada komentar dari penilai.') }}</p>
        @endforelse
    </div>
</div>
@endsection
