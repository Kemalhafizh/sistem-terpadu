<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 dark:text-white leading-tight">
            {{ __('Kompilasi & Integrasi e-Rapor (Kemendikdasmen)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Section -->
            <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4">
                <form method="GET" action="{{ route('report-cards.index') }}" class="flex flex-wrap w-full lg:w-auto items-end gap-4">
                    <div class="w-full sm:w-64">
                        <label for="classroom_id" class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-1">Pilih Kelas Binaan</label>
                        <select id="classroom_id" name="classroom_id" class="block w-full rounded-md shadow-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 focus:border-primary-400 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms ?? [] as $classroom)
                                <option value="{{ $classroom->id }}" {{ $selectedClassroomId == $classroom->id ? 'selected' : '' }}>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-44">
                        <label for="report_type" class="block font-medium text-sm text-slate-700 dark:text-slate-300 mb-1">Jenis Rapor</label>
                        <select id="report_type" name="report_type" class="block w-full rounded-md shadow-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100" onchange="this.form.submit()">
                            <option value="pas" {{ ($selectedReportType ?? 'pas') === 'pas' ? 'selected' : '' }}>Akhir Semester (PAS)</option>
                            <option value="pts" {{ ($selectedReportType ?? 'pas') === 'pts' ? 'selected' : '' }}>Tengah Semester (PTS)</option>
                        </select>
                    </div>
                </form>

                @if($selectedClassroomId)
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('report-cards.sync') }}">
                            @csrf
                            <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-amber-700 shadow-sm transition-all">
                                Kalkulasi Ulang Rapor
                            </button>
                        </form>

                        <a href="{{ route('report-cards.export-erapor', ['classroom_id' => $selectedClassroomId, 'report_type' => $selectedReportType ?? 'pas']) }}"
                           class="inline-flex items-center px-4 py-2 bg-emerald-700 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-emerald-800 shadow-sm transition-all">
                            Ekspor CSV e-Rapor
                        </a>
                    </div>
                @endif
            </div>

            @if(session('success'))
                <div class="bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-300 px-4 py-3 rounded-xl">
                    <ul class="list-disc ps-5 text-sm">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($selectedClassroomId && $completeness)
                <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-white">Dasbor Kelengkapan Input Nilai</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                Total Siswa: {{ $completeness['total_students'] }} | Catatan Wali Kelas Terisi: {{ $completeness['notes_filled'] }}
                            </p>
                        </div>
                        @if($completeness['deadline'])
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                Batas Input Nilai: {{ $completeness['deadline']['date'] }} ({{ $completeness['deadline']['title'] }})
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @forelse($completeness['subjects'] as $sub)
                            <div class="p-3 rounded-lg border {{ $sub['is_complete'] ? 'border-emerald-200 dark:border-emerald-800 bg-emerald-50/40 dark:bg-emerald-950/30' : 'border-amber-200 dark:border-amber-800 bg-amber-50/40 dark:bg-amber-950/30' }}">
                                <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $sub['name'] }}</div>
                                <div class="text-xs text-slate-600 dark:text-slate-300 mt-1">
                                    Nilai: {{ $sub['scored_count'] }}/{{ $sub['total_students'] }} | TP Siap: {{ $sub['tp_ready_count'] }}/{{ $sub['total_students'] }}
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-slate-500 dark:text-slate-400 col-span-3">Belum ada mapel dengan nilai atau jadwal pada kelas ini.</div>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-white">Unggah Massal PDF Rapor Resmi (e-Rapor)</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Unggah banyak file PDF sekaligus atau satu arsip ZIP. Sistem mencocokkan nama file dengan NISN siswa secara otomatis.</p>
                        <form method="POST" action="{{ route('report-cards.upload-official-pdfs') }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                            <div>
                                <label for="upload_template_id" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Template / Periode Rapor</label>
                                <select id="upload_template_id" name="template_id" required class="block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                                    <option value="">-- Pilih Template Rapor --</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ strtoupper($tpl->report_type ?? 'PAS') }} - {{ $tpl->status }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="official_pdf_files" class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Berkas PDF / ZIP</label>
                                <input id="official_pdf_files" type="file" name="files[]" multiple accept=".pdf,.zip" required class="block w-full text-xs text-slate-700 dark:text-slate-300 file:me-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200">
                            </div>
                            <button type="submit" class="px-4 py-2 bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition-colors">
                                Unggah & Cocokkan NISN
                            </button>
                        </form>
                    </div>

                    @if(\App\Http\Middleware\RoleMiddleware::allowsUser(auth()->user(), ['admin', 'superadmin', 'waka_kurikulum', ...\App\Support\Access\Roles::PRINCIPAL]))
                        <div class="bg-white dark:bg-slate-800 p-6 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm space-y-3">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-800 dark:text-white">Penerbitan Massal per Angkatan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Terbitkan seluruh rapor resmi angkatan agar tampil untuk siswa dan orang tua di aplikasi Hanyakra.</p>
                            <form method="POST" action="{{ route('report-cards.publish-batch') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3" onsubmit="return confirm('Terbitkan seluruh rapor resmi angkatan ini agar dapat dilihat oleh siswa dan orang tua?')">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Angkatan</label>
                                    <select name="grade_level" required class="block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                                        <option value="X">Kelas X (Fase E)</option>
                                        <option value="XI">Kelas XI (Fase F)</option>
                                        <option value="XII">Kelas XII (Fase F)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Jenis Rapor</label>
                                    <select name="report_type" required class="block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                                        <option value="pas">Akhir Semester (PAS)</option>
                                        <option value="pts">Tengah Semester (PTS)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Semester ke-</label>
                                    <input type="number" name="semester_number" min="1" max="6" value="1" required class="block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Tanggal Pengesahan</label>
                                    <input type="date" name="issued_date" value="{{ now()->toDateString() }}" class="block w-full rounded-md border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 text-sm">
                                </div>
                                <div class="sm:col-span-2">
                                    <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold uppercase tracking-wider transition-colors">
                                        Terbitkan ke Siswa & Orang Tua
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Ledger Table -->
            @if($selectedClassroomId)
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm rounded-xl border border-slate-200 dark:border-slate-700">
                    <form method="POST" action="{{ route('report-cards.store-notes') }}">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                                <thead class="bg-primary-900 text-white">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-16">Rank</th>
                                        <th scope="col" class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider">Nama Siswa</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-32 bg-primary-800">Total Nilai</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-32 bg-primary-700">Rata-Rata</th>
                                        <th scope="col" class="px-6 py-4 text-start text-xs font-black uppercase tracking-wider">Catatan Wali Kelas</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-44">Dokumen Rapor</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                                    @forelse($students ?? [] as $index => $student)
                                        @php
                                            $report = $reportCards->get($student->id);
                                            $rank = $report ? $report->rank_in_class : '-';
                                            $total = $report ? $report->total_score : 0;
                                            $avg = $report ? $report->average_score : 0;
                                            $notes = $report ? $report->homeroom_notes : '';
                                            $reportId = $report ? $report->id : null;
                                            $officialPdf = $officialPdfs->get($student->user_id);
                                        @endphp
                                        <tr class="hover:bg-blue-50/60 dark:hover:bg-slate-700/40 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($rank == 1)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-600 dark:text-amber-300 font-black border-2 border-amber-300 dark:border-amber-700" title="Juara 1">1</span>
                                                @elseif($rank == 2)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-200 font-black border-2 border-slate-300 dark:border-slate-500" title="Juara 2">2</span>
                                                @elseif($rank == 3)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 dark:bg-orange-900/50 text-orange-600 dark:text-orange-300 font-black border-2 border-orange-300 dark:border-orange-700" title="Juara 3">3</span>
                                                @else
                                                    <span class="text-sm font-bold text-slate-500 dark:text-slate-400">{{ $rank }}</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-slate-900 dark:text-white">
                                                {{ $student->user->name ?? 'Siswa' }}
                                                <div class="text-xs font-normal text-slate-400 dark:text-slate-400">NIS: {{ $student->nis }} | NISN: {{ $student->nisn }}</div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-slate-700 dark:text-slate-200 bg-slate-50 dark:bg-slate-900/40">
                                                {{ number_format($total, 2) }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-center font-black text-primary-600 dark:text-primary-300 bg-primary-50 dark:bg-primary-950/40">
                                                {{ number_format($avg, 2) }}
                                            </td>

                                            <td class="px-6 py-4">
                                                @if($reportId)
                                                    <input type="text" name="report_cards[{{ $reportId }}][homeroom_notes]" value="{{ $notes }}" class="block w-full text-sm rounded-md shadow-sm border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 focus:border-primary-400 focus:ring focus:ring-primary-200 focus:ring-opacity-50 transition-colors" placeholder="Ketik catatan untuk siswa ini...">
                                                @else
                                                    <span class="text-xs text-rose-500 dark:text-rose-400 italic">Data rapor belum dikalkulasi.</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-1">
                                                <a href="{{ route('report-cards.pdf', ['student' => $student, 'report_type' => $selectedReportType ?? 'pas']) }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-slate-800 dark:bg-slate-700 hover:bg-slate-700 dark:hover:bg-slate-600 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                                                    Pratinjau PDF
                                                </a>
                                                @if($officialPdf)
                                                    <a href="{{ route('report-cards.files.show', $officialPdf->id) }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                                                        PDF Resmi
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-sm text-slate-500 dark:text-slate-400">
                                                Tidak ada data siswa di kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-6 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-primary-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 transition ease-in-out duration-150 shadow-md">
                                Simpan Seluruh Catatan
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-blue-50 dark:bg-blue-950/30 border-s-4 border-blue-500 p-6 rounded-e-xl">
                    <div class="ms-3">
                        <p class="text-sm text-blue-700 dark:text-blue-200 font-medium">
                            Silakan pilih kelas binaan Anda untuk melihat kelengkapan nilai, mengekspor CSV e-Rapor, mengunggah PDF resmi, dan meninjau catatan wali kelas.
                        </p>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
