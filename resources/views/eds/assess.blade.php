<x-app-layout>
    <x-slot name="title">Evaluasi {{ $standard->code }}: {{ $period->name }}</x-slot>

    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Raport Mutu (EDS)', 'url' => route('eds.index')],
            ['label' => 'Dashboard Periode', 'url' => route('eds.show', $period->id)],
            ['label' => $standard->code, 'url' => '#']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('eds.show', $period->id) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 text-slate-650 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black tracking-wider bg-indigo-100 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 uppercase">{{ $standard->code }}</span>
                    <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight mt-1">
                        {{ $standard->name }}
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $standard->description }}</p>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $isCompleted = $period->status === 'completed';
        $canAssess = auth()->user()->can('assess', $period) && !$isCompleted;
    @endphp

    <div class="space-y-6">
        <!-- Banner Otorisasi / Status -->
        @if($isCompleted)
        <div class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-250 dark:border-emerald-900/50 flex items-center space-x-3" role="alert">
            <span class="text-xl">🔒</span>
            <div>
                <span class="font-bold">Periode Evaluasi Terkunci.</span> Evaluasi Diri Sekolah (EDS) telah diselesaikan secara formal. Seluruh pengisian dan berkas bukti fisik bersifat read-only (tidak dapat dimodifikasi).
            </div>
        </div>
        @elseif(!$canAssess)
        <div class="p-4 text-sm text-blue-800 rounded-2xl bg-blue-50 dark:bg-blue-950/30 dark:text-blue-400 border border-blue-250 dark:border-blue-900/50 flex items-center space-x-3" role="alert">
            <span class="text-xl">👁️</span>
            <div>
                <span class="font-bold">Mode Peninjauan (Read-Only).</span> Anda hanya memiliki hak akses peninjauan untuk memantau data mutu. Pengisian evaluasi dibatasi hanya untuk Waka Kurikulum, Admin, atau Superadmin.
            </div>
        </div>
        @endif

        @if ($errors->any())
        <div class="p-4 text-sm text-red-800 rounded-2xl bg-red-50 dark:bg-red-950/30 dark:text-red-400 border border-red-250 dark:border-red-900/50" role="alert">
            <span class="font-bold">Terdapat kesalahan penginputan:</span>
            <ul class="list-disc pl-5 mt-1 text-xs">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Formulir Penilaian Batch -->
        <form method="POST" action="{{ route('eds.assess.store', $period->id) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf

            <!-- Loop Indicators -->
            @foreach($standard->indicators as $indicator)
                @php
                    $assessment = $assessments[$indicator->id] ?? null;
                    $currentScore = $assessment ? $assessment->score : null;
                    $currentEvidence = $assessment ? $assessment->evidence : '';
                    $currentRecommendation = $assessment ? $assessment->recommendation : '';
                    $currentFiles = $assessment ? ($assessment->document_paths ?? []) : [];
                @endphp
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-250/50 dark:border-gray-700/50 shadow-xl overflow-hidden" 
                     x-data="{ scoreSelected: '{{ $currentScore }}', deleteFiles: [] }">
                    
                    <!-- Indicator Header -->
                    <div class="p-6 pb-4 border-b border-gray-150/40 dark:border-gray-750/40 bg-gray-50/50 dark:bg-gray-900/25 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="space-y-1">
                            <span class="text-xs font-black text-primary-500 dark:text-primary-400 tracking-wider">INDIKATOR {{ $indicator->code }}</span>
                            <h3 class="text-base font-extrabold text-gray-950 dark:text-white leading-snug">
                                {{ $indicator->name }}
                            </h3>
                            <p class="text-xs text-gray-400 leading-normal">{{ $indicator->description }}</p>
                        </div>
                        
                        <!-- Mini score indicator -->
                        <div class="shrink-0 flex items-center space-x-2">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Skor Saat Ini:</span>
                            <template x-if="scoreSelected">
                                <span :class="{
                                    'bg-red-500 text-white': scoreSelected == '1',
                                    'bg-amber-500 text-white': scoreSelected == '2',
                                    'bg-blue-500 text-white': scoreSelected == '3',
                                    'bg-emerald-500 text-white': scoreSelected == '4'
                                }" class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black transition-all" x-text="scoreSelected"></span>
                            </template>
                            <template x-if="!scoreSelected">
                                <span class="px-3 py-1 rounded-xl text-xs font-black bg-gray-100 text-gray-400 dark:bg-gray-900 dark:text-gray-550 border border-gray-200 dark:border-gray-750">BELUM</span>
                            </template>
                        </div>
                    </div>

                    <!-- Indicator Content -->
                    <div class="p-6 space-y-6">
                        <!-- Score Radio Grid -->
                        <div class="space-y-3">
                            <label class="block text-xs font-extrabold text-gray-450 dark:text-gray-450 uppercase tracking-wider">Skor Pemenuhan Mutu (Skala 1 - 4) <span class="text-red-500">*</span></label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <!-- Score 1 -->
                                <label :class="scoreSelected == '1' ? 'border-red-500 bg-red-50/50 dark:bg-red-950/10 dark:border-red-550 ring-2 ring-red-500/10' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50/50 dark:hover:bg-gray-750'" class="border rounded-2xl p-4 flex flex-col items-center text-center cursor-pointer transition-all duration-200 group relative">
                                    <input 
                                        type="radio" 
                                        name="assessments[{{ $indicator->id }}][score]" 
                                        value="1" 
                                        class="sr-only" 
                                        @change="scoreSelected = '1'" 
                                        {{ $currentScore == 1 ? 'checked' : '' }}
                                        @if(!$canAssess) disabled @endif
                                    />
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-400 group-hover:scale-105 transition-transform">1</span>
                                    <span class="text-xs font-black text-red-650 dark:text-red-400 uppercase tracking-wider mt-2.5">Kurang</span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 leading-relaxed">Belum memenuhi standar minimum nasional.</span>
                                </label>

                                <!-- Score 2 -->
                                <label :class="scoreSelected == '2' ? 'border-amber-500 bg-amber-50/50 dark:bg-amber-950/10 dark:border-amber-550 ring-2 ring-amber-500/10' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50/50 dark:hover:bg-gray-750'" class="border rounded-2xl p-4 flex flex-col items-center text-center cursor-pointer transition-all duration-200 group relative">
                                    <input 
                                        type="radio" 
                                        name="assessments[{{ $indicator->id }}][score]" 
                                        value="2" 
                                        class="sr-only" 
                                        @change="scoreSelected = '2'" 
                                        {{ $currentScore == 2 ? 'checked' : '' }}
                                        @if(!$canAssess) disabled @endif
                                    />
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 group-hover:scale-105 transition-transform">2</span>
                                    <span class="text-xs font-black text-amber-650 dark:text-amber-400 uppercase tracking-wider mt-2.5">Cukup</span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 leading-relaxed">Memenuhi standar minimum dengan catatan perbaikan.</span>
                                </label>

                                <!-- Score 3 -->
                                <label :class="scoreSelected == '3' ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/10 dark:border-blue-550 ring-2 ring-blue-500/10' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50/50 dark:hover:bg-gray-750'" class="border rounded-2xl p-4 flex flex-col items-center text-center cursor-pointer transition-all duration-200 group relative">
                                    <input 
                                        type="radio" 
                                        name="assessments[{{ $indicator->id }}][score]" 
                                        value="3" 
                                        class="sr-only" 
                                        @change="scoreSelected = '3'" 
                                        {{ $currentScore == 3 ? 'checked' : '' }}
                                        @if(!$canAssess) disabled @endif
                                    />
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400 group-hover:scale-105 transition-transform">3</span>
                                    <span class="text-xs font-black text-blue-650 dark:text-blue-400 uppercase tracking-wider mt-2.5">Baik</span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 leading-relaxed">Memenuhi seluruh kriteria standar dengan baik.</span>
                                </label>

                                <!-- Score 4 -->
                                <label :class="scoreSelected == '4' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-950/10 dark:border-emerald-550 ring-2 ring-emerald-500/10' : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50/50 dark:hover:bg-gray-750'" class="border rounded-2xl p-4 flex flex-col items-center text-center cursor-pointer transition-all duration-200 group relative">
                                    <input 
                                        type="radio" 
                                        name="assessments[{{ $indicator->id }}][score]" 
                                        value="4" 
                                        class="sr-only" 
                                        @change="scoreSelected = '4'" 
                                        {{ $currentScore == 4 ? 'checked' : '' }}
                                        @if(!$canAssess) disabled @endif
                                    />
                                    <span class="w-8 h-8 rounded-full flex items-center justify-center font-black text-xs bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 group-hover:scale-105 transition-transform">4</span>
                                    <span class="text-xs font-black text-emerald-650 dark:text-emerald-400 uppercase tracking-wider mt-2.5">Unggul</span>
                                    <span class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 leading-relaxed">Melampaui kriteria standar nasional secara konsisten.</span>
                                </label>
                            </div>
                        </div>

                        <!-- Textarea Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Evidence Text Area -->
                            <div class="space-y-1.5">
                                <label for="evidence-{{ $indicator->id }}" class="block text-xs font-bold text-gray-450 dark:text-gray-450 uppercase tracking-wider">Deskripsi Bukti Fisik / Narasi Kinerja</label>
                                <textarea 
                                    id="evidence-{{ $indicator->id }}" 
                                    name="assessments[{{ $indicator->id }}][evidence]" 
                                    rows="4" 
                                    placeholder="Jelaskan secara detail bukti fisik pendukung (misal: dokumen RPP lengkap, foto sarana, sertifikat kompetensi guru, dll.)"
                                    class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-2xl px-4 py-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none placeholder-gray-400 resize-none transition-all"
                                    @if(!$canAssess) readonly @endif
                                >{{ $currentEvidence }}</textarea>
                            </div>

                            <!-- Recommendation Text Area -->
                            <div class="space-y-1.5">
                                <label for="recom-{{ $indicator->id }}" class="block text-xs font-bold text-gray-450 dark:text-gray-450 uppercase tracking-wider">Rekomendasi Rencana Aksi Perbaikan</label>
                                <textarea 
                                    id="recom-{{ $indicator->id }}" 
                                    name="assessments[{{ $indicator->id }}][recommendation]" 
                                    rows="4" 
                                    placeholder="Tuliskan rekomendasi aksi konkret jika skor bernilai kurang dari 3.00, atau rencana pengembangan untuk mempertahankan keunggulan."
                                    class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-2xl px-4 py-3 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none placeholder-gray-400 resize-none transition-all"
                                    @if(!$canAssess) readonly @endif
                                >{{ $currentRecommendation }}</textarea>
                            </div>
                        </div>

                        <!-- Uploader & Files Listing Section -->
                        <div class="space-y-3">
                            <label class="block text-xs font-bold text-gray-450 dark:text-gray-450 uppercase tracking-wider">Unggahan Dokumen Bukti (PDF / JPG / PNG - Max 5MB)</label>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                                <!-- File Dropzone Uploader (Alpine.js integration) -->
                                <div>
                                    @if($canAssess)
                                    <div class="relative border-2 border-dashed border-gray-250 dark:border-gray-700 hover:border-primary-550 dark:hover:border-primary-500 rounded-2xl p-6 transition-colors duration-200 bg-gray-50/50 dark:bg-gray-950/20 group">
                                        <input 
                                            type="file" 
                                            name="assessments[{{ $indicator->id }}][files][]" 
                                            multiple 
                                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                        />
                                        <div class="text-center space-y-2 pointer-events-none">
                                            <span class="text-3xl inline-block group-hover:scale-110 transition-transform">📂</span>
                                            <p class="text-xs font-bold text-gray-600 dark:text-gray-300">Pilih Berkas atau Seret ke Sini</p>
                                            <p class="text-[10px] text-gray-400">PDF, JPEG, atau PNG (Maksimum 5MB per berkas)</p>
                                        </div>
                                    </div>
                                    @else
                                    <div class="border border-gray-150 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 rounded-2xl p-6 text-center text-xs text-gray-400">
                                        Fungsi unggah berkas dinonaktifkan dalam mode peninjauan.
                                    </div>
                                    @endif
                                </div>

                                <!-- Existing Files List -->
                                <div class="space-y-2.5">
                                    <p class="text-[10px] font-black text-gray-450 uppercase tracking-wider">Berkas Terlampir saat ini ({{ count($currentFiles) }}):</p>
                                    
                                    @forelse($currentFiles as $filePath)
                                        @php
                                            $fileName = basename($filePath);
                                            $isPdf = Str::endsWith(strtolower($fileName), '.pdf');
                                        @endphp
                                        <div class="flex items-center justify-between p-3 rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-200/50 dark:border-gray-800/80 text-xs font-bold">
                                            <div class="flex items-center space-x-2.5 truncate max-w-[75%]">
                                                <span>{{ $isPdf ? '📄' : '🖼️' }}</span>
                                                <a href="{{ $filePath }}" target="_blank" class="text-primary-600 hover:text-primary-750 dark:text-primary-400 dark:hover:text-primary-350 truncate transition-colors">{{ $fileName }}</a>
                                            </div>
                                            
                                            @if($canAssess)
                                            <!-- Delete checkbox styled as button label -->
                                            <label class="flex items-center space-x-1.5 cursor-pointer bg-red-50 dark:bg-red-950/20 px-2.5 py-1 rounded-xl text-red-650 dark:text-red-400 border border-red-200/50 dark:border-red-900/40 text-[10px] font-black uppercase tracking-wider hover:bg-red-100 transition-colors">
                                                <input 
                                                    type="checkbox" 
                                                    name="assessments[{{ $indicator->id }}][delete_files][]" 
                                                    value="{{ $filePath }}"
                                                    class="rounded text-red-600 focus:ring-red-500 h-3 w-3"
                                                    @change="if ($el.checked) { deleteFiles.push('{{ $filePath }}') } else { deleteFiles = deleteFiles.filter(f => f !== '{{ $filePath }}') }"
                                                />
                                                <span x-text="deleteFiles.includes('{{ $filePath }}') ? 'Batal Hapus' : 'Hapus'">Hapus</span>
                                            </label>
                                            @endif
                                        </div>
                                    @empty
                                        <p class="text-xs text-gray-450 italic pl-1">Belum ada dokumen bukti fisik yang dilampirkan.</p>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Action Buttons at bottom -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-250/50 dark:border-gray-700/50 shadow-xl p-6 flex flex-col md:flex-row justify-between items-center gap-4">
                
                <!-- Complete period checkbox -->
                <div>
                    @if($canAssess)
                        @can('manage', $period)
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input 
                                type="checkbox" 
                                name="complete_period" 
                                value="1"
                                class="rounded border-gray-300 dark:border-gray-700 text-emerald-600 focus:ring-emerald-500 h-5 w-5"
                            />
                            <div>
                                <span class="text-xs font-black text-gray-850 dark:text-white uppercase tracking-wide">Selesaikan Periode EDS secara Formal</span>
                                <p class="text-[10px] text-gray-400 mt-0.5">Centang ini jika seluruh 8 standar SNP telah selesai dievaluasi penuh dan siap ditutup.</p>
                            </div>
                        </label>
                        @endcan
                    @endif
                </div>

                <!-- Submit / Cancel buttons -->
                <div class="flex items-center space-x-3 w-full md:w-auto shrink-0 justify-end">
                    <a href="{{ route('eds.show', $period->id) }}" class="w-1/2 md:w-auto inline-flex justify-center items-center px-5 py-2.5 rounded-xl border border-gray-250 dark:border-gray-700 text-xs font-bold text-gray-750 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all text-center">
                        Kembali Ke Dashboard
                    </a>
                    
                    @if($canAssess)
                    <button type="submit" class="w-1/2 md:w-auto inline-flex justify-center items-center px-6 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-extrabold uppercase tracking-wider shadow-lg shadow-primary-500/20 transition-all text-center">
                        Simpan Penilaian
                    </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
