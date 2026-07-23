<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4" 
             x-data="{}">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <a href="{{ route('uks.dashboard') }}" class="hover:text-teal-600 transition-colors mr-2">
                        <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </span>
                    </a>
                    {{ __('Inventaris Obat UKS') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • MANAJEMEN STOK & KADALUWARSA</p>
            </div>
            
            <button type="button" @click="$dispatch('open-modal', 'add-medicine-modal')"
                    class="inline-flex items-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Tambah Obat Baru
            </button>
        </div>
    </x-slot>

    <div class="py-8" x-data="{
        editMedicineId: null,
        editName: '',
        editCategory: 'Obat Umum',
        editStock: 0,
        editMinStock: 5,
        editExpiryDate: '',
        editNotes: '',
        
        openEditModal(med) {
            this.editMedicineId = med.id;
            this.editName = med.name;
            this.editCategory = med.category;
            this.editStock = med.stock;
            this.editMinStock = med.min_stock;
            this.editExpiryDate = med.expiry_date ? med.expiry_date.split('T')[0] : '';
            this.editNotes = med.notes || '';
            $dispatch('open-modal', 'edit-medicine-modal');
        }
    }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/50 flex items-center text-teal-800 dark:text-teal-300">
                    <svg class="w-5 h-5 mr-3 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex items-center text-red-800 dark:text-red-300">
                    <svg class="w-5 h-5 mr-3 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Grid Card Katalog -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @forelse($medicines as $med)
                    @php
                        $isLowStock = $med->stock <= $med->min_stock;
                        $isExpired = $med->expiry_date && $med->expiry_date->isPast();
                        $isExpiringSoon = $med->expiry_date && !$isExpired && $med->expiry_date->diffInDays(now()) <= 30;
                        
                        $stockPct = $med->stock > 0 ? min(100, max(0, ($med->stock / ($med->min_stock * 2)) * 100)) : 0;
                        
                        $catColors = [
                            'P3K' => 'bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-250',
                            'Vitamin' => 'bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border-blue-250',
                            'Obat Umum' => 'bg-purple-50 dark:bg-purple-950/30 text-purple-700 dark:text-purple-400 border-purple-250'
                        ];
                        $catColor = $catColors[$med->category] ?? 'bg-gray-50 text-gray-700';
                    @endphp
                    
                    <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2.5rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col justify-between group hover:border-teal-500/30 transition-all duration-300 relative overflow-hidden">
                        
                        <!-- Top actions / Category -->
                        <div class="flex justify-between items-center mb-4">
                            <span class="px-2.5 py-1 {{ $catColor }} border rounded-lg text-[9px] font-black uppercase tracking-widest">
                                {{ $med->category }}
                            </span>
                            
                            <div class="flex items-center gap-1">
                                <!-- Edit button (Alpine) -->
                                <button type="button" @click="openEditModal({{ $med->toJson() }})" title="Edit Detail"
                                        class="p-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-150 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-500 dark:text-gray-400 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </button>
                                
                                <!-- Delete form -->
                                <form action="{{ route('uks.medicines.destroy', $med->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus obat ini dari inventaris sekolah? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus Obat" 
                                            class="p-1.5 bg-gray-50 dark:bg-gray-900 border border-gray-150 dark:border-gray-700 rounded-xl hover:bg-red-50 dark:hover:bg-red-950/20 text-red-600 transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Info -->
                        <div class="space-y-2 flex-grow">
                            <h4 class="text-base font-extrabold text-gray-900 dark:text-white leading-snug tracking-tight">
                                {{ $med->name }}
                            </h4>
                            
                            @if($med->notes)
                                <p class="text-xs text-gray-450 dark:text-gray-400 leading-normal line-clamp-2 italic" title="{{ $med->notes }}">
                                    "{{ $med->notes }}"
                                </p>
                            @else
                                <p class="text-xs text-gray-300 dark:text-gray-600 italic">Tidak ada catatan obat.</p>
                            @endif
                        </div>

                        <!-- Progress Bar Stock -->
                        <div class="mt-6 space-y-2">
                            <div class="flex justify-between items-center text-xs">
                                <span class="font-bold text-gray-400 dark:text-gray-500">Ketersediaan</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-extrabold {{ $isLowStock ? 'bg-red-100 dark:bg-red-950/50 text-red-700 dark:text-red-400' : 'bg-teal-100 dark:bg-teal-950/50 text-teal-700 dark:text-teal-400' }}">
                                    {{ $med->stock }} Pcs
                                </span>
                            </div>
                            <div class="w-full bg-gray-150 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full {{ $isLowStock ? 'bg-red-500' : 'bg-teal-500' }}" style="width: {{ $stockPct }}%"></div>
                            </div>
                            <div class="flex justify-between text-[10px] font-bold text-gray-400 dark:text-gray-500">
                                <span>Min: {{ $med->min_stock }}</span>
                                <span>Max Refill: {{ $med->min_stock * 2 }}</span>
                            </div>
                        </div>

                        <!-- Expiry Info -->
                        <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center flex-wrap gap-2 text-[10px] font-bold text-gray-400 dark:text-gray-500">
                            <span>Masa Kedaluwarsa</span>
                            @if($med->expiry_date)
                                @if($isExpired)
                                    <span class="text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/30 px-2 py-0.5 rounded">
                                        EXPIRED ({{ $med->expiry_date->translatedFormat('d/m/y') }})
                                    </span>
                                @elseif($isExpiringSoon)
                                    <span class="text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30 px-2 py-0.5 rounded animate-pulse">
                                        EXP SOON ({{ $med->expiry_date->translatedFormat('d/m/y') }})
                                    </span>
                                @else
                                    <span class="text-gray-650 dark:text-gray-350">
                                        {{ $med->expiry_date->translatedFormat('d M Y') }}
                                    </span>
                                @endif
                            @else
                                <span class="italic text-gray-300 dark:text-gray-650">Tidak Terdaftar</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-16 bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 text-center border border-gray-100 dark:border-gray-700/50 flex flex-col items-center justify-center">
                        <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4 animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <h5 class="text-base font-black text-gray-800 dark:text-white">Inventaris Obat Masih Kosong</h5>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-bold max-w-sm mt-1 uppercase tracking-wide">Silakan tambahkan data obat baru menggunakan tombol di pojok kanan atas.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $medicines->links() }}
            </div>
        </div>

        <!-- MODAL: TAMBAH OBAT -->
        <x-modal name="add-medicine-modal" focusable>
            <form method="POST" action="{{ route('uks.medicines.store') }}" class="p-6 sm:p-8 space-y-6">
                @csrf
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-750">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">Tambah Obat Inventaris Baru</h3>
                    <button type="button" @click="$dispatch('close')" class="text-gray-400 hover:text-gray-650 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Obat -->
                    <div class="sm:col-span-2 space-y-1">
                        <x-input-label for="name" value="Nama Obat *" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full rounded-xl" placeholder="Contoh: Paracetamol 500mg, Betadine" required />
                    </div>

                    <!-- Kategori -->
                    <div class="space-y-1">
                        <x-input-label for="category" value="Kategori *" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <select id="category" name="category" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-sm text-sm">
                            <option value="Obat Umum">Obat Umum</option>
                            <option value="P3K">P3K</option>
                            <option value="Vitamin">Vitamin</option>
                        </select>
                    </div>

                    <!-- Kadaluwarsa -->
                    <div class="space-y-1">
                        <x-input-label for="expiry_date" value="Tanggal Kedaluwarsa" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <input id="expiry_date" name="expiry_date" type="date" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-sm text-sm" />
                    </div>

                    <!-- Stok -->
                    <div class="space-y-1">
                        <x-input-label for="stock" value="Stok Awal *" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <x-text-input id="stock" name="stock" type="number" class="mt-1 block w-full rounded-xl" min="0" placeholder="0" required />
                    </div>

                    <!-- Min Stok -->
                    <div class="space-y-1">
                        <x-input-label for="min_stock" value="Batas Minimum Stok *" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <x-text-input id="min_stock" name="min_stock" type="number" class="mt-1 block w-full rounded-xl" min="1" placeholder="5" required />
                    </div>

                    <!-- Notes -->
                    <div class="sm:col-span-2 space-y-1">
                        <x-input-label for="notes" value="Catatan Tambahan" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-sm text-sm" placeholder="Aturan pakai obat atau deskripsi..."></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-750">
                    <button type="button" @click="$dispatch('close')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-755 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                        Simpan Obat
                    </button>
                </div>
            </form>
        </x-modal>

        <!-- MODAL: EDIT OBAT -->
        <x-modal name="edit-medicine-modal" focusable>
            <form method="POST" :action="`{{ route('uks.medicines.index') }}/${editMedicineId}`" class="p-6 sm:p-8 space-y-6">
                @csrf
                @method('PUT')
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-750">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white">Edit Detail Obat</h3>
                    <button type="button" @click="$dispatch('close')" class="text-gray-400 hover:text-gray-650 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nama Obat -->
                    <div class="sm:col-span-2 space-y-1">
                        <x-input-label for="edit_name" value="Nama Obat *" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <x-text-input id="edit_name" name="name" x-model="editName" type="text" class="mt-1 block w-full rounded-xl" required />
                    </div>

                    <!-- Kategori -->
                    <div class="space-y-1">
                        <x-input-label for="edit_category" value="Kategori *" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <select id="edit_category" name="category" x-model="editCategory" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-sm text-sm">
                            <option value="Obat Umum">Obat Umum</option>
                            <option value="P3K">P3K</option>
                            <option value="Vitamin">Vitamin</option>
                        </select>
                    </div>

                    <!-- Kadaluwarsa -->
                    <div class="space-y-1">
                        <x-input-label for="edit_expiry_date" value="Tanggal Kedaluwarsa" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <input id="edit_expiry_date" name="expiry_date" x-model="editExpiryDate" type="date" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-sm text-sm" />
                    </div>

                    <!-- Stok -->
                    <div class="space-y-1">
                        <x-input-label for="edit_stock" value="Stok Tersedia *" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <x-text-input id="edit_stock" name="stock" x-model.number="editStock" type="number" class="mt-1 block w-full rounded-xl" min="0" required />
                    </div>

                    <!-- Min Stok -->
                    <div class="space-y-1">
                        <x-input-label for="edit_min_stock" value="Batas Minimum Stok *" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <x-text-input id="edit_min_stock" name="min_stock" x-model.number="editMinStock" type="number" class="mt-1 block w-full rounded-xl" min="1" required />
                    </div>

                    <!-- Notes -->
                    <div class="sm:col-span-2 space-y-1">
                        <x-input-label for="edit_notes" value="Catatan Tambahan" class="text-xs font-bold text-gray-400 uppercase tracking-wide" />
                        <textarea id="edit_notes" name="notes" x-model="editNotes" rows="3" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-sm text-sm"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-gray-750">
                    <button type="button" @click="$dispatch('close')" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-755 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                        Perbarui Obat
                    </button>
                </div>
            </form>
        </x-modal>
    </div>
</x-app-layout>
