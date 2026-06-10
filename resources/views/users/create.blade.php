<x-app-layout>
    <x-slot name="header">
        <x-page-header 
            title="Tambah Pengguna Baru" 
            subtitle="Buat akun pengguna baru dengan hak akses dan peran tertentu di SISTA."
            :breadcrumbs="[
                ['label' => 'Kelola Pengguna', 'url' => route('users.index')],
                ['label' => 'Tambah']
            ]"
        />
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-xl border border-gray-200 dark:border-slate-700/80">
            <div class="p-8">
                <form method="POST" action="{{ route('users.store') }}" x-data="{ selectedRole: '{{ old('role', 'student') }}' }">
                    @csrf

                    <!-- Name -->
                    <x-form-group name="name" label="Nama Lengkap" required>
                        <input id="name" class="block w-full rounded-lg shadow-xs border-gray-300 dark:border-slate-650 focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-slate-900 text-sm py-2.5 px-3.5" type="text" name="name" value="{{ old('name') }}" required autofocus />
                    </x-form-group>

                    <!-- Email Address -->
                    <x-form-group name="email" label="Email Sekolah" required>
                        <input id="email" class="block w-full rounded-lg shadow-xs border-gray-300 dark:border-slate-650 focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-slate-900 text-sm py-2.5 px-3.5" type="email" name="email" value="{{ old('email') }}" required />
                    </x-form-group>

                    <!-- Password -->
                    <x-form-group name="password" label="Password Sementara" required helperText="Password minimal 8 karakter. Pengguna akan diminta mengubah password pada saat login pertama kali.">
                        <input id="password" class="block w-full rounded-lg shadow-xs border-gray-300 dark:border-slate-650 focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-slate-900 text-sm py-2.5 px-3.5" type="password" name="password" required autocomplete="new-password" />
                    </x-form-group>

                    <!-- Role -->
                    <x-form-group name="role" label="Pilih Peran Sistem" required>
                        <select id="role" name="role" x-model="selectedRole" class="block w-full rounded-lg shadow-xs border-gray-300 dark:border-slate-650 focus:border-primary-500 focus:ring-primary-500 bg-white dark:bg-slate-900 text-sm py-2.5 px-3.5">
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
                    </x-form-group>

                    <!-- Panel Penugasan Mapel Khusus Guru -->
                    <div x-show="['guru', 'bk'].includes(selectedRole)" x-transition.opacity class="mb-6 bg-gray-50/50 dark:bg-slate-900/40 p-6 rounded-2xl border border-gray-200 dark:border-slate-700/60" style="display: none;">
                        <div class="flex items-center mb-4">
                            <svg class="w-5 h-5 text-primary-650 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5s3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <h3 class="text-sm font-bold text-gray-800 dark:text-slate-200">Penugasan Mata Pelajaran</h3>
                        </div>
                        <p class="text-xs text-gray-400 dark:text-slate-400 mb-6">Pilih mata pelajaran yang diampu oleh guru ini.</p>
                        
                        @if(isset($subjects) && count($subjects) > 0)
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                                @foreach($subjects ?? [] as $category => $categorySubjects)
                                    <div>
                                        <h4 class="text-[10px] font-black text-gray-400 dark:text-slate-400 uppercase tracking-widest border-b border-gray-200 dark:border-slate-700 pb-2 mb-3">{{ $category }}</h4>
                                        <div class="space-y-2.5">
                                            @foreach($categorySubjects ?? [] as $subject)
                                                <label class="flex items-start group cursor-pointer">
                                                    <div class="flex items-center h-5">
                                                        <input type="checkbox" name="subjects[]" value="{{ $subject->id }}" 
                                                            class="w-4 h-4 rounded border-gray-300 dark:border-slate-650 text-primary-600 focus:ring-primary-500 transition duration-150 ease-in-out cursor-pointer"
                                                            {{ (is_array(old('subjects', [])) && in_array($subject->id, old('subjects', []))) ? 'checked' : '' }}>
                                                    </div>
                                                    <div class="ml-3 text-sm">
                                                        <span class="font-medium text-gray-700 dark:text-slate-300 group-hover:text-primary-650 transition-colors">{{ $subject->name }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <x-empty-state 
                                icon="box" 
                                message="Belum ada data mata pelajaran" 
                                submessage="Hubungi admin akademik untuk menambahkan mata pelajaran."
                            />
                        @endif
                        @error('subjects')
                            <p class="text-xs text-red-650 dark:text-red-400 mt-2 font-bold">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end mt-4 pt-4 border-t border-gray-150 dark:border-slate-700/60">
                        <a href="{{ route('users.index') }}" class="text-sm font-semibold text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-200 mr-4">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-primary-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-wider hover:bg-primary-700 active:bg-primary-900 transition ease-in-out duration-150 shadow-sm">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
