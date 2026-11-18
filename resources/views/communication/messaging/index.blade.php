<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                    </span>
                    Internal Messaging & Threaded Discussion System
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Platform komunikasi pesan berulir internal antara Guru, Wali Murid, dan Pengelola Sekolah.</p>
            </div>
            <a href="{{ route('messages.compose') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tulis Pesan Baru
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12 min-h-[600px]">

                <!-- Conversations List Sidebar (4 Cols) -->
                <div class="lg:col-span-4 border-r border-gray-100 dark:border-gray-700 flex flex-col">
                    <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Daftar Percakapan</h3>
                    </div>

                    <div class="overflow-y-auto divide-y divide-gray-100 dark:divide-gray-700 flex-1">
                        @forelse($conversations as $conv)
                            <a href="{{ route('messages.index', ['conversation_id' => $conv->id]) }}" class="p-4 block transition-colors hover:bg-gray-50 dark:hover:bg-gray-900/60 {{ optional($activeConversation)->id === $conv->id ? 'bg-indigo-50/60 dark:bg-indigo-950/40 border-l-4 border-indigo-600' : '' }}">
                                <div class="flex items-center justify-between gap-2 mb-1">
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white truncate">
                                        {{ $conv->subject ?? 'Percakapan Tanpa Subjek' }}
                                    </h4>
                                    <span class="text-[10px] font-mono text-gray-400 shrink-0">
                                        {{ optional($conv->latestMessage)->created_at ? optional($conv->latestMessage)->created_at->format('H:i') : '' }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500 dark:text-gray-400 truncate max-w-[200px]">
                                        {{ optional($conv->latestMessage)->body ?? 'Belum ada pesan' }}
                                    </span>
                                    @if($conv->unread_count > 0)
                                        <span class="px-2 py-0.5 bg-indigo-600 text-white font-mono text-[10px] font-bold rounded-full">
                                            {{ $conv->unread_count }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        @empty
                            <div class="p-8 text-center text-gray-400 italic text-xs">
                                Belum ada percakapan. Klik "Tulis Pesan Baru" untuk memulai.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Active Message Thread View (8 Cols) -->
                <div class="lg:col-span-8 flex flex-col">
                    @if($activeConversation)
                        <!-- Thread Header -->
                        <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white">{{ $activeConversation->subject }}</h3>
                                <p class="text-[11px] text-gray-400">
                                    Anggota: {{ $activeConversation->participants->pluck('user.name')->filter()->join(', ') }}
                                </p>
                            </div>
                            <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400 font-mono text-[10px] font-bold uppercase rounded">
                                {{ strtoupper($activeConversation->type) }}
                            </span>
                        </div>

                        <!-- Messages Thread Scroll Area -->
                        <div class="p-6 flex-1 overflow-y-auto space-y-4 max-h-[450px]">
                            @forelse($messages as $msg)
                                @php $isMe = $msg->sender_id === auth()->id(); @endphp
                                <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] font-bold text-gray-500">{{ optional($msg->sender)->name ?? 'Sistem' }}</span>
                                        <span class="text-[10px] font-mono text-gray-400">{{ $msg->created_at->format('d M H:i') }}</span>
                                    </div>
                                    <div class="max-w-md p-3.5 rounded-2xl text-xs leading-relaxed {{ $isMe ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-100 rounded-tl-none' }}">
                                        {{ $msg->body }}
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center text-gray-400 italic text-xs">Belum ada pesan dalam ulir percakapan ini.</div>
                            @endforelse
                        </div>

                        <!-- Reply Input Form -->
                        <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                            <form action="{{ route('messages.reply', $activeConversation->id) }}" method="POST" class="flex gap-3">
                                @csrf
                                <input type="text" name="body" required placeholder="Tulis balasan pesan..." class="flex-1 bg-white dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-xs px-4 py-3 focus:ring-2 focus:ring-indigo-500">
                                <button type="submit" class="px-5 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-xl text-xs transition-all shadow-md flex items-center gap-2">
                                    <span>Kirim</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="flex-1 flex items-center justify-center p-12 text-center text-gray-400 italic text-xs">
                            Pilih percakapan dari panel kiri untuk menampilkan riwayat ulir pesan.
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
