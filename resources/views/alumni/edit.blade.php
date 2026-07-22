<x-app-layout>
    <x-slot name="title">Edit Profil Alumni - {{ $alumni->student->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Edit Profil Alumni
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Ubah data angkatan, aktivitas, kontak, atau media sosial alumni {{ $alumni->student->name }}.
                </p>
            </div>
            <a href="{{ route('alumni.index') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <form action="{{ route('alumni.update', $alumni->uuid) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Informasi Siswa (Read-Only) & Angkatan -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Siswa Alumni</label>
                        <input type="text" value="{{ $alumni->student->name }}" disabled class="w-full bg-gray-100 dark:bg-gray-900 border border-gray-250 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-500 dark:text-gray-400 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tahun Kelulusan</label>
                        <input type="number" name="graduation_year" value="{{ old('graduation_year', $alumni->graduation_year) }}" required min="2000" max="{{ date('Y') + 1 }}" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 @error('graduation_year') border-red-500 @enderror">
                        @error('graduation_year')
                            <p class="text-red-500 text-[10px] mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Aktivitas Utama -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Aktivitas Utama</label>
                        <select name="current_activity" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="study" {{ old('current_activity', $alumni->current_activity) == 'study' ? 'selected' : '' }}>Melanjutkan Kuliah</option>
                            <option value="work" {{ old('current_activity', $alumni->current_activity) == 'work' ? 'selected' : '' }}>Bekerja</option>
                            <option value="entrepreneur" {{ old('current_activity', $alumni->current_activity) == 'entrepreneur' ? 'selected' : '' }}>Berwirausaha</option>
                            <option value="unemployed" {{ old('current_activity', $alumni->current_activity) == 'unemployed' ? 'selected' : '' }}>Mencari Kerja</option>
                            <option value="other" {{ old('current_activity', $alumni->current_activity) == 'other' ? 'selected' : '' }}>Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Instansi / Universitas</label>
                        <input type="text" name="institution_name" value="{{ old('institution_name', $alumni->institution_name) }}" placeholder="Undip / PT Astra..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Jabatan Pekerjaan / Jurusan</label>
                        <input type="text" name="current_position" value="{{ old('current_position', $alumni->current_position) }}" placeholder="S1 Teknik Elektro / Junior Developer..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Informasi Kontak & Domisili -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Surel (Email)</label>
                        <input type="email" name="email" value="{{ old('email', $alumni->email) }}" placeholder="alumni@email.com..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">No. Telepon / WA</label>
                        <input type="text" name="phone_number" value="{{ old('phone_number', $alumni->phone_number) }}" placeholder="0812..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kontak Lain (Opsional)</label>
                        <input type="text" name="contact_info" value="{{ old('contact_info', $alumni->contact_info) }}" placeholder="Telegram / Wali..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kabupaten/Kota</label>
                        <input type="text" name="city" value="{{ old('city', $alumni->city) }}" placeholder="Semarang..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Provinsi</label>
                        <input type="text" name="province" value="{{ old('province', $alumni->province) }}" placeholder="Jawa Tengah..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Negara</label>
                        <input type="text" name="country" value="{{ old('country', $alumni->country ?? 'Indonesia') }}" placeholder="Indonesia..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Sosial Media -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">LinkedIn URL</label>
                        <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $alumni->linkedin_url) }}" placeholder="https://linkedin.com/in/username..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Instagram URL</label>
                        <input type="url" name="instagram_url" value="{{ old('instagram_url', $alumni->instagram_url) }}" placeholder="https://instagram.com/username..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50 flex justify-end gap-2">
                    <a href="{{ route('alumni.index') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-6 text-xs font-bold transition-all">
                        Batal
                    </a>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2 px-6 text-xs font-bold transition-all shadow-md">
                        Perbarui Profil
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
