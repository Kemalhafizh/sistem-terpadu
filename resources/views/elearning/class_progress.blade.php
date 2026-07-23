<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('elearning.class.show', $elearningClass->uuid) }}" class="text-gray-400 hover:text-emerald-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                Monitoring Progres Belajar Kelas
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 animate-fade-in">

            <!-- Back Link -->
            <div>
                <a href="{{ route('elearning.class.show', $elearningClass->uuid) }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span>Kembali ke Aliran Kelas</span>
                </a>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Average Completion Card -->
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm space-y-2">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rata-rata Ketuntasan Kelas</p>
                    <div class="flex items-baseline gap-2">
                        <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $progressData['average_completion_percentage'] }}%</h3>
                    </div>
                    <div class="w-full h-1.5 bg-gray-100 dark:bg-slate-950 rounded-full overflow-hidden">
                        <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $progressData['average_completion_percentage'] }}%"></div>
                    </div>
                    <p class="text-[9px] text-slate-400">Rasio tuntas membaca materi & pengerjaan tugas</p>
                </div>

                <!-- Total Materials Count Card -->
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Materi Belajar Dibagikan</p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $elearningClass->materials->count() }}</h3>
                    <p class="text-[9px] text-slate-400">Materi yang dapat dibaca/diunduh oleh siswa</p>
                </div>

                <!-- Total Assignments Count Card -->
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tugas & Ujian Aktif</p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $elearningClass->assignments->count() }}</h3>
                    <p class="text-[9px] text-slate-400">Tugas/kuis terjadwal yang harus diselesaikan siswa</p>
                </div>
            </div>

            <!-- Student Progress Table Card -->
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-slate-800">
                    <h4 class="text-sm font-black text-slate-800 dark:text-white">Daftar Kemajuan Siswa</h4>
                    <p class="text-[11px] text-slate-400 mt-0.5">Pantau ketuntasan tonton/baca materi dan pengumpulan tugas per siswa kelas virtual.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse min-w-[800px]">
                        <thead>
                            <tr class="bg-slate-50/60 dark:bg-slate-950/40 border-b border-gray-100 dark:border-slate-800/80 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                <th class="px-6 py-4">Nama Siswa</th>
                                <th class="px-6 py-4 text-center">NIS</th>
                                <th class="px-6 py-4">Persentase Ketuntasan</th>
                                <th class="px-6 py-4 text-center">Materi Dibaca</th>
                                <th class="px-6 py-4 text-center">Tugas Diserahkan</th>
                                <th class="px-6 py-4 text-center">Rata-rata Nilai Tugas</th>
                                <th class="px-6 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-800/60 text-xs font-semibold text-gray-700 dark:text-slate-300">
                            @if(count($progressData['students']) === 0)
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                        Tidak ada siswa terdaftar di kelas fisik target.
                                    </td>
                                </tr>
                            @else
                                @foreach($progressData['students'] as $item)
                                    @php
                                        $s = $item['student'];
                                    @endphp
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/20 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="font-extrabold text-slate-900 dark:text-white">{{ $s->user?->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-bold mt-0.5">{{ $s->user?->email }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-center text-slate-400 font-bold">{{ $s->nis }}</td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center space-x-3 max-w-[200px]">
                                                <div class="flex-1 h-2 bg-slate-100 dark:bg-slate-900 rounded-full overflow-hidden">
                                                    <div class="h-full rounded-full bg-emerald-500" style="width: {{ $item['completion_percentage'] }}%"></div>
                                                </div>
                                                <span class="font-black text-slate-900 dark:text-white shrink-0">{{ $item['completion_percentage'] }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-slate-600 dark:text-slate-400">
                                            {{ $item['read_materials'] }} / {{ $elearningClass->materials->count() }}
                                        </td>
                                        <td class="px-6 py-4 text-center font-bold text-slate-600 dark:text-slate-400">
                                            {{ $item['submitted_assignments'] }} / {{ $elearningClass->assignments->count() }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($item['submitted_assignments'] > 0)
                                                <span class="font-extrabold text-slate-800 dark:text-white">{{ $item['average_score'] }}</span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <a href="{{ route('elearning.class.student-progress', [$elearningClass->uuid, $s->id]) }}" class="inline-flex px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-900 dark:hover:bg-slate-850 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold border border-slate-200 dark:border-slate-800 transition-all">
                                                Detail Checklist
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
