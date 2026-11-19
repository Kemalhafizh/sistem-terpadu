<x-app-layout>
    <x-slot name="title">Buku Induk Siswa &mdash; {{ $student->user?->name ?? '-' }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    Buku Induk Siswa Resmi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $student->user?->name ?? '-' }} &middot; NIS: {{ $student->nis }} &middot; NISN: {{ $student->nisn }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(\App\Http\Middleware\RoleMiddleware::allowsUser(auth()->user(), ['superadmin', 'admin', 'waka_kurikulum', 'staf_tu', 'kepala_sekolah', 'kepsek']))
                    <a href="{{ route('students.index') }}" class="px-4 py-2 rounded-xl bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white text-xs font-bold">
                        Daftar Siswa
                    </a>
                @endif
                <a href="{{ route('students.ledger.pdf', $student) }}" target="_blank" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow">
                    Unduh PDF Format Dinas
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        {{-- A. Identitas Siswa & Orang Tua --}}
        <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-200/60 dark:border-gray-700/50 shadow-sm space-y-4">
            <h3 class="text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 pb-2">
                A. Identitas Pribadi &amp; Keluarga
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="block text-[11px] font-bold uppercase text-gray-400">Nama Lengkap</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $student->user?->name ?? '-' }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase text-gray-400">NIS / NISN</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $student->nis ?: '-' }} / {{ $student->nisn ?: '-' }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase text-gray-400">NIK</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $student->nik ?: '-' }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase text-gray-400">Jenis Kelamin</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $student->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase text-gray-400">Tempat, Tanggal Lahir</span>
                    <span class="text-gray-800 dark:text-gray-200">
                        {{ $student->birth_place ?: '-' }}, {{ $student->birth_date ? \Carbon\Carbon::parse($student->birth_date)->translatedFormat('d F Y') : '-' }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase text-gray-400">Rombel Saat Ini &amp; Status</span>
                    <span class="text-gray-800 dark:text-gray-200 font-semibold">
                        {{ $student->classroom?->name ?? 'Belum Masuk Kelas' }} ({{ strtoupper((string) ($student->status ?? 'active')) }})
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase text-gray-400">Nama Ibu Kandung</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $student->mother_name ?: '-' }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase text-gray-400">Orang Tua / Wali Akun</span>
                    <span class="text-gray-800 dark:text-gray-200">
                        {{ $student->parent?->name ?? '-' }}
                        @if($student->parent?->phone_number)
                            ({{ $student->parent->phone_number }})
                        @endif
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase text-gray-400">Alamat</span>
                    <span class="text-gray-800 dark:text-gray-200">{{ $student->address ?: '-' }}</span>
                </div>
            </div>
        </div>

        {{-- B. Riwayat Penempatan Rombel per Tahun Ajaran --}}
        <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-200/60 dark:border-gray-700/50 shadow-sm space-y-4">
            <h3 class="text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 pb-2">
                B. Riwayat Rombongan Belajar per Tahun Ajaran
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-[11px] font-black uppercase text-gray-500">
                            <th class="py-2.5 px-3">Tahun Ajaran</th>
                            <th class="py-2.5 px-3">Kelas / Rombel</th>
                            <th class="py-2.5 px-3">Wali Kelas</th>
                            <th class="py-2.5 px-3">Periode</th>
                            <th class="py-2.5 px-3">Status Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($enrollments as $enrollment)
                            <tr>
                                <td class="py-2.5 px-3 font-bold text-gray-900 dark:text-white">{{ $enrollment->schoolYear?->name ?? '-' }}</td>
                                <td class="py-2.5 px-3">{{ $enrollment->classroom?->name ?? '-' }}</td>
                                <td class="py-2.5 px-3">{{ $enrollment->classroom?->teacher?->name ?? '-' }}</td>
                                <td class="py-2.5 px-3 text-xs text-gray-500">
                                    {{ $enrollment->started_on?->translatedFormat('d M Y') ?? '-' }}
                                    &ndash;
                                    {{ $enrollment->ended_on?->translatedFormat('d M Y') ?? 'Sekarang' }}
                                </td>
                                <td class="py-2.5 px-3 uppercase text-xs font-bold text-indigo-700 dark:text-indigo-400">
                                    {{ str_replace('_', ' ', (string) $enrollment->status) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-4 px-3 text-center text-gray-400 text-xs">Belum ada riwayat penempatan rombel tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- C. Riwayat Mutasi Siswa --}}
        <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-200/60 dark:border-gray-700/50 shadow-sm space-y-4">
            <h3 class="text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 pb-2">
                C. Riwayat Mutasi &amp; Rekap Presensi Kumulatif
            </h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
                <div class="p-3 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30">
                    <span class="block text-[10px] font-bold uppercase text-emerald-700">Hadir (H)</span>
                    <span class="text-xl font-black text-emerald-700">{{ $attendanceSummary['H'] }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-amber-50 dark:bg-amber-950/30">
                    <span class="block text-[10px] font-bold uppercase text-amber-700">Sakit (S)</span>
                    <span class="text-xl font-black text-amber-700">{{ $attendanceSummary['S'] }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-indigo-50 dark:bg-indigo-950/30">
                    <span class="block text-[10px] font-bold uppercase text-indigo-700">Izin (I)</span>
                    <span class="text-xl font-black text-indigo-700">{{ $attendanceSummary['I'] }}</span>
                </div>
                <div class="p-3 rounded-2xl bg-rose-50 dark:bg-rose-950/30">
                    <span class="block text-[10px] font-bold uppercase text-rose-700">Alpa (A)</span>
                    <span class="text-xl font-black text-rose-700">{{ $attendanceSummary['A'] }}</span>
                </div>
            </div>

            @if($mutations->isNotEmpty())
                <div class="overflow-x-auto pt-2">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-[11px] font-black uppercase text-gray-500">
                                <th class="py-2 px-3">Tanggal</th>
                                <th class="py-2 px-3">Jenis</th>
                                <th class="py-2 px-3">Sekolah Asal / Tujuan</th>
                                <th class="py-2 px-3">Alasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach($mutations as $mutation)
                                <tr>
                                    <td class="py-2 px-3">{{ \Carbon\Carbon::parse($mutation->mutation_date)->translatedFormat('d M Y') }}</td>
                                    <td class="py-2 px-3 font-bold uppercase text-xs">{{ $mutation->type === 'in' ? 'Mutasi Masuk' : 'Mutasi Keluar' }}</td>
                                    <td class="py-2 px-3">{{ $mutation->target_school ?: '-' }}</td>
                                    <td class="py-2 px-3">{{ $mutation->reason ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-xs text-gray-400">Tidak ada catatan mutasi masuk maupun keluar.</p>
            @endif
        </div>

        {{-- D. Riwayat Nilai Rapor & Mata Pelajaran per Semester --}}
        <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-200/60 dark:border-gray-700/50 shadow-sm space-y-5">
            <h3 class="text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 pb-2">
                D. Riwayat Nilai Rapor &amp; Akademik per Semester
            </h3>
            @forelse($semesterSummaries as $summary)
                <div class="p-4 rounded-2xl border border-gray-200/70 dark:border-gray-700 space-y-3">
                    <div class="flex flex-wrap justify-between items-center gap-2">
                        <div class="font-bold text-gray-900 dark:text-white">
                            Tahun Ajaran {{ $summary['academic_year']?->year ?? '-' }} &mdash; Semester {{ ucfirst((string) ($summary['academic_year']?->semester ?? '-')) }}
                        </div>
                        @if($summary['report_card'])
                            <div class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">
                                Rata-rata Rapor: {{ number_format((float) $summary['report_card']->average_score, 2) }}
                                @if($summary['report_card']->rank_in_class)
                                    &middot; Peringkat Kelas: #{{ $summary['report_card']->rank_in_class }}
                                @endif
                            </div>
                        @endif
                    </div>
                    @if($summary['subjects']->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700 text-gray-500 uppercase font-bold">
                                        <th class="py-2 px-3">Mata Pelajaran</th>
                                        <th class="py-2 px-3">Kelompok</th>
                                        <th class="py-2 px-3 text-right">KKM</th>
                                        <th class="py-2 px-3 text-right">Nilai Rata-rata</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                                    @foreach($summary['subjects'] as $subjectRow)
                                        <tr>
                                            <td class="py-2 px-3 font-semibold">{{ $subjectRow['subject_name'] }}</td>
                                            <td class="py-2 px-3">{{ $subjectRow['group'] }}</td>
                                            <td class="py-2 px-3 text-right">{{ $subjectRow['kkm'] }}</td>
                                            <td class="py-2 px-3 text-right font-bold">{{ $subjectRow['average_score'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    @if($summary['report_card']?->homeroom_notes)
                        <p class="text-xs text-gray-600 dark:text-gray-300 italic">Catatan Wali Kelas: {{ $summary['report_card']->homeroom_notes }}</p>
                    @endif
                </div>
            @empty
                <p class="text-xs text-gray-400">Belum ada catatan nilai rapor atau nilai semester.</p>
            @endforelse
        </div>

        {{-- E. Prestasi, Ekstrakurikuler & Kelulusan --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-200/60 dark:border-gray-700/50 shadow-sm space-y-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 pb-2">
                    E. Prestasi Tervalidasi
                </h3>
                @forelse($achievements as $ach)
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 text-xs">
                        <div class="font-bold text-gray-900 dark:text-white">{{ $ach->title }}</div>
                        <div class="text-gray-500 mt-0.5">{{ strtoupper((string) $ach->level) }} &middot; {{ $ach->organizer }} &middot; {{ $ach->event_date?->translatedFormat('d M Y') }}</div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">Belum ada catatan prestasi tervalidasi.</p>
                @endforelse
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 rounded-3xl p-6 border border-gray-200/60 dark:border-gray-700/50 shadow-sm space-y-3">
                <h3 class="text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-200 border-b border-gray-100 dark:border-gray-700 pb-2">
                    F. Ekstrakurikuler &amp; Kelulusan
                </h3>
                @forelse($extracurriculars as $extra)
                    <div class="flex justify-between items-center p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900/40 text-xs">
                        <span class="font-bold text-gray-800 dark:text-gray-200">{{ $extra->member?->extracurricular?->name ?? '-' }}</span>
                        <span class="font-semibold text-indigo-700 dark:text-indigo-400">{{ $extra->grade ?: '-' }}</span>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">Belum ada catatan nilai ekstrakurikuler.</p>
                @endforelse

                @if($alumni)
                    <div class="mt-3 p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 text-xs space-y-1">
                        <div class="font-bold text-emerald-800 dark:text-emerald-300">Data Kelulusan / Alumni</div>
                        <div>Tahun Lulus: <span class="font-bold">{{ $alumni->graduation_year }}</span></div>
                        <div>Aktivitas Lanjutan: {{ $alumni->current_activity ?: '-' }} @if($alumni->institution_name) ({{ $alumni->institution_name }}) @endif</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
