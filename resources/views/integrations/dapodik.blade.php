<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-extrabold text-2xl text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-teal-300 tracking-tight leading-tight">
                Sinkronisasi & Impor Dapodik
            </h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.integrations.dapodik.sync-queue') }}" class="inline-flex items-center px-4 py-2 border border-amber-700/60 rounded-xl text-sm font-semibold text-amber-300 bg-amber-950/40 hover:bg-amber-900/50 transition-all duration-200">
                    Antrean Update Dapodik ({{ $pendingSyncCount ?? 0 }})
                </a>
                <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-800 rounded-xl text-sm font-semibold text-gray-300 bg-gray-900 hover:bg-gray-800 hover:text-white transition-all duration-200">
                    &larr; Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-950 text-gray-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-8">
            @if(session('success'))
                <div class="p-4 rounded-2xl bg-emerald-950/60 border border-emerald-700 text-emerald-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 rounded-2xl bg-rose-950/60 border border-rose-700 text-rose-200 text-sm">
                    {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="p-4 rounded-2xl bg-rose-950/60 border border-rose-700 text-rose-200 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('batch_credentials_token'))
                <div class="p-5 rounded-2xl bg-indigo-950/50 border border-indigo-700 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-indigo-200 text-sm">Akun pengguna baru berhasil dibuat dari impor Dapodik.</p>
                        <p class="text-xs text-indigo-300 mt-1">Unduh lembar kredensial PDF untuk dibagikan melalui wali kelas atau TU. Pengguna wajib mengganti kata sandi pada login pertama.</p>
                    </div>
                    <a href="{{ route('users.import-credentials.download', session('batch_credentials_token')) }}" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-bold rounded-xl">
                        Unduh Lembar Akun (PDF)
                    </a>
                </div>
            @endif

            @if(session('import_result') && ! empty(session('import_result')['failed_rows']))
                @php $failedRows = session('import_result')['failed_rows']; @endphp
                <div class="p-5 rounded-2xl bg-amber-950/40 border border-amber-700 space-y-3">
                    <p class="font-bold text-amber-300 text-sm">Daftar Baris Gagal Diimpor ({{ count($failedRows) }})</p>
                    <div class="overflow-x-auto max-h-60 overflow-y-auto">
                        <table class="w-full text-left text-xs text-amber-100">
                            <thead>
                                <tr class="border-b border-amber-800/60">
                                    <th class="pb-2">Baris</th>
                                    <th class="pb-2">Identitas</th>
                                    <th class="pb-2">Penyebab</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-900/40">
                                @foreach($failedRows as $failed)
                                    <tr>
                                        <td class="py-2 pr-4 font-mono">#{{ $failed['row'] }}</td>
                                        <td class="py-2 pr-4 font-semibold">{{ $failed['identifier'] }}</td>
                                        <td class="py-2 text-rose-300">{{ implode('; ', $failed['errors']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Impor Awal / Pembaruan Dapodik -->
            <div class="backdrop-blur-xl bg-gray-900/40 border border-gray-800/80 rounded-3xl p-6 shadow-xl">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-emerald-300">Impor File Excel / CSV Dapodik</h3>
                    <p class="text-xs text-gray-400 mt-1">Urutan impor disarankan saat go-live: Rombel &rarr; PTK/Guru &rarr; Siswa. Baris yang gagal dicocokkan akan dilaporkan lengkap beserta alasannya.</p>
                </div>
                <form method="POST" action="{{ route('admin.integrations.dapodik.import') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label for="import_type" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Jenis Data</label>
                        <select name="type" id="import_type" class="w-full bg-gray-950 border border-gray-800 rounded-2xl py-3 px-4 text-sm text-gray-200 focus:outline-none focus:border-emerald-500">
                            <option value="students" {{ $type === 'students' ? 'selected' : '' }}>Peserta Didik (Siswa)</option>
                            <option value="teachers" {{ $type === 'teachers' ? 'selected' : '' }}>PTK / Guru &amp; Staf</option>
                            <option value="classrooms" {{ $type === 'classrooms' ? 'selected' : '' }}>Rombongan Belajar (Rombel)</option>
                        </select>
                    </div>
                    <div>
                        <label for="import_file" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">File (.xlsx / .xls / .csv)</label>
                        <input type="file" name="file" id="import_file" accept=".xlsx,.xls,.csv" required class="w-full bg-gray-950 border border-gray-800 rounded-2xl py-2.5 px-3 text-sm text-gray-300">
                    </div>
                    <div class="flex items-center gap-2 py-3">
                        <input type="hidden" name="create_accounts" value="0">
                        <input type="checkbox" name="create_accounts" id="create_accounts" value="1" checked class="rounded border-gray-700 bg-gray-950 text-emerald-500">
                        <label for="create_accounts" class="text-xs text-gray-300">Buat akun otomatis (siswa, PTK &amp; ortu)</label>
                    </div>
                    <div>
                        <button type="submit" class="w-full py-3 px-5 bg-emerald-600 hover:bg-emerald-500 font-bold text-sm text-white rounded-2xl transition-colors">
                            Mulai Impor Dapodik
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Tab Selectors -->
            <div class="flex flex-wrap gap-2 border-b border-gray-800 pb-px">
                <a href="{{ route('admin.integrations.dapodik', ['type' => 'students', 'classroom_id' => $classroomId]) }}" 
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-all duration-200 {{ $type === 'students' ? 'border-emerald-500 text-emerald-400 font-extrabold' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                     Data Siswa
                </a>
                <a href="{{ route('admin.integrations.dapodik', ['type' => 'teachers']) }}" 
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-all duration-200 {{ $type === 'teachers' ? 'border-emerald-500 text-emerald-400 font-extrabold' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                     Data PTK / Guru
                </a>
                <a href="{{ route('admin.integrations.dapodik', ['type' => 'classrooms']) }}" 
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-all duration-200 {{ $type === 'classrooms' ? 'border-emerald-500 text-emerald-400 font-extrabold' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                     Rombongan Belajar
                </a>
                <a href="{{ route('admin.integrations.dapodik', ['type' => 'attendances', 'classroom_id' => $classroomId]) }}" 
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-all duration-200 {{ $type === 'attendances' ? 'border-emerald-500 text-emerald-400 font-extrabold' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                     Rekap Absensi Siswa
                </a>
            </div>

            <!-- Two Column Layout: Left Filter/Preview, Right Pre-Validation -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Filters & Preview Column -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Filters Panel -->
                    <div class="backdrop-blur-xl bg-gray-900/40 border border-gray-800/80 rounded-3xl p-6 shadow-xl">
                        <form method="GET" action="{{ route('admin.integrations.dapodik') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                            <input type="hidden" name="type" value="{{ $type }}">
                            
                            @if(in_array($type, ['students', 'attendances']))
                                <div>
                                    <label for="classroom_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Filter Rombel Kelas</label>
                                    <select name="classroom_id" id="classroom_id" class="w-full bg-gray-950 border border-gray-800 rounded-2xl py-3 px-4 text-sm text-gray-200 focus:outline-none focus:border-emerald-500">
                                        <option value="">Semua Kelas</option>
                                        @foreach($classrooms as $cls)
                                            <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            @if($type === 'attendances')
                                <div>
                                    <label for="month" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Bulan Rekap</label>
                                    <input type="month" name="month" id="month" value="{{ request('month', date('Y-m')) }}" class="w-full bg-gray-950 border border-gray-800 rounded-2xl py-3 px-4 text-sm text-gray-200 focus:outline-none focus:border-emerald-500">
                                </div>
                            @endif

                            <div class="md:col-span-1">
                                <button type="submit" class="w-full py-3 bg-gray-800 hover:bg-gray-700 font-semibold text-sm rounded-2xl text-gray-200 transition-colors">
                                    Terapkan Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Data Preview Table -->
                    <div class="backdrop-blur-xl bg-gray-900/40 border border-gray-800/80 rounded-3xl p-6 shadow-xl">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-bold text-gray-100">
                                Pratinjau Data (10 Baris Pertama)
                            </h3>
                            
                            <form method="POST" action="{{ route('admin.integrations.dapodik.export') }}">
                                @csrf
                                <input type="hidden" name="type" value="{{ $type }}">
                                <input type="hidden" name="classroom_id" value="{{ $classroomId }}">
                                <input type="hidden" name="month" value="{{ request('month') }}">
                                
                                <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 font-bold text-sm text-gray-950 rounded-2xl shadow-lg shadow-emerald-950/20 transition-all duration-200">
                                     Ekspor ke Excel Dapodik
                                </button>
                            </form>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-gray-800 text-xs font-bold uppercase tracking-wider text-gray-400">
                                        @if($type === 'students')
                                            <th class="pb-3">NISN</th>
                                            <th class="pb-3">NIS</th>
                                            <th class="pb-3">Nama Lengkap</th>
                                            <th class="pb-3">L/P</th>
                                            <th class="pb-3">Tempat Lahir</th>
                                            <th class="pb-3">Tanggal Lahir</th>
                                            <th class="pb-3">Ibu Kandung</th>
                                            <th class="pb-3">Rombel</th>
                                        @elseif($type === 'teachers')
                                            <th class="pb-3">NUPTK</th>
                                            <th class="pb-3">NIY</th>
                                            <th class="pb-3">Nama Lengkap</th>
                                            <th class="pb-3">Email</th>
                                            <th class="pb-3">Jabatan</th>
                                            <th class="pb-3">Status</th>
                                        @elseif($type === 'classrooms')
                                            <th class="pb-3">Nama Rombel</th>
                                            <th class="pb-3">Tingkat</th>
                                            <th class="pb-3">Jurusan</th>
                                            <th class="pb-3">Wali Kelas</th>
                                        @elseif($type === 'attendances')
                                            <th class="pb-3">NISN</th>
                                            <th class="pb-3">Nama Siswa</th>
                                            <th class="pb-3">Kelas</th>
                                            <th class="pb-3 text-center">H</th>
                                            <th class="pb-3 text-center">S</th>
                                            <th class="pb-3 text-center">I</th>
                                            <th class="pb-3 text-center">A</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800/40 text-sm">
                                    @forelse($previewData as $row)
                                        <tr class="hover:bg-gray-800/10 transition-colors">
                                            @if($type === 'students')
                                                <td class="py-4 font-mono text-xs text-gray-300">
                                                    @if(empty($row['nisn']) || strlen($row['nisn']) !== 10)
                                                        <span class="text-rose-400 font-bold flex items-center space-x-1" title="Format NISN Bermasalah">
                                                            <span></span> <span>{{ $row['nisn'] ?: 'KOSONG' }}</span>
                                                        </span>
                                                    @else
                                                        {{ $row['nisn'] }}
                                                    @endif
                                                </td>
                                                <td class="py-4 text-xs text-gray-400">{{ $row['nis'] }}</td>
                                                <td class="py-4 font-semibold text-gray-200">{{ $row['nama'] }}</td>
                                                <td class="py-4 text-center">{{ $row['gender'] }}</td>
                                                <td class="py-4 text-xs {{ $row['tempat_lahir'] === '' ? 'text-rose-400 font-bold' : 'text-gray-400' }}">{{ $row['tempat_lahir'] ?: 'KOSONG' }}</td>
                                                <td class="py-4 text-xs text-gray-400">{{ $row['tanggal_lahir'] ?: '–' }}</td>
                                                <td class="py-4 text-xs {{ $row['ibu_kandung'] === '' ? 'text-rose-400 font-bold' : 'text-gray-400' }}">{{ $row['ibu_kandung'] ?: 'KOSONG' }}</td>
                                                <td class="py-4 text-xs">{{ $row['kelas'] ?: '–' }}</td>
                                            @elseif($type === 'teachers')
                                                <td class="py-4 font-mono text-xs text-gray-300">
                                                    @if(empty($row['nuptk']))
                                                        <span class="text-rose-400 font-bold flex items-center space-x-1" title="NUPTK Kosong">
                                                            <span></span> <span>KOSONG</span>
                                                        </span>
                                                    @else
                                                        {{ $row['nuptk'] }}
                                                    @endif
                                                </td>
                                                <td class="py-4 font-mono text-xs text-gray-400">{{ $row['niy'] ?: '–' }}</td>
                                                <td class="py-4 font-semibold text-gray-200">{{ $row['nama'] }}</td>
                                                <td class="py-4 text-xs text-gray-400">{{ $row['email'] }}</td>
                                                <td class="py-4 text-xs">{{ $row['jabatan'] }}</td>
                                                <td class="py-4 text-xs text-gray-400">{{ $row['status_kepegawaian'] }}</td>
                                            @elseif($type === 'classrooms')
                                                <td class="py-4 font-bold text-emerald-400">{{ $row['nama_kelas'] }}</td>
                                                <td class="py-4 text-center">{{ $row['tingkat_kelas'] }}</td>
                                                <td class="py-4 text-xs text-gray-400">{{ $row['jurusan'] }}</td>
                                                <td class="py-4 font-semibold text-gray-200">{{ $row['wali_kelas'] }}</td>
                                            @elseif($type === 'attendances')
                                                <td class="py-4 font-mono text-xs text-gray-300">{{ $row['nisn'] }}</td>
                                                <td class="py-4 font-semibold text-gray-200">{{ $row['nama'] }}</td>
                                                <td class="py-4 text-xs text-gray-400">{{ $row['kelas'] }}</td>
                                                <td class="py-4 text-center text-emerald-400 font-bold">{{ $row['hadir'] }}</td>
                                                <td class="py-4 text-center text-cyan-400 font-bold">{{ $row['sakit'] }}</td>
                                                <td class="py-4 text-center text-amber-400 font-bold">{{ $row['izin'] }}</td>
                                                <td class="py-4 text-center text-rose-400 font-bold">{{ $row['alpa'] }}</td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="py-8 text-center text-gray-500 text-sm">
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
                    <div class="backdrop-blur-xl bg-gray-900/40 border border-gray-800/80 rounded-3xl p-6 shadow-xl min-h-[300px]">
                        <h4 class="text-sm font-bold text-emerald-400 uppercase tracking-wider mb-4 flex items-center space-x-2">
                            <span> Pra-Validasi Kelayakan Dapodik</span>
                        </h4>

                        @if(empty($validationWarnings))
                            <div class="p-4 bg-emerald-950/40 border border-emerald-800/50 rounded-2xl flex flex-col items-center justify-center text-center py-8">
                                <svg class="w-12 h-12 text-emerald-400 mb-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm font-bold text-emerald-300">Data Layak Ekspor!</p>
                                <p class="text-xs text-gray-400 mt-2">Seluruh baris data dinilai lengkap dan memenuhi parameter field wajib Dapodik.</p>
                            </div>
                        @else
                            <div class="space-y-4">
                                <div class="p-3 bg-rose-950/40 border border-rose-800/50 rounded-2xl text-center">
                                    <p class="text-sm font-bold text-rose-400 flex items-center justify-center space-x-1">
                                        <span></span> <span>Ditemukan {{ count($validationWarnings) }} Masalah</span>
                                    </p>
                                    <p class="text-[11px] text-gray-400 mt-1">Beberapa data tidak lengkap dan berpotensi ditolak server Dapodik.</p>
                                </div>

                                <div class="max-h-[350px] overflow-y-auto space-y-3 pr-2 scrollbar-thin scrollbar-thumb-gray-800">
                                    @foreach($validationWarnings as $warn)
                                        <div class="p-3 bg-gray-950 border border-gray-800 rounded-2xl space-y-1">
                                            <div class="flex items-center justify-between border-b border-gray-900 pb-1.5 mb-1.5">
                                                <span class="text-xs font-bold text-emerald-400 truncate max-w-[120px]">{{ $warn['identifier'] }}</span>
                                                <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-800 text-gray-300">Baris {{ $warn['row'] }}</span>
                                            </div>
                                            <ul class="list-disc list-inside text-[11px] text-rose-400 space-y-0.5">
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
