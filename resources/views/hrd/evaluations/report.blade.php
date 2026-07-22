<x-app-layout>
    <x-slot name="title">Rekapitulasi Evaluasi Kinerja SISTA</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Rekapitulasi Evaluasi Kinerja Pegawai
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Daftar seluruh hasil evaluasi berkala guru dan staf SMA Islam Sultan Agung 1 Semarang.
                </p>
            </div>
            <a href="{{ route('hrd.dashboard') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
        <div class="p-6 border-b border-gray-100 dark:border-gray-700/50">
            <h3 class="font-bold text-gray-800 dark:text-white text-sm"> Daftar Hasil Penilaian</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-55 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Pegawai & Periode</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Skor Kriteria (P | Pr | S | K)</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Rata-rata Skor</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-4 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50 text-sm">
                    @forelse($evaluations as $ev)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/10 transition-all">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-800 dark:text-white">{{ $ev->employee->user->name }}</div>
                                <p class="text-[10px] text-gray-400 mt-0.5">Periode: {{ $ev->period }}</p>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @php
                                    $scores = is_string($ev->criteria_scores) ? json_decode($ev->criteria_scores, true) : $ev->criteria_scores;
                                @endphp
                                <span class="font-mono text-gray-650 dark:text-gray-300">
                                    {{ $scores['pedagogik'] ?? 0 }} | {{ $scores['profesional'] ?? 0 }} | {{ $scores['sosial'] ?? 0 }} | {{ $scores['kepribadian'] ?? 0 }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold">
                                <span class="text-indigo-650 dark:text-indigo-400 text-sm font-black">{{ $ev->total_score }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs font-black">
                                <span class="px-2.5 py-1 text-[10px] rounded-lg {{ $ev->grade === 'A' ? 'bg-emerald-50 text-emerald-700' : ($ev->grade === 'B' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                                    {{ $ev->grade }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('hrd.employees.show', $ev->employee->id) }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-1.5 px-3.5 text-xs font-bold transition-all">
                                     Detail Profil
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-12 text-gray-400 italic">
                                Belum ada hasil evaluasi kinerja tersimpan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
