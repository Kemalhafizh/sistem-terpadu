<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Pemantauan Tiket Perbaikan Aset (Kanban)') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">IoT QR Code Maintenance Dashboard</p>
            </div>
            <div>
                <a href="{{ route('admin.assets.maintenance.print-qr') }}" target="_blank" class="inline-flex items-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-black text-xs shadow-lg shadow-teal-600/10 hover:shadow-teal-600/20 transition-all space-x-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak Label QR A4</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        <!-- Notification Alerts -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-300 border border-emerald-100 dark:border-emerald-900/50 rounded-2xl flex items-center space-x-3 shadow-md animate-fade-in-up">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm font-semibold">{{ session('success') }}</div>
            </div>
        @endif

        <!-- Filter Toolbar -->
        <div class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm border border-gray-150 dark:border-gray-700/50 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <form action="{{ route('admin.assets.maintenance.index') }}" method="GET" class="w-full md:w-auto flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                <div class="min-w-[200px]">
                    <label class="block text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1.5">Saring Prioritas</label>
                    <select name="priority" onchange="this.form.submit()" class="w-full rounded-xl border-gray-200 dark:border-gray-750 bg-gray-50 dark:bg-gray-900/50 text-xs font-bold focus:border-teal-500 focus:ring-teal-500 text-gray-700 dark:text-gray-300">
                        <option value="">Semua Prioritas</option>
                        <option value="low" {{ $priorityFilter == 'low' ? 'selected' : '' }}>Low (Rendah)</option>
                        <option value="medium" {{ $priorityFilter == 'medium' ? 'selected' : '' }}>Medium (Sedang)</option>
                        <option value="high" {{ $priorityFilter == 'high' ? 'selected' : '' }}>High (Tinggi)</option>
                        <option value="critical" {{ $priorityFilter == 'critical' ? 'selected' : '' }}>Critical (Kritis)</option>
                    </select>
                </div>
                @if($priorityFilter)
                    <div class="flex items-end">
                        <a href="{{ route('admin.assets.maintenance.index') }}" class="px-4 py-2.5 bg-gray-100 dark:bg-gray-750 text-gray-600 dark:text-gray-400 rounded-xl text-xs font-bold hover:bg-gray-200 dark:hover:bg-gray-700 transition-all flex items-center space-x-1 self-end mt-5 sm:mt-0">
                            <span>Hapus Filter</span>
                        </a>
                    </div>
                @endif
            </form>
            <div class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                Total Tiket Aktif: {{ $pending->count() + $investigating->count() + $repairing->count() + $resolved->count() + $scrapped->count() }} Laporan
            </div>
        </div>

        <!-- Kanban Board Container -->
        <div class="overflow-x-auto pb-6">
            <div class="flex space-x-6 min-w-[1250px] lg:min-w-0 lg:grid lg:grid-cols-5 lg:space-x-0 lg:gap-4 items-start">
                
                <!-- 1. BARU (PENDING) -->
                <div class="flex-1 min-w-[240px] bg-slate-50/50 dark:bg-gray-900/20 p-4 rounded-[2rem] border border-gray-150 dark:border-gray-800/80 flex flex-col space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-850 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                            <h3 class="font-black text-sm text-gray-800 dark:text-gray-200 uppercase tracking-wider">Baru</h3>
                        </div>
                        <span class="px-2.5 py-0.5 bg-blue-100 dark:bg-blue-950 text-blue-600 dark:text-blue-400 text-[10px] font-black rounded-full">{{ $pending->count() }}</span>
                    </div>
                    <div class="flex flex-col space-y-3 min-h-[450px]">
                        @forelse($pending as $ticket)
                            @include('assets.maintenance._ticket-card', ['ticket' => $ticket])
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400 dark:text-gray-600 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                <span class="text-xs font-semibold">Tidak ada tiket baru</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 2. INVESTIGASI (INVESTIGATING) -->
                <div class="flex-1 min-w-[240px] bg-slate-50/50 dark:bg-gray-900/20 p-4 rounded-[2rem] border border-gray-150 dark:border-gray-800/80 flex flex-col space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-850 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span>
                            <h3 class="font-black text-sm text-gray-800 dark:text-gray-200 uppercase tracking-wider">Investigasi</h3>
                        </div>
                        <span class="px-2.5 py-0.5 bg-purple-100 dark:bg-purple-950 text-purple-600 dark:text-purple-400 text-[10px] font-black rounded-full">{{ $investigating->count() }}</span>
                    </div>
                    <div class="flex flex-col space-y-3 min-h-[450px]">
                        @forelse($investigating as $ticket)
                            @include('assets.maintenance._ticket-card', ['ticket' => $ticket])
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400 dark:text-gray-600 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                <span class="text-xs font-semibold">Tidak ada investigasi</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. PERBAIKAN (REPAIRING) -->
                <div class="flex-1 min-w-[240px] bg-slate-50/50 dark:bg-gray-900/20 p-4 rounded-[2rem] border border-gray-150 dark:border-gray-800/80 flex flex-col space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-850 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                            <h3 class="font-black text-sm text-gray-800 dark:text-gray-200 uppercase tracking-wider">Perbaikan</h3>
                        </div>
                        <span class="px-2.5 py-0.5 bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 text-[10px] font-black rounded-full">{{ $repairing->count() }}</span>
                    </div>
                    <div class="flex flex-col space-y-3 min-h-[450px]">
                        @forelse($repairing as $ticket)
                            @include('assets.maintenance._ticket-card', ['ticket' => $ticket])
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400 dark:text-gray-600 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="text-xs font-semibold">Tidak ada perbaikan</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 4. SELESAI (RESOLVED) -->
                <div class="flex-1 min-w-[240px] bg-slate-50/50 dark:bg-gray-900/20 p-4 rounded-[2rem] border border-gray-150 dark:border-gray-800/80 flex flex-col space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-850 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                            <h3 class="font-black text-sm text-gray-800 dark:text-gray-200 uppercase tracking-wider">Selesai</h3>
                        </div>
                        <span class="px-2.5 py-0.5 bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 text-[10px] font-black rounded-full">{{ $resolved->count() }}</span>
                    </div>
                    <div class="flex flex-col space-y-3 min-h-[450px]">
                        @forelse($resolved as $ticket)
                            @include('assets.maintenance._ticket-card', ['ticket' => $ticket])
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400 dark:text-gray-600 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span class="text-xs font-semibold">Tidak ada tiket selesai</span>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 5. AFKIR (SCRAPPED) -->
                <div class="flex-1 min-w-[240px] bg-slate-50/50 dark:bg-gray-900/20 p-4 rounded-[2rem] border border-gray-150 dark:border-gray-800/80 flex flex-col space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-850 pb-3">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                            <h3 class="font-black text-sm text-gray-800 dark:text-gray-200 uppercase tracking-wider">Afkir</h3>
                        </div>
                        <span class="px-2.5 py-0.5 bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400 text-[10px] font-black rounded-full">{{ $scrapped->count() }}</span>
                    </div>
                    <div class="flex flex-col space-y-3 min-h-[450px]">
                        @forelse($scrapped as $ticket)
                            @include('assets.maintenance._ticket-card', ['ticket' => $ticket])
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center text-gray-400 dark:text-gray-600 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-3xl">
                                <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span class="text-xs font-semibold">Tidak ada aset afkir</span>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
