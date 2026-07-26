@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Matriks Heatmap Ketercapaian Kompetensi Kelas</h1>
            <p class="text-xs text-gray-500 mt-1">Mata Pelajaran: <strong class="text-teal-600">{{ $subject->name }}</strong> (Total TP: {{ $heatmap['total_objectives'] }})</p>
        </div>
        <a href="{{ route('academic.competency.outcomes') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Kode CP</th>
                    <th class="py-3 px-4">Elemen</th>
                    <th class="py-3 px-4">Jumlah TP</th>
                    <th class="py-3 px-4">Status Penguasaan Ketercapaian</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($heatmap['outcomes'] as $oc)
                <tr>
                    <td class="py-3 px-4 font-mono font-bold text-teal-600">{{ $oc->cp_code }}</td>
                    <td class="py-3 px-4 font-semibold">{{ $oc->element }}</td>
                    <td class="py-3 px-4 font-bold">{{ $oc->objectives->count() }} TP</td>
                    <td class="py-3 px-4">
                        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-xs font-black">
                            Mastered (Tercapai)
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada data CP untuk mata pelajaran ini.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
