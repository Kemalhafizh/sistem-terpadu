<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 dark:text-slate-100 leading-tight">
                    {{ __('Catat Permohonan Mutasi Siswa') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                    {{ __('Permohonan mutasi masuk atau keluar akan diteruskan ke Kepala Sekolah untuk persetujuan resmi') }}
                </p>
            </div>
            <a href="{{ route('mutations.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-medium bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span>{{ __('Kembali ke Daftar Mutasi') }}</span>
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200/80 dark:border-slate-700 p-6 sm:p-8">
                @if ($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-700 dark:text-rose-200 text-sm">
                        <ul class="list-disc ps-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('mutations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="{ mutationType: @js(old('type', 'out')) }">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="mutation_type" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Tipe Mutasi') }}</label>
                            <select id="mutation_type" name="type" x-model="mutationType" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2.5 px-3 text-sm">
                                <option value="out">{{ __('Mutasi Keluar (Pindah Sekolah)') }}</option>
                                <option value="in">{{ __('Mutasi Masuk (Siswa Pindahan Baru)') }}</option>
                            </select>
                        </div>
                        <div>
                            <label for="mutation_date" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Tanggal Efektif Mutasi') }}</label>
                            <input id="mutation_date" type="date" name="mutation_date" value="{{ old('mutation_date', date('Y-m-d')) }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2.5 px-3 text-sm tabular-nums">
                        </div>
                    </div>

                    <div x-show="mutationType === 'out'">
                        <label for="student_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Pilih Siswa Aktif') }}</label>
                        <select id="student_id" name="student_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2.5 px-3 text-sm">
                            <option value="">{{ __('Pilih Siswa') }}</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                    {{ $student->user?->name }} ({{ __('NIS') }}: {{ $student->nis }} &middot; {{ $student->classroom?->name ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div x-show="mutationType === 'in'" style="display: none;" class="space-y-4 border-t border-b border-slate-200 dark:border-slate-700 py-5">
                        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Identitas Siswa Pindahan & Penempatan Rombel') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="student_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Nama Lengkap Siswa') }}</label>
                                <input id="student_name" type="text" name="student_name" value="{{ old('student_name') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2 px-3 text-sm">
                            </div>
                            <div>
                                <label for="classroom_id" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Rombel Tujuan') }}</label>
                                <select id="classroom_id" name="classroom_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2 px-3 text-sm">
                                    <option value="">{{ __('Pilih Rombel') }}</option>
                                    @foreach($classrooms ?? [] as $classroom)
                                        <option value="{{ $classroom->id }}" @selected(old('classroom_id') == $classroom->id)>{{ $classroom->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="nis" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('NIS') }}</label>
                                <input id="nis" type="text" name="nis" value="{{ old('nis') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2 px-3 text-sm tabular-nums">
                            </div>
                            <div>
                                <label for="nisn" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('NISN') }}</label>
                                <input id="nisn" type="text" name="nisn" value="{{ old('nisn') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2 px-3 text-sm tabular-nums">
                            </div>
                            <div>
                                <label for="gender" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Jenis Kelamin') }}</label>
                                <select id="gender" name="gender" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2 px-3 text-sm">
                                    <option value="L" @selected(old('gender') === 'L')>{{ __('Laki-laki') }}</option>
                                    <option value="P" @selected(old('gender') === 'P')>{{ __('Perempuan') }}</option>
                                </select>
                            </div>
                            <div>
                                <label for="mother_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Nama Ibu Kandung') }}</label>
                                <input id="mother_name" type="text" name="mother_name" value="{{ old('mother_name') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2 px-3 text-sm">
                            </div>
                            <div>
                                <label for="parent_name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Nama Orang Tua / Wali') }}</label>
                                <input id="parent_name" type="text" name="parent_name" value="{{ old('parent_name') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2 px-3 text-sm">
                            </div>
                            <div>
                                <label for="parent_phone" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">{{ __('Nomor HP Orang Tua / Wali') }}</label>
                                <input id="parent_phone" type="text" name="parent_phone" value="{{ old('parent_phone') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2 px-3 text-sm tabular-nums">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="target_school" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Sekolah Tujuan / Asal') }}</label>
                            <input id="target_school" type="text" name="target_school" value="{{ old('target_school') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2.5 px-3 text-sm" placeholder="{{ __('Nama sekolah tujuan atau asal') }}">
                        </div>
                        <div>
                            <label for="letter_number" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Nomor Surat Permohonan / Rekomendasi') }}</label>
                            <input id="letter_number" type="text" name="letter_number" value="{{ old('letter_number') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2.5 px-3 text-sm" placeholder="{{ __('Opsional') }}">
                        </div>
                    </div>

                    <div>
                        <label for="reason" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Alasan Mutasi') }}</label>
                        <input id="reason" type="text" name="reason" value="{{ old('reason') }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-100 py-2.5 px-3 text-sm" placeholder="{{ __('Contoh: Pindah domisili orang tua') }}">
                    </div>

                    <div>
                        <label for="document" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5">{{ __('Unggah Berkas Surat Pindah (PDF/JPG/PNG)') }}</label>
                        <input id="document" type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="w-full text-sm text-slate-600 dark:text-slate-300 file:me-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 dark:file:bg-slate-700 file:text-slate-700 dark:file:text-slate-200">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-200 dark:border-slate-700">
                        <a href="{{ route('mutations.index') }}" class="px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white transition-colors">{{ __('Batal') }}</a>
                        <button type="submit" class="px-5 py-2.5 bg-primary-600 text-white rounded-lg font-medium text-sm hover:bg-primary-700 transition-colors">
                            {{ __('Ajukan ke Kepala Sekolah') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
