@php
    $entityLabels = \App\Services\Content\ExportEngineService::ENTITIES;
    $formatLabels = \App\Services\Content\ExportEngineService::FORMATS;
    $statusStyles = [
        'completed' => ['Siap diunduh', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300'],
        'failed' => ['Gagal', 'bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300'],
        'processing' => ['Diproses', 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300'],
        'pending' => ['Menunggu', 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'],
    ];
@endphp
<div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="p-6 border-b border-gray-100 dark:border-gray-700">
        <h3 class="text-base font-black text-gray-900 dark:text-white">{{ $title }}</h3>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-500 dark:text-gray-400 uppercase text-xs tracking-wider border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3">Nama berkas</th>
                    <th scope="col" class="px-6 py-3">Data</th>
                    <th scope="col" class="px-6 py-3">Format</th>
                    <th scope="col" class="px-6 py-3">Status</th>
                    <th scope="col" class="px-6 py-3">Ukuran</th>
                    <th scope="col" class="px-6 py-3">Dibuat</th>
                    <th scope="col" class="px-6 py-3 text-right"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-gray-700 dark:text-gray-200">
                @forelse ($jobs as $job)
                    @php([$statusLabel, $statusClass] = $statusStyles[$job->status] ?? [$job->status, $statusStyles['pending'][1]])
                    <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-900/40 transition-colors align-top">
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-900 dark:text-white">{{ $job->job_name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">oleh {{ optional($job->user)->name ?? '—' }}</p>
                        </td>
                        <td class="px-6 py-4">{{ $entityLabels[$job->entity_type] ?? $job->entity_type }}</td>
                        <td class="px-6 py-4 font-semibold whitespace-nowrap">{{ $formatLabels[$job->format]['label'] ?? strtoupper($job->format) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-block whitespace-nowrap px-2.5 py-1 text-xs font-bold rounded-full {{ $statusClass }}">{{ $statusLabel }}</span>
                            @if ($job->status === 'failed' && $job->error_message)
                                <p class="text-xs text-rose-600 dark:text-rose-400 mt-1 max-w-xs break-words">{{ \Illuminate\Support\Str::limit($job->error_message, 140) }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 tabular-nums whitespace-nowrap text-gray-600 dark:text-gray-300">{{ $job->status === 'completed' ? $job->formatted_size : '—' }}</td>
                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ $job->created_at?->locale('id')->translatedFormat('d M Y, H:i') }}</td>
                        <td class="px-6 py-4 text-right">
                            @if ($job->status === 'completed')
                                <a href="{{ route('exports.download', $job->id) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm inline-flex items-center gap-1">
                                    Unduh
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Belum ada ekspor. Berkas yang Anda buat akan muncul di sini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if (method_exists($jobs, 'links'))
        <div class="px-6 py-4">{{ $jobs->links() }}</div>
    @endif
</div>
