<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    Monitoring Riwayat Ekspor & Interoperability Log
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Log pemantauan riwayat ekspor batch dataset institusi.</p>
            </div>
            <a href="{{ route('exports.index') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Ekspor Baru
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- History Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Arsip Log Ekspor Data</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Monitoring status penyelesaian dan ukuran berkas terarsip.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">ID Job</th>
                                <th class="px-6 py-4">Nama Pekerjaan</th>
                                <th class="px-6 py-4">Format</th>
                                <th class="px-6 py-4">Ukuran Berkas</th>
                                <th class="px-6 py-4">Waktu Selesai</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($history as $job)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">#JOB-{{ $job->id }}</td>
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $job->job_name }}</td>
                                    <td class="px-6 py-4 font-mono font-bold uppercase text-indigo-600 dark:text-indigo-400">{{ $job->format }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px]">{{ $job->formatted_size }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px]">{{ optional($job->completed_at)->format('d M Y H:i:s') ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right">
                                        @if($job->status === 'completed')
                                            <a href="{{ route('exports.download', $job->id) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                                Unduh
                                            </a>
                                        @else
                                            <span class="text-gray-400 text-[11px]">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada log ekspor data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
