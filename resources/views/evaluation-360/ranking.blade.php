@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Peringkat Evaluasi Kinerja Guru (Teacher Leaderboard)</h1>
            <p class="text-xs text-gray-500 mt-1">Ranking Kinerja Guru Teratas Berdasarkan Konsolidasi 360° & EDOM</p>
        </div>
        <a href="{{ route('hc.evaluation.cycles') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Peringkat</th>
                    <th class="py-3 px-4">Nama Guru</th>
                    <th class="py-3 px-4">Skor Overall</th>
                    <th class="py-3 px-4">Siswa (EDOM)</th>
                    <th class="py-3 px-4 text-right">Rincian Spider Radar</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($rankings as $idx => $rk)
                <tr>
                    <td class="py-3 px-4 font-black text-sky-600">#{{ $idx + 1 }}</td>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $rk->evaluatee->name }}</td>
                    <td class="py-3 px-4 font-black text-emerald-600">{{ $rk->overall_score }} / 5.0</td>
                    <td class="py-3 px-4 font-semibold text-indigo-600">{{ $rk->student_avg ?? '–' }}</td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('hc.evaluation.results', $rk->evaluatee_id) }}" class="text-xs font-bold text-sky-600 hover:underline">Lihat Rincian &rarr;</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada data peringkat evaluasi guru.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
