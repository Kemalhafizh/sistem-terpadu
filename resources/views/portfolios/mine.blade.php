<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 rounded-xl"></span>
                    <span>{{ __('e-Portfolio Saya') }}</span>
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Dokumentasikan prestasi, publikasikan sertifikat pendukung, serta pamerkan lencana kehormatan sekolah Anda secara global
                </p>
            </div>
            
            <div class="flex items-center space-x-2" 
                 x-data="{ 
                    shareUrl: '{{ route('portfolios.public', $student->uuid) }}',
                    copied: false,
                    copyLink() {
                        navigator.clipboard.writeText(this.shareUrl);
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2000);
                    }
                 }">
                <button @click="copyLink"
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 dark:bg-slate-100 dark:hover:bg-white text-white dark:text-slate-900 rounded-xl text-xs font-black uppercase tracking-wider transition active:scale-95 flex items-center space-x-1.5 shadow-md border border-slate-800 dark:border-slate-200">
                    <span x-text="copied ? ' Tersalin!' : ' Tautan Publik'"></span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8"
         x-data="{ 
            showCreateModal: false,
            showEditModal: false,
            editItem: { id: '', title: '', description: '', category: 'other', date: '', external_link: '' }
         }">
         
        {{-- Success Flash Alert --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50/80 dark:bg-emerald-950/30 backdrop-blur-sm border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3 animate-fade-in">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Top Summary Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            {{-- Profile Card --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex items-center space-x-4 shadow-sm md:col-span-2">
                <div class="w-14 h-14 bg-indigo-500/10 text-indigo-500 rounded-2xl flex items-center justify-center text-2xl font-black">
                    
                </div>
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-gray-100 leading-tight">
                        {{ $student->name }}
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-semibold">
                        NISN: {{ $student->nisn }} • Kelas: {{ $student->classroom->name ?? 'Belum Diatur' }}
                    </p>
                </div>
            </div>

            {{-- Stat 1: Total items --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Total Berkas</span>
                    <span class="text-3xl font-black text-gray-900 dark:text-gray-100 mt-1 block">{{ $student->portfolioItems->count() }}</span>
                </div>
                <span class="p-3 bg-blue-500/10 text-blue-500 rounded-2xl text-xl"></span>
            </div>

            {{-- Stat 2: Total badges --}}
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest block">Lencana Emas</span>
                    <span class="text-3xl font-black text-amber-600 mt-1 block">{{ $student->badges->count() }}</span>
                </div>
                <span class="p-3 bg-amber-500/10 text-amber-500 rounded-2xl text-xl"></span>
            </div>
        </div>

        {{-- Glow Shelf Badges Showcase --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm">
            <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest block mb-4">Rak Lencana Penghargaan Kehormatan</span>
            
            <div class="flex flex-wrap gap-4">
                @forelse($student->badges as $badge)
                    <div class="flex items-center space-x-3 p-3 bg-gray-50 dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-800 hover:shadow transition duration-300 relative group"
                         style="border-left: 4px solid {{ $badge->color }};">
                        <span class="text-2xl">{{ $badge->icon }}</span>
                        <div>
                            <span class="block text-xs font-black text-gray-800 dark:text-gray-200">{{ $badge->name }}</span>
                            <span class="block text-[8px] font-bold text-gray-400 dark:text-gray-500 uppercase mt-0.5">
                                Didapat: {{ \Carbon\Carbon::parse($badge->pivot->earned_at)->format('d-m-Y') }}
                            </span>
                        </div>

                        {{-- Tooltip info --}}
                        <div class="absolute bottom-14 left-1/2 transform -translate-x-1/2 bg-slate-900/95 dark:bg-slate-950/95 border border-slate-700/50 backdrop-blur-md rounded-xl p-3 text-white text-[10px] shadow-2xl pointer-events-none opacity-0 group-hover:opacity-100 transition-all duration-300 w-48 z-20 leading-normal">
                            <span class="block font-black text-[8px] uppercase tracking-widest text-amber-400 mb-1">Catatan Staf BK / Guru:</span>
                            <span class="italic font-medium">"{{ $badge->pivot->notes ?? 'Pencapaian luar biasa akademik/non-akademik.' }}"</span>
                        </div>
                    </div>
                @empty
                    <div class="p-4 bg-gray-50 dark:bg-slate-800 border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl text-center w-full">
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">
                            Belum ada lencana kehormatan yang dianugerahkan. Terus tunjukkan prestasi terbaikmu!
                        </p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Achievements & Documents Container --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest">Daftar Item & Klaim Prestasi Anda</span>
                <button @click="showCreateModal = true"
                        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition active:scale-95 shadow-md shadow-indigo-500/10 flex items-center space-x-1">
                    <span> Tambah Item Baru</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @forelse($student->portfolioItems as $item)
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 transition duration-300 hover:shadow-lg shadow-sm flex flex-col justify-between gap-4 border-l-4 
                         {{ $item->is_verified ? 'border-l-emerald-500 shadow-emerald-500/5' : 'border-l-amber-500 shadow-amber-500/5' }}">
                        
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                {{-- Category Badge --}}
                                <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50">
                                    {{ $item->category }}
                                </span>

                                {{-- Verification Status --}}
                                @if($item->is_verified)
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                         Terverifikasi
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300">
                                        ⏳ Menunggu Verifikasi
                                    </span>
                                @endif

                                {{-- Public Visibility Toggle Status --}}
                                @if($item->is_public)
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300">
                                         Publik
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md text-[8px] font-black uppercase tracking-wider bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                         Privat
                                    </span>
                                @endif
                            </div>

                            <h3 class="text-base font-black text-gray-900 dark:text-gray-100">
                                {{ $item->title }}
                            </h3>

                            @if($item->description)
                                <p class="text-xs text-gray-600 dark:text-gray-400 leading-relaxed font-medium">
                                    {{ $item->description }}
                                </p>
                            @endif

                            <div class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold grid grid-cols-2 gap-2 pt-1.5 border-t border-gray-100 dark:border-slate-800">
                                <div>Tanggal: {{ $item->date ? $item->date->format('d-m-Y') : '-' }}</div>
                                @if($item->external_link)
                                    <div>Tautan: <a href="{{ $item->external_link }}" target="_blank" class="text-indigo-500 hover:underline">Buka Karya ↗</a></div>
                                @endif
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-slate-800">
                            {{-- Public Visibility Form --}}
                            <form action="{{ route('portfolios.toggle-visibility', $item) }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-gray-700 dark:text-gray-300 rounded-lg text-[9px] font-black uppercase tracking-wider transition">
                                     Set {{ $item->is_public ? 'Privat' : 'Publik' }}
                                </button>
                            </form>

                            <div class="flex items-center space-x-1.5">
                                {{-- Certificate download button if uploaded --}}
                                @if($item->file_path)
                                    <a href="{{ Storage::url($item->file_path) }}" target="_blank"
                                       class="p-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-xs transition"
                                       title="Download Bukti/Sertifikat">
                                        
                                    </a>
                                @endif

                                {{-- Edit/Delete are disabled if verified --}}
                                @if(!$item->is_verified)
                                    <button @click="showEditModal = true; editItem = { id: '{{ $item->id }}', title: '{{ addslashes($item->title) }}', description: '{{ addslashes($item->description) }}', category: '{{ $item->category }}', date: '{{ $item->date ? $item->date->format('Y-m-d') : '' }}', external_link: '{{ $item->external_link }}' }"
                                            class="p-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-600 rounded-lg text-xs transition"
                                            title="Ubah Item">
                                        
                                    </button>
                                    
                                    <form action="{{ route('portfolios.destroy', $item) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus item portofolio ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs transition"
                                                title="Hapus Item">
                                            
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-2 bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl p-16 text-center shadow-sm">
                        <div class="flex flex-col items-center">
                            <div class="w-20 h-20 bg-gray-50 dark:bg-slate-800 rounded-2xl flex items-center justify-center mb-5 border border-gray-100">
                                <span class="text-3xl"></span>
                            </div>
                            <h3 class="text-lg font-black text-gray-800 dark:text-gray-200 mb-2">Belum Ada Portfolio</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm leading-relaxed">
                                Klaim keaslian prestasimu, unggah sertifikat hasil lomba, kepanitiaan, organisasi, atau proyek coding sains kamu sekarang!
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Premium Modal Create --}}
        <div x-show="showCreateModal" x-transition.opacity x-cloak
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            
            <div @click.away="showCreateModal = false"
                 class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-100 dark:border-slate-800 max-w-md w-full p-6 space-y-5 shadow-2xl relative animate-scale-up">
                 
                <div class="space-y-1">
                    <h4 class="text-lg font-black text-gray-900 dark:text-gray-100 flex items-center space-x-2">
                        <span class="p-1.5 bg-indigo-500/10 text-indigo-600 rounded-lg"></span>
                        <span>Tambah Portofolio Baru</span>
                    </h4>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Lengkapi formulir prestasi/karya anda secara valid</p>
                </div>

                <form action="{{ route('portfolios.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    {{-- Title --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Judul Prestasi / Kegiatan *</label>
                        <input type="text" name="title" required placeholder="Contoh: Juara 1 Lomba Coding Nasional"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all font-medium">
                    </div>

                    {{-- Category --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Kategori Kegiatan *</label>
                        <select name="category" required
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
                            <option value="prestasi">Piala / Kejuaraan (Prestasi)</option>
                            <option value="sertifikat">Sertifikat Kelulusan / Kursus</option>
                            <option value="karya">Karya Seni / Sastra</option>
                            <option value="proyek">Proyek Praktik / IT / KIR</option>
                            <option value="organisasi">Pengalaman Organisasi / OSIS</option>
                            <option value="other">Lain-lain</option>
                        </select>
                    </div>

                    {{-- Date & Link --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Tanggal Perolehan</label>
                            <input type="date" name="date"
                                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 transition-all font-medium">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Tautan Portofolio</label>
                            <input type="url" name="external_link" placeholder="https://..."
                                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 transition-all font-medium">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Deskripsi / Refleksi Diri</label>
                        <textarea name="description" rows="3" placeholder="Tuliskan kontribusi atau apa yang kamu pelajari dari kegiatan ini..."
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:border-indigo-500 transition-all font-medium leading-relaxed"></textarea>
                    </div>

                    {{-- File Upload --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Unggah Bukti / Sertifikat (PDF/Gambar, Max 5MB)</label>
                        <input type="file" name="file" accept=".pdf,.png,.jpg,.jpeg"
                               class="w-full text-xs text-gray-400 border border-gray-200 dark:border-slate-700 rounded-xl p-2 bg-gray-50 dark:bg-slate-800">
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="showCreateModal = false"
                                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold transition active:scale-95">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition shadow-lg shadow-indigo-500/15 active:scale-95">
                            Simpan Item
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Premium Modal Edit --}}
        <div x-show="showEditModal" x-transition.opacity x-cloak
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            
            <div @click.away="showEditModal = false"
                 class="bg-white dark:bg-slate-900 rounded-3xl border border-gray-100 dark:border-slate-800 max-w-md w-full p-6 space-y-5 shadow-2xl relative animate-scale-up">
                 
                <div class="space-y-1">
                    <h4 class="text-lg font-black text-gray-900 dark:text-gray-100 flex items-center space-x-2">
                        <span class="p-1.5 bg-indigo-500/10 text-indigo-600 rounded-lg"></span>
                        <span>Ubah Portofolio</span>
                    </h4>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider">Perbarui data prestasi/karya anda secara valid</p>
                </div>

                <form :action="'/portfolios/' + editItem.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    {{-- Title --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Judul Prestasi / Kegiatan *</label>
                        <input type="text" name="title" required x-model="editItem.title"
                               class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 transition-all font-medium">
                    </div>

                    {{-- Category --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Kategori Kegiatan *</label>
                        <select name="category" required x-model="editItem.category"
                                class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 transition-all">
                            <option value="prestasi">Piala / Kejuaraan (Prestasi)</option>
                            <option value="sertifikat">Sertifikat Kelulusan / Kursus</option>
                            <option value="karya">Karya Seni / Sastra</option>
                            <option value="proyek">Proyek Praktik / IT / KIR</option>
                            <option value="organisasi">Pengalaman Organisasi / OSIS</option>
                            <option value="other">Lain-lain</option>
                        </select>
                    </div>

                    {{-- Date & Link --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Tanggal Perolehan</label>
                            <input type="date" name="date" x-model="editItem.date"
                                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 transition-all font-medium">
                        </div>
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Tautan Portofolio</label>
                            <input type="url" name="external_link" x-model="editItem.external_link"
                                   class="w-full px-4 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-sm focus:outline-none focus:border-indigo-500 transition-all font-medium">
                        </div>
                    </div>

                    {{-- Description --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Deskripsi / Refleksi Diri</label>
                        <textarea name="description" rows="3" x-model="editItem.description"
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:border-indigo-500 transition-all font-medium leading-relaxed"></textarea>
                    </div>

                    {{-- File Upload --}}
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Ganti Bukti / Sertifikat (PDF/Gambar, Max 5MB)</label>
                        <input type="file" name="file" accept=".pdf,.png,.jpg,.jpeg"
                               class="w-full text-xs text-gray-400 border border-gray-200 dark:border-slate-700 rounded-xl p-2 bg-gray-50 dark:bg-slate-800">
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end space-x-2 pt-2">
                        <button type="button" @click="showEditModal = false"
                                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-slate-800 text-gray-600 dark:text-gray-300 rounded-xl text-xs font-bold transition active:scale-95">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition shadow-lg shadow-indigo-500/15 active:scale-95">
                            Perbarui Item
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
