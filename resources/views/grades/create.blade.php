<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Input Nilai Akademik (Spreadsheet Mode)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('grades.create') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label for="classroom_id" class="block font-medium text-sm text-gray-700 mb-1">Pilih Kelas</label>
                        <select id="classroom_id" name="classroom_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms ?? [] as $classroom)
                                <option value="{{ $classroom->id }}" {{ $selectedClassroomId == $classroom->id ? 'selected' : '' }}>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full md:w-1/3">
                        <label for="subject_id" class="block font-medium text-sm text-gray-700 mb-1">Mata Pelajaran</label>
                        <select id="subject_id" name="subject_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required onchange="this.form.submit()">
                            <option value="">-- Pilih Mapel --</option>
                            @foreach($subjects ?? [] as $subject)
                                <option value="{{ $subject->id }}" {{ $selectedSubjectId == $subject->id ? 'selected' : '' }}>
                                    {{ $subject->name }}
                                </option>
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

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Spreadsheet Table -->
            @if($selectedClassroomId && $selectedSubjectId)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                    <form method="POST" action="{{ route('grades.store') }}">
                        @csrf
                        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                        <input type="hidden" name="subject_id" value="{{ $selectedSubjectId }}">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border-b border-gray-200">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider w-16 rounded-tl-xl border-r border-gray-700">No</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider border-r border-gray-700">Nama Siswa</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black text-amber-300 uppercase tracking-wider w-24 border-r border-gray-700">Nilai Tugas</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black text-blue-300 uppercase tracking-wider w-24 border-r border-gray-700">Nilai UH</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black text-emerald-300 uppercase tracking-wider w-24 border-r border-gray-700">Nilai PTS</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black text-purple-300 uppercase tracking-wider w-24 rounded-tr-xl">Nilai PAS</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($students ?? [] as $index => $student)
                                        @php
                                            $studentGrades = $existingGrades->get($student->id) ?? collect([]);
                                            $tugas = $studentGrades->firstWhere('type', 'tugas')->score ?? '';
                                            $uh = $studentGrades->firstWhere('type', 'uh')->score ?? '';
                                            $pts = $studentGrades->firstWhere('type', 'pts')->score ?? '';
                                            $pas = $studentGrades->firstWhere('type', 'pas')->score ?? '';
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-500 font-medium border-r border-gray-200 bg-gray-50 text-center">{{ $index + 1 }}</td>
                                            <td class="px-6 py-3 whitespace-nowrap text-sm font-bold text-gray-900 border-r border-gray-200">
                                                {{ $student->name }}
                                            </td>
                                            
                                            <!-- Tugas -->
                                            <td class="px-2 py-2 whitespace-nowrap border-r border-gray-200">
                                                <input type="number" step="0.01" min="0" max="100" name="grades[{{ $student->id }}][tugas]" value="{{ $tugas }}" class="block w-full text-center text-sm font-bold rounded-md shadow-sm border-transparent hover:border-gray-300 focus:border-amber-400 focus:ring focus:ring-amber-200 focus:ring-opacity-50 transition-all bg-amber-50">
                                            </td>
                                            
                                            <!-- UH -->
                                            <td class="px-2 py-2 whitespace-nowrap border-r border-gray-200">
                                                <input type="number" step="0.01" min="0" max="100" name="grades[{{ $student->id }}][uh]" value="{{ $uh }}" class="block w-full text-center text-sm font-bold rounded-md shadow-sm border-transparent hover:border-gray-300 focus:border-blue-400 focus:ring focus:ring-blue-200 focus:ring-opacity-50 transition-all bg-blue-50">
                                            </td>
                                            
                                            <!-- PTS -->
                                            <td class="px-2 py-2 whitespace-nowrap border-r border-gray-200">
                                                <input type="number" step="0.01" min="0" max="100" name="grades[{{ $student->id }}][pts]" value="{{ $pts }}" class="block w-full text-center text-sm font-bold rounded-md shadow-sm border-transparent hover:border-gray-300 focus:border-emerald-400 focus:ring focus:ring-emerald-200 focus:ring-opacity-50 transition-all bg-emerald-50">
                                            </td>
                                            
                                            <!-- PAS -->
                                            <td class="px-2 py-2 whitespace-nowrap">
                                                <input type="number" step="0.01" min="0" max="100" name="grades[{{ $student->id }}][pas]" value="{{ $pas }}" class="block w-full text-center text-sm font-bold rounded-md shadow-sm border-transparent hover:border-gray-300 focus:border-purple-400 focus:ring focus:ring-purple-200 focus:ring-opacity-50 transition-all bg-purple-50">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">
                                                Tidak ada data siswa di kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-6 bg-gray-100 border-t border-gray-200 flex justify-between items-center">
                            <p class="text-xs text-gray-500"><b class="text-gray-800">Tips:</b> Gunakan tombol <kbd class="px-2 py-1 bg-white border border-gray-300 rounded-md shadow-sm">Tab</kbd> pada keyboard untuk berpindah ke kotak nilai berikutnya dengan cepat.</p>
                            <button type="submit" class="inline-flex items-center px-8 py-3 bg-gray-900 border border-transparent rounded-xl font-black text-sm text-white uppercase tracking-widest hover:bg-gray-800 active:bg-gray-900 focus:outline-none focus:ring ring-gray-300 transition ease-in-out duration-150 shadow-lg">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                Simpan Semua Nilai
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-xl">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700 font-medium">
                                Silakan pilih <b>Kelas</b> dan <b>Mata Pelajaran</b> di atas untuk membuka Spreadsheet Input Nilai.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
