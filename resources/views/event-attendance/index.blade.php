<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Presensi Kegiatan & Acara Sekolah') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    {{ school_name() }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('event-attendance.rekap') }}" class="px-4 py-2 bg-amber-500 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-500/20 hover:scale-[1.03] transition-all">
                     REKAP KEHADIRAN
                </a>
                @if(in_array(auth()->user()->role, ['guru', 'admin', 'superadmin']))
                    <a href="{{ route('event-attendance.create') }}" class="px-5 py-2 bg-primary-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-primary-900/20 hover:scale-[1.05] transition-all">
                        + KEGIATAN BARU
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filter Bar --}}
            <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100">
                <form action="{{ route('event-attendance.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tipe Kegiatan</label>
                        <select name="type" class="w-full bg-gray-50 border-none rounded-xl px-4 py-2 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                            <option value="">-- Semua Tipe --</option>
                            <option value="upacara" {{ request('type') === 'upacara' ? 'selected' : '' }}>Upacara Bendera</option>
                            <option value="sholat_dhuha" {{ request('type') === 'sholat_dhuha' ? 'selected' : '' }}>Sholat Dhuha</option>
                            <option value="sholat_dzuhur" {{ request('type') === 'sholat_dzuhur' ? 'selected' : '' }}>Sholat Dzuhur</option>
                            <option value="kultum" {{ request('type') === 'kultum' ? 'selected' : '' }}>Kultum / Ceramah</option>
                            <option value="phbi" {{ request('type') === 'phbi' ? 'selected' : '' }}>PHBI (Maulid, dll)</option>
                            <option value="rapat_guru" {{ request('type') === 'rapat_guru' ? 'selected' : '' }}>Rapat Guru</option>
                            <option value="osis" {{ request('type') === 'osis' ? 'selected' : '' }}>Kegiatan OSIS</option>
                            <option value="class_meeting" {{ request('type') === 'class_meeting' ? 'selected' : '' }}>Class Meeting</option>
                            <option value="lainnya" {{ request('type') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Bulan</label>
                        <input type="month" name="month" value="{{ request('month') }}" class="w-full bg-gray-50 border-none rounded-xl px-4 py-2 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                    </div>

                    <div class="flex items-end space-x-2">
                        <button type="submit" class="flex-1 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                            Filter
                        </button>
                        @if(request()->anyFilled(['type', 'month']))
                            <a href="{{ route('event-attendance.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                                Reset
                            </a>
                        @endif
                    </div>
                </form>
            </div>

            {{-- Event Table --}}
            <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[750px]">
                    <thead>
                        <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-wider">
                            <th class="pb-3">Kegiatan / Acara</th>
                            <th class="pb-3">Tanggal & Waktu</th>
                            <th class="pb-3 text-center">Tipe</th>
                            <th class="pb-3 text-center">Sasaran Peserta</th>
                            <th class="pb-3 text-center">Tingkat Kehadiran</th>
                            <th class="pb-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-xs font-bold text-gray-700">
                        @forelse($events as $event)
                            @php
                                $rate = $event->total_attendees > 0 ? round(($event->present_count / $event->total_attendees) * 100, 1) : 0;
                                
                                $typeLabels = [
                                    'upacara' => 'Upacara',
                                    'sholat_dhuha' => 'Sholat Dhuha',
                                    'sholat_dzuhur' => 'Sholat Dzuhur',
                                    'kultum' => 'Kultum',
                                    'phbi' => 'PHBI',
                                    'rapat_guru' => 'Rapat Guru',
                                    'osis' => 'OSIS',
                                    'class_meeting' => 'Class Meeting',
                                    'lainnya' => 'Lainnya'
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

                                $targetLabels = [
                                    'all_students' => 'Semua Murid',
                                    'all_teachers' => 'Semua Guru',
                                    'specific_class' => 'Kelas Tertentu',
                                    'specific_group' => 'Grup Spesifik'
                                ];
                            @endphp
                            <tr>
                                <td class="py-4">
                                    <p class="font-black text-gray-800">{{ $event->name }}</p>
                                    <p class="text-[9px] text-gray-400 uppercase font-black">ID: #{{ $event->id }}</p>
                                </td>
                                <td class="py-4">
                                    <p>{{ $event->event_date?->format('d M Y') }}</p>
                                    <p class="text-[9px] text-gray-400">
                                        {{ $event->start_time ? \Carbon\Carbon::parse($event->start_time)->format('H:i') : '' }}
                                        {{ $event->end_time ? ' - ' . \Carbon\Carbon::parse($event->end_time)->format('H:i') : '' }}
                                    </p>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $typeColors[$event->type] ?? 'bg-gray-100' }}">
                                        {{ $typeLabels[$event->type] ?? $event->type }}
                                    </span>
                                </td>
                                <td class="py-4 text-center">
                                    <span class="text-gray-500">{{ $targetLabels[$event->target] ?? $event->target }}</span>
                                </td>
                                <td class="py-4">
                                    <div class="flex flex-col items-center justify-center space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <div class="w-16 bg-gray-100 h-2 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full {{ $rate >= 85 ? 'bg-emerald-500' : ($rate >= 70 ? 'bg-amber-400' : 'bg-rose-500') }}" style="width: {{ $rate }}%"></div>
                                            </div>
                                            <span class="text-[10px] font-black">{{ $rate }}%</span>
                                        </div>
                                        <span class="text-[9px] text-gray-400 font-bold uppercase">{{ $event->present_count }} / {{ $event->total_attendees }} Hadir</span>
                                    </div>
                                </td>
                                <td class="py-4 text-right space-x-1">
                                    <a href="{{ route('event-attendance.show', $event->id) }}" class="px-3 py-1 bg-gray-50 hover:bg-gray-100 border border-gray-100 rounded-lg text-[9px] font-black uppercase tracking-wider text-gray-600 transition-all">
                                         DETAIL
                                    </a>
                                    @if(in_array(auth()->user()->role, ['guru', 'admin', 'superadmin']))
                                        <a href="{{ route('event-attendance.input', $event->id) }}" class="px-3 py-1 bg-primary-50 hover:bg-primary-100 border border-primary-100 rounded-lg text-[9px] font-black uppercase tracking-wider text-primary-600 transition-all">
                                             INPUT ABSEN
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400 font-bold">Belum ada kegiatan/acara sekolah terdaftar dalam periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                    {{ $events->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
