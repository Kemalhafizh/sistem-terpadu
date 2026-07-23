<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Broadcast Details') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Detail status pengiriman, performa dan logs penerima dari siaran pesan massal.</p>
            </div>
            
            <a href="{{ route('admin.whatsapp.broadcasts.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Siaran
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full sm:px-6 lg:px-8 space-y-6">
            
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

            @if(session('info'))
                <div class="bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800/30 text-amber-800 dark:text-amber-300 px-4 py-3 rounded-2xl flex items-center gap-3 shadow-sm">
                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xs font-bold">{{ session('info') }}</span>
                </div>
            @endif

            <!-- Main Status Grid -->
            @php
                $processed = $broadcast->sent_count + $broadcast->failed_count;
                $progressPercent = $broadcast->total_recipients > 0 ? round(($processed / $broadcast->total_recipients) * 100, 1) : 0;
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Progress Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex flex-col justify-between">
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Progres Pengiriman</span>
                        <div class="flex items-end justify-between mb-2">
                            <span class="text-3xl font-black text-teal-650 dark:text-teal-400">{{ $progressPercent }}%</span>
                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ $processed }} / {{ $broadcast->total_recipients }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                        <div class="bg-teal-600 h-3 rounded-full transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>

                <!-- Total Target Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="p-4 rounded-2xl bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Total Target</span>
                        <span class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($broadcast->total_recipients) }}</span>
                    </div>
                </div>

                <!-- Sukses Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider font-bold">Sukses</span>
                        <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400">{{ number_format($broadcast->sent_count) }}</span>
                    </div>
                </div>

                <!-- Gagal Card -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
                    <div class="p-4 rounded-2xl bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Gagal</span>
                        <span class="text-3xl font-black text-red-600 dark:text-red-400">{{ number_format($broadcast->failed_count) }}</span>
                    </div>
                </div>
            </div>

            <!-- Broadcast Details & Actions -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Info Panel -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">
                    <div>
                        <h3 class="font-bold text-sm text-gray-800 dark:text-white uppercase tracking-wider border-b border-gray-50 dark:border-gray-700 pb-3 mb-4">Informasi Siaran</h3>
                        
                        <div class="space-y-3 text-xs">
                            <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                <span class="font-bold text-gray-400 dark:text-gray-500 uppercase">Judul Siaran</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ $broadcast->title }}</span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                <span class="font-bold text-gray-400 dark:text-gray-500 uppercase">Template</span>
                                <span class="font-bold text-teal-650 dark:text-teal-400">
                                    {{ $broadcast->template ? $broadcast->template->name : 'Tanpa Template' }}
                                </span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                <span class="font-bold text-gray-400 dark:text-gray-500 uppercase">Target</span>
                                <span class="font-bold text-gray-900 dark:text-white">
                                    @if($broadcast->target_type === 'all_parents')
                                        Semua Wali Siswa
                                    @elseif($broadcast->target_type === 'all_teachers')
                                        Semua Guru
                                    @elseif($broadcast->target_type === 'specific_class')
                                        Kelas Spesifik
                                    @else
                                        Kustom
                                    @endif
                                </span>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                <span class="font-bold text-gray-400 dark:text-gray-500 uppercase">Status</span>
                                <div>
                                    @if($broadcast->status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/30">Completed</span>
                                    @elseif($broadcast->status === 'sending')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30 animate-pulse">Sending</span>
                                    @elseif($broadcast->status === 'scheduled')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30">Scheduled</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-400 border border-gray-200 dark:border-gray-800">Draft</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex justify-between py-1 border-b border-gray-50 dark:border-gray-700/50">
                                <span class="font-bold text-gray-400 dark:text-gray-500 uppercase">Pembuat</span>
                                <span class="font-bold text-gray-955 dark:text-white">{{ $broadcast->creator ? $broadcast->creator->name : 'System' }}</span>
                            </div>
                            <div class="flex justify-between py-1">
                                <span class="font-bold text-gray-400 dark:text-gray-500 uppercase">Tanggal Kirim</span>
                                <span class="font-bold text-gray-955 dark:text-white">
                                    {{ $broadcast->scheduled_at ? $broadcast->scheduled_at->translatedFormat('d M Y H:i') : $broadcast->created_at->translatedFormat('d M Y H:i') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Message Body -->
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-4 border border-gray-100 dark:border-gray-800">
                        <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-2">Isi Pesan</span>
                        <div class="text-xs text-gray-700 dark:text-gray-300 font-medium whitespace-pre-wrap leading-relaxed">{{ $broadcast->message }}</div>
                    </div>

                    <!-- Retry Button Form -->
                    @if($broadcast->failed_count > 0)
                        <div class="pt-2">
                            <form action="{{ route('admin.whatsapp.broadcasts.retry', $broadcast->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all shadow-sm hover:scale-[1.01] hover:-translate-y-0.5 focus:outline-none">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"></path></svg>
                                    Kirim Ulang yang Gagal ({{ $broadcast->failed_count }})
                                </button>
                            </form>
                        </div>
                    @endif
                </div>

                <!-- Recipient logs table -->
                <div class="lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden flex flex-col h-full">
                        <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Log Penerima</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 font-medium">
                                {{ $logs->total() }} Log
                            </span>
                        </div>

                        <div class="overflow-x-auto flex-grow">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30 text-gray-400 uppercase tracking-widest font-black">
                                        <th class="py-4 px-6">Nomor Telepon</th>
                                        <th class="py-4 px-6">Isi Pesan</th>
                                        <th class="py-4 px-6">Status</th>
                                        <th class="py-4 px-6">Error Detail</th>
                                        <th class="py-4 px-6 text-right">Terkirim Pada</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                    @forelse($logs as $log)
                                        <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/10 transition-colors">
                                            <td class="py-4 px-6 font-bold text-gray-900 dark:text-white">
                                                {{ $log->recipient }}
                                            </td>
                                            <td class="py-4 px-6 max-w-[180px] truncate text-gray-500 dark:text-gray-400 font-medium" title="{{ $log->message }}">
                                                {{ $log->message }}
                                            </td>
                                            <td class="py-4 px-6">
                                                @if($log->status === 'sent')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 border border-green-200 dark:border-green-800/30">Terkirim</span>
                                                @elseif($log->status === 'failed')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-800/30">Gagal</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30 font-medium font-bold">Tertunda</span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6 max-w-[150px] truncate text-red-500 font-medium font-bold" title="{{ $log->error_message }}">
                                                {{ $log->error_message ?? '-' }}
                                            </td>
                                            <td class="py-4 px-6 text-right text-gray-400 font-medium">
                                                {{ $log->created_at->translatedFormat('d M Y H:i') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="py-16 text-center text-gray-400 dark:text-gray-500 italic">
                                                Belum ada data pengiriman untuk siaran ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($logs->hasPages())
                            <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                                {{ $logs->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</x-app-layout>
