@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Automated Data Retention & PDP Purge Engine
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kebijakan Retensi Data & Penghapusan Otomatis UU PDP No. 27/2022</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Kebijakan Retensi Data Aktif</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Kategori Data</th>
                    <th class="py-3 px-4">Batas Masa Simpan</th>
                    <th class="py-3 px-4">Tindakan Retensi</th>
                    <th class="py-3 px-4">Status Kebijakan</th>
                    <th class="py-3 px-4 text-right">Penghapusan Terakhir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($policies as $pol)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $pol->data_category }}</td>
                    <td class="py-3 px-4 font-mono text-xs font-bold text-indigo-600">{{ $pol->retention_months }} Bulan</td>
                    <td class="py-3 px-4 uppercase text-xs font-semibold text-rose-600">{{ $pol->action }}</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            AKTIF
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right text-xs font-mono">{{ $pol->purgeLogs->first()->executed_at->format('d M Y') ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada kebijakan retensi data.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
