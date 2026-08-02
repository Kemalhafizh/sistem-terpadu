@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                Digital Twin School Infrastructure Simulation Engine
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Simulasi Virtual Infrastruktur & Beban Kapasitas Sekolah SMA ISA 1 Semarang</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Skenario Simulasi Digital Twin</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Skenario</th>
                    <th class="py-3 px-4">Parameter Masukan</th>
                    <th class="py-3 px-4">Prediksi Dampak / Hasil</th>
                    <th class="py-3 px-4 text-right">Dibuat Oleh</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($simulations as $sim)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $sim->scenario_name }}</td>
                    <td class="py-3 px-4 text-xs font-mono">{{ $sim->parameters }}</td>
                    <td class="py-3 px-4 text-xs text-emerald-600 font-semibold">{{ json_encode($sim->predicted_outcomes) }}</td>
                    <td class="py-3 px-4 text-right text-xs font-semibold">{{ $sim->creator->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada simulasi digital twin yang dijalankan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
