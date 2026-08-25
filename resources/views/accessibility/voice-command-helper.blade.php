<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Bantuan Navigasi Suara (Voice Navigation)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kamus perintah suara Web Speech API untuk aksesibilitas tanpa sentuhan (hands-free).
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Voice Command Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($commands as $cmd)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-3 hover:border-emerald-500 transition-all">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-[10px] font-bold uppercase text-slate-400">Ucapkan:</span>
                        <h4 class="font-bold text-slate-900 dark:text-white text-base">"{{ $cmd['command_phrase'] }}"</h4>
                    </div>
                    <div class="pt-2 border-t border-slate-100 dark:border-slate-800 text-xs text-slate-500 flex justify-between">
                        <span>Aksi: {{ $cmd['target_module'] }}</span>
                        <span class="font-mono text-emerald-600">{{ $cmd['action_url'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
