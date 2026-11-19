<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-extrabold text-2xl text-slate-900 dark:text-white tracking-tight leading-tight">
                Log Riwayat Audit Integrasi
            </h2>
            <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ showDetailModal: false, activeLogErrors: [] }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-start border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                <th class="pb-3 text-start">Provider</th>
                                <th class="pb-3 text-start">Aksi</th>
                                <th class="pb-3 text-start">Waktu Sinkron</th>
                                <th class="pb-3 text-center">Rekaman Sukses</th>
                                <th class="pb-3 text-center">Rekaman Gagal</th>
                                <th class="pb-3 text-start">Status</th>
                                <th class="pb-3 text-start">Ringkasan</th>
                                <th class="pb-3 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                            @forelse($logs as $log)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                    <td class="py-4 font-semibold text-slate-900 dark:text-slate-100">{{ $log->config->name }}</td>
                                    <td class="py-4 text-xs font-bold">
                                        <span class="px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200">
                                            {{ strtoupper($log->direction) }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-xs text-slate-600 dark:text-slate-300">
                                        {{ $log->created_at->format('d M Y, H:i:s') }}
                                        <span class="block text-[10px] text-slate-400 dark:text-slate-500">{{ $log->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="py-4 text-center font-bold text-slate-700 dark:text-slate-200">{{ $log->records_processed }}</td>
                                    <td class="py-4 text-center font-bold text-rose-600 dark:text-rose-400">
                                        @if($log->records_failed > 0)
                                            <span class="px-2 py-0.5 rounded-lg bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                                {{ $log->records_failed }}
                                            </span>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        @if($log->status === 'success')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300">
                                                Sukses
                                            </span>
                                        @elseif($log->status === 'partial')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-800/50 text-amber-700 dark:text-amber-300">
                                                Sebagian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300">
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-xs text-slate-600 dark:text-slate-300 max-w-xs truncate" title="{{ $log->summary }}">
                                        {{ $log->summary }}
                                    </td>
                                    <td class="py-4 text-end">
                                        @if(!empty($log->error_details))
                                            <button type="button"
                                                data-errors="{{ json_encode($log->error_details) }}"
                                                @click="activeLogErrors = JSON.parse($el.dataset.errors || '[]'); showDetailModal = true;"
                                                class="px-3.5 py-1.5 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 border border-slate-200 dark:border-slate-600 text-xs font-semibold rounded-xl text-slate-700 dark:text-slate-200 transition-colors">
                                                Detail Error
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400 dark:text-slate-500 font-semibold italic">Bersih</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                                        Belum ada rekaman log audit integrasi di platform SISTA.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination Links -->
                <div class="mt-6">
                    {{ $logs->links() }}
                </div>
            </div>

            <!-- Error Details Modal -->
            <div x-show="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 backdrop-blur-sm" style="display: none;" x-transition>
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 max-w-lg w-full space-y-5 shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-3">
                        <h3 class="text-lg font-bold text-slate-900 dark:text-white flex items-center gap-2">
                            <span>Riwayat Peringatan / Masalah Validasi</span>
                        </h3>
                        <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>

                    <div class="max-h-[350px] overflow-y-auto space-y-3 pe-2 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                        <template x-for="(err, idx) in activeLogErrors" :key="idx">
                            <div class="p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl">
                                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-1 mb-2">
                                    <span class="font-bold text-emerald-700 dark:text-emerald-400" x-text="err.identifier || 'Ekspor Error'"></span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200" x-text="'Baris ' + (err.row || 'N/A')"></span>
                                </div>
                                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-600 dark:text-rose-400">
                                    <template x-for="(errMsg, errIdx) in (err.errors || [err.message || 'Error tidak dikenal'])" :key="errIdx">
                                        <li x-text="errMsg"></li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="showDetailModal = false" class="px-5 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 font-semibold text-sm rounded-xl text-slate-800 dark:text-slate-100 transition-colors">Tutup</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
