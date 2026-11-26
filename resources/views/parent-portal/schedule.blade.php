<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-2xl text-gray-900 leading-tight">Jadwal Pelajaran</h2>
                <p class="text-sm font-semibold text-gray-500 mt-1">Jadwal Kelas Siswa Sepekan</p>
            </div>
            <a href="{{ route('parent-portal.dashboard') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- activePreset information -->
            <div class="bg-white rounded-[2rem] p-6 shadow-sm border border-gray-200/60 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div>
                    <h3 class="font-black text-lg text-gray-800">Kelas Aktif: <span class="text-indigo-600">{{ $student->classroom->name }}</span></h3>
                    <p class="text-xs text-gray-400 font-semibold mt-1">
                        Sistem Penjadwalan: {{ $activePreset->name ?? 'Preset Standar' }}
                    </p>
                </div>
                <span class="px-3.5 py-1.5 bg-indigo-50 text-indigo-600 rounded-xl text-xs font-black uppercase tracking-wider">
                    {{ count($schedules) }} Slot Jam Belajar
                </span>
            </div>

            <!-- Grid of Days -->
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                @foreach($days as $day)
                    @php 
                        $daySchedules = $schedules->where('day', $day)->sortBy('session_start');
                    @endphp
                    <div class="bg-white rounded-[2rem] p-5 shadow-sm border border-gray-200/60 flex flex-col">
                        <div class="border-b border-gray-100 pb-3 mb-4 flex justify-between items-center">
                            <h4 class="font-black text-gray-800 text-base">{{ $day }}</h4>
                            <span class="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-[10px] font-black uppercase tracking-widest">
                                {{ $daySchedules->count() }} Sesi
                            </span>
                        </div>
                        
                        <div class="space-y-3 flex-1">
                            @forelse($daySchedules as $sch)
                                @php 
                                    $timeStart = '-';
                                    $timeEnd = '-';
                                    if ($activePreset) {
                                        $startTimeObj = $activePreset->times->firstWhere('session_number', $sch->session_start);
                                        $endTimeObj = $activePreset->times->firstWhere('session_number', $sch->session_end);
                                        $timeStart = $startTimeObj ? date('H:i', strtotime($startTimeObj->start_time)) : '-';
                                        $timeEnd = $endTimeObj ? date('H:i', strtotime($endTimeObj->end_time)) : '-';
                                    }
                                @endphp
                                <div class="bg-gray-50 hover:bg-indigo-50/30 p-4 rounded-2xl border border-gray-100 hover:border-indigo-100/50 transition-all duration-300 relative group">
                                    <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <span class="w-1.5 h-1.5 bg-primary-500 rounded-full"></span>
                                    </div>
                                    <span class="text-[9px] font-black text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded block w-fit">
                                        Sesi {{ $sch->session_start }}{{ $sch->session_start != $sch->session_end ? '-' . $sch->session_end : '' }}
                                    </span>
                                    <h5 class="font-extrabold text-sm text-gray-900 mt-2 leading-snug">{{ $sch->subject->name }}</h5>
                                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-1.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        {{ $timeStart }} - {{ $timeEnd }}
                                    </p>
                                    <p class="text-[10px] text-gray-500 font-bold mt-2">
                                        Guru: {{ $sch->teacher->name }}
                                    </p>
                                </div>
                            @empty
                                <div class="h-28 bg-gray-50/50 rounded-2xl border border-dashed border-gray-200 flex items-center justify-center text-center p-4">
                                    <p class="text-xs text-gray-300 italic font-bold">Tidak ada kegiatan belajar mengajar.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
