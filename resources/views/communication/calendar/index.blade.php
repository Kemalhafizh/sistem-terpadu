<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </span>
                    Enterprise Calendar & Academic Schedule
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sistem kalender terpadu untuk koordinasi akademik, rapat guru, upacara, dan kegiatan sekolah.</p>
            </div>
            <a href="{{ route('resource_bookings.index') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Manajemen Reservasi Aset & Ruangan
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Category Filter Badges -->
            <div class="flex flex-wrap items-center gap-3 bg-white dark:bg-gray-800 p-4 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-xs font-bold">
                <span class="text-gray-400 uppercase font-mono text-[10px]">Kategori Jadwal:</span>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-lg">Akademik (#3B82F6)</span>
                <span class="px-3 py-1 bg-red-100 text-red-800 rounded-lg">Ujian (#EF4444)</span>
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-lg">Libur Resmi (#10B981)</span>
                <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-lg">Upacara (#F59E0B)</span>
                <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-lg">Rapat (#8B5CF6)</span>
            </div>

            <!-- Create Event Modal / Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Tambah Jadwal Acara Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Buat jadwal agenda akademik, rapat, atau upacara baru.</p>
                </div>

                <form action="{{ route('calendar.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Judul Acara / Agenda</label>
                        <input type="text" name="title" required placeholder="Rapat Koordinasi PAS Semester Genap" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Kategori Event</label>
                        <select name="event_type" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="academic">academic (Akademik)</option>
                            <option value="exam">exam (Ujian / Evaluation)</option>
                            <option value="meeting">meeting (Rapat Guru / Staff)</option>
                            <option value="ceremony">ceremony (Upacara / Kajian)</option>
                            <option value="holiday">holiday (Libur Nasional)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Waktu Mulai</label>
                        <input type="datetime-local" name="start_datetime" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Waktu Selesai</label>
                        <input type="datetime-local" name="end_datetime" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Lokasi / Ruangan</label>
                        <input type="text" name="location" placeholder="Ruang Rapat YBWSA / Aula Utama" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Visibilitas Hak Akses</label>
                        <select name="visibility" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="public">public (Seluruh Warga Sekolah)</option>
                            <option value="staff">staff (Guru & Staff Saja)</option>
                            <option value="private">private (Pribadi)</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <button type="submit" class="w-full px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all">
                            Simpan ke Kalender Sekolah
                        </button>
                    </div>
                </form>
            </div>

            <!-- Agenda Events Grid View -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Agenda Acara Terdaftar</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Daftar agenda kegiatan sekolah dalam rentang waktu terpilih.</p>
                    </div>
                </div>

                <div class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($events as $ev)
                        <div class="p-6 flex flex-col md:flex-row items-start justify-between gap-4 hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                            <div class="flex items-start gap-4">
                                <div class="w-3 h-12 rounded-full shrink-0" style="background-color: {{ $ev->color_hex }}"></div>
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded font-mono text-[10px] font-bold uppercase text-white" style="background-color: {{ $ev->color_hex }}">
                                            {{ $ev->event_type }}
                                        </span>
                                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $ev->title }}</h4>
                                    </div>
                                    <p class="text-xs text-gray-500 font-mono">
                                        Waktu: {{ $ev->start_datetime->format('d M Y H:i') }} - {{ $ev->end_datetime->format('H:i') }}
                                        @if($ev->location) | Lokasi: {{ $ev->location }} @endif
                                    </p>
                                    <p class="text-xs text-gray-400">Penyelenggara: {{ optional($ev->organizer)->name }} (Akses: {{ strtoupper($ev->visibility) }})</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 self-end md:self-center">
                                <form action="{{ route('calendar.rsvp', $ev->id) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="accepted">
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 rounded-lg text-xs font-bold hover:bg-emerald-200 transition-all">
                                        Hadir (RSVP)
                                    </button>
                                </form>

                                @if($ev->organizer_id === auth()->id() || auth()->user()->hasRole(['superadmin', 'admin']))
                                    <form action="{{ route('calendar.destroy', $ev->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus acara ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-500/10 text-rose-600 dark:text-rose-400 hover:bg-rose-500/20 rounded-lg text-xs font-bold transition-all">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-400 italic text-xs">
                            Belum ada jadwal acara terdaftar pada rentang waktu ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
