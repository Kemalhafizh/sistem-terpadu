<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-slate-800 dark:bg-slate-700 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    </div>
                    {{ __('Konsol AI Chatbot Admin') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Kelola Pengetahuan, Riwayat Percakapan, dan Analitik Chatbot</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.chatbot.faqs') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                    Kelola FAQ
                </a>
                <a href="{{ route('admin.chatbot.train-suggestions') }}" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                    Latih Chatbot
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Stats/KPI Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Chats -->
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center hover:shadow-md transition-all duration-300">
                    <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-950/40 rounded-2xl flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-xs text-gray-400 font-black uppercase tracking-widest">Total Percakapan</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $analytics['total_chats'] }}</h3>
                    </div>
                </div>

                <!-- Helpfulness Rate -->
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center hover:shadow-md transition-all duration-300">
                    <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-xs text-gray-400 font-black uppercase tracking-widest">Tingkat Kepuasan</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $analytics['helpfulness_rate'] }}%</h3>
                    </div>
                </div>

                <!-- Active FAQs -->
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center hover:shadow-md transition-all duration-300">
                    <div class="w-14 h-14 bg-amber-50 dark:bg-amber-950/40 rounded-2xl flex items-center justify-center text-amber-600 dark:text-amber-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-xs text-gray-400 font-black uppercase tracking-widest">Total Knowledge FAQ</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $faqsCount }} Item</h3>
                    </div>
                </div>

                <!-- Unhelpful Chats -->
                <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700 flex items-center hover:shadow-md transition-all duration-300">
                    <div class="w-14 h-14 bg-rose-50 dark:bg-rose-950/40 rounded-2xl flex items-center justify-center text-rose-600 dark:text-rose-400">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.905 0-.714.211-1.412.608-2.006L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path></svg>
                    </div>
                    <div class="ml-5">
                        <p class="text-xs text-gray-400 font-black uppercase tracking-widest">Feedback Negatif</p>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white mt-1">{{ $analytics['unhelpful_chats'] }}</h3>
                    </div>
                </div>
            </div>

            <!-- Main Layout Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Sesi Chats (2/3) -->
                <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h4 class="text-lg font-black text-gray-900 dark:text-white">Log Percakapan Terbaru</h4>
                            <p class="text-xs text-gray-400 font-bold">Interaksi terakhir pengguna dengan AI Chatbot</p>
                        </div>
                        <a href="{{ route('admin.chatbot.conversations') }}" class="text-xs font-black uppercase tracking-wider text-indigo-600 hover:text-indigo-700">
                            Lihat Semua
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        @foreach($recentChats as $chat)
                            <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-700/50 border border-gray-100 dark:border-gray-700 flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white">{{ $chat->user->name }} ({{ ucfirst($chat->user->role) }})</p>
                                    <p class="text-xs text-gray-500 mt-1 italic">"{{ Str::limit($chat->user_message, 60) }}"</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if(null !== $chat->was_helpful)
                                        <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg {{ $chat->was_helpful ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                            {{ $chat->was_helpful ? 'Bermanfaat' : 'Tidak Membantu' }}
                                        </span>
                                    @endif
                                    <a href="{{ route('admin.chatbot.conversations.show', $chat->id) }}" class="p-1 text-slate-400 hover:text-indigo-600">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- CSV FAQ Import/Export (1/3) -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-between">
                    <div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Impor & Ekspor FAQ</h4>
                        <p class="text-xs text-gray-400 font-bold mb-6">Manajemen pengetahuan chatbot via berkas CSV</p>
                        
                        <form action="{{ route('admin.chatbot.faqs.import') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-black text-gray-500 uppercase tracking-wider mb-2">Pilih File CSV</label>
                                <input type="file" name="csv_file" required class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-black file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                            </div>
                            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                                Unggah & Impor FAQ
                            </button>
                        </form>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('admin.chatbot.faqs.export') }}" class="w-full py-2.5 bg-gray-100 hover:bg-gray-200 text-slate-700 dark:bg-slate-700 dark:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Unduh Cadangan FAQ (.csv)
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
