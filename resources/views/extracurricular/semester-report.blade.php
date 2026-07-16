<x-app-layout>
    <x-slot name="title">Laporan Semester Ekskul - {{ $extracurricular->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📊 Laporan Semester Kesiswaan
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Ekskul: {{ $extracurricular->name }} | Pembina: {{ $extracurricular->advisor->name ?? '-' }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('ekskul.export-pdf', $extracurricular->id) }}" class="bg-rose-600 hover:bg-rose-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    📕 Ekspor Laporan PDF
                </a>
                <a href="{{ route('ekskul.manage') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                    ⬅️ Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Rincian Pembina & Info Ekskul -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
            <h3 class="font-black text-sm text-gray-850 dark:text-white">🏫 Profil & Agenda Latihan</h3>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                <div class="p-3 bg-gray-55/50 dark:bg-gray-900/50 rounded-2xl">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Nama Ekskul</span>
                    <p class="font-extrabold text-gray-800 dark:text-white mt-1">{{ $extracurricular->name }}</p>
                </div>
                <div class="p-3 bg-gray-55/50 dark:bg-gray-900/50 rounded-2xl">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Guru Pembina</span>
                    <p class="font-extrabold text-gray-800 dark:text-white mt-1">{{ $extracurricular->advisor->name ?? '-' }}</p>
                </div>
                <div class="p-3 bg-gray-55/50 dark:bg-gray-900/50 rounded-2xl">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Hari & Waktu</span>
                    <p class="font-extrabold text-gray-800 dark:text-white mt-1">{{ $extracurricular->schedule_day }}, {{ $extracurricular->schedule_time }}</p>
                </div>
                <div class="p-3 bg-gray-55/50 dark:bg-gray-900/50 rounded-2xl">
                    <span class="block text-[9px] text-gray-400 font-bold uppercase">Tempat Latihan</span>
                    <p class="font-extrabold text-gray-800 dark:text-white mt-1">{{ $extracurricular->location }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 1. Daftar Anggota Aktif -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm">👥 Daftar Anggota Aktif</h3>
                </div>
                <div class="overflow-y-auto max-h-[300px] divide-y divide-gray-100 dark:divide-gray-700/50 p-6 space-y-3">
                    @forelse($extracurricular->activeMembers as $mb)
                        <div class="flex justify-between items-center text-xs pt-2 first:pt-0">
                            <div>
                                <span class="font-bold text-gray-800 dark:text-white">{{ $mb->student->name }}</span>
                                <p class="text-[9px] text-gray-400 mt-0.5">NISN: {{ $mb->student->nisn ?? '-' }} | Kelas: {{ $mb->student->classroom->name ?? '-' }}</p>
                            </div>
                            <span class="px-2 py-0.5 bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 font-extrabold text-[9px] rounded-lg">
                                {{ strtoupper($mb->role) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-6">Belum ada anggota aktif.</p>
                    @endforelse
                </div>
            </div>

            <!-- 2. Rincian Prestasi Lomba -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                    <h3 class="font-bold text-gray-800 dark:text-white text-sm">🏆 Prestasi Lomba & Penghargaan</h3>
                </div>
                <div class="overflow-y-auto max-h-[300px] divide-y divide-gray-100 dark:divide-gray-700/50 p-6 space-y-3">
                    @forelse($extracurricular->achievements as $ac)
                        <div class="flex justify-between items-center text-xs pt-2 first:pt-0">
                            <div>
                                <span class="font-bold text-gray-800 dark:text-white">{{ $ac->title }}</span>
                                <p class="text-[9px] text-gray-400 mt-0.5">Organizer: {{ $ac->organizer }} | {{ $ac->event_date->format('d M Y') }}</p>
                            </div>
                            <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400 font-extrabold text-[9px] rounded-lg">
                                {{ strtoupper($ac->level) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 italic text-center py-6">Belum ada prestasi tercatat.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 3. Riwayat Dokumentasi Kegiatan -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">🖼️ Dokumentasi Agenda Latihan</h3>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-700/50 p-6 space-y-4 max-h-[300px] overflow-y-auto pr-2">
                @forelse($extracurricular->activities as $act)
                    <div class="pt-3 first:pt-0">
                        <div class="flex justify-between text-[10px] text-gray-400 font-bold mb-1">
                            <span>📅 {{ $act->activity_date->format('d M Y') }}</span>
                            <span class="font-bold text-gray-800 dark:text-white">{{ $act->title }}</span>
                        </div>
                        <p class="text-xs text-gray-700 dark:text-gray-300 italic mt-1 bg-gray-50/50 dark:bg-gray-900/30 p-3 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                            {{ $act->description }}
                        </p>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 italic text-center py-6">Belum ada dokumentasi kegiatan latihan rutin.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
