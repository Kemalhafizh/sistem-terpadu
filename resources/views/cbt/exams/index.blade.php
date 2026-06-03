<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">Ujian Online (CBT)</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Manajemen ujian berbasis komputer dengan sistem anti-kecurangan canggih.</p>
            </div>
            <div class="flex items-center gap-3">
                @if(in_array(auth()->user()->role, ['guru', 'waka_kurikulum', 'admin', 'superadmin']))
                    <a href="{{ route('cbt.question-banks.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        Bank Soal
                    </a>
                    <a href="{{ route('cbt.exams.create') }}" class="px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-extrabold shadow-md hover:-translate-y-0.5 transition-all flex items-center gap-1.5 border border-emerald-500/25">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        Buat Ujian Baru
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        {{-- KPI Metrics --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Total Ujian</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white">{{ $metrics['total'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Berlangsung</p>
                    <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $metrics['ongoing'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $metrics['ongoing'] > 0 ? 'animate-pulse' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Draft</p>
                    <h3 class="text-2xl font-black text-amber-600 dark:text-amber-400">{{ $metrics['draft'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </div>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm flex items-center justify-between group hover:shadow-md transition-all">
                <div class="space-y-1">
                    <p class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Selesai</p>
                    <h3 class="text-2xl font-black text-indigo-600 dark:text-indigo-400">{{ $metrics['completed'] }}</h3>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        {{-- Filter --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800 rounded-3xl p-5 shadow-sm">
            <form action="{{ route('cbt.exams.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Mapel</label>
                    <select name="subject_id" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                        <option value="">Semua Mapel</option>
                        @foreach($subjects as $s)<option value="{{ $s->id }}" {{ request('subject_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Kelas</label>
                    <select name="classroom_id" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                        <option value="">Semua Kelas</option>
                        @foreach($classrooms as $c)<option value="{{ $c->id }}" {{ request('classroom_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>@endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Status</label>
                    <select name="status" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                        <option value="">Semua</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[10px] font-black text-slate-500 dark:text-slate-400 uppercase tracking-wider">Jenis</label>
                    <select name="type" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white py-2.5 focus:border-blue-500 transition-all">
                        <option value="">Semua</option>
                        <option value="ulangan_harian" {{ request('type') === 'ulangan_harian' ? 'selected' : '' }}>Ulangan Harian</option>
                        <option value="uts" {{ request('type') === 'uts' ? 'selected' : '' }}>UTS</option>
                        <option value="uas" {{ request('type') === 'uas' ? 'selected' : '' }}>UAS</option>
                        <option value="try_out" {{ request('type') === 'try_out' ? 'selected' : '' }}>Try Out</option>
                    </select>
                </div>
                <button type="submit" class="py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl text-xs font-black uppercase tracking-wider hover:bg-slate-700 dark:hover:bg-slate-600 transition-all">Filter</button>
            </form>
        </div>

        {{-- Table --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]">
                    <thead>
                        <tr class="bg-slate-50/60 dark:bg-slate-950/40 border-b border-gray-100 dark:border-slate-800/80 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <th class="px-6 py-4">Ujian</th>
                            <th class="px-6 py-4">Mapel</th>
                            <th class="px-6 py-4">Kelas</th>
                            <th class="px-6 py-4 text-center">Jadwal</th>
                            <th class="px-6 py-4 text-center">Durasi</th>
                            <th class="px-6 py-4 text-center">Soal</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800/60 text-xs font-semibold text-gray-700 dark:text-slate-300">
                        @forelse($exams as $exam)
                            <tr class="hover:bg-slate-50/30 dark:hover:bg-slate-950/10 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $exam->title }}</div>
                                    @php
                                        $typeLabel = match($exam->type) { 'uts' => 'UTS', 'uas' => 'UAS', 'ulangan_harian' => 'UH', 'try_out' => 'Try Out', default => $exam->type };
                                        $typeColor = match($exam->type) { 'uts' => 'bg-purple-500/10 text-purple-600 border-purple-500/20', 'uas' => 'bg-rose-500/10 text-rose-600 border-rose-500/20', 'ulangan_harian' => 'bg-cyan-500/10 text-cyan-600 border-cyan-500/20', 'try_out' => 'bg-amber-500/10 text-amber-600 border-amber-500/20', default => 'bg-slate-500/10 text-slate-600 border-slate-500/20' };
                                    @endphp
                                    <span class="mt-1 inline-block px-2 py-0.5 rounded-md text-[9px] font-black uppercase tracking-wider border {{ $typeColor }}">{{ $typeLabel }}</span>
                                </td>
                                <td class="px-6 py-4">{{ $exam->subject?->name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $exam->classroom?->name ?? 'Semua Kelas' }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <div class="text-slate-800 dark:text-white font-bold">{{ $exam->start_time->format('d M Y') }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $exam->start_time->format('H:i') }} — {{ $exam->end_time->format('H:i') }}</div>
                                </td>
                                <td class="px-6 py-4 text-center font-bold">{{ $exam->duration_minutes }} mnt</td>
                                <td class="px-6 py-4 text-center font-bold">{{ $exam->total_questions }}</td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $statColor = match($exam->status) { 'draft' => 'bg-slate-500/10 text-slate-500 border-slate-500/20', 'published' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20', 'completed' => 'bg-blue-500/10 text-blue-600 border-blue-500/20', default => 'bg-slate-500/10 text-slate-600 border-slate-500/20' };
                                    @endphp
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $statColor }}">{{ strtoupper($exam->status) }}</span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap space-x-1">
                                    @if(auth()->user()->role === 'siswa' && $exam->isOngoing())
                                        <a href="{{ route('cbt.exams.take', $exam) }}" class="inline-block px-3 py-1.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-lg text-[10px] font-black uppercase tracking-wider hover:from-emerald-700 hover:to-teal-700 transition-all shadow-sm">Ikuti Ujian</a>
                                    @endif
                                    @if(in_array(auth()->user()->role, ['guru', 'waka_kurikulum', 'admin', 'superadmin']))
                                        <a href="{{ route('cbt.exams.show', $exam) }}" class="inline-block px-3 py-1.5 bg-slate-100 hover:bg-indigo-600 dark:bg-slate-800 dark:hover:bg-indigo-600 text-slate-700 hover:text-white dark:text-slate-300 dark:hover:text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition-all border border-slate-200 dark:border-slate-700/60 hover:border-indigo-600">Detail</a>
                                        @if($exam->status === 'published')
                                            <a href="{{ route('cbt.exams.monitor', $exam) }}" class="inline-block px-3 py-1.5 bg-amber-500/10 hover:bg-amber-600 text-amber-600 hover:text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition-all border border-amber-500/20 hover:border-amber-600">Monitor</a>
                                        @endif
                                        <a href="{{ route('cbt.exams.results', $exam) }}" class="inline-block px-3 py-1.5 bg-blue-500/10 hover:bg-blue-600 text-blue-600 hover:text-white rounded-lg text-[10px] font-black uppercase tracking-wider transition-all border border-blue-500/20 hover:border-blue-600">Hasil</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-slate-400 dark:text-slate-500 italic">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        <span class="font-bold">Belum ada ujian yang tersedia.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($exams->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-slate-800 bg-slate-50/20 dark:bg-slate-950/20">{{ $exams->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
