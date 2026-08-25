<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Majalah Digital & Buletin Sekolah') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Platform publikasi artikel, karya siswa, dan buletin berkala SMA Islam Sultan Agung 1 Semarang.
                </p>
            </div>
            <button onclick="document.getElementById('newMagazineModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Terbitkan Edisi Baru</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Magazine Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($magazines as $mag)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4 hover:border-emerald-500 transition-all flex flex-col justify-between">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                                Edisi #{{ $mag->issue_number }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $mag->created_at->format('d M Y') }}</span>
                        </div>
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white leading-snug">{{ $mag->title }}</h3>
                        <p class="text-xs text-slate-500">Penulis: {{ $mag->author->name ?? 'Redaksi' }}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between">
                        <span class="text-xs text-slate-400">Status: {{ ucfirst($mag->status) }}</span>
                        <a href="#" class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 text-xs font-bold hover:bg-emerald-100 transition-colors">
                            Baca Majalah
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Belum Ada Edisi Majalah Digital</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto">Mulai menulis dan menerbitkan artikel buletin sekolah untuk seluruh civitas akademika.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
