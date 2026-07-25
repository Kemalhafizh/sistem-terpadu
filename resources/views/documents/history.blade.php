<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    Riwayat Audit Tanda Tangan Digital & E-Stamp Log
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Log audit kriptografi permanen penandatanganan dokumen resmi institusi.</p>
            </div>
            <a href="{{ route('documents.pending_signatures') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Antrean Dokumen
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- History Audit Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Daftar Dokumen Ter-Tanda Tangan Sah</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih tautan verifikasi publik untuk mensimulasikan hasil pemindaian QR Code.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Kode QR Verifikasi</th>
                                <th class="px-6 py-4">Tipe Dokumen</th>
                                <th class="px-6 py-4">Nama Penandatangan</th>
                                <th class="px-6 py-4">Jabatan</th>
                                <th class="px-6 py-4">Cryptographic SHA-256 Hash</th>
                                <th class="px-6 py-4">Waktu Tanda Tangan</th>
                                <th class="px-6 py-4 text-right">Verifikasi Publik</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($history as $sig)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">
                                        {{ $sig->qr_verification_code }}
                                    </td>
                                    <td class="px-6 py-4 font-bold">{{ $sig->document_type }} #{{ $sig->document_id }}</td>
                                    <td class="px-6 py-4 font-bold">{{ optional($sig->signer)->name ?? 'Pejabat Berwenang' }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500">{{ $sig->signer_role }}</td>
                                    <td class="px-6 py-4 font-mono text-[10px] text-gray-400">
                                        <span class="bg-slate-100 dark:bg-gray-900 px-2 py-1 rounded max-w-[140px] truncate inline-block" title="{{ $sig->signature_hash }}">
                                            {{ Str::limit($sig->signature_hash, 16) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-gray-500">{{ $sig->signed_at->format('d M Y H:i:s') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('documents.verify_public', $sig->qr_verification_code) }}" target="_blank" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm inline-flex items-center gap-1">
                                            <span>Buka Verifikasi QR</span>
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada riwayat dokumen yang ditandatangani secara digital.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
