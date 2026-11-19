<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Mutaba\'ah Yaumiyah') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Jurnal Ibadah Harian: {{ $student->user->name }} ({{ $student->classroom->name ?? '-' }})
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if(auth()->user()->role === 'student')
                    <a href="{{ route('mutabaah.input-harian', ['date' => $date]) }}" 
                       class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-md shadow-emerald-900/10 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Isi Jurnal Hari Ini
                    </a>
                @endif
                <a href="{{ route('mutabaah.rekap-mingguan', ['student_id' => $student->id]) }}" 
                   class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold uppercase tracking-wider border border-gray-200 shadow-sm transition-all">
                    Rekap Mingguan
                </a>
                <a href="{{ route('mutabaah.statistik', ['student_id' => $student->id]) }}" 
                   class="px-4 py-2 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-xs font-bold uppercase tracking-wider border border-gray-200 shadow-sm transition-all">
                    Statistik
                </a>
                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'kepala_sekolah', 'guru']) && $student->classroom_id)
                    <a href="{{ route('mutabaah.rekap-kelas', ['classroom' => $student->classroom_id]) }}" 
                       class="px-4 py-2 bg-primary-600 hover:bg-primary-800 text-white rounded-xl text-xs font-bold uppercase tracking-wider shadow-md shadow-primary-900/10 transition-all">
                        Rekap Kelas
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                {{-- Left/Middle column: Calendar View --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 p-6">
                        @php
                            $carbonDate = \Carbon\Carbon::parse($date);
                            $monthName = $carbonDate->translatedFormat('F Y');
                            $prevMonth = $carbonDate->copy()->subMonth()->format('Y-m-d');
                            $nextMonth = $carbonDate->copy()->addMonth()->format('Y-m-d');
                            
                            $daysInMonth = $carbonDate->daysInMonth;
                            $startOfWeek = $carbonDate->copy()->startOfMonth()->dayOfWeek; // 0 (Sun) to 6 (Sat)
                            // Adjust startOfWeek for Monday-start if preferred, but standard is Sunday. Let's do standard.
                        @endphp
                        
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-black text-gray-800 tracking-tight">{{ $monthName }}</h3>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('mutabaah.index', ['date' => $prevMonth, 'student_id' => $student->id]) }}" 
                                   class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-gray-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </a>
                                <a href="{{ route('mutabaah.index', ['date' => now()->format('Y-m-d'), 'student_id' => $student->id]) }}" 
                                   class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 rounded-xl text-xs font-bold text-gray-600 transition-colors">
                                    Bulan Ini
                                </a>
                                <a href="{{ route('mutabaah.index', ['date' => $nextMonth, 'student_id' => $student->id]) }}" 
                                   class="p-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-gray-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        </div>

                        {{-- Calendar Grid --}}
                        <div class="grid grid-cols-7 gap-2 text-center text-xs font-black text-gray-400 uppercase tracking-wider mb-2">
                            <div>Ahd</div>
                            <div>Sen</div>
                            <div>Sel</div>
                            <div>Rab</div>
                            <div>Kam</div>
                            <div>Jum</div>
                            <div>Sab</div>
                        </div>
                        
                        <div class="grid grid-cols-7 gap-2">
                            {{-- Empty cells before start of month --}}
                            @for($i = 0; $i < $startOfWeek; $i++)
                                <div class="aspect-square bg-gray-50/50 rounded-2xl border border-dashed border-gray-100"></div>
                            @endfor
                            
                            {{-- Days of month --}}
                            @for($day = 1; $day <= $daysInMonth; $day++)
                                @php
                                    $currentDayStr = $carbonDate->copy()->day($day)->format('Y-m-d');
                                    $dayLog = $monthlyLogs->get($currentDayStr);
                                    $dayScore = $dayLog ? $dayLog->score : null;
                                    $isToday = $currentDayStr === now()->format('Y-m-d');
                                    $isSelected = $currentDayStr === $date;
                                    
                                    // Coloring logic
                                    $bgColor = 'bg-gray-50 text-gray-600 hover:bg-gray-100';
                                    $borderColor = 'border-transparent';
                                    if ($dayScore !== null) {
                                        if ($dayScore >= 85) {
                                            $bgColor = 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100';
                                        } elseif ($dayScore >= 70) {
                                            $bgColor = 'bg-amber-50 text-amber-700 hover:bg-amber-100';
                                        } else {
                                            $bgColor = 'bg-rose-50 text-rose-700 hover:bg-rose-100';
                                        }
                                    }
                                    
                                    if ($isSelected) {
                                        $borderColor = 'border-primary-500 ring-2 ring-primary-500/20';
                                    } elseif ($isToday) {
                                        $borderColor = 'border-gray-400';
                                    }
                                @endphp
                                <a href="{{ route('mutabaah.index', ['date' => $currentDayStr, 'student_id' => $student->id]) }}" 
                                   class="aspect-square flex flex-col justify-between p-2 rounded-2xl border {{ $borderColor }} {{ $bgColor }} transition-all relative group">
                                    <span class="font-black text-sm">{{ $day }}</span>
                                    @if($dayScore !== null)
                                        <span class="text-[9px] font-extrabold tracking-tight px-1.5 py-0.5 rounded-full bg-white/80 backdrop-blur-sm shadow-sm self-end">
                                            {{ $dayScore }}
                                        </span>
                                    @else
                                        <span class="w-1.5 h-1.5 bg-gray-300 rounded-full self-end mr-1 mb-1"></span>
                                    @endif
                                </a>
                            @endfor
                        </div>

                        {{-- Legend --}}
                        <div class="mt-6 pt-6 border-t border-gray-100 flex flex-wrap items-center gap-4 text-xs font-bold text-gray-500 justify-center">
                            <div class="flex items-center space-x-1.5">
                                <span class="w-3.5 h-3.5 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-100 flex items-center justify-center text-[9px]">A</span>
                                <span>Istimewa (85-100)</span>
                            </div>
                            <div class="flex items-center space-x-1.5">
                                <span class="w-3.5 h-3.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-100 flex items-center justify-center text-[9px]">B</span>
                                <span>Baik (70-84)</span>
                            </div>
                            <div class="flex items-center space-x-1.5">
                                <span class="w-3.5 h-3.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-100 flex items-center justify-center text-[9px]">C/D</span>
                                <span>Kurang (<70)</span>
                            </div>
                            <div class="flex items-center space-x-1.5">
                                <span class="w-3.5 h-3.5 rounded-lg bg-gray-50 text-gray-500 border border-gray-200 flex items-center justify-center text-[9px]">-</span>
                                <span>Belum Diisi</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right column: Day Details Panel --}}
                <div class="space-y-6">
                    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-6 py-4 bg-gray-50/50 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 class="text-xs font-black text-gray-400 uppercase tracking-widest">Detail Amalan</h3>
                                <p class="text-sm font-black text-gray-700 mt-0.5">
                                    {{ \Carbon\Carbon::parse($date)->translatedFormat('d F Y') }}
                                </p>
                            </div>
                            @if($log && $log->score !== null)
                                <div class="px-3 py-1.5 rounded-xl text-center bg-primary-50 text-primary-700 border border-primary-100">
                                    <div class="text-[10px] font-bold uppercase tracking-wider leading-none">Skor</div>
                                    <div class="text-xl font-black mt-0.5 leading-none">{{ $log->score }}</div>
                                </div>
                            @endif
                        </div>

                        <div class="p-6 space-y-5">
                            @if($log)
                                {{-- Verification Status Banner --}}
                                @if($log->verified_by)
                                    <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-2xl flex items-center space-x-3">
                                        <div class="bg-emerald-500 text-white p-1.5 rounded-lg">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-black text-emerald-950">Terverifikasi</p>
                                            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide">
                                                Oleh: {{ $log->verifier->name ?? 'Wali Murid' }}
                                            </p>
                                        </div>
                                    </div>
                                @else
                                    @if(auth()->user()->role === 'parent' || auth()->user()->role === 'guru' || auth()->user()->role === 'admin')
                                        <div class="bg-amber-50 border border-amber-100 p-4 rounded-2xl flex flex-col space-y-3">
                                            <div class="flex items-center space-x-3">
                                                <div class="bg-amber-500 text-white p-1.5 rounded-lg">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                                </div>
                                                <div>
                                                    <p class="text-xs font-black text-amber-950">Belum Diverifikasi</p>
                                                    <p class="text-[10px] font-bold text-amber-600">Tekan tombol di bawah untuk verifikasi</p>
                                                </div>
                                            </div>
                                            <form action="{{ route('mutabaah.verify', $student) }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <button type="submit" class="w-full py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all">
                                                    Verifikasi Amalan Hari Ini
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                @endif

                                {{-- Daily Logs breakdown --}}
                                <div class="space-y-3">
                                    <div class="text-xs font-black text-gray-400 uppercase tracking-widest">Daftar Amalan</div>
                                    
                                    {{-- Wajib Sholat --}}
                                    <div class="bg-gray-50/50 border border-gray-100 rounded-2xl p-4 space-y-3">
                                        <div class="text-[10px] font-bold text-gray-400 uppercase tracking-wider flex justify-between items-center">
                                            <span>Shalat 5 Waktu Wajib</span>
                                            <span class="text-primary-600 font-extrabold">Skor Max 75</span>
                                        </div>
                                        <div class="grid grid-cols-5 gap-1.5 text-center">
                                            @foreach(['subuh' => 'Sub', 'dzuhur' => 'Dzu', 'ashar' => 'Ash', 'maghrib' => 'Mag', 'isya' => 'Isy'] as $field => $label)
                                                @php
                                                    $val = $log->{"sholat_{$field}"};
                                                    $cellBg = 'bg-gray-100 text-gray-400';
                                                    if ($val === 2) {
                                                        $cellBg = 'bg-emerald-500 text-white font-extrabold';
                                                    } elseif ($val === 1) {
                                                        $cellBg = 'bg-primary-500 text-white font-extrabold';
                                                    }
                                                @endphp
                                                <div class="p-2 rounded-xl text-[10px] leading-tight {{ $cellBg }}">
                                                    <div class="font-extrabold">{{ $label }}</div>
                                                    <div class="text-[9px] mt-0.5 opacity-90 uppercase">
                                                        {{ $val === 2 ? 'Jam' : ($val === 1 ? 'Mun' : 'Tdk') }}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Sunnah & Other --}}
                                    <div class="space-y-2">
                                        @php
                                            $amalanSunnah = [
                                                ['label' => 'Shalat Dhuha', 'done' => $log->sholat_dhuha >= 1, 'xp' => '+5 pts'],
                                                ['label' => 'Shalat Tahajud', 'done' => $log->sholat_tahajud >= 1, 'xp' => '+5 pts'],
                                                ['label' => 'Tadarus Al-Qur\'an', 'done' => $log->tadarus_pages >= 1, 'info' => "{$log->tadarus_pages} Hal", 'xp' => '+10 pts'],
                                                ['label' => 'Puasa Sunnah', 'done' => $log->puasa_sunnah, 'xp' => '+5 pts'],
                                                ['label' => 'Sedekah Harian', 'done' => $log->sedekah, 'xp' => '+5 pts'],
                                                ['label' => 'Dzikir Pagi', 'done' => $log->dzikir_pagi, 'xp' => '+2.5 pts'],
                                                ['label' => 'Dzikir Sore', 'done' => $log->dzikir_sore, 'xp' => '+2.5 pts'],
                                            ];
                                        @endphp
                                        @foreach($amalanSunnah as $item)
                                            <div class="flex items-center justify-between p-3 bg-gray-50/50 hover:bg-gray-50 rounded-xl transition-all border border-gray-100/50">
                                                <div class="flex items-center space-x-2.5">
                                                    @if($item['done'])
                                                        <span class="w-5 h-5 rounded-full bg-emerald-500 text-white flex items-center justify-center">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                                        </span>
                                                    @else
                                                        <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center text-[10px] font-black">×</span>
                                                    @endif
                                                    <span class="text-xs font-bold text-gray-700">{{ $item['label'] }}</span>
                                                    @if(isset($item['info']) && $item['done'])
                                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[9px] font-black">{{ $item['info'] }}</span>
                                                    @endif
                                                </div>
                                                <span class="text-[9px] font-bold text-gray-400">{{ $item['xp'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Catatan Harian --}}
                                    @if($log->catatan_harian)
                                        <div class="mt-4 p-4 bg-primary-50/30 border border-primary-100/30 rounded-2xl">
                                            <div class="text-[10px] font-bold text-primary-500 uppercase tracking-widest">Catatan Refleksi</div>
                                            <p class="text-xs text-gray-600 mt-1 italic">"{{ $log->catatan_harian }}"</p>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto text-gray-400 mb-4">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    </div>
                                    <p class="text-sm font-bold text-gray-500">Belum ada data amalan harian.</p>
                                    @if(auth()->user()->role === 'student')
                                        <a href="{{ route('mutabaah.input-harian', ['date' => $date]) }}" 
                                           class="mt-4 inline-block px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                            Isi Jurnal Sekarang
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
