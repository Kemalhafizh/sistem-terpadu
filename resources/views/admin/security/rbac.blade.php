<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-2xl border border-emerald-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                    </span>
                    Immutable Cryptographic Audit Ledger & Enterprise RBAC
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Audit trail kriptografi berbasis rantai Hash SHA-256 (Anti-Manipulasi Database) dan Matriks Izin Peran Granular.</p>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('admin.security.rbac.verify-ledger') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-xs shadow-md shadow-emerald-500/20 flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> Verifikasi Integritas Rantai Kriptografi
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Ledger Integrity Status Banner -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-4 rounded-2xl {{ $integrity['is_valid'] ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' }}">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status Integritas Hash Chain (SHA-256 Merkle Link)</span>
                        <h4 class="text-xl font-black mt-0.5 {{ $integrity['is_valid'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            {{ $integrity['status'] }}
                        </h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Total {{ $integrity['total_logs'] }} entri terdaftar. {{ $integrity['tampered_count'] }} manipulasi terdeteksi.</p>
                    </div>
                </div>

                <div class="px-4 py-2 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 text-right">
                    <span class="text-[10px] text-gray-400 block font-bold uppercase">ALGORITMA HASH:</span>
                    <strong class="text-xs font-mono text-indigo-500">SHA-256 BLOCKCHAIN-LIKE CHAIN</strong>
                </div>
            </div>

            <!-- Cryptographic Audit Log Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Immutable Audit Trail (Rantai Log Kriptografi)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Mencatat mutasi data penting dengan linking hash log sebelumnya.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase text-[9px] font-bold">
                            <tr>
                                <th class="p-3 rounded-l-xl">ID Log</th>
                                <th class="p-3">Tipe Event</th>
                                <th class="p-3">Eksekutor (User)</th>
                                <th class="p-3">Previous Hash (SHA-256)</th>
                                <th class="p-3">Current Hash (SHA-256)</th>
                                <th class="p-3 text-right rounded-r-xl">Waktu Transaksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-750 font-medium">
                            @forelse($logs as $log)
                                <tr>
                                    <td class="p-3 font-mono font-bold text-gray-900 dark:text-white">#{{ $log->id }}</td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-1 bg-indigo-500/10 text-indigo-500 rounded-full font-bold text-[9px] font-mono">
                                            {{ $log->event_type }}
                                        </span>
                                    </td>
                                    <td class="p-3 font-bold text-gray-900 dark:text-white">
                                        {{ $log->user ? $log->user->name : 'System Lifecycle' }}
                                    </td>
                                    <td class="p-3 font-mono text-[10px] text-gray-400" title="{{ $log->previous_hash }}">
                                        {{ substr($log->previous_hash, 0, 16) }}...
                                    </td>
                                    <td class="p-3 font-mono text-[10px] text-emerald-500 font-bold" title="{{ $log->current_hash }}">
                                        {{ substr($log->current_hash, 0, 16) }}...
                                    </td>
                                    <td class="p-3 text-right font-mono text-gray-400 text-[10px]">
                                        {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-gray-400 text-xs">Belum ada entri log audit kriptografi terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-2">
                    {{ $logs->links() }}
                </div>
            </div>

            <!-- Enterprise Role-Permission Matrix Section -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Matriks Peran & Hak Akses Enterprise (RBAC Matrix)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pemetaan hak akses granular per peran pengguna pada SMA Islam Sultan Agung 1 Semarang.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($matrix as $roleKey => $roleData)
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-2">
                            <div class="flex items-center justify-between">
                                <strong class="text-xs font-black text-gray-900 dark:text-white uppercase tracking-wider">{{ $roleData['label'] }}</strong>
                                <span class="px-2 py-0.5 bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded font-mono text-[10px] font-bold">
                                    {{ $roleKey }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @foreach($roleData['permissions'] as $perm)
                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-md font-mono text-[10px] font-bold">
                                        {{ $perm }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
