<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('admin.chatbot.conversations') }}" class="text-sm font-bold text-gray-500 hover:text-indigo-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Riwayat
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    {{ __('Detail Percakapan Sesi') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Transkrip Percakapan dan Feedback Pengguna</p>
            </div>
            
            @if($conversation->intent !== 'escalated')
                <form action="{{ route('admin.chatbot.conversations.escalate', $conversation->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-rose-950/10 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Eskalasi ke Tiket Bantuan
                    </button>
                </form>
            @else
                <span class="px-4 py-2 bg-rose-100 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400 rounded-xl text-xs font-black uppercase tracking-wider border border-rose-200 dark:border-rose-900/50">
                    Sudah Dieskalasikan
                </span>
            @endif
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Session Meta -->
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex justify-between items-center">
                <div>
                    <h4 class="font-bold text-gray-800 dark:text-white">Pengguna: {{ $conversation->user->name }}</h4>
                    <p class="text-xs text-gray-500 mt-1">Sesi dimulai pada {{ $conversation->created_at->format('d M Y H:i:s') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-black uppercase text-gray-400 tracking-widest">Intent Terdeteksi</p>
                    <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 rounded-lg text-xs font-black text-slate-700 dark:text-slate-300 uppercase inline-block mt-1">
                        {{ $conversation->intent }}
                    </span>
                </div>
            </div>

            <!-- Transcript chat bubbles -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 space-y-6">
                <!-- User Message -->
                <div class="flex flex-col items-end">
                    <div class="bg-indigo-600 text-white rounded-2xl rounded-tr-none px-4 py-3 max-w-xl text-sm shadow-sm font-semibold">
                        {{ $conversation->user_message }}
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 font-bold">{{ $conversation->user->name }}</span>
                </div>

                <!-- Bot Response -->
                <div class="flex flex-col items-start">
                    <div class="bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-2xl rounded-tl-none px-4 py-3 max-w-xl text-sm shadow-sm leading-relaxed">
                        {!! nl2br(e($conversation->bot_response)) !!}
                    </div>
                    <span class="text-[10px] text-gray-400 mt-1 font-bold">AI Chatbot</span>
                </div>
            </div>

            <!-- Feedback Card -->
            @if(null !== $conversation->was_helpful)
                <div class="bg-{{ $conversation->was_helpful ? 'emerald' : 'rose' }}-50 dark:bg-{{ $conversation->was_helpful ? 'emerald' : 'rose' }}-950/20 rounded-[2rem] p-6 shadow-sm border border-{{ $conversation->was_helpful ? 'emerald' : 'rose' }}-100 dark:border-{{ $conversation->was_helpful ? 'emerald' : 'rose' }}-900/50 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white dark:bg-gray-800 text-{{ $conversation->was_helpful ? 'emerald' : 'rose' }}-500 flex items-center justify-center shadow-sm">
                            @if($conversation->was_helpful)
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                            @else
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l-3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path></svg>
                            @endif
                        </div>
                        <div>
                            <h4 class="font-black text-{{ $conversation->was_helpful ? 'emerald' : 'rose' }}-800 dark:text-{{ $conversation->was_helpful ? 'emerald' : 'rose' }}-400">Feedback Pengguna</h4>
                            <p class="text-xs text-gray-500 mt-0.5">Pengguna menandai percakapan ini sebagai: <strong>{{ $conversation->was_helpful ? 'Membantu/Bermanfaat' : 'Tidak Membantu' }}</strong></p>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
