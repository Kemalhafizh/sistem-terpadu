<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('elearning.class.show', $elearningClass->uuid) }}" class="text-gray-400 hover:text-emerald-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                Detail Materi & Diskusi
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">

            <!-- Back Link -->
            <div>
                <a href="{{ route('elearning.class.show', $elearningClass->uuid) }}" class="inline-flex items-center space-x-1.5 text-xs font-bold text-slate-500 hover:text-emerald-600 dark:text-slate-400 dark:hover:text-emerald-400 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span>Kembali ke Aliran Kelas</span>
                </a>
            </div>

            <!-- Material Core Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-slate-700 space-y-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg sm:text-xl font-extrabold text-gray-900 dark:text-white leading-tight">
                                {{ $material->title }}
                            </h3>
                            <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mt-1">
                                DITERBITKAN {{ $material->created_at->format('d M Y, H:i') }} ({{ $material->created_at->diffForHumans() }})
                            </p>
                        </div>
                    </div>
                </div>

                @if($material->description)
                    <div class="bg-gray-50/50 dark:bg-slate-900/50 p-5 rounded-2xl border border-gray-100/50 dark:border-slate-800">
                        <p class="text-xs sm:text-sm text-gray-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $material->description }}</p>
                    </div>
                @endif

                <!-- Attachment Details & Video -->
                @if($material->file_path || $material->video_url)
                    <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-slate-700">
                        <h4 class="text-xs font-black text-gray-400 dark:text-slate-500 uppercase tracking-widest">Media Pembelajaran</h4>
                        <div class="flex flex-col sm:flex-row gap-3">
                            @if($material->file_path)
                                <a href="{{ asset($material->file_path) }}" download class="flex-1 p-4 bg-emerald-500/5 hover:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 rounded-2xl text-xs font-bold border border-emerald-500/20 transition-all flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        <span>Unduh PDF Lampiran Materi</span>
                                    </div>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                </a>
                            @endif

                            @if($material->video_url)
                                <a href="{{ $material->video_url }}" target="_blank" class="flex-1 p-4 bg-red-500/5 hover:bg-red-500/10 text-red-700 dark:text-red-400 rounded-2xl text-xs font-bold border border-red-500/20 transition-all flex items-center justify-between">
                                    <div class="flex items-center space-x-2">
                                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Tonton Video Youtube</span>
                                    </div>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Discussion Forum Comments section -->
            <div class="space-y-4">
                <h4 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                    <span>Forum Diskusi Kelas ({{ $comments->count() }})</span>
                </h4>

                <!-- Comments Feed -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-gray-100 dark:border-slate-700 shadow-sm space-y-6">
                    @if($comments->isEmpty())
                        <div class="text-center py-6 text-gray-500 space-y-2">
                            <p class="text-xs font-bold">Belum ada diskusi untuk materi ini.</p>
                            <p class="text-[10px] text-gray-400">Tanyakan kesulitan Anda atau beri masukan mengenai topik ini di bawah.</p>
                        </div>
                    @else
                        <div class="space-y-6 divide-y divide-gray-50 dark:divide-slate-700/50">
                            @foreach($comments as $idx => $comment)
                                <div class="flex items-start gap-3 {{ $idx > 0 ? 'pt-6' : '' }}">
                                    @php
                                        $initial = substr($comment->user?->name ?? 'U', 0, 1);
                                        $isStaff = in_array($comment->user?->role, ['guru', 'staf_tu', 'admin', 'superadmin']);
                                        $avatarColor = $isStaff ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-slate-100 text-slate-700 dark:bg-slate-900 dark:text-slate-400';
                                    @endphp
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs shrink-0 {{ $avatarColor }} uppercase">
                                        {{ $initial }}
                                    </div>
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <div class="flex justify-between items-center gap-2">
                                            <div class="flex items-baseline gap-1.5 flex-wrap">
                                                <span class="text-xs font-black text-gray-900 dark:text-white">{{ $comment->user?->name }}</span>
                                                <span class="px-1.5 py-0.2 bg-slate-50 dark:bg-slate-950 text-slate-500 rounded text-[8px] font-bold uppercase border border-slate-200/50 dark:border-slate-800">
                                                    {{ $comment->user?->role === 'student' ? 'Siswa' : 'Pengajar' }}
                                                </span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[9px] font-bold text-gray-400">{{ $comment->created_at->diffForHumans() }}</span>
                                                
                                                @php
                                                    $isTeacher = in_array(auth()->user()->role, ['guru', 'staf_tu', 'admin', 'superadmin']) && auth()->id() === $elearningClass->teacher_id;
                                                    $isAdmin = in_array(auth()->user()->role, ['admin', 'superadmin']);
                                                @endphp
                                                @if(auth()->id() === $comment->user_id || $isTeacher || $isAdmin)
                                                    <form action="{{ route('elearning.comment.destroy', [$material->uuid, $comment->uuid]) }}" method="POST" onsubmit="return confirm('Hapus komentar ini?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $comment->content }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Submit Comment Form -->
                    <div class="pt-6 border-t border-gray-50 dark:border-slate-700/50">
                        <form action="{{ route('elearning.comment.store', $material->uuid) }}" method="POST" class="flex items-end gap-3">
                            @csrf
                            <div class="flex-1 space-y-1">
                                <label class="text-[10px] font-bold text-gray-400 dark:text-slate-400 uppercase tracking-widest">Kirim Pertanyaan / Komentar</label>
                                <textarea name="content" required rows="2" placeholder="Tuliskan komentar atau pertanyaan diskusi Anda di sini..." class="w-full rounded-2xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3 focus:border-emerald-500"></textarea>
                            </div>
                            <button type="submit" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black shrink-0 shadow-md transition-all flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                                <span>Kirim</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
