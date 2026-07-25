<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </span>
                    Multi-Format Batch Export Engine & Interoperability
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Mesin ekspor batch universal mendukung format PDF/A, Excel XLSX, CSV, JSON-LD, dan XML kompatibel Dapodik/EMIS.</p>
            </div>
            <a href="{{ route('exports.history') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Monitoring Riwayat Ekspor
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Format Selector Grid Badges -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="px-2.5 py-0.5 bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400 font-mono text-[10px] font-bold rounded">ISO 19005</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">PDF/A Document</h4>
                    <p class="text-[11px] text-gray-500">Arsip permanen berstandar ISO untuk cetakan resmi.</p>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 font-mono text-[10px] font-bold rounded">MICROSOFT</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">Excel XLSX</h4>
                    <p class="text-[11px] text-gray-500">Spreadsheet multi-sheet dengan formula & styling.</p>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400 font-mono text-[10px] font-bold rounded">RFC 4180</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">CSV Plain Text</h4>
                    <p class="text-[11px] text-gray-500">Format data mentah cepat terpisah koma.</p>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="px-2.5 py-0.5 bg-purple-100 text-purple-700 dark:bg-purple-950 dark:text-purple-400 font-mono text-[10px] font-bold rounded">W3C SPEC</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">JSON-LD Linked</h4>
                    <p class="text-[11px] text-gray-500">Payload metadata terstruktur untuk API & AI Integration.</p>
                </div>

                <div class="p-4 bg-white dark:bg-gray-800 rounded-2xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 font-mono text-[10px] font-bold rounded">KEMDIKBUD/EMIS</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-white">XML Dapodik</h4>
                    <p class="text-[11px] text-gray-500">Skema XML sinkronisasi data nasional.</p>
                </div>
            </div>

            <!-- Generate Batch Export Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Formulir Pembuatan Batch Export Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pilih entitas data dan format keluaran yang diinginkan.</p>
                </div>

                <form action="{{ route('exports.generate') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Pekerjaan Ekspor</label>
                        <input type="text" name="job_name" required value="Ekspor Data Pokok Siswa Semester Genap" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Entitas Dataset Target</label>
                        <select name="entity_type" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="students">Data Pokok Siswa & Profil (students)</option>
                            <option value="teachers">Data Guru & Tenaga Kependidikan (teachers)</option>
                            <option value="grades">Nilai Rapor & Transkrip Akademik (grades)</option>
                            <option value="finance">Laporan Keuangan SPP & Wadiah (finance)</option>
                            <option value="inventory">Inventaris Aset & Sarpras (inventory)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Format Berkas Keluaran</label>
                        <select name="format" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold">
                            <option value="xlsx">Excel XLSX (.xlsx)</option>
                            <option value="pdf_a">PDF/A Document (.pdf)</option>
                            <option value="csv">CSV Plain Text (.csv)</option>
                            <option value="json_ld">JSON-LD Linked Data (.json)</option>
                            <option value="xml_dapodik">XML Dapodik / EMIS (.xml)</option>
                        </select>
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30">
                            Jalankan Batch Export Engine
                        </button>
                    </div>
                </form>
            </div>

            <!-- Recent Export Jobs Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Hasil Ekspor Terkini</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Unduh berkas ekspor data yang telah selesai diproses.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Nama Ekspor</th>
                                <th class="px-6 py-4">Entitas Data</th>
                                <th class="px-6 py-4">Format</th>
                                <th class="px-6 py-4">Status Pemrosesan</th>
                                <th class="px-6 py-4">Pemohon</th>
                                <th class="px-6 py-4 text-right">Aksi Unduh</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($exportHistory as $job)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $job->job_name }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px] uppercase">{{ $job->entity_type }}</td>
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400 uppercase">{{ $job->format }}</td>
                                    <td class="px-6 py-4">
                                        @if($job->status === 'completed')
                                            <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 font-mono text-[10px] font-bold rounded uppercase">COMPLETED</span>
                                        @else
                                            <span class="px-2.5 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 font-mono text-[10px] font-bold rounded uppercase">{{ $job->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">{{ optional($job->user)->name }}</td>
                                    <td class="px-6 py-4 text-right">
                                        @if($job->status === 'completed')
                                            <a href="{{ route('exports.download', $job->id) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm inline-flex items-center gap-1">
                                                <span>Unduh Berkas</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                </svg>
                                            </a>
                                        @else
                                            <span class="text-gray-400 italic text-[11px]">Memproses...</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada riwayat pekerjaan ekspor data.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
