<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Buat Penilaian Harian Baru') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-bold text-gray-700">Formulir Rencana Evaluasi Formatif</h3>
                    <p class="text-xs text-gray-400">Silakan isi detail data penilaian harian di bawah ini.</p>
                </div>
                
                <form method="POST" action="{{ route('daily-assessments.store') }}" class="p-6 space-y-6">
                    @csrf

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                            <ul class="list-disc list-inside text-sm font-medium">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Kelas -->
                        <div>
                            <label for="classroom_id" class="block font-medium text-sm text-gray-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                            <select id="classroom_id" name="classroom_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classrooms as $cls)
                                    <option value="{{ $cls->id }}" {{ old('classroom_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Mapel -->
                        <div>
                            <label for="subject_id" class="block font-medium text-sm text-gray-700 mb-1">Mata Pelajaran <span class="text-red-500">*</span></label>
                            <select id="subject_id" name="subject_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                                <option value="">-- Pilih Mapel --</option>
                                @foreach($subjects as $sbj)
                                    <option value="{{ $sbj->id }}" {{ old('subject_id') == $sbj->id ? 'selected' : '' }}>{{ $sbj->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Judul -->
                        <div>
                            <label for="title" class="block font-medium text-sm text-gray-700 mb-1">Judul Penilaian <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" value="{{ old('title') }}" placeholder="Contoh: Kuis 1 Logaritma" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                        </div>

                        <!-- Tipe -->
                        <div>
                            <label for="type" class="block font-medium text-sm text-gray-700 mb-1">Tipe Penilaian <span class="text-red-500">*</span></label>
                            <select id="type" name="type" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                                <option value="">-- Pilih Tipe --</option>
                                <option value="ulangan_harian" {{ old('type') == 'ulangan_harian' ? 'selected' : '' }}>Ulangan Harian</option>
                                <option value="kuis" {{ old('type') == 'kuis' ? 'selected' : '' }}>Kuis</option>
                                <option value="praktikum" {{ old('type') == 'praktikum' ? 'selected' : '' }}>Praktikum</option>
                                <option value="project" {{ old('type') == 'project' ? 'selected' : '' }}>Proyek</option>
                                <option value="presentasi" {{ old('type') == 'presentasi' ? 'selected' : '' }}>Presentasi</option>
                                <option value="tugas" {{ old('type') == 'tugas' ? 'selected' : '' }}>Tugas</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Tanggal -->
                        <div>
                            <label for="assessment_date" class="block font-medium text-sm text-gray-700 mb-1">Tanggal Ujian <span class="text-red-500">*</span></label>
                            <input type="date" id="assessment_date" name="assessment_date" value="{{ old('assessment_date', date('Y-m-d')) }}" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                        </div>

                        <!-- Max Score -->
                        <div>
                            <label for="max_score" class="block font-medium text-sm text-gray-700 mb-1">Nilai Maksimum <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" id="max_score" name="max_score" value="{{ old('max_score', 100) }}" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required>
                        </div>

                        <!-- KKM Custom Override -->
                        <div>
                            <label for="kkm" class="block font-medium text-sm text-gray-700 mb-1">Override KKM <span class="text-xs text-gray-400">(Opsional)</span></label>
                            <input type="number" step="0.01" id="kkm" name="kkm" value="{{ old('kkm') }}" placeholder="Bawaan KKM Mapel" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                        </div>
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label for="description" class="block font-medium text-sm text-gray-700 mb-1">Keterangan / Materi Uji</label>
                        <textarea id="description" name="description" rows="3" placeholder="Tuliskan keterangan detail di sini..." class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">{{ old('description') }}</textarea>
                    </div>

                    <div class="border-t border-gray-200 pt-6 flex justify-end gap-3">
                        <a href="{{ route('daily-assessments.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gray-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring ring-gray-300 transition duration-150 shadow-md">
                            Simpan & Masukkan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
