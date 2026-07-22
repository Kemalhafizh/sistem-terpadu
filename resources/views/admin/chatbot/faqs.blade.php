<x-app-layout>
    <x-slot name="header">
        <h2 class="font-outfit font-black text-3xl text-gray-900 dark:text-white leading-tight">
             Kelola FAQ Chatbot Sekolah
        </h2>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Konfigurasi bank data tanya-jawab lokal asisten pintar SISTA.</p>
    </x-slot>

    <div class="py-6 space-y-6 max-w-7xl mx-auto" x-data="{ showCreateModal: false, showEditModal: false, activeFaq: null }">
        <!-- Dashboard Top Banner -->
        <div class="p-6 bg-gradient-to-r from-blue-900/40 via-indigo-900/40 to-purple-900/40 rounded-3xl border border-white/10 shadow-xl backdrop-blur-md flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-600 flex items-center justify-center text-2xl shadow-lg shadow-indigo-500/20"></div>
                <div>
                    <h3 class="text-lg font-bold text-white">Tambahkan FAQ Lokal</h3>
                    <p class="text-xs text-indigo-200 mt-0.5">Semua data tanya-jawab disimpan secara lokal tanpa biaya token eksternal.</p>
                </div>
            </div>
            <button @click="showCreateModal = true" class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-500/20 hover:shadow-indigo-500/30 transition duration-300 active:scale-95 focus:outline-none">
                + Tambah FAQ Baru
            </button>
        </div>

        <!-- FAQ Table/Card Grid -->
        <div class="bg-gray-950/80 rounded-3xl border border-white/5 shadow-xl backdrop-blur-md overflow-hidden">
            <div class="p-5 border-b border-white/5 flex items-center justify-between">
                <span class="text-sm font-bold text-white">Bank Tanya-Jawab</span>
                <span class="px-3 py-1 rounded-full bg-white/5 text-[11px] font-mono text-gray-400">{{ $faqs->count() }} FAQ Terdaftar</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-white/5 text-gray-300 border-b border-white/5">
                            <th class="px-6 py-4 font-bold">Kategori</th>
                            <th class="px-6 py-4 font-bold">Pertanyaan</th>
                            <th class="px-6 py-4 font-bold">Jawaban</th>
                            <th class="px-6 py-4 font-bold">Kata Kunci (Keywords)</th>
                            <th class="px-6 py-4 font-bold text-center">Penggunaan</th>
                            <th class="px-6 py-4 font-bold text-center">Status</th>
                            <th class="px-6 py-4 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5 text-gray-300">
                        @forelse($faqs as $faq)
                            <tr class="hover:bg-white/5 transition duration-200">
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider
                                        @if($faq->category === 'akademik') bg-blue-500/10 text-blue-400 border border-blue-500/20
                                        @elseif($faq->category === 'keuangan') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
                                        @elseif($faq->category === 'teknis') bg-amber-500/10 text-amber-400 border border-amber-500/20
                                        @else bg-purple-500/10 text-purple-400 border border-purple-500/20
                                        @endif">
                                        {{ $faq->category }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 font-semibold text-white max-w-[200px] truncate">
                                    {{ $faq->question }}
                                </td>
                                <td class="px-6 py-4 max-w-[250px] truncate text-gray-400">
                                    {{ $faq->answer }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @if(is_array($faq->keywords))
                                            @foreach($faq->keywords as $kw)
                                                <span class="px-1.5 py-0.5 rounded bg-white/5 border border-white/5 font-mono text-[9px] text-pink-400">
                                                    {{ $kw }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="text-gray-600">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-white font-bold">
                                    {{ $faq->usage_count }}x
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold 
                                        {{ $faq->is_active ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20' }}">
                                        {{ $faq->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2.5">
                                        <button @click="activeFaq = {{ json_encode($faq) }}; showEditModal = true" class="text-indigo-400 hover:text-indigo-300 font-bold transition">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.chatbot.faqs.destroy', $faq->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus FAQ ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300 font-bold transition">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                    Belum ada FAQ chatbot yang didaftarkan. Klik tombol di atas untuk membuat!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- CREATE MODAL (AlpineJS) -->
        <div x-show="showCreateModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
            <div @click.away="showCreateModal = false" class="w-full max-w-lg bg-gray-950 border border-white/10 rounded-3xl shadow-2xl overflow-hidden"
                 x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-6 border-b border-white/5 bg-gradient-to-r from-blue-900/20 to-purple-900/20 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white"> Tambah FAQ Baru</h3>
                    <button @click="showCreateModal = false" class="text-gray-400 hover:text-white"></button>
                </div>
                <form action="{{ route('admin.chatbot.faqs.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Pertanyaan</label>
                        <input type="text" name="question" required placeholder="Contoh: Bagaimana cara melihat E-Rapor?"
                               class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Jawaban Asisten</label>
                        <textarea name="answer" required rows="4" placeholder="Tuliskan jawaban yang akan disampaikan asisten cerdas..."
                                  class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Kategori</label>
                            <select name="category" required class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                                <option value="akademik" class="bg-gray-950">Akademik & Nilai</option>
                                <option value="keuangan" class="bg-gray-950">Keuangan & SPP</option>
                                <option value="teknis" class="bg-gray-950">Teknis & Sistem</option>
                                <option value="umum" class="bg-gray-950">Umum & Info</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Keywords (Koma Separator)</label>
                            <input type="text" name="keywords" placeholder="rapor, nilai, raport, belajar"
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    <div class="pt-4 border-t border-white/5 flex items-center justify-end gap-2.5">
                        <button type="button" @click="showCreateModal = false" class="px-4 py-2 rounded-2xl bg-white/5 text-gray-400 hover:text-white text-xs transition">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-500/20 transition">Simpan FAQ</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EDIT MODAL (AlpineJS) -->
        <div x-show="showEditModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4">
            <div @click.away="showEditModal = false" class="w-full max-w-lg bg-gray-950 border border-white/10 rounded-3xl shadow-2xl overflow-hidden"
                 x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                <div class="p-6 border-b border-white/5 bg-gradient-to-r from-blue-900/20 to-purple-900/20 flex items-center justify-between">
                    <h3 class="text-sm font-bold text-white"> Edit FAQ Chatbot</h3>
                    <button @click="showEditModal = false" class="text-gray-400 hover:text-white"></button>
                </div>
                <template x-if="activeFaq">
                    <form :action="'/admin/chatbot/faqs/' + activeFaq.id" method="POST" class="p-6 space-y-4">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Pertanyaan</label>
                            <input type="text" name="question" required x-model="activeFaq.question"
                                   class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Jawaban Asisten</label>
                            <textarea name="answer" required rows="4" x-model="activeFaq.answer"
                                      class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Kategori</label>
                                <select name="category" required x-model="activeFaq.category" class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                                    <option value="akademik" class="bg-gray-950">Akademik & Nilai</option>
                                    <option value="keuangan" class="bg-gray-950">Keuangan & SPP</option>
                                    <option value="teknis" class="bg-gray-950">Teknis & Sistem</option>
                                    <option value="umum" class="bg-gray-950">Umum & Info</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Keywords (Koma Separator)</label>
                                <input type="text" name="keywords" :value="activeFaq.keywords ? activeFaq.keywords.join(', ') : ''"
                                       class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-400 uppercase tracking-wide mb-1.5">Status</label>
                            <select name="is_active" required x-model="activeFaq.is_active" class="w-full bg-white/5 border border-white/10 rounded-2xl px-4 py-2.5 text-xs text-white focus:outline-none focus:border-indigo-500">
                                <option value="1" class="bg-gray-950">Aktif</option>
                                <option value="0" class="bg-gray-950">Nonaktif</option>
                            </select>
                        </div>
                        <div class="pt-4 border-t border-white/5 flex items-center justify-end gap-2.5">
                            <button type="button" @click="showEditModal = false" class="px-4 py-2 rounded-2xl bg-white/5 text-gray-400 hover:text-white text-xs transition">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-500/20 transition">Simpan Perubahan</button>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>
</x-app-layout>
