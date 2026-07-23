<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Direktorat Keuangan</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    ZIS & Wakaf YBWSA
                </h2>
            </div>
            <div>
                <a href="{{ route('admin.finance.charity.create') }}" 
                   class="inline-flex items-center px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-sm shadow-lg shadow-teal-600/20 hover:shadow-teal-600/30 transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                    </svg>
                    Buat Kampanye Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8 w-full">
        <!-- Session Flash Messages -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 p-4 rounded-2xl shadow-sm text-emerald-800 dark:text-emerald-300 font-bold text-sm flex items-center space-x-3">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Summary Panels -->
        @php
            $activeCount = $campaigns->where('is_active', true)->count();
            $totalCollected = $campaigns->sum('collected_amount');
            $totalCampaigns = $campaigns->count();
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Campaigns -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 text-gray-100 dark:text-gray-700/50 opacity-50 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col justify-between h-full">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Total Kampanye</span>
                        <h3 class="font-black text-4xl text-gray-800 dark:text-white mt-2">{{ $totalCampaigns }}</h3>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-4 font-medium">Keseluruhan program ZIS & Wakaf</span>
                </div>
            </div>

            <!-- Active Campaigns -->
            <div class="bg-teal-50/50 dark:bg-teal-950/20 p-6 rounded-[2rem] border border-teal-100 dark:border-teal-900/50 shadow-sm relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 text-teal-100 dark:text-teal-900/20 opacity-50 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col justify-between h-full">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Kampanye Aktif</span>
                        <h3 class="font-black text-4xl text-teal-800 dark:text-teal-300 mt-2">{{ $activeCount }}</h3>
                    </div>
                    <span class="text-xs text-teal-600 dark:text-teal-400 mt-4 font-medium">Program yang sedang menggalang dana</span>
                </div>
            </div>

            <!-- Total Collected -->
            <div class="bg-linear-to-br from-teal-600 to-emerald-600 p-6 rounded-[2rem] text-white shadow-xl shadow-teal-600/10 relative overflow-hidden group">
                <div class="absolute -right-4 -bottom-4 text-teal-500/20 opacity-50 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="relative z-10 flex flex-col justify-between h-full">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-teal-100">Donasi Terkumpul</span>
                        <h3 class="font-black text-3xl mt-2 leading-none">Rp {{ number_format($totalCollected, 0, ',', '.') }}</h3>
                    </div>
                    <span class="text-xs text-teal-100 mt-4 font-medium">Total dana disalurkan donatur</span>
                </div>
            </div>
        </div>

        <!-- Campaign Table Section -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-8 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h3 class="font-black text-lg text-gray-800 dark:text-white">Daftar Kampanye Donasi</h3>
                    <p class="text-xs text-gray-400 mt-1">Kelola status, target, dan verifikasi donatur kampanye ZIS & Wakaf.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/30 text-left text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                            <th class="px-8 py-5">Nama Kampanye</th>
                            <th class="px-6 py-5">Tipe</th>
                            <th class="px-6 py-5 text-right">Target Dana</th>
                            <th class="px-6 py-5 text-right font-black">Terkumpul</th>
                            <th class="px-8 py-5">Progress Capaian</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            <th class="px-8 py-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($campaigns as $campaign)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="max-w-xs md:max-w-sm">
                                        <p class="font-black text-gray-800 dark:text-white text-sm hover:text-teal-600 transition-colors">{{ $campaign->title }}</p>
                                        @if($campaign->end_date)
                                            <p class="text-[10px] text-gray-400 mt-1 flex items-center font-bold uppercase tracking-tighter">
                                                <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Berakhir: {{ $campaign->end_date->format('d M Y') }}
                                            </p>
                                        @else
                                            <p class="text-[10px] text-teal-600 dark:text-teal-400 mt-1 flex items-center font-bold uppercase tracking-tighter">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                                                </svg>
                                                Kampanye Abadi (Permanen)
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-6">
                                    @switch($campaign->type)
                                        @case('zakat')
                                            <span class="inline-block px-3 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                                                Zakat
                                            </span>
                                            @break
                                        @case('infaq')
                                            <span class="inline-block px-3 py-1 bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                                                Infaq
                                            </span>
                                            @break
                                        @case('shodaqoh')
                                            <span class="inline-block px-3 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                                                Shodaqoh
                                            </span>
                                            @break
                                        @case('wakaf')
                                            <span class="inline-block px-3 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                                                Wakaf
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-6 text-right font-bold text-gray-700 dark:text-gray-300">
                                    @if($campaign->target_amount > 0)
                                        Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 font-semibold italic text-xs">Tanpa Batas</span>
                                    @endif
                                </td>
                                <td class="px-6 py-6 text-right font-black text-teal-600 dark:text-teal-400">
                                    Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}
                                </td>
                                <td class="px-8 py-6">
                                    @if($campaign->target_amount > 0)
                                        @php
                                            $pct = $campaign->percentage_achieved;
                                        @endphp
                                        <div class="w-full max-w-[150px]">
                                            <div class="flex justify-between items-center text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">
                                                <span>{{ $pct }}%</span>
                                                <span class="text-gray-400 font-medium">capaian</span>
                                            </div>
                                            <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                                <div class="h-full bg-linear-to-r from-teal-500 to-emerald-500 rounded-full transition-all duration-500" style="width: {{ $pct }}%"></div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex items-center space-x-1.5 text-xs text-gray-400 dark:text-gray-500 font-medium">
                                            <span class="inline-block w-2.5 h-2.5 bg-teal-500 rounded-full animate-pulse"></span>
                                            <span>Menerima Donasi</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-6 py-6 text-center">
                                    @if($campaign->is_active)
                                        <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-full text-[10px] font-black tracking-widest uppercase">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 rounded-full text-[10px] font-black tracking-widest uppercase">
                                            Nonaktif
                                        </span>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <!-- Donors Button -->
                                        <a href="{{ route('admin.finance.charity.donors', $campaign->id) }}" 
                                           class="inline-flex items-center px-3 py-2 bg-teal-50 dark:bg-teal-950/40 hover:bg-teal-600 hover:text-white text-teal-700 dark:text-teal-300 rounded-lg text-xs font-bold transition-all shadow-sm gap-1.5"
                                           title="Kelola Donatur & Verifikasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                            <span>Donatur ({{ $campaign->donations_count ?? 0 }})</span>
                                        </a>

                                        <!-- Edit Button -->
                                        <a href="{{ route('admin.finance.charity.edit', $campaign->id) }}" 
                                           class="p-2 bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 rounded-lg transition-all border border-gray-200/50 dark:border-gray-600"
                                           title="Edit Kampanye">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-gray-400 dark:text-gray-500 font-medium">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <span>Belum ada kampanye donasi yang dibuat.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
