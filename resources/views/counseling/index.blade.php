<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </span>
                    {{ __('Daftar Riwayat Sesi Konseling') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • HISTORI LENGKAP BIMBINGAN BK</p>
            </div>
            
            <div class="flex gap-2">
                @if(in_array(auth()->user()->role, ['bk', 'admin', 'superadmin']))
                    <a href="{{ route('counseling.dashboard') }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-700 transition-all shadow-sm">
                        <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
                        </svg>
                        Ke Dashboard
                    </a>
                @endif
                <a href="{{ route('counseling.request-form') }}" class="inline-flex items-center px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-indigo-600/10">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Ajukan Permohonan Sesi
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ 
        openScheduleModal(sessionId, studentName) {
            $dispatch('open-modal', 'schedule-session-modal');
            this.activeSessionId = sessionId;
            this.activeStudentName = studentName;
        },
        openCancelModal(sessionId, studentName) {
            $dispatch('open-modal', 'cancel-session-modal');
            this.cancelSessionId = sessionId;
            this.cancelStudentName = studentName;
        },
        activeSessionId: null,
        activeStudentName: '',
        cancelSessionId: null,
        cancelStudentName: ''
    }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-indigo-50 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-900/50 flex items-center text-indigo-800 dark:text-indigo-300">
                    <svg class="w-5 h-5 mr-3 text-indigo-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex items-center text-red-800 dark:text-red-300">
                    <svg class="w-5 h-5 mr-3 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Filter Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <form method="GET" action="{{ route('counseling.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Search Input -->
                    @if(in_array(auth()->user()->role, ['bk', 'waka_kesiswaan', 'kepala_sekolah', 'admin', 'superadmin']))
                        <div class="lg:col-span-2">
                            <label for="search" class="block text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider">Cari Nama Siswa / Alasan</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Ketik nama siswa..." class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500 placeholder-gray-400">
                        </div>
                    @else
                        <div class="lg:col-span-2">
                            <label class="block text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider">Layanan BK Siswa</label>
                            <div class="mt-2 text-sm font-extrabold text-indigo-600">Portal Riwayat Konseling Anda</div>
                        </div>
                    @endif

                    <!-- Filter Status -->
                    <div>
                        <label for="status" class="block text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider">Status Sesi</label>
                        <select name="status" id="status" class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Status</option>
                            <option value="requested" {{ request('status') === 'requested' ? 'selected' : '' }}>Requested</option>
                            <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Filter Kategori -->
                    <div>
                        <label for="category" class="block text-xs font-black uppercase text-gray-400 dark:text-gray-500 tracking-wider">Kategori</label>
                        <select name="category" id="category" class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Semua Kategori</option>
                            <option value="pribadi" {{ request('category') === 'pribadi' ? 'selected' : '' }}>Pribadi</option>
                            <option value="akademik" {{ request('category') === 'akademik' ? 'selected' : '' }}>Akademik</option>
                            <option value="sosial" {{ request('category') === 'sosial' ? 'selected' : '' }}>Sosial</option>
                            <option value="karir" {{ request('category') === 'karir' ? 'selected' : '' }}>Karir</option>
                        </select>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-end gap-2">
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-indigo-600/10 text-center">
                            Filter
                        </button>
                        <a href="{{ route('counseling.index') }}" class="px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold uppercase hover:bg-gray-50 transition-all text-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl shadow-sm border border-gray-100 dark:border-gray-700/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Siswa & Kelas</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest font-bold">Kategori & Jenis</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest font-bold">Jadwal Sesi</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest font-bold">Konselor</th>
                                <th scope="col" class="px-6 py-4 class-left text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest font-bold">Status</th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($sessions as $session)
                                <tr class="hover:bg-gray-50/40 dark:hover:bg-gray-900/10 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-400 rounded-xl flex items-center justify-center shrink-0 font-extrabold text-sm mr-3">
                                                {{ substr($session->student->user->name, 0, 1) }}
                                            </div>
                                            <div>
                                                @if(in_array(auth()->user()->role, ['bk', 'waka_kesiswaan', 'kepala_sekolah', 'admin', 'superadmin']))
                                                    <a href="{{ route('counseling.student-profile', $session->student) }}" class="font-extrabold text-sm text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors block">
                                                        {{ $session->student->user->name }}
                                                    </a>
                                                @else
                                                    <span class="font-extrabold text-sm text-gray-900 dark:text-white block">{{ $session->student->user->name }}</span>
                                                @endif
                                                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $session->student->classroom->name ?? '-' }} • NISN: {{ $session->student->nisn }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <span class="px-2 py-0.5 text-[10px] font-black uppercase rounded bg-indigo-50 dark:bg-indigo-950 text-indigo-700 dark:text-indigo-400 mr-1.5">
                                            {{ $session->category }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-extrabold">
                                            {{ $session->type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs font-semibold text-gray-600 dark:text-gray-300">
                                        {{ $session->scheduled_at ? $session->scheduled_at->translatedFormat('d M Y • H:i') : '-' }} WIB
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-500 dark:text-gray-400">
                                        {{ $session->counselor->name ?? 'Belum Ditentukan' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($session->status === 'requested')
                                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full bg-yellow-50 dark:bg-yellow-950/30 text-yellow-700 dark:text-yellow-400 border border-yellow-200/50 dark:border-yellow-900/30">
                                                Requested
                                            </span>
                                        @elseif($session->status === 'scheduled')
                                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full bg-indigo-50 dark:bg-indigo-950/30 text-indigo-700 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-900/30">
                                                Scheduled
                                            </span>
                                        @elseif($session->status === 'in_progress')
                                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border border-teal-200/50 dark:border-teal-900/30 animate-pulse">
                                                In Progress
                                            </span>
                                        @elseif($session->status === 'completed')
                                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full bg-green-50 dark:bg-green-950/30 text-green-700 dark:text-green-400 border border-green-200/50 dark:border-green-900/30">
                                                Completed
                                            </span>
                                        @elseif($session->status === 'cancelled')
                                            <span class="px-2.5 py-1 text-[10px] font-black uppercase tracking-wider rounded-full bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200/50 dark:border-red-900/30">
                                                Cancelled
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-xs font-bold">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if(auth()->user()->role === 'bk')
                                                @if($session->status === 'requested')
                                                    <button @click="openScheduleModal('{{ $session->id }}', '{{ addslashes($session->student->user->name) }}')" class="px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white rounded-lg uppercase tracking-wider font-black transition-all">
                                                        Jadwalkan
                                                    </button>
                                                @elseif($session->status === 'scheduled')
                                                    <form action="{{ route('counseling.start', $session) }}" method="POST">
                                                        @csrf
                                                        <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg uppercase tracking-wider font-black transition-all">
                                                            Mulai
                                                        </button>
                                                    </form>
                                                @elseif($session->status === 'in_progress')
                                                    <a href="{{ route('counseling.session', $session) }}" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg uppercase tracking-wider font-black transition-all">
                                                        Interaksi
                                                    </a>
                                                @endif
                                            @endif

                                            @if($session->status === 'completed')
                                                <a href="{{ route('counseling.student-report', $session) }}" class="p-1.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-500 hover:text-indigo-600 rounded-lg shadow-sm transition-colors" title="Cetak Berita Acara PDF">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                </a>
                                            @endif

                                            @if(in_array($session->status, ['requested', 'scheduled']))
                                                <button @click="openCancelModal('{{ $session->id }}', '{{ addslashes($session->student->user->name) }}')" class="p-1.5 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-400 hover:text-red-600 rounded-lg shadow-sm transition-colors" title="Batalkan Sesi">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-sm font-bold text-gray-400 dark:text-gray-500">
                                        Belum ada catatan riwayat sesi konseling yang sesuai filter.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($sessions->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800">
                        {{ $sessions->links() }}
                    </div>
                @endif
            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL 1: JADWALKAN & PILIH KONSELOR -->
        <!-- ========================================== -->
        <x-modal name="schedule-session-modal" focusable>
            <form method="POST" :action="`{{ url('counseling/sessions') }}/${activeSessionId}/schedule`" class="p-6">
                @csrf
                <h3 class="text-lg font-extrabold text-gray-900 dark:text-white">
                    Konfirmasi & Penjadwalan Sesi
                </h3>
                <p class="text-xs text-gray-400 mt-1 uppercase font-black tracking-widest">Siswa: <span class="text-indigo-600" x-text="activeStudentName"></span></p>
                
                <div class="mt-6 space-y-4">
                    <div>
                        <label for="counselor_id" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Tentukan Konselor (Guru BK)</label>
                        <select name="counselor_id" id="counselor_id" required class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">-- Pilih Guru BK --</option>
                            @foreach($counselors as $bk)
                                <option value="{{ $bk->id }}">{{ $bk->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="scheduled_at" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Tanggal & Waktu Konseling</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" required class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close')" class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold uppercase text-gray-600 dark:text-gray-300 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Terapkan Sesi
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- ========================================== -->
        <!-- MODAL 2: BATALKAN SESI -->
        <!-- ========================================== -->
        <x-modal name="cancel-session-modal" focusable>
            <form method="POST" :action="`{{ url('counseling/sessions') }}/${cancelSessionId}/cancel`" class="p-6">
                @csrf
                <h3 class="text-lg font-extrabold text-red-600 dark:text-red-500">
                    Batalkan Sesi Bimbingan & Konseling
                </h3>
                <p class="text-xs text-gray-400 mt-1 uppercase font-black tracking-widest">Siswa: <span class="text-red-600" x-text="cancelStudentName"></span></p>
                
                <div class="mt-6">
                    <label for="cancel_reason" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Alasan Pembatalan Sesi</label>
                    <textarea name="cancel_reason" id="cancel_reason" required rows="3" placeholder="Masukkan alasan resmi pembatalan sesi konseling ini..." class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-red-500 focus:ring-red-500"></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close')" class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold uppercase text-gray-600 dark:text-gray-300 hover:bg-gray-50">
                        Kembali
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Batalkan Sesi
                    </button>
                </div>
            </form>
        </x-modal>

    </div>
</x-app-layout>
