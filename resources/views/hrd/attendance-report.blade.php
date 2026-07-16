<x-app-layout>
    <x-slot name="title">Laporan Kehadiran Pegawai SISTA</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📅 Laporan Kehadiran Bulanan Pegawai
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Rekapitulasi absensi presensi bulanan guru dan staf SMA Islam Sultan Agung 1 Semarang.
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('hrd.attendance-report.export', ['month' => $month, 'year' => $year]) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    📥 Ekspor CSV Excel
                </a>
                <a href="{{ route('hrd.dashboard') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                    ⬅️ Ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Pemilih Bulan/Tahun -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <form method="GET" action="{{ route('hrd.attendance-report') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Bulan</label>
                    <select name="month" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ sprintf('%02d', $m) }}" {{ $month == sprintf('%02d', $m) ? 'selected' : '' }}>
                                {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pilih Tahun</label>
                    <select name="year" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                        🔄 Refresh Laporan
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Rekap Absensi -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
                <h3 class="font-bold text-gray-800 dark:text-white text-sm">📅 Rekap Presensi Pegawai</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-55 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Pegawai & NIY</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Jabatan</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Hadir</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Terlambat</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Izin/Sakit/Cuti</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-center">Alpa</th>
                            <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Persentase</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                        @forelse($reportData as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-gray-800 dark:text-white">{{ $row['employee']->user->name ?? '-' }}</div>
                                    <p class="text-[10px] text-gray-400 mt-0.5">NIP: {{ $row['employee']->employee_number }}</p>
                                </td>
                                <td class="px-6 py-4 text-xs text-gray-500">
                                    {{ $row['employee']->position }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-800 dark:text-white">
                                    {{ $row['hadir'] }}
                                </td>
                                <td class="px-6 py-4 text-center text-amber-600 dark:text-amber-400 font-bold">
                                    {{ $row['terlambat'] }}
                                </td>
                                <td class="px-6 py-4 text-center text-blue-600 dark:text-blue-400">
                                    {{ $row['izin'] + $row['sakit'] + $row['cuti'] }}
                                </td>
                                <td class="px-6 py-4 text-center text-rose-600 dark:text-rose-400">
                                    {{ $row['alfa'] }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-black {{ $row['rate'] >= 90 ? 'bg-emerald-50 text-emerald-700' : ($row['rate'] >= 75 ? 'bg-blue-50 text-blue-700' : 'bg-rose-50 text-rose-700') }}">
                                        {{ $row['rate'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-12 text-gray-400 italic">
                                    Belum ada data absensi untuk periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
