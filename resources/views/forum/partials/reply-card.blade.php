@php
    $isBest = $reply->is_best_answer;
@endphp
<div class="relative bg-white/70 dark:bg-slate-900/70 backdrop-blur-md rounded-2xl border {{ $isBest ? 'border-emerald-400 dark:border-emerald-700 shadow-emerald-50 dark:shadow-none ring-2 ring-emerald-500/10' : 'border-gray-150/70 dark:border-slate-800/80' }} p-5 shadow-sm space-y-4 transition-all duration-300">
    
    <!-- Glow overlay for best answers -->
    @if($isBest)
        <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full blur-xl pointer-events-none"></div>
    @endif

    <!-- Header Row (Avatar, Role, Date, Pinned Ribbon) -->
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100/50 dark:border-slate-800/50 pb-3">
        <div class="flex items-center space-x-3">
            <!-- Avatar -->
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-800 dark:to-slate-900 flex items-center justify-center shadow-inner overflow-hidden shrink-0 border border-slate-200 dark:border-slate-700">
                <span class="text-2xs font-extrabold text-slate-700 dark:text-slate-300">
                    {{ strtoupper(substr($reply->author->name, 0, 2)) }}
                </span>
            </div>
            <div>
                <h5 class="text-xs font-black text-gray-800 dark:text-white leading-tight">
                    {{ $reply->author->name }}
                </h5>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">
                    {{ $reply->author->role }}
                </p>
            </div>
        </div>

        <div class="flex items-center space-x-2">
            @if($isBest)
                <span class="inline-flex px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 rounded-md text-[8px] font-black uppercase tracking-wider border border-emerald-200 dark:border-emerald-900 shadow-sm">
                     JAWABAN TERBAIK
                </span>
            @endif

            <span class="text-[9px] font-medium text-slate-400 dark:text-slate-500">
                {{ $reply->created_at->diffForHumans() }}
            </span>
        </div>
    </div>

    <!-- Body text -->
    <div class="text-xs text-gray-700 dark:text-slate-300 leading-relaxed break-words">
        {!! nl2br(e($reply->body)) !!}
    </div>

    <!-- File Attachment if any -->
    @if($reply->attachment_path)
        <div class="p-3 bg-slate-50 dark:bg-slate-950 border border-slate-200/50 dark:border-slate-800/80 rounded-xl flex items-center justify-between gap-3 max-w-sm">
            <div class="flex items-center space-x-2 overflow-hidden">
                <span class="p-1.5 bg-indigo-50 dark:bg-slate-900 text-indigo-600 dark:text-indigo-400 rounded">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </span>
                <div class="overflow-hidden">
                    <h5 class="text-[10px] font-bold text-gray-800 dark:text-slate-200 truncate">Lampiran Balasan</h5>
                    <p class="text-[8px] text-slate-400 font-bold uppercase truncate">{{ basename($reply->attachment_path) }}</p>
                </div>
            </div>
            <a href="{{ asset('storage/' . $reply->attachment_path) }}" target="_blank" download class="inline-flex px-2.5 py-1 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md text-[9px] font-black transition-all shadow-md shrink-0">
                Unduh
            </a>
        </div>
    @endif

    <!-- Footer Row (Reactions & Reply Action & Best Answer Toggle) -->
    <div class="flex flex-wrap items-center justify-between gap-4 pt-3 border-t border-gray-100/50 dark:border-slate-800/50">
        
        <!-- Polymorphic Reactions -->
        <div class="flex items-center space-x-1.5">
            @if(auth()->user()->role !== 'parent')
                <form action="{{ route('forum.react') }}" method="POST" class="inline flex items-center space-x-1">
                    @csrf
                    <input type="hidden" name="reactable_type" value="reply">
                    <input type="hidden" name="reactable_id" value="{{ $reply->id }}">
                    
                    <!-- Like  -->
                    <button type="submit" name="type" value="like" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold transition-all border {{ $reply->reactions->where('user_id', auth()->id())->where('type', 'like')->count() > 0 ? 'bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border-indigo-200/50 dark:border-indigo-900/50' : 'bg-transparent text-gray-400 dark:text-slate-500 border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-950/50' }}">
                        <span></span>
                        <span class="ml-1">{{ $reply->reactions->where('type', 'like')->count() }}</span>
                    </button>

                    <!-- Helpful  -->
                    <button type="submit" name="type" value="helpful" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold transition-all border {{ $reply->reactions->where('user_id', auth()->id())->where('type', 'helpful')->count() > 0 ? 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400 border-amber-200/50 dark:border-amber-900/50' : 'bg-transparent text-gray-400 dark:text-slate-500 border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-950/50' }}">
                        <span></span>
                        <span class="ml-1">{{ $reply->reactions->where('type', 'helpful')->count() }}</span>
                    </button>

                    <!-- Insightful  -->
                    <button type="submit" name="type" value="insightful" class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold transition-all border {{ $reply->reactions->where('user_id', auth()->id())->where('type', 'insightful')->count() > 0 ? 'bg-violet-50 dark:bg-violet-950/30 text-violet-600 dark:text-violet-400 border-violet-200/50 dark:border-violet-900/50' : 'bg-transparent text-gray-400 dark:text-slate-500 border-gray-200 dark:border-slate-800 hover:bg-gray-50 dark:hover:bg-slate-950/50' }}">
                        <span></span>
                        <span class="ml-1">{{ $reply->reactions->where('type', 'insightful')->count() }}</span>
                    </button>
                </form>
            @else
                <!-- Parent read-only view of reactions numbers -->
                <div class="flex items-center space-x-1.5 text-[10px] font-bold text-slate-400">
                    <span class="bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-850 px-2 py-0.5 rounded-full"> {{ $reply->reactions->where('type', 'like')->count() }}</span>
                    <span class="bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-850 px-2 py-0.5 rounded-full"> {{ $reply->reactions->where('type', 'helpful')->count() }}</span>
                    <span class="bg-gray-50 dark:bg-slate-950/50 border border-gray-100 dark:border-slate-850 px-2 py-0.5 rounded-full"> {{ $reply->reactions->where('type', 'insightful')->count() }}</span>
                </div>
            @endif
        </div>

        <div class="flex items-center space-x-2">
            <!-- Best Answer marking (Guru/Admin only, on top level replies only if we want to restrict depth, but controller allows all) -->
            @can('markBestAnswer', $thread)
                <form action="{{ route('forum.replies.best', $reply->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex px-3 py-1.5 border border-emerald-200/55 dark:border-emerald-900/55 text-emerald-600 dark:text-emerald-400 rounded-xl text-[10px] font-black hover:bg-emerald-50 dark:hover:bg-emerald-950/20 transition-all items-center space-x-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                        <span>{{ $isBest ? 'Batal Terbaik' : 'Tandai Terbaik' }}</span>
                    </button>
                </form>
            @endcan

            <!-- Nested quick reply triggers (Only up to depth 1, so depth 2 replies can't be replied to indefinitely to prevent infinite indentation chaos) -->
            @if(auth()->user()->role !== 'parent' && $depth < 2 && (!$thread->is_locked || auth()->user()->isAdmin() || auth()->user()->role === 'guru'))
                <button type="button" data-reply-id="{{ $reply->id }}" data-author-name="{{ $reply->author->name }}" class="trigger-quick-reply inline-flex px-3 py-1.5 border border-slate-200 dark:border-slate-800 hover:bg-slate-100 dark:hover:bg-slate-950/30 text-slate-600 dark:text-slate-400 rounded-xl text-[10px] font-black transition-all items-center space-x-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                    </svg>
                    <span>Balas</span>
                </button>
            @endif
        </div>
    </div>
</div>
