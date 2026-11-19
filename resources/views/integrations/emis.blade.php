<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-extrabold text-2xl text-slate-900 dark:text-white tracking-tight leading-tight">
                Sinkronisasi Ekspor EMIS Kemenag
            </h2>
            <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Tab Selectors -->
            <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-700 pb-px">
                <a href="{{ route('admin.integrations.emis', ['type' => 'students', 'classroom_id' => $classroomId]) }}"
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-colors {{ $type === 'students' ? 'border-cyan-600 text-cyan-700 dark:text-cyan-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                     Data Keagamaan Siswa
                </a>
                <a href="{{ route('admin.integrations.emis', ['type' => 'teachers']) }}"
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-colors {{ $type === 'teachers' ? 'border-cyan-600 text-cyan-700 dark:text-cyan-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                     Sertifikasi Guru (PTK)
                </a>
                <a href="{{ route('admin.integrations.emis', ['type' => 'classrooms']) }}"
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-colors {{ $type === 'classrooms' ? 'border-cyan-600 text-cyan-700 dark:text-cyan-400' : 'border-transparent text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                     Rombongan Belajar
                </a>
            </div>

            <!-- Main Panel -->
            <div class="space-y-6">
                <!-- Filters Panel -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl p-6 shadow-sm">
                    <form method="GET" action="{{ route('admin.integrations.emis') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <input type="hidden" name="type" value="{{ $type }}">

                        @if($type === 'students')
                            <div>
                                <label for="classroom_id" class="block text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider mb-2">Filter Rombel Kelas</label>
                                <select name="classroom_id" id="classroom_id" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl py-2.5 px-3 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-cyan-500">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classrooms as $cls)
                                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                                    @endforeach
                                </select>
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
                            Pratinjau Data EMIS (10 Baris Pertama)
                        </h3>

                        <form method="POST" action="{{ route('admin.integrations.emis.export') }}">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <input type="hidden" name="classroom_id" value="{{ $classroomId }}">

                            <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-cyan-600 hover:bg-cyan-700 font-bold text-sm text-white rounded-xl shadow-sm transition-colors">
                                Ekspor ke Excel EMIS Kemenag
                            </button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-start border-collapse">
                            <thead>
                                <tr class="border-b border-slate-200 dark:border-slate-700 text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                    @if($type === 'students')
                                        <th class="pb-3 text-start">NISN</th>
                                        <th class="pb-3 text-start">Nama Lengkap</th>
                                        <th class="pb-3 text-start">Gender</th>
                                        <th class="pb-3 text-start">Rombel</th>
                                        <th class="pb-3 text-start">Capaian Tahfidz</th>
                                    @elseif($type === 'teachers')
                                        <th class="pb-3 text-start">NUPTK</th>
                                        <th class="pb-3 text-start">Nama Lengkap</th>
                                        <th class="pb-3 text-start">Jabatan</th>
                                        <th class="pb-3 text-start">Status Pegawai</th>
                                        <th class="pb-3 text-start">Sertifikasi Guru</th>
                                    @elseif($type === 'classrooms')
                                        <th class="pb-3 text-start">Nama Rombel</th>
                                        <th class="pb-3 text-center">Tingkat</th>
                                        <th class="pb-3 text-start">Wali Kelas</th>
                                        <th class="pb-3 text-start">Tahun Pelajaran</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60 text-sm">
                                @forelse($previewData as $row)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                                        @if($type === 'students')
                                            <td class="py-3.5 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $row['nisn'] }}</td>
                                            <td class="py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['nama'] }}</td>
                                            <td class="py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $row['gender'] }}</td>
                                            <td class="py-3.5 text-xs text-slate-700 dark:text-slate-300">{{ $row['kelas'] }}</td>
                                            <td class="py-3.5 font-bold text-cyan-700 dark:text-cyan-400">{{ $row['capaian_tahfidz'] ?: '–' }}</td>
                                        @elseif($type === 'teachers')
                                            <td class="py-3.5 font-mono text-xs text-slate-700 dark:text-slate-300">{{ $row['nuptk'] ?: '–' }}</td>
                                            <td class="py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['nama'] }}</td>
                                            <td class="py-3.5 text-xs text-slate-700 dark:text-slate-300">{{ $row['jabatan'] }}</td>
                                            <td class="py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $row['status_kepegawaian'] }}</td>
                                            <td class="py-3.5">
                                                @if($row['sertifikasi'])
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800/50 text-emerald-700 dark:text-emerald-300">
                                                        {{ $row['sertifikasi'] }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-slate-400 dark:text-slate-500">{{ __('Belum tercatat') }}</span>
                                                @endif
                                            </td>
                                        @elseif($type === 'classrooms')
                                            <td class="py-3.5 font-bold text-cyan-700 dark:text-cyan-400">{{ $row['nama_kelas'] }}</td>
                                            <td class="py-3.5 text-center text-slate-700 dark:text-slate-300">{{ $row['tingkat'] }}</td>
                                            <td class="py-3.5 font-semibold text-slate-900 dark:text-slate-100">{{ $row['wali_kelas'] }}</td>
                                            <td class="py-3.5 text-xs text-slate-600 dark:text-slate-300">{{ $row['tahun_pelajaran'] }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-slate-500 dark:text-slate-400 text-sm">
                                            Tidak ada data pratinjau EMIS yang tersedia.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
