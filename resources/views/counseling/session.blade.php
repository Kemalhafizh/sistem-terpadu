<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </span>
                    {{ __('Ruang Interaksi Konseling') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">Sesi Konseling: {{ $session->student->user->name }} • ID: {{ substr($session->uuid, 0, 8) }}</p>
            </div>
            
            <div>
                <a href="{{ route('counseling.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 transition-all shadow-sm">
                    ← Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Ruang Rekam BK (Left 2/3) -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Detail Permasalahan Awal -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-indigo-100 dark:border-indigo-900/30">
                        <h4 class="text-xs font-black uppercase text-indigo-500 tracking-wider mb-3">Tujuan / Alasan Pengajuan</h4>
                        <div class="p-5 bg-indigo-50/20 dark:bg-indigo-950/10 border border-indigo-100/50 dark:border-indigo-900/20 rounded-2xl">
                            <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed italic">
                                "{{ $session->reason }}"
                            </p>
                        </div>
                    </div>

                    <!-- Rekam Catatan Rahasia (Terenkripsi) -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                        <div class="flex justify-between items-center mb-6">
                            <div>
                                <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                                    <span class="w-2.5 h-6 bg-emerald-600 rounded-full mr-3"></span>
                                    Catatan & Rencana Tindak Lanjut
                                </h3>
                                <p class="text-xs text-emerald-600 font-bold uppercase mt-1 tracking-wider">🔒 Enkripsi Transparan database diaktifkan</p>
                            </div>
                            <span class="px-3 py-1 bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-400 rounded-full text-xs font-black uppercase tracking-wider">
                                {{ $session->status }}
                            </span>
                        </div>

                        @if($session->status === 'in_progress' && auth()->user()->role === 'bk')
                            <!-- Form Rekam untuk BK -->
                            <form method="POST" action="{{ route('counseling.complete', $session) }}" class="space-y-6">
                                @csrf
                                
                                <div>
                                    <label for="session_notes" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Catatan Sesi Konseling (Rahasia)</label>
                                    <p class="text-[10px] text-gray-400 mt-1">Uraikan jalannya konseling, respon siswa, dan hasil observasi psikologis.</p>
                                    <textarea name="session_notes" id="session_notes" required rows="6" placeholder="Tuliskan catatan detail sesi di sini..." class="mt-2 block w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('session_notes', $session->session_notes) }}</textarea>
                                    @error('session_notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="action_plan" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Rencana Tindakan Siswa (Action Plan)</label>
                                    <p class="text-[10px] text-gray-400 mt-1">Komitmen atau langkah konkret yang harus dilakukan oleh siswa pasca sesi.</p>
                                    <textarea name="action_plan" id="action_plan" required rows="4" placeholder="Contoh: Siswa berkomitmen datang tepat waktu dan menandatangani pakta..." class="mt-2 block w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('action_plan', $session->action_plan) }}</textarea>
                                    @error('action_plan') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="follow_up_date" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Jadwal Evaluasi / Tindak Lanjut</label>
                                        <input type="date" name="follow_up_date" id="follow_up_date" value="{{ old('follow_up_date', $session->follow_up_date ? $session->follow_up_date->format('Y-m-d') : '') }}" class="mt-2 block w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('follow_up_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>

                                    <div>
                                        <label for="follow_up_notes" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Rencana Evaluasi (Tindak Lanjut)</label>
                                        <input type="text" name="follow_up_notes" id="follow_up_notes" value="{{ old('follow_up_notes', $session->follow_up_notes) }}" placeholder="Contoh: Evaluasi perkembangan poin disiplin 1 minggu..." class="mt-2 block w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        @error('follow_up_notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                <div class="pt-4 flex justify-end">
                                    <button type="submit" class="inline-flex items-center px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-xs font-black uppercase tracking-wider transition-all shadow-lg shadow-emerald-600/10">
                                        Selesaikan & Kunci Sesi
                                    </button>
                                </div>
                            </form>
                        @else
                            <!-- Tampilan Read-Only Selesai -->
                            <div class="space-y-6">
                                <div>
                                    <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Catatan Sesi (Terenkripsi Database)</h4>
                                    <div class="mt-2 p-5 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl">
                                        <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap">
                                            {{ $session->session_notes ?: 'Tidak ada catatan.' }}
                                        </p>
                                    </div>
                                </div>

                                <div>
                                    <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Rencana Tindakan Siswa (Action Plan)</h4>
                                    <div class="mt-2 p-5 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl">
                                        <p class="text-sm text-gray-800 dark:text-gray-200 leading-relaxed whitespace-pre-wrap">
                                            {{ $session->action_plan ?: 'Tidak ada rencana tindakan.' }}
                                        </p>
                                    </div>
                                </div>

                                @if($session->follow_up_date)
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Tanggal Tindak Lanjut</h4>
                                            <div class="mt-2 p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl">
                                                <p class="text-sm font-bold text-gray-800 dark:text-gray-200">
                                                    {{ $session->follow_up_date->translatedFormat('d F Y') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div>
                                            <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Catatan Tindak Lanjut</h4>
                                            <div class="mt-2 p-4 bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-800 rounded-2xl">
                                                <p class="text-sm text-gray-800 dark:text-gray-200">
                                                    {{ $session->follow_up_notes ?: '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                @if($session->status === 'completed')
                                    <div class="pt-4 flex justify-between items-center border-t border-gray-100 dark:border-gray-800">
                                        <p class="text-xs text-gray-400 font-bold">Sesi selesai dicatat pada {{ $session->ended_at->translatedFormat('d M Y H:i') }} WIB</p>
                                        <a href="{{ route('counseling.student-report', $session) }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-indigo-600/10">
                                            Cetak Berita Acara PDF
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                </div>

                <!-- Info Murid & Riwayat Pelanggaran (Right 1/3) -->
                <div class="space-y-8">
                    
                    <!-- Profil Murid -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col items-center text-center">
                        <div class="w-20 h-20 bg-indigo-100 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-400 rounded-full flex items-center justify-center font-extrabold text-3xl shadow-sm mb-4">
                            {{ substr($session->student->user->name, 0, 1) }}
                        </div>
                        <h4 class="font-extrabold text-base text-gray-900 dark:text-white">{{ $session->student->user->name }}</h4>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Kelas: {{ $session->student->classroom->name ?? '-' }} • NIS: {{ $session->student->nis }}</p>

                        <div class="w-full mt-6 grid grid-cols-2 gap-4 border-t border-gray-100 dark:border-gray-800 pt-6">
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Total Pelanggaran</p>
                                <span class="text-lg font-black {{ $session->student->total_points >= 50 ? 'text-red-600 dark:text-red-500' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $session->student->total_points }} Poin
                                </span>
                            </div>
                            <div>
                                <p class="text-[10px] font-black uppercase tracking-wider text-gray-400">Gelar Status</p>
                                <span class="text-xs font-black uppercase tracking-wide px-2 py-0.5 rounded {{ $session->student->total_points >= 50 ? 'bg-red-50 text-red-600' : 'bg-gray-50 text-gray-600' }}">
                                    {{ $session->student->total_points >= 50 ? 'Kritis' : 'Normal' }}
                                </span>
                            </div>
                        </div>

                        @if(in_array(auth()->user()->role, ['bk', 'waka_kesiswaan', 'kepala_sekolah', 'admin', 'superadmin']))
                            <a href="{{ route('counseling.student-profile', $session->student) }}" class="w-full mt-6 py-2.5 bg-gray-50 hover:bg-indigo-50 text-gray-500 hover:text-indigo-600 rounded-xl text-xs font-black uppercase tracking-wider transition-colors border border-gray-100">
                                Profil Holistik Siswa
                            </a>
                        @endif
                    </div>

                    <!-- Riwayat Konseling Terdahulu -->
                    <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50">
                        <h4 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider mb-4">
                            Konseling Terdahulu
                        </h4>

                        <div class="space-y-4">
                            @forelse($pastSessions as $past)
                                <div class="p-3 bg-gray-50 dark:bg-gray-950/20 border border-gray-100 dark:border-gray-900 rounded-xl">
                                    <div class="flex justify-between items-center">
                                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                                            {{ $past->category }}
                                        </span>
                                        <span class="text-[9px] font-bold text-gray-400">
                                            {{ $past->scheduled_at->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-300 mt-1 line-clamp-2">"{{ $past->reason }}"</p>
                                    <a href="{{ route('counseling.session', $past) }}" class="text-[10px] font-bold text-indigo-500 hover:underline mt-2 block">Detail Rekam →</a>
                                </div>
                            @empty
                                <p class="text-xs font-bold text-gray-400 dark:text-gray-500 text-center py-4">Belum ada riwayat konseling terdahulu.</p>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
