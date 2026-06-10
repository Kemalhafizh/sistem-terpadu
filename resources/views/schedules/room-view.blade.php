<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Jadwal Penggunaan Ruangan') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Pemakaian Ruangan Fisik Sekolah
                </p>
            </div>
            <div>
                <a href="{{ route('schedules.grid') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    ⬅️ PLOTTING JADWAL
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Room Selector --}}
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <form action="{{ route('schedules.room') }}" method="GET" class="flex items-center gap-3 w-full sm:w-auto">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pilih Ruangan:</label>
                    <select name="room_id" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-xs font-bold text-gray-700 focus:ring-primary-500 min-w-[250px] bg-gray-50 border-none px-4 py-2">
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach($rooms ?? [] as $rm)
                            <option value="{{ $rm->id }}" {{ optional($selectedRoom)->id == $rm->id ? 'selected' : '' }}>
                                {{ $rm->name }} {{ $rm->location ? '(' . $rm->location . ')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </form>

                @if($activePreset)
                    <div class="flex items-center space-x-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span>Preset Aktif: {{ $activePreset->name }}</span>
                    </div>
                @endif
            </div>

            @if($selectedRoom && $activePreset)
                <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full border-collapse">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="p-4 border-b border-r border-gray-100 w-32 text-left text-[10px] font-black text-gray-400 uppercase tracking-wider">Sesi</th>
                                    @foreach($days ?? [] as $day)
                                        <th class="p-4 border-b border-gray-200 min-w-[200px] text-center text-[10px] font-black text-gray-400 uppercase tracking-wider">{{ $day }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @for($i = 1; $i <= 11; $i++)
                                    @php $time = $activePreset->times->where('session_number', $i)->first(); @endphp
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="p-4 border-r border-gray-100 bg-gray-50/30">
                                            <div class="text-xs font-black text-gray-800">Jam ke-{{ $i }}</div>
                                            <div class="text-[9px] text-gray-400 font-black uppercase mt-0.5">{{ $time ? date('H:i', strtotime($time->start_time)) . ' - ' . date('H:i', strtotime($time->end_time)) : '--:--' }}</div>
                                        </td>
                                        @foreach($days ?? [] as $day)
                                            <td class="p-2 border border-gray-100 relative group min-h-[100px] text-center">
                                                @php 
                                                    $existing = $schedules->filter(function($s) use ($day, $i) {
                                                        return $s->day == $day && $i >= $s->session_start && $i <= $s->session_end;
                                                    })->first();
                                                @endphp

                                                @if($existing)
                                                    @if($existing->session_start == $i)
                                                        <div class="bg-teal-600 text-white rounded-2xl p-4 shadow-lg shadow-teal-900/10 text-left">
                                                            <p class="text-[9px] font-black uppercase tracking-widest mb-1 opacity-80">{{ $existing->subject->code }}</p>
                                                            <p class="text-xs font-black leading-tight">{{ $existing?->subject?->name }}</p>
                                                            <p class="text-[9px] mt-2 font-black uppercase tracking-wider bg-white/20 inline-block px-2 py-0.5 rounded-lg">Kelas: {{ $existing->classroom->name }}</p>
                                                            <span class="block text-[9px] font-black text-teal-100 mt-1 uppercase tracking-wider">👩‍🏫 {{ $existing->teacher->name }}</span>
                                                        </div>
                                                    @endif
                                                @else
                                                    <span class="text-gray-300 text-[10px] font-bold">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($selectedRoom)
                <div class="bg-amber-50 rounded-[2.5rem] p-12 text-center border border-amber-100">
                    <p class="text-amber-700 font-bold italic text-xs">Preset Waktu belum aktif. Hubungi bagian kurikulum untuk mengaktifkan preset.</p>
                </div>
            @else
                <div class="bg-gray-50 rounded-[2.5rem] p-12 text-center border border-gray-100">
                    <span class="text-4xl block">🏫</span>
                    <h3 class="font-black text-lg text-gray-800 mt-3">Jadwal Penggunaan Ruangan Fisik</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Silakan pilih ruangan di atas untuk melihat alokasi pemakaian ruangan fisik sekolah (kelas, laboratorium, dll.) dalam seminggu.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
