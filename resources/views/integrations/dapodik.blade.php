<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-extrabold text-2xl text-slate-900 dark:text-white tracking-tight leading-tight">
                Sinkronisasi &amp; Impor Dapodik
            </h2>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('admin.integrations.dapodik.sync-queue') }}" class="inline-flex items-center px-4 py-2 border border-amber-300 dark:border-amber-700/60 rounded-xl text-sm font-semibold text-amber-800 dark:text-amber-200 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors">
                    Antrean Update Dapodik ({{ $pendingSyncCount ?? 0 }})
                </a>
                <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 text-sm font-medium">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('batch_credentials_token'))
                <div class="p-5 rounded-2xl bg-indigo-50 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-indigo-900 dark:text-indigo-200 text-sm">Akun pengguna baru berhasil dibuat dari impor Dapodik.</p>
                        <p class="text-xs text-indigo-700 dark:text-indigo-300 mt-1">Unduh lembar kredensial PDF untuk dibagikan melalui wali kelas atau TU. Pengguna wajib mengganti kata sandi pada login pertama.</p>
                    </div>
                    <a href="{{ route('users.import-credentials.download', session('batch_credentials_token')) }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold rounded-xl transition-colors">
                        Unduh Lembar Akun (PDF)
                    </a>
                </div>
            @endif

            @if(session('import_result') && ! empty(session('import_result')['failed_rows']))
                @php $failedRows = session('import_result')['failed_rows']; @endphp
                <div class="p-5 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 space-y-3">
                    <p class="font-bold text-amber-900 dark:text-amber-300 text-sm">Daftar Baris Gagal Diimpor ({{ count($failedRows) }})</p>
                    <div class="overflow-x-auto max-h-60 overflow-y-auto">
                        <table class="w-full text-start text-xs text-amber-900 dark:text-amber-100">
                            <thead>
                                <tr class="border-b border-amber-200 dark:border-amber-800/60">
                                    <th class="pb-2 text-start">Baris</th>
                                    <th class="pb-2 text-start">Identitas</th>
                                    <th class="pb-2 text-start">Penyebab</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-200/70 dark:divide-amber-900/40">
                                @foreach($failedRows as $failed)
                                    <tr>
                                        <td class="py-2 pe-4 font-mono">#{{ $failed['row'] }}</td>
                                        <td class="py-2 pe-4 font-semibold">{{ $failed['identifier'] }}</td>
                                        <td class="py-2 text-rose-700 dark:text-rose-300">{{ implode('; ', $failed['errors']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Impor Awal / Pembaruan Dapodik -->
            <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                <div class="mb-4">
                    <h3 class="text-base font-bold text-emerald-700 dark:text-emerald-400">Impor File Excel / CSV Dapodik</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Urutan impor disarankan saat go-live: Rombel &rarr; PTK/Guru &rarr; Siswa. Baris yang gagal dicocokkan akan dilaporkan lengkap beserta alasannya.</p>
                </div>
                <form method="POST" action="{{ route('admin.integrations.dapodik.import') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label for="import_type" class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Jenis Data</label>
                        <select name="type" id="import_type" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-emerald-500">
                            <option value="students" {{ $type === 'students' ? 'selected' : '' }}>Peserta Didik (Siswa)</option>
                            <option value="teachers" {{ $type === 'teachers' ? 'selected' : '' }}>PTK / Guru &amp; Staf</option>
                            <option value="classrooms" {{ $type === 'classrooms' ? 'selected' : '' }}>Rombongan Belajar (Rombel)</option>
                        </select>
                    </div>
                    <div>
                        <label for="import_file" class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">File (.xlsx / .xls / .csv)</label>
                        <input type="file" name="file" id="import_file" accept=".xlsx,.xls,.csv" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2 px-3 text-sm text-slate-700 dark:text-slate-200">
                    </div>
                    <div class="flex items-center gap-2 py-2.5">
                        <input type="hidden" name="create_accounts" value="0">
                        <input type="checkbox" name="create_accounts" id="create_accounts" value="1" checked class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-emerald-600">
                        <label for="create_accounts" class="text-xs font-medium text-slate-700 dark:text-slate-300">Buat akun otomatis (siswa, PTK &amp; ortu)</label>
                    </div>
                    <div>
                        <button type="submit" class="w-full py-2.5 px-5 bg-emerald-600 hover:bg-emerald-700 font-bold text-sm text-white rounded-xl transition-colors shadow-sm">
                            Mulai Impor Dapodik
                        </button>
                    </div>
                </form>
            </div>

            <!-- Tab Selectors -->
            <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-700 pb-px">
                <a href="{{ route('admin.integrations.dapodik', ['type' => 'students', 'classroom_id' => $classroomId]) }}"
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-colors {{ $type === 'students' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                     Data Siswa
                </a>
                <a href="{{ route('admin.integrations.dapodik', ['type' => 'teachers']) }}"
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-colors {{ $type === 'teachers' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                     Data PTK / Guru
                </a>
                <a href="{{ route('admin.integrations.dapodik', ['type' => 'classrooms']) }}"
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-colors {{ $type === 'classrooms' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                     Rombongan Belajar
                </a>
                <a href="{{ route('admin.integrations.dapodik', ['type' => 'attendances', 'classroom_id' => $classroomId]) }}"
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-colors {{ $type === 'attendances' ? 'border-emerald-600 text-emerald-700 dark:text-emerald-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                     Rekap Absensi Siswa
                </a>
            </div>

            <!-- Two Column Layout: Left Filter/Preview, Right Pre-Validation -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- Filters & Preview Column -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Filters Panel -->
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                        <form method="GET" action="{{ route('admin.integrations.dapodik') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <input type="hidden" name="type" value="{{ $type }}">

                            @if(in_array($type, ['students', 'attendances']))
                                <div>
                                    <label for="classroom_id" class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Filter Rombel Kelas</label>
                                    <select name="classroom_id" id="classroom_id" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-emerald-500">
                                        <option value="">Semua Kelas</option>
                                        @foreach($classrooms as $cls)
                                            <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($type === 'attendances')
                                <div>
                                    <label for="month" class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Bulan Rekap</label>
                                    <input type="month" name="month" id="month" value="{{ request('month', date('Y-m')) }}" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-emerald-500">
                                </div>
                            @endif

                            <div class="md:col-span-1">
                                <button type="submit" class="w-full py-2.5 bg-slate-800 dark:bg-slate-700 hover:bg-slate-900 dark:hover:bg-slate-600 font-semibold text-sm rounded-xl text-white transition-colors">
                                    Terapkan Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Data Preview Table -->
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white">
                                Pratinjau Data (10 Baris Pertama)
                            </h3>

                            <form method="POST" action="{{ route('admin.integrations.dapodik.export') }}">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <input type="hidden" name="classroom_id" value="{{ $classroomId }}">
                                <input type="hidden" name="month" value="{{ request('month') }}">

                                <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 font-bold text-sm text-white rounded-xl shadow-sm transition-colors">
                                     Ekspor ke Excel Dapodik
                                </button>
                            </form>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-start border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                        @if($type === 'students')
                                            <th class="pb-3 text-start">NISN</th>
                                            <th class="pb-3 text-start">NIS</th>
                                            <th class="pb-3 text-start">Nama Lengkap</th>
                                            <th class="pb-3 text-center">L/P</th>
                                            <th class="pb-3 text-start">Tempat Lahir</th>
                                            <th class="pb-3 text-start">Tanggal Lahir</th>
                                            <th class="pb-3 text-start">Ibu Kandung</th>
                                            <th class="pb-3 text-start">Rombel</th>
                                        @elseif($type === 'teachers')
                                            <th class="pb-3 text-start">NUPTK</th>
                                            <th class="pb-3 text-start">NIY</th>
                                            <th class="pb-3 text-start">Nama Lengkap</th>
                                            <th class="pb-3 text-start">Email</th>
                                            <th class="pb-3 text-start">Jabatan</th>
                                            <th class="pb-3 text-start">Status</th>
                                        @elseif($type === 'classrooms')
                                            <th class="pb-3 text-start">Nama Rombel</th>
                                            <th class="pb-3 text-center">Tingkat</th>
                                            <th class="pb-3 text-start">Jurusan</th>
                                            <th class="pb-3 text-start">Wali Kelas</th>
                                        @elseif($type === 'attendances')
                                            <th class="pb-3 text-start">NISN</th>
                                            <th class="pb-3 text-start">Nama Siswa</th>
                                            <th class="pb-3 text-start">Kelas</th>
                                            <th class="pb-3 text-center">H</th>
                                            <th class="pb-3 text-center">S</th>
                                            <th class="pb-3 text-center">I</th>
                                            <th class="pb-3 text-center">A</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                                    @forelse($previewData as $row)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                            @if($type === 'students')
                                                <td class="py-3.5 font-mono text-xs text-slate-700 dark:text-slate-300">
                                                    @if(empty($row['nisn']) || strlen($row['nisn']) !== 10)
                                                        <span class="text-rose-600 dark:text-rose-400 font-bold" title="Format NISN Bermasalah">
                                                            {{ $row['nisn'] ?: 'KOSONG' }}
                                                        </span>
                                                    @else
                                                        {{ $row['nisn'] }}
                                                    @endif
                                                </td>
                                                <td class="py-3.5 text-xs text-slate-500 dark:text-slate-400">{{ $row['nis'] }}</td>
                                                <td class="py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['nama'] }}</td>
                                                <td class="py-3.5 text-center text-slate-700 dark:text-slate-300">{{ $row['gender'] }}</td>
                                                <td class="py-3.5 text-xs {{ $row['tempat_lahir'] === '' ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-600 dark:text-slate-300' }}">{{ $row['tempat_lahir'] ?: 'KOSONG' }}</td>
                                                <td class="py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $row['tanggal_lahir'] ?: '–' }}</td>
                                                <td class="py-3.5 text-xs {{ $row['ibu_kandung'] === '' ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-600 dark:text-slate-300' }}">{{ $row['ibu_kandung'] ?: 'KOSONG' }}</td>
                                                <td class="py-3.5 text-xs text-slate-700 dark:text-slate-300">{{ $row['kelas'] ?: '–' }}</td>
                                            @elseif($type === 'teachers')
                                                <td class="py-3.5 font-mono text-xs text-slate-700 dark:text-slate-300">
                                                    @if(empty($row['nuptk']))
                                                        <span class="text-rose-600 dark:text-rose-400 font-bold" title="NUPTK Kosong">KOSONG</span>
                                                    @else
                                                        {{ $row['nuptk'] }}
                                                    @endif
                                                </td>
                                                <td class="py-3.5 font-mono text-xs text-slate-500 dark:text-slate-400">{{ $row['niy'] ?: '–' }}</td>
                                                <td class="py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['nama'] }}</td>
                                                <td class="py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $row['email'] }}</td>
                                                <td class="py-3.5 text-xs text-slate-700 dark:text-slate-300">{{ $row['jabatan'] }}</td>
                                                <td class="py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $row['status_kepegawaian'] }}</td>
                                            @elseif($type === 'classrooms')
                                                <td class="py-3.5 font-bold text-emerald-700 dark:text-emerald-400">{{ $row['nama_kelas'] }}</td>
                                                <td class="py-3.5 text-center text-slate-700 dark:text-slate-300">{{ $row['tingkat_kelas'] }}</td>
                                                <td class="py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $row['jurusan'] }}</td>
                                                <td class="py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['wali_kelas'] }}</td>
                                            @elseif($type === 'attendances')
                                                <td class="py-3.5 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $row['nisn'] }}</td>
                                                <td class="py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['nama'] }}</td>
                                                <td class="py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $row['kelas'] }}</td>
                                                <td class="py-3.5 text-center text-emerald-600 dark:text-emerald-400 font-bold">{{ $row['hadir'] }}</td>
                                                <td class="py-3.5 text-center text-cyan-600 dark:text-cyan-400 font-bold">{{ $row['sakit'] }}</td>
                                                <td class="py-3.5 text-center text-amber-600 dark:text-amber-400 font-bold">{{ $row['izin'] }}</td>
                                                <td class="py-3.5 text-center text-rose-600 dark:text-rose-400 font-bold">{{ $row['alpa'] }}</td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                                                Tidak ada data pratinjau yang tersedia.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Right Pre-Validation Column -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm min-h-[300px]">
                        <h4 class="text-sm font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider mb-4">
                            Pra-Validasi Kelayakan Dapodik
                        </h4>

                        @if(empty($validationWarnings))
                            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-xl flex flex-col items-center justify-center text-center py-8">
                                <svg class="w-11 h-11 text-emerald-600 dark:text-emerald-400 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm font-bold text-emerald-800 dark:text-emerald-300">Data Layak Ekspor!</p>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-2">Seluruh baris data dinilai lengkap dan memenuhi parameter field wajib Dapodik.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-xl text-center">
                                    <p class="text-sm font-bold text-rose-700 dark:text-rose-300">
                                        Ditemukan {{ count($validationWarnings) }} Masalah
                                    </p>
                                    <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-1">Beberapa data tidak lengkap dan berpotensi ditolak server Dapodik.</p>
                                </div>

                                <div class="max-h-[350px] overflow-y-auto space-y-3 pe-1">
                                    @foreach($validationWarnings as $warn)
                                        <div class="p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl space-y-1">
                                            <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-1.5 mb-1.5">
                                                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-400 truncate max-w-[120px]">{{ $warn['identifier'] }}</span>
                                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200">Baris {{ $warn['row'] }}</span>
                                            </div>
                                            <ul class="list-disc list-inside text-[11px] text-rose-600 dark:text-rose-400 space-y-0.5">
                                                @foreach($warn['errors'] as $err)
                                                    <li>{{ $err }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
