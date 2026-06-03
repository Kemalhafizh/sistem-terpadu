<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Mata Pelajaran') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="p-8">
                <form method="POST" action="{{ route('subjects.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nama Mapel -->
                        <div class="md:col-span-1">
                            <label for="name" class="block font-medium text-sm text-gray-700 font-bold mb-1">Nama Mata Pelajaran</label>
                            <input id="name" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" 
                                   type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Matematika" required autofocus />
                            @error('name')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kode Mapel -->
                        <div class="md:col-span-1">
                            <label for="code" class="block font-medium text-sm text-gray-700 font-bold mb-1">Kode Pelajaran</label>
                            <input id="code" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" 
                                   type="text" name="code" value="{{ old('code') }}" placeholder="Contoh: MTK" required />
                            @error('code')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Kategori -->
                    <div class="mt-6">
                        <label for="category" class="block font-medium text-sm text-gray-700 font-bold mb-1">Kategori Pelajaran</label>
                        <select id="category" name="category" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                            <option value="">-- Pilih Kategori --</option>
                            <option value="Nasional" {{ old('category') == 'Nasional' ? 'selected' : '' }}>Muatan Nasional</option>
                            <option value="Lokal" {{ old('category') == 'Lokal' ? 'selected' : '' }}>Muatan Lokal</option>
                            <option value="Ciri Khas" {{ old('category') == 'Ciri Khas' ? 'selected' : '' }}>Ciri Khas Sultan Agung</option>
                            <option value="Kejuruan" {{ old('category') == 'Kejuruan' ? 'selected' : '' }}>Kejuruan / Produktif</option>
                        </select>
                        @error('category')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="mt-6">
                        <label for="description" class="block font-medium text-sm text-gray-700 font-bold mb-1">Keterangan (Opsional)</label>
                        <textarea id="description" name="description" rows="3" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" placeholder="Informasi tambahan mengenai mata pelajaran...">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-8 pt-4 border-t border-gray-100">
                        <a href="{{ route('subjects.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4 font-medium transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg shadow-primary-900/20">
                            Simpan Mapel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
