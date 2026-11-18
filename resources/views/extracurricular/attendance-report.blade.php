<x-app-layout>
    <x-slot name="title">Laporan Presensi - {{ $extracurricular->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Laporan Absensi Latihan Rutin
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Ekskul: {{ $extracurricular->name }} | Pembina: {{ $extracurricular->advisor->name ?? '-' }}
                </p>
            </div>
            <a href="{{ route('ekskul.manage') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Grid Stat Ringkas -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Anggota Aktif</span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $extracurricular->activeMembers->count() }} orang</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex items-center gap-4">
                <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center text-xl font-bold">
                    
                </div>
                <div>
                    <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Rata-rata Kehadiran Anggota</span>
                    @php
                        $avgRate = count($attendanceSummary) > 0 ? round(array_sum(array_column($attendanceSummary, 'rate')) / count($attendanceSummary), 1) : 0;
                    @endphp
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white mt-0.5">{{ $avgRate }}%</h3>
                </div>
            </div>
        </div>

        <!-- Tabel Kehadiran Anggota -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Rekap Presensi Per Siswa</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Siswa / NISN</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Hadir (H)</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Sakit (S)</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Izin (I)</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Alpa (A)</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Rasio Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($attendanceSummary as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 dark:text-white">{{ $row['student']->name }}</div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">NISN: {{ $row['student']->nisn ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-800 dark:text-white">
                                    {{ $row['hadir'] }}
                                </td>
                                <td class="px-6 py-4 text-center text-blue-600 dark:text-blue-400">
                                    {{ $row['sakit'] }}
                                </td>
                                <td class="px-6 py-4 text-center text-amber-600 dark:text-amber-400">
                                    {{ $row['izin'] }}
                                </td>
                                <td class="px-6 py-4 text-center text-rose-600 dark:text-rose-400">
                                    {{ $row['alfa'] }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $row['rate'] >= 85 ? 'bg-emerald-50 text-emerald-700' : ($row['rate'] >= 70 ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-700') }}">
                                        {{ $row['rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-gray-400 italic">
                                    Belum ada data absensi untuk ekskul ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
