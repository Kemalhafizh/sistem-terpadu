<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-teal-500/10 text-teal-600 dark:text-teal-400 rounded-2xl border border-teal-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </span>
                    Terminal Sirkulasi Barcode & NFC
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sirkulasi cepat peminjaman & pengembalian buku fisik perpustakaan instan.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('library.books.print-barcodes') }}" target="_blank" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2 border border-gray-300 dark:border-gray-700">
                    <svg class="w-4 h-4 text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    Cetak Label Barcode A4
                </a>
                <a href="{{ route('library.circulation.index') }}" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-teal-500/20">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="quickTerminal()">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Stats Bar -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gradient-to-br from-teal-500/10 via-emerald-500/5 to-transparent p-5 rounded-3xl border border-teal-500/20 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-teal-600 dark:text-teal-400 uppercase tracking-wider">Buku Sedang Dipinjam</p>
                        <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($activeLoansCount) }}</h3>
                    </div>
                    <div class="p-3 bg-teal-500 text-white rounded-2xl shadow-lg shadow-teal-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-500/10 via-purple-500/5 to-transparent p-5 rounded-3xl border border-indigo-500/20 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">Transaksi Hari Ini</p>
                        <h3 class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ number_format($todayLoansCount) }}</h3>
                    </div>
                    <div class="p-3 bg-indigo-500 text-white rounded-2xl shadow-lg shadow-indigo-500/30">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Terminal Main Area -->
            <div class="bg-gray-900 text-white p-6 sm:p-8 rounded-3xl border border-gray-800 shadow-2xl space-y-6 relative overflow-hidden">
                <!-- Decorative Blur Background -->
                <div class="absolute -right-20 -top-20 w-72 h-72 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-20 -bottom-20 w-72 h-72 bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Mode Switch Tabs -->
                <div class="flex items-center justify-between border-b border-gray-800 pb-4">
                    <div class="flex items-center gap-3">
                        <button @click="setMode('borrow')" :class="mode === 'borrow' ? 'bg-teal-500 text-white shadow-lg shadow-teal-500/30' : 'bg-gray-800 text-gray-400 hover:text-white'" class="px-5 py-2.5 rounded-2xl text-xs font-black tracking-wider uppercase transition-all duration-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            Mode Peminjaman (Borrow)
                        </button>
                        <button @click="setMode('return')" :class="mode === 'return' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'bg-gray-800 text-gray-400 hover:text-white'" class="px-5 py-2.5 rounded-2xl text-xs font-black tracking-wider uppercase transition-all duration-300 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l4 4m-4-4l4-4"></path>
                            </svg>
                            Mode Pengembalian (Return)
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" x-model="soundEnabled" class="sr-only peer">
                            <div class="relative w-9 h-5 bg-gray-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-teal-500"></div>
                            <span class="ms-2 text-xs font-semibold text-gray-400">Suara Bip</span>
                        </label>
                    </div>
                </div>

                <!-- Input Scanner -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between text-xs text-gray-400 font-medium">
                        <span x-text="mode === 'borrow' ? (student ? 'Langkah 2: Pindai Barcode / NFC Buku' : 'Langkah 1: Pindai Kartu Siswa (NIS / NFC)') : 'Pindai Barcode / NFC Buku yang Dikembalikan'"></span>
                        <span class="text-teal-400 font-mono animate-pulse">● Autofokus Aktif</span>
                    </div>
                    <form @submit.prevent="handleScanSubmit()">
                        <div class="relative">
                            <input type="text" x-ref="scanInput" x-model="scanBuffer" @blur="keepFocus()" placeholder="Tempelkan kartu NFC atau Scan Barcode di sini..." class="w-full bg-gray-950 text-teal-300 font-mono text-xl sm:text-2xl font-bold py-4 pl-14 pr-12 rounded-2xl border-2 border-teal-500/40 focus:border-teal-400 focus:ring-4 focus:ring-teal-500/20 transition-all placeholder:text-gray-700 shadow-inner" autofocus>
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-teal-400">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m0 14v1m8-8h-1M5 12H4m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707"></path>
                                </svg>
                            </div>
                            <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 p-2.5 bg-teal-500 hover:bg-teal-400 text-gray-950 font-bold rounded-xl transition-all shadow-md">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Active Work Area (Split Grid) -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 pt-2">
                    
                    <!-- Left: Active Student Profile Card -->
                    <div class="lg:col-span-5 bg-gray-950/60 p-5 rounded-2xl border border-gray-800/80 space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Identitas Siswa Peminjam</h4>
                            <template x-if="student">
                                <button @click="resetStudent()" class="text-[10px] text-rose-400 hover:text-rose-300 font-bold underline">Ganti Siswa</button>
                            </template>
                        </div>

                        <template x-if="student">
                            <div class="space-y-4 animate-fadeIn">
                                <div class="flex items-center gap-4 p-3 bg-gray-900/80 rounded-2xl border border-gray-800">
                                    <div class="w-12 h-12 rounded-xl bg-teal-500/20 text-teal-400 border border-teal-500/30 flex items-center justify-center font-black text-xl">
                                        <span x-text="student.name.charAt(0)"></span>
                                    </div>
                                    <div>
                                        <h3 class="font-bold text-white text-base leading-tight" x-text="student.name"></h3>
                                        <p class="text-xs text-gray-400 font-mono mt-0.5" x-text="'NIS: ' + student.nis"></p>
                                        <span class="inline-block px-2 py-0.5 bg-teal-500/10 text-teal-400 text-[10px] font-bold rounded-md border border-teal-500/20 mt-1" x-text="student.class_name"></span>
                                    </div>
                                </div>

                                <div class="text-xs text-gray-400 bg-gray-900/50 p-3 rounded-xl border border-gray-800 space-y-1">
                                    <p class="flex justify-between"><span>Peminjaman Aktif:</span> <strong class="text-white" x-text="student.active_loans_count + ' buku'"></strong></p>
                                </div>
                            </div>
                        </template>

                        <template x-if="!student">
                            <div class="py-8 text-center space-y-2 border-2 border-dashed border-gray-800 rounded-2xl">
                                <svg class="w-10 h-10 text-gray-700 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <p class="text-xs text-gray-500 font-medium">Belum ada siswa terpindai.<br>Silakan scan NIS / Kartu NFC siswa terlebih dahulu.</p>
                            </div>
                        </template>
                    </div>

                    <!-- Right: Feedback & Transaction Log -->
                    <div class="lg:col-span-7 space-y-4">
                        <!-- Alert Banner Feedback -->
                        <template x-if="lastFeedback">
                            <div :class="lastFeedback.type === 'success' ? 'bg-emerald-950/80 border-emerald-500/40 text-emerald-300' : 'bg-rose-950/80 border-rose-500/40 text-rose-300'" class="p-4 rounded-2xl border flex items-start gap-3 animate-fadeIn">
                                <div class="p-1 rounded-lg" :class="lastFeedback.type === 'success' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-rose-500/20 text-rose-400'">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-show="lastFeedback.type === 'success'" d="M5 13l4 4L19 7"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" x-show="lastFeedback.type !== 'success'" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </div>
                                <div class="flex-1 text-xs">
                                    <strong class="block font-bold mb-0.5" x-text="lastFeedback.type === 'success' ? 'Berhasil' : 'Peringatan / Gagal'"></strong>
                                    <p x-text="lastFeedback.message"></p>
                                </div>
                            </div>
                        </template>

                        <!-- Log Table Sesi Ini -->
                        <div class="bg-gray-950/60 rounded-2xl border border-gray-800/80 p-4 space-y-3">
                            <h4 class="text-xs font-black uppercase text-gray-400 tracking-wider">Log Transaksi Sesi Terminal Ini</h4>
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs">
                                    <thead class="text-gray-500 uppercase border-b border-gray-800 text-[10px]">
                                        <tr>
                                            <th class="pb-2">Waktu</th>
                                            <th class="pb-2">Siswa</th>
                                            <th class="pb-2">Buku</th>
                                            <th class="pb-2 text-right">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-800/60 font-medium">
                                        <template x-for="log in logs" :key="log.id">
                                            <tr>
                                                <td class="py-2.5 text-gray-500 font-mono" x-text="log.time"></td>
                                                <td class="py-2.5 text-white" x-text="log.student_name"></td>
                                                <td class="py-2.5 text-gray-300" x-text="log.book_title"></td>
                                                <td class="py-2.5 text-right">
                                                    <span :class="log.status === 'borrow' ? 'bg-teal-500/10 text-teal-400 border-teal-500/20' : 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20'" class="px-2 py-0.5 rounded text-[9px] font-bold border" x-text="log.status === 'borrow' ? 'PINJAM' : 'KEMBALI'"></span>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="logs.length === 0">
                                            <tr>
                                                <td colspan="4" class="py-6 text-center text-gray-600 italic">Belum ada transaksi di sesi terminal ini.</td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <script>
        function quickTerminal() {
            return {
                mode: 'borrow', // 'borrow' or 'return'
                scanBuffer: '',
                student: null,
                soundEnabled: true,
                lastFeedback: null,
                logs: [],
                logIdCounter: 1,

                init() {
                    this.keepFocus();
                },

                setMode(newMode) {
                    this.mode = newMode;
                    this.scanBuffer = '';
                    this.lastFeedback = null;
                    this.keepFocus();
                },

                keepFocus() {
                    this.$nextTick(() => {
                        if (this.$refs.scanInput) {
                            this.$refs.scanInput.focus();
                        }
                    });
                },

                playBeep(type = 'success') {
                    if (!this.soundEnabled) return;
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        
                        if (type === 'success') {
                            osc.frequency.value = 880; // A5
                            gain.gain.setValueAtTime(0.1, ctx.currentTime);
                            osc.start();
                            osc.stop(ctx.currentTime + 0.15);
                        } else {
                            osc.frequency.value = 300;
                            gain.gain.setValueAtTime(0.15, ctx.currentTime);
                            osc.start();
                            osc.stop(ctx.currentTime + 0.3);
                        }
                    } catch (e) {
                        // AudioContext fallback
                    }
                },

                resetStudent() {
                    this.student = null;
                    this.scanBuffer = '';
                    this.keepFocus();
                },

                handleScanSubmit() {
                    const code = this.scanBuffer.trim();
                    if (!code) return;

                    if (this.mode === 'borrow') {
                        if (!this.student) {
                            // Step 1: Scan Student
                            this.fetchStudent(code);
                        } else {
                            // Step 2: Scan Book
                            this.processBorrow(code);
                        }
                    } else {
                        // Mode Return: Scan Book
                        this.processReturn(code);
                    }
                },

                fetchStudent(code) {
                    fetch("{{ route('library.circulation.scan') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ code: code })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.type === 'student') {
                            this.student = data;
                            this.lastFeedback = {
                                type: 'success',
                                message: 'Siswa ' + data.name + ' berhasil diidentifikasi. Silakan scan barcode buku.'
                            };
                            this.playBeep('success');
                        } else if (data.type === 'book') {
                            this.lastFeedback = {
                                type: 'error',
                                message: 'Harap scan kartu NIS/NFC siswa terlebih dahulu sebelum memindai buku!'
                            };
                            this.playBeep('error');
                        } else {
                            this.lastFeedback = {
                                type: 'error',
                                message: data.error || 'Siswa tidak ditemukan.'
                            };
                            this.playBeep('error');
                        }
                        this.scanBuffer = '';
                        this.keepFocus();
                    })
                    .catch(err => {
                        this.lastFeedback = { type: 'error', message: 'Gagal memproses kode siswa.' };
                        this.playBeep('error');
                        this.scanBuffer = '';
                        this.keepFocus();
                    });
                },

                processBorrow(bookCode) {
                    fetch("{{ route('library.circulation.barcode-borrow') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            student_code: this.student.nis,
                            book_code: bookCode
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.lastFeedback = { type: 'success', message: data.message };
                            this.playBeep('success');
                            
                            // Log
                            this.logs.unshift({
                                id: this.logIdCounter++,
                                time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
                                student_name: data.student_name,
                                book_title: data.book_title,
                                status: 'borrow'
                            });

                            // Increment count
                            this.student.active_loans_count++;
                        } else {
                            this.lastFeedback = { type: 'error', message: data.message };
                            this.playBeep('error');
                        }
                        this.scanBuffer = '';
                        this.keepFocus();
                    })
                    .catch(err => {
                        this.lastFeedback = { type: 'error', message: 'Gagal memproses transaksi peminjaman.' };
                        this.playBeep('error');
                        this.scanBuffer = '';
                        this.keepFocus();
                    });
                },

                processReturn(bookCode) {
                    fetch("{{ route('library.circulation.barcode-return') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ book_code: bookCode })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.lastFeedback = { type: 'success', message: data.message };
                            this.playBeep('success');

                            this.logs.unshift({
                                id: this.logIdCounter++,
                                time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }),
                                student_name: data.student_name,
                                book_title: data.book_title,
                                status: 'return'
                            });
                        } else {
                            this.lastFeedback = { type: 'error', message: data.message };
                            this.playBeep('error');
                        }
                        this.scanBuffer = '';
                        this.keepFocus();
                    })
                    .catch(err => {
                        this.lastFeedback = { type: 'error', message: 'Gagal memproses transaksi pengembalian.' };
                        this.playBeep('error');
                        this.scanBuffer = '';
                        this.keepFocus();
                    });
                }
            };
        }
    </script>
</x-app-layout>
