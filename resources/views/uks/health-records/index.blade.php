<x-app-layout>
    <x-slot name="header">
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
                    {{ __('Rekam Medis Siswa') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • DATABASE KESEHATAN</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Alert Session Messages -->
            @if(session('success'))
                <div class="rounded-2xl p-4 bg-teal-50 dark:bg-teal-950/30 border border-teal-200 dark:border-teal-900/50 flex items-center text-teal-800 dark:text-teal-300">
                    <svg class="w-5 h-5 mr-3 text-teal-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-bold">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Main Table Card -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                    <div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center">
                            <span class="w-2 h-6 bg-teal-600 rounded-full mr-3"></span>
                            Daftar Rekam Medis Murid Aktif
                        </h4>
                        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Total Data: {{ $records->total() }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700/50 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                <th class="pb-3">Siswa</th>
                                <th class="pb-3 text-center">Golongan Darah</th>
                                <th class="pb-3">Riwayat Alergi</th>
                                <th class="pb-3">Kondisi Kronis</th>
                                <th class="pb-3 text-center">Mata (Visus)</th>
                                <th class="pb-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-55/30 dark:divide-gray-700/30">
                            @forelse($records as $rec)
                                <tr class="group hover:bg-gray-50/55 dark:hover:bg-gray-900/10 transition-all">
                                    <td class="py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-xl flex items-center justify-center shrink-0 font-extrabold text-sm mr-3">
                                                {{ substr($rec->student->user->name ?? '-', 0, 1) }}
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-extrabold text-gray-900 dark:text-white">
                                                    {{ $rec->student->user->name ?? 'N/A' }}
                                                </h5>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold mt-0.5">
                                                    Kelas: {{ $rec->student->classroom->name ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center">
                                        <span class="px-3 py-1 bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border border-teal-200/50 dark:border-teal-900/30 rounded-xl text-xs font-black uppercase tracking-wider">
                                            {{ $rec->blood_type === 'unknown' ? '?' : $rec->blood_type }}
                                        </span>
                                    </td>
                                    <td class="py-4 max-w-xs text-xs font-semibold text-gray-650 dark:text-gray-400">
                                        @if(!empty($rec->allergies))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($rec->allergies as $alg)
                                                    <span class="px-2 py-0.5 bg-red-50 dark:bg-red-950/20 text-red-650 dark:text-red-400 rounded text-[10px] font-bold">{{ $alg }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 max-w-xs text-xs font-semibold text-gray-650 dark:text-gray-400">
                                        @if(!empty($rec->chronic_conditions))
                                            <div class="flex flex-wrap gap-1">
                                                @foreach($rec->chronic_conditions as $cond)
                                                    <span class="px-2 py-0.5 bg-amber-50 dark:bg-amber-950/20 text-amber-650 dark:text-amber-400 rounded text-[10px] font-bold">{{ $cond }}</span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600">-</span>
                                        @endif
                                    </td>
                                    <td class="py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        <span class="capitalize">{{ $rec->vision_status }}</span>
                                    </td>
                                    <td class="py-4 text-right">
                                        <a href="{{ route('uks.health-records.show', $rec->student_id) }}" title="Edit Rekam Medis"
                                           class="inline-flex items-center p-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl hover:bg-teal-50 dark:hover:bg-teal-950/25 hover:border-teal-200 dark:hover:border-teal-900 text-teal-600 dark:text-teal-400 transition-all">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400 dark:text-gray-600 italic">Belum ada data rekam medis terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $records->links() }}
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
