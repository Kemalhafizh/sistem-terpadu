<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.finance.charity.index') }}" 
                   class="p-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-600 dark:text-gray-300 rounded-xl transition-all"
                   title="Kembali ke Daftar Kampanye">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </a>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Kelola Donasi</span>
                    <h2 class="font-black text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                        Detail Donatur Kampanye
                    </h2>
                </div>
            </div>
            <div class="px-4 py-2 bg-teal-50 dark:bg-teal-950/40 rounded-2xl border border-teal-100 dark:border-teal-900/50">
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400 block">Tipe Kampanye</span>
                <span class="text-sm font-bold text-teal-800 dark:text-teal-300 capitalize">{{ $campaign->type }}</span>
            </div>
        </div>
    </x-slot>

    <div class="space-y-8 w-full" x-data="{ showEvidenceModal: false, evidenceUrl: '' }">
        <!-- Flash Messages -->
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

        <!-- Campaign Stats Detail Panel -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-100 dark:border-gray-700 p-8 shadow-sm">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-4">
                    <span class="inline-block px-3 py-1 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                        {{ $campaign->type }}
                    </span>
                    <h3 class="font-black text-2xl text-gray-800 dark:text-white leading-tight">
                        {{ $campaign->title }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        {{ $campaign->description }}
                    </p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900/40 rounded-[2rem] p-6 border border-gray-100 dark:border-gray-800 space-y-4">
                    <h4 class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        Keuangan Kampanye
                    </h4>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 block">Terkumpul</span>
                            <span class="text-lg font-black text-teal-600 dark:text-teal-400">
                                Rp {{ number_format($campaign->collected_amount, 0, ',', '.') }}
                            </span>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold text-gray-400 dark:text-gray-500 block">Target</span>
                            <span class="text-lg font-black text-gray-700 dark:text-gray-300">
                                @if($campaign->target_amount > 0)
                                    Rp {{ number_format($campaign->target_amount, 0, ',', '.') }}
                                @else
                                    <span class="text-gray-400 italic text-sm">Abadi</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @if($campaign->target_amount > 0)
                        @php
                            $pct = $campaign->percentage_achieved;
                        @endphp
                        <div>
                            <div class="flex justify-between items-center text-[10px] font-bold text-gray-500 dark:text-gray-400 mb-1">
                                <span>Progress Pengumpulan</span>
                                <span>{{ $pct }}%</span>
                            </div>
                            <div class="w-full h-2.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-linear-to-r from-teal-500 to-emerald-500 rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center space-x-1.5 text-xs text-teal-600 dark:text-teal-400 font-bold uppercase tracking-widest">
                            <span class="inline-block w-2.5 h-2.5 bg-teal-500 rounded-full animate-pulse"></span>
                            <span>Menerima Donasi Tanpa Batas</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="bg-gray-100 dark:bg-gray-800 p-1.5 rounded-2xl flex items-center space-x-1 w-full md:w-auto border border-gray-200/50 dark:border-gray-700">
                <a href="{{ route('admin.finance.charity.donors', $campaign->id) }}" 
                   class="px-5 py-2 text-xs font-black uppercase tracking-wider rounded-xl transition-all {{ !$status ? 'bg-white dark:bg-gray-700 text-teal-600 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    Semua
                </a>
                <a href="{{ route('admin.finance.charity.donors', $campaign->id) }}?status=pending" 
                   class="px-5 py-2 text-xs font-black uppercase tracking-wider rounded-xl transition-all {{ $status === 'pending' ? 'bg-white dark:bg-gray-700 text-teal-600 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    Pending
                </a>
                <a href="{{ route('admin.finance.charity.donors', $campaign->id) }}?status=verified" 
                   class="px-5 py-2 text-xs font-black uppercase tracking-wider rounded-xl transition-all {{ $status === 'verified' ? 'bg-white dark:bg-gray-700 text-teal-600 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    Verified
                </a>
                <a href="{{ route('admin.finance.charity.donors', $campaign->id) }}?status=cancelled" 
                   class="px-5 py-2 text-xs font-black uppercase tracking-wider rounded-xl transition-all {{ $status === 'cancelled' ? 'bg-white dark:bg-gray-700 text-teal-600 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200' }}">
                    Dibatalkan
                </a>
            </div>
            <div class="text-xs font-bold text-gray-400 dark:text-gray-500">
                Menampilkan {{ $donations->firstItem() ?? 0 }} - {{ $donations->lastItem() ?? 0 }} dari {{ $donations->total() }} donasi
            </div>
        </div>

        <!-- Donations Table -->
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/30 text-left text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                            <th class="px-8 py-5">No. Resi</th>
                            <th class="px-6 py-5">Donatur</th>
                            <th class="px-6 py-5 text-right">Jumlah</th>
                            <th class="px-6 py-5 text-center">Metode</th>
                            <th class="px-6 py-5 text-center">Status</th>
                            <th class="px-6 py-5">Tanggal Masuk</th>
                            <th class="px-8 py-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($donations as $donation)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-colors">
                                <td class="px-8 py-6 font-bold text-gray-700 dark:text-gray-300">
                                    {{ $donation->receipt_number }}
                                </td>
                                <td class="px-6 py-6">
                                    @if($donation->is_anonymous)
                                        <p class="font-black text-gray-800 dark:text-white flex items-center">
                                            <span class="inline-block px-2 py-0.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded text-[9px] font-black uppercase mr-1.5 tracking-wider">
                                                Anonim
                                            </span>
                                            Hamba Allah
                                        </p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter mt-0.5">
                                            User: {{ $donation->user?->name ?? 'N/A' }}
                                        </p>
                                    @else
                                        <p class="font-black text-gray-800 dark:text-white">
                                            {{ $donation->donor_name ?? $donation->user?->name }}
                                        </p>
                                        @if($donation->donor_name && $donation->user)
                                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter mt-0.5">
                                                Oleh: {{ $donation->user->name }}
                                            </p>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-6 text-right font-black text-teal-600 dark:text-teal-400">
                                    Rp {{ number_format($donation->amount, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-6 text-center">
                                    @switch($donation->payment_method)
                                        @case('transfer')
                                            <span class="inline-block px-3 py-1 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                                                Transfer
                                            </span>
                                            @break
                                        @case('wadiah_wallet')
                                            <span class="inline-block px-3 py-1 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                                                E-Wallet
                                            </span>
                                            @break
                                        @case('cash')
                                            <span class="inline-block px-3 py-1 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-full text-[10px] font-black uppercase tracking-widest">
                                                Tunai
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-6 text-center">
                                    @switch($donation->status)
                                        @case('pending')
                                            <span class="inline-block px-3 py-1 bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400 rounded-full text-[10px] font-black tracking-widest uppercase">
                                                Pending
                                            </span>
                                            @break
                                        @case('verified')
                                            <span class="inline-block px-3 py-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 rounded-full text-[10px] font-black tracking-widest uppercase">
                                                Verified
                                            </span>
                                            @break
                                        @case('cancelled')
                                            <span class="inline-block px-3 py-1 bg-red-100 text-red-700 dark:bg-red-950/40 dark:text-red-400 rounded-full text-[10px] font-black tracking-widest uppercase">
                                                Dibatalkan
                                            </span>
                                            @break
                                    @endswitch
                                </td>
                                <td class="px-6 py-6 text-xs text-gray-500 dark:text-gray-400">
                                    {{ $donation->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end items-center gap-2">
                                        <!-- Evidence of Transfer Button -->
                                        @if($donation->payment_method === 'transfer' && $donation->evidence_path)
                                            <button @click="evidenceUrl = '{{ $donation->evidence_url }}'; showEvidenceModal = true" 
                                                    class="inline-flex items-center p-2 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm"
                                                    title="Lihat Bukti Transfer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        @endif

                                        <!-- Verification Actions -->
                                        @if($donation->status === 'pending')
                                            <!-- Verify Form -->
                                            <form action="{{ route('admin.finance.charity.verify', $donation->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="inline-flex items-center px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition-all shadow-md shadow-emerald-600/10"
                                                        title="Verifikasi Donasi">
                                                    Verifikasi
                                                </button>
                                            </form>

                                            <!-- Cancel Form -->
                                            <form action="{{ route('admin.finance.charity.cancel', $donation->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        onclick="return confirm('Apakah Anda yakin ingin membatalkan donasi ini?')"
                                                        class="inline-flex items-center px-3 py-1.5 bg-red-50 dark:bg-red-950/40 hover:bg-red-600 text-red-600 dark:text-red-400 hover:text-white rounded-lg text-xs font-bold transition-all"
                                                        title="Batalkan Donasi">
                                                    Batal
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-8 py-12 text-center text-gray-400 dark:text-gray-500 font-medium">
                                    <div class="flex flex-col items-center justify-center space-y-3">
                                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <span>Belum ada transaksi donasi yang sesuai filter.</span>
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
                    {{ $donations->appends(['status' => $status])->links() }}
                </div>
            @endif
        </div>

        <!-- Image Evidence Modal -->
        <div x-show="showEvidenceModal" 
             class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-xs transition-opacity" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             style="display: none;"
             @keydown.escape.window="showEvidenceModal = false">
            
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] border border-gray-200 dark:border-gray-700 shadow-2xl max-w-lg w-full overflow-hidden transform transition-all"
                 @click.away="showEvidenceModal = false"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95">
                
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/20">
                    <div>
                        <h4 class="font-black text-gray-800 dark:text-white text-base">Bukti Transfer</h4>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">Konfirmasi Pembayaran Transfer Bank</p>
                    </div>
                    <button @click="showEvidenceModal = false" class="p-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-xl transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="p-6 bg-gray-50 dark:bg-gray-900/50 flex justify-center items-center">
                    <img :src="evidenceUrl" alt="Bukti Transfer Donasi" class="max-h-[450px] object-contain rounded-2xl shadow-md border border-gray-200 dark:border-gray-700">
                </div>

                <div class="p-6 border-t border-gray-100 dark:border-gray-700 flex justify-end bg-gray-50/50 dark:bg-gray-900/20">
                    <button @click="showEvidenceModal = false" class="px-6 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-xl text-xs font-bold transition-all shadow-md">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
