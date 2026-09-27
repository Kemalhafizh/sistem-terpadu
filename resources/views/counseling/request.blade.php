<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <span class="w-8 h-8 bg-indigo-100 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 rounded-lg flex items-center justify-center mr-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </span>
                    {{ __('Pengajuan Sesi Konseling Baru') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">{{ mb_strtoupper(school_short_name()) }} • PENGAJUAN PERTEMUAN GURU BK</p>
            </div>
            
            <div>
                <a href="{{ route('counseling.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-gray-50 transition-all shadow-sm">
                    ← Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <div class="mb-6">
                    <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                        <span class="w-2.5 h-6 bg-indigo-600 rounded-full mr-3"></span>
                        Formulir Janji Temu Konseling
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">Isi formulir berikut dengan informasi yang lengkap agar Guru BK dapat mempersiapkan sesi dengan matang.</p>
                </div>

                <form method="POST" action="{{ route('counseling.request') }}" class="space-y-6">
                    @csrf

                    <!-- Pilihan Siswa -->
                    <div>
                        <label for="student_id" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Siswa yang Mengikuti Sesi</label>
                        @if(auth()->user()->role === 'student')
                            <!-- Jika login sebagai siswa -->
                            <input type="hidden" name="student_id" value="{{ $students->first()->id }}">
                            <div class="mt-2 p-4 bg-gray-50 dark:bg-gray-950/30 border border-gray-100 dark:border-gray-900 rounded-xl">
                                <span class="text-sm font-extrabold text-gray-900 dark:text-white">{{ $students->first()->user->name }}</span>
                                <span class="text-xs text-gray-400 block mt-0.5">{{ $students->first()->classroom->name ?? '-' }} • NISN: {{ $students->first()->nisn }}</span>
                            </div>
                        @elseif(auth()->user()->role === 'parent' && $students->count() === 1)
                            <!-- Jika login sebagai orang tua dan hanya memiliki 1 anak -->
                            <input type="hidden" name="student_id" value="{{ $students->first()->id }}">
                            <div class="mt-2 p-4 bg-gray-50 dark:bg-gray-950/30 border border-gray-100 dark:border-gray-900 rounded-xl">
                                <span class="text-sm font-extrabold text-gray-900 dark:text-white">{{ $students->first()->user->name }}</span>
                                <span class="text-xs text-gray-400 block mt-0.5">Anak Anda • Kelas {{ $students->first()->classroom->name ?? '-' }}</span>
                            </div>
                        @else
                            <!-- Jika Guru BK atau Orang tua dengan multi-anak -->
                            <select name="student_id" id="student_id" required class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">-- Pilih Siswa --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('student_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->user->name }} ({{ $student->classroom->name ?? 'Tanpa Kelas' }})
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('student_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Jenis & Kategori -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="type" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Jenis Sesi Konseling</label>
                            <select name="type" id="type" required class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="individual" {{ old('type') == 'individual' ? 'selected' : '' }}>Individual (1-on-1)</option>
                                <option value="group" {{ old('type') == 'group' ? 'selected' : '' }}>Kelompok (Group Counseling)</option>
                                <option value="home_visit" {{ old('type') == 'home_visit' ? 'selected' : '' }}>Kunjungan Rumah (Home Visit)</option>
                                <option value="conference" {{ old('type') == 'conference' ? 'selected' : '' }}>Konferensi Kasus (Ortu & Guru)</option>
                            </select>
                            @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="category" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Kategori Permasalahan</label>
                            <select name="category" id="category" required class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="pribadi" {{ old('category', 'pribadi') == 'pribadi' ? 'selected' : '' }}>Pribadi / Psikologis</option>
                                <option value="akademik" {{ old('category') == 'akademik' ? 'selected' : '' }}>Akademik / Belajar</option>
                                <option value="sosial" {{ old('category') == 'sosial' ? 'selected' : '' }}>Sosial / Interaksi Teman</option>
                                <option value="karir" {{ old('category') == 'karir' ? 'selected' : '' }}>Karir / Masa Depan / Kuliah</option>
                            </select>
                            @error('category') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <!-- Tanggal & Waktu Permintaan (Opsional jika siswa, Wajib jika BK) -->
                    <div>
                        <label for="scheduled_at" class="block text-xs font-black uppercase text-gray-400 tracking-wider">
                            Rencana Tanggal & Waktu Pertemuan
                            @if(in_array(auth()->user()->role, ['bk', 'admin', 'superadmin']))
                                <span class="text-red-500">*</span>
                            @else
                                <span class="text-gray-400 font-normal">(Opsional / Usulan Lusa jika dikosongi)</span>
                            @endif
                        </label>
                        <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ old('scheduled_at') }}" {{ in_array(auth()->user()->role, ['bk', 'admin', 'superadmin']) ? 'required' : '' }} class="mt-2 block w-full rounded-xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('scheduled_at') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Alasan Detail -->
                    <div>
                        <label for="reason" class="block text-xs font-black uppercase text-gray-400 tracking-wider">Alasan Pengajuan / Latar Belakang Masalah</label>
                        <p class="text-[10px] text-gray-400 mt-1">Uraikan secara ringkas apa yang ingin dikonsultasikan agar konselor memahami konteks masalah.</p>
                        <textarea name="reason" id="reason" required rows="5" placeholder="Tuliskan latar belakang masalah atau alasan janji temu..." class="mt-2 block w-full rounded-2xl border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('reason') }}</textarea>
                        @error('reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="pt-4 flex justify-end gap-2 border-t border-gray-100 dark:border-gray-800">
                        <a href="{{ route('counseling.index') }}" class="px-5 py-2.5 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-bold uppercase text-gray-600 dark:text-gray-300 hover:bg-gray-50 transition-colors">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-indigo-600/10">
                            Kirim Pengajuan
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
