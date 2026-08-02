@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Transportation & Bus Routing System
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manajemen Armada Rute Bus Antar-Jemput Siswa SMA ISA 1 Semarang</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Daftar Rute Bus Antar-Jemput</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Nama Rute</th>
                    <th class="py-3 px-4">Armada Bus & Plat</th>
                    <th class="py-3 px-4">Pengemudi (Driver)</th>
                    <th class="py-3 px-4">Jam Keberangkatan</th>
                    <th class="py-3 px-4 text-right">Jumlah Penumpang</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($routes as $rt)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $rt->route_name }}</td>
                    <td class="py-3 px-4 font-mono text-xs font-bold text-indigo-600">{{ $rt->bus->plate_number ?? '-' }}</td>
                    <td class="py-3 px-4 text-xs font-semibold">{{ $rt->bus->driver_name ?? '-' }} ({{ $rt->bus->driver_phone ?? '-' }})</td>
                    <td class="py-3 px-4 text-xs font-mono">{{ $rt->departure_time }} - {{ $rt->arrival_time }}</td>
                    <td class="py-3 px-4 text-right font-bold text-gray-900 dark:text-white">{{ $rt->passengers->count() }} Siswa</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400">Belum ada rute bus yang dijadwalkan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
