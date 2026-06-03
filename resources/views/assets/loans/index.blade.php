<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight">
                    {{ __('Peminjaman & Peminjam Aset') }}
                </h2>
                <p class="text-xs text-gray-500 font-bold mt-1">Modul Inventaris Aset Lanjutan — SISTA Portal</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'waka_sarpras', 'kepala_sekolah']))
                    <a href="{{ route('assets.depreciation') }}" class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl font-black text-xs shadow-md hover:shadow-lg transition-all flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 002 2h2a2 2 0 002-2"></path></svg>
                        <span>Penyusutan Nilai</span>
                    </a>
                    <a href="{{ route('assets.maintenances.index') }}" class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-xl font-black text-xs shadow-md hover:shadow-lg transition-all flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Perawatan Berkala</span>
                    </a>
                @endif
                @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'waka_sarpras']))
                    <a href="{{ route('assets.print.qr-labels') }}" class="px-4 py-2.5 bg-gray-900 hover:bg-gray-800 text-white rounded-xl font-black text-xs shadow-md hover:shadow-lg transition-all flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>
                        <span>Cetak Label QR</span>
                    </a>
                @endif
                @if(in_array(auth()->user()->role, ['guru', 'staf_tu', 'admin', 'superadmin', 'waka_sarpras']))
                    <a href="{{ route('assets.loans.create') }}" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl font-black text-xs shadow-lg shadow-primary-200 hover:shadow-xl transition-all flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                        <span>Ajukan Peminjaman</span>
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center space-x-2 text-sm font-bold shadow-sm">
                    <span class="p-1 bg-emerald-100 rounded-lg text-emerald-600">✓</span>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl flex items-center space-x-2 text-sm font-bold shadow-sm">
                    <span class="p-1 bg-red-100 rounded-lg text-red-600">⚠</span>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Glassmorphic Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Card 1 -->
                <div class="relative bg-white/70 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4 overflow-hidden group hover:scale-[1.02] hover:shadow-md transition-all duration-300">
                    <div class="p-4 bg-primary-50 rounded-2xl text-primary-600 transition-colors group-hover:bg-primary-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Ajuan</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">{{ $loans->total() }}</div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-primary-500/5 rounded-full pointer-events-none"></div>
                </div>

                <!-- Card 2 -->
                <div class="relative bg-white/70 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4 overflow-hidden group hover:scale-[1.02] hover:shadow-md transition-all duration-300">
                    <div class="p-4 bg-amber-50 rounded-2xl text-amber-600 transition-colors group-hover:bg-amber-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Persetujuan</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">
                            {{ $loans->where('status', 'pending')->count() }}
                        </div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-amber-500/5 rounded-full pointer-events-none"></div>
                </div>

                <!-- Card 3 -->
                <div class="relative bg-white/70 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4 overflow-hidden group hover:scale-[1.02] hover:shadow-md transition-all duration-300">
                    <div class="p-4 bg-blue-50 rounded-2xl text-blue-600 transition-colors group-hover:bg-blue-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Sedang Dipinjam</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">
                            {{ $loans->whereIn('status', ['approved', 'borrowed'])->count() }}
                        </div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-blue-500/5 rounded-full pointer-events-none"></div>
                </div>

                <!-- Card 4 -->
                <div class="relative bg-white/70 backdrop-blur-md p-6 rounded-3xl border border-gray-100 shadow-sm flex items-center space-x-4 overflow-hidden group hover:scale-[1.02] hover:shadow-md transition-all duration-300">
                    <div class="p-4 bg-emerald-50 rounded-2xl text-emerald-600 transition-colors group-hover:bg-emerald-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Selesai Kembali</div>
                        <div class="text-3xl font-black text-gray-900 mt-1">
                            {{ $loans->where('status', 'returned')->count() }}
                        </div>
                    </div>
                    <div class="absolute -right-6 -bottom-6 w-24 h-24 bg-emerald-500/5 rounded-full pointer-events-none"></div>
                </div>
            </div>

            <!-- Filter Panel -->
            @if(in_array(auth()->user()->role, ['admin', 'superadmin', 'waka_sarpras', 'kepala_sekolah', 'staf_tu']))
                <div class="bg-white/90 backdrop-blur-md p-6 rounded-3xl shadow-sm border border-gray-100">
                    <form action="{{ route('assets.loans.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Cari Peminjam atau Aset</label>
                            <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-xl border-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500" placeholder="Nama peminjam, nama aset, kode...">
                        </div>
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase mb-1">Status Permohonan</label>
                            <select name="status" class="w-full rounded-xl border-gray-100 text-sm focus:border-primary-500 focus:ring-primary-500">
                                <option value="">Semua Status</option>
                                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                <option value="returned" {{ request('status') === 'returned' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                <option value="lost" {{ request('status') === 'lost' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full py-2.5 bg-gray-900 text-white rounded-xl font-bold text-sm hover:bg-gray-800 transition-all shadow-sm">Filter & Cari</button>
                        </div>
                    </form>
                </div>
            @endif

            <!-- Loans Table -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest">Aset & Kode</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest font-sans">Peminjam</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest font-sans">Tanggal Pinjam</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest font-sans">Estimasi Kembali</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest font-sans">Status</th>
                            <th class="px-6 py-4 text-[11px] font-black text-gray-400 uppercase tracking-widest text-right font-sans">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($loans as $loan)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-5">
                                    <div class="font-black text-gray-900">{{ $loan->asset->name }}</div>
                                    <div class="text-[10px] font-bold text-primary-600 font-mono tracking-tighter">{{ $loan->asset->asset_code }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-bold text-gray-700 text-sm">{{ $loan->borrower->name }}</div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase">{{ $loan->borrower->role }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($loan->borrowed_at)
                                        <div class="text-sm font-semibold text-gray-700">{{ $loan->borrowed_at->format('d M Y') }}</div>
                                        <div class="text-[10px] text-gray-400 font-mono">{{ $loan->borrowed_at->format('H:i') }}</div>
                                    @else
                                        <span class="text-xs text-gray-400 font-bold italic">Belum Disetujui</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5">
                                    <div class="text-sm font-semibold text-gray-700">{{ $loan->expected_return_at->format('d M Y') }}</div>
                                    <div class="text-[10px] font-bold {{ $loan->expected_return_at->isPast() && !$loan->returned_at ? 'text-red-500' : 'text-gray-400' }}">
                                        {{ $loan->expected_return_at->isPast() && !$loan->returned_at ? 'Terlewat Batas!' : 'Tepat Waktu' }}
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    @if($loan->status === 'pending')
                                        <span class="px-3 py-1 bg-amber-50 border border-amber-100 text-amber-600 rounded-full text-[10px] font-black uppercase">PENGENDALIAN</span>
                                    @elseif($loan->status === 'approved' || $loan->status === 'borrowed')
                                        <span class="px-3 py-1 bg-blue-50 border border-blue-100 text-blue-600 rounded-full text-[10px] font-black uppercase">SEDANG DIPINJAM</span>
                                    @elseif($loan->status === 'returned')
                                        <span class="px-3 py-1 bg-emerald-50 border border-emerald-100 text-emerald-600 rounded-full text-[10px] font-black uppercase">KEMBALI</span>
                                    @elseif($loan->status === 'rejected')
                                        <span class="px-3 py-1 bg-red-50 border border-red-100 text-red-600 rounded-full text-[10px] font-black uppercase">DITOLAK</span>
                                    @elseif($loan->status === 'lost')
                                        <span class="px-3 py-1 bg-gray-100 border border-gray-200 text-gray-700 rounded-full text-[10px] font-black uppercase">HILANG</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-right">
                                    <a href="{{ route('assets.loans.show', $loan->uuid) }}" class="px-4 py-2 bg-gray-100 hover:bg-primary-50 text-gray-600 hover:text-primary-600 rounded-xl text-xs font-black transition-all inline-block shadow-sm">
                                        Periksa Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400 italic text-sm">Belum ada pengajuan peminjaman aset.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($loans->hasPages())
                    <div class="px-6 py-4 border-t border-gray-50">
                        {{ $loans->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
