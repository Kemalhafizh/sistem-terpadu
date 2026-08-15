@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            Dasbor Pengunjung SMA Islam Sultan Agung 1 Semarang
        </h1>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Manajemen log kunjungan kampus</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700 overflow-hidden">
        <div class="p-6 border-b border-gray-150 dark:border-gray-700">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pengunjung Aktif</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-900/50">
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Nama</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Tujuan</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Nomor Badge</th>
                        <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase dark:text-gray-400">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-gray-700 text-sm text-gray-600 dark:text-gray-300">
                    @forelse($activeVisitors ?? [] as $visitor)
                        <tr>
                            <td class="px-6 py-4">{{ $visitor->visitor_name }}</td>
                            <td class="px-6 py-4">{{ ucfirst($visitor->purpose) }}</td>
                            <td class="px-6 py-4 font-mono">{{ $visitor->badge_number }}</td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400">
                                    {{ $visitor->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center">Tidak ada pengunjung aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
