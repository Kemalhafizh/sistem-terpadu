<x-app-layout>
    <x-slot name="header">
        <h2 class="font-extrabold text-2xl text-slate-800 dark:text-slate-100 tracking-tight flex items-center gap-3">
            <span class="p-2 bg-emerald-100 text-emerald-700 rounded-xl dark:bg-emerald-950 dark:text-emerald-350"></span>
            Dashboard Pengawasan & Audit MPK
        </h2>
    </x-slot>

    <!-- breadcrumbs -->
    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Lembaga MPK', 'url' => '#', 'active' => true]
        ]" />
    </x-slot>

    <div class="space-y-6 font-sans text-slate-800 dark:text-slate-200">
        
        <!-- Tab Navigation -->
        <div class="flex flex-wrap gap-2 border-b border-slate-200 dark:border-slate-800 pb-3">
            <a href="{{ route('mpk.index') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-sm transition-all">
                 Audit Keuangan
            </a>
            <a href="{{ route('mpk.lpj.index') }}" class="px-4 py-2 bg-white text-slate-650 hover:bg-slate-50 border border-slate-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                 Repositori LPJ OSIS
            </a>
            <a href="{{ route('mpk.aspirations.index') }}" class="px-4 py-2 bg-white text-slate-650 hover:bg-slate-50 border border-slate-200 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                 Kotak Suara Aspirasi
            </a>
        </div>

        <!-- Session Status -->
        @if(session('success'))
            <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl text-xs font-semibold flex items-center gap-3">
                <span></span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Financial KPI Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Saldo Card -->
            <div class="bg-white border border-slate-100 dark:bg-gray-800 dark:border-gray-700 p-6 rounded-3xl shadow-xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-550/5 rounded-full blur-2xl"></div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2">Saldo Kas OSIS Terkini</p>
                <h3 class="text-3xl font-black text-slate-900 dark:text-white leading-none">
                    Rp {{ number_format($currentBalance, 0, ',', '.') }}
                </h3>
                <div class="mt-4 flex gap-4 text-[10px] font-bold uppercase tracking-wider">
                    <span class="text-emerald-600">Pemasukan: Rp {{ number_format($totalIncome, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Total Audit Card -->
            <div class="bg-white border border-slate-100 dark:bg-gray-800 dark:border-gray-700 p-6 rounded-3xl shadow-xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-blue-550/5 rounded-full blur-2xl"></div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2">Status Audit Kas</p>
                <h3 class="text-3xl font-black text-slate-900 dark:text-white leading-none">
                    {{ $verifiedCount + $flaggedCount }} / {{ $totalTransactions }}
                </h3>
                <div class="mt-4 flex gap-4 text-[10px] font-bold uppercase tracking-wider">
                    <span class="text-emerald-600">Terverifikasi: {{ $verifiedCount }}</span>
                    <span class="text-rose-500">Flagged: {{ $flaggedCount }}</span>
                </div>
            </div>

            <!-- Pending Card -->
            <div class="bg-white border border-slate-100 dark:bg-gray-800 dark:border-gray-700 p-6 rounded-3xl shadow-xl relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-24 h-24 bg-amber-550/5 rounded-full blur-2xl"></div>
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest leading-none mb-2">Belum Diaudit (Pending)</p>
                <h3 class="text-3xl font-black text-slate-900 dark:text-white leading-none">
                    {{ $pendingAuditCount }}
                </h3>
                <div class="mt-4 text-[10px] font-bold text-amber-600 uppercase tracking-wider">
                    Memerlukan Verifikasi Pengawas
                </div>
            </div>
        </div>

        <!-- OSIS Transactions Audit Table -->
        <div class="bg-white border border-slate-100 dark:bg-gray-800 dark:border-gray-700 rounded-3xl shadow-xl p-6 relative overflow-hidden" x-data="{ 
            selectedTx: null,
            openAuditModal(tx) {
                this.selectedTx = tx;
            }
        }">
            <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
                <div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Audit Aliran Kas OSIS</h3>
                    <p class="text-xs text-slate-400 font-medium">Daftar transaksi pemasukan & pengeluaran kas OSIS resmi</p>
                </div>
                <!-- Export Report OSIS -->
                @if(in_array(auth()->user()->role, ['waka_kesiswaan', 'admin', 'superadmin', 'kepala_sekolah']))
                <a href="{{ route('osis.dashboard') }}" class="px-4 py-2 bg-emerald-100 text-emerald-700 hover:bg-emerald-200 dark:bg-emerald-950/40 dark:text-emerald-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                     Masuk Dashboard OSIS
                </a>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 dark:border-slate-700 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                            <th class="py-4 px-3">Tanggal</th>
                            <th class="py-4 px-3">Deskripsi</th>
                            <th class="py-4 px-3">Kategori</th>
                            <th class="py-4 px-3">Tipe</th>
                            <th class="py-4 px-3">Jumlah</th>
                            <th class="py-4 px-3">Status Audit</th>
                            <th class="py-4 px-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-gray-700/20 transition-colors">
                                <td class="py-4 px-3 whitespace-nowrap font-semibold text-slate-500 dark:text-slate-400">
                                    {{ $tx->transaction_date->format('d M Y') }}
                                </td>
                                <td class="py-4 px-3 font-bold text-slate-800 dark:text-slate-200">
                                    {{ $tx->description }}
                                </td>
                                <td class="py-4 px-3 whitespace-nowrap">
                                    <span class="px-2.5 py-1 bg-slate-100 text-slate-700 dark:bg-gray-700 dark:text-gray-300 rounded-lg text-[10px] font-bold uppercase tracking-wider border border-slate-200/20">
                                        {{ $tx->category }}
                                    </span>
                                </td>
                                <td class="py-4 px-3 whitespace-nowrap">
                                    @if($tx->type === 'income')
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                                            Masuk
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 rounded-lg text-[10px] font-extrabold uppercase tracking-wider">
                                            Keluar
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-3 whitespace-nowrap font-extrabold text-slate-900 dark:text-white">
                                    Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-3 whitespace-nowrap">
                                    @if($tx->audit)
                                        @if($tx->audit->status === 'verified')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 rounded-full text-[10px] font-bold border border-emerald-200/50" title="Diverifikasi oleh {{ $tx->audit->auditor->name }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                                Terverifikasi
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 rounded-full text-[10px] font-bold border border-rose-200/50" title="Flagged oleh {{ $tx->audit->auditor->name }}: {{ $tx->audit->notes }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                Flagged (Revisi)
                                            </span>
                                        @endif
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 text-slate-600 dark:bg-gray-700 dark:text-gray-400 rounded-full text-[10px] font-bold border border-slate-200/50">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Belum Diaudit
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-3 whitespace-nowrap text-right">
                                    <div class="flex justify-end gap-2">
                                        @if($tx->receipt_path)
                                            <a href="{{ asset('storage/' . $tx->receipt_path) }}" target="_blank" class="p-1.5 hover:bg-slate-100 dark:hover:bg-gray-700 rounded-lg text-slate-500 dark:text-slate-400 hover:text-emerald-600 transition-colors" title="Lihat Bukti Nota">
                                                
                                            </a>
                                        @endif
                                        @if(in_array(auth()->user()->role, ['waka_kesiswaan', 'admin', 'superadmin', 'kepala_sekolah']))
                                            <button 
                                                @click="openAuditModal({{ json_encode($tx) }})" 
                                                class="px-3 py-1.5 bg-emerald-650 hover:bg-emerald-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider transition-all"
                                            >
                                                 Audit
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400 italic">
                                    Tidak ada transaksi kas OSIS terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $transactions->links() }}
            </div>

            <!-- Alpine.js Audit Modal -->
            <div 
                x-show="selectedTx" 
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                style="display: none;"
            >
                <div 
                    class="bg-white dark:bg-gray-800 border border-slate-100 dark:border-gray-700 w-full max-w-md rounded-3xl shadow-2xl p-6 relative overflow-hidden"
                    @click.away="selectedTx = null"
                >
                    <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
                    
                    <h4 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2 mb-1">
                        Audit Transaksi OSIS
                    </h4>
                    <p class="text-[10px] text-slate-400 font-extrabold uppercase tracking-widest mb-6">Penetapan Verifikasi Legislatif</p>

                    <template x-if="selectedTx">
                        <form method="POST" action="{{ route('mpk.audit.store') }}" class="space-y-5">
                            @csrf
                            <input type="hidden" name="osis_transaction_id" :value="selectedTx.id">

                            <!-- Details Card -->
                            <div class="bg-slate-50 dark:bg-gray-700/30 p-4 rounded-2xl border border-slate-150/40 space-y-2 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-slate-400 font-bold">Deskripsi:</span>
                                    <span class="font-extrabold text-slate-800 dark:text-slate-200" x-text="selectedTx.description"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400 font-bold">Jumlah:</span>
                                    <span class="font-extrabold text-slate-900 dark:text-white" x-text="'Rp ' + new Intl.NumberFormat('id-ID').format(selectedTx.amount)"></span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400 font-bold">Pencatat:</span>
                                    <span class="font-extrabold text-slate-850 dark:text-slate-250" x-text="selectedTx.recorder.name"></span>
                                </div>
                            </div>

                            <!-- Audit Status -->
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black text-emerald-850 uppercase tracking-widest">Keputusan Status Audit</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 dark:bg-gray-800 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-slate-50/50 transition-colors">
                                        <input type="radio" name="status" value="verified" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500/10" :checked="selectedTx.audit && selectedTx.audit.status === 'verified'">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-350">Verified</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 bg-white border border-slate-200 dark:bg-gray-800 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-slate-50/50 transition-colors">
                                        <input type="radio" name="status" value="flagged" class="w-4 h-4 text-rose-600 focus:ring-rose-500/10" :checked="selectedTx.audit && selectedTx.audit.status === 'flagged'">
                                        <span class="text-xs font-bold text-slate-700 dark:text-slate-350">Flagged (Revisi)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Audit Notes -->
                            <div class="space-y-2">
                                <label for="notes" class="block text-[10px] font-black text-emerald-850 uppercase tracking-widest">Catatan Evaluasi / Temuan</label>
                                <textarea 
                                    id="notes" 
                                    name="notes" 
                                    rows="3" 
                                    class="block w-full border border-slate-200 rounded-2xl text-xs text-slate-800 focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 focus:outline-none p-3.5"
                                    placeholder="Masukkan temuan audit atau rincian jika ada kecurigaan anggaran..."
                                    x-text="selectedTx.audit ? selectedTx.audit.notes : ''"
                                ></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="flex justify-end gap-3 pt-2">
                                <button type="button" @click="selectedTx = null" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-black uppercase tracking-wider transition-colors">
                                    Batal
                                </button>
                                <button type="submit" class="px-5 py-2 bg-emerald-650 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-emerald-650/10 transition-colors">
                                    Simpan Hasil Audit
                                </button>
                            </div>
                        </form>
                    </template>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
