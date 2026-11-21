<x-app-layout>
    <x-slot name="title">Buat Tiket Baru</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white tracking-tight">
                    {{ __('Buat Tiket Pengaduan') }}
                </h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    Laporkan kendala fasilitas, masalah IT, keuangan, atau bantuan administrasi internal.
                </p>
            </div>
            <a href="{{ route('helpdesk.tickets.index') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-slate-700 dark:text-slate-300 bg-white/70 dark:bg-slate-900/70 backdrop-blur-sm border border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800 transition-all duration-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Kembali ke Daftar
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto">
            
            @if($errors->any())
                <div class="mb-6 p-4 bg-red-500/10 border border-red-500/20 rounded-2xl text-xs text-red-600 dark:text-red-400 font-semibold">
                    <ul class="list-disc pl-4 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="rounded-3xl border border-white/20 dark:border-slate-800/80 bg-white/70 dark:bg-slate-900/70 backdrop-blur-xl shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800/80 bg-slate-50/50 dark:bg-slate-950/40">
                    <h3 class="text-sm font-black text-slate-800 dark:text-white flex items-center gap-2 uppercase tracking-wider">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Formulir Pengaduan Digital
                    </h3>
                </div>

                <form action="{{ route('helpdesk.tickets.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                    @csrf

                    <!-- Interactive Category Selector Grid -->
                    <div x-data="{ category: '{{ old('category', 'it') }}' }" class="space-y-3">
                        <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Kategori Pengaduan <span class="text-rose-500">*</span></label>
                        <input type="hidden" name="category" :value="category">
                        
                        <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                            <!-- IT SUPPORT -->
                            <div @click="category = 'it'" 
                                 :class="category === 'it' ? 'border-blue-500 bg-blue-500/5 dark:bg-blue-500/10 ring-2 ring-blue-500/30' : 'border-gray-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/30'"
                                 class="p-4 rounded-2xl border text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-2 group">
                                <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200">IT Support</span>
                            </div>

                            <!-- SARPRAS -->
                            <div @click="category = 'sarpras'" 
                                 :class="category === 'sarpras' ? 'border-amber-500 bg-amber-500/5 dark:bg-amber-500/10 ring-2 ring-amber-500/30' : 'border-gray-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/30'"
                                 class="p-4 rounded-2xl border text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-2 group">
                                <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200">Fasilitas/Sarpras</span>
                            </div>

                            <!-- AKADEMIK -->
                            <div @click="category = 'akademik'" 
                                 :class="category === 'akademik' ? 'border-violet-500 bg-violet-500/5 dark:bg-violet-500/10 ring-2 ring-violet-500/30' : 'border-gray-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/30'"
                                 class="p-4 rounded-2xl border text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-2 group">
                                <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200">Akademik</span>
                            </div>

                            <!-- KEUANGAN -->
                            <div @click="category = 'keuangan'" 
                                 :class="category === 'keuangan' ? 'border-emerald-500 bg-emerald-500/5 dark:bg-emerald-500/10 ring-2 ring-emerald-500/30' : 'border-gray-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/30'"
                                 class="p-4 rounded-2xl border text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-2 group">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200">Keuangan</span>
                            </div>

                            <!-- UMUM -->
                            <div @click="category = 'umum'" 
                                 :class="category === 'umum' ? 'border-slate-600 bg-slate-500/5 dark:bg-slate-500/10 ring-2 ring-slate-500/30' : 'border-gray-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/30'"
                                 class="p-4 rounded-2xl border text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-2 group">
                                <div class="w-10 h-10 rounded-xl bg-slate-500/10 text-slate-500 flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.257 3.099c.765-.136 1.522-.12 2.248.077 1.077.29 2.059.883 2.905 1.73a11.963 11.963 0 012.235 3.12 11.963 11.963 0 012.235-3.12 11.963 11.963 0 012.905-1.73c.726-.197 1.483-.213 2.248-.077M12 21V5m0 16a9.963 9.963 0 01-6-2.008M12 21a9.963 9.963 0 006-2.008"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200">Umum</span>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Priority Selector Grid -->
                    <div x-data="{ priority: '{{ old('priority', 'low') }}' }" class="space-y-3">
                        <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Tingkat Prioritas / SLA <span class="text-rose-500">*</span></label>
                        <input type="hidden" name="priority" :value="priority">

                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <!-- LOW -->
                            <div @click="priority = 'low'" 
                                 :class="priority === 'low' ? 'border-blue-500 bg-blue-500/5 dark:bg-blue-500/10 ring-2 ring-blue-500/30' : 'border-gray-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/30'"
                                 class="p-4 rounded-2xl border text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-1 group">
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Low</span>
                                <span class="text-[10px] text-slate-400 font-semibold mt-1">Resolusi Maks. 72 Jam</span>
                            </div>

                            <!-- MEDIUM -->
                            <div @click="priority = 'medium'" 
                                 :class="priority === 'medium' ? 'border-yellow-500 bg-yellow-500/5 dark:bg-yellow-500/10 ring-2 ring-yellow-500/30' : 'border-gray-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/30'"
                                 class="p-4 rounded-2xl border text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-1 group">
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Medium</span>
                                <span class="text-[10px] text-slate-400 font-semibold mt-1">Resolusi Maks. 48 Jam</span>
                            </div>

                            <!-- HIGH -->
                            <div @click="priority = 'high'" 
                                 :class="priority === 'high' ? 'border-orange-500 bg-orange-500/5 dark:bg-orange-500/10 ring-2 ring-orange-500/30' : 'border-gray-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/30'"
                                 class="p-4 rounded-2xl border text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-1 group">
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">High</span>
                                <span class="text-[10px] text-slate-400 font-semibold mt-1">Resolusi Maks. 24 Jam</span>
                            </div>

                            <!-- CRITICAL -->
                            <div @click="priority = 'critical'" 
                                 :class="priority === 'critical' ? 'border-red-500 bg-red-500/5 dark:bg-red-500/10 ring-2 ring-red-500/30' : 'border-gray-100 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-950/30'"
                                 class="p-4 rounded-2xl border text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center gap-1 group relative overflow-hidden">
                                <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider text-rose-500">Critical</span>
                                <span class="text-[10px] text-slate-400 font-semibold mt-1">Resolusi Maks. 4 Jam</span>
                            </div>
                        </div>
                    </div>

                    <!-- Subject Input -->
                    <div class="space-y-1.5">
                        <label for="title" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Subjek Masalah / Judul Laporan <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required placeholder="Contoh: AC Kelas XI-A Tidak Dingin / Printer TU Macet"
                               class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-900 dark:text-white px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200">
                    </div>

                    <!-- Description Textarea -->
                    <div class="space-y-1.5">
                        <label for="description" class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Deskripsi Detail Masalah <span class="text-rose-500">*</span></label>
                        <textarea name="description" id="description" rows="6" required placeholder="Jelaskan secara kronologis dan detail mengenai kendala yang terjadi, lokasi, serta dampaknya..."
                                  class="w-full text-xs font-semibold rounded-xl border-gray-200 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/50 text-gray-900 dark:text-white px-4 py-3 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all duration-200 resize-none">{{ old('description') }}</textarea>
                    </div>

                    <!-- Upload Attachment Zone -->
                    <div x-data="{ fileName: '' }" class="space-y-2">
                        <label class="block text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-widest">Lampirkan Foto / Berkas Bukti (Opsional)</label>
                        
                        <div class="relative">
                            <label for="attachment" class="flex flex-col items-center justify-center border-2 border-dashed border-slate-200 dark:border-slate-800 hover:border-indigo-500 dark:hover:border-indigo-500 rounded-3xl p-8 cursor-pointer hover:bg-slate-50/50 dark:hover:bg-slate-950/20 transition-all group">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center group-hover:scale-110 transition-transform mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300" x-text="fileName ? 'Berkas dipilih: ' + fileName : 'Klik atau seret berkas ke sini untuk mengunggah'"></span>
                                <span class="text-[10px] text-slate-400 mt-1.5">Mendukung berkas JPG, PNG, PDF, DOCX, ZIP (Maksimal 5MB)</span>
                                
                                <input type="file" name="attachment" id="attachment" class="hidden" accept=".jpg,.jpeg,.png,.pdf,.docx,.zip" @change="fileName = $event.target.files[0] ? $event.target.files[0].name : ''">
                            </label>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-slate-800/80">
                        <a href="{{ route('helpdesk.tickets.index') }}"
                           class="px-5 py-2.5 rounded-xl text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-colors">
                            Batal
                        </a>
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-xs font-black uppercase tracking-wider bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white shadow-md shadow-blue-500/25 hover:-translate-y-0.5 transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Kirim Tiket Pengaduan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
