<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 dark:text-white leading-tight">
                    {{ __('Bimbingan & Konseling (BK Online)') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ school_short_name() }} &middot; {{ __('Pendampingan Akademik, Pribadi, Sosial, dan Karier Siswa') }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('counseling.request-form') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>{{ __('Buat Sesi Konseling') }}</span>
                </a>
                <a href="{{ route('counseling.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-medium hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm">
                    <svg class="w-4 h-4 shrink-0 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                    </svg>
                    <span>{{ __('Daftar Semua Sesi') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{
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
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            @if(session('success'))
                <div class="rounded-xl p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center gap-3 text-emerald-800 dark:text-emerald-200">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-xl p-4 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 flex items-center gap-3 text-rose-800 dark:text-rose-200">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                </div>
            @endif

            {{-- Metrik Ringkasan BK --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Sesi Hari Ini') }}</p>
                    <h3 class="text-3xl font-semibold text-slate-900 dark:text-white mt-2 tabular-nums">{{ $todaySessionsCount }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Agenda konseling aktif') }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 border-s-4 border-s-amber-500">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Antrean Permintaan') }}</p>
                    <h3 class="text-3xl font-semibold text-amber-600 dark:text-amber-400 mt-2 tabular-nums">{{ $requestedQueueCount }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Menunggu penjadwalan guru BK') }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Sesi Terjadwal') }}</p>
                    <h3 class="text-3xl font-semibold text-slate-900 dark:text-white mt-2 tabular-nums">{{ $upcomingSessionsCount }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Agenda konseling mendatang') }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 border-s-4 border-s-primary-600">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Perlu Tindak Lanjut') }}</p>
                    <h3 class="text-3xl font-semibold text-primary-700 dark:text-primary-400 mt-2 tabular-nums">{{ $needsFollowUpCount }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Jadwal evaluasi pendampingan') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Agenda & Antrean --}}
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <div class="flex flex-wrap justify-between items-center gap-2 mb-5">
                            <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Sesi Konseling Hari Ini') }}</h4>
                            <span class="px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-md text-xs font-medium">
                                {{ now()->translatedFormat('l, d F Y') }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            @forelse($todaySessions as $session)
                                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-700/70 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-slate-200/70 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg flex items-center justify-center shrink-0 font-semibold text-sm">
                                            {{ mb_substr($session->student->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <a href="{{ route('counseling.student-profile', $session->student) }}" class="font-medium text-sm text-slate-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                                    {{ $session->student->user->name }}
                                                </a>
                                                <span class="text-xs text-slate-500 dark:text-slate-400">({{ $session->student->classroom->name ?? __('Tanpa Rombel') }})</span>
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-slate-200/70 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                                                    {{ ucfirst($session->category) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 line-clamp-1">{{ __('Pokok bahasan') }}: {{ $session->reason }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 flex items-center gap-1.5 tabular-nums">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span>{{ $session->scheduled_at->format('H:i') }} WIB &middot; {{ __('Konselor') }}: {{ $session->counselor->name ?? '-' }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($session->status === 'scheduled')
                                            <form action="{{ route('counseling.start', $session) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors">
                                                    {{ __('Mulai Sesi') }}
                                                </button>
                                            </form>
                                        @elseif($session->status === 'in_progress')
                                            <a href="{{ route('counseling.session', $session) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-medium transition-colors">
                                                {{ __('Buka Sesi Aktif') }}
                                            </a>
                                        @endif
                                        <button type="button" @click="openCancelModal('{{ $session->id }}', '{{ addslashes($session->student->user->name) }}')" class="p-2 bg-white hover:bg-rose-50 text-slate-500 hover:text-rose-600 dark:bg-slate-800 dark:hover:bg-rose-950/50 rounded-lg transition-colors border border-slate-200 dark:border-slate-700" title="{{ __('Batalkan Sesi') }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 bg-slate-50 dark:bg-slate-900/30 border border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Tidak ada agenda sesi konseling hari ini') }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Gunakan tombol Buat Sesi Konseling untuk menjadwalkan pertemuan baru.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <div class="flex justify-between items-center mb-5">
                            <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Permohonan Sesi Baru (Menunggu Konfirmasi)') }}</h4>
                            <span class="px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/50 text-amber-800 dark:text-amber-300 border border-amber-200/60 dark:border-amber-800/50 rounded-md text-xs font-medium">
                                {{ __('Antrean') }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            @forelse($requestedSessions as $session)
                                <div class="p-4 bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-700/70 rounded-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 bg-slate-200/70 dark:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg flex items-center justify-center shrink-0 font-semibold text-sm">
                                            {{ mb_substr($session->student->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="font-medium text-sm text-slate-900 dark:text-white">{{ $session->student->user->name }}</span>
                                                <span class="text-xs text-slate-500 dark:text-slate-400">({{ $session->student->classroom->name ?? '-' }})</span>
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-md bg-slate-200/70 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                                                    {{ ucfirst($session->category) }}
                                                </span>
                                            </div>
                                            <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">{{ __('Alasan') }}: {{ $session->reason }}</p>
                                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                                {{ __('Diajukan oleh') }}: <span class="font-medium text-slate-700 dark:text-slate-200">{{ $session->requester->name ?? __('Sistem') }}</span> &middot; {{ $session->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <button type="button" @click="openScheduleModal('{{ $session->id }}', '{{ addslashes($session->student->user->name) }}')" class="px-3.5 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors">
                                            {{ __('Jadwalkan') }}
                                        </button>
                                        <button type="button" @click="openCancelModal('{{ $session->id }}', '{{ addslashes($session->student->user->name) }}')" class="px-3.5 py-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 rounded-lg text-xs font-medium transition-colors">
                                            {{ __('Tolak') }}
                                        </button>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 bg-slate-50 dark:bg-slate-900/30 border border-dashed border-slate-200 dark:border-slate-700 rounded-xl">
                                    <svg class="w-10 h-10 mx-auto text-slate-300 dark:text-slate-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="text-sm font-medium text-slate-600 dark:text-slate-300">{{ __('Antrean permohonan kosong') }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Seluruh permohonan siswa telah dikonfirmasi dan dijadwalkan.') }}</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-white mb-4">
                            {{ __('Distribusi Kategori Konseling') }}
                        </h4>
                        <div id="counselingCategoryChart" class="w-full"></div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-white mb-4">
                            {{ __('Sesi Mendatang') }}
                        </h4>
                        <div class="space-y-3">
                            @forelse($upcomingSessions as $session)
                                <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-700/70 rounded-lg">
                                    <div class="w-11 h-10 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 rounded-lg flex items-center justify-center shrink-0 font-semibold text-xs tabular-nums">
                                        {{ $session->scheduled_at->format('d/m') }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-slate-900 dark:text-white truncate">{{ $session->student->user->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5 tabular-nums">
                                            {{ $session->scheduled_at->format('H:i') }} WIB &middot; {{ ucfirst($session->category) }}
                                        </p>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-4">{{ __('Belum ada agenda terjadwal berikutnya') }}</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-white mb-4">
                            {{ __('Evaluasi Tindak Lanjut') }}
                        </h4>
                        <div class="space-y-3">
                            @forelse($followUpSessions as $session)
                                <div class="flex items-center justify-between gap-2 p-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200/70 dark:border-slate-700/70 rounded-lg">
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-medium text-slate-900 dark:text-white truncate">{{ $session->student->user->name }}</p>
                                        <p class="text-xs text-amber-700 dark:text-amber-400 mt-0.5 tabular-nums">
                                            {{ __('Jadwal evaluasi') }}: {{ $session->follow_up_date->translatedFormat('d M Y') }}
                                        </p>
                                    </div>
                                    <a href="{{ route('counseling.session', $session) }}" class="p-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition-colors" title="{{ __('Detail') }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </a>
                                </div>
                            @empty
                                <p class="text-xs text-slate-500 dark:text-slate-400 text-center py-4">{{ __('Tidak ada sesi yang memerlukan evaluasi dekat ini') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-modal name="schedule-session-modal" focusable>
            <form method="POST" :action="`{{ url('counseling/sessions') }}/${activeSessionId}/schedule`" class="p-6">
                @csrf
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">
                    {{ __('Konfirmasi & Penjadwalan Sesi') }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Siswa') }}: <span class="font-medium text-slate-900 dark:text-white" x-text="activeStudentName"></span></p>

                <div class="mt-5 space-y-4">
                    <div>
                        <label for="counselor_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Tentukan Konselor (Guru BK)') }}</label>
                        <select name="counselor_id" id="counselor_id" required class="mt-1.5 block w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                            <option value="">{{ __('Pilih Guru BK') }}</option>
                            @foreach($counselors as $bk)
                                <option value="{{ $bk->id }}">{{ $bk->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="scheduled_at" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Tanggal & Waktu Konseling') }}</label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" required class="mt-1.5 block w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-primary-500 focus:ring-primary-500 tabular-nums">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close')" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                        {{ __('Batal') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors">
                        {{ __('Simpan Jadwal') }}
                    </button>
                </div>
            </form>
        </x-modal>

        <x-modal name="cancel-session-modal" focusable>
            <form method="POST" :action="`{{ url('counseling/sessions') }}/${cancelSessionId}/cancel`" class="p-6">
                @csrf
                <h3 class="text-base font-semibold text-rose-600 dark:text-rose-400">
                    {{ __('Batalkan Sesi Bimbingan & Konseling') }}
                </h3>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Siswa') }}: <span class="font-medium text-slate-900 dark:text-white" x-text="cancelStudentName"></span></p>

                <div class="mt-5">
                    <label for="cancel_reason" class="block text-sm font-medium text-slate-700 dark:text-slate-300">{{ __('Alasan Pembatalan Sesi') }}</label>
                    <textarea name="cancel_reason" id="cancel_reason" required rows="3" placeholder="{{ __('Masukkan alasan resmi pembatalan sesi konseling') }}" class="mt-1.5 block w-full rounded-lg border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 dark:text-slate-100 text-sm focus:border-rose-500 focus:ring-rose-500"></textarea>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" @click="$dispatch('close')" class="px-4 py-2 border border-slate-200 dark:border-slate-700 rounded-lg text-xs font-medium text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800">
                        {{ __('Kembali') }}
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-medium transition-colors">
                        {{ __('Batalkan Sesi') }}
                    </button>
                </div>
            </form>
        </x-modal>
    </div>

    <x-vendor.script lib="apexcharts" />
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const pCategory = parseInt("{{ $categoryStats['pribadi'] }}");
            const pAcademic = parseInt("{{ $categoryStats['akademik'] }}");
            const pSocial = parseInt("{{ $categoryStats['sosial'] }}");
            const pCareer = parseInt("{{ $categoryStats['karir'] }}");
            const isDark = document.documentElement.classList.contains('dark');
            const sessionUnit = @js(__('sesi'));

            new ApexCharts(document.querySelector("#counselingCategoryChart"), {
                chart: {
                    type: 'donut',
                    height: 260,
                    background: 'transparent'
                },
                theme: { mode: isDark ? 'dark' : 'light' },
                series: [pCategory, pAcademic, pSocial, pCareer],
                labels: [@js(__('Pribadi')), @js(__('Akademik')), @js(__('Sosial')), @js(__('Karier'))],
                colors: ['#0d9488', '#0284c7', '#f59e0b', '#64748b'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '68%',
                            labels: {
                                show: true,
                                name: { show: true, fontSize: '13px', fontWeight: 600, offsetY: -4 },
                                value: {
                                    show: true,
                                    fontSize: '18px',
                                    fontWeight: 600,
                                    offsetY: 4,
                                    formatter: function (val) { return val + ' ' + sessionUnit; }
                                },
                                total: {
                                    show: true,
                                    label: @js(__('Total Sesi')),
                                    fontSize: '12px',
                                    fontWeight: 500,
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' ' + sessionUnit;
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { position: 'bottom', fontSize: '12px' }
            }).render();
        });
    </script>
</x-app-layout>
