<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Hasil Belajar') }}: {{ $student->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Breadcrumb / Back button -->
            <a href="{{ route('dashboard') }}" class="inline-flex items-center text-sm font-bold text-primary-600 hover:text-primary-800 transition-colors">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Dashboard
            </a>

            <!-- Profile & Summary Section (Similar to Student View) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Summary Stats -->
                <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Rata-Rata Nilai</span>
                        <h3 class="text-4xl font-black text-primary-600">{{ $reportCard ? number_format($reportCard->average_score, 2) : '0.00' }}</h3>
                        <p class="text-xs text-gray-400 mt-2">Semester Berjalan</p>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center text-amber-500">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-1">Peringkat Kelas</span>
                        <h3 class="text-4xl font-black">#{{ $reportCard ? $reportCard->rank_in_class : '-' }}</h3>
                    </div>

                    <div class="bg-emerald-600 p-6 rounded-2xl shadow-lg border border-emerald-500 flex flex-col items-center justify-center text-center text-white">
                        <svg class="w-8 h-8 mb-3 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        <h4 class="text-sm font-bold mb-3 uppercase tracking-wider">Download Rapor</h4>
                        <a href="{{ route('report-cards.pdf', $student->id) }}" target="_blank" class="px-4 py-2 bg-white text-emerald-700 rounded-lg text-xs font-black hover:bg-emerald-50 transition-colors shadow-sm">
                            UNDUH PDF
                        </a>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h4 class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-4">Profil Siswa</h4>
                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-12 h-12 bg-primary-50 rounded-full flex items-center justify-center border border-primary-100 text-primary-600 font-black">
                             {{ substr($student->name, 0, 1) }}
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-800">{{ $student->name }}</h4>
                            <p class="text-[10px] text-gray-400">NISN: {{ $student->nisn }}</p>
                        </div>
                    </div>
                    <div class="space-y-2">
                         <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Kelas</span>
                            <span class="font-bold text-gray-800">{{ $student?->classroom?->name }}</span>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span class="text-gray-500">Wali Kelas</span>
                            <span class="font-bold text-gray-800">{{ $student->classroom->teacher ? $student->classroom->teacher->name : '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detailed Grades (Same Table Component) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                    <h3 class="font-black text-gray-800 uppercase tracking-widest text-sm">Transparansi Nilai</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Nilai Akhir</th>
                                <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Predikat</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($grades ?? [] as $grade)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-bold text-gray-900">{{ $grade?->subject?->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="text-lg font-black text-primary-600">{{ number_format($grade->score, 0) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        @php
                                            $score = $grade->score;
                                            $predikat = $score >= 90 ? 'A' : ($score >= 80 ? 'B' : ($score >= 75 ? 'C' : 'D'));
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-xs font-black {{ $score >= 75 ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                            {{ $predikat }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($score >= 90) Sangat Memuaskan
                                        @elseif($score >= 80) Baik
                                        @elseif($score >= 75) Cukup
                                        @else Perlu Bimbingan Tambahan
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400">Belum ada data nilai.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Homeroom Notes -->
            @if($reportCard && $reportCard->homeroom_notes)
                <div class="bg-gray-900 rounded-2xl p-8 text-white relative overflow-hidden">
                    <svg class="absolute right-0 bottom-0 w-32 h-32 text-white/5 -mb-8 -mr-8" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"></path></svg>
                    <h4 class="text-xs font-bold uppercase tracking-widest text-primary-400 mb-4">Pesan Wali Kelas</h4>
                    <p class="text-lg font-medium italic relative z-10">"{{ $reportCard->homeroom_notes }}"</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
