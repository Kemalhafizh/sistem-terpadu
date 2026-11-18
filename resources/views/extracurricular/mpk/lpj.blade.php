<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-3">
            <span class="p-2 bg-emerald-100 text-emerald-700 rounded-xl dark:bg-emerald-950 dark:text-emerald-300"></span>
            Repositori LPJ & Sidang Kesiswaan
        </h2>
    </x-slot>

    <!-- breadcrumbs -->
    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Lembaga MPK', 'url' => route('mpk.index')],
            ['label' => 'LPJ Hub', 'url' => '#', 'active' => true]
        ]" />
    </x-slot>

    <div class="space-y-6 font-sans text-slate-800 dark:text-slate-200">
        
        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
            <a href="{{ route('mpk.index') }}" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                 Audit Keuangan
            </a>
            <a href="{{ route('mpk.lpj.index') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all">
                 Repositori LPJ OSIS
            </a>
            <a href="{{ route('mpk.aspirations.index') }}" class="px-4 py-2 bg-white text-slate-700 hover:bg-slate-50 border border-slate-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
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
            
            <!-- Left Side: Repositori LPJ List (2 Columns Wide on large screens) -->
            <div class="lg:col-span-2 space-y-6" x-data="{ 
                selectedLpj: null,
                openReviewModal(lpj) {
                    this.selectedLpj = lpj;
                }
            }">
                <div class="bg-white border border-slate-100 dark:bg-gray-800 dark:border-gray-700 p-6 rounded-3xl shadow-xl">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mb-1">Daftar Dokumen LPJ OSIS</h3>
                    <p class="text-xs text-slate-400 font-medium mb-6">Berkas laporan kegiatan dan evaluasi organisasi siswa per semester</p>

                    <div class="space-y-4">
                        @forelse($lpjs as $lpj)
                            <div class="bg-slate-50/50 dark:bg-gray-700/20 border border-slate-200/40 p-5 rounded-2xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden group">
                                <div class="space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white">
                                            {{ $lpj->title }}
                                        </h4>
                                        <span class="px-2.5 py-0.5 bg-slate-200/60 dark:bg-gray-700 text-slate-700 dark:text-gray-300 rounded-md text-[9px] font-bold uppercase tracking-wider">
                                            TA {{ $lpj->academicYear->year }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-600 dark:text-slate-400 font-medium leading-relaxed max-w-lg">
                                        {{ $lpj->description ?? 'Tidak ada rincian keterangan.' }}
                                    </p>
                                    <div class="flex flex-wrap gap-x-4 gap-y-1 text-[10px] text-slate-400 font-bold uppercase tracking-wider">
                                        <span>Pengirim: {{ $lpj->submitter->name }}</span>
                                        <span>Tanggal: {{ $lpj->created_at->format('d M Y') }}</span>
                                        @if($lpj->reviewer)
                                            <span class="text-emerald-600">Reviewer: {{ $lpj->reviewer->name }}</span>
                                        @endif
                                    </div>

                                    @if($lpj->reviewer_notes)
                                        <div class="mt-2 p-3 bg-white dark:bg-gray-800 rounded-xl border border-slate-200/50 text-[11px] leading-relaxed text-slate-600 dark:text-slate-300">
                                            <span class="font-extrabold text-slate-700 dark:text-white block uppercase text-[9px] tracking-wider mb-1">Nota Sidang MPK:</span>
                                            "{{ $lpj->reviewer_notes }}"
                                        </div>
                                    @endif
                                </div>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto flex-shrink-0">
                                    <!-- Status Badge -->
                                    <div class="text-center md:text-right">
                                        @if($lpj->status === 'submitted')
                                            <span class="inline-flex px-3 py-1 bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300 rounded-full text-[10px] font-extrabold uppercase tracking-wider border border-amber-200/30">
                                                Menunggu Sidang
                                            </span>
                                        @elseif($lpj->status === 'approved')
                                            <span class="inline-flex px-3 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 rounded-full text-[10px] font-extrabold uppercase tracking-wider border border-emerald-200/30">
                                                Disetujui
                                            </span>
                                        @elseif($lpj->status === 'approved_with_notes')
                                            <span class="inline-flex px-3 py-1 bg-teal-50 text-teal-700 dark:bg-teal-950/40 dark:text-teal-300 rounded-full text-[10px] font-extrabold uppercase tracking-wider border border-teal-200/30">
                                                Disetujui dengan Catatan
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 rounded-full text-[10px] font-extrabold uppercase tracking-wider border border-rose-200/30">
                                                Revisi / Ditolak
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex gap-2 justify-end mt-2 sm:mt-0">
                                        <a href="{{ asset('storage/' . $lpj->file_path) }}" target="_blank" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-slate-800 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider transition-colors flex items-center justify-center gap-1.5" title="Buka Dokumen PDF">
                                             Dokumen
                                        </a>
                                        @if($canReview && $lpj->status === 'submitted')
                                            <button 
                                                @click="openReviewModal({{ json_encode($lpj) }})" 
                                                class="px-3 py-2 bg-emerald-700 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all"
                                            >
                                                 Keputusan
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="py-12 text-center text-slate-500 italic">
                                Belum ada berkas Laporan Pertanggungjawaban (LPJ) yang diajukan.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Alpine.js LPJ Review Modal -->
                <div 
                    x-show="selectedLpj" 
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
                        @click.away="selectedLpj = null"
                    >
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                        
                        <h4 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2 mb-1">
                            Sidang Putusan LPJ
                        </h4>
                        <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mb-6">Persetujuan Hasil Evaluasi Kesiswaan</p>

                        <template x-if="selectedLpj">
                            <form method="POST" :action="'/admin/mpk/lpj/' + selectedLpj.uuid + '/review'" class="space-y-5">
                                @csrf

                                <!-- LPJ Title -->
                                <div class="bg-slate-50 dark:bg-gray-700/30 p-4 rounded-2xl border border-slate-200/40 text-xs">
                                    <div class="flex justify-between mb-1">
                                        <span class="text-slate-400 font-bold">Judul LPJ:</span>
                                        <span class="font-extrabold text-slate-800 dark:text-slate-200" x-text="selectedLpj.title"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-400 font-bold">Tahun Ajaran:</span>
                                        <span class="font-extrabold text-slate-900 dark:text-slate-200" x-text="selectedLpj.academic_year.year"></span>
                                    </div>
                                </div>

                                <!-- Review Decision -->
                                <div class="space-y-2">
                                    <label class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Putusan Sidang</label>
                                    <div class="grid grid-cols-1 gap-2">
                                        <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 dark:bg-gray-800 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-slate-50/50 transition-colors">
                                            <input type="radio" name="status" value="approved" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500/10" required>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Disetujui Sepenuhnya</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 dark:bg-gray-800 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-slate-50/50 transition-colors">
                                            <input type="radio" name="status" value="approved_with_notes" class="w-4 h-4 text-teal-600 focus:ring-teal-500/10" required>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Disetujui Dengan Catatan</span>
                                        </label>
                                        <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 dark:bg-gray-800 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-slate-50/50 transition-colors">
                                            <input type="radio" name="status" value="rejected" class="w-4 h-4 text-rose-600 focus:ring-rose-500/10" required>
                                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Ditolak / Butuh Revisi</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- Reviewer Notes -->
                                <div class="space-y-2">
                                    <label for="reviewer_notes" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Nota/Catatan Evaluasi Sidang</label>
                                    <textarea 
                                        id="reviewer_notes" 
                                        name="reviewer_notes" 
                                        rows="4" 
                                        class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                        placeholder="Masukkan rekomendasi perbaikan, masukan atau nota putusan formal hasil persidangan..."
                                    ></textarea>
                                </div>

                                <!-- Actions -->
                                <div class="flex justify-end gap-3 pt-2">
                                    <button type="button" @click="selectedLpj = null" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-wider transition-colors">
                                        Batal
                                    </button>
                                    <button type="submit" class="px-5 py-2 bg-emerald-700 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-emerald-700/10 transition-colors">
                                        Ketuk Putusan
                                    </button>
                                </div>
                            </form>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Right Side: Submit LPJ Form (1 Column Wide) -->
            <div class="space-y-6">
                <div class="bg-white border border-slate-100 dark:bg-gray-800 dark:border-gray-700 p-6 rounded-3xl shadow-xl">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight mb-1">Ajukan Berkas LPJ</h3>
                    <p class="text-xs text-slate-400 font-medium mb-6">Unggah dokumen laporan resmi untuk ditinjau oleh pimpinan MPK</p>

                    <form method="POST" action="{{ route('mpk.lpj.store') }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf

                        <!-- Academic Year -->
                        <div class="space-y-2">
                            <label for="academic_year_id" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Tahun Ajaran Aktif</label>
                            <select 
                                id="academic_year_id" 
                                name="academic_year_id" 
                                class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                required
                            >
                                @foreach($academicYears as $year)
                                    <option value="{{ $year->id }}" {{ $activeYear && $activeYear->id === $year->id ? 'selected' : '' }}>
                                        {{ $year->year }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Title -->
                        <div class="space-y-2">
                            <label for="title" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Judul Laporan</label>
                            <input 
                                id="title" 
                                type="text" 
                                name="title" 
                                class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                placeholder="Contoh: LPJ Proker Class Meeting Semester Ganjil"
                                required 
                            />
                        </div>

                        <!-- Description -->
                        <div class="space-y-2">
                            <label for="description" class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Rincian Laporan (Deskripsi)</label>
                            <textarea 
                                id="description" 
                                name="description" 
                                rows="3" 
                                class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                placeholder="Masukkan ringkasan kegiatan, evaluasi singkat, atau lampiran daftar Sekbid..."
                            ></textarea>
                        </div>

                        <!-- PDF Upload Area -->
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black text-emerald-900 uppercase tracking-widest">Dokumen LPJ Resmi (Format PDF)</label>
                            <input 
                                id="file" 
                                type="file" 
                                name="file" 
                                accept="application/pdf"
                                class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                required 
                            />
                            <p class="text-[9px] text-slate-400 font-bold uppercase tracking-widest">Format file wajib PDF, ukuran maksimal berkas 10MB.</p>
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 py-4.5 rounded-[1.25rem] text-xs font-black text-white uppercase tracking-[0.2em] shadow-lg shadow-emerald-500/10 hover:shadow-emerald-500/25 hover:scale-[1.015] transition-all transform cursor-pointer">
                                Kirim Berkas LPJ
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
