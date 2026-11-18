<x-app-layout>
    <x-slot name="title">Perbandingan Cohort Alumni</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                     Cohort Comparison (Perbandingan Angkatan)
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Bandingkan statistik penyerapan dan keselarasan kurikulum antar-angkatan alumni.
                </p>
            </div>
            <a href="{{ route('alumni.tracer-study.analytics') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅ Kembali ke Analitik
            </a>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Pemilih Angkatan -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl">
            <form method="GET" action="{{ route('alumni.tracer-study.comparison') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Angkatan A</label>
                    <select name="year_a" required class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                        @foreach($graduationYears as $yr)
                            <option value="{{ $yr }}" {{ $yearA == $yr ? 'selected' : '' }}>Angkatan {{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Angkatan B</label>
                    <select name="year_b" required class="w-full bg-gray-50/50 dark:bg-gray-900/50 border border-gray-200 dark:border-gray-700 rounded-xl px-4 py-2.5 text-xs text-gray-800 dark:text-white focus:outline-none">
                        @foreach($graduationYears as $yr)
                            <option value="{{ $yr }}" {{ $yearB == $yr ? 'selected' : '' }}>Angkatan {{ $yr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white rounded-xl py-2.5 px-4 text-xs font-bold transition-all shadow-md">
                         Bandingkan Data
                    </button>
                </div>
            </form>
        </div>

        <!-- Kolom Perbandingan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Cohort A Card -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border-gray-200 dark:border-gray-700 shadow-xl space-y-6 border-t-4 border-t-blue-500">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700/50">
                    <h3 class="font-black text-lg text-blue-600 dark:text-blue-400"> Angkatan {{ $yearA }}</h3>
                    <span class="px-2.5 py-1 bg-blue-50 dark:bg-blue-950/20 text-blue-700 dark:text-blue-400 font-extrabold text-[10px] rounded-lg">Cohort A</span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs text-center">
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                        <span class="block text-[9px] text-gray-400 font-bold uppercase">Total Alumni</span>
                        <p class="font-extrabold text-base text-gray-800 dark:text-white mt-1">{{ $comparison['cohort_a']['total'] }} orang</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                        <span class="block text-[9px] text-gray-400 font-bold uppercase">Response Rate</span>
                        <p class="font-extrabold text-base text-gray-800 dark:text-white mt-1">{{ $comparison['cohort_a']['response_rate'] }}%</p>
                    </div>
                </div>

                <!-- Activity Bars -->
                <div class="space-y-3">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider"> Distribusi Aktivitas</h4>
                    
                    <div class="space-y-2 text-xs">
                        <!-- Kuliah -->
                        <div>
                            <div class="flex justify-between font-bold mb-1">
                                <span>Kuliah</span>
                                <span>{{ $comparison['cohort_a']['activity_distribution']['study'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-blue-500 h-full rounded-full" style="width: {{ $comparison['cohort_a']['activity_distribution']['study'] }}%"></div>
                            </div>
                        </div>

                        <!-- Kerja -->
                        <div>
                            <div class="flex justify-between font-bold mb-1">
                                <span>Kerja</span>
                                <span>{{ $comparison['cohort_a']['activity_distribution']['work'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $comparison['cohort_a']['activity_distribution']['work'] }}%"></div>
                            </div>
                        </div>

                        <!-- Wirausaha -->
                        <div>
                            <div class="flex justify-between font-bold mb-1">
                                <span>Wirausaha</span>
                                <span>{{ $comparison['cohort_a']['activity_distribution']['entrepreneur'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-purple-500 h-full rounded-full" style="width: {{ $comparison['cohort_a']['activity_distribution']['entrepreneur'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scores -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 grid grid-cols-2 gap-4 text-xs text-center">
                    <div class="p-3 bg-indigo-50/30 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/20">
                        <span class="block text-[9px] text-gray-400 font-bold uppercase">Avg Keselarasan</span>
                        <p class="font-extrabold text-base text-indigo-800 dark:text-indigo-300 mt-1">{{ $comparison['cohort_a']['avg_relevance'] }} / 5</p>
                    </div>
                    <div class="p-3 bg-indigo-50/30 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/20">
                        <span class="block text-[9px] text-gray-400 font-bold uppercase">Avg Kepuasan</span>
                        <p class="font-extrabold text-base text-indigo-800 dark:text-indigo-300 mt-1">{{ $comparison['cohort_a']['avg_satisfaction'] }} / 5</p>
                    </div>
                </div>
            </div>

            <!-- Cohort B Card -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border-gray-200 dark:border-gray-700 shadow-xl space-y-6 border-t-4 border-t-purple-500">
                <div class="flex justify-between items-center pb-4 border-b border-gray-100 dark:border-gray-700/50">
                    <h3 class="font-black text-lg text-purple-600 dark:text-purple-400"> Angkatan {{ $yearB }}</h3>
                    <span class="px-2.5 py-1 bg-purple-50 dark:bg-purple-950/20 text-purple-700 dark:text-purple-400 font-extrabold text-[10px] rounded-lg">Cohort B</span>
                </div>

                <div class="grid grid-cols-2 gap-4 text-xs text-center">
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                        <span class="block text-[9px] text-gray-400 font-bold uppercase">Total Alumni</span>
                        <p class="font-extrabold text-base text-gray-800 dark:text-white mt-1">{{ $comparison['cohort_b']['total'] }} orang</p>
                    </div>
                    <div class="p-3 bg-gray-50 dark:bg-gray-900 rounded-2xl">
                        <span class="block text-[9px] text-gray-400 font-bold uppercase">Response Rate</span>
                        <p class="font-extrabold text-base text-gray-800 dark:text-white mt-1">{{ $comparison['cohort_b']['response_rate'] }}%</p>
                    </div>
                </div>

                <!-- Activity Bars -->
                <div class="space-y-3">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider"> Distribusi Aktivitas</h4>
                    
                    <div class="space-y-2 text-xs">
                        <!-- Kuliah -->
                        <div>
                            <div class="flex justify-between font-bold mb-1">
                                <span>Kuliah</span>
                                <span>{{ $comparison['cohort_b']['activity_distribution']['study'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-blue-500 h-full rounded-full" style="width: {{ $comparison['cohort_b']['activity_distribution']['study'] }}%"></div>
                            </div>
                        </div>

                        <!-- Kerja -->
                        <div>
                            <div class="flex justify-between font-bold mb-1">
                                <span>Kerja</span>
                                <span>{{ $comparison['cohort_b']['activity_distribution']['work'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: {{ $comparison['cohort_b']['activity_distribution']['work'] }}%"></div>
                            </div>
                        </div>

                        <!-- Wirausaha -->
                        <div>
                            <div class="flex justify-between font-bold mb-1">
                                <span>Wirausaha</span>
                                <span>{{ $comparison['cohort_b']['activity_distribution']['entrepreneur'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                <div class="bg-purple-500 h-full rounded-full" style="width: {{ $comparison['cohort_b']['activity_distribution']['entrepreneur'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scores -->
                <div class="pt-4 border-t border-gray-100 dark:border-gray-700/50 grid grid-cols-2 gap-4 text-xs text-center">
                    <div class="p-3 bg-indigo-50/30 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/20">
                        <span class="block text-[9px] text-gray-400 font-bold uppercase">Avg Keselarasan</span>
                        <p class="font-extrabold text-base text-indigo-800 dark:text-indigo-300 mt-1">{{ $comparison['cohort_b']['avg_relevance'] }} / 5</p>
                    </div>
                    <div class="p-3 bg-indigo-50/30 dark:bg-indigo-950/10 rounded-2xl border border-indigo-100/50 dark:border-indigo-900/20">
                        <span class="block text-[9px] text-gray-400 font-bold uppercase">Avg Kepuasan</span>
                        <p class="font-extrabold text-base text-indigo-800 dark:text-indigo-300 mt-1">{{ $comparison['cohort_b']['avg_satisfaction'] }} / 5</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
