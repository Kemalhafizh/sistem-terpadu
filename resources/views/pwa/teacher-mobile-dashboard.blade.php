<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Dasbor Mobile Guru') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ $data['date_formatted'] ?? '' }}
                </p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                <svg class="w-3.5 h-3.5 mr-1 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Mode Mobile Aktif
            </span>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Quick Actions Row -->
        <div class="grid grid-cols-3 gap-3">
            <a href="{{ Route::has('teacher.attendance.index') ? route('teacher.attendance.index') : (Route::has('attendances.index') ? route('attendances.index') : '#') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm hover:border-emerald-500 dark:hover:border-emerald-500 transition-all text-center group">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-slate-700 dark:text-slate-200">Presensi Kelas</span>
            </a>
            <a href="{{ Route::has('grades.index') ? route('grades.index') : '#' }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm hover:border-cyan-500 dark:hover:border-cyan-500 transition-all text-center group">
                <div class="w-12 h-12 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 dark:text-cyan-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-slate-700 dark:text-slate-200">Input Nilai</span>
            </a>
            <a href="{{ Route::has('teacher.journal.index') ? route('teacher.journal.index') : (Route::has('teaching-journals.index') ? route('teaching-journals.index') : '#') }}" class="flex flex-col items-center justify-center p-4 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm hover:border-violet-500 dark:hover:border-violet-500 transition-all text-center group">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 dark:bg-violet-950/60 text-violet-600 dark:text-violet-400 flex items-center justify-center mb-2 group-hover:scale-110 transition-transform">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-slate-700 dark:text-slate-200">Jurnal Mengajar</span>
            </a>
        </div>

        <!-- Schedule Today -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Jadwal Mengajar Hari Ini
                </h3>
                <span class="text-xs text-slate-500">{{ count($data['schedules'] ?? []) }} Sesi</span>
            </div>

            @if(empty($data['schedules']))
                <div class="text-center py-8 text-slate-500 dark:text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-2 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm">Tidak ada jadwal mengajar aktif hari ini.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach($data['schedules'] as $sch)
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 flex items-center justify-between">
                            <div>
                                <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">{{ $sch['time'] }}</span>
                                <h4 class="font-bold text-slate-900 dark:text-white text-base mt-0.5">{{ $sch['subject'] }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $sch['class'] }} &bull; {{ $sch['room'] }}</p>
                            </div>
                            <a href="{{ Route::has('teacher.attendance.index') ? route('teacher.attendance.index') : (Route::has('attendances.index') ? route('attendances.index') : '#') }}" class="px-3 py-1.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-medium transition-colors">
                                Absensi
                            </a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Announcements -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
            <h3 class="font-bold text-lg text-slate-800 dark:text-white mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                Pengumuman Sekolah
            </h3>
            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse($data['announcements'] ?? [] as $ann)
                    <div class="py-3 first:pt-0 last:pb-0">
                        <h4 class="font-medium text-slate-800 dark:text-slate-200 text-sm">{{ $ann['title'] }}</h4>
                        <span class="text-xs text-slate-400 mt-0.5 block">{{ $ann['time_ago'] }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400 py-2">Belum ada pengumuman baru.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
