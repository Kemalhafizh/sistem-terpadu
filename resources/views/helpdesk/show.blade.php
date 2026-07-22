<x-app-layout>
    <x-slot name="title">Detail Tiket #{{ $ticket->ticket_number }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tight flex items-center gap-2">
                        Tiket #{{ $ticket->ticket_number }}
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Dibuat pada {{ $ticket->created_at->format('d F Y H:i') }} oleh <span class="font-extrabold text-slate-700 dark:text-slate-355">{{ $ticket->reporter?->name }}</span></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('helpdesk.tickets.index') }}"
                   class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-850 transition-all duration-200">
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl text-xs text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Left Column: Chat/Timeline Messenger Thread -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Ticket Subject & Main Issue Card -->
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white leading-tight">
                            {{ $ticket->title }}
                        </h3>
                        @php
                            $catColors = match ($ticket->category) {
                                'it' => 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border-cyan-500/20',
                                'sarpras' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                'akademik' => 'bg-violet-500/10 text-violet-600 dark:text-violet-400 border-violet-500/20',
                                'keuangan' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                'umum' => 'bg-slate-500/10 text-slate-600 dark:text-slate-400 border-slate-500/20',
                                default => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                            };
                            $catLabel = match ($ticket->category) {
                                'it' => 'IT Support',
                                'sarpras' => 'Sarpras',
                                'akademik' => 'Akademik',
                                'keuangan' => 'Keuangan',
                                'umum' => 'Umum',
                                default => $ticket->category,
                            };
                        @endphp
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-black uppercase tracking-wider border {{ $catColors }} shrink-0">
                            {{ $catLabel }}
                        </span>
                    </div>

                    <div class="text-xs text-gray-700 dark:text-slate-300 leading-relaxed whitespace-pre-line bg-slate-50/50 dark:bg-slate-950/40 border border-slate-100 dark:border-slate-800/60 p-4 rounded-2xl">
                        {{ $ticket->description }}
                    </div>

                    @if($ticket->attachment_path)
                        <div class="mt-4 pt-4 border-t border-gray-100 dark:border-slate-800/80">
                            <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-2">Lampiran Berkas Laporan</span>
                            <a href="{{ Storage::url($ticket->attachment_path) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                                <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Lihat / Unduh Berkas
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Chat Timeline Thread Wrapper -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest pl-2">Alur Diskusi & Respon Tiket</h4>

                    <div class="space-y-4">
                        
                        <!-- Auto-generated System Event: Ticket Created -->
                        <div class="flex items-center justify-center my-6">
                            <div class="px-4 py-1.5 bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 rounded-full text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                🆕 TIKET DIBUAT DENGAN PRIORITAS {{ strtoupper($ticket->priority) }}
                            </div>
                        </div>

                        @forelse($comments as $comment)
                            @php
                                $isReporter = $comment->user_id === $ticket->reporter_id;
                                $isInternalNote = $comment->is_internal;
                                
                                // Color Scheme
                                if ($isInternalNote) {
                                    $bubbleBg = 'bg-amber-500/5 dark:bg-amber-500/10 border-amber-300/40 dark:border-amber-500/30 text-slate-800 dark:text-slate-250';
                                    $labelColor = 'text-amber-600 dark:text-amber-400';
                                    $alignment = 'justify-start';
                                } elseif ($isReporter) {
                                    $bubbleBg = 'bg-blue-500/5 dark:bg-blue-500/10 border-blue-200/50 dark:border-blue-500/20 text-slate-800 dark:text-slate-200';
                                    $labelColor = 'text-blue-600 dark:text-blue-400';
                                    $alignment = 'justify-start';
                                } else {
                                    $bubbleBg = 'bg-slate-50/70 dark:bg-slate-900/60 border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-200';
                                    $labelColor = 'text-indigo-600 dark:text-indigo-400';
                                    $alignment = 'justify-start';
                                }
                            @endphp

                            <div class="flex {{ $alignment }} group animate-fade-in">
                                <div class="max-w-[85%] sm:max-w-[80%] rounded-2xl p-4 border shadow-xs relative {{ $bubbleBg }}">
                                    <!-- Header Info -->
                                    <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                        <span class="text-xs font-black {{ $labelColor }}">
                                            @if($isInternalNote)
                                                 Catatan Internal (Hanya Staf)
                                            @else
                                                {{ $comment->user?->name }}
                                            @endif
                                        </span>
                                        <span class="text-[9px] bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 font-extrabold uppercase px-1.5 py-0.5 rounded-md">
                                            {{ $comment->user?->role }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 dark:text-slate-500 font-semibold ml-auto">
                                            {{ $comment->created_at->format('d M Y H:i') }}
                                        </span>
                                    </div>

                                    <!-- Comment Body -->
                                    <p class="text-xs leading-relaxed whitespace-pre-line">
                                        {{ $comment->body }}
                                    </p>

                                    <!-- Attachment -->
                                    @if($comment->attachment_path)
                                        <div class="mt-3 pt-3 border-t border-slate-200/40 dark:border-slate-800/40 flex items-center justify-between gap-4">
                                            <a href="{{ Storage::url($comment->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                </svg>
                                                Unduh Lampiran ({{ basename($comment->attachment_path) }})
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="py-10 text-center text-slate-400 dark:text-slate-500 italic">
                                Belum ada tanggapan untuk tiket keluhan ini. Silakan kirim pesan atau respon pertama Anda di bawah.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Chat Reply Form Area -->
                @if(auth()->user()->can('comment', $ticket))
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm">
                        <form action="{{ route('helpdesk.tickets.comment', $ticket->uuid) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            
                            <div class="space-y-2">
                                <label for="body" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Ketik Tanggapan / Jawaban</label>
                                <textarea name="body" id="body" rows="4" required placeholder="Tuliskan respon, jawaban pemecahan masalah, atau update perkembangan di sini..."
                                          class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-850 dark:text-white px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 resize-none"></textarea>
                            </div>

                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                                <!-- Upload Mini Attachment -->
                                <div class="flex items-center gap-2">
                                    <label for="comment_attachment" class="cursor-pointer inline-flex items-center gap-1.5 px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-xl text-[10px] font-extrabold uppercase tracking-wider transition-colors border border-slate-200 dark:border-slate-700">
                                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                        </svg>
                                        Unggah Foto/PDF
                                    </label>
                                    <input type="file" name="attachment" id="comment_attachment" class="hidden" accept=".jpg,.jpeg,.png,.pdf,.docx,.zip">
                                    <span id="comment_file_name" class="text-[10px] text-slate-400 font-semibold italic max-w-xs truncate"></span>
                                    
                                    <script>
                                        document.getElementById('comment_attachment').addEventListener('change', function(e) {
                                            const file = e.target.files[0];
                                            document.getElementById('comment_file_name').innerText = file ? file.name : '';
                                        });
                                    </script>
                                </div>

                                <!-- Comment Control Controls (Internal checkbox & Submit) -->
                                <div class="flex items-center gap-3 ml-auto">
                                    @if(auth()->user()->can('viewInternalComments', $ticket))
                                        <label class="inline-flex items-center gap-2 cursor-pointer select-none text-[10px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 bg-amber-500/5 px-3 py-2 border border-amber-300/30 rounded-xl hover:bg-amber-500/10 transition-colors">
                                            <input type="checkbox" name="is_internal" value="1" class="rounded border-amber-300 text-amber-500 focus:ring-amber-500/35 bg-white dark:bg-slate-900 w-3.5 h-3.5">
                                             Catatan Staf
                                        </label>
                                    @endif

                                    <button type="submit" class="px-5 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md hover:-translate-y-0.5 transition-all">
                                        Kirim Respon
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @endif

            </div>

            <!-- Right Column: Ticket Properties, SLA & Quick Action Panels -->
            <div class="space-y-6">
                
                <!-- SLA Countdown / Status Indicator Widget -->
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm space-y-4">
                    <div>
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest block mb-1">Status Laporan</span>
                        @php
                            $statColors = match ($ticket->status) {
                                'open' => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                'in_progress' => 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border-purple-500/20',
                                'waiting' => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                'resolved' => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                'closed' => 'bg-slate-500/10 text-slate-500 border-slate-500/20',
                                default => 'bg-slate-500/10 text-slate-600 border-slate-500/20',
                            };
                        @endphp
                        <span class="px-3 py-1.5 rounded-xl text-xs font-black uppercase tracking-wider border {{ $statColors }} inline-block">
                            {{ strtoupper($ticket->status) }}
                        </span>
                    </div>

                    <div class="pt-4 border-t border-gray-150 dark:border-slate-800/80">
                        <span class="text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest block mb-1">Batas Waktu Resolusi (SLA)</span>
                        @if(in_array($ticket->status, ['resolved', 'closed']))
                            <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                SLA Terpenuhi (Terselesaikan)
                            </div>
                        @elseif($ticket->isOverdue())
                            <div class="flex flex-col gap-1">
                                <span class="px-2 py-1 bg-red-500/15 border border-red-500/35 rounded-lg text-[9px] font-black uppercase tracking-widest text-red-500 inline-block text-center animate-pulse">SLA BREACHED (Overdue)</span>
                                <span class="text-[10px] font-extrabold text-red-400 text-center mt-1">Melewati Batas: {{ $ticket->due_date?->format('d M Y H:i') }}</span>
                            </div>
                        @else
                            <div class="space-y-1">
                                <div class="text-xs font-bold text-slate-700 dark:text-slate-300">
                                    Batas SLA: <span class="font-extrabold text-slate-900 dark:text-white">{{ $ticket->due_date?->format('d M Y H:i') }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold italic">
                                    Sisa Waktu: {{ $ticket->due_date?->diffForHumans(null, true) }} lagi
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Ticket Info Details Card -->
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm space-y-4">
                    <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider mb-2">Informasi Tiket</h4>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between py-1 border-b border-gray-50 dark:border-slate-800/60">
                            <span class="text-slate-400 font-bold">Kategori</span>
                            <span class="font-black text-slate-850 dark:text-slate-200 capitalize">{{ $ticket->category }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50 dark:border-slate-800/60">
                            <span class="text-slate-400 font-bold">Prioritas</span>
                            @php
                                $priColors = match ($ticket->priority) {
                                    'low' => 'text-blue-500',
                                    'medium' => 'text-yellow-500',
                                    'high' => 'text-orange-500',
                                    'critical' => 'text-red-500 font-black animate-pulse',
                                    default => 'text-slate-500',
                                };
                            @endphp
                            <span class="font-black capitalize {{ $priColors }}">{{ $ticket->priority }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50 dark:border-slate-800/60">
                            <span class="text-slate-400 font-bold">Reporter</span>
                            <span class="font-black text-slate-850 dark:text-slate-200">{{ $ticket->reporter?->name }}</span>
                        </div>
                        <div class="flex justify-between py-1 border-b border-gray-50 dark:border-slate-800/60">
                            <span class="text-slate-400 font-bold">Role Reporter</span>
                            <span class="font-bold text-slate-500 capitalize">{{ $ticket->reporter?->role }}</span>
                        </div>
                        <div class="flex justify-between py-1">
                            <span class="text-slate-400 font-bold">Petugas Teknis</span>
                            <span class="font-black text-slate-850 dark:text-slate-200">{{ $ticket->assignee?->name ?? 'Belum Ditugaskan' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Assignment Panel (For Staff Only) -->
                @if(auth()->user()->can('assign', $ticket))
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm space-y-4">
                        <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Penugasan Petugas
                        </h4>

                        <form action="{{ route('helpdesk.tickets.assign', $ticket->uuid) }}" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <select name="assignee_id" required class="w-full text-xs font-semibold rounded-xl border-gray-250 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-850 dark:text-white py-2.5 focus:border-indigo-500 transition-all">
                                    <option value="">-- Pilih Petugas / Teknisi --</option>
                                    @foreach($staffList as $staff)
                                        <option value="{{ $staff->id }}" {{ $ticket->assignee_id === $staff->id ? 'selected' : '' }}>
                                            {{ $staff->name }} ({{ strtoupper($staff->role) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all">
                                Tugaskan Petugas
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Resolution Detail (Visible to all if resolved) -->
                @if($ticket->status === 'resolved' || $ticket->resolution)
                    <div class="bg-emerald-500/5 dark:bg-emerald-500/10 border border-emerald-500/20 rounded-3xl p-5 shadow-sm space-y-3">
                        <h4 class="text-xs font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Solusi / Resolusi Masalah
                        </h4>
                        <div class="text-xs text-emerald-800 dark:text-emerald-300 leading-relaxed bg-emerald-500/5 dark:bg-emerald-950/20 p-4 rounded-2xl border border-emerald-500/15">
                            {{ $ticket->resolution ?? 'Kendala telah diselesaikan oleh petugas.' }}
                        </div>
                        @if($ticket->resolved_at)
                            <span class="block text-[9px] text-emerald-600 dark:text-emerald-500 font-bold uppercase tracking-wider text-right">Diselesaikan pada {{ $ticket->resolved_at->format('d M Y H:i') }}</span>
                        @endif
                    </div>
                @endif

                <!-- Technical Staff Control Action Box (Close, Resolve) -->
                @if(in_array($ticket->status, ['open', 'in_progress', 'waiting']))
                    @if(auth()->user()->can('resolve', $ticket) || auth()->user()->can('close', $ticket))
                        <div x-data="{ openResolve: false }" class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl border border-white/20 dark:border-slate-800/80 rounded-3xl p-5 shadow-sm space-y-3">
                            <h4 class="text-xs font-black text-slate-800 dark:text-white uppercase tracking-wider mb-2">Tindakan Admin & Teknisi</h4>

                            <!-- Resolve ticket toggle -->
                            @if(auth()->user()->can('resolve', $ticket))
                                <div class="space-y-3">
                                    <button @click="openResolve = !openResolve" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all text-center block">
                                        Tandai Selesai / Kirim Solusi
                                    </button>

                                    <!-- Expandable Resolution input box -->
                                    <div x-show="openResolve" x-collapse x-cloak class="pt-3 border-t border-slate-100 dark:border-slate-800/80 space-y-3">
                                        <form action="{{ route('helpdesk.tickets.resolve', $ticket->uuid) }}" method="POST" class="space-y-3">
                                            @csrf
                                            <div>
                                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-wider mb-1.5">Catatan Resolusi Laporan</label>
                                                <textarea name="resolution" rows="3" required placeholder="Tuliskan detail perbaikan yang telah dilakukan dan solusi penyelesaian masalah..."
                                                          class="w-full text-xs font-semibold rounded-xl border-gray-250 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-850 dark:text-white px-3 py-2.5 focus:border-emerald-500 focus:ring-emerald-500/20 transition-all duration-200 resize-none"></textarea>
                                            </div>
                                            <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-black text-[9px] uppercase tracking-widest transition-all">
                                                Simpan & Selesaikan Tiket
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif

                            <!-- Close ticket toggle -->
                            @if(auth()->user()->can('close', $ticket))
                                <form action="{{ route('helpdesk.tickets.close', $ticket->uuid) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menutup tiket pengaduan ini secara permanen?')">
                                    @csrf
                                    <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-[10px] uppercase tracking-widest transition-all text-center block">
                                        Tutup Tiket Permanen
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                @endif

            </div>
            
        </div>

    </div>
</x-app-layout>
