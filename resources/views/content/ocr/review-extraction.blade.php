<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </span>
                    Side-by-Side IDP OCR Verification & Review
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sandingkan gambar dokumen fisik (kiri) dengan hasil ekstraksi teks otomatis OCR (kanan).</p>
            </div>
            <a href="{{ route('ocr.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar OCR
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden grid grid-cols-1 lg:grid-cols-12">

                <!-- Left Panel: Scanned Document Preview (6 Cols) -->
                <div class="lg:col-span-6 p-6 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-700 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Berkas Pemindaian Fisik</h3>
                        <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 font-mono text-[10px] font-bold rounded">
                            {{ $doc->confidence_score === null ? 'Diisi manual' : 'Confidence: '.number_format($doc->confidence_score * 100, 1).'%' }}
                        </span>
                    </div>

                    <!-- Document Scan Mock Container -->
                    <div class="bg-slate-900 rounded-2xl p-6 text-center space-y-4 border border-slate-700 min-h-[400px] flex flex-col justify-center items-center">
                        <div class="w-20 h-20 bg-indigo-500/20 text-indigo-400 rounded-2xl flex items-center justify-center border border-indigo-500/30">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="space-y-1">
                            <h4 class="text-sm font-bold text-white uppercase font-mono">{{ $doc->document_type }} SCAN DOCUMENT</h4>
                            <p class="text-xs font-mono text-gray-400 truncate max-w-xs">{{ $doc->original_file_path }}</p>
                        </div>
                        <div class="p-3 bg-slate-800 rounded-xl text-left font-mono text-[11px] text-gray-300 w-full space-y-1 border border-slate-700">
                            <p class="text-indigo-400 font-bold">HASIL EKSTRAKSI TEKS MENTAH OCR:</p>
                            @foreach(($doc->extracted_data ?? []) as $key => $val)
                                @if(!is_array($val))
                                    <p><span class="text-gray-500">{{ strtoupper($key) }}:</span> {{ $val }}</p>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Verified Extracted Data Form (6 Cols) -->
                <div class="lg:col-span-6 p-6 space-y-6">
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Formulir Verifikasi Hasil Ekstraksi</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Periksa dan sesuaikan data yang berhasil terbaca otomatis sebelum disimpan ke profil pendaftaran.</p>
                    </div>

                    <form action="{{ route('ocr.apply', $doc->id) }}" method="POST" class="space-y-4">
                        @csrf

                        @foreach(($doc->extracted_data ?? []) as $key => $val)
                            @if(!is_array($val))
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">{{ str_replace('_', ' ', $key) }}</label>
                                    <input type="text" name="extracted_data[{{ $key }}]" value="{{ $val }}" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold">
                                </div>
                            @endif
                        @endforeach

                        <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Simpan & Terapkan Ke Profil Pendaftaran
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
