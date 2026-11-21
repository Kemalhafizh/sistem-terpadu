<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-black text-2xl text-gray-800 leading-tight">
                     Jurnal Umum & Buku Besar (General Ledger)
                </h2>
                <p class="text-xs text-gray-400 font-bold mt-1 uppercase tracking-widest">Sistem Akuntansi Buku Besar Terpadu</p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="window.print()" class="px-5 py-2.5 bg-gray-900 text-white rounded-xl font-bold text-xs hover:bg-black transition-all shadow-md shadow-gray-900/10 flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                    </svg>
                    <span>Cetak Jurnal</span>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12 bg-gray-50/50 min-h-[calc(100vh-4rem)]">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8 animate-fade-in">
            
            <!-- Tab Navigasi -->
            <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap gap-2">
                <a href="{{ route('finance.reports.executive') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Ringkasan Eksekutif
                </a>
                <a href="{{ route('finance.reports.arrears') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Laporan Tunggakan
                </a>
                <a href="{{ route('finance.reports.projection') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Proyeksi Pendapatan
                </a>
                <a href="{{ route('finance.reports.monthly') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Rekap Bulanan
                </a>
                <a href="{{ route('finance.reports.income') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Laporan Laba Rugi
                </a>
                <a href="{{ route('finance.reports.balance') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Neraca Keuangan
                </a>
                <a href="{{ route('finance.reports.ledger') }}" class="px-4 py-2.5 rounded-xl text-xs font-black transition-all bg-emerald-50 text-emerald-700 shadow-sm">
                     Log Jurnal Umum
                </a>
            </div>

            <!-- Jurnal Entries List -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center bg-gray-50/30">
                    <div>
                        <h4 class="text-lg font-black text-gray-800 flex items-center">
                            <span class="w-2.5 h-6 bg-emerald-500 rounded-full mr-3"></span> Histori Entri Jurnal Berpasangan
                        </h4>
                        <p class="text-xs text-gray-400 font-semibold mt-1">Log kronologis seluruh aktivitas finansial sekolah secara riil</p>
                    </div>
                    <span class="text-xs font-mono font-black bg-gray-900 text-white px-4 py-1.5 rounded-full">Double-Entry Ledger</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 border-b border-gray-100">
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest w-40">Tanggal & Ref</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest">Keterangan Jurnal & Pos Akun</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right w-44">Debit</th>
                                <th class="px-8 py-5 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right w-44">Kredit</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($entries ?? [] as $entry)
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <!-- Tanggal & Referensi -->
                                    <td class="px-8 py-6 align-top">
                                        <div class="font-extrabold text-gray-800 text-xs font-mono">{{ \Carbon\Carbon::parse($entry->entry_date)->translatedFormat('d M Y') }}</div>
                                        <div class="text-[9px] font-mono font-black text-cyan-600 bg-cyan-50 px-2 py-0.5 rounded-md inline-block mt-1.5 uppercase tracking-tighter">
                                            {{ $entry->reference ?? '-' }}
                                        </div>
                                    </td>
                                    <!-- Detail Akun & Deskripsi Jurnal -->
                                    <td class="px-8 py-6 align-top">
                                        <div class="text-xs font-extrabold text-gray-900 mb-4 bg-gray-50 px-3 py-1.5 rounded-xl border border-gray-100/50">
                                            {{ $entry->description }}
                                        </div>
                                        
                                        <!-- Lines List -->
                                        <div class="space-y-3 font-medium text-xs">
                                            @foreach($entry->lines as $line)
                                                @php
                                                    $isCredit = $line->credit > 0;
                                                @endphp
                                                <div class="flex items-center justify-between {{ $isCredit ? 'pl-8' : '' }}">
                                                    <div class="flex items-center space-x-2.5">
                                                        <span class="font-mono text-[10px] text-gray-400 bg-gray-100 px-2 py-0.5 rounded">
                                                            {{ $line->account->code }}
                                                        </span>
                                                        <span class="{{ $isCredit ? 'text-gray-500 italic' : 'text-gray-800 font-bold' }}">
                                                            {{ $line->account->name }}
                                                        </span>
                                                    </div>
                                                    <span class="text-[10px] text-gray-400 font-mono uppercase tracking-tighter">
                                                        {{ $isCredit ? 'Cr' : 'Db' }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <!-- Sisi Debit -->
                                    <td class="px-8 py-6 align-top text-right font-mono text-xs">
                                        <div class="h-6 mb-4"></div> <!-- Spacer align dengan header entry -->
                                        <div class="space-y-3">
                                            @foreach($entry->lines as $line)
                                                <div class="h-4 font-bold {{ $line->debit > 0 ? 'text-emerald-600' : 'text-transparent select-none' }}">
                                                    {{ $line->debit > 0 ? 'Rp ' . number_format($line->debit, 0, ',', '.') : '-' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                    <!-- Sisi Kredit -->
                                    <td class="px-8 py-6 align-top text-right font-mono text-xs">
                                        <div class="h-6 mb-4"></div> <!-- Spacer align dengan header entry -->
                                        <div class="space-y-3">
                                            @foreach($entry->lines as $line)
                                                <div class="h-4 font-bold {{ $line->credit > 0 ? 'text-indigo-600' : 'text-transparent select-none' }}">
                                                    {{ $line->credit > 0 ? 'Rp ' . number_format($line->credit, 0, ',', '.') : '-' }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-12 text-center text-gray-400 italic text-xs">
                                        Belum ada aktivitas transaksi keuangan yang tercatat di jurnal umum sekolah.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginated Links -->
                @if($entries && $entries->hasPages())
                    <div class="px-8 py-6 bg-gray-50/50 border-t border-gray-100">
                        {{ $entries->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
