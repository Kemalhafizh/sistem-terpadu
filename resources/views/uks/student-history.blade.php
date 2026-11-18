<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('uks.visits.index') }}" class="hover:text-teal-600 transition-colors">
                <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </span>
            </a>
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                    {{ __('Riwayat Rekam Medis Siswa') }}
                </h2>
                <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">{{ mb_strtoupper(school_short_name()) }} • TIMELINE HISTORI KESEHATAN</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- STUDENT PROFILE HIGHLIGHT -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col sm:flex-row items-center sm:items-start gap-6">
                <!-- Avatar placeholder -->
                <div class="w-20 h-20 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-3xl flex items-center justify-center shrink-0 font-extrabold text-3xl shadow-inner border border-teal-100/50 dark:border-teal-900/30">
                    {{ substr($student->user->name ?? '-', 0, 1) }}
                </div>
                
                <!-- Student details -->
                <div class="flex-grow text-center sm:text-left space-y-4">
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $student->user->name }}</h3>
                        <p class="text-xs text-teal-600 dark:text-teal-400 font-extrabold uppercase tracking-widest mt-0.5">{{ $student->classroom->name ?? 'Tanpa Kelas' }}</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider mt-0.5">NIS: {{ $student->nis ?? '-' }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 max-w-md mx-auto sm:mx-0 pt-4 border-t border-gray-100 dark:border-gray-700">
                        <div class="space-y-1">
                            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider block">Golongan Darah</span>
                            <span class="px-2.5 py-0.5 bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 rounded-lg text-xs font-black">
                                {{ $student->blood_type ?: 'Belum Diketahui' }}
                            </span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider block">Total Kunjungan UKS</span>
                            <span class="text-sm font-extrabold text-gray-800 dark:text-white">
                                {{ $visits->count() }} Kali
                            </span>
                        </div>
                    </div>

                    @if($student->allergies)
                        <div class="space-y-1 pt-3 border-t border-gray-100 dark:border-gray-700">
                            <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-wider block text-left">Riwayat Alergi Murid</span>
                            <div class="p-3.5 bg-red-50/50 dark:bg-red-950/15 border border-red-100/50 dark:border-red-900/30 rounded-2xl text-xs text-red-700 dark:text-red-400 font-semibold leading-relaxed text-left">
                                {{ $student->allergies }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TIMELINE SECTION -->
            <div class="space-y-6">
                <h4 class="text-base font-black text-gray-900 dark:text-white flex items-center mb-6">
                    <span class="w-2 h-5 bg-teal-600 rounded-full mr-3"></span>
                    Histori Kronologis Kunjungan Medis
                </h4>

                <div class="relative pl-6 sm:pl-8 border-l border-gray-200 dark:border-gray-700 space-y-8 ml-4">
                    @forelse($visits as $visit)
                        @php
                            $sevColors = [
                                'berat' => 'bg-red-500 ring-red-100 dark:ring-red-950/50',
                                'sedang' => 'bg-amber-500 ring-amber-100 dark:ring-amber-950/50',
                                'ringan' => 'bg-teal-500 ring-teal-100 dark:ring-teal-950/50'
                            ];
                            $sevColor = $sevColors[$visit->severity] ?? 'bg-teal-500';
                            
                            $dispLabels = [
                                'kembali_kelas' => ['bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/30', 'Kembali Kelas'],
                                'istirahat_uks' => ['bg-teal-50 dark:bg-teal-950/30 text-teal-700 dark:text-teal-400 border-teal-200 dark:border-teal-900/30', 'Istirahat UKS'],
                                'pulang' => ['bg-blue-50 dark:bg-blue-950/30 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-900/30', 'Dipulangkan'],
                                'rujuk_rs' => ['bg-red-50 dark:bg-red-950/30 text-red-700 dark:text-red-400 border-red-200 dark:border-red-900/30', 'Dirujuk ke RS']
                            ];
                            $disp = $dispLabels[$visit->disposition] ?? ['bg-gray-50 text-gray-700', 'Istirahat'];
                        @endphp
                        
                        <!-- Timeline Item -->
                        <div class="relative group">
                            <!-- Dot Node -->
                            <span class="absolute -left-10 sm:-left-12 top-1.5 flex h-6 w-6 items-center justify-center rounded-full bg-white dark:bg-gray-800 ring-4 {{ $sevColor }}">
                                <span class="h-2 w-2 rounded-full bg-current"></span>
                            </span>

                            <!-- Main Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 space-y-4 hover:border-teal-500/20 transition-all duration-300">
                                
                                <!-- Card Header -->
                                <div class="flex justify-between items-start gap-4 flex-wrap">
                                    <div>
                                        <p class="text-xs font-black text-teal-600 dark:text-teal-400 uppercase tracking-widest">
                                            {{ $visit->visit_time->translatedFormat('d F Y • H:i') }} WIB
                                        </p>
                                        <h5 class="text-base font-extrabold text-gray-900 dark:text-white mt-1 group-hover:text-teal-600 transition-colors">
                                            Keluhan: "{{ $visit->complaint }}"
                                        </h5>
                                    </div>
                                    
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 border rounded text-[9px] font-black uppercase tracking-widest {{ $disp[0] }}">
                                            {{ $disp[1] }}
                                        </span>
                                        
                                        @if(in_array(auth()->user()->role, ['superadmin', 'admin', 'guru', 'bk', 'staf_tu', 'waka_kesiswaan']))
                                            <a href="{{ route('uks.visits.show', $visit->uuid) }}" 
                                               class="text-xs font-black text-teal-600 dark:text-teal-400 hover:underline">
                                                DETIL →
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <!-- Body details -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-3 border-t border-gray-100 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-400">
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Diagnosis Sementara</span>
                                        <p class="text-gray-800 dark:text-gray-200 font-extrabold">{{ $visit->diagnosis ?: '-' }}</p>
                                    </div>
                                    
                                    <div class="space-y-1">
                                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Penanganan Medis</span>
                                        <p class="text-gray-800 dark:text-gray-200">{{ $visit->treatment ?: '-' }}</p>
                                    </div>
                                </div>

                                <!-- Medicines administered -->
                                @if($visit->medicines->count() > 0)
                                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700 space-y-2">
                                        <span class="text-[9px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest block">Obat yang Diberikan</span>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($visit->medicines as $med)
                                                <span class="inline-flex items-center px-2.5 py-1 bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 rounded-lg text-xs font-bold">
                                                    {{ $med->name }} ({{ $med->pivot->quantity }} Pcs)
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Footer / Meta -->
                                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest">
                                    <span>Ditangani: {{ $visit->handler->name ?? '-' }}</span>
                                    
                                    @if($visit->parent_notified)
                                        <span class="text-emerald-600 dark:text-emerald-400 font-extrabold flex items-center">
                                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                            </svg>
                                            WA Sent to Parent
                                        </span>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 text-center border border-gray-100 dark:border-gray-700/50 flex flex-col items-center justify-center opacity-55">
                            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h5 class="text-sm font-black text-gray-800 dark:text-white uppercase tracking-wide">Belum Ada Riwayat Kunjungan</h5>
                            <p class="text-xs text-gray-400 mt-1">Siswa belum tercatat pernah masuk UKS dalam sistem digital.</p>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
