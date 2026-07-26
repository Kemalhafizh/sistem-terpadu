@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex justify-between items-center">
        <div>
            <h1 class="text-xl font-black text-gray-900 dark:text-white">Graduation Readiness Self-Checker Siswa</h1>
            <p class="text-xs text-gray-500 mt-1">Status Kesiapan Kelulusan Mandiri: <strong class="text-emerald-600 font-bold text-sm">{{ $readiness['readiness_score'] }}%</strong></p>
        </div>
        <a href="{{ route('academic.pathways.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs">Kembali</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Persyaratan Kelulusan</th>
                    <th class="py-3 px-4">Target Minimum</th>
                    <th class="py-3 px-4">Status Fulfillment</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($readiness['checklist'] as $chk)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $chk['description'] }}</td>
                    <td class="py-3 px-4 font-semibold text-indigo-600">{{ $chk['threshold'] }}</td>
                    <td class="py-3 px-4">
                        @if($chk['is_fulfilled'])
                        <span class="px-2.5 py-1 bg-emerald-100 text-emerald-700 rounded-lg text-xs font-bold uppercase">Lolos / Terpenuhi</span>
                        @else
                        <span class="px-2.5 py-1 bg-amber-100 text-amber-700 rounded-lg text-xs font-bold uppercase">Belum Lolos</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
