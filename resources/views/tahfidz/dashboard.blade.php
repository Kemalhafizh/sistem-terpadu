<x-app-layout>
    @php
        $viewer = auth()->user();
        $isStudentOrParent = $viewer->hasRole('student') || $viewer->hasRole('parent');
        $isParentViewer = $viewer->hasRole('parent');
        $canManageTahfidz = $viewer->hasRole('guru') || $viewer->hasRole('admin') || $viewer->hasRole('superadmin');
    @endphp

    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Dashboard Tahfidz Al-Qur\'an') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    @if($academicYear)
                        {{ __('Semester') }} {{ $academicYear->semester }} &middot; {{ __('Tahun Ajaran') }} {{ $academicYear->year }}
                    @else
                        {{ __('Tahun ajaran aktif belum diatur') }}
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('tahfidz.leaderboard') }}" class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-medium transition-colors shadow-sm">
                    {{ __('Capaian Tertinggi') }}
                </a>
                @if($canManageTahfidz)
                    <a href="{{ route('tahfidz.bulk-input') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm">
                        {{ __('Input Setoran Massal') }}
                    </a>
                    <a href="{{ route('tahfidz.target-setting') }}" class="px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm">
                        {{ __('Atur Target') }}
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- 1. Tampilan Siswa / Orang Tua --}}
            @if($isStudentOrParent)
                @if($isParentViewer && isset($students) && $students->isNotEmpty())
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <label for="parent_student_id" class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Pilih Data Hafalan Anak') }}</label>
                        <form action="{{ route('tahfidz.dashboard') }}" method="GET" class="flex items-center gap-2">
                            <select id="parent_student_id" name="student_id" onchange="this.form.submit()" class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 dark:text-slate-100 rounded-lg px-3.5 py-2 font-medium text-sm focus:border-primary-500 focus:ring-primary-500">
                                @foreach($students as $c)
                                    <option value="{{ $c->id }}" @selected(isset($student) && $student && $student->id === $c->id)>
                                        {{ $c->name }} ({{ __('Kelas') }} {{ $c->classroom?->name ?? '-' }})
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endif

                @if(isset($student) && $student && isset($progress))
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Kartu Target Semester --}}
                        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 border-s-4 border-s-primary-600 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">{{ __('Status Target Semester') }}</h3>
                                @if($progress['target'])
                                    @php
                                        $statusColors = [
                                            'completed' => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200/70 dark:border-emerald-800/60',
                                            'exceeded' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border-amber-200/70 dark:border-amber-800/60',
                                            'on_track' => 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border-sky-200/70 dark:border-sky-800/60',
                                            'behind' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-200/70 dark:border-rose-800/60',
                                        ];
                                        $statusLabels = [
                                            'completed' => __('Tercapai'),
                                            'exceeded' => __('Melampaui Target'),
                                            'on_track' => __('Sesuai Target'),
                                            'behind' => __('Tertinggal'),
                                        ];
                                    @endphp
                                    <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $statusColors[$progress['target']->status] ?? 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 border-slate-200 dark:border-slate-600' }}">
                                        {{ $statusLabels[$progress['target']->status] ?? $progress['target']->status }}
                                    </span>
                                    <div class="mt-5 space-y-2.5 text-xs tabular-nums">
                                        <div class="flex justify-between gap-2">
                                            <span class="text-slate-500 dark:text-slate-400">{{ __('Target Hafalan') }}</span>
                                            <span class="font-semibold text-slate-900 dark:text-white">{{ $progress['target']->target_juz }} {{ __('Juz') }} ({{ $progress['target']->target_pages }} {{ __('halaman') }})</span>
                                        </div>
                                        <div class="flex justify-between gap-2">
                                            <span class="text-slate-500 dark:text-slate-400">{{ __('Capaian Saat Ini') }}</span>
                                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">{{ $progress['target']->achieved_juz }} {{ __('Juz') }} ({{ $progress['target']->achieved_pages }} {{ __('halaman') }})</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300">{{ __('Target Belum Ditentukan') }}</span>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">{{ __('Hubungi guru pembimbing tahfidz untuk menetapkan target hafalan semester ini.') }}</p>
                                @endif
                            </div>

                            <div class="mt-6 border-t border-slate-200 dark:border-slate-700 pt-4 flex items-center justify-between">
                                <a href="{{ route('tahfidz.export-pdf', $student) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-800 dark:text-slate-100 rounded-lg text-xs font-medium transition-colors">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span>{{ __('Unduh Rapor PDF') }}</span>
                                </a>
                            </div>
                        </div>

                        {{-- Progres & Waktu --}}
                        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-5">{{ __('Analisis Progres & Waktu') }}</h3>
                                <div class="space-y-5 tabular-nums">
                                    <div>
                                        <div class="flex justify-between text-xs font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            <span>{{ __('Pencapaian Target Hafalan') }}</span>
                                            <span class="text-primary-600 dark:text-primary-400 font-semibold">{{ $progress['progress_percent'] }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-700 h-2.5 rounded-full overflow-hidden">
                                            <div class="bg-primary-600 h-full rounded-full" style="width: {{ min(100, max(0, (float) $progress['progress_percent'])) }}%"></div>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="flex justify-between text-xs font-medium text-slate-700 dark:text-slate-300 mb-2">
                                            <span>{{ __('Waktu Semester Berjalan') }}</span>
                                            <span class="text-amber-600 dark:text-amber-400 font-semibold">{{ $progress['time_elapsed_percent'] }}%</span>
                                        </div>
                                        <div class="w-full bg-slate-100 dark:bg-slate-700 h-2.5 rounded-full overflow-hidden">
                                            <div class="bg-amber-500 h-full rounded-full" style="width: {{ min(100, max(0, (float) $progress['time_elapsed_percent'])) }}%"></div>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                                            {{ __('Status capaian dibandingkan terhadap persentase waktu semester yang telah berjalan.') }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Ringkasan Setoran --}}
                        <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-4">{{ __('Ringkasan Setoran') }}</h3>
                                <div class="grid grid-cols-2 gap-3 tabular-nums">
                                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-200/70 dark:border-slate-700/70">
                                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Setoran Ziyadah') }}</p>
                                        <p class="text-2xl font-semibold text-emerald-600 dark:text-emerald-400 mt-1">{{ $progress['logs']->where('type', 'ziyadah')->count() }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('kali semester ini') }}</p>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-900/50 p-4 rounded-lg border border-slate-200/70 dark:border-slate-700/70">
                                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Setoran Murajaah') }}</p>
                                        <p class="text-2xl font-semibold text-slate-900 dark:text-white mt-1">{{ $progress['logs']->where('type', 'murajaah')->count() }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('kali semester ini') }}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-between items-center">
                                <span class="text-xs text-slate-500 dark:text-slate-400">{{ __('Riwayat Lengkap') }}</span>
                                <a href="{{ route('tahfidz.show', $student) }}" class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                    <span>{{ __('Buka Jurnal Lengkap') }}</span>
                                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Tabel Setoran Semester Ini --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">{{ __('Catatan Setoran Semester Ini') }}</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-start border-collapse text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-600 dark:text-slate-400">
                                        <th class="pb-3 pe-4 text-start">{{ __('Tanggal') }}</th>
                                        <th class="pb-3 pe-4 text-start">{{ __('Surah') }}</th>
                                        <th class="pb-3 pe-4 text-start">{{ __('Ayat') }}</th>
                                        <th class="pb-3 pe-4 text-start">{{ __('Tipe') }}</th>
                                        <th class="pb-3 px-3 text-center">{{ __('Juz') }}</th>
                                        <th class="pb-3 px-3 text-center">{{ __('Halaman') }}</th>
                                        <th class="pb-3 px-3 text-center">{{ __('Nilai') }}</th>
                                        <th class="pb-3 pe-4 text-start">{{ __('Guru Pembimbing') }}</th>
                                        <th class="pb-3 text-start">{{ __('Catatan') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs text-slate-700 dark:text-slate-300">
                                    @forelse($progress['logs'] as $log)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                            <td class="py-3.5 pe-4 tabular-nums">{{ $log->date?->format('d M Y') }}</td>
                                            <td class="py-3.5 pe-4 font-medium text-slate-900 dark:text-white">{{ $log->surah_name }}</td>
                                            <td class="py-3.5 pe-4 tabular-nums">{{ __('Ayat') }} {{ $log->from_ayat }} - {{ $log->to_ayat }}</td>
                                            <td class="py-3.5 pe-4">
                                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium {{ $log->type === 'ziyadah' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300' : 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300' }}">
                                                    {{ ucfirst($log->type) }}
                                                </span>
                                            </td>
                                            <td class="py-3.5 px-3 text-center tabular-nums">{{ __('Juz') }} {{ $log->juz ?? '-' }}</td>
                                            <td class="py-3.5 px-3 text-center tabular-nums">{{ $log->pages_count }} {{ __('hlm') }}</td>
                                            <td class="py-3.5 px-3 text-center">
                                                <span class="font-semibold text-primary-700 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/50 px-2 py-0.5 rounded-md tabular-nums">{{ $log->grade ?? '-' }}</span>
                                            </td>
                                            <td class="py-3.5 pe-4">{{ $log->teacher?->name ?? '-' }}</td>
                                            <td class="py-3.5 text-slate-500 dark:text-slate-400 max-w-[220px] truncate" title="{{ $log->notes }}">{{ $log->notes ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-8 text-center text-slate-500 dark:text-slate-400">{{ __('Belum ada setoran hafalan tercatat pada semester ini.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-10 text-center shadow-sm border border-slate-200 dark:border-slate-700">
                        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <h3 class="font-semibold text-base text-slate-900 dark:text-white">{{ __('Data Siswa Tidak Ditemukan') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Akun ini belum terhubung dengan profil siswa aktif.') }}</p>
                    </div>
                @endif
            @else
                {{-- 2. Tampilan Guru / Pembimbing / Manajemen --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                        <label for="tahfidz_classroom_id" class="text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Pilih Kelas Binaan') }}</label>
                        <form action="{{ route('tahfidz.dashboard') }}" method="GET" class="flex items-center gap-2">
                            <select id="tahfidz_classroom_id" name="classroom_id" onchange="this.form.submit()" class="bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 dark:text-slate-100 rounded-lg px-3.5 py-2 font-medium text-sm focus:border-primary-500 focus:ring-primary-500">
                                @foreach($classrooms as $c)
                                    <option value="{{ $c->id }}" @selected($classroom && $classroom->id === $c->id)>
                                        {{ __('Kelas') }} {{ $c->name }}{{ $c->major ? ' ('.$c->major.')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    @if($classroom)
                        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 dark:text-slate-400 tabular-nums">
                            <div>{{ __('Wali Kelas') }}: <span class="text-slate-900 dark:text-white font-medium">{{ $classroom->teacher?->name ?? __('Belum Diatur') }}</span></div>
                            <div class="h-4 w-px bg-slate-200 dark:bg-slate-700"></div>
                            <div>{{ __('Total Siswa') }}: <span class="text-slate-900 dark:text-white font-medium">{{ $students->count() }} {{ __('siswa') }}</span></div>
                        </div>
                    @endif
                </div>

                @if($classroom)
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Rata-rata Ketercapaian Target') }}</p>
                            <p class="text-3xl font-semibold text-primary-600 dark:text-primary-400 mt-2 tabular-nums">{{ $summary['average_completion_percent'] }}%</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Berdasarkan target halaman/juz siswa') }}</p>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Total Setoran Tercatat') }}</p>
                            <p class="text-3xl font-semibold text-emerald-600 dark:text-emerald-400 mt-2 tabular-nums">{{ $summary['total_setoran_count'] }} <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ __('kali') }}</span></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Akumulasi ziyadah dan murajaah semester ini') }}</p>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-3">{{ __('Distribusi Status Target') }}</p>
                            <div class="grid grid-cols-4 gap-2 text-center text-xs tabular-nums">
                                <div class="p-2 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300">
                                    <span class="block text-base font-semibold">{{ $summary['status_distribution']['exceeded'] }}</span>
                                    <span class="text-[11px]">{{ __('Melampaui') }}</span>
                                </div>
                                <div class="p-2 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300">
                                    <span class="block text-base font-semibold">{{ $summary['status_distribution']['completed'] }}</span>
                                    <span class="text-[11px]">{{ __('Tercapai') }}</span>
                                </div>
                                <div class="p-2 rounded-lg bg-sky-50 dark:bg-sky-950/40 text-sky-700 dark:text-sky-300">
                                    <span class="block text-base font-semibold">{{ $summary['status_distribution']['on_track'] }}</span>
                                    <span class="text-[11px]">{{ __('Sesuai') }}</span>
                                </div>
                                <div class="p-2 rounded-lg bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300">
                                    <span class="block text-base font-semibold">{{ $summary['status_distribution']['behind'] }}</span>
                                    <span class="text-[11px]">{{ __('Tertinggal') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Hafalan Siswa Kelas --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <div class="flex flex-wrap items-center justify-between gap-2 mb-5">
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Daftar Hafalan Siswa Kelas') }}</h3>
                            @if($canManageTahfidz)
                                <a href="{{ route('tahfidz.bulk-input', ['classroom_id' => $classroom->id]) }}" class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                    <span>{{ __('Input Setoran Massal') }}</span>
                                    <svg class="w-3.5 h-3.5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            @endif
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-start border-collapse text-sm">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-medium text-slate-600 dark:text-slate-400">
                                        <th class="pb-3 pe-4 text-start">{{ __('Siswa') }}</th>
                                        <th class="pb-3 pe-4 text-start">{{ __('NIS') }}</th>
                                        <th class="pb-3 px-3 text-center">{{ __('Target') }}</th>
                                        <th class="pb-3 px-3 text-center">{{ __('Capaian Ziyadah') }}</th>
                                        <th class="pb-3 px-3 text-center">{{ __('Progres') }}</th>
                                        <th class="pb-3 px-3 text-center">{{ __('Status') }}</th>
                                        <th class="pb-3 text-end">{{ __('Aksi') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-xs text-slate-700 dark:text-slate-300">
                                    @forelse($students as $st)
                                        @php
                                            $tgt = $st->tahfidzTargets->first();
                                            $progPct = 0;
                                            if ($tgt) {
                                                if ($tgt->target_pages > 0) {
                                                    $progPct = round(($tgt->achieved_pages / $tgt->target_pages) * 100, 1);
                                                } elseif ($tgt->target_juz > 0) {
                                                    $progPct = round(($tgt->achieved_juz / $tgt->target_juz) * 100, 1);
                                                }
                                            }
                                            $badgeColors = [
                                                'completed' => 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border-emerald-200/70 dark:border-emerald-800/60',
                                                'exceeded' => 'bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border-amber-200/70 dark:border-amber-800/60',
                                                'on_track' => 'bg-sky-50 dark:bg-sky-950/50 text-sky-700 dark:text-sky-300 border-sky-200/70 dark:border-sky-800/60',
                                                'behind' => 'bg-rose-50 dark:bg-rose-950/50 text-rose-700 dark:text-rose-300 border-rose-200/70 dark:border-rose-800/60',
                                            ];
                                            $labels = [
                                                'completed' => __('Tercapai'),
                                                'exceeded' => __('Melampaui'),
                                                'on_track' => __('Sesuai'),
                                                'behind' => __('Tertinggal'),
                                            ];
                                        @endphp
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                            <td class="py-3.5 pe-4">
                                                <p class="font-medium text-slate-900 dark:text-white">{{ $st->name }}</p>
                                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $st->gender }}</p>
                                            </td>
                                            <td class="py-3.5 pe-4 text-slate-500 dark:text-slate-400 tabular-nums">{{ $st->nis }}</td>
                                            <td class="py-3.5 px-3 text-center tabular-nums">
                                                @if($tgt)
                                                    {{ $tgt->target_juz }} {{ __('Juz') }} ({{ $tgt->target_pages }} {{ __('hlm') }})
                                                @else
                                                    <span class="text-slate-400">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-3 text-center text-emerald-600 dark:text-emerald-400 font-medium tabular-nums">
                                                @if($tgt)
                                                    {{ $tgt->achieved_juz }} {{ __('Juz') }} ({{ $tgt->achieved_pages }} {{ __('hlm') }})
                                                @else
                                                    @php
                                                        $maxJuz = $st->tahfidzLogs()->where('type', 'ziyadah')->whereNotNull('juz')->distinct()->count('juz');
                                                        $sumPages = $st->tahfidzLogs()->where('type', 'ziyadah')->sum('pages_count');
                                                    @endphp
                                                    {{ $maxJuz }} {{ __('Juz') }} ({{ $sumPages }} {{ __('hlm') }})
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-3">
                                                @if($tgt)
                                                    <div class="flex items-center justify-center gap-2 tabular-nums">
                                                        <div class="w-16 bg-slate-100 dark:bg-slate-700 h-2 rounded-full overflow-hidden">
                                                            <div class="bg-primary-600 h-full rounded-full" style="width: {{ min($progPct, 100) }}%"></div>
                                                        </div>
                                                        <span class="text-xs font-medium text-slate-700 dark:text-slate-200">{{ $progPct }}%</span>
                                                    </div>
                                                @else
                                                    <span class="text-slate-400 text-center block">-</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 px-3 text-center">
                                                @if($tgt)
                                                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium border {{ $badgeColors[$tgt->status] ?? 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-600' }}">
                                                        {{ $labels[$tgt->status] ?? $tgt->status }}
                                                    </span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400">{{ __('Belum Diatur') }}</span>
                                                @endif
                                            </td>
                                            <td class="py-3.5 text-end">
                                                <a href="{{ route('tahfidz.show', $st) }}" class="inline-flex px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 rounded-lg text-xs font-medium text-slate-700 dark:text-slate-200 transition-colors">
                                                    {{ __('Detail') }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400">{{ __('Tidak ada siswa terdaftar di kelas ini.') }}</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Aktivitas Setoran Terbaru --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-4">{{ __('Aktivitas Setoran Terbaru Kelas') }}</h3>
                        <div class="space-y-3">
                            @forelse($recentLogs as $log)
                                <div class="flex items-start gap-3 p-3.5 bg-slate-50 dark:bg-slate-900/50 rounded-xl border border-slate-200/70 dark:border-slate-700/70">
                                    <div class="w-9 h-9 rounded-lg shrink-0 bg-primary-50 dark:bg-primary-950/50 text-primary-700 dark:text-primary-300 flex items-center justify-center font-semibold text-xs">
                                        {{ $log->type === 'ziyadah' ? 'Z' : 'M' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between gap-2">
                                            <p class="text-sm font-medium text-slate-900 dark:text-white truncate">{{ $log->student?->name }}</p>
                                            <span class="text-xs text-slate-500 dark:text-slate-400 tabular-nums shrink-0">{{ $log->date?->format('d M Y') }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 tabular-nums">
                                            {{ $log->surah_name }} ({{ __('Ayat') }} {{ $log->from_ayat }} - {{ $log->to_ayat }}) &middot; {{ __('Nilai') }}: <span class="font-semibold text-primary-700 dark:text-primary-300">{{ $log->grade }}</span>
                                        </p>
                                        @if($log->notes)
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 ps-2.5 border-s-2 border-slate-300 dark:border-slate-600">{{ $log->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-sm text-slate-500 dark:text-slate-400">{{ __('Belum ada aktivitas setoran hafalan terbaru.') }}</div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-10 text-center shadow-sm border border-slate-200 dark:border-slate-700">
                        <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"></path>
                        </svg>
                        <h3 class="font-semibold text-base text-slate-900 dark:text-white">{{ __('Kelas Tidak Tersedia') }}</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Belum ada data kelas yang terdaftar dalam sistem.') }}</p>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
