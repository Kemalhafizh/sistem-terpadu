@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            Manajemen Acara Sekolah
        </h1>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 overflow-hidden mb-8">
        <div class="p-6 border-b border-gray-150 dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Daftar Acara</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Nama Acara</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Waktu</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Lokasi</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Kapasitas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                    @forelse($events ?? [] as $event)
                        <tr>
                            <td class="px-6 py-4 font-bold">{{ $event->event_name }}</td>
                            <td class="px-6 py-4">{{ \Carbon\Carbon::parse($event->event_datetime)->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4">{{ $event->venue }}</td>
                            <td class="px-6 py-4">{{ $event->max_capacity }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center">Belum ada acara.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
