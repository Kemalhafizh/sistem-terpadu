<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                Detail Aset: {{ $asset->asset_code }}
            </h2>
            <a href="{{ route('assets.index') }}" class="text-sm font-bold text-gray-500 hover:text-gray-800">← Kembali ke Daftar</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                <!-- Label Aset (Physical Label Design) -->
                <div class="md:col-span-1 bg-white p-6 rounded-[2rem] shadow-xl border-4 border-gray-900 flex flex-col items-center text-center">
                    <div class="text-[10px] font-black uppercase tracking-[0.3em] text-gray-400 mb-4">Properti Sekolah</div>
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $asset->asset_code }}" alt="QR Code" class="w-32 h-32 mb-4">
                    <div class="font-mono font-black text-lg tracking-tighter mb-2">{{ $asset->asset_code }}</div>
                    <div class="text-sm font-bold text-gray-800 uppercase px-4 py-1 bg-yellow-300 rounded-lg mb-4">{{ $asset->name }}</div>
                    <div class="text-[10px] font-bold text-gray-400">{{ mb_strtoupper(school_short_name()) }}</div>
                    
                    <button onclick="window.print()" class="mt-6 w-full py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-xs hover:bg-gray-200 transition-all flex items-center justify-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        Cetak Label
                    </button>
                </div>

                <!-- Info Detail -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                        <h3 class="text-lg font-black text-gray-800 mb-6 flex items-center">
                            <span class="w-2 h-6 bg-primary-600 rounded-full mr-3"></span> Spesifikasi Aset
                        </h3>
                        <div class="grid grid-cols-2 gap-y-6">
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kategori</p>
                                <p class="text-sm font-bold text-gray-800">{{ $asset?->category?->name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Lokasi Saat Ini</p>
                                <p class="text-sm font-bold text-gray-800">{{ $asset?->room?->name }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tanggal Perolehan</p>
                                <p class="text-sm font-bold text-gray-800">{{ $asset->purchase_date ? $asset->purchase_date->format('d F Y') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Harga Beli</p>
                                <p class="text-sm font-bold text-emerald-600">Rp {{ number_format($asset->purchase_price, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="mt-8 pt-6 border-t border-gray-50">
                            <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3">Deskripsi / Catatan</p>
                            <p class="text-sm text-gray-600 italic leading-relaxed">{{ $asset->description ?? 'Tidak ada catatan tambahan.' }}</p>
                        </div>
                    </div>

                    <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100">
                        <h3 class="text-lg font-black text-gray-800 mb-4 flex items-center">
                            <span class="w-2 h-6 bg-emerald-500 rounded-full mr-3"></span> Status & Kondisi
                        </h3>
                        <div class="flex items-center space-x-4">
                            <div class="flex-1 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Kondisi</p>
                                <p class="text-sm font-black {{ $asset->condition == 'good' ? 'text-emerald-600' : 'text-red-500' }}">{{ ucfirst($asset->condition) }}</p>
                            </div>
                            <div class="flex-1 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                <p class="text-[10px] font-black text-gray-400 uppercase mb-1">Ketersediaan</p>
                                <p class="text-sm font-black text-primary-600">{{ ucfirst($asset->status) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <style>
        @media print {
            body * { visibility: hidden; }
            .shadow-xl, .shadow-xl * { visibility: visible; }
            .shadow-xl { position: absolute; left: 0; top: 0; border: 2px solid black !important; }
            button { display: none !important; }
        }
    </style>
</x-app-layout>
