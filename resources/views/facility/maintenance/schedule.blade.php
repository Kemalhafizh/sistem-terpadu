@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>
                Asset Maintenance & Service Scheduler
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Jadwal Pemeliharaan Preventif Sarana Prasarana & Inventaris Sekolah</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Inventaris Aset Sarpras & Status Pemeliharaan</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Tag Aset</th>
                    <th class="py-3 px-4">Nama Barang / Aset</th>
                    <th class="py-3 px-4">Kategori & Lokasi</th>
                    <th class="py-3 px-4">Kondisi Fisik</th>
                    <th class="py-3 px-4 text-right">Riwayat Service</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($assets as $ast)
                <tr>
                    <td class="py-3 px-4 font-mono font-bold text-xs text-amber-600">{{ $ast->asset_tag }}</td>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $ast->name }}</td>
                    <td class="py-3 px-4 text-xs">{{ $ast->category }} ({{ $ast->location }})</td>
                    <td class="py-3 px-4">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            {{ strtoupper($ast->condition) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-right font-bold text-xs">{{ $ast->maintenanceLogs->count() }} Kali Service</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada aset sarpras yang terdaftar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
