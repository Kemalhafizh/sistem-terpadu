<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-2xl text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300 tracking-tight leading-tight">
                 Log Riwayat Audit Integrasi
            </h2>
            <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-800 rounded-xl text-sm font-semibold text-gray-300 bg-gray-900 hover:bg-gray-800 hover:text-white transition-all duration-200">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-950 text-gray-100 min-h-screen" x-data="{ showDetailModal: false, activeLogErrors: [] }">
        <div class="w-full sm:px-6 lg:px-8 space-y-8">

            <div class="backdrop-blur-xl bg-gray-900/40 border border-gray-800/80 rounded-3xl p-6 shadow-xl">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-800 text-xs font-bold uppercase tracking-wider text-gray-400">
                                <th class="pb-3">Provider</th>
                                <th class="pb-3">Aksi</th>
                                <th class="pb-3">Waktu Sinkron</th>
                                <th class="pb-3 text-center">Rekaman Sukses</th>
                                <th class="pb-3 text-center">Rekaman Gagal</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3">Ringkasan</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-800/40 text-sm">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-800/10 transition-colors">
                                    <td class="py-4 font-semibold text-gray-200">{{ $log->config->name }}</td>
                                    <td class="py-4 text-xs font-bold">
                                        <span class="px-2.5 py-0.5 rounded-full bg-gray-800 border border-gray-700 text-gray-300">
                                            {{ strtoupper($log->direction) }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-xs text-gray-400">
                                        {{ $log->created_at->format('d M Y, H:i:s') }}
                                        <span class="block text-[10px] text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="py-4 text-center font-bold text-gray-300">{{ $log->records_processed }}</td>
                                    <td class="py-4 text-center font-bold text-rose-400">
                                        @if($log->records_failed > 0)
                                            <span class="px-2 py-0.5 rounded-lg bg-rose-950/40 text-rose-400">
                                                {{ $log->records_failed }}
                                            </span>
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="py-4">
                                        @if($log->status === 'success')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-950/60 border border-emerald-800/40 text-emerald-400">
                                                Sukses
                                            </span>
                                        @elseif($log->status === 'partial')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-950/60 border border-amber-800/40 text-amber-400">
                                                Sebagian
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-950/60 border border-rose-800/40 text-rose-400">
                                                Gagal
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-xs text-gray-400 max-w-xs truncate" title="{{ $log->summary }}">
                                        {{ $log->summary }}
                                    </td>
                                    <td class="py-4 text-right">
                                        @if(!empty($log->error_details))
                                            <button @click="
                                                activeLogErrors = {{ json_encode($log->error_details) }};
                                                showDetailModal = true;
                                            " class="px-3.5 py-1.5 bg-gray-800 hover:bg-gray-800 border border-gray-700 text-xs font-semibold rounded-xl text-gray-300 transition-colors">
                                                Detail Error
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-600 font-semibold italic">Bersih</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-gray-500 text-sm">
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
            <div x-show="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-md" style="display: none;" x-transition>
                <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 max-w-lg w-full space-y-6">
                    <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                        <h3 class="text-lg font-bold text-gray-100 flex items-center space-x-2">
                            <span class="text-rose-400"></span>
                            <span>Riwayat Peringatan / Masalah Validasi</span>
                        </h3>
                        <button @click="showDetailModal = false" class="text-gray-400 hover:text-white transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                    
                    <div class="max-h-[350px] overflow-y-auto space-y-3 pr-2 scrollbar-thin scrollbar-thumb-gray-800 text-sm leading-relaxed text-gray-300">
                        <template x-for="(err, idx) in activeLogErrors" :key="idx">
                            <div class="p-3 bg-gray-950 border border-gray-900 rounded-2xl">
                                <div class="flex items-center justify-between border-b border-gray-900 pb-1 mb-2">
                                    <span class="font-bold text-emerald-400" x-text="err.identifier || 'Ekspor Error'"></span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-800 text-gray-300" x-text="'Baris ' + (err.row || 'N/A')"></span>
                                </div>
                                <ul class="list-disc list-inside space-y-0.5 text-xs text-rose-400">
                                    <template x-for="(errMsg, errIdx) in (err.errors || [err.message || 'Error tidak dikenal'])" :key="errIdx">
                                        <li x-text="errMsg"></li>
                                    </template>
                                </ul>
                            </div>
                        </template>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="button" @click="showDetailModal = false" class="px-6 py-2.5 bg-gray-800 hover:bg-gray-700 font-semibold rounded-2xl text-gray-200 transition-colors">Tutup</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
