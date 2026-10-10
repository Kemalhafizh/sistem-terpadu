<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Dashboard Tahfidz Al-Qur\'an') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    {{ $academicYear ? 'Semester ' . $academicYear->semester . ' - Tahun Ajaran ' . $academicYear->year : 'Tahun Ajaran Aktif Belum Diatur' }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('tahfidz.leaderboard') }}" class="px-4 py-2 bg-amber-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-500/20 hover:scale-[1.03] transition-all">
                     LEADERBOARD
                </a>
                @if(in_array(auth()->user()->role, ['guru', 'admin', 'superadmin']))
                    <a href="{{ route('tahfidz.bulk-input') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-600/20 hover:scale-[1.03] transition-all">
                         INPUT MASSAL
                    </a>
                    <a href="{{ route('tahfidz.target-setting') }}" class="px-4 py-2 bg-primary-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary-600/20 hover:scale-[1.03] transition-all">
                         ATUR TARGET
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- 1. Student / Parent Dashboard View --}}
            @if(in_array(auth()->user()->role, ['student', 'parent']))
                @if(auth()->user()->role === 'parent' && isset($students))
                    {{-- Child Selector for Parents --}}
                    <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <span class="text-xs font-black uppercase tracking-widest text-gray-400">Pilih Data Hafalan Anak:</span>
                        <form action="{{ route('tahfidz.dashboard') }}" method="GET" class="flex items-center gap-2">
                            <select name="student_id" onchange="this.form.submit()" class="bg-gray-50 border-none rounded-xl px-4 py-2 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                                @foreach($students as $c)
                                    <option value="{{ $c->id }}" {{ isset($student) && $student->id === $c->id ? 'selected' : '' }}>
                                        {{ $c->name }} (Kelas {{ $c->classroom?->name }})
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                @endif

                @if(isset($student) && $student)
                    {{-- Student Progress Cards --}}
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        {{-- Target & Completion status card --}}
                        <div class="bg-primary-900 rounded-[2.5rem] p-6 sm:p-8 text-white relative overflow-hidden shadow-xl flex flex-col justify-between min-h-[280px]">
                            <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                            
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-[0.3em] mb-6 opacity-60">Status Target Semester</h3>
                                @if($progress['target'])
                                    @php
                                        $statusColors = [
                                            'completed' => 'bg-emerald-500 text-white',
                                            'exceeded' => 'bg-amber-400 text-primary-950',
                                            'on_track' => 'bg-blue-500 text-white',
                                            'behind' => 'bg-rose-500 text-white'
                                        ];
                                        $statusLabels = [
                                            'completed' => 'Tercapai',
                                            'exceeded' => 'Melampaui Target',
                                            'on_track' => 'Sesuai Target',
                                            'behind' => 'Tertinggal'
                                        ];
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black uppercase tracking-widest {{ $statusColors[$progress['target']->status] ?? 'bg-gray-500' }}">
                                        {{ $statusLabels[$progress['target']->status] ?? $progress['target']->status }}
                                    </span>
                                    <div class="mt-6 space-y-2">
                                        <div class="flex justify-between text-xs">
                                            <span class="opacity-70">Target Hafalan:</span>
                                            <span class="font-black">{{ $progress['target']->target_juz }} Juz ({{ $progress['target']->target_pages }} Halaman)</span>
                                        </div>
                                        <div class="flex justify-between text-xs">
                                            <span class="opacity-70">Capaian Saat Ini:</span>
                                            <span class="font-black text-emerald-300">{{ $progress['target']->achieved_juz }} Juz ({{ $progress['target']->achieved_pages }} Halaman)</span>
                                        </div>
                                    </div>
                                @else
                                    <span class="px-3 py-1 rounded-full text-[9px] font-black bg-white/20 text-white uppercase tracking-widest">Target Belum Ditentukan</span>
                                    <p class="text-xs mt-4 opacity-60">Silakan hubungi Ustadz/Ustadzah pembimbing tahfidz untuk menentukan target semester ini.</p>
                                @endif
                            </div>

                            <div class="mt-8 border-t border-white/10 pt-4 flex items-center justify-between">
                                <a href="{{ route('tahfidz.export-pdf', $student->id) }}" class="px-4 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                                     PDF RAPOR
                                </a>
                            </div>
                        </div>

                        {{-- Progress bars card --}}
                        <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 mb-6">Analisis Progres & Waktu</h3>
                                
                                <div class="space-y-6">
                                    {{-- Academic Target Completion --}}
                                    <div>
                                        <div class="flex justify-between text-xs font-bold text-gray-600 mb-2">
                                            <span>Pencapaian Target Hafalan</span>
                                            <span class="text-primary-600">{{ $progress['progress_percent'] }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                            <div class="bg-primary-600 h-full rounded-full transition-all duration-500" style="width: {{ $progress['progress_percent'] }}%"></div>
                                        </div>
                                    </div>

                                    {{-- Time Elapsed --}}
                                    <div>
                                        <div class="flex justify-between text-xs font-bold text-gray-600 mb-2">
                                            <span>Waktu Semester Berjalan</span>
                                            <span class="text-amber-500">{{ $progress['time_elapsed_percent'] }}%</span>
                                        </div>
                                        <div class="w-full bg-gray-100 h-3 rounded-full overflow-hidden">
                                            <div class="bg-amber-400 h-full rounded-full transition-all duration-500" style="width: {{ $progress['time_elapsed_percent'] }}%"></div>
                                        </div>
                                        <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase tracking-wider">
                                            Status Semester dievaluasi otomatis berdasarkan durasi waktu terlewati.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Quick stats card --}}
                        <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 mb-6">Ringkasan Setoran</h3>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-50">
                                        <p class="text-[9px] font-black text-emerald-600 uppercase tracking-widest mb-1">Setoran Ziyadah</p>
                                        <p class="text-2xl font-black text-emerald-800">{{ $progress['logs']->where('type', 'ziyadah')->count() }}</p>
                                        <p class="text-[9px] font-bold text-emerald-500 uppercase">Kali Semester Ini</p>
                                    </div>
                                    <div class="bg-blue-50/50 p-4 rounded-2xl border border-blue-50">
                                        <p class="text-[9px] font-black text-blue-600 uppercase tracking-widest mb-1">Setoran Murajaah</p>
                                        <p class="text-2xl font-black text-blue-800">{{ $progress['logs']->where('type', 'murajaah')->count() }}</p>
                                        <p class="text-[9px] font-bold text-blue-500 uppercase">Kali Semester Ini</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-between items-center bg-gray-50 p-4 rounded-2xl">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Detail Terakhir</span>
                                <a href="{{ route('tahfidz.show', $student->id) }}" class="text-xs font-black text-primary-600 hover:underline">
                                    LIHAT JURNAL LENGKAP →
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Logs and setoran list --}}
                    <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100">
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 mb-6">Catatan Setoran Semester Ini</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                        <th class="pb-3">Tanggal</th>
                                        <th class="pb-3">Surah</th>
                                        <th class="pb-3">Ayat</th>
                                        <th class="pb-3">Tipe</th>
                                        <th class="pb-3 text-center">Juz</th>
                                        <th class="pb-3 text-center">Halaman</th>
                                        <th class="pb-3 text-center">Nilai</th>
                                        <th class="pb-3">Guru Pembimbing</th>
                                        <th class="pb-3">Catatan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-700">
                                    @forelse($progress['logs'] as $log)
                                        <tr>
                                            <td class="py-4">{{ $log->date?->format('d M Y') }}</td>
                                            <td class="py-4 font-black text-gray-800">{{ $log->surah_name }}</td>
                                            <td class="py-4">Ayat {{ $log->from_ayat }} - {{ $log->to_ayat }}</td>
                                            <td class="py-4">
                                                <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $log->type === 'ziyadah' ? 'bg-emerald-50 text-emerald-600' : 'bg-blue-50 text-blue-600' }}">
                                                    {{ $log->type }}
                                                </span>
                                            </td>
                                            <td class="py-4 text-center">Juz {{ $log->juz ?? '-' }}</td>
                                            <td class="py-4 text-center">{{ $log->pages_count }} Hal</td>
                                            <td class="py-4 text-center">
                                                <span class="font-black text-primary-600 bg-primary-50 px-2 py-0.5 rounded">{{ $log->grade ?? '-' }}</span>
                                            </td>
                                            <td class="py-4">{{ $log->teacher?->name ?? 'Ustadz Pembimbing' }}</td>
                                            <td class="py-4 italic text-gray-500 max-w-[200px] truncate" title="{{ $log->notes }}">{{ $log->notes ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-8 text-center text-gray-400 font-bold">Belum ada setoran hafalan tercatat pada semester ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100">
                        <span class="text-4xl"></span>
                        <h3 class="font-black text-lg text-gray-800 mt-4 uppercase">Data Siswa Tidak Ditemukan</h3>
                        <p class="text-xs text-gray-400 mt-2">Akun Anda belum terhubung dengan data profil siswa aktif.</p>
                    </div>
                @endif
            @endif

            {{-- 2. Guru / Admin Dashboard View --}}
            @if(in_array(auth()->user()->role, ['guru', 'admin', 'superadmin']))
                {{-- Classroom Selector and Quick Shortcuts --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <span class="text-xs font-black uppercase tracking-widest text-gray-400">Pilih Kelas Binaan:</span>
                        <form action="{{ route('tahfidz.dashboard') }}" method="GET" class="flex items-center gap-2">
                            <select name="classroom_id" onchange="this.form.submit()" class="bg-gray-50 border-none rounded-xl px-5 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                                @foreach($classrooms as $c)
                                    <option value="{{ $c->id }}" {{ $classroom && $classroom->id === $c->id ? 'selected' : '' }}>
                                        Kelas {{ $c->name }} ({{ $c->major }})
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>
                    @if($classroom)
                        <div class="flex items-center gap-4 text-xs font-bold text-gray-500">
                            <div>Wali Kelas: <span class="text-gray-800 font-black">{{ $classroom->teacher?->name ?? 'Belum Diatur' }}</span></div>
                            <div class="h-4 w-px bg-gray-200"></div>
                            <div>Total Siswa: <span class="text-gray-800 font-black">{{ $students->count() }} Orang</span></div>
                        </div>
                    @endif
                </div>

                @if($classroom)
                    {{-- Classroom aggregate metrics --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Average completion percent --}}
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Rata-rata Ketercapaian Target</p>
                                <p class="text-3xl font-black text-primary-600">{{ $summary['average_completion_percent'] }}%</p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Berdasarkan Halaman/Juz target siswa</p>
                            </div>
                            <div class="w-12 h-12 bg-primary-50 rounded-2xl flex items-center justify-center text-primary-600 text-lg"></div>
                        </div>

                        {{-- Total setoran logs count --}}
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                            <div class="space-y-1">
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Setoran Terdaftar</p>
                                <p class="text-3xl font-black text-emerald-600">{{ $summary['total_setoran_count'] }} <span class="text-xs text-gray-400 font-black">Kali</span></p>
                                <p class="text-[9px] font-bold text-gray-400 uppercase">Setoran ziyadah & muraja'ah semester ini</p>
                            </div>
                            <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 text-lg"></div>
                        </div>

                        {{-- Target status breakdown --}}
                        <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-100">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Distribusi Status Target</p>
                            <div class="grid grid-cols-4 gap-2 text-center text-[10px] font-bold">
                                <div class="p-2 rounded-xl bg-amber-50 text-amber-700">
                                    <span class="block text-lg font-black">{{ $summary['status_distribution']['exceeded'] }}</span>
                                    <span>Melampaui</span>
                                </div>
                                <div class="p-2 rounded-xl bg-emerald-50 text-emerald-700">
                                    <span class="block text-lg font-black">{{ $summary['status_distribution']['completed'] }}</span>
                                    <span>Tercapai</span>
                                </div>
                                <div class="p-2 rounded-xl bg-blue-50 text-blue-700">
                                    <span class="block text-lg font-black">{{ $summary['status_distribution']['on_track'] }}</span>
                                    <span>Sesuai</span>
                                </div>
                                <div class="p-2 rounded-xl bg-rose-50 text-rose-700">
                                    <span class="block text-lg font-black">{{ $summary['status_distribution']['behind'] }}</span>
                                    <span>Tertinggal</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Classroom student progress list --}}
                    <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400">Daftar Hafalan Siswa Kelas</h3>
                            <a href="{{ route('tahfidz.bulk-input', ['classroom_id' => $classroom->id]) }}" class="text-xs font-black text-primary-600 hover:underline">
                                INPUT SETORAN MASSAL →
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                        <th class="pb-3">Siswa</th>
                                        <th class="pb-3">NIS</th>
                                        <th class="pb-3 text-center">Target</th>
                                        <th class="pb-3 text-center">Capaian Ziyadah</th>
                                        <th class="pb-3 text-center">Progres Target</th>
                                        <th class="pb-3 text-center">Status</th>
                                        <th class="pb-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-700">
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
                                        @endphp
                                        <tr>
                                            <td class="py-4">
                                                <p class="font-black text-gray-800">{{ $st->name }}</p>
                                                <p class="text-[9px] text-gray-400 uppercase">Gender: {{ $st->gender }}</p>
                                            </td>
                                            <td class="py-4 text-gray-500">{{ $st->nis }}</td>
                                            <td class="py-4 text-center">
                                                @if($tgt)
                                                    {{ $tgt->target_juz }} Juz ({{ $tgt->target_pages }} Hal)
                                                @else
                                                    <span class="text-gray-300">-</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-center text-emerald-600">
                                                @if($tgt)
                                                    {{ $tgt->achieved_juz }} Juz ({{ $tgt->achieved_pages }} Hal)
                                                @else
                                                    @php
                                                        $maxJuz = $st->tahfidzLogs()->where('type', 'ziyadah')->whereNotNull('juz')->distinct()->count('juz');
                                                        $sumPages = $st->tahfidzLogs()->where('type', 'ziyadah')->sum('pages_count');
                                                    @endphp
                                                    {{ $maxJuz }} Juz ({{ $sumPages }} Hal)
                                                @endif
                                            </td>
                                            <td class="py-4">
                                                @if($tgt)
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <div class="w-16 bg-gray-100 h-2 rounded-full overflow-hidden">
                                                            <div class="bg-primary-600 h-full rounded-full" style="width: {{ min($progPct, 100) }}%"></div>
                                                        </div>
                                                        <span class="text-[10px] font-black text-primary-700">{{ $progPct }}%</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-300 text-center block">-</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-center">
                                                @if($tgt)
                                                    @php
                                                        $badgeColors = [
                                                            'completed' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                                            'exceeded' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                                            'on_track' => 'bg-blue-50 text-blue-600 border border-blue-100',
                                                            'behind' => 'bg-rose-50 text-rose-600 border border-rose-100'
                                                        ];
                                                        $labels = [
                                                            'completed' => 'Tercapai',
                                                            'exceeded' => 'Melampaui',
                                                            'on_track' => 'Sesuai',
                                                            'behind' => 'Tertinggal'
                                                        ];
                                                    @endphp
                                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $badgeColors[$tgt->status] ?? 'bg-gray-100 text-gray-500' }}">
                                                        {{ $labels[$tgt->status] ?? $tgt->status }}
                                                    </span>
                                                @else
                                                    <span class="text-[9px] font-bold text-gray-400 bg-gray-50 border border-gray-100 px-2 py-0.5 rounded-full">Belum Diatur</span>
                                                @endif
                                            </td>
                                            <td class="py-4 text-right">
                                                <a href="{{ route('tahfidz.show', $st->id) }}" class="px-3 py-1 bg-gray-50 hover:bg-gray-100 border border-gray-100 rounded-lg text-[10px] font-black uppercase tracking-wider text-gray-600 transition-all">
                                                     DETAIL
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-8 text-center text-gray-400 font-bold">Tidak ada siswa terdaftar di kelas ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Classroom recent setoran log feed --}}
                    <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100">
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 mb-6">Aktivitas Setoran Terbaru Kelas</h3>
                        <div class="space-y-4">
                            @forelse($recentLogs as $log)
                                <div class="flex items-start space-x-4 p-4 hover:bg-gray-50/50 rounded-2xl border border-transparent hover:border-gray-50 transition-all">
                                    <div class="w-10 h-10 rounded-2xl flex-shrink-0 bg-primary-50 text-primary-600 flex items-center justify-center font-black text-xs">
                                        {{ $log->type === 'ziyadah' ? 'Z' : 'M' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs font-black text-gray-800 truncate">{{ $log->student?->name }}</p>
                                            <span class="text-[9px] font-bold text-gray-400 uppercase">{{ $log->date?->format('d M Y') }}</span>
                                        </div>
                                        <p class="text-xs text-gray-600 mt-1">
                                            Membaca <span class="font-black">{{ $log->surah_name }}</span> (Ayat {{ $log->from_ayat }} - {{ $log->to_ayat }}) | Grade: <span class="font-black text-primary-600">{{ $log->grade }}</span>
                                        </p>
                                        @if($log->notes)
                                            <p class="text-[11px] text-gray-400 italic mt-1 bg-gray-50 p-2 rounded-lg border-l-2 border-primary-200">"{{ $log->notes }}"</p>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-400 font-bold">Belum ada aktivitas setoran hafalan baru-baru ini.</div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-3xl p-12 text-center shadow-sm border border-gray-100">
                        <span class="text-4xl"></span>
                        <h3 class="font-black text-lg text-gray-800 mt-4 uppercase">Kelas Tidak Tersedia</h3>
                        <p class="text-xs text-gray-400 mt-2">Belum ada data kelas terdaftar dalam sistem.</p>
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
