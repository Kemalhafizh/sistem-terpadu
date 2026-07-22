<x-app-layout>
    <x-slot name="title">Detail RPP — {{ $lessonPlan->topic }}</x-slot>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100 tracking-tight">
                     Rencana Pelaksanaan Pembelajaran (RPP)
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Detail lengkap dokumen RPP Digital & Panel Peninjauan.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('curriculum.lesson-plans.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 bg-white/70 dark:bg-gray-800/70 backdrop-blur-sm border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    Kembali
                </a>
                <a href="{{ route('curriculum.lesson-plans.pdf', $lessonPlan->uuid) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-red-500 to-rose-500 shadow-md shadow-red-500/20 hover:shadow-lg hover:shadow-red-500/35 hover:-translate-y-0.5 transition-all duration-300">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Unduh PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8 space-y-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- Flash Messages --}}
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                     class="rounded-xl border border-emerald-200 dark:border-emerald-700/50 bg-emerald-50/80 dark:bg-emerald-900/30 backdrop-blur-sm p-4">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" /></svg>
                        <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            {{-- Metadata Card --}}
            <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-xl overflow-hidden p-6">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-3">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-450 border border-emerald-200/50 dark:border-emerald-900/40">
                                Minggu Ke-{{ $lessonPlan->week_number }}
                            </span>
                            @if($lessonPlan->status === 'draft')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50">DRAFT</span>
                            @elseif($lessonPlan->status === 'submitted')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-blue-100 text-blue-800 dark:bg-blue-950/30 dark:text-blue-400 border border-blue-200 dark:border-blue-900/50">DIAJUKAN</span>
                            @elseif($lessonPlan->status === 'approved')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-450 border border-emerald-200 dark:border-emerald-900/50">DISETUJUI</span>
                            @elseif($lessonPlan->status === 'revision_needed')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-black bg-red-100 text-red-800 dark:bg-red-950/30 dark:text-red-400 border border-red-200 dark:border-red-900/50">REVISI</span>
                            @endif
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $lessonPlan->topic }}</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Disusun oleh: <strong class="text-gray-700 dark:text-gray-200">{{ $lessonPlan->user->name }}</strong> — Guru Pengampu {{ $lessonPlan->subject->name }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 lg:border-l lg:border-gray-200/60 dark:lg:border-gray-700/60 lg:pl-8">
                        <div>
                            <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Mata Pelajaran</span>
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $lessonPlan->subject->name }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Rombel / Kelas</span>
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $lessonPlan->classroom->name }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tahun Akademik</span>
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $lessonPlan->academicYear->year ?? '-' }}</span>
                        </div>
                        <div class="mt-2">
                            <span class="block text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Terakhir Diperbarui</span>
                            <span class="text-sm font-semibold text-gray-950 dark:text-white">{{ $lessonPlan->updated_at->locale('id')->isoFormat('D MMMM Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- RPP Body Sections --}}
            <div class="space-y-6">
                {{-- 1. Standar Kompetensi --}}
                <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg p-6 space-y-3">
                    <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-150 dark:border-gray-700 pb-3 flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 text-xs font-bold">I</span>
                        Standar Kompetensi / Kompetensi Inti & Dasar (KI - KD)
                    </h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                        {{ $lessonPlan->competency_standards ?: '— Tidak ditentukan —' }}
                    </p>
                </div>

                {{-- 2. Tujuan Pembelajaran --}}
                <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg p-6 space-y-3">
                    <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-150 dark:border-gray-700 pb-3 flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 text-xs font-bold">II</span>
                        Tujuan Pembelajaran
                    </h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                        {{ $lessonPlan->learning_objectives }}
                    </p>
                </div>

                {{-- 3. Kegiatan Pembelajaran --}}
                <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg p-6 space-y-3">
                    <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-150 dark:border-gray-700 pb-3 flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 text-xs font-bold">III</span>
                        Langkah & Kegiatan Pembelajaran
                    </h4>
                    <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                        {{ $lessonPlan->learning_activities }}
                    </p>
                </div>

                {{-- 4. Metode Penilaian --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg p-6 space-y-3">
                        <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-150 dark:border-gray-700 pb-3 flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 text-xs font-bold">IV</span>
                            Metode Penilaian / Asesmen
                        </h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                            {{ $lessonPlan->assessment_method ?: '— Tidak ditentukan —' }}
                        </p>
                    </div>

                    <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg p-6 space-y-3">
                        <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-150 dark:border-gray-700 pb-3 flex items-center gap-2">
                            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400 text-xs font-bold">V</span>
                            Sumber & Media Pembelajaran
                        </h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">
                            {{ $lessonPlan->resources ?: '— Tidak ditentukan —' }}
                        </p>
                    </div>
                </div>

                {{-- Attachment section if exists --}}
                @if($lessonPlan->file_path)
                    <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg p-6 space-y-3">
                        <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-150 dark:border-gray-700 pb-3 flex items-center gap-2">
                            <svg class="h-5 w-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Lampiran Dokumen PDF
                        </h4>
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-emerald-50/50 dark:bg-emerald-900/10 rounded-2xl border border-emerald-100 dark:border-emerald-900/30">
                            <div>
                                <span class="block text-sm font-bold text-emerald-900 dark:text-emerald-350">{{ basename($lessonPlan->file_path) }}</span>
                                <span class="block text-xs text-gray-400 mt-0.5">Dokumen RPP Tambahan / Rincian PDF</span>
                            </div>
                            <a href="{{ Storage::url($lessonPlan->file_path) }}" target="_blank"
                               class="inline-flex items-center justify-center bg-white dark:bg-gray-750 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50 rounded-xl px-4 py-2 text-xs font-bold hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all shadow-sm">
                                Buka di Tab Baru
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Peninjauan / Reviewer Details --}}
            @if($lessonPlan->reviewed_by && $lessonPlan->reviewed_at)
                <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-lg p-6 space-y-4">
                    <h4 class="text-base font-bold text-gray-900 dark:text-white border-b border-gray-150 dark:border-gray-700 pb-3 flex items-center gap-2">
                        <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Hasil Peninjauan Kurikulum
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Reviewer</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $lessonPlan->reviewer->name ?? '—' }}</span>
                        </div>
                        <div class="space-y-1.5">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Waktu Review</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $lessonPlan->reviewed_at->locale('id')->isoFormat('dddd, D MMMM Y — HH:mm') }} WIB</span>
                        </div>
                    </div>
                    @if($lessonPlan->reviewer_notes)
                        <div class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-2xl border border-gray-200 dark:border-gray-700/50">
                            <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Catatan Peninjau / Catatan Revisi</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 italic whitespace-pre-line">
                                "{{ $lessonPlan->reviewer_notes }}"
                            </p>
                        </div>
                    @endif
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- WAKA KURIKULUM / ADMIN REVIEW PANEL                           --}}
            {{-- ============================================================ --}}
            @if(in_array(auth()->user()->role ?? '', ['waka_kurikulum', 'admin']) && $lessonPlan->status === 'submitted')
                <div class="rounded-3xl border border-white/20 dark:border-gray-700/40 bg-white/70 dark:bg-gray-800/70 backdrop-blur-xl shadow-xl overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200/60 dark:border-gray-700/60 bg-gradient-to-r from-blue-50/50 to-indigo-50/50 dark:from-blue-900/10 dark:to-indigo-900/10">
                        <h4 class="text-base font-bold text-indigo-950 dark:text-indigo-300 flex items-center gap-2">
                            <svg class="h-5 w-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            Panel Peninjauan Kurikulum (Waka Kurikulum)
                        </h4>
                    </div>

                    <form action="{{ route('curriculum.lesson-plans.review', $lessonPlan->uuid) }}" method="POST" class="p-6 space-y-6" x-data="{ reviewDecision: '' }">
                        @csrf

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Keputusan Review RPP</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-emerald-300 dark:hover:border-emerald-600 cursor-pointer transition-all bg-white/40 dark:bg-gray-800/40">
                                    <input type="radio" name="status" value="approved" required x-model="reviewDecision"
                                           class="h-4 w-4 text-emerald-600 border-gray-300 focus:ring-emerald-500">
                                    <div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">Setujui RPP</span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">RPP disetujui untuk diimplementasikan guru</p>
                                    </div>
                                </label>
                                <label class="flex items-center gap-3 p-4 rounded-xl border border-gray-200 dark:border-gray-600 hover:border-red-300 dark:hover:border-red-600 cursor-pointer transition-all bg-white/40 dark:bg-gray-800/40">
                                    <input type="radio" name="status" value="revision_needed" required x-model="reviewDecision"
                                           class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                                    <div>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">Kembalikan untuk Revisi</span>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Guru perlu memperbaiki RPP sesuai catatan</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="reviewer_notes" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Catatan Peninjauan</label>
                            <textarea name="reviewer_notes" id="reviewer_notes" rows="4" required
                                      placeholder="Tuliskan umpan balik atau alasan jika memerlukan revisi..."
                                      class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 px-4 py-2.5 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 transition-colors resize-none"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-blue-500 to-indigo-600 shadow-md shadow-blue-500/25 hover:shadow-lg hover:shadow-blue-500/40 hover:-translate-y-0.5 transition-all duration-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                Kirim Keputusan Review
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
