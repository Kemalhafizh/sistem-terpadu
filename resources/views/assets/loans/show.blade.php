<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight">
                    {{ __('Detail Peminjaman Aset') }}
                </h2>
                <p class="text-xs text-gray-500 font-bold mt-1">Kelola permohonan peminjaman dan pengembalian aset</p>
            </div>
            <a href="{{ route('assets.loans.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs transition-all flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center space-x-2 text-sm font-bold shadow-sm">
                    <span class="p-1 bg-emerald-100 rounded-lg text-emerald-600"></span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left Column: Details of Asset and Borrower -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Asset Details Card -->
                    <div class="bg-white/95 backdrop-blur-md rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Informasi Barang Aset</h3>
                            <span class="px-3 py-1 bg-primary-50 text-primary-600 rounded-full text-[10px] font-black uppercase">{{ $loan->asset->category->name }}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Nama Aset</span>
                                <span class="text-base font-black text-gray-900">{{ $loan->asset->name }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Kode Aset</span>
                                <span class="text-sm font-mono font-black text-primary-600">{{ $loan->asset->asset_code }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Lokasi / Ruangan</span>
                                <span class="text-sm font-bold text-gray-700">{{ $loan->asset->room->name }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Kondisi Saat Ini</span>
                                <span class="inline-flex items-center text-xs font-bold mt-1">
                                    @if($loan->asset->condition == 'good')
                                        <span class="w-2 h-2 bg-emerald-500 rounded-full mr-1.5"></span> Baik
                                    @elseif($loan->asset->condition == 'damaged')
                                        <span class="w-2 h-2 bg-amber-500 rounded-full mr-1.5"></span> Rusak Ringan
                                    @else
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-1.5"></span> Rusak Berat
                                    @endif
                                </span>
                            </div>
                        </div>
                        @if($loan->asset->qr_code)
                            <div class="border-t border-gray-100 pt-4 flex flex-col md:flex-row items-center space-y-3 md:space-y-0 md:space-x-4">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=90x90&data={{ urlencode($loan->asset->qr_code) }}" class="p-1 border border-gray-100 rounded-xl" alt="QR Code">
                                <div>
                                    <h4 class="text-xs font-black text-gray-800 uppercase">Label QR Resmi</h4>
                                    <p class="text-[10px] text-gray-400 font-medium max-w-sm mt-1">Label stiker QR code tertempel secara fisik pada aset. Scan label QR menggunakan ponsel untuk memeriksa status kepemilikan aset secara real-time.</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Borrower and Loan Info Card -->
                    <div class="bg-white/95 backdrop-blur-md rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider">Detail Pengajuan Peminjaman</h3>
                            <span class="text-[10px] font-black text-gray-400 uppercase">Pengenal: #{{ $loan->id }}</span>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Nama Peminjam</span>
                                <span class="text-sm font-bold text-gray-700">{{ $loan->borrower->name }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Peran (Role)</span>
                                <span class="text-xs font-black text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md uppercase">{{ $loan->borrower->role }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Rencana Pengembalian</span>
                                <span class="text-sm font-bold text-gray-700">{{ $loan->expected_return_at->format('d M Y — H:i') }}</span>
                            </div>
                            <div>
                                <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider">Disetujui Oleh</span>
                                <span class="text-sm font-bold text-gray-700">{{ $loan->approver ? $loan->approver->name : '-' }}</span>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-3">
                            <span class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1">Tujuan Peminjaman</span>
                            <div class="text-sm text-gray-700 bg-gray-50 p-4 rounded-2xl italic">"{{ $loan->purpose }}"</div>
                        </div>
                    </div>

                    <!-- Return Info Card -->
                    @if($loan->status === 'returned')
                        <div class="bg-emerald-50/50 backdrop-blur-md rounded-3xl border border-emerald-100 shadow-sm p-6 space-y-4">
                            <h3 class="text-sm font-black text-emerald-950 uppercase tracking-wider border-b border-emerald-100 pb-3">Informasi Pengembalian Barang</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <span class="block text-[10px] font-black text-emerald-600 uppercase tracking-wider">Tanggal Pengembalian</span>
                                    <span class="text-sm font-bold text-emerald-900">{{ $loan->returned_at->format('d M Y — H:i') }}</span>
                                </div>
                                <div>
                                    <span class="block text-[10px] font-black text-emerald-600 uppercase tracking-wider">Kondisi Pengembalian</span>
                                    <span class="inline-flex items-center text-xs font-black text-emerald-900 bg-emerald-100 px-2.5 py-0.5 rounded-full uppercase mt-1">
                                        {{ $loan->return_condition }}
                                    </span>
                                </div>
                            </div>
                            @if($loan->return_notes)
                                <div class="border-t border-emerald-100 pt-3">
                                    <span class="block text-[10px] font-black text-emerald-600 uppercase tracking-wider mb-1">Catatan Pengembalian</span>
                                    <div class="text-sm text-emerald-900 italic">"{{ $loan->return_notes }}"</div>
                                </div>
                            @endif
                        </div>
                    @endif

                </div>

                <!-- Right Column: Control & Decisions Panel -->
                <div class="space-y-6">

                    <!-- Status Display Panel -->
                    <div class="bg-white/95 backdrop-blur-md rounded-3xl border border-gray-100 shadow-sm p-6 text-center space-y-3">
                        <span class="block text-[10px] font-black text-gray-400 uppercase tracking-widest">Status Saat Ini</span>
                        <div>
                            @if($loan->status === 'pending')
                                <span class="px-5 py-2 bg-gradient-to-r from-amber-400 to-orange-400 text-white rounded-full text-xs font-black uppercase shadow-md shadow-amber-100">MENUNGGU PERSETUJUAN</span>
                            @elseif($loan->status === 'approved' || $loan->status === 'borrowed')
                                <span class="px-5 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-full text-xs font-black uppercase shadow-md shadow-blue-100">SEDANG DIPINJAM</span>
                            @elseif($loan->status === 'returned')
                                <span class="px-5 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-full text-xs font-black uppercase shadow-md shadow-emerald-100">SUDAH KEMBALI</span>
                            @elseif($loan->status === 'rejected')
                                <span class="px-5 py-2 bg-gradient-to-r from-red-500 to-pink-500 text-white rounded-full text-xs font-black uppercase shadow-md shadow-red-100">PERMOHONAN DITOLAK</span>
                            @elseif($loan->status === 'lost')
                                <span class="px-5 py-2 bg-gray-800 text-white rounded-full text-xs font-black uppercase shadow-md shadow-gray-100">BARANG HILANG</span>
                            @endif
                        </div>
                        <p class="text-[10px] text-gray-400 font-bold uppercase mt-2">Dibuat Pada: {{ $loan->created_at->format('d M Y - H:i') }}</p>
                    </div>

                    <!-- Approval Form for Waka Sarpras (Pending Status) -->
                    @if($loan->status === 'pending' && in_array(auth()->user()->role, ['admin', 'superadmin', 'waka_sarpras']))
                        <div class="bg-white/95 backdrop-blur-md rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-3">Keputusan Persetujuan</h3>
                            <p class="text-xs text-gray-500 font-medium">Sebagai Waka Sarpras / Administrator, Anda berhak menyetujui atau menolak peminjaman inventaris sekolah.</p>
                            <form action="{{ route('assets.loans.approve', $loan->uuid) }}" method="POST" class="space-y-3">
                                @csrf
                                <button type="submit" name="action" value="approve" class="w-full py-3 bg-gradient-to-r from-primary-600 to-emerald-600 hover:from-primary-700 hover:to-emerald-700 text-white rounded-xl text-xs font-black shadow-lg shadow-primary-200 transition-all">
                                    Setujui Peminjaman
                                </button>
                                <button type="submit" name="action" value="reject" class="w-full py-3 bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 border border-gray-100 hover:border-red-100 rounded-xl text-xs font-black transition-all">
                                    Tolak Permohonan
                                </button>
                            </form>
                        </div>
                    @endif

                    <!-- Return Form for Handlers (Approved / Borrowed Status) -->
                    @if(in_array($loan->status, ['approved', 'borrowed']) && in_array(auth()->user()->role, ['admin', 'superadmin', 'waka_sarpras', 'staf_tu']))
                        <div class="bg-white/95 backdrop-blur-md rounded-3xl border border-gray-100 shadow-sm p-6 space-y-4">
                            <h3 class="text-sm font-black text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-3">Proses Pengembalian</h3>
                            <p class="text-xs text-gray-500 font-medium">Catat pengembalian barang secara tertib untuk memulihkan status ketersediaan aset sekolah.</p>
                            <form action="{{ route('assets.loans.return', $loan->uuid) }}" method="POST" class="space-y-4">
                                @csrf
                                <div>
                                    <label for="return_condition" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Kondisi Pengembalian</label>
                                    <select name="return_condition" id="return_condition" required class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5">
                                        <option value="good">Baik / Utuh</option>
                                        <option value="damaged">Rusak Ringan</option>
                                        <option value="broken">Rusak Berat</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="return_notes" class="block text-[10px] font-black text-gray-400 uppercase tracking-wider mb-1.5">Catatan Tambahan</label>
                                    <textarea name="return_notes" id="return_notes" rows="3" placeholder="Isi catatan jika ada kecacatan/kelengkapan yang berkurang..." class="w-full rounded-xl border-gray-200 text-xs focus:border-primary-500 focus:ring-primary-500 bg-gray-50 p-2.5"></textarea>
                                </div>
                                <button type="submit" class="w-full py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-black shadow-lg shadow-emerald-200 transition-all">
                                    Simpan Pengembalian
                                </button>
                            </form>
                        </div>
                    @endif

                </div>

            </div>

        </div>
    </div>
</x-app-layout>
