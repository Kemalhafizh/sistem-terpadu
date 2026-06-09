<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Rekap Mutaba\'ah Kelas') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Kelas: {{ $classroom->name }} | Wali Kelas: {{ $classroom->teacher->name ?? '-' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('mutabaah.export-pdf', ['classroom' => $classroom->id, 'week_start' => $weekStart]) }}" 
                   class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-md shadow-rose-900/10 transition-all flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Unduh PDF Rekap
                </a>
                <a href="{{ route('mutabaah.statistik', ['classroom_id' => $classroom->id]) }}" 
                   class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold uppercase tracking-wider border border-gray-200 shadow-sm transition-all">
                    Statistik Kelas
                </a>
            </div>
        </div>
    </x-slot>

    @php
        $carbonWeekStart = \Carbon\Carbon::parse($weekStart);
        $prevWeek = $carbonWeekStart->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $carbonWeekStart->copy()->addWeek()->format('Y-m-d');
        
        // Compute overall classroom metrics
        $validSummaries = $summaries->whereNotNull('grade');
        $avgScore = $validSummaries->avg('total_score') ?? 0;
        $gradeCounts = $validSummaries->groupBy('grade')->map->count();
        $signedCount = $validSummaries->where('parent_acknowledged', true)->count();
        $totalSiswa = $students->count();
    @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Week Switcher --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center space-x-2">
                    <span class="p-2 bg-emerald-50 text-emerald-600 rounded-xl">📅</span>
                    <div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block leading-none">Periode Laporan</span>
                        <span class="text-sm font-black text-gray-700 mt-1 block">
                            {{ $carbonWeekStart->translatedFormat('d M') }} - {{ \Carbon\Carbon::parse($weekEnd)->translatedFormat('d M Y') }}
                        </span>
                    </div>
                </div>
                
                <div class="flex items-center space-x-2">
                    <a href="{{ route('mutabaah.rekap-kelas', ['classroom' => $classroom->id, 'week_start' => $prevWeek]) }}" 
                       class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <a href="{{ route('mutabaah.rekap-kelas', ['classroom' => $classroom->id, 'week_start' => now()->startOfWeek()->format('Y-m-d')]) }}" 
                       class="px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-xs font-bold text-gray-600 transition-colors">
                        Minggu Ini
                    </a>
                    <a href="{{ route('mutabaah.rekap-kelas', ['classroom' => $classroom->id, 'week_start' => $nextWeek]) }}" 
                       class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm flex items-center space-x-4">
                    <div class="p-3 bg-emerald-50 text-emerald-600 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2"/></svg>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block leading-none">Rata-rata Skor</span>
                        <span class="text-2xl font-black text-gray-800 mt-1 block leading-none">{{ round($avgScore, 1) }}</span>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm flex items-center space-x-4">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block leading-none">Tanda Tangan Ortu</span>
                        <span class="text-2xl font-black text-gray-800 mt-1 block leading-none">{{ $signedCount }} / {{ $totalSiswa }}</span>
                    </div>
                </div>

                <div class="bg-white rounded-3xl border border-gray-100 p-6 shadow-sm flex items-center space-x-4 md:col-span-2">
                    <div class="p-3 bg-amber-50 text-amber-600 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="w-full">
                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block leading-none mb-1">Distribusi Grade</span>
                        <div class="flex gap-3 text-xs font-black">
                            <span class="text-emerald-600">A: {{ $gradeCounts->get('A', 0) }}</span>
                            <span class="text-amber-600">B: {{ $gradeCounts->get('B', 0) }}</span>
                            <span class="text-orange-600">C: {{ $gradeCounts->get('C', 0) }}</span>
                            <span class="text-rose-600">D: {{ $gradeCounts->get('D', 0) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Grade D Warning alert --}}
            @php
                $needsAttention = $validSummaries->where('grade', 'D');
            @endphp
            @if($needsAttention->count() > 0)
                <div class="bg-rose-50 border border-rose-100 rounded-3xl p-5 flex items-start space-x-3 shadow-sm">
                    <span class="text-2xl">⚠️</span>
                    <div>
                        <h4 class="text-sm font-black text-rose-950">PERHATIAN WALI KELAS</h4>
                        <p class="text-xs font-bold text-rose-700 mt-1">
                            Terdapat {{ $needsAttention->count() }} siswa dengan predikat **Grade D (Perlu Pembinaan)** minggu ini. Harap berikan catatan/bimbingan spiritual pada rekap masing-masing siswa di tabel bawah.
                        </p>
                    </div>
                </div>
            @endif

            {{-- Table list --}}
            <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-8 py-5 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-xs font-black text-gray-500 uppercase tracking-[0.2em]">Daftar Mutaba'ah Siswa</h3>
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Total: {{ $totalSiswa }} Siswa</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/20 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="px-8 py-4">Siswa</th>
                                <th class="px-6 py-4 text-center font-bold">Skor & Grade</th>
                                <th class="px-6 py-4 text-center font-bold">Shalat Wajib</th>
                                <th class="px-6 py-4 text-center font-bold">Tadarus</th>
                                <th class="px-6 py-4 text-center font-bold">Ortu</th>
                                <th class="px-6 py-4 font-bold">Catatan Pembinaan</th>
                                <th class="px-8 py-4 text-right font-bold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-xs font-bold text-gray-600">
                            @foreach($students as $student)
                                @php
                                    $summary = $summaries->get($student->id);
                                    
                                    $gradeColors = [
                                        'A' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
                                        'B' => 'bg-amber-50 text-amber-700 border-amber-100',
                                        'C' => 'bg-orange-50 text-orange-700 border-orange-100',
                                        'D' => 'bg-rose-50 text-rose-700 border-rose-100'
                                    ];
                                    $gradeColor = $summary ? ($gradeColors[$summary->grade] ?? 'bg-gray-50 text-gray-700 border-gray-100') : 'bg-gray-50 text-gray-400';
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="px-8 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-black flex items-center justify-center text-xs">
                                                {{ substr($student->user->name, 0, 2) }}
                                            </div>
                                            <div>
                                                <div class="font-black text-gray-800">{{ $student->user->name }}</div>
                                                <div class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">NISN: {{ $student->nisn }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($summary)
                                            <span class="inline-flex flex-col items-center justify-center">
                                                <span class="text-sm font-black text-gray-800">{{ $summary->total_score }}</span>
                                                <span class="px-2 py-0.5 rounded-md border text-[9px] font-black mt-1 uppercase {{ $gradeColor }}">
                                                    Grade {{ $summary->grade }}
                                                </span>
                                            </span>
                                        @else
                                            <span class="text-gray-400 italic">Belum Diisi</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($summary)
                                            <div class="text-gray-700">
                                                <span class="font-black">{{ $summary->sholat_5_waktu_count }}</span>/7 Hari
                                            </div>
                                            <div class="text-[9px] text-gray-400 font-bold mt-0.5">{{ $summary->sholat_berjamaah_count }}x Jamaah</div>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($summary)
                                            <span class="font-black text-gray-700">{{ $summary->tadarus_total_pages }}</span> Hal
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center whitespace-nowrap">
                                        @if($summary)
                                            @if($summary->parent_acknowledged)
                                                <span class="px-2 py-0.5 rounded-lg bg-emerald-50 border border-emerald-100 text-emerald-700 text-[9px] font-black uppercase tracking-wider">
                                                    Signed
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-lg bg-gray-50 border border-gray-200 text-gray-400 text-[9px] font-black uppercase tracking-wider">
                                                    Pending
                                                </span>
                                            @endif
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($summary)
                                            @if($summary->wali_kelas_comment)
                                                <p class="text-xs font-bold text-gray-700 max-w-xs truncate" title="{{ $summary->wali_kelas_comment }}">
                                                    "{{ $summary->wali_kelas_comment }}"
                                                </p>
                                            @else
                                                <span class="text-gray-400 italic">Belum ada catatan</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-4 text-right whitespace-nowrap">
                                        <div class="flex items-center justify-end space-x-2">
                                            @if($summary)
                                                <button onclick="openCommentModal({{ $summary->id }}, '{{ addslashes($summary->wali_kelas_comment) }}')" 
                                                        class="p-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg transition-colors"
                                                        title="Beri Catatan Pembinaan">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                                </button>
                                            @endif
                                            <a href="{{ route('mutabaah.index', ['student_id' => $student->id, 'date' => $weekStart]) }}" 
                                               class="p-2 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-lg transition-colors"
                                               title="Lihat Detail Harian">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- Comment Modal --}}
    <div id="commentModal" class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-xl max-w-md w-full overflow-hidden transform scale-95 transition-all">
            <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                <h3 class="text-sm font-black text-gray-800 uppercase tracking-wider">Tulis Catatan Pembinaan</h3>
                <button onclick="closeCommentModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="commentForm" method="POST">
                @csrf
                <div class="p-6">
                    <label for="commentTextArea" class="text-xs font-bold text-gray-400 uppercase tracking-wider block mb-1">Komentar / Bimbingan</label>
                    <textarea id="commentTextArea" name="wali_kelas_comment" rows="4" 
                              placeholder="Masukkan arahan atau catatan spiritual untuk siswa..."
                              class="w-full border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 rounded-2xl text-xs font-bold text-gray-700"></textarea>
                </div>
                <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-100 flex items-center justify-end space-x-2">
                    <button type="button" onclick="closeCommentModal()" 
                            class="px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 bg-indigo-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-indigo-900/10 transition-all">
                        Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
        function openCommentModal(summaryId, currentComment) {
            const form = document.getElementById('commentForm');
            const textArea = document.getElementById('commentTextArea');
            
            // Set form action dynamically
            form.action = `/mutabaah/weekly-comment/${summaryId}`;
            
            // Pre-fill comment
            textArea.value = currentComment || '';
            
            // Show modal
            const modal = document.getElementById('commentModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeCommentModal() {
            const modal = document.getElementById('commentModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    </script>
    @endpush
</x-app-layout>
