<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-black text-3xl text-gray-900 leading-tight">
                    {{ __('Formulir Peminjaman Aset') }}
                </h2>
                <p class="text-xs text-gray-500 font-bold mt-1">Ajukan permohonan peminjaman inventaris sekolah</p>
            </div>
            <a href="{{ route('assets.loans.index') }}" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs transition-all flex items-center space-x-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                <span>Kembali</span>
            </a>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="bg-white/95 backdrop-blur-md rounded-3xl border border-gray-100 shadow-xl overflow-hidden">
                <!-- Header Accent line -->
                <div class="h-2 bg-gradient-to-r from-primary-500 to-emerald-500"></div>

                <div class="p-8">
                    @if($errors->any())
                        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl text-xs font-bold space-y-1">
                            @foreach($errors->all() as $error)
                                <div> {{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form action="{{ route('assets.loans.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <!-- Asset Selection -->
                        <div>
                            <label for="asset_id" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Aset yang Tersedia</label>
                            <select name="asset_id" id="asset_id" required class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500 p-3 bg-gray-50/50">
                                <option value="">-- Pilih Aset --</option>
                                @foreach($assets as $asset)
                                    <option value="{{ $asset->id }}" {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->name }} ({{ $asset->asset_code }}) - Kondisi: {{ ucfirst($asset->condition) }} - Lokasi: {{ $asset->room->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-[10px] text-gray-400 font-bold mt-1.5 uppercase">Hanya menampilkan aset yang diizinkan untuk dipinjam (is_borrowable = true) & berstatus available.</p>
                        </div>

                        <!-- Expected Return Date -->
                        <div>
                            <label for="expected_return_at" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Rencana Tanggal Pengembalian</label>
                            <input type="datetime-local" name="expected_return_at" id="expected_return_at" required min="{{ now()->addDay()->format('Y-m-d\T00:00') }}" value="{{ old('expected_return_at') }}" class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500 p-3 bg-gray-50/50">
                            <p class="text-[10px] text-gray-400 font-bold mt-1.5 uppercase">Isi dengan rencana pengembalian. Tanggal pengembalian harus setelah hari ini.</p>
                        </div>

                        <!-- Purpose of Loan -->
                        <div>
                            <label for="purpose" class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Tujuan Penggunaan Aset</label>
                            <textarea name="purpose" id="purpose" required rows="4" placeholder="Jelaskan secara detail untuk apa aset ini akan dipinjam (misal: Kegiatan pembelajaran luar ruangan kelas XI-A)..." class="w-full rounded-xl border-gray-200 text-sm focus:border-primary-500 focus:ring-primary-500 p-3 bg-gray-50/50">{{ old('purpose') }}</textarea>
                        </div>

                        <!-- Info Card -->
                        <div class="p-4 bg-emerald-50/80 border border-emerald-100 rounded-2xl text-emerald-800 text-xs flex items-start space-x-2.5">
                            <span class="p-1 bg-emerald-100 rounded-lg text-emerald-600 font-bold">ℹ</span>
                            <div class="space-y-1">
                                <p class="font-black uppercase tracking-wider text-[10px]">Ketentuan Peminjaman Aset:</p>
                                <ul class="list-disc pl-4 space-y-1 text-emerald-700/90 font-medium">
                                    <li>Permohonan peminjaman harus disetujui oleh Waka Sarpras sebelum aset dipindahkan.</li>
                                    <li>Peminjam bertanggung jawab penuh atas keamanan dan kondisi fisik aset selama masa pinjam.</li>
                                    <li>Kerusakan atau kehilangan aset akibat kelalaian dapat dikenakan sanksi sesuai kebijakan sekolah.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('assets.loans.index') }}" class="px-6 py-3 bg-gray-50 hover:bg-gray-100 text-gray-600 rounded-xl text-xs font-black transition-all">
                                Batalkan
                            </a>
                            <button type="submit" class="px-8 py-3 bg-gradient-to-r from-primary-600 to-emerald-600 hover:from-primary-700 hover:to-emerald-700 text-white rounded-xl text-xs font-black shadow-lg shadow-primary-200 hover:shadow-xl transition-all">
                                Kirim Permohonan
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
