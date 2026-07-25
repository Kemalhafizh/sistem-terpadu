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
                    {{ $standard->code }}: {{ $standard->standard_name }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Checklist indikator mutu dan pembubuhan bukti fisik/digital BAN-SM.</p>
            </div>
            <a href="{{ route('accreditation.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard SNP
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Upload Evidence Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Unggah Berkas Bukti Fisik / Digital Baru</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Lampirkan dokumen, data, atau foto bukti ketercapaian indikator BAN-SM.</p>
                </div>

                <form action="{{ route('accreditation.upload_evidence') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Indikator Mutu Target</label>
                        <select name="accreditation_indicator_id" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            @foreach($standard->indicators as $ind)
                                <option value="{{ $ind->id }}">{{ $ind->indicator_code }}: {{ $ind->indicator_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tipe Bukti</label>
                        <select name="evidence_type" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            <option value="document">Document (PDF/A Surat / SK / Silabus)</option>
                            <option value="data">Data (Tabel Analytics / Spreadsheet)</option>
                            <option value="photo">Photo (Foto Sarpras / Kegiatan)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Path / URL Berkas Bukti</label>
                        <input type="text" name="file_path" required value="evidences/Dokumen_Silabus_KTSP_2026.pdf" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30">
                            Unggah Bukti Fisik SNP
                        </button>
                    </div>
                </form>
            </div>

            <!-- Indicators List & Evidences Table -->
            <div class="space-y-6">
                @foreach($standard->indicators as $indicator)
                    <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden p-6 space-y-4">
                        <div class="flex items-start justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-0.5 bg-slate-100 text-slate-800 dark:bg-gray-700 dark:text-gray-200 font-mono text-xs font-bold rounded">
                                        {{ $indicator->indicator_code }}
                                    </span>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ $indicator->indicator_name }}</h4>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $indicator->description }}</p>
                            </div>
                            <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-mono text-xs font-bold rounded-xl border border-indigo-200 dark:border-indigo-800">
                                Target: {{ $indicator->target_value }}
                            </span>
                        </div>

                        <!-- Evidences Table for this indicator -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                                    <tr>
                                        <th class="px-4 py-2.5">Path / Berkas Bukti</th>
                                        <th class="px-4 py-2.5">Tipe</th>
                                        <th class="px-4 py-2.5">Pengunggah</th>
                                        <th class="px-4 py-2.5">Status Verifikasi BAN-SM</th>
                                        <th class="px-4 py-2.5 text-right">Otorisasi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                                    @forelse($indicator->evidences as $ev)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                            <td class="px-4 py-3 font-mono text-indigo-600 dark:text-indigo-400 font-bold text-[11px]">{{ $ev->file_path }}</td>
                                            <td class="px-4 py-3 uppercase font-mono text-[10px]">{{ $ev->evidence_type }}</td>
                                            <td class="px-4 py-3 text-gray-500">{{ optional($ev->uploader)->name }}</td>
                                            <td class="px-4 py-3">
                                                @if($ev->isVerified())
                                                    <span class="px-2.5 py-0.5 bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 font-mono text-[10px] font-bold rounded">DIVERIFIKASI</span>
                                                @else
                                                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 font-mono text-[10px] font-bold rounded">PENDING VERIFIKASI</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                @if(! $ev->isVerified() && auth()->user()->hasRole(['superadmin', 'admin', 'kepala_sekolah']))
                                                    <form action="{{ route('accreditation.verify_evidence', $ev->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[11px] font-bold transition-all shadow-sm">
                                                            Verifikasi Bukti
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-gray-400 text-[10px] font-mono">
                                                        {{ $ev->verifier ? 'Oleh: ' . $ev->verifier->name : '-' }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-6 text-center text-gray-400 italic text-[11px]">
                                                Belum ada bukti fisik yang diunggah untuk indikator ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
