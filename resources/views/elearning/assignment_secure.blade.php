<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MODE AMAN UJIAN: {{ $elearningAssignment->title }}</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo-kotak.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Strict CSS lockdown of selection and touch gestures */
        body {
            user-select: none !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            -webkit-touch-callout: none !important;
            background-color: #090d16;
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
            overscroll-behavior: contain;
        }

        /* Prevent pull-to-refresh on mobile */
        html {
            overscroll-behavior-y: contain;
        }
    </style>
</head>
<body class="h-screen overflow-hidden flex flex-col justify-between select-none">

    <!-- CRITICAL LOCKOUT OVERLAY (Violations exceeded) -->
    <div id="cheated-overlay" class="{{ ($submission && $submission->status === 'locked_cheated') ? '' : 'hidden' }} fixed inset-0 z-[9999] bg-red-950/98 backdrop-blur-md flex flex-col items-center justify-center text-center p-6 space-y-6">
        <div class="w-24 h-24 bg-red-700/20 text-red-500 border border-red-500/30 rounded-3xl flex items-center justify-center shadow-lg shadow-red-950/80 animate-pulse">
            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <div class="space-y-2">
            <h1 class="text-3xl font-black tracking-tight text-red-500 uppercase">Akses Ujian Dikunci</h1>
            <p class="text-sm font-semibold text-red-400">Terdeteksi Upaya Pelanggaran Integritas Ujian</p>
        </div>
        <p class="text-xs text-slate-400 max-w-md leading-relaxed">
            Anda telah melampaui batas toleransi perpindahan layar (switch aplikasi/tab/menu) sebanyak <span id="overlay-violation-count" class="font-extrabold text-white text-sm">{{ $submission ? $submission->violation_count : 0 }}</span> kali. Sesuai regulasi sekolah, lembar jawaban Anda telah dibekukan.
        </p>
        <div class="pt-4">
            <a href="{{ route('elearning.assignment.show', $elearningAssignment->uuid) }}" class="px-6 py-3 bg-red-800 hover:bg-red-900 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                Kembali ke Detail Tugas
            </a>
        </div>
    </div>

    <!-- FULLSCREEN REQUIREMENT OVERLAY -->
    <div id="fullscreen-overlay" class="fixed inset-0 z-[9990] bg-slate-950/98 flex flex-col items-center justify-center text-center p-6 space-y-6">
        <div class="w-20 h-20 bg-emerald-600/10 text-emerald-500 border border-emerald-500/20 rounded-3xl flex items-center justify-center">
            <svg class="w-10 h-10 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path>
            </svg>
        </div>
        <div class="space-y-2">
            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight">MASUK MODE UJIAN AMAN</h2>
            <p class="text-xs text-slate-400 max-w-sm">Halaman ini harus berjalan dalam layar penuh (fullscreen) untuk mencegah interaksi luar selama pengerjaan.</p>
        </div>
        <button id="btn-enter-fullscreen" class="px-6 py-3.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-extrabold transition-all shadow-lg shadow-emerald-950/50">
            Aktifkan Layar Penuh & Mulai Ujian
        </button>
        <div>
            <a href="{{ route('elearning.assignment.show', $elearningAssignment->uuid) }}" class="text-[11px] font-bold text-gray-500 hover:underline">
                Batal & Kembali
            </a>
        </div>
    </div>

    <!-- MAIN SECURE CONTAINER -->
    <div class="flex-1 flex flex-col overflow-hidden relative">
        
        <!-- Header -->
        <header class="bg-slate-900 border-b border-slate-800 py-4 px-6 flex justify-between items-center flex-shrink-0">
            <div class="flex items-center space-x-2">
                <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full"></span>
                <span class="text-xs font-black text-emerald-500 uppercase tracking-widest">Sistem Pengawasan Aktif</span>
            </div>
            <div class="text-right">
                <h3 class="text-xs font-black text-slate-300 truncate max-w-[180px] sm:max-w-xs">{{ $elearningAssignment->title }}</h3>
                <p class="text-[9px] font-bold text-slate-500">Target Siswa: {{ $student?->user?->name ?? 'Administrator Bypass' }}</p>
            </div>
        </header>

        <!-- Notification Warning Bar -->
        <div id="violation-warning-bar" class="hidden bg-amber-600 text-white py-2 px-4 text-center text-[10px] font-black uppercase tracking-widest animate-pulse flex-shrink-0">
             PERINGATAN: Perpindahan layar terdeteksi! Pelanggaran: <span id="warning-violation-count">0</span> / {{ $elearningAssignment->max_violations }}
        </div>

        <!-- Scrollable Exam Area -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 space-y-6">
            
            <!-- Welcome Alert Mode -->
            <div class="bg-gradient-to-r from-red-950/30 to-slate-900 border border-red-900/30 p-5 rounded-2xl space-y-2">
                <h4 class="text-xs font-black text-red-500 uppercase tracking-wider">INSTRUKSI PENGERJAAN PENTING</h4>
                <ol class="list-decimal list-inside text-[11px] text-slate-300 space-y-1 leading-relaxed">
                    <li>Kerjakan soal yang tertera pada lembar kertas fisik buram Anda secara mandiri.</li>
                    <li>Selesai mengerjakan, potret lembar pengerjaan Anda langsung menggunakan kamera ponsel Anda.</li>
                    <li>Unggah hasil potret/PDF pengerjaan menggunakan tombol unggah di bagian bawah layar.</li>
                    <li>Setelah mengunggah, tekan tombol <strong>"Kumpulkan Ujian"</strong> untuk menyelesaikan sesi aman ini.</li>
                </ol>
            </div>

            <!-- Exam Questions Card -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 space-y-4">
                <span class="text-[9px] font-black text-gray-500 uppercase tracking-widest block">Lembar Soal Ujian</span>
                <h4 class="text-lg font-black tracking-tight text-white leading-tight">Soal & Petunjuk Masalah</h4>
                <div class="p-6 bg-slate-950/50 rounded-2xl border border-slate-800">
                    <p class="text-xs sm:text-sm text-slate-300 leading-relaxed whitespace-pre-line select-none">{{ $elearningAssignment->description }}</p>
                </div>
            </div>

        </main>
    </div>

    <!-- FOOTER WITH SUBMIT / FILE DROP -->
    <footer class="bg-slate-900 border-t border-slate-800 p-4 sm:p-6 flex-shrink-0">
        <div class="max-w-2xl mx-auto">
            <form id="secure-submit-form" action="{{ route('elearning.assignment.submit', $elearningAssignment->uuid) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="flex flex-col sm:flex-row items-end gap-3">
                    <div class="flex-1 w-full space-y-1.5 text-left">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Unggah Foto / PDF Lembar Jawaban (Maks 5MB)</label>
                        <input type="file" name="file" accept=".pdf,image/*" required class="w-full text-xs text-slate-400 bg-slate-950 border border-slate-800 rounded-xl py-2 px-3 focus:outline-none focus:border-emerald-600 file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-black file:bg-emerald-600/20 file:text-emerald-400">
                    </div>
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-950/30 flex items-center justify-center space-x-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <span>Kumpulkan Jawaban</span>
                    </button>
                </div>
            </form>
        </div>
    </footer>

    <!-- LOGICAL LOCK-DOWN SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const fullscreenOverlay = document.getElementById('fullscreen-overlay');
            const btnEnterFullscreen = document.getElementById('btn-enter-fullscreen');
            const cheatedOverlay = document.getElementById('cheated-overlay');
            const warningBar = document.getElementById('violation-warning-bar');
            const warningCountSpan = document.getElementById('warning-violation-count');
            const overlayCountSpan = document.getElementById('overlay-violation-count');

            const isSecureMode = {{ $elearningAssignment->is_secure_mode ? 'true' : 'false' }};
            const maxViolations = {{ $elearningAssignment->max_violations }};
            const assignmentId = {{ $elearningAssignment->id }};
            
            let isFocused = true;
            let loadedAt = Date.now();
            let isTransitioning = false;

            // Block Right-Click Context Menu
            document.addEventListener('contextmenu', (e) => {
                e.preventDefault();
                return false;
            });

            // Block selection & copy-paste shortcut keys
            document.addEventListener('keydown', (e) => {
                // Block Ctrl+C, Ctrl+V, Ctrl+U (view source), F12 (DevTools)
                if (e.ctrlKey && ['c', 'v', 'u', 'a'].includes(e.key.toLowerCase())) {
                    e.preventDefault();
                    return false;
                }
                if (e.key === 'F12') {
                    e.preventDefault();
                    return false;
                }
            });

            // Enter fullscreen handler
            function enterFullscreen() {
                isTransitioning = true;
                const element = document.documentElement;
                if (element.requestFullscreen) {
                    element.requestFullscreen();
                } else if (element.webkitRequestFullscreen) {
                    element.webkitRequestFullscreen();
                } else if (element.msRequestFullscreen) {
                    element.msRequestFullscreen();
                }
                fullscreenOverlay.classList.add('hidden');
                
                // Grace period to prevent false positive triggers during fullscreen transition
                setTimeout(() => {
                    isTransitioning = false;
                }, 1000);
            }

            btnEnterFullscreen.addEventListener('click', enterFullscreen);

            // Visibility / Focus tracker logic
            async function triggerViolation() {
                // Ignore violations in grace period (first 1.5 seconds)
                if (Date.now() - loadedAt < 1500) return;
                // Ignore during transition animations
                if (isTransitioning) return;

                try {
                    const response = await fetch(`/elearning/assignment/${assignmentId}/log-violation`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const data = await response.json();
                        
                        // Update UI Metrics
                        warningBar.classList.remove('hidden');
                        warningCountSpan.textContent = data.violation_count;
                        overlayCountSpan.textContent = data.violation_count;

                        if (data.locked) {
                            cheatedOverlay.classList.remove('hidden');
                            // Exit fullscreen if locked to return control to screen
                            if (document.exitFullscreen) {
                                document.exitFullscreen().catch(() => {});
                            }
                        }
                    }
                } catch (error) {
                    console.error('Gagal melacak pelanggaran:', error);
                }
            }

            // Page Visibility change tracking
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'hidden') {
                    triggerViolation();
                }
            });

            // Window Blur tracking (exiting page, split screen, notifications overlay)
            window.addEventListener('blur', () => {
                triggerViolation();
            });

            // Fullscreen Exit check
            function checkFullscreenExit() {
                const isFullscreen = document.fullscreenElement || 
                                     document.webkitFullscreenElement || 
                                     document.mozFullScreenElement || 
                                     document.msFullscreenElement;
                
                if (!isFullscreen && !cheatedOverlay.classList.contains('hidden')) {
                    // Already cheated/locked, ignore
                    return;
                }

                if (!isFullscreen) {
                    // Student exited fullscreen, count as violation
                    triggerViolation();
                    // Re-show requirement overlay
                    fullscreenOverlay.classList.remove('hidden');
                }
            }

            document.addEventListener('fullscreenchange', checkFullscreenExit);
            document.addEventListener('webkitfullscreenchange', checkFullscreenExit);
            document.addEventListener('mozfullscreenchange', checkFullscreenExit);
            document.addEventListener('MSFullscreenChange', checkFullscreenExit);
        });
    </script>
</body>
</html>
