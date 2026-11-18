<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-3">
            <span class="p-2 bg-emerald-100 text-emerald-700 rounded-xl dark:bg-emerald-950 dark:text-emerald-300"></span>
            Kotak Aspirasi & Suara Siswa
        </h2>
    </x-slot>

    <!-- breadcrumbs -->
    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Lembaga MPK', 'url' => route('mpk.index')],
            ['label' => 'Aspirasi', 'url' => '#', 'active' => true]
        ]" />
    </x-slot>

    <div class="space-y-6 font-sans text-slate-800 dark:text-slate-200">
        
        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
            <a href="{{ route('mpk.index') }}" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                 Audit Keuangan
            </a>
            <a href="{{ route('mpk.lpj.index') }}" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                 Repositori LPJ OSIS
            </a>
            <a href="{{ route('mpk.aspirations.index') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all">
                 Kotak Suara Aspirasi
            </a>
        </div>

        <!-- Session Status -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center gap-3">
                <span></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Left Side: Aspirations Feed (2 Columns Wide) -->
            <div class="lg:col-span-2 space-y-6" x-data="{ 
                selectedAsp: null,
                openResolveModal(asp) {
                    this.selectedAsp = asp;
                }
            }">
                <div class="bg-white border border-slate-100 dark:bg-gray-800 dark:border-gray-700 p-6 rounded-3xl shadow-xl">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mb-1">Feed Suara Aspirasi Siswa</h3>
                    <p class="text-xs text-slate-400 font-medium mb-6">Umpan suara masuk dari siswa mengenai iklim akademis dan sarana prasarana sekolah</p>

                    <div class="space-y-4">
                        @forelse($aspirations as $asp)
                            <div class="bg-slate-50/50 dark:bg-gray-700/20 border border-slate-200/40 p-5 rounded-2xl flex flex-col justify-between gap-4 relative overflow-hidden group">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white">
                                            {{ $asp->title }}
                                        </h4>
                                        <span class="px-2.5 py-0.5 bg-slate-200/60 dark:bg-gray-700 text-slate-700 dark:text-gray-300 rounded-md text-[9px] font-bold uppercase tracking-wider">
                                            Kategori: {{ strtoupper($asp->category) }}
                                        </span>
                                        @if($asp->is_anonymous)
                                            <span class="px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-300 rounded-md text-[9px] font-bold uppercase tracking-wider">
                                                 Anonim
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 font-medium leading-relaxed">
                                        {{ $asp->body }}
                                    </p>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                        <span>Pengirim: {{ $asp->is_anonymous ? 'Siswa Rahasia' : ($asp->student?->name ?? 'Siswa Umum') }}</span>
                                        <span>Tanggal: {{ $asp->created_at->format('d M Y') }}</span>
                                        @if($asp->resolver)
                                            <span class="text-emerald-600">Diselesaikan oleh: {{ $asp->resolver->name }}</span>
                                        @endif
                                    </div>

                                    @if($asp->resolution_notes)
                                        <div class="mt-3 p-3.5 bg-white dark:bg-gray-800 rounded-xl border border-slate-200/50 text-[11px] leading-relaxed text-slate-700 dark:text-slate-300">
                                            <span class="font-extrabold text-emerald-700 dark:text-emerald-400 block uppercase text-[9px] tracking-wider mb-1">Tanggapan/Solusi MPK & Sekolah:</span>
                                            "{{ $asp->resolution_notes }}"
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 border-t border-slate-200/40 pt-3 mt-1">
                                    <!-- Status Badge -->
                                    <div>
                                        @if($asp->status === 'pending')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 rounded-full text-[10px] font-extrabold uppercase tracking-wider border border-amber-200/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                Pending
                                            </span>
                                        @elseif($asp->status === 'reviewed')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-300 rounded-full text-[10px] font-extrabold uppercase tracking-wider border border-blue-200/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-600"></span>
                                                Ditinjau MPK
                                            </span>
                                        @elseif($asp->status === 'delegated')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 rounded-full text-[10px] font-extrabold uppercase tracking-wider border border-teal-200/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-teal-500 animate-bounce"></span>
                                                Didelegasikan ke OSIS
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 rounded-full text-[10px] font-extrabold uppercase tracking-wider border border-emerald-200/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                Selesai / Terjawab
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex gap-2 justify-end">
                                        @if($canResolve)
                                            <button 
                                                @click="openResolveModal({{ json_encode($asp) }})" 
                                                class="px-3.5 py-1.5 bg-emerald-700 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all"
                                            >
                                                 Jawab & Solusi
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-slate-500 italic">
                                Belum ada aspirasi kesiswaan yang dikirimkan.
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $aspirations->links() }}
                    </div>
                </div>

                <!-- Alpine.js Aspiration Resolve Modal -->
                <div 
                    x-show="selectedAsp" 
                    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    style="display: none;"
                >
                    <div 
                        class="bg-white dark:bg-gray-800 border border-slate-200 dark:border-gray-700 w-full max-w-md rounded-3xl shadow-2xl p-6 relative overflow-hidden"
                        @click.away="selectedAsp = null"
                    >
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                        
                        <h4 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2 mb-1">
                            Tanggapan Resmi MPK
                        </h4>
                        <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mb-6">Pemberian Solusi & Tindak Lanjut Aspirasi</p>

                        <template x-if="selectedAsp">
                            <form method="POST" :action="'/admin/mpk/aspirations/' + selectedAsp.uuid + '/resolve'" class="space-y-5">
                                @csrf

                                <!-- Aspiration Details -->
                                <div class="bg-slate-50 dark:bg-gray-700/30 p-4 rounded-2xl border border-slate-200/40 text-xs">
                                    <div class="flex flex-col mb-1">
                                        <span class="text-slate-400 font-bold">Judul Aspirasi:</span>
                                        <span class="font-extrabold text-slate-800 dark:text-slate-200" x-text="selectedAsp.title"></span>
                                    </div>
                                    <div class="flex flex-col mt-2">
                                        <span class="text-slate-400 font-bold">Isi Suara:</span>
                                        <span class="text-slate-700 dark:text-slate-300" x-text="selectedAsp.body"></span>
                                    </div>
                                </div>

                                <!-- Aspiration Action Status -->
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Update Status Tindakan</label>
                                    <div class="grid grid-cols-1 gap-2">
                                        <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 dark:bg-gray-800 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-slate-50/50 transition-colors">
                                            <input type="radio" name="status" value="reviewed" class="w-4 h-4 text-blue-600 focus:ring-blue-500/10" required>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Ditinjau MPK & Sekolah</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 dark:bg-gray-800 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-slate-50/50 transition-colors">
                                            <input type="radio" name="status" value="delegated" class="w-4 h-4 text-teal-600 focus:ring-teal-500/10" required>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Didelegasikan ke Pengurus OSIS</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 dark:bg-gray-800 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-slate-50/50 transition-colors">
                                            <input type="radio" name="status" value="resolved" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500/10" required>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Selesai / Terjawab Tuntas</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Resolution Notes -->
                                <div class="space-y-2">
                                    <label for="resolution_notes" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Tanggapan/Solusi Resmi Sekolah</label>
                                    <textarea 
                                        id="resolution_notes" 
                                        name="resolution_notes" 
                                        rows="4" 
                                        class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                        placeholder="Tuliskan solusi penyelesaian, hasil dengar pendapat, atau disposisi Waka Kesiswaan..."
                                        required
                                    ></textarea>
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="selectedAsp = null" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-wider transition-colors">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-emerald-700/10 transition-colors">
                                        Kirim Tanggapan
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Right Side: Submit Aspiration Form (Only visible/applicable to active students / users) -->
            <div class="space-y-6">
                <div class="bg-white border border-slate-100 dark:bg-gray-800 dark:border-gray-700 p-6 rounded-3xl shadow-xl">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mb-1">Kirim Aspirasi Baru</h3>
                    <p class="text-xs text-slate-400 font-medium mb-6">Sampaikan usul, saran, atau keluhan konstruktif Anda untuk masa depan sekolah</p>

                    <form method="POST" action="{{ route('mpk.aspirations.store') }}" class="space-y-4">
                        @csrf

                        <!-- Category -->
                        <div class="space-y-2">
                            <label for="category" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Kategori Aspirasi</label>
                            <select 
                                id="category" 
                                name="category" 
                                class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                required
                            >
                                <option value="akademik">Akademik & KBM</option>
                                <option value="sarpras">Fasilitas / Sarana Prasarana</option>
                                <option value="it">Sistem Digital / IT</option>
                                <option value="keuangan">Keuangan & SPP</option>
                                <option value="kegiatan">Kegiatan Siswa & OSIS</option>
                                <option value="umum">Umum / Lainnya</option>
                            </select>
                        </div>

                        <!-- Title -->
                        <div class="space-y-2">
                            <label for="title" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Subjek / Topik</label>
                            <input 
                                id="title" 
                                type="text" 
                                name="title" 
                                class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                placeholder="Contoh: Perbaikan AC Kelas XI-A"
                                required 
                            />
                        </div>

                        <!-- Body (Aspiration) -->
                        <div class="space-y-2">
                            <label for="body" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Isi Suara Aspirasi Anda</label>
                            <textarea 
                                id="body" 
                                name="body" 
                                rows="5" 
                                class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                placeholder="Tuliskan keluhan atau saran Anda secara rinci, sopan, dan membangun..."
                                required
                            ></textarea>
                        </div>

                        <!-- Anonymous Flag -->
                        <div class="flex items-center">
                            <label for="is_anonymous" class="inline-flex items-center cursor-pointer group">
                                <input 
                                    id="is_anonymous" 
                                    type="checkbox" 
                                    class="w-5 h-5 rounded-lg border-slate-300 bg-white text-emerald-600 focus:ring-emerald-500/10 cursor-pointer transition-all" 
                                    name="is_anonymous"
                                    value="1"
                                >
                                <span class="ms-3 text-[10px] font-black text-slate-500 uppercase tracking-widest group-hover:text-slate-700 transition-colors">
                                    Kirim Secara Rahasia (Anonim)
                                </span>
                            </label>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 py-4.5 rounded-[1.25rem] text-xs font-black text-white uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/10 hover:shadow-emerald-500/25 hover:scale-[1.015] transition-all transform cursor-pointer">
                                Kirim Aspirasi Siswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
