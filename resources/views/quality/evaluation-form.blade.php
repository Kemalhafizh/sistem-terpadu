<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </span>
                    Formulir Evaluasi Diri Sekolah (EDS) — TA {{ $cycle->academic_year }} {{ ucfirst($cycle->semester) }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Berikan penilaian independen pemenuhan standar mutu internal berbasis skala 1 s.d. 4.</p>
            </div>
            <a href="{{ route('quality.cycles') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Siklus
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Scale Reference Card -->
            <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-2xl border border-indigo-200 dark:border-indigo-800 text-xs flex flex-wrap items-center justify-between gap-4">
                <span class="font-bold text-indigo-900 dark:text-indigo-200 uppercase font-mono">Skala Penilaian EDS:</span>
                <span class="px-3 py-1 bg-white dark:bg-gray-800 text-rose-700 rounded-lg shadow-sm font-bold">1 = Kurang (Belum Menuju SNP)</span>
                <span class="px-3 py-1 bg-white dark:bg-gray-800 text-amber-700 rounded-lg shadow-sm font-bold">2 = Cukup (Menuju SNP)</span>
                <span class="px-3 py-1 bg-white dark:bg-gray-800 text-blue-700 rounded-lg shadow-sm font-bold">3 = Baik (Memenuhi SNP)</span>
                <span class="px-3 py-1 bg-white dark:bg-gray-800 text-emerald-700 rounded-lg shadow-sm font-bold">4 = Sangat Baik (Memenuhi SNP+)</span>
            </div>

            <!-- Questionnaire Forms per Standard -->
            <div class="space-y-6">
                @foreach($standards as $std)
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                            <div>
                                <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-mono text-[10px] font-bold rounded">
                                    {{ $std->code }}
                                </span>
                                <h3 class="text-base font-black text-gray-900 dark:text-white inline-block ml-2">{{ $std->standard_name }}</h3>
                            </div>
                        </div>

                        <form action="{{ route('quality.submit_evaluation', $cycle->id) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                            @csrf
                            <input type="hidden" name="standard_id" value="{{ $std->id }}">
                            <input type="hidden" name="question_text" value="Penilaian Mandiri Pemenuhan {{ $std->standard_name }}">

                            <div class="md:col-span-3">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Skor Nilai Mandiri (1 - 4)</label>
                                <select name="score" required class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold">
                                    <option value="4">Skor 4 — Sangat Baik (Memenuhi SNP+)</option>
                                    <option value="3" selected>Skor 3 — Baik (Memenuhi SNP)</option>
                                    <option value="2">Skor 2 — Cukup (Menuju SNP)</option>
                                    <option value="1">Skor 1 — Kurang (Belum Menuju SNP)</option>
                                </select>
                            </div>

                            <div class="md:col-span-6">
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Catatan Bukti Fisik / Rencana Perbaikan</label>
                                <input type="text" name="evidence_notes" placeholder="Dokumen Kurikulum Merdeka telah disahkan & diimplementasikan 100%" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5">
                            </div>

                            <div class="md:col-span-3">
                                <button type="submit" class="w-full px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                                    Simpan Evaluasi {{ $std->code }}
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
