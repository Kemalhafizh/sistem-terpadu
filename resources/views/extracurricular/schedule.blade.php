<x-app-layout>
    <x-slot name="title">Kalender Latihan & Jadwal Ekskul</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📅 Kalender Agenda & Latihan Ekskul
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Jadwal latihan rutin kesiswaan SMA Islam Sultan Agung 1 Semarang.
                </p>
            </div>
            <a href="{{ route('ekskul.index') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅️ Kembali ke Portal
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Grid Hari Kerja -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'] as $day)
                @php
                    $dayEkskuls = $extracurriculars->where('schedule_day', $day);
                @endphp
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4">
                    <div class="flex justify-between items-center pb-2 border-b border-gray-150 dark:border-gray-700/50">
                        <h3 class="font-black text-sm text-gray-800 dark:text-white">{{ $day }}</h3>
                        <span class="px-2 py-0.5 bg-indigo-50 dark:bg-indigo-950/20 text-indigo-700 dark:text-indigo-400 font-extrabold text-[9px] rounded-lg">
                            {{ $dayEkskuls->count() }} Kegiatan
                        </span>
                    </div>

                    <div class="space-y-3">
                        @forelse($dayEkskuls as $ek)
                            <div class="p-3 bg-gray-55/50 dark:bg-gray-900/50 rounded-2xl border border-gray-150 dark:border-gray-700/50 text-xs space-y-1.5 hover:border-indigo-400 transition-all">
                                <div class="font-bold text-gray-800 dark:text-white">{{ $ek->name }}</div>
                                <div class="text-[10px] text-gray-400">
                                    🕒 {{ $ek->schedule_time }}
                                </div>
                                <div class="text-[10px] text-gray-400">
                                    📍 {{ $ek->location }}
                                </div>
                                <div class="text-[9px] text-indigo-500 font-bold">
                                    👤 Pembina: {{ $ek->advisor->name ?? '-' }}
                                </div>
                            </div>
                        @empty
                            <p class="text-[11px] text-gray-400 italic text-center py-4">Tidak ada agenda latihan.</p>
                        @endforelse
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
