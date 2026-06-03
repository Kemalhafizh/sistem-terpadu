<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center space-x-3">
            <a href="{{ route('career-portal.manage') }}" class="w-9 h-9 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl flex items-center justify-center text-gray-500 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight tracking-tight">
                {{ __('Tambah Info Karir & Beasiswa') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
            <form action="{{ route('career-portal.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ opportunityType: '{{ old('type', 'job') }}' }">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Title -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Judul Lowongan / Nama Beasiswa <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                               placeholder="Contoh: Lowongan Java Developer / Beasiswa Prestasi ITB 2026">
                        @error('title')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Company Name -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Instansi / Perusahaan <span class="text-red-500">*</span></label>
                        <input type="text" name="company_name" value="{{ old('company_name') }}"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                               placeholder="Contoh: PT GoTo Gojek Tokopedia / Yayasan Sultan Agung">
                        @error('company_name')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori / Tipe <span class="text-red-500">*</span></label>
                        <select name="type" x-model="opportunityType" class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                            <option value="job">Lowongan Pekerjaan</option>
                            <option value="scholarship">Informasi Beasiswa</option>
                        </select>
                        @error('type')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contact Email -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email Kontak Pendaftaran <span class="text-red-500">*</span></label>
                        <input type="email" name="contact_email" value="{{ old('contact_email') }}"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                               placeholder="Contoh: recruitment@company.com">
                        @error('contact_email')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Closing Date -->
                    <div class="space-y-2">
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Batas Akhir Pendaftaran (Closing Date) <span class="text-red-500">*</span></label>
                        <input type="date" name="closing_date" value="{{ old('closing_date') }}"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                        @error('closing_date')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- PDF File Upload -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Upload Brosur Pendukung (PDF)</label>
                        <input type="file" name="file" accept="application/pdf"
                               class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500 font-semibold mt-1">Ukuran berkas maksimal 10 MB. Format wajib PDF.</p>
                        @error('file')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Deskripsi & Penjelasan <span class="text-red-500">*</span></label>
                        <textarea name="description" rows="5"
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                  placeholder="Berikan ringkasan informasi, cakupan beasiswa, detail pekerjaan, lokasi kerja, dsb..."></textarea>
                        @error('description')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Requirements -->
                    <div class="space-y-2 md:col-span-2">
                        <label class="block text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kualifikasi & Persyaratan <span class="text-red-500">*</span></label>
                        <textarea name="requirements" rows="5"
                                  class="w-full px-4 py-3 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl text-sm focus:outline-none focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                                  placeholder="Berikan daftar kualifikasi pelamar, dokumen persyaratan yang dibutuhkan, dsb..."></textarea>
                        @error('requirements')
                            <p class="text-xs text-red-500 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Button -->
                <div class="flex justify-end pt-4 border-t border-gray-50 dark:border-gray-700/50">
                    <button type="submit" class="px-8 py-3.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black shadow-lg shadow-primary-900/10 transition-all active:scale-95">
                        TAMBAH KESEMPATAN
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
