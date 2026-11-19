<x-guest-layout>
    <div class="max-w-xl mx-auto py-8 px-4">
        <div class="bg-white dark:bg-gray-900 rounded-3xl shadow-xl border border-gray-200 dark:border-gray-800 p-8 space-y-6">
            <div class="text-center space-y-2 border-b border-gray-100 dark:border-gray-800 pb-6">
                <p class="text-xs font-bold uppercase tracking-widest text-primary-600 dark:text-primary-400">
                    {{ school_short_name() }}
                </p>
                <h1 class="text-xl font-extrabold text-gray-900 dark:text-white">
                    Verifikasi Keaslian Surat Resmi
                </h1>
            </div>

            @if($state === 'valid' && $letter)
                <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 flex items-center gap-3">
                    <svg class="w-6 h-6 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-extrabold text-emerald-800 dark:text-emerald-300">DOKUMEN RESMI TERVERIFIKASI</p>
                        <p class="text-xs text-emerald-700 dark:text-emerald-400">Surat ini tercatat sah dan diterbitkan oleh {{ school_short_name() }}.</p>
                    </div>
                </div>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <dt class="text-gray-500">Nomor Surat</dt>
                        <dd class="font-bold text-gray-900 dark:text-white text-right">{{ $letter->letter_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <dt class="text-gray-500">Jenis Dokumen</dt>
                        <dd class="font-bold text-gray-900 dark:text-white text-right">{{ $letter->template?->name ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <dt class="text-gray-500">Tanggal Surat</dt>
                        <dd class="font-bold text-gray-900 dark:text-white text-right">{{ $letter->issue_date?->translatedFormat('d F Y') ?? '-' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                        <dt class="text-gray-500">Disetujui Oleh</dt>
                        <dd class="font-bold text-gray-900 dark:text-white text-right">{{ $letter->approver?->name ?? ($letter->template?->signer_title ?? 'Kepala Sekolah') }}</dd>
                    </div>
                    @if($letter->approved_at)
                        <div class="flex justify-between gap-4 border-b border-gray-100 dark:border-gray-800 pb-2">
                            <dt class="text-gray-500">Tanggal Pengesahan</dt>
                            <dd class="font-bold text-gray-900 dark:text-white text-right">{{ $letter->approved_at->translatedFormat('d F Y H:i') }} WIB</dd>
                        </div>
                    @endif
                </dl>
            @elseif($state === 'revoked' && $letter)
                <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 flex items-center gap-3">
                    <svg class="w-6 h-6 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-extrabold text-amber-800 dark:text-amber-300">DOKUMEN DIBATALKAN / DICABUT</p>
                        <p class="text-xs text-amber-700 dark:text-amber-400">Nomor {{ $letter->letter_number }} sudah dicabut dan tidak lagi berlaku.</p>
                    </div>
                </div>
            @else
                <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 flex items-center gap-3">
                    <svg class="w-6 h-6 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <p class="text-sm font-extrabold text-rose-800 dark:text-rose-300">KODE VERIFIKASI TIDAK VALID</p>
                        <p class="text-xs text-rose-700 dark:text-rose-400">Token verifikasi tidak terdaftar pada arsip resmi {{ school_short_name() }}.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-guest-layout>
