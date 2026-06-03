<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Pengguna') }}: {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white overflow-hidden shadow-sm sm:rounded-xl border border-gray-100">
            <div class="p-8 bg-white border-b border-gray-200">
                <form method="POST" action="{{ route('users.update', $user->id) }}" x-data="{ selectedRole: '{{ old('role', $user->role) }}' }">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div class="mb-5">
                        <label for="name" class="block font-medium text-sm text-gray-700">Nama Lengkap</label>
                        <input id="name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="text" name="name" value="{{ old('name', $user->name) }}" required autofocus />
                        @error('name')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div class="mb-5">
                        <label for="email" class="block font-medium text-sm text-gray-700">Email Sekolah</label>
                        <input id="email" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="email" name="email" value="{{ old('email', $user->email) }}" required />
                        @error('email')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="mb-5">
                        <label for="password" class="block font-medium text-sm text-gray-700">Password Baru <span class="text-xs text-gray-400 font-normal">(Kosongkan jika tidak ingin merubah)</span></label>
                        <input id="password" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" type="password" name="password" autocomplete="new-password" />
                        @error('password')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div class="mb-6">
                        <label for="role" class="block font-medium text-sm text-gray-700">Pilih Peran Sistem</label>
                        <select id="role" name="role" x-model="selectedRole" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            <optgroup label="Siswa & Wali">
                                <option value="student">Siswa</option>
                                <option value="parent">Wali Murid / Orang Tua</option>
                            </optgroup>
                            <optgroup label="Guru & Staf">
                                <option value="guru">Guru / Wali Kelas</option>
                                <option value="bk">Guru BK</option>
                                <option value="staf_tu">Staf Tata Usaha</option>
                            </optgroup>
                            <optgroup label="Pimpinan (Waka)">
                                <option value="waka_kurikulum">Waka Kurikulum</option>
                                <option value="waka_kesiswaan">Waka Kesiswaan</option>
                                <option value="waka_sarpras">Waka Sarpras</option>
                                <option value="waka_humas">Waka Humas</option>
                            </optgroup>
                            <optgroup label="Administrasi">
                                <option value="kepala_sekolah">Kepala Sekolah</option>
                                <option value="admin">Superadmin IT</option>
                            </optgroup>
                        </select>
                        @error('role')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Panel Penugasan Mapel Khusus Guru -->
                    <div x-show="['guru', 'bk'].includes(selectedRole)" x-transition.opacity class="mb-6 bg-gray-50 p-6 rounded-xl border border-gray-200" style="display: none;">
                        <div class="flex items-center mb-4">
                            <svg class="w-5 h-5 text-primary-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <h3 class="text-sm font-bold text-gray-800">Penugasan Mata Pelajaran</h3>
                        </div>
                        <p class="text-xs text-gray-500 mb-6">Pilih mata pelajaran yang diampu oleh guru ini. Akses modul khusus (seperti Tahfidz atau BK) akan terbuka secara otomatis berdasarkan pilihan ini.</p>
                        
                        @if(isset($subjects) && count($subjects) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                @foreach($subjects ?? [] as $category => $categorySubjects)
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest border-b border-gray-200 pb-2 mb-3">{{ $category }}</h4>
                                        <div class="space-y-2.5">
                                            @foreach($categorySubjects ?? [] as $subject)
                                                <label class="flex items-start group cursor-pointer">
                                                    <div class="flex items-center h-5">
                                                        <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" 
                                                            class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500 transition duration-150 ease-in-out cursor-pointer"
                                                            {{ (is_array(old('subjects', $userSubjects ?? [])) && in_array($subject->id, old('subjects', $userSubjects ?? []))) ? 'checked' : '' }}>
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <span class="font-medium text-gray-700 group-hover:text-primary-600 transition-colors">{{ $subject->name }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 bg-yellow-50 text-yellow-700 text-sm rounded-lg border border-yellow-100">
                                Belum ada data mata pelajaran di database. Silakan hubungi Superadmin.
                            </div>
                        @endif
                        @error('subjects')
                            <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-100">
                        <a href="{{ route('users.index') }}" class="text-sm text-gray-600 hover:text-gray-900 mr-4">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-sm">
                            Update Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
