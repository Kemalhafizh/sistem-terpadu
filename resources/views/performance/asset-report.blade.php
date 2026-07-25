<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.596 15.12a2 2 0 00-1.022.547l-1.42 1.42a2 2 0 00.586 3.414l2.828.943a18 18 0 0010.876 0l2.828-.943a2 2 0 00.586-3.414l-1.42-1.42z"></path>
                        </svg>
                    </span>
                    Asset Optimization & Core Web Vitals Audit
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Laporan analitis optimasi bundle JS/CSS, Brotli compression, dan metrik Core Web Vitals (LCP, FID, CLS).</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Grade Card -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 rounded-3xl text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 border border-slate-700">
                <div class="space-y-2 text-center md:text-left">
                    <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-mono text-[10px] font-bold uppercase rounded-xl">
                        CORE WEB VITALS AUDIT
                    </span>
                    <h3 class="text-3xl font-black tracking-tight">{{ $report['performance_grade'] }}</h3>
                    <p class="text-xs text-gray-300">Vite Code Splitting & Brotli Asset Compression Active</p>
                </div>

                <div class="grid grid-cols-3 gap-4 text-center bg-slate-800/80 p-6 rounded-2xl border border-slate-700/80 shrink-0">
                    <div>
                        <span class="text-[10px] font-mono text-gray-400 uppercase block">LCP</span>
                        <span class="text-xl font-bold text-emerald-400 font-mono">{{ $report['core_web_vitals']['lcp_seconds'] }}s</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-mono text-gray-400 uppercase block">FID</span>
                        <span class="text-xl font-bold text-emerald-400 font-mono">{{ $report['core_web_vitals']['fid_ms'] }}ms</span>
                    </div>
                    <div>
                        <span class="text-[10px] font-mono text-gray-400 uppercase block">CLS</span>
                        <span class="text-xl font-bold text-emerald-400 font-mono">{{ $report['core_web_vitals']['cls_score'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Bundle Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">Ukuran Bundle JavaScript & CSS</h4>
                    <div class="flex justify-between items-center font-mono text-xs p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                        <span>JS Minified Bundle:</span>
                        <span class="font-bold text-indigo-600">{{ $report['js_bundle_size_kb'] }} KB</span>
                    </div>
                    <div class="flex justify-between items-center font-mono text-xs p-3 bg-gray-50 dark:bg-gray-900 rounded-xl">
                        <span>CSS Purged Bundle:</span>
                        <span class="font-bold text-indigo-600">{{ $report['css_bundle_size_kb'] }} KB</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-3">
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">Rekomendasi Optimasi Lanjutan</h4>
                    <ul class="space-y-2 text-xs text-gray-600 dark:text-gray-300 font-medium">
                        @foreach($report['recommendations'] as $rec)
                            <li class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span>
                                {{ $rec }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
