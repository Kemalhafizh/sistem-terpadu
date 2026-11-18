<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Portal Siswa & Orang Tua</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    ZIS & Wakaf Portal
                </h2>
            </div>
            <div class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest">
                Riwayat Donasi & Sosial Anda
            </div>
        </div>
    </x-slot>

    <div class="space-y-8 w-full">
        <!-- Session Flash Messages -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/50 p-4 rounded-2xl shadow-sm text-emerald-800 dark:text-emerald-300 font-bold text-sm flex items-center space-x-3">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/50 p-4 rounded-2xl shadow-sm text-red-800 dark:text-red-300 font-bold text-sm flex items-center space-x-3">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Horizontal Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <a href="{{ route('portal.charity.portal') }}" 
                   class="border-b-2 border-transparent py-4 px-1 text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    Katalog Donasi
                </a>
                <a href="{{ route('portal.charity.history') }}" 
                   class="border-b-2 border-teal-600 py-4 px-1 text-sm font-black uppercase tracking-widest text-teal-600 dark:text-teal-400"
                   aria-current="page">
                    Riwayat Donasi
                </a>
            </nav>
        </div>

        <!-- Donation History Table Section -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-8 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center flex-wrap gap-4">
                <div>
                    <h3 class="font-black text-lg text-gray-800 dark:text-white">Catatan Donasi Saya</h3>
                    <p class="text-xs text-gray-400 mt-1">Daftar lengkap kontribusi amal yang Anda salurkan melalui program ZIS & Wakaf YBWSA.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/30 text-left text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                            <th class="px-8 py-5">Program Kampanye</th>
                            <th class="px-6 py-5">No. Resi</th>
                            <th class="px-6 py-5 text-right">Jumlah Donasi</th>
                            <th class="px-6 py-5 text-center">Metode</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            <th class="px-6 py-5">Tanggal</th>
                            <th class="px-8 py-5 text-right">Apresiasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($donations as $donation)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors">
                                <td class="px-8 py-6">
                                    <div class="max-w-xs md:max-w-md">
                                        <p class="font-black text-gray-800 dark:text-white text-sm">
                                            {{ $donation->campaign?->title ?? 'Program Kampanye Terhapus' }}
                                        </p>
                                        <p class="text-[9px] text-gray-400 font-bold uppercase tracking-wider mt-1">
                                            Tipe: {{ $donation->campaign?->type ?? 'N/A' }}
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-6 font-bold text-gray-700 dark:text-gray-300">
                                    {{ $donation->receipt_number }}
                                </td>
                                <td class="px-6 py-6 text-right font-black text-teal-600 dark:text-teal-400">
                                    Rp {{ number_format($donation->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-6 text-center">
                                    @switch($donation->payment_method)
                                        @case('transfer')
                                            <span class="inline-block px-3 py-1 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                                                Transfer
                                            </span>
                                            @break
                                        @case('wadiah_wallet')
                                            <span class="inline-block px-3 py-1 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                                                E-Wallet
                                            </span>
                                            @break
                                        @case('cash')
                                            <span class="inline-block px-3 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-full text-[9px] font-black uppercase tracking-widest">
                                                Tunai
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-6 text-center">
                                    @switch($donation->status)
                                        @case('pending')
                                            <span class="inline-block px-3 py-1 bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400 rounded-full text-[9px] font-black tracking-widest uppercase">
                                                Pending
                                            </span>
                                            @break
                                        @case('verified')
                                            <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 rounded-full text-[9px] font-black tracking-widest uppercase">
                                                Verified
                                            </span>
                                            @break
                                        @case('cancelled')
                                            <span class="inline-block px-3 py-1 bg-red-100 text-red-700 dark:bg-red-950/30 dark:text-red-400 rounded-full text-[9px] font-black tracking-widest uppercase">
                                                Dibatalkan
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-6 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $donation->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    @if($donation->status === 'verified')
                                        <a href="{{ route('portal.charity.certificate', $donation->id) }}" 
                                           class="inline-flex items-center px-4 py-2 bg-teal-50 dark:bg-teal-950/40 hover:bg-teal-600 text-teal-700 dark:text-teal-300 hover:text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-sm gap-2"
                                           title="Unduh E-Sertifikat Donasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            <span>Unduh E-Sertifikat</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 dark:text-gray-500 font-medium italic">
                                            -
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-gray-400 dark:text-gray-500 font-medium">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Anda belum pernah melakukan transaksi donasi.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Section -->
            @if($donations->hasPages())
                <div class="p-6 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                    {{ $donations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
