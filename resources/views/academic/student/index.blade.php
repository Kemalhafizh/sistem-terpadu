<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Hasil Belajar Saya') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            @if(!$activeYear)
                <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-r-xl">
                    <p class="text-sm text-amber-700">Tahun ajaran aktif belum ditentukan oleh kurikulum.</p>
                </div>
            @endif

            <!-- Profile & Summary Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Summary Stats -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Average Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Rata-Rata Nilai</span>
                        <h3 class="text-4xl font-black text-primary-600">{{ $reportCard ? number_format($reportCard->average_score, 2) : '0.00' }}</h3>
                        <p class="text-xs text-gray-400 mt-2">Semester ini</p>
                    </div>

                    <!-- Rank Card -->
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Peringkat Kelas</span>
                        <h3 class="text-4xl font-black text-amber-500">#{{ $reportCard ? $reportCard->rank_in_class : '-' }}</h3>
                        <p class="text-xs text-gray-400 mt-2">Dari seluruh siswa</p>
                    </div>

                    <!-- PDF Card -->
                    <div class="bg-primary-900 p-6 rounded-2xl shadow-lg border border-primary-800 flex flex-col items-center justify-center text-center text-white">
                        <svg class="w-8 h-8 mb-3 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        <h4 class="text-sm font-bold mb-3 uppercase tracking-wider">E-Rapor PDF</h4>
                        <a href="{{ route('report-cards.pdf', $student->id) }}" target="_blank" class="px-4 py-2 bg-white text-primary-900 rounded-lg text-xs font-black hover:bg-primary-50 transition-colors shadow-sm">
                            UNDUH SEKARANG
                        </a>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center border-2 border-primary-100">
                             <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h4 class="font-black text-gray-800">{{ $student->name }}</h4>
                            <p class="text-xs text-gray-400">NIS: {{ $student->nis }}</p>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Kelas</span>
                            <span class="font-bold text-gray-800">{{ $student?->classroom?->name }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tahun Ajaran</span>
                            <span class="font-bold text-gray-800">{{ $activeYear?->name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Grades Table -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex flex-col sm:flex-row justify-between items-start sm:items-center bg-gray-50/50 gap-4">
                    <div>
                        <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm">Rincian Nilai Akhir per Mata Pelajaran</h3>
                        <p class="text-[10px] text-gray-400 font-bold mt-1 uppercase tracking-wider">Nilai Gabungan Raport Akhir Semester</p>
                    </div>
                    <a href="{{ route('student.daily-assessments') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-md shadow-indigo-900/10 transition-all">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Lihat Nilai Harian & Remedial
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Tugas</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">UTS</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">UAS</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Predikat</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($grades ?? [] as $grade)
                                <tr class="hover:bg-primary-50/30 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $grade?->subject?->name }}</div>
                                        <div class="text-[10px] text-gray-400 font-medium tracking-tight">{{ $grade->subject->category }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">80</td> <!-- Mock component values -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">85</td> <!-- PTS -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">90</td> <!-- PAS -->
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex px-3 py-1 rounded-full text-xs font-black bg-primary-50 text-primary-700 border border-primary-100">
                                            {{ number_format($grade->score, 0) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $score = $grade->score;
                                            $predikat = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 75 ? 'C' : 'D'));
                                            $color = $score >= 75 ? 'text-emerald-600' : 'text-rose-600';
                                        @endphp
                                        <span class="text-sm font-black {{ $color }}">{{ $predikat }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-400 italic">
                                        Data nilai belum tersedia untuk semester ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Notes Section -->
            @if($reportCard && $reportCard->homeroom_notes)
                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-6">
                    <h4 class="text-sm font-black text-blue-800 uppercase tracking-widest mb-3 flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path></svg>
                        Catatan Wali Kelas
                    </h4>
                    <p class="text-sm text-blue-700 leading-relaxed italic">"{{ $reportCard->homeroom_notes }}"</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
