<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </span>
                    Intelligent Document Processing (IDP) & OCR Engine
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ekstraksi data otomatis dari hasil pemindaian KTP, Kartu Keluarga, Akta Kelahiran, dan Ijazah untuk akselerasi PPDB/SPMB.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Upload & Process Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Unggah Dokumen Scan Baru Untuk Ekstraksi OCR</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Pilih jenis dokumen dan nama berkas scan. Mesin OCR belum terpasang: data diisi manual pada layar review.</p>
                </div>

                <form action="{{ route('ocr.upload') }}" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jenis Dokumen Ter-Scan</label>
                        <select name="document_type" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="ktp">KTP Orang Tua / Wali (NIK & Alamat)</option>
                            <option value="kk">Kartu Keluarga (No KK & Anggota)</option>
                            <option value="ijazah">Ijazah / SKL SMP (No Ijazah, NISN & Nilai)</option>
                            <option value="akta">Akta Kelahiran Siswa</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Berkas Pemindaian (Scan File)</label>
                        <input type="text" name="file_name" required value="" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30">
                            Proses OCR & Ekstraksi Bidang
                        </button>
                    </div>
                </form>
            </div>

            <!-- OCR Processed Documents Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Riwayat Pemrosesan Dokumen OCR</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih "Review Side-by-Side" untuk memverifikasi dan mencocokkan hasil ekstraksi.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">ID Registrasi</th>
                                <th class="px-6 py-4">Jenis Dokumen</th>
                                <th class="px-6 py-4">Path Berkas Scan</th>
                                <th class="px-6 py-4">Akurasi Confidence</th>
                                <th class="px-6 py-4">Status OCR</th>
                                <th class="px-6 py-4 text-right">Aksi Verifikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($documents as $doc)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">#OCR-{{ $doc->id }}</td>
                                    <td class="px-6 py-4 font-bold uppercase font-mono text-xs">{{ $doc->document_type }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-[11px] truncate max-w-[200px]">{{ $doc->original_file_path }}</td>
                                    <td class="px-6 py-4 font-mono">
                                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 rounded font-bold">
                                            {{ $doc->confidence_score === null ? 'manual' : number_format($doc->confidence_score * 100, 1).'%' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400 font-mono text-[10px] font-bold rounded uppercase">
                                            {{ $doc->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('ocr.review', $doc->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                            Review Side-by-Side
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada dokumen yang diproses oleh OCR Engine.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
