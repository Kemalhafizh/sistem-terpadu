<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Nilai Harian & Formatif Anak') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Welcome Header Card -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-gray-700 text-lg">Hasil Belajar Harian: {{ $student->name }}</h3>
                    <p class="text-sm text-gray-500">Mata Pelajaran: Kelas <b class="text-gray-800">{{ $student->classroom->name ?? '-' }}</b> | NIS: <b class="text-gray-800">{{ $student->nis }}</b></p>
                </div>
                <a href="{{ route('parent-portal.dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest bg-white hover:bg-gray-50 shadow-sm transition">
                    Kembali ke Dashboard
                </a>
            </div>

            <!-- Grades List Table -->
            @if($dailyScores->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider w-16">No</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Penilaian</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-24">Tipe</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-28">Tanggal</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-20">KKM</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-24">Skor Awal</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-36">Status Remedial</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-primary-600 uppercase tracking-wider w-28">Nilai Akhir</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Guru</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($dailyScores as $index => $score)
                                    @php
                                        $as = $score->dailyAssessment;
                                        $typeLabels = [
                                            'ulangan_harian' => 'Ulangan Harian',
                                            'kuis' => 'Kuis',
                                            'praktikum' => 'Praktikum',
                                            'project' => 'Proyek',
                                            'presentasi' => 'Presentasi',
                                            'tugas' => 'Tugas',
                                        ];
                                        $final = $score->final_score;
                                        $passed = $score->is_passed;
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium text-center bg-gray-50 border-r border-gray-100">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                            {{ $as->subject->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            {{ $as->title }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-xs font-semibold text-gray-500">
                                            {{ $typeLabels[$as->type] ?? $as->type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-600">
                                            {{ $as->assessment_date->isoFormat('D MMM YYYY') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-800">
                                            {{ number_format($as->effective_kkm, 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold {{ $score->score !== null && $score->score < $as->effective_kkm ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ $score->score !== null ? number_format($score->score, 0) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($score->remedial_status === 'not_needed')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                    Lulus KKM
                                                </span>
                                            @elseif($score->remedial_status === 'pending')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-100">
                                                    Butuh Remedial
                                                </span>
                                            @elseif($score->remedial_status === 'completed')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                                    Remedial Lulus
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($final !== null)
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black {{ $passed ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ number_format($final, 0) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 font-bold">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $as->teacher->name }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 p-16 rounded-2xl text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <h3 class="mt-4 font-bold text-gray-700 text-lg">Belum Ada Nilai Harian</h3>
                    <p class="text-sm text-gray-400 mt-1">Belum ada catatan nilai harian formatif yang diunggah oleh guru mapel untuk anak Anda.</p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
