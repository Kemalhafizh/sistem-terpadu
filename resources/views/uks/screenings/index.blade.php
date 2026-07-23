<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('uks.dashboard') }}" class="hover:text-teal-600 transition-colors">
                    <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </span>
                </a>
                <div>
                    <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                        {{ __('Health Screening Berkala') }}
                    </h2>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • DETEKSI DINI & SCREENING</p>
                </div>
            </div>

            <button type="button" @click="$dispatch('open-modal', 'create-screening-modal')" class="inline-flex items-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Buat Jadwal Screening
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/50 flex items-center text-teal-800 dark:text-teal-300">
                    <svg class="w-5 h-5 mr-3 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Screenings List Card -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col">
                <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center mb-6">
                    <span class="w-2 h-6 bg-teal-600 rounded-full mr-3"></span>
                    Jadwal & Riwayat Pemeriksaan Kesehatan
                </h4>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700/50 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                <th class="pb-3">Judul Screening</th>
                                <th class="pb-3">Tanggal Pelaksanaan</th>
                                <th class="pb-3 text-center">Tahun Ajaran</th>
                                <th class="pb-3 text-center">Status</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-55/30 dark:divide-gray-700/30">
                            @forelse($screenings as $scr)
                                <tr class="group hover:bg-gray-50/55 dark:hover:bg-gray-900/10 transition-all">
                                    <td class="py-4">
                                        <h5 class="text-sm font-extrabold text-gray-900 dark:text-white group-hover:text-teal-600 transition-colors">
                                            {{ $scr->title }}
                                        </h5>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold mt-0.5">
                                            Target: {{ count($scr->target_classrooms ?? []) }} Kelas
                                        </p>
                                    </td>
                                    <td class="py-4 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        {{ $scr->screening_date->translatedFormat('d F Y') }}
                                    </td>
                                    <td class="py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        {{ $scr->academicYear->name ?? '-' }}
                                    </td>
                                    <td class="py-4 text-center">
                                        @if($scr->status === 'completed')
                                            <span class="px-2.5 py-1 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/30 rounded-lg text-[10px] font-black uppercase tracking-wider">Selesai</span>
                                        @elseif($scr->status === 'in_progress')
                                            <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30 rounded-lg text-[10px] font-black uppercase tracking-wider">Berjalan</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-900/30 rounded-lg text-[10px] font-black uppercase tracking-wider">Direncanakan</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <!-- Input Results (Show if not closed/completed/ planned) -->
                                            <a href="{{ route('uks.screenings.results', $scr->id) }}" title="Input Hasil Screening"
                                               class="inline-flex items-center px-3 py-1.5 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 border border-teal-200/50 dark:border-teal-900/30 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-teal-100 dark:hover:bg-teal-900/40 transition-all">
                                                <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                                Input Hasil
                                            </a>

                                            @if($scr->status === 'completed')
                                                <!-- Report Link -->
                                                <a href="{{ route('uks.screenings.report', $scr->id) }}" title="Lihat Laporan"
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 border border-blue-200/50 dark:border-blue-900/30 rounded-xl text-xs font-black uppercase tracking-wider hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                                    </svg>
                                                    Laporan
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400 dark:text-gray-600 italic">Belum ada jadwal screening direncanakan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $screenings->links() }}
                </div>
            </div>

        </div>
    </div>

    <!-- Modal: Buat Screening Baru -->
    <x-modal name="create-screening-modal" focusable>
        <form method="POST" action="{{ route('uks.screenings.store') }}" class="p-6">
            @csrf
            <h3 class="text-lg font-black text-gray-900 dark:text-white mb-4">Buat Jadwal Screening Kesehatan</h3>
            
            <div class="space-y-4">
                <!-- Title -->
                <div class="space-y-1">
                    <x-input-label for="title" value="Judul Kegiatan *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500" />
                    <x-text-input name="title" id="title" type="text" placeholder="Contoh: Pemeriksaan Kesehatan Gigi & Mulut Semester 1" required class="w-full" />
                </div>

                <!-- Screening Date -->
                <div class="space-y-1">
                    <x-input-label for="screening_date" value="Tanggal Pemeriksaan *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500" />
                    <x-text-input name="screening_date" id="screening_date" type="date" required class="w-full" />
                </div>

                <!-- Academic Year -->
                <div class="space-y-1">
                    <x-input-label for="academic_year_id" value="Tahun Ajaran *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500" />
                    <select name="academic_year_id" id="academic_year_id" required
                            class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                        @foreach($academicYears as $ay)
                            <option value="{{ $ay->id }}" {{ $ay->is_active ? 'selected' : '' }}>{{ $ay->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Classrooms -->
                <div class="space-y-1">
                    <x-input-label value="Target Kelas *" class="text-xs font-black uppercase text-gray-400 dark:text-gray-500" />
                    <div class="grid grid-cols-2 gap-2 max-h-40 overflow-y-auto p-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900">
                        @foreach($classrooms as $cls)
                            <label class="inline-flex items-center text-xs text-gray-650 dark:text-gray-300">
                                <input type="checkbox" name="target_classrooms[]" value="{{ $cls->id }}" class="rounded text-teal-600 focus:ring-teal-500 mr-2 border-gray-350 dark:border-gray-700">
                                {{ $cls->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6">
                <button type="button" @click="$dispatch('close-modal', 'create-screening-modal')" class="px-4 py-2 border border-gray-200 dark:border-gray-700 rounded-xl text-xs font-black uppercase hover:bg-gray-50 dark:hover:bg-gray-750 transition-all text-gray-700 dark:text-gray-300">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase transition-all shadow-md shadow-teal-600/10">
                    Simpan Jadwal
                </button>
            </div>
        </form>
    </x-modal>

</x-app-layout>
