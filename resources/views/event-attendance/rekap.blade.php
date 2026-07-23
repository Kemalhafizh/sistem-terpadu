<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Rekap Kehadiran Kegiatan Siswa') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Laporan Historis Kegiatan Non-KBM
                </p>
            </div>
            <div>
                @if(in_array(auth()->user()->role, ['guru', 'admin', 'superadmin']))
                    <a href="{{ route('event-attendance.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                        ⬅ KELOLA KEGIATAN
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Role-Based Filter Bars --}}
            @if(in_array(auth()->user()->role, ['guru', 'admin', 'superadmin']))
                {{-- Teacher/Admin Filter --}}
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                    <form action="{{ route('event-attendance.rekap') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Kelas</label>
                            <select name="classroom_id" onchange="this.form.submit()" class="w-full bg-gray-50 border-none rounded-xl px-4 py-2.5 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                                @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}" {{ (request('classroom_id') == $cls->id || (isset($classroom) && $classroom->id == $cls->id)) ? 'selected' : '' }}>
                                        {{ $cls->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Siswa</label>
                            <select name="student_id" onchange="this.form.submit()" class="w-full bg-gray-50 border-none rounded-xl px-4 py-2.5 font-bold text-xs focus:ring-4 focus:ring-primary-100" {{ $students->isEmpty() ? 'disabled' : '' }}>
                                @if($students->isEmpty())
                                    <option value="">-- Tidak Ada Siswa --</option>
                                @else
                                    <option value="">-- Pilih Siswa --</option>
                                    @foreach($students as $stud)
                                        <option value="{{ $stud->id }}" {{ (request('student_id') == $stud->id || (isset($student) && $student->id == $stud->id)) ? 'selected' : '' }}>
                                            {{ $stud->user->name ?? $stud->name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                                 Tampilkan Rekap
                            </button>
                        </div>
                    </form>
                </div>

            @elseif(auth()->user()->role === 'parent')
                {{-- Parent Child Selector --}}
                @if(isset($students) && count($students) > 0)
                    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-center justify-between">
                        <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pilih Data Anak:</span>
                        <div class="flex items-center space-x-2">
                            @foreach($students as $stud)
                                <a href="{{ route('event-attendance.rekap', ['student_id' => $stud->id]) }}" 
                                   class="px-4 py-2 rounded-xl text-xs font-black transition-all border {{ (isset($student) && $student->id == $stud->id) ? 'bg-primary-600 text-white border-primary-600 shadow-lg shadow-primary-900/10' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border-gray-100' }}">
                                     {{ $stud->user->name ?? $stud->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif

            @if(isset($student) && $student && $summary)
                {{-- Student Profile & KPI Cards --}}
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    {{-- Student Card --}}
                    <div class="bg-gradient-to-br from-primary-950 to-primary-900 rounded-[2.5rem] p-6 text-white shadow-xl relative overflow-hidden flex flex-col justify-between">
                        <div class="absolute right-0 top-0 translate-y-2 -translate-x-2 opacity-10 pointer-events-none">
                            <span class="text-9xl"></span>
                        </div>
                        <div class="relative z-10">
                            <p class="text-[9px] font-black uppercase tracking-widest text-primary-300">Profil Siswa</p>
                            <h3 class="font-black text-lg mt-1 tracking-tight leading-tight">{{ $student->user->name ?? $student->name }}</h3>
                            <p class="text-[10px] opacity-75 mt-1">NISN: {{ $student->nisn ?? $student->nis ?? '-' }}</p>
                        </div>
                        <div class="mt-8 border-t border-primary-800/50 pt-4">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-primary-300">Kelas Aktif:</span>
                                <span class="font-black">{{ $student->classroom->name ?? 'Tanpa Kelas' }}</span>
                            </div>
                            <div class="flex items-center justify-between text-xs mt-1">
                                <span class="text-primary-300">Wali Kelas:</span>
                                <span class="font-black">{{ $student->classroom->homeroomTeacher->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Rate KPI --}}
                    <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col justify-between">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-4">Tingkat Kehadiran</p>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-4xl font-black {{ $summary['rate'] >= 85 ? 'text-emerald-600' : ($summary['rate'] >= 70 ? 'text-amber-500' : 'text-rose-600') }}">
                                {{ $summary['rate'] }}%
                            </span>
                        </div>
                        <div class="mt-4">
                            <div class="w-full bg-gray-100 h-2 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $summary['rate'] >= 85 ? 'bg-emerald-500' : ($summary['rate'] >= 70 ? 'bg-amber-400' : 'bg-rose-500') }}" style="width: {{ $summary['rate'] }}%"></div>
                            </div>
                            <p class="text-[9px] text-gray-400 mt-2 font-bold uppercase">{{ $summary['hadir'] }} dari {{ $summary['total'] }} Kegiatan Dihadiri</p>
                        </div>
                    </div>

                    {{-- Present vs Absent --}}
                    <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 grid grid-cols-2 gap-4">
                        <div class="bg-emerald-50/50 p-4 rounded-2xl border border-emerald-100/50 flex flex-col justify-between">
                            <span class="text-[9px] text-emerald-600 font-black uppercase tracking-widest">Hadir + Late</span>
                            <span class="text-2xl font-black text-emerald-700 block mt-2">{{ $summary['hadir'] }}</span>
                        </div>
                        <div class="bg-rose-50/50 p-4 rounded-2xl border border-rose-100/50 flex flex-col justify-between">
                            <span class="text-[9px] text-rose-600 font-black uppercase tracking-widest">Mangkir (Alpa)</span>
                            <span class="text-2xl font-black text-rose-700 block mt-2">{{ $summary['alpa'] }}</span>
                        </div>
                    </div>

                    {{-- Izin/Sakit --}}
                    <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 flex flex-col justify-between">
                        <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest mb-4">Izin & Sakit</p>
                        <div class="flex items-baseline space-x-2">
                            <span class="text-4xl font-black text-amber-600">{{ $summary['izin'] }}</span>
                            <span class="text-xs text-gray-400 font-bold">Kali</span>
                        </div>
                        <p class="text-[9px] text-gray-400 mt-2 font-bold uppercase">Membutuhkan persetujuan / surat resmi</p>
                    </div>
                </div>

                {{-- Activity Breakdown Cards --}}
                <div class="space-y-4">
                    <h3 class="font-black text-lg text-gray-800 tracking-tight">Capaian per Jenis Kegiatan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @foreach($summary['types'] as $type => $typeStat)
                            @php
                                $typeLabels = [
                                    'upacara' => 'Upacara Bendera',
                                    'sholat_dhuha' => 'Sholat Dhuha',
                                    'sholat_dzuhur' => 'Sholat Dzuhur',
                                    'kultum' => 'Kultum / Ceramah',
                                    'phbi' => 'Kegiatan PHBI',
                                    'class_meeting' => 'Class Meeting',
                                    'lainnya' => 'Lain-Lain'
                                ];
                                
                                $typeIcons = [
                                    'upacara' => '🇮🇩',
                                    'sholat_dhuha' => '',
                                    'sholat_dzuhur' => '',
                                    'kultum' => '',
                                    'phbi' => '',
                                    'class_meeting' => '',
                                    'lainnya' => ''
                                ];

                                $tRate = $typeStat['rate'];
                            @endphp
                            <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-2xl">{{ $typeIcons[$type] ?? '' }}</span>
                                    <span class="text-[10px] font-black px-2 py-0.5 rounded-full {{ $tRate >= 85 ? 'bg-emerald-50 text-emerald-600' : ($tRate >= 70 ? 'bg-amber-50 text-amber-700' : 'bg-rose-50 text-rose-600') }}">
                                        {{ $tRate }}%
                                    </span>
                                </div>
                                <div>
                                    <h4 class="text-xs font-black text-gray-800">{{ $typeLabels[$type] ?? $type }}</h4>
                                    <p class="text-[9px] text-gray-400 mt-1 font-bold">
                                        HADIR: {{ $typeStat['hadir'] }} / {{ $typeStat['total'] }} Kali
                                    </p>
                                </div>
                                <div class="w-full bg-gray-50 h-1.5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $tRate >= 85 ? 'bg-emerald-500' : ($tRate >= 70 ? 'bg-amber-400' : 'bg-rose-500') }}" style="width: {{ $tRate }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- History Log Table --}}
                <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 overflow-x-auto">
                    <h3 class="font-black text-lg text-gray-800 border-b border-gray-50 pb-4 mb-4">Riwayat Log Kehadiran Kegiatan</h3>
                    
                    <table class="w-full text-left border-collapse min-w-[700px]">
                        <thead>
                            <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                                <th class="pb-3">Kegiatan / Acara</th>
                                <th class="pb-3">Tipe</th>
                                <th class="pb-3">Tanggal & Waktu</th>
                                <th class="pb-3 text-center">Status Kehadiran</th>
                                <th class="pb-3 text-center">Jam Masuk</th>
                                <th class="pb-3">Catatan / Keterangan</th>
                                @if(!in_array(auth()->user()->role, ['student', 'parent']))
                                    <th class="pb-3 text-right">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-700">
                            @forelse($summary['records'] as $record)
                                @php
                                    $statusLabels = [
                                        'hadir' => ' Hadir',
                                        'terlambat' => ' Terlambat',
                                        'izin' => ' Izin',
                                        'sakit' => ' Sakit',
                                        'alpa' => ' Alpa'
                                    ];

                                    $statusColors = [
                                        'hadir' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                        'terlambat' => 'bg-teal-50 text-teal-600 border border-teal-100',
                                        'izin' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                        'sakit' => 'bg-rose-50 text-rose-600 border border-rose-100',
                                        'alpa' => 'bg-gray-50 text-gray-500 border border-gray-100'
                                    ];

                                    $typeColors = [
                                        'upacara' => 'bg-red-50 text-red-600 border border-red-100',
                                        'sholat_dhuha' => 'bg-emerald-50 text-emerald-600 border border-emerald-100',
                                        'sholat_dzuhur' => 'bg-teal-50 text-teal-600 border border-teal-100',
                                        'kultum' => 'bg-amber-50 text-amber-700 border border-amber-100',
                                        'phbi' => 'bg-indigo-50 text-indigo-600 border border-indigo-100',
                                        'rapat_guru' => 'bg-slate-50 text-slate-600 border border-slate-100',
                                        'osis' => 'bg-blue-50 text-blue-600 border border-blue-100',
                                        'class_meeting' => 'bg-purple-50 text-purple-600 border border-purple-100',
                                        'lainnya' => 'bg-gray-50 text-gray-500 border border-gray-100'
                                    ];
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-4">
                                        <p class="font-black text-gray-800">{{ $record->event->name ?? '-' }}</p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase">ID EVENT: #{{ $record->event_id }}</p>
                                    </td>
                                    <td class="py-4">
                                        @if($record->event)
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $typeColors[$record->event->type] ?? 'bg-gray-100' }}">
                                                {{ str_replace('_', ' ', $record->event->type) }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-4 text-gray-600">
                                        <p>{{ $record->event?->event_date?->format('d M Y') }}</p>
                                        <p class="text-[9px] text-gray-400">
                                            {{ $record->event?->start_time ? \Carbon\Carbon::parse($record->event->start_time)->format('H:i') : '' }}
                                            {{ $record->event?->end_time ? ' - ' . \Carbon\Carbon::parse($record->event->end_time)->format('H:i') : '' }}
                                        </p>
                                    </td>
                                    <td class="py-4 text-center">
                                        <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider {{ $statusColors[$record->status] ?? 'bg-gray-100' }}">
                                            {{ $statusLabels[$record->status] ?? $record->status }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-center text-gray-600">
                                        {{ $record->check_in_time ? \Carbon\Carbon::parse($record->check_in_time)->format('H:i') . ' WIB' : '-' }}
                                    </td>
                                    <td class="py-4 text-gray-500 font-medium max-w-xs truncate" title="{{ $record->notes }}">
                                        {{ $record->notes ?? '-' }}
                                    </td>
                                    @if(!in_array(auth()->user()->role, ['student', 'parent']))
                                        <td class="py-4 text-right">
                                            <a href="{{ route('event-attendance.show', $record->event_id) }}" class="px-3 py-1 bg-gray-50 hover:bg-gray-100 border border-gray-100 rounded-lg text-[9px] font-black uppercase tracking-wider text-gray-600 transition-all">
                                                 DETAIL
                                            </a>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 font-bold">
                                        Belum ada riwayat presensi kegiatan untuk siswa ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            @else
                {{-- Empty/Selection State --}}
                <div class="bg-white rounded-[2.5rem] p-12 text-center border border-gray-100 space-y-4">
                    <span class="text-6xl block"></span>
                    <h3 class="font-black text-lg text-gray-800">Silakan pilih kelas dan siswa terlebih dahulu</h3>
                    <p class="text-xs text-gray-400 max-w-md mx-auto">Pilih kelas di atas untuk memunculkan daftar siswa, kemudian pilih salah satu siswa untuk melihat rangkuman statistik dan riwayat presensi kegiatan mereka.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
