@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Laporan Eksekutif Succession Readiness Matrix</h1>
            <p class="text-xs text-gray-500 mt-1">Matriks kesiapan calon pimpinan sekolah SMA ISA 1 Semarang</p>
        </div>
        <a href="{{ route('hc.succession.positions') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Kandidat Pimpinan</th>
                    <th class="py-3 px-4">Target Posisi</th>
                    <th class="py-3 px-4">Status Readiness</th>
                    <th class="py-3 px-4">Rencana Pengembangan (IDP)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($candidates as $c)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $c->candidate->name }}</td>
                    <td class="py-3 px-4 font-semibold text-amber-600 uppercase text-xs">{{ str_replace('_', ' ', $c->position->title) }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            {{ str_replace('_', ' ', $c->readiness_level) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-xs font-medium">{{ $c->development_plan ?? 'Diklat Manajerial Eksekutif' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada kandidat suksesi dalam laporan pipeline.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
