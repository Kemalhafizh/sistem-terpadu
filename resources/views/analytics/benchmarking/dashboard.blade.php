@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Academic Benchmarking & National Comparison Engine
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Perbandingan Pencapaian Nilai Ujian Internal SMA ISA 1 Semarang vs Rata-Rata Nasional</p>
        </div>
        <a href="{{ route('analytics.benchmarking.import') }}" class="px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-2xl font-bold text-xs shadow-md">+ Upload Dataset Statistik Nasional</a>
    </div>

    <!-- Benchmarks Table -->
    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Metrik Perbandingan Daya Saing Sekolah</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Mata Pelajaran</th>
                    <th class="py-3 px-4">Jenis Ujian</th>
                    <th class="py-3 px-4">Tahun</th>
                    <th class="py-3 px-4">Rata-Rata Sekolah</th>
                    <th class="py-3 px-4">Rata-Rata Nasional</th>
                    <th class="py-3 px-4">Percentile Rank</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($benchmarks as $bm)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $bm->subject->name ?? 'Matematika' }}</td>
                    <td class="py-3 px-4 font-semibold uppercase text-xs">{{ $bm->exam_type }}</td>
                    <td class="py-3 px-4 font-mono font-bold">{{ $bm->year }}</td>
                    <td class="py-3 px-4 font-black text-purple-600">{{ $bm->school_average }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-gray-500">{{ $bm->national_average ?? 'N/A' }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-purple-100 text-purple-700">
                            {{ $bm->percentile_rank ?? 50 }}th Percentile
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-gray-400">Belum ada data perbandingan benchmark nasional.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
