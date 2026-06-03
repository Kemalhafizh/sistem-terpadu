<x-app-layout>
    <x-slot name="title">Detail Jurnal Mengajar</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('teaching-journals.index') }}" class="p-2 bg-gray-150 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 rounded-xl transition-all">
                    <svg class="w-5 h-5 text-gray-700 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                        📄 Detail Jurnal Mengajar Harian
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Laporan detail administrasi pembelajaran kelas tatap muka.</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($teachingJournal->status === 'draft')
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-black bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 border border-gray-250 dark:border-gray-700">STATUS: DRAFT</span>
                @elseif($teachingJournal->status === 'submitted')
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-250 dark:border-amber-900/50">STATUS: MENUNGGU REVIEW</span>
                @else
                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-450 border border-emerald-250 dark:border-emerald-900/50">STATUS: REVIEWED / OK</span>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Kolom Kiri & Tengah: Rincian Jurnal -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 p-6 md:p-8 rounded-3xl border border-gray-200/60 dark:border-gray-700/60 shadow-xl space-y-6">
                <!-- Data Header -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 border-b border-gray-150 dark:border-gray-700 pb-6 text-sm">
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Guru Pengajar</span>
                        <span class="font-bold text-gray-800 dark:text-white mt-1 block">{{ $teachingJournal->teacher->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Kelas / Mapel</span>
                        <span class="font-bold text-gray-850 dark:text-gray-100 mt-1 block">{{ $teachingJournal->classroom->name }}</span>
                        <span class="text-xs text-gray-450">{{ $teachingJournal->subject->name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Tanggal Mengajar</span>
                        <span class="font-bold text-gray-800 dark:text-white mt-1 block">{{ $teachingJournal->teaching_date->format('l, d M Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Jam Pelajaran</span>
                        <span class="font-bold text-gray-800 dark:text-white mt-1 block">Jam Ke-{{ $teachingJournal->jam_ke ?: '-' }}</span>
                    </div>
                </div>

                <!-- Konten Materi Pokok & Aktivitas -->
                <div class="space-y-4">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Topik / Materi Pokok</h4>
                        <p class="text-lg font-extrabold text-gray-850 dark:text-white leading-relaxed">{{ $teachingJournal->topic }}</p>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Aktivitas & Uraian Pembelajaran</h4>
                        <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/40 dark:border-gray-750 text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                            {{ $teachingJournal->learning_activity ?: 'Tidak ada detail uraian aktivitas pembelajaran yang ditulis.' }}
                        </div>
                    </div>
                </div>

                <!-- Detail Tambahan (Metode, Kehadiran, Kendala) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Metode Pembelajaran</h4>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-xs font-bold bg-primary-50 dark:bg-primary-950/20 text-primary-700 dark:text-primary-400 border border-primary-100 dark:border-primary-900/40">
                            {{ $teachingJournal->learning_method ?: 'Metode Standar' }}
                        </span>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Statistik Kehadiran Siswa</h4>
                        <div class="text-xs text-gray-700 dark:text-gray-300 space-y-1 mt-1">
                            <div>Hadir: <strong class="text-emerald-600 dark:text-emerald-450">{{ $teachingJournal->students_present ?? 0 }} siswa</strong></div>
                            <div>Absen/Alpa: <strong class="text-red-650 dark:text-red-405">{{ $teachingJournal->students_absent ?? 0 }} siswa</strong></div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Tahun Akademik</h4>
                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-1 block">
                            {{ $teachingJournal->academicYear?->year }} ({{ $teachingJournal->academicYear?->semester === '1' ? 'Ganjil' : 'Genap' }})
                        </span>
                    </div>
                </div>

                <!-- Hambatan & Catatan Khusus -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-150 dark:border-gray-700">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Hambatan / Kendala Di Kelas</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $teachingJournal->obstacles ?: 'Tidak ada kendala berarti.' }}</p>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Catatan Khusus Sesi</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300">{{ $teachingJournal->notes ?: 'Tidak ada catatan khusus sesi.' }}</p>
                    </div>
                </div>

                <!-- Lampiran File -->
                @if($teachingJournal->attachment_path)
                <div class="pt-4 border-t border-gray-150 dark:border-gray-700 flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Lampiran Bukti Mengajar</h4>
                        <p class="text-xs text-gray-500">Berkas foto/modul bukti kegiatan tatap muka di kelas.</p>
                    </div>
                    <a href="{{ asset('storage/' . $teachingJournal->attachment_path) }}" target="_blank" class="bg-primary-50 hover:bg-primary-100 text-primary-700 dark:bg-primary-950/20 dark:hover:bg-primary-950/40 dark:text-primary-400 rounded-xl px-4 py-2 text-xs font-bold uppercase tracking-wider transition-all flex items-center border border-primary-200/50 dark:border-primary-900/50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Unduh Lampiran
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Kolom Kanan: Panel Tinjauan Reviewer (Wakakur/Kepsek) -->
        <div class="space-y-6">
            <!-- Panel Ulasan -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-6 h-fit">
                <div>
                    <h3 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-wider mb-2 flex items-center gap-2">
                        <span>🔍</span> Tinjauan Koordinator
                    </h3>
                    <p class="text-[11px] text-gray-400 dark:text-gray-550 leading-relaxed">Kolom verifikasi untuk menyatakan kesesuaian administrasi jurnal mengajar dengan realisasi kurikulum.</p>
                </div>

                @if(in_array(auth()->user()->role, ['waka_kurikulum', 'kepala_sekolah', 'admin', 'superadmin']) && $teachingJournal->status === 'submitted')
                <!-- Form Tulis Review (Untuk Koordinator) -->
                <form action="{{ route('teaching-journals.review', $teachingJournal->uuid) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-450 mb-1.5">Catatan Ulasan / Revisi</label>
                        <textarea name="reviewer_notes" placeholder="Berikan catatan perbaikan jika dikembalikan, atau catatan persetujuan..." class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-sm text-gray-800 dark:text-gray-250 focus:ring-1 focus:ring-primary-500 focus:outline-none" rows="4">{{ old('reviewer_notes', $teachingJournal->reviewer_notes) }}</textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <button type="submit" name="status" value="draft" class="bg-red-50 hover:bg-red-100 text-red-700 dark:bg-red-950/20 dark:hover:bg-red-950/40 dark:text-red-400 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider transition-all border border-red-200/50 dark:border-red-900/50">
                            Revisi / Tolak
                        </button>
                        <button type="submit" name="status" value="reviewed" class="bg-emerald-600 hover:bg-emerald-700 text-white py-2.5 rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-emerald-500/20 transition-all">
                            Setujui Jurnal
                        </button>
                    </div>
                </form>
                @else
                <!-- Tampilan Hasil Review -->
                <div class="space-y-4 text-sm">
                    @if($teachingJournal->reviewer_notes || $teachingJournal->reviewed_by)
                    <div class="p-4 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/40 dark:border-gray-750">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <span class="text-xs font-bold text-gray-700 dark:text-gray-300">Catatan Tinjauan:</span>
                        </div>
                        <p class="text-xs text-gray-650 dark:text-gray-350 italic">"{{ $teachingJournal->reviewer_notes ?: 'Telah ditinjau tanpa catatan tambahan.' }}"</p>
                        
                        <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-4 font-semibold border-t border-gray-200/40 dark:border-gray-750 pt-2">
                            <div>Peninjau: {{ $teachingJournal->reviewer?->name ?? 'Koordinator Kurikulum' }}</div>
                            <div class="mt-0.5">Waktu: {{ $teachingJournal->reviewed_at?->format('d-m-Y H:i') ?: '-' }}</div>
                        </div>
                    </div>
                    @else
                    <div class="text-center p-6 bg-gray-50/50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        <svg class="mx-auto h-8 w-8 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        <p class="text-xs text-gray-400 dark:text-gray-500 italic">Jurnal belum ditinjau oleh Koordinator.</p>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
