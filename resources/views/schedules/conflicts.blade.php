<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Audit Konflik & Bentrok Jadwal') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Pemeriksaan Konsistensi Jadwal Belajar Sekolah
                </p>
            </div>
            <div>
                <a href="{{ route('schedules.grid') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    ⬅ PLOTTING JADWAL
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-4 rounded-2xl relative" role="alert">
                    <span class="block text-xs font-bold">{{ session('success') }}</span>
                </div>
            @endif

            {{-- Audit Summary Card --}}
            <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center text-xl {{ count($conflicts) > 0 ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-emerald-50 text-emerald-600 border border-emerald-100' }}">
                        {{ count($conflicts) > 0 ? '' : '' }}
                    </div>
                    <div>
                        <h3 class="font-black text-lg text-gray-800">Status Audit Database</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Sistem memindai jadwal dewan guru, kelas, dan ketersediaan ruangan secara real-time.</p>
                    </div>
                </div>
                <div class="shrink-0">
                    <span class="px-4 py-2.5 rounded-full text-xs font-black uppercase tracking-wider {{ count($conflicts) > 0 ? 'bg-rose-500 text-white shadow-lg shadow-rose-500/20' : 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/20' }}">
                        {{ count($conflicts) }} BENTROK TERDETEKSI
                    </span>
                </div>
            </div>

            {{-- Conflict Details List --}}
            <div class="bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 space-y-6">
                <div class="border-b border-gray-50 pb-4">
                    <h3 class="font-black text-lg text-gray-800">Daftar Konflik Jadwal Terbuka</h3>
                    <p class="text-xs text-gray-400 mt-1">Daftar jadwal di bawah ini mengalami bentrok waktu mengajar guru, jam pelajaran kelas, atau pemakaian ruangan ganda.</p>
                </div>

                @forelse($conflicts as $conflict)
                    @php
                        $typeColors = [
                            'Guru' => 'bg-amber-50 text-amber-700 border border-amber-100',
                            'Kelas' => 'bg-blue-50 text-blue-600 border border-blue-100',
                            'Ruangan' => 'bg-purple-50 text-purple-600 border border-purple-100'
                        ];
                    @endphp
                    <div class="p-6 rounded-3xl border border-rose-100 bg-rose-50/10 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-gray-50 pb-3">
                            <div class="flex items-center space-x-2">
                                <span class="px-3 py-1 rounded-xl text-[9px] font-black uppercase tracking-wider {{ $typeColors[$conflict['type']] ?? 'bg-gray-100' }}">
                                    Bentrok {{ $conflict['type'] }}
                                </span>
                                <span class="text-xs font-black text-gray-800">Hari {{ $conflict['schedule_1']->day }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 font-bold uppercase">Tabrakan Jam: {{ $conflict['schedule_1']->session_start }} - {{ $conflict['schedule_1']->session_end }} vs {{ $conflict['schedule_2']->session_start }} - {{ $conflict['schedule_2']->session_end }}</span>
                        </div>

                        <p class="text-xs font-medium text-gray-600 leading-relaxed">{{ $conflict['description'] }}</p>

                        {{-- Dual Schedule Cards --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Schedule 1 --}}
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[9px] font-black text-gray-400 uppercase">Jadwal A (ID: #{{ $conflict['schedule_1']->id }})</span>
                                        <span class="text-xs font-black text-primary-600 bg-primary-50 px-2.5 py-0.5 rounded-lg">Sesi {{ $conflict['schedule_1']->session_start }}-{{ $conflict['schedule_1']->session_end }}</span>
                                    </div>
                                    <h4 class="font-black text-sm text-gray-800 leading-tight">{{ $conflict['schedule_1']->subject->name }}</h4>
                                    <p class="text-[10px] text-gray-500 font-bold">
                                         {{ $conflict['schedule_1']->teacher->name }} <br>
                                         Kelas: {{ $conflict['schedule_1']->classroom->name }} <br>
                                         Ruang: {{ $conflict['schedule_1']->room->name ?? 'Bawaan Kelas' }}
                                    </p>
                                </div>
                                <div class="border-t border-gray-50 pt-3 mt-4 flex justify-end">
                                    <form action="{{ route('schedules.destroy', $conflict['schedule_1']->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                                             HAPUS JADWAL A
                                        </button>
                                    </form>
                                </div>
                            </div>

                            {{-- Schedule 2 --}}
                            <div class="bg-white p-4 rounded-2xl border border-gray-100 flex flex-col justify-between">
                                <div class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[9px] font-black text-gray-400 uppercase">Jadwal B (ID: #{{ $conflict['schedule_2']->id }})</span>
                                        <span class="text-xs font-black text-primary-600 bg-primary-50 px-2.5 py-0.5 rounded-lg">Sesi {{ $conflict['schedule_2']->session_start }}-{{ $conflict['schedule_2']->session_end }}</span>
                                    </div>
                                    <h4 class="font-black text-sm text-gray-800 leading-tight">{{ $conflict['schedule_2']->subject->name }}</h4>
                                    <p class="text-[10px] text-gray-500 font-bold">
                                         {{ $conflict['schedule_2']->teacher->name }} <br>
                                         Kelas: {{ $conflict['schedule_2']->classroom->name }} <br>
                                         Ruang: {{ $conflict['schedule_2']->room->name ?? 'Bawaan Kelas' }}
                                    </p>
                                </div>
                                <div class="border-t border-gray-50 pt-3 mt-4 flex justify-end">
                                    <form action="{{ route('schedules.destroy', $conflict['schedule_2']->id) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 rounded-xl text-[9px] font-black uppercase tracking-widest transition-all">
                                             HAPUS JADWAL B
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-gray-400 font-bold">
                        <span class="text-6xl block"></span>
                        <h4 class="font-black text-emerald-600 text-sm mt-3 uppercase tracking-widest">Database Jadwal Bersih</h4>
                        <p class="text-[11px] text-gray-400 font-medium max-w-xs mx-auto mt-1">Luar biasa! Tidak ada bentrok waktu guru, jam kelas, maupun pemakaian ruangan ganda.</p>
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
