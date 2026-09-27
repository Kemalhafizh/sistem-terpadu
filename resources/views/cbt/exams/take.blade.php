<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $exam->title }} — Ujian CBT</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; -webkit-touch-callout: none; }
        input, textarea { -webkit-user-select: text; -moz-user-select: text; user-select: text; }
        body { font-family: 'Inter', sans-serif; overscroll-behavior: none; }
        .question-fade-enter { animation: fadeSlideIn 0.25s ease-out; }
        @keyframes fadeSlideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes shakeViolation { 0%,100% { transform: translateX(0); } 10%,30%,50%,70%,90% { transform: translateX(-4px); } 20%,40%,60%,80% { transform: translateX(4px); } }
        .shake { animation: shakeViolation 0.5s ease-in-out; }
    </style>
</head>
<body class="bg-slate-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col"
    x-data="examEngine()" x-init="initExam()"
    @contextmenu.prevent @copy.prevent @paste.prevent @cut.prevent @dragstart.prevent>

    {{-- BLOCKED OVERLAY --}}
    <div x-show="isBlocked" x-transition class="fixed inset-0 z-[200] bg-red-950/95 flex items-center justify-center p-6" style="display:none">
        <div class="text-center max-w-sm space-y-4">
            <div class="w-20 h-20 rounded-full bg-red-500/20 border-2 border-red-500 flex items-center justify-center mx-auto">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
            </div>
            <h1 class="text-2xl font-black text-white">UJIAN DIBLOKIR</h1>
            <p class="text-sm text-red-300">Anda telah melanggar batas maksimal pelanggaran (<span x-text="maxViolations"></span>x). Jawaban Anda telah disimpan dan dikirimkan secara otomatis.</p>
            <a href="{{ route('cbt.exams.results', $exam) }}" class="inline-block mt-4 px-6 py-3 bg-white text-red-700 rounded-xl text-sm font-bold">Lihat Hasil</a>
        </div>
    </div>

    {{-- VIOLATION WARNING MODAL --}}
    <div x-show="showViolationModal" x-transition class="fixed inset-0 z-[150] bg-red-950/90 backdrop-blur-sm flex items-center justify-center p-6" style="display:none">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 max-w-sm w-full text-center space-y-4 shadow-2xl shake border-2 border-red-500">
            <div class="w-16 h-16 rounded-full bg-red-500/10 border-2 border-red-500/50 flex items-center justify-center mx-auto">
                <svg class="w-8 h-8 text-red-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            </div>
            <h2 class="text-lg font-black text-red-600"> PELANGGARAN TERDETEKSI</h2>
            <p class="text-sm text-slate-600 dark:text-slate-300" x-text="violationMessage"></p>
            <div class="flex items-center justify-center gap-2">
                <span class="text-2xl font-black text-red-600" x-text="violationCount"></span>
                <span class="text-sm text-slate-400">/ <span x-text="maxViolations"></span> pelanggaran</span>
            </div>
            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                <div class="bg-red-500 h-2 rounded-full transition-all" :style="'width: ' + (violationCount / maxViolations * 100) + '%'"></div>
            </div>
            <button @click="dismissViolation()" class="w-full py-3 bg-gradient-to-r from-slate-800 to-slate-900 text-white rounded-xl text-sm font-bold hover:from-slate-700 hover:to-slate-800 transition-all">
                Kembali ke Ujian
            </button>
        </div>
    </div>

    {{-- TOP BAR --}}
    <header class="sticky top-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200 dark:border-slate-800 px-4 py-3">
        <div class="flex items-center justify-between max-w-2xl mx-auto">
            <div class="min-w-0 flex-1 mr-3">
                <h1 class="text-sm font-black text-slate-800 dark:text-white truncate">{{ $exam->title }}</h1>
                <p class="text-[10px] text-slate-400 font-semibold">{{ $exam->subject?->name }} — Soal <span x-text="currentQuestion + 1"></span>/<span x-text="totalQuestions"></span></p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                {{-- Violation Badge --}}
                <div x-show="violationCount > 0" class="flex items-center gap-1 px-2 py-1 rounded-lg bg-red-500/10 border border-red-500/20">
                    <svg class="w-3.5 h-3.5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01"></path></svg>
                    <span class="text-[10px] font-black text-red-600" x-text="violationCount + '/' + maxViolations"></span>
                </div>
                {{-- Timer --}}
                <div class="px-3 py-1.5 rounded-xl font-mono text-sm font-black" :class="remainingSeconds <= 300 ? 'bg-red-500/10 text-red-600 border border-red-500/30 animate-pulse' : 'bg-emerald-500/10 text-emerald-700 border border-emerald-500/20'" x-text="formatTime(remainingSeconds)"></div>
            </div>
        </div>
    </header>

    {{-- MAIN QUESTION AREA --}}
    <main class="flex-1 overflow-y-auto px-4 py-6 max-w-2xl mx-auto w-full">
        @foreach($questions as $idx => $q)
            <div x-show="currentQuestion === {{ $idx }}" x-transition:enter="question-fade-enter" class="space-y-5">
                {{-- Question Text --}}
                <div class="bg-white dark:bg-slate-900 rounded-2xl p-5 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Soal {{ $idx + 1 }}</p>
                    <p class="text-sm font-semibold text-slate-800 dark:text-white leading-relaxed">{{ $q->question_text }}</p>
                    @if($q->question_image)
                        <img src="{{ Storage::url($q->question_image) }}" alt="Gambar soal" class="mt-3 rounded-xl max-h-48 object-contain w-full">
                    @endif
                </div>

                {{-- Options --}}
                @if($q->type === 'multiple_choice')
                    <div class="space-y-2.5">
                        @php $opts = $shuffledOptions[$q->id] ?? []; @endphp
                        @foreach($opts as $key => $text)
                            <button type="button"
                                @click="selectAnswer({{ $q->id }}, '{{ $key }}')"
                                class="w-full flex items-center gap-3 p-4 rounded-xl border-2 text-left transition-all active:scale-[0.98]"
                                :class="answers[{{ $q->id }}] === '{{ $key }}' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-slate-300 dark:hover:border-slate-600'">
                                <span class="w-9 h-9 rounded-lg flex items-center justify-center text-xs font-black shrink-0 transition-all"
                                    :class="answers[{{ $q->id }}] === '{{ $key }}' ? 'bg-emerald-500 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'">{{ $key }}</span>
                                <span class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex-1">{{ $text }}</span>
                            </button>
                        @endforeach
                    </div>
                @elseif($q->type === 'true_false')
                    <div class="grid grid-cols-2 gap-3">
                        @foreach(['true' => 'Benar', 'false' => 'Salah'] as $val => $label)
                            <button type="button"
                                @click="selectAnswer({{ $q->id }}, '{{ $val }}')"
                                class="p-5 rounded-xl border-2 text-center transition-all active:scale-[0.98]"
                                :class="answers[{{ $q->id }}] === '{{ $val }}' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-950/30' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900'">
                                <span class="text-lg font-black" :class="answers[{{ $q->id }}] === '{{ $val }}' ? 'text-emerald-600' : 'text-slate-700 dark:text-slate-300'">{{ $label }}</span>
                            </button>
                        @endforeach
                    </div>
                @else
                    <textarea
                        @input="answers[{{ $q->id }}] = $event.target.value"
                        :value="answers[{{ $q->id }}] || ''"
                        rows="5" placeholder="Tulis jawaban esai Anda..."
                        class="w-full text-sm rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-slate-800 dark:text-white p-4 focus:border-emerald-500 transition-all"></textarea>
                @endif
            </div>
        @endforeach
    </main>

    {{-- BOTTOM NAVIGATION --}}
    <footer class="sticky bottom-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-t border-slate-200 dark:border-slate-800 px-4 py-3">
        <div class="max-w-2xl mx-auto space-y-3">
            {{-- Question Navigator --}}
            <div class="flex gap-1.5 overflow-x-auto pb-1 scrollbar-hide">
                @foreach($questions as $idx => $q)
                    <button @click="goToQuestion({{ $idx }})" class="w-8 h-8 rounded-lg text-[10px] font-black shrink-0 transition-all"
                        :class="{
                            'bg-emerald-500 text-white': answers[{{ $q->id }}] && currentQuestion !== {{ $idx }},
                            'bg-blue-500 text-white ring-2 ring-blue-300': currentQuestion === {{ $idx }},
                            'bg-slate-100 dark:bg-slate-800 text-slate-500': !answers[{{ $q->id }}] && currentQuestion !== {{ $idx }}
                        }">{{ $idx + 1 }}</button>
                @endforeach
            </div>
            {{-- Nav Buttons --}}
            <div class="flex items-center gap-3">
                <button @click="goToQuestion(currentQuestion - 1)" :disabled="currentQuestion === 0"
                    class="flex-1 py-3 rounded-xl text-sm font-bold transition-all border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-300 disabled:opacity-30">← Sebelumnya</button>

                <template x-if="currentQuestion < totalQuestions - 1">
                    <button @click="goToQuestion(currentQuestion + 1)"
                        class="flex-1 py-3 rounded-xl text-sm font-bold transition-all bg-slate-800 dark:bg-slate-700 text-white">Selanjutnya →</button>
                </template>
                <template x-if="currentQuestion === totalQuestions - 1">
                    <button @click="confirmSubmit()"
                        class="flex-1 py-3 rounded-xl text-sm font-extrabold transition-all bg-gradient-to-r from-emerald-600 to-teal-600 text-white shadow-lg shadow-emerald-500/20"> Kumpulkan Ujian</button>
                </template>
            </div>
        </div>
    </footer>

    <script>
        function examEngine() {
            return {
                currentQuestion: 0,
                answers: @json($attempt->answers ?? (object)[]),
                remainingSeconds: {{ $attempt->getRemainingSeconds() }},
                violationCount: {{ $attempt->violation_count }},
                maxViolations: {{ $exam->max_violations }},
                isBlocked: {{ $attempt->status === 'blocked_violation' ? 'true' : 'false' }},
                showViolationModal: false,
                violationMessage: '',
                timerInterval: null,
                autoSaveInterval: null,
                totalQuestions: {{ count($questionOrder) }},
                isFullscreen: false,
                isSecure: {{ $exam->is_secure_mode ? 'true' : 'false' }},

                initExam() {
                    // Start countdown
                    this.timerInterval = setInterval(() => {
                        this.remainingSeconds--;
                        if (this.remainingSeconds <= 0) {
                            clearInterval(this.timerInterval);
                            this.submitExam(true);
                        }
                    }, 1000);

                    // Auto-save every 20 seconds
                    this.autoSaveInterval = setInterval(() => this.saveAnswers(), 20000);

                    // Setup anti-cheat
                    if (this.isSecure) {
                        this.setupAntiCheat();
                        this.enterFullscreen();
                    }
                },

                setupAntiCheat() {
                    // Tab visibility
                    document.addEventListener('visibilitychange', () => {
                        if (document.hidden) {
                            this.recordViolation('tab_switch', 'Anda terdeteksi meninggalkan halaman ujian!');
                        }
                    });

                    // Window blur
                    window.addEventListener('blur', () => {
                        this.recordViolation('blur', 'Fokus terdeteksi keluar dari jendela ujian!');
                    });

                    // Fullscreen exit
                    document.addEventListener('fullscreenchange', () => {
                        if (!document.fullscreenElement) {
                            this.isFullscreen = false;
                            this.recordViolation('fullscreen_exit', 'Anda keluar dari mode layar penuh!');
                        } else {
                            this.isFullscreen = true;
                        }
                    });

                    // Block keyboard shortcuts
                    document.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && ['c','v','a','u','p','s'].includes(e.key.toLowerCase())) { e.preventDefault(); }
                        if (['F12','PrintScreen'].includes(e.key)) { e.preventDefault(); }
                    });
                },

                enterFullscreen() {
                    const el = document.documentElement;
                    if (el.requestFullscreen) el.requestFullscreen().catch(() => {});
                    else if (el.webkitRequestFullscreen) el.webkitRequestFullscreen();
                },

                async recordViolation(type, message) {
                    if (this.isBlocked) return;
                    this.violationMessage = message;
                    this.violationCount++;
                    this.showViolationModal = true;

                    // Haptic feedback
                    if (navigator.vibrate) navigator.vibrate([200, 100, 200, 100, 400]);

                    // Send to server
                    try {
                        const res = await fetch('{{ route("cbt.exams.log-violation", $exam) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ type: type })
                        });
                        const data = await res.json();
                        if (data.is_blocked) {
                            this.isBlocked = true;
                            clearInterval(this.timerInterval);
                            clearInterval(this.autoSaveInterval);
                        }
                    } catch (err) { console.error('Violation log error:', err); }
                },

                dismissViolation() {
                    this.showViolationModal = false;
                    if (this.isSecure) this.enterFullscreen();
                },

                selectAnswer(qId, answer) {
                    this.answers[qId] = answer;
                },

                goToQuestion(idx) {
                    if (idx >= 0 && idx < this.totalQuestions) {
                        this.currentQuestion = idx;
                        this.$nextTick(() => {
                            window.dispatchEvent(new CustomEvent('render-latex'));
                        });
                    }
                },

                async saveAnswers() {
                    try {
                        await fetch('{{ route("cbt.exams.save-answers", $exam) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ answers: this.answers })
                        });
                    } catch (err) { console.error('Auto-save error:', err); }
                },

                confirmSubmit() {
                    const answered = Object.values(this.answers).filter(v => v !== null && v !== '').length;
                    if (answered < this.totalQuestions) {
                        if (!confirm(`Anda baru menjawab ${answered} dari ${this.totalQuestions} soal. Yakin ingin mengumpulkan?`)) return;
                    } else {
                        if (!confirm('Yakin ingin mengumpulkan ujian? Jawaban tidak dapat diubah setelah dikumpulkan.')) return;
                    }
                    this.submitExam(false);
                },

                async submitExam(isAuto) {
                    clearInterval(this.timerInterval);
                    clearInterval(this.autoSaveInterval);
                    try {
                        const res = await fetch('{{ route("cbt.exams.submit", $exam) }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ answers: this.answers })
                        });
                        const data = await res.json();
                        if (data.redirect) window.location.href = data.redirect;
                        else window.location.href = '{{ route("cbt.exams.results", $exam) }}';
                    } catch (err) {
                        alert('Gagal mengumpulkan ujian. Coba lagi.');
                        console.error(err);
                    }
                },

                formatTime(s) {
                    if (s <= 0) return '00:00';
                    const m = Math.floor(s / 60);
                    const sec = s % 60;
                    return String(m).padStart(2, '0') + ':' + String(sec).padStart(2, '0');
                }
            };
        }
    </script>

    <!-- KaTeX LaTeX Math & Chemical Equation Renderer for Saintek -->
    <x-vendor.style lib="katex" />
    <x-vendor.script lib="katex" defer />
    <x-vendor.script lib="katex-mhchem" defer />
    <x-vendor.script lib="katex-auto-render" defer onload="renderSistaLatex()" />
    <script>
        function renderSistaLatex(element = document.body) {
            if (typeof renderMathInElement === 'function') {
                try {
                    renderMathInElement(element, {
                        delimiters: [
                            {left: '$$', right: '$$', display: true},
                            {left: '\\[', right: '\\]', display: true},
                            {left: '$', right: '$', display: false},
                            {left: '\\(', right: '\\)', display: false}
                        ],
                        throwOnError: false
                    });
                } catch (e) {
                    console.error("KaTeX Auto-render Error:", e);
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            renderSistaLatex();
        });
        window.addEventListener('render-latex', function() {
            renderSistaLatex();
        });
    </script>
</body>
</html>
