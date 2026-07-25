<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                        </svg>
                    </span>
                    Matriks Risiko 5x5 Color-Coded Heat Map (ERM Standard)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemetaan sebaran risiko institusi berdasarkan matriks Likelihood (Peluang) vs Impact (Dampak).</p>
            </div>
            <a href="{{ route('risk.register') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Risk Register
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Heat Map Legend -->
            <div class="flex flex-wrap items-center justify-end gap-3 text-xs font-mono font-bold">
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded">Low Risk (1 - 4)</span>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded">Medium Risk (5 - 9)</span>
                <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded">High Risk (10 - 14)</span>
                <span class="px-3 py-1 bg-rose-100 text-rose-800 rounded">Extreme Risk (15 - 25)</span>
            </div>

            <!-- 5x5 Heatmap Matrix Grid -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-center border-collapse border border-gray-200 dark:border-gray-700">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-900 text-gray-700 dark:text-gray-200 font-bold text-xs uppercase">
                                <th class="p-3 border border-gray-200 dark:border-gray-700 w-32">Likelihood \ Impact</th>
                                @foreach($matrixData['impacts'] as $imp)
                                    <th class="p-3 border border-gray-200 dark:border-gray-700 font-mono text-[11px]">{{ ucfirst($imp) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($matrixData['likelihoods'] as $lkh)
                                <tr>
                                    <td class="p-3 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 font-bold text-xs capitalize text-left">
                                        {{ str_replace('_', ' ', $lkh) }}
                                    </td>
                                    @foreach($matrixData['impacts'] as $imp)
                                        @php
                                            $cell = $matrixData['matrix'][$lkh][$imp];
                                            $level = $cell['level'];
                                            $bgClass = match($level) {
                                                'Extreme' => 'bg-rose-500/20 text-rose-900 dark:text-rose-200 border-rose-300',
                                                'High' => 'bg-amber-500/20 text-amber-900 dark:text-amber-200 border-amber-300',
                                                'Medium' => 'bg-blue-500/20 text-blue-900 dark:text-blue-200 border-blue-300',
                                                default => 'bg-emerald-500/20 text-emerald-900 dark:text-emerald-200 border-emerald-300',
                                            };
                                        @endphp
                                        <td class="p-4 border border-gray-200 dark:border-gray-700 {{ $bgClass }} min-w-[140px] align-top text-left space-y-2">
                                            <div class="flex justify-between items-center font-mono text-[10px] font-bold border-b border-gray-300/40 pb-1">
                                                <span>Skor: {{ $cell['score'] }}</span>
                                                <span>{{ strtoupper($level) }}</span>
                                            </div>
                                            <div class="space-y-1">
                                                @forelse($cell['risks'] as $r)
                                                    <span class="block px-2 py-1 bg-white/80 dark:bg-gray-900/80 rounded text-[10px] font-bold text-gray-800 dark:text-gray-200 truncate">
                                                        {{ $r->title }}
                                                    </span>
                                                @empty
                                                    <span class="text-[10px] italic opacity-50 block text-center py-2">- Kosong -</span>
                                                @endforelse
                                            </div>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
