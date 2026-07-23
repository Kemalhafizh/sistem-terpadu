<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                {{ __('Forum Diskusi & Kelas Virtual') }}
            </h2>
            @can('createThread', App\Models\ForumThread::class)
                <a href="{{ route('forum.create') }}" class="inline-flex px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white rounded-xl text-xs font-bold transition-all shadow-md hover:-translate-y-0.5 items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Mulai Diskusi Baru</span>
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">
            
            <!-- Welcome glassmorphic panel -->
            <div class="relative overflow-hidden rounded-[2rem] p-6 sm:p-8 border border-white/20 dark:border-slate-800/30 shadow-xl backdrop-blur-md bg-gradient-to-br from-indigo-900/80 via-primary-950/80 to-slate-950/90 text-white">
                <!-- Glowing background circles -->
                <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl"></div>
                <div class="absolute left-1/3 top-0 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl"></div>

                <div class="relative z-10 space-y-4">
                    <span class="inline-flex px-3 py-1 bg-indigo-500/20 text-indigo-300 rounded-full text-[10px] font-black uppercase tracking-widest border border-indigo-500/30">
                         Ruang Kolaborasi Asinkron
                    </span>
                    <h3 class="text-2xl sm:text-3xl font-black tracking-tight leading-tight">
                        Diskusikan Pelajaran & Berbagi Pengetahuan
                    </h3>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                        Selamat datang di forum terpadu Sultan Agung 1. Ajukan pertanyaan seputar materi pelajaran, jawab kegelisahan akademik teman sekelas, dan dapatkan bimbingan langsung dari guru-guru ahli.
                    </p>
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8 items-start">
                
                <!-- FILTER SIDEBAR (Left on Large, Top on Mobile) -->
                <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-24">
                    <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 shadow-lg space-y-6">
                        <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 8.293A1 1 0 013 7.586V4z"></path>
                            </svg>
                            Filter Diskusi
                        </h4>

                        <form action="{{ route('forum.index') }}" method="GET" class="space-y-5">
                            <!-- Search -->
                            <div class="space-y-1.5">
                                <label for="search" class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Cari Topik</label>
                                <div class="relative">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari judul / isi..." class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-700 dark:text-slate-300 pl-10 pr-4 py-2.5 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all">
                                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <!-- Scope -->
                            <div class="space-y-1.5">
                                <label for="scope" class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Cakupan</label>
                                <select name="scope" id="scope" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-700 dark:text-slate-300 py-2.5 focus:border-indigo-500 transition-all">
                                    <option value="">Semua Cakupan</option>
                                    <option value="school" {{ request('scope') === 'school' ? 'selected' : '' }}>Sekolah (Publik)</option>
                                    <option value="class" {{ request('scope') === 'class' ? 'selected' : '' }}>Kelas Saya</option>
                                    <option value="subject" {{ request('scope') === 'subject' ? 'selected' : '' }}>Mata Pelajaran</option>
                                </select>
                            </div>

                            <!-- Subject -->
                            <div class="space-y-1.5">
                                <label for="subject_id" class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Mata Pelajaran</label>
                                <select name="subject_id" id="subject_id" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-700 dark:text-slate-300 py-2.5 focus:border-indigo-500 transition-all">
                                    <option value="">Semua Mapel</option>
                                    @foreach($subjects as $subj)
                                        <option value="{{ $subj->id }}" {{ request('subject_id') == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Sort -->
                            <div class="space-y-1.5">
                                <label for="sort" class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Urutkan Berdasarkan</label>
                                <select name="sort" id="sort" class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-700 dark:text-slate-300 py-2.5 focus:border-indigo-500 transition-all">
                                    <option value="recent" {{ request('sort') === 'recent' ? 'selected' : '' }}>Aktivitas Terbaru</option>
                                    <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Utas Terpopuler</option>
                                </select>
                            </div>

                            @if(auth()->user()->role !== 'parent')
                                <!-- My Threads Checkbox -->
                                <div class="flex items-center space-x-2 pt-2">
                                    <input type="checkbox" name="my_threads" id="my_threads" value="1" {{ request('my_threads') ? 'checked' : '' }} class="rounded border-gray-300 dark:border-slate-800 text-indigo-600 focus:border-indigo-500 focus:ring-indigo-500 transition-all">
                                    <label for="my_threads" class="text-xs font-bold text-gray-600 dark:text-slate-400 select-none cursor-pointer">Hanya utas saya</label>
                                </div>
                            @endif

                            <div class="pt-4 flex gap-3">
                                <a href="{{ route('forum.index') }}" class="w-1/2 py-2.5 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl text-xs font-extrabold text-center hover:bg-slate-50 dark:hover:bg-slate-950/30 transition-all">
                                    Reset
                                </a>
                                <button type="submit" class="w-1/2 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-extrabold shadow-md hover:-translate-y-0.5 transition-all">
                                    Terapkan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- UTAS DISCUSSION FEED -->
                <div class="lg:col-span-3 space-y-6">
                    @if(session('success'))
                        <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-900/50 rounded-2xl flex items-center space-x-3 text-emerald-800 dark:text-emerald-400 animate-slide-in">
                            <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs font-extrabold">{{ session('success') }}</span>
                        </div>
                    @endif

                    @if($threads->isEmpty())
                        <div class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border border-gray-100 dark:border-slate-800 rounded-[2rem] p-16 text-center max-w-xl mx-auto space-y-4 shadow-lg">
                            <div class="w-20 h-20 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-500 dark:text-indigo-400 rounded-full flex items-center justify-center mx-auto shadow-inner">
                                <svg class="w-10 h-10 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <h4 class="text-lg font-black text-gray-800 dark:text-white">Belum Ada Utas Diskusi</h4>
                            <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed">
                                Tidak ada diskusi yang cocok dengan filter pencarian Anda, atau mungkin belum ada diskusi yang dibuat untuk cakupan ini.
                            </p>
                            @can('createThread', App\Models\ForumThread::class)
                                <div class="pt-2">
                                    <a href="{{ route('forum.create') }}" class="inline-flex px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black transition-all shadow-md items-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <span>Buat Topik Pertama</span>
                                    </a>
                                </div>
                            @endcan
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($threads as $thread)
                                @php
                                    $isPinned = $thread->is_pinned;
                                    $isAnnounce = $thread->is_announcement;
                                @endphp
                                <div class="relative overflow-hidden bg-white/70 dark:bg-slate-900/70 backdrop-blur-md rounded-3xl border {{ $isPinned ? 'border-indigo-400 dark:border-indigo-700 shadow-indigo-100 dark:shadow-none ring-2 ring-indigo-500/20' : ($isAnnounce ? 'border-emerald-400 dark:border-emerald-700 ring-2 ring-emerald-500/10' : 'border-gray-100 dark:border-slate-800/80') }} p-6 sm:p-7 shadow-sm hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between group">
                                    
                                    <!-- Glow overlay for announcements or pinned posts -->
                                    @if($isPinned)
                                        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl"></div>
                                    @elseif($isAnnounce)
                                        <div class="absolute top-0 right-0 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl"></div>
                                    @endif

                                    <div class="space-y-4">
                                        <!-- Header row (Avatar, Scope Badges, Pinned/Announce Indicator) -->
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <div class="flex items-center space-x-3">
                                                <!-- Avatar -->
                                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 flex items-center justify-center shadow-inner overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
                                                    <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                                        {{ strtoupper(substr($thread->author->name, 0, 2)) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h5 class="text-xs font-black text-gray-800 dark:text-white leading-tight">
                                                        {{ $thread->author->name }}
                                                    </h5>
                                                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                                        {{ $thread->author->role }}
                                                    </p>
                                                </div>
                                            </div>

                                            <div class="flex items-center space-x-2">
                                                @if($isPinned)
                                                    <span class="inline-flex px-2 py-0.5 bg-indigo-100 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 rounded-md text-[9px] font-black uppercase tracking-wider border border-indigo-200 dark:border-indigo-900 animate-pulse">
                                                         PINNED
                                                    </span>
                                                @endif
                                                
                                                @if($isAnnounce)
                                                    <span class="inline-flex px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 rounded-md text-[9px] font-black uppercase tracking-wider border border-emerald-200 dark:border-emerald-900">
                                                         PENGUMUMAN
                                                    </span>
                                                @endif

                                                <!-- Scope badges -->
                                                @if($thread->scope === 'school')
                                                    <span class="inline-flex px-2 py-0.5 bg-sky-50 dark:bg-sky-950/30 text-sky-700 dark:text-sky-400 rounded text-[9px] font-black uppercase tracking-wide border border-sky-100 dark:border-sky-900/30">
                                                        Sekolah
                                                    </span>
                                                @elseif($thread->scope === 'class')
                                                    <span class="inline-flex px-2 py-0.5 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 rounded text-[9px] font-black uppercase tracking-wide border border-amber-100 dark:border-amber-900/30">
                                                        Kelas: {{ $thread->classroom?->name ?? 'Semua' }}
                                                    </span>
                                                @elseif($thread->scope === 'subject')
                                                    <span class="inline-flex px-2 py-0.5 bg-violet-50 dark:bg-violet-950/30 text-violet-700 dark:text-violet-400 rounded text-[9px] font-black uppercase tracking-wide border border-violet-100 dark:border-violet-900/30">
                                                        Mapel: {{ $thread->subject?->name ?? 'Lainnya' }}
                                                    </span>
                                                @endif

                                                @if($thread->is_locked)
                                                    <span class="inline-flex p-1 bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400 rounded border border-red-100 dark:border-red-900/30" title="Diskusi Terkunci">
                                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                                                        </svg>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <!-- Title and Body Preview -->
                                        <div class="space-y-1.5">
                                            <h4 class="text-base font-extrabold text-gray-900 dark:text-white leading-tight group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                                <a href="{{ route('forum.show', $thread->uuid) }}">{{ $thread->title }}</a>
                                            </h4>
                                            <p class="text-xs text-gray-500 dark:text-slate-400 line-clamp-3 leading-relaxed">
                                                {{ strip_tags($thread->body) }}
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Footer metadata -->
                                    <div class="mt-6 pt-4 border-t border-gray-100/50 dark:border-slate-800/50 flex flex-wrap items-center justify-between gap-4">
                                        <div class="flex items-center space-x-4 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">
                                            <span class="flex items-center">
                                                <svg class="w-3.5 h-3.5 mr-1 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                                </svg>
                                                {{ $thread->replies_count }} Jawaban
                                            </span>
                                            
                                            @if($thread->attachment_path)
                                                <span class="flex items-center bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 px-1.5 py-0.5 rounded">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                    Lampiran
                                                </span>
                                            @endif
                                        </div>

                                        <div class="text-[10px] font-semibold text-slate-400 dark:text-slate-500">
                                            @if($thread->last_reply_at)
                                                <span>Aktif terakhir {{ $thread->last_reply_at->diffForHumans() }}</span>
                                            @else
                                                <span>Dibuat {{ $thread->created_at->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="pt-6">
                            {{ $threads->links() }}
                        </div>
                    @endif
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
