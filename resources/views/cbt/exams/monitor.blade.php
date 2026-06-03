<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">🔴 Monitor Ujian Live</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $exam->title }} — {{ $exam->subject?->name }} — {{ $exam->classroom?->name ?? 'Semua Kelas' }}</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('cbt.exams.results', $exam) }}" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-all">Lihat Hasil</a>
                <a href="{{ route('cbt.exams.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all border border-slate-200 dark:border-slate-700">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6" x-data="proctorMonitor()" x-init="initMonitor()">

        {{-- Stats Bar --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Total Peserta</p>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white" x-text="students.length"></h3>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Sedang Mengerjakan</p>
                <h3 class="text-2xl font-black text-blue-600 dark:text-blue-400" x-text="students.filter(s => s.status === 'in_progress').length"></h3>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Selesai</p>
                <h3 class="text-2xl font-black text-emerald-600 dark:text-emerald-400" x-text="students.filter(s => ['graded','submitted'].includes(s.status)).length"></h3>
            </div>
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Diblokir</p>
                <h3 class="text-2xl font-black text-red-600 dark:text-red-400" x-text="students.filter(s => s.status === 'blocked_violation').length"></h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Student Grid --}}
            <div class="lg:col-span-2 space-y-4">
                <h4 class="text-sm font-black text-slate-800 dark:text-white">Peserta Ujian</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <template x-for="(student, idx) in students" :key="student.uuid">
                        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border rounded-2xl p-4 shadow-sm transition-all duration-300"
                            :class="{
                                'border-red-500 shadow-red-500/20 shadow-lg ring-2 ring-red-500/30': student.status === 'blocked_violation' || student.violation_count >= {{ $exam->max_violations }},
                                'border-amber-400 shadow-amber-400/10': student.violation_count > 0 && student.violation_count < {{ $exam->max_violations }},
                                'border-emerald-400': ['graded','submitted'].includes(student.status),
                                'border-gray-100 dark:border-slate-800/80': student.violation_count === 0 && student.status === 'in_progress'
                            }"
                            :id="'student-' + student.uuid">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <p class="text-xs font-bold text-slate-800 dark:text-white" x-text="student.student_name"></p>
                                    <p class="text-[10px] text-slate-400" x-text="'NIS: ' + student.student_nis"></p>
                                </div>
                                <span class="px-2 py-1 rounded-lg text-[9px] font-black uppercase tracking-wider border"
                                    :class="{
                                        'bg-blue-500/10 text-blue-600 border-blue-500/20 animate-pulse': student.status === 'in_progress',
                                        'bg-emerald-500/10 text-emerald-600 border-emerald-500/20': ['graded','submitted'].includes(student.status),
                                        'bg-red-500/15 text-red-600 border-red-500/30 animate-pulse': student.status === 'blocked_violation',
                                        'bg-amber-500/10 text-amber-600 border-amber-500/20': student.status === 'timeout'
                                    }" x-text="student.status === 'in_progress' ? 'Mengerjakan' : student.status === 'blocked_violation' ? 'DIBLOKIR' : student.status === 'timeout' ? 'Timeout' : 'Selesai'"></span>
                            </div>
                            {{-- Progress --}}
                            <div class="mb-2">
                                <div class="flex justify-between text-[10px] font-bold text-slate-400 mb-1">
                                    <span>Progres</span>
                                    <span x-text="student.answers_count + '/' + student.total_questions"></span>
                                </div>
                                <div class="w-full h-1.5 bg-slate-200 dark:bg-slate-700 rounded-full">
                                    <div class="h-1.5 bg-blue-500 rounded-full transition-all" :style="'width:' + (student.answers_count / student.total_questions * 100) + '%'"></div>
                                </div>
                            </div>
                            {{-- Violation & Score --}}
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" :class="student.violation_count > 0 ? 'text-red-500' : 'text-emerald-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span class="text-[10px] font-black" :class="student.violation_count > 0 ? 'text-red-600' : 'text-emerald-600'" x-text="student.violation_count + 'x pelanggaran'"></span>
                                </div>
                                <template x-if="student.score !== null">
                                    <span class="text-xs font-black" :class="student.score >= {{ $exam->passing_score }} ? 'text-emerald-600' : 'text-red-600'" x-text="'Nilai: ' + student.score"></span>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
                <div x-show="students.length === 0" class="text-center py-8 text-slate-400">
                    <p class="font-bold">Belum ada peserta yang memulai ujian.</p>
                </div>
            </div>

            {{-- Live Alert Feed --}}
            <div class="space-y-4">
                <h4 class="text-sm font-black text-slate-800 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    Alert Pelanggaran Live
                </h4>
                <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-4 shadow-sm max-h-[600px] overflow-y-auto space-y-2">
                    <template x-for="(alert, i) in alerts" :key="i">
                        <div class="flex items-start gap-2 p-3 rounded-xl bg-red-50/50 dark:bg-red-950/20 border border-red-200 dark:border-red-800/30 animate-pulse-once">
                            <div class="w-7 h-7 rounded-lg bg-red-500/10 text-red-500 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"></path></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[10px] font-black text-red-700 dark:text-red-400" x-text="alert.studentName"></p>
                                <p class="text-[10px] text-slate-500" x-text="alert.violationType.replace('_', ' ')"></p>
                                <p class="text-[9px] text-slate-400 mt-0.5" x-text="alert.timestamp"></p>
                            </div>
                            <span class="text-[10px] font-black text-red-600 shrink-0" x-text="alert.violationCount + 'x'"></span>
                        </div>
                    </template>
                    <div x-show="alerts.length === 0" class="text-center py-6 text-slate-400">
                        <svg class="w-6 h-6 mx-auto text-slate-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-[10px] font-bold">Belum ada pelanggaran</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function proctorMonitor() {
            return {
                students: @json($attempts),
                alerts: [],

                initMonitor() {
                    if (window.Echo) {
                        window.Echo.private('cbt.exam.{{ $exam->id }}')
                            .listen('.student-cheated', (e) => {
                                // Add to alerts
                                this.alerts.unshift({
                                    studentName: e.studentName,
                                    studentNis: e.studentNis,
                                    violationType: e.violationType,
                                    violationCount: e.violationCount,
                                    timestamp: e.timestamp,
                                });

                                // Update student card
                                const student = this.students.find(s => s.uuid === e.attemptUuid);
                                if (student) {
                                    student.violation_count = e.violationCount;
                                    if (e.violationCount >= {{ $exam->max_violations }}) {
                                        student.status = 'blocked_violation';
                                    }
                                }

                                // Shake the student card
                                const card = document.getElementById('student-' + e.attemptUuid);
                                if (card) {
                                    card.classList.add('shake');
                                    setTimeout(() => card.classList.remove('shake'), 600);
                                }

                                // Play alarm chime
                                try {
                                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                                    const osc = ctx.createOscillator();
                                    const gain = ctx.createGain();
                                    osc.connect(gain);
                                    gain.connect(ctx.destination);
                                    osc.frequency.value = 880;
                                    gain.gain.value = 0.3;
                                    osc.start();
                                    osc.stop(ctx.currentTime + 0.3);
                                } catch(e) {}
                            });
                    }
                }
            };
        }
    </script>

    <style>
        @keyframes shakeViolation { 0%,100%{transform:translateX(0)} 10%,30%,50%,70%,90%{transform:translateX(-3px)} 20%,40%,60%,80%{transform:translateX(3px)} }
        .shake { animation: shakeViolation 0.5s ease-in-out; }
        .animate-pulse-once { animation: pulse 1s ease-in-out 1; }
    </style>
</x-app-layout>
