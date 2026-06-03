<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Lengkapi Biodata Siswa') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="p-8 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('students.store') }}">
                    @csrf

                    <!-- Akun Login (User Link) -->
                    <div class="mb-5">
                        <label for="user_id" class="block font-medium text-sm text-gray-700">Pilih Akun Login Siswa</label>
                        <select id="user_id" name="user_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                            <option value="" disabled selected>-- Pilih Akun Siswa --</option>
                            @foreach($users ?? [] as $user)
                                <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hanya menampilkan akun dengan Role 'student' yang belum diverifikasi biodatanya.</p>
                        @error('user_id')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                        <!-- NIS -->
                        <div>
                            <label for="nis" class="block font-medium text-sm text-gray-700">NIS (Nomor Induk Siswa)</label>
                            <input id="nis" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="text" name="nis" value="{{ old('nis') }}" required />
                            @error('nis')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NISN -->
                        <div>
                            <label for="nisn" class="block font-medium text-sm text-gray-700">NISN (Nomor Induk Siswa Nasional)</label>
                            <input id="nisn" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="text" name="nisn" value="{{ old('nisn') }}" required />
                            @error('nisn')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                        <!-- Gender -->
                        <div>
                            <label for="gender" class="block font-medium text-sm text-gray-700">Jenis Kelamin</label>
                            <select id="gender" name="gender" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                                <option value="" disabled selected>-- Pilih --</option>
                                <option value="L">Laki-laki</option>
                                <option value="P">Perempuan</option>
                            </select>
                            @error('gender')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Birth Date -->
                        <div>
                            <label for="birth_date" class="block font-medium text-sm text-gray-700">Tanggal Lahir</label>
                            <input id="birth_date" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="date" name="birth_date" value="{{ old('birth_date') }}" required />
                            @error('birth_date')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Classroom -->
                    <div class="mb-5 border-t border-gray-100 pt-5">
                        <label for="classroom_id" class="block font-medium text-sm text-gray-700">Penempatan Kelas (Rombel)</label>
                        <select id="classroom_id" name="classroom_id" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            <option value="">-- Belum Masuk Kelas (Bisa Nanti) --</option>
                            @foreach($classrooms ?? [] as $classroom)
                                <option value="{{ $classroom->id }}">Kelas {{ $classroom->grade_level }} - {{ $classroom->name }}</option>
                            @endforeach
                        </select>
                        @error('classroom_id')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Address -->
                    <div class="mb-5">
                        <label for="address" class="block font-medium text-sm text-gray-700">Alamat Lengkap</label>
                        <textarea id="address" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" name="address" rows="3">{{ old('address') }}</textarea>
                        @error('address')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-6 pt-4 border-t border-gray-100">
                        <a href="{{ route('students.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                            Simpan Biodata
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
