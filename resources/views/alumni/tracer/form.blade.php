<x-app-layout>
    <x-slot name="title">Kuesioner Tracer Study Alumni</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📋 Kuesioner Tracer Study
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Bantu kami meningkatkan mutu pembelajaran di SMA Islam Sultan Agung 1 Semarang.
                </p>
            </div>
            <a href="{{ route('alumni.show', $alumni->uuid) }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                👁️ Lihat Profil
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <!-- Notifikasi Sukses -->
        @if(session('success'))
            <div class="mb-6 p-4 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-400 rounded-3xl border border-emerald-100 dark:border-emerald-900/30 text-xs font-bold">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <form action="{{ $existingResponse ? route('alumni.tracer-study.update', $existingResponse->uuid) : route('alumni.tracer-study.submit') }}" method="POST" class="space-y-6">
                @csrf
                @if($existingResponse)
                    @method('PUT')
                @endif

                <!-- Aktivitas Utama -->
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Aktivitas Anda Saat Ini</label>
                    <select name="current_activity" id="activity-select" required onchange="toggleFormFields(this.value)" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="study" {{ old('current_activity', $alumni->current_activity) == 'study' ? 'selected' : '' }}>Melanjutkan Kuliah / Pendidikan</option>
                        <option value="work" {{ old('current_activity', $alumni->current_activity) == 'work' ? 'selected' : '' }}>Bekerja (Karyawan / PNS / Swasta)</option>
                        <option value="entrepreneur" {{ old('current_activity', $alumni->current_activity) == 'entrepreneur' ? 'selected' : '' }}>Berwirausaha / Buka Usaha Mandiri</option>
                        <option value="unemployed" {{ old('current_activity', $alumni->current_activity) == 'unemployed' ? 'selected' : '' }}>Mencari Pekerjaan</option>
                        <option value="other" {{ old('current_activity', $alumni->current_activity) == 'other' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                </div>

                <!-- Bagian 1: Melanjutkan Kuliah -->
                <div id="section-study" class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/50 hidden">
                    <h3 class="text-xs font-black text-blue-600 dark:text-blue-400 uppercase tracking-wider">🎓 Informasi Kuliah</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Perguruan Tinggi</label>
                            <input type="text" name="institution_name" value="{{ old('institution_name', $alumni->institution_name) }}" placeholder="Universitas Diponegoro..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Program Studi / Jurusan</label>
                            <input type="text" name="study_major" value="{{ old('study_major', $existingResponse->study_major ?? '') }}" placeholder="S1 Teknik Informatika..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Bagian 2: Bekerja / Wirausaha -->
                <div id="section-work" class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/50 hidden">
                    <h3 class="text-xs font-black text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">💼 Informasi Pekerjaan / Bisnis</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Nama Perusahaan / Usaha</label>
                            <input type="text" name="employer_name" value="{{ old('employer_name', $existingResponse->employer_name ?? '') }}" placeholder="PT Astra Internasional..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Posisi / Jabatan</label>
                            <input type="text" name="job_title" value="{{ old('job_title', $existingResponse->job_title ?? '') }}" placeholder="Staf Admin / Software Engineer..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tahun Mulai</label>
                            <input type="number" name="entry_year" value="{{ old('entry_year', $existingResponse->entry_year ?? date('Y')) }}" placeholder="2025" min="2000" max="{{ date('Y') + 1 }}" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kisaran Pendapatan (Gaji Bulanan)</label>
                            <select name="salary_range" class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Gaji --</option>
                                <option value="< 3M" {{ old('salary_range', $existingResponse->salary_range ?? '') == '< 3M' ? 'selected' : '' }}>< Rp 3.000.000</option>
                                <option value="3M - 5M" {{ old('salary_range', $existingResponse->salary_range ?? '') == '3M - 5M' ? 'selected' : '' }}>Rp 3.000.000 - Rp 5.000.000</option>
                                <option value="5M - 10M" {{ old('salary_range', $existingResponse->salary_range ?? '') == '5M - 10M' ? 'selected' : '' }}>Rp 5.000.000 - Rp 10.000.000</option>
                                <option value="> 10M" {{ old('salary_range', $existingResponse->salary_range ?? '') == '> 10M' ? 'selected' : '' }}>> Rp 10.000.000</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Bagian 3: Feedback & Keselarasan -->
                <div class="space-y-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">🎯 Evaluasi Hubungan Kurikulum & Pembelajaran</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Seberapa selaras materi di sekolah dengan aktivitas Anda saat ini? (1-5)</label>
                            <div class="flex gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="flex-1 text-center py-2 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all">
                                        <input type="radio" name="relevance_score" value="{{ $i }}" required {{ old('relevance_score', $existingResponse->relevance_score ?? 5) == $i ? 'checked' : '' }} class="mr-1">
                                        <span class="text-xs font-bold text-gray-800 dark:text-white">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Tingkat kepuasan Anda terhadap bekal pendidikan dari sekolah? (1-5)</label>
                            <div class="flex gap-2">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="flex-1 text-center py-2 border border-gray-200 dark:border-gray-700 rounded-xl cursor-pointer hover:bg-indigo-50/50 dark:hover:bg-indigo-950/20 transition-all">
                                        <input type="radio" name="satisfaction_score" value="{{ $i }}" required {{ old('satisfaction_score', $existingResponse->satisfaction_score ?? 5) == $i ? 'checked' : '' }} class="mr-1">
                                        <span class="text-xs font-bold text-gray-800 dark:text-white">{{ $i }}</span>
                                    </label>
                                @endfor
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Saran / Masukan untuk Kurikulum Sekolah</label>
                        <textarea name="suggestions" rows="4" placeholder="Tuliskan saran perbaikan materi pembelajaran atau program sekolah agar lebih relevan dengan kebutuhan alumni..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 text-xs text-gray-800 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('suggestions', $existingResponse->suggestions ?? '') }}</textarea>
                    </div>
                </div>

                <!-- Tombol Aksi -->
                <div class="pt-6 border-t border-gray-100 dark:border-gray-700/50 flex justify-end gap-2">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2 px-8 text-xs font-bold transition-all shadow-md">
                        {{ $existingResponse ? '✏️ Perbarui Respons' : '🚀 Kirim Kuesioner' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleFormFields(activity) {
            const studySec = document.getElementById('section-study');
            const workSec = document.getElementById('section-work');

            // Reset visibility
            studySec.classList.add('hidden');
            workSec.classList.add('hidden');

            if (activity === 'study') {
                studySec.classList.remove('hidden');
            } else if (activity === 'work' || activity === 'entrepreneur') {
                workSec.classList.remove('hidden');
            }
        }

        // Initialize state on load
        document.addEventListener("DOMContentLoaded", function() {
            const selectValue = document.getElementById('activity-select').value;
            toggleFormFields(selectValue);
        });
    </script>
</x-app-layout>
