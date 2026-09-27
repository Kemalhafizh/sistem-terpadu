<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Perpustakaan | {{ $student->user->name }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Gaya Khusus Cetak Kartu */
        @media print {
            body {
                background: white;
                color: black;
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
            .print-area {
                margin: 0;
                box-shadow: none !important;
                border: none !important;
                background: white !important;
                padding: 0 !important;
            }
            .card-container {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }
        }

        /* Desain Holographic Premium */
        .glass-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95) 0%, rgba(240, 253, 244, 0.9) 100%);
            position: relative;
            overflow: hidden;
        }

        .glass-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.05) 0%, transparent 60%);
            pointer-events: none;
            z-index: 1;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">

    <!-- Tombol Cetak & Navigasi (Tidak Ikut Dicetak) -->
    <div class="no-print mb-8 flex gap-3">
        <a href="{{ route('library.index') }}" class="px-5 py-3 bg-white text-gray-800 rounded-2xl font-black text-xs hover:bg-gray-50 border border-gray-200 shadow-sm transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Katalog
        </a>
        <button onclick="window.print()" class="px-5 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-2xl font-black text-xs shadow-lg shadow-primary-200 transition-all flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-3a2 2 0 00-2-2H9a2 2 0 00-2 2v3a2 2 0 002 2zm5-17V4a1 1 0 011-1h2a1 1 0 011 1v3"></path></svg>
            Cetak Kartu (Print)
        </button>
    </div>

    <!-- Area Cetak Kartu -->
    <div class="print-area flex flex-col md:flex-row gap-8 items-center justify-center">
        
        <!-- BAGIAN DEPAN KARTU (Front) -->
        <div class="card-container glass-card w-[350px] h-[220px] rounded-[1.5rem] shadow-xl border border-emerald-100 flex flex-col justify-between p-5 relative select-none">
            <!-- Kop Sekolah -->
            <div class="flex items-center gap-2.5 border-b border-emerald-100/50 pb-2.5 z-10">
                <div class="w-8 h-8 bg-emerald-600 rounded-lg flex items-center justify-center font-black text-white text-base shadow-sm">
                    A
                </div>
                <div>
                    <h2 class="text-[9px] font-black text-gray-800 uppercase tracking-widest leading-none">{{ school_short_name() }}</h2>
                    <p class="text-[7px] font-bold text-emerald-600 uppercase tracking-wider mt-0.5">KARTU ANGGOTA PERPUSTAKAAN</p>
                </div>
            </div>

            <!-- Detail Anggota -->
            <div class="flex items-center gap-4 py-3 z-10">
                <!-- Foto Placeholder / Avatar -->
                <div class="w-16 h-20 bg-gray-50 border border-gray-100 rounded-xl overflow-hidden flex flex-col items-center justify-center p-1 shadow-inner">
                    <svg class="w-10 h-10 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <!-- Biodata -->
                <div class="flex-1 min-w-0">
                    <h3 class="text-xs font-black text-gray-900 leading-tight truncate uppercase">{{ $student->user->name }}</h3>
                    <div class="mt-2 space-y-0.5 text-[8px] font-bold text-gray-400 uppercase tracking-wider">
                        <div>NIS &bull; <span class="text-gray-700 font-black">{{ $student->nis }}</span></div>
                        <div>NISN &bull; <span class="text-gray-700">{{ $student->nisn }}</span></div>
                        <div>Kelas &bull; <span class="text-gray-700">{{ $student->classroom->name ?? '-' }}</span></div>
                    </div>
                </div>
            </div>

            <!-- Footer Kartu -->
            <div class="flex items-center justify-between border-t border-emerald-100/50 pt-2 z-10">
                <span class="text-[6px] font-bold text-gray-400 uppercase tracking-widest">SISTEM INTEGRASI SEKOLAH</span>
                <span class="text-[7px] font-black text-emerald-600 uppercase tracking-wider">SMART CARD</span>
            </div>
        </div>

        <!-- BAGIAN BELAKANG KARTU (Back) -->
        <div class="card-container glass-card w-[350px] h-[220px] rounded-[1.5rem] shadow-xl border border-emerald-100 flex flex-col justify-between p-5 relative select-none">
            <!-- Header Aturan -->
            <div class="border-b border-emerald-100/50 pb-2 z-10">
                <h4 class="text-[8px] font-black text-gray-800 uppercase tracking-widest leading-none">Ketentuan Anggota</h4>
            </div>

            <!-- Isi Aturan & Barcode/QR Code -->
            <div class="flex-1 flex items-center justify-between py-2 z-10">
                <!-- Aturan (Kiri) -->
                <ul class="text-[6.5px] font-bold text-gray-500 space-y-1 max-w-[200px] list-disc pl-3">
                    <li>Kartu ini wajib dibawa saat berkunjung dan melakukan peminjaman buku.</li>
                    <li>Siswa bertanggung jawab penuh atas buku yang dipinjam.</li>
                    <li>Keterlambatan pengembalian buku dikenakan denda sesuai kebijakan sekolah.</li>
                    <li>Kartu tidak dapat dipindahtangankan kepada siswa lain.</li>
                </ul>

                <!-- QR Code (Kanan) -->
                <div class="flex flex-col items-center justify-center pr-2">
                    <div id="qrcode" class="p-1.5 bg-white border border-gray-200 rounded-xl shadow-sm"></div>
                    <div class="text-[7px] font-black text-gray-800 tracking-widest mt-1.5">{{ $student->nis }}</div>
                </div>
            </div>

            <!-- Footer Ttd/Stempel -->
            <div class="flex items-center justify-between border-t border-emerald-100/50 pt-2 z-10">
                <span class="text-[6px] font-bold text-gray-400 uppercase tracking-widest">Validitas Otomatis via QR</span>
                <span class="text-[6px] font-black text-gray-400 uppercase tracking-widest">Kepala Perpustakaan</span>
            </div>
        </div>

    </div>

    <!-- QRCode.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" integrity="sha512-CNgIRecGo7nOMILqofLYLT3ILRSJKfxTMc13YJLOKIyVU9Yu0WaqtVzRTtO9aOPLRODtNy8WMTGs1TLyeeIEGA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        // Generate QR Code untuk NIS Siswa
        window.addEventListener('DOMContentLoaded', () => {
            const qrcodeContainer = document.getElementById("qrcode");
            new QRCode(qrcodeContainer, {
                text: "{{ $student->nis }}",
                width: 70,
                height: 70,
                colorDark: "#064e3b", // Emerald 900
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    </script>
</body>
</html>
