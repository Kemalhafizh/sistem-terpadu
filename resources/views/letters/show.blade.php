<x-app-layout>
    <x-slot name="title">Detail Surat Resmi</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 no-print">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Rincian Dokumen Surat
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $letter->letter_number ?? 'Draf Surat (Belum Diterbitkan)' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('letters.index') }}" class="inline-flex items-center justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                    ⬅ Kembali
                </a>
                
                @if($letter->status === 'draft' && in_array(auth()->user()->role, ['staf_tu', 'admin', 'superadmin']))
                    <form action="{{ route('letters.issue', $letter->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menerbitkan surat ini? Setelah diterbitkan, nomor surat akan otomatis dihasilkan dan surat tidak dapat diubah lagi.')" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                             Terbitkan Surat
                        </button>
                    </form>
                @endif

                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                     Cetak Langsung
                </button>

                @if($letter->status === 'issued')
                    <a href="{{ route('letters.pdf', $letter->id) }}" class="bg-red-600 hover:bg-red-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                        ⬇ Unduh PDF
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <!-- Custom Print Stylesheet -->
    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
            /* Hide everything except the print wrapper */
            body > *:not(.print-wrapper) {
                display: none !important;
            }
            #main-content {
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
            }
            .no-print {
                display: none !important;
            }
            /* Reset absolute sizes for physical page printing */
            .print-sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
            }
            /* Reset layout templates */
            header, nav, aside, .sidebar-wrapper, .navigation-wrapper {
                display: none !important;
            }
        }
    </style>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start print-wrapper">
        <!-- Panel Informasi / Log Aktivitas -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl space-y-4 no-print">
            <h4 class="text-sm font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider border-b border-gray-100 dark:border-gray-700/50 pb-2">
                 Detail Administrasi
            </h4>

            <div class="space-y-3 text-sm">
                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Status Dokumen</span>
                    @if($letter->status === 'draft')
                        <span class="inline-block mt-1 px-2.5 py-1 bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 rounded-lg text-xs font-bold border border-amber-100 dark:border-amber-900/30">
                            DRAF (BELUM DITERBITKAN)
                        </span>
                    @elseif($letter->status === 'issued')
                        <span class="inline-block mt-1 px-2.5 py-1 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 rounded-lg text-xs font-bold border border-emerald-100 dark:border-emerald-900/30">
                            DITERBITKAN (ISSUED)
                        </span>
                    @else
                        <span class="inline-block mt-1 px-2.5 py-1 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg text-xs font-bold border border-red-100 dark:border-red-900/30">
                            DIBATALKAN (REVOKED)
                        </span>
                    @endif
                </div>

                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Tipe Template</span>
                    <p class="font-bold text-gray-900 dark:text-gray-100 mt-0.5">{{ $letter->template->name }}</p>
                </div>

                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Tanggal Terbit</span>
                    <p class="font-bold text-gray-900 dark:text-gray-100 mt-0.5">{{ $letter->issue_date->translatedFormat('d F Y') }}</p>
                </div>

                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Penerima Manfaat</span>
                    @if($letter->student)
                        <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                            <p class="font-extrabold text-sm text-gray-800 dark:text-white"> {{ $letter->student->name }}</p>
                            <p class="text-[11px] text-gray-400 mt-1">NISN: {{ $letter->student->nisn }} | Kelas: {{ $letter->student->classroom?->name ?? '-' }}</p>
                        </div>
                    @elseif($letter->employee)
                        <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                            <p class="font-extrabold text-sm text-gray-800 dark:text-white"> {{ $letter->employee->name }}</p>
                            <p class="text-[11px] text-gray-400 mt-1">NIP: {{ $letter->employee->nip ?? '-' }} | Jabatan: {{ $letter->employee->position ?? '-' }}</p>
                        </div>
                    @else
                        <p class="text-gray-400 italic mt-0.5">Tidak terikat akun internal</p>
                    @endif
                </div>

                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Dibuat Oleh</span>
                    <p class="font-bold text-gray-900 dark:text-gray-100 mt-0.5"> {{ $letter->creator->name }}</p>
                    <p class="text-[11px] text-gray-400">Tanggal Pengarsipan: {{ $letter->created_at->translatedFormat('d M Y H:i') }} WIB</p>
                </div>

                @if($letter->notes)
                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Catatan Internal</span>
                    <p class="text-gray-700 dark:text-gray-300 italic mt-0.5 p-2 bg-gray-100/50 dark:bg-gray-900/30 rounded-xl border border-gray-200/30 text-xs">{{ $letter->notes }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Render Lembar Surat A4 (Cetak) -->
        <div class="lg:col-span-2 flex justify-center items-start print-sheet-wrapper">
            <div class="print-sheet w-[21cm] min-h-[29.7cm] bg-white text-black p-[2cm] shadow-2xl border border-gray-200 dark:border-transparent rounded font-serif text-[12pt] leading-relaxed relative flex flex-col justify-between">
                
                <!-- Content Top -->
                <div>
                    <!-- Kop Surat Resmi -->
                    @if($letter->template->header_html)
                        {!! $letter->template->header_html !!}
                    @else
                        <div class="flex border-b-4 border-double border-black pb-3 mb-6 items-center">
                            <div class="w-[80px] text-center pr-4">
                                <div class="w-[60px] h-[60px] bg-gray-100 border border-gray-400 rounded-full flex items-center justify-center text-[10px] font-sans font-bold">LOGO</div>
                            </div>
                            <div class="flex-grow text-center">
                                <h2 class="m-0 text-[14pt] uppercase font-bold">{{ school_foundation_name(false) }}</h2>
                                <h1 class="m-0 text-[16pt] uppercase font-bold tracking-wide">{{ school_short_name() }}</h1>
                                @if(school_accreditation())<p class="m-0 text-[9pt] italic leading-tight">Terakreditasi {{ school_accreditation() }}</p>@endif
                                <p class="m-0 text-[9pt] italic leading-tight">{{ school_contact_line() }}</p>
                            </div>
                        </div>
                    @endif

                    <!-- Judul & Nomor Surat -->
                    <div class="text-center mb-8">
                        <h3 class="text-[13pt] uppercase font-bold underline m-0">{{ $letter->template->name }}</h3>
                        <p class="text-[11pt] font-serif m-0 mt-1">Nomor: {{ $letter->letter_number ?? 'DRAF_SURAT_BELUM_DITERBITKAN' }}</p>
                    </div>

                    <!-- Badan Surat Hasil Render -->
                    <div class="text-justify font-serif text-[12pt] leading-relaxed">
                        {!! $renderedBody !!}
                    </div>
                </div>

                <!-- Content Bottom (Tanda Tangan) -->
                <div class="mt-12 flex justify-between">
                    <div class="w-1/2"></div>
                    <div class="w-1/2 text-left pl-10">
                        <p class="m-0">Semarang, {{ $letter->issue_date->translatedFormat('d F Y') }}</p>
                        <p class="m-0 mt-1">{{ $letter->template->signer_title ?? 'Kepala Sekolah' }},</p>
                        
                        <!-- Digital Signature Scan Placeholder -->
                        <div class="h-[90px] relative my-2">
                            <!-- In-app secure signature view if exists -->
                            <div class="absolute inset-0 flex items-center justify-start opacity-80">
                                @if($letter->template->signature_path)
                                    <span class="text-[10px] font-sans bg-emerald-500/20 text-emerald-700 px-2.5 py-1 rounded font-bold border border-emerald-500/30 uppercase tracking-widest no-print">TTD DIGITAL TERSEMAT</span>
                                @else
                                    <span class="text-[10px] font-sans bg-gray-100 text-gray-500 px-2 py-1 rounded font-semibold border border-gray-300 uppercase tracking-widest no-print">TTD MANUAL</span>
                                @endif
                            </div>
                        </div>

                        <p class="m-0 font-bold underline">{{ signature_name($letter->template->signer_name ?: school_principal_name()) }}</p>
                        <p class="m-0 text-sm">NIP. {{ $letter->employee ? ($letter->employee->nip ?? '-') : '-' }}</p>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</x-app-layout>
