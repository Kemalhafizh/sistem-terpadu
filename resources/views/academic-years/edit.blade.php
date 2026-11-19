<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Periode Akademik') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="p-8 bg-white">
                <form method="POST" action="{{ route('academic-years.update', $academicYear) }}">
                    @csrf
                    @method('PUT')

                    <!-- Tahun Ajaran -->
                    <div class="mb-5">
                        <label for="year" class="block font-medium text-sm text-gray-700 font-bold mb-1">Tahun Pelajaran (e.g. 2024/2025)</label>
                        <input id="year" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" 
                               type="text" name="year" value="{{ old('year', $academicYear->year) }}" required autofocus />
                        @error('year')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Semester -->
                    <div class="mb-5">
                        <label for="semester" class="block font-medium text-sm text-gray-700 font-bold mb-1">Semester</label>
                        <select id="semester" name="semester" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                            <option value="Ganjil" {{ old('semester', $academicYear->semester) == 'Ganjil' ? 'selected' : '' }}>Ganjil (Odd)</option>
                            <option value="Genap" {{ old('semester', $academicYear->semester) == 'Genap' ? 'selected' : '' }}>Genap (Even)</option>
                        </select>
                        @error('semester')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5 pt-4 border-t border-gray-100">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block font-medium text-sm text-gray-700">Tanggal Mulai (Opsional)</label>
                            <input id="start_date" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="date" name="start_date" value="{{ old('start_date', $academicYear->start_date) }}" />
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block font-medium text-sm text-gray-700">Tanggal Berakhir (Opsional)</label>
                            <input id="end_date" class="block mt-1 w-full rounded-lg shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="date" name="end_date" value="{{ old('end_date', $academicYear->end_date) }}" />
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-8 pt-4 border-t border-gray-100">
                        <a href="{{ route('academic-years.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4 font-medium transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-lg shadow-primary-900/20">
                            Update Periode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
