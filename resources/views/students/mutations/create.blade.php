<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 dark:text-gray-100 leading-tight">
            {{ __('Catat Permohonan Mutasi Siswa') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('mutations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ mutationType: '{{ old('type', 'out') }}' }">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">Tipe Mutasi</label>
                            <select name="type" x-model="mutationType" required class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                                <option value="out">Mutasi Keluar (Pindah Sekolah)</option>
                                <option value="in">Mutasi Masuk (Siswa Pindahan Baru)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">Tanggal Efektif Mutasi</label>
                            <input type="date" name="mutation_date" value="{{ old('mutation_date', date('Y-m-d')) }}" required class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                        </div>
                    </div>

                    <div x-show="mutationType === 'out'">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">Pilih Siswa Aktif</label>
                        <select name="student_id" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                    {{ $student->user?->name }} (NIS: {{ $student->nis }} · {{ $student->classroom?->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="mutationType === 'in'" class="space-y-4 border-t border-b border-gray-100 dark:border-gray-700 py-4">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-gray-100">Identitas Siswa Pindahan &amp; Penempatan Rombel</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Lengkap Siswa</label>
                                <input type="text" name="student_name" value="{{ old('student_name') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Rombel Tujuan</label>
                                <select name="classroom_id" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                                    <option value="">-- Pilih Rombel --</option>
                                    @foreach($classrooms ?? [] as $classroom)
                                        <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">NIS</label>
                                <input type="text" name="nis" value="{{ old('nis') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">NISN</label>
                                <input type="text" name="nisn" value="{{ old('nisn') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Jenis Kelamin</label>
                                <select name="gender" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Ibu Kandung</label>
                                <input type="text" name="mother_name" value="{{ old('mother_name') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nama Orang Tua / Wali</label>
                                <input type="text" name="parent_name" value="{{ old('parent_name') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Nomor HP Orang Tua / Wali</label>
                                <input type="text" name="parent_phone" value="{{ old('parent_phone') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">Sekolah Tujuan / Asal</label>
                            <input type="text" name="target_school" value="{{ old('target_school') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm" placeholder="Nama sekolah tujuan atau asal">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">Nomor Surat Permohonan / Rekomendasi</label>
                            <input type="text" name="letter_number" value="{{ old('letter_number') }}" class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm" placeholder="Opsional">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">Alasan Mutasi</label>
                        <input type="text" name="reason" value="{{ old('reason') }}" required class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 p-3 text-sm" placeholder="Contoh: Pindah domisili orang tua">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase mb-2">Unggah Berkas Surat Pindah (PDF/JPG/PNG)</label>
                        <input type="file" name="document" class="w-full text-sm text-gray-500">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('mutations.index') }}" class="px-5 py-2.5 text-sm font-semibold text-gray-500 hover:text-gray-700">Batal</a>
                        <button type="submit" class="px-6 py-2.5 bg-primary-600 text-white rounded-xl font-bold text-sm hover:bg-primary-700 transition-colors">
                            Ajukan ke Kepala Sekolah
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
