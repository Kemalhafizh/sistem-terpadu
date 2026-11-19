<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kompilasi & Integrasi e-Rapor (Kemendikdasmen)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4">
                <form method="GET" action="{{ route('report-cards.index') }}" class="flex flex-wrap w-full lg:w-auto items-end gap-4">
                    <div class="w-full sm:w-64">
                        <label for="classroom_id" class="block font-medium text-sm text-gray-700 mb-1">Pilih Kelas Binaan</label>
                        <select id="classroom_id" name="classroom_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms ?? [] as $classroom)
                                <option value="{{ $classroom->id }}" {{ $selectedClassroomId == $classroom->id ? 'selected' : '' }}>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:w-44">
                        <label for="report_type" class="block font-medium text-sm text-gray-700 mb-1">Jenis Rapor</label>
                        <select id="report_type" name="report_type" class="block w-full rounded-md shadow-sm border-gray-300" onchange="this.form.submit()">
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
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('error') }}</span>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl">
                    <ul class="list-disc pl-5 text-sm">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if($selectedClassroomId && $completeness)
                <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <div>
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800">Dasbor Kelengkapan Input Nilai</h3>
                            <p class="text-xs text-gray-500">
                                Total Siswa: {{ $completeness['total_students'] }} | Catatan Wali Kelas Terisi: {{ $completeness['notes_filled'] }}
                            </p>
                        </div>
                        @if($completeness['deadline'])
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                Batas Input Nilai: {{ $completeness['deadline']['date'] }} ({{ $completeness['deadline']['title'] }})
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        @forelse($completeness['subjects'] as $sub)
                            <div class="p-3 rounded-lg border {{ $sub['is_complete'] ? 'border-emerald-200 bg-emerald-50/40' : 'border-amber-200 bg-amber-50/40' }}">
                                <div class="text-xs font-bold text-gray-900">{{ $sub['name'] }}</div>
                                <div class="text-xs text-gray-600 mt-1">
                                    Nilai: {{ $sub['scored_count'] }}/{{ $sub['total_students'] }} | TP Siap: {{ $sub['tp_ready_count'] }}/{{ $sub['total_students'] }}
                                </div>
                            </div>
                        @empty
                            <div class="text-xs text-gray-500 col-span-3">Belum ada mapel dengan nilai atau jadwal pada kelas ini.</div>
                        @endforelse
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-3">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800">Unggah Massal PDF Rapor Resmi (e-Rapor)</h3>
                        <p class="text-xs text-gray-500">Unggah banyak file PDF sekaligus atau satu arsip ZIP. Sistem mencocokkan nama file dengan NISN siswa secara otomatis.</p>
                        <form method="POST" action="{{ route('report-cards.upload-official-pdfs') }}" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                            <div>
                                <label for="upload_template_id" class="block text-xs font-semibold text-gray-700 mb-1">Template / Periode Rapor</label>
                                <select id="upload_template_id" name="template_id" required class="block w-full rounded-md border-gray-300 text-sm">
                                    <option value="">-- Pilih Template Rapor --</option>
                                    @foreach($templates as $tpl)
                                        <option value="{{ $tpl->id }}">{{ $tpl->name }} ({{ strtoupper($tpl->report_type ?? 'PAS') }} - {{ $tpl->status }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="official_pdf_files" class="block text-xs font-semibold text-gray-700 mb-1">Berkas PDF / ZIP</label>
                                <input id="official_pdf_files" type="file" name="files[]" multiple accept=".pdf,.zip" required class="block w-full text-xs text-gray-700">
                            </div>
                            <button type="submit" class="px-4 py-2 bg-primary-700 hover:bg-primary-800 text-white rounded-lg text-xs font-bold uppercase tracking-wider">
                                Unggah & Cocokkan NISN
                            </button>
                        </form>
                    </div>

                    @if(\App\Http\Middleware\RoleMiddleware::allowsUser(auth()->user(), ['admin', 'superadmin', 'waka_kurikulum', ...\App\Support\Access\Roles::PRINCIPAL]))
                        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-3">
                            <h3 class="text-sm font-bold uppercase tracking-wider text-gray-800">Penerbitan Massal per Angkatan</h3>
                            <p class="text-xs text-gray-500">Terbitkan seluruh rapor resmi angkatan agar tampil untuk siswa dan orang tua di aplikasi Hanyakra.</p>
                            <form method="POST" action="{{ route('report-cards.publish-batch') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @csrf
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Angkatan</label>
                                    <select name="grade_level" required class="block w-full rounded-md border-gray-300 text-sm">
                                        <option value="X">Kelas X (Fase E)</option>
                                        <option value="XI">Kelas XI (Fase F)</option>
                                        <option value="XII">Kelas XII (Fase F)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jenis Rapor</label>
                                    <select name="report_type" required class="block w-full rounded-md border-gray-300 text-sm">
                                        <option value="pas">Akhir Semester (PAS)</option>
                                        <option value="pts">Tengah Semester (PTS)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Semester ke-</label>
                                    <input type="number" name="semester_number" min="1" max="6" value="1" required class="block w-full rounded-md border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Pengesahan</label>
                                    <input type="date" name="issued_date" value="{{ now()->toDateString() }}" class="block w-full rounded-md border-gray-300 text-sm">
                                </div>
                                <div class="sm:col-span-2">
                                    <button type="submit" class="px-4 py-2 bg-emerald-700 hover:bg-emerald-800 text-white rounded-lg text-xs font-bold uppercase tracking-wider">
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
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                    <form method="POST" action="{{ route('report-cards.store-notes') }}">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-primary-900 text-white">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-16">Rank</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Nama Siswa</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-32 bg-primary-800">Total Nilai</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-32 bg-primary-700">Rata-Rata</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Catatan Wali Kelas</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-44">Dokumen Rapor</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
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
                                        <tr class="hover:bg-blue-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($rank == 1)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-black border-2 border-amber-300" title="Juara 1">1</span>
                                                @elseif($rank == 2)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500 font-black border-2 border-gray-300" title="Juara 2">2</span>
                                                @elseif($rank == 3)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-600 font-black border-2 border-orange-300" title="Juara 3">3</span>
                                                @else
                                                    <span class="text-sm font-bold text-gray-500">{{ $rank }}</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900">
                                                {{ $student->user->name ?? 'Siswa' }}
                                                <div class="text-xs font-normal text-gray-400">NIS: {{ $student->nis }} | NISN: {{ $student->nisn }}</div>
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-700 bg-gray-50">
                                                {{ number_format($total, 2) }}
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-center font-black text-primary-600 bg-primary-50">
                                                {{ number_format($avg, 2) }}
                                            </td>

                                            <td class="px-6 py-4">
                                                @if($reportId)
                                                    <input type="text" name="report_cards[{{ $reportId }}][homeroom_notes]" value="{{ $notes }}" class="block w-full text-sm rounded-md shadow-sm border-gray-300 focus:border-primary-400 focus:ring focus:ring-primary-200 focus:ring-opacity-50 transition-colors" placeholder="Ketik catatan untuk siswa ini...">
                                                @else
                                                    <span class="text-xs text-red-500 italic">Data rapor belum dikalkulasi.</span>
                                                @endif
                                            </td>

                                            <td class="px-6 py-4 whitespace-nowrap text-center space-x-1">
                                                <a href="{{ route('report-cards.pdf', ['student' => $student->id, 'report_type' => $selectedReportType ?? 'pas']) }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
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
                                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                                Tidak ada data siswa di kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-primary-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 transition ease-in-out duration-150 shadow-md">
                                Simpan Seluruh Catatan
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-xl">
                    <div class="ml-3">
                        <p class="text-sm text-blue-700 font-medium">
                            Silakan pilih kelas binaan Anda untuk melihat kelengkapan nilai, mengekspor CSV e-Rapor, mengunggah PDF resmi, dan meninjau catatan wali kelas.
                        </p>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
