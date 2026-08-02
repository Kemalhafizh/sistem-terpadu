@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Digital Forensics & Compliance Audit Console
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Konsol Penyelidikan Keamanan System Log & Jejak Aktivitas Forensik</p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700 shadow-sm p-6 overflow-x-auto">
        <h3 class="text-base font-black text-gray-900 dark:text-white mb-4">Log Event Forensik Real-time</h3>
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50 dark:bg-gray-900 text-xs font-bold text-gray-400 uppercase">
                <tr>
                    <th class="py-3 px-4">Timestamp</th>
                    <th class="py-3 px-4">Tipe Event</th>
                    <th class="py-3 px-4">User</th>
                    <th class="py-3 px-4">IP Address</th>
                    <th class="py-3 px-4 text-right">Tingkat Bahaya</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700 font-mono text-xs">
                @forelse($events as $ev)
                <tr>
                    <td class="py-3 px-4 text-gray-400">{{ $ev->occurred_at->format('Y-m-d H:i:s') }}</td>
                    <td class="py-3 px-4 font-bold text-gray-900 dark:text-white">{{ $ev->event_type }}</td>
                    <td class="py-3 px-4 text-xs font-sans">{{ $ev->user->name ?? 'System' }}</td>
                    <td class="py-3 px-4 text-indigo-600">{{ $ev->ip_address }}</td>
                    <td class="py-3 px-4 text-right">
                        <span class="px-2 py-0.5 text-xs font-bold rounded uppercase bg-amber-100 text-amber-800">
                            {{ strtoupper($ev->severity) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-6 text-center text-gray-400 font-sans">Belum ada event forensik tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
