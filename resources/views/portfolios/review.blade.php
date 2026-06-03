<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center space-x-2">
                    <a href="{{ route('portfolios.index') }}" class="text-xs font-black uppercase tracking-wider text-indigo-500 hover:text-indigo-600 transition flex items-center space-x-1">
                        <span>&larr; Kembali ke Direktori</span>
                    </a>
                </div>
                <h2 class="font-black text-2xl text-gray-900 dark:text-gray-100 leading-tight tracking-tight flex items-center space-x-2 mt-1">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 rounded-xl">🔍</span>
                    <span>Ulas e-Portfolio Siswa</span>
                </h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-medium">
                    Evaluasi klaim sertifikat prestasi {{ $student->name }} dan anugerahkan lencana kehormatan sekolah
                </p>
            </div>
            
            <div class="flex items-center space-x-2">
                <span class="px-4 py-2 rounded-2xl text-xs font-black uppercase tracking-widest bg-indigo-100 text-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
                    Kelas: {{ $student->classroom->name ?? 'Belum Diatur' }}
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        
        {{-- Flash Alerts --}}
        @if(session('success'))
            <div class="p-4 bg-emerald-50/80 dark:bg-emerald-950/30 backdrop-blur-sm border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-300 rounded-2xl flex items-center space-x-3">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            {{-- Column 1 & 2: List of Achievements to Verify --}}
            <div class="lg:col-span-2 space-y-4">
                <span class="text-[9px] font-black text-indigo-500 uppercase tracking-widest block">Ulasan Berkas & Dokumen Sertifikat</span>
                
                @forelse($student->portfolioItems as $item)
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-6 transition duration-300 hover:shadow-lg shadow-sm flex flex-col justify-between gap-4 border-l-4 
                         {{ $item->is_verified ? 'border-l-emerald-500 shadow-emerald-500/5' : 'border-l-amber-500 shadow-amber-500/5' }}">
                        
                        <div class="space-y-2">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50 text-[8px] font-black uppercase tracking-wider">
                                    {{ $item->category }}
                                </span>
                                <span class="text-[9px] text-gray-400 font-bold">
                                    Diajukan: {{ $item->created_at->format('d-m-Y') }}
                                </span>
                                @if($item->is_verified)
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300">
                                        ✓ Terverifikasi oleh {{ $item->verifier->name ?? 'Staf' }}
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[8px] font-black uppercase tracking-wider bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300 animate-pulse">
                                        ⏳ Menunggu Tindakan Anda
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

                            <div class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1.5 border-t border-gray-100 dark:border-slate-800">
                                <div>Tanggal Perolehan: {{ $item->date ? $item->date->format('d-m-Y') : '-' }}</div>
                                @if($item->external_link)
                                    <div>Tautan Luar: <a href="{{ $item->external_link }}" target="_blank" class="text-indigo-500 hover:underline">Lihat Karya Siswa ↗</a></div>
                                @endif
                            </div>
                        </div>

                        {{-- Action buttons for verification --}}
                        <div class="flex items-center justify-between pt-2 border-t border-gray-100 dark:border-slate-800">
                            <div>
                                @if($item->file_path)
                                    <a href="{{ Storage::url($item->file_path) }}" target="_blank"
                                       class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition active:scale-95 flex items-center space-x-1">
                                        <span>📄 Buka Sertifikat</span>
                                    </a>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-gray-500 font-medium italic">Tidak ada berkas bukti diunggah</span>
                                @endif
                            </div>

                            <form action="{{ route('portfolios.verify', $item) }}" method="POST">
                                @csrf
                                @if($item->is_verified)
                                    <input type="hidden" name="status" value="unverified">
                                    <button type="submit"
                                            class="px-4 py-2 bg-rose-100 hover:bg-rose-200 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 rounded-xl text-[10px] font-black uppercase tracking-wider transition active:scale-95">
                                        ❌ Batalkan Verifikasi
                                    </button>
                                @else
                                    <input type="hidden" name="status" value="verified">
                                    <button type="submit"
                                            class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition active:scale-95 shadow-md shadow-emerald-500/10">
                                        ✓ Setujui & Verifikasi
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl p-16 text-center shadow-sm">
                        <div class="flex flex-col items-center">
                            <span class="text-3xl mb-4">📭</span>
                            <h3 class="text-base font-black text-gray-800 dark:text-gray-200">Tidak ada pengajuan berkas</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400 max-w-xs leading-relaxed mt-1">
                                Siswa belum pernah mengunggah draf pencapaian portofolio apapun untuk diulas saat ini.
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Column 3: Award Badge Form & Active Badges --}}
            <div class="space-y-6">
                {{-- Form to award badge --}}
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-indigo-100 dark:border-indigo-950/50 rounded-3xl p-6 shadow-xl relative overflow-hidden">
                    <span class="text-[9px] font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-widest block mb-1">Apresiasi Kehormatan</span>
                    <h4 class="text-sm font-black text-gray-900 dark:text-gray-100 mb-4">Anugerahkan Lencana Sekolah</h4>

                    <form action="{{ route('portfolios.award-badge', $student) }}" method="POST" class="space-y-4">
                        @csrf
                        
                        {{-- Select Badge --}}
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Pilih Lencana Penghargaan *</label>
                            <select name="badge_id" required
                                    class="w-full px-3 py-2.5 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:border-indigo-500 transition-all">
                                <option value="">-- Pilih Lencana --</option>
                                @foreach($allBadges as $badge)
                                    <option value="{{ $badge->id }}">{{ $badge->icon }} {{ $badge->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Award Notes --}}
                        <div class="space-y-1.5">
                            <label class="block text-[10px] font-black uppercase tracking-widest text-gray-500 dark:text-gray-400">Alasan / Catatan Penghargaan *</label>
                            <textarea name="notes" required rows="3" placeholder="Contoh: Meraih Juara 1 Olimpiade Fisika tingkat Kota tahun 2026..."
                                      class="w-full px-3 py-2 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs focus:outline-none focus:border-indigo-500 transition-all font-medium leading-relaxed"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition shadow-lg shadow-indigo-500/10 active:scale-95">
                            🏆 Anugerahkan Lencana
                        </button>
                    </form>
                </div>

                {{-- List of already earned badges --}}
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-3xl p-6 shadow-sm">
                    <span class="text-[9px] font-black text-amber-500 uppercase tracking-widest block mb-4">Lencana Aktif Siswa</span>
                    
                    <div class="space-y-4">
                        @forelse($student->badges as $badge)
                            <div class="p-3.5 bg-gray-50 dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-800 flex items-start space-x-3"
                                 style="border-left: 4px solid {{ $badge->color }};">
                                <span class="text-2xl shrink-0 pt-0.5">{{ $badge->icon }}</span>
                                <div class="space-y-1">
                                    <span class="block text-xs font-black text-gray-900 dark:text-gray-100 leading-tight">{{ $badge->name }}</span>
                                    <span class="block text-[8px] font-bold text-gray-400 dark:text-gray-500 uppercase">Dianugerahi: {{ \Carbon\Carbon::parse($badge->pivot->earned_at)->format('d-m-Y') }}</span>
                                    @if($badge->pivot->notes)
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-relaxed font-semibold italic bg-white/50 dark:bg-slate-900/50 p-2 rounded-xl mt-1.5 border border-gray-100/50 dark:border-slate-850">
                                            "{{ $badge->pivot->notes }}"
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 text-center py-4">
                                Siswa belum memiliki lencana penghargaan saat ini.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
