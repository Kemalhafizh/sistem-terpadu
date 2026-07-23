<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Progres Gamifikasi Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">
            <!-- Grid Atas: Header Informasi Personal & Streak Card -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Card 1: Level & XP Detail -->
                <div class="bg-gradient-to-br from-indigo-900 to-indigo-950 text-white rounded-[2rem] p-8 shadow-xl relative overflow-hidden group border border-indigo-500/20 col-span-1 lg:col-span-2 flex flex-col justify-between">
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-indigo-500 rounded-full opacity-10 blur-xl"></div>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-white/10 rounded-2xl flex items-center justify-center text-3xl font-black shadow-lg">
                                
                            </div>
                            <div>
                                <h3 class="text-2xl font-black tracking-tight text-indigo-300">Level {{ $student->level }}</h3>
                                <p class="text-xs text-indigo-200 font-bold uppercase tracking-widest">{{ $student->name }}</p>
                            </div>
                        </div>
                        
                        <!-- Progress Bar Ke Level Berikutnya -->
                        <div class="space-y-2 pt-4">
                            <div class="flex justify-between text-xs font-black uppercase tracking-wider text-indigo-200">
                                <span>Kemajuan Level</span>
                                <span>{{ $student->total_xp % 100 }} / 100 XP ({{ $student->xp_progress_percent }}%)</span>
                            </div>
                            <div class="w-full bg-slate-800 rounded-full h-4 overflow-hidden border border-indigo-500/20 p-0.5 shadow-inner">
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-full rounded-full shadow-lg shadow-indigo-500/40" style="width: {{ $student->xp_progress_percent }}%"></div>
                            </div>
                            <p class="text-[10px] text-indigo-300 font-medium italic">Kumpulkan {{ 100 - ($student->total_xp % 100) }} XP lagi untuk naik ke Level {{ $student->level + 1 }}!</p>
                        </div>
                    </div>

                    <div class="pt-6 flex gap-6 text-center border-t border-indigo-500/20 mt-6 lg:mt-0">
                        <div class="flex-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Kumulatif XP</p>
                            <p class="text-2xl font-black text-white mt-1">{{ $student->total_xp }} <span class="text-xs text-indigo-300">XP</span></p>
                        </div>
                        <div class="w-px bg-indigo-500/20"></div>
                        <div class="flex-1">
                            <p class="text-[10px] font-black uppercase tracking-widest text-indigo-300">Lencana Koleksi</p>
                            <p class="text-2xl font-black text-white mt-1">{{ $student->achievements->count() }} <span class="text-xs text-indigo-300">Badges</span></p>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Streak Kehadiran ( Flame Card) -->
                <div class="bg-white rounded-[2rem] p-8 shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center relative overflow-hidden group">
                    <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-orange-100 rounded-full opacity-20 blur-xl"></div>
                    <p class="text-[11px] font-black uppercase tracking-[0.2em] text-gray-400 mb-6">Konsistensi Kehadiran</p>
                    
                    <div class="w-32 h-32 rounded-full bg-gradient-to-br {{ $student->current_streak > 0 ? 'from-orange-100 to-amber-100' : 'from-slate-100 to-slate-200' }} flex items-center justify-center mb-6 shadow-md shadow-orange-500/5 group-hover:scale-110 transition-transform duration-300">
                        <span class="text-6xl animate-pulse"></span>
                    </div>

                    <div class="space-y-1">
                        <h4 class="text-3xl font-extrabold text-gray-900">{{ $student->current_streak }} Hari</h4>
                        <p class="text-xs font-bold text-orange-600 uppercase tracking-wide">Streak Kehadiran Aktif</p>
                    </div>

                    <div class="mt-6 pt-4 border-t border-gray-50 w-full flex justify-between text-xs text-gray-500 font-bold">
                        <span>Rekor Terpanjang:</span>
                        <span class="text-gray-900"> {{ $student->longest_streak }} Hari</span>
                    </div>
                </div>

            </div>

            <!-- Grid Bawah: Recent Achievements & XP Logs -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Bagian Kiri: 3 Lencana Terbaru (1/3) -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h4 class="text-lg font-black text-gray-900 flex items-center">
                            <span class="w-2.5 h-6 bg-purple-600 rounded-full mr-3"></span>
                            Lencana Terbaru
                        </h4>
                        <a href="{{ route('gamification.achievements') }}" class="text-xs font-black text-indigo-600 hover:underline">Semua →</a>
                    </div>

                    <div class="flex-1 flex flex-col justify-center space-y-4">
                        @forelse($student->achievements->take(3) as $sa)
                            <div class="p-4 bg-purple-50/50 hover:bg-purple-50 transition-colors border border-purple-100 rounded-2xl flex items-center gap-4">
                                <div class="w-12 h-12 bg-white rounded-xl shadow-md border border-purple-100 flex items-center justify-center text-2xl">
                                    {{ $sa->definition->icon }}
                                </div>
                                <div class="flex-grow">
                                    <h5 class="text-sm font-black text-gray-900">{{ $sa->definition->name }}</h5>
                                    <p class="text-[10px] text-purple-700 font-bold uppercase tracking-wider mt-0.5">{{ $sa->earned_at->translatedFormat('d M Y') }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-12 text-center opacity-50">
                                <span class="text-4xl mb-3"></span>
                                <p class="text-xs font-bold text-gray-500 leading-relaxed">Belum ada lencana terbuka.<br>Kerjakan tugas dan presensi untuk membuka!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Bagian Kanan: Log Aktivitas XP Terbaru (2/3) -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 lg:col-span-2">
                    <h4 class="text-lg font-black text-gray-900 mb-6 flex items-center">
                        <span class="w-2.5 h-6 bg-indigo-600 rounded-full mr-3"></span>
                        Riwayat Penambahan XP
                    </h4>

                    <div class="space-y-4">
                        @forelse($student->xpLogs as $log)
                            <div class="p-4 bg-gray-50/80 rounded-2xl flex items-center justify-between hover:bg-gray-50 transition-colors border border-gray-100/50">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 {{ $log->xp_amount > 0 ? 'bg-indigo-100 text-indigo-600' : 'bg-red-100 text-red-600' }} rounded-xl flex items-center justify-center font-extrabold shadow-sm">
                                        +{{ $log->xp_amount }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-black text-gray-900">{{ $log->description }}</p>
                                        <div class="mt-1 flex items-center gap-2 text-[10px] text-gray-400 font-bold uppercase tracking-tight">
                                            <span>Sumber: {{ $log->source }}</span>
                                            <span>•</span>
                                            <span>{{ $log->created_at->diffForHumans() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs font-black text-indigo-500 whitespace-nowrap bg-white px-3 py-1.5 rounded-lg border border-gray-100 shadow-sm">
                                    +{{ $log->xp_amount }} XP
                                </span>
                            </div>
                        @empty
                            <div class="flex flex-col items-center justify-center py-16 text-center opacity-50">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-xs font-bold text-gray-500">Belum ada riwayat aktivitas XP.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
