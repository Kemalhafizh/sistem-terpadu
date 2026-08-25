<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Kokpit Perkembangan Siswa (Student Progress Cockpit)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Pelacakan jejak akademik, kompetensi menyeluruh, dan deteksi dini risiko belajar siswa.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        @if($snapshot)
            <!-- Student Header Banner -->
            <div class="bg-gradient-to-r from-teal-600 to-emerald-700 rounded-3xl p-6 text-white shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-white/20">Persentil Sekolah: Top {{ 100 - $snapshot->overall_percentile }}%</span>
                    <h3 class="text-2xl font-black mt-2">{{ $selectedStudent->nama_lengkap ?? $selectedStudent->name }}</h3>
                    <p class="text-teal-100 text-sm">NIS: {{ $selectedStudent->nis ?? '-' }} &bull; {{ $selectedStudent->classroom->name ?? 'Kelas X' }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="px-4 py-2 rounded-2xl bg-white/20 backdrop-blur-md text-xs font-bold">
                        Status Risiko: {{ ucfirst($snapshot->at_risk_status) }}
                    </span>
                </div>
            </div>

            <!-- Learning Journey Timeline -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white flex items-center">
                        <svg class="w-5 h-5 mr-2 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Garis Waktu Perjalanan Belajar (Learning Journey)
                    </h3>
                    <div class="space-y-4 relative before:absolute before:inset-0 before:left-3.5 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800 pt-2">
                        @foreach($snapshot->academic_timeline ?? [] as $step)
                            <div class="relative flex items-start space-x-3">
                                <div class="w-7 h-7 rounded-full bg-emerald-500 text-white flex items-center justify-center font-bold text-xs shadow-md z-10">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700/60 flex-1">
                                    <div class="flex justify-between items-center text-xs">
                                        <h4 class="font-bold text-slate-900 dark:text-white">{{ $step['event'] }}</h4>
                                        <span class="text-emerald-600 font-bold">Skor: {{ $step['score'] }}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-400 mt-1 block">{{ $step['date'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Competency Breakdown -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Peta Kompetensi Terpadu</h3>
                    <div class="space-y-3 pt-2">
                        @foreach($snapshot->competency_radar ?? [] as $domain => $val)
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-bold">
                                    <span class="text-slate-700 dark:text-slate-300 uppercase">{{ ucfirst(str_replace('_', ' ', $domain)) }}</span>
                                    <span class="text-teal-600 dark:text-teal-400">{{ $val }}%</span>
                                </div>
                                <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-teal-500 to-emerald-400 rounded-full" style="width: {{ $val }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-slate-500">Belum ada snapshot perkembangan siswa.</p>
            </div>
        @endif
    </div>
</x-app-layout>
