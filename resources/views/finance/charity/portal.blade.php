<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Portal Siswa & Orang Tua</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    ZIS & Wakaf Portal
                </h2>
            </div>
            <div class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest">
                Layanan Sosial Kemasyarakatan YBWSA
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

        <!-- Horizontal Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('portal.charity.portal') }}" 
                   class="border-b-2 border-teal-600 py-4 px-1 text-sm font-black uppercase tracking-widest text-teal-600 dark:text-teal-400"
                   aria-current="page">
                    Katalog Donasi
                </a>
                <a href="{{ route('portal.charity.history') }}" 
                   class="border-b-2 border-transparent py-4 px-1 text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    Riwayat Donasi
                </a>
            </nav>
        </div>

        <!-- Premium Salad Wadiah E-Wallet Card -->
        <div class="bg-linear-to-r from-teal-600 via-emerald-600 to-cyan-700 p-8 rounded-[2.5rem] text-white shadow-xl shadow-teal-700/10 relative overflow-hidden max-w-md">
            <div class="absolute -right-10 -bottom-10 opacity-25 text-white">
                <svg class="w-48 h-48" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
            </div>
            <div class="relative z-10 flex flex-col justify-between h-full space-y-6">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-[9px] font-black uppercase tracking-[0.2em] text-teal-100/80 block">Kartu E-Wallet Siswa</span>
                        <h4 class="text-xs font-bold text-white mt-0.5">Wadiah Smart Card</h4>
                    </div>
                    <svg class="w-10 h-10 text-teal-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                </div>
                
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-teal-100/90 block">Salad Wadiah (E-Wallet)</span>
                    <h3 class="text-3xl font-black mt-1 leading-none tracking-tight">
                        Salad Wadiah: Rp {{ number_format($walletBalance, 0, ',', '.') }}
                    </h3>
                </div>

                <div class="flex justify-between items-center text-[10px] text-teal-100/80 font-bold uppercase tracking-wider">
                    <span>{{ auth()->user()->name }}</span>
                    <span>SISTA Cashless</span>
                </div>
            </div>
        </div>

        <!-- Section Heading -->
        <div>
            <h3 class="font-black text-xl text-gray-800 dark:text-white uppercase tracking-widest text-[13px]">Program Donasi Aktif</h3>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Salurkan kepedulian Anda untuk memajukan dakwah dan sarana pendidikan Islam.</p>
        </div>

        <!-- Grid List of Active Campaigns -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($campaigns as $campaign)
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col justify-between overflow-hidden">
                    <div class="p-6 space-y-4">
                        <div class="flex justify-between items-center">
                            @switch($campaign->type)
                                @case('zakat')
                                    <span class="inline-block px-3 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                                        Zakat
                                    </span>
                                    @break
                                @case('infaq')
                                    <span class="inline-block px-3 py-1 bg-cyan-50 dark:bg-cyan-950/40 text-cyan-600 dark:text-cyan-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                                        Infaq
                                    </span>
                                    @break
                                @case('shodaqoh')
                                    <span class="inline-block px-3 py-1 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                                        Shodaqoh
                                    </span>
                                    @break
                                @case('wakaf')
                                    <span class="inline-block px-3 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                                        Wakaf
                                    </span>
                                    @break
                            @endswitch

                            @if($campaign->end_date)
                                <span class="text-[9px] font-bold text-red-500 uppercase tracking-tighter">
                                    Hingga: {{ $campaign->end_date->format('d M Y') }}
                                </span>
                            @else
                                <span class="text-[9px] font-bold text-teal-600 dark:text-teal-400 uppercase tracking-tighter">
                                    Kampanye Abadi
                                </span>
                            @endif
                        </div>

                        <div>
                            <h4 class="font-black text-gray-800 dark:text-white text-base leading-snug line-clamp-1">
                                {{ $campaign->title }}
                            </h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 line-clamp-3 leading-relaxed">
                                {{ $campaign->description }}
                            </p>
                        </div>

                        <!-- Progress Bar & Amounts -->
                        <div class="space-y-2 pt-2">
                            <div class="flex justify-between items-center text-[10px] font-bold">
                                <span class="text-gray-400">Terkumpul</span>
                                <span class="text-teal-600 dark:text-teal-400">
                                    Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}
                                </span>
                            </div>
                            
                            @if($campaign->target_amount > 0)
                                @php
                                    $pct = $campaign->percentage_achieved;
                                @endphp
                                <div class="w-full h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-linear-to-r from-teal-500 to-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <div class="flex justify-between items-center text-[9px] font-medium text-gray-400">
                                    <span>Target: Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}</span>
                                    <span>{{ $pct }}%</span>
                                </div>
                            @else
                                <div class="w-full h-2 bg-teal-100/50 dark:bg-teal-950/20 rounded-full overflow-hidden">
                                    <div class="h-full bg-teal-500 rounded-full w-full"></div>
                                </div>
                                <div class="text-[9px] font-medium text-gray-400 text-right">
                                    Sumbangan Sukarela Abadi
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Footer Button -->
                    <div class="p-6 pt-0 border-t border-gray-50 dark:border-gray-800/80">
                        <a href="{{ route('portal.charity.donate', $campaign->id) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-3 bg-teal-600 hover:bg-teal-700 text-white text-xs font-black uppercase tracking-widest rounded-xl shadow-md shadow-teal-600/10 transition-all hover:shadow-lg hover:-translate-y-0.5 mt-4">
                            Berdonasi
                            <svg class="w-4 h-4 ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-[2.5rem] text-center text-gray-400 dark:text-gray-500 font-medium">
                    <div class="flex flex-col items-center justify-center space-y-3">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4a2 2 0 012-2m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                        </svg>
                        <span>Saat ini belum ada kampanye donasi aktif.</span>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
