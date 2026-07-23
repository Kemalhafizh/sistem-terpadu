<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Input Nilai Penilaian Harian (Spreadsheet Mode)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Context Info Card -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="font-bold text-gray-700 text-lg">Penilaian: {{ $assessment->title }}</h3>
                    <p class="text-sm text-gray-500">Kelas: <b class="text-gray-800">{{ $assessment->classroom->name }}</b> | Pelajaran: <b class="text-gray-800">{{ $assessment->subject->name }}</b> | KKM: <span class="font-bold text-primary-600" id="kkm-val">{{ number_format($assessment->effective_kkm, 0) }}</span></p>
                </div>
                <a href="{{ route('daily-assessments.show', $assessment->id) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest bg-white hover:bg-gray-50 shadow-sm transition">
                    Kembali
                </a>
            </div>

            <!-- Spreadsheet Form -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <form method="POST" action="{{ route('daily-assessments.store-scores', $assessment->id) }}">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 border-b border-gray-200">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider w-16 border-r border-gray-700">No</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider border-r border-gray-700">Siswa</th>
                                    <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-36 border-r border-gray-700">Input Nilai</th>
                                    <th scope="col" class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Catatan Tambahan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($assessment->scores as $index => $score)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 font-medium border-r border-gray-200 bg-gray-50 text-center">{{ $index + 1 }}</td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-gray-900 border-r border-gray-200">
                                            {{ $score->student->name }}
                                            <div class="text-xs text-gray-400 font-normal">NIS: {{ $score->student->nis }}</div>
                                        </td>
                                        
                                        <!-- Score Input -->
                                        <td class="px-3 py-2 whitespace-nowrap border-r border-gray-200">
                                            <input type="number" step="0.01" min="0" max="{{ $assessment->max_score }}" 
                                                   name="grades[{{ $score->student_id }}][score]" 
                                                   value="{{ old("grades.{$score->student_id}.score", $score->score) }}" 
                                                   class="score-input block w-full text-center text-sm font-bold rounded-md shadow-sm border-gray-300 focus:ring-opacity-50 transition-all"
                                                   placeholder="0 - {{ number_format($assessment->max_score, 0) }}">
                                        </td>
                                        
                                        <!-- Notes Input -->
                                        <td class="px-3 py-2">
                                            <input type="text" name="grades[{{ $score->student_id }}][notes]" 
                                                   value="{{ old("grades.{$score->student_id}.notes", $score->notes) }}" 
                                                   class="block w-full text-sm rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50"
                                                   placeholder="Contoh: Sangat aktif, kurang konsentrasi, dll.">
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                                            Tidak ada siswa terdaftar pada kelas ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-between items-center">
                        <p class="text-xs text-gray-500"><b class="text-gray-800">Tips:</b> Nilai di bawah KKM ({{ number_format($assessment->effective_kkm, 0) }}) akan otomatis disorot warna merah.</p>
                        <button type="submit" class="inline-flex items-center px-8 py-3 bg-gray-900 border border-transparent rounded-xl font-black text-sm text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring ring-gray-300 transition ease-in-out duration-150 shadow-lg">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Live JS to highlight scores below KKM in red -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const kkm = parseFloat(document.getElementById('kkm-val').innerText);
            const inputs = document.querySelectorAll('.score-input');

            function checkKkm(input) {
                const val = parseFloat(input.value);
                if (!isNaN(val) && val < kkm) {
                    // Highlight red
                    input.classList.remove('bg-white', 'border-gray-300');
                    input.classList.add('bg-red-50', 'border-red-400', 'text-red-900', 'focus:border-red-400', 'focus:ring-red-200');
                } else {
                    // Reset standard
                    input.classList.remove('bg-red-50', 'border-red-400', 'text-red-900', 'focus:border-red-400', 'focus:ring-red-200');
                    input.classList.add('bg-white', 'border-gray-300');
                }
            }

            inputs.forEach(input => {
                // Check on initial load
                checkKkm(input);
                // Check on input change
                input.addEventListener('input', function() {
                    checkKkm(this);
                });
            });
        });
    </script>
</x-app-layout>
