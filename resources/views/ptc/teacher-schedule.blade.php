@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Jadwal Agenda Konsultasi Saya (Guru / Wali Kelas)</h1>
            <p class="text-xs text-gray-500 mt-1">Timelinejanji temu konsultasi dengan Orang Tua murid</p>
        </div>
        <a href="{{ route('growth.ptc.sessions') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Jam Waktu</th>
                    <th class="py-3 px-4">Nama Orang Tua / Wali</th>
                    <th class="py-3 px-4">Nama Siswa</th>
                    <th class="py-3 px-4">Topik Diskusi</th>
                    <th class="py-3 px-4">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($slots as $sl)
                <tr>
                    <td class="py-3 px-4 font-mono font-bold text-indigo-600">{{ substr($sl->start_time, 0, 5) }} - {{ substr($sl->end_time, 0, 5) }} WIB</td>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $sl->parent->name ?? 'Parent' }}</td>
                    <td class="py-3 px-4 font-semibold text-gray-700 dark:text-gray-300">{{ $sl->student->name ?? '-' }}</td>
                    <td class="py-3 px-4 text-xs font-medium">{{ $sl->topic ?? 'Konsultasi Perkembangan Belajar Siswa' }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-indigo-100 text-indigo-700">
                            {{ $sl->status }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada janji temu konsultasi yang dibooking oleh orang tua.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
