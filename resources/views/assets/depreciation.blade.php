<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight">
                    {{ __('Kalkulasi Penyusutan Aset') }}
                </h2>
                <p class="text-xs text-gray-500 font-bold mt-1">Laporan Depresiasi Garis Lurus (Straight-Line Depreciation) Real-time — SISTA Portal</p>
            </div>
            <a href="{{ route('assets.loans.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs transition-all flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Stat Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1: Total Initial Value -->
                <div class="bg-white/75 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-4 bg-primary-50 rounded-2xl text-primary-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M12 16V7"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Harga Perolehan</div>
                        <div class="text-2xl font-black text-gray-900 mt-1">
                            Rp {{ number_format($assets->sum('purchase_price'), 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Card 2: Accumulated Depreciation -->
                <div class="bg-white/75 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-4 bg-rose-50 rounded-2xl text-rose-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Akumulasi Penyusutan</div>
                        <div class="text-2xl font-black text-gray-900 mt-1">
                            Rp {{ number_format($assets->sum('purchase_price') - $assets->sum('current_value'), 0, ',', '.') }}
                        </div>
                    </div>
                </div>

                <!-- Card 3: Current Book Value -->
                <div class="bg-white/75 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4">
                    <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Nilai Buku Saat Ini</div>
                        <div class="text-2xl font-black text-gray-900 mt-1">
                            Rp {{ number_format($assets->sum('current_value'), 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Explanation Box -->
            <div class="bg-emerald-50/70 border border-emerald-100 p-5 rounded-3xl text-emerald-800 text-xs flex items-start space-x-3.5">
                <div class="p-2 bg-emerald-100 rounded-2xl text-emerald-600 font-bold text-sm"></div>
                <div>
                    <h4 class="font-black uppercase tracking-wider text-[10px] text-emerald-950 mb-1">Metode Garis Lurus (Straight-Line Depreciation):</h4>
                    <p class="leading-relaxed text-emerald-900 font-medium">Penyusutan dihitung per hari secara real-time sejak <strong>Tanggal Perolehan (Purchase Date)</strong> berdasarkan total <strong>Umur Manfaat (Useful Life Years)</strong> yang dikonfigurasi. Nilai buku aset terus berkurang secara linier setiap hari hingga mencapai nilai sisa Rp 0 ketika masa manfaatnya telah habis terlampaui.</p>
                </div>
            </div>

            <!-- Depreciation Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Nama & Kode Aset</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Tanggal Perolehan</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Masa Manfaat</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Harga Perolehan</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Penyusutan Akumulatif</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Nilai Buku Saat Ini</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Sisa Nilai</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($assets as $asset)
                            @php
                                $price = $asset->purchase_price ?: 0;
                                $current = $asset->current_value;
                                $depreciation = max(0, $price - $current);
                                $remainingPercent = $price > 0 ? round(($current / $price) * 100) : 0;
                            @endphp
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="font-black text-gray-900">{{ $asset->name }}</div>
                                    <div class="text-[10px] font-bold text-primary-600 font-mono tracking-tighter">{{ $asset->asset_code }}</div>
                                </td>
                                <td class="px-6 py-5 text-xs font-bold text-gray-600">
                                    {{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}
                                </td>
                                <td class="px-6 py-5 text-xs font-bold text-gray-700">
                                    {{ $asset->useful_life_years ?: '-' }} Tahun
                                </td>
                                <td class="px-6 py-5 text-xs font-black text-gray-700">
                                    Rp {{ number_format($price, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-xs font-bold text-rose-600">
                                    Rp {{ number_format($depreciation, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5 text-xs font-black text-emerald-600">
                                    Rp {{ number_format($current, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center space-x-2">
                                        <div class="w-16 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                            @if($remainingPercent > 50)
                                                <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $remainingPercent }}%"></div>
                                            @elseif($remainingPercent > 20)
                                                <div class="bg-amber-500 h-1.5 rounded-full" style="width: {{ $remainingPercent }}%"></div>
                                            @else
                                                <div class="bg-red-500 h-1.5 rounded-full" style="width: {{ $remainingPercent }}%"></div>
                                            @endif
                                        </div>
                                        <span class="text-[10px] font-black text-gray-500">{{ $remainingPercent }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada aset dengan harga perolehan dan masa manfaat yang tercatat.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>
