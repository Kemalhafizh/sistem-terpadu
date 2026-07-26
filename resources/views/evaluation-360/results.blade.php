@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Hasil Konsolidasi Evaluasi 360°: {{ $teacher->name }}</h1>
            <p class="text-xs text-gray-500 mt-1">Skor Overall Performance Kinerja Guru: <strong class="text-sky-600 font-black text-base">{{ $summary->overall_score ?? 4.2 }} / 5.0</strong></p>
        </div>
        <a href="{{ route('hc.evaluation.cycles') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <!-- 5 Perspectives Grid -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-3">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Supervisor</span>
            <p class="text-xl font-black text-indigo-600">{{ $summary->supervisor_avg ?? 4.2 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Peer (Rekan)</span>
            <p class="text-xl font-black text-teal-600">{{ $summary->peer_avg ?? 4.1 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Siswa (EDOM)</span>
            <p class="text-xl font-black text-sky-600">{{ $summary->student_avg ?? 4.5 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Orang Tua</span>
            <p class="text-xl font-black text-amber-500">{{ $summary->parent_avg ?? 4.3 }}</p>
        </div>
        <div class="bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-100 text-center space-y-1">
            <span class="text-[10px] font-bold text-gray-400 uppercase">Self</span>
            <p class="text-xl font-black text-rose-500">{{ $summary->self_score ?? 4.0 }}</p>
        </div>
    </div>

    <!-- Individual Development Plan Card -->
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm space-y-4">
        <h3 class="text-base font-black text-gray-900 dark:text-white uppercase tracking-wider">Individual Development Plan (Rekomendasi Pengembangan)</h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 rounded-2xl border border-emerald-100 space-y-1">
                <h4 class="text-xs font-bold text-emerald-800 uppercase">Kekuatan Utama (Strengths)</h4>
                <ul class="text-xs text-emerald-700 font-semibold list-disc list-inside">
                    @foreach($summary->strengths ?? ['Penguasaan materi pedagogik unggul'] as $str)
                    <li>{{ $str }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="p-4 bg-amber-50 dark:bg-amber-950/20 rounded-2xl border border-amber-100 space-y-1">
                <h4 class="text-xs font-bold text-amber-800 uppercase">Area Pengembangan (Improvements)</h4>
                <ul class="text-xs text-amber-700 font-semibold list-disc list-inside">
                    @foreach($summary->improvements ?? ['Pelatihan pembuatan modul interaktif SCORM'] as $imp)
                    <li>{{ $imp }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
