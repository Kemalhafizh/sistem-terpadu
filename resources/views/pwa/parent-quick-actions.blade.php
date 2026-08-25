<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Aksi Cepat Orang Tua') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ $data['parent_name'] ?? 'Wali Murid' }} &bull; SMA Islam Sultan Agung 1 Semarang
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Children Cards -->
        @foreach($data['children'] ?? [] as $child)
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">{{ $child['classroom'] }}</span>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ $child['name'] }}</h3>
                        <p class="text-xs text-slate-500">NIS: {{ $child['nis'] }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $child['today_status'] === 'hadir' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300' }}">
                        {{ ucfirst($child['today_status']) }}
                    </span>
                </div>

                <!-- Unpaid Bills Banner -->
                @if($child['unpaid_bills_total'] > 0)
                    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900 flex items-center justify-between">
                        <div>
                            <span class="text-xs text-amber-700 dark:text-amber-300">Tagihan SPP Belum Terbayar:</span>
                            <div class="text-lg font-bold text-amber-900 dark:text-amber-200">Rp {{ number_format($child['unpaid_bills_total'], 0, ',', '.') }}</div>
                        </div>
                        <a href="{{ Route::has('parent.billings') ? route('parent.billings') : (Route::has('parent.bills.index') ? route('parent.bills.index') : '#') }}" class="px-3.5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold transition-colors">
                            Bayar Sekarang
                        </a>
                    </div>
                @endif

                <!-- One-Tap Action Buttons -->
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <a href="{{ Route::has('parent.attendance.index') ? route('parent.attendance.index') : (Route::has('parent.attendances.index') ? route('parent.attendances.index') : '#') }}" class="py-2.5 px-3 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-medium border border-slate-200 dark:border-slate-700 flex items-center justify-center space-x-1.5 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>Izin / Sakit</span>
                    </a>
                    <a href="{{ Route::has('parent.messages.index') ? route('parent.messages.index') : '#' }}" class="py-2.5 px-3 rounded-2xl bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-medium border border-slate-200 dark:border-slate-700 flex items-center justify-center space-x-1.5 hover:bg-cyan-50 hover:text-cyan-700 hover:border-cyan-200 transition-all">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <span>Chat Wali Kelas</span>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
</x-app-layout>
