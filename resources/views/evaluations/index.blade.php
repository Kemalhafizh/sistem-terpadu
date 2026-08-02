@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Student Feedback & Course Evaluation System
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Evaluasi Akhir Semester Metode Pengajaran Guru & Materi Pelajaran (Anonim)</p>
        </div>
    </div>

    @if(session('success'))
    <div class="p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-2xl text-sm font-semibold">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Evaluasi Pembelajaran Aktif</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Mata Pelajaran</th>
                    <th class="py-3 px-4">Guru Pengampu</th>
                    <th class="py-3 px-4">Tahun Akademik</th>
                    <th class="py-3 px-4 text-right">Isi Evaluasi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($evaluations as $ev)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $ev->subject->name ?? 'Mata Pelajaran' }}</td>
                    <td class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">{{ $ev->teacher->name ?? 'Guru' }}</td>
                    <td class="py-3 px-4 font-mono text-xs">{{ $ev->academic_year }} (Semester {{ $ev->semester }})</td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('evaluations.fill', $ev->id) }}" class="px-3 py-1 bg-pink-600 hover:bg-pink-700 text-white rounded-lg text-xs font-bold">Isi Kuesioner &rarr;</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada evaluasi pembelajaran aktif saat ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
