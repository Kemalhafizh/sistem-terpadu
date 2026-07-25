<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </span>
                    {{ $sop->code }}: {{ $sop->title }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pembacaan dokumen Prosedur Operasional Standar resmi (Versi {{ $sop->version }}.0).</p>
            </div>
            <a href="{{ route('sop.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Katalog SOP
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto space-y-6">

            <!-- Formal SOP Document Paper Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-lg p-8 space-y-6">

                <!-- SOP Document Formal Header Header Seal -->
                <div class="border-b border-gray-200 dark:border-gray-700 pb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="space-y-1">
                        <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-mono text-[10px] font-bold rounded uppercase">
                            KATEGORI: {{ $sop->category }}
                        </span>
                        <h1 class="text-xl font-black text-gray-900 dark:text-white leading-snug">{{ $sop->title }}</h1>
                        <p class="text-xs font-mono text-gray-500">Kode Dokumen: {{ $sop->code }} | Versi: {{ $sop->version }}.0</p>
                    </div>

                    <div class="text-right text-xs font-mono space-y-1 shrink-0">
                        <p><span class="text-gray-400">Efektif:</span> <span class="font-bold text-gray-700 dark:text-gray-300">{{ $sop->effective_date->format('d M Y') }}</span></p>
                        <p><span class="text-gray-400">Penyusun:</span> <span class="font-bold text-gray-700 dark:text-gray-300">{{ optional($sop->author)->name }}</span></p>
                        <p><span class="text-gray-400">Peninjauan:</span> <span class="font-bold text-gray-700 dark:text-gray-300">{{ $sop->review_date->format('d M Y') }}</span></p>
                    </div>
                </div>

                <!-- SOP HTML Content Body -->
                <div class="prose dark:prose-invert max-w-none font-sans text-sm leading-relaxed whitespace-pre-line text-gray-800 dark:text-gray-200 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-gray-100 dark:border-gray-800">
                    {!! nl2br(e($sop->content_html)) !!}
                </div>

                <!-- Acknowledgement Action Card -->
                <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold text-gray-700 dark:text-gray-300">Tingkat Kepatuhan Staf (Compliance Rate)</p>
                        <p class="text-sm font-mono font-black text-emerald-600 dark:text-emerald-400">{{ $complianceRate }}% Staf Telah Menandatangani</p>
                    </div>

                    <div>
                        @if($isAcknowledged)
                            <div class="px-4 py-2 bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 rounded-xl text-xs font-bold font-mono flex items-center gap-2 border border-emerald-300">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Anda Telah Membaca & Menyetujui SOP Ini
                            </div>
                        @else
                            <form action="{{ route('sop.acknowledge', $sop->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    Saya Telah Membaca & Memahami SOP Ini
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
