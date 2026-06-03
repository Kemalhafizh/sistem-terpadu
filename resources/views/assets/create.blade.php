<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-gray-800 leading-tight">
            {{ __('Registrasi Aset Baru') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <form action="{{ route('assets.store') }}" method="POST" class="p-10 space-y-8">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Nama Barang -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Nama Barang / Aset</label>
                            <input type="text" name="name" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4" placeholder="Contoh: Proyektor Epson EB-X400">
                        </div>

                        <!-- Kategori -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Kategori</label>
                            <select name="category_id" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="">Pilih Kategori</option>
                                @foreach($categories ?? [] as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }} ({{ $cat->code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Ruangan -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Lokasi Ruangan</label>
                            <select name="room_id" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="">Pilih Lokasi</option>
                                @foreach($rooms ?? [] as $room)
                                    <option value="{{ $room->id }}">{{ $room->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Tanggal Beli -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Tanggal Pembelian</label>
                            <input type="date" name="purchase_date" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                        </div>

                        <!-- Harga Beli -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Harga Beli (Rp)</label>
                            <input type="number" name="purchase_price" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4" placeholder="0">
                        </div>

                        <!-- Kondisi -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Kondisi Awal</label>
                            <select name="condition" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="good">Baik / Normal</option>
                                <option value="damaged">Rusak Ringan</option>
                                <option value="broken">Rusak Berat</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Status Ketersediaan</label>
                            <select name="status" required class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4">
                                <option value="available">Tersedia</option>
                                <option value="borrowed">Sedang Dipinjam</option>
                                <option value="repairing">Sedang Perbaikan</option>
                                <option value="lost">Hilang</option>
                            </select>
                        </div>

                        <!-- Masa Manfaat -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Masa Manfaat (Tahun)</label>
                            <input type="number" name="useful_life_years" min="1" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4" placeholder="Contoh: 5">
                        </div>

                        <!-- Hak Peminjaman -->
                        <div>
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Hak Peminjaman Mandiri</label>
                            <div class="flex items-center space-x-3 mt-4">
                                <input type="checkbox" id="is_borrowable" name="is_borrowable" value="1" class="rounded border-gray-200 text-primary-600 focus:ring-primary-500 h-5 w-5">
                                <label for="is_borrowable" class="text-sm font-bold text-gray-600">Izinkan dipinjam Guru / Staf</label>
                            </div>
                        </div>

                        <!-- Deskripsi -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-black text-gray-700 uppercase tracking-widest mb-3">Keterangan Tambahan</label>
                            <textarea name="description" rows="4" class="w-full rounded-2xl border-gray-100 shadow-sm focus:border-primary-500 focus:ring-primary-500 transition-all p-4" placeholder="Contoh: Serial Number, Vendor, atau catatan lainnya..."></textarea>
                        </div>
                    </div>

                    <div class="pt-10 flex items-center justify-end space-x-4 border-t border-gray-50">
                        <a href="{{ route('assets.index') }}" class="text-sm font-bold text-gray-400 hover:text-gray-600 transition-all">Batal & Kembali</a>
                        <button type="submit" class="px-12 py-4 bg-gray-900 text-white rounded-[1.5rem] font-black text-sm shadow-2xl shadow-gray-200 hover:bg-black hover:-translate-y-1 transition-all">
                            DAFTARKAN ASET SEKARANG
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
