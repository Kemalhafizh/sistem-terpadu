<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-extrabold text-2xl text-transparent bg-clip-text bg-gradient-to-r from-cyan-400 to-blue-300 tracking-tight leading-tight">
                 Sinkronisasi Ekspor EMIS Kemenag
            </h2>
            <a href="{{ route('admin.integrations.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-800 rounded-xl text-sm font-semibold text-gray-300 bg-gray-900 hover:bg-gray-850 hover:text-white transition-all duration-200">
                &larr; Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-950 text-gray-100 min-h-screen">
        <div class="w-full sm:px-6 lg:px-8 space-y-8">
            
            <!-- Tab Selectors -->
            <div class="flex flex-wrap gap-2 border-b border-gray-800 pb-px">
                <a href="{{ route('admin.integrations.emis', ['type' => 'students', 'classroom_id' => $classroomId]) }}" 
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-all duration-200 {{ $type === 'students' ? 'border-cyan-500 text-cyan-400 font-extrabold' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                     Data Keagamaan Siswa
                </a>
                <a href="{{ route('admin.integrations.emis', ['type' => 'teachers']) }}" 
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-all duration-200 {{ $type === 'teachers' ? 'border-cyan-500 text-cyan-400 font-extrabold' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                     Sertifikasi Guru (PTK)
                </a>
                <a href="{{ route('admin.integrations.emis', ['type' => 'classrooms']) }}" 
                   class="px-5 py-3 text-sm font-bold border-b-2 transition-all duration-200 {{ $type === 'classrooms' ? 'border-cyan-500 text-cyan-400 font-extrabold' : 'border-transparent text-gray-400 hover:text-gray-200' }}">
                     Rombongan Belajar
                </a>
            </div>

            <!-- Main Panel -->
            <div class="space-y-6">
                <!-- Filters Panel -->
                <div class="backdrop-blur-xl bg-gray-900/40 border border-gray-800/80 rounded-3xl p-6 shadow-xl">
                    <form method="GET" action="{{ route('admin.integrations.emis') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <input type="hidden" name="type" value="{{ $type }}">
                        
                        @if($type === 'students')
                            <div>
                                <label for="classroom_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Filter Rombel Kelas</label>
                                <select name="classroom_id" id="classroom_id" class="w-full bg-gray-950 border border-gray-800 rounded-2xl py-3 px-4 text-sm text-gray-200 focus:outline-none focus:border-cyan-500">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classrooms as $cls)
                                        <option value="{{ $cls->id }}" {{ $classroomId == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                                    @endforeach
                                </select>
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
                            Pratinjau Data EMIS (10 Baris Pertama)
                        </h3>
                        
                        <form method="POST" action="{{ route('admin.integrations.emis.export') }}">
                            @csrf
                            <input type="hidden" name="type" value="{{ $type }}">
                            <input type="hidden" name="classroom_id" value="{{ $classroomId }}">
                            
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 font-bold text-sm text-gray-950 rounded-2xl shadow-lg shadow-cyan-950/20 transition-all duration-200">
                                 Ekspor ke Excel EMIS Kemenag
                            </button>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-800 text-xs font-bold uppercase tracking-wider text-gray-400">
                                    @if($type === 'students')
                                        <th class="pb-3">NISN</th>
                                        <th class="pb-3">Nama Lengkap</th>
                                        <th class="pb-3">Gender</th>
                                        <th class="pb-3">Rombel</th>
                                        <th class="pb-3">Capaian Tahfidz</th>
                                    @elseif($type === 'teachers')
                                        <th class="pb-3">NUPTK</th>
                                        <th class="pb-3">Nama Lengkap</th>
                                        <th class="pb-3">Jabatan</th>
                                        <th class="pb-3">Status Pegawai</th>
                                        <th class="pb-3">Sertifikasi Guru</th>
                                    @elseif($type === 'classrooms')
                                        <th class="pb-3">Nama Rombel</th>
                                        <th class="pb-3">Tingkat</th>
                                        <th class="pb-3">Wali Kelas</th>
                                        <th class="pb-3">Tahun Pelajaran</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-800/40 text-sm">
                                @forelse($previewData as $row)
                                    <tr class="hover:bg-gray-800/10 transition-colors">
                                        @if($type === 'students')
                                            <td class="py-4 font-mono text-xs text-gray-300">{{ $row['nisn'] }}</td>
                                            <td class="py-4 font-semibold text-gray-200">{{ $row['nama'] }}</td>
                                            <td class="py-4 text-xs text-gray-400">{{ $row['gender'] }}</td>
                                            <td class="py-4 text-xs">{{ $row['kelas'] }}</td>
                                            <td class="py-4 font-bold text-cyan-400">{{ $row['capaian_tahfidz'] ?: '–' }}</td>
                                        @elseif($type === 'teachers')
                                            <td class="py-4 font-mono text-xs text-gray-300">{{ $row['nuptk'] ?: '–' }}</td>
                                            <td class="py-4 font-semibold text-gray-200">{{ $row['nama'] }}</td>
                                            <td class="py-4 text-xs">{{ $row['jabatan'] }}</td>
                                            <td class="py-4 text-xs text-gray-400">{{ $row['status_kepegawaian'] }}</td>
                                            <td class="py-4">
                                                @if($row['sertifikasi'])
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-950/60 border border-emerald-800/40 text-emerald-400">
                                                        {{ $row['sertifikasi'] }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-500">{{ __('Belum tercatat') }}</span>
                                                @endif
                                            </td>
                                        @elseif($type === 'classrooms')
                                            <td class="py-4 font-bold text-cyan-400">{{ $row['nama_kelas'] }}</td>
                                            <td class="py-4 text-center">{{ $row['tingkat'] }}</td>
                                            <td class="py-4 font-semibold text-gray-200">{{ $row['wali_kelas'] }}</td>
                                            <td class="py-4 text-xs text-gray-400">{{ $row['tahun_pelajaran'] }}</td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-gray-500 text-sm">
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
