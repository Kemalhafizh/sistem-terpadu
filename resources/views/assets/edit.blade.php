<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-800 leading-tight">
            {{ __('Edit Aset: ') }} {{ $asset->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('assets.update', $asset) }}" method="POST" class="p-10 space-y-8">
                    @csrf
                    @method('PUT')

                    <div class="bg-gray-50 rounded-2xl p-4 mb-4">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Kode Inventaris:</span>
                        <span class="text-sm font-black text-gray-700 ml-2">{{ $asset->asset_code }}</span>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Nama Barang / Aset</label>
                            <input type="text" name="name" value="{{ old('name', $asset->name) }}" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Kategori</label>
                            <select name="category_id" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}" {{ $asset->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }} ({{ $cat->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Lokasi Ruangan</label>
                            <select name="room_id" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                @foreach($rooms ?? [] as $room)
                                    <option value="{{ $room->id }}" {{ $asset->room_id == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Tanggal Pembelian</label>
                            <input type="date" name="purchase_date" value="{{ old('purchase_date', $asset->purchase_date) }}" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Harga Beli (Rp)</label>
                            <input type="number" name="purchase_price" value="{{ old('purchase_price', $asset->purchase_price) }}" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Kondisi</label>
                            <select name="condition" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="good" {{ $asset->condition == 'good' ? 'selected' : '' }}>Baik / Normal</option>
                                <option value="damaged" {{ $asset->condition == 'damaged' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="broken" {{ $asset->condition == 'broken' ? 'selected' : '' }}>Rusak Berat</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Status Ketersediaan</label>
                            <select name="status" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="available" {{ $asset->status == 'available' ? 'selected' : '' }}>Tersedia</option>
                                <option value="borrowed" {{ $asset->status == 'borrowed' ? 'selected' : '' }}>Sedang Dipinjam</option>
                                <option value="repairing" {{ $asset->status == 'repairing' ? 'selected' : '' }}>Sedang Perbaikan</option>
                                <option value="lost" {{ $asset->status == 'lost' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>

                        <!-- Masa Manfaat -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Masa Manfaat (Tahun)</label>
                            <input type="number" name="useful_life_years" value="{{ old('useful_life_years', $asset->useful_life_years) }}" min="1" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4" placeholder="Contoh: 5">
                        </div>

                        <!-- Hak Peminjaman -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Hak Peminjaman Mandiri</label>
                            <div class="flex items-center space-x-3 mt-4">
                                <input type="checkbox" id="is_borrowable" name="is_borrowable" value="1" {{ old('is_borrowable', $asset->is_borrowable) ? 'checked' : '' }} class="rounded border-gray-200 text-primary-600 focus:ring-primary-500 h-5 w-5">
                                <label for="is_borrowable" class="text-sm font-bold text-gray-600">Izinkan dipinjam Guru / Staf</label>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Keterangan Tambahan</label>
                            <textarea name="description" rows="4" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">{{ old('description', $asset->description) }}</textarea>
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-end space-x-4 border-t border-gray-50">
                        <a href="{{ route('assets.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-all">Batal & Kembali</a>
                        <button type="submit" class="px-12 py-4 bg-primary-600 text-white rounded-[1.5rem] font-black text-sm shadow-2xl shadow-primary-200 hover:bg-primary-700 hover:-translate-y-1 transition-all">
                            SIMPAN PERUBAHAN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
