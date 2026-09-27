<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    Pembubuhan Tanda Tangan Digital (E-Stamp)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Verifikasi pratinjau dokumen sebelum pembubuhan e-stamp ber-kriptografi SHA-256.</p>
            </div>
            <a href="{{ route('documents.pending_signatures') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Antrean
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto space-y-6">

            <!-- Document Details Card -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-6">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-4 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-600 uppercase font-mono tracking-wider block">Dokumen Resmi Institusi</span>
                        <h3 class="text-xl font-black text-gray-900 dark:text-white mt-1">{{ $signature->document_type }} #{{ $signature->document_id }}</h3>
                    </div>
                    <span class="px-3 py-1 bg-slate-100 dark:bg-gray-900 font-mono text-xs font-bold rounded-xl text-slate-700 dark:text-gray-300">
                        {{ $signature->qr_verification_code }}
                    </span>
                </div>

                <!-- Digital Stamp Mockup Box -->
                <div class="p-8 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 text-center space-y-4">
                    <div class="w-24 h-24 mx-auto bg-indigo-500/10 border-2 border-indigo-500 rounded-full flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-mono text-[10px] font-bold uppercase p-2 leading-tight">
                        E-STAMP VERIFIED SISTA YBWSA
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</h4>
                        <p class="text-xs font-mono text-gray-500">{{ $signature->signer_role }} — {{ school_name() }}</p>
                        <p class="text-[11px] font-mono text-indigo-600 dark:text-indigo-400">Algoritma: SHA-256 Cryptographic Digest + QR Verification Code</p>
                    </div>
                </div>

                <!-- Sign Execution Form -->
                <form action="{{ route('documents.sign', $signature->id) }}" method="POST" class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700" onsubmit="return confirm('PERNYATAAN RESMI: Apakah Anda yakin ingin membubuhi Tanda Tangan Digital (E-Stamp) secara sah pada dokumen ini?')">
                    @csrf
                    <div class="bg-indigo-50 dark:bg-indigo-950/40 p-4 rounded-xl text-xs text-indigo-900 dark:text-indigo-200 border border-indigo-200 dark:border-indigo-800">
                        <p class="font-bold">Landasan Hukum Tanda Tangan Digital:</p>
                        <p class="mt-1">Pembubuhan tanda tangan ini mengikat secara hukum berdasarkan UU ITE No. 11/2008 & UU No. 19/2016. Hash unik SHA-256 dan IP Address ({{ request()->ip() }}) akan dicatat secara permanen pada ledger audit institusi.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-lg shadow-emerald-600/30 transition-all flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Bubuhi Tanda Tangan Digital Sekarang
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </div>
</x-app-layout>
