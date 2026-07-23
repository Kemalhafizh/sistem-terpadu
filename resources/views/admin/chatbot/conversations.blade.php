<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('admin.chatbot.dashboard') }}" class="text-sm font-bold text-gray-500 hover:text-indigo-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dasbor Chatbot
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    {{ __('Riwayat Percakapan Chatbot') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Audit log interaksi AI Chatbot seluruh pengguna</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="pb-4 text-xs font-black uppercase text-gray-400 tracking-wider">Pengguna</th>
                                <th class="pb-4 text-xs font-black uppercase text-gray-400 tracking-wider">Pesan Masuk</th>
                                <th class="pb-4 text-xs font-black uppercase text-gray-400 tracking-wider">Intent Terdeteksi</th>
                                <th class="pb-4 text-xs font-black uppercase text-gray-400 tracking-wider">Tanggal</th>
                                <th class="pb-4 text-xs font-black uppercase text-gray-400 tracking-wider">Rating</th>
                                <th class="pb-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            @foreach($conversations as $chat)
                                <tr>
                                    <td class="py-4">
                                        <div class="font-bold text-gray-800 dark:text-white text-sm">{{ $chat->user->name }}</div>
                                        <div class="text-[10px] font-black uppercase text-gray-400 mt-0.5">{{ $chat->user->role }}</div>
                                    </td>
                                    <td class="py-4 text-sm text-gray-600 dark:text-gray-300">
                                        {{ Str::limit($chat->user_message, 80) }}
                                    </td>
                                    <td class="py-4 text-xs font-bold text-slate-500">
                                        <span class="px-2 py-0.5 bg-gray-100 dark:bg-gray-700 rounded-lg text-[10px] uppercase font-black">
                                            {{ $chat->intent }}
                                        </span>
                                    </td>
                                    <td class="py-4 text-xs text-gray-500">
                                        {{ $chat->created_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="py-4">
                                        @if(null !== $chat->was_helpful)
                                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-lg {{ $chat->was_helpful ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">
                                                {{ $chat->was_helpful ? 'Bermanfaat' : 'Tidak Membantu' }}
                                            </span>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-right">
                                        <a href="{{ route('admin.chatbot.conversations.show', $chat->id) }}" class="inline-flex px-4 py-2 bg-gray-50 hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all border border-gray-200 dark:border-slate-800">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-8">
                    {{ $conversations->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
