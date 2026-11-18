<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </span>
                    Tulis Pesan Internal Baru
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Mulai percakapan langsung atau buat grup diskusi baru.</p>
            </div>
            <a href="{{ route('messages.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Pesan
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6 max-w-4xl mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200 dark:border-gray-700/80 shadow-sm p-6 space-y-6">

                <form action="{{ route('messages.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tipe Percakapan</label>
                        <div class="flex items-center gap-4 text-xs font-bold">
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="type" value="direct" checked class="text-indigo-600 focus:ring-indigo-500">
                                Direct Message (Personal)
                            </label>
                            <label class="inline-flex items-center gap-2">
                                <input type="radio" name="type" value="group" class="text-indigo-600 focus:ring-indigo-500">
                                Group Discussion
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">ID Penerima (Direct User ID)</label>
                        <input type="number" name="recipient_id" placeholder="Masukkan User ID Penerima (misal: 2)" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Subjek / Topik Diskusi</label>
                        <input type="text" name="subject" placeholder="Konsultasi Akademik Siswa / Koordinasi Panitia" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Isi Pesan Pertama</label>
                        <textarea name="body" rows="6" required placeholder="Tuliskan isi pesan Anda secara jelas dan sopan..." class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm p-4 focus:ring-2 focus:ring-indigo-500"></textarea>
                    </div>

                    <div class="flex justify-end pt-4 border-t border-gray-100 dark:border-gray-700">
                        <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-indigo-600/30 transition-all">
                            Kirim & Buat Percakapan
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
