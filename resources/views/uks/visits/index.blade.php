<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight flex items-center">
                    <a href="{{ route('uks.dashboard') }}" class="hover:text-teal-600 transition-colors mr-2">
                        <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                        </span>
                    </a>
                    {{ __('Daftar Kunjungan UKS') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">{{ mb_strtoupper(school_short_name()) }} • CATATAN MEDIS KUNJUNGAN</p>
            </div>
            
            <a href="{{ route('uks.visits.create') }}" class="inline-flex items-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Catat Kunjungan Baru
            </a>
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

            @if(session('error'))
                <div class="rounded-2xl p-4 bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-900/50 flex items-center text-red-800 dark:text-red-300">
                    <svg class="w-5 h-5 mr-3 text-red-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Filter Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <form method="GET" action="{{ route('uks.visits.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <!-- Search Input -->
                    <div class="md:col-span-2 space-y-1">
                        <label for="search" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cari Siswa / Keluhan</label>
                        <div class="relative">
                            <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Cari nama siswa, NIS, atau keluhan..." 
                                class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 pl-10 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                        </div>
                    </div>

                    <!-- Severity Filter -->
                    <div class="space-y-1">
                        <label for="severity" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tingkat Gejala</label>
                        <select name="severity" id="severity" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                            <option value="">Semua Tingkat</option>
                            <option value="ringan" {{ request('severity') === 'ringan' ? 'selected' : '' }}>Ringan</option>
                            <option value="sedang" {{ request('severity') === 'sedang' ? 'selected' : '' }}>Sedang</option>
                            <option value="berat" {{ request('severity') === 'berat' ? 'selected' : '' }}>Berat</option>
                        </select>
                    </div>

                    <!-- Disposition Filter -->
                    <div class="space-y-1">
                        <label for="disposition" class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tindakan / Disposisi</label>
                        <select name="disposition" id="disposition" class="w-full bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl px-3 py-2.5 text-sm focus:border-teal-500 focus:ring-teal-500 dark:text-gray-300">
                            <option value="">Semua Tindakan</option>
                            <option value="kembali_kelas" {{ request('disposition') === 'kembali_kelas' ? 'selected' : '' }}>Kembali Kelas</option>
                            <option value="istirahat_uks" {{ request('disposition') === 'istirahat_uks' ? 'selected' : '' }}>Istirahat UKS</option>
                            <option value="pulang" {{ request('disposition') === 'pulang' ? 'selected' : '' }}>Pulang</option>
                            <option value="rujuk_rs" {{ request('disposition') === 'rujuk_rs' ? 'selected' : '' }}>Rujuk RS</option>
                        </select>
                    </div>

                    <!-- Filter Actions -->
                    <div class="md:col-span-4 flex justify-end gap-2 mt-2">
                        @if(request()->anyFilled(['search', 'severity', 'disposition']))
                            <a href="{{ route('uks.visits.index') }}" class="px-4 py-2 border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-750 text-gray-700 dark:text-gray-300 rounded-xl text-xs font-black uppercase tracking-wider transition-all">
                                Reset Filter
                            </a>
                        @endif
                        <button type="submit" class="px-4 py-2 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Table Panel -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700">
                                <th class="pb-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Siswa / Kelas</th>
                                <th class="pb-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Waktu Kunjungan</th>
                                <th class="pb-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Keluhan & Diagnosis</th>
                                <th class="pb-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 text-center">Tingkat Gejala</th>
                                <th class="pb-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 text-center">Disposisi</th>
                                <th class="pb-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 text-center">Parent WA</th>
                                <th class="pb-4 text-xs font-black uppercase tracking-widest text-gray-400 dark:text-gray-500 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($visits as $visit)
                                <tr class="group hover:bg-gray-50/50 dark:hover:bg-gray-900/20 transition-all">
                                    <!-- Student details -->
                                    <td class="py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-xl flex items-center justify-center shrink-0 font-extrabold text-sm mr-3">
                                                {{ substr($visit->student->user->name ?? '-', 0, 1) }}
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-extrabold text-gray-900 dark:text-white group-hover:text-teal-600 transition-colors">
                                                    {{ $visit->student->user->name ?? 'N/A' }}
                                                </h5>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold mt-0.5">
                                                    Kelas: {{ $visit->student->classroom->name ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Time of visit -->
                                    <td class="py-4 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        <div>{{ $visit->visit_time->translatedFormat('d M Y') }}</div>
                                        <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-0.5 font-bold uppercase tracking-wider">
                                            {{ $visit->visit_time->translatedFormat('H:i') }} WIB
                                            @if($visit->leave_time)
                                                - {{ $visit->leave_time->translatedFormat('H:i') }}
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Complaint & Diagnosis -->
                                    <td class="py-4 max-w-xs">
                                        <div class="text-xs font-extrabold text-teal-600 dark:text-teal-400 truncate">
                                            "{{ $visit->complaint }}"
                                        </div>
                                        @if($visit->diagnosis)
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5" title="{{ $visit->diagnosis }}">
                                                Diagnosis: {{ $visit->diagnosis }}
                                            </div>
                                        @else
                                            <div class="text-[10px] text-gray-300 dark:text-gray-600 italic mt-0.5">Belum didiagnosis</div>
                                        @endif
                                    </td>

                                    <!-- Severity Badge -->
                                    <td class="py-4 text-center">
                                        @if($visit->severity === 'berat')
                                            <span class="px-2.5 py-1 bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border border-red-200 dark:border-red-900/30 rounded-lg text-[10px] font-black uppercase tracking-wider">Berat</span>
                                        @elseif($visit->severity === 'sedang')
                                            <span class="px-2.5 py-1 bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30 rounded-lg text-[10px] font-black uppercase tracking-wider">Sedang</span>
                                        @else
                                            <span class="px-2.5 py-1 bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border border-teal-200 dark:border-teal-900/30 rounded-lg text-[10px] font-black uppercase tracking-wider">Ringan</span>
                                        @endif
                                    </td>

                                    <!-- Disposition Badge -->
                                    <td class="py-4 text-center">
                                        @php
                                            $dispLabels = [
                                                'kembali_kelas' => ['bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/30', 'Kembali Kelas'],
                                                'istirahat_uks' => ['bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border-teal-200 dark:border-teal-900/30', 'Istirahat UKS'],
                                                'pulang' => ['bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-900/30', 'Pulang'],
                                                'rujuk_rs' => ['bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-900/30', 'Rujuk RS']
                                            ];
                                            $disp = $dispLabels[$visit->disposition] ?? ['bg-gray-50 text-gray-700', 'Istirahat'];
                                        @endphp
                                        <span class="px-2.5 py-1 {{ $disp[0] }} border rounded-lg text-[10px] font-black uppercase tracking-wider">{{ $disp[1] }}</span>
                                    </td>

                                    <!-- Parent WA Badge -->
                                    <td class="py-4 text-center">
                                        @if($visit->parent_notified)
                                            <span class="inline-flex items-center text-emerald-600 dark:text-emerald-400 font-extrabold text-xs" title="Terkirim via Whatsapp">
                                                <svg class="w-4 h-4 mr-1 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                </svg>
                                                Sent
                                            </span>
                                        @else
                                            <span class="inline-flex items-center text-gray-400 dark:text-gray-500 text-xs">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                                </svg>
                                                No / NA
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-4 text-right">
                                        <div class="flex items-center justify-end gap-1">
                                            <!-- Timeline History link -->
                                            <a href="{{ route('uks.student-history', $visit->student->id) }}" title="Riwayat Kesehatan Murid"
                                               class="p-2 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-750 text-gray-500 dark:text-gray-400 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </a>

                                            <!-- Show Details -->
                                            <a href="{{ route('uks.visits.show', $visit->uuid) }}" title="Rincian Kunjungan"
                                               class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-teal-600 dark:text-teal-400 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>

                                            <!-- Edit Visit -->
                                            <a href="{{ route('uks.visits.edit', $visit->id) }}" title="Edit Kunjungan"
                                               class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 text-amber-650 dark:text-amber-400 transition-all">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                                </svg>
                                            </a>

                                            <!-- Cancel / Delete Visit -->
                                            <form action="{{ route('uks.visits.destroy', $visit->uuid) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan/menghapus catatan kunjungan ini? Stok obat yang diberikan akan dikembalikan otomatis.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Batalkan Kunjungan" 
                                                        class="p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-red-50 dark:hover:bg-red-950/20 text-red-600 dark:text-red-500 transition-all">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center">
                                        <div class="flex flex-col items-center justify-center opacity-50">
                                            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <p class="text-sm font-bold text-gray-500 dark:text-gray-400">Tidak ada data kunjungan yang cocok dengan pencarian / filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $visits->appends(request()->query())->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
