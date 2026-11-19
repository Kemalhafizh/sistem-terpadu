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
                <a href="{{ route('letters.index') }}" class="inline-flex items-center gap-1.5 justify-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span>Kembali</span>
                </a>

                @can('update', $letter)
                    <a href="{{ route('letters.edit', $letter->id) }}" class="inline-flex items-center bg-amber-500 hover:bg-amber-600 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                        Edit Draf
                    </a>
                @endcan

                @can('submit', $letter)
                    <form action="{{ route('letters.submit-approval', $letter->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                            Ajukan Persetujuan Kepsek
                        </button>
                    </form>
                @endcan

                @if($letter->status === 'approved')
                    @can('issue', \App\Models\Letter::class)
                        <form action="{{ route('letters.issue', $letter->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menerbitkan surat ini? Setelah diterbitkan, nomor surat dan QR verifikasi akan otomatis dihasilkan.')" class="bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                                Terbitkan Surat
                            </button>
                        </form>
                    @endcan
                @endif

                <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                    Cetak Langsung
                </button>

                @if($letter->status === 'issued')
                    <a href="{{ route('letters.pdf', $letter->id) }}" class="inline-flex items-center gap-1.5 bg-red-600 hover:bg-red-700 text-white rounded-xl py-2 px-4 text-xs font-bold transition-all shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"></path></svg>
                        <span>Unduh PDF</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <style>
        @media print {
            body {
                background: white !important;
                color: black !important;
            }
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
            .print-sheet {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
            }
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
                    @php
                        $badge = match ($letter->status) {
                            'draft' => ['bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400 border-amber-100 dark:border-amber-900/30', 'DRAF (BELUM DIAJUKAN)'],
                            'pending' => ['bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600 dark:text-indigo-400 border-indigo-100 dark:border-indigo-900/30', 'MENUNGGU PERSETUJUAN KEPSEK'],
                            'revision' => ['bg-orange-50 dark:bg-orange-900/20 text-orange-600 dark:text-orange-400 border-orange-100 dark:border-orange-900/30', 'DIKEMBALIKAN UNTUK REVISI'],
                            'approved' => ['bg-cyan-50 dark:bg-cyan-900/20 text-cyan-700 dark:text-cyan-300 border-cyan-100 dark:border-cyan-900/30', 'DISETUJUI (SIAP DITERBITKAN)'],
                            'issued' => ['bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 border-emerald-100 dark:border-emerald-900/30', 'DITERBITKAN (ISSUED)'],
                            default => ['bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 border-red-100 dark:border-red-900/30', strtoupper($letter->status)],
                        };
                    @endphp
                    <span class="inline-block mt-1 px-2.5 py-1 rounded-lg text-xs font-bold border {{ $badge[0] }}">
                        {{ $badge[1] }}
                    </span>
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
                            <p class="font-extrabold text-sm text-gray-800 dark:text-white">{{ $letter->student->user?->name ?? '-' }}</p>
                            <p class="text-[11px] text-gray-400 mt-1">NISN: {{ $letter->student->nisn }} | Kelas: {{ $letter->student->classroom?->name ?? '-' }}</p>
                        </div>
                    @elseif($letter->employee)
                        <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl border border-gray-200/50 dark:border-gray-700/50">
                            <p class="font-extrabold text-sm text-gray-800 dark:text-white">{{ $letter->employee->user?->name ?? '-' }}</p>
                            <p class="text-[11px] text-gray-400 mt-1">NIP: {{ $letter->employee->nip ?? '-' }} | Jabatan: {{ $letter->employee->position ?? '-' }}</p>
                        </div>
                    @else
                        <p class="text-gray-400 italic mt-0.5">Tidak terikat akun internal</p>
                    @endif
                </div>

                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Dibuat Oleh</span>
                    <p class="font-bold text-gray-900 dark:text-gray-100 mt-0.5">{{ $letter->creator->name }}</p>
                    <p class="text-[11px] text-gray-400">Tanggal Pengarsipan: {{ $letter->created_at->translatedFormat('d M Y H:i') }} WIB</p>
                </div>

                @if($letter->approver)
                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Disetujui Oleh</span>
                    <p class="font-bold text-emerald-700 dark:text-emerald-400 mt-0.5">{{ $letter->approver->name }}</p>
                    @if($letter->approved_at)
                        <p class="text-[11px] text-gray-400">{{ $letter->approved_at->translatedFormat('d M Y H:i') }} WIB</p>
                    @endif
                </div>
                @endif

                @if($letter->workflowRequest)
                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Alur Persetujuan</span>
                    <a href="{{ route('workflows.show', $letter->workflowRequest->id) }}" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Lihat Audit Trail Persetujuan
                    </a>
                </div>
                @endif

                @if($verificationUrl)
                <div class="p-3 bg-emerald-50/70 dark:bg-emerald-950/30 rounded-2xl border border-emerald-200/60 dark:border-emerald-800/40 space-y-2">
                    <span class="block text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">Verifikasi QR Resmi</span>
                    <div id="letter-verification-qr" class="bg-white p-2 rounded-xl inline-block"></div>
                    <p class="text-[10px] text-gray-500 break-all">{{ $verificationUrl }}</p>
                </div>
                @endif

                @if($letter->notes)
                <div>
                    <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider">Catatan Internal</span>
                    <p class="text-gray-700 dark:text-gray-300 italic mt-0.5 p-2 bg-gray-100/50 dark:bg-gray-900/30 rounded-xl border border-gray-200/30 text-xs whitespace-pre-line">{{ $letter->notes }}</p>
                </div>
                @endif

                @can('revoke', $letter)
                <form action="{{ route('letters.revoke', $letter->id) }}" method="POST" class="pt-3 border-t border-gray-100 dark:border-gray-700/50 space-y-2">
                    @csrf
                    <input type="text" name="reason" placeholder="Alasan pembatalan surat (opsional)" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-1.5 text-xs">
                    <button type="submit" onclick="return confirm('Cabut keabsahan surat yang sudah terbit ini?')" class="w-full bg-rose-600 hover:bg-rose-700 text-white rounded-xl py-2 px-3 text-xs font-bold transition-all">
                        Batalkan Surat (Revoke)
                    </button>
                </form>
                @endcan
            </div>
        </div>

        <!-- Render Lembar Surat A4 (Cetak) -->
        <div class="lg:col-span-2 flex justify-center items-start print-sheet-wrapper">
            <div class="print-sheet w-[21cm] min-h-[29.7cm] bg-white text-black p-[2cm] shadow-2xl border border-gray-200 dark:border-transparent rounded font-serif text-[12pt] leading-relaxed relative flex flex-col justify-between">
                <div>
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

                    <div class="text-center mb-8">
                        <h3 class="text-[13pt] uppercase font-bold underline m-0">{{ $letter->template->name }}</h3>
                        <p class="text-[11pt] font-serif m-0 mt-1">Nomor: {{ $letter->letter_number ?? 'DRAF_SURAT_BELUM_DITERBITKAN' }}</p>
                    </div>

                    <div class="text-justify font-serif text-[12pt] leading-relaxed">
                        {!! $renderedBody !!}
                    </div>
                </div>

                <div class="mt-12 flex justify-between items-end">
                    <div class="w-1/2 pr-6">
                        @if($verificationUrl)
                            <div class="border border-gray-300 rounded p-2 text-[9pt] font-sans">
                                <div class="font-bold uppercase text-[8pt] text-gray-700">Verifikasi Keaslian Dokumen</div>
                                <div class="text-[8pt] text-gray-600">Kode: {{ $letter->verification_token }}</div>
                                <div class="text-[7.5pt] text-gray-500 break-all">{{ $verificationUrl }}</div>
                            </div>
                        @endif
                    </div>
                    <div class="w-1/2 text-left pl-10">
                        <p class="m-0">Semarang, {{ $letter->issue_date->translatedFormat('d F Y') }}</p>
                        <p class="m-0 mt-1">{{ $letter->template->signer_title ?? 'Kepala Sekolah' }},</p>

                        <div class="h-[90px] relative my-2">
                            <div class="absolute inset-0 flex items-center justify-start opacity-80">
                                @if($letter->template->signature_path)
                                    <span class="text-[10px] font-sans bg-emerald-500/20 text-emerald-700 px-2.5 py-1 rounded font-bold border border-emerald-500/30 uppercase tracking-widest no-print">TTD DIGITAL TERSEMAT</span>
                                @else
                                    <span class="text-[10px] font-sans bg-gray-100 text-gray-500 px-2 py-1 rounded font-semibold border border-gray-300 uppercase tracking-widest no-print">TTD MANUAL</span>
                                @endif
                            </div>
                        </div>

                        @php
                            $signerName = $letter->template->signer_name ?: ($letter->approver?->name ?: school_principal_name());
                        @endphp
                        <p class="m-0 font-bold underline">{{ signature_name($signerName) }}</p>
                        <p class="m-0 text-sm">{{ signature_nip(school_principal_nip()) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($verificationUrl)
        <x-vendor.script lib="qrcodejs" />
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('letter-verification-qr');
                if (container && typeof QRCode !== 'undefined') {
                    new QRCode(container, {
                        text: @json($verificationUrl),
                        width: 112,
                        height: 112,
                        correctLevel: QRCode.CorrectLevel.M,
                    });
                }
            });
        </script>
    @endif
</x-app-layout>
