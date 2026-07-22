<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-2xl border border-rose-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </span>
                    Forensic Audit Trail & Security Threat Logs
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pencatatan riwayat peristiwa keamanan, inspeksi jejak IP, dan forensik payload request.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.security.index') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow-md shadow-rose-500/20 flex items-center gap-2 transition-all">
                    Dashboard Keamanan
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ payloadModalOpen: false, activePayload: null }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Toolbar -->
            <div class="bg-white dark:bg-gray-800 p-4 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <form action="{{ route('admin.security.logs') }}" method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
                    <div>
                        <select name="severity" onchange="this.form.submit()" class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-rose-500">
                            <option value="">Semua Tingkat Keparahan</option>
                            <option value="critical" {{ $severityFilter === 'critical' ? 'selected' : '' }}>Critical</option>
                            <option value="high" {{ $severityFilter === 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ $severityFilter === 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ $severityFilter === 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>

                    <div>
                        <select name="resolved" onchange="this.form.submit()" class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white text-xs font-semibold rounded-xl px-3 py-2 focus:ring-2 focus:ring-rose-500">
                            <option value="">Semua Status Resolusi</option>
                            <option value="0" {{ $resolvedFilter === '0' ? 'selected' : '' }}>Belum Selesai (Unresolved)</option>
                            <option value="1" {{ $resolvedFilter === '1' ? 'selected' : '' }}>Selesai (Resolved)</option>
                        </select>
                    </div>
                </form>

                <div class="text-xs font-mono text-gray-400">
                    Menampilkan {{ $logs->firstItem() ?? 0 }}-{{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} log
                </div>
            </div>

            <!-- Logs Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase text-[9px] font-bold">
                            <tr>
                                <th class="p-3 rounded-l-xl">Waktu / User</th>
                                <th class="p-3">Jenis Peristiwa</th>
                                <th class="p-3">IP Address & User Agent</th>
                                <th class="p-3">Severity</th>
                                <th class="p-3">Payload Data</th>
                                <th class="p-3 text-right rounded-r-xl">Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-750 font-medium">
                            @forelse($logs as $log)
                                <tr>
                                    <td class="p-3">
                                        <strong class="text-gray-900 dark:text-white block font-bold">{{ $log->created_at->format('d-m-Y H:i:s') }}</strong>
                                        <span class="text-[10px] text-gray-400 block">{{ $log->user->name ?? 'Anonim / Visitor' }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="font-mono font-bold text-gray-800 dark:text-gray-200 block">{{ $log->event_type }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="font-mono font-bold text-rose-600 dark:text-rose-400 block">{{ $log->ip_address }}</span>
                                        <span class="text-[10px] text-gray-400 truncate max-w-[200px] block" title="{{ $log->user_agent }}">{{ $log->user_agent ?? '-' }}</span>
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $log->severity_badge }}">
                                            {{ $log->severity }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        @if($log->payload_data)
                                            <button @click="activePayload = {{ json_encode($log->payload_data) }}; payloadModalOpen = true" class="px-2.5 py-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-[10px] font-mono font-bold transition-all">
                                                Lihat JSON Payload
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-[10px] italic">-</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-right">
                                        @if(!$log->is_resolved)
                                            <form action="{{ route('admin.security.resolve-threat', $log->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white rounded-xl text-[10px] font-bold uppercase transition-all">
                                                    Resolve
                                                </button>
                                            </form>
                                        @else
                                            <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 rounded-full font-bold text-[9px] uppercase border border-emerald-500/20">Selesai</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-8 text-center text-gray-400 italic">Belum ada data log ancaman keamanan forensik yang tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-700">
                    {{ $logs->links() }}
                </div>
            </div>

        </div>

        <!-- Payload JSON Modal -->
        <div x-show="payloadModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="payloadModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="payloadModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="payloadModalOpen" x-transition.scale class="inline-block align-bottom bg-gray-900 text-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-800 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                        <h3 class="text-sm font-black uppercase text-teal-400 tracking-wider">Payload Forensic JSON Request</h3>
                        <button @click="payloadModalOpen = false" class="text-gray-400 hover:text-white"></button>
                    </div>

                    <pre class="bg-gray-950 p-4 rounded-2xl border border-gray-800 text-teal-300 font-mono text-xs overflow-x-auto max-h-80" x-text="JSON.stringify(activePayload, null, 2)"></pre>

                    <div class="flex justify-end pt-2">
                        <button @click="payloadModalOpen = false" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-xs font-bold">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
