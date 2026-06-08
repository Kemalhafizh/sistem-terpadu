<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Input Nilai Remedial Siswa') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="p-6 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-bold text-gray-700">Perbaikan Nilai Formatif</h3>
                    <p class="text-xs text-gray-400">Silakan isi nilai hasil ujian remedial/perbaikan siswa.</p>
                </div>

                <form method="POST" action="{{ route('daily-assessments.store-remedial', $score->id) }}" class="p-6 space-y-6">
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

                    <!-- Student Profile Widget -->
                    <div class="bg-amber-50 border border-amber-100 p-4 rounded-xl space-y-3">
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-400 block text-xs font-semibold uppercase">Nama Siswa</span>
                                <span class="font-bold text-gray-800">{{ $score->student->name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-xs font-semibold uppercase">NIS</span>
                                <span class="font-bold text-gray-800">{{ $score->student->nis }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-xs font-semibold uppercase">Mata Pelajaran</span>
                                <span class="font-bold text-gray-800">{{ $score->dailyAssessment->subject->name }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-xs font-semibold uppercase">Penilaian</span>
                                <span class="font-bold text-gray-800">{{ $score->dailyAssessment->title }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-xs font-semibold uppercase">Batas KKM</span>
                                <span class="font-bold text-primary-600">{{ number_format($score->dailyAssessment->effective_kkm, 0) }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-xs font-semibold uppercase">Nilai Awal</span>
                                <span class="font-bold text-red-600">{{ number_format($score->score, 0) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Input Remedial Score -->
                    <div>
                        <label for="remedial_score" class="block font-medium text-sm text-gray-700 mb-1">Nilai Remedial <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0" max="100" id="remedial_score" name="remedial_score" 
                               value="{{ old('remedial_score', $score->remedial_score) }}" 
                               class="block w-full rounded-md shadow-sm border-gray-300 focus:border-amber-400 focus:ring focus:ring-amber-200 focus:ring-opacity-50 font-bold" 
                               required placeholder="0.00 - 100.00">
                        <p class="text-xs text-gray-400 mt-1">Sesuai Kurikulum Merdeka, nilai remedial yang lulus akan dicapping maksimal setara KKM ({{ number_format($score->dailyAssessment->effective_kkm, 0) }}).</p>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block font-medium text-sm text-gray-700 mb-1">Catatan Remedial</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Contoh: Mengikuti pendampingan materi terlebih dahulu." class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">{{ old('notes', $score->notes) }}</textarea>
                    </div>

                    <div class="border-t border-gray-200 pt-6 flex justify-end gap-3">
                        <a href="{{ route('daily-assessments.remedial') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none transition">
                            Batal
                        </a>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 bg-gray-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-900 focus:outline-none transition shadow-md">
                            Simpan Perbaikan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
