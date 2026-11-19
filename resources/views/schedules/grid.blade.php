<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Plotting Jadwal Pelajaran') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    {{ school_name() }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('schedules.teacher') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-blue-500/20 hover:scale-[1.03] transition-all">
                     JADWAL GURU
                </a>
                <a href="{{ route('schedules.room') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-indigo-500/20 hover:scale-[1.03] transition-all">
                     JADWAL RUANGAN
                </a>
                <a href="{{ route('schedules.conflicts') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-rose-500/20 hover:scale-[1.03] transition-all">
                     AUDIT BENTROK
                </a>
                @if($selectedClassroom)
                    <a href="{{ route('schedules.print.class', $selectedClassroom) }}" class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-amber-500/20 hover:scale-[1.03] transition-all">
                         CETAK KELAS
                    </a>
                @endif
                <a href="{{ route('schedules.print.master') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-lg shadow-emerald-500/20 hover:scale-[1.03] transition-all">
                     CETAK MASTER
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-100 text-emerald-700 px-5 py-4 rounded-2xl relative" role="alert">
                    <span class="block text-xs font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-100 text-rose-700 px-5 py-4 rounded-2xl relative" role="alert">
                    <span class="block text-xs font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Classroom Selector & Info -->
            <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                <form action="{{ route('schedules.grid') }}" method="GET" class="flex items-center gap-3 w-full sm:w-auto">
                    <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pilih Kelas:</label>
                    <select name="classroom_id" onchange="this.form.submit()" class="rounded-xl border-gray-200 text-xs font-bold text-gray-700 focus:ring-primary-500 min-w-[200px] bg-gray-50 border-none px-4 py-2">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classrooms ?? [] as $cls)
                            <option value="{{ $cls->id }}" {{ optional($selectedClassroom)->id == $cls->id ? 'selected' : '' }}>
                                {{ $cls->name }} (Grade {{ $cls->grade_level }})
                            </option>
                        @endforeach
                    </select>
                </form>

                @if($activePreset)
                    <div class="flex items-center space-x-2 px-4 py-2 bg-emerald-50 text-emerald-700 rounded-full text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span>Preset Aktif: {{ $activePreset->name }}</span>
                    </div>
                @else
                    <div class="px-4 py-2 bg-amber-50 text-amber-700 rounded-full text-[10px] font-black uppercase tracking-wider border border-amber-100">
                         Belum ada Preset Waktu Aktif
                    </div>
                @endif
            </div>

            @if($selectedClassroom && $activePreset)
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
                                            <td class="p-2 border border-gray-100 relative group min-h-[100px]">
                                                @php 
                                                    $existing = $schedules->filter(function($s) use ($day, $i) {
                                                        return $s->day == $day && $i >= $s->session_start && $i <= $s->session_end;
                                                    })->first();
                                                @endphp

                                                @if($existing)
                                                    @if($existing->session_start == $i)
                                                        @php
                                                            // Duration height class
                                                            $duration = $existing->session_end - $existing->session_start + 1;
                                                        @endphp
                                                        <div class="bg-primary-600 text-white rounded-2xl p-4 shadow-lg shadow-primary-900/10 relative">
                                                            <form action="{{ route('schedules.destroy', $existing->id) }}" method="POST" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                                @csrf @method('DELETE')
                                                                <button type="submit" class="text-white hover:text-red-200">
                                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>
                                                                </button>
                                                            </form>
                                                            <p class="text-[9px] font-black uppercase tracking-widest mb-1 opacity-80">{{ $existing->subject->code }}</p>
                                                            <p class="text-xs font-black leading-tight">{{ $existing?->subject?->name }}</p>
                                                            <p class="text-[9px] mt-2 font-black uppercase tracking-wider bg-white/20 inline-block px-2 py-0.5 rounded-lg">{{ $existing->teacher->name }}</p>
                                                            @if($existing->room)
                                                                <p class="text-[9px] font-black text-amber-200 mt-1 uppercase tracking-wider flex items-center">
                                                                     {{ $existing->room->name }}
                                                                </p>
                                                            @endif
                                                        </div>
                                                    @endif
                                                @else
                                                    <button onclick="openPlotModal('{{ $day }}', {{ $i }})" class="w-full h-12 border-2 border-gray-100 border-dashed rounded-xl flex items-center justify-center text-gray-300 hover:text-primary-600 hover:border-primary-300 hover:bg-gray-50/50 transition-all">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                    </button>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($selectedClassroom)
                <div class="bg-amber-50 rounded-[2.5rem] p-12 text-center border border-amber-100">
                    <p class="text-amber-700 font-bold italic text-xs">Preset Waktu belum aktif. Aktifkan preset terlebih dahulu di menu "Preset Waktu".</p>
                </div>
            @else
                <div class="bg-gray-50 rounded-[2.5rem] p-12 text-center border border-gray-100">
                    <span class="text-4xl block"></span>
                    <h3 class="font-black text-lg text-gray-800 mt-3">Plotting Sesi Belajar Kelas</h3>
                    <p class="text-xs text-gray-400 mt-1 max-w-sm mx-auto">Pilih kelas di atas untuk mulai menyusun plotting jadwal pelajaran per hari dan jam sesi.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Plot Modal -->
    <div id="plot-modal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] p-8 max-w-lg w-full shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-2 bg-primary-600"></div>
            <h3 class="text-xl font-black text-gray-800 mb-6 flex items-center">
                <span id="label-day" class="text-primary-600 mr-2 uppercase tracking-widest text-[10px] bg-primary-50 px-3 py-1 rounded-lg">SENIN</span>
                Plot Mata Pelajaran
            </h3>

            <form action="{{ route('schedules.store') }}" method="POST">
                @csrf
                <input type="hidden" name="classroom_id" value="{{ optional($selectedClassroom)->id }}">
                <input type="hidden" name="day" id="input-day">
                <input type="hidden" name="session_start" id="input-session-start">

                <div class="space-y-5">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Mata Pelajaran</label>
                        <select name="subject_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100" required>
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects ?? [] as $sbj)
                                <option value="{{ $sbj->id }}">{{ $sbj->code }} - {{ $sbj->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Guru Pengampu</label>
                        <select name="teacher_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100" required>
                            <option value="">-- Pilih Guru --</option>
                            @foreach($teachers ?? [] as $tch)
                                <option value="{{ $tch->id }}">{{ $tch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Ruangan <span class="text-gray-400 font-normal text-[9px]">(Opsional)</span></label>
                        <select name="room_id" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                            <option value="">-- Tanpa Ruangan (Bawaan Kelas) --</option>
                            @foreach($rooms ?? [] as $rm)
                                <option value="{{ $rm->id }}">{{ $rm->name }} ({{ $rm->location ?? '-' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Selesai pada Jam ke-</label>
                            <select name="session_end" id="input-session-end" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100" required>
                                @for($i = 1; $i <= 11; $i++)
                                    <option value="{{ $i }}">Jam ke-{{ $i }}</option>
                                @endfor
                            </select>
                            <p class="text-[9px] text-gray-400 mt-2 font-bold uppercase">*Maksimal 4 jam pelajaran berturut-turut</p>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-3 mt-8 pt-6 border-t border-gray-100">
                    <button type="button" onclick="closePlotModal()" class="flex-1 py-3 bg-gray-50 text-gray-500 font-black rounded-xl text-xs uppercase tracking-widest transition-all hover:bg-gray-100">BATAL</button>
                    <button type="submit" class="flex-1 py-3 bg-primary-600 text-white font-black rounded-xl text-xs uppercase tracking-widest shadow-xl shadow-primary-900/10 hover:bg-primary-700 transition-all">SIMPAN PLOTTING</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openPlotModal(day, session) {
            document.getElementById('plot-modal').classList.remove('hidden');
            document.getElementById('label-day').innerText = day.toUpperCase();
            document.getElementById('input-day').value = day;
            document.getElementById('input-session-start').value = session;
            
            // Set session_end default and options (max 4 sessions)
            const endSelect = document.getElementById('input-session-end');
            endSelect.innerHTML = '';
            for(let i = session; i <= Math.min(session + 3, 11); i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.innerText = 'Jam ke-' + i;
                endSelect.appendChild(opt);
            }
        }

        function closePlotModal() {
            document.getElementById('plot-modal').classList.add('hidden');
        }
    </script>
</x-app-layout>
