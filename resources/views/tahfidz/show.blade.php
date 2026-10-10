<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Jurnal Tahfidz') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Capaian Hafalan: {{ $student?->name }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('tahfidz.dashboard') }}" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    ← DASHBOARD
                </a>
                @if(auth()->user()->isAdmin() || auth()->user()->role === 'guru')
                    <button onclick="document.getElementById('modal-tahfidz').classList.remove('hidden')" class="px-6 py-2 bg-primary-600 text-white rounded-xl text-[10px] font-black uppercase tracking-widest shadow-xl shadow-primary-900/20 hover:scale-[1.05] transition-all">
                        + INPUT SETORAN BARU
                    </button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            {{-- Progres Visual --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Recap Card & Target -->
                <div class="lg:col-span-1 bg-primary-900 rounded-[2.5rem] p-6 sm:p-8 text-white relative overflow-hidden shadow-2xl flex flex-col justify-between min-h-[320px]">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -mr-16 -mt-16 blur-2xl"></div>
                    
                    <div>
                        <h3 class="text-xs font-black uppercase tracking-[0.3em] mb-4 opacity-60">Statistik Hafalan</h3>
                        <div class="grid grid-cols-1 gap-4 mb-6">
                            <div>
                                <p class="text-[9px] font-bold text-primary-300 uppercase tracking-widest mb-1">Total Setoran</p>
                                <p class="text-2xl font-black">{{ $logs->count() }} <span class="text-xs opacity-50">Kali</span></p>
                            </div>
                        </div>

                        <div class="border-t border-white/10 pt-4 space-y-4">
                            <h4 class="text-[10px] font-black uppercase tracking-widest text-primary-200">Progres Target Semester</h4>
                            @if(isset($progress['target']) && $progress['target'])
                                @php
                                    $statusColors = [
                                        'completed' => 'bg-emerald-500 text-white',
                                        'exceeded' => 'bg-amber-400 text-primary-950',
                                        'on_track' => 'bg-blue-500 text-white',
                                        'behind' => 'bg-rose-500 text-white'
                                    ];
                                    $statusLabels = [
                                        'completed' => 'Tercapai',
                                        'exceeded' => 'Melampaui',
                                        'on_track' => 'Sesuai',
                                        'behind' => 'Tertinggal'
                                    ];
                                @endphp
                                <div class="flex items-center justify-between">
                                    <span class="px-2.5 py-0.5 rounded-full text-[8px] font-black uppercase tracking-wider {{ $statusColors[$progress['target']->status] ?? 'bg-gray-500' }}">
                                        {{ $statusLabels[$progress['target']->status] ?? $progress['target']->status }}
                                    </span>
                                    <span class="text-xs font-bold">{{ $progress['progress_percent'] }}%</span>
                                </div>
                                <div class="w-full bg-white/10 h-2 rounded-full overflow-hidden">
                                    <div class="bg-white h-full rounded-full" style="width: {{ $progress['progress_percent'] }}%"></div>
                                </div>
                                <p class="text-[9px] opacity-75 font-bold">Target: {{ $progress['target']->target_juz }} Juz ({{ $progress['target']->target_pages }} Hal) | Capaian: {{ $progress['target']->achieved_juz }} Juz ({{ $progress['target']->achieved_pages }} Hal)</p>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[8px] font-black bg-white/20 text-white uppercase tracking-wider">Belum Diatur</span>
                                <p class="text-[10px] opacity-60">Ustadz/Ustadzah belum memasukkan target untuk semester ini.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 border-t border-white/10 pt-4">
                        <p class="text-[9px] font-bold text-primary-300 uppercase tracking-widest mb-1">Setoran Terakhir</p>
                        <p class="text-sm font-black italic">
                            {{ $logs->first() ? $logs->first()->surah_name . ' (Ayat ' . $logs->first()->from_ayat . '-' . $logs->first()->to_ayat . ')' : 'Belum Ada Setoran' }}
                        </p>
                    </div>
                </div>

                <!-- Timeline / Log -->
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 flex flex-col">
                    <h3 class="text-xs font-black uppercase tracking-[0.3em] text-gray-400 mb-8">Riwayat Setoran Terbaru</h3>
                    <div class="space-y-6 flex-1">
                        @foreach($logs as $log)
                            <div class="flex items-start space-x-4 group">
                                <div class="mt-1 w-10 h-10 rounded-2xl {{ $log?->type == 'ziyadah' ? 'bg-emerald-50 text-emerald-600' : 'bg-primary-50 text-primary-600' }} flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-all font-black text-xs">
                                    {{ $log?->type == 'ziyadah' ? 'Z' : 'M' }}
                                </div>
                                <div class="flex-1 border-b border-gray-50 pb-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="text-sm font-black text-gray-800 tracking-tight">{{ $log?->surah_name }}</p>
                                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                                Juz {{ $log?->juz ?? '-' }} | Ayat {{ $log?->from_ayat }} - {{ $log?->to_ayat }} ({{ $log?->pages_count }} Halaman)
                                            </p>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest">{{ $log?->date?->format('d M Y') }}</p>
                                            <p class="text-xs font-black text-primary-600">Grade: {{ $log?->grade ?? '-' }}</p>
                                        </div>
                                    </div>
                                    @if($log?->notes)
                                        <p class="mt-2 text-xs text-gray-500 italic bg-gray-50 p-2 rounded-lg border-l-2 border-primary-200">"{{ $log?->notes }}"</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        @if($logs->isEmpty())
                            <div class="flex flex-col items-center justify-center py-20 opacity-30">
                                <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 19 7.5 19s3.332-.477 4.5-1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 19 16.5 19c-1.746 0-3.332-.477-4.5-1.253"></path></svg>
                                <p class="font-black text-sm uppercase tracking-widest">Belum ada riwayat setoran</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Modal Input Tahfidz --}}
    @if(auth()->user()->isAdmin() || auth()->user()->role === 'guru')
        <div id="modal-tahfidz" class="fixed inset-0 bg-primary-950/80 backdrop-blur-md z-[100] flex items-center justify-center hidden p-4">
            <div class="bg-white w-full max-w-lg rounded-[2rem] sm:rounded-[2.5rem] overflow-hidden shadow-3xl">
                <div class="px-6 py-4 sm:px-8 sm:py-6 bg-primary-900 text-white flex justify-between items-center">
                    <h3 class="font-black uppercase tracking-widest text-xs sm:text-sm">Input Setoran Baru</h3>
                    <button onclick="document.getElementById('modal-tahfidz').classList.add('hidden')" class="text-white hover:opacity-50 transition-all"></button>
                </div>
                <form action="{{ route('tahfidz.store') }}" method="POST" class="p-6 sm:p-8 space-y-4 sm:space-y-6 max-h-[80vh] overflow-y-auto">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="col-span-2" x-data="{ surahName: '' }">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Surah</label>
                            <select name="surah_number" x-on:change="surahName = $event.target.options[$event.target.selectedIndex].text.split('. ').slice(1).join('. ')" required class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3 font-bold focus:ring-4 focus:ring-primary-100">
                                <option value="">-- Pilih Surah --</option>
                                @foreach($surahs as $num => $name)
                                    <option value="{{ $num }}">{{ $num }}. {{ $name }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" name="surah_name" x-bind:value="surahName">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Juz (1-30)</label>
                            <input type="number" name="juz" required min="1" max="30" value="30" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3 font-bold focus:ring-4 focus:ring-primary-100">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Halaman Count</label>
                            <input type="number" name="pages_count" required min="0" value="1" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3 font-bold focus:ring-4 focus:ring-primary-100">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Ayat Dari</label>
                            <input type="number" name="from_ayat" required min="1" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3 font-bold focus:ring-4 focus:ring-primary-100">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Sampai Ayat</label>
                            <input type="number" name="to_ayat" required min="1" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3 font-bold focus:ring-4 focus:ring-primary-100">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tipe</label>
                            <select name="type" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3 font-bold focus:ring-4 focus:ring-primary-100">
                                <option value="ziyadah">Ziyadah (Baru)</option>
                                <option value="murajaah">Murajaah (Ulang)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nilai (Grade)</label>
                            <select name="grade" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3 font-bold focus:ring-4 focus:ring-primary-100">
                                <option value="A">A (Mumtaz)</option>
                                <option value="B">B (Jayyid Jiddan)</option>
                                <option value="C">C (Jayyid)</option>
                                <option value="D">D (Maqbul)</option>
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tanggal</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3 font-bold focus:ring-4 focus:ring-primary-100">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Catatan Guru (Opsional)</label>
                            <textarea name="notes" rows="2" class="w-full bg-gray-50 border-none rounded-2xl px-5 py-3 font-bold focus:ring-4 focus:ring-primary-100" placeholder="Catatan kelancaran..."></textarea>
                        </div>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="w-full bg-primary-600 text-white font-black uppercase tracking-widest py-4 rounded-2xl shadow-2xl shadow-primary-900/20 hover:scale-[1.02] transition-all">
                            SIMPAN SETORAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</x-app-layout>
