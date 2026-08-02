@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                Drag-and-Drop Executive Dashboard Builder
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kustomisasi Tata Letak Widget Dashboard Eksekutif SMA ISA 1 Semarang</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Dashboard Kustom Tersimpan</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Judul Dashboard</th>
                    <th class="py-3 px-4">Deskripsi</th>
                    <th class="py-3 px-4">Jumlah Widget</th>
                    <th class="py-3 px-4 text-right">Default</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($dashboards as $db)
                <tr>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $db->title }}</td>
                    <td class="py-3 px-4 text-xs">{{ $db->description ?? '-' }}</td>
                    <td class="py-3 px-4 font-mono font-bold text-indigo-600">{{ $db->widgets->count() }} Widget</td>
                    <td class="py-3 px-4 text-right">
                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg uppercase bg-emerald-100 text-emerald-700">
                            {{ $db->is_default ? 'DEFAULT' : 'OPSIONAL' }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-400">Belum ada dashboard kustom yang dibuat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
