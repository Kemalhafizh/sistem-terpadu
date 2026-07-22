<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-bold leading-tight text-gray-800 dark:text-white">
            {{ __('UAT Feedback Report & Analytics') }}
        </h2>
    </x-slot>

    <x-slot name="breadcrumbs">
        <nav class="flex text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">Dashboard</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ route('admin.uat.feedback.dashboard') }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">UAT Feedback</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 md:ml-2 text-gray-700 dark:text-gray-300">Analytics Report</span>
                    </div>
                </li>
            </ol>
        </nav>
    </x-slot>

    <div class="space-y-8">
        
        <!-- Header Actions -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl p-6 rounded-3xl border border-white/20 dark:border-gray-700/50 shadow-lg">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Laporan Kualitatif Pengujian Pengguna (UAT)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Analisis feedback yang masuk untuk mengukur kepuasan civitas akademika SMA ISA 1 Semarang.</p>
            </div>
            <a href="{{ route('admin.uat.feedback.dashboard') }}" 
               class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path></svg>
                Kelola Feedback
            </a>
        </div>

        <!-- Key Metrics Header Card Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Total Feedbacks -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-600/10 border border-emerald-500/20 text-emerald-500 flex items-center justify-center text-xl shrink-0">
                    
                </div>
                <div>
                    <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider">Total Laporan Masuk</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-0.5">{{ $totalFeedback }}</h3>
                    <p class="text-[10px] text-gray-500 font-semibold mt-0.5">Dari seluruh civitas akademika</p>
                </div>
            </div>

            <!-- Average Satisfaction Rating -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-500 flex items-center justify-center text-xl shrink-0">
                    
                </div>
                <div>
                    <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider">Skor Kepuasan Pengguna</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-0.5">{{ number_format($avgRating, 1) }} / 5.0</h3>
                    <div class="flex gap-0.5 mt-0.5 text-amber-400 text-xs">
                        @for($i = 1; $i <= 5; $i++)
                            <span>{{ $avgRating >= $i ? '' : ($avgRating >= $i - 0.5 ? '½' : '') }}</span>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Resolution Rate -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50 flex items-center space-x-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-500 flex items-center justify-center text-xl shrink-0">
                    
                </div>
                @php
                    $resolvedCount = ($byStatus['fixed']['count'] ?? 0) + ($byStatus['verified']['count'] ?? 0);
                    $resolutionRate = $totalFeedback > 0 ? round(($resolvedCount / $totalFeedback) * 100, 1) : 0;
                @endphp
                <div>
                    <span class="text-xs font-bold text-gray-400 block uppercase tracking-wider">Tingkat Penyelesaian</span>
                    <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-0.5">{{ $resolutionRate }}%</h3>
                    <p class="text-[10px] text-gray-500 font-semibold mt-0.5">{{ $resolvedCount }} dari {{ $totalFeedback }} terselesaikan</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Category and Priority Chart mockups (Left) -->
            <div class="lg:col-span-8 space-y-6">
                <!-- Breakdown By Category -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50">
                    <h4 class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-6">Breakdown Kategori Masukan</h4>
                    
                    <div class="space-y-4">
                        @foreach([
                            'bug' => ['label' => 'Bug / Galat Sistem ', 'color' => 'bg-red-500'],
                            'ui_suggestion' => ['label' => 'Saran Tampilan / UI ', 'color' => 'bg-sky-500'],
                            'unclear_feature' => ['label' => 'Fitur Kurang Jelas ', 'color' => 'bg-amber-500'],
                            'slow' => ['label' => 'Lambat / Performa ', 'color' => 'bg-orange-500'],
                            'other' => ['label' => 'Lainnya ', 'color' => 'bg-gray-500'],
                        ] as $key => $meta)
                            @php
                                $item = $byCategory[$key] ?? ['count' => 0, 'percentage' => 0];
                            @endphp
                            <div class="space-y-1.5">
                                <div class="flex justify-between items-center text-xs font-semibold text-gray-800 dark:text-gray-200">
                                    <span>{{ $meta['label'] }}</span>
                                    <span>{{ $item['count'] }} Laporan ({{ $item['percentage'] }}%)</span>
                                </div>
                                <div class="w-full bg-gray-100 dark:bg-gray-950 rounded-full h-2">
                                    <div class="{{ $meta['color'] }} h-2 rounded-full" style="width: {{ $item['percentage'] }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Top Pages Impacted -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50">
                    <h4 class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-4">Top 5 Halaman dengan Laporan Bug Terbanyak</h4>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400 font-bold uppercase tracking-wider">
                                    <th class="py-2.5 px-3">Path Halaman</th>
                                    <th class="py-2.5 px-3 text-center">Jumlah Laporan</th>
                                    <th class="py-2.5 px-3 text-center">Rating Kepuasan Halaman</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800/50 font-semibold text-gray-700 dark:text-gray-200">
                                @forelse($topPages as $page)
                                    <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/10">
                                        <td class="py-3 px-3 font-mono text-emerald-500 hover:text-emerald-400 break-all select-all">
                                            {{ Str::replace(url('/'), '', $page->page_url) }}
                                        </td>
                                        <td class="py-3 px-3 text-center text-gray-900 dark:text-white font-bold">
                                            {{ $page->total }} Laporan
                                        </td>
                                        <td class="py-3 px-3 text-center text-amber-500 font-bold">
                                            {{ number_format($page->avg_rating, 1) }} 
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-6 text-gray-500 font-semibold">
                                            Belum ada laporan UAT masuk.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Status & Priority Widgets (Right) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Breakdown By Status -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50">
                    <h4 class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-5">Status Tindak Lanjut</h4>
                    
                    <div class="space-y-3">
                        @foreach([
                            'open' => ['label' => 'Open (Terbuka)', 'color' => 'text-amber-500'],
                            'in_progress' => ['label' => 'In Progress (Diproses)', 'color' => 'text-purple-500'],
                            'fixed' => ['label' => 'Fixed (Diperbaiki)', 'color' => 'text-blue-500'],
                            'verified' => ['label' => 'Verified (Terverifikasi)', 'color' => 'text-emerald-500'],
                            'wont_fix' => ['label' => "Won't Fix (Diabaikan)", 'color' => 'text-gray-500'],
                        ] as $key => $meta)
                            @php
                                $item = $byStatus[$key] ?? ['count' => 0, 'percentage' => 0];
                            @endphp
                            <div class="flex items-center justify-between p-2.5 bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-900 rounded-xl">
                                <span class="text-xs font-bold text-gray-700 dark:text-gray-300">{{ $meta['label'] }}</span>
                                <div class="text-right">
                                    <span class="text-xs font-black {{ $meta['color'] }}">{{ $item['count'] }}</span>
                                    <span class="text-[10px] text-gray-500 font-semibold block">({{ $item['percentage'] }}%)</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Breakdown By Priority -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-6 shadow-xl border border-white/20 dark:border-gray-700/50">
                    <h4 class="text-xs font-black uppercase tracking-widest text-emerald-600 dark:text-emerald-400 mb-5">Prioritas Bug & Isu</h4>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <!-- Critical -->
                        <div class="p-3 bg-red-500/10 border border-red-500/25 rounded-2xl text-center">
                            <span class="text-[9px] font-black uppercase tracking-wider text-red-500">Critical </span>
                            <h4 class="text-xl font-black text-red-600 dark:text-red-400 mt-1">{{ $byPriority['critical'] ?? 0 }}</h4>
                        </div>

                        <!-- High -->
                        <div class="p-3 bg-orange-500/10 border border-orange-500/25 rounded-2xl text-center">
                            <span class="text-[9px] font-black uppercase tracking-wider text-orange-500">High </span>
                            <h4 class="text-xl font-black text-orange-600 dark:text-orange-400 mt-1">{{ $byPriority['high'] ?? 0 }}</h4>
                        </div>

                        <!-- Medium -->
                        <div class="p-3 bg-amber-500/10 border border-amber-500/25 rounded-2xl text-center">
                            <span class="text-[9px] font-black uppercase tracking-wider text-amber-500">Medium </span>
                            <h4 class="text-xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $byPriority['medium'] ?? 0 }}</h4>
                        </div>

                        <!-- Low -->
                        <div class="p-3 bg-emerald-500/10 border border-emerald-500/25 rounded-2xl text-center">
                            <span class="text-[9px] font-black uppercase tracking-wider text-emerald-500">Low </span>
                            <h4 class="text-xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $byPriority['low'] ?? 0 }}</h4>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
