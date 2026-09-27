<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Penyusun Jadwal Visual (Drag-and-Drop)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Atur jadwal mata pelajaran, guru, dan ruangan dengan deteksi konflik otomatis.
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <button onclick="document.getElementById('saveModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    <span>Simpan Draft</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Drafts Bar -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm flex items-center justify-between overflow-x-auto">
            <div class="flex items-center space-x-3">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Draft Tersimpan:</span>
                @forelse($drafts as $d)
                    <span class="px-3 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-medium text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-700 flex items-center space-x-2">
                        <span>{{ $d->name }}</span>
                        <span class="w-2 h-2 rounded-full {{ $d->status === 'published' ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    </span>
                @empty
                    <span class="text-xs text-slate-400">Belum ada draft tersimpan.</span>
                @endforelse
            </div>
        </div>

        <!-- Main Schedule Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <!-- Sidebar: Available Subjects & Teachers -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-5 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                <h3 class="font-bold text-base text-slate-800 dark:text-white flex items-center">
                    <svg class="w-4 h-4 mr-2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Daftar Mata Pelajaran
                </h3>
                <div class="space-y-2 max-h-96 overflow-y-auto pr-1">
                    @foreach($subjects as $subj)
                        <div draggable="true" class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700 cursor-move hover:border-emerald-500 transition-all flex items-center justify-between">
                            <div>
                                <h4 class="font-semibold text-xs text-slate-900 dark:text-white">{{ $subj->name }}</h4>
                                <span class="text-[10px] text-slate-500">{{ $subj->code ?? 'MAPEL' }}</span>
                            </div>
                            <span class="px-2 py-0.5 rounded-lg bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 text-[10px] font-bold">Slot</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Schedule Canvas Grid -->
            <div class="lg:col-span-3 bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Grid Waktu Pembelajaran Mingguan</h3>
                    <span class="text-xs text-slate-500">{{ school_name() }}</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-500 dark:text-slate-400">
                                <th class="p-3">Jam / Hari</th>
                                <th class="p-3">Senin</th>
                                <th class="p-3">Selasa</th>
                                <th class="p-3">Rabu</th>
                                <th class="p-3">Kamis</th>
                                <th class="p-3">Jumat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                            @foreach(['07:00 - 08:30', '08:30 - 10:00', '10:15 - 11:45', '12:30 - 14:00'] as $timeSlot)
                                <tr>
                                    <td class="p-3 font-semibold text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/40 rounded-xl whitespace-nowrap">{{ $timeSlot }}</td>
                                    @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day)
                                        <td class="p-2">
                                            <div class="min-h-[70px] rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-700/80 p-2 flex flex-col items-center justify-center text-slate-400 hover:border-emerald-500 transition-colors">
                                                <span class="text-[10px]">Tarik Mapel ke Sini</span>
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
