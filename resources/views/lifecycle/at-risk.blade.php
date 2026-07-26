@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Daftar Siswa Berisiko (At-Risk Early Warning System)</h1>
            <p class="text-xs text-gray-500 mt-1">Siswa dengan indikator risiko mental/akademis yang memerlukan intervensi konseling BK</p>
        </div>
        <a href="{{ route('growth.lifecycle.retention') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Siswa</th>
                    <th class="py-3 px-4">Role</th>
                    <th class="py-3 px-4">Status Akun</th>
                    <th class="py-3 px-4 text-right">Aksi Intervensi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($students as $st)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $st->name }}</td>
                    <td class="py-3 px-4 font-mono text-xs uppercase">{{ $st->role }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-rose-100 text-rose-700">
                            At-Risk Flagged
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right">
                        <a href="{{ route('growth.lifecycle.timeline', $st->id) }}" class="text-xs font-bold text-indigo-600 hover:underline">Lifecycle Timeline &rarr;</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Tidak ada siswa berisiko tinggi saat ini. Semua siswa dalam kondisi terkendali.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
