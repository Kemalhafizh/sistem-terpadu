<x-app-layout>
    <x-slot name="title">Penilaian Kinerja - {{ $employee->user->name }}</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Form Evaluasi Kinerja Guru/Staf
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pegawai: {{ $employee->user->name }} | Jabatan: {{ $employee->position }}
                </p>
            </div>
            <a href="{{ route('hrd.employees.show', $employee->id) }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                 Lihat Profil
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Input Form -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl lg:col-span-2">
            <form action="{{ route('hrd.employees.evaluation.store', $employee->id) }}" method="POST" class="space-y-6">
                @csrf
                
                <!-- Periode Penilaian -->
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Periode Penilaian Kinerja</label>
                    <select name="period" required class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                        <option value="Semester Ganjil 2025/2026">Semester Ganjil 2025/2026</option>
                        <option value="Semester Genap 2025/2026">Semester Genap 2025/2026</option>
                        <option value="Semester Ganjil 2026/2027">Semester Ganjil 2026/2027</option>
                        <option value="Semester Genap 2026/2027">Semester Genap 2026/2027</option>
                    </select>
                </div>

                <!-- Sliders Kompetensi -->
                <div class="space-y-5 pt-4 border-t border-gray-150 dark:border-gray-700">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider"> Kriteria Kompetensi Kinerja</h3>

                    <!-- Pedagogik -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold text-gray-800 dark:text-white">
                            <span>1. Kompetensi Pedagogik</span>
                            <span id="label-pedagogik" class="text-indigo-600 dark:text-indigo-400">80</span>
                        </div>
                        <input type="range" name="pedagogik" min="0" max="100" value="80" oninput="updateLiveScore('pedagogik', this.value)" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>

                    <!-- Profesional -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold text-gray-800 dark:text-white">
                            <span>2. Kompetensi Profesional</span>
                            <span id="label-profesional" class="text-indigo-600 dark:text-indigo-400">80</span>
                        </div>
                        <input type="range" name="profesional" min="0" max="100" value="80" oninput="updateLiveScore('profesional', this.value)" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>

                    <!-- Sosial -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold text-gray-800 dark:text-white">
                            <span>3. Kompetensi Sosial</span>
                            <span id="label-sosial" class="text-indigo-600 dark:text-indigo-400">80</span>
                        </div>
                        <input type="range" name="sosial" min="0" max="100" value="80" oninput="updateLiveScore('sosial', this.value)" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>

                    <!-- Kepribadian -->
                    <div class="space-y-2">
                        <div class="flex justify-between text-xs font-bold text-gray-800 dark:text-white">
                            <span>4. Kompetensi Kepribadian</span>
                            <span id="label-kepribadian" class="text-indigo-600 dark:text-indigo-400">80</span>
                        </div>
                        <input type="range" name="kepribadian" min="0" max="100" value="80" oninput="updateLiveScore('kepribadian', this.value)" class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                    </div>
                </div>

                <!-- Evaluasi Kualitatif -->
                <div class="space-y-4 pt-4 border-t border-gray-150 dark:border-gray-700">
                    <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider"> Ulasan Kualitatif</h3>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Kekuatan Utama (Strengths)</label>
                        <textarea name="strengths" rows="3" placeholder="Sebutkan hal-hal positif atau prestasi kerja pegawai..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-800 dark:text-white focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Area Perbaikan (Improvements)</label>
                        <textarea name="improvements" rows="3" placeholder="Sebutkan kompetensi yang perlu ditingkatkan..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-800 dark:text-white focus:outline-none"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Sasaran / Target Semester Depan (Goals)</label>
                        <textarea name="goals" rows="3" placeholder="Tulis target peningkatan kinerja yang harus dicapai..." class="w-full bg-gray-55/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl p-4 text-xs text-gray-800 dark:text-white focus:outline-none"></textarea>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-150 dark:border-gray-700 flex justify-end">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-2.5 px-8 text-xs font-bold transition-all shadow-md">
                         Simpan Hasil Evaluasi
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Score Feedback Card -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex flex-col justify-center items-center text-center self-start min-h-[300px]">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kalkulasi Skor Live</span>
            
            <div class="w-32 h-32 rounded-full border-8 border-indigo-600 flex flex-col items-center justify-center mt-6 shadow-inner">
                <span id="live-avg" class="text-3xl font-black text-indigo-700 dark:text-indigo-400">80.0</span>
                <span class="text-[10px] text-gray-400 font-bold">RATA-RATA</span>
            </div>

            <div class="mt-6">
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Predikat Otomatis</span>
                <h3 id="live-grade" class="font-black text-4xl text-emerald-600 dark:text-emerald-400 mt-1">B</h3>
                <p id="live-grade-label" class="text-[11px] text-gray-400 font-bold mt-1">SANGAT MEMUASKAN</p>
            </div>
        </div>
    </div>

    <script>
        const scores = {
            pedagogik: 80,
            profesional: 80,
            sosial: 80,
            kepribadian: 80
        };

        function updateLiveScore(key, val) {
            scores[key] = parseFloat(val);
            document.getElementById('label-' + key).innerText = val;

            // Recalculate average
            const sum = scores.pedagogik + scores.profesional + scores.sosial + scores.kepribadian;
            const avg = (sum / 4).toFixed(1);
            document.getElementById('live-avg').innerText = avg;

            // Update grade predicate
            let grade = 'E';
            let label = 'SANGAT KURANG';
            
            if (avg >= 85) {
                grade = 'A';
                label = 'SANGAT BAIK';
            } else if (avg >= 70) {
                grade = 'B';
                label = 'BAIK (MEMUASKAN)';
            } else if (avg >= 55) {
                grade = 'C';
                label = 'CUKUP';
            } else if (avg >= 40) {
                grade = 'D';
                label = 'KURANG';
            }

            document.getElementById('live-grade').innerText = grade;
            document.getElementById('live-grade-label').innerText = label;
        }
    </script>
</x-app-layout>
