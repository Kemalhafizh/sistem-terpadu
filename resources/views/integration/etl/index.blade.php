<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </span>
                    Enterprise ETL Pipeline & Migration Toolkit
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pipeline ETL (Extract-Transform-Load) migrasi data massal dari Dapodik, EMIS, Excel legacy, dan CSV.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Create ETL Job Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Buat Pekerjaan Migrasi ETL Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pilih sumber data dan tabel target dalam basis data SISTA.</p>
                </div>

                <form action="{{ route('etl.create_job') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Pekerjaan ETL</label>
                        <input type="text" name="name" required value="Migrasi Data Pokok Siswa Dapodik 2026" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Sumber (Source Type)</label>
                        <select name="source_type" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold">
                            <option value="dapodik_xml">XML Dapodik Kemdikbud</option>
                            <option value="emis_json">JSON EMIS Kemenag</option>
                            <option value="excel" selected>Excel Legacy (.xlsx)</option>
                            <option value="csv">CSV Plain Text (.csv)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Path Berkas Sumber</label>
                        <input type="text" name="source_file_path" required value="imports/data_siswa_dapodik_2026.xlsx" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                        <input type="hidden" name="target_table" value="students">
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30">
                            Konfigurasikan Pekerjaan ETL
                        </button>
                    </div>
                </form>
            </div>

            <!-- ETL Jobs List Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Riwayat Eksekusi Pipeline ETL</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Jalankan, pantau progres, atau lakukan rollback pada pekerjaan migrasi data.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Nama Pekerjaan</th>
                                <th class="px-6 py-4">Tipe Sumber</th>
                                <th class="px-6 py-4">Tabel Target</th>
                                <th class="px-6 py-4">Progres Record</th>
                                <th class="px-6 py-4">Status</th>
                                <th class="px-6 py-4 text-right">Aksi Execution</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($jobs as $j)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $j->name }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px] uppercase">{{ $j->source_type }}</td>
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $j->target_table }}</td>
                                    <td class="px-6 py-4 font-mono text-[11px] text-gray-500">
                                        {{ $j->records_processed }} / {{ $j->records_total }} ({{ $j->progress_percentage }}%)
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 font-mono text-[10px] font-bold rounded uppercase">
                                            {{ $j->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        @if($j->status !== 'completed')
                                            <a href="{{ route('etl.configure_mapping', $j->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                                Visual Mapper
                                            </a>
                                            <form action="{{ route('etl.execute', $j->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                                    Eksekusi Pipeline
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('etl.progress', $j->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                                Lihat Progress
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada pekerjaan ETL migrasi.
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
