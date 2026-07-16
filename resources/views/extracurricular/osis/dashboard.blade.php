<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-emerald-100 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mr-3">
                        🎒
                    </span>
                    {{ __('Dashboard OSIS & Kesiswaan') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 SEMARANG</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                @if(auth()->user()->role !== 'student')
                    <a href="{{ route('osis.members') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md">
                        👥 Kepengurusan
                    </a>
                    <a href="{{ route('osis.work-programs') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md">
                        📋 Program Kerja
                    </a>
                    <a href="{{ route('osis.elections') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md">
                        🗳️ Pemilihan OSIS
                    </a>
                    <button type="button" @click="$dispatch('open-modal', 'add-transaction-modal')" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md">
                        ➕ Catat Kas Baru
                    </button>
                @endif
                
                <button type="button" @click="$dispatch('open-modal', 'export-pdf-modal')" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 dark:hover:bg-gray-750 transition-all shadow-sm">
                    📜 Cetak Laporan PDF
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/50 flex items-center text-emerald-800 dark:text-emerald-300 animate-fade-in">
                    <span class="text-sm font-bold">✅ {{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex items-center text-red-800 dark:text-red-300 animate-fade-in">
                    <span class="text-sm font-bold">❌ {{ session('error') }}</span>
                </div>
            @endif

            <!-- 3-Grid KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Stat 1: Total Pemasukan -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between group">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Total Pemasukan</p>
                        <h3 class="text-3xl font-extrabold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($totalIncome, 0, ',', '.') }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Penerimaan kas OSIS</p>
                    </div>
                </div>

                <!-- Stat 2: Total Pengeluaran -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between group">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Total Pengeluaran</p>
                        <h3 class="text-3xl font-extrabold text-red-600 dark:text-red-500">Rp {{ number_format($totalExpense, 0, ',', '.') }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Pengeluaran kas OSIS</p>
                    </div>
                </div>

                <!-- Stat 3: Saldo Aktif -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-200/50 dark:border-gray-700/50 flex items-center justify-between group">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Saldo Bersih</p>
                        <h3 class="text-3xl font-extrabold text-teal-600 dark:text-teal-400">Rp {{ number_format($currentBalance, 0, ',', '.') }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Sisa saldo aktif</p>
                    </div>
                </div>
            </div>

            <!-- E-Voting & Proker Quick Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Pemilu OSIS -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-200/50 dark:border-gray-700/50 shadow-md space-y-4">
                    <h3 class="font-extrabold text-sm text-gray-800 dark:text-white flex items-center">
                        🗳️ Pemilihan Ketua OSIS Aktif
                    </h3>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($elections as $el)
                            <div class="py-3 flex justify-between items-center text-xs">
                                <div>
                                    <span class="font-bold text-gray-800 dark:text-white">{{ $el->title }}</span>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Periode: {{ $el->voting_start->format('d M') }} s/d {{ $el->voting_end->format('d M Y') }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <a href="{{ route('osis.elections.voting', $el->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-1 px-3 text-[10px] font-bold transition-all shadow-md">
                                        🗳️ Bilik Suara
                                    </a>
                                    <a href="{{ route('osis.elections.results', $el->id) }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-1 px-3 text-[10px] font-bold transition-all">
                                        📈 Hasil
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic text-center py-6">Tidak ada pemilihan Ketua OSIS aktif.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Program Kerja Terbaru -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-200/50 dark:border-gray-700/50 shadow-md space-y-4">
                    <h3 class="font-extrabold text-sm text-gray-800 dark:text-white flex items-center">
                        📋 Program Kerja OSIS
                    </h3>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @forelse($programs as $prog)
                            <div class="py-3 flex justify-between items-center text-xs">
                                <div>
                                    <span class="font-bold text-gray-800 dark:text-white">{{ $prog->title }}</span>
                                    <p class="text-[10px] text-gray-400 mt-0.5">{{ $prog->sekbid }} | Realisasi: Rp {{ number_format($prog->budget_actual, 0, ',', '.') }}</p>
                                </div>
                                <span class="px-2 py-0.5 rounded-lg text-[9px] font-extrabold {{ $prog->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                                    {{ strtoupper($prog->status) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic text-center py-6">Belum ada program kerja tercatat.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Buku Kas Table -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 border border-gray-200/50 dark:border-gray-700/50 shadow-md space-y-6">
                <div>
                    <h4 class="text-lg font-black text-gray-900 dark:text-white">
                        📖 Jurnal Kas & Transaksi OSIS
                    </h4>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700 text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Jenis</th>
                                <th class="py-3 px-4">Deskripsi Transaksi</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-right">Nominal</th>
                                @if(auth()->user()->role !== 'student')
                                    <th class="py-3 px-4 text-center">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-300">
                            @forelse($transactions as $trx)
                                <tr class="hover:bg-slate-50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="py-4 px-4 text-gray-400 dark:text-gray-500">
                                        {{ $trx->transaction_date->translatedFormat('d M Y') }}
                                    </td>
                                    <td class="py-4 px-4">
                                        @if($trx->type === 'income')
                                            <span class="px-2 py-0.5 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400 rounded-lg text-[9px] font-extrabold uppercase">Masuk</span>
                                        @else
                                            <span class="px-2 py-0.5 bg-red-50 dark:bg-red-950/20 text-red-700 dark:text-red-400 rounded-lg text-[9px] font-extrabold uppercase">Keluar</span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-4 font-extrabold text-gray-900 dark:text-white">
                                        {{ $trx->description }}
                                    </td>
                                    <td class="py-4 px-4 text-gray-500 dark:text-gray-400">
                                        {{ $trx->category }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-black {{ $trx->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                        Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                    </td>
                                    @if(auth()->user()->role !== 'student')
                                        <td class="py-4 px-4 text-center">
                                            <form action="{{ route('osis.transactions.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-rose-600 hover:text-rose-700 font-bold">Hapus</button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400 dark:text-gray-500">
                                        Belum ada data transaksi kas OSIS yang tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                    {{ $transactions->links() }}
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: Catat Kas Baru -->
    <x-modal name="add-transaction-modal" focusable>
        <form method="POST" action="{{ route('osis.transactions.store') }}" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Pencatatan Baru Kas OSIS</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Jenis Transaksi</label>
                    <select id="type" name="type" class="w-full bg-gray-55 dark:bg-gray-900 text-gray-850 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs">
                        <option value="income">Kas Masuk (Pemasukan)</option>
                        <option value="expense">Kas Keluar (Pengeluaran)</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Kategori Kas</label>
                    <select id="category" name="category" class="w-full bg-gray-55 dark:bg-gray-900 text-gray-850 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs">
                        <option value="Iuran">Iuran Bulanan Siswa</option>
                        <option value="Sponsor">Sponsorship / Sponsor</option>
                        <option value="Kegiatan">Pendanaan Kegiatan</option>
                        <option value="Perlengkapan">Perlengkapan / Aset</option>
                        <option value="Lainnya">Lainnya / Lain-lain</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nominal (Rupiah)</label>
                    <input name="amount" type="number" min="1" class="w-full bg-gray-55 dark:bg-gray-900 text-gray-850 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs" required />
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tanggal Transaksi</label>
                    <input name="transaction_date" type="date" class="w-full bg-gray-55 dark:bg-gray-900 text-gray-850 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs" value="{{ now()->toDateString() }}" required />
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Deskripsi / Catatan</label>
                <input name="description" type="text" class="w-full bg-gray-55 dark:bg-gray-900 text-gray-850 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs" required />
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-150 dark:border-gray-700">
                <button type="button" @click="$dispatch('close')" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-white rounded-xl text-xs font-bold">Batal</button>
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md">Simpan Transaksi</button>
            </div>
        </form>
    </x-modal>

    <!-- Modal: Cetak Laporan PDF -->
    <x-modal name="export-pdf-modal" focusable>
        <form method="GET" action="{{ route('osis.export') }}" class="p-6 space-y-6">
            <h3 class="text-lg font-black text-gray-900 dark:text-white">Cetak Laporan Keuangan Kas OSIS</h3>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tanggal Mulai</label>
                    <input name="start_date" type="date" class="w-full bg-gray-55 dark:bg-gray-900 text-gray-850 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs" value="{{ now()->startOfMonth()->toDateString() }}" required />
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Tanggal Selesai</label>
                    <input name="end_date" type="date" class="w-full bg-gray-55 dark:bg-gray-900 text-gray-850 dark:text-white border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-xs" value="{{ now()->toDateString() }}" required />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-150 dark:border-gray-700">
                <button type="button" @click="$dispatch('close')" class="px-4 py-2 bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-white rounded-xl text-xs font-bold">Batal</button>
                <button type="submit" @click="$dispatch('close')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md">Ekspor Unduh PDF</button>
            </div>
        </form>
    </x-modal>
</x-app-layout>
