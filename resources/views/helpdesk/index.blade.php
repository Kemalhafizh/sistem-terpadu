<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Helpdesk Internal') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sistem pelaporan masalah, perbaikan fasilitas, dan bantuan teknis internal.</p>
            </div>
            <div class="flex items-center gap-3">
                @if(in_array(auth()->user()->role, ['staf_tu', 'waka_sarpras', 'admin', 'superadmin']))
                    <a href="{{ route('helpdesk.dashboard') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Dashboard Analitik
                    </a>
                @endif
                <a href="{{ route('helpdesk.tickets.create') }}" class="px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-xs font-extrabold shadow-md hover:-translate-y-0.5 transition-all flex items-center gap-1.5 border border-blue-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Tiket Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">
        
        <!-- KPI Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            
            <!-- Card Total -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Laporan</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ $metrics['total'] ?? 0 }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>

            <!-- Card Open / Active -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Tiket Aktif</p>
                    <h3 class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $metrics['open'] ?? 0 }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Card Resolved -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Terselesaikan</p>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $metrics['resolved'] ?? 0 }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>

            <!-- Card Overdue -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">SLA Overdue</p>
                    <h3 class="text-2xl font-black text-rose-600 dark:text-rose-400">{{ $metrics['overdue'] ?? 0 }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ ($metrics['overdue'] ?? 0) > 0 ? 'animate-bounce' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>

        </div>

        <!-- Dynamic Filter Panel -->
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800 rounded-3xl p-5 shadow-sm">
            <form action="{{ route('helpdesk.tickets.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                
                <!-- Search -->
                <div class="space-y-1.5">
                    <label for="search" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cari Tiket</label>
                    <div class="relative">
                        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari judul, deskripsi, #..." class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white pl-9 pr-4 py-2.5 focus:border-blue-500 transition-all">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Category filter -->
                <div class="space-y-1.5">
                    <label for="category" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kategori</label>
                    <select name="category" id="category" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                        <option value="">-- Semua Kategori --</option>
                        <option value="it" {{ request('category') === 'it' ? 'selected' : '' }}>IT Support</option>
                        <option value="sarpras" {{ request('category') === 'sarpras' ? 'selected' : '' }}>Sarana Prasarana (Fasilitas)</option>
                        <option value="akademik" {{ request('category') === 'akademik' ? 'selected' : '' }}>Akademik</option>
                        <option value="keuangan" {{ request('category') === 'keuangan' ? 'selected' : '' }}>Administrasi Keuangan</option>
                        <option value="umum" {{ request('category') === 'umum' ? 'selected' : '' }}>Umum</option>
                    </select>
                </div>

                <!-- Priority filter -->
                <div class="space-y-1.5">
                    <label for="priority" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Prioritas</label>
                    <select name="priority" id="priority" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                        <option value="">-- Semua Prioritas --</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low (Rendah)</option>
                        <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium (Sedang)</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High (Tinggi)</option>
                        <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critical (Kritis)</option>
                    </select>
                </div>

                <!-- Status filter & Submit -->
                <div class="grid grid-cols-5 gap-2 items-end">
                    <div class="col-span-3 space-y-1.5">
                        <label for="status" class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</label>
                        <select name="status" id="status" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                            <option value="">-- Semua Status --</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="waiting" {{ request('status') === 'waiting' ? 'selected' : '' }}>Waiting</option>
                            <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="col-span-2">
                        <button type="submit" class="w-full py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-slate-700 dark:hover:bg-slate-600 transition-all text-center">
                            Filter
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Ticket Listing Card Table -->
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden animate-fade-in">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50/60 dark:bg-slate-950/40 border-b border-gray-100 dark:border-slate-800/80 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <th class="px-6 py-4">No. Tiket</th>
                            <th class="px-6 py-4">Subjek Masalah</th>
                            <th class="px-6 py-4 text-center">Kategori</th>
                            <th class="px-6 py-4 text-center">Prioritas</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4">Pelapor</th>
                            <th class="px-6 py-4">Petugas</th>
                            <th class="px-6 py-4">Batas SLA</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800/60 text-xs font-semibold text-gray-700 dark:text-slate-300">
                        @forelse($tickets as $t)
                            <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-950/10 transition-colors">
                                <!-- Ticket Number -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="font-extrabold text-slate-900 dark:text-white uppercase">#{{ $t->ticket_number }}</span>
                                    <span class="block text-[10px] text-slate-400 mt-0.5">{{ $t->created_at->format('d M Y H:i') }}</span>
                                </td>
                                
                                <!-- Title -->
                                <td class="px-6 py-4 max-w-xs">
                                    <div class="font-bold text-slate-900 dark:text-white truncate" title="{{ $t->title }}">{{ $t->title }}</div>
                                    <div class="text-[10px] text-slate-400 truncate mt-0.5" title="{{ $t->description }}">{{ Str::limit($t->description, 60) }}</div>
                                </td>
                                
                                <!-- Category -->
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $catLabel = match ($t->category) {
                                            'it' => 'IT Support',
                                            'sarpras' => 'Sarpras',
                                            'akademik' => 'Akademik',
                                            'keuangan' => 'Keuangan',
                                            'umum' => 'Umum',
                                            default => $t->category,
                                        };
                                        $catColors = match ($t->category) {
                                            'it' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20',
                                            'sarpras' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                            'akademik' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border-violet-500/20',
                                            'keuangan' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                            'umum' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20',
                                            default => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $catColors }}">{{ $catLabel }}</span>
                                </td>
                                
                                <!-- Priority -->
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $priLabel = match ($t->priority) {
                                            'low' => 'Low',
                                            'medium' => 'Medium',
                                            'high' => 'High',
                                            'critical' => 'Critical',
                                            default => $t->priority,
                                        };
                                        $priColors = match ($t->priority) {
                                            'low' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                            'medium' => 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 border-yellow-500/20',
                                            'high' => 'bg-orange-500/10 text-orange-600 dark:text-orange-400 border-orange-500/20',
                                            'critical' => 'bg-red-500/15 text-red-600 dark:text-red-400 border-red-500/30 animate-pulse',
                                            default => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $priColors }}">{{ $priLabel }}</span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    @php
                                        $statColors = match ($t->status) {
                                            'open' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                            'in_progress' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                                            'waiting' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                            'resolved' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                            'closed' => 'bg-slate-500/10 text-slate-500 border-slate-500/20',
                                            default => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $statColors }}">{{ strtoupper($t->status) }}</span>
                                </td>
                                
                                <!-- Reporter -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-extrabold text-slate-900 dark:text-white">{{ $t->reporter?->name ?? 'User Deleted' }}</div>
                                    <div class="text-[10px] text-slate-400 capitalize mt-0.5">{{ $t->reporter?->role }}</div>
                                </td>
                                
                                <!-- Assignee -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($t->assignee)
                                        <div class="font-extrabold text-slate-900 dark:text-white">{{ $t->assignee->name }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">Teknisi</div>
                                    @else
                                        <span class="text-[10px] font-extrabold text-slate-400 dark:text-slate-500 italic uppercase">Belum Ditugaskan</span>
                                    @endif
                                </td>
                                
                                <!-- SLA Deadline -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if(in_array($t->status, ['resolved', 'closed']))
                                        <span class="text-[10px] font-bold text-slate-400">SLA Selesai</span>
                                    @elseif($t->isOverdue())
                                        <span class="px-2 py-0.5 bg-red-500/10 text-red-500 border border-red-500/20 rounded-md text-[9px] font-black uppercase tracking-wider animate-pulse">SLA Breached (Overdue)</span>
                                        <span class="block text-[10px] text-red-400 mt-1">{{ $t->due_date ? $t->due_date->format('d M Y H:i') : '-' }}</span>
                                    @else
                                        <span class="text-slate-700 dark:text-slate-300 font-bold">{{ $t->due_date ? $t->due_date->format('d M Y H:i') : '-' }}</span>
                                        <span class="block text-[9px] text-slate-400 mt-0.5">{{ $t->due_date ? $t->due_date->diffForHumans() : '' }}</span>
                                    @endif
                                </td>
                                
                                <!-- Actions -->
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <a href="{{ route('helpdesk.tickets.show', $t->uuid) }}" class="px-3 py-1.5 bg-slate-100 hover:bg-indigo-600 dark:bg-slate-800 dark:hover:bg-indigo-600 text-slate-700 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition-all border border-slate-200 dark:border-slate-700/60 hover:border-indigo-600">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500 italic">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5a2 2 0 012-2zm-6-3h.01M9 16h.01"></path>
                                        </svg>
                                        <span class="font-bold">Tidak ada tiket pengaduan ditemukan.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Links -->
            @if($tickets->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-950/20">
                    {{ $tickets->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
