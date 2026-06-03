<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Rekap Presensi Harian') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
                <form method="GET" action="{{ route('attendances.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                    <div class="w-full md:w-1/3">
                        <label for="classroom_id" class="block font-medium text-sm text-gray-700 mb-1">Pilih Kelas</label>
                        <select id="classroom_id" name="classroom_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms as $classroom)
                                <option value="{{ $classroom->id }}" {{ $selectedClassroomId == $classroom->id ? 'selected' : '' }}>{{ $classroom->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full md:w-1/4">
                        <label for="month" class="block font-medium text-sm text-gray-700 mb-1">Bulan</label>
                        <select id="month" name="month" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ $month == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="w-full md:w-1/6">
                        <label for="year" class="block font-medium text-sm text-gray-700 mb-1">Tahun</label>
                        <input type="number" id="year" name="year" value="{{ $year }}" min="2020" max="2030" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="this.form.submit()">
                    </div>
                </form>
            </div>

            @if($summary)
            <!-- Summary Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 text-center">
                    <div class="text-3xl font-black text-emerald-600">{{ $summary->hadir }}</div>
                    <div class="text-xs font-bold text-emerald-500 uppercase tracking-wider mt-1">Hadir</div>
                </div>
                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-center">
                    <div class="text-3xl font-black text-blue-600">{{ $summary->sakit }}</div>
                    <div class="text-xs font-bold text-blue-500 uppercase tracking-wider mt-1">Sakit</div>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 text-center">
                    <div class="text-3xl font-black text-amber-600">{{ $summary->izin }}</div>
                    <div class="text-xs font-bold text-amber-500 uppercase tracking-wider mt-1">Izin</div>
                </div>
                <div class="bg-red-50 border border-red-200 rounded-2xl p-5 text-center">
                    <div class="text-3xl font-black text-red-600">{{ $summary->alpa }}</div>
                    <div class="text-xs font-bold text-red-500 uppercase tracking-wider mt-1">Alpa</div>
                </div>
            </div>

            <!-- Attendance Table -->
            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider w-12">No</th>
                                <th class="px-6 py-4 text-left text-xs font-black text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-emerald-600 uppercase tracking-wider w-16">H</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-blue-600 uppercase tracking-wider w-16">S</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-amber-600 uppercase tracking-wider w-16">I</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-red-600 uppercase tracking-wider w-16">A</th>
                                <th class="px-6 py-4 text-center text-xs font-black text-gray-500 uppercase tracking-wider w-20">% Hadir</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($students as $index => $student)
                                @php
                                    $studentAtt = $attendances->get($student->id, collect([]));
                                    $h = $studentAtt->where('status', 'H')->count();
                                    $s = $studentAtt->where('status', 'S')->count();
                                    $i = $studentAtt->where('status', 'I')->count();
                                    $a = $studentAtt->where('status', 'A')->count();
                                    $total = $h + $s + $i + $a;
                                    $pct = $total > 0 ? round(($h / $total) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 text-sm text-gray-500 font-medium">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        {{ $student?->user?->name }}
                                        <div class="text-xs text-gray-400 font-normal">NIS: {{ $student?->nis }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-emerald-600">{{ $h }}</td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-blue-600">{{ $s }}</td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-amber-600">{{ $i }}</td>
                                    <td class="px-6 py-4 text-center text-sm font-bold text-red-600">{{ $a }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $pct >= 90 ? 'bg-emerald-100 text-emerald-800' : ($pct >= 75 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                            {{ $pct }}%
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">Tidak ada data siswa di kelas ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif(!$selectedClassroomId)
                <div class="bg-blue-50 border-l-4 border-blue-500 p-6 rounded-r-xl">
                    <p class="text-sm text-blue-700 font-medium">Silakan pilih kelas pada filter di atas untuk melihat rekap presensi.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
