<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Keuangan Sekolah</span>
                <h2 class="font-black text-3xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight mt-1">Canteen Cashless Merchants</h2>
            </div>
            <div class="flex items-center space-x-2">
                <span class="px-4 py-2 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 rounded-2xl text-xs font-bold border border-teal-200/50 dark:border-teal-900/30">
                    Sistem Kasir Terintegrasi
                </span>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
        
        <!-- Status Messages -->
        @if(session('success'))
            <div class="bg-emerald-50 dark:bg-emerald-950/20 border-l-4 border-emerald-500 p-4 rounded-2xl shadow-sm text-emerald-800 dark:text-emerald-300 font-bold text-sm flex items-center space-x-2 animate-fade-in">
                <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        @if(session('error'))
            <div class="bg-red-50 dark:bg-red-950/20 border-l-4 border-red-500 p-4 rounded-2xl shadow-sm text-red-800 dark:text-red-300 font-bold text-sm flex items-center space-x-2 animate-fade-in">
                <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            <!-- Form Card (1 col) -->
            <div class="bg-white dark:bg-gray-800 p-8 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700/80 transition-all duration-300 hover:shadow-md">
                <div class="mb-6">
                    <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400 font-sans">Registrasi</span>
                    <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight mt-1">Tambah Merchant Baru</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Daftarkan pemilik toko/kantin baru ke sistem pembayaran cashless.</p>
                </div>

                <form action="{{ route('admin.finance.canteen.merchants.store') }}" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label for="name" class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2 block">Nama Kantin / Tenant</label>
                        <input type="text" name="name" id="name" required placeholder="Contoh: Kopi Kebaikan / Kantin Jujur"
                            class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 py-3 px-4 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-gray-800 dark:text-gray-100 text-sm transition-all" />
                        @error('name')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="owner_user_id" class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2 block">Pemilik Toko (User)</label>
                        <select name="owner_user_id" id="owner_user_id" required
                            class="w-full rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 py-3 px-4 focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-gray-800 dark:text-gray-100 text-sm transition-all">
                            <option value="" disabled selected>-- Pilih Pengguna --</option>
                            @foreach($potentialOwners as $owner)
                                <option value="{{ $owner->id }}">{{ $owner->name }} ({{ strtoupper($owner->role) }} - {{ $owner->email }})</option>
                            @endforeach
                        </select>
                        @error('owner_user_id')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="w-full py-4 bg-teal-600 hover:bg-teal-700 text-white font-black rounded-2xl text-xs uppercase tracking-widest shadow-lg shadow-teal-600/20 hover:shadow-teal-600/35 transition-all duration-300 transform active:scale-95">
                        Daftarkan Merchant
                    </button>
                </form>
            </div>

            <!-- List Card (2 cols) -->
            <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700/80 overflow-hidden">
                <div class="p-8 border-b border-gray-50 dark:border-gray-700/50">
                    <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Database</span>
                    <h3 class="text-xl font-black text-gray-800 dark:text-gray-100 tracking-tight mt-1">Daftar Merchant Kantin</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-900/30 text-left text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest border-b border-gray-100 dark:border-gray-700/50">
                                <th class="py-5 px-8">Merchant</th>
                                <th class="py-5 px-8">Pemilik / Owner</th>
                                <th class="py-5 px-8 text-center">Status</th>
                                <th class="py-5 px-8 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @forelse($merchants as $merchant)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-900/20 transition-colors">
                                    <td class="py-6 px-8">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 rounded-xl bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 flex items-center justify-center font-bold">
                                                {{ strtoupper(substr($merchant->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <p class="font-black text-gray-800 dark:text-gray-200">{{ $merchant->name }}</p>
                                                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">ID: #{{ $merchant->id }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-6 px-8">
                                        <p class="font-bold text-gray-700 dark:text-gray-300">{{ $merchant->owner->name ?? 'N/A' }}</p>
                                        <p class="text-[10px] text-gray-400 tracking-tight">{{ $merchant->owner->email ?? 'N/A' }}</p>
                                    </td>
                                    <td class="py-6 px-8 text-center">
                                        @if($merchant->is_active)
                                            <span class="inline-flex px-3 py-1 bg-emerald-100 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[10px] font-black tracking-widest uppercase">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="inline-flex px-3 py-1 bg-red-100 dark:bg-red-950/30 text-red-700 dark:text-red-400 rounded-full text-[10px] font-black tracking-widest uppercase">
                                                Non-Aktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-6 px-8 text-right">
                                        <form action="{{ route('admin.finance.canteen.merchants.toggle', $merchant->id) }}" method="POST" class="inline">
                                            @csrf
                                            @if($merchant->is_active)
                                                <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white dark:bg-red-950/20 dark:hover:bg-red-600 dark:text-red-400 dark:hover:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 border border-red-200/50 dark:border-red-900/30">
                                                    Nonaktifkan
                                                </button>
                                            @else
                                                <button type="submit" class="px-4 py-2 bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white dark:bg-emerald-950/20 dark:hover:bg-emerald-600 dark:text-emerald-400 dark:hover:text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all duration-200 border border-emerald-200/50 dark:border-emerald-900/30">
                                                    Aktifkan
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-16 text-center text-gray-400 dark:text-gray-500 italic text-sm">
                                        Belum ada merchant kantin yang terdaftar.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
