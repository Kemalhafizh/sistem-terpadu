<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('elearning.class.show', $elearningAssignment->elearningClass->uuid) }}" class="text-gray-400 hover:text-emerald-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                {{ __('Rincian Tugas & Ujian') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 animate-fade-in">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Main Detail Card -->
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-6 sm:p-10 shadow-sm border border-gray-100 dark:border-slate-700 space-y-6 relative overflow-hidden group">
                <div class="absolute -right-24 -top-24 w-48 h-48 bg-amber-500/5 rounded-full blur-3xl"></div>
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="inline-flex px-2 py-0.5 bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-400 rounded text-[10px] font-black uppercase tracking-wider">
                            {{ $elearningAssignment->elearningClass->name }}
                        </span>
                        <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight leading-tight">
                            {{ $elearningAssignment->title }}
                        </h3>
                    </div>
                    
                    @if($elearningAssignment->is_secure_mode)
                        <div class="inline-flex items-center px-4 py-2 bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 rounded-2xl text-xs font-black uppercase tracking-widest border border-red-100 dark:border-red-900/30">
                            <svg class="w-4 h-4 mr-1.5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            Sistem Lock-down Aktif
                        </div>
                    @endif
                </div>

                <!-- Assignment description box -->
                <div class="p-6 bg-gray-50 dark:bg-slate-900/50 rounded-3xl border border-gray-100 dark:border-slate-800/80">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-3">Soal / Instruksi Pengerjaan</h4>
                    <p class="text-xs sm:text-sm text-gray-700 dark:text-slate-300 leading-relaxed whitespace-pre-line">{{ $elearningAssignment->description }}</p>
                </div>

                <!-- Logistics / Due Date Stats -->
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-xs font-bold pt-4 border-t border-gray-100 dark:border-slate-700/50">
                    <div class="space-y-1">
                        <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Batas Pengumpulan</span>
                        <span class="text-gray-800 dark:text-white">{{ \Carbon\Carbon::parse($elearningAssignment->due_at)->format('d M Y, H:i') }}</span>
                    </div>
                    <div class="space-y-1">
                        <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Pengumpulan Terlambat</span>
                        <span class="text-gray-800 dark:text-white">{{ $elearningAssignment->allow_late ? 'Diizinkan' : 'Dilarang' }}</span>
                    </div>
                    <div class="col-span-2 md:col-span-1 space-y-1">
                        <span class="text-gray-400 block uppercase text-[10px] tracking-wider">Sisa Waktu Pengerjaan</span>
                        <span class="{{ $elearningAssignment->isOverdue() ? 'text-red-500' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $elearningAssignment->isOverdue() ? 'Batas Waktu Terlewati' : \Carbon\Carbon::parse($elearningAssignment->due_at)->diffForHumans() }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- SUBMISSION / ACTION CORNER -->
            <div class="space-y-6">
                <h4 class="text-base font-extrabold text-gray-900 dark:text-white flex items-center">
                    <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </span>
                    Status Pengerjaan Anda
                </h4>

                @if($submission)
                    @if($submission->status === 'locked_cheated')
                        <!-- Lock Cheated Status Warning Card -->
                        <div class="bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/50 p-6 sm:p-8 rounded-[2.5rem] space-y-4 shadow-sm relative overflow-hidden">
                            <div class="flex items-start space-x-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-200 rounded-2xl flex items-center justify-center">
                                    <svg class="w-6 h-6 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                <div class="space-y-1">
                                    <h4 class="text-lg font-black text-red-800 dark:text-red-400">Lembar Ujian Anda Terkunci</h4>
                                    <p class="text-xs text-red-700 dark:text-red-300 font-medium">Sistem mendeteksi upaya keluar layar atau pindah aplikasi sebanyak <strong class="text-sm">{{ $submission->violation_count }} kali</strong> selama ujian berlangsung.</p>
                                </div>
                            </div>
                            <p class="text-xs text-red-600 dark:text-red-400 leading-relaxed">
                                Sesuai kebijakan integritas sekolah, pengerjaan Anda otomatis ditangguhkan. Silakan hubungi guru pengampu mata pelajaran <strong class="text-gray-800 dark:text-white">({{ $elearningAssignment->elearningClass->teacher?->name }})</strong> untuk membuka blokir atau mengajukan ujian susulan manual.
                            </p>
                        </div>
                    @elseif($submission->status === 'graded')
                        <!-- Graded Status Card -->
                        <div class="bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-900/50 p-6 sm:p-8 rounded-[2.5rem] grid grid-cols-1 md:grid-cols-3 gap-6 shadow-sm">
                            <div class="md:col-span-2 space-y-4">
                                <div class="flex items-center space-x-3 text-emerald-800 dark:text-emerald-400">
                                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900 text-emerald-600 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    </div>
                                    <div>
                                        <h4 class="text-base font-black">Tugas Telah Dinilai</h4>
                                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest">Dikumpulkan pada {{ \Carbon\Carbon::parse($submission->submitted_at)->format('d M Y, H:i') }}</p>
                                    </div>
                                </div>
                                
                                @if($submission->feedback)
                                    <div class="p-4 bg-white dark:bg-slate-900 rounded-2xl border border-emerald-100 dark:border-slate-800">
                                        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Feedback Guru</span>
                                        <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed italic">"{{ $submission->feedback }}"</p>
                                    </div>
                                @endif
                            </div>

                            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-emerald-100 dark:border-slate-800 p-6 flex flex-col items-center justify-center text-center shadow-inner">
                                <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2">Nilai Akhir</span>
                                <span class="text-5xl font-black text-emerald-600 dark:text-emerald-400">{{ $submission->score }}</span>
                                <span class="text-[9px] font-bold text-gray-400 mt-2">KKM Mapel: {{ $elearningAssignment->elearningClass->subject?->kkm ?? 75 }}</span>
                            </div>
                        </div>
                    @else
                        <!-- Submitted but not graded -->
                        <div class="bg-white dark:bg-slate-800 rounded-3xl border border-gray-100 dark:border-slate-700 p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-50 dark:bg-blue-950 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <div>
                                    <h5 class="text-sm font-extrabold text-gray-900 dark:text-white leading-tight">Tugas Berhasil Dikumpulkan</h5>
                                    <p class="text-[10px] font-bold text-gray-400 uppercase">Menunggu penilaian dan koreksi dari guru pengajar.</p>
                                </div>
                            </div>
                            
                            @if($submission->file_path)
                                <a href="{{ asset($submission->file_path) }}" target="_blank" class="px-4 py-2 bg-gray-50 dark:bg-slate-900 border border-gray-100 dark:border-slate-800 text-xs font-bold text-gray-600 dark:text-slate-300 rounded-xl hover:text-emerald-600 transition-colors">
                                    Lihat Lembar Jawaban Saya
                                </a>
                            @endif
                        </div>
                    @endif
                @else
                    <!-- Not submitted yet -->
                    @if($elearningAssignment->isOverdue() && !$elearningAssignment->allow_late)
                        <div class="bg-gray-100 dark:bg-slate-900 border border-gray-200 dark:border-slate-800 p-6 rounded-3xl text-center text-gray-500">
                            <p class="text-xs font-bold">Batas pengumpulan tugas telah berakhir. Tugas ini tidak mengizinkan pengumpulan terlambat.</p>
                        </div>
                    @else
                        <!-- Form Submission -->
                        @if($elearningAssignment->is_secure_mode)
                            <!-- SECURE MODE INSTRUCTIONS -->
                            <div class="bg-slate-900 text-white rounded-[2.5rem] p-6 sm:p-10 shadow-xl space-y-6 relative overflow-hidden">
                                <div class="absolute -right-20 -bottom-20 w-48 h-48 bg-red-500/10 rounded-full blur-3xl"></div>
                                <div class="absolute -left-20 -top-20 w-48 h-48 bg-emerald-500/5 rounded-full blur-3xl"></div>

                                <div class="space-y-2 text-center">
                                    <div class="w-16 h-16 bg-red-600 rounded-3xl mx-auto flex items-center justify-center text-white shadow-lg shadow-red-950/50">
                                        <svg class="w-8 h-8 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    </div>
                                    <h4 class="text-lg font-black tracking-tight text-red-500 uppercase">Ujian / Tugas Menggunakan Mode Aman Ujian</h4>
                                    <p class="text-xs text-gray-300 max-w-md mx-auto">Untuk menjaga integritas akademik, pengerjaan kuis ini diawasi dengan sistem pengetatan seluler PWA.</p>
                                </div>

                                <div class="bg-gray-800/80 border border-gray-700/50 p-5 rounded-2xl space-y-3 text-xs max-w-lg mx-auto leading-relaxed">
                                    <div class="flex items-center space-x-2 text-amber-400 font-bold">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <span>Aturan Integritas Penting:</span>
                                    </div>
                                    <ul class="list-disc list-inside space-y-1.5 text-gray-300">
                                        <li>Layar browser akan dikunci penuh dalam mode <strong class="text-white">Fullscreen</strong>.</li>
                                        <li>Dilarang keras menyalin teks, menekan tombol kembali, membuka tab baru, atau meminimize aplikasi.</li>
                                        <li>Setiap aksi meninggalkan halaman / meminimize app dicatat sebagai pelanggaran.</li>
                                        <li>Toleransi kesalahan pemindahan layar dibatasi maksimal <strong class="text-white font-extrabold">{{ $elearningAssignment->max_violations }} kali</strong> sebelum lembar ujian dikunci mati.</li>
                                    </ul>
                                </div>

                                <div class="text-center pt-2">
                                    <a href="{{ route('elearning.assignment.secure', $elearningAssignment->uuid) }}" class="inline-flex px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-extrabold shadow-lg shadow-emerald-950/20 transform hover:-translate-y-0.5 transition-all">
                                        Mulai Ujian Mode Aman (Lock-down)
                                    </a>
                                </div>
                            </div>
                        @else
                            <!-- STANDARD UPLOAD FORM -->
                            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-slate-700 shadow-sm space-y-4">
                                <div class="space-y-1">
                                    <h4 class="text-base font-extrabold text-gray-900 dark:text-white">Kumpulkan Lembar Jawaban</h4>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">Silakan unggah foto lembar jawaban Anda (JPEG/PNG) atau dokumen berkas PDF hasil pengerjaan.</p>
                                </div>
                                
                                @if($elearningAssignment->isOverdue() && $elearningAssignment->allow_late)
                                    <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-900/30 p-4 rounded-xl text-xs font-bold text-amber-700 dark:text-amber-400 flex items-center space-x-2">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                                        <span>PERINGATAN: Batas pengumpulan terlewati. Pengumpulan Anda akan ditandai terlambat.</span>
                                    </div>
                                @endif

                                <form action="{{ route('elearning.assignment.submit', $elearningAssignment->uuid) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div class="space-y-2">
                                        <label class="text-xs font-bold text-gray-400 uppercase">Pilih Berkas Lembar Jawaban (PDF, JPG, JPEG, PNG - Max 5MB)</label>
                                        <input type="file" name="file" accept=".pdf,image/*" required class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                                    </div>
                                    <div class="pt-2">
                                        <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-md transition-all">
                                            Kumpulkan Jawaban Secara Aman
                                        </button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
