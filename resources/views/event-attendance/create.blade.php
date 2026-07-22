<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center justify-between space-y-4 md:space-y-0">
            <div>
                <h2 class="font-black text-2xl text-gray-800 tracking-tighter">
                    {{ __('Buat Kegiatan & Acara Baru') }}
                </h2>
                <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mt-1">
                    Inisialisasi Sistem Presensi Non-KBM
                </p>
            </div>
            <div>
                <a href="{{ route('event-attendance.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all">
                    ⬅ KEMBALI
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 overflow-x-hidden">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-[2.5rem] p-6 sm:p-10 shadow-sm border border-gray-100" 
                 x-data="{ 
                     target: '{{ old('target', 'all_students') }}',
                     searchStudent: '',
                     searchTeacher: ''
                 }">
                
                <div class="mb-8 border-b border-gray-100 pb-6">
                    <h3 class="font-black text-lg text-gray-800">Detail Rencana Kegiatan</h3>
                    <p class="text-xs text-gray-400 mt-1">Isi informasi kegiatan dengan lengkap. Sistem akan otomatis menginisialisasi draf kehadiran (dengan status default Alpa) untuk seluruh peserta yang ditargetkan.</p>
                </div>

                <form method="POST" action="{{ route('event-attendance.store') }}" class="space-y-8">
                    @csrf

                    @if($errors->any())
                        <div class="bg-rose-50 border border-rose-100 text-rose-700 px-5 py-4 rounded-2xl relative" role="alert">
                            <h4 class="font-black text-xs uppercase tracking-wider mb-2">Terjadi Kesalahan:</h4>
                            <ul class="list-disc list-inside text-xs font-bold space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Form Fields Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Nama Kegiatan --}}
                        <div class="md:col-span-2">
                            <label for="name" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Nama Kegiatan / Acara <span class="text-rose-500">*</span></label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Upacara Bendera Hari Senin / Sholat Dzuhur Berjamaah" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100" required>
                        </div>

                        {{-- Tipe Kegiatan --}}
                        <div>
                            <label for="type" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tipe Kegiatan <span class="text-rose-500">*</span></label>
                            <select id="type" name="type" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="upacara" {{ old('type') === 'upacara' ? 'selected' : '' }}>Upacara Bendera</option>
                                <option value="sholat_dhuha" {{ old('type') === 'sholat_dhuha' ? 'selected' : '' }}>Sholat Dhuha</option>
                                <option value="sholat_dzuhur" {{ old('type') === 'sholat_dzuhur' ? 'selected' : '' }}>Sholat Dzuhur</option>
                                <option value="kultum" {{ old('type') === 'kultum' ? 'selected' : '' }}>Kultum / Ceramah</option>
                                <option value="phbi" {{ old('type') === 'phbi' ? 'selected' : '' }}>PHBI (Peringatan Hari Besar Islam)</option>
                                <option value="rapat_guru" {{ old('type') === 'rapat_guru' ? 'selected' : '' }}>Rapat Guru</option>
                                <option value="osis" {{ old('type') === 'osis' ? 'selected' : '' }}>Kegiatan OSIS</option>
                                <option value="class_meeting" {{ old('type') === 'class_meeting' ? 'selected' : '' }}>Class Meeting</option>
                                <option value="lainnya" {{ old('type') === 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                        </div>

                        {{-- Tanggal Kegiatan --}}
                        <div>
                            <label for="event_date" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Tanggal Pelaksanaan <span class="text-rose-500">*</span></label>
                            <input type="date" id="event_date" name="event_date" value="{{ old('event_date', date('Y-m-d')) }}" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100" required>
                        </div>

                        {{-- Waktu Mulai --}}
                        <div>
                            <label for="start_time" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Waktu Mulai <span class="text-gray-400 text-[9px] font-normal">(Opsional)</span></label>
                            <input type="time" id="start_time" name="start_time" value="{{ old('start_time') }}" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                        </div>

                        {{-- Waktu Selesai --}}
                        <div>
                            <label for="end_time" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Waktu Selesai <span class="text-gray-400 text-[9px] font-normal">(Opsional)</span></label>
                            <input type="time" id="end_time" name="end_time" value="{{ old('end_time') }}" class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100">
                        </div>
                    </div>

                    {{-- Target Sasaran Peserta --}}
                    <div class="border-t border-gray-100 pt-6">
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Sasaran Peserta / Target Presensi <span class="text-rose-500">*</span></label>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
                            {{-- Option: All Students --}}
                            <label class="relative flex flex-col p-4 bg-gray-50 rounded-2xl cursor-pointer border-2 transition-all hover:bg-gray-100/50"
                                   :class="target === 'all_students' ? 'border-primary-600 bg-primary-50/20' : 'border-transparent'">
                                <input type="radio" name="target" value="all_students" x-model="target" class="sr-only">
                                <span class="text-xl mb-2"></span>
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-800">Semua Murid</span>
                                <span class="text-[9px] text-gray-400 mt-1">Seluruh siswa aktif sekolah</span>
                            </label>

                            {{-- Option: All Teachers --}}
                            <label class="relative flex flex-col p-4 bg-gray-50 rounded-2xl cursor-pointer border-2 transition-all hover:bg-gray-100/50"
                                   :class="target === 'all_teachers' ? 'border-primary-600 bg-primary-50/20' : 'border-transparent'">
                                <input type="radio" name="target" value="all_teachers" x-model="target" class="sr-only">
                                <span class="text-xl mb-2"></span>
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-800">Semua Guru</span>
                                <span class="text-[9px] text-gray-400 mt-1">Seluruh staf & guru pengajar</span>
                            </label>

                            {{-- Option: Specific Class --}}
                            <label class="relative flex flex-col p-4 bg-gray-50 rounded-2xl cursor-pointer border-2 transition-all hover:bg-gray-100/50"
                                   :class="target === 'specific_class' ? 'border-primary-600 bg-primary-50/20' : 'border-transparent'">
                                <input type="radio" name="target" value="specific_class" x-model="target" class="sr-only">
                                <span class="text-xl mb-2"></span>
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-800">Kelas Spesifik</span>
                                <span class="text-[9px] text-gray-400 mt-1">Siswa dari kelas-kelas tertentu</span>
                            </label>

                            {{-- Option: Specific Group --}}
                            <label class="relative flex flex-col p-4 bg-gray-50 rounded-2xl cursor-pointer border-2 transition-all hover:bg-gray-100/50"
                                   :class="target === 'specific_group' ? 'border-primary-600 bg-primary-50/20' : 'border-transparent'">
                                <input type="radio" name="target" value="specific_group" x-model="target" class="sr-only">
                                <span class="text-xl mb-2"></span>
                                <span class="text-[10px] font-black uppercase tracking-wider text-gray-800">Grup Kustom</span>
                                <span class="text-[9px] text-gray-400 mt-1">Kombinasi personal pilihan</span>
                            </label>
                        </div>

                        {{-- Panel: Specific Class --}}
                        <div x-show="target === 'specific_class'" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100 space-y-4"
                             style="display: none;">
                            <div class="flex items-center justify-between">
                                <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Pilih Kelas Sasaran</h4>
                                <span class="text-[9px] font-bold text-primary-600 bg-primary-50 px-2 py-0.5 rounded-full">Bisa memilih lebih dari satu</span>
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                                @foreach($classrooms as $classroom)
                                    <label class="flex items-center space-x-3 bg-white p-3 rounded-xl border border-gray-100 hover:border-primary-300 transition-all cursor-pointer">
                                        <input type="checkbox" name="target_ids[]" value="{{ $classroom->id }}" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                        <span class="text-xs font-bold text-gray-700">{{ $classroom->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Panel: Specific Group --}}
                        <div x-show="target === 'specific_group'" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform -translate-y-2"
                             x-transition:enter-end="opacity-100 transform translate-y-0"
                             class="bg-gray-50/50 rounded-3xl p-6 border border-gray-100 space-y-6"
                             style="display: none;">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {{-- Sub-panel: Siswa --}}
                                <div class="bg-white rounded-2xl p-4 border border-gray-100 flex flex-col h-96">
                                    <div class="mb-4">
                                        <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Siswa</h5>
                                        <input type="text" x-model="searchStudent" placeholder="Cari nama siswa..." class="w-full bg-gray-50 border-none rounded-xl px-3 py-2 font-bold text-[11px] focus:ring-4 focus:ring-primary-100">
                                    </div>
                                    <div class="flex-1 overflow-y-auto divide-y divide-gray-50 pr-2">
                                        @foreach($students as $student)
                                            @if($student->user)
                                                <label class="flex items-center justify-between py-2 cursor-pointer hover:bg-gray-50/50 px-1 rounded-lg transition-all"
                                                       x-show="searchStudent === '' || '{{ strtolower($student->user->name) }}'.includes(searchStudent.toLowerCase())">
                                                    <div class="flex items-center space-x-3">
                                                        <input type="checkbox" name="target_ids[]" value="{{ $student->user->id }}" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                                        <div class="text-left">
                                                            <p class="text-xs font-black text-gray-800">{{ $student->user->name }}</p>
                                                            <p class="text-[9px] text-gray-400">{{ $student->classroom->name ?? 'Tanpa Kelas' }} • NISN: {{ $student->nisn }}</p>
                                                        </div>
                                                    </div>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>

                                {{-- Sub-panel: Guru --}}
                                <div class="bg-white rounded-2xl p-4 border border-gray-100 flex flex-col h-96">
                                    <div class="mb-4">
                                        <h5 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Staf & Guru</h5>
                                        <input type="text" x-model="searchTeacher" placeholder="Cari nama guru..." class="w-full bg-gray-50 border-none rounded-xl px-3 py-2 font-bold text-[11px] focus:ring-4 focus:ring-primary-100">
                                    </div>
                                    <div class="flex-1 overflow-y-auto divide-y divide-gray-50 pr-2">
                                        @foreach($teachers as $teacher)
                                            <label class="flex items-center justify-between py-2 cursor-pointer hover:bg-gray-50/50 px-1 rounded-lg transition-all"
                                                   x-show="searchTeacher === '' || '{{ strtolower($teacher->name) }}'.includes(searchTeacher.toLowerCase())">
                                                <div class="flex items-center space-x-3">
                                                    <input type="checkbox" name="target_ids[]" value="{{ $teacher->id }}" class="rounded text-primary-600 focus:ring-primary-500 border-gray-300">
                                                    <div class="text-left">
                                                        <p class="text-xs font-black text-gray-800">{{ $teacher->name }}</p>
                                                        <p class="text-[9px] text-gray-400">NIP/NIK: {{ $teacher->nip ?? '-' }}</p>
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Kegiatan --}}
                    <div class="border-t border-gray-100 pt-6">
                        <label for="notes" class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2">Catatan Tambahan / Agenda Acara</label>
                        <textarea id="notes" name="notes" rows="4" placeholder="Tuliskan catatan khusus atau deskripsi kegiatan di sini..." class="w-full bg-gray-50 border-none rounded-xl px-4 py-3 font-bold text-xs focus:ring-4 focus:ring-primary-100">{{ old('notes') }}</textarea>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="border-t border-gray-100 pt-8 flex items-center justify-end space-x-3">
                        <a href="{{ route('event-attendance.index') }}" class="px-5 py-3 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-black uppercase tracking-widest transition-all">
                            Batal
                        </a>
                        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-xs font-black uppercase tracking-widest transition-all shadow-xl shadow-primary-900/10">
                             Buat Kegiatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
