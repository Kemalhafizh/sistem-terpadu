<x-app-layout>
    <x-slot name="title">Repositori Bukti Fisik Akreditasi</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <span></span> Repositori Bukti Fisik Akreditasi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Arsip digital bukti fisik pendukung instrumen akreditasi 8 Standar Nasional Pendidikan (SNP).
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button @click="$dispatch('open-modal', 'upload-document-modal')" class="inline-flex items-center justify-center bg-primary-600 hover:bg-primary-700 text-white rounded-xl py-2.5 px-5 text-sm font-black uppercase tracking-wider shadow-lg shadow-primary-500/20 hover:shadow-primary-500/35 transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Unggah Bukti Fisik
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ activeAccordion: 'SNP-1' }">
        <!-- Submenu Navigation -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex flex-wrap gap-1">
            <a href="{{ route('compliance.index') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                 Dashboard Kepatuhan
            </a>
            <a href="{{ route('compliance.checklist') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                 Checklist Instrumen
            </a>
            <a href="{{ route('compliance.documents') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all bg-primary-600 text-white shadow-md shadow-primary-500/20">
                 Repositori Bukti Fisik
            </a>
            <a href="{{ route('compliance.timeline') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                 Timeline Visitasi
            </a>
        </div>

        <!-- Document standards list (accordion) -->
        <div class="space-y-4">
            @foreach($standards as $standard)
            @php
                $stdDocs = $documents->get($standard->id) ?? collect();
            @endphp
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg overflow-hidden">
                <!-- Accordion Header -->
                <button 
                    @click="activeAccordion = (activeAccordion === '{{ $standard->code }}' ? '' : '{{ $standard->code }}')"
                    class="w-full px-6 py-5 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/30 border-b border-gray-150/50 dark:border-gray-750/50 text-left hover:bg-gray-100/50 dark:hover:bg-gray-850 transition-colors"
                >
                    <div class="flex items-center space-x-3">
                        <div class="p-2 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                            <span class="text-xs font-black">{{ $standard->code }}</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-gray-800 dark:text-white">{{ $standard->name }}</h3>
                            <p class="text-[10px] text-gray-400 mt-0.5">{{ $stdDocs->count() }} Dokumen Terupload</p>
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-400 dark:text-gray-550 text-sm font-bold" x-show="activeAccordion !== '{{ $standard->code }}'">▼</span>
                        <span class="text-gray-400 dark:text-gray-550 text-sm font-bold" x-show="activeAccordion === '{{ $standard->code }}'">▲</span>
                    </div>
                </button>

                <!-- Accordion Content -->
                <div x-show="activeAccordion === '{{ $standard->code }}'" x-collapse class="p-6 space-y-4">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="pb-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Nama Dokumen</th>
                                    <th class="pb-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Pengunggah</th>
                                    <th class="pb-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal Unggah</th>
                                    <th class="pb-3 text-xs font-bold text-gray-400 uppercase tracking-wider">Catatan</th>
                                    <th class="pb-3 text-xs font-bold text-gray-400 uppercase tracking-wider text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stdDocs as $doc)
                                <tr class="border-b border-gray-100 dark:border-gray-750/50 hover:bg-gray-50/20 dark:hover:bg-gray-850/20">
                                    <td class="py-4">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-lg"></span>
                                            <span class="text-xs font-extrabold text-gray-800 dark:text-gray-200">{{ $doc->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 text-xs text-gray-600 dark:text-gray-400 font-semibold">{{ $doc->uploader->name }}</td>
                                    <td class="py-4 text-xs text-gray-450 dark:text-gray-500 font-medium">{{ $doc->created_at->format('d M Y') }}</td>
                                    <td class="py-4 text-xs text-gray-500 dark:text-gray-400 font-normal italic max-w-[200px] truncate" title="{{ $doc->notes }}">
                                        {{ $doc->notes ?: '-' }}
                                    </td>
                                    <td class="py-4 text-right">
                                        <div class="inline-flex space-x-2">
                                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="p-1.5 bg-gray-100 dark:bg-gray-750 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg text-xs font-bold transition-all" title="Buka File">
                                                 Buka
                                            </a>
                                            <form method="post" action="{{ route('compliance.documents.destroy', $doc->id) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus dokumen bukti akreditasi ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-950/40 rounded-lg text-xs font-bold transition-all" title="Hapus Dokumen">
                                                     Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-xs text-gray-450 dark:text-gray-550 font-normal">
                                        Belum ada dokumen bukti fisik terunggah untuk standar ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Modal Unggah Dokumen Bukti Fisik -->
    <x-modal name="upload-document-modal" focusable>
        <form method="post" action="{{ route('compliance.documents.upload') }}" enctype="multipart/form-data" class="p-6 bg-white dark:bg-gray-900 rounded-3xl">
            @csrf
            
            <h2 class="text-lg font-black text-gray-950 dark:text-white flex items-center gap-1.5">
                <span></span> Unggah Bukti Fisik Baru
            </h2>
            
            <p class="mt-1.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-normal">
                Unggah berkas bukti fisik pendukung instrumen akreditasi sekolah (PDF, JPG, PNG, DOC, DOCX maksimal 10MB).
            </p>

            <div class="mt-6 space-y-4">
                <div>
                    <label for="standard_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Pilih Standar SNP</label>
                    <select 
                        id="standard_id" 
                        name="standard_id" 
                        required 
                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    >
                        @foreach($standards as $standard)
                        <option value="{{ $standard->id }}">{{ $standard->code }} - {{ $standard->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="name" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Nama Dokumen</label>
                    <input 
                        id="name" 
                        name="name" 
                        type="text" 
                        required 
                        placeholder="Contoh: Rencana Kerja Jangka Menengah (RKJM)" 
                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label for="file" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Berkas Bukti Fisik</label>
                    <input 
                        id="file" 
                        name="file" 
                        type="file" 
                        required 
                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    />
                </div>

                <div>
                    <label for="notes" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">Catatan Pendukung (Opsional)</label>
                    <textarea 
                        id="notes" 
                        name="notes" 
                        rows="2"
                        placeholder="Tambahkan catatan khusus, nomor SK, atau petunjuk audit jika diperlukan..." 
                        class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                    ></textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end space-x-3">
                <button type="button" @click="$dispatch('close')" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-880 transition-all">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-extrabold uppercase tracking-wider shadow-lg shadow-primary-500/20 transition-all">
                    Unggah Berkas
                </button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
