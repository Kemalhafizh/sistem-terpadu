<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-xl md:text-2xl text-gray-800 dark:text-white leading-tight tracking-tight uppercase">
                    {{ __('WhatsApp Broadcasts') }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Kirim pesan massal ke wali murid, guru, atau kelas tertentu secara terjadwal.</p>
            </div>
            
            <a href="{{ route('admin.whatsapp.broadcasts.create') }}" class="inline-flex items-center px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white text-xs font-bold uppercase tracking-wider rounded-xl transition-all hover:scale-105 shadow-sm focus:outline-none">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Siaran Baru
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Navigation Tabs -->
            <div class="flex flex-wrap gap-2 border-b border-gray-200 dark:border-gray-700 pb-4 mb-6">
                <a href="{{ route('admin.whatsapp.dashboard') }}" class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition-all {{ request()->routeIs('admin.whatsapp.dashboard') ? 'bg-teal-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-gray-700' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.whatsapp.templates.index') }}" class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition-all {{ request()->routeIs('admin.whatsapp.templates.index') || request()->routeIs('admin.whatsapp.templates.*') ? 'bg-teal-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-gray-700' }}">
                    Templates
                </a>
                <a href="{{ route('admin.whatsapp.broadcasts.index') }}" class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition-all {{ request()->routeIs('admin.whatsapp.broadcasts.index') || request()->routeIs('admin.whatsapp.broadcasts.*') ? 'bg-teal-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-gray-700' }}">
                    Broadcasts
                </a>
                <a href="{{ route('admin.whatsapp.automations.index') }}" class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition-all {{ request()->routeIs('admin.whatsapp.automations.index') || request()->routeIs('admin.whatsapp.automations.*') ? 'bg-teal-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-gray-700' }}">
                    Automations
                </a>
                <a href="{{ route('admin.whatsapp.index') }}" class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition-all {{ request()->routeIs('admin.whatsapp.index') ? 'bg-teal-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-gray-700' }}">
                    Settings & Simulator
                </a>
                <a href="{{ route('admin.whatsapp.logs') }}" class="px-4 py-2 text-xs font-bold uppercase tracking-wider rounded-xl transition-all {{ request()->routeIs('admin.whatsapp.logs') ? 'bg-teal-600 text-white shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-500 hover:text-gray-900 dark:hover:text-white border border-gray-100 dark:border-gray-700' }}">
                    Logs
                </a>
            </div>

            <!-- Broadcasts Table Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50 flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-800 dark:text-white uppercase tracking-wider">Riwayat Siaran WhatsApp</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 bg-gray-50/30 dark:bg-gray-800/30 text-gray-400 uppercase tracking-widest font-black">
                                <th class="py-4 px-6">Informasi Siaran</th>
                                <th class="py-4 px-6">Template</th>
                                <th class="py-4 px-6">Target</th>
                                <th class="py-4 px-6" style="min-width: 180px;">Progres Pengiriman</th>
                                <th class="py-4 px-6 text-center">Status</th>
                                <th class="py-4 px-6">Jadwal / Kirim</th>
                                <th class="py-4 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse($broadcasts as $broadcast)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/10 transition-colors">
                                    <!-- Informasi Siaran -->
                                    <td class="py-4 px-6">
                                        <div class="font-bold text-gray-900 dark:text-white text-sm hover:text-teal-600 dark:hover:text-teal-400 transition-colors">
                                            <a href="{{ route('admin.whatsapp.broadcasts.show', $broadcast->id) }}">{{ $broadcast->title }}</a>
                                        </div>
                                        <span class="block text-[10px] text-gray-400 dark:text-gray-500 font-semibold uppercase tracking-wider mt-1">Dibuat: {{ $broadcast->created_at->diffForHumans() }}</span>
                                    </td>

                                    <!-- Template -->
                                    <td class="py-4 px-6">
                                        @if($broadcast->template)
                                            <span class="font-bold text-gray-700 dark:text-gray-300">
                                                {{ $broadcast->template->name }}
                                            </span>
                                            <span class="block text-[10px] text-teal-600 dark:text-teal-400 font-semibold uppercase mt-0.5">{{ $broadcast->template->category }}</span>
                                        @else
                                            <span class="italic text-gray-400 dark:text-gray-500">Pesan Kustom</span>
                                        @endif
                                    </td>

                                    <!-- Target -->
                                    <td class="py-4 px-6">
                                        @if($broadcast->target_type === 'all_parents')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400 border border-purple-200 dark:border-purple-800/30">
                                                Orang Tua
                                            </span>
                                        @elseif($broadcast->target_type === 'all_teachers')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30">
                                                Guru
                                            </span>
                                        @elseif($broadcast->target_type === 'specific_class')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/30">
                                                Kelas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30">
                                                Kustom
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Progres Pengiriman -->
                                    <td class="py-4 px-6">
                                        @php
                                            $total = $broadcast->total_recipients ?? 0;
                                            $sent = $broadcast->sent_count ?? 0;
                                            $failed = $broadcast->failed_count ?? 0;
                                            
                                            $percentage = $total > 0 ? min(100, round(($sent / $total) * 100)) : 0;
                                            $failedPercentage = $total > 0 ? min(100, round(($failed / $total) * 100)) : 0;
                                        @endphp
                                        <div class="space-y-1">
                                            <div class="flex justify-between text-[10px] font-bold text-gray-500 dark:text-gray-400">
                                                <span>{{ $sent }} / {{ $total }} Terkirim</span>
                                                <span>{{ $percentage }}%</span>
                                            </div>
                                            <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden flex">
                                                <div class="bg-teal-500 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                                                @if($failed > 0)
                                                    <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $failedPercentage }}%"></div>
                                                @endif
                                            </div>
                                            @if($failed > 0)
                                                <div class="text-[9px] text-red-500 dark:text-red-400 font-bold flex items-center gap-1">
                                                    <span class="w-1.5 h-1.5 bg-red-500 rounded-full inline-block"></span>
                                                    {{ $failed }} Gagal Kirim
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-4 px-6 text-center">
                                        @if($broadcast->status === 'completed')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-green-100 dark:bg-green-950/50 text-green-800 dark:text-green-400 border border-green-200 dark:border-green-800/30">
                                                <span class="w-1.5 h-1.5 mr-1 bg-green-500 rounded-full"></span>
                                                Selesai
                                            </span>
                                        @elseif($broadcast->status === 'sending')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 dark:bg-blue-950/50 text-blue-800 dark:text-blue-400 border border-blue-200 dark:border-blue-800/30">
                                                <span class="w-1.5 h-1.5 mr-1 bg-blue-500 rounded-full animate-pulse"></span>
                                                Mengirim
                                            </span>
                                        @elseif($broadcast->status === 'scheduled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-100 dark:bg-amber-950/50 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800/30">
                                                <span class="w-1.5 h-1.5 mr-1 bg-amber-500 rounded-full"></span>
                                                Jadwal
                                            </span>
                                        @elseif($broadcast->status === 'cancelled')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-100 dark:bg-red-950/50 text-red-800 dark:text-red-400 border border-red-200 dark:border-red-800/30">
                                                <span class="w-1.5 h-1.5 mr-1 bg-red-500 rounded-full"></span>
                                                Dibatalkan
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-400 border border-gray-200 dark:border-gray-700">
                                                <span class="w-1.5 h-1.5 mr-1 bg-gray-400 dark:bg-gray-500 rounded-full"></span>
                                                Draft
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Scheduled At / Sent At -->
                                    <td class="py-4 px-6">
                                        <div class="text-[10px] space-y-1">
                                            <div>
                                                <span class="font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest text-[8px] block">Rencana Kirim</span>
                                                <span class="font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ $broadcast->scheduled_at ? $broadcast->scheduled_at->format('d M Y H:i') : 'Instan' }}
                                                </span>
                                            </div>
                                            @if($broadcast->sent_at)
                                                <div>
                                                    <span class="font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest text-[8px] block">Waktu Selesai</span>
                                                    <span class="font-semibold text-gray-700 dark:text-gray-300">
                                                        {{ $broadcast->sent_at->format('d M Y H:i') }}
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Action -->
                                    <td class="py-4 px-6 text-center">
                                        <a href="{{ route('admin.whatsapp.broadcasts.show', $broadcast->id) }}" 
                                           class="inline-flex items-center px-3 py-1.5 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700/50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all border border-gray-100 dark:border-gray-600 hover:scale-105"
                                           title="Detail Siaran">
                                            <svg class="w-3.5 h-3.5 mr-1 text-teal-600 dark:text-teal-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-16 text-center text-gray-400 dark:text-gray-500 italic">
                                        Belum ada siaran WhatsApp yang dibuat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($broadcasts->hasPages())
                    <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
                        {{ $broadcasts->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
