<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">Administrasi Keuangan</h2>
            <div class="flex space-x-2" x-data="exportHub()">
                <button onclick="openModal('modal-category')" class="bg-white text-gray-700 px-4 py-2 rounded-xl font-bold text-sm shadow-sm border border-gray-200 hover:bg-gray-50 transition-all">
                    + Kategori Iuran
                </button>
                <button @click="openHub()" type="button" class="bg-emerald-600 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg shadow-emerald-900/20 hover:bg-emerald-700 transition-all flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 012-2H5a2 2 0 012 2v4a2 2 0 002 2m8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    Cetak & Ekspor
                </button>
                <button onclick="openModal('modal-bulk')" class="bg-primary-600 text-white px-4 py-2 rounded-xl font-bold text-sm shadow-lg shadow-primary-900/20 hover:bg-primary-700 transition-all">
                    Generate Tagihan Bulanan
                </button>
                
                @include('finance.partials.export-modal')
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-emerald-50 border-l-4 border-emerald-400 p-4 rounded-xl shadow-sm text-emerald-800 font-bold text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl shadow-sm text-red-800 font-bold text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                <form action="{{ route('finance.billings.index') }}" method="GET" class="flex flex-col md:flex-row items-center gap-4">
                    <div class="relative w-full md:w-96">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search" id="global-search" value="{{ request('search') }}" 
                            class="block w-full pl-10 pr-3 py-2 border border-gray-100 rounded-xl leading-5 bg-gray-50 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 sm:text-sm transition duration-150 ease-in-out" 
                            placeholder="Cari Nama Siswa atau NISN (Ctrl+K)">
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                        <select name="classroom_id" class="block w-full md:w-48 py-2 px-3 border border-gray-100 bg-gray-50 rounded-xl focus:outline-none focus:ring-primary-500 sm:text-sm">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($classrooms ?? [] as $cls)
                                <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                            @endforeach
                        </select>

                        <select name="status" class="block w-full md:w-40 py-2 px-3 border border-gray-100 bg-gray-50 rounded-xl focus:outline-none focus:ring-primary-500 sm:text-sm">
                            <option value="">-- Semua Status --</option>
                            <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Belum Bayar</option>
                            <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>Cicilan</option>
                            <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Lunas</option>
                        </select>

                        <button type="submit" class="inline-flex items-center px-6 py-2 bg-gray-800 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-700 transition ease-in-out duration-150 shadow-md">
                            Filter
                        </button>
                        
                        @if(request()->hasAny(['search', 'classroom_id', 'status']))
                            <a href="{{ route('finance.billings.index') }}" class="text-xs text-red-600 hover:text-red-800 font-bold uppercase underline tracking-tighter">Reset</a>
                        @endif
                    </div>
                </form>
            </div>

            <!-- Billings Table -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="overflow-x-auto animate-fade-in">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50/50 text-left text-[11px] font-black text-gray-400 uppercase tracking-[0.2em]">
                                <th class="p-6">Siswa</th>
                                <th class="p-6">Kategori</th>
                                <th class="p-6 text-right">Total Tagihan</th>
                                <th class="p-6 text-right">Sisa Hutang</th>
                                <th class="p-6 text-center">Status</th>
                                <th class="p-6 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($billings ?? [] as $billing)
                                <tr class="hover:bg-gray-50/30 transition-colors">
                                    <td class="p-6">
                                        <p class="font-black text-gray-800">{{ $billing->student?->user?->name }}</p>
                                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">{{ $billing->student->nis }} • {{ $billing->student?->classroom?->name ?? 'N/A' }}</p>
                                    </td>
                                    <td class="p-6">
                                        <span class="inline-block px-3 py-1 bg-gray-100 rounded-full text-[10px] font-black text-gray-500 uppercase">
                                            {{ $billing?->category?->name }} {{ $billing->month ? '- ' . $billing->month : '' }}
                                        </span>
                                    </td>
                                    <td class="p-6 text-right font-bold text-gray-700">
                                        Rp {{ number_format($billing->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="p-6 text-right font-black {{ $billing->remaining_balance > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                                        Rp {{ number_format($billing->remaining_balance, 0, ',', '.') }}
                                    </td>
                                    <td class="p-6 text-center">
                                        @switch($billing->status)
                                            @case('paid')
                                                <span class="px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-[10px] font-black tracking-widest uppercase">Lunas</span>
                                                @break
                                            @case('partial')
                                                <span class="px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-[10px] font-black tracking-widest uppercase">Cicilan</span>
                                                @break
                                            @default
                                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-[10px] font-black tracking-widest uppercase">Belum Bayar</span>
                                        @endswitch
                                    </td>
                                    <td class="p-6 text-right">
                                        <div class="flex justify-end space-x-2">
                                            @if($billing->status != 'paid')
                                                <button onclick="openPaymentModal(this)" data-id="{{ $billing->id }}" data-student-name="{{ $billing->student?->user?->name }}" data-remaining="{{ $billing->remaining_balance }}" data-category-name="{{ $billing?->category?->name }}" class="p-2 bg-emerald-100 text-emerald-700 rounded-lg hover:bg-emerald-600 hover:text-white transition-all shadow-sm" title="Input Bayar">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                </button>

                                                <form action="{{ route('finance.billings.whatsapp-reminder', $billing->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="p-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-600 hover:text-white transition-all shadow-sm" title="Kirim Pengingat WA">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M12.012 2C6.48 2 2.012 6.48 2.012 12c0 2.016.6 3.888 1.632 5.46L2.012 22l4.704-1.236A9.92 9.92 0 0012.012 22c5.532 0 10-4.468 10-10s-4.468-10-10-10zm.012 18.252c-1.848 0-3.564-.6-4.968-1.632l-.36-.216-2.736.72.732-2.664-.24-.384a8.161 8.161 0 01-1.188-4.236c0-4.548 3.708-8.256 8.268-8.256s8.256 3.708 8.256 8.256-3.708 8.256-8.256 8.256-.008 0-.008 0z"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                            
                                            <button onclick="openHistoryModal(this)" data-id="{{ $billing->id }}" data-student-name="{{ $billing->student?->user?->name }}" data-category-name="{{ $billing?->category?->name }}" class="p-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-600 hover:text-white transition-all shadow-sm" title="Riwayat & Kuitansi">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </button>

                                            <a href="{{ route('finance.billings.edit', $billing) }}" class="p-2 bg-amber-100 text-amber-700 rounded-lg hover:bg-amber-600 hover:text-white transition-all shadow-sm" title="Edit Tagihan">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                            </a>

                                            <form action="{{ route('finance.billings.sync', $billing->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" title="Sinkron ulang data" class="p-2 bg-gray-100 text-gray-500 rounded-lg hover:bg-gray-800 hover:text-white transition-all shadow-sm">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-20 text-center text-gray-400 italic">Belum ada data tagihan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-6 bg-gray-50/30">
                    {{ $billings->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Category -->
    <div id="modal-category" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] p-10 max-w-md w-full shadow-2xl relative overflow-hidden">
            <h3 class="text-xl font-black text-gray-800 mb-6">Master Kategori Pembayaran</h3>
            <form action="{{ route('finance.categories.store') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Nama Iuran</label>
                        <input type="text" name="name" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500" placeholder="Contoh: SPP Bulanan" required>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Nominal Default</label>
                        <input type="number" name="default_amount" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500" placeholder="500000" required>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="is_monthly" value="1" class="rounded border-gray-100 text-primary-600 focus:ring-primary-500 mr-2">
                        <span class="text-sm font-bold text-gray-600 tracking-tight">Iuran Bulanan?</span>
                    </div>
                </div>
                <div class="flex space-x-3 mt-8">
                    <button type="button" onclick="closeModal('modal-category')" class="flex-1 py-3 bg-gray-100 text-gray-500 font-bold rounded-xl text-sm">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-primary-600 text-white font-black rounded-xl text-sm shadow-lg shadow-primary-900/20">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Bulk Generate -->
    <div id="modal-bulk" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2rem] p-10 max-w-lg w-full shadow-2xl relative overflow-hidden">
            <h3 class="text-xl font-black text-gray-800 mb-6">Generate Tagihan Masal</h3>
            <form action="{{ route('finance.billings.bulk') }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Pilih Kategori</label>
                        <select name="payment_category_id" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-sm" required>
                            @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Tahun Ajaran</label>
                            <select name="academic_year_id" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-sm" required>
                                @foreach(\App\Models\AcademicYear::all() as $ay)
                                    <option value="{{ $ay->id }}">{{ $ay->year }} / {{ $ay->semester }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Bulan (Opsional)</label>
                            <select name="month" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-sm">
                                <option value="">N/A</option>
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] ?? [] as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Nominal Tagihan</label>
                        <input type="number" name="amount" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500" placeholder="500000" required>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Jatuh Tempo</label>
                        <input type="date" name="due_date" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500" required>
                    </div>
                </div>
                <div class="flex space-x-3 mt-8">
                    <button type="button" onclick="closeModal('modal-bulk')" class="flex-1 py-3 bg-gray-100 text-gray-500 font-bold rounded-xl text-sm">Batal</button>
                    <button type="submit" class="flex-1 py-3 bg-primary-600 text-white font-black rounded-xl text-sm shadow-lg shadow-primary-900/20">Eksekusi Massal</button>
                </div>
                <p class="text-[10px] text-gray-400 mt-4 text-center italic">Satu klik akan membuat tagihan untuk SEMUA siswa yang belum memiliki tagihan di periode/bulan tersebut.</p>
            </form>
        </div>
    </div>

    <!-- Modal Payment -->
    <div id="modal-payment" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-lg w-full shadow-2xl relative overflow-hidden">
            <h3 class="text-2xl font-black text-gray-800 mb-2">Input Pembayaran</h3>
            <p class="text-primary-600 font-bold text-sm mb-8" id="pay-info">Tagihan: -</p>

            <form action="{{ route('finance.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="billing_id" id="pay-billing-id">
                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Sisa Hutang</label>
                        <div class="text-3xl font-black text-red-500" id="pay-remaining-text">Rp 0</div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Jumlah Bayar</label>
                        <input type="number" name="amount_paid" id="pay-amount-input" class="w-full rounded-2xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-xl font-black" required>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Metode</label>
                            <select name="payment_method" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-sm">
                                <option value="cash">Tunai / Cash</option>
                                <option value="transfer">Transfer Bank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Tanggal Bayar</label>
                            <input type="date" name="payment_date" value="{{ date('Y-m-d') }}" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-sm" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-1">Catatan Tambahan</label>
                        <textarea name="note" rows="2" class="w-full rounded-xl border-gray-100 bg-gray-50 focus:ring-primary-500 text-sm" placeholder="Contoh: Titipan orang tua via staf"></textarea>
                    </div>
                </div>
                <div class="flex space-x-3 mt-10">
                    <button type="button" onclick="closeModal('modal-payment')" class="flex-1 py-4 bg-gray-100 text-gray-500 font-bold rounded-2xl text-sm">Batal</button>
                    <button type="submit" class="flex-1 py-4 bg-primary-600 text-white font-black rounded-2xl text-sm shadow-xl shadow-primary-900/20 hover:scale-[1.02] transition-all">SIMPAN & CETAK KWITANSI</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal History -->
    <div id="modal-history" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4">
        <div class="bg-white rounded-[2.5rem] p-10 max-w-2xl w-full shadow-2xl relative overflow-hidden">
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h3 class="text-2xl font-black text-gray-800">Riwayat Pembayaran</h3>
                    <p class="text-primary-600 font-bold text-sm" id="history-info">Siswa: -</p>
                </div>
                <button onclick="closeModal('modal-history')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="overflow-hidden border border-gray-100 rounded-2xl">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-[10px] font-black text-gray-400 uppercase tracking-widest">
                            <th class="px-6 py-4">Tgl Bayar</th>
                            <th class="px-6 py-4">No. Kuitansi</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="history-table-body" class="divide-y divide-gray-50">
                        <!-- Content loaded via JS -->
                    </tbody>
                </table>
            </div>
            <div class="mt-8">
                <button onclick="closeModal('modal-history')" class="w-full py-4 bg-gray-800 text-white font-black rounded-2xl text-sm">TUTUP</button>
            </div>
        </div>
    </div>

    <script type="application/json" id="billings-data">
        @json($billings->items())
    </script>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
        function openPaymentModal(btn) {
            const id = btn.getAttribute('data-id');
            const studentName = btn.getAttribute('data-student-name');
            const remaining = parseFloat(btn.getAttribute('data-remaining'));
            const category = btn.getAttribute('data-category-name');
            openModal('modal-payment');
            document.getElementById('pay-billing-id').value = id;
            document.getElementById('pay-info').innerText = "Siswa: " + studentName + " (" + category + ")";
            document.getElementById('pay-remaining-text').innerText = "Rp " + new Intl.NumberFormat('id-ID').format(remaining);
            document.getElementById('pay-amount-input').value = remaining;
            document.getElementById('pay-amount-input').max = remaining;
        }

        const billingsData = JSON.parse(document.getElementById('billings-data').textContent);

        function exportHub() {
            return {
                open: false,
                title: 'LAPORAN DAFTAR TAGIHAN KEUANGAN SISWA',
                subtitle: 'SMA Terpadu Premium Academic System',
                orientation: 'portrait',
                theme: 'emerald',
                columns: {
                    no: true,
                    siswa: true,
                    kategori: true,
                    tagihan: true,
                    hutang: true,
                    status: true
                },
                showSignature: true,
                signatureRole: 'Bendahara Sekolah',
                signatureName: '{{ auth()->user()->name }}',
                billings: [],
                init() {
                    const dataEl = document.getElementById('billings-data');
                    if (dataEl) {
                        this.billings = JSON.parse(dataEl.textContent);
                    }
                },
                openHub() {
                    this.open = true;
                    document.body.classList.add('overflow-hidden');
                },
                closeHub() {
                    this.open = false;
                    document.body.classList.remove('overflow-hidden');
                },
                printReport() {
                    window.print();
                },
                formatCurrency(val) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
                }
            }
        }

        function openHistoryModal(btn) {
            const id = parseInt(btn.getAttribute('data-id'));
            const studentName = btn.getAttribute('data-student-name');
            const category = btn.getAttribute('data-category-name');
            openModal('modal-history');
            document.getElementById('history-info').innerText = "Siswa: " + studentName + " (" + category + ")";
            
            const billing = billingsData.find(b => b.id === id);
            const tbody = document.getElementById('history-table-body');
            tbody.innerHTML = '';

            if (!billing || !billing.payments || billing.payments.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="px-6 py-8 text-center text-gray-400 italic">Belum ada riwayat pembayaran.</td></tr>';
                return;
            }

            billing.payments.forEach(pay => {
                const date = new Date(pay.payment_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                const amount = new Intl.NumberFormat('id-ID').format(pay.amount_paid);
                const url = `{{ url('finance/payments') }}/${pay.id}/receipt`;

                tbody.innerHTML += `
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-6 py-4 text-sm font-bold text-gray-700">${date}</td>
                        <td class="px-6 py-4 text-sm text-gray-500 font-mono">${pay.receipt_number}</td>
                        <td class="px-6 py-4 text-right text-sm font-black text-emerald-600">Rp ${amount}</td>
                        <td class="px-6 py-4 text-center">
                            <a href="${url}" target="_blank" class="inline-flex p-2 bg-gray-100 text-gray-600 rounded-lg hover:bg-primary-600 hover:text-white transition-all shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 012-2H5a2 2 0 012 2v4a2 2 0 002 2m8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            </a>
                        </td>
                    </tr>
                `;
            });
        }
    </script>
</x-app-layout>
