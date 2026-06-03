<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Tambah Kelas Baru') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="p-8 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('classrooms.store') }}">
                    @csrf

                    <!-- Nama Kelas -->
                    <div class="mb-5">
                        <label for="name" class="block font-medium text-sm text-gray-700">Nama Lengkap Kelas (Contoh: X MIPA 1)</label>
                        <input id="name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Masukkan Nama Kelas" />
                        @error('name')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                        <!-- Grade Level -->
                        <div>
                            <label for="grade_level" class="block font-medium text-sm text-gray-700">Tingkat Kelas</label>
                            <select id="grade_level" name="grade_level" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                                <option value="" disabled selected>Pilih Tingkat</option>
                                <option value="X">Kelas X (Sepuluh)</option>
                                <option value="XI">Kelas XI (Sebelas)</option>
                                <option value="XII">Kelas XII (Dua Belas)</option>
                            </select>
                            @error('grade_level')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Major -->
                        <div>
                            <label for="major" class="block font-medium text-sm text-gray-700">Jurusan</label>
                            <input id="major" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="text" name="major" value="{{ old('major') }}" required placeholder="Contoh: MIPA, IPS" />
                            @error('major')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                        <!-- Teacher / Wali Kelas -->
                        <div>
                            <label for="teacher_id" class="block font-medium text-sm text-gray-700">Pilih Wali Kelas (Opsional)</label>
                            <select id="teacher_id" name="teacher_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                                <option value="">-- Belum Ditentukan --</option>
                                @foreach($teachers ?? [] as $teacher)
                                    <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                                @endforeach
                            </select>
                            @error('teacher_id')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Academic Year -->
                        <div>
                            <label for="academic_year" class="block font-medium text-sm text-gray-700">Tahun Ajaran</label>
                            <input id="academic_year" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="text" name="academic_year" value="{{ old('academic_year', '2026/2027') }}" required />
                            @error('academic_year')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-100">
                        <a href="{{ route('classrooms.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                            Simpan Kelas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
