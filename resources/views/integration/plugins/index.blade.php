<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a2 2 0 002 2h1a2 2 0 110 4h-1a2 2 0 00-2 2v1a2 2 0 11-4 0v-1a2 2 0 00-2-2H7a2 2 0 110-4h1a2 2 0 002-2V5a2 2 0 012-2z"></path>
                        </svg>
                    </span>
                    Third-Party Plugin Architecture & Extension Registry
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Arsitektur plugin modular dengan event-driven hooks tanpa mengubah core codebase.</p>
            </div>
            <a href="{{ route('plugins.marketplace') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
                Buka Plugin Marketplace
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Install Plugin Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Pasang Plugin Ekstensi Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Daftarkan paket ekstensi pihak ketiga untuk menambah fungsionalitas SISTA.</p>
                </div>

                <form action="{{ route('plugins.install') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Plugin</label>
                        <input type="text" name="name" required value="Modul Integrasi LMS Moodle Sync" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Slug Unik Plugin</label>
                        <input type="text" name="slug" required value="moodle-lms-sync" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Versi & Pengembang</label>
                        <input type="text" name="version" required value="1.2.0" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono">
                        <input type="hidden" name="author" value="Third-Party Dev Team">
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30">
                            Pasang Plugin Ke Sistem
                        </button>
                    </div>
                </form>
            </div>

            <!-- Installed Plugins Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($plugins as $p)
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4 flex flex-col justify-between">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-mono text-[10px] font-bold rounded">
                                    v{{ $p->version }}
                                </span>
                                <span class="px-2.5 py-0.5 {{ $p->is_enabled ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }} font-mono text-[10px] font-bold rounded uppercase">
                                    {{ $p->is_enabled ? 'ENABLED' : 'DISABLED' }}
                                </span>
                            </div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white">{{ $p->name }}</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-mono">Pengembang: {{ $p->author }}</p>
                        </div>

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center">
                            <span class="text-[11px] font-mono text-gray-400">{{ $p->hooks->count() }} Event Hooks</span>
                            <form action="{{ route('plugins.toggle', $p->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="enable" value="{{ $p->is_enabled ? '0' : '1' }}">
                                <button type="submit" class="px-3 py-1.5 {{ $p->is_enabled ? 'bg-rose-600 hover:bg-rose-700' : 'bg-emerald-600 hover:bg-emerald-700' }} text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                    {{ $p->is_enabled ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="md:col-span-3 p-12 bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 text-center text-gray-400 italic">
                        Belum ada plugin ekstensi terpasang.
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
