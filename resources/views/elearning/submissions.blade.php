<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('elearning.class.show', $elearningAssignment->elearningClass->uuid) }}" class="text-gray-400 hover:text-emerald-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                {{ __('Evaluasi Pengumpulan Ujian / Tugas') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 sm:py-10 animate-fade-in">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Assignment Summary Card -->
            <div class="bg-white dark:bg-slate-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-slate-700 relative overflow-hidden group">
                <div class="relative z-10 space-y-3">
                    <span class="inline-flex px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 rounded text-[10px] font-black uppercase tracking-wider">
                        {{ $elearningAssignment->elearningClass->name }}
                    </span>
                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight leading-tight">
                        {{ $elearningAssignment->title }}
                    </h3>
                    <div class="flex flex-wrap gap-x-6 gap-y-2 text-xs font-bold text-gray-400">
                        <span>Batas Waktu: <strong class="text-gray-600 dark:text-slate-300">{{ \Carbon\Carbon::parse($elearningAssignment->due_at)->format('d M Y, H:i') }}</strong></span>
                        <span>Mode Keamanan: <strong class="{{ $elearningAssignment->is_secure_mode ? 'text-red-500' : 'text-gray-600 dark:text-slate-300' }}">{{ $elearningAssignment->is_secure_mode ? 'Lock-down Seluler (Max '.$elearningAssignment->max_violations.' Pelanggaran)' : 'Standar' }}</strong></span>
                        <span>Total Masuk: <strong class="text-gray-600 dark:text-slate-300">{{ $submissions->count() }} Pengumpulan</strong></span>
                    </div>
                </div>
            </div>

            <!-- Submissions Table Section -->
            <div class="bg-white dark:bg-slate-800 rounded-[2rem] shadow-sm border border-gray-100 dark:border-slate-700 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-slate-700/50">
                    <h4 class="text-base font-extrabold text-gray-900 dark:text-white">Daftar Pengumpulan Lembar Ujian Siswa</h4>
                </div>

                @if($submissions->isEmpty())
                    <div class="p-12 text-center text-gray-500">
                        <svg class="w-16 h-16 text-gray-300 dark:text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                        </svg>
                        <p class="text-xs font-bold">Belum ada siswa yang mengumpulkan tugas atau ujian ini.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs font-bold">
                            <thead>
                                <tr class="bg-gray-50 dark:bg-slate-900/50 text-gray-400 dark:text-slate-400 uppercase tracking-widest border-b border-gray-100 dark:border-slate-800">
                                    <th class="py-4 px-6">Siswa</th>
                                    <th class="py-4 px-6">Waktu Pengumpulan</th>
                                    <th class="py-4 px-6">Deteksi Pelanggaran</th>
                                    <th class="py-4 px-6">Status Integritas</th>
                                    <th class="py-4 px-6">Nilai Akhir</th>
                                    <th class="py-4 px-6 text-right">Aksi Penilaian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-slate-800">
                                @foreach($submissions as $sub)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-slate-900/10 transition-colors">
                                        <!-- Siswa Info -->
                                        <td class="py-4 px-6">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400 rounded-full flex items-center justify-center font-bold">
                                                    {{ substr($sub->student?->user?->name ?? 'S', 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-extrabold text-gray-900 dark:text-white leading-none mb-1">{{ $sub->student?->user?->name ?? 'Siswa' }}</p>
                                                    <p class="text-[10px] font-bold text-gray-400">NIS: {{ $sub->student?->nis }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <!-- Submitted At -->
                                        <td class="py-4 px-6 text-gray-600 dark:text-slate-300">
                                            {{ \Carbon\Carbon::parse($sub->submitted_at)->format('d M Y, H:i') }}
                                        </td>
                                        <!-- Violation Logs -->
                                        <td class="py-4 px-6">
                                            @if($elearningAssignment->is_secure_mode)
                                                <span class="inline-flex items-center px-2 py-1 rounded-lg {{ $sub->violation_count >= $elearningAssignment->max_violations ? 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400' : ($sub->violation_count > 0 ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400' : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400') }}">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                                    {{ $sub->violation_count }} Pelanggaran
                                                </span>
                                            @else
                                                <span class="text-gray-400 dark:text-slate-500 font-medium">-</span>
                                            @endif
                                        </td>
                                        <!-- Integrity Status -->
                                        <td class="py-4 px-6">
                                            @if($sub->status === 'locked_cheated')
                                                <span class="inline-flex px-2 py-0.5 bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-400 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                                    Locked (Cheated)
                                                </span>
                                            @elseif($sub->status === 'graded')
                                                <span class="inline-flex px-2 py-0.5 bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-400 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                                    Selesai Dinilai
                                                </span>
                                            @else
                                                <span class="inline-flex px-2 py-0.5 bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-400 rounded-lg text-[10px] font-black uppercase tracking-wider">
                                                    Menunggu Koreksi
                                                </span>
                                            @endif
                                        </td>
                                        <!-- Score -->
                                        <td class="py-4 px-6 text-sm font-extrabold text-gray-900 dark:text-white">
                                            {{ $sub->score !== null ? $sub->score : 'Belum Dinilai' }}
                                        </td>
                                        <!-- Grading action -->
                                        <td class="py-4 px-6 text-right" x-data="{ openGrade: false }">
                                            <div class="flex items-center justify-end space-x-2">
                                                @if($sub->file_path)
                                                    <a href="{{ asset($sub->file_path) }}" target="_blank" class="px-3 py-1.5 bg-gray-50 hover:bg-gray-100 dark:bg-slate-900 dark:border-slate-800 text-gray-700 dark:text-slate-300 border border-gray-100 rounded-lg transition-all" title="Buka berkas pengerjaan">
                                                        Buka File
                                                    </a>
                                                @endif
                                                
                                                <button @click="openGrade = !openGrade" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg transition-all">
                                                    Nilai
                                                </button>
                                            </div>

                                            <!-- Floating Grade Overlay Card (Alpine.js) -->
                                            <div x-show="openGrade" @click.away="openGrade = false" class="absolute right-6 mt-2 bg-white dark:bg-slate-800 border border-gray-100 dark:border-slate-700 p-6 rounded-2xl shadow-xl z-20 w-80 text-left space-y-4" style="display:none;">
                                                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-slate-700/50">
                                                    <h5 class="text-xs font-black text-gray-800 dark:text-white uppercase tracking-wider">Input Nilai Siswa</h5>
                                                    <button @click="openGrade = false" class="text-gray-400 hover:text-gray-600">&times;</button>
                                                </div>
                                                
                                                <form action="{{ route('elearning.submission.grade', $sub->id) }}" method="POST" class="space-y-3">
                                                    @csrf
                                                    <div class="space-y-1">
                                                        <label class="text-[10px] font-bold text-gray-400 uppercase">Skor Nilai (0 - 100)</label>
                                                        <input type="number" name="score" value="{{ $sub->score }}" min="0" max="100" step="0.01" required class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3">
                                                    </div>
                                                    <div class="space-y-1">
                                                        <label class="text-[10px] font-bold text-gray-400 uppercase">Feedback / Masukan Guru</label>
                                                        <textarea name="feedback" rows="2" class="w-full rounded-xl border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900 text-xs px-4 py-3" placeholder="Tuliskan masukan singkat...">{{ $sub->feedback }}</textarea>
                                                    </div>
                                                    <div class="pt-2">
                                                        <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold shadow-md">
                                                            Simpan Nilai
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
