<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Presensi Harian Siswa') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('attendances.create') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label for="classroom_id" class="block font-medium text-sm text-gray-700 mb-1">Pilih Kelas</label>
                        <select id="classroom_id" name="classroom_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ $selectedClassroomId == $classroom->id ? 'selected' : '' }}>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="w-full md:w-1/3">
                        <label for="date" class="block font-medium text-sm text-gray-700 mb-1">Tanggal Presensi</label>
                        <input type="date" id="date" name="date" value="{{ $date }}" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required onchange="this.form.submit()">
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

            <!-- Attendance Table -->
            @if($selectedClassroomId)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                    <form method="POST" action="{{ route('attendances.store') }}">
                        @csrf
                        <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                        <input type="hidden" name="date" value="{{ $date }}">

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider w-16">No</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black text-emerald-600 uppercase tracking-wider w-24">Hadir</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black text-blue-600 uppercase tracking-wider w-24">Sakit</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black text-amber-600 uppercase tracking-wider w-24">Izin</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black text-red-600 uppercase tracking-wider w-24">Alpa</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($students as $index => $student)
                                        @php
                                            $existing = $existingAttendances->get($student->id);
                                            $status = $existing ? $existing->status : 'H';
                                            $notes = $existing ? $existing->notes : '';
                                        @endphp
                                        <tr class="hover:bg-gray-50 transition-colors">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                                {{ $student?->user?->name }}
                                                <div class="text-xs text-gray-400 font-normal">NIS: {{ $student?->nis }}</div>
                                            </td>
                                            
                                            <!-- Hadir -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="attendances[{{ $student->id }}][status]" value="H" class="w-5 h-5 text-emerald-600 border-gray-300 focus:ring-emerald-500 cursor-pointer" {{ $status == 'H' ? 'checked' : '' }}>
                                                </label>
                                            </td>
                                            
                                            <!-- Sakit -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="attendances[{{ $student->id }}][status]" value="S" class="w-5 h-5 text-blue-600 border-gray-300 focus:ring-blue-500 cursor-pointer" {{ $status == 'S' ? 'checked' : '' }}>
                                                </label>
                                            </td>
                                            
                                            <!-- Izin -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="attendances[{{ $student->id }}][status]" value="I" class="w-5 h-5 text-amber-500 border-gray-300 focus:ring-amber-400 cursor-pointer" {{ $status == 'I' ? 'checked' : '' }}>
                                                </label>
                                            </td>
                                            
                                            <!-- Alpa -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="attendances[{{ $student->id }}][status]" value="A" class="w-5 h-5 text-red-600 border-gray-300 focus:ring-red-500 cursor-pointer" {{ $status == 'A' ? 'checked' : '' }}>
                                                </label>
                                            </td>
                                            
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input type="text" name="attendances[{{ $student->id }}][notes]" value="{{ $notes }}" class="block w-full text-sm rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50 placeholder-gray-300" placeholder="Opsional...">
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                                Tidak ada data siswa di kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-primary-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 disabled:opacity-25 transition ease-in-out duration-150 shadow-md">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Data Presensi
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
                                Silakan pilih kelas terlebih dahulu pada filter di atas untuk mulai mengisi data presensi.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
