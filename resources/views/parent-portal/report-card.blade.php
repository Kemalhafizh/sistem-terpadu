<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">Rapor Hasil Belajar</h2>
                <p class="text-sm font-semibold text-gray-500 mt-1">Laporan Nilai Akademik Resmi Siswa</p>
            </div>
            <a href="{{ route('parent-portal.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Child Profile Summary -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-150/60 flex flex-col md:flex-row justify-between gap-6">
                <div class="flex items-center gap-5">
                    <div class="w-14 h-14 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center font-black text-2xl">
                        {{ substr($student->user->name, 0, 1) }}
                    </div>
                    <div>
                        <h3 class="text-xl font-black text-gray-800">{{ $student->user->name }}</h3>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mt-0.5">
                            Kelas: {{ $student->classroom->name }} • NIS: {{ $student->nis }}
                        </p>
                    </div>
                </div>
                
                <div class="flex gap-4 items-center">
                    <div class="bg-indigo-50/50 px-4 py-3 rounded-2xl border border-indigo-100/50 text-center">
                        <span class="text-gray-400 font-semibold uppercase text-[9px] block">Rata-Rata</span>
                        <strong class="text-indigo-700 font-black text-xl block mt-0.5">{{ round($student->grades()->avg('score') ?? 0, 1) }}</strong>
                    </div>
                    <div class="bg-indigo-50/50 px-4 py-3 rounded-2xl border border-indigo-100/50 text-center">
                        <span class="text-gray-400 font-semibold uppercase text-[9px] block">Peringkat</span>
                        <strong class="text-indigo-700 font-black text-xl block mt-0.5">{{ $rank }} <span class="text-xs font-normal">dari {{ $totalClassStudents }}</span></strong>
                    </div>
                </div>
            </div>

            <!-- Subject Grades Ledger -->
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-150/60 overflow-hidden">
                <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                    <h4 class="text-sm font-black text-gray-800 uppercase tracking-wider">Ledger Nilai Mata Pelajaran</h4>
                    <span class="px-2.5 py-1 bg-emerald-50 text-emerald-600 rounded-lg text-xs font-black uppercase tracking-wider">
                        Semester Aktif
                    </span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-100 text-[10px] font-black uppercase tracking-wider text-gray-400">
                                <th class="py-4 px-6">Mata Pelajaran</th>
                                <th class="py-4 px-6 text-center">KKM</th>
                                <th class="py-4 px-6 text-center">Tugas / Harian</th>
                                <th class="py-4 px-6 text-center">UTS (Tengah Semester)</th>
                                <th class="py-4 px-6 text-center">UAS (Akhir Semester)</th>
                                <th class="py-4 px-6 text-center">Nilai Akhir</th>
                                <th class="py-4 px-6 text-center">Predikat</th>
                                <th class="py-4 px-6 text-center">Status Kelulusan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm font-semibold text-gray-700">
                            @forelse($grades as $subjectId => $subjectGrades)
                                @php 
                                    $subject = $subjectGrades->first()?->subject;
                                    $kkm = $subject->kkm ?? 75;
                                    
                                    $tugas = $subjectGrades->where('type', 'tugas')->avg('score') ?? $subjectGrades->where('type', 'Harian')->avg('score');
                                    $uts = $subjectGrades->where('type', 'uts')->avg('score') ?? $subjectGrades->where('type', 'UTS')->avg('score');
                                    $uas = $subjectGrades->where('type', 'uas')->avg('score') ?? $subjectGrades->where('type', 'UAS')->avg('score');
                                    
                                    // Calculate final score: e.g. 40% tugas, 30% uts, 30% uas, fallback to average if some are missing
                                    $scoresCount = 0;
                                    $scoresSum = 0;
                                    if ($tugas !== null) { $scoresSum += $tugas * 0.4; $scoresCount += 0.4; }
                                    if ($uts !== null) { $scoresSum += $uts * 0.3; $scoresCount += 0.3; }
                                    if ($uas !== null) { $scoresSum += $uas * 0.3; $scoresCount += 0.3; }
                                    
                                    $finalScore = $scoresCount > 0 ? ($scoresSum / $scoresCount) : $subjectGrades->avg('score');
                                    $finalScore = round($finalScore, 1);
                                    
                                    $isPassed = $finalScore >= $kkm;
                                    
                                    $predikat = match (true) {
                                        $finalScore >= 90 => 'A (Sangat Baik)',
                                        $finalScore >= 80 => 'B (Baik)',
                                        $finalScore >= $kkm => 'C (Cukup)',
                                        default => 'D (Kurang)',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4 px-6 font-extrabold text-gray-900">{{ $subject->name }}</td>
                                    <td class="py-4 px-6 text-center text-gray-500">{{ $kkm }}</td>
                                    <td class="py-4 px-6 text-center">
                                        {{ $tugas !== null ? round($tugas, 1) : '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        {{ $uts !== null ? round($uts, 1) : '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        {{ $uas !== null ? round($uas, 1) : '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-center font-black text-indigo-600">
                                        {{ $finalScore }}
                                    </td>
                                    <td class="py-4 px-6 text-center text-xs font-black">
                                        {{ $predikat }}
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @if($isPassed)
                                            <span class="inline-flex items-center px-2.5 py-1 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-black uppercase tracking-wider">
                                                Lulus KKM
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-black uppercase tracking-wider">
                                                Di Bawah KKM
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-gray-400 italic">
                                        Belum ada data nilai akademik terekam untuk siswa.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Homeroom Teacher Notes & PDF Download -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Homeroom Notes Widget (2/3 Columns) -->
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-150/60">
                    <h4 class="text-sm font-black text-gray-800 uppercase tracking-wider mb-6">Catatan & Masukan Wali Kelas</h4>
                    @if($reportCard)
                        <div class="space-y-6">
                            <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100 relative">
                                <span class="absolute top-4 right-4 text-xs font-black text-gray-300 uppercase tracking-widest">
                                    Catatan Umum
                                </span>
                                <p class="text-sm font-semibold text-gray-700 leading-relaxed italic">
                                    "{{ $reportCard->homeroom_notes ?? 'Belum ada catatan wali kelas.' }}"
                                </p>
                            </div>
                            
                            @if($reportCard->extracurricular_notes)
                                <div class="bg-gray-50/50 p-6 rounded-2xl border border-gray-100 relative">
                                    <span class="absolute top-4 right-4 text-xs font-black text-gray-300 uppercase tracking-widest">
                                        Ekstrakurikuler
                                    </span>
                                    <p class="text-sm font-semibold text-gray-700 leading-relaxed italic">
                                        "{{ $reportCard->extracurricular_notes }}"
                                    </p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-10 text-center text-gray-400 italic">
                            Belum ada lembar rapor resmi atau masukan wali kelas yang diterbitkan untuk semester ini.
                        </div>
                    @endif
                </div>

                <!-- PDF Print Widget (1/3 Column) -->
                <div class="lg:col-span-1 bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-150/60 flex flex-col justify-between">
                    <div>
                        <h4 class="text-sm font-black text-gray-800 uppercase tracking-wider mb-4">Unduh Dokumen Rapor</h4>
                        <p class="text-xs text-gray-400 font-semibold leading-relaxed">
                            Unduh dokumen cetak rapor digital resmi yang diterbitkan oleh sekolah untuk disalin atau dicetak secara mandiri.
                        </p>
                        
                        <div class="bg-indigo-50/40 border border-indigo-100/50 rounded-2xl p-4 mt-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider block">Format File</span>
                                <strong class="text-gray-800 font-black text-xs block">E-Rapor Resmi.PDF</strong>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        @if($reportCard)
                            <a href="{{ route('report-cards.pdf', $student->id) }}" target="_blank" class="w-full text-center py-4 bg-primary-600 hover:bg-primary-500 text-white rounded-2xl text-xs font-black uppercase tracking-wider shadow-lg shadow-primary-900/10 transition-all flex items-center justify-center gap-2">
                                📥 Cetak Rapor Digital PDF
                            </a>
                        @else
                            <button disabled class="w-full py-4 bg-gray-100 text-gray-400 rounded-2xl text-xs font-black uppercase tracking-wider cursor-not-allowed">
                                Rapor Belum Diterbitkan
                            </button>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
