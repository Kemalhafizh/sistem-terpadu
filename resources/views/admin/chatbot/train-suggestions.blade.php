<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('admin.chatbot.dashboard') }}" class="text-sm font-bold text-gray-500 hover:text-indigo-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dasbor Chatbot
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    {{ __('Pelatihan AI Chatbot (Saran Pertanyaan)') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Daftar pertanyaan masuk yang belum dikenali oleh Chatbot</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                
                @if(empty($suggestions))
                    <div class="py-16 flex flex-col items-center justify-center text-center">
                        <div class="w-20 h-20 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-500 rounded-full flex items-center justify-center mb-6">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h4 class="text-lg font-black text-gray-800 dark:text-white mb-1">Semua Pertanyaan Teridentifikasi</h4>
                        <p class="text-xs text-gray-400 max-w-sm">Chatbot berhasil menjawab seluruh pertanyaan masuk dengan baik baru-baru ini.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-700">
                                    <th class="pb-4 text-xs font-black uppercase text-gray-400 tracking-wider">Pertanyaan Pengguna</th>
                                    <th class="pb-4 text-xs font-black uppercase text-gray-400 tracking-wider text-center">Frekuensi Ditanyakan</th>
                                    <th class="pb-4"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-gray-700/50">
                                @foreach($suggestions as $suggestion)
                                    <tr>
                                        <td class="py-4 text-sm font-semibold text-gray-800 dark:text-white">
                                            "{{ $suggestion['user_message'] }}"
                                        </td>
                                        <td class="py-4 text-sm font-bold text-center text-rose-500">
                                            {{ $suggestion['frequency'] }}x
                                        </td>
                                        <td class="py-4 text-right">
                                            <a href="{{ route('admin.chatbot.faqs') }}?prefill_question={{ urlencode($suggestion['user_message']) }}" class="inline-flex px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-sm">
                                                Jadikan FAQ Baru
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>
