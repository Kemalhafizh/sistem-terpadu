<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Daftar Penilaian Harian') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Quick Stats & Actions -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <div>
                    <h3 class="font-bold text-gray-700 text-lg">Manajemen Nilai Formatif & Harian</h3>
                    <p class="text-sm text-gray-500">Mencatat ulangan harian, kuis, kriteria penilaian, dan penugasan Kurikulum Merdeka.</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('daily-assessments.create') }}" class="inline-flex items-center px-4 py-2.5 bg-gray-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring ring-gray-300 transition duration-150 shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Buat Penilaian Baru
                    </a>
                    <a href="{{ route('daily-assessments.remedial') }}" class="inline-flex items-center px-4 py-2.5 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-amber-100 transition duration-150 shadow-sm">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Daftar Remedial
                    </a>
                </div>
            </div>

            <!-- Filter Card -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('daily-assessments.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/2">
                        <label for="classroom_id" class="block font-medium text-sm text-gray-700 mb-1">Filter Kelas</label>
                        <select id="classroom_id" name="classroom_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="this.form.submit()">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($classrooms as $cls)
                                <option value="{{ $cls->id }}" {{ request('classroom_id') == $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-1/2">
                        <label for="subject_id" class="block font-medium text-sm text-gray-700 mb-1">Filter Mata Pelajaran</label>
                        <select id="subject_id" name="subject_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="this.form.submit()">
                            <option value="">-- Semua Mapel --</option>
                            @foreach($subjects as $sbj)
                                <option value="{{ $sbj->id }}" {{ request('subject_id') == $sbj->id ? 'selected' : '' }}>{{ $sbj->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Assessment Table / List -->
            @if($assessments->count() > 0)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Judul Penilaian</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Kelas</th>
                                    <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Tipe</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-16">KKM</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider">Status Nilai</th>
                                    <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-40">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($assessments as $as)
                                    @php
                                        $typeColors = [
                                            'ulangan_harian' => 'bg-red-50 text-red-700 border-red-200',
                                            'kuis' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'praktikum' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'project' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'presentasi' => 'bg-purple-50 text-purple-700 border-purple-200',
                                            'tugas' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        ];
                                        $typeLabels = [
                                            'ulangan_harian' => 'Ulangan Harian',
                                            'kuis' => 'Kuis',
                                            'praktikum' => 'Praktikum',
                                            'project' => 'Proyek',
                                            'presentasi' => 'Presentasi',
                                            'tugas' => 'Tugas',
                                        ];
                                        
                                        $gradedCount = $as->scores->filter(fn($s) => $s->score !== null)->count();
                                        $totalStud = $as->scores->count();
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-bold text-gray-900">{{ $as->title }}</div>
                                            <div class="text-xs text-gray-400">T.A. {{ $as->academicYear->year }} ({{ $as->academicYear->semester }})</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                            {{ $as->classroom->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">
                                            {{ $as->subject->name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $typeColors[$as->type] ?? 'bg-gray-50 text-gray-700 border-gray-200' }}">
                                                {{ $typeLabels[$as->type] ?? $as->type }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                            {{ $as->assessment_date->isoFormat('D MMM YYYY') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-800">
                                            {{ number_format($as->effective_kkm, 0) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center">
                                            @if($gradedCount === 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600 border border-gray-200">
                                                    Belum Dinilai
                                                </span>
                                            @elseif($gradedCount < $totalStud)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                                    Parsial ({{ $gradedCount }}/{{ $totalStud }})
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    Lengkap
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm flex justify-center gap-2">
                                            <a href="{{ route('daily-assessments.show', $as->id) }}" class="inline-flex items-center px-3 py-1.5 bg-blue-50 text-blue-700 border border-blue-100 hover:bg-blue-100 text-xs font-bold rounded-xl transition duration-150">
                                                Detail
                                            </a>
                                            <a href="{{ route('daily-assessments.input-scores', $as->id) }}" class="inline-flex items-center px-3 py-1.5 bg-gray-900 text-white hover:bg-gray-800 text-xs font-bold rounded-xl transition duration-150">
                                                Nilai
                                            </a>
                                            <a href="{{ route('daily-assessments.analytics', $as->classroom_id) }}?subject_id={{ $as->subject_id }}" class="inline-flex items-center px-3 py-1.5 bg-purple-50 text-purple-700 border border-purple-100 hover:bg-purple-100 text-xs font-bold rounded-xl transition duration-150">
                                                Analitik
                                            </a>
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
                    <h3 class="mt-4 font-bold text-gray-700 text-lg">Belum Ada Penilaian</h3>
                    <p class="text-sm text-gray-400 mt-1">Anda belum membuat rencana penilaian harian untuk kelas dan pelajaran ini.</p>
                    <a href="{{ route('daily-assessments.create') }}" class="mt-6 inline-flex items-center px-4 py-2.5 bg-gray-900 border border-transparent rounded-xl font-bold text-xs text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-900 focus:outline-none transition duration-150">
                        Buat Penilaian Baru
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
