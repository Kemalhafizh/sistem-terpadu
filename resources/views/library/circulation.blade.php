<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Sirkulasi Perpustakaan') }}
            </h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('library.manage') }}" class="px-4 py-2 bg-gray-100 text-gray-800 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all">Manajemen Buku</a>
                <a href="{{ route('library.index') }}" class="px-4 py-2 bg-gray-900 text-white rounded-xl font-bold text-xs hover:bg-black transition-all">Katalog Perpus</a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            <!-- Alert Section -->
            <div id="alert-container" class="hidden"></div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- PANEL SCANNER & INPUT MANUAL (Kiri) -->
                <div class="lg:col-span-1 space-y-6">
                    <!-- Scanner Kamera -->
                    <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden relative">
                        <div class="absolute top-4 right-4 flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 bg-emerald-500 rounded-full animate-ping"></span>
                            <span class="text-[10px] font-black text-gray-400 uppercase tracking-wider">Kamera Aktif</span>
                        </div>
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Pemindai Kamera (QR/Barcode)</h3>
                        
                        <div class="relative bg-gray-50 rounded-3xl overflow-hidden aspect-video border border-gray-200 flex flex-col items-center justify-center p-2 mb-4">
                            <div id="reader" class="w-full h-full object-cover"></div>
                            <div id="reader-placeholder" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-950 text-white text-center p-4">
                                <svg class="w-12 h-12 text-gray-400 mb-3 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h.01M16 16h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span class="text-xs font-bold mb-1">Kamera Dinonaktifkan</span>
                                <p class="text-[10px] text-gray-500 max-w-[200px]">Tekan Mulai untuk mengaktifkan kamera pemindai</p>
                                <button onclick="startScanner()" class="mt-3 px-4 py-2 bg-primary-600 text-white rounded-xl text-[10px] font-black hover:bg-primary-700 transition-all">MULAI SCAN</button>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button id="btn-stop-scan" onclick="stopScanner()" class="hidden w-full py-2.5 bg-red-50 text-red-600 rounded-xl text-xs font-black text-center hover:bg-red-100 transition-all">HENTIKAN</button>
                        </div>
                    </div>

                    <!-- Input Kode Manual -->
                    <div class="bg-white p-6 rounded-[2.5rem] shadow-sm border border-gray-100">
                        <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider mb-4">Input Kode Manual</h3>
                        <form id="manual-scan-form" onsubmit="handleManualScan(event)">
                            <div class="flex gap-2">
                                <input type="text" id="manual-code" class="flex-1 px-4 py-3 rounded-2xl border-gray-200 bg-gray-50 text-sm focus:bg-white transition-all focus:ring-2 focus:ring-primary-500" placeholder="Ketik NIS Siswa atau Kode Buku...">
                                <button type="submit" class="px-5 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl font-black text-xs transition-all shadow-md shadow-primary-200">CARI</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- DETAIL TRANSAKSI & FLOW SIRKULASI (Kanan) -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="bg-white p-8 rounded-[2.5rem] shadow-sm border border-gray-100 min-h-[400px] flex flex-col">
                        <!-- State Kosong (Belum Scan) -->
                        <div id="circulation-empty-state" class="flex-1 flex flex-col items-center justify-center text-center py-10">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"></path></svg>
                            </div>
                            <h4 class="text-lg font-black text-gray-800">Menunggu Pemindaian</h4>
                            <p class="text-sm text-gray-400 max-w-sm mt-1">Gunakan kamera pemindai di sebelah kiri atau ketik manual untuk mencari kartu siswa (NIS) atau barcode buku perpustakaan.</p>
                        </div>

                        <!-- State Detail Siswa Ter-Scan -->
                        <div id="student-detail-state" class="hidden space-y-6 flex-1">
                            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 bg-primary-50 text-primary-600 rounded-2xl flex items-center justify-center font-black text-lg">
                                        <span id="student-initials">S</span>
                                    </div>
                                    <div>
                                        <h3 id="student-name" class="font-black text-lg text-gray-900 leading-tight">Nama Siswa</h3>
                                        <p class="text-xs font-bold text-gray-400 uppercase"><span id="student-nis">NIS</span> &bull; <span id="student-class">Kelas</span></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span id="student-quota-badge" class="px-3 py-1.5 bg-emerald-50 text-emerald-600 text-xs font-black rounded-xl">0 / 3 Buku</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Form Peminjaman Baru -->
                                <div class="bg-gray-50 p-6 rounded-3xl border border-gray-100 space-y-4">
                                    <h4 class="text-xs font-black text-gray-800 uppercase tracking-widest">Pinjamkan Buku Fisik Baru</h4>
                                    
                                    <!-- Input Buku Ter-Scan -->
                                    <div class="space-y-3">
                                        <div id="book-selected-panel" class="hidden p-4 bg-white border border-emerald-100 rounded-2xl flex items-center justify-between">
                                            <div>
                                                <h5 id="selected-book-title" class="text-xs font-black text-gray-900 leading-tight">Judul Buku</h5>
                                                <p id="selected-book-author" class="text-[10px] text-gray-400 font-bold uppercase mt-0.5">Penulis</p>
                                            </div>
                                            <button onclick="clearSelectedBook()" class="text-red-500 hover:text-red-700">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            </button>
                                        </div>
                                        <div id="book-unselected-panel" class="p-4 bg-white border border-gray-200 border-dashed rounded-2xl text-center py-6">
                                            <p class="text-xs text-gray-400">Silakan scan kode QR/Barcode buku, atau masukkan kode manual buku.</p>
                                        </div>
                                    </div>

                                    <!-- Form Set Tanggal & Pinjam -->
                                    <form id="borrow-submit-form" onsubmit="submitBorrowForm(event)" class="space-y-4">
                                        <input type="hidden" name="student_id" id="borrow-student-id">
                                        <input type="hidden" name="library_book_id" id="borrow-book-id">

                                        <!-- Opsi Tipe Batas Waktu -->
                                        <div>
                                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2">Tipe Batas Waktu Peminjaman</label>
                                            <select name="due_date_type" id="due-date-type" onchange="toggleDueDateInput(this.value)" class="w-full px-4 py-3 rounded-2xl border-gray-200 bg-white text-xs focus:ring-2 focus:ring-primary-500 font-bold">
                                                <option value="regular" selected>Buku Bacaan Umum (7 Hari)</option>
                                                <option value="package">Buku Paket Pelajaran (Akhir Tahun Ajaran: {{ $currentAcademicYear?->end_date ? \Carbon\Carbon::parse($currentAcademicYear->end_date)->format('d-m-Y') : '1 Semester' }})</option>
                                                <option value="custom">Kustom Tanggal Pilihan</option>
                                            </select>
                                        </div>

                                        <!-- Input Tanggal Kustom (Hidden by default) -->
                                        <div id="custom-due-date-panel" class="hidden">
                                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-2">Pilih Tanggal Pengembalian</label>
                                            <input type="date" name="custom_due_date" id="custom-due-date" class="w-full px-4 py-3 rounded-2xl border-gray-200 bg-white text-xs focus:ring-2 focus:ring-primary-500 font-bold" min="{{ date('Y-m-d') }}">
                                        </div>

                                        <button type="submit" id="btn-submit-borrow" disabled class="w-full py-3.5 bg-gray-200 text-gray-400 rounded-2xl font-black text-xs tracking-widest uppercase transition-all shadow-md">KONFIRMASI PINJAM</button>
                                    </form>
                                </div>

                                <!-- Daftar Buku Sedang Dipinjam Siswa -->
                                <div class="space-y-4">
                                    <h4 class="text-xs font-black text-gray-800 uppercase tracking-widest">Buku Fisik yang Sedang Dipinjam</h4>
                                    
                                    <div id="student-loans-container" class="space-y-3 max-h-[250px] overflow-y-auto pr-1">
                                        <!-- Looping akan diisi secara AJAX -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- MONITORING MONITOR TABEL PEMINJAMAN (Bawah) -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="border-b border-gray-100 p-6 flex flex-wrap items-center justify-between gap-4 bg-gray-50/50">
                    <div>
                        <h3 class="font-black text-lg text-gray-900 leading-tight">Monitoring Aktivitas Sirkulasi</h3>
                        <p class="text-xs font-bold text-gray-400 uppercase">Daftar seluruh transaksi peminjaman buku fisik sekolah</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 text-[10px] font-black text-gray-400 uppercase tracking-widest bg-gray-50/20">
                                <th class="p-6">Siswa</th>
                                <th class="p-6">Buku</th>
                                <th class="p-6">Tanggal Pinjam</th>
                                <th class="p-6">Jatuh Tempo</th>
                                <th class="p-6">Pengembalian</th>
                                <th class="p-6">Denda</th>
                                <th class="p-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeLoans as $loan)
                                <tr class="border-b border-gray-100 text-xs hover:bg-gray-50/50 transition-all">
                                    <td class="p-6">
                                        <div class="font-black text-gray-900">{{ $loan->student->user->name ?? '-' }}</div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">{{ $loan->student->nis }}</div>
                                    </td>
                                    <td class="p-6">
                                        <div class="font-black text-gray-900 leading-tight">{{ $loan->book->title }}</div>
                                        <div class="text-[9px] font-bold text-gray-400 uppercase mt-0.5">{{ $loan->book->author ?? 'Sekolah' }}</div>
                                    </td>
                                    <td class="p-6 font-bold text-gray-500">{{ $loan->borrowed_at->format('d-m-Y H:i') }}</td>
                                    <td class="p-6 font-bold {{ $loan->due_at->isPast() && !$loan->returned_at ? 'text-red-500 animate-pulse' : 'text-gray-500' }}">
                                        {{ $loan->due_at->format('d-m-Y') }}
                                    </td>
                                    <td class="p-6">
                                        @if($loan->returned_at)
                                            <span class="px-2.5 py-1 bg-gray-100 text-gray-600 rounded-lg text-[9px] font-black uppercase">DIKEMBALIKAN</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-blue-50 text-blue-600 rounded-lg text-[9px] font-black uppercase">SEDANG DIPINJAM</span>
                                        @endif
                                    </td>
                                    <td class="p-6 font-bold text-gray-900">
                                        @if($loan->fine_amount > 0)
                                            <div class="text-red-600">Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}</div>
                                            @if($loan->fine_paid_at)
                                                <div class="text-[8px] text-emerald-600 uppercase font-black tracking-widest mt-0.5">LUNAS &bull; {{ $loan->fine_paid_at->format('d-m-Y') }}</div>
                                            @else
                                                <div class="text-[8px] text-red-400 uppercase font-black tracking-widest mt-0.5">BELUM DIBAYAR</div>
                                            @endif
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="p-6 text-right">
                                        <div class="flex justify-end gap-2">
                                            @if(!$loan->returned_at)
                                                <button onclick="confirmReturn('{{ $loan->id }}', '{{ $loan->book->title }}', {{ $loan->calculateFine() }})" class="px-3 py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-xl text-[10px] font-black transition-all">Kembalikan</button>
                                            @elseif($loan->fine_amount > 0 && !$loan->fine_paid_at)
                                                <button onclick="confirmPayFine('{{ $loan->id }}', {{ $loan->fine_amount }})" class="px-3 py-1.5 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white rounded-xl text-[10px] font-black transition-all">Bayar Denda</button>
                                            @else
                                                <span class="text-[10px] font-bold text-gray-400 uppercase italic">Selesai</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="p-12 text-center text-gray-400">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3">
                                            <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        </div>
                                        <div class="font-black text-sm">Tidak Ada Transaksi Aktif</div>
                                        <div class="text-xs">Sirkulasi perpustakaan kosong saat ini.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($activeLoans->hasPages())
                    <div class="p-6 border-t border-gray-100">
                        {{ $activeLoans->appends(['all_page' => $allLoans->currentPage()])->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Html5-Qrcode CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js" integrity="sha512-r6rDA7W6ZeQhvl8S7yRV0VUkyIEw3wQe5KAD1Y5421Y14MpHtV9cxHfZudzECFde0zTy5r4a76tKz+dIBAO3+g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let html5QrcodeScanner = null;
        let selectedStudent = null;
        let selectedBook = null;

        // CSRF Token Setup untuk AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // --- SCANNER LOGIC ---
        function startScanner() {
            document.getElementById('reader-placeholder').classList.add('hidden');
            document.getElementById('btn-stop-scan').classList.remove('hidden');

            html5QrcodeScanner = new Html5Qrcode("reader");
            
            // Konfigurasi scan kamera
            const config = { 
                fps: 10, 
                qrbox: function(width, height) {
                    return {
                        width: Math.min(width * 0.7, 250),
                        height: Math.min(height * 0.7, 250)
                    };
                }
            };

            html5QrcodeScanner.start(
                { facingMode: "environment" }, 
                config,
                onScanSuccess
            ).catch(err => {
                showToast('Gagal mengakses kamera.', 'error');
                stopScanner();
            });
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    html5QrcodeScanner = null;
                    document.getElementById('reader-placeholder').classList.remove('hidden');
                    document.getElementById('btn-stop-scan').classList.add('hidden');
                }).catch(err => {
                    console.error("Gagal menghentikan scanner: ", err);
                });
            }
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Mainkan audio scan pendek
            try {
                let beep = new AudioContext();
                let osc = beep.createOscillator();
                let gain = beep.createGain();
                osc.connect(gain);
                gain.connect(beep.destination);
                osc.frequency.value = 880;
                osc.type = "sine";
                gain.gain.setValueAtTime(0.1, beep.currentTime);
                osc.start();
                osc.stop(beep.currentTime + 0.1);
            } catch(e) {}

            processScannedCode(decodedText);
        }

        // --- PROCESSING SCAN OR MANUAL CODE ---
        function handleManualScan(e) {
            e.preventDefault();
            const codeInput = document.getElementById('manual-code');
            const code = codeInput.value.trim();
            if (code === '') return;

            processScannedCode(code);
            codeInput.value = '';
        }

        function processScannedCode(code) {
            fetch("{{ route('library.circulation.scan') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify({ code: code })
            })
            .then(res => {
                if (!res.ok) {
                    return res.json().then(err => { throw new Error(err.error || 'Terjadi kesalahan') });
                }
                return res.json();
            })
            .then(data => {
                if (data.type === 'student') {
                    loadStudentDetail(data);
                    showToast(`Siswa "${data.name}" ter-scan!`, 'success');
                } else if (data.type === 'book') {
                    if (selectedStudent) {
                        selectBookForBorrow(data);
                        showToast(`Buku "${data.title}" ter-scan!`, 'success');
                    } else {
                        Swal.fire({
                            title: 'Pindai Siswa Dahulu',
                            text: `Buku "${data.title}" terdeteksi, silakan scan kartu anggota Siswa terlebih dahulu untuk memproses peminjaman.`,
                            icon: 'warning',
                            confirmButtonColor: '#10b981'
                        });
                    }
                }
            })
            .catch(err => {
                Swal.fire({
                    title: 'Kesalahan Scan',
                    text: err.message,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
            });
        }

        // --- RENDER DETAIL SISWA ---
        function loadStudentDetail(student) {
            selectedStudent = student;
            document.getElementById('circulation-empty-state').classList.add('hidden');
            
            const detailPanel = document.getElementById('student-detail-state');
            detailPanel.classList.remove('hidden');

            // Render Info Siswa
            document.getElementById('student-name').innerText = student.name;
            document.getElementById('student-nis').innerText = student.nis;
            document.getElementById('student-class').innerText = student.class_name;
            document.getElementById('student-initials').innerText = student.name.substring(0, 2).toUpperCase();
            
            // Atur Kuota Peminjaman
            const quotaBadge = document.getElementById('student-quota-badge');
            quotaBadge.innerText = `${student.active_loans_count} / 3 Buku`;
            if (student.active_loans_count >= 3) {
                quotaBadge.className = "px-3 py-1.5 bg-red-50 text-red-600 text-xs font-black rounded-xl";
            } else {
                quotaBadge.className = "px-3 py-1.5 bg-emerald-50 text-emerald-600 text-xs font-black rounded-xl";
            }

            document.getElementById('borrow-student-id').value = student.id;

            // Render Riwayat Pinjaman Aktif Siswa
            const loansContainer = document.getElementById('student-loans-container');
            loansContainer.innerHTML = '';

            if (student.active_loans.length === 0) {
                loansContainer.innerHTML = `
                    <div class="p-6 bg-white border border-gray-100 rounded-2xl text-center text-xs text-gray-400">
                        Siswa tidak memiliki peminjaman aktif saat ini.
                    </div>
                `;
            } else {
                student.active_loans.forEach(loan => {
                    const overdueBadge = loan.is_overdue 
                        ? `<div class="text-[8px] text-red-500 font-black uppercase mt-1 animate-pulse">OVERDUE (Denda: Rp ${loan.fine})</div>`
                        : '';
                    
                    loansContainer.innerHTML += `
                        <div class="p-4 bg-white border ${loan.is_overdue ? 'border-red-100 bg-red-50/10' : 'border-gray-100'} rounded-2xl flex items-center justify-between">
                            <div class="flex-1 min-w-0 pr-4">
                                <h5 class="text-xs font-black text-gray-900 leading-tight truncate">${loan.title}</h5>
                                <p class="text-[9px] text-gray-400 font-bold uppercase mt-0.5">Jatuh Tempo: ${loan.due_at}</p>
                                ${overdueBadge}
                            </div>
                            <button onclick="confirmReturn('${loan.id}', '${loan.title}', ${loan.fine})" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white rounded-lg text-[9px] font-black transition-all">Kembalikan</button>
                        </div>
                    `;
                });
            }

            clearSelectedBook();
        }

        // --- RENDER BUKU PILIHAN UNTUK DIPINJAM ---
        function selectBookForBorrow(book) {
            if (!book.is_available) {
                Swal.fire({
                    title: 'Buku Tidak Tersedia',
                    text: `Buku "${book.title}" saat ini sedang dipinjam oleh siswa lain atau tidak tersedia.`,
                    icon: 'error',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            selectedBook = book;
            document.getElementById('book-unselected-panel').classList.add('hidden');
            
            const selectedPanel = document.getElementById('book-selected-panel');
            selectedPanel.classList.remove('hidden');
            document.getElementById('selected-book-title').innerText = book.title;
            document.getElementById('selected-book-author').innerText = book.author;
            
            document.getElementById('borrow-book-id').value = book.id;

            // Enable tombol borrow jika kuota mencukupi
            const btnBorrow = document.getElementById('btn-submit-borrow');
            if (selectedStudent && selectedStudent.active_loans_count < 3) {
                btnBorrow.removeAttribute('disabled');
                btnBorrow.className = "w-full py-3.5 bg-primary-600 text-white rounded-2xl font-black text-xs tracking-widest uppercase transition-all shadow-md shadow-primary-200 hover:bg-primary-700 cursor-pointer";
            } else {
                btnBorrow.setAttribute('disabled', 'true');
                btnBorrow.className = "w-full py-3.5 bg-gray-200 text-gray-400 rounded-2xl font-black text-xs tracking-widest uppercase transition-all cursor-not-allowed";
            }
        }

        function clearSelectedBook() {
            selectedBook = null;
            document.getElementById('book-selected-panel').classList.add('hidden');
            document.getElementById('book-unselected-panel').classList.remove('hidden');
            document.getElementById('borrow-book-id').value = '';

            const btnBorrow = document.getElementById('btn-submit-borrow');
            btnBorrow.setAttribute('disabled', 'true');
            btnBorrow.className = "w-full py-3.5 bg-gray-200 text-gray-400 rounded-2xl font-black text-xs tracking-widest uppercase transition-all cursor-not-allowed";
        }

        // --- FORM LOGIC TANGGAL JATUH TEMPO ---
        function toggleDueDateInput(type) {
            const panel = document.getElementById('custom-due-date-panel');
            const input = document.getElementById('custom-due-date');
            
            if (type === 'custom') {
                panel.classList.remove('hidden');
                input.setAttribute('required', 'true');
            } else {
                panel.classList.add('hidden');
                input.removeAttribute('required');
            }
        }

        // --- SUBMIT PINJAM BUKU ---
        function submitBorrowForm(e) {
            e.preventDefault();
            if (!selectedStudent || !selectedBook) return;

            const form = document.getElementById('borrow-submit-form');
            const formData = new FormData(form);
            
            const payload = {
                student_id: formData.get('student_id'),
                library_book_id: formData.get('library_book_id'),
                due_date_type: formData.get('due_date_type'),
                custom_due_date: formData.get('custom_due_date')
            };

            fetch("{{ route('library.circulation.borrow') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil Dipinjam!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        location.reload(); // Reload untuk memperbarui dashboard sirkulasi
                    });
                } else {
                    Swal.fire({
                        title: 'Gagal Meminjam',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Koneksi bermasalah.', 'error');
            });
        }

        // --- PROSES KEMBALI BUKU ---
        function confirmReturn(loanId, bookTitle, fineAmount) {
            let confirmText = `Apakah Anda yakin ingin mengembalikan buku "${bookTitle}"?`;
            let confirmIcon = 'question';

            if (fineAmount > 0) {
                confirmText = `Buku "${bookTitle}" terlambat dikembalikan! Siswa dikenai denda sebesar Rp ${fineAmount.toLocaleString('id-ID')}.`;
                confirmIcon = 'warning';
            }

            Swal.fire({
                title: 'Konfirmasi Pengembalian',
                text: confirmText,
                icon: confirmIcon,
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Kembalikan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    executeReturn(loanId);
                }
            });
        }

        function executeReturn(loanId) {
            fetch(`/library/circulation/return/${loanId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.fine_amount > 0) {
                        Swal.fire({
                            title: 'Buku Dikembalikan (Ada Denda)',
                            text: data.message,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            cancelButtonColor: '#6b7280',
                            confirmButtonText: 'Bayar Denda Sekarang',
                            cancelButtonText: 'Tutup'
                        }).then((resFine) => {
                            if (resFine.isConfirmed) {
                                executePayFine(loanId);
                            } else {
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Pengembalian Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonColor: '#10b981'
                        }).then(() => {
                            location.reload();
                        });
                    }
                } else {
                    Swal.fire({
                        title: 'Pengembalian Gagal',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Koneksi bermasalah.', 'error');
            });
        }

        // --- PROSES PELUNASAN DENDA ---
        function confirmPayFine(loanId, fineAmount) {
            Swal.fire({
                title: 'Pelunasan Denda',
                text: `Apakah Anda yakin ingin membayar lunas denda keterlambatan sebesar Rp ${fineAmount.toLocaleString('id-ID')}? Transaksi akan langsung dicatatkan ke Kas Keuangan sekolah.`,
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Bayar Lunas!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    executePayFine(loanId);
                }
            });
        }

        function executePayFine(loanId) {
            fetch(`/library/circulation/pay-fine/${loanId}`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Denda Dilunasi!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonColor: '#10b981'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        title: 'Pembayaran Gagal',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(err => {
                console.error(err);
                showToast('Koneksi bermasalah.', 'error');
            });
        }

        // --- HELPER TOAST NOTIFIKASI ---
        function showToast(message, type = 'info') {
            const colors = {
                success: 'bg-emerald-500',
                error: 'bg-red-500',
                info: 'bg-blue-500',
                warning: 'bg-yellow-500'
            };
            
            const toast = document.createElement('div');
            toast.className = `fixed bottom-5 right-5 z-50 text-white text-xs font-black px-5 py-3.5 rounded-2xl shadow-xl transition-all duration-300 transform translate-y-10 opacity-0 ${colors[type] || colors.info}`;
            toast.innerText = message;
            
            document.body.appendChild(toast);
            
            // Animate In
            setTimeout(() => {
                toast.classList.remove('translate-y-10', 'opacity-0');
            }, 10);
            
            // Animate Out & Remove
            setTimeout(() => {
                toast.classList.add('translate-y-10', 'opacity-0');
                setTimeout(() => {
                    toast.remove();
                }, 300);
            }, 4000);
        }
    </script>
</x-app-layout>
