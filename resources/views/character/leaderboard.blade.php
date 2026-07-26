@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Papan Peringkat Merit Points Karakter Positif</h1>
            <p class="text-xs text-gray-500 mt-1">Top 10 Siswa Berprestasi Karakter P5 & Akhlak Mahmudah</p>
        </div>
        <a href="{{ route('growth.character.dimensions') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Peringkat</th>
                    <th class="py-3 px-4">Nama Siswa</th>
                    <th class="py-3 px-4">Total Merit Points</th>
                    <th class="py-3 px-4 text-right">Detail Rapor Karakter</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($topMerits as $idx => $m)
                <tr>
                    <td class="py-3 px-4 font-black text-amber-500">#{{ $idx + 1 }}</td>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $m->student->name }}</td>
                    <td class="py-3 px-4 font-black text-emerald-600">+{{ $m->total_points }} Poin</td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('growth.character.student_report', $m->student_id) }}" class="text-xs font-bold text-amber-600 hover:underline">Lihat Radar Karakter &rarr;</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data merit points.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
