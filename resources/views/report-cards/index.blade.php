<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kompilasi E-Rapor (Wali Kelas)') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filter Section -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-center gap-4">
                <form method="GET" action="{{ route('report-cards.index') }}" class="flex w-full md:w-auto items-end gap-4">
                    <div class="w-full md:w-64">
                        <label for="classroom_id" class="block font-medium text-sm text-gray-700 mb-1">Pilih Kelas Binaan</label>
                        <select id="classroom_id" name="classroom_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" required onchange="this.form.submit()">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classrooms ?? [] as $classroom)
                                <option value="{{ $classroom->id }}" {{ $selectedClassroomId == $classroom->id ? 'selected' : '' }}>
                                    {{ $classroom->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>

                @if($selectedClassroomId)
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('report-cards.sync') }}">
                            @csrf
                            <input type="hidden" name="classroom_id" value="{{ $selectedClassroomId }}">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-amber-600 border border-transparent rounded-lg font-bold text-xs text-white uppercase tracking-widest hover:bg-amber-700 active:bg-amber-900 focus:outline-none focus:border-amber-900 focus:ring ring-amber-300 shadow-sm transition-all">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                Kalkulasi Ulang Rapor
                            </button>
                        </form>

                        <button type="button" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 shadow-sm transition-all">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                            Cetak PDF Masal
                        </button>
                    </div>
                @endif
            </div>

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative" role="alert">
                    <span class="block sm:inline font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Ledger Table -->
            @if($selectedClassroomId)
                <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-200">
                    <form method="POST" action="{{ route('report-cards.store-notes') }}">
                        @csrf
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-primary-900 text-white">
                                    <tr>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-16">Rank</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Nama Siswa</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-32 bg-primary-800">Total Nilai</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-32 bg-primary-700">Rata-Rata</th>
                                        <th scope="col" class="px-6 py-4 text-left text-xs font-black uppercase tracking-wider">Catatan Wali Kelas</th>
                                        <th scope="col" class="px-6 py-4 text-center text-xs font-black uppercase tracking-wider w-24">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @forelse($students ?? [] as $index => $student)
                                        @php
                                            $report = $reportCards->get($student->id);
                                            $rank = $report ? $report->rank_in_class : '-';
                                            $total = $report ? $report->total_score : 0;
                                            $avg = $report ? $report->average_score : 0;
                                            $notes = $report ? $report->homeroom_notes : '';
                                            $reportId = $report ? $report->id : null;
                                        @endphp
                                        <tr class="hover:bg-blue-50 transition-colors">
                                            <!-- Rank -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                @if($rank == 1)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-100 text-amber-600 font-black border-2 border-amber-300" title="Juara 1">1</span>
                                                @elseif($rank == 2)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-500 font-black border-2 border-gray-300" title="Juara 2">2</span>
                                                @elseif($rank == 3)
                                                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-600 font-black border-2 border-orange-300" title="Juara 3">3</span>
                                                @else
                                                    <span class="text-sm font-bold text-gray-500">{{ $rank }}</span>
                                                @endif
                                            </td>
                                            
                                            <!-- Nama -->
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-black text-gray-900">
                                                {{ $student->user->name ?? 'Siswa' }}
                                                <div class="text-xs font-normal text-gray-400">NIS: {{ $student->nis }}</div>
                                            </td>
                                            
                                            <!-- Total -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center font-bold text-gray-700 bg-gray-50">
                                                {{ number_format($total, 2) }}
                                            </td>
                                            
                                            <!-- Rata-rata -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center font-black text-primary-600 bg-primary-50">
                                                {{ number_format($avg, 2) }}
                                            </td>
                                            
                                            <!-- Catatan -->
                                            <td class="px-6 py-4">
                                                @if($reportId)
                                                    <input type="text" name="report_cards[{{ $reportId }}][homeroom_notes]" value="{{ $notes }}" class="block w-full text-sm rounded-md shadow-sm border-gray-300 focus:border-primary-400 focus:ring focus:ring-primary-200 focus:ring-opacity-50 transition-colors" placeholder="Ketik catatan untuk siswa ini...">
                                                @else
                                                    <span class="text-xs text-red-500 italic">Error: Data rapor gagal dikompilasi.</span>
                                                @endif
                                            </td>

                                            <!-- Aksi Cetak PDF -->
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <a href="{{ route('report-cards.pdf', $student->id) }}" target="_blank" class="inline-flex items-center px-3 py-1 bg-gray-800 hover:bg-gray-700 text-white text-xs font-bold rounded-lg shadow-sm transition-colors">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                    PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">
                                                Tidak ada data siswa di kelas ini.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-primary-600 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-widest hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 transition ease-in-out duration-150 shadow-md">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Simpan Seluruh Catatan
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
                                Silakan pilih kelas binaan Anda untuk melihat rekapitulasi nilai akhir dan memberikan catatan pada e-Rapor siswa.
                            </p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
