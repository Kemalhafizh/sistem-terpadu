<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center">
            <a href="{{ route('hrd.employees.index') }}" class="mr-4 text-gray-400 hover:text-primary-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="font-black text-2xl text-gray-900 tracking-tight">Tambah Pegawai Baru</h2>
                <p class="text-sm text-gray-500 font-medium">Daftarkan akun staf/guru ke dalam database kepegawaian (HRD).</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 p-8 sm:p-12">
                
                @if($errors->any())
                    <div class="mb-8 bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-xl shadow-sm">
                        <ul class="list-disc pl-5 text-sm font-medium">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('hrd.employees.store') }}" method="POST" class="space-y-8">
                    @csrf

                    <div>
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 pb-2">1. Data Utama Pegawai</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Pilih Akun User <span class="text-red-500">*</span></label>
                                <select name="user_id" required class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-medium">
                                    <option value="">-- Pilih Akun User --</option>
                                    @foreach($availableUsers ?? [] as $user)
                                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }} (Role: {{ strtoupper($user->role) }})
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-400 mt-2">Hanya menampilkan user dengan role staf/guru yang belum terdaftar di HRD.</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Nomor Induk Yayasan (NIY) <span class="text-red-500">*</span></label>
                                <input type="text" name="employee_number" value="{{ old('employee_number') }}" required class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">NUPTK</label>
                                <input type="text" name="nuptk" value="{{ old('nuptk') }}" inputmode="numeric" maxlength="16" class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-medium">
                                <p class="text-xs text-gray-400 mt-2">{{ __('16 digit, untuk ekspor Dapodik. Kosongkan bila pegawai belum punya NUPTK.') }}</p>
                                @error('nuptk')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">{{ __('ID di Mesin Sidik Jari') }}</label>
                                <input type="text" name="fingerprint_id" value="{{ old('fingerprint_id') }}" maxlength="32" class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-medium">
                                <p class="text-xs text-gray-400 mt-2">{{ __('Nomor yang dipakai pegawai ini saat mendaftar sidik jari di mesin. Dipakai untuk mencocokkan log mesin yang diimpor.') }}</p>
                                @error('fingerprint_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Jabatan / Posisi <span class="text-red-500">*</span></label>
                                <input type="text" name="position" value="{{ old('position') }}" required placeholder="Contoh: Guru Matematika" class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Status Kepegawaian <span class="text-red-500">*</span></label>
                                <select name="employment_status" required class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-medium">
                                    <option value="gtt" {{ old('employment_status') == 'gtt' ? 'selected' : '' }}>Guru Tidak Tetap (GTT) / Honorer</option>
                                    <option value="gty" {{ old('employment_status') == 'gty' ? 'selected' : '' }}>Guru Tetap Yayasan (GTY)</option>
                                    <option value="staf_tetap" {{ old('employment_status') == 'staf_tetap' ? 'selected' : '' }}>Staf Tetap</option>
                                    <option value="honorer" {{ old('employment_status') == 'honorer' ? 'selected' : '' }}>Staf Honorer</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Tanggal Mulai Bekerja <span class="text-red-500">*</span></label>
                                <input type="date" name="hire_date" value="{{ old('hire_date') }}" required class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-medium text-gray-600">
                            </div>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-6 border-b border-gray-100 pb-2">2. Data Penggajian & Rekening</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Gaji Pokok (Bulan)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-medium">Rp</span>
                                    </div>
                                    <input type="number" name="base_salary" value="{{ old('base_salary', 0) }}" min="0" class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 pl-12 focus:ring-primary-500 font-medium">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Honor Per Jam (Jika Berlaku)</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <span class="text-gray-500 font-medium">Rp</span>
                                    </div>
                                    <input type="number" name="hourly_rate" value="{{ old('hourly_rate', 0) }}" min="0" class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 pl-12 focus:ring-primary-500 font-medium">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Nama Bank</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="Contoh: BSI" class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-medium">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Nomor Rekening</label>
                                <input type="text" name="bank_account" value="{{ old('bank_account') }}" class="w-full rounded-2xl border-gray-200 bg-gray-50 p-4 focus:ring-primary-500 font-medium">
                            </div>
                        </div>
                    </div>

                    <div class="pt-6">
                        <button type="submit" class="w-full py-4 bg-primary-600 text-white rounded-[2rem] font-black text-lg shadow-2xl shadow-primary-900/30 hover:scale-[1.02] transition-transform">
                            SIMPAN DATA PEGAWAI
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
