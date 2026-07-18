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
                    Edit Kampanye Donasi
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
                <h3 class="font-black text-lg text-gray-800 dark:text-white">Ubah Informasi Kampanye</h3>
                <p class="text-xs text-gray-400 mt-1">Perbarui data, tenggat waktu, target, atau status keaktifan dari program.</p>
            </div>

            <form action="{{ route('admin.finance.charity.update', $campaign->id) }}" method="POST" class="p-8 space-y-6">
                @csrf
                @method('PUT')

                <!-- Title -->
                <div class="space-y-2">
                    <label for="title" class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        Nama / Judul Kampanye <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title" id="title" value="{{ old('title', $campaign->title) }}" required
                           class="block w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-bold text-sm transition-all"
                           placeholder="Masukkan nama kampanye">
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
                            <option value="zakat" {{ old('type', $campaign->type) === 'zakat' ? 'selected' : '' }}>Zakat</option>
                            <option value="infaq" {{ old('type', $campaign->type) === 'infaq' ? 'selected' : '' }}>Infaq</option>
                            <option value="shodaqoh" {{ old('type', $campaign->type) === 'shodaqoh' ? 'selected' : '' }}>Shodaqoh</option>
                            <option value="wakaf" {{ old('type', $campaign->type) === 'wakaf' ? 'selected' : '' }}>Wakaf</option>
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
                        <input type="date" name="end_date" id="end_date" 
                               value="{{ old('end_date', $campaign->end_date ? $campaign->end_date->format('Y-m-d') : '') }}"
                               class="block w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900/50 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent dark:text-white font-bold text-sm transition-all">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">Biarkan kosong jika kampanye berjalan abadi.</p>
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
                        <input type="number" name="target_amount" id="target_amount" 
                               value="{{ old('target_amount', $campaign->target_amount ? (int) $campaign->target_amount : '') }}" min="0" step="1"
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
                              placeholder="Masukkan detail penjelasan program donasi">{{ old('description', $campaign->description) }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Active Toggle -->
                <div class="space-y-2 py-2">
                    <span class="block text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2">
                        Status Publikasi Kampanye
                    </span>
                    <div class="flex items-center space-x-3" x-data="{ active: {{ $campaign->is_active ? 'true' : 'false' }} }">
                        <button type="button" @click="active = !active" 
                                :class="active ? 'bg-teal-600' : 'bg-gray-200 dark:bg-gray-700'" 
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none">
                            <span :class="active ? 'translate-x-5' : 'translate-x-0'" 
                                  class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out"></span>
                        </button>
                        <input type="hidden" name="is_active" :value="active ? 1 : 0">
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300">
                            Aktif (Menerima Donasi)
                        </span>
                    </div>
                    @error('is_active')
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
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
