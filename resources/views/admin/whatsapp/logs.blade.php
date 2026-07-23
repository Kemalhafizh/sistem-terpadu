<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 dark:text-white leading-tight tracking-tight uppercase">
                    {{ __('WhatsApp Gateway Logs') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Riwayat lengkap aktivitas pengiriman pesan WhatsApp beserta status detail.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            
            <!-- Navigation Tabs -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-2 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-wrap gap-2">
                <a href="{{ route('admin.whatsapp.dashboard') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Dashboard
                </a>
                <a href="{{ route('admin.whatsapp.templates.index') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Templates
                </a>
                <a href="{{ route('admin.whatsapp.broadcasts.index') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Broadcasts
                </a>
                <a href="{{ route('admin.whatsapp.automations.index') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Automations
                </a>
                <a href="{{ route('admin.whatsapp.index') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    Settings
                </a>
                <a href="{{ route('admin.whatsapp.logs') }}" class="px-4 py-2 rounded-2xl text-xs font-bold uppercase tracking-wider transition-all bg-teal-600 text-white shadow-sm hover:scale-102">
                    Logs
                </a>
            </div>

            <!-- Alerts -->
            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/30 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xs font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/30 text-red-800 dark:text-red-300 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xs font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Filter Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                <form action="{{ route('admin.whatsapp.logs') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    
                    <!-- Search Input -->
                    <div class="sm:col-span-2 lg:col-span-1 font-bold">
                        <label for="search" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Pencarian</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari nomor/pesan..." class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl pl-9 pr-3 py-2.5 text-gray-850 dark:text-white focus:ring-teal-500 focus:border-teal-500 font-medium">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Status Select -->
                    <div>
                        <label for="status" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Status</label>
                        <select name="status" id="status" class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-gray-800 dark:text-white focus:ring-teal-500 focus:border-teal-500 font-medium">
                            <option value="">Semua Status</option>
                            <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Tertunda</option>
                        </select>
                    </div>

                    <!-- Type Select/Input -->
                    <div>
                        <label for="type" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Tipe/Kategori</label>
                        <select name="type" id="type" class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-gray-800 dark:text-white focus:ring-teal-500 focus:border-teal-500 font-medium">
                            <option value="">Semua Kategori</option>
                            <option value="manual_test" {{ request('type') === 'manual_test' ? 'selected' : '' }}>Simulator Test</option>
                            <option value="broadcast" {{ request('type') === 'broadcast' ? 'selected' : '' }}>Siaran Massal</option>
                            <option value="attendance" {{ request('type') === 'attendance' ? 'selected' : '' }}>Absensi Alpa</option>
                            <option value="billing" {{ request('type') === 'billing' ? 'selected' : '' }}>Tagihan SPP/Iuran</option>
                            <option value="exam" {{ request('type') === 'exam' ? 'selected' : '' }}>Ujian CBT</option>
                            <option value="uks" {{ request('type') === 'uks' ? 'selected' : '' }}>UKS Darurat</option>
                            <option value="grade" {{ request('type') === 'grade' ? 'selected' : '' }}>Akademik/Nilai</option>
                            <option value="mutabaah" {{ request('type') === 'mutabaah' ? 'selected' : '' }}>Mutaba'ah</option>
                            <option value="tahfidz" {{ request('type') === 'tahfidz' ? 'selected' : '' }}>Tahfidz</option>
                        </select>
                    </div>

                    <!-- Date Input -->
                    <div>
                        <label for="date" class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Tanggal</label>
                        <input type="date" name="date" id="date" value="{{ request('date') }}" class="w-full text-xs bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-gray-800 dark:text-white focus:ring-teal-500 focus:border-teal-500 font-medium">
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm focus:outline-none hover:-translate-y-0.5">
                            Filter
                        </button>
                        <a href="{{ route('admin.whatsapp.logs') }}" class="inline-flex items-center justify-center p-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-750 dark:hover:bg-gray-700 text-gray-500 dark:text-gray-400 rounded-xl transition-all focus:outline-none" title="Reset Filters">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"></path></svg>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table of logs -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Log Audit Gateway</h3>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                        Total: {{ $logs->total() }} Log
                    </span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30 text-gray-400 uppercase tracking-widest font-black">
                                <th class="py-4 px-6">Penerima (Nomor)</th>
                                <th class="py-4 px-6">Isi Pesan</th>
                                <th class="py-4 px-6">Tipe/Kategori</th>
                                <th class="py-4 px-6">Status</th>
                                <th class="py-4 px-6">Error Detail</th>
                                <th class="py-4 px-6">Tanggal Kirim</th>
                                <th class="py-4 px-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($logs as $log)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="py-4 px-6 font-bold text-gray-900 dark:text-white">
                                        {{ $log->recipient }}
                                    </td>
                                    <td class="py-4 px-6 max-w-xs truncate text-gray-500 dark:text-gray-400 font-medium" title="{{ $log->message }}">
                                        {{ $log->message }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold tracking-wider uppercase bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                                            {{ str_replace('Notification', '', $log->type ?? 'Manual') }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        @if($log->status === 'sent')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/30">Terkirim</span>
                                        @elseif($log->status === 'failed')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/30">Gagal</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30 font-medium">Tertunda</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 max-w-[150px] truncate text-red-500 font-medium" title="{{ $log->error_message }}">
                                        {{ $log->error_message ?? '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-gray-400 font-medium">
                                        {{ $log->created_at->translatedFormat('d M Y H:i') }}
                                        <span class="block text-[10px] text-gray-400 font-medium mt-0.5">{{ $log->created_at->diffForHumans() }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <form action="{{ route('admin.whatsapp.resend', $log->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1.5 rounded-lg text-gray-400 hover:text-teal-600 dark:hover:text-teal-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors" title="Kirim Ulang Pesan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"></path></svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-16 text-center text-gray-400 dark:text-gray-500 italic">
                                        Tidak ditemukan log pengiriman WhatsApp yang sesuai filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($logs->hasPages())
                    <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        {{ $logs->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
