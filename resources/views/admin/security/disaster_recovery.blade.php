<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                        </svg>
                    </span>
                    Disaster Recovery & Automated Database Snapshot
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sistem pemulihan bencana data otomatis, verifikasi integritas kriptografi SHA-256, dan Point-In-Time Restore (PITR).</p>
            </div>
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.disaster-recovery.audit') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Jalankan Audit Integritas
                    </button>
                </form>

                <form action="{{ route('admin.disaster-recovery.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="label" value="manual_dashboard">
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Buat Snapshot Baru
                    </button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Metrics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Snapshot Tersimpan</span>
                    <div class="flex items-baseline gap-2 mt-2">
                        <span class="text-3xl font-black text-gray-900 dark:text-white">{{ count($snapshots) }}</span>
                        <span class="text-xs text-gray-500">Berkas Cadangan</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Kapasitas Penyimpanan</span>
                    <div class="flex items-baseline gap-2 mt-2">
                        <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $totalStorageFormatted }}</span>
                        <span class="text-xs text-gray-500">Penggunaan Disk</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Status Integritas DB</span>
                    <div class="mt-2">
                        @if($auditResult['status'] === 'HEALTHY')
                            <span class="px-3 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                HEALTHY (SEHAT)
                            </span>
                        @else
                            <span class="px-3 py-1 bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                                CORRUPTED / ATTENTION
                            </span>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Algoritma Kriptografi</span>
                    <div class="mt-2">
                        <span class="px-3 py-1 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 rounded-xl font-mono text-xs font-bold">
                            SHA-256 Checksum
                        </span>
                    </div>
                </div>
            </div>

            <!-- Database Integrity Audit Banner -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Hasil Audit Integritas Database Terakhir</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pemeriksaan struktur tabel, indeks, serta konsistensi hubungan foreign key.</p>
                    </div>
                    <span class="text-xs font-mono text-gray-400">Diperbarui: {{ $auditResult['timestamp'] ?? '-' }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($auditResult['checks'] as $key => $check)
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700/80 flex justify-between items-center">
                            <div>
                                <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block">{{ $check['name'] }}</span>
                                <span class="text-[10px] font-mono text-gray-400">
                                    {{ isset($check['size_readable']) ? $check['size_readable'] : (isset($check['errors_count']) ? 'Errors: ' . $check['errors_count'] : 'OK') }}
                                </span>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-[10px] font-mono font-bold {{ $check['status'] === 'PASSED' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400' }}">
                                {{ $check['status'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Snapshots Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Berkas Snapshot Database</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih snapshot untuk memverifikasi checksum atau melakukan Point-In-Time Restore.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Nama Berkas Snapshot</th>
                                <th class="px-6 py-4">Label</th>
                                <th class="px-6 py-4">Ukuran</th>
                                <th class="px-6 py-4">SHA-256 Checksum</th>
                                <th class="px-6 py-4">Waktu Pembuatan</th>
                                <th class="px-6 py-4 text-right">Aksi Recovery</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($snapshots as $snapshot)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $snapshot['filename'] }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded font-mono text-[10px] font-bold uppercase">
                                            {{ $snapshot['label'] ?? 'auto' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono">
                                        {{ round(($snapshot['size_bytes'] ?? 0) / 1024 / 1024, 2) }} MB
                                    </td>
                                    <td class="px-6 py-4 font-mono text-[11px]">
                                        <span class="bg-slate-100 dark:bg-gray-900 px-2 py-1 rounded text-slate-600 dark:text-gray-400 max-w-[180px] inline-block truncate" title="{{ $snapshot['checksum_sha256'] ?? '-' }}">
                                            {{ Str::limit($snapshot['checksum_sha256'] ?? '-', 24) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-gray-500">
                                        {{ Carbon\Carbon::parse($snapshot['created_at'])->format('d M Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <form action="{{ route('admin.disaster-recovery.verify') }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="filename" value="{{ $snapshot['filename'] }}">
                                            <button type="submit" class="px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-[11px] font-bold transition-all">
                                                Verifikasi SHA-256
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.disaster-recovery.restore') }}" method="POST" class="inline" onsubmit="return confirm('PERINGATAN! Pemulihan snapshot akan menggantikan database aktif saat ini. Lanjutkan?')">
                                            @csrf
                                            <input type="hidden" name="filename" value="{{ $snapshot['filename'] }}">
                                            <button type="submit" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-[11px] font-bold transition-all shadow-sm">
                                                Pulihkan (Restore)
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.disaster-recovery.delete') }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus snapshot ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="filename" value="{{ $snapshot['filename'] }}">
                                            <button type="submit" class="px-3 py-1.5 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 rounded-lg text-[11px] font-bold transition-all">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada snapshot database tersimpan. Klik "Buat Snapshot Baru" di atas.
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
