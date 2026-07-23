<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('elearning.index') }}" class="text-gray-400 hover:text-emerald-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                {{ $elearningClass->name }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10" x-data="{ activeTab: 'stream' }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">

            <!-- Class Cover Banner -->
            <div class="bg-gradient-to-r {{ $elearningClass->is_archived ? 'from-slate-700 to-slate-900' : 'from-emerald-800 to-emerald-950' }} text-white rounded-[2rem] p-6 sm:p-10 shadow-lg relative overflow-hidden">
                <div class="absolute -right-16 -top-16 w-48 h-48 bg-emerald-500/10 rounded-full blur-2xl"></div>
                <div class="relative z-10 space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex px-2.5 py-1 bg-emerald-600/30 text-emerald-300 rounded text-[10px] font-black uppercase tracking-wider">
                            {{ $elearningClass->subject?->name }}
                        </span>
                        @if($elearningClass->is_archived)
                            <span class="inline-flex px-2.5 py-1 bg-red-600/30 text-red-300 rounded text-[10px] font-black uppercase tracking-wider">
                                Diarsipkan
                            </span>
                        @endif
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight">{{ $elearningClass->name }}</h3>
                    <p class="text-xs sm:text-sm text-emerald-200">
                        Kelas Fisik: <strong class="text-white">{{ $elearningClass->classroom?->name }}</strong>
                        <span class="mx-2">|</span>
                        Pengajar: <strong class="text-white">{{ $elearningClass->teacher?->name }}</strong>
                    </p>
                    @if($elearningClass->description)
                        <div class="pt-4 border-t border-emerald-700/50 max-w-xl">
                            <p class="text-xs sm:text-sm text-gray-200 leading-relaxed">{{ $elearningClass->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tabbed Navigation (Mobile-first, Premium Glassmorphism) -->
            <div class="flex p-1 bg-gray-100 dark:bg-slate-800 rounded-2xl max-w-md">
                <button @click="activeTab = 'stream'" :class="activeTab === 'stream' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="flex-1 py-2.5 text-center text-xs font-black uppercase tracking-wider rounded-xl transition-all">
                    Aktivitas Kelas
                </button>
                <button @click="activeTab = 'materials'" :class="activeTab === 'materials' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="flex-1 py-2.5 text-center text-xs font-black uppercase tracking-wider rounded-xl transition-all">
                    Materi Belajar
                </button>
                <button @click="activeTab = 'assignments'" :class="activeTab === 'assignments' ? 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 shadow-sm' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white'" class="flex-1 py-2.5 text-center text-xs font-black uppercase tracking-wider rounded-xl transition-all">
                    Tugas & Ujian
                </button>
            </div>

            <!-- STREAM / AKTIVITAS KELAS TAB -->
            <div x-show="activeTab === 'stream'" class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Sidebar (Informasi & Pembuat Konten) -->
                    <div class="space-y-6">
                        @if(in_array(auth()->user()->role, ['guru', 'staf_tu', 'admin', 'superadmin']))
                            <!-- Quick Action Panel for Teachers -->
                            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 space-y-4">
                                <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Aksi Pengajar</h4>
                                <div class="grid grid-cols-1 gap-2">
                                    <button @click="activeTab = 'materials'" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold text-center transition-all flex items-center justify-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        <span>Bagikan Materi</span>
                                    </button>
                                    <button @click="activeTab = 'assignments'" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-xl text-xs font-bold text-center transition-all flex items-center justify-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                        <span>Terbitkan Tugas/Ujian</span>
                                    </button>
                                    
                                    <a href="{{ route('elearning.class.progress', $elearningClass->uuid) }}" class="px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold text-center transition-all flex items-center justify-center space-x-2 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                                        <span>Progress Kelas</span>
                                    </a>

                                    <form action="{{ route('elearning.class.duplicate', $elearningClass->uuid) }}" method="POST" onsubmit="return confirm('Duplikat kelas virtual ini beserta materi & tugasnya?')">
                                        @csrf
                                        <button type="submit" class="w-full px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-xl text-xs font-bold text-center transition-all flex items-center justify-center space-x-2 border border-slate-200 dark:border-slate-800">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path></svg>
                                            <span>Duplikat Kelas</span>
                                        </button>
                                    </form>

                                    @if($elearningClass->is_archived)
                                        <form action="{{ route('elearning.class.unarchive', $elearningClass->uuid) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-2.5 bg-emerald-100 hover:bg-emerald-200 text-emerald-800 rounded-xl text-xs font-bold text-center transition-all flex items-center justify-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"></path></svg>
                                                <span>Puluhkan Kelas</span>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('elearning.class.archive', $elearningClass->uuid) }}" method="POST" onsubmit="return confirm('Arsipkan kelas virtual ini?')">
                                            @csrf
                                            <button type="submit" class="w-full px-4 py-2.5 bg-red-100 hover:bg-red-200 text-red-800 rounded-xl text-xs font-bold text-center transition-all flex items-center justify-center space-x-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>
                                                <span>Arsipkan Kelas</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if(auth()->user()->role === 'student' && $studentProgress)
                            <!-- Student Progress Card -->
                            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 space-y-4">
                                <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Progres Belajar Anda</h4>
                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs font-bold text-gray-700 dark:text-slate-300">
                                        <span>Ketuntasan Kelas</span>
                                        <span class="text-emerald-600 dark:text-emerald-400 font-extrabold">{{ $studentProgress['completion_percentage'] }}%</span>
                                    </div>
                                    <div class="w-full h-2 bg-gray-100 dark:bg-slate-900 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $studentProgress['completion_percentage'] }}%"></div>
                                    </div>
                                    <div class="pt-2 text-[10px] text-gray-400 space-y-1 font-semibold">
                                        <p class="flex justify-between">
                                            <span>Materi Dibaca:</span>
                                            <span class="text-gray-700 dark:text-slate-300 font-bold">{{ $studentProgress['read_materials'] }} / {{ $studentProgress['total_materials'] }}</span>
                                        </p>
                                        <p class="flex justify-between">
                                            <span>Tugas Dikumpulkan:</span>
                                            <span class="text-gray-700 dark:text-slate-300 font-bold">{{ $studentProgress['submitted_assignments'] }} / {{ $studentProgress['total_assignments'] }}</span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-slate-700 space-y-4">
                            <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Mata Pelajaran</h4>
                            <div class="flex items-center space-x-3 bg-gray-50 dark:bg-slate-900 p-4 rounded-2xl">
                                <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center font-bold">
                                    {{ substr($elearningClass->subject?->name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-gray-800 dark:text-white">{{ $elearningClass->subject?->name }}</p>
                                    <p class="text-[10px] font-bold text-gray-400">{{ $elearningClass->subject?->code }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stream timeline -->
                    <div class="lg:col-span-2 space-y-8">

                        <!-- Announcements Section -->
                        <div class="space-y-4">
                            <h4 class="text-base font-extrabold text-gray-900 dark:text-white">Pengumuman Kelas</h4>
                            @if(in_array(auth()->user()->role, ['guru', 'staf_tu', 'admin', 'superadmin']))
                                <form action="{{ route('elearning.announcement.store', $elearningClass->uuid) }}" method="POST" class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl p-4 shadow-sm flex gap-3 items-end">
                                    @csrf
                                    <div class="flex-1 space-y-1">
                                        <textarea name="content" required rows="2" placeholder="Tulis pengumuman baru untuk kelas..." class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3"></textarea>
                                    </div>
                                    <button type="submit" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shrink-0 shadow-md transition-all">
                                        Kirim
                                    </button>
                                </form>
                            @endif

                            @if($announcements->isEmpty())
                                <div class="bg-white/50 dark:bg-slate-800/50 border border-dashed border-gray-200 dark:border-slate-700 rounded-3xl p-6 text-center text-gray-400">
                                    <p class="text-[11px] font-bold">Belum ada pengumuman kelas.</p>
                                </div>
                            @else
                                <div class="space-y-3">
                                    @foreach($announcements as $ann)
                                        <div class="bg-white dark:bg-slate-800 rounded-3xl p-5 border border-gray-100 dark:border-slate-700 shadow-sm relative group">
                                            <div class="flex justify-between items-start gap-4">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-900 text-slate-600 dark:text-slate-400 flex items-center justify-center font-bold text-xs uppercase">
                                                        {{ substr($ann->user?->name ?? 'G', 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-black text-gray-800 dark:text-white">{{ $ann->user?->name }}</p>
                                                        <p class="text-[9px] font-bold text-gray-400">{{ $ann->created_at->diffForHumans() }}</p>
                                                    </div>
                                                </div>
                                                @if(auth()->id() === $ann->user_id || in_array(auth()->user()->role, ['admin', 'superadmin']))
                                                    <form action="{{ route('elearning.announcement.destroy', [$elearningClass->uuid, $ann->uuid]) }}" method="POST" onsubmit="return confirm('Hapus pengumuman ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                            <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed mt-3 whitespace-pre-line">{{ $ann->content }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Main Activity Timeline -->
                        <div class="space-y-4">
                            <h4 class="text-base font-extrabold text-gray-900 dark:text-white">Aliran Aktivitas Terbaru</h4>

                            @php
                                $stream = collect()
                                    ->concat($materials->map(function($m) { $m->type = 'material'; return $m; }))
                                    ->concat($assignments->map(function($a) { $a->type = 'assignment'; return $a; }))
                                    ->sortByDesc('created_at');
                            @endphp

                            @if($stream->isEmpty())
                                <div class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl p-10 text-center text-gray-500">
                                    <p class="text-xs font-bold">Belum ada aktivitas di kelas virtual ini.</p>
                                </div>
                            @else
                                @foreach($stream as $item)
                                    @if($item->type === 'material')
                                        <!-- Material Stream Card -->
                                        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm space-y-4">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                    </div>
                                                    <div>
                                                        <h5 class="text-sm font-extrabold text-gray-900 dark:text-white leading-tight">Materi Baru Diterbitkan</h5>
                                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $item->created_at->diffForHumans() }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    @if(auth()->user()->role === 'student' && auth()->user()->student)
                                                        @php $isViewed = $item->isViewedBy(auth()->user()->student); @endphp
                                                        @if($isViewed)
                                                            <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded text-[9px] font-black uppercase border border-emerald-500/20">Dibaca </span>
                                                        @else
                                                            <span class="px-2 py-0.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded text-[9px] font-black uppercase border border-amber-500/20">Belum Dibaca</span>
                                                        @endif
                                                    @endif
                                                    <span class="inline-flex px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 rounded text-[9px] font-black uppercase tracking-wider">Materi</span>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <h6 class="text-base font-bold text-gray-900 dark:text-white">{{ $item->title }}</h6>
                                                <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed line-clamp-3 whitespace-pre-line">{{ $item->description }}</p>
                                            </div>
                                            
                                            <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-slate-700">
                                                <span class="text-[10px] font-bold text-gray-400">
                                                    Total Diskusi: <strong class="text-gray-700 dark:text-slate-300">{{ $item->comments->count() }} komentar</strong>
                                                </span>
                                                <a href="{{ route('elearning.material.show', [$elearningClass->uuid, $item->uuid]) }}" class="inline-flex px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                                                    Buka Materi & Diskusi
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <!-- Assignment Stream Card -->
                                        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm space-y-4">
                                            <div class="flex items-start justify-between">
                                                <div class="flex items-center space-x-3">
                                                    <div class="w-10 h-10 bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 rounded-xl flex items-center justify-center">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                                    </div>
                                                    <div>
                                                        <h5 class="text-sm font-extrabold text-gray-900 dark:text-white leading-tight">Tugas / Ujian Baru Diterbitkan</h5>
                                                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">{{ $item->created_at->diffForHumans() }}</p>
                                                    </div>
                                                </div>
                                                <div class="flex space-x-1">
                                                    @if($item->is_secure_mode)
                                                        <span class="inline-flex px-2 py-0.5 bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 rounded text-[9px] font-black uppercase tracking-wider">Aman</span>
                                                    @endif
                                                    <span class="inline-flex px-2 py-0.5 bg-amber-50 dark:bg-amber-950 text-amber-700 dark:text-amber-400 rounded text-[9px] font-black uppercase tracking-wider">Tugas</span>
                                                </div>
                                            </div>
                                            <div class="space-y-2">
                                                <h6 class="text-base font-bold text-gray-900 dark:text-white">{{ $item->title }}</h6>
                                                <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed line-clamp-3 whitespace-pre-line">{{ $item->description }}</p>
                                            </div>
                                            
                                            <div class="flex justify-between items-center pt-4 border-t border-gray-100 dark:border-slate-700 text-xs font-bold">
                                                <span class="text-gray-400 font-semibold text-[10px]">
                                                    Batas Waktu: <strong class="text-gray-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($item->due_at)->format('d M Y, H:i') }}</strong>
                                                </span>
                                                
                                                @if(auth()->user()->role === 'student')
                                                    <a href="{{ route('elearning.assignment.show', $item->uuid) }}" class="inline-flex px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                                                        Kerjakan Tugas
                                                    </a>
                                                @else
                                                    <a href="{{ route('elearning.assignment.submissions', $item->uuid) }}" class="inline-flex px-4 py-2 bg-gray-100 hover:bg-gray-200 text-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-xl text-xs font-bold transition-all border border-gray-200 dark:border-slate-800">
                                                        Nilai Siswa ({{ $item->submissions->count() }})
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- MATERIALS TAB -->
            <div x-show="activeTab === 'materials'" class="space-y-6">
                <!-- Create Material Form for Teachers -->
                @if(in_array(auth()->user()->role, ['guru', 'staf_tu', 'admin', 'superadmin']))
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                        <h4 class="text-base font-black text-gray-900 dark:text-white mb-4">Bagikan Materi Belajar Baru</h4>
                        <form action="{{ route('elearning.material.store', $elearningClass->uuid) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-400 uppercase">Judul Materi</label>
                                    <input type="text" name="title" required placeholder="Contoh: Bab 1 Pengenalan Termodinamika" class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-400 uppercase">Link Video (Optional - Youtube)</label>
                                    <input type="url" name="video_url" placeholder="https://youtube.com/watch?v=..." class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3">
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-gray-400 uppercase">Deskripsi / Catatan Materi</label>
                                <textarea name="description" rows="3" placeholder="Tuliskan petunjuk pembelajaran atau ringkasan singkat materi..." class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3"></textarea>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-gray-400 uppercase">Lampiran Berkas PDF / DOC (Max 10MB)</label>
                                <input type="file" name="file" accept=".pdf,.doc,.docx,.zip" class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            </div>
                            <div class="pt-2">
                                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-md transition-all">
                                    Terbitkan Materi
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- List of Materials -->
                <div class="space-y-4">
                    <h4 class="text-base font-extrabold text-gray-900 dark:text-white">Materi Pembelajaran Tersedia</h4>
                    @if($materials->isEmpty())
                        <div class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl p-10 text-center text-gray-500">
                            <p class="text-xs font-bold">Belum ada materi pembelajaran yang dibagikan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($materials as $material)
                                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm space-y-4 hover:shadow-md transition-shadow flex flex-col justify-between">
                                    <div class="space-y-4">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center shrink-0 animate-pulse-slow">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                </div>
                                                <div>
                                                    <h5 class="text-sm font-extrabold text-gray-900 dark:text-white leading-tight line-clamp-1">{{ $material->title }}</h5>
                                                    <p class="text-[10px] font-bold text-gray-400">Dibuat {{ $material->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            @if(auth()->user()->role === 'student' && auth()->user()->student)
                                                @php $isViewed = $material->isViewedBy(auth()->user()->student); @endphp
                                                @if($isViewed)
                                                    <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded text-[9px] font-black uppercase border border-emerald-500/20 shrink-0">Dibaca </span>
                                                @else
                                                    <span class="px-2 py-0.5 bg-amber-500/10 text-amber-600 dark:text-amber-400 rounded text-[9px] font-black uppercase border border-amber-500/20 shrink-0">Baru</span>
                                                @endif
                                            @endif
                                        </div>
                                        @if($material->description)
                                            <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed line-clamp-3 whitespace-pre-line">{{ $material->description }}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="pt-4 border-t border-gray-100 dark:border-slate-700 flex justify-between items-center">
                                        <span class="text-[10px] font-bold text-gray-400">
                                            Total Diskusi: <strong class="text-gray-700 dark:text-slate-300">{{ $material->comments->count() }} komentar</strong>
                                        </span>
                                        <a href="{{ route('elearning.material.show', [$elearningClass->uuid, $material->uuid]) }}" class="inline-flex px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                                            Buka Materi & Diskusi
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- ASSIGNMENTS TAB -->
            <div x-show="activeTab === 'assignments'" class="space-y-6">
                <!-- Create Assignment Form for Teachers -->
                @if(in_array(auth()->user()->role, ['guru', 'staf_tu', 'admin', 'superadmin']))
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-slate-700">
                        <h4 class="text-base font-black text-gray-900 dark:text-white mb-4">Terbitkan Tugas / Ujian Baru</h4>
                        <form action="{{ route('elearning.assignment.store', $elearningClass->uuid) }}" method="POST" class="space-y-4" x-data="{ isSecure: false }">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-400 uppercase">Judul Tugas / Ujian</label>
                                    <input type="text" name="title" required placeholder="Contoh: Kuis Harian Termodinamika X-A" class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3">
                                </div>
                                <div class="space-y-1">
                                    <label class="text-xs font-bold text-gray-400 uppercase">Batas Akhir Pengumpulan (Due Date)</label>
                                    <input type="datetime-local" name="due_at" required class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3">
                                </div>
                            </div>
                            <div class="space-y-1">
                                <label class="text-xs font-bold text-gray-400 uppercase">Instruksi Soal / Detail Pengerjaan</label>
                                <textarea name="description" rows="5" required placeholder="Tuliskan soal-soal atau petunjuk tugas di sini secara lengkap..." class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3"></textarea>
                            </div>
                            
                            <!-- Security and Mode Options -->
                            <div class="bg-gray-50 dark:bg-slate-900 p-4 rounded-2xl grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="flex items-center space-x-2 text-xs font-bold text-gray-700 dark:text-slate-300">
                                        <input type="checkbox" name="is_secure_mode" value="1" x-model="isSecure" class="rounded text-emerald-600 focus:ring-emerald-500">
                                        <span>Aktifkan Mode Aman Seluler (Lock-down)</span>
                                    </label>
                                    <p class="text-[10px] text-gray-400">Siswa wajib pengerjaan via aplikasi PWA standalone, tidak bisa keluar layar/pindah aplikasi selama ujian berlangsung.</p>
                                </div>
                                <div class="space-y-2">
                                    <label class="flex items-center space-x-2 text-xs font-bold text-gray-700 dark:text-slate-300">
                                        <input type="checkbox" name="allow_late" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500">
                                        <span>Izinkan Keterlambatan</span>
                                    </label>
                                    <p class="text-[10px] text-gray-400">Jika diaktifkan, siswa tetap bisa mengumpulkan tugas setelah batas waktu terlewati dengan keterangan terlambat.</p>
                                </div>
                            </div>

                            <div x-show="isSecure" class="space-y-1 bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/30 p-4 rounded-2xl" style="display:none;">
                                <label class="text-xs font-black text-red-800 dark:text-red-400 uppercase">Batas Maksimal Pelanggaran Toleransi (Switch Tab / App)</label>
                                <input type="number" name="max_violations" value="3" min="1" max="10" class="w-32 rounded-xl border-red-200 dark:border-red-900 bg-white dark:bg-slate-950 text-xs font-bold text-red-700 px-4 py-3">
                                <p class="text-[10px] text-red-500 mt-1">Apabila siswa keluar layar/pindah aplikasi melebihi batas ini, lembar ujian otomatis terkunci dengan status pelanggaran integritas (locked_cheated).</p>
                            </div>

                            <div class="pt-2">
                                <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-md transition-all">
                                    Terbitkan Tugas / Ujian
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- List of Assignments -->
                <div class="space-y-4">
                    <h4 class="text-base font-extrabold text-gray-900 dark:text-white">Tugas & Ujian Terjadwal</h4>
                    @if($assignments->isEmpty())
                        <div class="bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 rounded-3xl p-10 text-center text-gray-500">
                            <p class="text-xs font-bold">Belum ada tugas atau ujian yang diterbitkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($assignments as $assignment)
                                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:shadow-md transition-all">
                                    <div class="space-y-2">
                                        <div class="flex items-center space-x-2">
                                            <h5 class="text-base font-extrabold text-gray-900 dark:text-white">{{ $assignment->title }}</h5>
                                            <div class="flex space-x-1">
                                                @if($assignment->is_secure_mode)
                                                    <span class="inline-flex px-2 py-0.5 bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 rounded text-[9px] font-black uppercase tracking-wider">Lock-Down</span>
                                                @endif
                                                @if($assignment->isOverdue())
                                                    <span class="inline-flex px-2 py-0.5 bg-gray-100 dark:bg-slate-900 text-gray-500 rounded text-[9px] font-black uppercase tracking-wider">Berakhir</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 rounded text-[9px] font-black uppercase tracking-wider">Aktif</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-gray-400 dark:text-slate-500 font-bold">
                                            <span>
                                                Batas Waktu: <strong class="text-gray-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($assignment->due_at)->format('d M Y, H:i') }}</strong>
                                            </span>
                                            <span>•</span>
                                            <span>Toleransi Pelanggaran: <strong class="text-gray-600 dark:text-slate-300">{{ $assignment->is_secure_mode ? $assignment->max_violations.'x' : '-' }}</strong></span>
                                        </div>
                                    </div>

                                    <div>
                                        @if(auth()->user()->role === 'student')
                                            <a href="{{ route('elearning.assignment.show', $assignment->uuid) }}" class="inline-flex px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-emerald-900/10">
                                                Kerjakan Tugas
                                            </a>
                                        @else
                                            <a href="{{ route('elearning.assignment.submissions', $assignment->uuid) }}" class="inline-flex px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-slate-700 dark:bg-slate-900 dark:text-slate-300 rounded-xl text-xs font-bold transition-all border border-gray-200 dark:border-slate-800">
                                                Daftar Pengumpulan ({{ $assignment->submissions->count() }})
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
