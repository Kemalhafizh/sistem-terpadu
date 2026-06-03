<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Rekap Nilai Akademik') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('grades.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label for="classroom_id" class="block font-medium text-sm text-gray-700 mb-1">Pilih Kelas</label>
                        <select id="classroom_id" name="classroom_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms ?? [] as $classroom)
                                <option value="{{ $classroom->id }}" {{ $selectedClassroomId == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-1/3">
                        <label for="subject_id" class="block font-medium text-sm text-gray-700 mb-1">Mata Pelajaran</label>
                        <select id="subject_id" name="subject_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="this.form.submit()">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects ?? [] as $subj)
                                <option value="{{ $subj->id }}" {{ $selectedSubjectId == $subj->id ? 'selected' : '' }}>{{ $subj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            @if($students->count() > 0)
            <!-- Grade Table -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="p-4 bg-gray-50 border-b border-gray-200">
                    <h3 class="font-bold text-gray-700">
                        Nilai {{ $subject->name ?? '' }} — KKM: <span class="text-primary-600">{{ $subject->kkm ?? 75 }}</span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider w-12">No</th>
                                <th class="px-4 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                                <th class="px-4 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-20">Tugas</th>
                                <th class="px-4 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-20">UH</th>
                                <th class="px-4 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-20">PTS</th>
                                <th class="px-4 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-20">PAS</th>
                                <th class="px-4 py-4 text-center text-xs font-black text-primary-600 uppercase tracking-wider w-24">Rata-rata</th>
                                <th class="px-4 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-24">Predikat</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($students ?? [] as $index => $student)
                                @php
                                    $sg = $gradeData->get($student->id, collect([]));
                                    $tugas = $sg->get('tugas')?->score;
                                    $uh    = $sg->get('uh')?->score;
                                    $pts   = $sg->get('pts')?->score;
                                    $pas   = $sg->get('pas')?->score;

                                    $scores = collect([$tugas, $uh, $pts, $pas])->filter(fn($v) => $v !== null);
                                    $avg = $scores->count() > 0 ? round($scores->avg(), 1) : null;

                                    $kkm = $subject->kkm ?? 75;
                                    $passed = $avg !== null && $avg >= $kkm;

                                    if ($avg === null) $predikat = '-';
                                    elseif ($avg >= 90) $predikat = 'A';
                                    elseif ($avg >= 80) $predikat = 'B';
                                    elseif ($avg >= $kkm) $predikat = 'C';
                                    else $predikat = 'D';
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-4 py-4 text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-4 py-4 text-sm font-bold text-gray-900">
                                        {{ $student->name }}
                                        <div class="text-xs text-gray-400 font-normal">NIS: {{ $student->nis }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm {{ $tugas !== null && $tugas < $kkm ? 'text-red-600 font-bold' : 'text-gray-700' }}">{{ $tugas !== null ? number_format($tugas, 0) : '-' }}</td>
                                    <td class="px-4 py-4 text-center text-sm {{ $uh !== null && $uh < $kkm ? 'text-red-600 font-bold' : 'text-gray-700' }}">{{ $uh !== null ? number_format($uh, 0) : '-' }}</td>
                                    <td class="px-4 py-4 text-center text-sm {{ $pts !== null && $pts < $kkm ? 'text-red-600 font-bold' : 'text-gray-700' }}">{{ $pts !== null ? number_format($pts, 0) : '-' }}</td>
                                    <td class="px-4 py-4 text-center text-sm {{ $pas !== null && $pas < $kkm ? 'text-red-600 font-bold' : 'text-gray-700' }}">{{ $pas !== null ? number_format($pas, 0) : '-' }}</td>
                                    <td class="px-4 py-4 text-center">
                                        @if($avg !== null)
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-black {{ $passed ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $avg }}
                                            </span>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center text-sm font-bold {{ $predikat === 'D' ? 'text-red-600' : ($predikat === 'A' ? 'text-emerald-600' : 'text-gray-700') }}">{{ $predikat }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif(!$selectedClassroomId || !$selectedSubjectId)
                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-xl">
                    <p class="text-sm text-blue-700 font-medium">Silakan pilih kelas dan mata pelajaran pada filter di atas untuk melihat rekap nilai.</p>
                </div>
            @else
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 p-12 rounded-2xl text-center">
                    <p class="text-gray-400 font-bold">Belum ada data nilai untuk filter yang dipilih.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
