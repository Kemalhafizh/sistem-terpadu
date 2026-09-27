<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Hub Belajar Siswa') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    {{ $data['student_name'] ?? 'Siswa' }} &bull; {{ $data['classroom'] ?? 'Kelas X' }}
                </p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300">
                    Presensi: {{ $data['attendance_rate'] ?? 100 }}%
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-4xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Tahfidz Card Highlight -->
        @if(!empty($data['latest_tahfidz']))
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-3xl p-6 text-white shadow-lg relative overflow-hidden">
                <div class="relative z-10">
                    <span class="text-xs font-semibold uppercase tracking-wider bg-white/20 px-2.5 py-1 rounded-full">Progres Tahfidz Terbaru</span>
                    <h3 class="text-2xl font-bold mt-2">{{ $data['latest_tahfidz']['surah'] }}</h3>
                    <p class="text-emerald-100 text-sm mt-0.5">{{ $data['latest_tahfidz']['ayah'] }} @if(filled($data['latest_tahfidz']['score'])) &bull; Nilai: {{ $data['latest_tahfidz']['score'] }}@endif</p>
                </div>
            </div>
        @endif

        <!-- Recent Grades Carousel / Grid -->
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center">
                    <svg class="w-5 h-5 mr-2 text-cyan-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    Nilai Akademik Terbaru
                </h3>
                <a href="{{ Route::has('student.grades.index') ? route('student.grades.index') : (Route::has('grades.index') ? route('grades.index') : '#') }}" class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">Lihat Rapor</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @forelse($data['recent_grades'] ?? [] as $gr)
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 text-center">
                        <span class="text-xs text-slate-500 dark:text-slate-400">{{ $gr['type'] }}</span>
                        <div class="text-2xl font-black text-slate-800 dark:text-white my-1">{{ $gr['score'] }}</div>
                        <p class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate">{{ $gr['subject'] }}</p>
                    </div>
                @empty
                    <div class="col-span-full text-center py-6 text-slate-400 text-sm">Belum ada catatan nilai terbaru.</div>
                @endforelse
            </div>
        </div>

        <!-- Quick Links -->
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ Route::has('learning.index') ? route('learning.index') : '#' }}" class="p-5 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center space-x-3 hover:border-emerald-500 transition-all">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 dark:text-white text-sm">Materi & Tugas</h4>
                    <p class="text-xs text-slate-500">Akses modul pembelajaran</p>
                </div>
            </a>
            <a href="{{ Route::has('student.schedule.index') ? route('student.schedule.index') : (Route::has('schedules.index') ? route('schedules.index') : '#') }}" class="p-5 bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-sm flex items-center space-x-3 hover:border-cyan-500 transition-all">
                <div class="w-10 h-10 rounded-2xl bg-cyan-50 dark:bg-cyan-950/60 text-cyan-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="font-bold text-slate-800 dark:text-white text-sm">Jadwal Kelas</h4>
                    <p class="text-xs text-slate-500">Kalender & jam pelajaran</p>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
