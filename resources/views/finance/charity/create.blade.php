<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.finance.charity.index') }}" 
               class="p-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-600 dark:text-gray-300 rounded-xl transition-all"
               title="Kembali ke Daftar Kampanye">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <div>
                <span class="text-[10px] font-black uppercase tracking-widest text-teal-600 dark:text-teal-400">Penggalangan Dana</span>
                <h2 class="font-black text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    Buat Kampanye Baru
                </h2>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto py-4">
        @if($errors->any())
            <div class="bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800/50 p-4 rounded-2xl shadow-sm text-red-800 dark:text-red-300 font-bold text-sm mb-6">
                <p class="font-black mb-1">Periksa kembali isian Anda:</p>
                <ul class="list-disc pl-5 space-y-1 font-semibold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-8 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/20">
                <h3 class="font-black text-lg text-gray-800 dark:text-white">Formulir Kampanye</h3>
                <p class="text-xs text-gray-400 mt-1">Masukkan informasi mendetail untuk program penggalangan dana ZIS & Wakaf.</p>
            </div>

            <form action="{{ route('admin.finance.charity.store') }}" method="POST" class="p-8 space-y-6">
                @csrf

                <!-- Title -->
                <div class="space-y-2">
                    <label for="title" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        Nama / Judul Kampanye <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="title" id="title" value="{{ old('title') }}" required
                               class="block w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-bold text-sm transition-all"
                               placeholder="Contoh: Renovasi Masjid Kampus, Beasiswa Yatim Duafa">
                    </div>
                    @error('title')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type & End Date Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Type -->
                    <div class="space-y-2">
                        <label for="type" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                            Tipe Kampanye <span class="text-red-500">*</span>
                        </label>
                        <select name="type" id="type" required
                                class="block w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-bold text-sm transition-all">
                            <option value="">-- Pilih Tipe --</option>
                            <option value="zakat" {{ old('type') === 'zakat' ? 'selected' : '' }}>Zakat</option>
                            <option value="infaq" {{ old('type') === 'infaq' ? 'selected' : '' }}>Infaq</option>
                            <option value="shodaqoh" {{ old('type') === 'shodaqoh' ? 'selected' : '' }}>Shodaqoh</option>
                            <option value="wakaf" {{ old('type') === 'wakaf' ? 'selected' : '' }}>Wakaf</option>
                        </select>
                        @error('type')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- End Date -->
                    <div class="space-y-2">
                        <label for="end_date" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                            Tanggal Selesai (Opsional)
                        </label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                               class="block w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-bold text-sm transition-all">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">Biarkan kosong jika kampanye berjalan permanen (abadi).</p>
                        @error('end_date')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Target Amount -->
                <div class="space-y-2">
                    <label for="target_amount" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        Target Dana (Rupiah - Opsional)
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-gray-400 font-black text-sm pointer-events-none">Rp</span>
                        <input type="number" name="target_amount" id="target_amount" value="{{ old('target_amount') }}" min="0" step="1"
                               class="block w-full pl-10 pr-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-bold text-sm transition-all"
                               placeholder="Contoh: 50000000">
                    </div>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500">Biarkan kosong atau isi 0 jika tidak ada target nominal khusus.</p>
                    @error('target_amount')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="space-y-2">
                    <label for="description" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        Deskripsi Kampanye <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="5" required
                              class="block w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-semibold text-sm transition-all"
                              placeholder="Tuliskan latar belakang program, tujuan penggalangan dana, dan rencana penyaluran..."></textarea>
                    @error('description')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Buttons -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700 flex justify-end items-center gap-4">
                    <a href="{{ route('admin.finance.charity.index') }}" 
                       class="px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl font-bold text-sm transition-all">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-8 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-xl font-bold text-sm shadow-lg shadow-teal-600/20 transition-all duration-300 transform hover:-translate-y-0.5">
                        Simpan Kampanye
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
