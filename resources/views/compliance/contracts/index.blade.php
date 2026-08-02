@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Legal Contract & Vendor Compliance Manager
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manajemen Kontrak Legal Vendor, Perpanjangan MOU & Kepatuhan Legalitas</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Daftar Kontrak Vendor & Lisensi Software</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">No. Kontrak</th>
                    <th class="py-3 px-4">Judul Perjanjian</th>
                    <th class="py-3 px-4">Nama Vendor / Penyedia</th>
                    <th class="py-3 px-4">Nilai Kontrak</th>
                    <th class="py-3 px-4">Masa Berlaku</th>
                    <th class="py-3 px-4 text-right">Status Legal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($contracts as $ct)
                <tr>
                    <td class="py-3 px-4 font-mono font-bold text-xs text-indigo-600">{{ $ct->contract_number }}</td>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $ct->title }}</td>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $ct->vendor_name }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-emerald-600">Rp {{ number_format($ct->contract_value, 0, ',', '.') }}</td>
                    <td class="py-3 px-4 text-xs font-mono">{{ $ct->start_date->format('d/m/Y') }} - {{ $ct->end_date->format('d/m/Y') }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            {{ strtoupper($ct->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-6 text-center text-gray-400">Belum ada dokumen kontrak legal vendor.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
