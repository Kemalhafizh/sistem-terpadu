<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h2 class="font-bold text-xl text-gray-800 dark:text-white leading-tight tracking-tight">
                {{ __('Detail Diskusi') }}
            </h2>
            <a href="{{ route('forum.index') }}" class="inline-flex px-4 py-2 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 rounded-xl text-xs font-bold hover:bg-slate-50 dark:hover:bg-slate-950/30 transition-all items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali ke Daftar Forum</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">
            
            <!-- Alerts -->
            @if(session('success'))
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-900/50 rounded-2xl flex items-center space-x-3 text-emerald-800 dark:text-emerald-400 animate-slide-in">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xs font-extrabold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- MAIN THREAD POST CARD -->
            <div class="relative overflow-hidden bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-[2rem] border {{ $thread->is_pinned ? 'border-indigo-400 dark:border-indigo-700 shadow-indigo-100 dark:shadow-none' : 'border-gray-100 dark:border-slate-800' }} p-6 sm:p-8 shadow-xl space-y-6">
                <!-- Glowing pin decoration -->
                @if($thread->is_pinned)
                    <div class="absolute -right-6 -top-6 w-24 h-24 bg-indigo-500/10 rounded-full blur-2xl"></div>
                @endif

                <!-- Author info & Meta -->
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 dark:border-slate-800 pb-5">
                    <div class="flex items-center space-x-4">
                        <!-- Avatar -->
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center shadow-lg overflow-hidden shrink-0 border border-indigo-200 dark:border-indigo-800">
                            <span class="text-sm font-black text-white">
                                {{ strtoupper(substr($thread->author->name, 0, 2)) }}
                            </span>
                        </div>
                        <div>
                            <h4 class="text-sm font-black text-gray-900 dark:text-white flex items-center gap-1.5">
                                {{ $thread->author->name }}
                                <span class="px-2 py-0.5 bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 rounded text-[9px] font-black uppercase tracking-wider">
                                    {{ $thread->author->role }}
                                </span>
                            </h4>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">
                                Diterbitkan {{ $thread->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>

                    <!-- Pinned/Announce badge & Scope info -->
                    <div class="flex items-center space-x-2">
                        @if($thread->is_pinned)
                            <span class="inline-flex px-2 py-1 bg-indigo-100 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 rounded-md text-[9px] font-black uppercase tracking-wider border border-indigo-200 dark:border-indigo-900">
                                📌 PINNED
                            </span>
                        @endif
                        
                        @if($thread->is_announcement)
                            <span class="inline-flex px-2 py-1 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 rounded-md text-[9px] font-black uppercase tracking-wider border border-emerald-200 dark:border-emerald-900">
                                📢 PENGUMUMAN
                            </span>
                        @endif

                        @if($thread->scope === 'school')
                            <span class="inline-flex px-2 py-1 bg-sky-50 dark:bg-sky-950/30 text-sky-700 dark:text-sky-400 rounded text-[9px] font-black uppercase tracking-wide border border-sky-100 dark:border-sky-900/30">
                                Sekolah (Publik)
                            </span>
                        @elseif($thread->scope === 'class')
                            <span class="inline-flex px-2 py-1 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 rounded text-[9px] font-black uppercase tracking-wide border border-amber-100 dark:border-amber-900/30">
                                Kelas: {{ $thread->classroom?->name ?? 'Semua' }}
                            </span>
                        @elseif($thread->scope === 'subject')
                            <span class="inline-flex px-2 py-1 bg-violet-50 dark:bg-violet-950/30 text-violet-700 dark:text-violet-400 rounded text-[9px] font-black uppercase tracking-wide border border-violet-100 dark:border-violet-900/30">
                                Mapel: {{ $thread->subject?->name ?? 'Lainnya' }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Discussion Body -->
                <div class="space-y-6">
                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white leading-tight">
                        {{ $thread->title }}
                    </h3>
                    
                    <!-- Markdown body styling -->
                    <div class="text-xs sm:text-sm text-gray-700 dark:text-slate-300 leading-relaxed space-y-4 max-w-none break-words">
                        {!! nl2br(e($thread->body)) !!}
                    </div>

                    <!-- Attachment if any -->
                    @if($thread->attachment_path)
                        <div class="p-4 bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800/80 rounded-2xl flex items-center justify-between gap-4 max-w-md">
                            <div class="flex items-center space-x-3 overflow-hidden">
                                <span class="p-2 bg-indigo-50 dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 rounded-lg">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </span>
                                <div class="overflow-hidden">
                                    <h5 class="text-xs font-black text-gray-800 dark:text-slate-200 truncate">Berkas Lampiran</h5>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase truncate">{{ basename($thread->attachment_path) }}</p>
                                </div>
                            </div>
                            <a href="{{ asset('storage/' . $thread->attachment_path) }}" target="_blank" download class="inline-flex px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-[10px] font-black transition-all shadow-md shrink-0">
                                Unduh
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Footer row (Reactions counts and Action controls) -->
                <div class="pt-6 border-t border-gray-100/50 dark:border-slate-800/50 flex flex-wrap items-center justify-between gap-6">
                    
                    <!-- Reactions (Polymorphic Likes/Helpful/Insightful) -->
                    <div class="flex items-center space-x-2">
                        @if(auth()->user()->role !== 'parent')
                            <form action="{{ route('forum.react') }}" method="POST" class="inline flex items-center space-x-1">
                                @csrf
                                <input type="hidden" name="reactable_type" value="thread">
                                <input type="hidden" name="reactable_id" value="{{ $thread->id }}">
                                
                                <!-- Like 👍 -->
                                <button type="submit" name="type" value="like" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold transition-all border {{ $thread->reactions->where('user_id', auth()->id())->where('type', 'like')->count() > 0 ? 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border-indigo-200/50 dark:border-indigo-900/50' : 'bg-transparent text-gray-500 dark:text-slate-400 border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-950/50' }}">
                                    <span>👍</span>
                                    <span class="ml-1.5">{{ $thread->reactions->where('type', 'like')->count() }}</span>
                                </button>

                                <!-- Helpful 💡 -->
                                <button type="submit" name="type" value="helpful" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold transition-all border {{ $thread->reactions->where('user_id', auth()->id())->where('type', 'helpful')->count() > 0 ? 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border-amber-200/50 dark:border-amber-900/50' : 'bg-transparent text-gray-500 dark:text-slate-400 border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-950/50' }}">
                                    <span>💡</span>
                                    <span class="ml-1.5">{{ $thread->reactions->where('type', 'helpful')->count() }}</span>
                                </button>

                                <!-- Insightful 🧠 -->
                                <button type="submit" name="type" value="insightful" class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold transition-all border {{ $thread->reactions->where('user_id', auth()->id())->where('type', 'insightful')->count() > 0 ? 'bg-violet-50 dark:bg-violet-950/30 text-violet-600 dark:text-violet-400 border-violet-200/50 dark:border-violet-900/50' : 'bg-transparent text-gray-500 dark:text-slate-400 border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-950/50' }}">
                                    <span>🧠</span>
                                    <span class="ml-1.5">{{ $thread->reactions->where('type', 'insightful')->count() }}</span>
                                </button>
                            </form>
                        @else
                            <!-- Parents view only reaction numbers -->
                            <div class="flex items-center space-x-2 text-xs font-bold text-slate-400">
                                <span class="bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-800 px-2.5 py-1 rounded-full">👍 {{ $thread->reactions->where('type', 'like')->count() }}</span>
                                <span class="bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-800 px-2.5 py-1 rounded-full">💡 {{ $thread->reactions->where('type', 'helpful')->count() }}</span>
                                <span class="bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-800 px-2.5 py-1 rounded-full">🧠 {{ $thread->reactions->where('type', 'insightful')->count() }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Moderation controls for authorized users -->
                    <div class="flex items-center space-x-2">
                        @can('moderate', $thread)
                            <form action="{{ route('forum.threads.pin', $thread->uuid) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-950/35 text-slate-600 dark:text-slate-400 rounded-xl text-xs font-bold transition-all" title="{{ $thread->is_pinned ? 'Lepas Sematan' : 'Sematkan Utas' }}">
                                    <svg class="w-4 h-4 {{ $thread->is_pinned ? 'text-indigo-500' : '' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </form>

                            <form action="{{ route('forum.threads.lock', $thread->uuid) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-950/35 text-slate-600 dark:text-slate-400 rounded-xl text-xs font-bold transition-all" title="{{ $thread->is_locked ? 'Buka Kunci' : 'Kunci Utas' }}">
                                    <svg class="w-4 h-4 {{ $thread->is_locked ? 'text-red-500' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        @if($thread->is_locked)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        @endif
                                    </svg>
                                </button>
                            </form>
                        @endcan

                        @can('deleteThread', $thread)
                            <form action="{{ route('forum.threads.destroy', $thread->uuid) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus diskusi ini? Tindakan ini tidak bisa dibatalkan.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 border border-red-200/50 hover:bg-red-50 dark:hover:bg-red-950/30 text-red-600 dark:text-red-400 rounded-xl text-xs font-bold transition-all" title="Hapus Utas">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- REPLIES SECTION -->
            <div class="space-y-6">
                <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                    <span class="w-8 h-8 bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </span>
                    Tanggapan & Jawaban ({{ $thread->replies_count }})
                </h3>

                @if($replies->isEmpty())
                    <div class="bg-white/50 dark:bg-slate-900/50 border border-gray-100 dark:border-slate-800 rounded-3xl p-10 text-center max-w-md mx-auto space-y-3">
                        <p class="text-xs text-gray-500 dark:text-slate-400 font-bold uppercase tracking-wider">Belum ada tanggapan</p>
                        <p class="text-[11px] text-gray-400 dark:text-slate-500">Jadilah yang pertama untuk memberikan tanggapan informatif pada diskusi ini!</p>
                    </div>
                @else
                    <div class="space-y-6" id="replies-list-container">
                        @foreach($replies as $reply)
                            <!-- Top level reply (Level 0) -->
                            <div class="space-y-4" id="reply-group-{{ $reply->id }}">
                                @include('forum.partials.reply-card', ['reply' => $reply, 'depth' => 0])

                                <!-- Child replies (Level 1) Container -->
                                <div id="reply-children-{{ $reply->id }}" class="ml-10 sm:ml-12 border-l border-slate-200 dark:border-slate-800/80 pl-6 sm:pl-8 space-y-4 relative {{ $reply->children->isEmpty() ? 'hidden' : '' }}">
                                    @foreach($reply->children as $child)
                                        <div class="relative" id="reply-group-{{ $child->id }}">
                                            <!-- Thread connector line styling -->
                                            <div class="absolute -left-px top-6 w-6 border-b border-slate-200 dark:border-slate-800/80"></div>
                                            @include('forum.partials.reply-card', ['reply' => $child, 'depth' => 1])

                                            <!-- Grandchild replies (Level 2) Container -->
                                            <div id="reply-children-{{ $child->id }}" class="ml-8 sm:ml-10 border-l border-slate-200 dark:border-slate-800/80 pl-6 sm:pl-8 space-y-4 relative {{ $child->children->isEmpty() ? 'hidden' : '' }}">
                                                @foreach($child->children as $grandchild)
                                                    <div class="relative" id="reply-group-{{ $grandchild->id }}">
                                                        <div class="absolute -left-px top-6 w-6 border-b border-slate-200 dark:border-slate-800/80"></div>
                                                        @include('forum.partials.reply-card', ['reply' => $grandchild, 'depth' => 2])
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Replies Pagination -->
                    <div class="pt-6">
                        {{ $replies->links() }}
                    </div>
                @endif
            </div>

            <!-- REPLY INPUT FORM -->
            <div id="reply_editor_section" class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-3xl border border-gray-100 dark:border-slate-800 p-6 sm:p-8 shadow-xl space-y-4">
                
                @if($thread->is_locked && !auth()->user()->isAdmin() && auth()->user()->role !== 'guru')
                    <!-- Locked banner -->
                    <div class="p-6 bg-amber-50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-900/50 rounded-2xl flex flex-col items-center text-center space-y-2 text-amber-800 dark:text-amber-400">
                        <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <h4 class="text-xs font-black uppercase tracking-wider">Topik Diskusi Ini Telah Dikunci</h4>
                        <p class="text-[11px] text-amber-600 dark:text-amber-500">Guru pengajar atau admin telah mengunci utas ini. Tanggapan baru tidak dapat ditambahkan.</p>
                    </div>
                @elseif(auth()->user()->role === 'parent')
                    <!-- Parent read-only notification banner -->
                    <div class="p-5 bg-slate-50 dark:bg-slate-950/40 border border-slate-200/50 dark:border-slate-800/50 rounded-2xl flex flex-col items-center text-center space-y-1 text-slate-500 dark:text-slate-400">
                        <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <h4 class="text-xs font-black uppercase tracking-wider">Akses Penonton (Read-Only)</h4>
                        <p class="text-[10px] text-slate-400">Sebagai Orang Tua, Anda hanya memiliki izin membaca forum untuk memantau aktivitas akademik anak Anda.</p>
                    </div>
                @else
                    <!-- Writing Reply Form -->
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-slate-800">
                        <h4 class="text-sm font-black text-gray-900 dark:text-white flex items-center">
                            <span class="w-6 h-6 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded flex items-center justify-center mr-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path></svg>
                            </span>
                            Kirim Tanggapan
                        </h4>
                        
                        <!-- Quick Reply Information Indicator -->
                        <div id="replying_indicator" class="hidden flex items-center space-x-2 bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900 px-3 py-1 rounded-xl text-[10px] font-bold text-indigo-700 dark:text-indigo-400">
                            <span>Membalas ke: <strong id="replying_name"></strong></span>
                            <button type="button" id="cancel_reply_target" class="text-red-500 hover:text-red-700 focus:outline-none">&times;</button>
                        </div>
                    </div>

                    <form action="{{ route('forum.replies.store', $thread->uuid) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="parent_id" id="reply_parent_id" value="">

                        <!-- Rich input textarea -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Teks Tanggapan</span>
                                <button type="button" id="reply_preview_toggle" class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase hover:underline">Pratinjau Markdown</button>
                            </div>
                            <div class="space-y-2">
                                <textarea name="body" id="reply_body" required rows="5" placeholder="Tuliskan tanggapan atau penjelasan informatif Anda..." class="w-full text-xs font-medium rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-800 dark:text-white px-4 py-3 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 transition-all"></textarea>
                                
                                <div id="reply_preview_container" class="hidden min-h-[100px] p-4 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-xs prose dark:prose-invert max-w-none overflow-y-auto leading-relaxed">
                                    <span class="text-slate-400 italic">Menyiapkan pratinjau...</span>
                                </div>
                            </div>
                        </div>

                        <!-- Attachment and button -->
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pt-2">
                            <div class="w-full sm:w-2/3 space-y-1">
                                <label for="reply_attachment" class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest block">Unggah Lampiran Pendukung (Opsional)</label>
                                <input type="file" name="attachment" id="reply_attachment" class="w-full text-xs text-slate-500 border border-gray-150 dark:border-slate-800 bg-transparent rounded-lg px-2.5 py-1 focus:indigo-500">
                            </div>

                            <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black shadow-md hover:-translate-y-0.5 transition-all self-end shrink-0">
                                Kirim Tanggapan
                            </button>
                        </div>
                    </form>
                @endif
            </div>

        </div>
    </div>

    <!-- Nested Reply trigger and live Markdown preview script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Laravel Echo setup for forum replies
            const threadId = {{ $thread->id }};
            if (window.Echo) {
                window.Echo.private('forum.thread.' + threadId)
                    .listen('.reply-posted', (e) => {
                        console.log('Real-time forum reply received:', e);
                        if (e.reply) {
                            appendReplyToDOM(e.reply);
                        }
                    });
            }
            
            // Render reply card function
            function renderReplyCard(reply, depth) {
                const isBest = reply.is_best_answer;
                const authorInitials = reply.author.name.substring(0, 2).toUpperCase();
                
                let attachmentHtml = '';
                if (reply.attachment_path) {
                    const baseName = reply.attachment_path.split('/').pop();
                    attachmentHtml = `
                        <div class="p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800/80 rounded-xl flex items-center justify-between gap-3 max-w-sm">
                            <div class="flex items-center space-x-2 overflow-hidden">
                                <span class="p-1.5 bg-indigo-50 dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </span>
                                <div class="overflow-hidden">
                                    <h5 class="text-[10px] font-bold text-gray-800 dark:text-slate-200 truncate">Lampiran Balasan</h5>
                                    <p class="text-[8px] text-slate-400 font-bold uppercase truncate">${baseName}</p>
                                </div>
                            </div>
                            <a href="/storage/${reply.attachment_path}" target="_blank" download class="inline-flex px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-[9px] font-black transition-all shadow-md shrink-0">
                                Unduh
                            </a>
                        </div>
                    `;
                }
                
                let replyButtonHtml = '';
                const currentUserRole = "{{ auth()->user()->role }}";
                const isLocked = {{ $thread->is_locked ? 'true' : 'false' }};
                const isTeacherOrAdmin = {{ (auth()->user()->isAdmin() || auth()->user()->role === 'guru') ? 'true' : 'false' }};
                if (currentUserRole !== 'parent' && depth < 2 && (!isLocked || isTeacherOrAdmin)) {
                    replyButtonHtml = `
                        <button type="button" data-reply-id="${reply.id}" data-author-name="${reply.author.name}" class="trigger-quick-reply inline-flex px-3 py-1.5 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-950/30 text-slate-600 dark:text-slate-400 rounded-xl text-[10px] font-black transition-all items-center space-x-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                            </svg>
                            <span>Balas</span>
                        </button>
                    `;
                }

                return `
                    <div id="reply-card-${reply.id}" class="relative bg-white/70 dark:bg-slate-900/70 backdrop-blur-md rounded-2xl border border-gray-150/70 dark:border-slate-800/80 p-5 shadow-sm space-y-4 transition-all duration-300 animate-fade-in">
                        <!-- Header Row -->
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100/50 dark:border-slate-800/50 pb-3">
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 flex items-center justify-center shadow-inner overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
                                    <span class="text-2xs font-extrabold text-slate-700 dark:text-slate-300">${authorInitials}</span>
                                </div>
                                <div>
                                    <h5 class="text-xs font-black text-gray-800 dark:text-white leading-tight">${reply.author.name}</h5>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">${reply.author.role}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="text-[9px] font-medium text-slate-400 dark:text-slate-500">${reply.created_at_human}</span>
                            </div>
                        </div>
                        <!-- Body Text -->
                        <div class="text-xs text-gray-700 dark:text-slate-300 leading-relaxed break-words">
                            ${reply.body.replace(/\n/g, '<br />')}
                        </div>
                        ${attachmentHtml}
                        <!-- Footer Row -->
                        <div class="flex flex-wrap items-center justify-end gap-4 pt-3 border-t border-gray-100/50 dark:border-slate-800/50">
                            <div class="flex items-center space-x-2">
                                ${replyButtonHtml}
                            </div>
                        </div>
                    </div>
                `;
            }

            function appendReplyToDOM(reply) {
                if (document.getElementById(`reply-card-${reply.id}`)) return;
                
                const emptyBanner = document.querySelector('.bg-white\\/50.dark\\:bg-slate-900\\/50');
                if (emptyBanner) emptyBanner.remove();
                
                let depth = 0;
                if (reply.parent_id) {
                    const parentGroup = document.getElementById(`reply-group-${reply.parent_id}`);
                    if (parentGroup) {
                        const parentChildrenContainer = document.getElementById(`reply-children-${reply.parent_id}`);
                        if (parentChildrenContainer) {
                            const isParentLevel0 = parentGroup.parentElement.id === 'replies-list-container';
                            depth = isParentLevel0 ? 1 : 2;
                            
                            const cardHtml = `
                                <div class="relative animate-fade-in" id="reply-group-${reply.id}">
                                    <div class="absolute -left-px top-6 w-6 border-b border-slate-200 dark:border-slate-800/80"></div>
                                    ${renderReplyCard(reply, depth)}
                                    ${depth === 1 ? `<div id="reply-children-${reply.id}" class="ml-8 sm:ml-10 border-l border-slate-200 dark:border-slate-800/80 pl-6 sm:pl-8 space-y-4 relative hidden"></div>` : ''}
                                </div>
                            `;
                            
                            parentChildrenContainer.insertAdjacentHTML('beforeend', cardHtml);
                            parentChildrenContainer.classList.remove('hidden');
                            bindQuickReply(reply.id);
                        }
                    }
                } else {
                    const repliesContainer = document.getElementById('replies-list-container');
                    if (repliesContainer) {
                        const cardHtml = `
                            <div class="space-y-4 animate-fade-in" id="reply-group-${reply.id}">
                                ${renderReplyCard(reply, 0)}
                                <div id="reply-children-${reply.id}" class="ml-10 sm:ml-12 border-l border-slate-200 dark:border-slate-800/80 pl-6 sm:pl-8 space-y-4 relative hidden"></div>
                            </div>
                        `;
                        repliesContainer.insertAdjacentHTML('beforeend', cardHtml);
                        bindQuickReply(reply.id);
                    }
                }
                
                const newCard = document.getElementById(`reply-card-${reply.id}`);
                if (newCard) {
                    newCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            function bindQuickReply(replyId) {
                const card = document.getElementById(`reply-card-${replyId}`);
                if (!card) return;
                
                const replyBtn = card.querySelector('.trigger-quick-reply');
                if (replyBtn) {
                    replyBtn.addEventListener('click', (e) => {
                        e.preventDefault();
                        const parentIdInput = document.getElementById('reply_parent_id');
                        const indicator = document.getElementById('replying_indicator');
                        const nameSpan = document.getElementById('replying_name');
                        const bodyArea = document.getElementById('reply_body');
                        
                        if (parentIdInput) {
                            parentIdInput.value = replyId;
                            nameSpan.textContent = replyBtn.dataset.authorName;
                            indicator.classList.remove('hidden');
                            
                            document.getElementById('reply_editor_section').scrollIntoView({ behavior: 'smooth' });
                            bodyArea.focus();
                        }
                    });
                }
            }

            // Bind existing quick reply buttons
            const replyBtns = document.querySelectorAll('.trigger-quick-reply');
            replyBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const replyId = btn.dataset.replyId;
                    const authorName = btn.dataset.authorName;

                    const parentIdInput = document.getElementById('reply_parent_id');
                    const indicator = document.getElementById('replying_indicator');
                    const nameSpan = document.getElementById('replying_name');
                    const bodyArea = document.getElementById('reply_body');

                    if(parentIdInput) {
                        parentIdInput.value = replyId;
                        nameSpan.textContent = authorName;
                        indicator.classList.remove('hidden');
                        
                        document.getElementById('reply_editor_section').scrollIntoView({ behavior: 'smooth' });
                        bodyArea.focus();
                    }
                });
            });

            const cancelBtn = document.getElementById('cancel_reply_target');
            if(cancelBtn) {
                cancelBtn.addEventListener('click', () => {
                    const parentIdInput = document.getElementById('reply_parent_id');
                    const indicator = document.getElementById('replying_indicator');
                    if (parentIdInput) parentIdInput.value = '';
                    if (indicator) indicator.classList.add('hidden');
                });
            }

            // AJAX Form Submission
            const replyForm = document.querySelector('#reply_editor_section form');
            if (replyForm) {
                replyForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    
                    const formData = new FormData(replyForm);
                    const submitBtn = replyForm.querySelector('button[type="submit"]');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Mengirim...';
                    
                    try {
                        const response = await fetch(replyForm.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            if (data.success && data.reply) {
                                document.getElementById('reply_body').value = '';
                                document.getElementById('reply_attachment').value = '';
                                
                                const parentIdInput = document.getElementById('reply_parent_id');
                                if (parentIdInput) parentIdInput.value = '';
                                
                                const indicator = document.getElementById('replying_indicator');
                                if (indicator) indicator.classList.add('hidden');
                                
                                appendReplyToDOM(data.reply);
                                
                                const alertHtml = `
                                    <div id="temp-success-alert" class="p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/50 dark:border-emerald-900/50 rounded-2xl flex items-center space-x-3 text-emerald-800 dark:text-emerald-400 animate-slide-in mb-6">
                                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <span class="text-xs font-extrabold">Tanggapan berhasil dikirim!</span>
                                    </div>
                                `;
                                const contentWrapper = document.querySelector('.max-w-6xl');
                                if (contentWrapper) {
                                    contentWrapper.insertAdjacentHTML('afterbegin', alertHtml);
                                    setTimeout(() => {
                                        document.getElementById('temp-success-alert')?.remove();
                                    }, 5000);
                                }
                            }
                        } else {
                            alert('Gagal mengirim tanggapan. Silakan coba lagi.');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Terjadi kesalahan jaringan.');
                    } finally {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Kirim Tanggapan';
                    }
                });
            }

            // Markdown preview for reply
            const replyBodyText = document.getElementById('reply_body');
            const replyPreviewContainer = document.getElementById('reply_preview_container');
            const replyPreviewToggle = document.getElementById('reply_preview_toggle');
            
            let isReplyPreview = false;

            if(replyPreviewToggle) {
                replyPreviewToggle.addEventListener('click', () => {
                    isReplyPreview = !isReplyPreview;
                    if (isReplyPreview) {
                        replyBodyText.classList.add('hidden');
                        replyPreviewContainer.classList.remove('hidden');
                        replyPreviewToggle.textContent = 'Edit Konten';
                        
                        let raw = replyBodyText.value;
                        if (!raw.trim()) {
                            replyPreviewContainer.innerHTML = '<span class="text-slate-400 italic">Konten kosong.</span>';
                            return;
                        }

                        let parsed = raw
                            .replace(/&/g, "&amp;")
                            .replace(/</g, "&lt;")
                            .replace(/>/g, "&gt;")
                            .replace(/\*\*(.*)\*\*/gim, '<strong>$1</strong>')
                            .replace(/\*(.*)\*/gim, '<em>$1</em>')
                            .replace(/^\s*\-\s(.*$)/gim, '<li class="list-disc ml-4">$1</li>')
                            .replace(/\n$/gim, '<br />')
                            .replace(/\n/g, '<br />');

                        replyPreviewContainer.innerHTML = parsed;
                    } else {
                        replyBodyText.classList.remove('hidden');
                        replyPreviewContainer.classList.add('hidden');
                        replyPreviewToggle.textContent = 'Pratinjau Markdown';
                    }
                });
            }
        });
    </script>
</x-app-layout>
