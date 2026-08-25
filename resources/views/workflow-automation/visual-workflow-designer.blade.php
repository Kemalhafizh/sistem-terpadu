<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Desainer Alur Kerja Visual (BPMN Workflow Designer)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Rancang logika persetujuan otomatis, routing paralel, dan eskalasi deadline dokumen sekolah.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Blueprint Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($blueprints as $bp)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4 hover:border-emerald-500 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                            {{ ucfirst(str_replace('_', ' ', $bp->process_type)) }}
                        </span>
                        <span class="w-2.5 h-2.5 rounded-full {{ $bp->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ $bp->name }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Dibuat oleh: {{ $bp->creator->name ?? 'Admin' }}</p>
                    </div>

                    <!-- Flow Nodes Mini Map -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/60 rounded-2xl border border-slate-100 dark:border-slate-700 space-y-2">
                        <span class="text-[10px] font-bold text-slate-400 uppercase">Tahapan Alur:</span>
                        <div class="flex items-center space-x-1.5 overflow-x-auto text-[10px]">
                            @foreach($bp->nodes as $node)
                                <span class="px-2 py-1 bg-white dark:bg-slate-700 rounded-lg text-slate-700 dark:text-slate-200 whitespace-nowrap shadow-2xs font-semibold">
                                    {{ $node->node_name }}
                                </span>
                                @if(!$loop->last)
                                    <span class="text-slate-400">→</span>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Belum Ada Blueprint Alur Kerja</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto">Rancang alur persetujuan dokumen seperti RKAS, surat keputusan, dan pengadaan barang secara otomatis.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
